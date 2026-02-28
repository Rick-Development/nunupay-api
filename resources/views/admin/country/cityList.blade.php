@extends('admin.layouts.app')
@section('page_title',__('City Management'))
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
                            <li class="breadcrumb-item active" aria-current="page">@lang('Countries')</li>
                            <li class="breadcrumb-item active" aria-current="page">@lang($country->name)</li>
                            <li class="breadcrumb-item active" aria-current="page">@lang($state->name)</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <!-- Card -->
        <div class="card">
            <div class="card-header card-header-content-md-between">
                <div class="mb-2 mb-md-0">
                    <div class="input-group input-group-merge input-group-flush">
                        <div class="input-group-prepend input-group-text"><i class="bi-search"></i></div>
                        <input id="datatableSearch" type="search" class="form-control"
                               placeholder="{{trans('Search City')}}"
                               aria-label="Search Country" autocomplete="off">
                    </div>
                </div>

                <div class="d-grid d-sm-flex justify-content-md-end align-items-sm-center gap-2">
                    <div id="datatableCounterInfo">
                        <div class="d-flex flex-wrap  align-items-center">
                        <span class="fs-5 me-3">
                          <span id="datatableCounter">0</span> @lang('Selected')
                        </span>
                            <div class="">
                                <button type="button" class="btn btn-outline-danger btn-sm w-100" id="showHideDropdown"
                                        data-bs-toggle="dropdown" aria-expanded="false" data-bs-auto-close="outside">
                                    <i class="bi-check2-square me-1"></i> @lang('Actions')
                                </button>
                                <div class="dropdown-menu dropdown-menu-end mt-1" aria-labelledby="showHideDropdown"
                                     style="">

                                    <a class="dropdown-item me-1" href="javascript:void(0)"
                                       data-bs-toggle="modal" data-bs-target="#all_active" id="allActiveBtn">
                                        <i class="bi-toggle-on"></i> @lang('Activate')
                                    </a>
                                    <a class="dropdown-item me-1" href="javascript:void(0)"
                                       data-bs-toggle="modal" data-bs-target="#all_inactive" id="deactivateBtn">
                                        <i class="bi-toggle-off"></i> @lang('Deactivate')
                                    </a>
                                    <a class="dropdown-item me-1" href="javascript:void(0)"
                                       data-bs-toggle="modal" data-bs-target="#deleteMultiple" id="deleteBtn">
                                        <i class="bi-trash"></i> @lang('Delete')
                                    </a>

                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex flex-wrap ">

                        <a href="{{ route('admin.city.create', ['country' => $country, 'state' => $state]) }}" class="btn btn-sm btn-primary me-2">
                            @lang('Add New')
                        </a>

                        <a href="{{ route('admin.countryState',['country'=>$country]) }}" class="btn btn-sm btn-info me-2">
                            <i class="bi-arrow-left"></i> @lang('Back')
                        </a>
                    </div>

                </div>
            </div>


            <div class=" table-responsive datatable-custom  ">
                <table id="datatable"
                       class="js-datatable table table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
                       data-hs-datatables-options='{
               "columnDefs": [{
                  "targets": [0, 3],
                  "orderable": false
                }],
               "order": [],
               "info": {
                 "totalQty": "#datatableWithPaginationInfoTotalQty"
               },
               "search": "#datatableSearch",
               "entries": "#datatableEntries",
               "pageLength": 15,
               "isResponsive": false,
               "isShowPaging": false,
               "pagination": "datatablePagination"
             }'>
                    <thead class="thead-light">
                    <tr>
                        <th class="table-column-pe-0">
                            <div class="form-check">
                                <input class="form-check-input check-all tic-check" type="checkbox" name="check-all"
                                       id="datatableCheckAll">
                                <label class="form-check-label" for="datatableCheckAll"></label>
                            </div>
                        </th>

                        <th class="table-column-ps-0">@lang('City Name')</th>

                        <th>@lang('Status')</th>
                        <th>@lang('Action')</th>
                    </tr>
                    </thead>

                    <tbody id="loadingData">

                    </tbody>

                </table>
            </div>


            <div class="card-footer">
                <div class="row justify-content-center justify-content-sm-between align-items-sm-center">
                    <div class="col-sm mb-2 mb-sm-0">
                        <div class="d-flex justify-content-center justify-content-sm-start align-items-center">
                            <span class="me-2">@lang('Showing:')</span>
                            <div class="tom-select-custom">
                                <select id="datatableEntries"
                                        class="js-select form-select form-select-borderless w-auto" autocomplete="off"
                                        data-hs-tom-select-options='{
                                    "searchInDropdown": false,
                                    "hideSearch": true
                                  }'>
                                    <option value="10">10</option>
                                    <option value="15" selected>15</option>
                                    <option value="20">20</option>
                                    <option value="30">30</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                            </div>
                            <span class="text-secondary me-2">of</span>
                            <span id="datatableWithPaginationInfoTotalQty"></span>
                        </div>
                    </div>
                    <div class="col-sm-auto">
                        <div class="d-flex  justify-content-center justify-content-sm-end">
                            <nav id="datatablePagination" aria-label="Activity pagination"></nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('loadModal')

        <div class="modal fade" id="all_active" role="dialog" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3 class="modal-title" id=""><i class="fa-light fa-square-check"></i> @lang('Confirmation')</h3>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>@lang("Are you really want to active the cities?")</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-white" data-bs-dismiss="modal">@lang('Close')</button>
                        <form action="" method="post">
                            @csrf
                            <a href="" class="btn btn-primary active-yes"><span>@lang('Yes')</span></a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="all_inactive" role="dialog" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3 class="modal-title" id=""><i class="fa-light fa-square-check"></i> @lang('Confirmation')</h3>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>@lang("Are you really want to Inactive the cities?")</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-white" data-bs-dismiss="modal">@lang('Close')</button>
                        <form action="" method="post">
                            @csrf
                            <a href="" class="btn btn-primary inactive-yes"><span>@lang('Yes')</span></a>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Modal -->
        <div class="modal fade" id="deleteMultiple" tabindex="-1" role="dialog" aria-labelledby="deleteMultipleLabel"
             data-bs-backdrop="static"
             aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3 class="modal-title" id="deleteMultipleLabel"><i class="fa-light fa-square-check"></i> @lang('Confirmation')</h3>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="" method="post">
                        @csrf
                        <div class="modal-body">
                            @lang('"Do you want to delete all selected city?')
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-white" data-bs-dismiss="modal">@lang('Close')</button>
                            <button type="submit" class="btn btn-primary delete-multiple">@lang('Confirm')</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    @endpush

