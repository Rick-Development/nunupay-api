<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Recipient extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = ['id'];

    protected $casts = [
        'bank_info' => 'object',
    ];

    protected static function booted(): void
    {
        static::creating(function (Recipient $recipient) {
            $recipient->uuid = Str::orderedUuid();
            $recipient->user_id = auth()->id();
        });
    }

    public function bank()
    {
        return $this->belongsTo(Banks::class, );
    }

    public function service()
    {
        return $this->belongsTo(CountryService::class,'service_id','id' );
    }

    public function user(){
        return $this->belongsTo(User::class);
    }
    public function recipientUser(){
        return $this->belongsTo(User::class, 'r_user_id', 'id');
    }

    public function currency(){
        return $this->belongsTo(CountryCurrency::class);
    }

    public function country(){
        return $this->belongsTo(Country::class);
    }

    public function moneyTransfers()
    {
        return $this->hasMany(MoneyTransfer::class, 'recipient_id','id');
    }

}
