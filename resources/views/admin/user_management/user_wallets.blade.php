@extends('admin.layouts.app')
@section('page_title',__('View Profile'))
@section('content')
    <div class="content container-fluid">
        <div class="row justify-content-lg-center">
            <div class="col-lg-10">
                @include('admin.user_management.components.header_user_profile')
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4 class="card-title m-0">@lang('Wallet List')</h4>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-thead-bordered table-nowrap table-align-middle card-table">
                                    <thead class="thead-light">
                                    <tr>
                                        <th >@lang('SL.')</th>
                                        <th >@lang('Name')</th>
                                        <th >@lang('Balance')</th>
                                        <th >@lang('Currency')</th>
                                        <th >@lang('Status')</th>
                                        <th >@lang('Created At')</th>
                                        <th >@lang('More')</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse($wallets as $key => $item)
                                        <tr>
                                            <td data-label="@lang('SL.')">{{ ++$key }} </td>
                                            <td data-label="@lang('User')">
                                                {{ $item->currency->name }}
                                            </td>
                                            <td data-label="@lang('Balance')">{{ getAmount($item->balance,2) }}</td>
                                            <td data-label="@lang('Currency')">{{ $item->currency_code }} </td>
                                            <td data-label="@lang('Status')">
                                                {!! renderStatusBadge($item->status) !!}
                                            </td>

                                            <td data-label="@lang('Created At')">{{dateTime($item->created_at)}}</td>
                                            <td data-label="@lang('More')">
                                                <div class="dropdown nav-scroller-dropdown">
                                                    <button type="button" class="btn btn-white btn-icon btn-sm" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                                        <i class="bi-three-dots-vertical"></i>
                                                    </button>
                                                    <div class="dropdown-menu dropdown-menu-end mt-1" aria-labelledby="profileDropdown" style="">
                                                        <a class="dropdown-item changeStatus" data-bs-target="#walletStatus" href="javascript:void(0)"
                                                           data-route="{{ route('admin.user.changeWalletStatus',$item->id) }}" data-bs-toggle="modal">
                                                            @if($item->status == 1)
                                                                <i class="bi-toggle-off dropdown-item-icon"></i>@lang('Deactivate')
                                                                @else
                                                                <i class="bi-toggle-on dropdown-item-icon"></i>@lang('Activate')
                                                            @endif
                                                        </a>
                                                        <a class="dropdown-item" href="{{ route('admin.user.walletTransactions',$item->uuid) }}">
                                                            <i class="bi-arrow-counterclockwise dropdown-item-icon"></i>@lang('Transactions')
                                                        </a>
                                                        <a class="dropdown-item addBalance" href="javascript:void(0)"
                                                           data-route="{{ route('admin.user.update.balance', $item->uuid) }}"
                                                           data-balance="{{ currencyPositionCalc($item->balance,$item->currency) }}"
                                                           data-bs-toggle="modal" data-bs-target="#addBalanceModal">
                                                            <i class="bi bi-cash-coin dropdown-item-icon"></i>
                                                            @lang('Manage Balance')
                                                        </a>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        {!! renderNoData() !!}
                                    @endforelse
                                    </tbody>
                                </table>
                                <div class="card-footer">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('admin.user_management.components.update_balance_modal')
    @include('admin.user_management.components.update_wallet_status')

@endsection

@push('css-lib')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/tom-select.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/flatpickr.min.css') }}">
@endpush


@push('js-lib')
    <script src="{{ asset('assets/admin/js/tom-select.complete.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/flatpickr.min.js') }}"></script>
@endpush

@push('script')
    <script>
        $(document).ready(function () {
            $('.changeStatus').on('click', function () {
                let route = $(this).data('route');
                $('.setRoute').attr('action', route);
            });

            $(document).on('click', '.addBalance', function () {
                $('.setBalanceRoute').attr('action', $(this).data('route'));
                $('.user-balance').text($(this).data('balance'));
            });

            @if ($errors->any())
                @foreach ($errors->unique() as $error)
                Notiflix.Notify.failure("{{ trans($error) }}");
                @endforeach
                setTimeout(function () {
                    window.location.reload();
                }, 500);
            @endif


        });
    </script>
@endpush


