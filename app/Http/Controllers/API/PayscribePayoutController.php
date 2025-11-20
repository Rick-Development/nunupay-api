<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Helpers\Payscribe\Payout\PayoutHelper;
use App\Http\Helpers\Payscribe\PayscribeBalanceHelper;
use App\Http\Helpers\Payscribe\PayscribePayoutHelper;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PayscribePayoutController extends Controller
{
    private $modelPath = 'PayscribePayout';

    public function __construct(private PayscribePayoutHelper $payscribePayoutHelper, private PayscribeBalanceHelper $payscribeBalanceHelper){}

    public function accountLookUp(Request $request) {
        $data = $request->validate([
            'account' => 'required | string',
            'bank' => 'required | string',
        ]);
        
        try {
            $response = json_decode($this->payscribePayoutHelper->validateAccountBeforeInitiatingTransfer($data), 
            true);

            return $response;
        } 
        catch(\Exception $e){
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function payoutFee(Request $request){
        $data = $request->validate([
            'amount' => 'required | string',
        ]);
        try {
            $response = json_decode($this->payscribePayoutHelper->getPayoutsFee($data['amount']), 
            true);
            return $response;
        } 
        catch(\Exception $e){
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function transfer(Request $request)
    {
        $data = $request->validate([
            'amount' => 'required | string',
            'bank' => 'required | string',
            'account' => 'required | string',
            'currency' => 'required | string',
            'narration' => 'required | string',
        ]);

        $referenceId = Str::uuid();
        $referenceIdString = (string) $referenceId;
        $data = array_merge($data, ['ref' => $referenceIdString]);

        try {
            $validateBalance = $this->payscribeBalanceHelper->validateBalance($data['amount']);
            
            if(!!$validateBalance){
                return $validateBalance;
            }

            $response = json_decode($this->payscribePayoutHelper->transfer($data), 
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

    public function verifyTransfer(Request $request) {
        $data = $request->validate([
            'trans_id' => 'required | string',
        ]);

        try {
            $response = json_decode($this->payscribePayoutHelper->verifyTransfer($data), 
            true);


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
        $totalCharge = $response['message']['details']['total'];
        $charge = $response['message']['details']['fee'];
        $amount =  $response['message']['details']['amount'];
        // $balance = auth()->user()->account_balance - $totalCharge;
        $transId = $response['message']['details']['trans_id'];
        Transaction::create([
            'transactional_type' => $modelPath,
            'user_id' => auth()->user()->id,
            'amount' => $amount,
            'currency' => 'NGN',
            'charge' => $charge,
            'trx_type' => '-',
            'remarks' => $response['description'],
            'trx_id' => $transId,
            'transaction_status' => 'proccessing',
        ]);
    } 
}