<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\GoogleAuthenticator;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\BasicControl;
use App\Models\CountryCurrency;
use App\Traits\Notify;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Facades\App\Services\BasicService;
use Facades\App\Services\CurrencyLayerService;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use PragmaRX\Google2FA\Google2FA;
use Stevebauman\Purify\Facades\Purify;
use function GuzzleHttp\Promise\all;
use hisorange\BrowserDetect\Parser as Browser;
use App\Http\Controllers\API\PayscribeUserCardController;


class BasicControlController extends Controller
{
    use Notify;
    public function index($settings = null)
    {
        $settings = $settings ?? 'settings';
        abort_if(!in_array($settings, array_keys(config('generalsettings'))), 404);
        $settingsDetails = config("generalsettings.{$settings}");
        return view('admin.control_panel.settings', compact('settings', 'settingsDetails'));
    }

    public function basicControl()
    {
        $data['basicControl'] = basicControl();
        $data['timeZones'] = timezone_identifiers_list();
        $data['dateFormat'] = config('dateformat');

        $data['currencies'] = CountryCurrency::all();

        return view('admin.control_panel.basic_control', $data);
    }

    public function cookieControl(Request $request)
    {
        if ($request->isMethod('get')) {
            $data['basic'] = basicControl();
            return view('admin.control_panel.cookie', $data);
        }
        elseif ($request->isMethod('post')) {
            $data = $request->validate([
                'cookie_title' => 'required|string|max:100',
                'cookie_sub_title' => 'required|string|max:100',
                'cookie_url' => 'required|string|max:100',
                'cookie_status' => 'nullable|numeric|in:0,1',
            ]);
            $basic = BasicControl();
            $basic->update($data);

            Artisan::call('optimize:clear');
            return back()->with('success', 'Successfully Updated');
        }
    }

