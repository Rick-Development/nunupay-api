<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GiftcardWebhookController extends Controller
{
    public function handle(Request $request)
    {
        // Log the webhook for debugging
        Log::info('Giftcard Webhook received:', $request->all());

        // Verify the webhook signature
        if (!$this->verifySignature($request)) {
            return response()->json(['error' => 'Invalid signature'], 403);
        }

        // Parse the event from the request
        $event = $request->input('type'); // The 'type' field specifies the event type

        switch ($event) {
            case 'airtime_transaction.status':
                Log::info('Processing airtime transaction!');
                // Add your handling logic here
                break;

            case 'giftcard_transaction.status':
                Log::info('Processing giftcard transaction!');
                // Add your handling logic here
                break;

            default:
                Log::warning("Unhandled event type: {$event}");
                break;
        }

        // Return a 200 response to acknowledge receipt
        return response()->json(['status' => 'success'], 200);
    }

    private function verifySignature(Request $request)
    {
        $headerSignature = $request->header('X-Reloadly-Signature'); // Replace with actual header name
        $headerTimestamp = $request->header('X-Reloadly-Request-Timestamp'); // Replace with actual timestamp header name
        $secret = config('services.reloadly.webhook_secret'); // Store in .env

        $payload = $request->getContent();
        $dataToSign = $payload . ':' . $headerTimestamp;

        // Generate the HMAC signature
        $calculatedSignature = hash_hmac('sha256', $dataToSign, $secret);

        // Compare the calculated signature with the header signature
        return hash_equals($calculatedSignature, $headerSignature);
    }
}
