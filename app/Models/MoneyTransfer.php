<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Support\Str;

class MoneyTransfer extends Model
{
    use HasFactory, Prunable;

    protected static $user;

    protected $guarded = ['id'];

    protected $casts = [
        'user_information' => 'object',
    ];

    public static function boot(): void
    {
        parent::boot();
        static::$user = auth()->user();

        static::creating(function (MoneyTransfer $moneyTransfer) {
            $moneyTransfer->uuid = Str::orderedUuid();
            $moneyTransfer->trx_id = self::generateOrderNumber();
            $moneyTransfer->sender_id = static::$user->id;
            $moneyTransfer->user_information = (object) [
                'username' => static::$user->username,
                'email' => static::$user->email,
                'phone' => static::$user->phone,
            ];
        });
    }

    public static function generateOrderNumber()
    {
        $lastOrder = self::orderBy('id', 'desc')->first();
        if ($lastOrder && isset($lastOrder->trx_id)) {
            $lastOrderNumber = (int)filter_var($lastOrder->trx_id, FILTER_SANITIZE_NUMBER_INT);
            $newOrderNumber = $lastOrderNumber + 1;
        } else {
            $newOrderNumber = strRandomNum(10);
        }
        return  $newOrderNumber;
    }


    public function user()
    {
        return $this->belongsTo(User::class, 'sender_id','id');
    }

    public function recipient()
    {
        return $this->belongsTo(Recipient::class, 'recipient_id','id')->withTrashed();
    }

    public function senderCurrency()
    {
        return $this->belongsTo(CountryCurrency::class, 'sender_currency','code');
    }
    public function receiverCurrency()
    {
        return $this->belongsTo(CountryCurrency::class, 'receiver_currency','code');
    }

    public function currency()
    {
        return $this->belongsTo(CountryCurrency::class, 'receiver_currency','code');
    }

    public function service()
    {
        return $this->belongsTo(CountryService::class, 'service_id','id');
    }

    public function getStatusForTransfer($status) {
        switch ($status) {
            case 0:
                return '<span class="text-warning">Draft/Initiate</span>';
            case 1:
                return '<span class="text-success">Completed</span>';
            case 2:
                return '<span class="text-info">Under Review</span>';
            case 3:
                return '<span class="text-danger">Rejected</span>';
            default:
                return '';
        }
    }

    /*for user*/
    public function getStatusBadge()
    {
        switch ($this->status) {
            case 0: $status = 'pending'; $statusText = trans('Draft'); $icon = 'fa-save';break;
            case 1: $status = 'received'; $statusText = trans('Complete'); $icon = 'fa-check-circle'; break;
            case 2: $status = 'info'; $statusText = trans('Under Review'); $icon = 'fa-file-magnifying-glass'; break;
            case 3: $status = 'failed';  $statusText = trans('Rejected'); $icon = 'fa-warning'; break;
            default: return '';
        }
        return '<div class="icon icon-'. $status .'" title="' . trans($statusText) . '"><i class="fa-regular '. $icon .'"></i></div>';
    }

    public function deposit()
    {
        return $this->morphOne(Deposit::class, 'depositable');
    }

    public function prunable(): Builder
    {
        return static::where('created_at', '<=', now()->subDays(2))->where('status', 0);
    }
}
