<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Helpers\Payscribe\CardIssusing\CreateCardHelper;
use App\Http\Helpers\Payscribe\PayscribeBalanceHelper;
use App\Models\BasicControl;
use App\Models\PayscribeVirtualCardDetails;
use App\Models\PayscribeVirtualCardTransaction;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PayscribeCreateCardController extends Controller
{
    private $modelPath = 'PayscribeCardIssuing';

    public function __construct(private CreateCardHelper $createCardHelper, private PayscribeBalanceHelper $payscribeBalanceHelper){}

    public function createCard(Request $request){
        $data = $request->validate(
            [
                "customer_id"=> 'required | string',
                "currency"=> 'required | string',
                "brand"=> 'required | string | in:VISA,MASTERCARD',
                "amount"=> 'required | integer | min:1',
                "type"=> 'required | string | in:virtual,physical',
            ]
        );

        // Validate the user's balance before proceeding
        $cardIssuingRate = (int) BasicControl::first()->card_issuing_rate;
        $cardDepositRate = (int) BasicControl::first()->card_deposit_rate;
        $depositAmount = $data['amount'] * $cardDepositRate;
        // \Log::info()
        
        $totalCharged = $depositAmount + $cardIssuingRate;

        $validateBalance = $this->payscribeBalanceHelper->validateBalance($totalCharged);
        
        if(!!$validateBalance){
            return $validateBalance;
        }
        //Modified amount ...Payscribe topup 1 USD by default 
        // $amount = $data['amount'] - 1;
        // Generate a UUID
        $referenceId = Str::uuid();
        // Convert to string if needed
        $referenceIdString = (string) $referenceId;
        $data = array_merge($data, ['ref' => $referenceIdString]);

        $response = json_decode($this->createCardHelper->createCard($data), true);
        
        if($response['status'] === true){
            // Create a transaction record for the card issuing
            $this->createTransaction($data, $response, $depositAmount); 
            $this->cardIssuingTransaction($data, $response, $cardIssuingRate);

            $this->virtualCardDetails($response, $data);
            //update balance
            $this->updateBalance($totalCharged);
            /// Store card details

        }

        return $response;
    }


    public function virtualCardDetails($response, $request) {
        PayscribeVirtualCardDetails::create([
            'user_id' => auth()->user()->id,
            'card_id' => $response['message']['details']['card']['id'],
            'card_type' => $response['message']['details']['card']['card_type'],
            'currency' => $response['message']['details']['card']['currency'],
            'brand' => $response['message']['details']['card']['brand'],
            'card_name' => $response['message']['details']['card']['name'],
            'masked' => $response['message']['details']['card']['masked'],
            'card_number' => $response['message']['details']['card']['number'],
            'expiry_date' => $response['message']['details']['card']['expiry'],
            'ccv' => $response['message']['details']['card']['ccv'],
            'billing_address' => $response['message']['details']['card']['billing'],
            'trans_id' => $response['message']['details']['trans_id'],
            'ref' => $response['message']['details']['ref'],
            'balance' => $request['amount'],
        ]);
    }
    private function createTransaction($request, $response, $depositAmount) {
        $balance = auth()->user()->account_balance - $depositAmount;
        $transId = $response['message']['details']['trans_id'];
        Transaction::create([
            'transactional_type' => 'Card Topup',
            'user_id' => auth()->user()->id,
            'amount' => $depositAmount,
            'currency' => 'NGN',
            // 'charge' => $request['amount'],
            'trx_type' => '-',
            'remarks' => 'You have successfully funded your card with ' . $request['amount'] . ' USD',
            'trx_id' => $transId,
            'transaction_status' => 'success',
        ]);

        // $this->payscribeBalanceHelper->updateUserBalance($balance);
    } 

    private function cardIssuingTransaction($request, $response, $cardIssuingRate) {
        $balance = auth()->user()->account_balance - $cardIssuingRate;
        $transId = $response['message']['details']['trans_id'];
        Transaction::create([
            'transactional_type' => 'Card Issuing',
            'user_id' => auth()->user()->id,
            'amount' => $cardIssuingRate,
            'currency' => 'NGN',
            'trx_type' => '+',
            'remarks' => 'Card Issuing at ' . $cardIssuingRate . ' NGN', 
            'trx_id' => $transId,
            'transaction_status' => 'success',
        ]);

        // $this->payscribeBalanceHelper->updateUserBalance($balance);
    } 

    private function updateBalance($totalCharged){
        $balance = auth()->user()->account_balance - $totalCharged;
        $this->payscribeBalanceHelper->updateUserBalance($balance);

    }



    // private function cardIssuingTransaction($request, $response, $cardIssuingRate) {
    //     $balance = auth()->user()->account_balance - $cardIssuingRate;
    //     $transId = $response['message']['details']['trans_id'];
    //     PayscribeVirtualCardTransaction::create([
    //         'transactional_type' => $this->modelPath,
    //         'user_id' => auth()->user()->id,
    //         'amount' => $cardIssuingRate,
    //         'currency' => 'USD',
    //         'balance' => $balance,
    //         'charge' => $request['amount'],
    //         'trx_type' => '-',
    //         'remarks' => $response['description'],
    //         'trx_id' => $transId,
    //         'transaction_status' => 'processing',
    //         // 'discount' => $response['message']['details']['discount'],
    //     ]);

    //     $this->payscribeBalanceHelper->updateUserBalance($balance);
    // } 


    public function cardIssuingRate() {
        $cardIssuingRate = BasicControl::first()->card_issuing_rate;
        return response()->json([
            'status' => true,
            'message' => 'Card Issuing Rate',
            'data' => $cardIssuingRate,
        ], 200);
    }

    public function cardDepositRate() {
        $cardDepositRate = BasicControl::first()->card_deposit_rate;
        return response()->json([
            'status' => true,
            'message' => 'Card Issuing Rate',
            'data' => $cardDepositRate,
        ], 200);
    }

    public function cardWithdrawalRate() {
        $cardWithdarwalRate = BasicControl::first()->card_withdrawal_rate;
        return response()->json([
            'status' => true,
            'message' => 'Card Issuing Rate',
            'data' => $cardWithdarwalRate,
        ], 200);
    }



    public function customerTransactions(string $cardId)
    {
        
        $transactions = PayscribeVirtualCardTransaction::where('card_id', $cardId)->paginate(10);
        return response()->json(['transactions' => $transactions]);
        // return response()->json(['transactions' => auth()->id()]);
    }

    public function customerCardDetails()
    {
        
        $transactions = PayscribeVirtualCardDetails::where('user_id', auth()->id())->paginate(10);
        return response()->json(['transactions' => $transactions]);
        // return response()->json(['transactions' => auth()->id()]);
    }
}