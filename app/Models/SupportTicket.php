<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

class SupportTicket extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'ticket', 'subject', 'status', 'last_reply'];

    protected $dates = ['last_reply'];

    public function getUsernameAttribute()
    {
        return $this->name;
    }

    protected static function boot()
    {
        parent::boot();
        static::saved(function () {
            Cache::forget('ticketRecord');
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function messages(){
        return $this->hasMany(SupportTicketMessage::class)->latest();
    }

    public function lastReply(){
        return $this->hasOne(SupportTicketMessage::class)->latest();
    }
    public function  getLastMessageAttribute(){
        return Str::limit($this->lastReply->message,40);
    }



    public function getStatusBadge()
    {
        switch ($this->status) {
            case 0: $status = 'warning'; $statusText = trans('Open'); break;
            case 1: $status = 'success'; $statusText = trans('Answered'); break;
            case 2: $status = 'primary'; $statusText = trans('Replied'); break;
            case 3: $status = 'danger';  $statusText = trans('Closed'); break;
            default: return '';
        }

        return '<span class="badge text-bg-'. $status .'"> ' . trans($statusText) . '</span>';
    }


    public static function generateTicketNumber()
    {
        $lastOrder = self::orderBy('created_at', 'desc')->first();

        if ($lastOrder->ticket) {
            $lastOrderNumber = (int)filter_var($lastOrder->ticket, FILTER_SANITIZE_NUMBER_INT);
            $newOrderNumber = $lastOrderNumber + 1;
        } else {
            $newOrderNumber = mt_rand(10000, 999999);
        }

        return $newOrderNumber;
    }
}
