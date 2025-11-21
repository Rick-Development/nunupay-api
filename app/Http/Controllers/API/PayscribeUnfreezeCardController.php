<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Helpers\Payscribe\CardIssusing\UnfreezeCardHelper;
use Illuminate\Http\Request;
use App\Models\PayscribeVirtualCardDetails;

class PayscribeUnfreezeCardController extends Controller
{
    public function __construct(private UnfreezeCardHelper $unfreezeCardHelper){} 
    public function unfreezeCard(Request $request){
        $data = $request->validate(
            [
                'card_id' => 'required | string'
            ]
        );

        
    // Get last card transaction for this card & user
        $card = PayscribeVirtualCardDetails::where('user_id', auth()->id())
            ->where('card_id', $data['card_id'])
            ->first(); 

        if (!$card) {
            return response()->json([
                'status'  => false,
                'message' => 'Card does not exist!'
            ], 404);
        }

        // Append ref
        $data['ref'] = $card->ref;  // Use arrow, not array
        $response = json_decode($this->unfreezeCardHelper->unfreezeCard($data), true);
        
        return $response;
    }
}