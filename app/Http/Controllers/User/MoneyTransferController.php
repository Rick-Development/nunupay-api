<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\CountryCurrency;
use App\Models\Gateway;
use App\Models\Kyc;
use App\Models\MoneyTransfer;
use App\Models\Recipient;
use App\Models\User;
use App\Models\UserWallet;
use App\Traits\Notify;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MoneyTransferController extends Controller
{
    use Notify;

    public function __construct()
    {
        $this->theme = template();
    }

    public function clearSession()
    {
        session()->forget('uuid');
        return response()->json(['message' => 'Session cleared successfully']);
    }

    public function transferList(Request $request)
    {
        try {
            $user = auth()->user();
            $search = $request->all();
            $transfersQuery = MoneyTransfer::where('sender_id', $user->id)
                ->when(isset($search['name']), fn($query) => $query->whereHas('recipient', fn($query) => $query->where('name', 'LIKE', "%{$search['name']}%")
                    ->orWhere('email', 'LIKE', "%{$search['name']}%")
                )
                )
                ->when(isset($search['status']), fn($query) => $query->where('status', '=', $search['status']));
            if (isset($search['start_date'], $search['end_date']) && $search['start_date'] !== '' && $search['end_date'] !== '') {
                $transfersQuery->whereBetween('created_at', [
                    Carbon::parse($search['start_date']),
                    Carbon::parse($search['end_date'])->endOfDay()
                ]);
            }

            $transfers = $transfersQuery->with(['recipient:id,name,email'])
                ->latest()
                ->paginate(basicControl()->paginate);

            $groupedTransfers = $transfers->getCollection()->groupBy(function ($transfer) {
                return Carbon::parse($transfer->created_at)->format('Y-m-d');
            });
            $transfers->setCollection($groupedTransfers->flatten(1));

            return view($this->theme . 'user.transfer.list', compact('transfers', 'groupedTransfers'));
        } catch (\Exception $e) {
            throw new \Exception('Error in transferList: ' . $e->getMessage());
        }
    }

    public function transferDetails($uuid)
    {
        $userId = auth()->user()->id;
        $transferDetails = MoneyTransfer::with(['senderCurrency', 'receiverCurrency'])->where('sender_id', $userId)->where('uuid', $uuid)->firstOrFail();
        return view($this->theme . 'user.transfer.details', compact('transferDetails'));
    }

    public function transferAmount(Request $request)
    {
        if ($request->wallet) {
            $wallet = UserWallet::where('user_id', auth()->id())
                ->whereStatus(1)
                ->where('uuid', $request->wallet)
                ->first();

            if (!$wallet) {
                return to_route('user.dashboard')->with('error', 'Something went wrong');
            }
            session()->put('trx_wallet_id', $wallet->id);
        } else {
            session()->forget('trx_wallet_id');
        }

        if ($request->recipient) {
            $recipient = Recipient::where('uuid', $request->recipient)->first();
            if ($recipient) {
                session()->put('recipient_uuid', $recipient->uuid);
            } else {
                session()->forget('recipient_uuid');
            }
        } else {
            session()->forget('recipient_uuid');
        }

        session()->forget('uuid');
        $data['country'] = Country::where('status', 1)->get();
        return view($this->theme . 'user.transfer.amount', $data);
    }

    /*axios request*/
    public function currencyList()
    {
        $currencies = CountryCurrency::with('country:id,name,image,image_driver,send_to,receive_from')
            ->whereHas('country', function ($query) {
                $query->where('status', 1);
            })
            ->latest()->get()
            ->map(function ($item) {
                $item->country_name = $item->country->name;
                $item->image = $item->country?->getImage();
                $item->send_to = $item->country->send_to;
                $item->receive_from = $item->country->receive_from;
                return $item;
            });
        $senderCurrencies = $currencies->where('send_to', 1)->values()->all();
        $receiverCurrencies = $currencies->where('receive_from', 1)->values()->all();

        return response()->json([
            'senderCurrencies' => $senderCurrencies,
            'receiverCurrencies' => $receiverCurrencies,
        ]);
    }

    public function transferRecipient(Request $request, $country = null)
    {
        $rcp_uuid = session()->get('recipient_uuid');
        if ($rcp_uuid){
            return to_route('user.transferReview',$rcp_uuid);
        }

        $userId = auth()->id();
        $search = $request->input('search');
        $data['countryName'] = $country;

        if ($search && str_starts_with($search, '@')) {
            $searchUsername = ltrim($search, '@');
            $user = User::where('username', $searchUsername)->first();
            $data['users'] = $user ? collect([$user]) : collect();
        } else {
            $query = Recipient::where('user_id', $userId)
                ->where(function ($query) use($country) {
                    $query->whereNotNull('r_user_id')
                        ->orWhereHas('currency.country', function ($query) use ($country) {
                            $query->where('name', $country);
                        });
                });

            if ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'LIKE', "%$search%")
                        ->orWhere('email', 'LIKE', "%$search%")
                        ->orWhereHas('bank', function ($subquery) use ($search) {
                            $subquery->where('name', 'LIKE', "%$search%");
                        })
                        ->orWhereHas('bank.service', function ($subquery) use ($search) {
                            $subquery->where('name', 'LIKE', "%$search%");
                        });
                });
            }
            $data['recipients'] = $query->paginate(basicControl()->paginate);
        }
        return view($this->theme . 'user.transfer.recipients', $data);
    }


    public function verify()
    {
        $data['kyc'] = Kyc::orderBy('id', 'asc')->where('status', 1)->get();
        return view(template() . 'user.transfer.verification', $data);
    }

    public function transferReview($uuid)
    {
        $userId = auth()->id();
        $recipient = Recipient::where('user_id', $userId)->where('uuid', $uuid)->firstOr(function () {
            throw new \Exception('An Error Occurred with Recipient');
        });

        return view($this->theme . 'user.transfer.review', compact('recipient'));
    }


    /*axios request*/
    public function paymentStore(Request $request)
    {
        $sessionWallet = session()->get('trx_wallet_id');
        if ($sessionWallet){
            $wallet = UserWallet::find($sessionWallet);
            if (!$wallet || $wallet->user_id != auth()->id() || $wallet->currency_code != $request->sender_currency){
                return response()->json(['error' => 'Something went wrong, please try again'], 422);
            }
        }

        $rcpUserId = $request->r_user_id;
        $recipient = Recipient::find($request->recipient_id);
        if (!$recipient) {
            return response()->json(['error' => 'Recipient not found'], 422);
        }
        $rules = [
            'recipient_id' => 'required|exists:recipients,id',
            'send_currency_id' => 'required|exists:country_currency,id',
            'receive_currency_id' => 'required|exists:country_currency,id',
            'send_amount' => 'required|numeric',
            'fees' => 'required|numeric',
            'payable_amount' => 'required|numeric',
            'sender_currency' => 'required',
            'receiver_currency' => 'required',
            'rate' => 'required|numeric',
            'recipient_get_amount' => 'required|numeric',
        ];
        if ($rcpUserId != null) {
            $rules['service_id'] = 'nullable';
            $rules['r_user_id'] = 'required:exists:users,id';
        } else {
            $rules['service_id'] = 'required|exists:country_services,id';
        }

        $data = $request->validate($rules);

        if ($rcpUserId == null && $recipient->currency_id != $request->receive_currency_id) {
            return response()->json(['error' => 'Recipient currency and receiver currency do not match, please try again'], 422);
        }

        $data['wallet_id'] = $sessionWallet ?? null;
        $uuid = session()->get('uuid');

        if ($uuid) {
            $moneyTransfer = MoneyTransfer::where('uuid', $uuid)->firstOrFail();
            $moneyTransfer->update($data);
        } else {
            $moneyTransfer = MoneyTransfer::create($data);
            session()->put('uuid', $moneyTransfer->uuid);
        }

        $route = route('user.transferPay', $moneyTransfer->uuid);
        return response()->json(['message' => 'Data stored successfully', 'paymentUrl' => $route]);
    }

    public function transferPay($uuid)
    {
        $transfer = MoneyTransfer::where('uuid', $uuid)->firstOrFail();
        if ($transfer->recipient->deleted_at != null) {
            return back()->with('error', 'Invalid Transaction. You Removed This Recipient');
        }
        if ($transfer->resubmitted == 0 || in_array($transfer->status, [1, 2])) {
            return to_route('user.transferDetails', $uuid)->with('error', 'You are not eligible for this transfer');
        }

        $rate = $transfer->receiverCurrency->rate / $transfer->senderCurrency->rate;
        $receiver_amount = $transfer->payable_amount * $rate;
        $transfer->rate = $rate;
        $transfer->recipient_get_amount = $receiver_amount;
        $transfer->save();

        try {
            $reviewStored = MoneyTransfer::with('recipient.bank:id,service_id')->where('uuid', $uuid)->firstOrFail();

            $data = [
                'payDetails' => $reviewStored,
                'receiver_amount' => $transfer->recipient_get_amount,
                'recipient' => $reviewStored->recipient,
                'recipientBank' => $reviewStored->recipient->bank,
                'gateways' => Gateway::where('status', 1)->orderBy('sort_by', 'ASC')->get(),
                'wallets' => UserWallet::where('status', 1)->where('user_id', auth()->id())->get()
            ];

            if ($transfer->wallet_id !== null) {
                return view($this->theme . 'user.transfer.otp', $data);
            } else {
                return view($this->theme . 'user.transfer.pay', $data);
            }

        } catch (\Exception $e) {
            throw new \Exception('An error occurred while loading transfer details. ' . $e->getMessage() . ' Please try again.');
        }
    }

    public function destroy($uuid)
    {
        $transferData = MoneyTransfer::where('uuid', $uuid)->firstOrFail();
        $transferData->delete();

        return to_route('user.dashboard')->with('success', 'Your Transfer has canceled');
    }

    /*axios request from transfer pay*/
    public function currencyRate(Request $request)
    {
        $selectedCurrency = $request->query('selectedCurrency');
        $senderCurrency = $request->query('senderCurrency');
        $selectedCurrencyInfo = CountryCurrency::where('code', $selectedCurrency)->first();
        $senderCurrencyInfo = CountryCurrency::where('code', $senderCurrency)->first();
        $selectedCurrencyRate = $selectedCurrencyInfo ? $selectedCurrencyInfo->rate : null;
        $senderCurrencyRate = $senderCurrencyInfo ? $senderCurrencyInfo->rate : null;
        $rate = $selectedCurrencyRate / $senderCurrencyRate;
        $response = [
            'selectedCurrencyRate' => $selectedCurrencyRate,
            'senderCurrencyRate' => $senderCurrencyRate,
            'rate' => $rate,
        ];
        return response()->json($response);
    }

    public function getWalletBalance(Request $request)
    {
        $walletId = $request->input('walletId');
        $wallet = UserWallet::where('id', $walletId)->where('user_id', auth()->id())->first();
        $walletBalance = $wallet ? $wallet->balance : null;

        return response()->json(['walletBalance' => $walletBalance]);
    }


}
