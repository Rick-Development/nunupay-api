<section class="feature-section">
    <div class="container">
        <div class="row">
            <div class="section-header text-center">
                <div class="section-subtitle">@lang(@$features['single']['heading'])</div>
                <h2 class="section-title text-center mx-auto">@lang(@$features['single']['sub_heading'])</h2>
                <p class="cmn-para-text mx-auto">@lang(@$features['single']['title'])</p>
            </div>
        </div>
        <div class="row g-4 g-xxl-5">
            @foreach(collect($features['multiple'])->toArray() as $item)
            <div class="col-lg-3 col-sm-6">
                <div class="cmn-box">
                    <div class="icon-box">
                        <img src="{{ getFile(@$item['media']->image->driver,@$item['media']->image->path) }}" alt="">
                    </div>
                    <div class="text-box">
                        <h5>@lang($item['title'])</h5>
                        <span>@lang($item['sub_title'])</span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
