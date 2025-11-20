@extends($theme.'layouts.user')
@section('title',trans('Deposit History'))
@section('content')
<div class="dashboard-wrapper">
    <a href="{{ route('user.dashboard') }}" class="back-btn">
        <i class="fa-regular fa-angle-left"></i>@lang('Back to Dashboard')
    </a>
    <div class="row">
        <div class="col-xxl-8 col-xl-10 mx-auto">

            <div class="transaction-list-section">
                <h4 class="mb-15">@lang('Deposit History')</h4>
                <div class="d-flex justify-content-between">
                    <div class="search-bar">
                        <form class="search-form d-flex align-items-center" method="get" action="">
                            <input name="search" value="{{ old('search', request()->search) }}"
                                   type="text" class="form-control" id="TransactionId" placeholder="transaction">
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
                    @forelse ($groupedFunds as $date => $dailyTransfers)
                        <p class="mb-3">{{ dateTime($date) }}</p>
                        @foreach ($dailyTransfers as $item)

                            @php
                                $amount = currencyPositionCalc($item->amount,$item->curr);
                                $baseAmount = currencyPosition($item->payable_amount_in_base_currency);
                                $charge = currencyPosition($item->charge);
                                $statusBadge = $item->getStatusBadge();
                            @endphp

                            @if($item->status == 0)
                                <a href="{{ route('payment.process', $item->trx_id) }}" target="_blank" class="item">
                            @else
                                <a class="showInfo item" href="#"
                                   data-amount="{{ $amount }}"
                                   data-baseamount="{{ $baseAmount }}"
                                   data-charge="{{ $charge }}"
                                   data-method="{{ $item->gatewayName }}"
                                   data-gatewayimage="{{ getFile($item->gateway?->driver, $item->gateway?->image) }}"
                                   data-status="{{ $statusBadge }}"
                                   data-trxid="{{ $item->trx_id }}"
                                   data-note="{{ $item->note ?? 'N/A' }}"
                                   data-bs-toggle="modal"
                                   data-bs-target="#infoViewModal">
                            @endif

                                <div class="left-side">
                                    {!! $item->getStatusIcon() !!}
                                    <div class="d-flex gap-2">@lang('Deposit via')
                                        <span class="fw-bold text-capitalize">{{ $item->gatewayName }}</span>
                                    </div>
                                </div>
                                <div class="right-side d-flex align-items-center">
                                    <h5 class="mb-0">
                                        {{ currencyPositionCalc($item->amount,$item->curr) }}
                                        @if (!$item->curr)
                                            {{ $item->payment_method_currency }}
                                        @endif
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
                    {{ $funds->appends($_GET)->links($theme.'partials.pagination') }}
                </div>
            </div>
        </div>
    </div>
</div>


<!-- offCanvas sidebar start -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasExample" aria-labelledby="offcanvasExampleLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="offcanvasExampleLabel"><i class="fa-light fa-magnifying-glass me-2"></i>
            {{ trans('Search') }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <form action="" method="get">
            <div class="row g-4">
                <div class="col-12">
                    <label for="" class="form-label">@lang('Search')</label>
                    <input name="search" value="{{ old('search', request()->search) }}"
                           type="text" class="form-control" id="TransactionId" placeholder="transaction">
                </div>
                <div>
                    <label class="form-label">{{ trans('status') }}</label>
                    <select class="cmn-select2" name="status">
                        <option value="">{{ trans('All status') }}</option>
                        <option value="0"
                                @if(request()->status == '0') selected @endif>{{ trans('Pending') }}</option>
                        <option value="1"
                                @if(request()->status == '1') selected @endif>{{ trans('Success') }}</option>
                        <option value="2"
                                @if(request()->status == '2') selected @endif>{{ trans('Requested') }}</option>
                        <option value="3"
                                @if(request()->status == '3') selected @endif>{{ trans('Rejected') }}</option>
                    </select>
                </div>

                <div class="schedule-component-section">
                    <label class="form-label" for="Date">{{ trans('Start Date') }}</label>
                    <div class="schedule-form mb-3">
                        <input type="date" class="form-control" name="start_date" placeholder="Select a date"
                               value="{{ old('start_date', request()->start_date) }}" id="datePick" autocomplete="off">
                    </div>
                    <label class="form-label" for="Date">{{ trans('End Date') }}</label>
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
                        <h4 class="mt-3 mb-1">@lang('Payment Information')</h4>
                    </div>

                    <div class="row mb-6">
                        <div class="col-md-4">
                            <small class="text-cap mb-0">@lang('Payment method:')</small>
                            <div class="d-flex align-items-center">
                                <img class=" me-2 gateway_image rounded-circle"
                                     src="" alt="Gateway Image">
                                <span class="method"></span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <small class="text-cap mb-0">@lang('Payment amount:')</small> <br>
                            <span class="amount fw-bold vertical-align-middle"></span>
                        </div>
                        <div class="col-md-4">
                            <small class="text-cap mb-0">@lang('Status:')</small><br>
                            <span class=" status">@lang('Generated')</span>
                        </div>
                    </div>

                    <div class="title mb-2 mt-4">@lang('Summary')</div>
                    <ul class="list-container mb-4 ">
                        <li class="item py-2">
                            <span>@lang('Transaction Id')</span>
                            <span class=" fw-semibold trxId"></span>
                        </li>
                        <li class="item py-2">
                            <span>@lang('Charge')</span>
                            <span class=" fw-semibold text-danger charge"></span>
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
            const { amount, baseamount, charge, method, gatewayimage, status, trxid, note } = this.dataset;

            $('.method').html(method);
            $('.gateway_image').attr('src', gatewayimage);
            $('.amount').html(amount);
            $('.status').html(status);
            $('.trxId').html('#'+trxid);
            $('.charge').html(charge);
            $('.baseAmount').html(baseamount);
            $('.note').html(note);

            $('#infoViewModal').modal('show');
        });

    </script>
@endpush
