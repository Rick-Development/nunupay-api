<?php

namespace App\Http\Controllers\User;

use App\Helpers\UserSystemInfo;
use App\Http\Controllers\Controller;
use App\Jobs\UserAllRecordDeleteJob;
use App\Models\CountryCurrency;
use App\Models\Deposit;
use App\Models\Gateway;
use App\Models\Kyc;
use App\Models\Language;
use App\Models\MoneyTransfer;
use App\Models\NotificationTemplate;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserKyc;
use App\Models\UserWallet;
use App\Models\VirtualCardOrder;
use App\Rules\PhoneLength;
use App\Traits\Notify;
use App\Traits\Upload;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use PragmaRX\Google2FA\Google2FA;


class HomeController extends Controller
{
    use Upload,Notify;

    public function __construct()
    {
        $this->middleware(['auth']);
        $this->middleware(function ($request, $next) {
            $this->user = auth()->user();
            return $next($request);
        });
        $this->theme = template();
    }

    public function saveToken(Request $request)
    {
        Auth::user()
            ->fireBaseToken()
            ->create([
                'token' => $request->token,
            ]);
        return response()->json([
            'msg' => 'token saved successfully.',
        ]);
    }

    public function index()
    {
        $data['user'] = $this->user;
        $data['firebaseNotify'] = config('firebase');
        $data['wallets'] = UserWallet::where('user_id', auth()->id())->get();

        $data['currency'] = CountryCurrency::with('country:id,name,image,image_driver')
            ->whereHas('country', fn($query) => $query->where('receive_from', 1)->orWhere('send_to', 1))
            ->get();

        $data['transactions'] = Transaction::query()
            ->where('user_id', $this->user->id)
            ->latest()->get();

        $startOfYear = Carbon::now()->startOfYear();
        $endOfYear = Carbon::now()->endOfYear();
        $funds = Deposit::query()
            ->where('user_id', $this->user->id)
            ->whereNull('depositable_type')
            ->whereBetween('created_at', [$startOfYear, $endOfYear])
            ->latest()
            ->get();
        $monthlyDeposits = $funds->groupBy(function ($fund) {
            return Carbon::parse($fund->created_at)->format('m');
        })->map(function ($group) {
            return $group->sum('payable_amount_in_base_currency');
        });
        $monthsInYear = range(1, 12);
        $monthlyDeposits = collect($monthsInYear)->mapWithKeys(function ($month) use ($monthlyDeposits) {
            $monthName = date('M', mktime(0, 0, 0, $month, 1));
            $amount = $monthlyDeposits->get(str_pad($month, 2, '0', STR_PAD_LEFT), 0);
            return [$monthName => $amount];
        });
        $data['monthlyDeposits'] = $monthlyDeposits;

        $currentMonth = Carbon::now()->format('M');
        $data['currentMonthDeposit'] = $monthlyDeposits->get($currentMonth);
        $sendMoney = Deposit::query()
            ->where('user_id', $this->user->id)
            ->where('status', 1)
            ->where('depositable_type',MoneyTransfer::class)
            ->whereMonth('created_at', Carbon::now()->subMonth()->month)
            ->latest()
            ->get();
        $data['lastMonthSendMoney'] = $sendMoney->sum('payable_amount_in_base_currency');
        $data['defaultWallet'] = UserWallet::where('user_id', auth()->id())
            ->where('default', 1)
            ->firstOr(function () {
                return UserWallet::where('user_id', auth()->id())->first();
            });
        $lastMonthTrx = Transaction::query()
            ->where('user_id', $this->user->id)
            ->whereMonth('created_at', now()->month)
            ->latest()
            ->get();
        $data['currentMonthTransactions'] = $lastMonthTrx->sum('base_amount');


        $currentYear = now()->format('Y');
        $transactions = Transaction::where('user_id', $this->user->id)
            ->where('created_at', '>=', Carbon::now()->subMonths(12))
            ->select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('YEAR(created_at) as year'), 'trx_type', 'base_amount'
            )
            ->whereYear('created_at', $currentYear)
            ->orderBy('month', 'asc')
            ->get();
        for ($month = 1; $month <= 9; $month++) {
            $transactionsOfMonth = $transactions->where('month', $month);
            $totalTrx = $transactionsOfMonth->sum('base_amount');
            $data['totalTrx'][] = getAmount($totalTrx);
            $totalSend = $transactionsOfMonth->where('trx_type', '-')->sum('base_amount');
            $data['totalSend'][] = getAmount($totalSend);
            $totalReceive = $transactionsOfMonth->where('trx_type', '+')->sum('base_amount');
            $data['totalReceive'][] = getAmount($totalReceive);
            $data['months'][] = date('M', mktime(0, 0, 0, $month, 1));
        }

