@extends($theme . 'layouts.user')
@section('title', trans('Dashboard'))
@section('content')
    <div class="dashboard-wrapper">
        <div class="breadcrumb-area">
            <h3 class="title">@lang('Dashboard')</h3>
        </div>
        <div class="row" id="firebase-app">
            <div v-if="user_foreground == '1' || user_background == '1'">
                <div class="alert alert-warning alert-dismissible justify-content-between flex-column gap-3 flex-sm-row"
                     role="alert" v-if="notificationPermission == 'default' && !is_notification_skipped" v-cloak>
                    <div class="d-flex align-items-center">
                        <div class="icon-area"><i class="fa-light fa-triangle-exclamation"></i></div>
                        <div class="text-area">
                            <div class="description">
                                @lang('Do not miss any single important notification! Allow your browser to get instant push notification')
                            </div>
                        </div>
                    </div>
                    <button class="cmn-btn text-nowrap" id="allow-notification">@lang('Allow me')</button>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"
                            @click.prevent="skipNotification"><i class="fa-regular fa-xmark"></i></button>
                </div>
            </div>
            <div class="alert alert-warning alert-dismissible" role="alert"
                 v-if="notificationPermission == 'denied' && !is_notification_skipped" v-cloak>
                <div class="icon-area"><i class="fa-light fa-triangle-exclamation"></i></div>
                <div class="text-area">
                    <div class="description">
                        @lang('Please allow your browser to get instant push notification. Allow it from notification setting')
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"
                        @click.prevent="skipNotification"><i class="fa-regular fa-xmark"></i></button>
            </div>
        </div>

        <div class="row g-4 g-xxl-5 align-items-center">
            <div class="col-lg-6">
                <div class="row">
                    <div class="col-12">
                        <div class="mb-20 d-flex align-items-center justify-content-between gap-3">
                            <h4>@lang('My Wallets')</h4>
                            <div class="dropdown">
                                <button class="action-btn-secondary" type="button" data-bs-toggle="dropdown"
                                        aria-expanded="false">
                                    <i class="fa-regular fa-ellipsis"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="#" data-bs-target="#addWallet"
                                           data-bs-toggle="modal">@lang('Add New Wallet')</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    @if($wallets->isNotEmpty())

                        <div class="col-12">
                            <div class="owl-carousel owl-theme card-carousel">
                                @foreach($wallets as $item)
                                    <div class="item">
                                        <a href="{{ route('user.wallet.details', $item->uuid) }}" class="box-card">
                                            <div class="box-card-header">
                                                <div class="left-side">
                                                    <i class="fa-solid fa-wallet"></i>@lang('wallet')
                                                </div>
                                                <div class="right-side">
                                                    <i class="fa-regular fa-wifi fa-rotate-90"></i>
                                                </div>
                                            </div>
                                            <div class="box-card-body">
                                                <p class="sub-title">@lang('Balance')</p>
                                                <h2 class="title">{{ number_format($item->balance, 2) }}
                                                    <span class="sub">{{ $item->currency?->code }}</span>
                                                </h2>
                                            </div>
                                            <div class="box-card-footer">
                                                <p class="sub-title mt-20">
                                                    {{ $item->currency?->name }}
                                                </p>
                                                <img src="{{ $item->currency?->country?->getImage() }}" alt="">
                                            </div>
                                        </a>
                                    </div>

                                @endforeach

                            </div>
                        </div>
                    @else
                        <div class="col-12 text-center mb-5">
                            <img id="notFoundImage" src="" alt="@lang('You do not have any wallet')"
                                  class="text-center w-25">
                            <h5 class="mt-3">@lang('You do not have any wallet')</h5>
                        </div>
                    @endif
                </div>

            </div>

            <div class="col-lg-6">
                <div class="row">
                    <div class="col-12">
                        <div class="mb-20 d-flex align-items-center justify-content-between gap-3">
                            <h4>@lang('Statistics')</h4>
                        </div>
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-sm-6">
                        <div class="statistics-box">
                            <div class="statistics-box-header">
                                <p>@lang('Wallet Balance')</p>
                                <div>
                                    <h6 class="mb-0 up"><i class="fa-solid fa-arrow-trend-up"></i></h6>
                                    <small>@lang('Default Wallet')</small>
                                </div>
                            </div>

                            <div class="statistics-box-body">
                                <h5 class="title">{{ number_format(@$defaultWallet->balance,2)  }}
                                    <span class="sub">{{ @$defaultWallet->currency_code }}</span>
                                </h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="statistics-box">
                            <div class="statistics-box-header">
                                <p>@lang('Send Money')</p>
                                <div>
                                    <h6 class="mb-0 down"><i class="fa-solid fa-arrow-trend-up"></i></h6>
                                    <small>@lang('Last Month')</small>
                                </div>
                            </div>
                            <div class="statistics-box-body">
                                <h5 class="title">{{ number_format($lastMonthSendMoney,2) }}
                                    <span class="sub">{{ basicControl()->base_currency }}</span>
                                </h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="statistics-box">
                            <div class="statistics-box-header">
                                <p>@lang('Deposit')</p>
                                <div>
                                    <h6 class="mb-0 up"><i class="fa-solid fa-arrow-trend-up"></i></h6>
                                    <small>@lang('Current Month')</small>
                                </div>
                            </div>
                            <div class="statistics-box-body">
                                <h5 class="title">{{ number_format($currentMonthDeposit,2) }}
                                    <span class="sub">{{ basicControl()->base_currency }}</span>
                                </h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="statistics-box">
                            <div class="statistics-box-header">
                                <p>@lang('Transactions')</p>
                                <div>
                                    <h6 class="mb-0 up"><i class="fa-solid fa-arrow-trend-up"></i></h6>
                                    <small>@lang('Current Month')</small>
                                </div>
                            </div>
                            <div class="statistics-box-body">
                                <h5 class="title">{{ number_format($currentMonthTransactions,2) }}
                                    <span class="sub">{{ basicControl()->base_currency }}</span>
                                </h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div class="mt-30">
            <div class="row g-4 g-xxl-5">
                <div class="col-lg-6">
                    <div class="mb-20 d-flex align-items-center justify-content-between gap-3">
                        <h4>@lang('Transaction')</h4>
                    </div>
                    <div id="columnChart"></div>
                </div>
                <div class="col-lg-6">
                    <div class="mb-20 d-flex align-items-center justify-content-between gap-3">
                        <h4>@lang('Deposit')</h4>
                    </div>
                    <div id="lineChart"></div>
                </div>
            </div>
        </div>

        <div class="mt-30">
            <div class="d-flex justify-content-between align-items-center border-0 flex-wrap gap-3 mb-20">
                <h4 class="mb-0">@lang('Latest Transactions')</h4>
                <div class="gap-3 d-flex">
                    <a href="{{ route('user.allTransaction') }}" class="cmn-btn">
                        @lang('See all') <i class="fa-regular fa-arrow-right"></i>
                    </a>
                </div>
            </div>
            <div class="cmn-table">
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                        <tr>
                            <th scope="col">@lang('Type')</th>
                            <th scope="col">@lang('Transaction ID')</th>
                            <th scope="col">@lang('Amount')</th>
                            <th scope="col">@lang('Time & Date')</th>
                            <th scope="col">@lang('Remarks')</th>
                            <th scope="col">@lang('action')</th>
                        </tr>
                        </thead>
                        <tbody>

                        @forelse($transactions->take(6) as $item)
                            @php
                                $amount = currencyPositionCalc($item->amount, $item->curr);
                                $charge = currencyPosition($item->charge);
                            @endphp

                            <tr>
                                <td data-label="Type">
                                    <div class="type">
                                        <span class="icon {{ $item->trx_type == '-' ? 'icon-sent' : 'icon-received' }}">
                                            <i class="fa-regular {{ $item->trx_type == '-' ? 'fa-arrow-up' : 'fa-arrow-down' }}"></i>
                                        </span>
                                        <span>{{ $item->trx_type == '-' ? trans('Sent') : trans('Received') }}</span>
                                    </div>
                                </td>
                                <td data-label="Transaction ID"><span>#{{ $item->trx_id }}</span></td>
                                <td data-label="Amount">
                                    <span class="fw-bold">{{ $amount }}</span>
                                </td>

                                <td data-label="Time">
                                    <span>{{ dateTime($item->created_at) }}</span>
                                </td>
                                <td data-label="Remarks">
                                    <span>{{ $item->remarks }}</span>
                                </td>
                                <td data-label="Action">
                                    <a class="cmn-btn3 showInfo" href="#"
                                       data-trx_type="{{ $item->trx_type }}" data-remarks="{{ $item->remarks }}"
                                       data-amount="{{ $amount }}" data-charge="{{ $charge }}"
                                       data-trxid="{{ $item->trx_id }}"
                                       data-trx_date="{{ dateTime($item->created_at) }}"
                                       data-note="{{ $item->note ?? 'N/A' }}"
                                       data-bs-toggle="modal"
                                       data-bs-target="#infoViewModal"><i class="fa-regular fa-eye"></i>
                                        @lang('View Details')
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <td colspan="10" class="text-center">
                                <img class="text-center w-25 notFoundImage" id="notFoundImage2"
                                     src="{{ asset(config('filelocation.not_found_light')) }}"
                                     title="{{ trans('No Data Found') }}" alt="{{ trans('No Data Found') }}">
                                <p class="mt-2">@lang('No Data Found')</p>
                            </td>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
