@extends('admin.layouts.app')
@section('page_title', __('Payscribe Setting'))
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
                                            <h6 class="m-0 font-weight-bold text-primary">@lang('Payscribe Settings')</h6>
                                        </div>
                                        <div class="card-body">
                                            <form action="{{ route('admin.payscribe.settings') }}" method="post">
                                                @csrf
                                                <div class="col-md-12 mb-3">
                                                        <div class="form-group">
                                                            <label class="form-label">
                                                                @lang('API KEY')
                                                            </label>
                                                            <div class="input-group">
                                                                <input type="text" class="form-control" step="0.001"
                                                                       name="payscribe_apikey"
                                                                       value="{{old('payscribe_apikey',$basicControl->payscribe_apikey)}}">
                                                                <div class="input-group-prepend">
                                                                
                                                                </div>
                                                            </div>
                                                            @error('payscribe_apikey')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                                                        </div>
                                                    </div>
                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <div class="form-group">
                                                            <label class="form-label">
                                                                @lang('Card issuing amount in USD')
                                                            </label>
                                                            <div class="input-group">
                                                                <input type="text" class="form-control" step="0.001"
                                                                       name="card_issuing_amount_in_usd"
                                                                       value="{{old('card_issuing_amount_in_usd',$basicControl->card_issuing_amount_in_usd)}}">
                                                                <div class="input-group-prepend">
                                                                    <span class="form-control">USD</span>
                                                                </div>
                                                            </div>
                                                            @error('card_issuing_amount_in_usd')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <div class="form-group">
                                                            <label class="form-label">
                                                                @lang('Card Issuing charge in USD')
                                                            </label>
                                                            <div class="input-group">
                                                                <input type="text" class="form-control" step="0.001"
                                                                       name="card_issuing_charge_in_usd"
                                                                       value="{{old('card_issuing_charge_in_usd',$basicControl->card_issuing_charge_in_usd)}}">
                                                                <div class="input-group-prepend">
                                                                    <span class="form-control">USD</span>
                                                                </div>
                                                            </div>
                                                            @error('card_issuing_charge_in_usd')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <div class="form-group">
                                                            <label class="form-label">
                                                                @lang('Card deposit charge in USD')
                                                            </label>
                                                            <div class="input-group">
                                                                <input type="text" class="form-control" step="0.001"
                                                                       name="card_deposit_charge_in_usd"
                                                                       value="{{old('card_deposit_charge_in_usd',$basicControl->card_deposit_charge_in_usd)}}">
                                                                <div class="input-group-prepend">
                                                                    <span class="form-control">USD</span>
                                                                </div>
                                                            </div>
                                                            @error('card_deposit_charge_in_usd')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label class="form-label">
                                                                @lang('Card Withdrawal charge in USD')
                                                            </label>
                                                            <div class="input-group">
                                                                <input type="text" class="form-control" step="0.001"
                                                                       name="card_withdrawal_charge_in_usd"
                                                                       value="{{old('card_withdrawal_charge_in_usd',$basicControl->card_withdrawal_charge_in_usd)}}">
                                                                <div class="input-group-prepend">
																	<span class="form-control">USD</span>
                                                                </div>
                                                            </div>
                                                            @error('card_withdrawal_charge_in_usd')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                                                        </div>
                                                    </div>

                                                </div>
                                                
                                                
                                                
                                                
                                                
                                                
                                                
                                                
                                                
                                                
                                                
                                                <div class="col-md-12 mb-3 mt-3 d-flex justify-content-between">
                                                        <div class="text-primary text-cente fs-3 fw-bold"  >
                                                                @lang('Payscribe FX Rates')
                                                            </div>
                                                            <div class="text-danger text-sm">
                                                                ₦{{ $rate }}
                                                            </div>
                                                    </div>
                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <div class="form-group">
                                                            <label class="form-label">
                                                                @lang('Card issuing Rate')
                                                            </label>
                                                            <div class="input-group">
                                                                <input type="text" class="form-control" step="0.001"
                                                                       name="card_issuing_rate"
                                                                       value="{{old('card_issuing_rate',$basicControl->card_issuing_rate)}}">
                                                                <div class="input-group-prepend">
                                                                    <span class="form-control">NGN</span>
                                                                </div>
                                                            </div>
                                                            @error('card_issuing_rate')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <div class="form-group">
                                                            <label class="form-label">
                                                                @lang('Card Deposit Rate')
                                                            </label>
                                                            <div class="input-group">
                                                                <input type="text" class="form-control" step="0.001"
                                                                       name="card_deposit_rate"
                                                                       value="{{old('card_deposit_rate',$basicControl->card_deposit_rate)}}">
                                                                <div class="input-group-prepend">
                                                                    <span class="form-control">NGN</span>
                                                                </div>
                                                            </div>
                                                            @error('card_deposit_rate')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label class="form-label">
                                                                @lang('Card Withdrawal Rate')
                                                            </label>
                                                            <div class="input-group">
                                                                <input type="text" class="form-control" step="0.001"
                                                                       name="card_withdrawal_rate"
                                                                       value="{{old('card_withdrawal_rate',$basicControl->card_withdrawal_rate)}}">
                                                                <div class="input-group-prepend">
																	<span class="form-control">NGN</span>
                                                                </div>
                                                            </div>
                                                            @error('card_withdrawal_rate')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                                                        </div>
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

