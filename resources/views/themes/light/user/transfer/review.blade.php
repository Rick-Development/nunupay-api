@extends($theme.'layouts.user')
@section('title', trans('Payment Review'))

@section('content')

    <div class="dashboard-wrapper">
        <div class="row">
            <div class="col-xxl-9 col-lg-10 mx-auto">
                <div class="row g-4 g-sm-5">
                    @include($theme.'partials.payment_step')

                    <div class="col-xxl-6 col-xl-7 col-lg-8 col-md-9 order-1 order-md-2">
                        <div class="transfer-details-section" id="app">
                            <h4 class="mb-30">@lang('Review details of your transfer')</h4>
                            <div class="recipient-box2">
                                <div class="left-side">
                                    <div class="img-box">
                                        @if(!$recipient->r_user_id)
                                            <span class="recipient-avatar-name">{{ substr(ucfirst($recipient->name), 0, 1) }}</span>
                                            <img class="recipient-flag" src="{{ $recipient->currency?->country?->getImage() }}"
                                                 alt="">
                                        @else
                                            <img class="recipient-avatar" src="{{ $recipient->recipientUser?->getImage() }}" alt="">
                                            <img class="recipient-flag" src="{{ getFavicon() }}" alt="">

                                        @endif
                                    </div>
                                    <div class="text-box">
                                        <h6 class="fw-bold">{{ $recipient->name }}</h6>
                                        <small>{{ $recipient->email }}</small>
                                    </div>
                                </div>
                                <div class="right-side">
                                    <h6 class="fw-bold" v-cloak>@{{ get_amount }} @{{ receiverCurrency }}</h6>
                                    <small v-cloak>@{{ sendAmount }} @{{ senderCurrency }}</small>
                                </div>
                            </div>
                            <h5 class="mt-20">@lang('Transfer Details')</h5>
                            <hr class="cmn-hr2">
                            <div class="transfer-list pt-0 pb-0">
                                <div class="item">
                                    <span>@lang('You send exactly')</span>
                                    <h6 v-cloak>@{{ sendAmount }} @{{ senderCurrency }}</h6>
                                </div>
                                <div class="item">
                                    <span>@lang('Transfer Fee')</span>
                                    <h6 v-cloak>@{{ transferFee }} @{{ senderCurrency }}</h6>
                                </div>
                                <div class="item">
                                    <span>@lang('Send Total')</span>
                                    <h6 v-cloak>@{{ sendTotal }} @{{ senderCurrency }}</h6>
                                </div>
                                <div class="item">
                                    <span>@lang('Recipient will Get')</span>
                                    <h5 v-cloak>@{{ get_amount }} @{{ receiverCurrency }}</h5>
                                </div>
                            </div>

                            @if(!$recipient->r_user_id)
                                <h5 class="mt-20">@lang('Recipient Account details')</h5>
                                <hr class="cmn-hr2">
                                <div class="transfer-list pt-0 pb-0">
                                    <div class="item">
                                        <span>@lang('Service Name')</span>
                                        <h6>@lang($recipient->service?->name)</h6>
                                    </div>
                                    <div class="item">
                                        <span>@lang('Bank Name')</span>
                                        <h6>@lang($recipient->bank?->name)</h6>
                                    </div>
                                    @foreach($recipient->bank_info ?? [] as $key => $bank)
                                        <div class="item">
                                            <span>{{ snake2Title($key) }}</span>
                                            <span class="fw-semibold">{{ $bank }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                            <div class="d-flex align-items-sm-center justify-content-between gap-3 mt-40 flex-column flex-sm-row">
                                <div class="left-side order-2 order-sm-1">
                                    <a href="{{ route('user.dashboard') }}" class="cmn-btn4">@lang('Cancel')</a>
                                </div>
                                <div class="right-side d-flex align-items-center gap-3  order-1 order-sm-2">
                                    <a href="{{ route('user.transferRecipient',$recipient->currency?->country?->name) }}"
                                       class="cmn-btn3"><i class="fa-regular fa-angle-left"></i>@lang('Back')
                                    </a>
                                    <button type="button" @click="goNext" class="cmn-btn2">
                                        @lang('confirm & continue')<i class="fa-regular fa-arrow-right"></i>
                                    </button>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('script')
    @include('partials.calculationScript')
@endpush
