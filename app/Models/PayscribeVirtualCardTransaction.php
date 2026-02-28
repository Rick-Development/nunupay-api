<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayscribeVirtualCardTransaction extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'user_id',
        'card_id',
        'amount',
        'currency',
        'brand',
        'balance',
        'charge',
        'trx_type',
        'remarks',
        'trx_id',
        'ref',
        'event_id',
        'action',
    ];
    // protected $table = 'payscribe_virtual_card_transactions';
}