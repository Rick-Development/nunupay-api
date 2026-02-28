<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class UserWallet extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function currency()
    {
        return $this->belongsTo(CountryCurrency::class, 'currency_code', 'code');
    }

    public function countryTC()
    {
        return $this->hasOneThrough(
            Country::class,
            CountryCurrency::class,
            'code',
            'id',
            'currency_code',
            'country_id'
        );
    }

    protected static function booted(): void
    {
        static::creating(function (UserWallet $wallet) {
            $wallet->uuid = Str::orderedUuid();
        });
    }

    public function transactional()
    {
        return $this->morphOne(Transaction::class, 'transactional');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }
}
