@extends($theme.'layouts.user')
@section('title', trans('Recipient Details'))

@section('content')
    <div class="dashboard-wrapper">
        <a href="{{ route('user.recipient.index') }}" class="back-btn mb-20">
            <i class="fa-regular fa-angle-left"></i>@lang('Back To Recipient List')</a>
        <div class="col-xxl-6 col-xl-10 mx-auto">
            <h4 class="mb-30">@lang('Recipient details')</h4>
            <div class="details-section">
                <div class="recipient-box2">
                    <div class="left-side">
                        <div class="img-box">
                            @if(!$recipient->r_user_id)
                                <span class="recipient-avatar-name">{{ substr(ucfirst($recipient->name), 0, 1) }}</span>
                                <img class="recipient-flag" src="{{ $recipient->currency?->country?->getImage() }}"
                                     alt="">
                            @else
                                <img class="recipient-avatar" src="{{ $recipient->recipientUser?->getImage() }}" alt="">
                                <img class="recipient-flag"
                                     src="{{ getFile(basicControl()->favicon_driver, basicControl()->favicon) }}"
                                     alt="">
                            @endif
                        </div>
                        <div class="text-box">
                            <h6 class="fw-bold">@lang($recipient->name)</h6>
                        </div>
                    </div>
                    <div class="right-side">
                        <a href="{{ route('user.transferAmount').'?recipient='.$recipient->uuid }}"
                           class="cmn-btn3" id="countryToSend"
                           data-currency-code="{{ $recipient?->currency?->code }}"
                        >
                            <i class="fa-regular fa-arrow-up"></i> @lang('send')
                        </a>
                        @if($recipient->r_user_id)
                            <a href="{{ route('user.requestMoneyForm',$recipient->uuid) }}" class="cmn-btn3">
                                <i class="fa-regular fa-arrow-down"></i> @lang('request')
                            </a>
                        @endif

                        <button type="button" class="delete-btn deleteBtn" data-bs-toggle="modal"
                                data-bs-target="#deleteModal"
                                data-route="{{ route("user.recipient.destroy", $recipient->id) }}">{{ trans('Delete') }}
                        </button>
                    </div>
                </div>
                <h5 class="mt-20">@lang('Basic Information')</h5>
                <hr class="cmn-hr2">
                <div class="transfer-list pt-0 pb-0">
                    <div class="item">
                        <span>@lang('Nick Name')</span>
                        <h6><a href="#" class="link" data-bs-toggle="modal" data-bs-target="#editModal">
                                {{ trans('change nickname') }}</a></h6>
                    </div>
                    <div class="item">
                        <span>@lang('Email')</span>
                        <h6>@lang($recipient->email)</h6>
                    </div>
                    <div class="item">
                        <span>@lang('Type')</span>
                        <h6>{{ ($recipient->type == 0) ? trans('My Self') : trans('Others') }} </h6>
                    </div>

                    @if(!$recipient->r_user_id)
                        <div class="item">
                            <span>@lang('Country Name')</span>
                            <h6>@lang($recipient?->currency?->country?->name)</h6>
                        </div>
                        <div class="item">
                            <span>@lang('Currency')</span>
                            <h6>@lang($recipient?->currency?->code) - @lang($recipient?->currency?->name)</h6>
                        </div>
                    @endif
                </div>

                @if(!$recipient->r_user_id)
                    <h5 class="mt-20">@lang('Account Information')</h5>
                    <hr class="cmn-hr2">
                    <div class="transfer-list pt-0 pb-0">
                        @foreach($recipient->bank_info ?? [] as $key => $bank)
                            <div class="item">
                                <span>{{ snake2Title($key) }}</span>
                                <span class="fw-semibold">{{ $bank }}</span>
                            </div>
                        @endforeach
                        <div class="item">
                            <span>@lang('Service Name')</span>
                            <h6>@lang($recipient?->service?->name)</h6>
                        </div>
                        <div class="item">
                            <span>@lang('Bank Name')</span>
                            <h6>@lang($recipient?->bank?->name)</h6>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

@endsection


@push('loadModal')

    <!-- Edit Modal -->
    <div id="editModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="edit" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="recipient-details">@lang('Edit Recipient Nickname')</h5>
                    <button type="button" class="cmn-btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="fa-light fa-xmark"></i></button>
                </div>
                <form action="{{ route('user.recipient.update.name', $recipient->id) }}" method="post">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="form-label">@lang('Name')</label>
                            <input type="text" name="name" value="{{ old('name',$recipient->name) }}"
                                   class="edit-name form-control @error('name') is-invalid @enderror">
                            @error('name')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="cmn-btn3"
                                data-bs-dismiss="modal">@lang('Close')</button>
                        <button type="submit" class="cmn-btn">@lang('Save changes')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- End Edit Modal -->

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">{{ trans('Confirm Deletion') }}</h5>
                    <button type="button" class="cmn-btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="fa-light fa-xmark"></i></button>
                </div>
                <div class="modal-body">
                    @if ($recipient->moneyTransfers()->exists())
                        <p class="text-danger">{{ trans($recipient->moneyTransfers()->count().' transfers associated with this recipient.' ) }}</p>
                    @endif
                    <p>{{ trans('Are you sure you want to delete this recipient?') }}</p>
                    <p id="recipientInfo"></p>
                </div>
                <div class="modal-footer">
                    <form id="deleteForm" method="post" action="" class="setRoute">
                        @csrf
                        @method('delete')
                        <button type="button" class="cmn-btn3 btn-sm"
                                data-bs-dismiss="modal">{{ trans('Close') }}</button>
                        <button type="submit" class="delete-btn">{{ trans('Delete') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endpush

@push('script')

    <script>
        $(document).ready(function () {
            $('.deleteBtn').on('click', function () {
                let route = $(this).data('route');
                $('.setRoute').attr('action', route);
            });

            @if($errors->has('name'))
            $('#editModal').modal('show');
            @endif

            $(document).on('click', '#countryToSend', function () {
                let currencyCode = $(this).data('currency-code');
                document.cookie = `receiverCurrency=${currencyCode}; path=/`;
            });
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




