<?php
namespace App\Http\Helpers;
use Illuminate\Support\Facades\Log;

class GiftCardConnectionHelper{
  private $apiClientId;
    private $apiClientSecret;
    private $apiUrl;
    private $apiAuthUrl;

    public function __construct() {
        $this->apiClientId = config('services.reloadly.client_id'); // Load from config
        $this->apiClientSecret = config('services.reloadly.client_secret'); // Load from config
        $this->apiUrl = config('services.reloadly.api_url'); // Load from config
        $this->apiAuthUrl = config('services.reloadly.auth_url'); // Load from config
    }


public function authentication(){
    
    
$curl = curl_init();

curl_setopt_array($curl, [
	CURLOPT_URL => $this->apiAuthUrl,
	CURLOPT_RETURNTRANSFER => true,
	CURLOPT_ENCODING => "",
	CURLOPT_MAXREDIRS => 10,
	CURLOPT_TIMEOUT => 30,
	CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	CURLOPT_CUSTOMREQUEST => "POST",
	CURLOPT_POSTFIELDS => "{\n    \"client_id\": \"$this->apiClientId\",\n    \"client_secret\": \"$this->apiClientSecret\",\n    \"grant_type\": \"client_credentials\",\n    \"audience\": \"$this->apiUrl\"\n}",
	CURLOPT_HTTPHEADER => [
		"Accept: application/json",
		"Content-Type: application/json"
	],
]);

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);
$decodedResponse = json_decode($response,true);
return $decodedResponse['access_token'];
// if ($err) {
// 	echo "cURL Error #:" . $err;
// } else {
// 	echo $response;
// }
}
     

     public function get($url){
        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL =>$this->apiUrl . $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => array(
            "Authorization: Bearer ".$this->authentication()
        ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return $response;

    }
    public function post($url, array $data) {
        $curl = curl_init();
        
        // Log the request details
        Log::info([
            "url" => $this->apiUrl . $url,
            "data" => $data,
            "Api key" => $this->authentication(),
        ]);
        
        // Convert the data array to JSON
        $jsonData = json_encode($data);
        
        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->apiUrl . $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $jsonData,
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer ". $this->authentication(),
                "Content-Type: application/json", // Set the content type to JSON
                "Content-Length: " . strlen($jsonData) // Set the content length
            ),
        ));
        
        $response = curl_exec($curl);
        // $response = json_decode( $response,true);
        
        // Log the response
        Log::info($response);
        
        curl_close($curl);

        // {"status":false,"description":"Missing information, please check the errors","errors":{"email":"The email field must contain a valid email address."}} 
        
        return $response;
    }

    public function patch($url,$data){

        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'PATCH',
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_HTTPHEADER => array(
            "Authorization: Bearer ".$this->authentication()
        ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return $response;

}
}