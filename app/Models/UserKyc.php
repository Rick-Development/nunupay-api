<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class UserKyc extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'kyc_id', 'kyc_type', 'kyc_info', 'status', 'reason'];

    protected $casts = [
        'kyc_info' => 'object',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    protected static function boot()
    {
        parent::boot();
        static::saved(function () {
            Cache::forget('userKYCRecord');
        });
    }


    public function kycPosition()
    {
        $kycStatus = $this->kyc_status;
        if ($kycStatus === 'verified') {
            return 'verified';
        } elseif ($kycStatus === 'pending') {
            return 'pending';
        } elseif ($kycStatus === 'rejected') {
            return 'rejected';
        }
    }

    public function kyc()
    {
        return $this->belongsTo(KYC::class, 'kyc_id','id');
    }

}
