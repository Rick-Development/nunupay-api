@extends($theme . 'layouts.app')
@section('title',trans('Blog Details'))
@section('content')

<section class="blog-details-section">
    <div class="container">
        <div class="row g-5">
            <div class="col-xxl-8 col-lg-7 order-2 order-lg-1">
                <div class="blog-details">
                    <div class="thum-inner">
                        <div class="blog-image">
                            <img src="{{ getFile(optional($blogDetails->blog)->blog_image_driver, optional($blogDetails->blog)->blog_image) }}" alt="">
                            <div class="date">{{ dateTime(optional($blogDetails->blog)->created_at) }}</div>
                        </div>
                    </div>
                    <div class="blog-author">
                        <div class="author-img">
                            <img src="{{ getFile(optional($blogDetails->blog)->author_image_driver, optional($blogDetails->blog)->author_image) }}" alt="">
                        </div>
                        <div class="author-info">
                            <a href="#"><h5>@lang($blogDetails->author_name)</h5></a>
                            <span>@lang($blogDetails->author_title)</span>
                        </div>
                    </div>
                    <div class="blog-header">
                        <h3 class="">@lang(optional($blogDetails)->title)</h3>
                    </div>
                    <div class="blog-para">
                        @lang( '<p>'. nl2br(strip_tags($blogDetails->description, '<p><br>')) .'</p>' )
                    </div>
                </div>
            </div>

            <div class="col-xxl-4 col-lg-5 order-1 order-lg-2">
                <div class="blog-sidebar">
                    <div class="blog-widget-area">
                        <form action="{{ route('blogSearch') }}" method="get">
                        <div class="search-box">
                            <input type="text" name="search" class="form-control" placeholder="Search here...">
                            <button type="submit" class="search-btn"><i class="far fa-search"></i></button>
                        </div>
                        </form>
                    </div>
                    <div class="blog-widget-area">
                        <div class="widget-title">
                            <h4>{{ trans('Recent Post') }}</h4>
                        </div>
                        @foreach($recent_blogs->take(3) as $item)
                            <a href="{{ route('blog.details',optional($item->details)->slug ?? 'blog-description') }}" class="blog-widget-item">
                                <div class="blog-widget-image">
                                    <img src="{{ getFile($item->blog_image_driver,$item->blog_image) }}" alt="">
                                </div>
                                <div class="blog-widget-content">
                                    <div class="blog-title">@lang($item->details?->title)</div>
                                    <div class="blog-date"><i class="fa-regular fa-calendar-days"></i>{{ dateTime($item->created_at) }}</div>
                                </div>
                            </a>
                        @endforeach
                    </div>

                    <div class="blog-widget-area">
                        <div class="categories-area">
                            <div class="categories-header">
                                <div class="widget-title"><h4>{{ trans('Categories') }}</h4></div>
                            </div>
                            <ul class="categories-list">
                                @foreach($categories as $item)
                                <li>
                                    <a href="{{ route('blog.categoryWise', [slug(optional($item)->name), $item->id]) }}">
                                        <span>@lang($item->name)</span> <span class="highlight">{{$blogCount[$item->id] ?? 0}}</span>
                                    </a>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</section>

@include($theme.'sections.footer')
@endsection
