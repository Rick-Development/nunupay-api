<!DOCTYPE html>
<html lang="en" @if(session()->get('rtl') == 1) dir="rtl" @endif >
<head data-notfoundlight="{{asset(config('filelocation.not_found_dark'))}}"
      data-notfounddark="{{asset(config('filelocation.not_found_light'))}}">
    <meta charset="UTF-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <link href="{{ getFile(basicControl()->favicon_driver, basicControl()->favicon) }}" rel="icon">

    <title> @yield('title') </title>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="stylesheet" href="{{ asset($themeTrue.'css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset($themeTrue.'css/fontawesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset($themeTrue.'css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset($themeTrue.'css/owl.carousel.min.css') }}">
    <link rel="stylesheet" href="{{ asset($themeTrue.'css/owl.theme.default.min.css') }}">
    <link rel="stylesheet" href="{{ asset($themeTrue.'css/swiper-bundle.min.css') }}">
    <link rel="stylesheet" href="{{ asset($themeTrue.'css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset($themeTrue.'css/flatpickr.min.css') }}">

    @stack('css-lib')

    <link rel="stylesheet" href="{{ asset($themeTrue.'css/dashboard.css') }}">

    @stack('style')

</head>

<body onload="preloader_function()" class="">

<div id="preloader">
    <div class="loader">
        <div class="ring"></div>
        <div class="ring"></div>
        <div class="ring"></div>
        <p>{{ trans('Loading') }}...</p>
    </div>
</div>

@include($theme.'partials.user_header')
@include($theme.'partials.user_sidebar')


@yield('content')

@stack('loadModal')

<script src="{{ asset($themeTrue.'js/jquery-3.6.1.min.js') }}"></script>
<script src="{{ asset($themeTrue.'js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset($themeTrue.'js/select2.min.js') }}"></script>
<script src="{{ asset($themeTrue.'js/owl.carousel.min.js') }}"></script>
<script src="{{ asset($themeTrue.'js/dashboard.js') }}" defer></script>
<script src="{{ asset($themeTrue.'js/apexcharts.min.js') }}"></script>
<script src="{{ asset($themeTrue.'js/flatpickr.js') }}"></script>
@stack('js-lib')

<!-- GLOBAL link -->
<script src="{{ asset('assets/global/js/notiflix-aio-3.2.6.min.js') }}"></script>
<script src="{{ asset('assets/global/js/pusher.min.js') }}"></script>
<script src="{{ asset('assets/global/js/vue.min.js') }}"></script>
<script src="{{ asset('assets/global/js/axios.min.js') }}"></script>
<script src="{{ asset('assets/global/js/debounce_lodash@4.js') }}"></script>



@stack('script')

@include('partials.notify')

</body>
</html>

