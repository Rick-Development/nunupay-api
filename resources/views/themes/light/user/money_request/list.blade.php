@extends($theme.'layouts.user')
@section('title',trans('Money Request History'))
@section('content')

    <div class="dashboard-wrapper">
        <a href="{{ route('user.dashboard') }}" class="back-btn">
            <i class="fa-regular fa-angle-left"></i>@lang('Back to Dashboard')
        </a>
        <div class="row">
            <div class="col-xxl-8 col-xl-10 mx-auto">
                <div class="transaction-list-section">
                    <h4 class="mb-15">@lang('Money Request List')</h4>
                    <div class="d-flex justify-content-between">
                        <div class="search-bar">
                            <form class="search-form d-flex align-items-center" method="get" action="">
                                <input type="text" class="form-control" name="search" placeholder="@lang('Search by transaction id')"
                                       value="{{ old('search', request()->search) }}">
                                <button type="submit" class="search-icon" title="Search">
                                    <i class="fa-regular fa-magnifying-glass"></i></button>
                            </form>
                        </div>
                        <button type="button" class="cmn-btn " data-bs-toggle="offcanvas"
                                data-bs-target="#offcanvasExample"
                                aria-controls="offcanvasExample">
                            <i class="fa-light fa-magnifying-glass"></i>{{ trans('Filter') }}
                        </button>
                    </div>
                    <div class="transaction-list">
                        @forelse ($groupedTransactions as $date => $dailyTransactions)
                            <p class="mb-3">{{ dateTime($date) }}</p>
                            @foreach ($dailyTransactions as $item)
                                <a href="{{ route('user.moneyRequestDetails',$item->trx_id) }}" class="item">
                                    <div class="left-side">
                                        <div class="icon {{ $item->requester_id == auth()->id() ? 'icon-sent' : 'icon-received' }}">
                                            <i class="fa-regular {{ $item->requester_id == auth()->id() ? 'fa-arrow-up' : 'fa-arrow-down' }}"></i>
                                        </div>
                                        <div class="d-flex gap-2">
                                            @if($item->requester_id == auth()->id())
                                                @lang('Money request has been sent to')
                                                <span class="fw-bold">{{ $item->rcpUser?->fullname() }}</span>
                                            @else
                                                @lang('You have received a money request from')
                                                <span class="fw-bold">{{ $item->reqUser?->fullname() }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="right-side text-end">
                                        <h5 class="mb-0">
                                            {{ currencyPositionCalc($item->amount, $item->curr) }}
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



