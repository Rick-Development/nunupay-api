<?php

namespace App\Http\Controllers\API;

use App\Helpers\UserSystemInfo;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\ApiResponse;
use App\Traits\Notify;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use PragmaRX\Google2FA\Google2FA;

class TwoFASecurityController extends Controller
{
    use ApiResponse, Notify;

    private function generateSecretKeyForUser(User $user)
    {
        $google2fa = new Google2FA();
        $secret = $google2fa->generateSecretKey();
        $user->update(['two_fa_code' => $secret]);

        return $secret;
    }

    public function twoFASecurity()
    {
        $basic = basicControl();
        try {
            $user = auth()->user();

            $google2fa = new Google2FA();
            $secret = $user->two_fa_code ?? $this->generateSecretKeyForUser($user);

            $qrCodeUrl = $google2fa->getQRCodeUrl(
                auth()->user()->username,
                $basic->site_title,
                $secret
            );

            $data = [
                'twoFactorEnable' => $user->two_fa == 0 ? false : true,
                'secret' => $secret,
                'qrCodeUrl' => $qrCodeUrl,
                'downloadApp' => 'https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2&hl=en',
            ];
            return response()->json($this->withSuccess($data));
        } catch (\Exception $e) {
            return response()->json($this->withError($e->getMessage()));
        }
    }


    public function twoFASecurityEnable(Request $request)
    {
        try {
            $user = auth()->user();
            $validateUser = Validator::make($request->all(),
                [
                    'code' => 'required',
                ]);
            if ($validateUser->fails()) {
                return response()->json($this->withError(collect($validateUser->errors())->collapse()));
            }

            $secret = auth()->user()->two_fa_code;

            $google2fa = new Google2FA();
            $valid = $google2fa->verifyKey($secret, $request->code);
            if ($valid) {
                $user['two_fa'] = 1;
                $user['two_fa_verify'] = 1;
                $user->save();

                $this->mail($user, 'TWO_STEP_ENABLED', [
                    'action' => 'Enabled',
                    'code' => $user->two_fa_code,
                    'ip' => request()->ip(),
                    'browser' => UserSystemInfo::get_browsers() . ', ' . UserSystemInfo::get_os(),
                    'time' => date('d M, Y h:i:s A'),
                ]);

                return response()->json($this->withSuccess('Google Authenticator Has Been Enabled.'));
            } else {
                return response()->json($this->withError('Wrong Verification Code.'));
            }
        } catch (\Exception $e) {
            return response()->json($this->withError($e->getMessage()));
        }
    }

    public function twoFASecurityDisable(Request $request)
    {
        try {
            $validate = Validator::make($request->all(),
                [
                    'password' => 'required',
                ]);
            if ($validate->fails()) {
                return response()->json($this->withError(collect($validate->errors())->collapse()));
            }

            if (!Hash::check($request->password, auth()->user()->password)) {
                return response()->json($this->withError('Incorrect password. Please try again.'));
            }

            $user = auth()->user();

            $user->update([
                'two_fa' => 0,
                'two_fa_verify' => 1,
            ]);

            return response()->json($this->withSuccess('Two-step authentication disabled successfully.'));
        } catch (\Exception $e) {
            return response()->json($this->withError($e->getMessage()));
        }
    }
}
