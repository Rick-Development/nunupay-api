<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory;

    protected $table = 'countries';

    protected $guarded = ['id'];

    public function states()
    {
        return $this->hasMany(CountryState::class, 'country_id');
    }

    public function cities()
    {
        return $this->hasMany(CountryCity::class, 'country_id');
    }

    public function currency()
    {
        return $this->hasOne(CountryCurrency::class, 'country_id');
    }

    public function banks()
    {
        return $this->hasMany(Banks::class, 'country_id');
    }

    public function service()
    {
        return $this->hasMany(CountryService::class, 'country_id');
    }

    public function countryImage()
    {
        $image = $this->image;
        $name = $this->name;
        $starIcon = ($this->currency->default == 1) ? '<span class="avatar-status avatar-sm-status avatar-status-success"><i class="fa-solid fa-star"></i></span>' : '';

        if (!$image) {
            $firstLetter = substr($this->name, 0, 1);
            return '<div class="avatar avatar-sm avatar-soft-primary avatar-circle">
                    <span class="avatar-initials">' . $firstLetter . '</span>
                    ' . $starIcon . '
                </div>';
        } else {
            $url = getFile($this->image_driver, $this->image);
            return '<div class="avatar avatar-sm avatar-circle">
                    <img class="avatar-img" src="' . $url . '" alt="' . $name . '">
                    ' . $starIcon . '
                </div>';
        }
    }

    public function getImage()
    {
        if ($this->currency->code == 'EUR'){
            return asset('assets/global/img/euro.png');
        }else{
            return getFile($this->image_driver,$this->image );
        }
    }

}
