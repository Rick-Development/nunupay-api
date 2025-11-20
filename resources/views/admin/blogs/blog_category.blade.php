@extends('admin.layouts.app')
@section('page_title',__('Blog Category'))
@section('content')
<div class="content container-fluid">
    <div class="page-header">
        <div class="row align-items-end">
            <div class="col-sm mb-2 mb-sm-0">
                <h1 class="page-header-title">@yield('page_title')</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-no-gutter">
                        <li class="breadcrumb-item">
                            <a class="breadcrumb-link" href="{{ route('admin.dashboard') }}">@lang('Dashboard')</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">@lang('Manage Blog')</li>
                        <li class="breadcrumb-item active" aria-current="page">@lang('Category')</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header card-header-content-md-between">
            <div class="mb-2 mb-md-0">
                <h4 class="card-title m-0">@lang('Category List')</h4>
            </div>

            <div class="d-grid d-sm-flex justify-content-md-end align-items-sm-center gap-2">

                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add">
                    @lang('Add New')
                </button>

                <div class="dropdown px-2">
                    <button type="button" class="btn btn-secondary btn-md w-100"
                            id="dropdownMenuClickable" data-bs-auto-close="false"
                            id="usersFilterDropdown"
                            data-bs-toggle="dropdown"
                            aria-expanded="false">
                        <i class="bi-filter me-1"></i> @lang('Filter')
                    </button>

                    <div class="dropdown-menu dropdown-menu-sm-end dropdown-card card-dropdown-filter-centered filter_dropdown"
                        aria-labelledby="dropdownMenuClickable">
                        <div class="card">
                            <div class="card-header card-header-content-between">
                                <h5 class="card-header-title">@lang('Filter')</h5>
                                <button type="button" class="btn btn-ghost-secondary btn-icon btn-sm ms-2" id="filter_close_btn">
                                    <i class="bi-x-lg"></i>
                                </button>
                            </div>

                            <div class="card-body">
                                <form action="" id="filter_form" method="get">
                                    <div class="mb-4">
                                        <span class="text-cap text-body">@lang('Search by name')</span>
                                        <div class="row">
                                            <div class="col-12">
                                                <input type="text" name="name" class="form-control" id="name"
                                                       value="{{ old('name', request()->name) }}"autocomplete="off">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-sm-12 mb-4">
                                            <span class="text-cap text-body">@lang('Date Range')</span>
                                            <div class="input-group mb-3 custom">
                                                <input type="text" id="filter_date_range"
                                                       name="date"
                                                       class="js-flatpickr form-control"
                                                       placeholder="Select dates"
                                                       data-hs-flatpickr-options='{
                                                             "dateFormat": "Y/m/d",
                                                             "mode": "range"
                                                           }' aria-describedby="flatpickr_filter_date_range">
                                                <span class="input-group-text" id="flatpickr_filter_date_range">
                                                    <i class="bi bi-arrow-counterclockwise"></i>
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row gx-2">
                                        <div class="col">
                                            <div class="d-grid">
                                                <button type="button" id="clear_filter" class="btn btn-white">@lang('Clear Filters')</button>
                                            </div>
                                        </div>
                                        <div class="col">
                                            <div class="d-grid">
                                                <button type="submit" class="btn btn-primary" id="filter_button">
                                                    <i class="bi-search"></i> @lang('Apply')</button>
                                            </div>
                                        </div>
                                    </div>

                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table -->
        <table class="table table-borderless table-thead-bordered">
            <thead class="thead-light">
            <tr>
                <th scope="col">@lang('Serial No.')</th>
                <th scope="col">@lang('Category Name')</th>
                <th scope="col">@lang('Status')</th>
                <th scope="col">@lang('Action')</th>
            </tr>
            </thead>
            <tbody>
            @forelse($cat as $item)
                <tr>
                    <td>@serial</td>
                    <td>@lang($item->name)</td>
                    <td>
                        {!! renderStatusBadge($item->status) !!}
                    </td>
                    <td>
                        <div class="btn-group" role="group">
                            <a class="btn btn-white btn-sm edit-button" type="button" title="@lang('Edit')"
                                    data-bs-toggle="modal" data-bs-target="#editModal"
                                    data-name="{{$item->name}}"
                                    data-status="{{$item->status}}"
                                    data-route="{{ route('admin.blogCatUpdate', $item->id) }}">
                                <span><i class="bi-pencil fill me-1"></i> </span> @lang('Edit')
                            </a>
                            <div class="btn-group">
                                <button type="button" class="btn btn-white btn-icon btn-sm dropdown-toggle dropdown-toggle-empty" id="pageEditDropdown" data-bs-toggle="dropdown" aria-expanded="false"></button>
                                <div class="dropdown-menu dropdown-menu-end mt-1" aria-labelledby="pageEditDropdown" style="">
                                    <a class="dropdown-item btn notiflix-confirm" title="@lang('Delete')"
                                       data-bs-toggle="modal" data-bs-target="#deleteModal"
                                       data-route="{{route('admin.blogCatDelete',$item->id)}}"
                                    ><i class="bi-trash dropdown-item-icon"></i> @lang('Delete')
                                    </a>
                                </div>
                            </div>
                        </div>

                    </td>
                </tr>
                @empty
                    {!! renderNoData() !!}
                @endforelse
            </tbody>
        </table>
        <!-- End Table -->
        <div class="card-footer">
            <div class="row justify-content-center justify-content-sm-between align-items-sm-center">
                <div class="col-sm-auto">
                    <div class="d-flex  justify-content-center justify-content-sm-end">
                        <nav id="datatablePagination" aria-label="Activity pagination"></nav>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('loadModal')

    <!-- Add Modal -->
    <div id="add" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="add" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="Add Blog Category">@lang('Add Blog Category')</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{route('admin.blogCatStore')}}" method="post">
                    @csrf
                    <div class="modal-body">
                        <div class="modal-body">
                            <div class="form-group">
                                <label class="form-label">@lang('Category Name')</label>
                                <input type="text" name="name" value="{{old('name')}}" placeholder="Personal Finance"
                                       class="form-control add-name @error('name') is-invalid @enderror">
                                @error('name')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                    <button type="button" class="btn btn-white" data-bs-dismiss="modal">@lang('Close')</button>
                    <button type="submit" class="btn btn-primary">@lang('Save')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- End Modal -->

    <!-- Edit Modal -->
    <div id="editModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="edit" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="Edit-Blog-Category">@lang('Edit Blog Category')</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="" method="post" id="editForm">
                    @csrf
                    @method('put')
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="form-label">@lang('Name')</label>
                            <input type="text" name="name" value="{{ old('name') }}" class="edit-name form-control @error('name') is-invalid @enderror">
                            @error('name')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                            <label class="row form-check form-switch mt-3 mx-2" for="status">
                                @lang('Status')
                                <span class="col-4 col-sm-3 text-end">
                                    <input type='hidden' value='0' name='status'>
                                    <input class="form-check-input @error('status') is-invalid @enderror"
                                        type="checkbox" name="status" value="1" id="status">
                                </span>
                                @error('status')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                            </label>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-white" data-bs-dismiss="modal">@lang('Close')</button>
                        <button type="submit" class="btn btn-primary">@lang('Save changes')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- End Edit Modal -->

    <!-- Delete Modal -->
    <div id="deleteModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="deleteModal" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="delete-modal">@lang('Delete Blog Category')</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>@lang('Are you sure to delete this?')</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-white" data-bs-dismiss="modal">@lang('Close')</button>
                    <form action="" method="post" class="deleteRoute">
                        @csrf
                        @method('delete')
                        <button type="submit" class="btn btn-soft-danger">@lang('Yes')</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- End Modal -->



@endpush


@push('css-lib')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/flatpickr.min.css') }}">
@endpush


@push('js-lib')
    <script src="{{ asset('assets/admin/js/flatpickr.min.js') }}"></script>
@endpush


@push('script')


    <script>
        $(document).ready(function () {
            $(document).on('click', '.edit-button', function () {
                $('#editForm').attr('action', $(this).data('route'));
                $('.edit-name').val($(this).data('name'));
                var statusValue = $(this).data('status');
                $('#status').prop('checked', statusValue == 1);
            });

            $('.notiflix-confirm').on('click', function () {
                var route = $(this).data('route');
                $('.deleteRoute').attr('action', route)
            })

        });

    </script>

    <script>
        $(document).on('ready', function () {
            HSCore.components.HSFlatpickr.init('.js-flatpickr')
        });
    </script>

    @if ($errors->any())
        <script>
            "use strict";
            @foreach ($errors->unique() as $error)
            Notiflix.Notify.failure("{{ trans($error) }}");
            @endforeach
        </script>
    @endif
@endpush




