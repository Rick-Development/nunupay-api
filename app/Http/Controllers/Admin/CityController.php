<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\CountryCity;
use App\Models\CountryState;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class CityController extends Controller
{

    public function cityList(Country $country, CountryState $state)
    {
        $cities = $state->cities;
        return DataTables::of($cities)
            ->addColumn('checkbox', function ($item) {
                return ' <input type="checkbox" id="chk-' . $item->id . '" class="form-check-input row-tic tic-check"
                name="check" value="' . $item->id . '" data-id="' . $item->id . '">';
            })
            ->addColumn('city_name', function ($item) {
                return $item->name ?? 'N/A';
            })
            ->addColumn('status', function ($item) {
                return renderStatusBadge($item->status);
            })
            ->addColumn('action', function ($item) use ($country, $state) {
                $editUrl = route('admin.city.edit', ['country' => $country->id, 'state' => $state->id, 'city' => $item->id]);

                return '<a href="' . $editUrl . '" class="btn btn-white btn-sm edit_user_btn">
                            <i class="bi-pencil-fill me-1"></i> ' . trans("Edit") . '
                        </a>
                        ';
            })
            ->rawColumns(['checkbox', 'city_name', 'status', 'action'])
            ->make(true);
    }


    public function create($country, $state)
    {
        return view('admin.country.cityCreate', compact('country', 'state'));
    }


    public function store(Request $request ,$country, $state)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:country_cities,name,NULL,id,country_id,' . $request->country_id . ',state_id,' . $request->state_id,
            'country_id' => 'required',
            'state_id' => 'required',
            'status' => 'required'
        ]);

        $data['country_id'] =$country;
        $data['state_id'] =$state;

        CountryCity::create($data);

        return redirect()->route('admin.stateCity', ['country' => $country, 'state' => $state])
            ->with('success', 'City updated successfully');

    }

    public function edit(Country $country, CountryState $state, CountryCity $city)
    {
        return view('admin.country.cityEdit', compact('country', 'state', 'city'));
    }


    public function update(Request $request, $country, $state, $city)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:country_cities,name,' . $city . ',id,country_id,' . $country . ',state_id,' . $state,
            'status' => 'required'
        ]);

        $cityData = CountryCity::findOrFail($city);
        $cityData->update($data);

        return redirect()->route('admin.stateCity', ['country' => $country, 'state' => $state, 'city' => $city])
            ->with('success', 'City updated successfully');
    }


    public function deleteMultiple(Request $request)
    {
        if ($request->strIds === null) {
            session()->flash('error', 'You did not select any cities.');
            return response()->json(['error' => 1]);
        }

        DB::transaction(function () use ($request) {
            $cities = CountryCity::whereIn('id', $request->strIds)->get();
            $cities->each->delete();
        });

        session()->flash('success', 'Selected cities deleted successfully');
        return response()->json(['success' => 1]);
    }




    public function activeMultiple(Request $request)
    {
        if ($request->strIds == null) {
            session()->flash('error', 'You do not select any city.');
            return response()->json(['error' => 1]);
        } else {
            CountryCity::whereIn('id', $request->strIds)->update([
                'status' => 1,
            ]);
            session()->flash('success', 'State Status Activated');
            return response()->json(['success' => 1]);
        }
    }

    public function inactiveMultiple(Request $request)
    {

        if ($request->strIds == null) {
            session()->flash('error', 'You do not select any city.');
            return response()->json(['error' => 1]);
        } else {
            CountryCity::whereIn('id', $request->strIds)->update([
                'status' => 0,
            ]);

            session()->flash('success', 'State Status Deactivated');
            return response()->json(['success' => 1]);

        }
    }
}
