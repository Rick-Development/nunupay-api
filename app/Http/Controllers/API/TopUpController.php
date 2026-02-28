<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\TopUp;
use App\Models\DataTopUp;

class TopUpController extends Controller
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

            // Check if the status is success and return the token
            if ($data['status'] === 'success') {
                // return response()->json(['success'=>$data]);
                return $data['data']['accessToken'];
            } else {
                // return response()->json(['Authentication failed:'=>$data]);
                // Handle unsuccessful authentication
                return 'Authentication failed: ' . $data['message'];
            }
        } else {
            // return response()->json(['Authentication failed: try again'=>$response]);
            // Handle HTTP errors
            return  $response->json();
        }
    }
    public function getNetwork(Request $request)
    {
        $token = $this->authenticate();

        // $auth = $this->authenticate();
        try {
        $phone = $request->query('phone');
        ///topup/network?phone=08136863210

        // Make an external API request to get network information
        $response = Http::withToken($token)->get("{$this->baseUrl}/topup/network", [
            'phone' => $phone,
        ]);
        // return response()->json(['auth'=>$token]);
        // return $response->json();
        return response()->json($response->json());
    } catch (\Exception $e) {
        // return response()->json(['auth'=>$token]);
        // Handle exceptions (e.g., log the error, return a response, etc.)
        return response()->json(['error' => $e]);
    }
    }

    public function getDataPlans(Request $request)
    {
        $phone = $request->query('phone');

        $token = $this->authenticate();
        ///topup/network?phone=08136863210

        // Make an external API request to get network information
        $response = Http::withToken($token)->get("{$this->baseUrl}/topup/dataPlans", [
            'phone' => $phone,
        ]);

        return $response->json();
    }

    public function airtimeTopup(Request $request)
    {
        $request->validate([
            'phoneNumber' => 'required|string',
            'network' => 'required|string',
            'amount' => 'required|numeric',
            'transactionReference' => 'required|string',
        ]);
    
        $token = $this->authenticate();
        ///topup/network?phone=08136863210

        // Make an external API request to get network information
        $response = Http::withToken($token)->post("{$this->baseUrl}/topup/airtime", [
            'phoneNumber' => $request->phoneNumber,
            'network' => $request->network,
            'amount' => $request->amount,
            'transactionReference' => $request->transactionReference,
        ]);
    
        // Save top-up record to the database
        $topUp = new TopUp();
        $topUp->phone_number = $request->phoneNumber;
        $topUp->network = $request->network;
        $topUp->amount = $request->amount;
        $topUp->transaction_reference = $request->transactionReference;
        $topUp->save();
    
        return $response->json();
    }

    public function dataTopup(Request $request)
    {
        $request->validate([
            'phoneNumber' => 'required|string',
            'amount' => 'required|numeric',
            'network' => 'required|string',
            'productId' => 'required|string',
            'transactionReference' => 'required|string',
        ]);
    
        $token = $this->authenticate();
        ///topup/network?phone=08136863210

        // Make an external API request to get network information
        $response = Http::withToken($token)->post("{$this->baseUrl}/topup/data", [
            'phoneNumber' => $request->phoneNumber,
            'amount' => $request->amount,
            'network' => $request->network,
            'productId' => $request->productId,
            'transactionReference' => $request->transactionReference,
        ]);
    
        // Save data top-up record to the database
        $dataTopUp = new DataTopUp();
        $dataTopUp->phone_number = $request->phoneNumber;
        $dataTopUp->network = $request->network;
        $dataTopUp->product_id = $request->productId;
        $dataTopUp->amount = $request->amount;
        $dataTopUp->transaction_reference = $request->transactionReference;
        $dataTopUp->save();
    
        return $response->json();
    }

    public function getTopupStatus(Request $request)
    {
        $request->validate([
            'transReference' => 'required|string',
        ]);

        $token = $this->authenticate();
        ///topup/network?phone=08136863210

        // Make an external API request to get network information
        $response = Http::withToken($token)->get("{$this->baseUrl}/topup/status", [
            'transReference' => $request->transReference,
        ]);

        return $response->json();
    }
}