@extends($theme.'layouts.user')
@section('title', trans('Exchange Money'))

@section('content')
    <div class="dashboard-wrapper">
        <a href="{{ route('user.dashboard') }}" class="back-btn mb-30">
            <i class="fa-regular fa-angle-left"></i>@lang('Back to Dashboard')
        </a>

        <div class="col-xxl-5 col-lg-10 mx-auto">
            <div class="breadcrumb-area mb-30">
                <h3 class="title">@yield('title')</h3>
            </div>
            <div class="card">
                <div class="card-header">
                    <h4>@lang('Exchange Money From Your :currency Wallet',['currency'=>$code])</h4>
                    <p>@lang('Fast and reliable international money transfer app.')</p>
                </div>
                <div class="card-body" id="Exchange">

                    <div class="row">
                        <div class="col-12">
                            <p class="title h6 mb-2">@lang('Your Account Balance is')
                                <strong>{{currencyPositionCalc($wallet->balance,$wallet->currency)}}</strong>
                            </p>
                            <label for="send" class="form-label">
                                @lang('Enter amount you want to exchange')</label>
                            <div class="input-box mb-2">
                                <input type="text" name="send_amount" class="form-control" id="send"
                                       placeholder="100.00" />
                                <div class="input-box-text">
                                    {{ $code }} - {{ $wallet->currency?->name }}</div>
                            </div>
                            <span id="balance_warning" class="text-danger" style="display: none;">
                                {{ trans('Insufficient Balance') }}
                            </span>
                        </div>

                        <div class="col-12">
                            <label for="receive" class="form-label">@lang('Receiving wallet amount')</label>
                            <div class="input-box">
                                <input type="text" name="receive_amount" class="form-control" id="receive" value="0.00"/>
                                <select class="cmn-select2" name="receiver_currency" id="receiverWallet">
                                    @foreach($wallets as $item)
                                        <option value="{{ $item->id }}" data-rate="{{ $item->currency->rate }}">
                                            {{ $item->currency?->code }} - {{ $item->currency?->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="btn-area">
                        <button type="button" id="goNext" class="cmn-btn mt-4 w-100">@lang('continue')</button>
                    </div>

                </div>
            </div>
        </div>

    </div>
@endsection

@push('script')
    <script>
        $(document).ready(function() {
            let sendRate = {{ $wallet->currency->rate }};
            let receiveRate = 0;
            let sendInput = $('#send');
            let receiveInput = $('#receive');

            function updateRecipientAmount() {
                const senderAmount = parseFloat($("#send").val());
                const receiveRate = parseFloat($("#receiverWallet option:selected").data('rate'));
                const rate = receiveRate / sendRate;
                const recipientAmount = senderAmount * rate;
                receiveInput.val(recipientAmount ? recipientAmount.toFixed(2) : '0');
            }
            function updateSenderAmount() {
                const recipientAmount = parseFloat($("#receive").val());
                const rate = sendRate / receiveRate;
                const senderAmount = recipientAmount * rate;
                sendInput.val(senderAmount.toFixed(2));
            }

            $("#receiverWallet").on("change", function() {
                receiveRate = parseFloat($(this).find(':selected').data('rate'));
                updateRecipientAmount();
            }).trigger('change');

            $('#send, #receive').on('input', function() {
                $(this).val(function(index, value) {
                    return value.replace(/[^\d.]/g, '').replace(/(\..*)\./g, '$1');
                });
            });

            sendInput.on("input", updateRecipientAmount);
            receiveInput.on("input", updateSenderAmount);
            sendInput.on("input", function() {
                let sendAmount = parseFloat($(this).val());
                let balance = parseFloat({{ $wallet->balance }});
                $("#balance_warning").toggle(sendAmount > balance);
            });
        });

        $(document).on('click', '#goNext', function () {
            const sendAmount = parseFloat($("#send").val());
            const receiveAmount = parseFloat($("#receive").val());
            const senderWalletId = {{ $wallet->id }};
            const receiverWalletId = $('#receiverWallet').val();

            let $url = '{{ route("user.moneyExchange") }}';

            axios.post($url, {
                sendAmount,
                receiveAmount,
                senderWalletId,
                receiverWalletId
            })
                .then(response => {
                    if (response.data.status == "success"){
                        window.location.href = response.data.url;
                    } else{
                        Notiflix.Notify.failure(response.data.msg);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        })
    </script>


@endpush


