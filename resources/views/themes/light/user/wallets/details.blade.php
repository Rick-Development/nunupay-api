@extends($theme.'layouts.user')
@section('title',trans('Wallet Details'))
@section('content')

    <div class="dashboard-wrapper">
        <a href="{{ route('user.dashboard') }}" class="back-btn">
            <i class="fa-regular fa-angle-left"></i>@lang('Back to Dashboard')
        </a>

        <div class="row">
            <div class="col-xxl-8 col-xl-10 mx-auto">
                <div class="balance-card mb-30">
                    <div class="balance-card-left">
                        <div class="img-area">
                            <img src="{{ $wallet->currency?->country?->getImage() }}" alt="">
                        </div>
                        <div class="text-box">
                            <p class="mb-0">@lang('Total balance')</p>
                            <h3 class="mb-2">{{ currencyPositionCalc($wallet->balance,$wallet->currency) }}</h3>
                            <div><span class="fw-semibold">
                                1 USD = {{ getAmount($wallet->currency->rate) }} {{ $wallet->currency_code }}
                            </span>
                            </div>
                        </div>
                    </div>
                    <div class="balance-card-right">
                        <div class="btn-area d-flex flex-wrap align-items-center gap-2">
                            <a href="{{ route('user.transferAmount').'?wallet='.$wallet->uuid }}" class="cmn-btn2"
                               id="sendCurrency" data-currency-code="{{ $wallet->currency_code }}"
                               data-wallet-id="{{ $wallet->id }}">
                                <i class="fa-regular fa-arrow-up"></i> @lang('send')
                            </a>
                            <a href="{{ route('user.wallet.exchange',$wallet->uuid) }}" class="cmn-btn2"><i
                                    class="fa-regular fa-arrows-repeat"></i>@lang('convert')</a>


                            @if($wallet->default == 0)
                                <div class="dropdown dropdown-menu-end">
                                    <button class="dropdown-btn" type="button" data-bs-toggle="dropdown"
                                            aria-expanded="false">
                                        <i class="fa-regular fa-ellipsis"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <form action="{{ route('user.defaultWallet', $wallet->id) }}" method="post">
                                            @csrf
                                            <button type="submit" class="dropdown-item">
                                                <i class="fa-regular fa-badge-check"></i>@lang('Make Default')</button>
                                        </form>
                                    </ul>
                                </div>
                            @endif
                        </div>

                        @if($wallet->status == 0)
                            <div class="text-danger fw-semibold mt-20">@lang('This card has been blocked')</div>
                        @endif

                        @if($wallet->default == 1)
                            <div class="progress-area mt-20">
                                <p class="mb-1"><small class="fw-semibold">@lang('This is your default wallet')</small></p>
                                <div class="progress" role="progressbar" aria-label="Basic example" aria-valuenow="25"
                                     aria-valuemin="0" aria-valuemax="100">
                                    <div class="progress-bar" style="width: 100%"></div>
                                </div>
                            </div>
                        @else

                        @endif
                    </div>
                </div>
                <hr class="cmn-hr2">
                <div class="transaction-list-section mt-30">
                    <h4 class="mb-15">@lang('Transactions')</h4>
                    <div class="d-flex flex-column flex-sm-row gap-3">
                        <div class="search-bar">
                            <form class="search-form d-flex align-items-center" method="get" action="">
                                <input type="text" class="form-control" name="transaction" placeholder="@lang('Search by transaction id') or remarks"
                                       value="{{ old('transaction', request()->transaction) }}">
                                <button type="submit" class="search-icon" title="Search">
                                    <i class="fa-regular fa-magnifying-glass"></i></button>
                            </form>
                        </div>
                    </div>
                    <div class="transaction-list">
                        @forelse ($groupedTransactions as $date => $dailyTransactions)
                            <p class="mb-3">{{ dateTime($date) }}</p>
                            @foreach ($dailyTransactions as $transaction)
                                @php
                                    $amount = currencyPositionCalc($transaction->amount,$transaction->curr);
                                    $baseAmount = currencyPosition($transaction->base_amount);
                                    $charge = currencyPosition($transaction->charge);
                                @endphp

                                <a class="item showInfo" href="#"
                                   data-trx_type="{{ $transaction->trx_type }}"
                                   data-remarks="{{ $transaction->remarks }}"
                                   data-amount="{{ $amount }}"
                                   data-baseamount="{{ $baseAmount }}"
                                   data-charge="{{ $charge }}"
                                   data-trxid="{{ $transaction->trx_id }}"
                                   data-trx_date="{{ dateTime($transaction->created_at) }}"
                                   data-bs-toggle="modal"
                                   data-bs-target="#infoViewModal"
                                >
                                    <div class="left-side">
                                        <div
                                            class="icon {{ $transaction->trx_type == '-' ? 'icon-sent' : 'icon-received' }}">
                                            <i class="fa-regular {{ $transaction->trx_type == '-' ? 'fa-arrow-up' : 'fa-arrow-down' }}"></i>
                                        </div>
                                        <div class="d-flex gap-2">
                                            @lang($transaction->remarks)
                                        </div>
                                    </div>
                                    <div class="right-side text-end">
                                        <h5 class="mb-0">
                                            {{ $transaction->trx_type}}{{ currencyPositionCalc($transaction->amount, $transaction->curr) }}
                                        </h5>
                                    </div>
                                </a>
                            @endforeach
                        @empty
                            <div class="container text-center mt-5">
                                <img id="notFoundImage" src="" alt="@lang('No Data Found')" class="text-center w-25">
                                <p class="mt-2">@lang('No Data Found')</p>
                            </div>
                        @endforelse
                        {{ $transactions->appends($_GET)->links($theme.'partials.pagination') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('script')
    <script>
        $(document).on('click', '#sendCurrency', function () {
            let currencyCode = $(this).data('currency-code');
            document.cookie = `senderCurrency=${currencyCode}; path=/`;
        });
    </script>
@endpush


@push('loadModal')
    <div class="modal fade" id="infoViewModal" tabindex="-1" role="dialog" aria-hidden="true"
         data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="text-center mb-5">
                        <h4 class="mt-3 mb-1">@lang('Transaction Details')</h4>
                    </div>

                    <div class="row mb-6">
                        <div class="transaction-list mt-2" id="trxModal">
                            <div class="item">
                                <div class="left-side">
                                    <div class="icon">
                                        <i class="fa-regular"></i>
                                    </div>
                                    <span class="remarks"></span>
                                </div>
                                <div class="d-flex gap-2">
                                    <strong class="trxId"></strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="title mb-2 mt-4">@lang('Summary')</div>
                    <ul class="list-container mb-4 ">
                        <li class="item py-2">
                            <span>@lang('Amount')</span>
                            <span class=" fw-semibold amount"></span>
                        </li>
                        <li class="item py-2">
                            <span>@lang('Charge')</span>
                            <span class=" fw-semibold charge"></span>
                        </li>
                        <li class="item py-2">
                            <span>@lang('Transaction Date')</span>
                            <span class=" fw-semibold trx_date"></span>
                        </li>

                    </ul>
                    <div class="modal-footer-text mt-3">
                        <div class="d-flex justify-content-end gap-3 status-buttons">
                            <button type="button" class="cmn-btn2" data-bs-dismiss="modal">@lang('Close')</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endpush

@push('script')
    <script>
        $('.showInfo').click(function() {
            const { amount, baseamount, charge, trxid, note, trx_type, trx_date, remarks } = this.dataset;

            $('.amount').html(amount);
            $('.trxId').html('#'+trxid);
            $('.charge').html(charge);
            $('.trx_date').html(trx_date);

            const iconClass = trx_type === '-' ? 'icon-sent' : 'icon-received';
            const icon = trx_type === '-' ? 'fa-arrow-up' : 'fa-arrow-down';

            $('#trxModal .icon').attr('class', `icon ${iconClass}`);
            $('#trxModal .icon i').attr('class', `fa-regular ${icon}`);
            $('#trxModal .left-side span').html(remarks);


            $('#infoViewModal').modal('show');
        });

    </script>
@endpush
