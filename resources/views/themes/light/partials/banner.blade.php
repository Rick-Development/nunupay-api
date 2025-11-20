
    <style>
        .banner-area {
            padding-top: 220px;
            padding-bottom: 100px;
            position: relative;
            z-index: 1;
            background-image: linear-gradient(rgba(2, 26, 46, 0.6), rgba(2, 25, 44, 0.6)),
                url({{  isset($banner->breadcrumb_image)
                        ? getFile($banner->breadcrumb_image_driver, $banner->breadcrumb_image)
                        : (isset($user_verify) && isset($user_verify->media->image) && $user_verify
                            ? getFile($user_verify->media->image->driver, $user_verify->media->image->path)
                            : asset($themeTrue.'img/banner/banner2.jpg'))
                    }});
            background-size: cover;
            background-position: 100% 75%;
        }
    </style>

    <div class="banner-area">
        <div class="container">
            <div class="row ">
                <div class="col">
                    <div class="breadcrumb-area">
                        <h3>
                            @if(isset($pageSeo['page_title']))
                                @lang($pageSeo['page_title'])
                            @else
                                @yield('title')
                            @endif
                        </h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('page','/') }}"><i class="fa-light fa-house"></i> {{ trans('Home') }}</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">
                                @if(isset($pageSeo['page_title']))
                                    @lang($pageSeo['page_title'])
                                @else
                                    @yield('title')
                                @endif
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
