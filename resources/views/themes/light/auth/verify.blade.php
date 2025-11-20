@extends($theme.'layouts.app')

@section('title')
    @lang('Email Verify')
@endsection

@section('content')
@include($theme.'partials.banner')

<section class="login-signup-page">
    <div class="container">
        <div class="row g-lg-5 justify-content-between align-items-center">
            <div class="col-xl-5 col-md-6 order-2 order-md-1">
                <div class="login-signup-form">
                    <form class="login-form" action="{{route('user.mailVerify')}}"  method="post">
                        @csrf
                        <div class="login-signup-header">
                            @if (session('resent'))
                                <div class="alert alert-success" role="alert">
                                    {{ __('A fresh verification link has been sent to your email address.') }}
                                </div>
                            @endif
                            <h4>@lang('Verify Your Email Address')</h4>
                            <div class="description">@lang('Before proceeding, please check your email for a verification link')</div>
                            <button type="submit" class="btn cmn-btn mt-30 w-100">@lang('click here to request another')</button>
                        </div>
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
