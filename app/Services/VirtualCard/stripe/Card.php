<?php

namespace App\Services\VirtualCard\stripe;

use App\Models\VirtualCardMethod;
use App\Models\VirtualCardOrder;
use App\Models\VirtualCardTransaction;
use Stripe\Exception\ApiErrorException;
use Stripe\Issuing\Cardholder;
use Stripe\Issuing\Card as IssueCard;
use Stripe\Stripe;

require_once(base_path('app/Services/stripe-php/init.php'));

class Card
{

    public static function getTrx($card_id): void
    {
        $secret_key = optional(VirtualCardMethod::where('code', 'stripe')->first())->parameters->secret_key;
        $stripe = new \Stripe\StripeClient($secret_key);

        $transactions = $stripe->issuing->transactions->all(['card' => $card_id, 'limit' => 100]);

        $cardOrder = VirtualCardOrder::where('card_id', $card_id)->firstOrFail();

        foreach ($transactions->data as $transaction) {
            $formattedData = [
                'card_id' => $transaction->card,
                'amount' => abs($transaction->amount) / 100,
                'balance' => $transaction->balance_transaction,
                'type' => $transaction->type,
                'currency' => strtoupper($transaction->currency),
                'reference' => $transaction->id,
                'date' => \Carbon\Carbon::createFromTimestamp($transaction->created)->format('Y-m-d H:i:s'),
                'narration' => $transaction->merchant_data['name'],
            ];

            VirtualCardTransaction::updateOrCreate(
                ['user_id' => auth()->id(), 'card_id' => $card_id, 'trx_id' => $transaction->id],
                [
                    'card_order_id' => $cardOrder->id,
                    'data' => $formattedData,
                    'amount' => abs($transaction->amount) / 100,
                    'currency' => strtoupper($transaction->currency),
                ]
            );
        }
    }


    public static function cardRequest($cardOrder, $operation)
    {
        $cardMethod = VirtualCardMethod::where('code', 'stripe')->firstOrFail();
        $secret_key = $cardMethod->parameters?->secret_key;

        if (!is_string($secret_key) || empty($secret_key)) {
            return [
                'status' => 'error',
                'data' => 'Invalid or missing secret key'
            ];
        }

        return match ($operation) {
            'create' => self::createCard($secret_key, $cardOrder),
            'block' => self::blockCard($secret_key, $cardOrder),
            'unblock' => self::unblockCard($secret_key, $cardOrder),
            'fundApprove' => self::fundAddCard($secret_key, $cardOrder),
            default => [
                'status' => 'error',
                'data' => 'Invalid operation'
            ],
        };
    }


    public static function createCard($secret_key, $cardOrder): array
    {
        $card_currency = strtolower($cardOrder->currency);
        Stripe::setApiKey($secret_key);
        try {
            $userInfo = $cardOrder?->form_input;
            $dateString = $userInfo->DateOfBirth->field_value;
            list($year, $month, $day) = explode('-', $dateString);
            $dob = ['day' => (int)$day, 'month' => (int)$month, 'year' => (int)$year];
            $cardholder = Cardholder::create([
                'name' => $userInfo->BillingName->field_value,
                'email' => $userInfo->Email->field_value,
                'phone_number' => $userInfo->Phone->field_value,
                'status' => 'active',
                'type' => 'individual',
                'individual' => [
                    'first_name' => $userInfo->FirstName->field_value,
                    'last_name' => $userInfo->LastName->field_value,
                    'dob' => $dob,
                    'card_issuing' => [
                        'user_terms_acceptance' => [
                            'ip' => $_SERVER['REMOTE_ADDR'],
                            'date' => time(),
                        ],
                    ],
                ],
                'billing' => [
                    'address' => [
                        'line1' => $userInfo->BillingAddress->field_value,
                        'city' => $userInfo->BillingCity->field_value,
                        'state' => $userInfo->BillingState->field_value,
                        'postal_code' => $userInfo->BillingPostalCode->field_value,
                        'country' => $userInfo->BillingCountry->field_value,
                    ],
                ],
            ]);
            $cardholder = Cardholder::retrieve($cardholder->id);
            $virtualCard = IssueCard::create([
                'cardholder' => $cardholder->id,
                'currency' => $card_currency,
                'type' => 'virtual',
                'status' => 'active',
            ]);

            $stripe = new \Stripe\StripeClient($secret_key);
            $card = $stripe->issuing->cards->retrieve($virtualCard->id, ['expand' => ['number', 'cvc']]);
            // $card = $stripe->issuing->cards->retrieve('ic_1PnEUQCmxe5i1RDbkovqXGEC', ['expand' => ['number', 'cvc']]);

            $preprocessedData = self::preprocessCardData($card);

            $exp_month = str_pad($card->exp_month, 2, '0', STR_PAD_LEFT);
            $exp_year = $card->exp_year;
            $expiry_date = $exp_year . '-' . $exp_month . '-01';
            return [
                'status' => 'success',
                'name_on_card' => $card->cardholder?->name ?? null,
                'card_id' => $card->id,
                'brand' => $card->brand,
                'expiry_date' => $expiry_date,
                'cvv' => $card->cvc ?? null,
                'card_number' => $card->number ?? null,
                'balance' => $card->balance ?? null,
                'data' => $preprocessedData,
            ];

        } catch (ApiErrorException $e) {
            return [
                'status' => 'error',
                'data' => $e->getMessage()
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'data' => 'An unexpected error occurred: ' . $e->getMessage()
            ];
        }
    }


