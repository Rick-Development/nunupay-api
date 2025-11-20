<!DOCTYPE html>
<html lang="en" @if(session()->get('rtl') == 1) dir="rtl" @endif >

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#5956e9">
    <title> @if(isset($pageSeo['page_title']))@lang($pageSeo['page_title'])@else
        @yield('title')@endif
    </title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @if(isset($pageSeo['meta_title']))
        <meta name="title" content="@lang($pageSeo['meta_title'])">
    @endif
        <meta name="author" content="{{ basicControl()->site_title }}">
    @if(isset($pageSeo['meta_description']))
        <meta name="description" content="@lang($pageSeo['meta_description'])">
    @endif
    @if(isset($pageSeo['meta_keywords']))
        <meta name="keywords" content="@lang(implode(', ', $pageSeo['meta_keywords']))">
    @endif
        <meta name="robots" content="{{ $pageSeo['meta_robots'] ?? '' }}">
        <meta property="og:type" content="website">
        <meta property="og:site_name" content="{{ basicControl()->site_title }}">
    @if(isset($pageSeo['meta_title']))
        <meta property="og:title" content="@lang($pageSeo['meta_title'])">
    @endif
    @if(isset($pageSeo['og_description']))
        <meta property="og:description" content="@lang($pageSeo['og_description'])">
    @endif
    @if(isset($pageSeo['seo_meta_image']))
        <meta property="og:image" content="{{ $pageSeo['seo_meta_image'] }}">
    @endif


    <link href="{{ getFile(basicControl()->favicon_driver, basicControl()->favicon) }}" rel="icon">

    <link rel="stylesheet" href="{{ asset($themeTrue.'css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset($themeTrue.'css/fontawesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset($themeTrue.'css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset($themeTrue.'css/owl.carousel.min.css') }}">
    <link rel="stylesheet" href="{{ asset($themeTrue.'css/owl.theme.default.min.css') }}">
    <link rel="stylesheet" href="{{ asset($themeTrue.'css/slick.css') }}">
    <link rel="stylesheet" href="{{ asset($themeTrue.'css/slick-theme.css') }}">
    <link rel="stylesheet" href="{{ asset($themeTrue.'css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset($themeTrue.'css/nouislider.min.css') }}">
    <link rel="stylesheet" href="{{ asset($themeTrue.'css/flatpickr.min.css') }}">
    @stack('css-lib')
    <link rel="stylesheet" href="{{ asset($themeTrue.'css/style.css') }}">
    @stack('style')

</head>

<body onload=""  class="body">
{{--<body onload="preloader_function()"  class="body">--}}

{{--    <div id="preloader">--}}
{{--        <div class="loader">--}}
{{--            <div class="ring"></div>--}}
{{--            <div class="ring"></div>--}}
{{--            <div class="ring"></div>--}}
{{--            <p>{{ trans('Loading') }}...</p>--}}
{{--        </div>--}}
{{--    </div>--}}

    @include($theme.'partials.nav')


    @if(@$banner && $banner->breadcrumb_status == 1 && isset($banner->breadcrumb_image))
        @include($theme.'partials.banner')
    @endif

    <main id="main header-upper header-fixed">
        @yield('content')
    </main>

    @stack('loadModal')

    <!-- JS Global Compulsory  -->
    <script src="{{ asset('assets/global/js/notiflix-aio-3.2.6.min.js') }}"></script>
    <script src="{{ asset('assets/global/js/vue.min.js') }}"></script>
    <script src="{{ asset('assets/global/js/axios.min.js') }}"></script>
    <script src="{{ asset('assets/global/js/pusher.min.js') }}"></script>

    <!-- JS Library -->
    <script src="{{ asset($themeTrue.'js/jquery-3.6.1.min.js') }}"></script>
    <script src="{{ asset($themeTrue.'js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset($themeTrue.'js/owl.carousel.min.js') }}"></script>
    <script src="{{ asset($themeTrue.'js/swiper-bundle.min.js') }}"></script>
    <script src="{{ asset($themeTrue.'js/slick.min.js') }}"></script>
    <script src="{{ asset($themeTrue.'js/select2.min.js') }}"></script>
    <script src="{{ asset($themeTrue.'js/nouislider.min.js') }}"></script>
    <script src="{{ asset($themeTrue.'js/modernizr.custom.js') }}"></script>
    <script src="{{ asset($themeTrue.'js/flatpickr.js') }}"></script>
    <!-- Main Js link -->
    <script src="{{ asset($themeTrue.'js/main.js') }}"></script>

    @stack('js-lib')

    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    </script>

    @stack('script')

    @include('partials.notify')

    @include('plugins')

{{--    <script>--}}
{{--        let fixed_top = $(".header-upper.header-fixed");--}}
{{--        $(window).on("scroll", function () {--}}
{{--            if ($(window).scrollTop() > 90) {--}}
{{--                fixed_top.addClass("show");--}}
{{--                document.getElementById('sitelogo').src = "{{ getFile(basicControl()->logo_driver, basicControl()->logo) }}"--}}
{{--            } else {--}}
{{--                document.getElementById('sitelogo').src = "{{ getFile(basicControl()->admin_dark_mode_logo_driver, basicControl()->admin_dark_mode_logo) }}"--}}
{{--                fixed_top.removeClass("show");--}}
{{--            }--}}
{{--        });--}}
{{--    </script>--}}

</body>
</html>



