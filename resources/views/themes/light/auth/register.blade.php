@extends($theme.'layouts.app')
@section('title',trans('Signup'))
@section('content')

    <section class="login-signup-page">
        <div class="container">
            <div class="row g-lg-5 justify-content-between align-items-center">
                <div class="col-xl-5 col-md-6 order-2 order-md-1">
                    <div class="login-signup-form">
                        <form action="{{ route('register') }}" method="post">
                            @csrf
                            <div class="login-signup-header">
                                <h4>@lang( @$content->contentDetails[0]->description->title_one )</h4>
                                <div class="description">@lang( @$content->contentDetails[0]->description->title_two )</div>
                            </div>
                            <div class="row g-3">
                                @if(isset($sponsor))
                                    <div class="col-md-12">
                                        <div class="form-group mb-10">
                                            <label>@lang('Sponsor Name')</label>
                                            <input type="text" name="sponsor" class="form-control" id="sponsor"
                                                   placeholder="{{trans('Sponsor By') }}"
                                                   value="{{$sponsor}}" readonly>
                                        </div>
                                    </div>
                                @endif

                                <div class="col-12">
                                    <input type="text" name="first_name" class="form-control" placeholder="@lang('First Name')" value="{{old('first_name')}}">
                                    @error('first_name')
                                        <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                                <div class="col-12">
                                    <input type="text" name="last_name" class="form-control" placeholder="@lang('Last Name')" value="{{old('last_name')}}">
                                    @error('last_name')
                                        <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                                <div class="col-12">
                                    <input type="text" name="username" class="form-control" placeholder="@lang('username')" value="{{old('username')}}">
                                    @error('username')
                                    <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                                <div class="col-12">
                                    <input type="email" name="email" class="form-control" placeholder="@lang('Email')" value="{{old('email')}}">
                                    @error('email')
                                    <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <input id="telephone" class="form-control" name="phone" value="{{old('phone')}}" type="tel">
                                    <input type="hidden" name="phone_code" id="phoneCode" value="{{old('phone_code')}}"/>
                                    <input type="hidden" name="country" id="countryName" value="{{ old('country') }}"/>
                                    <input type="hidden" name="country_code" id="countryCode" value="{{ old('country_code') ?? 'bd' }}"/>
                                </div>
                                @error('phone')
                                <span class="invalid-feedback d-block mt-1"><strong>@lang($message)</strong></span>
                                @enderror

                                <div class="col-12">
                                    <div class="password-box">
                                        <input type="password" name="password" class="form-control password" placeholder="@lang('password')">
                                        <i class="password-icon fa-regular fa-eye toggle-password"></i>
                                    </div>
                                    @error('password')
                                    <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                                <div class="col-12">
                                    <div class="password-box">
                                        <input type="password" name="password_confirmation" class="form-control password" placeholder="@lang('confirm password')">
                                        <i class="password-icon fa-regular fa-eye toggle-password"></i>
                                    </div>
                                    @error('password_confirmation')
                                    <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>

                                @if($basicControl->google_recaptcha === 1 && $basicControl->google_recaptcha_register === 1)
                                    <div class="row mt-4">
                                        <div class="g-recaptcha @error('g-recaptcha-response') is-invalid @enderror"
                                             data-sitekey="{{ env('GOOGLE_RECAPTCHA_SITE_KEY') }}"></div>
                                        @error('g-recaptcha-response')
                                        <span class="invalid-feedback d-block text-danger" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                        @enderror
                                    </div>
                                @endif
                            </div>
                            <button id="signupBtn" type="submit" class="btn cmn-btn mt-30 w-100">@lang('signup')</button>
                            <div class="pt-20 text-center">
                                @lang('Already have an account?')
                                <p class="mb-0 highlight"><a href="{{ route('login') }}">@lang('Login Here')</a></p>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="col-xl-6 col-md-6 order-1 order-md-2 d-none d-md-block">
                    <div class="login-signup-thums">
                        <img src="{{getFile(@$content->media?->image?->driver, @$content->media?->image?->path)  }}" alt="">
                    </div>
                </div>
            </div>
        </div>
    </section>

    @include(template().'sections.footer')

@endsection


@push('css-lib')
    <link rel="stylesheet" href="{{ asset($themeTrue.'css/intlTelInput.min.css') }}">
@endpush

@push('js-lib')
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <script src="{{ asset($themeTrue.'js/intlTelInput.min.js') }}"></script>

@endpush

@push('script')
    <script>

        $(document).ready(() => {
            $('.toggle-password').on('click', function () {
                const passwordInput = $(this).prev('.password');
                const passwordType = passwordInput.attr('type');
                passwordInput.attr('type', passwordType === 'password' ? 'text' : 'password');
                $(this).toggleClass('fa-eye-slash', passwordType === 'password');
            });

            const input = document.querySelector("#telephone");
            const iti = window.intlTelInput(input, {
                initialCountry: $('#countryCode').val(),
                separateDialCode: true,
            });
            function updateCountryInfo() {
                const selectedCountryData = iti.getSelectedCountryData();
                const phoneCode = '+' +selectedCountryData.dialCode;
                const countryName = selectedCountryData.name;
                const countryCode = selectedCountryData.iso2;
                $('#phoneCode').val(phoneCode)
                $('#countryName').val(countryName)
                $('#countryCode').val(countryCode)
            }
            input.addEventListener("countrychange", updateCountryInfo);
            updateCountryInfo();
        });

        $(document).ready(() =>{
            $('.password').on('input', function() {
                let passwordLength = $(this).val().length;
                if (passwordLength < 6) {
                    $('#signupBtn').hide();
                } else {
                    $('#signupBtn').show();
                }
            });
        });
    </script>
@endpush
