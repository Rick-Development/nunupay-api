<section class="footer-section pb-50">
    <div class="container">
        <div class="row gy-4 gy-sm-5">
            <div class="col-lg-4 col-sm-6">
                <div class="footer-widget">
                    <div class="widget-logo mb-30">
                        <a href="#"><img class="logo" src="{{ @getFile($footer['single']['media']->logo->driver,$footer['single']['media']->logo->path) }}" alt=""></a>
                    </div>
                    <p>@lang( strip_tags($footer['single']['details']) )</p>
                    <div class="social-area mt-50">
                        <ul class="d-flex">
                            @foreach(collect($footer['multiple'])->take(5)->toArray() as $item)
                                <li><a href="{{ @$item['media']->link }}"><i class="{{ @$item['media']->icon }}"></i></a></li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-sm-6">
                <div class="footer-widget">
                    <h5 class="widget-title">@lang('Quick Links')</h5>
                    <ul>
                        @if(getFooterMenuData('useful_link') != null)
                            @foreach(getFooterMenuData('useful_link') as $list)
                                {!! $list !!}
                            @endforeach
                        @endif
                    </ul>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6 pt-sm-0 pt-3 ps-lg-5">
                <div class="footer-widget">
                    <h5 class="widget-title">@lang('Support links')</h5>
                    <ul>
                        @if(getFooterMenuData('support_link') != null)
                            @foreach(getFooterMenuData('support_link') as $list)
                                {!! $list !!}
                            @endforeach
                        @endif
                    </ul>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6 pt-sm-0 pt-3">
                <div class="footer-widget">
                    <h5 class="widget-title">@lang('Contact Us')</h5>
                    <p class="contact-item"><i class="fa-regular fa-location-dot"></i>
                        @lang( $footer['single']['location'] )
                    </p>
                    <p class="contact-item"><i class="fa-regular fa-envelope"></i> {{ $footer['single']['email'] }}</p>
                    <p class="contact-item"><i class="fa-regular fa-phone"></i> {{ $footer['single']['phone'] }}</p>
                </div>
            </div>
        </div>
        <hr class="cmn-hr">
        <!-- Copyright-area-start -->
        <div class="copyright-area">
            <div class="row gy-4">
                <div class="col-sm-6">
                    <p>@lang('All rights reserved') &copy; {{date('Y')}} by<a class="highlight" href="{{ route('page','/') }}"></a> </p>
                </div>
                <div class="col-sm-6">
                    <div class="language">
                        @foreach(collect($footer['languages']) as $item)
                            <a href="{{ route('language', $item->short_name) }}" class="{{ session('lang') == $item->short_name ? 'active-language base-success' : '' }}">
                                <span class="flag-icon flag-icon-{{ strtolower($item->short_name) }}"></span>
                                @lang($item->name)
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        <!-- Copyright-area-end -->
    </div>
</section>

