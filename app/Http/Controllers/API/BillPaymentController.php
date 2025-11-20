<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\BillPayment;

class BillPaymentController extends Controller
{
    protected $baseUrl = 'http://102.216.128.75:9090/vas/api/v1'; // Replace with your external API base URL

    protected function authenticate()
    {
        // Define your API credentials
        $username = env('NINE_PAYMENT_API_KEY'); // Store your API key in the .env file
        $password = env('NINE_PAYMENT_SECRET_KEY'); // Store your secret key in the .env file

        // Prepare the request body
        $body = [
            'username' => $username,
            'password' => $password,
        ];

        // Send the POST request to the authentication endpoint
        $response = Http::post("http://102.216.128.75:9090/identity/api/v1/authenticate", $body);

        // Check if the response is successful
        if ($response->successful()) {
            // Parse the response JSON
            $data = $response->json();
            // return response()->json(['response'=>$data]);
            // Check if the status is success and return the token
            if ($data['status'] === 'success') {
                return $data['data']['accessToken'];
            } else {
                // Handle unsuccessful authentication
                return 'Authentication failed: ' . $data['message'];
            }
        } else {
            // Handle HTTP errors
            return  $response->json();
        }
    }

    public function getCategories(Request $request)
    {
        try {
            // Authenticate once and retrieve the token
            $token = $this->authenticate();
            // return $token;
    
            // Initialize cURL session
            $ch = curl_init();
    
            // Set cURL options
            curl_setopt($ch, CURLOPT_URL, "{$this->baseUrl}/billspayment/categories");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Authorization: Bearer $token",
                "Content-Type: application/json"
            ]);
    
            // Execute cURL request
            $response = curl_exec($ch);
    
            // Check for errors in the cURL request
            if (curl_errno($ch)) {
                throw new \Exception('cURL Error: ' . curl_error($ch));
            }
    
            // Close the cURL session
            curl_close($ch);
    
            // Decode the JSON response and return it
            return response()->json(json_decode($response, true));
        } catch (\Exception $e) {
            // Handle exceptions and return a meaningful error message
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
    

    public function getBillers(Request $request, $categoryId)
    {
        $token = $this->authenticate();
        // Make an external API request to get billers based on category
        $response = Http::withToken($token)->get("{$this->baseUrl}/billspayment/billers/{$categoryId}");

        return $response->json();
    }

    public function getBillerInputFields(Request $request, $billerId)
    {
        // Make an external API request to get input fields for a biller
        $token = $this->authenticate();
        // Make an external API request to get billers based on category
        $response = Http::withToken($token)->get("{$this->baseUrl}/billspayment/fields/{$billerId}");

        return $response->json();
    }

    public function validateBillerInput(Request $request)
    {
        $request->validate([
            'customerId' => 'required|string',
            'billerId' => 'required|string',
            'itemId' => 'required|string',
        ]);

        // Make an external API request to validate biller input
        $response = Http::post("{$this->baseUrl}/billspayment/validate", [
            'customerId' => $request->customerId,
            'billerId' => $request->billerId,
            'itemId' => $request->itemId,
        ]);

        return $response->json();
    }

    public function initiateBillPayment(Request $request)
    {
        $request->validate([
            'customerId' => 'required|string',
            'billerId' => 'required|string',
            'customerPhone' => 'required|string',
            'customerName' => 'required|string',
            'otherField' => 'required|string',
            'debitAccount' => 'required|string',
            'amount' => 'required|numeric',
            'transactionReference' => 'required|string',
        ]);
    
        // Make an external API request to initiate bill payment
        $response = Http::post("{$this->baseUrl}/billspayment/pay", [
            'customerId' => $request->customerId,
            'billerId' => $request->billerId,
            'customerPhone' => $request->customerPhone,
            'customerName' => $request->customerName,
            'otherField' => $request->otherField,
            'debitAccount' => $request->debitAccount,
            'amount' => $request->amount,
            'transactionReference' => $request->transactionReference,
        ]);
    
        // Save bill payment record to the database
        $billPayment = new BillPayment();
        $billPayment->customer_id = $request->customerId;
        $billPayment->biller_id = $request->billerId;
        $billPayment->customer_phone = $request->customerPhone;
        $billPayment->customer_name = $request->customerName;
        $billPayment->other_field = $request->otherField;
        $billPayment->debit_account = $request->debitAccount;
        $billPayment->amount = $request->amount;
        $billPayment->transaction_reference = $request->transactionReference;
        $billPayment->save();
    
        return $response->json();
    }

    public function getPaymentStatus(Request $request)
    {
        $request->validate([
            'transReference' => 'required|string',
        ]);

        // Make an external API request to get the status of a bill payment transaction
        $response = Http::get("{$this->baseUrl}/billspayment/status", [
            'transReference' => $request->transReference,
        ]);

        return $response->json();
    }
}