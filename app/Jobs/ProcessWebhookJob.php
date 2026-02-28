<?php

namespace App\Jobs;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $webhookData;
    public $ipAddress;
    private $PAYSCRIBE_SECRET_KEY;

    private  $user;
    private string $senderAcc;
    private string $bankCode;
    private float $orderAmount;
    private string $transId;
    private string $customerAccNum;

    private string $currency;

    private string $customerId;


    /**
     * Create a new job instance.
     */
    public function __construct(array $webhookData, string $ipAddress)
    {
        $this->PAYSCRIBE_SECRET_KEY = config('services.payscribe.secret');
        $this->ipAddress = $ipAddress;
        $this->webhookData = $webhookData;
        $this->customerId = $webhookData['customer']['id'];
        
        // $this->user = auth()->id(); //authenticated user
       //authenticated user
        $this->senderAcc = $webhookData['transaction']['sender_account'];
        $this->bankCode = $webhookData['transaction']['bank_code'];
        $this->orderAmount = $webhookData['transaction']['amount'];
        $this->transId = $webhookData['trans_id'];
        $this->customerAccNum = $webhookData['customer']['number'];
        $this->currency = $webhookData['transaction']['currency'];

    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->user = User::where('payscribe_id', $this->customerId)->first();


        // $this->user = User::where('payscribe_id', $this->customerId)->get(); //authenticated user
        Log::info('user', ['user' => $this->user]);
        // \Log::info('Payscribe Webhook received:', $this->webhookData);


        if(!$this->verifyTransactionHash()) {
            Log::info('Invaid transaction hash');
            return;
        }

        if(!$this->verifyPublicAddress()) {
            Log::info('Invalid public address');
            return;
        }

        if(!$this->checkDuplicate()) {
            $balance = $this->user->account_balance + $this->orderAmount;
            $modelPath = 'App\Models\Transaction';
            $transaction =Transaction::create([
                'transactional_type' => $modelPath,
                'user_id' => $this->user->id ?? 1,
                'amount' => $this->orderAmount,
                'currency' => $this->currency,
                'balance' => $balance,
                'charge' => 0.00,
                'trx_type' => '+',
                'remarks' => 'Credit from Payscribe',
                'trx_id' => $this->transId,
            ]);
            Log::info('transaction', ['trx' => $transaction]);

        }else {
            Log::warning('Duplicate transaction detected');
            return;
        }
        
        
        $this->processPayement();
    }

    private function verifyPublicAddress() {
        Log::info('trans_hash:', ['server' => $_SERVER['REMOTE_ADDR']]);
        // return $_SERVER['REMOTE_ADDR'] = '162.254.34.78';
        return $this->ipAddress === '162.254.34.78';
    }

    private function verifyTransactionHash() {
     

        $combination = $this->PAYSCRIBE_SECRET_KEY . $this->senderAcc . $this->customerAccNum . $this->bankCode . $this->orderAmount . $this->transId;

        $hash = hash( 'SHA512', $combination );
        $hashUpperCase = strtoupper( $hash );
        // Log::info('trans_hash:', ['hash' => $hashUpperCase]);

        return $this->webhookData['transaction_hash'] === $hashUpperCase;
    }

    private function checkDuplicate() {
        $trans_id = $this->webhookData['trans_id'];

        $isDuplicate =  Transaction::where('trx_id', $trans_id)->exists();
        Log::info('isDuplicate:', ['dup' => $isDuplicate]);
        return $isDuplicate;
    }

    private function processPayement() {
        $balance = $this->user->account_balance + $this->orderAmount;

        $this->user->update([
            'account_balance' => $balance
        ]);
        // $this->user->account_balance = $balance;
        // $this->user->save();
        Log::info('user', ['userBalance' => $balance]);
        Log::info('user', ['user' => $this->user]);

    }
}