<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Recipient;
use App\Models\Transaction;
use App\Models\UserWallet;
use App\Traits\ManageWallet;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WalletController extends Controller
{
    use ManageWallet;

    public function __construct()
    {
        $this->middleware(['auth']);
        $this->middleware(function ($request, $next) {
            $this->user = auth()->user();
            return $next($request);
        });
        $this->theme = template();
    }

    public function walletDetails(Request $request, $uuid)
    {
        $search = $request->all();
        $basic = basicControl();
        $userId = Auth::id();
        $wallet = UserWallet::where('uuid', $uuid)->where('user_id',$userId)->firstOrFail();

        $transactions = Transaction::query()
            ->where('user_id', $userId)
            ->where('wallet_id', $wallet->id)
            ->when(isset($search['transaction']), fn ($query) =>
                $query->where('trx_id', 'LIKE', '%' . $search['transaction'] . '%')
            )
            ->when(isset($search['start_date'], $search['end_date']) && $search['start_date'] !== '' && $search['end_date'] !== '', fn ($query) =>
                $query->whereBetween('created_at', [
                    Carbon::parse($search['start_date']),
                    Carbon::parse($search['end_date'])->endOfDay()
                ])
            )
            ->latest()
            ->paginate($basic->paginate);

        $groupedTransactions = $transactions->getCollection()->groupBy(function($date) {
            return Carbon::parse($date->created_at)->format('Y-m-d');
        });

        $transactions->setCollection($groupedTransactions->flatten(1));

        return view($this->theme . 'user.wallets.details', compact('transactions', 'groupedTransactions', 'wallet'));
    }


    public function walletExchange($uuid)
    {
        $user_id = auth()->id();
        $data['wallets'] = UserWallet::where('user_id', $user_id)
            ->where('uuid', '!=', $uuid)
            ->whereStatus(1)
            ->get();
        $data['wallet'] = UserWallet::where('user_id', $user_id)
            ->where('uuid', $uuid)
            ->whereStatus(1)
            ->first();
        if (!$data['wallet']) {
            return back()->with('error', 'Wallet not found or has been blocked');
        }
        $data['code'] = $data['wallet']->currency_code;
        return view(template().'user.wallets.exchange', $data);
    }


    private function createTransaction($wallet,$amount,$type,$remarks)
    {
        $currency = $wallet->currency_code;
        $rate = $this->getCurrencyRate(basicControl()->base_currency,$currency);
        $baseAmount = $amount*$rate;

        $transaction = new Transaction();
        $transaction->user_id = $this->user->id;
        $transaction->wallet_id = $wallet->id ?? null;
        $transaction->amount = $amount;
        $transaction->base_amount = $baseAmount;
        $transaction->currency = $wallet->currency_code;
        $transaction->charge = 0;
        $transaction->balance = getAmount($wallet->balance ?? $transaction->balance ?? 0);
        $transaction->trx_type = $type;
        $transaction->trx_id = strRandom();
        $transaction->remarks = $remarks;
        return $transaction;
    }

    public function moneyExchange(Request $request)
    {
        $request->validate([
            'senderWalletId' => 'required',
            'receiverWalletId' => 'required',
            'sendAmount' => 'required|numeric|min:0',
            'receiveAmount' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $senderWallet = UserWallet::where('user_id',auth()->id())->find($request->senderWalletId);
            $receiverWallet = UserWallet::where('user_id',auth()->id())->find($request->receiverWalletId);

            if (!$senderWallet || !$receiverWallet){
                return response()->json([
                    'status' => 'error',
                    'msg' => 'Invalid wallet'
                ]);
            }
            if ($request->sendAmount > $senderWallet->balance) {
                return response()->json([
                    'status' => 'error',
                    'msg' => 'Insufficient Balance'
                ]);
            }
            $senderWallet->balance -= $request->sendAmount;
            $senderWallet->save();
            $type = "-";
            $remarks = "Converted Money to $receiverWallet->currency_code wallet";

            $senderTransaction = $this->createTransaction($senderWallet,$request->sendAmount,$type,$remarks);
            $senderWallet->transactional()->save($senderTransaction);

            $receiverWallet->balance += $request->receiveAmount;
            $receiverWallet->save();
            $type = "+";
            $remarks = "Converted Money From $senderWallet->currency_code wallet";

            $receiverTransaction = $this->createTransaction($receiverWallet,$request->receiveAmount,$type,$remarks);
            $receiverWallet->transactional()->save($receiverTransaction);

            $msg = "Money Converted successfully. From $senderWallet->currency_code to $receiverWallet->currency_code wallet";
            session()->put('success-message',$msg);
            DB::commit();
            return response()->json([
                'status' => 'success',
                'msg' => $msg,
                'url' => route('transfer-success')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'msg' => 'An error occurred while processing the transaction.',
            ]);
        }
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $request->validate([
            'currency_code' => [
                'required',
                'unique:user_wallets,currency_code,NULL,id,user_id,' . auth()->id(),
                'exists:country_currency,code',
            ],
        ], [
            'currency_code.required' => 'The currency code field is required.',
            'currency_code.unique' => 'The selected currency already exists on your wallets.',
            'currency_code.exists' => 'The selected currency code does not exist.',
        ]);

        UserWallet::create([
            'user_id' => $user->id,
            'currency_code' => $request->currency_code,
            'balance' => 0,
            'status' => 1,
            'default' => 0,
        ]);
        return to_route('user.dashboard')->with('success','Wallet created successfully');
    }

    public function defaultWallet(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            $userId = auth()->id();

            $wallet = UserWallet::where('user_id', $userId)->whereStatus(1)->find($id);
            if (!$wallet) {
                return back()->with('error', 'Invalid Wallet');
            }
            UserWallet::where('user_id', $userId)->update(['default' => 0]);
            $wallet->default = 1;
            $wallet->save();

            DB::commit();
            return back()->with('success', 'Wallet Default Successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Something Went Wrong');
        }
    }

    public function walletList()
    {
        $currencies = UserWallet::with('currency')
            ->whereStatus(1)
            ->latest()->get();
        return response()->json([
            'wallets' => $currencies,
        ]);
    }

}
