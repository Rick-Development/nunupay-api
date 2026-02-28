@extends('admin.layouts.app')
@section('page_title', __('Add Country'))
@section('content')
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title">@yield('page_title')</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb breadcrumb-no-gutter">
                            <li class="breadcrumb-item"><a class="breadcrumb-link"
                                                           href="{{ route('admin.dashboard') }}">@lang('Dashboard')</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">@lang('Countries')</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <div class="row payment_method">
            <div class="col-lg-12">
                <div class="d-grid gap-3 gap-lg-5">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between">
                            <h2 class="card-title mt-2 h4">@lang('Add New Country')</h2>
                            <a href="{{route('admin.country.index')}}" class="btn btn-sm btn-info">
                                <i class="bi-arrow-left"></i> @lang('Back')
                            </a>
                        </div>
                        <div class="card-body">
                            <form action="{{route('admin.country.store')}}" method="post" enctype="multipart/form-data">
                                @csrf
                                <div class="row mb-4 d-flex align-items-center">

                                    <div class="col-md-6">
                                        <label for="name" class="form-label">@lang('Country Name')</label>
                                        <input type="text" class="form-control  @error('name') is-invalid @enderror"
                                               name="name" id="name" placeholder="@lang("Bangladesh")" aria-label="name"
                                               autocomplete="off" value="{{ old('name') }}">
                                        @error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                    </div>

                                    <div class="col-md-2">
                                        <label for="iso2" class="form-label">@lang('Country ISO 2')</label>
                                        <input type="text" class="form-control  @error('iso2') is-invalid @enderror"
                                               name="iso2" id="iso2" placeholder="@lang("BD")" aria-label="iso2"
                                               autocomplete="off" value="{{ old('iso2') }}">
                                        @error('iso2')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                    </div>

                                    <div class="col-md-2">
                                        <label for="iso3" class="form-label">@lang('Country ISO 3')</label>
                                        <input type="text" class="form-control  @error('iso3') is-invalid @enderror"
                                               name="iso3" id="iso3" placeholder="@lang("BGD")" aria-label="iso3"
                                               autocomplete="off" value="{{ old('iso3') }}">
                                        @error('iso3')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                    </div>

                                    <div class="col-md-2">
                                        <label for="phone_code" class="form-label">@lang('Country Phone Code')</label>
                                        <input type="text" class="form-control  @error('phone_code') is-invalid @enderror"
                                               name="phone_code" id="phone_code" placeholder="@lang("+880")" aria-label="phone_code"
                                               autocomplete="off" value="{{ old('phone_code') }}">
                                        @error('phone_code')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                    </div>
                                </div>

                                <div class="card mb-5">
                                    <div class="card-header">
                                        <h4 class="card-header-title">@lang('Country Currency Information')</h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">

                                            <div class="col-md-4 mb-3">
                                                <label for="currency_name" class="form-label">@lang('Currency Name')</label>
                                                <input type="text" class="form-control  @error('currency_name') is-invalid @enderror"
                                                       name="currency_name" id="currency_name" placeholder="@lang("Bangladeshi Taka")" aria-label="currency_name"
                                                       autocomplete="off"
                                                       value="{{ old('currency_name') }}">
                                                @error('currency_name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                            </div>

                                            <div class="col-md-4 mb-3">
                                                <label for="currency_code" class="form-label">@lang('Currency Code')</label>
                                                <input type="text" class="form-control  @error('currency_code') is-invalid @enderror"
                                                       name="currency_code" id="currency_code" placeholder="@lang("BDT")" aria-label="currency_code"
                                                       autocomplete="off"
                                                       value="{{ old('currency_code') }}">
                                                @error('currency_code')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                            </div>

                                            <div class="col-md-4 mb-3">
                                                <label for="currency_rate" class="form-label">@lang('Currency Rate')</label>
                                                <input type="text" class="form-control  @error('currency_rate') is-invalid @enderror"
                                                       name="currency_rate" id="currency_rate" placeholder="@lang("90.00")" aria-label="currency_rate"
                                                       autocomplete="off"
                                                       value="{{ old('currency_rate') }}">
                                                @error('currency_rate')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                            </div>

                                            <div class="col-md-4 ">
                                                <label for="currency_symbol" class="form-label">@lang('Currency Symbol')</label>
                                                <input type="text" class="form-control  @error('currency_symbol') is-invalid @enderror"
                                                       name="currency_symbol" id="currency_symbol" aria-label="currency_symbol"
                                                       autocomplete="off" placeholder="@lang("TK")"
                                                       value="{{ old('currency_symbol') }}">
                                                @error('currency_symbol')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                            </div>

                                            <div class="col-md-4 ">
                                                <label for="currency_symbol_native" class="form-label">@lang('Currency Native Symbol')</label>
                                                <input type="text" class="form-control  @error('currency_symbol_native') is-invalid @enderror"
                                                       name="currency_symbol_native" id="currency_symbol_native"  aria-label="currency_symbol_native"
                                                       autocomplete="off" placeholder="@lang("৳")"
                                                       value="{{ old('currency_symbol_native') }}">
                                                @error('currency_symbol_native')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                            </div>

                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <label class="form-check form-check-dashed" for="imageUploader">
                                            @lang('Country Flag')
                                            <img id="countryImage"
                                                 class="avatar avatar-xl avatar-4x3 avatar-centered h-100 mb-2"
                                                 src="{{ asset('assets/admin/img/oc-browse-file.svg') }}"
                                                 alt="Image Description" data-hs-theme-appearance="default">
                                            <img id="countryImage"
                                                 class="avatar avatar-xl avatar-4x3 avatar-centered h-100 mb-2"
                                                 src="{{ asset('assets/admin/img/oc-browse-file-light.svg') }}"
                                                 alt="Image Description" data-hs-theme-appearance="dark">
                                            <span class="d-block">@lang("Browse your file here")</span>
                                            <input type="file" class="js-file-attach form-check-input" name="image"
                                                   id="imageUploader" data-hs-file-attach-options='{
                                                      "textTarget": "#countryImage",
                                                      "mode": "image",
                                                      "targetAttr": "src",
                                                      "allowTypes": [".png", ".jpeg", ".jpg"]
                                                   }'>
                                        </label>
                                        @error("image")
                                        <span class="invalid-feedback d-block">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="col-md-8">
                                        <label class="row form-check form-switch mt-3" for="country_status">
                                            <span class="col-4 col-sm-9 ms-0 ">
                                                <span class="d-block text-dark">@lang("Country Status")</span>
                                                <span class="d-block fs-5">@lang("Click on this Switch for change the status.")</span>
                                            </span>

                                            <span class="col-2 col-sm-3 text-end">
                                                <input type='hidden' value='0' name='status'>
                                                <input class="form-check-input @error('status') is-invalid @enderror"
                                                       type="checkbox" name="status" id="countryStatus" value="1" checked>
                                                <label class="form-check-label text-center" for="countryStatus"></label>
                                            </span>
                                            @error('status')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                                        </label>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-start mt-4">
                                    <button type="submit" class="btn btn-primary">@lang('Save')</button>
                                </div>

                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection



@push('js-lib')
    <script src="{{ asset('assets/admin/js/hs-file-attach.min.js') }}"></script>
@endpush

@push('script')
    <script>
        'use strict';
        $(document).ready(function () {
            new HSFileAttach('.js-file-attach')
            HSCore.components.HSTomSelect.init('.js-select')
        });
    </script>
@endpush

