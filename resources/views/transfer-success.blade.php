@extends($theme.'layouts.user')
@section('title', trans('Transfer Success'))

@section('content')
    <div class="dashboard-wrapper">
        <div class="success-section">
            <div class="success-box">
                <div class="image-box">
                    <img src="{{ asset('assets/global/img/success.gif') }}" alt="">
                </div>
                <div class="content-box">
                    <h2>@lang(session()->get('success-message') ?? 'Transfer Successful')</h2>
                    <p>{{ trans('Thanks a bunch for filling that out. It means a lot to us,
                        just like you do! We really appreciate you
                        giving us a moment of your time today. Thanks for being with us.') }}
                    </p>
                    <a href="{{ route('user.dashboard') }}" class="cmn-btn2 mt-20">{{ trans('Go to Dashboard') }}</a>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
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
@endpush
