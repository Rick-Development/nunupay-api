<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Traits\Upload;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class CountryController extends Controller
{
    use Upload;

    public function index()
    {
        $totalCountry = \Cache::get('totalCountry');
        if (!$totalCountry){
            $totalCountry = Country::count('id');
            \Cache::put('totalCountry', $totalCountry);
        }
        return view('admin.country.index', compact('totalCountry'));
    }

    public function countryList(Request $request)
    {
        $countries = Country::with(['currency:id,country_id,rate,code,symbol,default'])
            ->when(!empty($request->search['value']), function ($query) use ($request) {
                $query->where('name', 'LIKE', '%' . $request->search['value'] . '%')
                ->orWhereHas('currency', function ($q) use($request){
                    $q->where('code', 'LIKE', '%' . $request->search['value'] . '%');
                });
            });
        $basicControl = basicControl();
        $siteCurrency =  $basicControl->base_currency;

        return DataTables::of($countries)
            ->addColumn('checkbox', function ($item) {
                return ' <input type="checkbox" id="chk-' . $item->id . '" class="form-check-input row-tic tic-check"
                name="check" value="' . $item->id . '" data-id="' . $item->id . '">';
            })
            ->addColumn('name', function ($item) {
                $editUrl = route('admin.country.edit', $item->id);
                return '<a class="d-flex align-items-center me-2" href="' . $editUrl . '">
                            <div class="flex-shrink-0"> ' . $item->countryImage() . ' </div>
                            <div class="flex-grow-1 ms-3">
                                <h5 class="text-hover-primary mb-0">' . $item->name . '</h5>
                            </div>
                        </a>';
            })
            ->addColumn('currency', function ($item) {
                return $item->currency ? $item->currency->code : 'N/A';
            })
            ->addColumn('currency_symbol', function ($item) {
                return $item->currency ? $item->currency->symbol : 'N/A';
            })
            ->addColumn('rate', function ($item)  use ($siteCurrency){
                    return '1 '.$siteCurrency .' = ' .fractionNumber(optional($item->currency)->rate) . ' '.optional($item->currency)->code;
            })
            ->addColumn('sendable', function ($item) {
                return renderStatusBadge($item->send_to,1);
            })
            ->addColumn('receivable', function ($item) {
                return renderStatusBadge($item->receive_from,1);
            })
            ->addColumn('status', function ($item) {
                return renderStatusBadge($item->status);
            })
            ->addColumn('action', function ($item) {
                $editUrl = route('admin.country.edit', $item->id);
                $state = route('admin.countryState', $item->id);
                $bank = route('admin.countryBank', $item->id);
                return '<div class="btn-group" role="group">
                            <a href="' . $editUrl . '" class="btn btn-white btn-sm edit_user_btn">
                                <i class="bi-pencil-fill me-1"></i> ' . trans("Edit") . '
                            </a>
                            <div class="btn-group">
                                <button type="button" class="btn btn-white btn-icon btn-sm dropdown-toggle dropdown-toggle-empty" id="userEditDropdown" data-bs-toggle="dropdown" aria-expanded="false"></button>
                                <div class="dropdown-menu dropdown-menu-end mt-1" aria-labelledby="userEditDropdown">
                                   <a class="dropdown-item" href="' . $state . '">
                                     <i class="bi-globe dropdown-item-icon"></i> ' . trans("Manage State") . ' </a>
                                   <a class="dropdown-item" href="' . $bank . '">
                                     <i class="bi-bank dropdown-item-icon"></i> ' . trans("Manage Banks") . ' </a>
                                </div>
                            </div>
                        </div>';
            })->rawColumns(['action', 'checkbox', 'name','rate', 'status', 'sendable', 'receivable'])
            ->make(true);
    }

    public function create()
    {
        return view('admin.country.create');
    }

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'iso2' => 'required|max:2',
                'iso3' => 'required|max:3',
                'phone_code' => 'required',
                'currency_name' => 'required',
                'currency_code' => 'required',
                'currency_rate' => 'required',
                'currency_symbol' => 'required',
                'currency_symbol_native' => 'required',
                'image' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg|max:4096',
            ]);
            if ($validator->fails()) {
                return back()->withInput()->withErrors($validator);
            }
            $data = Arr::except($request->all(), ['_token', '_method', 'image']);

            if ($request->hasFile('image')) {
                $image = $this->fileUpload($request->image, config('filelocation.country.path'), null, config('filelocation.country.size'), 'webp', 60);
                throw_if(empty($image['path']), new \Exception('Image could not be uploaded'));

                $data['image'] = $image['path'];
                $data['image_driver'] = $image['driver'];
            }
            $country = Country::create($data);
            $currencyData = [
                'name' => $request->input('currency_name'),
                'code' => $request->input('currency_code'),
                'rate' => $request->input('currency_rate'),
                'symbol' => $request->input('currency_symbol'),
                'symbol_native' => $request->input('currency_symbol_native'),
            ];
            $country->currency()->create($currencyData);

            DB::commit();
            return redirect()->route('admin.country.index')->with('success', 'Country added successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    public function edit(Country $country)
    {
        return view('admin.country.edit', compact('country'));
    }

    public function update(Request $request, Country $country)
    {
        try {
            DB::beginTransaction();
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'iso2' => 'required|max:2',
                'iso3' => 'required|max:3',
                'phone_code' => 'required',
                'currency_name' => 'required',
                'currency_code' => 'required',
                'currency_rate' => 'required',
                'currency_symbol' => 'required',
                'currency_symbol_native' => 'required',
                'image' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg|max:4096',
            ]);

            if ($validator->fails()) {
                return back()->withInput()->withErrors($validator);
            }
            $data = Arr::except($request->all(), ['_token', '_method', 'image']);

            if ($request->hasFile('image')) {
                try {
                    $image = $this->fileUpload($request->image, config('filelocation.country.path'), null, config('filelocation.country.size'), 'webp', 90);
                    throw_if(empty($image['path']), new \Exception('Image could not be uploaded'));
                    $data['image'] = $image['path'];
                    $data['image_driver'] = $image['driver'];
                } catch (\Exception $exp) {
                    return back()->with('error', 'Image could not be uploaded.');
                }
            }
            $country->update($data);

            $currencyData = [
                'name' => $request->input('currency_name'),
                'code' => $request->input('currency_code'),
                'rate' => $request->input('currency_rate'),
                'symbol' => $request->input('currency_symbol'),
                'symbol_native' => $request->input('currency_symbol_native'),
                'default' => $request->input('default_currency'),
            ];
            $country->currency()->update($currencyData);

            DB::commit();
            return redirect()->route('admin.country.index')->with('success', 'Country updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    public function importCountries()
    {
        $this->broadcastMessage('Please wait few moments...');

        if (!File::exists('assets/worldSeeder.txt')){

            $folderToRemove = base_path('vendor/nnjeim/world/src/Database/Migrations');
            if (File::exists($folderToRemove)) {
                File::deleteDirectory($folderToRemove);
            }
            File::put('assets/worldSeeder.txt',''.time());
            $output = new \Symfony\Component\Console\Output\BufferedOutput();
            \Illuminate\Support\Facades\Artisan::call('db:seed --class=WorldSeeder');
            // Retrieve the command output, if needed
            $output = \Illuminate\Support\Facades\Artisan::output();

            $this->broadcastMessage($output->fetch());
        }
        $this->broadcastMessage('Your function completed successfully.');
    }

    protected function broadcastMessage($message)
    {
        return response()->json(['message' => $message]);
    }

    public function deleteMultiple(Request $request)
    {
        if ($request->strIds == null) {
            session()->flash('error', 'You do not select any Country.');
            return response()->json(['error' => 1]);
        } else {
            DB::transaction(function () use ($request) {
                $countries = Country::with('states','banks','cities','currency')->whereIn('id', $request->strIds)->get();

                foreach ($countries as $country) {
                    $this->fileDelete($country->image_driver, $country->image);
                    $country->states->each->delete();
                    $country->banks->each->delete();
                    $country->cities->each->delete();
                    $country->currency()->delete();
                }
                Country::whereIn('id', $request->strIds)->delete();
            });

            session()->flash('success', 'Selected Data deleted successfully');
            return response()->json(['success' => 1]);
        }
    }

    public function activeMultiple(Request $request)
    {
        if ($request->strIds == null) {
            session()->flash('error', 'You do not select any Country.');
            return response()->json(['error' => 1]);
        } else {
            Country::whereIn('id', $request->strIds)->update([
                'status' => 1,
            ]);
            session()->flash('success', 'Country Status Activated');
            return response()->json(['success' => 1]);
        }
    }

    public function inactiveMultiple(Request $request)
    {
        if ($request->strIds == null) {
            session()->flash('error', 'You do not select any Country.');
            return response()->json(['error' => 1]);
        } else {
            Country::whereIn('id', $request->strIds)->update([
                'status' => 0,
            ]);
            session()->flash('success', 'Country Status Inactive Successfully');
            return response()->json(['success' => 1]);
        }
    }


    public function sendMultiple(Request $request)
    {
        if ($request->strIds == null) {
            session()->flash('error', 'You do not select any Country.');
            return response()->json(['error' => 1]);
        } else {
            Country::whereIn('id', $request->strIds)->update([
                'send_to' => DB::raw('1 - send_to'),
            ]);
            session()->flash('success', 'success');
            return response()->json(['success' => 1]);
        }
    }


    public function receiveMultiple(Request $request)
    {
        if ($request->strIds == null) {
            session()->flash('error', 'You do not select any Country.');
            return response()->json(['error' => 1]);
        } else {
            Country::whereIn('id', $request->strIds)->update([
                'receive_from' => DB::raw('1 - receive_from'),
            ]);
            session()->flash('success', 'success');
            return response()->json(['success' => 1]);
        }
    }


    public function updateRates()
    {
        Artisan::call('app:currency-rate-update');

        return back()->with('success','Currency rates updated successfully');
    }

    public function countryState(Country $country)
    {
        return view('admin.country.stateList', compact('country'));
    }

    public function countryBank(Country $country)
    {
        return view('admin.country.bankList', compact('country'));
    }


}
