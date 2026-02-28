<?php

namespace App\Http\Middleware;

use App\Traits\ApiResponse;
use App\Traits\Notify;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckVerificationApi
{
    use ApiResponse,Notify;
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */

    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        if ($user->sms_verification && $user->email_verification && $user->status && $user->two_fa_verify) {
            return $next($request);
        } else {
            if ($user->email_verification == 0) {
                $user->verify_code = code(6);
                $user->sent_at = Carbon::now();
                $user->save();
                $this->verifyToMail($user, 'VERIFICATION_CODE', [
                    'code' => $user->verify_code
                ]);
                return response()->json($this->withError('Email Verification Required'));
            } elseif ($user->sms_verification == 0) {
                $user->verify_code = code(6);
                $user->sent_at = Carbon::now();
                $user->save();
                $this->verifyToSms($user, 'VERIFICATION_CODE', [
                    'code' => $user->verify_code
                ]);
                return response()->json($this->withError('Mobile Verification Required'));
            } elseif ($user->status == 0) {
                return response()->json($this->withError('Your account has been suspend'));
            } elseif ($user->two_fa_verify == 0) {
                return response()->json($this->withError('Two FA Verification Required'));
            }
        }
    }
}
