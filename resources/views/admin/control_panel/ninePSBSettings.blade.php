@extends('admin.layouts.app')
@section('page_title', __('9PSB Setting'))
@section('content')
    <div class="content container-fluid" id="setting-section">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title">@yield('page_title')</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb breadcrumb-no-gutter">
                            <li class="breadcrumb-item">
                                <a class="breadcrumb-link" href="{{ route('admin.dashboard') }}">@lang('Dashboard')</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">@lang('Settings')</li>
                            <li class="breadcrumb-item active" aria-current="page">@yield('page_title')</li>
                        </ol>
                    </nav>

                </div>
            </div>
        </div>

        <div class="row">
            <div class="section-body">
                <div class="row mt-sm-4">
                    <div class="col-12 col-md-4 col-lg-3">
                        @include('admin.control_panel.components.sidebar', ['settings' => config('generalsettings.settings'), 'suffix' => 'Settings'])
                    </div>
                    <div class="col-12 col-md-8 col-lg-9">
                        <div class="container-fluid" id="container-wrapper">
                            <div class="row justify-content-md-center">
                                <div class="col-lg-12">
                                    <div class="card mb-4 card-primary shadow">
                                        <div
                                            class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                            <h6 class="m-0 font-weight-bold text-primary">@lang('9PSB Settings')</h6>
                                        </div>
                                        <div class="card-body">
                                            <form action="{{ route('admin.ninepsb.settings') }}" method="post">
                                                @csrf
                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <div class="form-group">
                                                            <label class="form-label">
                                                                @lang('NINE_PAYMENT_USERNAME')
                                                            </label>
                                                            <div class="input-group">
                                                                <input type="text" class="form-control" step="0.001"
                                                                       name="nine_payment_username"
                                                                       value="{{old('nine_payment_username',$basicControl->nine_payment_username)}}">
                                                                <div class="input-group-prepend">
                                                                    
                                                                </div>
                                                            </div>
                                                            @error('nine_payment_username')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <div class="form-group">
                                                            <label class="form-label">
                                                                @lang('NINE_PAYMENT_PASSWORD')
                                                            </label>
                                                            <div class="input-group">
                                                                <input type="text" class="form-control" step="0.001"
                                                                       name="nine_payment_password"
                                                                       value="{{old('nine_payment_password',$basicControl->nine_payment_password)}}">
                                                                <div class="input-group-prepend">
                                                                    
                                                                </div>
                                                            </div>
                                                            @error('nine_payment_password')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <div class="form-group">
                                                            <label class="form-label">
                                                                @lang('NINE_PAYMENT_CLIENT_ID')
                                                            </label>
                                                            <div class="input-group">
                                                                <input type="text" class="form-control" step="0.001"
                                                                       name="nine_payment_client_id"
                                                                       value="{{old('nine_payment_client_id',$basicControl->nine_payment_client_id)}}">
                                                                <div class="input-group-prepend">
                                                                    
                                                                </div>
                                                            </div>
                                                            @error('nine_payment_client_id')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label class="form-label">
                                                                @lang('NINE_PAYMENT_CLIENT_SECRET')
                                                            </label>
                                                            <div class="input-group">
                                                                <input type="text" class="form-control" step="0.001"
                                                                       name="nine_payment_client_secret"
                                                                       value="{{old('nine_payment_client_secret',$basicControl->nine_payment_client_secret)}}">
                                                                <div class="input-group-prepend">
																	
                                                                </div>
                                                            </div>
                                                            @error('nine_payment_client_secret')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                                                        </div>
                                                    </div>
                                                    
                                                    
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label class="form-label">
                                                                @lang('NINE_PAYMENT_API_KEY')
                                                            </label>
                                                            <div class="input-group">
                                                                <input type="text" class="form-control" step="0.001"
                                                                       name="nine_payment_api_key"
                                                                       value="{{old('nine_payment_api_key',$basicControl->nine_payment_api_key)}}">
                                                                <div class="input-group-prepend">
																	
                                                                </div>
                                                            </div>
                                                            @error('nine_payment_api_key')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                                                        </div>
                                                    </div>
                                                    
                                                    
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label class="form-label">
                                                                @lang('NINE_PAYMENT_SECRET_KEY')
                                                            </label>
                                                            <div class="input-group">
                                                                <input type="text" class="form-control" step="0.001"
                                                                       name="nine_payment_secret_key"
                                                                       value="{{old('nine_payment_secret_key',$basicControl->nine_payment_secret_key)}}">
                                                                <div class="input-group-prepend">
																	
                                                                </div>
                                                            </div>
                                                            @error('nine_payment_secret_key')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                                                        </div>
                                                    </div>

                                                </div>



                                                <div class="row mt-2">
                                                    <div class="col-md-6 mb-3">
                                                        <div class="form-group">
                                                            <label class="form-label">
                                                                @lang('Transfer to Airpero charge in NGN')
                                                            </label>
                                                            <div class="input-group">
                                                                <input type="number" class="form-control" step="0.001"
                                                                       name="transfer_to_airpero_charge_in_ngn"
                                                                       value="{{old('transfer_to_airpero_charge_in_ngn',$basicControl->transfer_to_airpero_charge_in_ngn)}}">
                                                                <div class="input-group-prepend">
                                                                    <span class="form-control">NGN</span>
                                                                </div>
                                                            </div>
                                                            @error('transfer_to_airpero_charge_in_ngn')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <div class="form-group">
                                                            <label class="form-label">
                                                                @lang('Bill Payment charge in NGN')
                                                            </label>
                                                            <div class="input-group">
                                                                <input type="number" class="form-control" step="0.001"
                                                                       name="bill_payment_charge_in_ngn"
                                                                       value="{{old('bill_payment_charge_in_ngn',$basicControl->bill_payment_charge_in_ngn)}}">
                                                                <div class="input-group-prepend">
                                                                    <span class="form-control">NGN</span>
                                                                </div>
                                                            </div>
                                                            @error('bill_payment_charge_in_ngn')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <div class="form-group">
                                                            <label class="form-label">
                                                                @lang('Transfer to other bank Charge in NGN')
                                                            </label>
                                                            <div class="input-group">
                                                                <input type="number" class="form-control" step="0.001"
                                                                       name="transfer_to_other_bank_charge_in_ngn"
                                                                       value="{{old('transfer_to_other_bank_charge_in_ngn',$basicControl->transfer_to_other_bank_charge_in_ngn)}}">
                                                                <div class="input-group-prepend">
                                                                    <span class="form-control">NGN</span>
                                                                </div>
                                                            </div>
                                                            @error('transfer_to_other_bank_charge_in_ngn')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label class="form-label">
                                                                @lang('Deposit charge in NGN')
                                                            </label>
                                                            <div class="input-group">
                                                                <input type="number" class="form-control" step="0.001"
                                                                       name="deposit_charge_in_ngn"
                                                                       value="{{old('deposit_charge_in_ngn',$basicControl->deposit_charge_in_ngn)}}">
                                                                <div class="input-group-prepend">
																	<span class="form-control">NGN</span>
                                                                </div>
                                                            </div>
                                                            @error('deposit_charge_in_ngn')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                                                        </div>
                                                    </div>

                                                </div>
                                                
                                                    <div class="col-md-12">
                                                        <div class="form-group">
                                                            <label class="form-label">
                                                                @lang('Admin Account Number')
                                                            </label>
                                                            <div class="input-group">
                                                                <input type="number" class="form-control" step="0.001"
                                                                       name="nine_payment_account_number"
                                                                       value="{{old('nine_payment_account_number',$basicControl->nine_payment_account_number)}}">
                                                            </div>
                                                            @error('nine_payment_account_number')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                                                        </div>
                                                    </div>
                                                

                                                <div class="d-flex justify-content-start mt-4">
                                                    <button type="submit" id="submit" class="btn btn-primary">@lang('Save changes')</button>
                                                </div>

                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

