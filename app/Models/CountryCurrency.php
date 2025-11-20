<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class CountryCurrency extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $table = 'country_currency';

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function wallet()
    {
        return $this->hasOne(UserWallet::class, 'currency_code', 'code');
    }

    /*for admin*/
    public function userWallets()
    {
        return $this->hasMany(UserWallet::class, 'currency_code', 'code');
    }

    public function userWalletBalance()
    {
        return $this->userWallets()->sum('balance');
    }


    public function scopeOrderByUserWalletBalance($query)
    {
        return $query->leftJoin('user_wallets', 'country_currency.code', '=', 'user_wallets.currency_code')
            ->select('country_currency.*')
            ->selectRaw('SUM(user_wallets.balance) / country_currency.rate as total_balance')
            ->groupBy('country_currency.code')
            ->orderByDesc('total_balance');
    }

    public function topCurrenciesByWalletBalance($limit = 100)
    {
        return $this->where(function ($query) {
                $query->where('countries.send_to', 1)
                    ->orWhere('countries.receive_from', 1);
            })
            ->groupBy('country_currency.code', 'country_currency.name', 'country_currency.created_at', 'country_currency.updated_at')
            ->orderBy('total_balance', 'desc')
            ->limit($limit)
            ->get();
    }

}
