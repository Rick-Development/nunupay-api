@extends($theme.'layouts.app')
@section('title',trans($page_title ?? 'Get OTP'))

@section('content')
    @include($theme.'partials.banner')

    <div class="otp-wrapper">
        <div class="container">
            <div class="d-flex justify-content-center">
                <div class="col-xl-5 col-md-6 order-2 order-md-1">
                    <div class="row g-4 mt-10 ">
                        <div class="col-12">
                            <div class="otp-container">
                                <div class="item">
                                    <input class="form-check-input" type="radio" name="verification_option"
                                           id="sms_verify" value="sms" checked>
                                    <label class="form-check-label" for="sms_verify">
                                        <span class="icon-area">
                                            <i class="fa-regular fa-message-sms"></i>
                                        </span>
                                        <span class="content-area">
                                            <h5>@lang('SMS Verify')</h5>
                                            <span>@lang('we will send an otp in this number')</span>
                                            <span class="fw-semibold">{!! maskString(auth()->user()->phone) !!} </span>
                                        </span>
                                    </label>
                                </div>
                                <div class="item">
                                    <input class="form-check-input" type="radio" name="verification_option"
                                           id="email_verify" value="email">
                                    <label class="form-check-label" for="email_verify">
                                                        <span class="icon-area">
                                                            <i class="fa-regular fa-envelope"></i>
                                                        </span>
                                        <span class="content-area">
                                                            <h5>@lang('Email Verify')</h5>
                                                            <span>@lang('we will send an otp in this email')</span>
                                                            <span
                                                                class="fw-semibold">{{ maskEmail(auth()->user()->email) }}</span>
                                                        </span>
                                    </label>
                                </div>
                                <div class="item">
                                    <input class="form-check-input" type="radio" name="verification_option"
                                           id="both_verify" value="both">
                                    <label class="form-check-label" for="both_verify">
                                                        <span class="icon-area">
                                                            <i class="fa-regular fa-arrow-right-arrow-left"></i>
                                                        </span>
                                        <span class="content-area">
                                                            <h5>@lang('Both Email & SMS')</h5>
                                                            <span>@lang('we sent an otp code to your email & sms both.')</span>
                                                        </span>
                                    </label>
                                </div>
                                <div class="item">
                                    <input class="form-check-input" type="radio" name="verification_option"
                                           id="whatsapp_verify" value="whatsapp">
                                    <label class="form-check-label" for="whatsapp_verify">
                                                        <span class="icon-area">
                                                           <i class="fa-brands fa-whatsapp"></i>
                                                        </span>
                                        <span class="content-area">
                                                            <h5>@lang('Whats App')</h5>
                                                            <span>@lang('we sent an otp code to your whats app number.')</span>
                                                        </span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 d-flex justify-content-end">
                            <a href="javascript:void(0);" class="cmn-btn4" id="send_otp">
                                {{ (auth()->user()->verify_code == null) ? trans('Send OTP') : trans('Resend OTP') }}
                            </a>
                        </div>
                        <form action="{{route('user.smsVerify')}}" method="post">
                            @csrf
                            <div class="col-12 mb-2">
                                <input type="text" id="otp-input" name="code"
                                       class="form-control @error('code') is-invalid @enderror"
                                       placeholder="Enter OTP">
                                @error('code')<span class="text-danger  mt-1">{{ $message }}</span>@enderror
                            </div>

                            <div class="col-12 d-flex justify-content-between g-4 ">
                                <button type="submit" id="goNext" class="cmn-btn submitBtn">
                                    {{ trans('submit') }}
                                </button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>

    @include(template().'sections.footer')
@endsection

@push('script')
    <script>
        $(document).ready(function () {

            const showErrorMessages = (messages) => {
                if (Array.isArray(messages)) {
                    messages.forEach(message => Notiflix.Notify.failure(message));
                } else {
                    Notiflix.Notify.failure(messages);
                }
            };

            const sendOtpRequest = (selectedOption) => {
                axios.get('{{ route('userOtp') }}', {
                    params: {option: selectedOption}
                })
                    .then(response => {

                        console.log(response.data)
                        const {status, message} = response.data;
                        if (status !== 'success') {
                            showErrorMessages(message);
                            return;
                        }
                        Notiflix.Notify.success(message);
                    })
                    .catch(() => Notiflix.Notify.failure('Something went wrong, please try again'));
            };

            $(document).on('click', '#send_otp', function () {
                const selectedOption = document.querySelector('input[name="verification_option"]:checked').value;
                sendOtpRequest(selectedOption);
            });

        });
    </script>
@endpush
