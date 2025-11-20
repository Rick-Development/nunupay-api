<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponse;
use App\Traits\Notify;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OTPController extends Controller
{
    use Notify,ApiResponse;

    public function transferOtp(Request $request)
    {
        $user = auth()->user();
        if (!$user){
            return response()->json($this->withError('Something went wrong, Invalid user'));
        }

        if ($request->isMethod('get')) {
            $rules = ['option' => 'required',];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json($this->withError(collect($validator->errors())->collapse()));
            }

            $otp = strRandomNum(4);

            $user->verify_code = $otp;
            $user->sent_at = now();
            $user->save();

            $params = ['otp' => $otp];
            if ($request->option == 'sms' || $request->option == 'both') {
                $this->sms($user, 'USER_TRANSFER_OTP', $params);
            }
            if ($request->option == 'email' || $request->option == 'both') {
                $this->mail($user, 'USER_TRANSFER_OTP', $params);
            }
            if ($request->option == 'whatsapp') {
                $this->sendWhatsAppMessage($otp);
            }
            return response()->json($this->withSuccess('OTP sent successfully'));
        }

        if ($request->isMethod('post')) {
            $rules = ['otp' => 'required',];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json($this->withError(collect($validator->errors())->collapse()));
            }
            $userOtp = $user->verify_code;
            $providedOtp = $request->input('otp');

            if ($userOtp && $userOtp === $providedOtp) {
                return response()->json($this->withSuccess('OTP verified successfully'));
            }
            else{
                return response()->json($this->withError("Invalid OTP."));
            }
        }
        return response()->json($this->withError('Invalid Request Method'));
    }

}
