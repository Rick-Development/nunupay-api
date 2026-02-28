@extends('admin.layouts.app')
@section('page_title', __('Transfer Details'))
@section('content')
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title">@yield('page_title')</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb breadcrumb-no-gutter">
                            <li class="breadcrumb-item">
                                <a class="breadcrumb-link" href="{{ route('admin.dashboard') }}">@lang("Dashboard")</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">@lang('Money Transfer')</li>
                            <li class="breadcrumb-item active" aria-current="page">@yield('page_title')</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <div class="section-body">
            <div class="row mt-sm-4">
                <div class="col-12 col-md-12 col-lg-12">
                    <div class="container-fluid" id="container-wrapper">

                        <div class="row justify-content-md-center">
                            <div class="col-lg-6 col-md-6">
                                <div class="card mb-4 card-primary shadow h-100">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h4 class="card-title m-0 py-2">@lang('Transfer Details')</h4>
                                        <div class="d-flex justify-content-end">
                                            @if($status == 3)
                                                <a href="javascript:void(0)"
                                                   data-bs-target="#rejectReason"
                                                   data-bs-toggle="modal"
                                                   class="btn btn-secondary btn-sm mx-2">@lang('Rejected Reason')</a>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <ol class="list-group">
                                            <li class="list-group-item d-flex justify-content-between">
                                                <span class="fw-semibold">{{ __('Transaction Id') }}</span>
                                                <span class="fw-semibold">
                                                    #{{ $transfer->trx_id }}
                                                </span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between">
                                                <span class="fw-semibold">{{ __('Send Amount') }}</span>
                                                <span class="">
                                                    {{ currencyPositionCalc($transfer->send_amount,$transfer->senderCurrency) }}
                                                </span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between">
                                                <span class="fw-semibold">{{ __('Fees') }}</span>
                                                <span class=" text-danger">
                                                    {{ currencyPositionCalc($transfer->fees,$transfer->senderCurrency) }}
                                                </span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between">
                                                <span class="fw-semibold">{{ __('Exchange Rate') }}</span>
                                                <span class="">
                                                    {{ currencyPositionCalc(1,$transfer->senderCurrency) }} =
                                                    {{ currencyPositionCalc($transfer->rate,$transfer->receiverCurrency) }}
                                                </span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between">
                                                <span class="fw-semibold">{{ __('Receiver Amount') }}</span>
                                                <span class="">
                                                    {{ currencyPositionCalc($transfer->recipient_get_amount,$transfer->receiverCurrency) }}
                                                </span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between">
                                                <span class="fw-semibold">{{ __('Service Name') }}</span>
                                                <span class="">{{ optional($transfer->service)->name }}</span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between">
                                                <span class="fw-semibold">{{ __('Send to') }}</span>
                                                <span class="">{{ optional(optional($transfer->recipient)->bank)->name }}</span>
                                            </li>
                                            @forelse(optional($transfer->recipient)->bank_info ?? [] as $key=>$value)
                                                <li class="list-group-item d-flex justify-content-between">
                                                    <span class="fw-semibold">{{ snake2Title($key) }}</span>
                                                    <span class="">{{ $value }}</span>
                                                </li>
                                            @empty
                                            @endforelse
                                        </ol>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card mb-4 card-primary shadow h-100">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h4 class="m-0 card-title">@lang('Customer details')</h4>
                                        <a href="{{route('admin.transferList')}}" class="btn btn-info btn-sm">
                                            <i class="bi bi-arrow-left me-2"></i>@lang('Back')</a>
                                    </div>
                                    <div class="card-body">
                                        <ol class="list-group list-group">
                                            <li class="list-group-item d-flex justify-content-between">
                                                <span class="fw-semibold">{{ __('Sender Name') }}</span>
                                                <a href="{{route('admin.user.view.profile',$transfer->user?->id)}}"
                                                   class="decoration__none">
                                                    <span class="fw-semibold">{{optional($transfer->user)->fullname()}}</span>
                                                </a>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between">
                                                <span class="fw-semibold">{{ __('Sender Email') }}</span>
                                                <span class="">{{optional($transfer->user)->email}}</span>
                                            </li>

                                            <li class="list-group-item d-flex justify-content-between">
                                                <span class="fw-semibold">{{ __('Receiver Name') }}</span>
                                                <span
                                                    class="">{{optional($transfer->recipient)->name}}</span>
                                            </li>


                                            <li class="list-group-item d-flex justify-content-between">
                                                <span class="fw-semibold">{{ __('Receiver Email') }}</span>
                                                <span
                                                    class="">{{optional($transfer->recipient)->email}}</span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between">
                                                <span class="fw-semibold">{{ __('Initiate At') }}</span>
                                                <span class="">{{ $transfer->created_at->format('d M Y h:iA') }}</span>
                                            </li>

                                            <li class="list-group-item d-flex justify-content-between">
                                                <span class="fw-semibold">{{ __('Paid At') }}</span>
                                                <span class="">
                                                    {{ \Carbon\Carbon::parse($transfer->paid_at)->format('d M Y h:iA') }}
                                                </span>
                                            </li>

                                            <li class="list-group-item d-flex justify-content-between">
                                                <span class="fw-semibold">{{ __('Status') }}</span>
                                                <span class="fw-semibold">{!! $transfer->getStatusForTransfer($status) !!}</span>

                                            </li>
                                        </ol>

                                        <div class="mt-5">
                                            @if($status == 2)
                                            <button class="btn btn-success me-2"
                                                    data-bs-target="#approveModal"
                                                    data-bs-toggle="modal">@lang('Approve')</button>
                                                @if($status == 2)
                                                <button class="btn btn-danger "
                                                        data-bs-target="#rejectModal"
                                                        data-bs-toggle="modal">@lang('Reject')</button>
                                                @endif
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

