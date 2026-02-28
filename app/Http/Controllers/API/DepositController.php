<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Deposit;
use App\Models\Gateway;
use App\Models\MoneyTransfer;
use App\Models\VirtualCardOrder;
use App\Traits\ApiResponse;
use App\Traits\ManageWallet;
use Facades\App\Services\BasicService;
use Illuminate\Http\Request;
use App\Traits\PaymentValidationCheck;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class DepositController extends Controller
{
    use ApiResponse,PaymentValidationCheck,ManageWallet;

    public function supportedCurrency(Request $request)
    {
        try {
            $gateway = Gateway::where('id', $request->gateway)->first();
            $data = [
                'supported_currency' => $gateway->supported_currency,
                'currencyType' => $gateway->currency_type,
                'rates' => $gateway->receivable_currencies,
            ];
            return response()->json($this->withSuccess($data));
        } catch (\Exception $e) {
            return response()->json($this->withError($e->getMessage()));
        }
    }

    public function checkAmount(Request $request)
    {
        try {
            $amount = $request->amount;
            $selectedCurrency = $request->selected_currency;
            $selectGateway = $request->select_gateway;
            $selectedCryptoCurrency = $request->selectedCryptoCurrency;
            $data = $this->checkAmountValidate($amount, $selectedCurrency, $selectGateway, $selectedCryptoCurrency);
            return response()->json($this->withSuccess($data));
        }
        catch (\Exception $e ){
            return response()->json($this->withError('Invalid request'));
        }
    }

    public function checkAmountValidate($amount, $selectedCurrency, $selectGateway, $selectedCryptoCurrency = null)
    {
        $selectGateway = Gateway::where('id', $selectGateway)->where('status', 1)->first();
        if (!$selectGateway) {
            return response()->json($this->withError('Payment method not available for this transaction'));
        }

        if ($selectGateway->currency_type == 1) {
            $selectedCurrency = array_search($selectedCurrency, $selectGateway->supported_currency);
            if ($selectedCurrency !== false) {
                $selectedPayCurrency = $selectGateway->supported_currency[$selectedCurrency];
            } else {
                return response()->json($this->withError('Please choose the currency you\'d like to use for payment'));
            }
        }

        if ($selectGateway->currency_type == 0) {
            $selectedCurrency = array_search($selectedCryptoCurrency, $selectGateway->supported_currency);
            if ($selectedCurrency !== false) {
                $selectedPayCurrency = $selectGateway->supported_currency[$selectedCurrency];
            } else {
                return response()->json($this->withError('Please choose the currency you\'d like to use for payment'));
            }
        }

        if ($selectGateway) {
            $receivableCurrencies = $selectGateway->receivable_currencies;
            if (is_array($receivableCurrencies)) {
                if ($selectGateway->id < 999) {
                    $currencyInfo = collect($receivableCurrencies)->where('name', $selectedPayCurrency)->first();
                } else {
                    if ($selectGateway->currency_type == 1) {
                        $currencyInfo = collect($receivableCurrencies)->where('currency', $selectedPayCurrency)->first();
                    } else {
                        $currencyInfo = collect($receivableCurrencies)->where('currency', $selectedCryptoCurrency)->first();
                    }
                }
            } else {
                return null;
            }
        }

        $currencyType = $selectGateway->currency_type;
        $limit = $currencyType == 0 ? 8 : 2;
        $amount = getAmount($amount, $limit);
        $status = false;

        if ($currencyInfo) {
            $percentage = getAmount($currencyInfo->percentage_charge, $limit);
            $percentage_charge = getAmount(($amount * $percentage) / 100, $limit);
            $fixed_charge = getAmount($currencyInfo->fixed_charge, $limit);
            $min_limit = getAmount($currencyInfo->min_limit, $limit);
            $max_limit = getAmount($currencyInfo->max_limit, $limit);
            $charge = getAmount($percentage_charge + $fixed_charge, $limit);
        }

        $basicControl = basicControl();
        $payable_amount = getAmount($amount + $charge, $limit);
        $amount_in_base_currency = getAmount($payable_amount / $currencyInfo->conversion_rate ?? 1, $limit);

        if ($amount < $min_limit || $amount > $max_limit) {
            $message = "minimum payment $min_limit and maximum payment limit $max_limit";
        } else {
            $status = true;
            $message = "Amount : $amount" . " " . $selectedPayCurrency;
        }

        $data['status'] = $status;
        $data['message'] = $message;
        $data['fixed_charge'] = $fixed_charge;
        $data['percentage'] = $percentage;
        $data['percentage_charge'] = $percentage_charge;
        $data['min_limit'] = $min_limit;
        $data['max_limit'] = $max_limit;
        $data['payable_amount'] = $payable_amount;
        $data['charge'] = $charge;
        $data['amount'] = $amount;
        $data['conversion_rate'] = $currencyInfo->conversion_rate ?? 1;
        $data['amount_in_base_currency'] = number_format($amount_in_base_currency, 2);
        $data['currency'] = ($selectGateway->currency_type == 1) ? ($currencyInfo->name ?? $currencyInfo->currency) : "USD";
        $data['base_currency'] = $basicControl->base_currency;
        $data['currency_limit'] = $limit;

        return $data;
    }

    public function paymentRequest(Request $request, $transfer = null)
    {
        $userId = \auth()->id();

        $amount = $request->amount;
        $gateway = $request->gateway_id;
        $currency = $request->supported_currency;
        $cryptoCurrency = $request->supported_crypto_currency;
        $walletId = $request->wallet_id;

        $rules = [
            'amount' => 'required',
            'gateway_id' => 'required',
            'supported_currency' => 'required',
            'wallet_id' => $transfer || $request->has('card_id') ? 'nullable' : 'required',
            'card_id' => 'nullable|exists:virtual_card_orders,id',
        ];
        $validator = validator($request->all(),$rules);
        if ($validator->fails()) {
            return response()->json($this->withError(collect($validator->errors())->collapse()));
        }

        $cardId = $request->card_id;
        if ($cardId) {
            $card = VirtualCardOrder::find($cardId);
            if (!$card) {
                return response()->json($this->withError('Invalid card.'));
            }
            $checkAmountValidate = $this->validationCheck($amount, $gateway, $currency, $cryptoCurrency);

            if ($checkAmountValidate['status'] == 'error') {
                return response()->json($this->withError($checkAmountValidate['msg']));
            }
            $data = $checkAmountValidate['data'];

            if ($currency != $card->currency){
                $message = "Your virtual card currency is $card->currency. does not support $currency for adding funds.";
                return response()->json($this->withError($message));
            }

            $deposit = Deposit::create([
                'user_id' => $userId,
                'depositable_id' =>  $cardId ?? null,
                'depositable_type' => VirtualCardOrder::class ?? null,
                'payment_method_id' => $data['gateway_id'],
                'payment_method_currency' => $data['currency'],
                'amount' => $amount,
                'wallet_id' => $walletId ?? null,
                'percentage_charge' => $data['percentage_charge'],
                'fixed_charge' => $data['fixed_charge'],
                'payable_amount' => $data['payable_amount'],
                'base_currency_charge' => $data['base_currency_charge'],
                'payable_amount_in_base_currency' =>$data['payable_amount_base_in_currency'],
                'trx_id' => strRandom(),
                'status' => 0,
            ]);

            return response()->json($this->withSuccess($deposit->trx_id));
        }

        if ($walletId !== null) {
            $userWallet = Auth::user()->wallets()->find($walletId);
            if (!$userWallet) {
                return response()->json($this->withError('Invalid wallet'));
            }
            if (isset($transfer) && ($amount > $userWallet->balance)) {
                return response()->json($this->withError('Insufficient Balance'));
            }
        }

        if (isset($transfer)){
            $transferInfo = MoneyTransfer::find($transfer);
            $feeRate = $this->getCurrencyRate($currency,$transferInfo->sender_currency);
            $transferFee = $transferInfo->fees * $feeRate;
        }


        try {
            $baseRate = $this->getCurrencyRate(basicControl()->base_currency, $currency);
            $baseAmount = $amount * $baseRate;
            if (isset($transfer)) {
                $baseCharge = $transferFee * $baseRate;
            }
            if ($gateway == 0) {

                $deposit = Deposit::create([
                    'user_id' => Auth::user()->id,
                    'depositable_id' => $transfer ?? null,
                    'depositable_type' => isset($transfer) ? MoneyTransfer::class : null,
                    'payment_method_id' => $gateway,
                    'payment_method_currency' => $currency,
                    'amount' => $amount,
                    'wallet_id' => $walletId ?? null,
                    'percentage_charge' => 0,
                    'fixed_charge' => isset($transfer) ? $transferFee : 0,
                    'payable_amount' => $amount,
                    'base_currency_charge' => $baseCharge ?? 0,
                    'payable_amount_in_base_currency' => $baseAmount ?? 0,
                    'trx_id' => strRandom(),
                    'status' => 0,
                ]);

                BasicService::preparePaymentUpgradation($deposit);

            } else {

                $checkAmountValidate = $this->validationCheck($amount, $gateway, $currency, $cryptoCurrency);

                if ($checkAmountValidate['status'] == 'error') {
                    return response()->json($this->withError($checkAmountValidate['msg']));
                }

                $deposit = Deposit::create([
                    'user_id' => Auth::user()->id,
                    'depositable_id' => $transfer ?? null,
                    'depositable_type' => isset($transfer) ? MoneyTransfer::class : null,
                    'payment_method_id' => $checkAmountValidate['data']['gateway_id'],
                    'payment_method_currency' => $checkAmountValidate['data']['currency'],
                    'amount' => $amount,
                    'wallet_id' => $walletId ?? null,
                    'percentage_charge' => $checkAmountValidate['data']['percentage_charge'],
                    'fixed_charge' => isset($transfer) ? $transferFee : $checkAmountValidate['data']['fixed_charge'],
                    'payable_amount' => isset($transfer) ? $amount : $checkAmountValidate['data']['payable_amount'],
                    'base_currency_charge' => isset($transfer) ? $baseCharge : $checkAmountValidate['data']['base_currency_charge'],
                    'payable_amount_in_base_currency' => isset($transfer) ? $baseAmount : $checkAmountValidate['data']['payable_amount_base_in_currency'],
                    'trx_id' => strRandom(),
                    'status' => 0,
                ]);
            }
            $url = route('payment.process', $deposit->trx_id);

            $data = [
                'url' => $url,
                'trx_id' => $deposit->trx_id,
            ];

            if (!isset($transfer)) {
                return response()->json($this->withSuccess($data));
            } elseif (isset($transfer) && $gateway == 0) {
                return response()->json($this->withSuccess('Payment success'));
            } else {
                return response()->json($this->withSuccess($data));
            }

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

}
