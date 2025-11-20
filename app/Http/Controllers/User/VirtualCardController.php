<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\VirtualCardMethod;
use App\Models\VirtualCardOrder;
use App\Models\VirtualCardTransaction;
use App\Services\VirtualCard\stripe\Card;
use App\Traits\ManageWallet;
use App\Traits\Notify;
use App\Traits\Upload;
use App\Traits\VirtualCardTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Stevebauman\Purify\Facades\Purify;

require_once(base_path('app/Services/stripe-php/init.php'));

class VirtualCardController extends Controller
{
    use Notify,Upload,ManageWallet, VirtualCardTrait;

    public function __construct()
    {
        $this->theme = template();
    }

    public function index()
    {
        $userId = auth()->id();
        $cards = VirtualCardOrder::query()->where('user_id', $userId);

        $hasPendingOrders = $cards->clone()->whereIn('status', [0, 3, 4])->exists();

        if (!$hasPendingOrders && basicControl()->v_card_multiple == 0) {
            $hasStatusOneOrder = $cards->clone()->where('status', 1)->exists();
            $orderLock = $hasStatusOneOrder ? 'true' : 'false';
        } else {
            $orderLock = $hasPendingOrders ? 'true' : 'false';
        }

        $data['cardOrder'] = $cards->clone()
            ->where('status',2)->where('resubmitted',1)
            ->whereRelation('cardMethod','status',1)
            ->latest()->first();

        $data['approveCards'] = $cards->clone()->cards()->latest()->get();

        return view($this->theme . 'user.virtual_card.cardForm', $data, compact('orderLock'));
    }


    public function order()
    {
        $basicControl = basicControl();
        $checkOrder = VirtualCardOrder::where('user_id', auth()->id())->whereIn('status', [0, 3, 4])->latest()->exists();
        if ($checkOrder) {
            return back()->with('error', 'You are not eligible for request card');
        }
        if ($basicControl->v_card_multiple == 0) {
            $checkOrder = VirtualCardOrder::where('user_id', auth()->id())->where('status', 1)->latest()->exists();
            if ($checkOrder) {
                return back()->with('error', 'You are not eligible for multiple card');
            }
        }

        $data['virtualCardMethod'] = VirtualCardMethod::where('status', 1)->firstOrFail();
        return view($this->theme.'user.virtual_card.orderCard', $data);
    }

    public function orderSubmit(Request $request)
    {
        $virtualCardMethod = VirtualCardMethod::where('status', 1)->firstOrFail();
        if (!$this->checkUserBalance()) {
            return back()->withInput()->with('error', 'Please add fund to your default wallet');
        }

        $purifiedData = Purify::clean($request->all());
        $validationRules = ['currency' => 'required'];

        $validate = Validator::make($purifiedData, $validationRules);
        if ($validate->fails()) {
            return back()->withErrors($validate)->withInput();
        }
        $rulesSpecification = $this->buildValidationRules($virtualCardMethod);
        $this->validate($request, $rulesSpecification);

        $reqFieldSpecification = $this->processFormFields($request, $virtualCardMethod);

        $virtualCardOrder = new VirtualCardOrder();
        $virtualCardOrder->form_input = $reqFieldSpecification;
        $virtualCardOrder->virtual_card_method_id = $virtualCardMethod->id;
        $virtualCardOrder->user_id = auth()->id();
        $virtualCardOrder->currency = $purifiedData['currency'];
        $virtualCardOrder->status = 4;
        $virtualCardOrder->save();

        return redirect()->route('user.order.confirm', $virtualCardOrder->id)->with('success', 'Request initiated successfully');
    }

    public function confirmOrder(Request $request, $orderId)
    {
        $order = VirtualCardOrder::with('user', 'cardMethod')
            ->where('user_id', auth()->id())
            ->where('id', $orderId)
            ->where('status', 4)
            ->firstOrFail();

        if ($request->isMethod('get')) {
            return view($this->theme.'user.virtual_card.confirm', compact('orderId', 'order'));
        }

        $this->chargePay($order);
        $order->status = 0;
        $order->save();

        return redirect()->route('user.virtual.card')->with('success', 'Virtual card request sent successfully');
    }

    public function orderReSubmit(Request $request)
    {
        $virtualCardMethod = VirtualCardMethod::where('status', 1)->firstOrFail();
        $cardOrder = VirtualCardOrder::where('user_id', auth()->id())
            ->where('status',2)->where('resubmitted',1)->where('virtual_card_method_id',$virtualCardMethod)
            ->latest()->first();
        if(!$cardOrder){
            return back()->with('error', "This card method is inactive. The currently active method is $virtualCardMethod->name");
        }

        if ($request->isMethod('get')) {
            return view($this->theme.'user.virtual_card.reOrderCard', compact('virtualCardMethod', 'cardOrder'));
        }

        $purifiedData = Purify::clean($request->except('image', '_token', '_method'));
        $rules = ['currency' => 'required'];
        $message = ['currency.required' => 'Currency field is required'];

        $validate = Validator::make($purifiedData, $rules, $message);
        if ($validate->fails()) {
            return back()->withInput()->withErrors($validate);
        }

        if (!$this->checkUserBalance()) {
            return back()->withInput()->with('error', 'Please add fund to your default wallet');
        }

        $rulesSpecification = $this->buildValidationRules($virtualCardMethod);
        $this->validate($request, $rulesSpecification);

        $reqFieldSpecification = $this->processFormFields($request, $virtualCardMethod);

        $cardOrder->form_input = $reqFieldSpecification;
        $cardOrder->currency = $purifiedData['currency'];
        $cardOrder->status = 3;
        $cardOrder->save();

        $this->chargePay($cardOrder);

        return redirect()->route('user.virtual.card')->with('success', 'Re-Submitted Successfully');
    }

    public function cardBlock(Request $request, $id)
    {
        $purifiedData = Purify::clean($request->except('_token', '_method'));
        $rules = [
            'reason' => 'required',
        ];
        $message = [
            'reason.required' => 'Reason field is required',
        ];
        $validate = Validator::make($purifiedData, $rules, $message);
        if ($validate->fails()) {
            return back()->withInput()->withErrors($validate);
        }
        $card = VirtualCardOrder::findOrFail($id);
        if ($card->user_id != auth()->id()) {
            return back()->with('error', 'You have not permission');
        }
        $card->status = 5;
        $card->reason = $purifiedData['reason'];
        $card->save();
        return back()->with('success', 'Block Request Send');
    }

    public function cardTransaction($card_id = null)
    {
        if (!$card_id) {
            return back()->withErrors('Card Id not found');
        }

        if ($this->isStripeCard($card_id)) {
            Card::getTrx($card_id);
        }

        $cardTransactions = VirtualCardTransaction::where('user_id', auth()->id())
            ->where('card_id', $card_id)
            ->latest()
            ->paginate(20);

        $groupedTransactions = $cardTransactions->getCollection()->groupBy(function ($date) {
            return \Carbon\Carbon::parse($date->created_at)->format('Y-m-d');
        });
        $cardTransactions->setCollection($groupedTransactions->flatten(1));

        return view($this->theme . 'user.virtual_card.transaction', compact('cardTransactions', 'groupedTransactions', 'card_id'));
    }


    protected function isStripeCard($card_id): bool
    {
        return str_starts_with($card_id, 'ic_');
    }

}