        return view($this->theme . 'user.dashboard', $data);
    }


    public function settings()
    {
        $user = $this->user;
        $userNotificationPermission = $user->notifypermission;
        $data['notification'] = NotificationTemplate::where('notify_for', 0)->where('user_show',1)->get();
        return view($this->theme . 'user.profile.settings', $data, compact('user', 'userNotificationPermission'));
    }
    public function notificationPermissionStore(Request $request)
    {
        try {
            $default = ['WELCOME_NEW_USER', 'PASSWORD_RESET', 'USER_TRANSFER_OTP', 'VERIFICATION_CODE'];
            $user = $this->user;
            $userTemplate = $user->notifypermission()->firstOrFail();
            $mergeWithDefault = function($keys) use ($default) {
                return array_values(array_unique(array_merge($keys ?? [], $default)));
            };
            $userTemplate->template_email_key = $mergeWithDefault($request->email_key);
            $userTemplate->template_sms_key = $mergeWithDefault($request->sms_key);
            $userTemplate->template_in_app_key = $mergeWithDefault($request->in_app_key);
            $userTemplate->template_push_key = $mergeWithDefault($request->push_key);
            $userTemplate->save();
            return back()->with('success', 'Notification Permission Updated Successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function earn()
    {
        $user = $this->user;
        $url = URL::to("/register/$user->username");
        $referUsers = User::where('referral_id', $user->id)->latest()->get();
        $data = [
            'user' => $user,
            'referUser' => $referUsers,
            'url' => $url,
        ];
        return view($this->theme . 'user.refer.earn', $data);
    }


    public function referList(Request $request)
    {
        $search = $request->input('search');
        $user = $this->user;
        $referUsersQuery = User::where('referral_id', $user->id);
        if ($search) {
            $referUsersQuery->where(function ($query) use ($search) {
                $query->where('firstname', 'LIKE', "%$search%")
                    ->orWhere('lastname', 'LIKE', "%$search%")
                    ->orWhere('email', 'LIKE', "%$search%")
                    ->orWhere('country', 'LIKE', "%$search%");
            });
        }
        $referUsers = $referUsersQuery->latest()->paginate(basicControl()->paginate);
        $data = [
            'user' => $user,
            'referUser' => $referUsers,
        ];
        return view($this->theme . 'user.refer.referList', $data);
    }

    public function referDetails($id)
    {
        $userId = auth()->user()->id;
        $referUser = User::where('referral_id',$userId)->where('id',$id)->firstOrFail();

        return view($this->theme.'user.refer.referDetails', compact('referUser'));
    }

    public function profile()
    {
        $data['user'] = $this->user;
        $data['languages'] = Language::all();
        $data['kyc'] = Kyc::where('status', 1)->get();
        $data['timeZones'] = timezone_identifiers_list();
        return view($this->theme . 'user.profile.profile', $data);
    }

    public function profileUpdateImage(Request $request)
    {
        $rules = [
            'image' => 'required|image|mimes:jpg,png,jpeg|max:4096',
        ];
        $message = [
            'image.max' => 'Maximum 4MB image Allowed!'
        ];
        $validator = Validator::make($request->all(), $rules, $message);
        if ($validator->fails()) {
            return back()->withInput()->withErrors($validator);
        }

        $user = $this->user;
        if ($request->hasFile('image')) {
            $image = $this->fileUpload($request->image, config('filelocation.userProfile.path'), null,null,'webp', 80, $user->image, $user->image_driver);
            if ($image) {
                $profileImage = $image['path'];
                $ImageDriver = $image['driver'];
            }
        }
        $user->image = $profileImage ?? $user->image;
        $user->image_driver = $ImageDriver ?? $user->image_driver;
        $user->save();
        return back()->with('success', 'Updated Successfully.');
    }

    public function profileUpdate(Request $request)
    {
        $languages = Language::pluck('id')->toArray();

        $req = $request->except('_method', '_token');
        $user = $this->user;
        $phoneCode = $request->input('phone_code');

        $rules = [
            'first_name' => 'required|string|min:1',
            'last_name' => 'required|string|min:1',
            'email' => 'email:rfc,dns',
            'phone_code' => 'required|string|max:15',
            'username' => "sometimes|required|alpha_dash|min:5|unique:users,username," . $user->id,
            'address_one' => 'required',
            'language_id' => Rule::in($languages),
            'phone' => ['required', 'string', "unique:users,phone, $user->id",new PhoneLength($phoneCode)],
        ];

        $message = [
            'firstname.required' => 'First Name field is required',
            'lastname.required' => 'Last Name field is required',
        ];

        $validator = Validator::make($req, $rules, $message);
        if ($validator->fails()) {
            $validator->errors()->add('profile', '1');
            return back()->withErrors($validator)->withInput();
        }

        $user->language_id = $req['language_id'];
        $user->firstname = $req['first_name'];
        $user->lastname = $req['last_name'];
        $user->email = $req['email'];
        $user->phone = $req['phone'];
        $user->phone_code = $req['phone_code'];
        $user->country_code = Str::upper($req['country_code']);
        $user->country = $req['country'];
        $user->username = $req['username'];
        $user->address_one = $req['address_one'];
        $user->address_two = $req['address_two'];
        $user->save();
        return back()->with('success', 'Updated Successfully.');
    }

    public function updatePassword(Request $request)
    {
        $rules = [
            'current_password' => "required",
            'password' => "required|min:5|confirmed",
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $validator->errors()->add('password', '1');
            return back()->withErrors($validator)->withInput();
        }
        $user = $this->user;
        try {
            if (Hash::check($request->current_password, $user->password)) {
                $user->password = bcrypt($request->password);
                $user->save();
                return back()->with('success', 'Password Changes successfully.');
            } else {
                throw new \Exception('Current password did not match');
            }
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function verify($id){

        $user = auth()->user();
        $data['kyc'] = Kyc::where('status', 1)->get();
        $data['item'] = Kyc::where('status', 1)->findOrFail($id);
        $data['userKyc'] = UserKyc::where('kyc_id', $id)
            ->where('user_id', $user->id)
            ->first();
        return view($this->theme.'user.profile.verify',$data);
    }


    public function kycVerificationSubmit(Request $request)
    {
        $kyc = Kyc::where('id', $request->type)->where('status', 1)->firstOrFail();
        $params = $kyc->input_form;
        $reqData = $request->except('_token', '_method');
        $rules = [];
        if ($params !== null) {
            foreach ($params as $key => $cus) {
                $rules[$key] = [$cus->validation == 'required' ? $cus->validation : 'nullable'];
                if ($cus->type === 'file') {
                    $rules[$key][] = 'image';
                    $rules[$key][] = 'mimes:jpeg,jpg,png';
                    $rules[$key][] = 'max:2048';
                } elseif ($cus->type === 'text') {
                    $rules[$key][] = 'max:191';
                } elseif ($cus->type === 'number') {
                    $rules[$key][] = 'numeric';
                } elseif ($cus->type === 'textarea') {
                    $rules[$key][] = 'min:3';
                    $rules[$key][] = 'max:300';
                }
            }
        }

        $validator = Validator::make($reqData, $rules);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $reqField = [];
        foreach ($request->except('_token', '_method', 'type') as $k => $v) {
            foreach ($params as $inKey => $inVal) {
                if ($k == $inKey) {
                    if ($inVal->type == 'file' && $request->hasFile($inKey)) {
                        try {
                            $file = $this->fileUpload($request[$inKey], config('filelocation.kyc.path'),null,null,'webp', 80);
                            $reqField[$inKey] = [
                                'field_name' => $inVal->field_name,
                                'field_label' => $inVal->field_label,
                                'field_value' => $file['path'],
                                'field_driver' => $file['driver'],
                                'validation' => $inVal->validation,
                                'type' => $inVal->type,
                            ];
                        } catch (\Exception $exp) {
                            return back()->withInput()->with('error','Could not upload your ' . $inKey);
                        }
                    } else {
                        $reqField[$inKey] = [
                            'field_name' => $inVal->field_name,
                            'field_label' => $inVal->field_label,
                            'validation' => $inVal->validation,
                            'field_value' => $v,
                            'type' => $inVal->type,
                        ];
                    }
                }
            }
        }

        UserKyc::updateOrCreate(
            [
                'user_id' => auth()->id(),
                'kyc_id' => $kyc->id,
            ],
            [
                'user_id' => auth()->id(),
                'kyc_id' => $kyc->id,
                'kyc_type' => $kyc->name,
                'kyc_info' => $reqField,
                'status' => 0
            ]
        );

        return back()->with('success', 'KYC Submitted Successfully');
    }

    public function addFund(Request $request)
    {
        $card = $request->has('card')
            ? VirtualCardOrder::find($request->card)
            : null;

        if ($request->has('card') && !$card) {
            return back()->with('error', __('Card not found.'));
        }

        session()->forget('balance_error');
        $data['basic'] = basicControl();
        $data['gateways'] = Gateway::where('status', 1)->orderBy('sort_by', 'ASC')->get();
        $data['wallets'] = UserWallet::where('user_id',auth()->id())->where('status', 1)->get();
        $data['card'] = $card;
        return view($this->theme . 'user.fund.deposit', $data);
    }

    public function fund(Request $request)
    {
        $search = $request->all();
        $userId = Auth::id();
        $funds = Deposit::query()
            ->where('user_id', $userId)
            ->where('status', '!=' ,0)
            ->whereNull('depositable_type')
            ->when(isset($search['search']), fn ($query) =>
                $query->where('trx_id', 'LIKE', '%' . $search['search'] . '%')
            )
            ->when(isset($search['status']) && $search['status'] !== '', fn ($query) =>
                $query->where('status', $search['status'])
            )
            ->when(isset($search['start_date'], $search['end_date']) && $search['start_date'] !== '' && $search['end_date'] !== '', fn ($query) =>
                $query->whereBetween('created_at', [
                    Carbon::parse($search['start_date']),
                    Carbon::parse($search['end_date'])->endOfDay()
                ])
            )
            ->latest()->paginate(20);

        $groupedFunds = $funds->getCollection()->groupBy(function ($fund) {
            return Carbon::parse($fund->created_at)->format('Y-m-d');
        });

        $funds->setCollection($groupedFunds->flatten(1));

        return view($this->theme . 'user.fund.list', compact('funds','groupedFunds'));

    }

    public function allTransaction(Request $request)
    {
        $search = $request->all();
        $userId = Auth::id();
        $transactions = Transaction::query()
            ->where('user_id', $userId)
            ->when(isset($search['search']), fn ($query) =>
                $query->where('trx_id', 'LIKE', '%' . $search['search'] . '%')
                    ->orWhere('remarks', 'LIKE', '%' . $search['search'] . '%')
            )
            ->when(isset($search['start_date'], $search['end_date']) && $search['start_date'] !== '' && $search['end_date'] !== '', fn ($query) =>
                $query->whereBetween('created_at', [
                    Carbon::parse($search['start_date']),
                    Carbon::parse($search['end_date'])->endOfDay()
                ])
            )
            ->latest()->paginate(20);

        $groupedTransactions = $transactions->getCollection()->groupBy(function($date) {
            return Carbon::parse($date->created_at)->format('Y-m-d');
        });

        $transactions->setCollection($groupedTransactions->flatten(1));

        return view($this->theme . 'user.transaction.list', compact('transactions','groupedTransactions'));
    }

    public function twoStepSecurity()
    {
        $basic = basicControl();
        $user = auth()->user();

        $google2fa = new Google2FA();
        $secret = $user->two_fa_code ?? $this->generateSecretKeyForUser($user);

        $qrCodeUrl = $google2fa->getQRCodeUrl(
            auth()->user()->username,
            $basic->site_title,
            $secret
        );
        return view($this->theme . 'user.twoFA.index', compact('secret', 'qrCodeUrl'));
    }

    private function generateSecretKeyForUser(User $user)
    {
        $google2fa = new Google2FA();
        $secret = $google2fa->generateSecretKey();
        $user->update(['two_fa_code' => $secret]);
        return $secret;
    }

    public function twoStepEnable(Request $request)
    {
        $user = $this->user;
        $secret = $user->two_fa_code;

        $google2fa = new Google2FA();
        $valid = $google2fa->verifyKey($secret,$request->code);
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
            return back()->with('success', 'Google Authenticator Has Been Enabled.');
        }else {
            return back()->with('error', 'Wrong Verification Code.');
        }
    }

    public function twoStepDisable(Request $request)
    {
        $this->validate($request, [
            'password' => 'required',
        ]);
        if (!Hash::check($request->password, $this->user->password)) {
            return back()->with('error', 'Incorrect password. Please try again.');
        }
        $this->user->update([
            'two_fa' => 0,
            'two_fa_verify' => 1,
        ]);
        return to_route('user.dashboard')->with('success', 'Two-step authentication disabled successfully.');
    }

    public function updateEmail(Request $request, User $user)
    {
        if ($user->id !== Auth::id()) {
            return back()->with(['error' => 'You do not have permission to update this user.']);
        }
        $request->validate([
            'email' => 'required|email|unique:users,email',
        ]);
        $data = $request->only('email');
        $user->update($data);
        return back()->with('success', 'Your Email updated successfully.');
    }

    public function deleteAccount()
    {
        $user = $this->user;
        if ($user) {
            UserAllRecordDeleteJob::dispatch($user);
            $user->delete();
            Auth::logout();
            return redirect(url('/'))->with('success', 'Your account has been deleted successfully.');
        } else {
            return back()->withErrors(['error' => 'User not found.']);
        }
    }

    public function logoutFromAllDevices(Request $request)
    {
        $user = $request->user();
        if ($user) {
            $user->remember_token = null;
            $user->save();
            Auth::guard()->logout();
            return redirect(route('login'))->with('success', 'Logged out of all devices successfully.');
        } else {
            return back()->with('error', 'Invalid user.');
        }
    }


}