@endsection


@push('loadModal')
    <div class="modal fade" id="addWallet" tabindex="-1" aria-labelledby="share" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content ">
                <form method="POST" action="{{ route('user.wallet.store') }}">
                    @csrf
                    <div class="modal-header">
                        <h4 class="modal-title" id="share">@lang('Create New Wallet')</h4>
                        <button type="button" class="cmn-btn-close" data-bs-dismiss="modal" aria-label="Close">
                            <i class="fa-light fa-xmark"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="col-12 mb-2">
                            <div id="formModal">
                                <label class="form-label" for="currency">@lang('wallet currency')</label>
                                <select class="cmn-select2-image" name="currency_code" id="currency">
                                    @foreach ($currency as $item)
                                        <option data-img="{{ $item->country?->getImage() }}"
                                                data-country="{{ $item->country?->id }}" value="{{ $item->code }}"
                                            {{ request()->old('currency_code') == $item->id ? 'selected' : '' }}>
                                            {{ optional($item)->code }} - @lang($item->name)
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="cmn-btn3" data-bs-dismiss="modal">@lang('Close')</button>
                        <button type="submit" class="cmn-btn">@lang('Create')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="infoViewModal" tabindex="-1" role="dialog" aria-hidden="true"
         data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="text-center mb-5">
                        <h4 class="mt-3 mb-1">@lang('Transaction Details')</h4>
                    </div>

                    <div class="row mb-6">
                        <div class="transaction-list mt-2">
                            <div class="item">
                                <div class="left-side">
                                    <div class="icon">
                                        <i class="fa-regular"></i>
                                    </div>
                                    <span class="remarks"></span>
                                </div>
                                <div class="d-flex gap-2">
                                    <strong class="trxId"></strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="title mb-2 mt-4">@lang('Summary')</div>
                    <ul class="list-container mb-4 ">
                        <li class="item py-2">
                            <span>@lang('Amount')</span>
                            <span class=" fw-bold amount"></span>
                        </li>
                        <li class="item py-2">
                            <span>@lang('Charge')</span>
                            <span class=" fw-semibold text-danger charge"></span>
                        </li>
                        <li class="item py-2">
                            <span>@lang('Transaction Date')</span>
                            <span class=" fw-semibold trx_date"></span>
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
        $(document).ready(function () {
            $('.cmn-select2-image').select2({
                templateResult: formatState,
                templateSelection: formatState,
                dropdownParent: $("#formModal"),
            });
        });

        function formatState(state) {
            if (!state.id || !state.element || !state.element.getAttribute('data-img')) {
                return state.text;
            }
            const baseUrl = "{{ asset('assets/upload') }}";
            const imagePath = state.element.getAttribute('data-img');
            const $state = $(
                '<span><img src="' + imagePath + '" class="img-flag" /> ' + state.text + '</span>'
            );
            return $state;
        }
    </script>

    <script>
        $('.showInfo').click(function () {
            const {amount, charge, trxid, trx_type, trx_date, remarks, note} = this.dataset;

            $('.amount').html(amount);
            $('.trxId').html('#' + trxid);
            $('.charge').html(charge);
            $('.trx_date').html(trx_date);
            $('.note').html(note);

            const iconClass = trx_type === '-' ? 'icon-sent' : 'icon-received';
            const icon = trx_type === '-' ? 'fa-arrow-up' : 'fa-arrow-down';

            $('.transaction-list .icon').attr('class', `icon ${iconClass}`);
            $('.transaction-list .icon i').attr('class', `fa-regular ${icon}`);
            $('.transaction-list .left-side span').html(remarks);

            $('#infoViewModal').modal('show');
        });

    </script>
