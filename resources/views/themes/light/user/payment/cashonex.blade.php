@extends($theme.'layouts.user')
@section('title')
    {{ 'Pay with '.optional($deposit->gateway)->name ?? '' }}
@endsection

@section('content')
    <div class="dashboard-wrapper">
        <div class="col-xxl-8 col-lg-10 mx-auto">
        <div class="breadcrumb-area"><h3 class="title">@yield('title')</h3></div>
            <div class="card secbg">
                <div class="card-body">
                    <div class="row justify-content-center">
                        <div class="col-md-12 p-4">
                            <div class="card-wrapper"></div>
                            <br><br>
                            <form role="form" id="payment-form" method="{{$data->method}}"
                                  action="{{$data->url}}">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="form-label"><strong>@lang("CARD NAME")</strong></label>
                                        <div class="search-box flex-grow-1">
                                            <input type="text" class="form-control white" name="name"
                                                   placeholder="Card Name" autocomplete="off" required>
                                            <button type="button" class="search-btn"><i class="fa fa-font"></i></button>
                                        </div>
                                        @error('name')<span
                                            class="text-danger  mt-1">{{ $message }}</span>@enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label"><strong>@lang("CARD NUMBER")</strong></label>
                                        <div class="search-box flex-grow-1">
                                            <input type="tel" class="form-control white" name="cardNumber"
                                                   placeholder="Valid Card Number" autocomplete="off" autofocus
                                                   required>
                                            <button type="button" class="search-btn"><i class="fa fa-credit-card"></i></button>
                                        </div>
                                    </div>
                                    @error('cardNumber')<span class="text-danger  mt-1">{{ $message }}</span>@enderror
                                </div>
                                <br>
                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="form-label"><strong>@lang("EXPIRATION DATE")</strong></label>
                                        <div class="input-group">
                                            <input
                                                type="tel"
                                                class="form-control"
                                                name="cardExpiry"
                                                placeholder="MM / YYYY"
                                                autocomplete="off"
                                                required/>
                                        </div>
                                        @error('cardExpiry')<span
                                            class="text-danger  mt-1">{{ $message }}</span>@enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label"><strong>@lang("CVC CODE")</strong></label>
                                        <div class="input-group">
                                            <input
                                                type="tel"
                                                class="form-control"
                                                name="cardCVC"
                                                placeholder="CVC"
                                                autocomplete="off"
                                                required/>
                                        </div>
                                        @error('cardCVC')<span
                                            class="text-danger  mt-1">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <br>
                                <div class="btn-wrapper">
                                    <input class="cmn-btn w-100 " type="submit" value="PAY NOW">
                                </div>
                            </form>
                        </div>

                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection

@push('js-lib')
    <script type="text/javascript" src="https://rawgit.com/jessepollak/card/master/dist/card.js"></script>
@endpush

@push('script')

    <script>
        (function ($) {
            $(document).ready(function () {
                var card = new Card({
                    form: '#payment-form',
                    container: '.card-wrapper',
                    formSelectors: {
                        numberInput: 'input[name="cardNumber"]',
                        expiryInput: 'input[name="cardExpiry"]',
                        cvcInput: 'input[name="cardCVC"]',
                        nameInput: 'input[name="name"]'
                    }
                });
            });
        })(jQuery);
    </script>
@endpush
