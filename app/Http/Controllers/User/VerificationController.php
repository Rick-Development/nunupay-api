<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Content;
use App\Models\User;
use App\Traits\ApiResponse;
use App\Traits\Notify;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use PragmaRX\Google2FA\Google2FA;
use Twilio\Rest\Client;

class VerificationController extends Controller
{
    use Notify, ApiResponse;

    public function __construct()
    {
        $this->middleware(['auth']);

        $this->middleware(function ($request, $next) {
            $this->user = auth()->user();
            return $next($request);
        });
        $this->theme = template();
    }

    public function checkValidCode($user, $code, $add_min = 10000)
    {
        if (!$code) return false;
        if (!$user->sent_at) return false;
        if (Carbon::parse($user->sent_at)->addMinutes($add_min) < Carbon::now()) return false;
        if ($user->verify_code != $code) return false;
        return true;
    }

    public function check()
    {
        $user = $this->user;
        $data['user_verify'] = Content::with('contentDetails')->where('name','user_verify')
            ->where('type', 'single')->first();
        if (!$user->status) {
            Auth::guard('web')->logout();
        } elseif (!$user->email_verification) {
            if (!$this->checkValidCode($user, $user->verify_code)) {
                $user->verify_code = code(6);
                $user->sent_at = Carbon::now();
                $user->save();
                $this->verifyToMail($user, 'VERIFICATION_CODE', [
                    'code' => $user->verify_code
                ]);
                session()->flash('success', 'Email verification code has been sent');
            }
            $page_title = 'Email Verification';

            return view(template() . 'auth.verification.email',$data, compact('user', 'page_title'));
        } elseif (!$user->sms_verification) {
            if (!$this->checkValidCode($user, $user->verify_code)) {
                $user->verify_code = code(6);
                $user->sent_at = Carbon::now();
                $user->save();

                $this->verifyToSms($user, 'VERIFICATION_CODE', [
                    'code' => $user->verify_code
                ]);
                session()->flash('success', 'SMS verification code has been sent');
            }
            $page_title = 'SMS Verification';

            return view(template() . 'auth.verification.sms',$data, compact('user', 'page_title'));
        } elseif (!$user->two_fa_verify) {
            $page_title = '2FA Code';
            return view(template() . 'auth.verification.2stepSecurity',$data, compact('user', 'page_title'));

        }
        return redirect()->route('user.dashboard');
    }

    public function userOtp(Request $request)
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

        return response()->json($this->withError('Invalid Request Method'));
    }


    public function resendCode()
    {
        $type = request()->type;
        $user = $this->user;
        if ($this->checkValidCode($user, $user->verify_code, 2)) {
            $target_time = Carbon::parse($user->sent_at)->addMinutes(2)->timestamp;
            $delay = $target_time - time();
            throw ValidationException::withMessages(['resend' => 'Please Try after ' . gmdate("i:s", $delay) . ' minutes']);
        }
        if (!$this->checkValidCode($user, $user->verify_code)) {
            $user->verify_code = code(6);
            $user->sent_at = Carbon::now();
            $user->save();
        } else {
            $user->sent_at = Carbon::now();
            $user->save();
        }

        if ($type === 'email') {
            $this->verifyToMail($user, 'VERIFICATION_CODE', [
                'code' => $user->verify_code
            ]);
            return back()->with('success', 'Email verification code has been sent.');
        } elseif ($type === 'mobile') {
            $this->verifyToSms($user, 'VERIFICATION_CODE', [
                'code' => $user->verify_code
            ]);
            return back()->with('success', 'SMS verification code has been sent');
        } else {
            throw ValidationException::withMessages(['error' => 'Sending Failed']);
        }
    }

    public function mailVerify(Request $request)
    {
        $rules = [
            'code' => 'required',
        ];
        $msg = [
            'code.required' => 'Email verification code is required',
        ];
        $validate = $this->validate($request, $rules, $msg);
        $user = $this->user;
        if ($this->checkValidCode($user, $request->code)) {
            $user->email_verification = 1;
            $user->verify_code = null;
            $user->sent_at = null;
            $user->save();
            return redirect()->intended(route('user.dashboard'));
        }
        throw ValidationException::withMessages(['error' => 'Verification code didn\'t match!']);
    }

    public function smsVerify(Request $request)
    {
        $rules = [
            'code' => 'required',
        ];
        $msg = [
            'code.required' => 'SMS verification code is required',
        ];
        $validate = $this->validate($request, $rules, $msg);

        $user = Auth::user();

        if ($this->checkValidCode($user, $request->code)) {
            $user->sms_verification = 1;
            $user->verify_code = null;
            $user->sent_at = null;
            $user->save();

            return redirect()->intended(route('user.dashboard'));
        }
        throw ValidationException::withMessages(['error' => 'Verification code didn\'t match!']);
    }

    public function twoFAverify(Request $request)
    {
        $this->validate($request, [
            'code' => 'required',
        ], [
            'code.required' => '2FA verification code is required',
        ]);

        $user = Auth::user();
        $secret = auth()->user()->two_fa_code;

        $google2fa = new Google2FA();
        $valid = $google2fa->verifyKey($secret,$request->code);

        if ($valid) {
            $user->two_fa_verify = 1;
            $user->save();
            return redirect()->intended(route('user.dashboard'));
        }
        throw ValidationException::withMessages(['error' => 'Wrong Verification Code']);

    }

}
