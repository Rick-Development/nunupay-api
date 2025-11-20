<!DOCTYPE html>
<html lang="en" @if(session()->get('rtl') == 1) dir="rtl" @endif >

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>
    <link href="{{ getFile(basicControl()->favicon_driver, basicControl()->favicon) }}" rel="icon">
    <link rel="stylesheet" href="{{ asset($themeTrue.'css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset($themeTrue.'css/fontawesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset($themeTrue.'css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset($themeTrue.'css/style.css') }}">

</head>

<body onload="preloader_function()" class="pb-0">
<div id="preloader">
    <div class="loader">
        <div class="ring"></div><div class="ring"></div><div class="ring"></div><p>{{ trans('Loading') }}...</p>
    </div>
</div>

<section class="error-section">
    <div class="container">
        <div class="row g-5 align-items-center">
            <div class="col-sm-6">
                <div class="error-thum">
                    @hasSection('error_image')
                        @yield('error_image')
                    @else
                        <img src="{{ asset(config('filelocation.error2')) }}" alt="">
                    @endif
                </div>

            </div>
            <div class="col-sm-6">
                <div class="error-content">
                    <div class="error-title">@yield('error_code')</div>
                    <div class="error-info">@yield('error_message')</div>
                    <div class="btn-area">
                        <a href="{{ url('/') }}" class="cmn-btn"> <i class="fa-solid fa-arrow-left me-2"></i> {{ trans('Back to Homepage') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


<script src="{{ asset($themeTrue.'js/jquery-3.6.1.min.js') }}"></script>
<script src="{{ asset($themeTrue.'js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset($themeTrue.'js/main.js') }}"></script>

</body>

</html>
