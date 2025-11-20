<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banks;
use App\Models\Country;
use App\Models\CountryService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class BanksController extends Controller
{
    public function create(Country $country)
    {
        $data['services'] = CountryService::query()->whereStatus(1)->get();
        return view('admin.country.bankCreate',$data, compact('country'));
    }

    public function bankList(Request $request, Country $country)
    {
        $banks = $country->banks()->orderBy('service_id','ASC');

        if (!empty($request->search['value'])) {
            $banks->where('name', 'LIKE', '%' . $request->search['value'] . '%');
        }

        return DataTables::of($banks)
            ->addColumn('checkbox', function ($item) {
                return ' <input type="checkbox" id="chk-' . $item->id . '" class="form-check-input row-tic tic-check"
                name="check" value="' . $item->id . '" data-id="' . $item->id . '">';
            })
            ->addColumn('bank_name', function ($item) {
                return $item->name ??  'N/A';
            })
            ->addColumn('service_name', function ($item) {
                return $item->service->name ??  'N/A';
            })
            ->addColumn('minimum_amount', function ($item) {
                return $item->localMinAmount ??  'N/A';
            })
            ->addColumn('maximum_amount', function ($item) {
                return $item->localMaxAmount ??  'N/A';
            })
            ->addColumn('status', function ($item) {
                return renderStatusBadge($item->status);
            })
            ->addColumn('action', function ($item) {
                $editUrl = route('admin.bank.edit', ['country' => $item->country_id, 'bank' => $item->id]);
                return '<a href="' . $editUrl . '" class="btn btn-white btn-sm edit_user_btn">
                            <i class="bi-pencil-fill me-1"></i> ' . trans("Edit") . '
                        </a>
                       ';
            })
            ->rawColumns(['checkbox', 'bank_name', 'service_name', 'minimum_amount', 'maximum_amount', 'status','action',])
            ->make(true);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'country_id' => 'required',
            'service_id' => 'required',
            'name' => 'required',
            'localMinAmount' => 'required|numeric',
            'localMaxAmount' => 'required|numeric',
            'status' => 'required',
            'field_name.*' => 'required|string',
            'input_type.*' => 'required|in:text,textarea,file,date,number',
            'is_required.*' => 'required|in:required,optional',
        ]);

        if ($validator->fails()) {
            return back()->withInput()->withErrors($validator);
        }

        $input_form = collect($request->field_name)->map(function ($field, $a) use ($request) {
            return [
                'field_name' => clean($field),
                'field_label' => $field,
                'type' => $request->input_type[$a],
                'validation' => $request->is_required[$a],
            ];
        })->keyBy('field_name')->toArray();

        $data = Arr::except($request->all(), ['_token', '_method', 'field_name', 'input_type', 'is_required']);
        $data['services_form'] = $input_form;

        Banks::create($data);
        $country= $request->input('country_id');

        return redirect(route('admin.countryBank',$country))->with('success', 'Bank created successfully');

    }

    public function edit(Country $country, Banks $bank)
    {
        $data['services'] = CountryService::whereStatus(1)->get();
        return view('admin.country.bankEdit',$data, compact('country','bank'));
    }

    public function update(Request $request, Banks $bank)
    {
        $country= $request->input('country_id');

        $validator = Validator::make($request->all(), [
            'country_id' => 'required',
            'name' => 'required',
            'localMinAmount' => 'required|numeric',
            'localMaxAmount' => 'required|numeric',
            'status' => 'required',
            'field_name.*' => 'required|string',
            'input_type.*' => 'required|in:text,textarea,file,date,number',
            'is_required.*' => 'required|in:required,optional',
        ]);

        if ($validator->fails()) {
            return back()->withInput()->withErrors($validator);
        }

        $input_form = [];
        if ($request->has('field_name')) {
            for ($a = 0; $a < count($request->field_name); $a++) {
                $arr = array();
                $arr['field_name'] = clean($request->field_name[$a]);
                $arr['field_label'] = $request->field_name[$a];
                $arr['type'] = $request->input_type[$a];
                $arr['validation'] = $request->is_required[$a];
                $input_form[$arr['field_name']] = $arr;
            }
        }

        $data = Arr::except($request->all(), ['_token', '_method', 'field_name', 'input_type', 'is_required']);
        $data['services_form'] = $input_form;

        $bank->update($data);

        return redirect(route('admin.countryBank',$country))->with('success', 'Bank updated successfully');
    }

    public function deleteMultiple(Request $request)
    {
        if ($request->strIds === null) {
            session()->flash('error', 'You did not select any bank.');
            return response()->json(['error' => 1]);
        }
        DB::transaction(function () use ($request) {
            $banks = Banks::whereIn('id', $request->strIds)->get();
            foreach ($banks as $bank) {
                $bank->delete();
            }
        });

        session()->flash('success', 'Selected banks and associated data deleted successfully');
        return response()->json(['success' => 1]);
    }

    public function activeMultiple(Request $request)
    {
        if ($request->strIds == null) {
            session()->flash('error', 'You do not select any bank.');
            return response()->json(['error' => 1]);
        } else {
            Banks::whereIn('id', $request->strIds)->update([
                'status' => 1,
            ]);
            session()->flash('success', 'Bank Status Activated');
            return response()->json(['success' => 1]);
        }
    }

    public function inactiveMultiple(Request $request)
    {
        if ($request->strIds == null) {
            session()->flash('error', 'You do not select any Bank.');
            return response()->json(['error' => 1]);
        } else {
            Banks::whereIn('id', $request->strIds)->update([
                'status' => 0,
            ]);
            session()->flash('success', 'Bank Status Deactivated');
            return response()->json(['success' => 1]);
        }
    }
}
