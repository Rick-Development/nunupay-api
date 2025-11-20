@extends($theme . 'layouts.app')
@section('title',trans('Home'))
@section('content')
    {!!  $sectionsData !!}
@endsection


@push('script')

    @if(request()->is('/') )

        @include('partials.calculationScript')
        <script>
            $(document).ready(function () {
                let $title = $('.hero-title');
                let words = $title.html().split(' ');
                if (words.length >= 3) {
                    words[2] = '<span class="text-style highlight">' + words[2] + '</span>';
                    $title.html(words.join(' '));
                }
            });
        </script>


        <script>
            $(document).on('click', '#countryToSend', function () {
                let currencyCode = $(this).data('currency-code');
                document.cookie = `receiverCurrency=${currencyCode}; path=/`;
            });
        </script>
    @endif

@endpush
