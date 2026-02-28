@extends($theme.'layouts.user')
@section('title', trans('Pay Amount'))

@section('content')
<div class="dashboard-wrapper">
    <a href="{{ route('user.transferList') }}" class="back-btn mb-50">
        <i class="fa-regular fa-angle-left"></i>@lang('Back To Transfer History')</a>

    <div class="col-xxl-9 col-lg-11 mx-auto">
        <div class="row g-2 g-sm-5">

            <div class="col-lg-7 col-md-6">
                <div class="card h-100">
                    <div class="card-body">
                        <h4 class="mb-15">{{ trans('Your preferred payment method?') }}</h4>
                        <div class="payment-section">
                            <ul class="payment-container-list">
                                <li class="item">
                                    <input class="form-check-input selectPayment" type="radio"
                                           name="gateway" id="wallet-payment" value="0">
                                    <label class="form-check-label" for="wallet-payment">
                                        <span class="image-area">
                                            <img src="{{ asset('assets/global/img/wallet.png') }}" alt="">
                                        </span>
                                        <span class="content-area">
                                            <h5>@lang('Wallet Payment')</h5>
                                            <span>{{ trans('Send money from your wallet') }}</span>
                                        </span>
                                    </label>
                                </li>
                                @foreach($gateways as $key => $gateway)
                                    <li class="item">
                                        <input class="form-check-input selectPayment" type="radio" name="gateway"
                                               id="gateway{{$key}}"
                                               data-gateway="{{$gateway->name}}"
                                               data-payment="{{ $gateway->id }}"
                                        >
                                        <label class="form-check-label" for="gateway{{$key}}">
                                            <span class="image-area">
                                                <img src="{{ getFile($gateway->driver, $gateway->image) }}" alt="">
                                            </span>
                                            <span class="content-area">
                                                <h5>{{ $gateway->name }}</h5>
                                                <span>{{ $gateway->description }}</span>
                                            </span>
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
                        <!-- Transfer details section start -->
                        <div class="transfer-details-section" >
                            <ul class="transfer-list" id="app">
                                <li class="item mb-3 title">
                                    <h4>{{ trans('Transfer Details') }}</h4>
                                </li>
                                <li class="item mb-3">
                                    <span>{{ trans('You send exactly') }}</span>
                                    <h6>  {{ currencyPositionCalc($payDetails->send_amount,$payDetails->senderCurrency) }} </h6>
                                </li>
                                <li class="item mb-3">
                                    <span>{{ trans('Transfer fees (included)') }}</span>
                                    <span class="text-danger">  {{ currencyPositionCalc($payDetails->fees,$payDetails->senderCurrency) }} </span>
                                </li>
                                <li class="item mb-3">
                                    <span>{{ trans('Send Total') }}</span>
                                    <h6>  {{ currencyPositionCalc($payDetails->payable_amount,$payDetails->senderCurrency) }} </h6>
                                </li>
                                <li class="item mb-3">
                                    <span id="receiverFirstName">{{ explode(' ', optional($recipient)->name)[0] }} {{ trans('will get') }}</span>
                                    <h6>  {{ currencyPositionCalc($payDetails->recipient_get_amount,$payDetails->receiverCurrency) }} </h6>
                                </li>
                                <li class="item border-bottom pb-3 mb-3">
                                    <span>{{ trans('Service') }}</span>
                                    <span class="fw-semibold">@lang($recipientBank?->service?->name)</span>
                                </li>
                            </ul>
                            <div class="currency">
                                <label class="form-label mt-0" for="supported_currency">
                                    {{ trans('Select Gateway Currency') }}
                                    <span data-bs-toggle="tooltip" data-bs-placement="top"
                                          data-bs-title="Kindly choose the currency through which you'd like to transfer money using the gateway.">
                                        <i class="fa-regular fa-circle-question"></i></span>
                                </label>

                                <select class="cmn-select2 mx-5 dd" name="supported_currency" id="supported_currency">
                                    <option value="" >{{ trans('Select a gateway first') }}</option>
                                </select>
                            </div>

                            <div class="selectWallet"></div>
                            <div class="mt-3 add-select-field mb-3"></div>
                            <div class="showCharge mb-3"></div>
                            <span class="feedback mb-3 text-danger"></span>

                        </div>

                        <div class="">
                            <button type="button" id="goNext"   class="mt-2 cmn-btn w-100 submitBtn disable_pay_now" disabled>
                                {{ trans('confirm and continue') }}
                            </button>

                            <form id="deleteForm" method="POST" action="{{ route('user.transferDestroy', $payDetails->uuid) }}" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="delete-btn mt-2 w-100"
                                        data-bs-toggle="modal"
                                        data-bs-target="#confirmationModal">
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
                    @lang('Are you sure you want to cancel this transfer?')
                </div>
                <div class="modal-footer">
                    <button type="button" class="cmn-btn3" data-bs-dismiss="modal">@lang('Close')</button>
                    <button type="button" class="delete-btn" onclick="confirmDelete()">@lang('Confirm')</button>
                </div>
            </div>
        </div>
    </div>
@endpush

@push('script')

    @include('partials.calculationScript')

    <script>
        function confirmDelete() {
            document.getElementById('deleteForm').submit();
        }
    </script>

@endpush

