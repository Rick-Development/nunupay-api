@extends($theme.'layouts.user')
@section('title', trans('OTP'))

@section('content')
    <div class="dashboard-wrapper">
        <a href="{{ route('user.transferList') }}" class="back-btn mb-10">
            <i class="fa-regular fa-angle-left"></i>@lang('Back to Transfer List')
        </a>

        <div class="row">
            <div class="col-xxl-5 col-xl-7 col-lg-8 col-md-9 mx-auto">

                <div class="container">
                    <div class="card-header">
                        <h4>@lang('Select Your verify option')</h4>
                    </div>
                    <div class="card-body mt-3">
                        <div class="row g-4">
                            <div class="col-12">
                                <div class="otp-container">
                                    <div class="item">
                                        <input class="form-check-input" type="radio" name="verification_option" id="sms_verify" value="sms" checked>
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
                                        <input class="form-check-input" type="radio" name="verification_option" id="email_verify" value="email">
                                        <label class="form-check-label" for="email_verify">
                                            <span class="icon-area">
                                                <i class="fa-regular fa-envelope"></i>
                                            </span>
                                            <span class="content-area">
                                                <h5>@lang('Email Verify')</h5>
                                                <span>@lang('we will send an otp in this email')</span>
                                                <span class="fw-semibold">{{ maskEmail(auth()->user()->email) }}</span>
                                            </span>
                                        </label>
                                    </div>
                                    <div class="item">
                                        <input class="form-check-input" type="radio" name="verification_option" id="both_verify" value="both">
                                        <label class="form-check-label" for="both_verify">
                                            <span class="icon-area">
                                                <i class="fa-regular fa-arrow-right-arrow-left"></i>
                                            </span>
                                            <span class="content-area">
                                                <h5>@lang('Both Email & SMS')</h5>
                                                <span>@lang('we will send an otp code to your email & sms both.')</span>
                                            </span>
                                        </label>
                                    </div>
                                    <div class="item">
                                        <input class="form-check-input" type="radio" name="verification_option" id="whatsapp_verify" value="whatsapp">
                                        <label class="form-check-label" for="whatsapp_verify">
                                            <span class="icon-area">
                                               <i class="fa-brands fa-whatsapp"></i>
                                            </span>
                                            <span class="content-area">
                                                <h5>@lang('Whats App')</h5>
                                                <span>@lang('we will send an otp code to your whats app number.')</span>
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

                            <div class="col-12 mb-2">
                                <input type="text" id="otp-input" class="form-control" placeholder="Enter OTP">
                            </div>

                            <div class="col-12 d-flex justify-content-between g-4 ">
                                <button type="button" id="goNext"   class="cmn-btn submitBtn">
                                    {{ trans('transfer confirm') }}
                                </button>

                                <form id="deleteForm" method="POST" action="{{ route('user.transferDestroy', $payDetails->uuid) }}" >
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="delete-btn" data-bs-toggle="modal" data-bs-target="#confirmationModal">
                                        {{ trans('Cancel this transfer') }}
                                    </button>
                                </form>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        $(document).ready(function () {
            const sendAmount = {{ $payDetails->send_amount }};
            const senderCurrency = "{{ $payDetails->sender_currency }}";
            const transferId = "{{ $payDetails->id }}";
            const walletId = {{ $payDetails->wallet_id }} ;
            const gateway = 0;
            const CC = null;

            const showErrorMessages = (messages) => {
                if (Array.isArray(messages)) {
                    messages.forEach(message => Notiflix.Notify.failure(message));
                } else {
                    Notiflix.Notify.failure(messages);
                }
            };

            const sendOtpRequest = (selectedOption) => {
                axios.get('{{ route('user.transferOtp') }}', {
                    params: { option: selectedOption, transferId }
                })
                    .then(response => {
                        const { status, message } = response.data;
                        if (status !== 'success') {
                            showErrorMessages(message);
                            return;
                        }
                        Notiflix.Notify.success(message);
                    })
                    .catch(() => Notiflix.Notify.failure('Something went wrong, please try again'));
            };

            const verifyOtp = (otpInput) => {
                return axios.post('{{ route('user.transferOtp') }}', { otp: otpInput, transferId })
                    .then(response => {
                        const { status, message } = response.data;
                        if (status !== 'success') {
                            showErrorMessages(message);
                            return false;
                        }
                        return true;
                    });
            };

            const getWalletBalance = () => {
                return axios.get('{{ route('user.walletBalance') }}', {
                    params: { walletId }
                })
                    .then(response => parseFloat(response.data.walletBalance));
            };

            const processPayment = (button) => {
                const $url = '{{ route("payment.request", ["transfer" => "transfer:id"]) }}'.replace('transfer:id', transferId);

                button.prop('disabled', true);
                Notiflix.Block.standard('#goNext', 'Processing...', { backgroundColor: '#fff' });

                axios.post($url, {
                    amount: sendAmount,
                    gateway_id: gateway,
                    supported_currency: senderCurrency,
                    supported_crypto_currency: CC,
                    wallet_id: walletId,
                })
                    .then(response => {
                        console.log(response)

                        const { status, message, url } = response.data;
                        if (status === "success") {
                            window.location.href = url;
                        } else {
                            Notiflix.Notify.failure(status === "error" ? message : 'Something went wrong with the transaction, please try again.');
                        }
                    })
                    .catch(() => Notiflix.Notify.failure('Error processing payment.'))
                    .finally(() => {
                        Notiflix.Block.remove('#goNext');
                        button.prop('disabled', false);
                    });
            };

            $(document).on('click', '#send_otp', function () {
                const selectedOption = document.querySelector('input[name="verification_option"]:checked').value;
                sendOtpRequest(selectedOption);
            });

            $(document).on('click', '#goNext', async function () {
                const button = $(this);
                const otpInput = $('#otp-input').val();
                if (!otpInput) {
                    Notiflix.Notify.failure('OTP is required');
                    return;
                }

                if (await verifyOtp(otpInput)) {
                    const walletBalance = await getWalletBalance().catch(() => {
                        Notiflix.Notify.failure('Error fetching wallet balance.');
                    });
                    if (isNaN(walletBalance) || walletBalance < sendAmount) {
                        Notiflix.Notify.failure('Insufficient wallet balance. Please deposit on your wallet');
                        return;
                    }
                    processPayment(button);
                }
            });
        });

        function confirmDelete() {
            document.getElementById('deleteForm').submit();
        }
    </script>
@endpush



@push('loadModal')
    <div class="modal fade" id="confirmationModal" tabindex="-1" role="dialog" aria-labelledby="confirmationModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmationModalLabel">@lang('Confirmation')</h5>
                    <button type="button" class="cmn-btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="fa-light fa-xmark"></i>
                    </button>
                </div>
                <div class="modal-body">
                    @lang('Are you sure you want to permanently remove this transfer?')
                </div>
                <div class="modal-footer">
                    <button type="button" class="cmn-btn3" data-bs-dismiss="modal">@lang('Close')</button>
                    <button type="button" class="delete-btn" onclick="confirmDelete()">@lang('Confirm')</button>
                </div>
            </div>
        </div>
    </div>
@endpush
