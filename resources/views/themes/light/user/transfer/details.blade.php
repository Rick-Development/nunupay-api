@extends($theme.'layouts.user')
@section('title', trans('Transfer Details'))

@section('content')
<div class="dashboard-wrapper">
    <a href="{{ route('user.transferList') }}" class="back-btn">
        <i class="fa-regular fa-angle-left"></i>@lang('Back to Transfer List')
    </a>
    <div class="row">
        <div class="col-xxl-8 col-xl-10 mx-auto mt-30">
            @if($transferDetails->status == 3)
                <div class="alert alert-danger justify-content-between flex-column gap-3 flex-sm-row card-resubmit"
                     role="alert">
                    <div class="d-flex align-items-center">
                        <div class="icon-area"><i class="fa-light fa-triangle-exclamation"></i></div>
                        <div class="text-area">
                            <div class="description">
                                {{ trans('You Have Rejected for this Transfer Request') }}
                            </div>
                        </div>
                    </div>
                    <div class="btn-area row g-3 align-items-center justify-content-md-between justify-content-center">
                        <div class="col-auto">
                            <button class="cmn-btn3"
                                    data-bs-target="#reasonModal" data-bs-toggle="modal">{{ trans('Reason') }}
                            </button>
                        </div>
                        <div class="col-auto">
                            @if($transferDetails->resubmitted == 1)
                                <a href="{{ route('user.transferPay', $transferDetails->uuid) }}"
                                   class="cmn-btn">{{ trans('Resubmit') }}</a>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
            <div class="card">

                <div class="card-header details-header">

                    <div class="item">
                        <div class="item-left">
                            <div class="thumb-area">
                                <i class="fa-light fa-arrow-up"></i>
                            </div>
                            <div class="content-area">
                                <h5 class="mb-0">@lang('Sent Money To') {{ $transferDetails->recipient?->name }}</h5>
                            </div>
                        </div>
                        <div class="item-right">
                            <div class="content-area">
                                <h6 class="mb-1">
                                    {{ currencyPositionCalc($transferDetails->recipient_get_amount,$transferDetails->receiverCurrency) }}
                                </h6>
                                <span>{{ currencyPositionCalc($transferDetails->send_amount,$transferDetails->senderCurrency) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body transfer-details-recipients">
                    <div class="row g-lg-5">
                        <div class="col-md-6 ">
                            <ul class="transfer-list">
                                <li class="item title"><h6>{{ trans('Transfer Details') }}</h6></li>
                                <li class="item"><span>{{ trans('You send exactly') }}</span>
                                    <span class="fw-semibold">
                                        {{ currencyPositionCalc($transferDetails->send_amount,$transferDetails->senderCurrency) }}</span>
                                </li>
                                <li class="item"><span>{{ trans('Transfer Fee') }}</span>
                                    <span class="fw-semibold text-danger">
                                        {{ currencyPositionCalc($transferDetails->fees,$transferDetails->senderCurrency) }}</span>
                                </li>

                                <li class="item"><span>{{ trans('Send Total') }}</span> <span class="fw-semibold">
                                        {{ currencyPositionCalc($transferDetails->payable_amount, $transferDetails->senderCurrency) }}</span>
                                </li>
                                <li class="item pb-4 border-bottom"><span>{{ trans('Exchange Rate') }}</span>
                                    <span class="fw-semibold">{{ currencyPositionCalc(1,$transferDetails->senderCurrency) }} =
                                    {{ currencyPositionCalc($transferDetails->rate,$transferDetails->receiverCurrency) }}</span>
                                </li>
                                <li class="item pb-4 border-bottom"><span>{{ trans('Recipient will Get') }}</span>
                                    <span class="fw-semibold text-success">
                                   {{ currencyPositionCalc($transferDetails->recipient_get_amount,$transferDetails->receiverCurrency) }}</span>
                                </li>
                                <li class="item"><span>{{ trans('Transaction ID') }}</span> <span class="">
                                        #{{ $transferDetails->trx_id }}</span>
                                </li>
                            </ul>
                        </div>
                        <div class="col-md-6 ">
                            <ul class="transfer-list">

                                <li class="item title"><h6>{{ trans('Recipients details') }}</h6></li>
                                <li class="item"><span>{{ trans('Name') }}</span>
                                    <span class="fw-semibold">{{ $transferDetails->recipient?->name }}</span>
                                </li>
                                <li class="item"><span>{{ trans('Email') }}</span>
                                    <span class="fw-semibold">{{ $transferDetails->recipient?->email }}</span>
                                </li>
                                @if(!$transferDetails->r_user_id)
                                    <li class="item"><span>{{ trans('Send to') }}</span>
                                        <span class="fw-semibold">{{ $transferDetails->recipient?->bank?->name }}</span>
                                    </li>
                                    <li class="item"><span>{{ trans('Service') }}</span>
                                        <span class="fw-semibold">{{ $transferDetails->service?->name }}</span>
                                    </li>
                                @endif

                                @forelse(optional($transferDetails->recipient)->bank_info ?? [] as $key=>$value)
                                    <li class="item">
                                        <span>{{ snake2Title($key) }}</span>
                                        <span class="fw-semibold">{{ $value }}</span>
                                    </li>
                                @empty
                                @endforelse

                                <li class="item"><span>@lang('Transfer Status')</span>
                                    <span class="fw-semibold">{!! $transferDetails->getStatusForTransfer($transferDetails->status) !!} </span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


@push('loadModal')
    <div id="reasonModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modal" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="reject-reason">@lang('Rejected Reason')</h5>
                    <button type="button" class="cmn-btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="fa-light fa-xmark"></i></button>
                </div>
                <div class="modal-body text-justify">
                    @lang($transferDetails->reason)
                </div>
                <div class="modal-footer">
                    <button type="button" class="cmn-btn2" data-bs-dismiss="modal">@lang('Close')</button>
                </div>
            </div>
        </div>
    </div>
@endpush




