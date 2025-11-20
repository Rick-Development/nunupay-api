@extends($theme.'layouts.user')
@section('title',__('Card Transactions'))

@section('content')
    <div class="dashboard-wrapper">
        <a href="{{ route('user.virtual.card') }}" class="back-btn mb-20">
            <i class="fa-regular fa-angle-left"></i>@lang('Back To Card List')</a>
        <div class="col-xxl-8 col-xl-10 mx-auto">
            <div class="transaction-list-section">
                <h4 class="mb-15">
                    {{ trans('Card Transactions') }} - {{@$cardTransactions[0]->cardOrder->card_number ?? 424252629900}}
                </h4>
                <div class="transaction-list">
                    @forelse ($groupedTransactions as $date => $dailyTransactions)
                        <p class="mb-3">{{ dateTime($date) }}</p>
                        @foreach ($dailyTransactions as $transaction)
                            <div class="item showInfo">
                                <div class="left-side">
                                    <div class="icon icon-info">
                                        <i class="fa-regular fa-check-circle"></i>
                                    </div>
                                    <div class="d-flex gap-2 fw-semibold">
                                        @lang('Transaction via')
                                        <span class="fw-bold">{{ $transaction->cardOrder?->cardMethod?->name }}</span>
                                    </div>
                                </div>
                                <div class="right-side text-end">
                                    <h5 class="mb-0">
                                        {{ currencyPositionCalc($transaction->amount, $transaction->curr) }}
                                    </h5>
                                </div>
                            </div>
                        @endforeach
                    @empty
                        <div class="container text-center mt-5">
                            <img id="notFoundImage" src="" alt="@lang('No Data Found')" class="text-center w-25">
                            <p class="mt-2">@lang('No Data Found')</p>
                        </div>
                    @endforelse
                    {{ $cardTransactions->appends($_GET)->links($theme.'partials.pagination') }}
                </div>
            </div>

        </div>
    </div>
@endsection

