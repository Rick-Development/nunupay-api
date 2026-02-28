@extends($theme.'layouts.user')
@section('title',trans('Deposit'))
@section('content')

    <div class="dashboard-wrapper">

        <div class="col-xxl-8 col-lg-10 mx-auto">
        <div class="breadcrumb-area"><h3 class="title">@lang('Deposit')</h3></div>

            @php
                $cardError = \Cache::get('v_card_currency_error');
            @endphp
            @if($cardError && $card != null)
                <div class="alert alert-danger alert-dismissible" role="alert">
                    <div class="icon-area"><i class="fa-light fa-triangle-exclamation"></i></div>
                    <div class="text-area">
                        <div class="description">
                            {{ $cardError }}
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"
                        onclick="{{  \Cache::forget('v_card_currency_error') }}">
                        <i class="fa-regular fa-xmark"></i>
                    </button>
                </div>
            @endif

        <form action="{{ route('payment.request') }}" method="post" enctype="multipart/form-data" id="form">
            @csrf
            <div class="row g-4">
                <div class="col-lg-7 col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="mb-15">{{ trans('Your preferred payment method?') }}</h5>
                            <div class="payment-section">
                                <ul class="payment-container-list">
                                    @foreach($gateways as $key => $method)
                                        <li class="item">
                                            <input type="radio" class="form-check-input selectPayment"
                                                   name="gateway_id"
                                                   id="{{ $method->name }}"
                                                   value="{{ $method->id }}"
                                                   autocomplete="off" />
                                            <label class="form-check-label" for="{{ $method->name }}">
                                                <div class="image-area">
                                                    <img src="{{ getFile($method->driver, $method->image) }}" alt="">
                                                </div>
                                                <div class="content-area">
                                                    <h5>{{ $method->name }}</h5>
                                                    <span>{{ $method->description }}</span>
                                                </div>
                                            </label>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-5 col-md-6">
                    <div class="card">
                        <div class="card-body">

                            @if($card == null)
                                <label class="form-label mt-3" for="supported_wallet">{{ trans('Select Your Wallet') }}
                                    <span data-bs-toggle="tooltip" data-bs-placement="top"
                                          data-bs-title="Please designate the wallet to which you wish to allocate funds.">
                                        <i class="fa-regular fa-circle-question"></i></span>
                                </label>
                                <select class="cmn-select2 " name="wallet_id" id="supported_wallet">
                                    @foreach($wallets as $item)
                                    <option value="{{ $item->id }}" >{{ $item->currency->code }} - {{ $item->currency->name }}</option>
                                    @endforeach
                                </select>
                            @else
                                <input type="hidden" name="card_id" value="{{ $card->id }}">
                            @endif

                            <label class="form-label mt-3" for="supported_currency">{{ trans('Select Currency') }}
                                <span data-bs-toggle="tooltip" data-bs-placement="top"
                                      data-bs-title="Kindly choose the currency through which you'd like to deposit using the gateway.">
                                <i class="fa-regular fa-circle-question"></i></span>
                            </label>
                            <select class="cmn-select2" name="supported_currency" id="supported_currency">
                                <option value="" selected disabled>{{ trans('Select a gateway first') }}</option>
                            </select>

                            <div class="mt-3 add-select-field mb-2"></div>

                            <label class="form-label mt-0 " for="">{{ trans('Enter Amount') }}</label>
                            <input class=" mb-2 form-control @error('amount') is-invalid @enderror"
                                   name="amount" type="text" id="amount"
                                   placeholder="{{ trans('Enter Amount') }}" autocomplete="off"
                                   onkeyup="this.value = this.value.replace (/^\.|[^\d\.]/g, '')"
                            />
                            <div class="invalid-feedback">@error('amount') @lang($message) @enderror</div>
                            <div class="valid-feedback"></div>

                            <div class="side-box mt-3 mb-3">
                                <div class="showCharge">

                                </div>
                            </div>

                            <div class="input-box mb-3">
                                <div class="form-check">
                                    <input checked class="form-check-input" type="checkbox" value="" id="tandc"/>
                                    <label class="form-check-label" for="tandc">
                                        {{ trans('I agree to Terms & Conditions.') }}
                                    </label>
                                </div>
                            </div>

                            <button type="submit" class="cmn-btn w-100 check submitBtn" disabled>{{ trans('Make Payment') }}</button>

                            <a href="{{ route('user.dashboard') }}" class="delete-btn mt-20 w-100">{{ trans('cancel this transfer') }}</a>

                        </div>
                    </div>
                </div>
            </div>
        </form>

        </div>
    </div>

@endsection

