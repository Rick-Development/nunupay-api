<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\UserSystemInfo;
use App\Http\Controllers\Controller;
use App\Models\Content;
use App\Models\Page;
use App\Models\User;
use App\Models\UserLogin;
use App\Rules\PhoneLength;
use App\Traits\Notify;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Auth\Events\Registered;
use Facades\App\Services\Google\GoogleRecaptchaService;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers, Notify;

    protected $maxAttempts = 3;
    protected $decayMinutes = 5;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/user/dashboard';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->theme = template();
        $this->middleware('guest');
    }

    /**
     * Show the application registration form.
     *
     * @return \Illuminate\View\View
     */
    public function showRegistrationForm()
    {
        $data['banner'] = Page::where('name', 'register')->select('page_title', 'breadcrumb_image', 'breadcrumb_image_driver', 'breadcrumb_status')->first();

        $data['content'] = Content::with('contentDetails')->where('name', 'register')->where('type', 'single')->first();

        $basic = basicControl();
        if ($basic->registration == 0) {
            return redirect('/')->with('warning', 'Registration Has Been Disabled.');
        }
        return view(template() . 'auth.register', $data);
    }

    public function sponsor($sponsor)
    {
        $data['banner'] = Page::where('name', 'register')->select('page_title', 'breadcrumb_image', 'breadcrumb_image_driver', 'breadcrumb_status')->first();

        $data['content'] = Content::with('contentDetails')->where('name', 'register')->where('type', 'single')->first();

        $basic = basicControl();
        if ($basic->registration == 0) {
            return back()->with('warning', 'Registration Has Been Disabled.');
        }
        $data['sponsor'] = $sponsor;
        return view(template() . 'auth.register', $data);
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param array $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        $phoneCode = $data['phone_code'];
        $basicControl = basicControl();
        if ($basicControl->strong_password == 0) {
            $rules['password'] = ['required', 'min:6', 'confirmed'];
        } else {
            $rules['password'] = ["required", 'confirmed',
                Password::min(6)->mixedCase()
                    ->letters()
                    ->numbers()
                    ->symbols()
                    ->uncompromised()];
        }
        if ($basicControl->reCaptcha_status_registration) {
            GoogleRecaptchaService::responseRecaptcha($data['g-recaptcha-response']);
            $rules['g-recaptcha-response'] = ['sometimes', 'required'];
        }
        $rules['first_name'] = ['required', 'string', 'max:91'];
        $rules['last_name'] = ['required', 'string', 'max:91'];
        $rules['username'] = ['required', 'alpha_dash', 'min:5', 'unique:users,username'];
        $rules['email'] = ['required', 'string', 'email', 'max:255', 'unique:users,email'];
        $rules['phone'] = ['required', 'string', 'unique:users,phone', new PhoneLength($phoneCode)];
        $rules['phone_code'] = ['required', 'string', 'max:15'];
        $rules['country'] = ['required', 'string', 'max:80'];
        $rules['country_code'] = ['required', 'string', 'max:80'];

        return Validator::make($data, $rules, [
            'first_name.required' => 'First Name Field is required',
            'last_name.required' => 'Last Name Field is required',
            'g-recaptcha-response.required' => 'The reCAPTCHA field is required.',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param array $data
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
        $basic = basicControl();
        $sponsorId = null;

        if (isset($data['sponsor'])) {
            $sponsor = User::where('username', $data['sponsor'])->where('email_verification', 1)->where('sms_verification', 1)->where('status', 1)->first();
            if ($sponsor) {
                $sponsorId = $sponsor->id;
            }
        }

        $softDeletedUser = User::withTrashed()->where('email', $data['email'])->first();
        if ($softDeletedUser) {
            $softDeletedUser->forceDelete();
        }

        $user = User::create([
            'firstname' => $data['first_name'],
            'lastname' => $data['last_name'],
            'username' => $data['username'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'phone_code' => $data['phone_code'],
            'country' => $data['country'],
            'country_code' => strtoupper($data['country_code']),
            'password' => Hash::make($data['password']),
            'email_verification' => ($basic->email_verification) ? 0 : 1,
            'sms_verification' => ($basic->sms_verification) ? 0 : 1,
            'remember_token' => Str::random(10),
            'referral_id' => $sponsorId,
            'refer_bonus' => $basic->refer_status == 1 ? 0 : 1,
        ]);

        return $user;
    }

    public function register(Request $request)
    {
        $this->validator($request->all())->validate();

        $user = $this->create($request->all());

        $this->guard()->login($user);

        if ($response = $this->registered($request, $user)) {
            return $response;
        }
        if ($request->ajax()) {
            return route('user.home');
        }

        return $request->wantsJson()
            ? new JsonResponse([], 201)
            : redirect($this->redirectPath());
    }

    protected function registered(Request $request, $user)
    {
        $user->last_login = Carbon::now();
        $user->last_seen = Carbon::now();
        $user->two_fa_verify = ($user->two_fa == 1) ? 0 : 1;
        $user->save();

        $info = @json_decode(json_encode(getIpInfo()), true);
        $ul['user_id'] = $user->id;

        $ul['longitude'] = (!empty(@$info['long'])) ? implode(',', $info['long']) : null;
        $ul['latitude'] = (!empty(@$info['lat'])) ? implode(',', $info['lat']) : null;
        $ul['country_code'] = (!empty(@$info['code'])) ? implode(',', $info['code']) : null;
        $ul['location'] = (!empty(@$info['city'])) ? implode(',', $info['city']) . (" - " . @implode(',', @$info['area']) . "- ") . @implode(',', $info['country']) . (" - " . @implode(',', $info['code']) . " ") : null;
        $ul['country'] = (!empty(@$info['country'])) ? @implode(',', @$info['country']) : null;

        $ul['ip_address'] = UserSystemInfo::get_ip();
        $ul['browser'] = UserSystemInfo::get_browsers();
        $ul['os'] = UserSystemInfo::get_os();
        $ul['get_device'] = UserSystemInfo::get_device();
        UserLogin::create($ul);

        $this->sendWelcomeEmail($user);
        event(new Registered($user));
    }


    protected function guard()
    {
        return Auth::guard();
    }

}
