<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MoneyRequest extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function rcpUser()
    {
        return $this->belongsTo(User::class, 'recipient_id','id');
    }
    public function reqUser()
    {
        return $this->belongsTo(User::class, 'requester_id','id');
    }

    public function curr()
    {
        return $this->belongsTo(CountryCurrency::class, 'currency', 'code');
    }
    public function getStatusIcon()
    {
        switch ($this->status) {
            case 0: $status = 'pending'; $statusText = trans('Pending'); $icon = 'fa-save';break;
            case 1: $status = 'received'; $statusText = trans('Approved'); $icon = 'fa-check-circle'; break;
            case 2: $status = 'failed';  $statusText = trans('Rejected'); $icon = 'fa-warning'; break;
            default: return '';
        }
        return '<div class="icon icon-'. $status .'" title="' . trans($statusText) . '"><i class="fa-regular '. $icon .'"></i></div>';
    }

    public function getStatusBadge()
    {
        switch ($this->status) {
            case 0: $status = 'warning'; $statusText = trans('Pending'); break;
            case 1: $status = 'success'; $statusText = trans('Approved'); break;
            case 2: $status = 'danger';  $statusText = trans('Rejected'); break;
            default: return '';
        }
        return '<span class="badge text-bg-'. $status .'"> ' . trans($statusText) . '</span>';
    }

    public function transactional()
    {
        return $this->morphOne(Transaction::class, 'transactional');
    }
}
