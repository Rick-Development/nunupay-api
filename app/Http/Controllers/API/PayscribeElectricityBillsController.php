<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Helpers\Payscribe\BillsPayments\ElectricityBillsHelper;
use App\Http\Helpers\Payscribe\PayscribeBalanceHelper;
use App\Http\Helpers\Payscribe\PayscribePayoutHelper;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Str;


class PayscribeElectricityBillsController extends Controller
{
    // private $giftcardHelper;
    // public function __construct()
    // {
    //     $this->giftcardHelper  = new GiftcardHelper();
    // }
    // $userID = 
    private $modelPath = 'PayscribeElectricityBills';

    public function __construct(private ElectricityBillsHelper $electricityBillsHelper, private PayscribeBalanceHelper $payscribeBalanceHelper) {}

    
    public function validateElectricity(Request $request) {
        $data = $request->validate([
            'meter_number' => 'required | string',
            "meter_type" => 'required | string',
            "amount" => 'required | string',
            "service" => 'required | string',
        ]);
        /// No transqaction id for validate 
        try{

            $response = json_decode($this->electricityBillsHelper->validateElectricity($data), true);
            return $response;
        }
        catch(\Exception $e){
            return $response = [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
        
    
    }

    public function payElectricity(Request $request) {
        $data = $request->validate([
            'meter_number' => 'required | string',
            "meter_type" => 'required | string',
            "amount" => 'required | string',
            "service" => 'required | string',
            "phone" => 'required | string',
            "customer_name" => 'required | string',
        ]);

        $referenceId = Str::uuid();
        $referenceIdString = (string) $referenceId;
        $data = array_merge($data, ['ref' => $referenceIdString]);

        try {
            $validateBalance = $this->payscribeBalanceHelper->validateBalance($data['amount']);
            
            if(!!$validateBalance){
                return $validateBalance;
            }
            
            $response = json_decode($this->electricityBillsHelper->payElectricity($data), true);
            
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

    public function requeryTransaction(Request $request) {
        $data = $request->validate([
            'transaction_id' => 'required | string',
        ]);
        try {

            $response = json_decode($this->electricityBillsHelper->requeryTransaction($data['transaction_id']), true);
            return $response;
            // return $data['transaction_id'];
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
        $transId = $response['message']['details']['trans_id'] ?? null;
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
            'transaction_status' => 'proccessing',
        ]);

        $this->payscribeBalanceHelper->updateUserBalance($balance);
    } 

}