@extends($theme.'layouts.user')
@section('title',trans('All Transaction History'))
@section('content')

    <div class="dashboard-wrapper">
        <a href="{{ route('user.dashboard') }}" class="back-btn">
            <i class="fa-regular fa-angle-left"></i>@lang('Back to Dashboard')
        </a>
        <div class="row">
            <div class="col-xxl-8 col-xl-10 mx-auto">
                <div class="transaction-list-section">
                    <h4 class="mb-15">@lang('Transactions')</h4>
                    <div class="d-flex justify-content-between">
                        <div class="search-bar">
                            <form class="search-form d-flex align-items-center" method="get" action="">
                                <input type="text" class="form-control" name="search" placeholder="@lang('Search by transaction id'), or remarks"
                                       value="{{ old('search', request()->search) }}">
                                <button type="submit" class="search-icon" title="Search">
                                    <i class="fa-regular fa-magnifying-glass"></i></button>
                            </form>
                        </div>
                        <button type="button" class="cmn-btn " data-bs-toggle="offcanvas"
                                data-bs-target="#offcanvasExample"
                                aria-controls="offcanvasExample"><i class="fa-light fa-magnifying-glass"></i>{{ trans('Filter') }}
                        </button>
                    </div>
                    <div class="transaction-list">
                        @forelse ($groupedTransactions as $date => $dailyTransactions)
                            <p class="mb-3">{{ dateTime($date) }}</p>
                            @foreach ($dailyTransactions as $transaction)

                                @php
                                    $amount = currencyPositionCalc($transaction->amount,$transaction->curr);
                                    $charge = currencyPosition($transaction->charge);
                                @endphp

                                <a class="item showInfo" href="#"
                                   data-trx_type="{{ $transaction->trx_type }}"
                                   data-remarks="{{ $transaction->remarks }}"
                                   data-note="{{ $item->note ?? 'N/A' }}"
                                   data-amount="{{ $amount }}"
                                   data-charge="{{ $charge }}"
                                   data-trxid="{{ $transaction->trx_id }}"
                                   data-trx_date="{{ dateTime($transaction->created_at) }}"
                                   data-bs-toggle="modal"
                                   data-bs-target="#infoViewModal"
                                >
                                    <div class="left-side">
                                        <div class="icon {{ $transaction->trx_type == '-' ? 'icon-sent' : 'icon-received' }}">
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



    <!-- offCanvas sidebar start -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasExample" aria-labelledby="offcanvasExampleLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="offcanvasExampleLabel"><i class="fa-light fa-magnifying-glass me-2"></i>{{ trans('Search') }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <form action="" method="get">
                <div class="row g-4">
                    <div>
                        <label for="TransactionId" class="form-label">{{ trans('Transaction ID') }}</label>
                        <input name="search" value="{{ old('search', request()->search) }}"
                               type="text" class="form-control" id="TransactionId" placeholder="B7H9CV6BATZ8">
                    </div>
                    <div class="schedule-component-section">
                        <label class="form-label" for="startDate">{{ trans('Start Date') }}</label>
                        <div class="schedule-form mb-3">
                            <input type="date" class="form-control" name="start_date" placeholder="Select a date"
                                   value="{{ old('start_date', request()->start_date) }}" id="datePick" autocomplete="off">
                        </div>
                        <label class="form-label" for="datePick">{{ trans('End Date') }}</label>
                        <div class="schedule-form">
                            <input type="date" class="form-control" name="end_date" placeholder="Select a date"
                                   value="{{ old('end_date', request()->end_date) }}" id="datePick" autocomplete="off">
                        </div>
                    </div>
                    <div class="btn-area">
                        <button type="submit" class="cmn-btn">
                            <i class="fa-light fa-magnifying-glass me-2"></i>{{ trans('Search') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

@endsection

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
                            <span class=" fw-bold amount"></span>
                        </li>
                        <li class="item py-2">
                            <span>@lang('Charge')</span>
                            <span class=" fw-semibold text-danger charge"></span>
                        </li>
                        <li class="item py-2">
                            <span>@lang('Transaction Date')</span>
                            <span class=" fw-semibold trx_date"></span>
                        </li>
                        <li class="item py-2">
                            <span>@lang('Note')</span>
                            <span class=" fw-semibold note"></span>
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
            const { amount, charge, trxid, trx_type, trx_date, remarks ,note} = this.dataset;

            $('.amount').html(amount);
            $('.trxId').html('#'+trxid);
            $('.charge').html(charge);
            $('.trx_date').html(trx_date);
            $('.note').html(note);

            const iconClass = trx_type === '-' ? 'icon-sent' : 'icon-received';
            const icon = trx_type === '-' ? 'fa-arrow-up' : 'fa-arrow-down';

            $('#trxModal .icon').attr('class', `icon ${iconClass}`);
            $('#trxModal .icon i').attr('class', `fa-regular ${icon}`);
            $('#trxModal .left-side span').html(remarks);

            $('#infoViewModal').modal('show');
        });

    </script>
@endpush





