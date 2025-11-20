<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Helpers\Payscribe\BillsPayments\AirtimeToWalletHelper;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PayscribeAirtimeToWalletController extends Controller
{
    private $modelPath = 'PayscribeAirtimeToWallet';
    public function __construct(private AirtimeToWalletHelper $airtimeToWalletHelper)
    {
        // 
    }

    public function airtimeToWalletLookup(Request $request)
    {
        $response = json_decode($this->airtimeToWalletHelper->airtimeToWalletLookup(), true);
        

        return $response;
    }


    public function airtimeToWallet(Request $request) {
        $data = $request->validate([
            'network' => 'required',
            'phone_number' => 'required',
            'from' => 'required',
            'amount' => 'required | integer | min:1000 | max:20000',
        ]);

        $referenceId = Str::uuid();
        $referenceIdString = (string) $referenceId;
        $data = array_merge($data, ['ref' => $referenceIdString]);

        try{
            $response = json_decode($this->airtimeToWalletHelper->airtimeToWallet($data), true);
            // if($response['status'] === true){
            //     $this->createTransaction($data, $response, $this->modelPath); 
            // }
            return $response;
        }catch(\Exception $e){
            return $response = [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    private function createTransaction($request, $response, $modelPath)
    {
        $balance = auth()->user()->account_balance - $request['amount'];
        $transId = $response['message']['details']['trans_id'];
        Transaction::create([
            'transactional_type' => $modelPath,
            'user_id' => auth()->user()->id,
            'amount' => $request['amount'],
            'currency' => 'NGN',
            // 'balance' => $balance,
            'charge' => $request['amount'],
            'trx_type' => '-',
            'remarks' => $response['description'],
            'trx_id' => $transId,
            'transaction_status' => 'processing',
            'discount' => $response['message']['details']['discount'],
        ]);
        
    }
}