@extends('admin.layouts.app')
@section('page_title', __('Edit Virtual Card'))
@section('content')
<div class="content container-fluid">
    <div class="page-header">
        <div class="row align-items-end">
            <div class="col-sm mb-2 mb-sm-0">
                <h1 class="page-header-title">@yield('page_title')</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-no-gutter">
                        <li class="breadcrumb-item"><a class="breadcrumb-link"
                                                       href="{{ route('admin.dashboard') }}">@lang("Dashboard")</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">@lang('Virtual Card')</li>
                        <li class="breadcrumb-item active" aria-current="page">@lang('Available Methods')</li>

                    </ol>
                </nav>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <div class="d-grid gap-3 gap-lg-5">
                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h2 class="card-title h4 mt-2">@lang('Edit Virtual Card')</h2>
                        <a href="{{route('admin.virtual.card')}}" class="btn btn-sm btn-info">
                            <i class="bi-arrow-left"></i> @lang('Back')
                        </a>
                    </div>
                    <div class="card-body">
                        <form method="post"
                              action="{{route('admin.virtual.cardUpdate',$virtualCardMethod->id)}}"
                              enctype="multipart/form-data">
                            @csrf
                            @method('put')
                            @if($virtualCardMethod->alert_message)
                                <div class="row">
                                    <div class="col-md-12">
                                        <label class="text-warning">{{$virtualCardMethod->alert_message}}</label>
                                    </div>
                                </div>
                            @endif
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="form-label" for="name">@lang('Name')</label>
                                        <input type="text" value="@lang($virtualCardMethod->name)"
                                               name="name"
                                               class="form-control mt-2 @error('name') is-invalid @enderror"
                                               disabled required>
                                        <div class="invalid-feedback">
                                            @error('name') @lang($message) @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="tom-select-custom">
                                        <label class="form-label" for="currency">@lang('Debit Currency')</label>
                                        <select class="js-select form-select db_currency mt-2 @error('currency') is-invalid @enderror"
                                                name="debit_currency" required>
                                            @foreach($virtualCardMethod->currencies as $key => $currency)
                                                @foreach($currency as $curKey => $singleCurrency)
                                                    <option value="{{ $curKey }}"
                                                            {{ old('debit_currency', $virtualCardMethod->debit_currency) == $curKey ? 'selected' : '' }} data-fiat="{{ $key }}">{{ trans($curKey) }}</option>
                                                @endforeach
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback">
                                            @error('debit_currency') @lang($message) @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="tom-select-custom">
                                        <label class="form-label" for="currency">@lang('Currency')</label>
                                        <select class="js-select form-select mt-2 @error('currency') is-invalid @enderror"
                                            name="currency[]" multiple="multiple" id="selectCurrency" required >
                                            @foreach($virtualCardMethod->currencies as $key => $currency)
                                                @foreach($currency as $curKey => $singleCurrency)
                                                    <option value="{{ $curKey }}"
                                                            {{ in_array($curKey,$virtualCardMethod->currency) == true ? 'selected' : '' }} data-fiat="{{ $key }}">{{ trans($curKey) }}</option>
                                                @endforeach
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback">
                                            @error('currency') @lang($message) @enderror
                                        </div>
                                    </div>
                                </div>

                            </div>
                            @if($virtualCardMethod->add_fund_parameter)
                                <div class="row mt-3">
                                    @foreach($virtualCardMethod->add_fund_parameter as $key => $currency)
                                        <div
                                            class="col-md-6 {{in_array($key,$virtualCardMethod->currency)?'':'d-none'}}">
                                            <div class="card card-primary shadow params-color">
                                                <div
                                                    class="card-header text-dark font-weight-bold">{{$key}} @lang('Charges and limits')</div>
                                                <div class="card-body">
                                                    <div class="row">
                                                        @foreach($currency as $key1 => $parameter)
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label class="form-label"
                                                                        for="{{ $parameter->field_level }}">{{ __(ucfirst(str_replace('_',' ', $parameter->field_level))) }}</label>
                                                                    <div class="input-group">
                                                                        <input type="text"
                                                                               name="fund[{{$key}}][{{$key1}}]"
                                                                               class="form-control"
                                                                               value="{{$parameter->field_value}}">
                                                                        <div
                                                                            class="input-group-prepend">
                                                                    <span
                                                                        class="form-control">{{$parameter->field_level == 'Percent Charge' ? '%':$key}}</span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                            <div class="row mt-4">
                                @foreach ($virtualCardMethod->parameters as $key => $parameter)
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="form-label"
                                                for="{{ $key }}">{{ __(strtoupper(str_replace('_',' ', $key))) }}</label>
                                            <input type="text" name="{{ $key }}"
                                                   value="{{ old($key, $parameter) }}"
                                                   id="{{ $key }}"
                                                   class="form-control @error($key) is-invalid @enderror">
                                            <div class="invalid-feedback">
                                                @error($key) @lang($message) @enderror
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            @if($virtualCardMethod->extra_parameters)
                                <div class="row">
                                    @foreach($virtualCardMethod->extra_parameters as $key => $param)
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="form-label"
                                                    for="{{ $key }}">{{ __(strtoupper(str_replace('_',' ', $key))) }}</label>
                                                <div class="input-group">
                                                    <input type="text" name=""
                                                           class="form-control"
                                                           value="{{$param}}"
                                                           disabled>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <div class="row mb-3 mt-3">
                                <div class="col-md-12">
                                    <label for="summernote" class="form-label">@lang("Information Box")</label>
                                    <textarea class="form-control" name="info_box" id="summernote"
                                              rows="3">{{ old("info_box", optional($virtualCardMethod)->info_box) }}</textarea>
                                    @error("info_box")<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                                </div>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-check form-check-dashed" for="imageUploader">
                                    @lang('Method Image')
                                    <img id="uploadImage"
                                         class="avatar avatar-xl avatar-4x3 avatar-centered h-100 mb-2"
                                         src="{{ getFile($virtualCardMethod->image_driver, $virtualCardMethod->image) }}"
                                         alt="Image Description" data-hs-theme-appearance="default">
                                    <img id="uploadImage"
                                         class="avatar avatar-xl avatar-4x3 avatar-centered h-100 mb-2"
                                         src="{{ getFile($virtualCardMethod->driver, $virtualCardMethod->image) }}"
                                         alt="Image Description" data-hs-theme-appearance="dark">
                                    <span class="d-block">@lang("Browse your file here")</span>
                                    <input type="file" class="js-file-attach form-check-input" name="image"
                                           id="imageUploader" data-hs-file-attach-options='{
                                                      "textTarget": "#uploadImage",
                                                      "mode": "image",
                                                      "targetAttr": "src",
                                                      "allowTypes": [".png", ".jpeg", ".jpg"]
                                                   }'>
                                </label>
                                @error("image")
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                                @enderror
                            </div>

                            <button type="submit" class="btn btn-block btn-primary">@lang('Save Changes')</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


@push('css-lib')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/summernote-bs5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/tom-select.bootstrap5.css') }}">
@endpush

@push('js-lib')
    <script src="{{ asset('assets/admin/js/summernote-bs5.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/hs-file-attach.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/tom-select.complete.min.js') }}"></script>
@endpush


@push('script')
    <script>
        'use strict';
        $(document).ready(function () {
            $('#summernote').summernote({
                placeholder: 'Info Box.',
                height: 80,
                callbacks: {
                    onBlurCodeview: function () {
                        let codeviewHtml = $(this).siblings('div.note-editor').find('.note-codable').val();
                        $(this).val(codeviewHtml);
                    }
                },
            });

            new HSFileAttach('.js-file-attach')
            HSCore.components.HSTomSelect.init('.js-select')
        });

    </script>
@endpush

