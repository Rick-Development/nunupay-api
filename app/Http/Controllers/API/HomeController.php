<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\CountryCurrency;
use App\Models\Deposit;
use App\Models\Gateway;
use App\Models\Language;
use App\Models\NotificationTemplate;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserWallet;
use App\Models\VirtualCardOrder;
use App\Traits\ApiResponse;
use App\Traits\Upload;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;

class HomeController extends Controller
{
    use ApiResponse, Upload;

    public function appConfig()
    {
        try {
            $basic = basicControl();
            $statusLabel = [
                1 => 'Active',
                0 => 'Inactive'
            ];
            $data = [
                'id' => $basic->id,
                'theme' => $basic->theme,
                'site_title' => $basic->site_title,
                'primary_color' => $basic->primary_color,
                'secondary_color' => $basic->secondary_color,
                'time_zone' => $basic->time_zone,
                'base_currency' => $basic->base_currency,
                'currency_symbol' => $basic->currency_symbol,
                'admin_prefix' => $basic->admin_prefix,
                'is_currency_position' => $basic->is_currency_position,
                'paginate' => $basic->paginate,
                
                
                'nine_payment_account_number' => $basic->nine_payment_account_number,
                 'nine_payment_username' => $basic->nine_payment_username, 
                'nine_payment_password' => $basic->nine_payment_password, 
                 'nine_payment_client_id' => $basic->nine_payment_client_id,
                'nine_payment_client_secret' => $basic->nine_payment_client_secret, 
                'nine_payment_api_key' => $basic->nine_payment_api_key,
                'nine_payment_secret_key' => $basic-> nine_payment_secret_key,
                'transfer_to_airpero_charge_in_ngn' => $basic->transfer_to_airpero_charge_in_ngn,
                'bill_payment_charge_in_ngn' => $basic->bill_payment_charge_in_ngn,
                'transfer_to_other_bank_charge_in_ngn' => $basic->transfer_to_other_bank_charge_in_ngn,
                'deposit_charge_in_ngn' => $basic->deposit_charge_in_ngn,
                'payscribe_apikey' => $basic->payscribe_apikey, 
                'card_issuing_amount_in_usd' => $basic->card_issuing_amount_in_usd,
                'card_issuing_charge_in_usd' => $basic->card_issuing_charge_in_usd,
                'card_deposit_charge_in_usd' => $basic->card_deposit_charge_in_usd,
                'card_withdrawal_charge_in_usd' => $basic->card_withdrawal_charge_in_usd,
                
                
                'registration' => $statusLabel[$basic->registration],
                'fraction_number' => $basic->fraction_number,
                'sender_email' => $basic->sender_email,
                'favicon' => getFile($basic->favicon_driver, $basic->favicon),
                'site_logo' => getFile($basic->logo_driver, $basic->logo),
                'admin_logo_light' => getFile($basic->admin_logo_driver, $basic->admin_logo),
                'admin_logo_dark' => getFile($basic->admin_dark_mode_logo_driver, $basic->admin_dark_mode_logo),
                'paymentSuccessUrl' => route('success'),
                'paymentFailedUrl' => route('failed'),
            ];

            return response()->json($this->withSuccess($data));
        } catch (\Exception $e) {
            return response()->json($this->withError($e->getMessage()));
        }
    }

    public function language(Request $request)
    {
        try {
            if (!$request->id) {
                $data['languages'] = Language::select(['id', 'name', 'short_name'])->where('status', 1)->get();
                return response()->json($this->withSuccess($data));
            }
            $lang = Language::where('status', 1)->find($request->id);
            if (!$lang) {
                return response()->json($this->withError('Record not found'));
            }

            $json = file_get_contents(resource_path('lang/') . $lang->short_name . '.json');
            if (empty($json)) {
                return response()->json($this->withError('File Not Found.'));
            }

            $json = json_decode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            return response()->json($this->withSuccess($json));
        } catch (\Exception $e) {
            return response()->json($this->withError($e->getMessage()));
        }
    }

