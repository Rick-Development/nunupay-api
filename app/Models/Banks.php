<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banks extends Model
{
    use HasFactory;
    protected $table = "country_banks";
    protected $guarded = ['id'];

    protected $casts = [
        'services_form' => 'object',
    ];

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function service()
    {
        return $this->belongsTo(CountryService::class, 'service_id');
    }
}
