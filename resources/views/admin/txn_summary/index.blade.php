@extends('admin.layouts.app')
@section('page_title', __('Transaction Summary'))
@section('content')
    <div class="content container-fluid mb-3">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title">@yield('page_title')</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb breadcrumb-no-gutter">
                            <li class="breadcrumb-item">
                                <a class="breadcrumb-link" href="{{route('admin.dashboard')}}">@lang('Dashboard')</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">@yield('page_title')</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <div class="row">
            @include('admin.partials.transaction.txnGraph')
        </div>
        <div class="row g-5">
            <div class="col-xl-3 col-sm-6 col-lg-3">
                <a class="card user-card card-hover-shadow h-100">
                    <div class="card-body">
                        <div class="card-title-top">
                            <i class="bi-cash-stack"></i>
                            <h6 class="card-subtitle">@lang('Total Transaction')</h6>
                        </div>
                        <div class="row align-items-center gx-2 mb-1">
                            <h2 class="card-title text-inherit" id="totalTxn">125</h2>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-xl-3 col-sm-6 col-lg-3">
                <a class="card user-card card-hover-shadow h-100">
                    <div class="card-body">
                        <div class="card-title-top">
                            <i class="bi-cash-stack"></i>
                            <h6 class="card-subtitle">@lang("Daily Transaction")</h6>
                        </div>
                        <div class="row align-items-center gx-2 mb-1">
                            <h2 class="card-title text-inherit" id="dailyTxn"></h2>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-xl-3 col-sm-6 col-lg-3">
                <a class="card user-card card-hover-shadow h-100">
                    <div class="card-body">
                        <div class="card-title-top">
                            <i class="bi-cash-coin"></i>
                            <h6 class="card-subtitle">@lang("Total Profit")</h6>
                        </div>
                        <div class="row align-items-center gx-2 mb-1">
                            <h2 class="card-title text-inherit" id="totalProfit"></h2>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-xl-3 col-sm-6 col-lg-3">
                <a class="card user-card card-hover-shadow h-100">
                    <div class="card-body">
                        <div class="card-title-top">
                            <i class="bi-cash-coin"></i>
                            <h6 class="card-subtitle">@lang("Daily Profit")</h6>
                        </div>
                        <div class="row align-items-center gx-2 mb-1">
                            <h2 class="card-title text-inherit" id="dailyProfit"></h2>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <div class="card mt-5">
            <div class="card-header card-header-content-between">
                <h4 class="card-header-title">@lang("Currencies")</h4>
            </div>
            <div class="table-responsive">
                <table class="table table-borderless table-thead-bordered table-align-middle card-table">
                    <thead class="thead-light">
                    <tr>
                        <th>@lang('SL')</th>
                        <th>@lang('Currency')</th>
                        <th>@lang('User Wallet Amount')</th>
                        <th>@lang('Currency Rate')</th>
                        <th>@lang('Status')</th>
                        <th>@lang('Action')</th>
                    </tr>
                    </thead>
                    <tbody>

                    @forelse($currencies as $item)
                        <tr>
                            <td><span> @serial </span></td>
                            <td>
                                <div class="d-flex align-items-center me-2">
                                    <div class="flex-shrink-0">
                                        {!! $item->country->countryImage() !!}
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <span class="d-block h5 mb-0"> {{ $item->name }}</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center fw-semibold">
                                  {{ currencyPositionCalc($item->userWalletBalance(), $item->wallet?->currency)  }}
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    @lang('1 USD = '.getAmount($item->rate).' '.$item->code)
                                </div>
                            </td>
                            <td>{!! renderStatusBadge($item->country->status) !!}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a class="btn btn-white btn-sm"
                                       href="{{ route('admin.txnSummary.details',$item->code) }}">
                                        <i class="bi-eye-fill me-1"></i> @lang("View Transaction")
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        {!! renderNoData() !!}
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection

@push('js-lib')
    <script src="{{ asset('assets/admin/js/chart.min.js') }}"></script>
    <script src="{{ asset('assets/global/js/apexcharts.min.js') }}"></script>
@endpush

@push('script')
    <script>
        let $monthlyTransaction = 0;
        let $totalTransaction = {};

        Notiflix.Block.standard('#RecentTransactionGraph');

        function monthlyTransaction(keyDataset = 0) {
            $.ajax({
                url: "{{ route('admin.monthly.transaction') }}",
                data: {
                    keyDataset
                },
                dataType: 'json',
                type: "GET",
                success: function (data) {

                    $totalTransaction[keyDataset] = data.dailyTransaction;
                    $monthlyTransaction = (data.totalTransaction)
                    $monthlyProfit = (data.totalProfit)

                    $(".transaction_amount").text($monthlyTransaction);

                    const todayDate = "Day " + new Date().getDate().toString().padStart(2, '0');
                    const todayTransaction = data.dailyTransaction[todayDate];
                    const todayProfit = data.dailyProfit[todayDate];
                    const baseCurrency = "{{ basicControl()->base_currency }}";


                    $("#dailyTxn").text(todayTransaction + " " + baseCurrency);
                    $("#dailyProfit").text(todayProfit + " " + baseCurrency);
                    $("#totalTxn").text($monthlyTransaction);
                    $("#totalProfit").text($monthlyProfit);

                    updateChart(keyDataset);
                    Notiflix.Block.remove('#RecentTransactionGraph');
                },
                async: false
            });
        }

        const updatingChartDatasets = [
            [$totalTransaction[0]],
            [$totalTransaction[1]]
        ];
        HSCore.components.HSChartJS.init(document.querySelector('#updatingLineChart'), {
            data: {
                datasets: [
                    {
                        data: updatingChartDatasets[0][0]
                    },
                    {
                        data: updatingChartDatasets[0][1]
                    }
                ]
            }
        });

        const updatingLineChart = HSCore.components.HSChartJS.getItem('updatingLineChart');

        function updateChart(keyDataset) {
            updatingChartDatasets[keyDataset] = [$totalTransaction[keyDataset]];

            updatingLineChart.data.datasets.forEach(function (dataset, key) {
                dataset.data = updatingChartDatasets[keyDataset][key];
            });
            updatingLineChart.update();
        }

        document.querySelectorAll('[data-bs-toggle="chart"]').forEach(item => {
            item.addEventListener('click', e => {
                let keyDataset = e.currentTarget.getAttribute('data-datasets');
                monthlyTransaction(keyDataset);
            });
        });
        monthlyTransaction(0);

    </script>
@endpush














