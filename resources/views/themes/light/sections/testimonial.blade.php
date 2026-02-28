<section class="testimonial-section">
    <div class="container">
        <div class="row">
            <div class="section-header mb-50 text-center">
                <div class="section-subtitle">@lang(@$testimonial['single']['heading'])</div>
                <h2 class="">@lang(@$testimonial['single']['sub_heading'])</h2>
                <p class="cmn-para-text m-auto">@lang(@$testimonial['single']['title'])</p>
            </div>
        </div>
        <div class="row">
            <div class="owl-carousel owl-theme testimonial-carousel">
                @foreach(collect($testimonial['multiple'])->toArray() as $item)
                <div class="item">
                    <div class="testimonial-box">
                        <div class="testimonial-header">
                            <div class="testimonial-title-area">
                                <div class="testimonial-thumbs">
                                    <img src="{{ getFile(@$item['media']->image->driver,@$item['media']->image->path) }}" alt="">
                                </div>
                                <div class="testimonial-title">
                                    <h5>@lang($item['name'])</h5>
                                    <h6>@lang($item['location'])</h6>
                                </div>
                            </div>
                            <div class="qoute-icon">
                                <i class="fa-sharp fa-regular fa-quote-left"></i>
                            </div>
                        </div>
                        <div class="quote-area">
                            <p>@lang(strip_tags($item['narration']))</p>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

</section>
