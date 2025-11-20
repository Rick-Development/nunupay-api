<?php

namespace App\Http\Controllers\api;
// app/Http/Controllers/NinepsbWebhookController.php


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NinepsbWebhookController extends Controller
{
    // Handle incoming webhook
    public function handle(Request $request)
    {
        // Log incoming request data for debugging
        Log::info('Received NinePSB Webhook:', $request->all());

        // // Validate incoming request data (ensure all required fields are present)
        // $validated = $request->validate([
        //     'merchant' => 'required|string',
        //     'amount' => 'required|string',
        //     'transactionref' => 'required|string',
        //     'orderref' => 'required|string',
        //     'code' => 'required|string',
        //     'message' => 'required|string',
        // ]);

        // You can add custom logic here to process the data
        // For example, saving the transaction to the database or triggering other actions.

        // Return a response confirming the webhook was processed successfully
        return response()->json([
            'message' => 'Webhook processed successfully',
            'status' => 'success',
        ]);
    }
}
