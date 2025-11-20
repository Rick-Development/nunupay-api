@extends('admin.layouts.app')
@section('page_title', __('Manage Virtual Card'))
@section('content')
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title">@yield('page_title')</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb breadcrumb-no-gutter">
                            <li class="breadcrumb-item"><a class="breadcrumb-link"
                                                           href="{{ route('admin.dashboard') }}">@lang("Dashboard")</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">@lang('Virtual Card')</li>
                            <li class="breadcrumb-item active" aria-current="page">@lang('Available Method')</li>

                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title m-0">@lang('Virtual Cards')</h4>

            </div>

            <div class="table-responsive">
                <table class="table table-thead-bordered table-nowrap table-align-middle card-table">
                    <thead class="thead-light">
                    <tr>
                        <th>@lang('No.')</th>
                        <th>@lang('Name')</th>
                        <th>@lang('Status')</th>
                        <th>@lang('Action')</th>
                    </tr>
                    </thead>
                    <tbody id="sortable">
                    @if(count($virtualCardMethods) > 0)
                        @foreach($virtualCardMethods as $method)
                            <tr data-code="{{ $method->code }}">
                                <td data-label="@lang('Serial No.')">@serial</td>
                                <td data-label="@lang('Name')">{{ $method->name }} </td>
                                <td data-label="@lang('Status')">
                                    {!! renderStatusBadge($method->status) !!}
                                </td>
                                <td data-label="@lang('Action')">

                                    <a class="btn btn-white btn-sm" href="{{ route('admin.virtual.cardEdit', $method->id) }}">
                                        <i class="bi-pencil-fill me-1"></i> Edit
                                    </a>
                                    @if($method->status == 0)
                                        <a href="javascript:void(0)"
                                           class="btn btn-white text-success btn-sm changeBtn"
                                           data-bs-target="#statusChange"
                                           data-bs-toggle="modal"
                                           data-bs-toggle="tooltip"
                                           data-bs-placement="top"
                                           data-route="{{ route('admin.virtual.cardStatusCng', $method->id) }}"
                                           data-bs-original-title="@lang('Active this method')">
                                            <i class="fas fa-check-circle"></i> @lang('Active')
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    @else
                        {!! renderNoData() !!}
                    @endif
                    </tbody>
                </table>
                <div class="card-footer">

                </div>
            </div>
        </div>
    </div>

@endsection

@push('loadModal')

    <div class="modal fade" id="statusChange" tabindex="-1" role="dialog" aria-labelledby="statusModalLabel"
         data-bs-backdrop="static" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="statusModalLabel"><i class="bi bi-check2-square"></i> @lang("Confirmation")</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="" method="post" class="setRoute">
                    @csrf
                    <div class="modal-body">
                        <p>@lang("Do you want to change the status?")</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-white" data-bs-dismiss="modal">@lang('Close')</button>
                        <button type="submit" class="btn btn-primary">@lang('Confirm')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endpush

@push('css-lib')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/flatpickr.min.css') }}">
@endpush


@push('js-lib')
    <script src="{{ asset('assets/admin/js/flatpickr.min.js') }}"></script>
@endpush



@push('script')
    <script>
        'use strict'
        $(document).on('click', '.changeBtn', function () {
            let route = $(this).data('route');
            $('.setRoute').attr('action', route);
        })
    </script>
@endpush
