@extends('admin.layouts.app')
@section('page_title',__('Card List'))

@section('content')
    <div class="content container-fluid">
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

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title m-0">@lang('Card List')</h4>
                @include('admin.virtual_card.card.searchForm')
            </div>
            <div class="table-responsive">
                <table class="table table-thead-bordered table-nowrap table-align-middle card-table">
                    <thead class="thead-light">
                    <tr>
                        <th>@lang('SL.')</th>
                        <th>@lang('User')</th>
                        <th>@lang('Provider')</th>
                        <th>@lang('Card Number')</th>
                        <th>@lang('Currency')</th>
                        <th>@lang('Balance')</th>
                        <th>@lang('Brand')</th>
                        <th>@lang('Expiry Date')</th>
                        <th>@lang('Status')</th>
                        <th scope="col">@lang('More')</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($cardLists as $key => $item)
                        <tr>
                            <td data-label="@lang('SL.')">{{ ++$key }} </td>
                            <td data-label="@lang('User')">
                                <a href="{{ route('admin.user.edit', $item->user_id)}}"
                                   class="text-decoration-none">
                                    <div class="d-lg-flex d-block align-items-center ">
                                        {!!  optional($item->user)->profilePicture() !!}
                                        <div class="mx-2 d-inline-flex d-lg-block align-items-center">
                                            <p class="text-dark mb-0 font-16 font-weight-medium">{{Str::limit(optional($item->user)->firstname ?? __('N/A'),20)}}</p>
                                            <span
                                                class="text-muted font-14 ml-1">{{ '@'.optional($item->user)->username ?? __('N/A')}}</span>
                                        </div>
                                    </div>
                                </a>
                            </td>
                            <td data-label="@lang('Provider')">{{ optional($item->cardMethod)->name }} </td>
                            <td data-label="@lang('Card Number')">{{ $item->card_number }} </td>
                            <td data-label="@lang('Currency')">{{ $item->currency }} </td>
                            <td data-label="@lang('Balance')">{{ $item->balance }} {{ $item->currency }}</td>
                            <td data-label="@lang('Brand')">{{ $item->brand }} </td>
                            <td data-label="@lang('Expiry Date')">{{ $item->expiry_date }} </td>
                            <td data-label="@lang('Status')">
                                {!! $item->getStatusInfo() !!}
                            </td>

                            <td data-label="@lang('More')">
                                <a class="btn btn-white btn-sm" href="{{ route('admin.virtual.cardView', $item->id) }}"
                                   title="View"><i class="bi-eye-fill me-1"></i>
                                </a>
                                <a class="btn btn-white btn-sm"
                                   href="{{ route('admin.virtual.cardTransaction', $item->id) }}"
                                   title="Transaction"><i class="bi-bar-chart-line me-1"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        {!! renderNoData() !!}
                    @endforelse
                    </tbody>
                </table>
                <div class="card-footer">
                    {{ $cardLists->appends($_GET)->links($theme.'partials.pagination') }}
                </div>
            </div>
        </div>
    </div>
@endsection

@push('css-lib')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/tom-select.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/flatpickr.min.css') }}">

@endpush


@push('js-lib')
    <script src="{{ asset('assets/admin/js/flatpickr.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/tom-select.complete.min.js') }}"></script>

@endpush

@push('script')
    <script>
        $(document).on('ready', function () {
            HSCore.components.HSTomSelect.init('.js-select');
            HSCore.components.HSFlatpickr.init('.js-flatpickr')
        })
    </script>
@endpush
