@extends('admin.layouts.app')
@section('page_title', __('Edit Bank'))
@section('content')
<div class="content container-fluid">
    <div class="page-header">
        <div class="row align-items-end">
            <div class="col-sm mb-2 mb-sm-0">
                <h1 class="page-header-title">@yield('page_title')</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-no-gutter">
                        <li class="breadcrumb-item"><a class="breadcrumb-link"
                                                       href="{{ route('admin.dashboard') }}">@lang('Dashboard')</a></li>
                        <li class="breadcrumb-item active" aria-current="page">@yield('page_title')</li>
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
                        <h2 class="card-title h4 mt-2">@lang('Edit Bank For ')@lang($country->name)</h2>
                        <a href="{{route('admin.countryBank',$country)}}" class="btn btn-sm btn-info">
                            <i class="bi-arrow-left"></i> @lang('Back')
                        </a>
                    </div>
                    <div class="card-body">
                        <form action="{{route('admin.bank.update',$bank)}}" method="post" enctype="multipart/form-data">
                            @csrf
                            @method('put')
                            <div class="row mb-4 d-flex align-items-center">
                                <input type="hidden" name="country_id" value="{{ $country->id }}">
                                <div class="col-md-3">
                                    <label for="name" class="form-label">@lang('Bank Name')</label>
                                    <input type="text" class="form-control  @error('name') is-invalid @enderror"
                                           name="name" id="name" placeholder="@lang("New York")" aria-label="name"
                                           autocomplete="off"
                                           value="{{ old('name',$bank->name) }}">
                                    @error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                </div>

                                <div class="col-md-3">
                                    <label for="localMinAmount" class="form-label">@lang('Minimum Amount')</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control  @error('localMinAmount') is-invalid @enderror"
                                               name="localMinAmount" id="localMinAmount" placeholder="@lang("1000")" aria-label="localMinAmount"
                                               autocomplete="off" value="{{ old('localMinAmount',$bank->localMinAmount) }}">
                                        <span class="input-group-text"> @lang(optional($country->currency)->code) </span>
                                        @error('localMinAmount')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <label for="localMaxAmount" class="form-label">@lang('Maximum Amount')</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control  @error('localMaxAmount') is-invalid @enderror"
                                               name="localMaxAmount" id="localMaxAmount" placeholder="@lang("99000")" aria-label="localMaxAmount"
                                               autocomplete="off" value="{{ old('localMaxAmount',$bank->localMaxAmount) }}">
                                        <span class="input-group-text"> @lang(optional($country->currency)->code) </span>
                                        @error('localMaxAmount')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <label class="row form-check form-switch mt-3" for="state_status">
                                        <span class="col-4 col-sm-9 ms-0 ">
                                          <span class="d-block text-dark">@lang("Bank Status")</span>
                                          <span class="d-block fs-5">@lang("Click on this Switch for change the status.")</span>
                                        </span>

                                        <span class="col-2 col-sm-3 text-end">
                                            <input type='hidden' value='0' name='status'>
                                            <input class="form-check-input @error('status') is-invalid @enderror" type="checkbox"
                                                   name="status" id="stateStatus" value="1"
                                                   {{ old('status', $bank->status) == 1 ? 'checked' : '' }}>
                                            <label class="form-check-label text-center" for="stateStatus"></label>
                                        </span>
                                        @error('status')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                                    </label>
                                </div>
                                <div class="col-md-6">
                                    <label for="category" class="form-label">@lang("Service Type")</label>
                                    <div class="tom-select-custom">
                                        <select class="js-select form-select" autocomplete="off" name="service_id"
                                                data-hs-tom-select-options='{
                                                      "placeholder": "@lang('select a service')",
                                                      "hideSearch": true
                                                    }' required>
                                            @foreach($services as $item)
                                                <option value="{{ $item->id }}" {{ $item->id == old('service_id', $bank->service_id) ? 'selected' : '' }}>@lang($item->name)</option>
                                            @endforeach
                                        </select>
                                        @error("service_id")
                                        <span class="invalid-feedback d-block">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                            </div>

                            <div class="card mb-3 mb-lg-5">
                                <div class="card-header card-header-content-sm-between">
                                    <h4 class="card-header-title mb-2 mb-sm-0">@lang("Services Form")</h4>
                                    <div class="d-sm-flex align-items-center gap-2">
                                        <a class="js-create-field btn btn-outline-info btn-sm add_field_btn"
                                           href="javascript:void(0);">
                                            <i class="bi-plus"></i> @lang("Add Field")
                                        </a>
                                    </div>
                                </div>

                                <div class="table-responsive datatable-custom dynamic-feild-table">
                                    <table id="datatable"
                                           class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table overflow-visible">
                                        <thead class="thead-light">
                                        <tr>
                                            <th>@lang("Field Name")</th>
                                            <th>@lang("Input Type")</th>
                                            <th>@lang("Validation Type")</th>
                                            <th></th>
                                        </tr>
                                        </thead>

                                        @php
                                            $oldInputFormCount = old('field_name', $bank->services_form) ? count( old('field_name', (array) $bank->services_form)) : 0;
                                        @endphp
                                        <tbody id="addFieldContainer">
                                        @if( 0 < $oldInputFormCount)
                                            @php
                                                $oldKycInputForm = collect(old('field_name', (array)$bank->services_form))->values();
                                            @endphp

                                            @for($i = 0; $i < $oldInputFormCount; $i++)
                                                <tr>
                                                    <td>
                                                        <input type="text" name="field_name[]" class="form-control"
                                                               value="{{ old("field_name.$i", $oldKycInputForm[$i]->field_label ?? '') }}"
                                                               placeholder="@lang("Field Name")" autocomplete="off">
                                                        @error("field_name.$i")
                                                        <span class="invalid-feedback d-block">{{ $message }}</span>
                                                        @enderror
                                                    </td>

                                                    <td>
                                                        <div class="tom-select-custom">
                                                            <select class="js-select form-select"
                                                                    name="input_type[]"
                                                                    data-hs-tom-select-options='{
                                                                        "searchInDropdown": false,
                                                                        "hideSearch": true
                                                                      }'>
                                                                <option
                                                                    value="text" {{ old("input_type.$i", $oldKycInputForm[$i]->type ?? '') == 'text' ? 'selected' : '' }}>@lang('Text')</option>
                                                                <option
                                                                    value="textarea" {{ old("input_type.$i", $oldKycInputForm[$i]->type ?? '') == 'textarea' ? 'selected' : '' }}>@lang('Textarea')</option>
                                                                <option
                                                                    value="file" {{ old("input_type.$i", $oldKycInputForm[$i]->type ?? '') == 'file' ? 'selected' : '' }}>@lang('File')</option>
                                                                <option
                                                                    value="number" {{ old("input_type.$i", $oldKycInputForm[$i]->type ?? '') == 'number' ? 'selected' : '' }}>@lang('Number')</option>
                                                                <option
                                                                    value="date" {{ old("input_type.$i", $oldKycInputForm[$i]->type ?? '') == 'date' ? 'selected' : '' }}>@lang('Date')</option>
                                                            </select>
                                                            @error("input_type.$i")
                                                            <span
                                                                class="invalid-feedback d-block">{{ $message }}</span>
                                                            @enderror
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="tom-select-custom">
                                                            <select class="js-select form-select"
                                                                    name="is_required[]"
                                                                    data-hs-tom-select-options='{
                                                                        "searchInDropdown": false,
                                                                        "hideSearch": true
                                                                      }'>
                                                                <option
                                                                    value="required" {{ old("is_required.$i", $oldKycInputForm[$i]->validation ?? '') == 'required' ? 'selected' : '' }}>@lang('Required')</option>
                                                                <option
                                                                    value="optional" {{ old("is_required.$i", $oldKycInputForm[$i]->validation ?? '') == 'optional' ? 'selected' : '' }}>@lang('Optional')</option>
                                                            </select>
                                                            @error("is_required.$i")
                                                            <span
                                                                class="invalid-feedback d-block">{{ $message }}</span>
                                                            @enderror
                                                        </div>
                                                    </td>
                                                    <td class="table-column-ps-0">
                                                        <button type="button" class="btn btn-white remove-row">
                                                            <i class="bi-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endfor
                                        @endif

                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="d-flex justify-content-start mt-4">
                                <button type="submit" class="btn btn-primary">@lang('Save changes')</button>
                            </div>

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
            $(document).on('click', '.add_field_btn', function () {
                let rowCount = $('#addFieldContainer tr').length;
                let markUp = `
                            <tr id="addVariantsTemplate">
                                <td>
                                    <input type="text" class="form-control" name="field_name[]" placeholder="@lang("Account Holder Name")" autocomplete="off" required>
                                </td>
                                <td>
                                    <div class="tom-select-custom">
                                        <select class="js-select-dynamic-input-type${rowCount} form-select" name="input_type[]"
                                                data-hs-tom-select-options='{"searchInDropdown": false, "hideSearch": true}'>
                                            <option value="text">@lang('Text')</option>
                                            <option value="textarea">@lang('Textarea')</option>
                                            <option value="file">@lang('File')</option>
                                            <option value="number">@lang('Number')</option>
                                            <option value="date">@lang('Date')</option>
                                        </select>
                                    </div>
                                </td>
                                <td>
                                    <div class="tom-select-custom">
                                        <select class="js-select-dynamic-validation-type${rowCount} form-select" name="is_required[]"
                                                data-hs-tom-select-options='{"searchInDropdown": false, "hideSearch": true}'>
                                            <option value="required">@lang('Required')</option>
                                            <option value="optional">@lang('Optional')</option>
                                        </select>
                                    </div>
                                </td>
                                <td class="table-column-ps-0">
                                    <button type="button" class="btn btn-white remove-row">
                                                            <i class="bi-trash"></i>
                                                        </button>
                                </td>
                            </tr>`;

                $("#addFieldContainer").append(markUp);

                const selectClass = `.js-select-dynamic-input-type${rowCount}, .js-select-dynamic-validation-type${rowCount}`;

                $("#addFieldContainer").find(selectClass).each(function () {
                    HSCore.components.HSTomSelect.init($(this));
                });
            });

            $(document).on('click', '.remove-row', function (e) {
                e.preventDefault();
                $(this).closest('tr').remove();
            });

            HSCore.components.HSTomSelect.init('.js-select')
        });

    </script>
@endpush



