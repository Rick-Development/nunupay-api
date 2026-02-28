<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Helpers\Payscribe\BillsPayments\EpinsHelper;
use App\Http\Helpers\Payscribe\PayscribeBalanceHelper;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PayscribeEpinsController extends Controller
{
    private $modelPath = 'PayscribeEpins';
    public function __construct(private EpinsHelper $epinsHelper, private PayscribeBalanceHelper $payscribeBalanceHelper ){}


    public function avaliableEpin() {
        try {
            $response = json_decode($this->epinsHelper->getAvaliableEpin(), true);
            return $response;
        } 
        catch(\Exception $e){
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function purchaseEpin(Request $request) {
        $data = $request->validate([
            'id' => 'required | string',
            'qty' => 'required | string',
            'amount' => 'required | string',
        ]);
        $referenceId = Str::uuid();
        $referenceIdString = (string) $referenceId;
        $data = array_merge($data, ['ref' => $referenceIdString]);
        try {
            $validateBalance = $this->payscribeBalanceHelper->validateBalance($data['amount']);
            
            if(!!$validateBalance){
                return $validateBalance;
            }
            
            $response = json_decode($this->epinsHelper->purchaseEpins($data), true);
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

    public function jambUserLookup(Request $request) {
        $data = $request->validate([
            'id' => 'required | string',
            'account' => 'sometimes | string',
        ]);
        try {
            $response = json_decode($this->epinsHelper->jambUserLookup($data), true);
            return $response;
        } 
        catch(\Exception $e){
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function retreiveEpin(Request $request) {
        $data = $request->validate([
            'trans_id' => 'required | string',
        ]);
        try {
            $response = json_decode($this->epinsHelper->retreiveEpins($data), true);
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
            'amount' =>  $response['message']['details']['total_charge'],
            'currency' => 'NGN',
            'balance' => $balance,
            'charge' => $response['message']['details']['total_charge'],
            'trx_type' => '-',
            'remarks' => $response['description'],
            'trx_id' => $transId,
            'transaction_status' => 'processing',
            'discount' => $response['message']['details']['discount'],
        ]);
        $this->payscribeBalanceHelper->updateUserBalance($balance);
    } 

}