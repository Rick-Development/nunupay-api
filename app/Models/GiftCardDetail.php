<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GiftCardDetail extends Model
{
    use HasFactory;

    public $fillable = ['product_name', 'country_code', 'currency_code', 'giftcard_brand', 'images', 'seller_email', 'trade', 'message', 'giftcardCode'];
    protected $casts = [
        'images' => 'array' // Automatically converts JSON to/from array
    ];

}