<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class PayscribeCustomerController extends Controller
{
    // protected $baseUrl = 'https://sandbox.payscribe.ng/api/v1/customers'; // Base URL for customer-related API
    protected $baseUrl; // Base URL for customer-related API
    // protected $apiKey = 'ps_pk_test_mjwKJDOh41Zrl5uMXUJqwy3pyPYx5d';
    protected $apiKey;

    public function __construct() {
        $this->apiKey = config('services.payscribe.public'); // Store in .env
       //'ps_pk_test_Od2eDKnXWrVAAXat85kV4fQYjV0sAi'; 
        $this->baseUrl = config('services.payscribe.api_url') . '/customers'; // Store in .env
       
        }

    /**
     * Create a new customer (Tier 0) in the Payscribe ecosystem.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function createCustomer(Request $request): JsonResponse
    {
       
        $data = ([
            "first_name" => $request->firstname,
            "last_name" => $request->lastname,
            "phone" => '0'. $request->phone,
            "email" => $request->email,
            "country" => "NG"
        ]);
        $response = Http::withHeaders([
            'Authorization' => "Bearer $this->apiKey", 
        ])->post("{$this->baseUrl}/create", $data);

        return $this->handleResponse($response);
    }


 public function  upgradeToTierOne(Request $request): JsonResponse
    {
        $request->validate([
            'customer_id' => 'required | string',
            'dob' => 'required | string',
            'street' => 'required | string',
            'city' => 'required | string',
            'state' => 'required | string',
            'country' => 'required | string',
            'postal_code' => 'required | string',
            'identification_type' => 'required | string',
            'identification_number' => 'required | string',
            // 'photo' => 'sometimes | image'
        ]);
        // $serverUrl = config('app.url');
        // $image = $request->file('photo');
        // // Storage::disk('public')->put('giftcards', $image);
        // $uploadedImg = $image->store('customerImg', 'public');
        // $imgUrl = asset($uploadedImg);
        // $fullUrl = $serverUrl . $imgUrl;
        
        $imagePath = asset('storage/app/public/uploads/AdihROvTAnyM1PFKWLLC0Gaqm3cRoW6YatXS9NG0.png');

        $data = [
            'customer_id' => $request->customer_id,
            'dob' => $request->dob,
            'address' => [
                'street' => $request->street,
                'city' => $request->city,
                'state' => $request->state,
                'country' => $request->country,
                'postal_code' => $request->postal_code,
            ],
            'identification_type' => $request->identification_type,
            'identification_number' => $request->identification_number,
            'photo' => $imagePath,
        ];
        
        $response = Http::withHeaders([
            'Authorization' => "Bearer $this->apiKey", 
        ])->post("{$this->baseUrl}/create/tier1", $data);

        return $this->handleResponse($response);
    }



 public function  upgradeToTierTwo(Request $request): JsonResponse
    {
        $serverUrl = config('app.url');

        $request->validate([
            'customer_id' => 'required | string',
            'type' => 'required | string',
            'number' => 'required | string',
            'country' => 'required | string',
            // 'image' => 'required | image'
        ]);

        // $image = $request->file('image');

        // $uploadedImg = $image->store('customerImg', 'public');
        // $imgUrl = Storage::url($uploadedImg);
        // $fullUrl = $serverUrl . $imgUrl;

        $imagePath = asset('storage/app/public/uploads/AdihROvTAnyM1PFKWLLC0Gaqm3cRoW6YatXS9NG0.png');

        $data = [
            'customer_id' => $request->customer_id,
            'identity' => [
                'type' => $request->type,
                'number' => $request->number,
                'country' => $request->country,
                'image' => $imagePath,
            ],
        ];
        
        $response = Http::withHeaders([
            'Authorization' => "Bearer $this->apiKey", 
        ])->post("{$this->baseUrl}/create/tier2", $data);

        return $this->handleResponse($response);
    }


    /**
     * Retrieve all customers with optional filtering.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getAllCustomers(Request $request): JsonResponse
    {
        $queryParams = [
            'page' => $request->get('page', 1),
            'page_size' => $request->get('page_size', 10),
            'start_date' => $request->get('start_date'),
            'end_date' => $request->get('end_date'),
            'search' => $request->get('search'),
        ];

        $response = Http::withHeaders([
            'Authorization' => "Bearer $this->apiKey", // Replace with your actual API key
        ])->get("{$this->baseUrl}/", $queryParams);

        return $this->handleResponse($response);
    }

    /**
     * Get detailed information about a specific customer.
     *
     * @param string $customerId
     * @return JsonResponse
     */
    public function getCustomerDetails(string $customerId): JsonResponse
    {
        $response = Http::withHeaders([
            'Authorization' => "Bearer $this->apiKey", // Replace with your actual API key
        ])->get("{$this->baseUrl}/{$customerId}/details");

        return $this->handleResponse($response);
    }

    /**
     * Whitelist or blacklist a customer based on their status.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function toggleCustomerBlacklist(Request $request): JsonResponse
    {
        $request->validate([
            'customer_id' => 'required|string',
            'blacklist' => 'required|boolean',
        ]);

        $response = Http::withHeaders([
            'Authorization' => "Bearer $this->apiKey", // Replace with your actual API key
        ])->post("{$this->baseUrl}/blacklist", $request->all());

        return $this->handleResponse($response);
    }

    /**
     * Update customer details in the Payscribe ecosystem.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updateCustomer(Request $request): JsonResponse
    {
        $request->validate([
            'customer_id' => 'required|string',
            'phone' => 'required|string',
            'dob' => 'required|date_format:Y-m-d',
            'address' => 'required|array',
            'identification_number' => 'required|string',
            'identification_type' => 'required|string',
            'photo' => 'required|string',
            'identity' => 'required|array',
        ]);

        $response = Http::withHeaders([
            'Authorization' => "Bearer $this->apiKey", // Replace with your actual API key
        ])->patch("{$this->baseUrl}/update", $request->all());  

        return $this->handleResponse($response);
    }

    /**
     * Retrieve all transactions for a specific customer.
     *
     * @param string $customerId
     * @return JsonResponse
     */
    public function getCustomerTransactions(string $customerId): JsonResponse
    {
        $response = Http::withHeaders([
            'Authorization' => "Bearer $this->apiKey", // Replace with your actual API key
        ])->get("{$this->baseUrl}/{$customerId}/transactions");

        return $this->handleResponse($response);
    }

    public function customerBalance(Request $request): JsonResponse
    {
        $data = $request->validate([
            'customer_id' => 'required|string',
        ]);
        $customerBalance = User::where('payscribe_id', $data['customer_id'])->first()->account_balance;
        return response()->json(['balance' => $customerBalance]);
    }

    public function customerTransactions(): JsonResponse
    {
        
        $transactions = Transaction::where('user_id', auth()->id())->paginate(10);
        return response()->json(['transactions' => $transactions]);
        // return response()->json(['transactions' => auth()->id()]);
    }

    public function resetPin(Request $request): JsonResponse
    {
        $data = $request->validate([
            'pin' => 'required|string',
        ]);
        User::where('id', auth()->id())->update(['user_pin' => $data['pin']]);
        return response()->json(['message' => 'Pin reset successful']);

    }
    /**
     * Handle the response from the Payscribe API.
     *
     * @param \Illuminate\Http\Client\Response $response
     * @return JsonResponse
     */
    protected function handleResponse(\Illuminate\Http\Client\Response $response): JsonResponse
    {
        if ($response->successful()) {
            return response()->json($response->json(), $response->status());
        }

        return response()->json(['error' => $response->json()], $response->status());
    }
}