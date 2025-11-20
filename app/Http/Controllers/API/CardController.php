<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\VirtualCardMethod;
use App\Models\VirtualCardOrder;
use App\Models\VirtualCardTransaction;
use App\Services\VirtualCard\stripe\Card;
use App\Traits\ApiResponse;
use App\Traits\ManageWallet;
use App\Traits\Notify;
use App\Traits\Upload;
use App\Traits\VirtualCardTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Stevebauman\Purify\Facades\Purify;

class CardController extends Controller
{
    use ApiResponse, Notify, Upload, ManageWallet, VirtualCardTrait;

    public function index()
    {
        try {
            $basicControl = basicControl();
            $orderLock = 'false';

            $data['Cards'] = VirtualCardOrder::cards()->where('user_id', auth()->id())
                ->select('id', 'currency', 'fund_amount', 'card_number')
                ->latest()->get()->makeHidden('is_active');

            $checkOrder = VirtualCardOrder::where('user_id', auth()->id())->whereIn('status', [0, 3, 4])->latest()->exists();
            if ($checkOrder) {
                $orderLock = 'true';
            }
            if ($basicControl->v_card_multiple == 0) {
                $checkOrder = VirtualCardOrder::where('user_id', auth()->id())->where('status', 1)->latest()->exists();
                if ($checkOrder) {
                    $orderLock = 'true';
                }
            }
            $data['cardOrder'] = VirtualCardOrder::where('user_id', auth()->id())->where('status', '!=', 1)->latest()->first();
            $data['approveCards'] = VirtualCardOrder::cards()->where('user_id', auth()->id())->latest()->get();
            $data['orderLock'] = $orderLock;
            $data['cardCharge'] = currencyPosition($basicControl->v_card_charge);

            return response()->json($this->withSuccess($data));
        } catch (\Exception $e) {
            return response()->json($this->withError($e->getMessage()));
        }
    }

    public function order()
    {
        try {
            $basicControl = basicControl();
            $checkOrder = VirtualCardOrder::where('user_id', auth()->id())->whereIn('status', [0, 3, 4])->latest()->exists();
            if ($checkOrder) {
                return response()->json($this->withError('You are not eligible for request card.'));
            }
            if ($basicControl->v_card_multiple == 0) {
                $checkOrder = VirtualCardOrder::where('user_id', auth()->id())->where('status', 1)->latest()->exists();
                if ($checkOrder) {
                    return response()->json($this->withError('You are not eligible for multiple card.'));
                }
            }
            $data['virtualCardMethod'] = VirtualCardMethod::where('status', 1)->firstOrFail();
            return response()->json($this->withSuccess($data));
        } catch (\Exception $e) {
            return response()->json($this->withError($e->getMessage()));
        }
    }

    public function orderSubmit(Request $request)
    {
        return $this->processOrder($request, 'submit');
    }

    public function orderReSubmit(Request $request)
    {
        return $this->processOrder($request, 'resubmit');
    }

    private function processOrder(Request $request, $type)
    {
        try {
            $virtualCardMethod = VirtualCardMethod::where('status', 1)->first();
            if (!$virtualCardMethod) {
                return response()->json($this->withError('Card method not found'));
            }
            if (!$this->checkUserBalance()) {
                return response()->json($this->withError('Please add funds to your default wallet'));
            }

            $purifiedData = Purify::clean($request->all());

            $rulesSpecification = $this->buildValidationRules($virtualCardMethod);
            $validationRules = array_merge([
                'currency' => 'required',
            ], $rulesSpecification);

            $validate = Validator::make($purifiedData, $validationRules);
            if ($validate->fails()) {
                return response()->json($this->withError(collect($validate->errors())->collapse()));
            }
            $reqFieldSpecification = $this->processFormFields($request, $virtualCardMethod);

            if ($type == 'submit') {
                $virtualCardOrder = new VirtualCardOrder();
                $virtualCardOrder->status = 4;
            } else {
                $virtualCardOrder = VirtualCardOrder::where('user_id', auth()->id())
                    ->latest()
                    ->first();
                $virtualCardOrder->status = 3;
            }

            $virtualCardOrder->form_input = $reqFieldSpecification;
            $virtualCardOrder->virtual_card_method_id = $virtualCardMethod->id;
            $virtualCardOrder->user_id = auth()->id();
            $virtualCardOrder->currency = $purifiedData['currency'];
            $virtualCardOrder->save();

            if ($type == 'resubmit') {
                $this->chargePay($virtualCardOrder);
                return response()->json($this->withSuccess('Re Submitted Successfully.'));
            }

            return response()->json($this->withSuccess('Request initiated successfully'));
        } catch (\Exception $e) {
            return response()->json($this->withError($e->getMessage()));
        }
    }


    public function confirmOrder(Request $request, $orderId)
    {
        try {
            $order = VirtualCardOrder::with('user', 'cardMethod')
                ->where('user_id', auth()->id())
                ->where('id', $orderId)->first();
            if (!$order || $order->status != 4) {
                return response()->json($this->withError('Invalid Order or Request already send.'));
            }

            $this->chargePay($order);
            $order->status = 0;
            $order->save();
            return response()->json($this->withSuccess('Virtual card request sent successfully'));

        } catch (\Exception $e) {
            return response()->json($this->withError($e->getMessage()));
        }
    }

    public function cardBlock(Request $request, $id)
    {
        try {
            $purifiedData = Purify::clean($request->except('_token', '_method'));
            $rules = [
                'reason' => 'required',
            ];
            $message = [
                'reason.required' => 'Reason field is required',
            ];

            $validate = Validator::make($purifiedData, $rules, $message);

            if ($validate->fails()) {
                return response()->json($this->withError(collect($validate->errors())->collapse()));
            }
            $card = VirtualCardOrder::findOrFail($id);
            if ($card->user_id != auth()->id()) {
                return response()->json($this->withError('You do not have permission to access this.'));
            }
            $card->status = 5;
            $card->reason = $purifiedData['reason'];
            $card->save();
            return response()->json($this->withSuccess('Block Request Send'));
        } catch (\Exception $e) {
            return response()->json($this->withError($e->getMessage()));
        }
    }

    public function cardTransaction($card_id = null)
    {
        try {
            if (!$card_id) {
                return response()->json($this->withError('Card Id not found'));
            }
            $data['card_id'] = $card_id;

            if ($this->isStripeCard($card_id)) {
                Card::getTrx($card_id);
            }

            $trx = VirtualCardTransaction::with(['cardOrder.cardMethod:id,name'])->where('user_id', auth()->id())
                ->where('card_id', $card_id);

            $trxs = $trx->latest()->paginate(20);

            $data['transactions'] = $trxs->map(fn($item) => [

                'provider_id' => $item->cardOrder?->cardMethod?->id,
                'provider_name' => $item->cardOrder?->cardMethod?->name,
                'amount' => $item->amount,
                'currency' => $item->currency,
                'status' => $item->cardOrder?->status,
                'trx_id' => $item->trx_id,
            ]);

            return response()->json($this->withSuccess($data));
        } catch (\Exception $e) {
            return response()->json($this->withError($e->getMessage()));
        }
    }

    protected function isStripeCard($card_id): bool
    {
        return str_starts_with($card_id, 'ic_');
    }
}
