<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Helpers\Payscribe\BillsPayments\IntAirtimeDataHelper;
use App\Http\Helpers\Payscribe\PayscribeBalanceHelper;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PayscribelIntAirtimeDataController extends Controller
{
    private $modelPath = 'PayscribelIntAirtimeData';
    public function __construct(private IntAirtimeDataHelper $intAirtimeDataHelper, private PayscribeBalanceHelper $payscribeBalanceHelper){}

    public function IntBillsCountries() {
        try {
            $response = json_decode($this->intAirtimeDataHelper->getIntBillCountries(), true);
            return $response;
        } 
        catch(\Exception $e){
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function IntBillsProviders(Request $request) {
        $data = $request->validate([
            'iso' => 'required | string',
        ]);
        try {
            $response = json_decode($this->intAirtimeDataHelper->getIntBillProviders($data), true);
            return $response;
        } 
        catch(\Exception $e){
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function IntBillsProducts(Request $request) {
        $data = $request->validate([
            'iso' => 'required | string',
            'code' => 'required | string',
        ]);
        try {
            $response = json_decode($this->intAirtimeDataHelper->getIntBillProducts($data), true);
            return $response;
        } 
        catch(\Exception $e){
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function EstimateRates(Request $request){
        $data = $request->validate([
            'iso' => 'required | string',
            'sku' => 'required | string',
            'amount' => 'required | string',
        ]);
        try {
            $response = json_decode($this->intAirtimeDataHelper->getIntEstimateRates($data), true);
            return $response;
        } 
        catch(\Exception $e){
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function VendIntBills(Request $request) {
        $data = $request->validate([
            'iso' => 'required | string',
            'provider_code' => 'required | string',
            'sku' => 'required | string',
            'amount' => 'required | string',
            'account' => 'required | string',
            'debit_currency' => 'sometimes | string',
        ]);
        $referenceId = Str::uuid();
        $referenceIdString = (string) $referenceId;
        $data = array_merge($data, ['ref' => $referenceIdString]);
        try {
            $validateBalance = $this->payscribeBalanceHelper->validateBalance($data['amount']);
            
            if(!!$validateBalance){
                return $validateBalance;
            }

            $response = json_decode($this->intAirtimeDataHelper->vendIntBills($data), true);
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
            $transId = $response['message']['details']['trans_id'];
            $balance = auth()->user()->account_balance - $request['amount'];
            Transaction::create([
                'transactional_type' => $modelPath,
                'user_id' => auth()->user()->id,
                'amount' => $response['message']['details']['total_charged'],
                'currency' => 'USD',
                'balance' => $balance,
                'trx_type' => '-',
                'remarks' => $response['description'],
                'trx_id' => $transId,
                'transaction_status' => 'processing',
                'discount' => $response['message']['details']['discount'],
            ]);
            $this->payscribeBalanceHelper->updateUserBalance($balance);
        } 

}