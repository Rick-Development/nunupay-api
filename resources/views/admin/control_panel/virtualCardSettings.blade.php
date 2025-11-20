@extends('admin.layouts.app')
@section('page_title', __('Virtual Card Setting'))
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
                                            <h6 class="m-0 font-weight-bold text-primary">@lang('Virtual-Card Settings')</h6>
                                        </div>
                                        <div class="card-body">
                                            <form action="{{ route('admin.virtual-card.settings') }}" method="post">
                                                @csrf
                                                <div class="row">

                                                    <div class="col-md-6">
                                                        <label class="row form-check form-switch mt-3" for="multipleStatus">
                                                            <span class="col-4 col-sm-9 ms-0 ">
                                                                <span class="d-block text-dark">@lang("Allow existing users on create multiple card")</span>
                                                                <span class="d-block fs-5">@lang("Click on this Switch for change the status.")</span>
                                                            </span>

                                                            <span class="col-2 col-sm-3 text-end">
                                                                <input type='hidden' value='0' name='v_card_multiple'>
                                                                <input class="form-check-input @error('v_card_multiple') is-invalid @enderror"
                                                                       type="checkbox" name="v_card_multiple" id="multipleStatus" value="1"
                                                                    {{ old('v_card_multiple', $basicControl->v_card_multiple) == 1 ? 'checked' : '' }}>
                                                                <label class="form-check-label text-center" for="multipleStatus"></label>
                                                            </span>
                                                            @error('v_card_multiple')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                                                        </label>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label class="form-label">
                                                                @lang('Charges exiting users per card request')
                                                            </label>
                                                            <div class="input-group">
                                                                <input type="number" class="form-control" step="0.001"
                                                                       name="v_card_charge"
                                                                       value="{{$basicControl->v_card_charge}}">
                                                                <div class="input-group-prepend">
																	<span class="form-control">{{ $basicControl->base_currency }}</span>
                                                                </div>
                                                            </div>
                                                            @error('v_card_charge')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
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