    public function transactionList(Request $request)
    {
        try {
            $search = $request->all();
            $basic = basicControl();
            $userId = Auth::id();
            $transactions = Transaction::where('user_id', $userId)
                ->select('id', 'trx_id', 'amount', 'base_amount', 'charge', 'currency', 'remarks', 'trx_type', 'created_at')
                ->when(isset($search['transaction']), fn($query) => $query->where('trx_id', 'LIKE', '%' . $search['transaction'] . '%')
                )
                ->when(isset($search['start_date'], $search['end_date']) && $search['start_date'] !== '' && $search['end_date'] !== '', fn($query) => $query->whereBetween('created_at', [
                    Carbon::parse($search['start_date']),
                    Carbon::parse($search['end_date'])->endOfDay()
                ])
                )
                ->latest()->paginate($basic->paginate);

            $formattedData = $transactions->map(function ($item) use ($basic) {
                return array_merge($item->toArray(), [
                    'amount' => currencyPositionCalc($item->amount, $item->curr),
                    'charge' => getAmount($item->charge, 2),
                    'trx_type' => $item->trx_type,
                    'created_at' => dateTime($item->created_at),
                ]);
            });
            $data['transactions'] = $formattedData;

            return response()->json($this->withSuccess($data));
        } catch (\Exception $e) {
            return response()->json($this->withError($e->getMessage()));
        }
    }

    public function fundList(Request $request)
    {
        try {
            $search = $request->all();
            $basic = basicControl();
            $userId = Auth::id();
            $funds = Deposit::with(['gateway:id,name'])
                ->where('user_id', $userId)
                ->where('status', '!=', 0)
                ->whereNull('depositable_type')
                ->when(isset($search['transaction']), fn($query) => $query->where('trx_id', 'LIKE', '%' . $search['transaction'] . '%'))
                ->when(isset($search['gateway']), fn($query) => $query->whereHas('gateway', fn($subquery) => $subquery->where('name', 'LIKE', '%' . $search['gateway'] . '%')))
                ->when(isset($search['status']), fn($query) => $query->where('status', '=', $search['status']))
                ->when(isset($search['start_date'], $search['end_date']) && $search['start_date'] !== '' && $search['end_date'] !== '',
                    fn($query) => $query->whereBetween('created_at', [
                        Carbon::parse($search['start_date']),
                        Carbon::parse($search['end_date'])->endOfDay()
                    ])
                )
                ->latest()->paginate($basic->paginate);

            $statusLabels = [
                0 => 'Pending',
                1 => 'Success',
                2 => 'Requested',
                3 => 'Rejected',
            ];
            $formattedData = $funds->map(fn($item) => [
                'id' => $item->id,
                'transactionId' => $item->trx_id,
                'amount' => getAmount($item->amount, 2),
                'charge' => getAmount($item->percentage_charge, 2),
                'payment_method_id' => $item->payment_method_id,
                'payment_method_currency' => $item->payment_method_currency,
                'status' => $statusLabels[$item->status] ?? 'Unknown',
                'created_at' => dateTime($item->created_at),
                'gateway' => $item->gatewayName,
                ...($item->note ? ['note' => $item->note] : []),
            ]);
            $data['funds'] = $formattedData;
            return response()->json($this->withSuccess($data));

        } catch (\Exception $e) {
            return response()->json($this->withError($e->getMessage()));
        }

    }

    public function referList(Request $request)
    {
        try {
            $search = $request->input('search');
            $referUsersQuery = User::where('referral_id', auth()->id());
            if ($search) {
                $referUsersQuery->where(function ($query) use ($search) {
                    $query->where('firstname', 'LIKE', "%$search%")
                        ->orWhere('lastname', 'LIKE', "%$search%")
                        ->orWhere('email', 'LIKE', "%$search%")
                        ->orWhere('country', 'LIKE', "%$search%");
                });
            }
            $referUsers = $referUsersQuery->latest()->paginate(basicControl()->paginate);
            $user = auth()->user();
            $data['refer'] = [
                'url' => URL::to("/register/$user->username"),
                'earnAmount' => getAmount(basicControl()->refer_earn_amount),
                'freeTransfer' => getAmount(basicControl()->refer_free_transfer),
                'referStatus' => (basicControl()->refer_status == 1) ? true : false,
                'referTitle' => basicControl()->refer_title,
            ];
            $formattedData = $referUsers->map(fn($item) => [
                'id' => $item->id,
                'firstname' => $item->firstname,
                'lastname' => $item->lastname,
                'email' => $item->email,
                'phone' => $item->phone,
                'country' => $item->country,
                'address_one' => $item->address_one ?? 'N/A',
                'address_two' => $item->address_two ?? 'N/A',
                'image' => getFile($item->image_driver, $item->image),
                'join_date' => $item->created_at,
            ]);
            $data['referUser'] = $formattedData;
            return response()->json($this->withSuccess($data));

        } catch (\Exception $e) {
            return response()->json($this->withError($e->getMessage()));
        }
    }