@endpush


@push('script')
    <script>
        $(document).ready(function () {

            const baseCurrSymbol = "{{ basicControl()->currency_symbol }}";
            const baseCurrency = "{{ basicControl()->base_currency }}";
            if ($('#columnChart').length) {
                let options = {
                    theme: {
                        mode: $('.dark-theme').length ? 'dark' : 'light',
                    },
                    series: [{
                        name: 'Total Send',
                        data: @json($totalSend)
                    }, {
                        name: 'Total Transactions',
                        data: @json($totalTrx)
                    }, {
                        name: 'Total Received',
                        data: @json($totalReceive)
                    }],
                    chart: {
                        type: 'bar',
                        height: 350,
                        background: $('.dark-theme').length ? '#232327' : ''
                    },
                    colors: ['#00afb9', '#99b21f', '#6b9080'],
                    plotOptions: {
                        bar: {
                            horizontal: false,
                            columnWidth: '55%',
                            endingShape: 'rounded'
                        },
                    },
                    dataLabels: {
                        enabled: false
                    },
                    stroke: {
                        show: true,
                        width: 2,
                        colors: ['transparent']
                    },
                    xaxis: {
                        categories: @json($months),
                    },
                    yaxis: {},
                    fill: {
                        opacity: 1,
                    },
                    tooltip: {
                        y: {
                            formatter: function (val) {
                                return baseCurrSymbol + val;
                            }
                        }
                    }
                };
                var chart = new ApexCharts(document.querySelector("#columnChart"), options);
                chart.render();
            }

            let monthlyDeposits = @json($monthlyDeposits);

            function calculateMaxValue(data) {
                const maxValue = Math.max(...Object.values(data));
                const increment = 10000;
                return Math.ceil(maxValue / increment) * increment;
            }

            const maxYValue = calculateMaxValue(monthlyDeposits) +10000;

            if ($('#lineChart').length) {
                let options = {
                    theme: {
                        mode: $('.dark-theme').length ? 'dark' : 'light'
                    },
                    series: [{
                        name: "Deposit",
                        data: Object.values(monthlyDeposits).map(value => parseFloat(value).toFixed(2))
                    }],
                    chart: {
                        height: 350,
                        type: 'line',

                        background: $('.dark-theme').length ? '#232327' : '',
                        dropShadow: {
                            enabled: true,
                            color: '#000',
                            top: 18,
                            left: 7,
                            blur: 10,
                            opacity: 0.2
                        },
                        zoom: {
                            enabled: false
                        },
                        toolbar: {
                            show: false
                        }
                    },
                    colors: ['#99b21f'],
                    dataLabels: {
                        enabled: true,
                        formatter: function (val) {
                            return baseCurrSymbol + val;
                        }
                    },
                    stroke: {
                        curve: 'smooth'
                    },
                    title: {
                        text: 'Monthly Deposit History for Current Year',
                        align: 'left'
                    },
                    grid: {
                        borderColor: '#e7e7e7',
                        row: {
                            colors: [$('.dark-theme').length ? '#232327' : '#f3f3f3', 'transparent'],
                            opacity: 0.5
                        },
                    },
                    markers: {
                        size: 1
                    },
                    xaxis: {
                        categories: Object.keys(monthlyDeposits),
                        title: {
                            text: 'Month'
                        }
                    },
                    yaxis: {
                        min: 0,
                        max: maxYValue
                    },
                    legend: {
                        position: 'top',
                        horizontalAlign: 'right',
                        floating: true,
                        offsetY: -25,
                        offsetX: -5
                    }
                };

                let chart = new ApexCharts(document.querySelector("#lineChart"), options);
                chart.render();
            }

        })
    </script>
