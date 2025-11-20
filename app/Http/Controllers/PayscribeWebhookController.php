<?php
namespace App\Http\Controllers;

use App\Jobs\ProcessWebhookJob;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PayscribeWebhookController extends Controller
{
    private $webhookData;

    private $eventType;
    public $ipAddress;
    private $PAYSCRIBE_SECRET_KEY;


    public function __construct()
    {
        $this->PAYSCRIBE_SECRET_KEY = config('services.payscribe.secret');

         
    }

    public function handle(Request $request)
    {
        // Log the webhook for debugging
        Log::info('Payscribe Webhook received:', $request->all());
        /// TODO : check webhook server ip address
        $this->webhookData = $request->all();
        $this->eventType = $this->webhookData['event_type'];
        $this->ipAddress = $request->ip();

        $this->event($this->eventType);
        return response()->json(['status' => $this->eventType], 200);

        
        
  
    }

    private function event($eventType) {
        switch ($eventType) {
            case 'bills.created':
                Log::info('Processing airtime transaction!');
                $this->billCreated();
                break;

            case 'bills.status':
                Log::info('Processing airtime transaction!');
                $this->billStatus();
                break;

            case 'payouts.created':
                Log::info('Processing payout transaction!');
                $this->payout();
                break;

            case 'customers.created':
                Log::info('Processing airtime transaction!');
                break;
            case 'customers.updated':
                Log::info('Processing airtime transaction!');
                break;

            case 'accounts.payment.status':
                Log::info('Processing airtime transaction!');
                $this->accountPaymentStatus();
                break;

            default:
                Log::warning("Unhandled event type: {$eventType}");
                break;
        }
    }

    private function accountPaymentStatus() {
                $customerId = $this->webhookData['customer']['id'];
                $user = User::where('payscribe_id', $customerId)->first();

        
                // if(!$this->verifyPublicAddress()) {
                //     Log::info('Invalid public address');
                //     return;
                // }
        
                if(!$this->checkDuplicate()) {
                    $amount = $this->webhookData['transaction']['amount'];
                    $chargeFee = $this->webhookData['fee'];
                    $totalAmount = $amount - $chargeFee;
                    $balance = $user->account_balance + $totalAmount;
                    $modelPath = 'App\Models\Transaction';
                    $transId = $this->webhookData['trans_id'];
                    $sessionId = $this->webhookData['transaction']['session_id'];
                    $currency = $this->webhookData['transaction']['currency'];
                    $remarks = $this->webhookData['transaction']['narration'];
                    $created_at = $this->webhookData['created_at'];
                    $sessionId = $this->webhookData['transaction']['session_id'];
                    $transaction =Transaction::create([
                        'event_id'=> $this->webhookData['event_id'],
                        'transactional_type' => $modelPath,
                        'user_id' => $user->id,
                        'amount' => $amount,
                        'currency' => $currency,
                        'balance' => $balance,
                        'charge' => $chargeFee,
                        'trx_type' => '+',
                        'remarks' => $remarks,
                        'trx_id' => $transId,
                        'created_at' => $created_at,
                        'session_id' => $sessionId,
                        'transaction_status' => 'success',
                    ]);
                    Log::info('transaction', ['trx' => $transaction]);

                    $this->processPayement($balance, $user);
        
                }else {
                    Log::warning('Duplicate transaction detected');
                    return;
                }
    }


    private function payout() {
        // if(!$this->verifyPublicAddress()) {
        //     Log::info('Invalid public address');
        //     return;
        // }

        $transId = $this->webhookData['trans_id'];
        $transaction = Transaction::where(['trx_id' => $transId])->where('transaction_status', '!=', 'success')->first();
        
        if(!!$transaction) {
            $userId = $transaction->user_id;
            $user = User::where('id', $userId)->first();
            $amount = $this->webhookData['amount'];
            $balance = $user->account_balance - $amount;
            $transStatus = $this->webhookData['status'];
            
            Log::info('transaction', ['user' => $user]);
            if($transStatus === 'success') {
                /// ===Update the transaction status
                $transaction->update([
                    'balance' => $balance,
                    'transaction_status' => $transStatus,
                    'updated_at' => $this->webhookData['created_at'],
                    'session_id' => $this->webhookData['session_id'],
                ]);

                /// ===Update the user account balance
                $this->processPayement($balance, $user);
            }
        }else {
            Log::warning('Duplicate transaction detected');
            return;
        }
    }


    private function billCreated() {
        // if(!$this->verifyPublicAddress()) {
        //     Log::info('Invalid public address');
        //     return;
        // }
            $transId = $this->webhookData['trans_id'];
            $transStatus = $this->webhookData['transaction_status'];
            Transaction::where(['trx_id' => $transId])->update([
                'event_id' => $this->webhookData['event_id'],
                'transaction_status' => $transStatus,
                'created_at' => $this->webhookData['created_at'],
            ]);
    }
    
    private function billStatus() {
        // if(!$this->verifyPublicAddress()) {
        //     Log::info('Invalid public address');
        //     return;
        // }


        $transId = $this->webhookData['trans_id'];
        $transaction = Transaction::where(['trx_id' => $transId])->where('transaction_status', '!=', 'success')->first();

        if(!!$transaction) {
            $userId = $transaction->user_id;
            $user = User::where('id', $userId)->first();
            $amount = $this->webhookData['amount'];
            // $balance = $user->account_balance - $amount;
            $transStatus = $this->webhookData['transaction_status'];
            $transaction->update([
                'transaction_status' => $transStatus,
                'updated_at' => $this->webhookData['updated_at'],
                'remarks' => $this->webhookData['remark'],
                // 'balance' => $balance,
    
            ]);
            Log::info('transaction', ['user' => $user]);
            // $this->processPayement($balance, $user);
        }else {
            Log::warning('Duplicate Transaction');
            return;
        }
    }

    // ========== Verify the webhook ================= //
    private function verifyPublicAddress() {
        // Log::info('trans_hash:', ['server' => $_SERVER['REMOTE_ADDR']]);
        // return $_SERVER['REMOTE_ADDR'] = '162.254.34.78';
        return $this->ipAddress === '162.254.34.78';
    }

    private function verifyTransactionHash() {
        $senderAcc = $this->webhookData['transaction']['sender_account'];
        $customerAccNum = $this->webhookData['customer']['number'];
        $bankCode = $this->webhookData['transaction']['bank_code'];
        $orderAmount = $this->webhookData['transaction']['amount'];
        $transId = $this->webhookData['trans_id'];

        $combination = $this->PAYSCRIBE_SECRET_KEY . $senderAcc . $customerAccNum . $bankCode . $orderAmount . $transId;

        $hash = hash( 'SHA512', $combination );
        $hashUpperCase = strtoupper( $hash );
        // Log::info('trans_hash:', ['hash' => $hashUpperCase]);

        return $this->webhookData['transaction_hash'] === $hashUpperCase;
    }

    private function checkDuplicate() {
        $transId = $this->webhookData['trans_id'];

        $isDuplicate =  Transaction::where('trx_id', $transId)->exists();
        Log::info('isDuplicate:', ['dup' => $isDuplicate]);
        return $isDuplicate;
    }

    private function processPayement($balance, $user) {

        $user->update([
            'account_balance' => $balance
        ]);
        // $this->user->account_balance = $balance;
        // $this->user->save();
        Log::info('user', ['userBalance' => $balance]);
        Log::info('user', ['user' => $user]);

    }



    // public function handle(Request $request)
    // {
    //     // Log the webhook for debugging
    //     \Log::info('Payscribe Webhook received:', $request->all());

    //     // Verify signature if required
    //     $this->verifySignature($request);

    //     // Handle specific events
    //     $event = $request->input('event'); // Example: 'bill.payment.success'
    //     switch ($event) {
    //         case 'bill.payment.success':
    //             // Handle successful bill payment
    //             break;

    //         case 'bill.payment.failed':
    //             // Handle failed bill payment
    //             break;

    //         default:
    //             \Log::warning("Unhandled Payscribe event: {$event}");
    //     }

    //     return response()->json(['status' => 'success'], 200);
    // }



    // private function verifySignature(Request $request)
    // {
    //     $headerSignature = $request->header('X-Payscribe-Signature'); // Replace with actual header
    //     $secret = config('services.payscribe.secret'); // Store in .env

    //     $payload = $request->getContent();
    //     $calculatedSignature = hash_hmac('sha256', $payload, $secret);

    //     \Log::info([
    //         'Payscribe Webhook with:', $request->all(),
    //         $headerSignature , $calculatedSignature
    //         ]);
    //     if ($headerSignature !== $calculatedSignature) {
    //         abort(403, 'Invalid signature.');
    //     }
    // }


    // private function verifySignature(Request $request)
    // {
    //     $headerSignature = $request->header('Remote-address'); // Replace with actual header
    //     $secret = config('services.payscribe.secret'); // Store in .env

    //     $payload = $request->getContent();
    //     $_SERVER['REMOTE_ADDR'] = $headerSignature;
    //     return $headerSignature;
    //     // return $calculatedSignature = hash_hmac('sha256', $payload, $secret);

    //     // \Log::info([
    //     //     'Payscribe Webhook with:', $request->all(),
    //     //     $headerSignature , $calculatedSignature
    //     //     ]);
    //     // if ($headerSignature !== $calculatedSignature) {
    //     //     abort(403, 'Invalid signature.');
    //     // }
    // }
}