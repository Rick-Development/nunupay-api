<?php

namespace App\Http\Helpers\Payscribe;

use App\Http\Helpers\ConnectionHelper;
use App\Models\Transaction;
use App\Models\User;

class PayscribeBalanceHelper extends ConnectionHelper{

    public function __construct(){
        parent::__construct();
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
        ]);
    } 

    public function updateUserBalance($balance){
        User::where('id', auth()->id())->update(['account_balance' => $balance]);

    }

    public function validateBalance($amount){
        $user = auth()->user();
        $amount = (int) $amount;
        if($user->account_balance < $amount){
            return response()->json([
                'status' => 'error',
                'message' => 'Insufficient balance'
            ]);
        }
    }
    
}