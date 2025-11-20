<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VirtualCardOrder extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    protected $casts = [
        'form_input' => 'object',
        'card_info' => 'object',
        'test' => 'object',
    ];
    protected $appends = ['is_active'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeCards($query)
    {
        return $query->whereIn('status', [1, 5, 6, 7, 8, 9]);
    }

    public function getIsActiveAttribute()
    {
        if ($this->expiry_date) {
            if ($this->expiry_date > Carbon::now()) {
                if ($this->status == 1 || $this->status == 5 || $this->status == 6 || $this->status == 8) {
                    return 'Active';
                }
            }
        }
        return 'Inactive';
    }

    public function cardMethod()
    {
        return $this->belongsTo(VirtualCardMethod::class, 'virtual_card_method_id','id');
    }

    public function transactional()
    {
        return $this->morphOne(Transaction::class, 'transactional');
    }

    public function chargeCurrency()
    {
        return $this->belongsTo(CountryCurrency::class, 'charge_currency', 'code');
    }

    public function curr()
    {
        return $this->belongsTo(CountryCurrency::class, 'currency', 'code');
    }

    const STATUSES = [
        0 => ['class' => 'warning', 'text' => 'Pending'],
        1 => ['class' => 'success', 'text' => 'Approved'],
        2 => ['class' => 'danger', 'text' => 'Rejected'],
        3 => ['class' => 'info', 'text' => 'Resubmit'],
        4 => ['class' => 'primary', 'text' => 'Generate'],
        5 => ['class' => 'dark', 'text' => 'Block Request'],
        6 => ['class' => 'secondary', 'text' => 'Fund Rejected'],
        7 => ['class' => 'info', 'text' => 'Blocked'],
        8 => ['class' => 'primary', 'text' => 'Add Fund Request'],
        9 => ['class' => 'danger', 'text' => 'Inactive']
    ];

    public function getStatusInfo()
    {
        $statusId = $this->status;
        $statusInfo = self::STATUSES[$statusId] ?? null;

        if ($statusInfo) {
            return '<span class="badge bg-soft-' . $statusInfo['class'] . ' text-' . $statusInfo['class'] . '">' .
                '<span class="legend-indicator bg-' . $statusInfo['class'] . '"></span>' . $statusInfo['text'] .
                '</span>';
        }

        return '';
    }

}
