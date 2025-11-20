<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\UserWallet;
use App\Traits\ApiResponse;
use App\Traits\ManageWallet;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class WalletController extends Controller
{
    use ApiResponse, ManageWallet;

    public function __construct()
    {
        $this->middleware(['auth']);
        $this->middleware(function ($request, $next) {
            $this->user = auth()->user();
            return $next($request);
        });
        $this->theme = template();
    }


    public function walletList()
    {
        try {
            $data['wallets'] = UserWallet::with('currency')
                ->whereStatus(1)
                ->latest()->get();
            return response()->json($this->withSuccess($data));
        } catch (\Exception $e) {
            return response()->json($this->withError($e->getMessage()));
        }
    }

    public function walletTransaction(Request $request, $uuid)
    {
        try {
            $search = $request->all();
            $basic = basicControl();
            $userId = Auth::id();
            $wallet = UserWallet::where('uuid',$uuid)->first();
            if (!$wallet){
                return response()->json($this->withError('Invalid Wallet'));
            }
            $transactions = Transaction::where('user_id', $userId)
                ->where('wallet_id', $wallet->id)
                ->select('id', 'trx_id', 'amount', 'charge', 'currency', 'remarks','trx_type', 'created_at')
                ->when(isset($search['transaction']), fn($query) => $query->where('trx_id', 'LIKE', '%' . $search['transaction'] . '%')
                )
                ->when(isset($search['start_date'], $search['end_date']) && $search['start_date'] !== '' && $search['end_date'] !== '', fn($query) => $query->whereBetween('created_at', [
                    Carbon::parse($search['start_date']),
                    Carbon::parse($search['end_date'])->endOfDay()
                ])
                )
                ->latest()->paginate($basic->paginate);

            $formattedData = $transactions->map(function ($item) {
                return array_merge($item->toArray(), [
                    'amount' => currencyPositionCalc($item->amount,$item->curr),
                    'charge' => getAmount($item->charge, 2),
                    'trx_type' => $item->trx_type ,
                    'created_at' => dateTime($item->created_at),
                ]);
            });
            $data['transactions'] = $formattedData;

            return response()->json($this->withSuccess($data));
        } catch (\Exception $e) {
            return response()->json($this->withError($e->getMessage()));
        }
    }


    public function walletExchange(Request $request)
    {
        $validator = validator($request->all(), [
            'uuid' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json($this->withError(collect($validator->errors())->collapse()));
        }
        $uuid = $request->uuid;

        try {
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
                return response()->json($this->withError('Wallet not found or has been blocked.'));
            }
            $data['code'] = $data['wallet']->currency_code;

            return response()->json($this->withSuccess($data));
        } catch (\Exception $e) {
            return response()->json($this->withError($e->getMessage()));
        }
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
        $validator = validator($request->all(), [
            'senderWalletId' => 'required',
            'receiverWalletId' => 'required',
            'sendAmount' => 'required|numeric|min:0',
            'receiveAmount' => 'required|numeric|min:0',
        ]);
        if ($validator->fails()) {
            return response()->json($this->withError(collect($validator->errors())->collapse()));
        }
        try {
            DB::beginTransaction();

            $senderWallet = UserWallet::where('user_id',auth()->id())->findOrFail($request->senderWalletId);
            $receiverWallet = UserWallet::where('user_id',auth()->id())->findOrFail($request->receiverWalletId);

            if ($request->sendAmount > $senderWallet->balance) {
                return response()->json($this->withError('Insufficient Balance'));
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

            DB::commit();
            return response()->json($this->withSuccess('Currency exchanged successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json($this->withError($e->getMessage()));
        }
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $rules = [
            'currency_code' => [
                'required',
                'unique:user_wallets,currency_code,NULL,id,user_id,' . auth()->id(),
                'exists:country_currency,code',
            ],
        ];
        $messages = [
            'currency_code.unique' => 'The selected currency already exists on your wallets.',
            'currency_code.exists' => 'The selected currency is invalid or not supported.',
        ];
        $validator = validator($request->all(),$rules,$messages);
        if ($validator->fails()) {
            return response()->json($this->withError(collect($validator->errors())->collapse()));
        }
        try {
            UserWallet::create([
                'user_id' => $user->id,
                'currency_code' => $request->currency_code,
                'balance' => 0,
                'status' => 1,
                'default' => 0,
            ]);

            return response()->json($this->withSuccess('Wallet created successfully'));
        } catch (\Exception $e) {
            return response()->json($this->withError($e->getMessage()), 500);
        }
    }

    public function defaultWallet(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            $userId = auth()->id();
            $wallet = UserWallet::where('user_id', $userId)->find($id);
            if (!$wallet) {
                return response()->json($this->withError("Invalid Wallet"));
            }
            UserWallet::where('user_id', $userId)->update(['default' => 0]);
            $wallet->default = 1;
            $wallet->save();
            DB::commit();
            return response()->json($this->withSuccess('Wallet Default Successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json($this->withError($e->getMessage()));
        }
    }

}
