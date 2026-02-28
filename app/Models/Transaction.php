<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = ['transactional_id', 'transactional_type', 'user_id','wallet_id', 'amount', 'base_amount', 'currency', 'balance', 'charge', 'trx_type', 'remarks', 'trx_id', 'transaction_status', 'discount', 'session_id', 'event_id'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function transactional()
    {
        return $this->morphTo();
    }

    public function curr()
    {
        return $this->belongsTo(CountryCurrency::class, 'currency', 'code');
    }

    public function getStatusBadge()
    {
        switch ($this->trx_type) {
            case "+": $status = 'success'; $statusText = trans('Amount credited'); break;
            case "-": $status = 'danger';  $statusText = trans('Amount deducted'); break;
            default: return '';
        }
        return '<span class="text-'. $status .'"> ' . trans($statusText) . '</span>';
    }

}