<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CountryCity extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $table = 'country_cities';

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function state()
    {
        return $this->belongsTo(CountryState::class, 'state_id');
    }
}
