<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Helpers\Payscribe\BillsPayments\AirtimeHelper;
use App\Http\Helpers\Payscribe\PayscribeBalanceHelper;
use App\Http\Helpers\Payscribe\PayscribePayoutHelper;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Str;


class PayscribeAirtimeController extends Controller
{

    private $modelPath = 'PayscribeAirtime';

    public function __construct(private AirtimeHelper $airtimeHelper, private PayscribeBalanceHelper $payscribeBalanceHelper) {}

    public function airtime(Request $request) {
        $data = $request->validate([
            'network' => 'required | string',
            "amount" => 'required | string',
            "recipient" => 'required | string',
            "ported" => 'sometimes | boolean',
        ]);

        $referenceId = Str::uuid();
        $referenceIdString = (string) $referenceId;
        $data = array_merge($data, ['ref' => $referenceIdString]);
        
        try {
            $validateBalance = $this->payscribeBalanceHelper->validateBalance($data['amount']);
            
            if(!!$validateBalance){
                return $validateBalance;
            }

            $response = json_decode($this->airtimeHelper->vendairtime($data), 
            true);

            if($response['status'] === true){
                $this->createTransaction($data, $response, $this->modelPath); 

            }
            return $response;
        } 
        catch(\Exception $e){
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
        
    }



    private function createTransaction($request, $response, $modelPath) {
        $balance = auth()->user()->account_balance - $request['amount'];
        $transId = $response['message']['details']['trans_id'];
        Transaction::create([
            'transactional_type' => $modelPath,
            'user_id' => auth()->user()->id,
            'amount' => $request['amount'],
            'currency' => 'NGN',
            'balance' => $balance,
            'charge' => $request['amount'],
            'trx_type' => '-',
            'remarks' => $response['description'],
            'trx_id' => $transId,
            'transaction_status' => 'processing',
            'discount' => $response['message']['details']['discount'],
        ]);

        $this->payscribeBalanceHelper->updateUserBalance($balance);
    } 
}