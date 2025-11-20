<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\VirtualCardMethod;
use App\Models\VirtualCardOrder;
use App\Models\VirtualCardTransaction;
use App\Services\VirtualCard\stripe\Card;
use App\Traits\ApiResponse;
use App\Traits\ManageWallet;
use App\Traits\Notify;
use App\Traits\Upload;
use App\Traits\VirtualCardTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Stevebauman\Purify\Facades\Purify;

class NinePaymentService extends Controller
{
    use ApiResponse, Notify, Upload, ManageWallet, VirtualCardTrait;

//     public function index()
//     {
        

// // Set the API endpoint URL
// $url = "http://102.216.128.75:9090/bank9ja/api/v2/k1/authenticate";

// // The data to send in the POST request
// $data = [
//     "username" => "PK_TEST_2Aufw49wci",
//     "password" => "EdQDUBMrf4pekY4dXkq2Ki8WSHJ9BM0z",
//     "clientId" => "waas",
//     "clientSecret" => "cRAwnWElcNMUZpALdnlve6PubUkCPOQR"
// ];

// // Initialize cURL
// $ch = curl_init($url);

// // Set cURL options
// curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return response as a string
// curl_setopt($ch, CURLOPT_POST, true); // Set POST method
// curl_setopt($ch, CURLOPT_TIMEOUT, 30); // 30 seconds
// curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30); // 30 seconds
// curl_setopt($ch, CURLOPT_VERBOSE, true);
// curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
// curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
// curl_setopt($ch, CURLOPT_HTTPHEADER, [
//     'Content-Type: application/json', // Specify content type as JSON
//     'Accept: application/json'
// ]);
// curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data)); // Send data as JSON

// // Execute cURL request
// $response = curl_exec($ch);

// // Check for cURL errors
// if (curl_errno($ch)) {
//     echo 'Error: ' . curl_error($ch);
//     var_dump($ch);
// } else {
//     // Parse and print the response
//     $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
//     if ($http_status === 200) {
//         // The request was successful, display the response
//         echo 'Response: ' . $response;
//     } else {
//         // Something went wrong, display the response
//         echo 'HTTP Status Code: ' . $http_status . "\n";
//         echo 'Response: ' . $response;
//     }
// }

// // Close cURL resource
// curl_close($ch);

//     }

public function index(){
    
    
// Set your username and password
$username = "PK_TEST_2Aufw49wci"; // replace with your actual username
$password = "EdQDUBMrf4pekY4dXkq2Ki8WSHJ9BM0z"; // replace with your actual password

// Initialize cURL session
$ch = curl_init();

// The URL to send the request to
$url = "http://102.216.128.75:9090/bank9ja/api/v2/k1/authenticate";
// $url = 'http://102.216.128.75:9090/identity/api/v1/authenticate';


// Proxy settings
$proxy = "223.206.197.63:8080"; // replace with your actual proxy address and port
// $proxyUserPwd = "proxy_user:proxy_password"; // replace with your proxy username and password if required

// Data to be sent in the POST request
$data = json_encode([
    'username' => $username,
    'password' => $password,
    'clientId' => 'waas',
    'clientSecret' => 'cRAwnWElcNMUZpALdnlve6PubUkCPOQR'
]);

// Set the cURL options
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json', // Set content type to JSON
    'Accept: application/json', // Expect JSON response
    'Content-Length: ' . strlen($data) // Include content length
]);
// Set proxy options
curl_setopt($ch, CURLOPT_PROXY, $proxy);
// curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxyUserPwd); // Uncomment if authentication is needed

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return response as a string

// Execute the request
$response = curl_exec($ch);

// Check for errors
if (curl_errno($ch)) {
    echo 'cURL Error: ' . curl_error($ch);
} else {
    // Get HTTP status code and response
    $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    echo 'HTTP Status Code: ' . $http_status . "\n";
    echo 'Response: ' . $response . "\n";
}

// Close the cURL session
curl_close($ch);




}


}