<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function handle(Request $request)
    {
        // Here we will log the incoming request to inspect it
        Log::info('Webhook received:', $request->all());

        // Process the webhook notification
        // Validate incoming data or check the authentication
        // If necessary, you can implement basic auth verification
        $this->verifyBasicAuth($request);

        // You can handle the logic of storing the transaction or sending a response here
        // For example, saving the data in the database:

        // $transaction = new Transaction($request->all());
        // $transaction->save();

        // Respond with a success message
        return response()->json([
            'status' => 'success',
            'message' => 'Webhook received successfully',
        ]);
    }

    // Function to verify Basic Auth
    private function verifyBasicAuth(Request $request)
    {
        $credentials = $request->headers->get('Authorization');
        
        if (!$credentials) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Assuming Basic Auth is in format "Basic base64_encode(username:password)"
        $decodedCredentials = base64_decode(substr($credentials, 6)); // Remove "Basic " part
        list($username, $password) = explode(":", $decodedCredentials);

        // Validate the username and password (you can store these securely)
        if ($username !== 'your_username' || $password !== 'your_password') {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }
}