    public function basicControlUpdate(Request $request)
    {

        $request->validate([
            'site_title' => 'required|string|min:3|max:100',
            'time_zone' => 'required|string',
            'base_currency' => 'required|string|min:1|max:100',
            'currency_symbol' => 'required|string|min:1|max:100',
            'currency_rate' => 'required',
            'fraction_number' => 'required|integer|not_in:0',
            'paginate' => 'required|integer|not_in:0',
            'date_format' => 'required',
            'admin_prefix' => 'required|string|min:3|max:100',
            'primary_color' => 'required|string',
            'secondary_color' => 'required|string',
        ]);

        try {
            $basic = BasicControl();
            $response = BasicControl::updateOrCreate([
                'id' => $basic->id ?? ''
            ], [
                'site_title' => $request->site_title,
                'time_zone' => $request->time_zone,
                'base_currency' => $request->base_currency,
                'currency_symbol' => $request->currency_symbol,
                'currency_rate' => $request->currency_rate,
                'fraction_number' => $request->fraction_number,
                'date_time_format' => $request->date_format,
                'paginate' => $request->paginate,
                'admin_prefix' => $request->admin_prefix,
                'primary_color' => $request->primary_color,
                'secondary_color' => $request->secondary_color,
            ]);

            if (!$response)
                throw new Exception('Something went wrong, when updating data');

            $env = [
                'APP_TIMEZONE' => $response->time_zone,
                'APP_DEBUG' => $response->error_log == 0 ? 'true' : 'false'
            ];
            BasicService::setEnv($env);

            session()->flash('success', 'Basic Control Configure Successfully');
            Artisan::call('optimize:clear');
            return back();
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function basicControlActivityUpdate(Request $request)
    {

        $request->validate([
            'strong_password' => 'nullable|numeric|in:0,1',
            'registration' => 'nullable|numeric|in:0,1',
            'error_log' => 'nullable|numeric|in:0,1',
            'is_active_cron_notification' => 'nullable|numeric|in:0,1',
            'has_space_between_currency_and_amount' => 'nullable|numeric|in:0,1',
            'is_force_ssl' => 'nullable|numeric|in:0,1',
            'is_currency_position' => 'nullable|string|in:left,right'
        ]);

        try {
            $basic = BasicControl();
            $response = BasicControl::updateOrCreate([
                'id' => $basic->id ?? ''
            ], [
                'error_log' => $request->error_log,
                'strong_password' => $request->strong_password,
                'registration' => $request->registration,
                'is_active_cron_notification' => $request->is_active_cron_notification,
                'has_space_between_currency_and_amount' => $request->has_space_between_currency_and_amount,
                'is_currency_position' => $request->is_currency_position,
                'is_force_ssl' => $request->is_force_ssl
            ]);

            if (!$response)
                throw new Exception('Something went wrong, when updating data');

            session()->flash('success', 'Basic Control Configure Successfully');
            Artisan::call('optimize:clear');
            return back();
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }


    }


    public function virtualCardSettings(Request $request)
    {
        $basicControl = basicControl();

        if ($request->isMethod('get')) {
            return view('admin.control_panel.virtualCardSettings', compact('basicControl'));
        } elseif ($request->isMethod('post')) {

            $purifiedData = Purify::clean($request->all());

            $validator = Validator::make($purifiedData, [
                'v_card_multiple' => 'nullable|integer|min:0|in:0,1',
                'v_card_charge' => 'nullable|integer|min:0',
            ]);
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }
            $purifiedData = (object)$purifiedData;
            $basicControl->v_card_multiple = $purifiedData->v_card_multiple;
            $basicControl->v_card_charge = $purifiedData->v_card_charge;
            $basicControl->save();

            return back()->with('success', 'Successfully Updated');
        }
    }


    public function moneyTransferSettings(Request $request)
    {
        $basicControl = basicControl();

        if ($request->isMethod('get')) {
            return view('admin.control_panel.moneyTransferSettings', compact('basicControl'));
        } elseif ($request->isMethod('post')) {

            $purifiedData = Purify::clean($request->all());

            $validator = Validator::make($purifiedData, [
                'min_amount' => 'nullable|min:0',
                'max_amount' => 'nullable|min:0',
                'min_transfer_fee' => 'nullable|min:0',
                'max_transfer_fee' => 'nullable|min:0',
            ]);
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }
            $purifiedData = (object)$purifiedData;
            $basicControl->min_amount = $purifiedData->min_amount;
            $basicControl->max_amount = $purifiedData->max_amount;
            $basicControl->min_transfer_fee = $purifiedData->min_transfer_fee;
            $basicControl->max_transfer_fee = $purifiedData->max_transfer_fee;
            $basicControl->save();

            return back()->with('success', 'Successfully Updated');
        }
    }
    
    
    
    public function ninepsbSettings(Request $request)
    {
        $basicControl = basicControl();

        if ($request->isMethod('get')) {
            return view('admin.control_panel.ninePSBSettings', compact('basicControl'));
        } elseif ($request->isMethod('post')) {

            $purifiedData = Purify::clean($request->all());

            // $validator = Validator::make($purifiedData, [
            //     'min_amount' => 'nullable|min:0',
            //     'max_amount' => 'nullable|min:0',
            //     'min_transfer_fee' => 'nullable|min:0',
            //     'max_transfer_fee' => 'nullable|min:0',
            // ]);
            // if ($validator->fails()) {
            //     return back()->withErrors($validator)->withInput();
            // }
            $purifiedData = (object)$purifiedData;
            $basicControl->nine_payment_username = $purifiedData->nine_payment_username;
            $basicControl->nine_payment_password = $purifiedData->nine_payment_password;
            $basicControl->nine_payment_client_id = $purifiedData->nine_payment_client_id;
            $basicControl->nine_payment_client_secret = $purifiedData->nine_payment_client_secret;
            $basicControl->nine_payment_api_key = $purifiedData->nine_payment_api_key;
            $basicControl->nine_payment_secret_key = $purifiedData->nine_payment_secret_key;
            
            
            $basicControl->transfer_to_airpero_charge_in_ngn = $purifiedData->transfer_to_airpero_charge_in_ngn;
            $basicControl->bill_payment_charge_in_ngn = $purifiedData->bill_payment_charge_in_ngn;
            $basicControl->transfer_to_other_bank_charge_in_ngn = $purifiedData->transfer_to_other_bank_charge_in_ngn;
            $basicControl->deposit_charge_in_ngn = $purifiedData->deposit_charge_in_ngn;
            $basicControl->nine_payment_account_number =  $purifiedData->nine_payment_account_number;
            $basicControl->save();

            return back()->with('success', 'Successfully Updated');
        }
    }
    
    
    