@endsection


@push('css-lib')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/tom-select.bootstrap5.css') }}">
@endpush


@push('js-lib')
    <script src="{{ asset('assets/admin/js/hs-file-attach.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/tom-select.complete.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/select.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/appear.min.js') }}"></script>
    <script src="{{ asset("assets/admin/js/hs-counter.min.js") }}"></script>
@endpush


@push('script')

    <script>

        $('#allActiveBtn, #deactivateBtn, #deleteBtn').on('click', function () {
            $('#showHideDropdown').dropdown('hide');
        });

        $(document).on('ready', function () {
            new HSCounter('.js-counter')
            new HSFileAttach('.js-file-attach')
            HSCore.components.HSTomSelect.init('.js-select', {
                maxOptions: 250,
            })

            HSCore.components.HSDatatables.init($('#datatable'), {
                processing: true,
                serverSide: true,
                ordering: false,

                ajax: {
                    url: "{{ route("admin.city.list",['country' => $country->id, 'state' => $state->id]) }}",
                },
                columns: [
                    {data: 'checkbox', name: 'checkbox'},
                    {data: 'city_name', name: 'city_name'},
                    {data: 'status', name: 'status'},
                    {data: 'action', name: 'action'},
                ],
                select: {
                    style: 'multi',
                    selector: 'td:first-child input[type="checkbox"]',
                    classMap: {
                        checkAll: '#datatableCheckAll',
                        counter: '#datatableCounter',
                        counterInfo: '#datatableCounterInfo'
                    }
                },
                language: {
                    zeroRecords: `<div class="text-center p-4">
                    <img class="dataTables-image mb-3" src="{{ asset('assets/admin/img/oc-error.svg') }}" alt="Image Description" data-hs-theme-appearance="default">
                    <img class="dataTables-image mb-3" src="{{ asset('assets/admin/img/oc-error-light.svg') }}" alt="Image Description" data-hs-theme-appearance="dark">
                    <p class="mb-0">No data to show</p>
                    </div>`,
                    processing: `<div><div></div><div></div><div></div><div></div></div>`
                },
            })

            $.fn.dataTable.ext.errMode = 'throw';

            $(document).on('click', '#datatableCheckAll', function () {
                $('input:checkbox').not(this).prop('checked', this.checked);
            });

            $(document).on('change', ".row-tic", function () {
                let length = $(".row-tic").length;
                let checkedLength = $(".row-tic:checked").length;
                if (length == checkedLength) {
                    $('#check-all').prop('checked', true);
                } else {
                    $('#check-all').prop('checked', false);
                }
            });


            $(document).on('click', '.delete-multiple', function (e) {
                e.preventDefault();
                let strIds = $(".row-tic:checked").map((_, checkbox) => $(checkbox).data('id')).get();
                $('#deleteMultiple').modal('hide');
                showLoadingBlock('Deleting Your Selected Data...', 'arrows');

                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: "{{ route('admin.city.delete.multiple') }}",
                    data: {strIds: strIds},
                    dataType: 'json',
                    type: "post",
                    success: function (data) {
                        location.reload();
                    },
                });
            });

            function showLoadingBlock(message, iconType) {
                let icon = (iconType === 'hourglass') ? 'hourglass' : 'arrows';
                Notiflix.Block[icon]('#loadingData', message, {
                    backgroundColor: 'rgba(0,0,0,0.8)',
                    svgColor: '#32c682',
                    messageColor: '#fff',
                    clickable: true,
                    borderRadius: '5px',
                    messageFontSize: '18px',
                    svgSize: '70px',
                    zIndex: 5000,
                });
            }

            function hideLoadingBlock() {
                Notiflix.Block.remove('#loadingData');
            }

        });

        //multiple active
        $(document).on('click', '.active-yes', function (e) {
            e.preventDefault();
            var allVals = [];
            $(".row-tic:checked").each(function () {
                allVals.push($(this).attr('data-id'));
            });

            var strIds = allVals;

            $.ajax({
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf_token"]').attr('content')},
                url: "{{ route('admin.city.active.multiple') }}",
                data: {strIds: strIds},
                datatType: 'json',
                type: "post",
                success: function (data) {
                    location.reload();

                },
            });
        });

        //multiple deactive
        $(document).on('click', '.inactive-yes', function (e) {
            e.preventDefault();
            var allVals = [];
            $(".row-tic:checked").each(function () {
                allVals.push($(this).attr('data-id'));
            });

            var strIds = allVals;
            $.ajax({
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf_token"]').attr('content')},
                url: "{{ route('admin.city.inactive.multiple') }}",
                data: {strIds: strIds},
                datatType: 'json',
                type: "post",
                success: function (data) {
                    location.reload();

                }
            });
        });

    </script>

@endpush






