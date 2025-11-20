<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Helpers\Payscribe\BillsPayments\DataBundleHelper;
use App\Http\Helpers\Payscribe\PayscribeBalanceHelper;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Str;


class PayscribeDataBundleController extends Controller
{
    private $modelPath = 'PayscribeDataBundle';

    public function __construct(private DataBundleHelper $dataBundleHelper, private PayscribeBalanceHelper $payscribeBalanceHelper){}
    public function dataLookup(Request $request) {
        $data = $request->validate([
            'network' => 'required | string',
        ]);
        $response = json_decode($this->dataBundleHelper->dataLookup($data['network']), true);
        return $response;
        
    }

    public function dataVending(Request $request) {
        $data = $request->validate([
            "plan" => 'required | string',
            "recipient" => 'required | string',
            "network" => 'required | string',
            "amount" => 'required | string',
        ]);
        $validateBalance = $this->payscribeBalanceHelper->validateBalance($data['amount']);
            
        if(!!$validateBalance){
                return $validateBalance;
        }
        

        $referenceId = Str::uuid();
        $referenceIdString = (string) $referenceId;
        $data = array_merge($data, ['ref' => $referenceIdString]);
        
        $response = json_decode($this->dataBundleHelper->dataVending($data), true);

        if($response['status'] === true){
            $this->createTransaction($data, $response, $this->modelPath); 

        }
        
        return $response;
    }

    private function createTransaction($request, $response, $modelPath) {
        $balance = auth()->user()->account_balance - $response['message']['details']['total_charge'];
        $transId = $response['message']['details']['trans_id'] ?? null;
        Transaction::create([
            'transactional_type' => $modelPath,
            'user_id' => auth()->user()->id,
            'amount' => $response['message']['details']['amount'],
            'currency' => 'NGN',
            'charge' => $response['message']['details']['total_charge'],
            'balance' => $balance,
            'trx_type' => '-',
            'remarks' => $response['description'],
            'trx_id' => $transId,
            'transaction_status' => 'proccessing',
        ]);
        $this->payscribeBalanceHelper->updateUserBalance($balance);
    } 
}