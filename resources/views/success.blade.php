<!DOCTYPE html>
<html lang="en" @if(session()->get('rtl') == 1) dir="rtl" @endif >
<head>
    <meta charset="utf-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@lang('Payment Success') | @lang(basicControl()->site_title)</title>
    <link href="{{ getFile(basicControl()->favicon_driver, basicControl()->favicon) }}" rel="icon">

    <link rel="stylesheet" href="{{ asset($themeTrue.'css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset($themeTrue.'css/style.css') }}">
    <script src="{{ asset($themeTrue.'js/bootstrap.bundle.min.js') }}"></script>

    <style>
        .success-section {
            height: 100vh;
        }

        .success-box {
            text-align: center;
            max-width: 600px;
            border: 1px solid var(--border-color1);
            padding: 40px 30px;
            box-shadow: var(--shadow2);
            border-radius: 15px;
        }

        .success-box .image-box {
            margin-bottom: 10px;
        }

        .success-box .image-box img {
            max-width: 150px;
        }
        .countdownStyle{
            font-size: 18px;
            margin-top: 10px;
        }
    </style>
</head>
<body class="p-0">
<section class="success-section">
    <div class="container h-100 justify-content-center align-items-center d-flex">
        <div class="success-box">
            <div class="image-box">
                <img src="{{asset("assets/global/img/check.png")}}" alt="">
            </div>
            <div class="content-box">
                <h2>{{ trans('Payment Successful') }}</h2>
                <p>{{ trans('Thanks a bunch for filling that out. It means a lot to us, just like you do! We really appreciate you
                    giving us a moment of your time today. Thanks for being with us.') }}</p>
                <a href="{{ route('user.dashboard') }}" class="cmn-btn mt-20">{{ trans('Go to Dashboard') }}</a>
            </div>

            <div class="countdownStyle" >{{ trans('Redirecting to home in') }} <span id="countdown">10</span>
                seconds...
            </div>

        </div>
    </div>
</section>

<script>
    let countdown = 10;

    function updateCountdown() {
        document.getElementById('countdown').innerText = countdown;
    }

    function redirectWithCountdown() {
        updateCountdown();
        var countdownInterval = setInterval(function () {
            countdown--;
            updateCountdown();
            if (countdown <= 0) {
                clearInterval(countdownInterval);
                window.location.href = '{{ url('/') }}';
            }
        }, 1000);
    }

    setTimeout(redirectWithCountdown, 3000);
</script>

</body>
</html>
