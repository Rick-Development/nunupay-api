@extends($theme.'layouts.app')
@section('title', 'Password Reset')
@section('content')
@include($theme.'partials.banner')

<section class="login-signup-page">
    <div class="container">
        <div class="row g-lg-5 justify-content-between align-items-center">
            <div class="col-xl-5 col-md-6 order-2 order-md-1">
                <div class="login-signup-form">

                @if (session('status'))
                    <div class="alert alert-success" role="alert">
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('password.email') }}" class="php-email-form">
                    @csrf
                    <div class="login-signup-header">
                        <h4>@lang('Forgot Your Password!')</h4>
                        <div class="description">@lang('Enter Your Email Address & Send Request for reset link')</div>
                    </div>
                    <div class="row g-3">
                        <div class="col-12">
                            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror"
                                   name="email" value="{{ old('email') }}" required autocomplete="email"
                                   autofocus placeholder="@lang('Enter Your Email Address')">
                            @error('email')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>
                    <button type="submit" class="btn cmn-btn mt-30 w-100">@lang('Send Password Reset Link') </button>
                </form>

                </div>
            </div>
            <div class="col-xl-6 col-md-6 order-1 order-md-2 d-none d-md-block">
                <div class="login-signup-thums">
                    <img src="{{ isset($user_verify->media->image_two)
                                ? getFile(@$user_verify->media->image_two->driver, @$user_verify->media->image_two->path)
                                : asset($themeTrue.'img/login-signup/login-signup.jpg') }}" alt=""
                    >
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