    public function gateways()
    {
        try {
            $gateways = Gateway::where('status', 1)->get();
            $formattedData = $gateways->map(fn($item) => [
                'id' => $item->id,
                'code' => $item->name,
                'name' => $item->name,
                'image' => getFile($item->driver, $item->image),
                'description' => $item->description,
                'parameters' => $item->parameters,
                'supported_currency' => $item->supported_currency,
                'receivable_currencies' => $item->receivable_currencies,
                'currencies' => $item->currencies,
                'is_crypto' => ($item->currency_type == 0) ? true : false,
            ]);
            $data['gateways'] = $formattedData;
            return response()->json($this->withSuccess($data));
        } catch (\Exception $e) {
            return response()->json($this->withError($e->getMessage()));
        }
    }

    public function notificationSettings()
    {
        try {
            $notifications = NotificationTemplate::where('notify_for', 0)->where('user_show', 1)->get();
            $statusLabels = [
                '0 = Inactive',
                '1 = Active',
            ];
            $formattedData = $notifications->map(fn($item) => [
                'id' => $item->id,
                'name' => $item->name,
                'key' => $item->template_key,
                'status' => $item->status,
            ]);

            $user = auth()->user();
            $data['statusLabels'] = $statusLabels;
            $data['notification'] = $formattedData;
            $data['userHasPermission'] = $user->notifypermission;

            return response()->json($this->withSuccess($data));
        } catch (\Exception $e) {
            return response()->json($this->withError($e->getMessage()));
        }
    }

    public function notificationPermissionStore(Request $request)
    {
        try {
            $user = Auth::user();
            $rules = [
                'email_key' => 'required',
                'sms_key' => 'required',
                'in_app_key' => 'required',
                'push_key' => 'required',
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json($this->withError(collect($validator->errors())->collapse()));
            }
            $userTemplate = $user->notifypermission()->first();
            if (!$userTemplate) {
                return response()->json($this->withError('Record not found'));
            }
            $default = ['WELCOME_NEW_USER', 'PASSWORD_RESET', 'USER_TRANSFER_OTP', 'VERIFICATION_CODE'];
            $mergeWithDefault = function ($keys) use ($default) {
                return array_values(array_unique(array_merge($keys, $default)));
            };
            $userTemplate->template_email_key = $mergeWithDefault($request->email_key);
            $userTemplate->template_sms_key = $mergeWithDefault($request->sms_key);
            $userTemplate->template_in_app_key = $mergeWithDefault($request->in_app_key);
            $userTemplate->template_push_key = $mergeWithDefault($request->push_key);
            $userTemplate->save();

            return response()->json($this->withSuccess('Notification Permission Updated Successfully.'));
        } catch (\Exception $e) {
            return response()->json($this->withError($e->getMessage()));
        }
    }

    public function pusherConfig()
    {
        try {
            $data['apiKey'] = env('PUSHER_APP_KEY');
            $data['cluster'] = env('PUSHER_APP_CLUSTER');
            $data['channel'] = 'user-notification.' . Auth::id();
            $data['event'] = 'UserNotification';

            $data['chattingChannel'] = 'offer-chat-notification.' . Auth::id();

            return response()->json($this->withSuccess($data));
        } catch (\Exception $e) {
            return response()->json($this->withError($e->getMessage()));
        }
    }

    public function dashboard()
    {
        try {
            $user = auth()->user();
            $basic = basicControl();
            $data = [
                'baseCurrency' => $basic->base_currency,
                'baseCurrencySymbol' => $basic->currency_symbol,
            ];
            $data['wallets'] = UserWallet::with('currency:id,code,rate')->where('user_id', auth()->id())
                ->select('id', 'uuid', 'currency_code', 'balance', 'status', 'default')
                ->get();
            $data['currency'] = CountryCurrency::with('country:id,name,image,image_driver')
                ->select('name', 'code', 'country_id')
                ->whereHas('country', fn($query) => $query->where('receive_from', 1)->orWhere('send_to', 1))
                ->get()
                ->map(function ($currency) {
                    $currency->country->image = $currency->country->getImage();
                    return $currency;
                });

            return response()->json($this->withSuccess($data));
        } catch (\Exception $e) {
            return response()->json($this->withError($e->getMessage()));
        }
    }

    public function logoutFromAllDevices(Request $request)
    {
        try {
            $user = $request->user();
            if ($user) {
                $user->remember_token = null;
                $user->save();
                Auth::guard()->logout();
                return response()->json($this->withSuccess('Logged out of all devices successfully'));
            } else {
                return response()->json($this->withError('Invalid user'));
            }
        } catch (\Exception $e) {
            return response()->json($this->withError($e->getMessage()));
        }
    }

}
