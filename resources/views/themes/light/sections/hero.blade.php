<style>
    [v-cloak] {
        display: none;
    }
</style>

<div id="MoneyTransfer" v-cloak>
    <div class="hero-section">
        <div class="container">
            <div class="row g-5 justify-content-between align-items-center">

                <div class="col-xl-6 col-lg-6">
                    <div class="hero-content">
                        <div class="section-subtitle"><i class="fa-solid fa-fire"></i>@lang(@$hero['single']['main_heading'])</div>
                        <h1 class="hero-title">@lang(@$hero['single']['heading'])</h1>

                        <p class="hero-description">@lang(@$hero['single']['sub_heading'])</p>
                        <div class="cmn-list mt-30 mb-30">
                            @forelse(collect($hero['multiple'])->toArray() as $item)
                                <div class="item"><img src="{{$themeTrue.'/img/icon/arrow-right.png'}}" alt="">
                                    {{ $item['feature'] }}
                                </div>
                            @empty
                            @endforelse
                        </div>
                        <div class="btn-area">
                            <a href="{{ @$hero['single']['media']->button_link_one }}" class="cmn-btn">
                                @lang(@$hero['single']['button_one'])
                            </a>
                            <a href="{{ @$hero['single']['media']->button_link_two }}" class="video-play-btn">
                                <i class="fa-regular fa-play"></i>
                                @lang(@$hero['single']['button_two'])
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-xl-5 col-lg-6">
                    <div class="calculator-section">
                        <img class="shape1" src="{{$themeTrue.'/img/arrow-right.png'}}" alt="">
                        <div class="calculator" id="MoneyTransfer2">
                            <div class="calculator-header">
                                <h4>@lang(@$hero['single']['title'])</h4>
                                <p>@lang(@$hero['single']['sub_title'])</p>
                            </div>
                            <form action="" method="post">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="input-amount-box" id="inputAmountBox">
                                            <span class="linear-gradient"></span>
                                            <div class="input-amount-wrapper">
                                                <label class="form-label mb-2">@lang('You send exactly')</label>
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

                                    <div class="form-description">
                                        <span class="single-description">
                                            <i class="fa-regular fa-arrow-right-arrow-left"></i>
                                            {{ trans('Transfer fee:') }} <strong v-cloak>@{{ transferFee.toFixed(2) }} USD</strong>,
                                            {{ trans('IN Local:') }} <strong v-cloak>@{{ transferLocalFee.toFixed(2) }} @{{ sendFrom.code }}</strong>
                                        </span>
                                    </div>

                                    <div class="col-12">
                                        <div class="input-amount-box" id="inputAmountBox2">
                                            <span class="linear-gradient"></span>
                                            <div class="input-amount-wrapper">
                                                <label class="form-label mb-2">@lang('Receiver Amount  (Without Fee)')</label>
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
                                                            <div id="receiverCurrName">@{{ receiveFrom.name }}</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="btn-area">
                                        <button type="button" @click="goNext" class="cmn-btn w-100" :disabled="send_amount / senderCurrencyRate < minAmount || send_amount / senderCurrencyRate > maxAmount">
                                            @lang('continue')
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <!-- End Hero -->


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














