@extends($theme.'layouts.user')
@section('title', trans('Money Request Details'))

@section('content')
    <div class="dashboard-wrapper">
        <a href="{{ route('user.moneyRequestList') }}" class="back-btn">
            <i class="fa-regular fa-angle-left"></i>@lang('Back To Money Request List')</a>
        <div class="row">
            <div class="col-xxl-6 col-xl-10 mx-auto mt-30">
                @if(session('balance_error'))
                    <div class="alert alert-danger alert-dismissible justify-content-between flex-column gap-3 flex-sm-row"
                        role="alert">
                        <div class="d-flex align-items-center">
                            <div class="icon-area"><i class="fa-light fa-triangle-exclamation"></i></div>
                            <div class="text-area">
                                <div class="title">@lang(session()->get('balance_error'))</div>
                            </div>
                        </div>
                        <a href="{{ route('user.add.fund') }}" class="cmn-btn text-nowrap">@lang('Deposit')</a>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                            <i class="fa-regular fa-xmark"></i>
                        </button>
                    </div>
                @endif

                <h4 class="mb-30">@lang('Transaction details')</h4>
                <div class="card">
                    <div class="details-section card-body">
                        <div class="transaction-list mt-2">
                            <div class="item">
                                <div class="left-side">
                                    {!! $trx->getStatusIcon() !!}
                                    @if($trx->requester_id == auth()->id())
                                        @lang('Money request has been sent to')
                                        <span class="fw-bold">{{$trx->rcpUser?->fullname() }}</span>
                                    @else
                                        @lang('You have received a money request from')
                                        <span class="fw-bold">{{ $trx->reqUser?->fullname() }}</span>
                                    @endif
                                </div>
                                <div class="d-flex gap-2">
                                    <strong>#@lang($trx->trx_id)</strong>
                                </div>
                            </div>

                            <h5 class="mt-20">@lang('Basic Information')</h5>
                            <hr class="cmn-hr2">
                            <div class="transfer-list pt-0 pb-0">
                                <div class="item">
                                    <span>@lang('Request Amount')</span>
                                    <h6 class="fw-bold">{{ currencyPositionCalc($trx->amount,$trx->curr) }}</h6>
                                </div>
                                <div class="item">
                                    <span>@lang('Request Date')</span>
                                    <h6>{{ dateTime($trx->created_at) }}</h6>
                                </div>
                            </div>
                            <h5 class="mt-20">@lang($isRequester ? 'Recipient Information' : 'Requester Information')</h5>
                            <hr class="cmn-hr2">
                            <div class="transfer-list pt-0 pb-0">
                                <div class="item">
                                    <span>@lang('Full Name')</span>
                                    <h6>{{ $isRequester ? $trx->rcpUser?->fullname() : $trx->reqUser?->fullname() }}</h6>
                                </div>
                                <div class="item">
                                    <span>@lang('Email')</span>
                                    <h6>{{ $isRequester ? $trx->rcpUser?->email : $trx->reqUser?->email }}</h6>
                                </div>
                            </div>


                            <div class="action">
                                @if(!$isRequester && $trx->status == 0)
                                    <div class="d-flex justify-content-between gap-4 mt-3">
                                        <form action="{{ route('user.moneyRequestAction') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="trx_id" value="{{ $trx->trx_id }}">
                                            <input type="hidden" name="action" id="action" value="">
                                            <button type="submit" class="cmn-btn2"
                                                    onclick="setAction('approve')">@lang('Approve')</button>
                                            <button type="submit" class="delete-btn"
                                                    onclick="setAction('reject')">@lang('Reject')</button>
                                        </form>
                                    </div>
                                @endif
                                @if($trx->status != 0)
                                    <div class="mt-3">
                                        <div
                                            class="{{ $trx->status == 1 ? 'alert alert-success' : 'alert alert-danger' }}">
                                            {{ $trx->status == 1 ? __('Money Request has been Approved') : __('Money Request Rejected') }}
                                        </div>
                                    </div>
                                @endif
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection


@push('script')
    <script>
        function setAction(action) {
            document.getElementById('action').value = action;
        }
    </script>
@endpush




