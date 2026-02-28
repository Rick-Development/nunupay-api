<section class="how-it-work">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="section-header text-center mb-50">
                    <div class="section-subtitle">@lang(@$how_it_work['single']['heading'])</div>
                    <h2 class="section-title mx-auto">@lang(@$how_it_work['single']['sub_heading'])</h2>
                    <p class="cmn-para-text mx-auto">@lang(@$how_it_work['single']['title'])</p>
                </div>
            </div>
        </div>
        <div class="row g-4 g-xxl-5 align-items-center">
            @foreach(collect($how_it_work['multiple'])->toArray() as $key=>$item)
            <div class="col-md-4 col-sm-6">
                <div class="cmn-box">
                    <div class="icon-box">
                        <img src="{{ getFile(@$item['media']->image->driver,@$item['media']->image->path) }}" alt="">
                        <div class="number">{{ $key+1 }}</div>
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
