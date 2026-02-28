<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\CountryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class ServiceController extends Controller
{
    public function index(){
        return view('admin.country.serviceList');
    }

    public function serviceList(Request $request)
    {
        $services = CountryService::when(!empty($request->search['value']), function ($query) use ($request) {
            $query->where('name', 'LIKE', '%' . $request->search['value'] . '%');
        });

        return DataTables::of($services)
            ->addColumn('checkbox', function ($item) {
                return ' <input type="checkbox" id="chk-' . $item->id . '" class="form-check-input row-tic tic-check"
                name="check" value="' . $item->id . '" data-id="' . $item->id . '">';
            })
            ->addColumn('service_name', function ($item) {
                return $item->name ??  'N/A';
            })
            ->addColumn('status', function ($item) {
                return renderStatusBadge($item->status);
            })
            ->addColumn('action', function ($item) {
                $editUrl = route('admin.service.edit', ['service' => $item->id]);
                return '<div class="btn-group" role="group">
                            <a href="' . $editUrl . '" class="btn btn-white btn-sm edit_user_btn">
                                <i class="bi-pencil-fill me-1"></i> ' . trans("Edit") . '
                            </a>
                        </div>';
            })
            ->rawColumns(['checkbox', 'service_name', 'status','action',])
            ->make(true);
    }

    public function create()
    {
        return view('admin.country.serviceCreate');
    }

    public function store(Request $request )
    {
        $request->validate([
            'name' => 'required|string|max:40|unique:country_services',
        ]);
        $data = $request->except('_token');
        CountryService::create($data);
        return redirect()->route('admin.service.index')->with('success', 'Service created successfully');
    }

    public function edit(CountryService $service)
    {
        return view('admin.country.serviceEdit', compact('service'));
    }

    public function update(Request $request, CountryService $service)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);
        $data = $request->except('_token','_method');
        $service->update($data);

        return redirect()->route('admin.service.index',$service)->with('success', 'Service updated successfully');
    }

    public function deleteMultiple(Request $request)
    {
        if ($request->strIds === null) {
            session()->flash('error', 'You did not select any service.');
            return response()->json(['error' => 1]);
        }
        DB::transaction(function () use ($request) {
            $services = CountryService::whereIn('id', $request->strIds)->get();
            foreach ($services as $service) {
                $service->delete();
            }
        });

        session()->flash('success', 'Selected data deleted successfully');
        return response()->json(['success' => 1]);
    }

    public function activeMultiple(Request $request)
    {
        if ($request->strIds == null) {
            session()->flash('error', 'You do not select any service.');
            return response()->json(['error' => 1]);
        } else {
            CountryService::whereIn('id', $request->strIds)->update([
                'status' => 1,
            ]);
            session()->flash('success', 'Service Status Activated');
            return response()->json(['success' => 1]);
        }
    }

    public function inactiveMultiple(Request $request)
    {
        if ($request->strIds == null) {
            session()->flash('error', 'You do not select any service.');
            return response()->json(['error' => 1]);
        } else {
            CountryService::whereIn('id', $request->strIds)->update([
                'status' => 0,
            ]);
            session()->flash('success', 'Service Status Deactivated');
            return response()->json(['success' => 1]);
        }
    }
}
