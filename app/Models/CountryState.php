<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CountryState extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $fillable = ['name','country_id'];
    protected $table = 'country_states';

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function cities()
    {
        return $this->hasMany(CountryCity::class, 'state_id');
    }
}
