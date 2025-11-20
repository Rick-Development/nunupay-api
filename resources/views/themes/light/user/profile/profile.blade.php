@extends($theme.'layouts.user')
@section('title', trans('Profile'))

@section('content')
    <div class="dashboard-wrapper">
        <div class="col-xxl-8 col-lg-10 mx-auto">
        <div class="breadcrumb-area"><h3 class="title">@lang('Profile Settings')</h3></div>

                <div class="account-settings-navbar">
                    <ul class="nav">
                        <li class="nav-item">
                            <a class="nav-link active" aria-current="page" href="{{ route('user.profile') }}">
                                <i class="fa-light fa-user"></i>@lang('Profile')</a>
                        </li>
                        @forelse($kyc as $key => $item)
                            <li class="nav-item">
                                <a class="nav-link" aria-current="page" href="{{ route('user.verify', $item->id) }}">
                                    <i class="fa-solid fa-badge-check"></i>{{ $item->name }}</a>
                            </li>
                        @empty
                        @endforelse
                    </ul>
                </div>

                <div class="account-settings-profile-section">
                    <div class="card">
                        <div class="card-header">
                            <form method="post" action="{{ route('user.profile.update.image') }}"
                                  enctype="multipart/form-data">
                                @csrf
                                <h5 class="card-title">@lang('Profile Details')</h5>
                                <div class="profile-details-section">
                                    <div class="d-flex gap-3 align-items-center">
                                        <div class="image-area">
                                            <img id="profile" src="{{ getFile($user->image_driver, $user->image) }}"
                                                 alt="{{ @$user->fullname() }}">
                                        </div>
                                        <div class="btn-area">
                                            <div class="btn-area-inner d-flex">
                                                <div class="cmn-file-input">
                                                    <label for="formFile"
                                                           class="form-label"><i class="fal fa-camera-alt me-2"></i> {{ trans('choose photo') }}</label>
                                                    <input name="image" class="form-control" type="file" id="formFile"
                                                           onchange="previewImage('profile')">
                                                </div>
                                                <button type="submit"
                                                        class="btn btn-outline-success mx-2 ">@lang('Update')</button>
                                            </div>

                                            <small>@lang('Allowed JPG, JPEG or PNG. Max size of 4M')</small>
                                        </div>
                                        @error('image')<span
                                            class="invalid-feedback">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                            </form>
                        </div>

                        <div class="card-body pt-0">
                            <div class="profile-form-section">
                                <form action="{{ route('user.profile.update')}}" method="post"
                                      enctype="multipart/form-data">
                                    @csrf
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label" for="firstName">@lang('First Name')</label>
                                            <input type="text" id="firstName"
                                                   class="form-control @error('first_name') is-invalid @enderror"
                                                   name="first_name" placeholder="Jhon" autocomplete="off"
                                                   value="{{ old('first_name', $user->firstname) }}"/>
                                            @error('first_name')<span
                                                class="invalid-feedback">{{ $message }}</span>@enderror

                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label" for="lastName">@lang('Last Name')</label>
                                            <input type="text" id="lastName"
                                                   class="form-control @error('last_name') is-invalid @enderror"
                                                   name="last_name" placeholder="Doe" autocomplete="off"
                                                   value="{{ old('last_name', $user->lastname) }}"/>
                                            @error('last_name')<span
                                                class="invalid-feedback">{{ $message }}</span>@enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label" for="username">@lang('username')</label>
                                            <input type="text"
                                                   class="form-control @error('username') is-invalid @enderror"
                                                   name="username" id="username"
                                                   value="{{ old('username', $user->username) }}"
                                                   placeholder="jhondoe" autocomplete="off"/>
                                            @error('username')<span
                                                class="invalid-feedback">{{ $message }}</span>@enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label" for="email">@lang('email address')</label>
                                            <input type="email" class="form-control @error('email') is-invalid @enderror" name="email"
                                                   value="{{ old('email', $user->email) }}" id="email"
                                                   placeholder="example@gmail.com" autocomplete="off" readonly />
                                            @error('email')<span
                                                class="invalid-feedback">{{ $message }}</span>@enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label for="phone" class="form-label">@lang('phone number')</label>
                                            <input type="hidden" name="phone_code" id="phoneCode"/>
                                            <input type="hidden" name="country_code" id="countryCode"/>
                                            <input type="hidden" name="country" id="countryName"/>
                                            <input id="telephone" class="form-control" name="phone" type="tel" onkeyup="this.value = this.value.replace (/^\.|[^\d\.]/g, '')">
                                            @error('phone')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                                        </div>

                                        <div class=" col-md-6">
                                            <label class="form-label" for="select">@lang('preferred language')</label>
                                            <select class="cmn-select2 " name="language_id" id="select">
                                                <option value="" disabled>@lang('Select Language')</option>
                                                @foreach($languages as $la)
                                                    <option value="{{$la->id}}"
                                                        {{ old('language_id', $user->language_id) == $la->id ? 'selected' : '' }}>@lang($la->name)</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label" for="address_one">{{ trans('Address One') }}</label>
                                            <textarea class="form-control @error('address_one') is-invalid @enderror"
                                                      cols="30" rows="2"
                                                      name="address_one" id="address_one"
                                                      placeholder="@lang('Address')">{{ old('address_one', $user->address_one) }}</textarea>
                                            @error('address_one')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label" for="address_two">{{ trans('Address Two') }}</label>
                                            <textarea class="form-control @error('address_two') is-invalid @enderror"
                                                      cols="30" rows="2"
                                                      name="address_two" id="address_two"
                                                      placeholder="@lang('Address')">{{ old('address', $user->address_two) }}</textarea>
                                            @error('address_two')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                        </div>
                                    </div>

                                    <div class="btn-area d-flex g-3">
                                        <button class="cmn-btn">@lang('save changes')</button>
                                        <a  class="cmn-btn3" href="javascript:location.reload()">@lang('Cancel')</a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
        </div>
    </div>

