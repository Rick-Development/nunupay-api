@extends($theme.'layouts.user')
@section('title',__('Virtual Cards'))

@section('content')
<div class="dashboard-wrapper">
    <div class="col-xxl-8 col-xl-10 mx-auto">
        <div class="breadcrumb-area"><h3 class="title">@lang('Virtual Cards')</h3></div>

        <div class="alert alert-info alert-dismissible" role="alert">
            <div class="icon-area"><i class="fa-light fa-info-circle"></i></div>
            <div class="text-area">
                <div class="description">
                    {{ trans('A “virtual card” is stored on your phone and can be used to pay contactless in stores or online, but has its own unique card number, expiry date, and CVC.') }}
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"><i class="fa-regular fa-xmark"></i></button>
        </div>

    @if(!empty($cardOrder))
        @if($cardOrder->status == 0 || $cardOrder->status == 3)
            <div class="alert alert-warning alert-dismissible" role="alert">
                <div class="icon-area"><i class="fa-light fa-circle-exclamation"></i></div>
                <div class="text-area">
                    <div class="description">{{ trans('Your virtual card request is pending.') }}</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"><i class="fa-regular fa-xmark"></i></button>
            </div>
        @endif
        @if($cardOrder->status == 2)
            <div class="alert alert-danger justify-content-between flex-column gap-3 flex-sm-row card-resubmit" role="alert">
                <div class="d-flex align-items-center">
                    <div class="icon-area"><i class="fa-light fa-triangle-exclamation"></i></div>
                    <div class="text-area">
                        <div class="description">
                            @lang('Your virtual card request is rejected by authority')
                        </div>
                    </div>
                </div>
                <div class="btn-area row g-3 justify-content-md-between justify-content-center">
                    <div class="col-auto">
                        <button class="delete-btn" data-bs-target="#rejectReason" data-bs-toggle="modal">
                            @lang('Reason')
                        </button>
                    </div>
                    <div class="col-auto">
                        @if($cardOrder->resubmitted == 1)
                            <a href="{{route('user.virtual.card.orderReSubmit')}}" class="cmn-btn text-nowrap">
                                @lang('Resubmit Now')
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @endif
        @if($cardOrder->status == 4)
            <div class="alert alert-warning alert-dismissible" role="alert">
                <div class="icon-area"><i class="fa-light fa-circle-exclamation"></i></div>
                <div class="text-area">
                    <div class="description">{{ trans('Your virtual card request is generated please make it complete.') }}</div>
                </div>
                <a href="{{route('user.order.confirm',$cardOrder->id)}}" class="cmn-btn mx-5">@lang('Confirm')</a>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"><i class="fa-regular fa-xmark"></i></button>
            </div>
        @endif
    @endif
        <div class="row mb-3">
            <div class="container-fluid" id="container-wrapper">
                <div class="row g-3 justify-content-between mt-3">
                    @if(count($approveCards)>0)
                        @foreach($approveCards as $card)
                            <div class="col-md-6 col-lg-4">
                                <div class="card bank-card-box">
                                    <div class="card-header d-flex flex-column gap-3 ">
                                        <div class="d-flex gap-3">
                                            <div class="icon">
                                                <i class="fas fa-credit-card"></i>
                                            </div>
                                            <div class="text">
                                                <h5><span class="text-break">{{$card->card_number ?? "45155121554155"}}</span>  | <span>{{$card->currency}}</span></h5>
                                                <div class="d-flex flex-row gap-3 ">
                                                    <span>{{$card->name_on_card ?? "Card User"}} </span>
                                                    @if($card->status == 5)
                                                        <span class="mb-0 text-danger">@lang('Requested Block')</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        <div class="dropdown settings-dropdown dropdown-menu-end">
                                            <button class="dropdown-toggle settings-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="fa-regular fa-gear"></i>
                                            </button>
                                            <ul class="dropdown-menu">

                                                @if($card->status != 9)
                                                    <a class="dropdown-item"
                                                       href="{{route('user.add.fund').'?card='.$card->id }}">@lang('Add Fund')</a>
                                                    @if($card->status != 5 && $card->status != 6)
                                                        <li><a class="dropdown-item blockRqst"
                                                               data-bs-target="#blockRqst"
                                                               data-bs-toggle="modal"
                                                               data-route="{{route('user.virtual.cardBlock',$card->id)}}"
                                                               href="javascript:void(0)">@lang('Block Card')</a></li>
                                                    @endif
                                                @endif
                                                <li><a class="dropdown-item" href="{{route('user.virtual.cardTransaction',$card->card_Id)}}">{{ trans('Transaction') }}</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="card-body py-10">
                                        <div class="bottom-area d-flex justify-content-between align-items-end">
                                            <div>
                                            <span
                                                class="badge {{($card->is_active == 'Active') ? 'text-bg-success':'text-bg-danger'}} mb-3">{{$card->is_active}}</span>
                                                <p class="mb-0">@lang('Valid Thru:') {{\Carbon\Carbon::parse($card->expiry_date)->format('m/y')}}</p>
                                            </div>
                                            <div>
                                                <p class="mb-1">@lang('CVV:') {{$card->cvv}}</p>
                                                <h4 class="balance mb-1">{{getAmount($card->balance,2)}} {{$card->currency}}</h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach

                    @else
                        <div class="col-md-6 col-lg-4 text-center">
                            <img id="notFoundImage" src="" alt="@lang('You do not have any virtual card')" class="text-center w-50">
                            <h5 class="title mt-3">@lang('You do not have any virtual card')</h5>
                        </div>
                    @endif
                    <div class="col-md-6 col-lg-4">
                        <a href="{{route('user.virtual.card.order')}}" class="decoration__none">
                            <div class="card bank-card-box">
                                <div class="card-header d-flex align-items-center gap-3">
                                    <div class="icon"><i class="fas fa-credit-card"></i></div>
                                    <div class="text">
                                        <div class="d-flex justify-content-start">
                                            <h5 class="">@lang('Virtual Card')</h5>
                                            @if($orderLock == 'true')
                                                <i class="fa fa-lock mx-3 "></i>
                                            @endif
                                        </div>
                                        <span class="text-danger">@lang('Per Card Request Charge') {{ currencyPosition(basicControl()->v_card_charge) }}</span>
                                    </div>
                                </div>
                                <div class="card-body py-10">
                                    <h6 class="fw-semibold">@lang('Request for get a virtual card')</h6>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('loadModal')
        @if(!empty($cardOrder))
            @if($cardOrder->status == 2)
                <div id="rejectReason" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="reason-modalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title text-dark font-weight-bold"
                                    id="reason-modalLabel">@lang('Rejected Reason')</h4>
                                <button type="button" class="cmn-btn-close" data-bs-dismiss="modal" aria-label="Close">
                                    <i class="fa-light fa-xmark"></i></button>
                            </div>
                            <div class="modal-body">
                                <div class="row mt-2">
                                    <div class="col-md-12">
                                        <div class="modal-body text-justify">
                                            @lang($cardOrder->reason)
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="cmn-btn2" data-bs-dismiss="modal">@lang('Close')</button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endif

        <div id="blockRqst" class="modal fade" tabindex="-1" role="dialog"
             aria-labelledby="primary-header-modalLabel"
             aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title text-dark font-weight-bold"
                            id="primary-header-modalLabel">@lang('Block Confirmation')</h4>
                        <button type="button" class="cmn-btn-close" data-bs-dismiss="modal" aria-label="Close">
                            <i class="fa-light fa-xmark"></i></button>
                    </div>
                    <form action="" method="post" class="blockForm">
                        @csrf
                        <div class="modal-body">
                            <p>@lang('Are You sure to send block request for this card ?')</p>
                            <div class="row">
                                <div class="col-md-12">
                                    <label>@lang('Reason For Block')</label>
                                    <textarea class="form-control" name="reason"></textarea>
                                    @error('reason')
                                    <span class="text-danger">{{$message}}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="cmn-btn3" data-bs-dismiss="modal">@lang('Close')</button>
                            <button type="submit" class="cmn-btn">@lang('Yes')</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div id="fundRqst" class="modal fade" tabindex="-1" role="dialog"
             aria-labelledby="primary-header-modalLabel"
             aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title text-dark font-weight-bold"
                            id="primary-header-modalLabel">@lang('Add Fund Confirmation')</h4>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">×</button>
                    </div>
                    <form action="#" method="post" class="">
                        @csrf
                        <div class="modal-body">
                            <p>@lang('Are You sure to send fund request for this card ?')</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="cmn-btn3" data-bs-dismiss="modal">@lang('Close')</button>
                            <button type="submit" class="btn btn-primary">@lang('Yes')</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
@endpush


@push('script')
	<script>
		'use strict';
		$(document).on('click', '.blockRqst', function () {
			var route = $(this).data('route');
			$('.blockForm').attr('action', route);
		})
    </script>
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
