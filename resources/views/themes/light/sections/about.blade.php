<section id="about" class="about-section">
    <div class="container">
        <div class="row g-5 align-items-center">
            <div class="col-md-6 justify-content-center d-flex">
                <div class="about-thum">
                    <img src="{{ getFile(@$about['single']['media']->image->driver,@$about['single']['media']->image->path) }}" alt="">
                </div>
            </div>
            <div class="col-md-6">
                <div class="about-content mx-auto">
                    <div class="section-subtitle">@lang(@$about['single']['heading'])</div>
                    <h2 class="section-title">@lang( @$about['single']['sub_heading'] )</h2>
                    <p class="cmn-para-text">@lang( strip_tags(@$about['single']['description']) )</p>
                    <p>@lang( @$about['single']['question']  )</p>
                    <div class="btn-area">
                        <a href="{{ @$about['single']['media']->button_link }}" class="cmn-btn">@lang( @$about['single']['button_name'] )</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

