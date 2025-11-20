<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayscribeVirtualCardDetails extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'card_id',
        'card_name',
        'card_number',
        'card_type',
        'currency',
        'brand',
        'masked',
        'expiry_date',
        'ccv',
        'billing_address',
        'trans_id',
        'ref',
        'balance',
        'prev_balance',
    ];
    
    protected $casts = [
        'billing_address' => 'array',
    ];

}


// $cardIssuingRate = BasicControl::first()->card_issuing_rate;

// /// Amount customer whats to deposit
// $cardDepositRate = BasicControl::first()->card_deposit_rate;
// $payscribeDepostRate = 1500;

// $adminDepositAmount = $data['amount'] * $cardDepositRate; // USD to NGN for card deposit rate by admin
// $paysceibeDepositAmount = $data['amount'] * $payscribeDepostRate; // USD to NGN for card deposit rate by admin


// /// totalcharge for validating balance
// $totalCharged = $adminDepositAmount + $cardIssuingRate;

// $validateBalance = $this->payscribeBalanceHelper->validateBalance($totalCharged);