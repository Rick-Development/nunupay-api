<?php

namespace App\Services;

use App\Models\MoneyTransfer;
use App\Models\Transaction;
use App\Models\VirtualCardOrder;
use App\Traits\ManageWallet;
use App\Traits\Notify;
use GPBMetadata\Google\Api\Auth;
use Illuminate\Support\Facades\DB;

class BasicService
{
    use Notify, ManageWallet;

    public function setEnv($value)
    {
        $envPath = base_path('.env');
        $env = file($envPath);
        foreach ($env as $env_key => $env_value) {
            $entry = explode("=", $env_value, 2);
            $env[$env_key] = array_key_exists($entry[0], $value) ? $entry[0] . "=" . $value[$entry[0]] . "\n" : $env_value;
        }
        $fp = fopen($envPath, 'w');
        fwrite($fp, implode($env));
        fclose($fp);
    }

    public function preparePaymentUpgradation($deposit)
    {

        DB::beginTransaction();
        try {
            if (!$deposit->user || !in_array($deposit->status, [0,2])) {
                return false;
            }

            $basic = basicControl();

            $walletBalance = null;
            $transactionRemarks = 'Deposit';
            $transactionType = '+';

            $pm_currency = $deposit->payment_method_currency;
            $walletCurrency = optional(optional($deposit->wallet)->currency)->code;
            $rate = $this->getCurrencyRate($walletCurrency, $pm_currency);
            $amount = $deposit->amount * $rate;

            if ($deposit->depositable_type == null) {
                $walletBalance = $this->updateWallet($deposit->user_id, $deposit->wallet_id, $amount, 1);
                $deposit->status = 1;
                $deposit->save();

                if ($deposit->user->refer_bonus == 0 && $basic->refer_status == 1) {
                    $deposit->user->refer_bonus = 1;
                    $deposit->user->save();
                    $sendingWallet = $deposit->user->sendingReferBonusWallet();
                    $rate = $this->getCurrencyRate($sendingWallet->currency_code, 'USD');
                    $amount = $basic->refer_earn_amount * $rate;
                    $sendingWallet->balance += $amount;
                    $sendingWallet->save();
                }
                $this->sendNotifications($deposit);
            }

            if ($deposit->depositable) {
                if ($deposit->depositable instanceof MoneyTransfer) {
                    $deposit->depositable->update([
                        'status' => 2,
                        'payment_status' => 0,
                        'paid_at' => now(),
                    ]);
                    $transactionRemarks = 'Transferred Money';
                    $transactionType = '-';
                    $this->sendMoneyNotifications($deposit);
                } elseif ($deposit->depositable instanceof VirtualCardOrder) {
                    $deposit->depositable->update([
                        'status' => 8,
                        'fund_amount' => $deposit->amount,
                        'fund_charge' => $deposit->fixed_charge,
                    ]);
                    $transactionRemarks = 'Virtual Card Funded';
                    $transactionType = '-';
                }

                if ($deposit->wallet_id) {
                    $walletBalance = $this->updateWallet($deposit->user_id, $deposit->wallet_id, $amount, 0);
                }

                $deposit->status = 1;
                $deposit->save();

            }
            $transaction = $this->createTransaction($deposit, $walletBalance, $transactionRemarks, $transactionType);
            $this->saveDepositAndTransaction($deposit, $transaction);
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
    }


    private function createTransaction($deposit, $walletBalance, $remarks, $type): Transaction
    {
        $transaction = new Transaction();
        $transaction->user_id = $deposit->user_id;
        $transaction->wallet_id = $deposit->wallet_id ?? null;
        $transaction->amount = $deposit->amount;
        $transaction->base_amount = $deposit->payable_amount_in_base_currency;
        $transaction->currency = $deposit->payment_method_currency;
        $transaction->charge = getAmount($deposit->base_currency_charge);
        $transaction->balance = getAmount($walletBalance ?? $transaction->balance ?? 0);
        $transaction->trx_type = $type;
        $transaction->trx_id = $deposit->trx_id;
        $transaction->remarks = $remarks . ' Via ' . (optional($deposit->gateway)->name ?? ($deposit->payment_method_id == 0 ? "Wallet" : ""));
        return $transaction;
    }

    private function saveDepositAndTransaction($deposit, $transaction): void
    {
        $deposit->transactional()->save($transaction);
        $deposit->save();
    }

    private function sendNotifications($deposit): void
    {
        $user = $deposit->user;
        try {
            $params = [
                'amount' => getAmount($deposit->amount, 2),
                'currency' => $deposit->payment_method_currency,
                'transaction' => $deposit->trx_id,
            ];

            $action = [
                "link" => route('user.fund.index'),
                "icon" => "fa fa-money-bill-alt text-white"
            ];
            $firebaseAction = '#';
            $this->sendMailSms($user, 'ADD_FUND_USER_USER', $params);
            $this->userPushNotification($user, 'ADD_FUND_USER_USER', $params, $action);
            $this->userFirebasePushNotification($user, 'ADD_FUND_USER_USER', $params, $firebaseAction);

            $paramsAdmin = [
                'username' => optional($user)->username,
                'amount' => getAmount($deposit->amount),
                'currency' => $deposit->payment_method_currency,
                'transaction' => $deposit->trx_id,
            ];
            $actionAdmin = [
                "name" => $user->fullname(),
                "image" => $user->getImage(),
                "link" => route('admin.payment.log'),
                "icon" => "fas fa-money-bill-alt text-white"
            ];

            $firebaseActionAdmin = "#";
            $this->adminMail('ADD_FUND_USER_ADMIN', $paramsAdmin, $actionAdmin);
            $this->adminPushNotification('ADD_FUND_USER_ADMIN', $paramsAdmin, $actionAdmin);
            $this->adminFirebasePushNotification('ADD_FUND_USER_ADMIN', $paramsAdmin, $firebaseActionAdmin);
        } catch (\Exception $e) {

        }
    }

    private function sendMoneyNotifications($deposit): void
    {
        $user = $deposit->user;
        try {
            $params = [
                'amount' => getAmount($deposit->amount),
                'currency' => $deposit->payment_method_currency,
                'transaction' => $deposit->trx_id,
            ];
            $action = [
                "link" => route('user.transferList'),
                "icon" => "fa fa-money-bill-alt text-white"
            ];
            $firebaseAction = '#';
            $this->sendMailSms($user, 'MONEY_TRANSFER_USER', $params);
            $this->userPushNotification($user, 'MONEY_TRANSFER_USER', $params, $action);
            $this->userFirebasePushNotification($user, 'MONEY_TRANSFER_USER', $params, $firebaseAction);

            $paramsAdmin = [
                'username' => optional($user)->username,
                'amount' => getAmount($deposit->amount),
                'currency' => $deposit->payment_method_currency,
                'transaction' => $deposit->trx_id,
            ];
            $actionAdmin = [
                "name" => $user->fullname(),
                "image" => $user->getImage(),
                "link" => route('admin.transferList'),
                "icon" => "fas fa-money-bill-alt text-white"
            ];

            $firebaseActionAdmin = "#";
            $this->adminMail('MONEY_TRANSFER_ADMIN', $paramsAdmin, $actionAdmin);
            $this->adminPushNotification('MONEY_TRANSFER_ADMIN', $paramsAdmin, $actionAdmin);
            $this->adminFirebasePushNotification('MONEY_TRANSFER_ADMIN', $paramsAdmin, $firebaseActionAdmin);
        } catch (\Exception $e) {

        }
    }

    public function cryptoQR($wallet, $amount, $crypto = null)
    {
        $cryptoQr = $wallet . "?amount=" . $amount;
        return "https://quickchart.io/chart?cht=qr&chl=$cryptoQr";
    }
}