@endsection


@push('loadModal')


    <div id="approveModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="primary-header-modalLabel"
         aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title text-dark font-weight-bold"
                        id="primary-header-modalLabel">@lang('Approve Confirmation')</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>

                </div>
                <form action="{{route('admin.transferApprove',$transfer->id)}}" method="get">
                    <div class="modal-body">
                        <p>@lang('Are you want to approve this transfer request ?')</p>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-white" data-bs-dismiss="modal">@lang('Close')</button>
                        <button type="submit" class="btn btn-primary change-yes">@lang('Yes')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="rejectModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="primary-header-modalLabel"
         aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title text-dark font-weight-bold"
                        id="primary-header-modalLabel">@lang('Reject Confirmation')</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{route('admin.transferRejected',$transfer->id)}}" method="post">
                    @csrf
                    <div class="modal-body">
                        <div class="row ">
                            <div class="selectgroup w-100">
                                <label class="form-label">@lang('Allow to user resubmitted ?')</label>

                                <input type="radio" class="btn-check mx-2" name="resubmitted" id="success-outlined"
                                       value="1" checked>
                                <label class="btn btn-sm btn-outline-success" for="success-outlined">Yes</label>

                                <input type="radio" class="btn-check" name="resubmitted" id="danger-outlined" value="0">
                                <label class="btn btn-sm btn-outline-danger" for="danger-outlined">No</label>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-12">
                                <lable class="form-label">@lang('Reasons')</lable>
                                <textarea class="form-control" name="reason" required></textarea>
                            </div>
                            @error('reason')<span class="text-danger ml-2">{{$message}}</span>@enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-white" data-bs-dismiss="modal">@lang('Close')</button>
                        <button type="submit" class="btn btn-primary change-yes">@lang('Yes')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @if($status == 3)
        <div id="rejectReason" class="modal fade" tabindex="-1" role="dialog"
             aria-labelledby="primary-header-modalLabel"
             aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title text-dark font-weight-bold"
                            id="primary-header-modalLabel">@lang('Rejected Reason ')</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row mt-2">
                            <div class="col-md-12">
                                <lable class="form-label">@lang('Reasons')</lable>
                                <div class="modal-body">
                                    @lang($transfer->reason)
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-dark" data-bs-dismiss="modal">@lang('Close')</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

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