@push('script')
    <script>
        'use strict';

        $(document).ready(function () {

            $('.submitBtn').on('click', function() {
                $(this).prop('disabled', true);
                $('#form').submit();
            });


            let amountField = $('#amount');
            let amountStatus = false;
            let selectedGateway = "";

            function clearMessage(fieldId) {
                $(fieldId).removeClass('is-valid')
                $(fieldId).removeClass('is-invalid')
                $(fieldId).closest('div').find(".invalid-feedback").html('');
                $(fieldId).closest('div').find(".is-valid").html('');
            }

            $(document).on('click', '.selectPayment', function () {
                let id = this.id;
                selectedGateway = $(this).val();
                supportCurrency(selectedGateway);
                $('.add-select-field').empty();
            });

            function supportCurrency(selectedGateway) {

                if (!selectedGateway) {
                    console.error('Selected Gateway is undefined or null.');
                    return;
                }

                $('#supported_currency').empty();

                axios.get("{{ route('supported.currency') }}", {
                    params: {
                        gateway: selectedGateway
                    }
                })
                    .then(function (response) {

                        if (response.data === "") {
                            let markup = `<option value="USD">USD</option>`;
                            $('#supported_currency').append(markup);
                        }

                        let markup = '<option value="" selected disabled>{{ trans('Choose Currency') }}</option>';
                        $('#supported_currency').append(markup);

                        if (response.data.currencyType == 1) {
                            response.data.data.forEach(function (value) {
                                let markup = `<option value="${value}">${value}</option>`;
                                $('#supported_currency').append(markup);
                            });
                        } else {
                            let markup = `<option value="USD">USD</option>`;
                            $('#supported_currency').append(markup);
                        }

                        let markup2 = '<option value="">{{ trans('Select a Crypto Currency') }}</option>';
                        $('#supported_crypto_currency').append(markup2);

                        if (response.data.currencyType == 0) {

                            let markup2 = `<label class="form-label" for="supported_crypto_currency">{{ trans('Pay To Crypto Currency') }}</label>
                            <select class="cmn-select2 form-control"
                                    name="supported_crypto_currency"
                                    id="supported_crypto_currency">
                                <option value="">Select a Crypto Currency</option>
                            </select>`;
                            $('.add-select-field').append(markup2);
                            $('#supported_crypto_currency').select2();

                            response.data.data.forEach(function (value) {
                                let markupOption = `<option value="${value}">${value}</option>`;
                                $('#supported_crypto_currency').append(markupOption);
                            });
                        }
                    })
                    .catch(function (error) {
                        console.error('Axios Error:', error);
                    });
            }


            $(document).on('change, input', "#amount, #supported_currency, .selectPayment, #supported_crypto_currency", function (e) {

                let amount = amountField.val();
                let selectedCurrency = $('#supported_currency').val();
                let selectedCryptoCurrency = $('#supported_crypto_currency').val();
                let currency_type = 1;

                if (!isNaN(amount) && amount > 0) {

                    let fraction = amount.split('.')[1];
                    let limit = currency_type == 0 ? 8 : 2;

                    if (fraction && fraction.length > limit) {
                        amount = (Math.floor(amount * Math.pow(10, limit)) / Math.pow(10, limit)).toFixed(limit);
                        amountField.val(amount);
                    }

                    checkAmount(amount, selectedCurrency, selectedGateway, selectedCryptoCurrency)

                    if (selectedCurrency != null) {

                    }
                } else {
                    clearMessage(amountField)
                    $('.showCharge').html('')
                }
            });

            function checkAmount(amount, selectedCurrency, selectGateway, selectedCryptoCurrency = null) {
                $.ajax({
                    method: "GET",
                    url: "{{ route('deposit.checkAmount') }}",
                    dataType: "json",
                    data: {
                        'amount': amount,
                        'selected_currency': selectedCurrency,
                        'select_gateway': selectGateway,
                        'selectedCryptoCurrency': selectedCryptoCurrency,
                    }
                }).done(function (response) {
                    let amountField = $('#amount');
                    if (response.status) {
                        clearMessage(amountField);
                        $(amountField).addClass('is-valid');
                        $(amountField).closest('div').find(".valid-feedback").html(response.message);
                        amountStatus = true;
                        let base_currency = "{{basicControl()->base_currency}}"
                        showCharge(response, base_currency);
                        $('.submitBtn').prop('disabled', false);
                    } else {
                        $('.submitBtn').prop('disabled', true);
                        amountStatus = false;
                        $('.showCharge').html('');
                        clearMessage(amountField);
                        $(amountField).addClass('is-invalid');
                        $(amountField).closest('div').find(".invalid-feedback").html(response.message);
                    }
                });
            }

            function showCharge(response, currency) {
                let txnDetails = `<ul class="list-group">
						<li class="list-group-item d-flex justify-content-between">
							<span>{{ __('Amount In') }} ${response.currency} </span>
							<span class="text-success"> ${response.amount} ${response.currency}</span>
						</li>

						<li class="list-group-item d-flex justify-content-between">
							<span>{{ __('Charge') }}</span>
							<span class="text-danger">  ${response.charge} ${response.currency}</span>
						</li>

						<li class="list-group-item d-flex justify-content-between">
							<span>{{ __('Payable Amount') }}</span>
							<span class="text-info"> ${response.payable_amount} ${response.currency}</span>
						</li>

					</ul>`;
                $('.showCharge').html(txnDetails)
            }
        });

    </script>
@endpush

@push('notify')
    @if ($errors->any())
        <script>
            "use strict";
            @foreach ($errors->unique() as $error)
            Notiflix.Notify.failure("{{ trans($error) }}");
            @endforeach
        </script>
    @endif
@endpush






