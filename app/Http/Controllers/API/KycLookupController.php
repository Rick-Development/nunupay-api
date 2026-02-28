<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Helpers\Payscribe\Kyc\KycLookup;
use App\Models\User;
use App\Models\UserKyc;
use Illuminate\Http\Request;

class KycLookupController extends Controller
{
    public function __construct(private KycLookup $kycLookup){}
  public function bvnLookup(Request $request) {
        $data = $request->validate([
            'type' => 'required | string',
            'value' => 'required | string',
        ]);
        $response = json_decode($this->kycLookup->bvnLookup($data['type'], $data['value']), true);
        // return $response;
        if(!$response['status']) {
            return response()->json([
                'description' => $response['description']
            ], 403);
        }

        
        $details = $response['message']['details'];
        $this->storeKYCdetails($details);

        
        return response()->json([
            'data' => $response
        ], 200);
    }  

    public function storeKYCdetails($details){
        $kycInfo = [
            'first_name' => $details['first_name'],
            'last_name' => $details['last_name'],
            'middle_name' => $details['middle_name'],
            'gender' => $details['gender'],
            'phone_number' => $details['phone_number'],
            'expiry_date' => $details['expiry_date'],
            'dob' => $details['dob'],
            'photo' => $details['photo'],
            'transaction_status' => $details['transaction_status'],
            'amount' => $details['amount'],
            'total_charge' => $details['total_charge'],
            'trans_id' => $details['trans_id'],
            'datetime' => $details['datetime'],
        ];

        UserKyc::updateOrCreate(['user_id' => auth()->id()], [
            'kyc_type' => "KYC Verifiaction",
            'kyc_info' => $kycInfo,
            'status' => 'pending',
        ]);
    }


    public function sendKYCOtp() {

        $phoneNum = UserKyc::where(['user_id' => auth()->id()])->pluck('kyc_info')->pluck('phone_number')->firstOrFail();

        // Sanitize phone number
        // $phoneNum = '09015310055';
        $sanitizedPhoneNum = $this->sanitizePhoneNumber($phoneNum);

        $message = $this->sendOTP($sanitizedPhoneNum);

        // return response
        return response()->json([
            'otp' => $message
        ]);

    }

    public function sendOTP($mobile_number)
    {
        $api_key = config('services.termii.api_key');
        $base_url = config('services.termii.base_url');
        $curl = curl_init();
        $data = array(
            "api_key" => "$api_key",
            "message_type" => "NUMERIC",
            "to" => "234$mobile_number",  
            "from" => "N-Alert",
            "channel" => "dnd",
            "pin_attempts" => 10,
            "pin_time_to_live" => 5,
            "pin_length" => 5,
            "pin_placeholder" => "< 12345 >",
            "message_text" => "Your one time OTP is < 12345 > .Valid for 5 minutes",
            "pin_type" => "NUMERIC"
        );

        $post_data = json_encode($data);

        curl_setopt_array($curl, array(
            CURLOPT_URL => "$base_url/api/sms/otp/send",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $post_data,
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json"
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return response()->json(json_decode($response, true));
    }


    public function verifyTermiiOtp(Request $request){
        $data = $request->validate([
            'otp_code' => 'required | string',
            'pin_id' => 'required | string'
        ]);

        $api_key = config('services.termii.api_key');
        $base_url = config('services.termii.base_url');

        $curl = curl_init();
        $data = array ( "api_key" => $api_key,
                     "pin_id" => $data['pin_id'],
                     "pin" => $data['otp_code'],
                    );
        
        $post_data = json_encode($data);
        
        curl_setopt_array($curl, array(
         CURLOPT_URL => "$base_url/api/sms/otp/verify",
         CURLOPT_RETURNTRANSFER => true,
         CURLOPT_ENCODING => "",
         CURLOPT_MAXREDIRS => 10,
         CURLOPT_TIMEOUT => 0,
         CURLOPT_FOLLOWLOCATION => true,
         CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
         CURLOPT_CUSTOMREQUEST => "POST",
         CURLOPT_POSTFIELDS => $post_data,
         CURLOPT_HTTPHEADER => array(
           "Content-Type: application/json"
         ),
        ));
        
        $response = curl_exec($curl);
        
        curl_close($curl);
        $verifiedRes =json_decode($response, true);
        
        $this->updateUserKYCStatus($verifiedRes);
        
        return response()->json([
            'data' => $verifiedRes
        ]);
        
    }

    public function updateUserKYCStatus($verifiedRes) {
        if($verifiedRes['verified'] !== 'true') {
            UserKyc::where(['user_id' => auth()->id()])->update([
                'status' => 2,
            ]);
            return;
        }
        UserKyc::where(['user_id' => auth()->id()])->update([
            'status' => 1,
        ]);
        $this->updateUser();
    }

    public function updateUser(){
        $kycInfo = UserKyc::where(['user_id' => auth()->id()])->pluck('kyc_info')->firstOrFail();
        $first_name = $kycInfo->first_name;
        $last_name = $kycInfo->last_name;
        User::where(['id' => auth()->id()])->update([
            'firstname' => $first_name,
            'lastname' => $last_name,
        ]);

    }

    private function sanitizePhoneNumber($phoneNumber)
    {
        // Check if the phone number starts with '0'
        if (strpos($phoneNumber, '0') === 0) {
            // Remove the leading '0'
            $phoneNumber = substr($phoneNumber, 1);
        }
    
        return $phoneNumber;
    }



    
    
    public function sendTermmiOtpCode($mobile_number, $kycOTP){
        $api_key = config('services.termii.api_key');
        $base_url = config('services.termii.base_url');
        $curl = curl_init();
        $data = array(
            "api_key" => $api_key, 
            "to" => "234$mobile_number",  
            "from" => "talert", 
            "sms" => "Your KYC OTP pin is $kycOTP. Valid for 10 minutes, one-time use",  
            "type" => "plain",
            "channel" => "dnd"
            );
        
        $post_data = json_encode($data);
        
        curl_setopt_array($curl, array(
            CURLOPT_URL => "$base_url/api/sms/send",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $post_data,
            CURLOPT_HTTPHEADER => [
            "Accept: application/json",
            "Content-Type: application/json"
            ],
            ));
        
        $response = curl_exec($curl);
        
        curl_close($curl);
        return response()->json(json_decode($response, true));
    }

    private function generateFiveDigitOTPCode()
    {
        return random_int(10000, 99999);
    }


  


    //   public function sendKYCOtp() {
    //     // $kycResponse = UserKyc::where(['user_id' => auth()->id()])->firstOrFail();
    //     //Get Phone number
    //     $phoneNum = UserKyc::where(['user_id' => auth()->id()])->pluck('kyc_info')->pluck('phone_number')->firstOrFail();
    //     // $phoneNum = $kycResponse['otp']['message']['details']['phone_number'];

    //     // Sanitize phone number
    //     $phoneNum = '09015310055';
    //     $sanitizedPhoneNum = $this->sanitizePhoneNumber($phoneNum);

    //     //Store Genereated OTP code
    //     // $otpCode = $this->generateFiveDigitOTPCode();
    //     // $user = User::find(auth()->id());
    //     // $user->update([
    //     //     'verify_code' => $otpCode,
    //     // ]);

    //     // Send OTP code using termii otp
    //     // $message = $this->sendTermmiOtpCode($sanitizedPhoneNum, $otpCode);
    //     $message = $this->sendOTP($sanitizedPhoneNum);

    //     // return response
    //     return response()->json([
    //         'otp' => $message
    //     ]);

    // }
}