    public function payscribeSettings(Request $request)
    {
        $basicControl = basicControl();


            if ($request->isMethod('get')) {
                // Call the controller method and get the raw JSON response
                $payscribe = app(PayscribeUserCardController::class);
                $response = $payscribe->getCurrencyPair('USD', 'NGN'); 
                
                // Step 1: Decode the raw response body (similar to `jsonDecode(response.body)`)
                $result = json_decode(json_encode($response), true); // Converts JSON to associative array
                
                // Step 2: Extract message and details (similar to `jsonDecode(result)`)
                $resultBody = json_decode($result['original'], true); // In case the response is double-encoded, this will re-parse it
                
                // Step 3: Extract the details section (similar to `resultBody['message']['details']`)
                $details = $resultBody['message']['details'];
                
                // Step 4: Extract the rate from details (similar to `${details['rate']}`)
                $res = $details['rate'];
                
                // Optional: Format the rate to 2 decimal places (like "1425.04")
                $rate = number_format($res, 2);
                // $rate = json_encode($result);
                
                // Return or pass it to a view
                return view('admin.control_panel.payscribeSettings', compact('basicControl', 'rate'));
            }
            elseif ($request->isMethod('post')) {

            $purifiedData = Purify::clean($request->all());

            // $validator = Validator::make($purifiedData, [
            //     'min_amount' => 'nullable|min:0',
            //     'max_amount' => 'nullable|min:0',
            //     'min_transfer_fee' => 'nullable|min:0',
            //     'max_transfer_fee' => 'nullable|min:0',
            // ]);
            // if ($validator->fails()) {
            //     return back()->withErrors($validator)->withInput();
            // }
            
            $purifiedData = (object)$purifiedData;
            $basicControl->payscribe_apikey = $purifiedData->payscribe_apikey;
            $basicControl->card_issuing_amount_in_usd = $purifiedData->card_issuing_amount_in_usd;
            $basicControl->card_issuing_charge_in_usd = $purifiedData->card_issuing_charge_in_usd;
            $basicControl->card_deposit_charge_in_usd = $purifiedData->card_deposit_charge_in_usd;
            $basicControl->card_withdrawal_charge_in_usd = $purifiedData->card_withdrawal_charge_in_usd;
            // $basicControl->max_transfer_fee = $purifiedData->max_transfer_fee;
            
            $basicControl->card_withdrawal_rate = $purifiedData->card_withdrawal_rate;
            $basicControl->card_deposit_rate = $purifiedData->card_deposit_rate;
            $basicControl->card_issuing_rate = $purifiedData->card_issuing_rate;
            $basicControl->save();

            return back()->with('success', 'Successfully Updated');
        }
    }
    
    
    public function adsBanner(Request $request)
    {
        $basicControl = basicControl();

        if ($request->isMethod('get')) {
            return view('admin.control_panel.adsbanner', compact('basicControl'));
        } elseif ($request->isMethod('post')) {


            return back()->with('success', 'Successfully Updated');
        }
    }
    
    

