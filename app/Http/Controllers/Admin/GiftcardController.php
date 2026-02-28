<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Helpers\Reloadly\GiftcardHelper;
class GiftcardController extends Controller
{
    //
    
    public function index(Request $request){
        $giftcardHelper = new GiftcardHelper();
         $data = [
    "productId" => 10,
    "quantity" => 1,
    "unitPrice" => 5,
    "customIdentifier" => "obucks10e",
    "productAdditionalRequirements"=> [
        "userId" => "12"
    ],
    "senderName" => "John Doe",
    "recipientEmail" => "anyone@email.com",
    "recipientPhoneDetails" => [
        "countryCode" => "ES",
        "phoneNumber" => "012345678"
    ],
    "preOrder" => false
];
        return response()->json($giftcardHelper->getProducts());
    }
    
    
    
}
