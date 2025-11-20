<?php

namespace App\Http\Controllers;

use App\Models\MoneyRequest;
use App\Models\Recipient;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserWallet;
use App\Traits\ApiResponse;
use App\Traits\ManageWallet;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class MoneyRequestController extends Controller
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

    public function showRequestMoneyForm($uuid)
    {
        $route = 'user.requestMoneyForm';

        try {
            $recipient = Recipient::where('uuid', $uuid)->firstOrFail();
            $rUser = User::find($recipient->r_user_id);

            if (!$rUser) {
                $errorMessage = 'Invalid Recipient User';
                return request()->routeIs($route)
                    ? back()->with('error', $errorMessage)
                    : response()->json($this->withError($errorMessage));
            }

            $wallets = UserWallet::with('currency')->where('user_id', $rUser->id)->get();
            $data = [
                'wallets' => $wallets,
                'recipient' => $rUser
            ];

            return request()->routeIs($route)
                ? view($this->theme . 'user.money_request.requestForm', $data)
                : response()->json($this->withSuccess($data));
        } catch (\Exception $e) {
            $errorMessage = "Something went wrong. {$e->getMessage()}";
            return request()->routeIs($route)
                ? abort(404)
                : response()->json($this->withError($errorMessage), 404);
        }
    }


    public function requestMoney(Request $request)
    {
        $route = 'user.requestMoney';
        try {
            $rules = [
                'wallet' => 'required|exists:user_wallets,uuid',
                'recipient_id' => 'required|exists:users,id',
                'amount' => 'required|numeric',
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                if (request()->routeIs($route)) {
                    return back()->withErrors($validator)->withInput();
                }
                return response()->json($this->withError(\collect($validator->errors())->collapse()));
            }
            $wallet = UserWallet::where('uuid', $request->wallet)->first();

            $data = [
                'requester_id' => auth()->id(),
                'recipient_id' => intval($request->recipient_id),
                'wallet_uuid' => $wallet->uuid,
                'amount' => $request->amount,
                'currency' => $wallet->currency_code,
                'trx_id' => strRandom(),
            ];
            MoneyRequest::create($data);

            if (request()->routeIs($route)) {
                return to_route('user.moneyRequestList')->with('success', 'Request money processed successfully');
            }
            return response()->json($this->withSuccess('Request money processed successfully'));
        } catch (\Exception $e) {
            $errorMessage = "Something went wrong. {$e->getMessage()}";
            return request()->routeIs($route)
                ? abort(404)
                : response()->json($this->withError($errorMessage), 404);
        }
    }


    public function moneyRequestList(Request $request)
    {
        $search = $request->all();
        $userId = Auth::id();
        $transactions = MoneyRequest::query()
            ->with('rcpUser:id,username,email,firstname,lastname,image,image_driver')
            ->with('reqUser:id,username,email,firstname,lastname,image,image_driver')
            ->where(function ($query) use ($userId) {
                $query->where('requester_id', $userId)
                    ->orWhere('recipient_id', $userId);
            })
            ->when(isset($search['search']), function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('trx_id', 'LIKE', '%' . $search['search'] . '%');
                });
            })
            ->when(isset($search['start_date'], $search['end_date']) && $search['start_date'] !== '' && $search['end_date'] !== '', function ($query) use ($search) {
                $query->whereBetween('created_at', [
                    Carbon::parse($search['start_date']),
                    Carbon::parse($search['end_date'])->endOfDay()
                ]);
            })
            ->latest()
            ->paginate(20);

        $groupedTransactions = $transactions->getCollection()->groupBy(function ($date) {
            return Carbon::parse($date->created_at)->format('Y-m-d');
        });
        $transactions->setCollection($groupedTransactions->flatten(1));

        if (request()->routeIs('user.moneyRequestList')) {
            return view($this->theme . 'user.money_request.list', compact('transactions', 'groupedTransactions'));
        } else {
            return response()->json($this->withSuccess($transactions));
        }
    }

    public function moneyRequestDetails($trx_id)
    {
        try {
            $userId = auth()->id();
            $trx = MoneyRequest::query()
                ->with('rcpUser:id,username,email,firstname,lastname,image,image_driver')
                ->with('reqUser:id,username,email,firstname,lastname,image,image_driver')
                ->where('trx_id', $trx_id)
                ->where(function ($query) use ($userId) {
                    $query->where('requester_id', $userId)
                        ->orWhere('recipient_id', $userId);
                })
                ->firstOrFail();
            $isRequester = $trx->requester_id == $userId;
            $data = [
                'trx' => $trx,
                'isRequester' => $isRequester,
            ];

            return request()->routeIs('user.moneyRequestDetails')
                ? view($this->theme . 'user.money_request.details', $data)
                : response()->json($this->withSuccess($data));
        } catch (\Exception $e) {
            return request()->routeIs('user.moneyRequestDetails')
                ? to_route('user.moneyRequestList')->with('error', 'Invalid transaction')
                : response()->json($this->withError('Invalid transaction'));
        }
    }

    public function moneyRequestAction(Request $request)
    {
        $userId = auth()->id();
        $route = 'user.moneyRequestAction';
        $messages = [
            'approve' => 'Money request approved successfully.',
            'reject' => 'Money request rejected successfully.',
            'error' => 'Something went wrong.',
        ];
        $rules = [
            'trx_id' => 'required|exists:money_requests,trx_id',
            'action' => 'required|in:approve,reject',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $response = $this->withError(collect($validator->errors())->collapse());
            return request()->routeIs($route)
                ? back()->withErrors($validator)->withInput()
                : response()->json($response);
        }

        DB::beginTransaction();
        try {
            $trx = MoneyRequest::with('rcpUser:id,username,email,firstname,lastname,image,image_driver')
                ->where('trx_id', $request->input('trx_id'))
                ->where(function ($query) use ($userId) {
                    $query->where('requester_id', $userId)
                        ->orWhere('recipient_id', $userId);
                })
                ->firstOrFail();

            if ($request->input('action') == 'approve') {
                $userWallet = UserWallet::where('uuid', $trx->wallet_uuid)->firstOrFail();

                $rcpWallet = UserWallet::where('user_id', $trx->requester_id)
                    ->where('currency_code', $trx->currency)
                    ->first();
                if (!$rcpWallet) {
                    $rcpWallet = UserWallet::where('user_id', $trx->requester_id)
                        ->where('default', 1)
                        ->firstOr(function () use ($trx, $messages, &$response) {
                            return UserWallet::where('user_id', $trx->requester_id)
                                ->firstOr(function () use ($messages, &$response) {
                                    $response = $this->withError('Invalid recipient wallet');
                                    return null;
                                });
                        });
                }

                $rcpCurrRate = $this->getCurrencyRate($rcpWallet->currency_code, $userWallet->currency_code);
                $rcpAmount = $trx->amount * $rcpCurrRate;

                if ($userWallet->balance < $trx->amount) {
                    $balanceError = "Insufficient Balance. Please add money to your {$userWallet->currency_code} wallet";

                    $response = $this->withError($balanceError);
                    session()->put('balance_error',$response['message']);
                    return request()->routeIs($route)
                        ? back()->with('error', $balanceError)
                        : response()->json($response);
                }

                $userWallet->decrement('balance', $trx->amount);
                $remarks = "Money sent to {$trx->reqUser->fullname()}";
                $userTransaction = $this->createTransaction($userWallet, $trx->amount, '-', $remarks);
                $userWallet->transactional()->save($userTransaction);


                $rcpWallet->increment('balance', $rcpAmount);
                $remarks = "Money received from {$trx->rcpUser->fullname()}";
                $rcpTransaction = $this->createTransaction($rcpWallet, $rcpAmount, '+', $remarks);
                $rcpWallet->transactional()->save($rcpTransaction);

                $trx->update(['status' => 1]);
                $response = $this->withSuccess($messages['approve']);
                DB::commit();
            } else {
                $trx->update(['status' => 2]);
                $response = $this->withSuccess($messages['reject']);
                DB::commit();
            }

            return request()->routeIs($route)
                ? back()->with('success', $response['message'])
                : response()->json($response);
        } catch (\Exception $e) {
            DB::rollBack();
            $response = $this->withError($messages['error'] . ' ' . $e->getMessage());
            return request()->routeIs($route)
                ? back()->with('error', $messages['error'] . ' ' . $e->getMessage())
                : response()->json($response);
        }
    }

    private function createTransaction($wallet,$amount,$type,$remarks): Transaction
    {
        $currency = $wallet->currency_code;
        $rate = $this->getCurrencyRate(basicControl()->base_currency,$currency);
        $baseAmount = $amount*$rate;

        $transaction = new Transaction();
        $transaction->user_id = $wallet->user_id;
        $transaction->wallet_id = $wallet->id ?? null;
        $transaction->amount = $amount;
        $transaction->base_amount = $baseAmount;
        $transaction->currency = $currency;
        $transaction->charge = 0;
        $transaction->balance = getAmount($wallet->balance ?? $transaction->balance ?? 0);
        $transaction->trx_type = $type;
        $transaction->trx_id = strRandom();
        $transaction->remarks = $remarks;
        return $transaction;
    }


}
