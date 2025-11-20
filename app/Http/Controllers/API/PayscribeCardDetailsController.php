<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Helpers\Payscribe\CardIssusing\CardDetailsHelper;
use Illuminate\Http\Request;

class PayscribeCardDetailsController extends Controller
{
    public function __construct(private CardDetailsHelper $cardDetailsHelper)
    {
    }
    public function getCardDetails(Request $request){
        $data = $request->validate(
            [
                'ref' => 'required | string'
            ]
        );
        $response = json_decode($this->cardDetailsHelper->getCardDetails($data), true);
        return $response;
        // $cardDetails = new CardDetails();
        // $response = $cardDetails->getCardDetails($data['card_id']);
    }
}