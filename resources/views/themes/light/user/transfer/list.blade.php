@extends($theme.'layouts.user')
@section('title', trans('Money Transfer History'))

@section('content')
<div class="dashboard-wrapper">
    <a href="{{ route('user.dashboard') }}" class="back-btn">
        <i class="fa-regular fa-angle-left"></i>@lang('Back to Dashboard')
    </a>
    <div class="row">
        <div class="col-xxl-8 col-xl-10 mx-auto">
            <div class="transaction-list-section">
                <h4 class="mb-15">@lang('Transfer History')</h4>
                <div class="d-flex justify-content-between">
                    <div class="search-bar">
                        <form class="search-form d-flex align-items-center" method="get" action="">
                            <input name="name" value="{{ old('name', request()->name) }}"
                                   type="text" class="form-control" id="name" placeholder="Name, Email">
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
                    @forelse ($groupedTransfers as $date => $dailyTransfers)
                        <p class="mb-3">{{ dateTime($date) }}</p>
                        @foreach ($dailyTransfers as $item)
                            <a class="item"
                                 href="{{ $item->status == 0 ? route('user.transferPay', $item->uuid) : route('user.transferDetails',$item->uuid)  }}">
                                <div class="left-side">
                                    {!! $item->getStatusBadge() !!}

                                    <div class="d-flex gap-2">@lang('Sending money to')
                                        <span class="fw-bold text-capitalize">{{ $item->recipient?->name }}</span>
                                    </div>
                                </div>
                                <div class="right-side">
                                    <h5 class="mb-0">
                                        {{ currencyPositionCalc($item->send_amount,$item->senderCurrency) }}
                                    </h5>
                                    <small class="fw-semibold">
                                        {{ currencyPositionCalc($item->recipient_get_amount,$item->receiverCurrency) }}
                                    </small>
                                </div>
                            </a>
                        @endforeach
                    @empty
                        <div class="container text-center mt-5">
                            <img id="notFoundImage" src="" alt="@lang('No Data Found')" class="text-center w-25">
                            <p class="mt-2">@lang('No Data Found')</p>
                        </div>
                    @endforelse
                    {{ $transfers->appends($_GET)->links($theme.'partials.pagination') }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- offCanvas sidebar start -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasExample" aria-labelledby="offcanvasExampleLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="offcanvasExampleLabel"><i
                class="fa-light fa-magnifying-glass me-2"></i>{{ trans('Search') }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <form action="" method="get">
            <div class="row g-4">
                    <div>
                        <label class="form-label">{{ trans('status') }}</label>
                        <select class="cmn-select2" name="status">
                            <option value="">{{ trans('All status') }}</option>
                            <option value="0"
                                    @if(request()->status == '0') selected @endif>{{ trans('Draft/Initiate') }}</option>
                            <option value="1"
                                    @if(request()->status == '1') selected @endif>{{ trans('Completed') }}</option>
                            <option value="2"
                                    @if(request()->status == '2') selected @endif>{{ trans('Under Review') }}</option>
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
