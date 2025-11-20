<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Helpers\Payscribe\Collections\NGNVirtualAccountsHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PayscribeNGNVirtualAccountController extends Controller
{
    function __construct(private NGNVirtualAccountsHelper $nGNVirtualAccountsHelper)
    {
    }
    public function virtualAccountDetails(){
        $user = auth()->user();
        $account_num = $user->account_number;   
        $response = $this->nGNVirtualAccountsHelper->getVirtualAccountDetails($account_num);
        // return response($account_num, 200);
        return response()->json($response, $response['status_code']);

    }

    public function deactivateVirtualAccount(){
        $user = auth()->user();
        $data =[
            "account" => $user->account_number
        ];
          
        $response = $this->nGNVirtualAccountsHelper->deactivateVirtualAccount($data);
        return response()->json($response, $response['status_code']);
        // return response()->json($data);
    }

    public function activateVirtualAccount(){
        $user = auth()->user();
        $data =[
            "account" => $user->account_number
        ];
          
        $response = $this->nGNVirtualAccountsHelper->deactivateVirtualAccount($data);
        return response()->json($response, $response['status_code']);
        // return response()->json($data);
    }

    public function dynamicTemporaryVirtualAccount(Request $request){
        $user = auth()->user();
        
        $referenceId = Str::uuid();
        $referenceIdString = (string) $referenceId;
        $reqData = $request->validate([
            'amount' => 'required | integer',
        ]);

        $data = [
            "account_type" => "dynamic",
            "ref" => "$referenceIdString",
            "currency" => "NGN",
            "order" => [
                "amount" => $reqData['amount'],
                "amount_type" => "EXACT",
                "description" => "A new payment for {$user['firstname']} {$user['lastname']} Order with {$user['payscribe_id']}",
                "expiry" => [
                    "duration" => 1,
                    "duration_type" => "hours"
                ]
            ],
            "customer" => [
                "name" => $user['firstname'] . '' . $user['lastname'],
                "email" => $user['email'],
                "phone" => "+234". $user['phone']
            ]
        ];
        // return response()->json($data);

        $response = $this->nGNVirtualAccountsHelper->createDynamicTemporaryVirtualAccount($data);

        return response()->json($response, $response['status_code']);
       
    } 

    // public varifyPayment(Request $request){
    //     $dataReq = $request->validate([
    //         'amount' => 'required | integer',
    //         'account_number' => 'required | string',
    //     ]);
    //     $user = auth()->user();
    // }

    
}