@extends($theme.'layouts.app')
@section('title',trans('Login'))
@section('content')

<section class="login-signup-page">
    <div class="container">
        <div class="row g-lg-5 justify-content-between align-items-center">
            <div class="col-xl-5 col-md-6 order-2 order-md-1">
                <div class="login-signup-form">
                    <form action="{{ route('login') }}" method="post" class="php-email-form" id="form">
                        @csrf
                        <div class="login-signup-header">
                            <h4>@lang( @$content->contentDetails[0]->description->title_one )</h4>
                            <div class="description">@lang( @$content->contentDetails[0]->description->title_two )</div>
                        </div>
                        <div class="row g-3">
                            <div class="col-12">
                                <input type="text" name="username" class="form-control" value="{{ old('username','demouser') }}"  placeholder="@lang('Username or Email')">
                                @error('username')
                                    <span class="invalid-feedback d-block" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="col-12">
                                <div class="password-box">
                                    <input type="password" name="password" class="form-control password" value="demouser" placeholder="@lang('Password')">
                                    <i class="password-icon fa-regular fa-eye"></i>
                                </div>
                            </div>

                            @if($basicControl->manual_recaptcha == 1 && $basicControl->manual_recaptcha_login == 1)
                                <div class="col-12">
                                    <div class="input-group">
                                        <span class="input-group-text w-50 bg-white">
                                            <img src="{{ route('captcha') . '?rand=' . rand() }}" id="captcha_image"
                                                 class="img-fluid rounded" alt="Captcha Image">
                                        </span>
                                        <input type="text" class="form-control" name="captcha" id="captcha"
                                               autocomplete="off" placeholder="Enter Captcha" required>
                                        <a href="javascript: refreshCaptcha();" class="input-group-text">
                                            <i class="fas fa-sync-alt text-white" aria-hidden="true"></i>
                                        </a>
                                        @error('captcha')
                                        <span class="invalid-feedback d-block"
                                              role="alert"><strong>{{ $message }}</strong></span>
                                        @enderror
                                    </div>
                                </div>
                            @endif

                            @if($basicControl->google_recaptcha == 1 && $basicControl->google_recaptcha_login == 1)
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

                            <div class="col-12 mt-4">
                                <div class="form-check d-flex justify-content-between">
                                    <div class="check">
                                        <input class="form-check-input" type="checkbox" name="remember"
                                               id="remember" {{ old('remember') ? 'checked' : '' }}>

                                        <label class="form-check-label" for="remember">
                                            {{ __('Remember Me') }}
                                        </label>
                                    </div>
                                    <div class="forgot highlight">
                                        <a class="btn btn-link" href="{{ route('password.request') }}">
                                            {{ __('Forgot Your Password?') }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn cmn-btn mt-20 w-100" id="submitBtn">{{ trans('Log In') }}</button>

                        @if(config('socialite.google_status') || config('socialite.facebook_status') || config('socialite.github_status'))
                            <hr class="divider">
                        @endif

                        <div class="cmn-btn-group">
                            <div class="row g-2 justify-content-center">
                                @if(config('socialite.google_status'))
                                    <div class="col-sm-4">
                                        <a href="{{route('socialiteLogin','google')}}"
                                           class="btn cmn-btn3 w-100 social-btn"><img
                                                src="{{$themeTrue.'img/login-signup/google.png'}}"
                                                alt="...">@lang('Google')
                                        </a>
                                    </div>
                                @endif
                                @if(config('socialite.facebook_status'))
                                    <div class="col-sm-4">
                                        <a href="{{route('socialiteLogin','facebook')}}"
                                           class="btn cmn-btn3 w-100 social-btn"><img
                                                src="{{$themeTrue.'img/login-signup/facebook.png'}}"
                                                alt="...">@lang('Facebook')
                                        </a>
                                    </div>
                                @endif
                                @if(config('socialite.github_status'))
                                    <div class="col-sm-4">
                                        <a href="{{route('socialiteLogin','github')}}"
                                           class="btn cmn-btn3 w-100 social-btn"><img
                                                src="{{$themeTrue.'img/login-signup/github.png'}}"
                                                alt="...">@lang('Github')
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>



                        <div class="pt-20 text-center">
                            @lang("Don't have an account?")
                            <p class="mb-0 highlight"><a href="{{ route('register') }}">@lang('Create a Account')</a></p>
                        </div>
                    </form>
                </div>
            </div>
            <div class="col-xl-6 col-md-6 order-1 order-md-2 d-none d-md-block">
                <div class="login-signup-thums">
                    <img src="{{getFile(@$content->media->image->driver, @$content->media->image->path)  }}" alt="">
                </div>
            </div>
        </div>
    </div>
</section>

    @include(template().'sections.footer')

@endsection

@push('js-lib')
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
@endpush

@push('script')
    <script>
        $(document).ready(function () {
            $('#submitBtn').click(function () {
                $(this).prop('disabled', true);
                $('#form').submit();
            });
        });

        const password = document.querySelector('.password');
        const passwordIcon = document.querySelector('.password-icon');

        passwordIcon.addEventListener("click", function () {
            if (password.type == 'password') {
                password.type = 'text';
                passwordIcon.classList.add('fa-eye-slash');
            } else {
                password.type = 'password';
                passwordIcon.classList.remove('fa-eye-slash');
            }
        })

        function refreshCaptcha(){
            let img = document.images['captcha_image'];
            img.src = img.src.substring(
                0,img.src.lastIndexOf("?")
            )+"?rand="+Math.random()*1000;
        }

    </script>
@endpush
