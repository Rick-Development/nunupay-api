<form action="{{ route('admin.virtual.cardList',$type) }}" method="get">
    <div class="dropdown px-2">
        <button type="button" class="btn btn-secondary btn-md w-100" data-bs-auto-close="false"
                data-bs-toggle="dropdown" aria-expanded="false"><i class="bi-filter me-1"></i> @lang('Filter')
        </button>

        <div class="dropdown-menu dropdown-menu-sm-end dropdown-card card-dropdown-filter-centered filter_dropdown">
            <div class="card">
                <div class="card-header card-header-content-between">
                    <h5 class="card-header-title">@lang('Filter')</h5>
                    <button type="button" class="btn btn-ghost-secondary btn-icon btn-sm ms-2" id="filter_close_btn">
                        <i class="bi-x-lg"></i>
                    </button>
                </div>

                <div class="card-body">
                    <form action="" method="get" id="filter_form">
                        <div class="mb-4">
                            <span class="text-cap text-body">@lang('User')</span>
                            <div class="row">
                                <div class="col-12">
                                    <input type="text" name="user" class="form-control" id="user"
                                           value="{{ old('user', request()->user) }}" autocomplete="off"
                                           placeholder="demouser">
                                </div>
                            </div>
                        </div>
                        <div class="mb-4">
                            <span class="text-cap text-body">@lang('Card Number')</span>
                            <div class="row">
                                <div class="col-12">
                                    <input type="text" name="card_number" class="form-control" id="card_number"
                                           value="{{ old('card_number', request()->card_number) }}" autocomplete="off"
                                           placeholder="424252629900">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm mb-4">
                                <small class="text-cap text-body">@lang('Methods')</small>
                                <div class="tom-select-custom">
                                    <select
                                        class="js-select js-datatable-filter form-select form-select-sm"
                                        id="filter_status" name="method_id"
                                        data-target-column-index="4" data-hs-tom-select-options='{
                                                                  "placeholder": "All Methods",
                                                                  "searchInDropdown": false,
                                                                  "hideSearch": false,
                                                                  "dropdownWidth": "100"
                                                                }'>
                                        <option value="all"
                                            data-option-template='<span class="d-flex align-items-center">
                                            <span class="legend-indicator bg-secondary"></span>All Methods</span>'>
                                        </option>
                                        @foreach($virtualCardMethods as $key => $singleMethod)
                                            <option value="{{ $singleMethod->id }}"
                                                {{ @request()->method_id == $singleMethod->id ? 'selected' : '' }}
                                                data-option-template='<span class="d-flex align-items-center">
                                                    <span class="legend-indicator bg-success"></span>{{ __($singleMethod->name) }}</span>'>
                                            </option>
                                        @endforeach


                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm mb-4">
                                <small class="text-cap text-body">@lang('Status')</small>
                                <div class="tom-select-custom">
                                    <select
                                        class="js-select js-datatable-filter form-select form-select-sm"
                                        id="filter_status" name="status"
                                        data-target-column-index="4" data-hs-tom-select-options='{
                                                                  "placeholder": "Any status",
                                                                  "searchInDropdown": false,
                                                                  "hideSearch": false,
                                                                  "dropdownWidth": "100"
                                                                }'>
                                        <option value="all"
                                                data-option-template='<span class="d-flex align-items-center"><span class="legend-indicator bg-secondary"></span>All Status</span>'>
                                            @lang('All Status')
                                        </option>
                                        <option value="approved" {{ @request()->status == 'approved' ? 'selected' : '' }}
                                        data-option-template='<span class="d-flex align-items-center">
                                                                <span class="legend-indicator bg-success"></span>approved</span>'>
                                            @lang('approved')
                                        </option>
                                        <option value="rejected" {{ @request()->status == 'rejected' ? 'selected' : '' }}
                                        data-option-template='<span class="d-flex align-items-center"><span class="legend-indicator bg-danger"></span>rejected</span>'>
                                            @lang('rejected')
                                        </option>
                                        <option value="block-request" {{ @request()->status == 'block-request' ? 'selected' : '' }}
                                        data-option-template='<span class="d-flex align-items-center"><span class="legend-indicator bg-warning"></span>block-request</span>'>
                                            @lang('block-request')
                                        </option>
                                        <option value="fund-rejected" {{  @request()->status == 'fund-rejected' ? 'selected' : '' }}
                                        data-option-template='<span class="d-flex align-items-center"><span class="legend-indicator bg-danger"></span>fund-rejected</span>'>
                                            @lang('fund-rejected')
                                        </option>
                                        <option value="block" {{  @request()->status == 'block' ? 'selected' : '' }}
                                        data-option-template='<span class="d-flex align-items-center"><span class="legend-indicator bg-danger"></span>block</span>'>
                                            @lang('block')
                                        </option>
                                        <option value="add-fund-request" {{  @request()->status == 'add-fund-request' ? 'selected' : '' }}
                                        data-option-template='<span class="d-flex align-items-center"><span class="legend-indicator bg-info"></span>add-fund-request</span>'>
                                            @lang('add-fund-request')
                                        </option>
                                        <option value="inactive" {{  @request()->status == 'inactive' ? 'selected' : '' }}
                                        data-option-template='<span class="d-flex align-items-center"><span class="legend-indicator bg-warning"></span>inactive</span>'>
                                            @lang('inactive')
                                        </option>

                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-12 mb-4">
                                <span class="text-cap text-body">@lang('Expiry Date')</span>
                                <div class="input-group mb-3 custom">
                                    <input type="text" id="filter_date_range" name="expiry_date" value="{{ @request()->expiry_date }}"
                                           class="js-flatpickr form-control" placeholder="Select date"
                                           data-hs-flatpickr-options='{
                                                             "dateFormat": "Y-m-d",
                                                             "mode": "single"
                                                           }' aria-describedby="flatpickr_filter_date_range">
                                    <span class="input-group-text" id="flatpickr_filter_date_range">
                                        <i class="bi bi-arrow-counterclockwise"></i></span>
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
</form>
