<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\CountryCurrency;
use App\Models\Deposit;
use App\Models\MoneyTransfer;
use App\Models\Recipient;
use App\Models\User;
use App\Traits\ApiResponse;
use App\Traits\ManageWallet;
use App\Traits\PaymentValidationCheck;
use Carbon\Carbon;
use Facades\App\Services\BasicService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class MoneyTransferController extends Controller
{
    use ApiResponse, PaymentValidationCheck, ManageWallet;

    public function transferList(Request $request)
    {
        try {
            $user = auth()->user();
            $search = $request->all();
            $transfersQuery = MoneyTransfer::where('sender_id', $user->id)
                ->select('id', 'uuid', 'recipient_id', 'send_amount', 'recipient_get_amount', 'sender_currency', 'receiver_currency', 'status', 'created_at','trx_id')
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
            $transfersQuery->with(['recipient:id,name,email,deleted_at']);
            $transfer = $transfersQuery->latest()->paginate(basicControl()->paginate);

            $statusLabels = [
                0 => 'Initiate',
                1 => 'Completed',
                2 => 'Under Review',
                3 => 'Rejected',
            ];
            $formattedData = $transfer->map(function ($item) use ($statusLabels) {
                return array_merge($item->toArray(), [
                    'status' => $statusLabels[$item->status],
                    'created_at' => dateTime($item->created_at),
                    'send_amount' => getAmount($item->send_amount, 2),
                    'recipient_get_amount' => getAmount($item->recipient_get_amount, 2),
                    'recipient_status' => (!$item->recipient?->deleted_at) ? true : false,
                ]);
            });
            $data['transfers'] = $formattedData;
            return response()->json($this->withSuccess($data));
        } catch (\Exception $e) {
            return response()->json($this->withError($e->getMessage()));
        }
    }

    public function transferDetails($uuid)
    {
        try {
            $userId = auth()->id();
            $item = MoneyTransfer::with([
                'senderCurrency:id,name,code,rate,symbol,symbol_native,precision',
                'receiverCurrency:id,name,code,rate,symbol,symbol_native,precision',
                'recipient'
            ])
                ->where('sender_id', $userId)->where('uuid', $uuid)->firstOrFail();
            $statusLabels = [
                0 => 'Initiate',
                1 => 'Completed',
                2 => 'Under Review',
                3 => 'Rejected',
            ];
            $formattedData = [
                'id' => $item->id,
                'uuid' => $item->uuid,
                'send_amount' => getAmount($item->send_amount, 2),
                'transfer_fee' => getAmount($item->fees, 2),
                'send_total' => getAmount($item->payable_amount, 2),
                'exchange_rate' => getAmount($item->rate, 2),
                'receiver_amount' => getAmount($item->recipient_get_amount),
                'trx_id' => $item->trx_id,
                'sender_currency' => $item->senderCurrency,
                'receiver_currency' => $item->receiverCurrency,
                'recipient_details' => [
                    'name' => optional($item->recipient)->name,
                    'email' => optional($item->recipient)->email,
                    'send_to' => $item->recipient?->bank?->name,
                    'service' => $item->recipient?->service?->name,
                    'bank_info' => optional($item->recipient)->bank_info,
                ],
                'Reject Reason' => $item->reason ?? null,
                'resubmitted' => ($item->resubmitted == 0) ? false : true,
                'status' => $statusLabels[$item->status] ?? 'Unknown',
            ];
            $data['transferDetails'] = $formattedData;

            return response()->json($this->withSuccess($data));
        } catch (\Exception $e) {
            return response()->json($this->withError($e->getMessage()));
        }
    }

    public function transferAmount()
    {
        try {
            $basic = basicControl();
            $limitations = [
                'minimum_amount' => getAmount($basic->min_amount, 2),
                'maximum_amount' => getAmount($basic->max_amount, 2),
                'minimum_transfer_fee' => getAmount($basic->min_transfer_fee, 2),
                'maximum_transfer_fee' => getAmount($basic->max_transfer_fee, 2),
                'currency' => 'USD',
            ];
            $currencies = CountryCurrency::with('country:id,name,image,image_driver,send_to,receive_from')
                ->whereHas('country', fn($query) => $query->where('status', 1))
                ->latest()->get()
                ->map(function ($item) {
                    $country = $item->country;
                    return [
                        'id' => $item->id,
                        'currency_code' => $item->code,
                        'currency_name' => $item->name,
                        'country_name' => $item->country->name,
                        'country_image' => $country->getImage(),
                        'rate' => $item->country->currency->rate,
                        'send_to' => $country->send_to,
                        'receive_from' => $country->receive_from,
                    ];
                });

            $senderCurrencies = $currencies->where('send_to', 1)->values()->all();
            $receiverCurrencies = $currencies->where('receive_from', 1)->values()->all();

            $data = compact('limitations', 'senderCurrencies', 'receiverCurrencies');
            return response()->json($this->withSuccess($data));
        } catch (\Exception $e) {
            return response()->json($this->withError($e->getMessage()));
        }
    }

    public function transferRecipient(Request $request, $country = null)
    {
        try {
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
                    })
                    ->when($search, function ($query, $search) {
                        $query->where('name', 'LIKE', "%$search%")
                            ->orWhere('email', 'LIKE', "%$search%")
                            ->orWhereHas('currency.country', function ($sq) use ($search) {
                                $sq->where('name', 'LIKE', "%$search%")
                                    ->orWhere('code', 'LIKE', "%$search%");
                            })
                            ->orWhereHas('service', function ($sq) use ($search) {
                                $sq->where('name', 'LIKE', "%$search%");
                            });
                    });
                $recipients = $query->latest()->paginate(basicControl()->paginate);
                $formattedData = $recipients->map(fn($item) => [
                    'id' => $item->id,
                    'user_id' => $item->user_id,
                    'uuid' => $item->uuid,
                    'type' => ($item->type == 0) ? 'my-self' : 'others',
                    'name' => $item->name,
                    'email' => $item->email,
                    'currency_code' => optional($item->currency)->code,
                    'currency_name' => optional($item->currency)->name,
                    'service_name' => optional($item->service)->name,
                    'country_image' => $item->currency?->country?->getImage(),
                    'r_user_id' => $item->r_user_id,
                    'r_user_image' => $item->recipientUser?->getImage(),
                    'favicon' => getFile(basicControl()->favicon_driver, basicControl()->favicon),
                ]);
                $data['recipients'] = $formattedData;
            }
            return response()->json($this->withSuccess($data));
        } catch (\Exception $e) {
            return response()->json($this->withError($e->getMessage()));
        }
    }

    public function transferReview($uuid)
    {
        try {
            $userId = auth()->id();
            $item = Recipient::where('user_id', $userId)->where('uuid', $uuid)->firstOr(function () {
                return response()->json($this->withError('An Error Occurred with Recipient'));
            });
            $formattedData = [
                'id' => $item->id,
                'calculation' => 'Calculation is stored in localstorage till this page',
                'Transfer details' => [
                    'Send amount',
                    'Transfer fee',
                    'Send Total',
                    'Recipient get amount',
                ],
                'Recipients details' => [
                    'name' => $item->name,
                    'email' => $item->email,
                    'bank_name' => optional($item->bank)->name,
                    'service_name' => optional($item->service)->name,
                    'service_id' => optional($item->service)->id,

                ],
            ];
            $data['transferReview'] = $formattedData;

            return response()->json($this->withSuccess($data));
        } catch (\Exception $e) {
            return response()->json($this->withError($e->getMessage()));
        }
    }

    public function paymentStore(Request $request)
    {
        try {
            $rcpUserId = $request->r_user_id;
            $recipient = Recipient::find($request->recipient_id);
            if (!$recipient) {
                return response()->json($this->withError('Recipient not found'));
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
            $data = Validator::make($request->all(), $rules);
            if ($data->fails()) {
                return response()->json($this->withError(collect($data->errors())->collapse()));
            }

            if ($rcpUserId == null && $recipient->currency_id != $request->receive_currency_id) {
                return response()->json($this->withError('Recipient currency and receiver currency do not match, please try again'));
            }
            $validatedData = $data->validated();

            $moneyTransfer = MoneyTransfer::create($validatedData);

            return response()->json($this->withSuccess($moneyTransfer->trx_id));
        } catch (\Exception $e) {
            return response()->json($this->withError($e->getMessage()));
        }
    }

    public function transferPayment(Request $request)
    {
        $rules = [
            'trx_id' => 'required',
            'gateway_id' => 'required',
            'currency' => 'required',
        ];
        $rules['wallet_id'] = 'required_if:gateway_id,0';
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json($this->withError(collect($validator->errors())->collapse()));
        }
        $transfer = MoneyTransfer::where('trx_id', $request->trx_id)->first();
        if (!$transfer) {
            return response()->json($this->withError('Records not found'));
        }

        $gateway = $request->gateway_id;
        $gatewayCurrency = $request->currency;
        $amount = $transfer->send_amount;

        $rate = $this->getCurrencyRate($gatewayCurrency,$transfer->sender_currency);
        $gatewayCurrencyAmount = $amount * $rate;

        if ($gateway == 0) {
            $walletId = $request->wallet_id;
            if (!$walletId){
                return response()->json($this->withError('wallet id filed is required'));
            }
            $rate = $this->getCurrencyRate(basicControl()->base_currency, $gatewayCurrency);
            $baseAmount = $amount * $rate;
            $deposit = Deposit::create([
                'user_id' => Auth::user()->id,
                'depositable_id' => $transfer->id ?? null,
                'depositable_type' => isset($transfer) ? MoneyTransfer::class : null,
                'payment_method_id' => 0,
                'payment_method_currency' => $gatewayCurrency,
                'amount' => $gatewayCurrencyAmount,
                'wallet_id' => $walletId ?? null,
                'percentage_charge' => 0,
                'fixed_charge' => 0,
                'payable_amount' => $gatewayCurrencyAmount,
                'base_currency_charge' => 0,
                'payable_amount_in_base_currency' => $baseAmount ?? 0,
                'trx_id' => strRandom(),
                'status' => 0,
            ]);
            BasicService::preparePaymentUpgradation($deposit);
            return response()->json($this->withSuccess('Payment Success'));

        }
        else {
            $checkAmountValidate = $this->validationCheck($gatewayCurrencyAmount, $gateway, $gatewayCurrency,null);
            if ($checkAmountValidate['status'] == 'error') {
                return response()->json($this->withError($checkAmountValidate['msg']));
            }
            $deposit = Deposit::create([
                'user_id' => Auth::user()->id,
                'depositable_id' => $transfer->id ?? null,
                'depositable_type' => isset($transfer) ? MoneyTransfer::class : null,
                'payment_method_id' => $checkAmountValidate['data']['gateway_id'],
                'payment_method_currency' => $checkAmountValidate['data']['currency'],
                'amount' => $gatewayCurrencyAmount,
                'wallet_id' => $walletId ?? null,
                'percentage_charge' => $checkAmountValidate['data']['percentage_charge'],
                'fixed_charge' => $checkAmountValidate['data']['fixed_charge'],
                'payable_amount' => $checkAmountValidate['data']['payable_amount'],
                'base_currency_charge' => $checkAmountValidate['data']['base_currency_charge'],
                'payable_amount_in_base_currency' => $checkAmountValidate['data']['payable_amount_base_in_currency'],
                'trx_id' => strRandom(),
                'status' => 0,
            ]);
        }
        return response()->json($this->withSuccess($deposit->trx_id));
    }

    public function transferPay($uuid)
    {
        try {
            $item = MoneyTransfer::with('recipient.bank:id,service_id')->where('uuid', $uuid)->first();
            if (!$item){
                return response()->json($this->withError('Transfer Not Found'));
            }
            if ($item->recipient->deleted_at != null) {
                return response()->json($this->withError('Invalid Transaction. You Removed This Recipient'));
            }
            $formattedData = [
                'id' => $item->id,
                'senderCurrency' => $item->sender_currency,
                'send_amount' => currencyPositionCalc($item->send_amount, $item->senderCurrency),
                'transfer_fee' => currencyPositionCalc($item->fees, $item->senderCurrency),
                'send_total' => currencyPositionCalc($item->payable_amount, $item->senderCurrency),
                'receiver_amount' => currencyPositionCalc($item->recipient_get_amount, $item->receiverCurrency),
                'service_name' => $item->service->name,
            ];
            $data['payDetails'] = $formattedData;

            return response()->json($this->withSuccess($data));
        } catch (\Exception $e) {
            return response()->json($this->withError($e->getMessage()));
        }
    }

    public function currencyRate(Request $request)
    {
        try {
            $selectedCurrency = $request->selectedCurrency;
            $senderCurrency = $request->senderCurrency;
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
            return response()->json($this->withSuccess($response));
        } catch (\Exception $e) {
            return response()->json($this->withError($e->getMessage()));
        }
    }

}
