<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Helpers\Payscribe\CardIssusing\CardDetailsHelper;
use Illuminate\Http\Request;
use App\Models\PayscribeVirtualCardDetails;
use App\Traits\ApiResponse; // Import the trait

class PayscribeCardDetailsController extends Controller
{
    use ApiResponse; // Use the trait
    public function __construct(private CardDetailsHelper $cardDetailsHelper)
    {
    }
    public function getCardDetails(Request $request){
        $data = $request->validate(
            [
                'card_id' => 'required | string'
            ]
        );
        $response = json_decode($this->cardDetailsHelper->getCardDetails($data), true);
        return $response;
    }

    

    public function GetvirtualCards(Request $request) {
        $cards = PayscribeVirtualCardDetails::where('user_id',auth()->user()->id)->get();
        return response()->json($this->withSuccess($cards),200); 
    }
}