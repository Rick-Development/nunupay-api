@extends($theme.'layouts.user')
@section('title', trans('Add Recipient Details'))

@section('content')
    <div class="dashboard-wrapper">
        <a href="{{ route('user.recipient.index') }}" class="back-btn">
            <i class="fa-regular fa-angle-left"></i>@lang('Back To Recipient List')</a>

        <div class="col-xxl-5 col-xl-10 mx-auto">
            <div class="transfer-section">
                <div class="card">
                    <form action="{{ route('user.recipient.store') }}" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="card-body">
                            <h4 class="mb-20 text-center">{{ trans('Add New Recipient') }}</h4>

                            <div class="col-12 mb-2">
                                <label class="form-label" for="currency">@lang('recipient currency')</label>
                                <select class="cmn-select2-image" name="currency_id" id="currency">
                                    @foreach($currency as $item)
                                        <option data-img="{{ $item->country?->getImage() }}"
                                                data-country="{{$item->country->id}}"
                                                value="{{ $item->id }}"
                                            {{ $item->id == old('currency_id', ($countryId ? $countryId : $currency->first()->id)) ? 'selected' : '' }}
                                        >{{ optional($item)->code }} - @lang( $item->name)
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 mb-20">
                                <label for="name" class="form-label">{{ trans('Recipient name') }}</label>
                                <input name="name" type="text"
                                       class="form-control @error('name') is-invalid @enderror" id="name"
                                       value="{{ old('name') }}" placeholder="Jhon Doe" required>
                                @error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            </div>
                            <div class="col-12 mb-20">
                                <label for="email" class="form-label">{{ trans('Recipient email') }}</label>
                                <input name="email" type="email" id="email" value="{{ old('email') }}"
                                       class="form-control @error('email') is-invalid @enderror"
                                       placeholder="example@mail.com" required>
                                @error('email')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            </div>
                            <input type="hidden" name="type" value="{{ $type ?? 1 }}">

                            <h6 class="mb-10 mt-3 text-success text-center">{{ trans('choose a service') }}</h6>

                            <div class="tab-section" id="loadingData">
                                <ul class="nav nav-pills mb-3 tab-inline-flex" id="pills-tab" role="tablist">
                                    <!-- tabs added here -->
                                </ul>
                                <div class="tab-content" id="pills-tabContent">
                                    <!-- tabs content here -->
                                </div>
                                @if ($errors->any())
                                    <div>
                                        <ul>
                                            @foreach ($errors->all() as $error)
                                                @unless(in_array($error, ['The email field is required.', 'The name field is required.']))
                                                    <li class="text-danger">{{ $error }}</li>
                                                @endunless
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                                <input type="hidden" id="service_id" name="service_id" value="{{ old('service_id') }}">

                                <button type="submit" class="cmn-btn mt-20 w-100">{{ trans('continue') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>

        $(document).ready(function () {

            $('#currency').on('change', _.debounce(fetchServicesAndGenerateTabs, 150)).trigger('change');
            // $('#currency').on('change', fetchServicesAndGenerateTabs).trigger('change');
            $('#pills-tab').on('click', '.change_service', handleServiceChange);
            $('#pills-tabContent').on('change', '.change_bank', handleBankChange);
        });

        function fetchServicesAndGenerateTabs() {
            const countryId = $('#currency :selected').data('country');

            axios.get('{{ route('user.getServices') }}', { params: { country_id: countryId } })
                .then(response => {
                    const services = response.data.services;
                    Object.keys(services).length > 0 ? renderTabs(services) : renderNoServiceAvailable();
                })
                .catch(error => console.error('Error fetching services:', error));
        }

        function renderTabs(services) {
            $('#pills-tab').html('');
            $('#pills-tabContent').html('');

            const serviceId = $('#service_id').val() || Object.keys(services)[0];

            Object.entries(services).forEach(([id, serviceName], index) => {
                const isActive = id === serviceId ? 'show active' : '';
                const tabId = `tab-${id}`;
                const contentId = `content-${id}`;

                const tabContentHtml = `
                <div class="tab-pane fade ${isActive}" id="${contentId}" role="tabpanel" aria-labelledby="${tabId}" tabindex="0">
                    <div class="row g-4 mt-20">
                        <div class="col-12">
                            <label class="form-label" for="bankName-${id}">@lang('Bank Name')</label>
                            <select class="cmn-select2 change_bank" name="bank_id" id="bankName-${id}"></select>
                        </div>
                        <div class="mt-3 negative dff" id="dynamicFormFields${contentId}"></div>
                    </div>
                </div>`;

                const tabHtml =         `
                <li class="nav-item change_service" data-tab_service_id="${id}" role="presentation">
                    <button class="nav-link ${isActive}" id="${tabId}" data-bs-toggle="pill" data-bs-target="#${contentId}"
                        type="button" role="tab" aria-controls="${contentId}" aria-selected="${isActive}">
                        ${serviceName}
                    </button>
                </li>`;

                $('#pills-tab').append(tabHtml);

                $('#pills-tabContent').append(tabContentHtml);

                if (isActive || index === 0) {
                    const countryId = $("#currency option:selected").data('country');
                    changeService(countryId, id);
                }

                $(`#bankName-${id}`).select2();
            });
        }

        function renderNoServiceAvailable() {
            $('#pills-tab').html('');
            $('#pills-tabContent').empty().append($('<h4>', { text: 'No service available', class: 'text-center', }));
        }

        function handleServiceChange() {
            const serviceId = $(this).data('tab_service_id');
            const countryId = $("#currency option:selected").data('country');
            changeService(countryId, serviceId);
        }

        function handleBankChange() {
            const tabChangeId = $(this).closest('.tab-pane').attr('id');
            const selectedBank = $(this).val();
            fetchBankFormFields(selectedBank, tabChangeId);
        }

        function changeService(countryId, serviceId) {

            $('#service_id').val(serviceId)

            showLoading('Loading Banks & generating fields...');

            var $select = $(`#bankName-${serviceId}`);

            $('.change_bank').not(`#bankName-${serviceId}`).empty();


            axios.get('{{ route('user.getBank') }}', {params: {service_id: serviceId, country_id: countryId}})
                .then(response => {
                    const banks = response.data.banks;

                    const oldBankId = '{{ old('bank_id') }}';

                    $select.empty().append(
                        banks.map(bank => $('<option>', {value: bank.id, text: bank.name, selected: bank.id == oldBankId}))
                    ).trigger('change');

                    $('.dff').not(`#dynamicFormFieldscontent-${serviceId}`).empty();

                })
                .catch(error => console.error('Error fetching banks:', error));
        }

        function fetchBankFormFields(selectedBank, tabChangeId) {

            showLoading('Loading Form Fields...', 'standard');

            axios.get('{{ route('user.generateFields') }}', {params: {bank_id: selectedBank}})
                .then(response => {
                    generateFormFields(response.data, tabChangeId);
                })
                .catch(error => console.error('Error fetching banks form fields:', error))
            hideLoading();
        }

        function generateFormFields(serviceFormFields, tabChangeId) {

            const dynamicFormFields = $(`#dynamicFormFields${tabChangeId}`);

            dynamicFormFields.empty();

            const oldValues = {!! json_encode(old()) !!};
            const errors = {!! json_encode($errors->getMessages()) !!};

            Object.values(serviceFormFields).forEach(field => {
                const { field_label, field_name, type, validation } = field;

                const labelElement = $('<label>', { for: field_name, class: 'form-label', text: field_label });

                let inputElement;

                switch (type) {
                    case 'text':
                    case 'number':
                    case 'file':
                    case 'date':
                        inputElement = $('<input>', {name: field_name, type: type, id: field_name,
                            class: `form-control mb-2 ${errors[field_name] ? 'is-invalid' : ''}`,
                            value: oldValues[field_name] || '',
                            required: validation === 'required',
                        });
                        if (errors[field_name]) inputElement.after($('<span>', {class: 'invalid-feedback', text: errors[field_name][0],}));
                        break;
                    case 'textarea':
                        inputElement = $('<textarea>', {name: field_name, id: field_name,
                            class: `form-control ${errors[field_name] ? 'is-invalid' : ''}`, rows: 3,
                        }).text(oldValues[field_name]);
                        break;

                    default:
                        console.warn(`Unsupported field type: ${type}`);
                        return;
                }
                dynamicFormFields.append(labelElement, inputElement);
            });
        }

        function showLoading(message, iconType) {
            let icon = iconType ?? 'arrows';
            Notiflix.Block[icon]('#loadingData', message, {
                backgroundColor: 'rgb(255,255,255)',
                svgColor: '#32c682',
                messageColor: '#2eb930',
                clickable: true,
                borderRadius: '5px',
                messageFontSize: '18px',
                svgSize: '70px',
                zIndex: 5000,
            });
        }
        function hideLoading() {
            Notiflix.Block.remove('#loadingData');
        }

    </script>

    <script>
        $(document).ready(function () {
            $('.cmn-select2-image').select2({
                templateResult: formatState,
                templateSelection: formatState
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

@endpush
