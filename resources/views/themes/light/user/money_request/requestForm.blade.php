@extends($theme.'layouts.user')
@section('title', trans('Recipient Details'))

@section('content')
    <div class="dashboard-wrapper">
        <a href="{{ route('user.recipient.index') }}" class="back-btn mb-20">
            <i class="fa-regular fa-angle-left"></i>@lang('Back To Recipient List')</a>
        <div class="col-xxl-5 col-xl-10 mx-auto">
            <div class="card">
                <div class="card-body">
                    <h4 class="mb-30">@lang('Request Money From ' .$recipient->fullname())</h4>
                    <form action="{{ route('user.requestMoney') }}" method="post">
                        @csrf
                        <div class="col-12 mb-2">
                            <label class="form-label" for="currency">@lang('select wallet currency')</label>
                            <select class="cmn-select2-image" name="wallet" id="currency">
                                @foreach($wallets as $item)
                                    <option
                                        data-img="{{ $item->countryTC?->getImage() ?? '' }}"
                                        data-country="{{ $item->countryTC?->id ?? '' }}"
                                        value="{{ $item->uuid }}"
                                        {{ request()->old('wallet') == $item->uuid ? 'selected' : '' }}
                                    >
                                        {{ $item->currency?->code }} - @lang( $item->currency?->name)
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12">
                            <label for="amount">@lang('Amount')</label>
                            <input type="text" class="form-control" name="amount" id="amount" placeholder="e.g: 100"
                                   onkeyup="this.value = this.value.replace (/^\.|[^\d\.]/g, '')">
                            @error('amount') <span class="text-danger"> {{ $message }}</span> @enderror
                        </div>
                        <input type="hidden" name="recipient_id" value="{{ $recipient->id }}">

                        <div class="col-12 mt-3">
                            <button type="submit" class="cmn-btn">@lang('Confirm')</button>
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
            $('.deleteBtn').on('click', function () {
                let route = $(this).data('route');
                $('.setRoute').attr('action', route);
            });

            @if($errors->has('name'))
            $('#editModal').modal('show');
            @endif
        });
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
            const imagePath = state.element.getAttribute('data-img');
            const $state = $(
                '<span><img src="' + imagePath + '" class="img-flag" /> ' + state.text + '</span>'
            );
            return $state;
        }
    </script>

@endpush

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




