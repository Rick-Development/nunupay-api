<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Jobs\UserAllRecordDeleteJob;
use App\Models\Kyc;
use App\Models\Language;
use App\Models\User;
use App\Models\UserKyc;
use App\Rules\PhoneLength;
use App\Traits\ApiResponse;
use App\Traits\Upload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    use ApiResponse, Upload;

    public function profile()
    {
        try {
            $user = User::where('id', auth()->id())->first();

            $formattedData = [
                'id' => $user->id,
                'firstname' => $user->firstname,
                'lastname' => $user->lastname,
                'username' => $user->username,
                'user_pin' => $user->user_pin,
                'email' => $user->email,
                'phone' => $user->phone,
                'language_id' => $user->language_id,
                'Language' => $user->language?->name,
                'userJoinDate' => dateTime($user->created_at),
                'address_one' => $user->address_one ?? null,
                'address_two' => $user->address_two ?? null,
                'image' => getFile($user->image_driver, $user->image),
                'phone_code' => $user->phone_code ?? null,
                'country' => $user->country ?? null,
                'country_code' => $user->country_code ?? null,
                'kyc' => $user->kyc ?? 0,
            ];
            $data['profile'] = $formattedData;

            return response()->json($this->withSuccess($data));
        } catch (\Exception $e) {
            return response()->json($this->withError($e->getMessage()));
        }
    }
    
    

    public function profileUpdateImage(Request $request)
    {
        try {
            $rules = [
                'image' => 'required|image|mimes:jpg,png,jpeg|max:4096',
            ];
            $message = [
                'image.max' => 'Maximum 4MB image Allowed!'
            ];
            $validator = Validator::make($request->all(), $rules, $message);
            if ($validator->fails()) {
                return response()->json($this->withError(collect($validator->errors())->collapse()));
            }
            $user = Auth::user();
            if ($request->hasFile('image')) {
                $image = $this->fileUpload($request->image, config('filelocation.userProfile.path'), null, null, 'webp', 80, $user->image, $user->image_driver);
                if ($image) {
                    $profileImage = $image['path'];
                    $ImageDriver = $image['driver'];
                }
            }
            $user->image = $profileImage ?? $user->image;
            $user->image_driver = $ImageDriver ?? $user->image_driver;
            $user->save();

            return response()->json($this->withSuccess('Updated Successfully'));
        } catch (\Exception $e) {
            return response()->json($this->withError($e->getMessage()));
        }
    }

    private function getMaxPhoneLength($phoneCode)
    {
        $maxPhoneLength = 15;
        foreach (config('country') as $country) {
            if ($country['phone_code'] == $phoneCode) {
                $maxPhoneLength = $country['phoneLength'];
                break;
            }
        }
        return $maxPhoneLength;
    }

    public function profileUpdate(Request $request)
    {
        try {
            $req = $request->except('_method', '_token');
            $user = Auth::user();
            $phoneCode = $request->input('phone_code');
            $rules = [
                'first_name' => 'required|string|min:1',
                'last_name' => 'required|string|min:1',
                'username' => "sometimes|required|alpha_dash|min:5|unique:users,username," . $user->id,
                'language_id' => 'required|integer',
                'address_one' => 'required',
                'country' => 'nullable|string',
                'country_code' => 'nullable|string',
                'phone_code' => 'required|numeric',
                'phone' => ['required', 'numeric', "unique:users,phone, $user->id", new PhoneLength($phoneCode)],
            ];

            $message = [
                'firstname.required' => 'First Name field is required',
                'lastname.required' => 'Last Name field is required',
            ];
            $validator = Validator::make($req, $rules, $message);
            if ($validator->fails()) {
                return response()->json($this->withError(collect($validator->errors())->collapse()));
            }
            $user->firstname = $req['first_name'];
            $user->lastname = $req['last_name'];
            $user->phone = $req['phone'];
            $user->username = $req['username'];
            $user->language_id = $req['language_id'];
            $user->address_one = $req['address_one'];
            $user->save();

            return response()->json($this->withSuccess("Updated Successfully"));
        } catch (\Exception $e) {
            return response()->json($this->withError($e->getMessage()));
        }
    }
    
    
 public function updatePin(Request $request)
{
    try {
        // Exclude unnecessary fields
        $req = $request->except('_method', '_token');
        $user = Auth::user();

        // Validation rules and messages
        $rules = [
            'user_pin' => 'required|digits:4',
        ];
        $messages = [
            'user_pin.required' => 'The PIN is required.',
            'user_pin.digits' => 'The PIN must be exactly 4 digits.',
        ];

        // Validate request
        $validator = Validator::make($req, $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => collect($validator->errors())->collapse(),
            ], 422); // Use proper status codes
        }

        // Update user PIN
        $user->user_pin = $req['user_pin'];
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'PIN updated successfully.',
        ]);
    } catch (\Exception $e) {
        // Log exception for debugging
        \Log::error('Error updating PIN: ' . $e->getMessage());

        return response()->json([
            'status' => 'error',
            'message' => 'An error occurred while updating the PIN. Please try again.',
        ], 500);
 
}
}