@endsection

@push('notify')
    @if ($errors->any())
        <script>
            "use strict";
            @foreach ($errors->unique() as $error)
                @if (strpos($error, 'image') !== false)
                    Notiflix.Notify.failure("{{ trans($error) }}");
                    @break
                @endif
            @endforeach
        </script>
    @endif
@endpush


@push('css-lib')
    <link rel="stylesheet" href="{{ asset($themeTrue.'css/intlTelInput.min.css') }}">
@endpush

@push('js-lib')
    <script src="{{ asset($themeTrue.'js/intlTelInput.min.js') }}"></script>
@endpush


@push('script')
    <script>
        $(document).ready(function (){

            const input = document.querySelector("#telephone");
            const iti = window.intlTelInput(input, {
                initialCountry: "bd",
                separateDialCode: true,
            });
            input.addEventListener("countrychange", updateCountryInfo);
            updateCountryInfo();
            function updateCountryInfo() {
                const selectedCountryData = iti.getSelectedCountryData();
                console.log(selectedCountryData)
                const phoneCode = '+' + selectedCountryData.dialCode;
                const countryCode = selectedCountryData.iso2;
                const countryName = selectedCountryData.name;
                $('#phoneCode').val(phoneCode);
                $('#countryCode').val(countryCode);
                $('#countryName').val(countryName);
            }

            const initialPhone = "{{ old('phone', $user->phone) }}";
            const initialPhoneCode = "{{ old('phone_code', $user->phone_code) }}";
            const initialCountryCode = "{{ old('country_code', $user->country_code) }}";
            const initialCountry = "{{ old('country', $user->country) }}";
            if (initialPhoneCode) {
                iti.setNumber(initialPhoneCode);
            }
            if (initialCountryCode) {
                iti.setNumber(initialCountryCode);
            }
            if (initialCountry) {
                iti.setNumber(initialCountry);
            }
            if (initialPhone) {
                iti.setNumber(initialPhone);
            }
        })
    </script>
@endpush