    public function referUserSettings (Request $request)
    {
        $basicControl = basicControl();

        if ($request->isMethod('get')) {
            return view('admin.control_panel.referUserSettings ', compact('basicControl'));
        } elseif ($request->isMethod('post')) {

            $purifiedData = Purify::clean($request->all());

            $validator = Validator::make($purifiedData, [
                'refer_status' => 'required|integer|in:0,1',
                'refer_title' => 'required|string|max:255',
                'refer_earn_amount' => 'required|min:0',
                'refer_free_transfer' => 'required|min:0',
            ]);
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }
            $purifiedData = (object)$purifiedData;
            $basicControl->refer_status = $purifiedData->refer_status;
            $basicControl->refer_title = $purifiedData->refer_title;
            $basicControl->refer_earn_amount = $purifiedData->refer_earn_amount;
            $basicControl->refer_free_transfer = $purifiedData->refer_free_transfer;
            $basicControl->save();

            return back()->with('success', 'Successfully Updated');
        }
    }


    public function twoFaControl()
    {
        $basic = basicControl();
        $admin = Auth::guard('admin')->user();
        $google2fa = new Google2FA();
        $secret = $admin->two_fa_code ?? $this->generateSecretKeyForUser($admin);

        $qrCodeUrl = $google2fa->getQRCodeUrl(
            auth()->user()->username,
            $basic->site_title,
            $secret
        );
        return view('admin.control_panel.twofa_control', compact('secret', 'qrCodeUrl'));
    }

    public function twoFaRegenerate()
    {
        $admin = Auth::guard('admin')->user();
        $admin->two_fa_code = null;
        $admin->save();
        session()->flash('success', 'Re-generate Successfully');
        return redirect()->route('admin.twoFa.control');
    }

    private function generateSecretKeyForUser(Admin $user)
    {
        $google2fa = new Google2FA();
        $secret = $google2fa->generateSecretKey();
        $user->update(['two_fa_code' => $secret]);
        return $secret;
    }

    public function twoFaEnable(Request $request)
    {
        $user = Auth::guard('admin')->user();
        $secret = auth()->user()->two_fa_code;
        $google2fa = new Google2FA();
        $valid = $google2fa->verifyKey($secret, $request->code);

        if ($valid) {
            $user['two_fa'] = 1;
            $user['two_fa_verify'] = 1;
            $user->save();

            $browser = new Browser();
            $this->adminMail('TWO_STEP_ENABLED', [
                'action' => 'Enabled',
                'code' => $user->two_fa_code,
                'ip' => request()->ip(),
                'browser' => $browser->browserName() . ', ' . $browser->platformName(),
                'time' => date('d M, Y h:i:s A'),
            ]);

            return back()->with('success', 'Google Authenticator Has Been Enabled.');
        } else {
            return back()->with('error', 'Wrong Verification Code.');
        }
    }

    public function twoFaDisable(Request $request)
    {
        $this->validate($request, [
            'password' => 'required',
        ]);

        if (!Hash::check($request->password, auth()->user()->password)) {
            return back()->with('error', 'Incorrect password. Please try again.');
        }

        Auth::guard('admin')->user()->update([
            'two_fa' => 0,
            'two_fa_verify' => 1,
        ]);
        return back()->with('success', 'Two-step authentication disabled successfully.');
    }

    public function twoFaCheck(Request $request)
    {
        if ($request->method() == 'GET') {
            return view('admin.auth.twofa-verify');
        } elseif ($request->method() == 'POST') {
            $this->validate($request, [
                'code' => 'required',
            ], [
                'code.required' => '2 FA verification code is required',
            ]);

            $user = Auth::guard('admin')->user();
            $secret = $user->two_fa_code;

            $google2fa = new Google2FA();
            $valid = $google2fa->verifyKey($secret,$request->code);

            if ($valid) {
                $user->two_fa_verify = 1;
                $user->save();
                return redirect()->intended(route('admin.dashboard'));
            }
            return back()->with('error', 'Wrong Verification Code.');
        }
    }

}
