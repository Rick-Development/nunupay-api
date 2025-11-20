@extends('admin.layouts.app')
@section('page_title', __('Money Transfer Setting'))
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
                                            <h6 class="m-0 font-weight-bold text-primary">@lang('Money Transfer Settings')</h6>
                                        </div>
                                        <div class="card-body">
                                            <form action="{{ route('admin.money-transfer.settings') }}" method="post">
                                                @csrf
                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <div class="form-group">
                                                            <label class="form-label">
                                                                @lang('Minimum transfer amount in USD')
                                                            </label>
                                                            <div class="input-group">
                                                                <input type="number" class="form-control" step="0.001"
                                                                       name="min_amount"
                                                                       value="{{old('min_amount',$basicControl->min_amount)}}">
                                                                <div class="input-group-prepend">
                                                                    <span class="form-control">USD</span>
                                                                </div>
                                                            </div>
                                                            @error('min_amount')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <div class="form-group">
                                                            <label class="form-label">
                                                                @lang('Maximum transfer amount in USD')
                                                            </label>
                                                            <div class="input-group">
                                                                <input type="number" class="form-control" step="0.001"
                                                                       name="max_amount"
                                                                       value="{{old('max_amount',$basicControl->max_amount)}}">
                                                                <div class="input-group-prepend">
                                                                    <span class="form-control">USD</span>
                                                                </div>
                                                            </div>
                                                            @error('max_amount')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <div class="form-group">
                                                            <label class="form-label">
                                                                @lang('Minimum transfer fee in USD')
                                                            </label>
                                                            <div class="input-group">
                                                                <input type="number" class="form-control" step="0.001"
                                                                       name="min_transfer_fee"
                                                                       value="{{old('min_transfer_fee',$basicControl->min_transfer_fee)}}">
                                                                <div class="input-group-prepend">
                                                                    <span class="form-control">USD</span>
                                                                </div>
                                                            </div>
                                                            @error('min_transfer_fee')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label class="form-label">
                                                                @lang('Maximum transfer fee in USD')
                                                            </label>
                                                            <div class="input-group">
                                                                <input type="number" class="form-control" step="0.001"
                                                                       name="max_transfer_fee"
                                                                       value="{{old('max_transfer_fee',$basicControl->max_transfer_fee)}}">
                                                                <div class="input-group-prepend">
																	<span class="form-control">USD</span>
                                                                </div>
                                                            </div>
                                                            @error('max_transfer_fee')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
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

