
<section class="blog-section">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="section-header text-center mb-50">
                    <div class="section-subtitle">@lang($blog['single']['heading'])</div>
                    <h2 class="section-title mx-auto">@lang($blog['single']['sub_heading'])</h2>
                    <p class="cmn-para-text mx-auto">@lang($blog['single']['title'])</p>
                </div>
            </div>
        </div>
        <div class="row g-4">
            @foreach(collect($blog['multiple'])->take(3) as $item)
            <div class="col-lg-4 col-md-6">
                <div class="blog-box">
                    <div class="img-box">
                        <a href="{{ route('blog.details', $item->details?->slug  ?? 'blog-description') }}">
                            <img src="{{ getFile(@$item->blog_image_driver,@$item->blog_image) }}" alt="">
                        </a>
                        <div class="date">{{ diffForHumans($item->created_at) }}</div>
                    </div>
                    <div class="content-box">
                        <div class="blog-author">
                            <div class="author-img">
                                <img src="{{ getFile(@$item->author_image_driver,@$item->author_image) }}" alt="">
                            </div>
                            <div class="author-info ">
                                <h6><a href="">@lang($item->details?->author_name)</a></h6>
                                <span>@lang($item->details?->author_title)</span>
                            </div>
                        </div>
                        <div class="blog-title">
                            <h5><a href="{{ route('blog.details', optional($item->details)->slug  ?? 'blog-description') }}">
                                    @lang($item->details?->title)</a></h5>
                        </div>
                        <div class="para-text">
                            <p>@lang(strip_tags($item->details['description']))</p>
                        </div>
                        <a href="{{ route('blog.details', $item->details?->slug  ?? 'blog-description') }}" class="blog-btn">
                            @lang('Read more')</a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

