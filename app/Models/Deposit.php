<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class Deposit extends Model
{
    use HasFactory, Prunable;

    protected $fillable = ['depositable_id', 'depositable_type', 'user_id', 'payment_method_id', 'payment_method_currency',
                            'amount', 'wallet_id', 'percentage_charge', 'fixed_charge', 'payable_amount', 'base_currency_charge',
                            'payable_amount_in_base_currency', 'btc_amount', 'btc_wallet', 'payment_id', 'information',
                            'trx_id', 'status', 'note'];


    protected $casts = [
        'information' => 'object'
    ];

    public function transactional()
    {
        return $this->morphOne(Transaction::class, 'transactional');
    }

    public function depositable()
    {
        return $this->morphTo();
    }

    protected static function boot()
    {
        parent::boot();
        static::saved(function () {
            Cache::forget('paymentRecord');
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function getGatewayNameAttribute()
    {
        return $this->payment_method_id == 0 ? 'Wallet' : $this->gateway->name ?? null;
    }

    public function gateway()
    {
        return $this->belongsTo(Gateway::class, 'payment_method_id', 'id');
    }

    public function picture()
    {
        $image = optional($this->gateway)->image;
        if (!$image) {
            $firstLetter = substr(optional($this->gateway)->name, 0, 1);
            return '<div class="avatar avatar-sm avatar-soft-primary avatar-circle">
                        <span class="avatar-initials">' . $firstLetter . '</span>
                     </div>';

        } else {
            $url = getFile(optional($this->gateway)->driver, optional($this->gateway)->image);
            return '<div class="avatar avatar-sm avatar-circle">
                        <img class="avatar-img" src="' . $url . '" alt="Image Description">
                     </div>';

        }
    }

    public function wallet()
    {
        return $this->belongsTo(UserWallet::class, 'wallet_id', 'id');
    }

    /*for admin*/
    public function getStatusClass()
    {
        return [
            '0' => 'text-dark',
            '1' => 'text-success',
            '2' => 'text-dark',
            '3' => 'text-danger',
        ][$this->status] ?? 'text-danger';
    }

    /*for user*/
    public function getStatusIcon()
    {
        switch ($this->status) {
            case 0: $status = 'pending'; $statusText = trans('Pending'); $icon = 'fa-save';break;
            case 1: $status = 'received'; $statusText = trans('Success'); $icon = 'fa-check-circle'; break;
            case 2: $status = 'info'; $statusText = trans('Requested'); $icon = 'fa-file-magnifying-glass'; break;
            case 3: $status = 'failed';  $statusText = trans('Rejected'); $icon = 'fa-warning'; break;
            default: return '';
        }
        return '<div class="icon icon-'. $status .'" title="' . trans($statusText) . '"><i class="fa-regular '. $icon .'"></i></div>';
    }
    public function getStatusBadge()
    {
        switch ($this->status) {
            case 0: $status = 'warning'; $statusText = trans('Pending'); break;
            case 1: $status = 'success'; $statusText = trans('Success'); break;
            case 2: $status = 'info'; $statusText = trans('Requested'); break;
            case 3: $status = 'danger';  $statusText = trans('Rejected'); break;
            default: return '';
        }
        return '<span class="badge text-bg-'. $status .'"> ' . trans($statusText) . '</span>';
    }

    public function curr()
    {
        return $this->belongsTo(CountryCurrency::class, 'payment_method_currency', 'code');
    }


    /*admin reject transfer*/
    public function scopeMoneyTransfers($query, $depositableId = null)
    {
        $query = $query->whereHasMorph('depositable', MoneyTransfer::class);

        if (!is_null($depositableId)) {
            $query = $query->where('depositable_id', $depositableId);
        }
        return $query;
    }

    public function prunable(): Builder
    {
        return static::where('created_at', '<=', now()->subDays(2))->where('status', 0);
    }

}
