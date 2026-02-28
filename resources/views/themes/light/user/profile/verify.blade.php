@extends($theme.'layouts.user')
@section('title', trans('Identity Verify'))

@section('content')
    <div class="dashboard-wrapper">
        <div class="col-xxl-8 col-lg-10 mx-auto">
            <div class="breadcrumb-area"><h3 class="title">@lang('Identity Verify')</h3></div>

            <div class="account-settings-navbar">
                <ul class="nav">
                    <li class="nav-item">
                        <a class="nav-link" aria-current="page" href="{{ route('user.profile') }}">
                            <i class="fa-light fa-user"></i>@lang('Profile')</a>
                    </li>
                    @forelse($kyc as $key => $k)
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('user.verify') && $item->id == $k->id ? 'active' : '' }}"
                               aria-current="page" href="{{ route('user.verify', $k->id) }}">
                                <i class="fa-solid fa-badge-check"></i>{{ $k->name }}</a>
                        </li>
                    @empty
                    @endforelse
                </ul>
            </div>

            @if($userKyc && $userKyc->status == 0)
                <div class="alert alert-warning alert-dismissible" role="alert">
                    <div class="icon-area"><i class="fa-light fa-circle-exclamation"></i></div>
                    <div class="text-area">
                        <div class="title">@lang('Your ' . $userKyc->kyc_type . ' is pending..')</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"><i
                            class="fa-regular fa-xmark"></i></button>
                </div>
            @elseif($userKyc && $userKyc->status == 1)
                <div class="alert alert-success alert-dismissible" role="alert">
                    <div class="icon-area"><i class="fa-light fa-circle-check"></i></div>
                    <div class="text-area">
                        <div class="title">@lang('Your ' . $userKyc->kyc_type . ' has already been completed.')</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"><i
                            class="fa-regular fa-xmark"></i></button>
                </div>
            @elseif($userKyc && $userKyc->status == 2)
                <div class="alert alert-danger alert-dismissible justify-content-between flex-column gap-3 flex-sm-row"
                     role="alert">
                    <div class="d-flex align-items-center">
                        <div class="icon-area"><i class="fa-light fa-triangle-exclamation"></i></div>
                        <div class="text-area">
                            <div class="title">@lang('Your ' . $userKyc->kyc_type . ' has been rejected.')</div>
                        </div>
                    </div>
                    <button class="delete-btn text-nowrap" data-bs-target="#rejectReason" data-bs-toggle="modal">
                        @lang('Reason')</button>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                        <i class="fa-regular fa-xmark"></i></button>
                </div>
            @endif

            @if(!$userKyc  || $userKyc->status == 2)
                <form action="{{ route('user.kyc.verification.submit') }}" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="account-settings-profile-section">
                        <div class="card">
                            <div class="card-header border-0 text-start text-md-center">
                                <h5 class="card-title">{{ trans('KYC Information') }}</h5>
                                <p>{{ trans('Verify your process instantly.') }}</p>
                            </div>
                            <div class="card-body pt-0">
                                <div class="row g-2">
                                    <input type="hidden" name="type" value="{{ $item->id }}">
                                    @foreach($item->input_form as $k => $value)
                                        <div class="col-12">
                                            <label class="form-label">{{ $value->field_label }}</label>
                                            @if($value->type == "file")
                                                <div class="upload-image col-md-6 mt-3 mb-3">
                                                    <div class="d-flex gap-3 align-items-center">
                                                        <div class="btn-area">
                                                            <div class="btn-area-inner d-flex">
                                                                <div class="cmn-file-input">
                                                                    <label for="formFile" class="form-label cmn-btn">
                                                                        <i class="fal fa-camera-alt me-2"></i>
                                                                        {{ trans('Upload File') }}
                                                                    </label>
                                                                    <input class="form-control"
                                                                           name="{{ @$value->field_name }}" type="file"
                                                                           id="formFile"
                                                                           onchange="previewImage('upload')">
                                                                </div>
                                                            </div>
                                                            @if($errors->has(@$value->field_name))
                                                                <div
                                                                    class="error text-danger">@lang($errors->first(@$value->field_name)) </div>
                                                            @endif
                                                        </div>
                                                        <div class="image-area">
                                                            <img id="upload"
                                                                 src="{{ asset(config('filelocation.default2')) }}"
                                                                 alt="">
                                                        </div>
                                                    </div>
                                                </div>
                                            @elseif($value->type == "textarea")
                                                <textarea class="form-control" id="" cols="30" rows="5"
                                                          name="{{ $value->field_name }}"
                                                          placeholder="{{ $value->field_label }}"
                                                >{{ old($value->field_name, $userKyc->kyc_info->{$value->field_name}->field_value ?? '') }}</textarea>
                                            @elseif($value->type == "date")
                                                <input type="date" class="form-control"
                                                       name="{{ $value->field_name }}"
                                                       value="{{ old($value->field_name,  $userKyc->kyc_info->{$value->field_name}->field_value ?? '') }}"
                                                       placeholder="{{ $value->field_label }}"
                                                       id="datePick"
                                                       @if($value->validation == "required") required @endif
                                                >
                                            @else
                                                <input type="{{ $value->type }}" class="form-control"
                                                       name="{{ $value->field_name }}"
                                                       value="{{ old($value->field_name,  $userKyc->kyc_info->{$value->field_name}->field_value ?? '') }}"
                                                       placeholder="{{ $value->field_label }}"
                                                       @if($value->type == "number") onkeyup="this.value = this.value.replace (/^\.|[^\d\.]/g, '')"
                                                       @endif
                                                       @if($value->validation == "required") required @endif
                                                >
                                            @endif
                                            @if($errors->has($value->field_name))
                                                <div
                                                    class="error text-danger">@lang($errors->first($value->field_name)) </div>
                                            @endif

                                        </div>
                                    @endforeach

                                    <div class="btn-area">
                                        <button type="submit" class="cmn-btn mt-2">
                                            {{ ($userKyc && $userKyc->status == 2) ? trans('re-submit') : trans('submit') }}
                                        </button>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            @endif
        </div>
    </div>

@endsection

@push('loadModal')
    @if($userKyc && $userKyc->status == 2)
        <div id="rejectReason" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="reason-modalLabel"
             aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title text-dark font-weight-bold"
                            id="reason-modalLabel">@lang('Rejected Reason ')</h4>
                        <button type="button" class="cmn-btn-close" data-bs-dismiss="modal" aria-label="Close">
                            <i class="fa-light fa-xmark"></i></button>
                    </div>
                    <div class="modal-body">
                        <div class="row mt-2">
                            <div class="col-md-12">
                                <div class="modal-body text-justify">
                                    @lang($userKyc->reason)
                                    <br><br>
                                    <p>{{ trans('Please Try again with the correct Information') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="cmn-btn2" data-bs-dismiss="modal">@lang('Close')</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endpush


