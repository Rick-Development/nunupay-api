<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Helpers\Payscribe\BillsPayments\FundBetWalletHelper;
use App\Http\Helpers\Payscribe\PayscribeBalanceHelper;
use App\Http\Helpers\Payscribe\PayscribePayoutHelper;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PayscribeFundBetWalletController extends Controller
{

    private $modelPath = 'PayscribeFundBetWallet';

    public function __construct(private FundBetWalletHelper $fundBetWalletHelper, private PayscribeBalanceHelper $payscribeBalanceHelper){} 
    //
    public function bettingServiceProviderList() {
        $response = json_decode( $this->fundBetWalletHelper->bettingServiceProviderList(), true);
        return $response;
    }

    public function validateBetAccount(Request $request) {
        $data = $request->validate([
            'bet_id' => 'required | string',
            'customer_id' => 'required | string',
        ]);

        $response = json_decode( $this->fundBetWalletHelper->validateBetAccount($data['bet_id'], $data['customer_id']), true);
        return $response;
    }

    public function fundWallet(Request $request) {
        $data = $request->validate( [
            "bet_id" => "string",
            "customer_id" =>  'required | string',
            "customer_name" =>  'required | string',
            "amount" =>  'required | integer',
        ]);


        $referenceId = Str::uuid();
        $referenceIdString = (string) $referenceId;
        $data = array_merge($data, ['ref' => $referenceIdString]);

        $validateBalance = $this->payscribeBalanceHelper->validateBalance($data['amount']);
            
        if(!!$validateBalance){
            return $validateBalance;
        }

        $response = json_decode($this->fundBetWalletHelper->fundWallet($data), true);
        
        if($response['status'] === true){
            $this->createTransaction($data, $response, $this->modelPath); 

        }
        return $response;

    }

    private function createTransaction($request, $response, $modelPath) {
        $balance = auth()->user()->account_balance - $request['amount'];
        $transId = $response['message']['details']['trans_id'];
        Transaction::create([
            'transactional_type' => $modelPath,
            'user_id' => auth()->user()->id,
            'amount' => $request['amount'],
            'currency' => 'NGN',
            'charge' => $request['amount'],
            'trx_type' => '-',
            'remarks' => $response['description'],
            'balance' => $balance,
            'trx_id' => $transId,
            'transaction_status' => 'proccessing',
        ]);
        $this->payscribeBalanceHelper->updateUserBalance($balance);

    }
}