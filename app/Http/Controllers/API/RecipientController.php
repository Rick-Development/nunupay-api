<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Banks;
use App\Models\CountryService;
use App\Models\Recipient;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RecipientController extends Controller
{
    use ApiResponse;

    public function recipientList(Request $request)
    {
        try {
            $userId = auth()->id();
            $search = $request->input('search');
            $data = [];

            if ($search && str_starts_with($search, '@')) {
                $searchUsername = ltrim($search, '@');
                $user = User::where('username', $searchUsername)->first();
                $data['users'] = $user ? collect([$user]) : collect();
            } else {
                $query = Recipient::where('user_id', $userId)
                    ->when($search, function ($query, $search) {
                        $query->where(function ($q) use ($search) {
                            $q->where('name', 'LIKE', "%$search%")
                                ->orWhere('email', 'LIKE', "%$search%")
                                ->orWhereHas('currency.country', function ($sq) use ($search) {
                                    $sq->where('name', 'LIKE', "%$search%")
                                        ->orWhere('code', 'LIKE', "%$search%");
                                })
                                ->orWhereHas('service', function ($sq) use ($search) {
                                    $sq->where('name', 'LIKE', "%$search%");
                                });
                        });
                    });
                $recipients = $query->latest()->paginate(20);
                $formattedData = $recipients->map(fn($item) => [
                    'id' => $item->id,
                    'user_id' => $item->user_id,
                    'uuid' => $item->uuid,
                    'type' => ($item->type == 0) ? 'my-self' : 'others',
                    'name' => $item->name,
                    'email' => $item->email,
                    'currency_code' => optional($item->currency)->code,
                    'currency_name' => optional($item->currency)->name,
                    'service_name' => optional($item->service)->name,
                    'country_image' => $item->currency?->country?->getImage(),
                    'r_user_id' => $item->r_user_id,
                    'r_user_image' => $item->recipientUser?->getImage(),
                    'favicon' => getFile(basicControl()->favicon_driver, basicControl()->favicon),
                ]);
                $data['recipients'] = $formattedData;
            }

            return response()->json($this->withSuccess($data));
        } catch (\Exception $e) {
            return response()->json($this->withError('Something went wrong: ' . $e->getMessage()), 500);
        }
    }


    public function recipientDetails($uuid)
    {
        try {
            $recipient = Recipient::where('user_id', auth()->id())->where('uuid', $uuid)->first();
            if (!$recipient) {
                return response()->json($this->withError('Invalid Recipient'));
            }
            $bankInfoArray = [];
            foreach ($recipient->bank_info ?? [] as $key => $bank) {
                $bankInfoArray[snake2Title($key)] = $bank;
            }
            $formattedData = [
                'id' => $recipient->id,
                'type' => ($recipient->type == 0) ? 'my-self' : 'others',
                'name' => $recipient->name,
                'currency_code' => $recipient->currency?->code,
                'currency_name' => optional($recipient->currency)->name,
                'country_name' => optional(optional($recipient->currency)->country)->name,
                'country_image' => $recipient->currency?->country?->getImage(),
                'service_name' => optional($recipient->service)->name,
                'bank_name' => optional($recipient->bank)->name,
                'bank_info' => $bankInfoArray ,
            ];
            $data['recipient'] = $formattedData;
            return response()->json($this->withSuccess($data));
        } catch (\Exception $e) {
            return response()->json($this->withError($e->getMessage()));
        }
    }


    public function updateName(Request $request, Recipient $recipient)
    {
        try {
            if ($recipient->user_id != auth()->user()->id) {
                return response()->json($this->withError('You are not authorized to update this recipient'));
            }
            $rules = [
                'name' => 'required|string|max:20',
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json($this->withError(collect($validator->errors())->collapse()));
            }
            $recipient->update($request->only('name'));
            $formattedData = [
                'id' => $recipient->id,
                'name' => $recipient->name,
            ];
            $data['recipient'] = $formattedData;
            return response()->json($this->withSuccess("Recipient Name Updated"));
        } catch (\Exception $e) {
            return response()->json($this->withError($e->getMessage()));
        }
    }

    public function destroy(Recipient $recipient)
    {
        if (!$recipient) {
            return response()->json($this->withError('Recipient Not Found'));
        }
        try {
            $recipient->delete();
            return response()->json($this->withSuccess("Recipient Deleted Successfully"));
        } catch (\Exception $e) {
            return response()->json($this->withError($e->getMessage()));
        }
    }

    public function store(Request $request)
    {
        try {
            $baseRules = [
                'currency_id' => 'required|integer|exists:country_currency,id',
                'bank_id' => 'required|integer|exists:country_banks,id',
                'service_id' => 'required|integer|exists:country_services,id',
                'email' => 'required|email|max:255',
                'name' => 'required|string|max:255',
                'type' => 'required|integer|in:0,1',
            ];
            $bank = Banks::find($request->bank_id);
            if (!$bank) {
                return response()->json($this->withError('bank_id not found'));
            }
            $formField = optional($bank)->services_form;
            $additionalRules = $this->generateValidationRules($formField);
            $rules = array_merge($baseRules, $additionalRules);
            $requestData = $request->except('_token', 'transfer');
            $validator = Validator::make($requestData, $rules);

            if ($validator->fails()) {
                return response()->json($this->withError(collect($validator->errors())->collapse()));
            }
            $excludeFields = ["currency_id", "bank_id", "email", "name", 'service_id', 'type'];
            $bankInfoFields = array_diff_key($requestData, array_flip($excludeFields));
            $data = array_diff_key($requestData, $bankInfoFields);
            $data['bank_info'] = $bankInfoFields;

            $recipient = Recipient::create($data);

            return response()->json($this->withSuccess("Recipient created successfully"));
        } catch (\Exception $e) {
            return response()->json($this->withError($e->getMessage()));
        }
    }

    private function generateValidationRules($formField)
    {
        $rules = [];
        foreach ($formField as $field) {
            $fieldName = $field->field_name;
            $baseRule = [$field->validation == 'required' ? 'required' : 'nullable'];

            switch ($field->type) {
                case 'file':
                    $rules[$fieldName] = array_merge($baseRule, ['image', 'mimes:jpeg,jpg,png', 'max:2048']);
                    break;
                case 'text':
                    $rules[$fieldName] = array_merge($baseRule, ['max:191']);
                    break;
                case 'number':
                    $rules[$fieldName] = array_merge($baseRule, ['numeric']);
                    break;
                case 'textarea':
                    $rules[$fieldName] = array_merge($baseRule, ['min:3', 'max:300']);
                    break;
            }
        }
        return $rules;
    }

    public function getServices(Request $request)
    {
        $rules = [
            'country_id' => 'required|exists:countries,id',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json($this->withError(collect($validator->errors())->collapse()));
        }
        $countryId = $request->query('country_id');
        $services = CountryService::where('status', 1)
            ->whereIn('id', function ($query) use ($countryId) {
                $query->from('country_banks')
                    ->where('country_id', $countryId)
                    ->select('service_id');
            })->select('id', 'name')
            ->with(['banks' => function ($query) use ($countryId) {
                $query->where('country_id', $countryId);
            }])
            ->get();
        return response()->json($this->withSuccess(['services' => $services]));
    }

    public function getBank(Request $request)
    {
        $rules = [
            'service_id' => 'required|exists:country_services,id',
            'country_id' => 'required|exists:countries,id',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json($this->withError(collect($validator->errors())->collapse()));
        }
        $serviceId = $request->input('service_id');
        $countryId = $request->input('country_id');
        $banks = Banks::where('service_id', $serviceId)->where('country_id', $countryId)->where('status', 1)->get();

        return response()->json($this->withSuccess(['banks' => $banks]));
    }

    public function generateFields(Request $request)
    {
        $rules = [
            'bank_id' => 'required|exists:country_banks,id',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json($this->withError(collect($validator->errors())->collapse()));
        }
        $selectedBank = Banks::findOrFail($request->bank_id);
        $serviceFormFields = $selectedBank->services_form;

        return response()->json($this->withSuccess($serviceFormFields));
    }

    public function userStore(Request $request)
    {
        $rules = ['r_user_id' => 'required|integer|exists:users,id'];
        $msg = [
            'r_user_id.required' => 'Recipient user id required',
            'r_user_id.exists' => 'Invalid Recipient user id',
            'r_user_id.integer' => 'Recipient user id have to be a integer value',
        ];
        $req = $request->except('_token', 'transfer');
        $validate = Validator::make($req, $rules, $msg);
        if ($validate->fails()) {
            return response()->json($this->withError(collect($validate->errors())->collapse()));
        }

        try {
            $exists = Recipient::where('user_id', auth()->id())
                ->where('r_user_id', $request->r_user_id)
                ->exists();
            if ($exists) {
                return response()->json($this->withError('This user is already added as a recipient'), 409);
            }
            $newUser = User::findOrFail($request->r_user_id);
            $data = [
                'r_user_id' => $request->r_user_id,
                'name' => $newUser->fullname(),
                'email' => $newUser->email,
            ];
            $recipient = Recipient::create($data);

            $trackId = $recipient->uuid;
            $response = [
                'message' => 'Recipient Created successfully',
                'recipient' => [
                    'uuid' => $recipient->uuid,
                    'name' => $recipient->name,
                    'email' => $recipient->email,
                ],
            ];

            if (session('transfer')) {
                $response['redirect_url'] = route('user.transferReview', ['uuid' => $trackId]);
                $response['message'] = 'Recipient Added.';
            } else {
                $response['redirect_url'] = route('user.recipient.index');
            }

            return response()->json($this->withSuccess($response));
        } catch (\Exception $e) {
            return response()->json($this->withError('Something went wrong: ' . $e->getMessage()));
        }
    }


}
