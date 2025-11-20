<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Banks;
use App\Models\Country;
use App\Models\CountryCurrency;
use App\Models\CountryService;
use App\Models\Recipient;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use function App\Http\Controllers\collect;

class RecipientController extends Controller
{
    use ApiResponse;

    public function __construct()
    {
        $this->theme = template();
    }

    public function index(Request $request)
    {
        $userId = auth()->id();
        $search = $request->input('search');
        $data = [];

        if ($search && str_starts_with($search, '@')) {
            $searchUsername = ltrim($search, '@');
            $user = User::where('username', $searchUsername)->first();
            if ($user) {
                $data['users'] = \collect([$user]);
            } else {
                $data['users'] = \collect();
            }
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

            $data['recipients'] = $query->latest()->paginate(20);
        }
        return view($this->theme . 'user.recipient.list', $data);
    }


    public function create($type = 'others',$addNew = null, $countryName = null)
    {
        if (!in_array($type, ['my-self', 'others'])) {
            abort(404);
        }
        $typeValue = ($type === 'others') ? 1 : 0;
        $data['type'] = $typeValue;
        $addNew == "transfer" ? session(['transfer' => true]) : session()->forget('transfer');
        $data['countryId'] = Country::where('name', $countryName)->value('id');
        $data['currency'] = CountryCurrency::with('country:id,name,image,image_driver')
            ->whereHas('country', fn ($query) => $query->where('receive_from', 1))
            ->get();
        return view($this->theme.'user.recipient.add',$data);
    }

    /*axios request*/
    public function getServices(Request $request)
    {
        $request->validate([
            'country_id' => 'required|exists:countries,id',
        ]);
        $countryId = $request->query('country_id');
        $services = CountryService::where('status', 1)
            ->whereIn('id', fn ($query) => $query
                ->from('country_banks')
                ->where('country_id', $countryId)
                ->select('service_id')
                ->pluck('service_id')
            )
            ->pluck('name', 'id');
        return response()->json(['services' => $services]);
    }

    public function getBank(Request $request)
    {
        $request->validate([
            'service_id' => 'required',
            'country_id' => 'required|exists:countries,id',
        ]);
        $serviceId = $request->input('service_id');
        $countryId = $request->input('country_id');
        $banks = Banks::where('service_id', $serviceId)->where('country_id', $countryId)->where('status', 1)->get();

        return response()->json(['banks' => $banks]);
    }

    public function generateFields(Request $request)
    {
        $request->validate([
            'bank_id' => 'required|exists:country_banks,id',
        ]);
        $selectedBank = Banks::findOrFail($request->bank_id);
        $serviceFormFields = $selectedBank->services_form;

        return response()->json($serviceFormFields);
    }

    public function store(Request $request)
    {
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
            return back()->withInput()->with(['error' => 'Bank Not Found']);
        }
        $formField = optional($bank)->services_form;
        $additionalRules  = $this->generateValidationRules($formField);
        $rules = array_merge($baseRules, $additionalRules);
        $requestData = $request->except('_token','transfer');
        $data = Validator::make($requestData, $rules);
        if ($data->fails()) {
            return back()->withInput()->withErrors($data);
        }
        $excludeFields = ["currency_id", "bank_id", "email", "name",'service_id','type'];
        $bankInfoFields = array_diff_key($requestData, array_flip($excludeFields));
        $data = array_diff_key($requestData, $bankInfoFields);
        $data['bank_info'] = $bankInfoFields;

        $recipient = Recipient::create($data);

        $trackId = $recipient->uuid;
        return session('transfer')
            ? redirect(route('user.transferReview',['uuid' => $trackId]))->with('success', 'Recipient Added..')
            : redirect(route('user.recipient.index'))->with('success', 'Recipient Created successfully');
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

    public function details($uuid)
    {
        $recipient= Recipient::where('user_id',auth()->id())->where('uuid',$uuid)->first();
        if (!$recipient) {
           return to_route('user.recipient.index')->with('error','Invalid Recipient');
        }
        return view($this->theme.'user.recipient.details', compact('recipient'));
    }

    public function updateName(Request $request, Recipient $recipient)
    {
        if ($recipient->user_id != auth()->id()) {
            return back()->with('error', 'You are not authorized to update this recipient.');
        }
        $request->validate([
            'name' => 'required|string',
        ]);
        $data = $request->only('name');
        $recipient->update($data);
        return back()->with('success', 'Recipient name updated successfully.');
    }

    public function destroy(Recipient $recipient)
    {
        try {
            DB::beginTransaction();
            $recipient->delete();
            DB::commit();
            return redirect(route('user.recipient.index'))->with('success', 'Recipient and associated transfers deleted successfully');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect(route('user.recipient.index'))->with('error', 'Error deleting recipient and associated money transfers: ' . $e->getMessage());
        }
    }

    public function userStore(Request $request)
    {
        $rules = ['r_user_id' => 'required|integer|exists:users,id',];
        $req = $request->except('_token','transfer');
        $data = Validator::make($req, $rules);
        if ($data->fails()) {
            return back()->withInput()->withErrors($data);
        }
        $exists = Recipient::where('user_id', auth()->id())->where('r_user_id', $request->r_user_id)->exists();
        if ($exists) {
            return back()->with('error', 'This user is already added as a recipient');
        }
        $newUser = User::findOrFail($request->r_user_id);

        $data= [
            'r_user_id' => $request->r_user_id,
            'name' => $newUser->fullname(),
            'email' => $newUser->email,
        ];
        $recipient = Recipient::create($data);

        $trackId = $recipient->uuid;
        return session('transfer')
            ? redirect(route('user.transferReview',['uuid' => $trackId]))->with('success', 'Recipient Added..')
            : redirect(route('user.recipient.index'))->with('success', 'Recipient Created successfully');
    }

}