public function getPin()
{
    try {
        // Get the authenticated user
        $user = Auth::user();

        // Check if the user has a PIN
        if (!$user->user_pin) {
            return response()->json([
                'status' => 'error',
                'message' => 'No PIN found for this user.',
            ], 404);
        }

        // Return the user's PIN (optional: you may want to encrypt or mask it)
        return response()->json([
            'status' => 'success',
            'pin' => $user->user_pin,
        ]);
    } catch (\Exception $e) {
        // Log exception for debugging
        \Log::error('Error fetching PIN: ' . $e->getMessage());

        return response()->json([
            'status' => 'error',
            'message' => 'An error occurred while fetching the PIN. Please try again.',
        ], 500);
    }
}

    

    public function updateEmail(Request $request, User $user)
    {
        try {
            if ($user->id !== Auth::id()) {
                return response()->json($this->withError('You do not have permission to update this user.'));
            }
            $request->validate([
                'email' => 'required|email|unique:users,email',
            ]);
            $data = $request->only('email');
            $user->update($data);

            return response()->json($this->withSuccess('Email Updated Successfully'));
        } catch (\Exception $e) {
            return response()->json($this->withError($e->getMessage()));
        }
    }

    public function updatePassword(Request $request)
    {
        try {
            $user = Auth::user();
            $rules = [
                'current_password' => "required",
                'password' => "required|min:6|confirmed",
                'password_confirmation' => "required|min:6",
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json($this->withError(collect($validator->errors())->collapse()));
            }

            if (Hash::check($request->current_password, $user->password)) {
                $user->password = bcrypt($request->password);
                $user->save();
                return response()->json($this->withSuccess('Password Changes successfully.'));
            } else {
                return response()->json($this->withError('Current password did not match'));
            }
        } catch (\Exception $e) {
            return response()->json($this->withError($e->getMessage()));
        }
    }

    public function verify($id = null)
    {
        try {
            if (!$id) {
                $data['kyc_list'] = Kyc::where('status', 1)->get();
                return response()->json($this->withSuccess($data));
            }
            $user = auth()->user();
            $item = Kyc::where('status', 1)->find($id);
            if (!$item) {
                return response()->json($this->withError('Record not found'));
            }
            $userKyc = UserKyc::where('kyc_id', $id)
                ->where('user_id', $user->id)
                ->first();
            $formShow = "isFormShow";
            $msg = "msg";

            if ($userKyc) {
                $type = $userKyc->kyc_type;
                $status = $userKyc->status;

                $data = [
                    $formShow => true,
                    $msg => null,
                    'kycFormData' => $item,
                ];
                if ($status == 0) {
                    $data[$formShow] = false;
                    $data[$msg] = "Your {$type} submission has been pending";
                } elseif ($status == 1) {
                    $data[$formShow] = false;
                    $data[$msg] = "Your {$type} already verified";
                } elseif ($status == 2) {
                    $data[$msg] = "Your previous {$type} request has been rejected";
                    $data['rejectReason'] = ($userKyc->reason) ? $userKyc->reason : null;
                }
            } else {
                $data = [
                    $formShow => true,
                    $msg => null,
                    'kycFormData' => $item,
                ];
            }
            return response()->json($this->withSuccess($data));
        } catch (\Exception $e) {
            return response()->json($this->withError($e->getMessage()));
        }
    }

    public function kycVerificationSubmit(Request $request)
    {
        try {
            $checkKyc = UserKyc::where('user_id', auth()->id())->get();

            foreach ($checkKyc as $kyc) {
                if ($kyc->status === 0 || $kyc->status === 1) {
                    return response()->json($this->withError('KYC has already been submitted.'));
                }
            }
            foreach ($checkKyc as $kyc) {
                if ($kyc->status == 2) {
                    $kyc->delete();
                }
            }
            $kyc = Kyc::where('id', $request->type)->where('status', 1)->first();
            if (!$kyc) {
                return response()->json($this->withError('Kyc Not Found'));
            }
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
                return response()->json($this->withError(collect($validator->errors())->collapse()));
            }

            // {
            //     "customer_id": "c39ff749-f6aa-417b-a181-2aee77960bfd",
            //     "dob": "1990-06-20",
            //     "address": {
            //         "street": "No 16, Adeola odeku street, victoria island",
            //         "city": "Ojota",
            //         "state": "Lagos",
            //         "country": "NG",
            //         "postal_code": "882700"
            //     },
            //     "identification_type": "BVN",
            //     "identification_number": "22288771100",
            //     "photo": "{{$randomImageUrl}}"
            // }

            $reqField = [];
            foreach ($request->except('_token', '_method', 'type') as $k => $v) {
                foreach ($params as $inKey => $inVal) {
                    if ($k == $inKey) {
                        if ($inVal->type == 'file' && $request->hasFile($inKey)) {
                            try {
                                $file = $this->fileUpload($request[$inKey], config('filelocation.kyc.path'));
                                $reqField[$inKey] = [
                                    'field_name' => $inVal->field_name,
                                    'field_label' => $inVal->field_label,
                                    'field_value' => $file['path'],
                                    'field_driver' => $file['driver'],
                                    'validation' => $inVal->validation,
                                    'type' => $inVal->type,
                                ];
                            } catch (\Exception $exp) {
                                return response()->json($this->withError("Could not upload your {$inKey}"));
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
            UserKyc::create([
                'user_id' => auth()->id(),
                'kyc_id' => $kyc->id,
                'kyc_type' => $kyc->name,
                'kyc_info' => $reqField
            ]);

            return response()->json($this->withSuccess("KYC Submitted Successfully"));
        } catch (\Exception $e) {
            return response()->json($this->withError($e->getMessage()));
        }
    }

    public function deleteAccount()
    {
        try {
            $user = auth()->user();
            if ($user) {
                UserAllRecordDeleteJob::dispatch($user);
                $user->delete();
                return response()->json($this->withSuccess('Your account has been deleted successfully.'));
            } else {
                return response()->json($this->withError('Invalid user'));
            }
        } catch (\Exception $e) {
            return response()->json($this->withError($e->getMessage()));
        }
    }

}
