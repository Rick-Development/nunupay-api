<section class="country-support-section">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="section-header text-center">
                    <div class="section-subtitle">@lang(@$countries['single']['heading']) </div>
                    <h2 class="section-title mx-auto">@lang(@$countries['single']['sub_heading'] )</h2>
                </div>
            </div>
        </div>
        <div class="row g-4 justify-content-center">
            @foreach($country as $item)
                <a href="{{ route('user.transferAmount') }}"
                   id="countryToSend" data-currency-code="{{ $item->currency->code }}"
                   class="col-xxl-2 col-lg-3 col-md-4 col-sm-5">
                    <div class="country-box">
                        <div class="thumb-area">
                            <img src="{{ getFile($item->image_driver, $item->image) }}" alt="@lang('countries')">
                        </div>
                        <div class="content-area">
                            <h6>@lang($item->name)</h6>
                        </div>
                    </div>
                </a>
            @endforeach

        </div>
    </div>
</section>
