<section class="why-choose-us">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6 order-2 order-md-1">
                <div class="why-choose-us-content">
                    <div class="section-subtitle">@lang(@$why_choose_us['single']['heading'])</div>
                    <h2 class="section-title">@lang(@$why_choose_us['single']['sub_heading'])</h2>
                    <p class="cmn-para-text">@lang(@$why_choose_us['single']['description'])</p>
                    <ul class="choose-us-list">
                        @foreach(collect(@$why_choose_us['multiple'])->toArray() as $item)
                            <li class="item"><img src="{{$themeTrue}}/img/icon/checkmark.png" alt="">
                                <h6>@lang(@$item['step'])</h6>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <div class="col-md-6 order-1 order-md-2">
                <div class="why-choose-us-thum">
                    <img src="{{ getFile(@$why_choose_us['single']['media']->image->driver,@$why_choose_us['single']['media']->image->path) }}" alt="">
                </div>
            </div>
        </div>
    </div>
</section>
