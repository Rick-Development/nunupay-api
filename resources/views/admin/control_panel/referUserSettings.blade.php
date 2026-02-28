@extends('admin.layouts.app')
@section('page_title', __('Refer User Setting'))
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
                                            <h6 class="m-0 font-weight-bold text-primary">@lang('Referral Settings')</h6>
                                        </div>
                                        <div class="card-body">
                                            <form action="{{ route('admin.refer-user.settings') }}" method="post">
                                                @csrf

                                                <label class="row form-check form-switch mb-4" for="refer_status">
                                                    <span class="col-8 col-sm-9 ms-0">
                                                        <span class="d-block text-dark">@lang("Status")</span>
                                                        <span class="d-block fs-5">
                                                            @lang("Change status to enable or disable refer bonus on website.")
                                                        </span>
                                                    </span>
                                                    <span class="col-4 col-sm-3 text-end">
                                                        <input type='hidden' value='0' name='refer_status'>
                                                        <input type="checkbox" name="refer_status" id="refer_status"
                                                               value="1" class="form-check-input"
                                                            {{ $basicControl->refer_status == 1 ? 'checked' : ''}} >
                                                    </span>
                                                    @error('refer_status')
                                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                                    @enderror
                                                </label>

                                                <div class="row mb-4">
                                                    <label for="refer_title"
                                                           class="col-sm-3 col-form-label form-label">@lang("Refer Title")</label>
                                                    <div class="col-12">
                                                        <input type="text"
                                                               class="form-control @error('refer_title') is-invalid @enderror"
                                                               name="refer_title" id="refer_title"
                                                               placeholder="@lang("Refer Title")"
                                                               value="{{ old('refer_title', $basicControl->refer_title) }}"
                                                               autocomplete="off">
                                                        @error('refer_title')
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>


                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <div class="form-group">
                                                            <label class="form-label">
                                                                @lang('Referral earn amount in USD')
                                                            </label>
                                                            <div class="input-group">
                                                                <input type="number" class="form-control" step="0.001"
                                                                       name="refer_earn_amount"
                                                                       value="{{old('refer_earn_amount',$basicControl->refer_earn_amount)}}">
                                                                <div class="input-group-prepend">
                                                                    <span class="form-control">USD</span>
                                                                </div>
                                                            </div>
                                                            @error('refer_earn_amount')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <div class="form-group">
                                                            <label class="form-label">
                                                                @lang('Refer free transfer up to amount in USD')
                                                            </label>
                                                            <div class="input-group">
                                                                <input type="number" class="form-control" step="0.001"
                                                                       name="refer_free_transfer"
                                                                       value="{{old('refer_free_transfer',$basicControl->refer_free_transfer)}}">
                                                                <div class="input-group-prepend">
                                                                    <span class="form-control">USD</span>
                                                                </div>
                                                            </div>
                                                            @error('refer_free_transfer')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
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

