<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Helpers\Payscribe\BillsPayments\CableTVSubscriptionHelper;
use App\Http\Helpers\Payscribe\PayscribeBalanceHelper;
use App\Http\Helpers\Payscribe\PayscribePayoutHelper;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Str;


class PayscribeCableTvSubsController extends Controller
{
    private $modelPath = 'PayscribeCableTvSubs';

    public function __construct(private CableTVSubscriptionHelper $cableTVSubHelper, private PayscribeBalanceHelper $payscribeBalanceHelper ) {}


    public function fetchBouquents(Request $request) {
        $data = $request->validate([
            'service' => ['required' , 'string', function($attribute, $value, $fail) {
                if (!in_array($value, ['dstv', 'gotv', 'startimes'])) {
                    $fail($value . ' value is invalid. Please use either dstv, gotv or startimes');
                }
            }],
        ]);
        try {
            $response = json_decode($this->cableTVSubHelper->fetchBouquets($data['service']), true);
            return $response;
        } 
        catch(\Exception $e){
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function validateSmartCardNumber(Request $request) {
        $data = $request->validate([
            'service' => 'required | string',
            'account' => 'required | string',
            'month' => 'sometimes | integer',
            'plan_id' => 'required | string',
        ]);
        try{
            return json_decode($this->cableTVSubHelper->validateSmartCardNumber($data), true);
        }
        catch(\Exception $e){
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function payCableTv() {
        $data = request()->validate([
            'plan_id' => 'required | string',
            'customer_name' => 'required | string',
            'account' => 'required | string',
            'service' => 'required | string',
            'phone' => 'required | string',
            'email' => 'required | string',
            'month' => 'required | integer',
            'amount' => 'required | integer',
        ]);
        // Generate a UUID
        $referenceId = Str::uuid();
        // Convert to string if needed
         $referenceIdString = (string) $referenceId;
        $data = array_merge($data, ['ref' => $referenceIdString]);


        try {
            $validateBalance = $this->payscribeBalanceHelper->validateBalance($data['amount']);
            
            if(!!$validateBalance){
                return $validateBalance;
            }

            $response = json_decode($this->cableTVSubHelper->payCableTV($data), true);
            
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
    
    public function topUpCableTv(Request $request)   {
        $data = $request->validate([
            'amount' => 'required | string',
            'customer_name' => 'required | string',
            'account' => 'required | string',
            'service' => 'required | string',
            'phone' => 'required | string',
            'email' => 'required | string',
            'month' => 'required | string',
        ]);
            // Generate a UUID
        $referenceId = Str::uuid();
        // Convert to string if needed
        $referenceIdString = (string) $referenceId;
        $data = array_merge($data, ['ref' => $referenceIdString]);
                
        try {
            $validateBalance = $this->payscribeBalanceHelper->validateBalance($data['amount']);
            if(!!$validateBalance){
                return $validateBalance;
            }
            $response = json_decode($this->cableTVSubHelper->topupCableTV($data), true);
            
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
        $transId = $response['message']['details']['trans_id'] ?? null;
        Transaction::create([
            'transactional_type' => $modelPath,
            'user_id' => auth()->user()->id,
            'amount' => $response['message']['details']['amount'],
            'currency' => 'NGN',
            'balance' => $balance,
            'charge' => $response['message']['details']['total_charged'],
            'trx_type' => '-',
            'remarks' => $response['description'],
            'trx_id' => $transId,
            'transaction_status' => 'proccessing',

        ]);

        $this->payscribeBalanceHelper->updateUserBalance($balance);
    } 
}