@endpush


@if ($firebaseNotify)
    @push('script')
        <script type="module">
            import {
                initializeApp
            } from "https://www.gstatic.com/firebasejs/9.17.1/firebase-app.js";
            import {
                getMessaging,
                getToken,
                onMessage
            } from "https://www.gstatic.com/firebasejs/9.17.1/firebase-messaging.js";

            const firebaseConfig = {
                apiKey: "{{ $firebaseNotify['apiKey'] }}",
                authDomain: "{{ $firebaseNotify['authDomain'] }}",
                projectId: "{{ $firebaseNotify['projectId'] }}",
                storageBucket: "{{ $firebaseNotify['storageBucket'] }}",
                messagingSenderId: "{{ $firebaseNotify['messagingSenderId'] }}",
                appId: "{{ $firebaseNotify['appId'] }}",
                measurementId: "{{ $firebaseNotify['measurementId'] }}"
            };

            const app = initializeApp(firebaseConfig);
            const messaging = getMessaging(app);
            if ('serviceWorker' in navigator) {
                navigator.serviceWorker.register('{{ getProjectDirectory() }}' + `/firebase-messaging-sw.js`, {
                    scope: './'
                }).then(function (registration) {
                    requestPermissionAndGenerateToken(registration);
                }).catch(function (error) {
                });
            } else {
            }

            onMessage(messaging, (payload) => {
                if (payload.data.foreground || parseInt(payload.data.foreground) == 1) {
                    const title = payload.notification.title;
                    const options = {
                        body: payload.notification.body,
                        icon: payload.notification.icon,
                    };
                    new Notification(title, options);
                }
            });

            function requestPermissionAndGenerateToken(registration) {
                document.addEventListener("click", function (event) {
                    if (event.target.id == 'allow-notification') {
                        Notification.requestPermission().then((permission) => {
                            if (permission === 'granted') {
                                getToken(messaging, {
                                    serviceWorkerRegistration: registration,
                                    vapidKey: "{{ $firebaseNotify['vapidKey'] }}"
                                })
                                    .then((token) => {
                                        $.ajax({
                                            url: "{{ route('user.save.token') }}",
                                            method: "post",
                                            data: {
                                                token: token,
                                            },
                                            success: function (res) {
                                            }
                                        });
                                        window.newApp.notificationPermission = 'granted';
                                    });
                            } else {
                                window.newApp.notificationPermission = 'denied';
                            }
                        });
                    }
                });
            }
        </script>
        <script>
            window.newApp = new Vue({
                el: "#firebase-app",
                data: {
                    user_foreground: '',
                    user_background: '',
                    notificationPermission: Notification.permission,
                    is_notification_skipped: sessionStorage.getItem('is_notification_skipped') == '1'
                },
                mounted() {
                    sessionStorage.clear();
                    this.user_foreground = "{{ $firebaseNotify['user_foreground'] }}";
                    this.user_background = "{{ $firebaseNotify['user_background'] }}";
                },
                methods: {
                    skipNotification() {
                        sessionStorage.setItem('is_notification_skipped', '1')
                        this.is_notification_skipped = true;
                    }
                }
            });
        </script>
    @endpush
@endif

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
