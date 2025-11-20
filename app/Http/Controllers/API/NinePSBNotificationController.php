<?php

namespace App\Http\Controllers\api;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\JsonResponse;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Traits\ApiResponse;
use App\Traits\ManageWallet;
use App\Traits\Notify;
use App\Traits\Upload;
use App\Traits\VirtualCardTrait;

class NinePSBNotificationController extends Controller
{
    //
    
    use ApiResponse, Notify, Upload, ManageWallet, VirtualCardTrait;
    
    public function sendNotification(Request $request){
        
        
        $user = User::where('username', $request['username'])->first();
// $responseArray = json_decode($response, TRUE);

\Log::info('API Request', ['Request' => $request->all(), 'user'=>$user]);
  $params = [
                'amount' => $request['amount'],
                'currency' =>  'NGN',
                'transaction' =>$request['transaction']
            ];
            
        $action = [
            "link" => "",
            "icon" => "fa fa-money-bill-alt text-white"
        ];
        $this->sendMailSms($user, 'MONEY_TRANSFER_USER', $params);
        $this->userPushNotification($user, 'MONEY_TRANSFER_USER', $params, $action);
        $this->userFirebasePushNotification($user, 'MONEY_TRANSFER_USER', $params);


        
         return response()->json([
            'message' => 'Webhook processed successfully',
            'status' => 'success',
        ]);
        
    }
    
    
    
    public function sendPaymentNotification(Request $request){
        
        
        $user = User::where('username', $request['username'])->first();
// $responseArray = json_decode($response, TRUE);

\Log::info('API Request', ['Request' => $request->all(), 'user'=>$user]);
  $params = [
                'amount' => $request['amount'],
                'operator' =>  $request['operator'],
                'id' =>$request['id'],
                'transactionid' => $request['transactionid'],
                'action' =>  $request['action']
            ];
            
        $action = [
            "link" => "",
            "icon" => "fa fa-money-bill-alt text-white"
        ];
        $this->sendMailSms($user, 'PAYMENT_SUCCESSFUL', $params);
        $this->userPushNotification($user, 'PAYMENT_SUCCESSFUL', $params, $action);
        $this->userFirebasePushNotification($user, 'PAYMENT_SUCCESSFUL', $params);


        
         return response()->json([
            'message' => 'Webhook processed successfully',
            'status' => 'success',
        ]);
        
    }
}
