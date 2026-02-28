@extends('admin.layouts.app')
@section('page_title', __('Transaction Summary'))
@section('content')
    <div class="content container-fluid dashboard-height mb-2">

        <div class="row">
            @include('admin.partials.transaction.txnGraph')
        </div>

        @include('admin.partials.transaction.txnList')

        <div class="row g-5 mt-2">
            <div class="col-xl-3 col-sm-6 col-lg-3 mb-5">
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

            <div class="col-xl-3 col-sm-6 col-lg-3 mb-5">
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

            <div class="col-xl-3 col-sm-6 col-lg-3 mb-5">
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

            <div class="col-xl-3 col-sm-6 col-lg-3 mb-5">
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
                url: "{{ route('admin.monthly.transaction',$code) }}",
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

@push('script')
    <script>
        $(document).on('ready', function () {

            HSCore.components.HSFlatpickr.init('.js-flatpickr')
            HSCore.components.HSTomSelect.init('.js-select', {
                maxOptions: 250,
            })
            HSCore.components.HSDatatables.init($('#datatable'), {
                processing: true,
                serverSide: true,
                ordering: false,
                ajax: {
                    url: "{{ route("admin.txnSummary.list",$code) }}",
                },

                columns: [
                    {data: 'no', name: 'no'},
                    {data: 'trx', name: 'trx'},
                    {data: 'user', name: 'user'},
                    {data: 'amount', name: 'amount'},
                    {data: 'charge', name: 'charge'},
                    {data: 'remarks', name: 'remarks'},
                    {data: 'date-time', name: 'date-time'},
                ],

                language: {
                    zeroRecords: `<div class="text-center p-4">
                    <img class="dataTables-image mb-3" src="{{ asset('assets/admin/img/oc-error.svg') }}" alt="Image Description" data-hs-theme-appearance="default">
                    <img class="dataTables-image mb-3" src="{{ asset('assets/admin/img/oc-error-light.svg') }}" alt="Image Description" data-hs-theme-appearance="dark">
                    <p class="mb-0">No data to show</p>
                    </div>`,
                    processing: `<div><div></div><div></div><div></div><div></div></div>`
                },
            });

            document.getElementById("filter_button").addEventListener("click", function () {
                let filterTransactionId = $('#transaction_id_filter_input').val();
                let filterDate = $('#filter_date_range').val();
                const datatable = HSCore.components.HSDatatables.getItem(0);
                datatable.ajax.url("{{ route('admin.transaction.search') }}" + "?filterTransactionID=" + filterTransactionId +
                    "&filterDate=" + filterDate).load();
            });
            $.fn.dataTable.ext.errMode = 'throw';
        });

    </script>
@endpush
















