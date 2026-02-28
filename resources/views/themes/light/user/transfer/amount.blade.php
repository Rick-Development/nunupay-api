@extends($theme.'layouts.user')
@section('title', trans('Send Money'))

@section('content')
<div id="MoneyTransfer" v-cloak>
    <div class="dashboard-wrapper">
        <div class="row">
            <div class="col-xxl-9 col-lg-10 mx-auto">
                <div class="row g-4 g-sm-5">
                    @include($theme.'partials.payment_step')

                    <div class="col-xxl-6 col-xl-7 col-lg-8 col-md-9 order-1 order-md-2">
                        <div class="calculator-section">
                            <form class="calculator">
                                <div class="calculator-header">
                                    <h4>@lang('How much do you want to send?')</h4>
                                    <p class="mb-0">@lang('Fast and reliable international money transfer app.')</p>
                                </div>
                                <div class="calculator-body">
                                    <div class="row g-4">
                                        <div class="col-12">
                                            <div class="input-amount-box" id="inputAmountBox">
                                                <span class="linear-gradient"></span>
                                                <div class="input-amount-wrapper">
                                                    <label class="form-label mb-1">@lang('You send exactly')</label>
                                                    <div class="input-amount-box-inner" id="inputAmountBoxInner">
                                                        <a href="#" class="icon-area" data-bs-toggle="modal"
                                                           data-bs-target="#senderModal">
                                                            <img class="img-flag" id="senderImage" :src="sendFrom.image" alt="">
                                                        </a>
                                                        <div class="text-area w-100">
                                                            <div class="d-flex gap-3 justify-content-between">
                                                                <a href="#" class="currency-name d-flex align-items-center gap-1"
                                                                   data-bs-toggle="modal" data-bs-target="#senderModal">
                                                                    <div class="title" id="senderCode">@{{ sendFrom.code }}</div>
                                                                    <i class="fa-regular fa-angle-down"></i>
                                                                </a>
                                                                <input type="text" name="send_amount" id="send"
                                                                       placeholder="0.00"
                                                                       autocomplete="off"
                                                                       v-model="send_amount"
                                                                       @change="getValue" @keypress="onlyNumber"
                                                                       @input="updateSenderAmount"
                                                                >
                                                            </div>
                                                            <div class="d-flex justify-content-between">
                                                                <div class="sub-title" id="senderCurrName">@{{ sendFrom.name }}</div>
                                                                <div class="fw-500 text-danger" v-cloak
                                                                     v-if="send_amount / senderCurrencyRate < minAmount">
                                                                    {{ trans('Amount must be at least') }} @{{ (minAmount * senderCurrencyRate).toFixed(2) }} @{{ sendFrom.code }}
                                                                </div>
                                                                <div class="fw-500 text-danger" v-cloak
                                                                     v-else-if="send_amount / senderCurrencyRate > maxAmount">
                                                                    {{ trans('Amount must not exceed') }} @{{ maxAmount*senderCurrencyRate }} @{{ sendFrom.code }}
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="input-amount-box" id="inputAmountBox2">
                                                <span class="linear-gradient"></span>
                                                <div class="input-amount-wrapper">
                                                    <label class="form-label mb-1">@lang('Receiver Amount  (Without Fee)')</label>
                                                    <div class="input-amount-box-inner" id="inputAmountBoxInner2">
                                                        <a href="#" class="icon-area" data-bs-toggle="modal"
                                                           data-bs-target="#receiverModal">
                                                            <img class="img-flag" id="receiverImage" :src="receiveFrom.image" alt="">
                                                        </a>
                                                        <div class="text-area w-100">
                                                            <div class="d-flex gap-3 justify-content-between">
                                                                <a href="#" class="currency-name d-flex align-items-center gap-1"
                                                                   data-bs-toggle="modal" data-bs-target="#receiverModal">
                                                                    <div class="title" id="receiverCode">@{{ receiveFrom.code }}</div>
                                                                    <i class="fa-regular fa-angle-down"></i>
                                                                </a>
                                                                <input type="text"
                                                                       name="" id="" placeholder="0.00"
                                                                       value="0.00"
                                                                       v-model="get_amount" @change="sendValue"
                                                                       @keypress="onlyNumber"
                                                                       onkeyup="this.value = this.value.replace (/^\.|[^\d\.]/g, '')"
                                                                       @input="updateRecipientAmount">
                                                            </div>
                                                            <div class="d-flex justify-content-between">
                                                                <div id="receiverCurrName" v-cloak>@{{ receiveFrom.name }}</div>

                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="info-list">
                                                <div class="item">
                                                    <div class="left-side">
                                                        <div class="icon-box">
                                                            <i class="fa-regular fa-arrows-repeat"></i>
                                                        </div>
                                                        @lang('Transfer fee:')
                                                    </div>
                                                    <h6 v-cloak>@{{ transferFee.toFixed(2) }} USD</h6>
                                                </div>
                                                <div class="item">
                                                    <div class="left-side">
                                                        <div class="icon-box">
                                                            <i class="fa-regular fa-arrows-repeat"></i>
                                                        </div>
                                                        @lang('Transfer fee IN Local:')
                                                    </div>
                                                    <h6 v-cloak>@{{ transferLocalFee.toFixed(2) }} @{{ sendFrom.code }}</h6>
                                                </div>
                                                <hr class="cmn-hr2">
                                                <div class="item">
                                                    <div class="left-side">
                                                        <div class="icon-box">
                                                            <i class="fa-regular fa-wallet"></i>
                                                        </div>
                                                        @lang('Recipient will get:')
                                                    </div>
                                                    <h6 v-cloak>@{{ totalAmount.toFixed(2) }} @{{ receiveFrom.code }}</h6>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div
                                                class="d-flex align-items-sm-center justify-content-between gap-3 mt-20 flex-column flex-sm-row">
                                                <div class="left-side order-2 order-sm-1">
                                                    <a href="{{ route('user.dashboard') }}" class="cmn-btn4">@lang('Cancel')</a>
                                                </div>
                                                <div class="right-side d-flex align-items-center gap-3  order-1 order-sm-2">
                                                    <a href="{{ url()->previous() }}" class="cmn-btn3"><i class="fa-regular fa-angle-left"></i>@lang('Back')</a>
                                                    <button type="button" @click="goNext" class="cmn-btn2"
                                                            :disabled="send_amount / senderCurrencyRate < minAmount || send_amount / senderCurrencyRate > maxAmount">
                                                        @lang('continue')<i class="fa-regular fa-arrow-right"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>


    <!-- Sender Modal -->
    <div class="modal fade calculator-modal" id="senderModal"  data-bs-keyboard="false"
         tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header flex-column">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="modal-title" id="staticBackdropLabel">Select a currency</h3>
                        <button type="button" class="cmn-btn-close" data-bs-dismiss="modal" aria-label="Close">
                            <i class="fa-light fa-xmark"></i>
                        </button>
                    </div>
                    <div class="search-box mt-10">
                        <input type="text" id="search-input" onkeyup="filterItems('search-input')" class="form-control"
                               placeholder="Search here...">
                        <button type="submit" class="search-btn"><i class="far fa-search"></i></button>
                    </div>
                </div>
                <div class="modal-body">
                    <div id="currency-list" class="currency-list">

                        <div class="item" id="sendItem" v-for="item in senderCurrencies"  @click="changeSender(item.id)">
                            <div class="left-side">
                                <div class="img-area">
                                    <img class="img-flag" :src="item.image" alt="" id="senderImgSrc">
                                </div>
                                <div class="text-area">
                                    <div class="title" id="senderCode">@{{ item.code }}</div>
                                    <div class="sub-title">@{{ item.name }}</div>
                                </div>
                            </div>

                            <div class="right-side" id="checkCurrency">
                                <i v-if="sendFrom.id === item.id" class="fa-sharp fas fa-circle-check"></i>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Receiver Modal -->
    <div class="modal fade calculator-modal" id="receiverModal" data-bs-keyboard="false"
         tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header flex-column">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="modal-title" id="staticBackdropLabel">Select a currency</h3>
                        <button type="button" class="cmn-btn-close" data-bs-dismiss="modal" aria-label="Close">
                            <i class="fa-light fa-xmark"></i>
                        </button>
                    </div>
                    <div class="search-box mt-10">
                        <input type="text" id="search-input2" onkeyup="filterItems('search-input2')" class="form-control"
                               placeholder="Search here...">
                        <button type="submit" class="search-btn"><i class="far fa-search"></i></button>
                    </div>
                </div>
                <div class="modal-body">
                    <div id="currency-list" class="currency-list">
                        <div class="item" v-for="item in receiverCurrencies" @click="changeReceive(item.id)">
                            <div class="left-side">
                                <div class="img-area">
                                    <img class="img-flag" :src="item.image" alt="">
                                </div>
                                <div class="text-area">
                                    <div class="title">@{{ item.code }}</div>
                                    <div class="sub-title">@{{ item.name }}</div>
                                </div>
                            </div>
                            <div class="right-side" id="checkCurrency">
                                <i v-if="receiveFrom.id === item.id" class="fa-sharp fas fa-circle-check"></i>
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
