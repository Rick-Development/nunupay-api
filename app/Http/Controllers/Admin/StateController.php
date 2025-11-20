<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\CountryState;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class StateController extends Controller
{

    public function create(Country $country)
    {
        return view('admin.country.stateCreate', compact('country'));
    }

    public function store(Request $request )
    {
        $request->validate([
            'country_id' => 'required',
            'name' => 'required|string|max:255|unique:country_states,name,NULL,id,country_id,' . $request->country_id,
        ]);
        $data = $request->except('_token');
        $state = CountryState::create($data);
        return redirect()->route('admin.countryState',$state->country)->with('success', 'State created successfully');
    }


    public function stateList(Request $request, Country $country)
    {
        $states = CountryState::query()->where('country_id',$country->id);
        if (!empty($request->search['value'])) {
            $states->where('name', 'LIKE', '%' . $request->search['value'] . '%');
        }

        return DataTables::of($states)
            ->addColumn('checkbox', function ($item) {
                return ' <input type="checkbox" id="chk-' . $item->id . '" class="form-check-input row-tic tic-check"
                name="check" value="' . $item->id . '" data-id="' . $item->id . '">';
            })
            ->addColumn('state_name', function ($item) {
                return $item->name ??  'N/A';
            })
            ->addColumn('status', function ($item) {
                return renderStatusBadge($item->status);
            })
            ->addColumn('action', function ($item) {
                $editUrl = route('admin.state.edit', ['country' => $item->country_id, 'state' => $item->id]);
                $city = route('admin.stateCity', ['country' => $item->country_id, 'state' => $item->id]);
                return '<div class="btn-group" role="group">
                      <a href="' . $editUrl . '" class="btn btn-white btn-sm edit_user_btn">
                        <i class="bi-pencil-fill me-1"></i> ' . trans("Edit") . '
                      </a>
                    <div class="btn-group">
                      <button type="button" class="btn btn-white btn-icon btn-sm dropdown-toggle dropdown-toggle-empty" id="userEditDropdown" data-bs-toggle="dropdown" aria-expanded="false"></button>
                      <div class="dropdown-menu dropdown-menu-end mt-1" aria-labelledby="userEditDropdown">

                       <a class="dropdown-item" href="' . $city . '">
                         <i class="bi-building dropdown-item-icon"></i> ' . trans("Manage City") . '
                       </a>

                      </div>
                    </div>
                  </div>';
            })
            ->rawColumns(['checkbox', 'state_name', 'status','action',])
            ->make(true);
    }

    public function edit(Country $country,CountryState $state)
    {
        return view('admin.country.stateEdit', compact('country','state'));
    }

    public function update(Request $request, CountryState $state)
    {
        $request->validate([
            'country_id' => 'required',
            'name' => 'required|string|max:255|unique:country_states,name,' . $state->id . ',id,country_id,' . $request->country_id,
        ]);
        $data = $request->except('_token','_method');
        $state->update($data);
        return redirect()->route('admin.countryState',$state->country)
            ->with('success', 'State updated successfully');
    }

    public function deleteMultiple(Request $request)
    {
        if ($request->strIds === null) {
            session()->flash('error', 'You did not select any states.');
            return response()->json(['error' => 1]);
        }
        DB::transaction(function () use ($request) {
            $states = CountryState::with('cities')->whereIn('id', $request->strIds)->get();
            foreach ($states as $state) {
                $state->cities->each->delete();
                $state->delete();
            }
        });
        session()->flash('success', 'Selected states and associated cities deleted successfully');
        return response()->json(['success' => 1]);
    }

    public function activeMultiple(Request $request)
    {
        if ($request->strIds == null) {
            session()->flash('error', 'You do not select any states.');
            return response()->json(['error' => 1]);
        } else {
            CountryState::whereIn('id', $request->strIds)->update([
                'status' => 1,
            ]);
            session()->flash('success', 'State Status Activated');
            return response()->json(['success' => 1]);
        }
    }

    public function inactiveMultiple(Request $request)
    {
        if ($request->strIds == null) {
            session()->flash('error', 'You do not select any Country.');
            return response()->json(['error' => 1]);
        } else {
            CountryState::whereIn('id', $request->strIds)->update([
                'status' => 0,
            ]);
            session()->flash('success', 'State Status Deactivated');
            return response()->json(['success' => 1]);
        }
    }


    public function stateCity(Country $country, CountryState $state)
    {
        $state = CountryState::with('cities')->find($state->id);
        $cities = $state->cities;
        return view('admin.country.cityList', compact('country', 'state', 'cities'));
    }
}