    public static function updateCardStatus($secret_key, $cardOrder, $status): array
    {
        $stripe = new \Stripe\StripeClient($secret_key);

        try {
            $updatedCard = $stripe->issuing->cards->update($cardOrder->card_Id, ['status' => $status]);

            return [
                'status' => $updatedCard->status === $status ? 'success' : 'error',
                'data' => $updatedCard->status === $status ? ucfirst($status) . ' successfully' : 'Card status update failed',
            ];
        } catch (ApiErrorException $e) {
            return [
                'status' => 'error',
                'data' => $e->getMessage(),
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'data' => 'An error occurred: ' . $e->getMessage(),
            ];
        }
    }

    public static function blockCard($secret_key, $cardOrder): array
    {
        return self::updateCardStatus($secret_key, $cardOrder, 'inactive');
    }

    public static function unblockCard($secret_key, $cardOrder): array
    {
        return self::updateCardStatus($secret_key, $cardOrder, 'active');
    }


    public static function createSource($secret_key, $token, $currency, $name)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://api.stripe.com/v1/sources");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'type' => 'card',
            'token' => $token,
            'currency' => $currency,
            'owner[name]' => $name,
        ]));
        curl_setopt($ch, CURLOPT_USERPWD, $secret_key . ":");

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);

        return json_decode($result, true);
    }


    public static function fundAddCard($secret_key, $cardOrder)
    {
        try {
            if ($cardOrder) {
                $amount = $cardOrder->fund_amount;
                $cardOrder->balance += $amount;

                return [
                    'status' => 'success',
                    'balance' => $cardOrder->balance,
                    'data' => 'Funds successfully added to card balance',
                ];
            } else {
                return [
                    'status' => 'error',
                    'data' => 'Failed to add funds to card balance',
                ];
            }
        } catch (ApiErrorException $e) {
            return [
                'status' => 'error',
                'data' => $e->getMessage()
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'data' => 'An unexpected error occurred: ' . $e->getMessage()
            ];
        }
    }


    public static function preprocessCardData($virtualCard): array
    {
        $exp_month = str_pad($virtualCard->exp_month, 2, '0', STR_PAD_LEFT);
        $exp_year = substr($virtualCard->exp_year, -2);
        $expiry_date = $exp_month . '/' . $exp_year;

        $cardholderAddress = $virtualCard->cardholder?->billing?->address;

        return [
            'id' => $virtualCard->id,
            'balance' => number_format($virtualCard->balance ?? 0, 2),
            'currency' => strtoupper($virtualCard->currency ?? ''),
            'brand' => strtoupper($virtualCard->brand ?? ''),
            'name_on_card' => $virtualCard->cardholder?->name ?? null,
            'email' => $virtualCard->cardholder?->email ?? null,
            'phone_number' => $virtualCard->cardholder?->phone_number ?? null,
            'card_number' => $virtualCard->number ?? null,
            'last4_digit_of_card' => $virtualCard->last4 ?? null,
            'exp_month' => $virtualCard->exp_month,
            'exp_year' => $virtualCard->exp_year,
            'expiry_date' => $expiry_date,
            'cvc' => $virtualCard->cvc ?? null,
            'address_street' => $cardholderAddress->line1,
            'address_city' => $cardholderAddress->city,
            'address_country' => $cardholderAddress->country,
            'postal_code' => $cardholderAddress->postal_code,
            'status' => $virtualCard->status,
        ];
    }
}


//$secret_key = 'sk_test_51OLg62Cmxe5i1RDb2m9UJuIq89UGccR6wK5NVFmpY6NyK24ohjaDQ2wDf2QwDIlNskkmWJVv2lx6eL50xvocdf3700R8VT3350';
