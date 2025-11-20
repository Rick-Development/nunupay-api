<section class="newsletter-section pt-50 pb-50">
    <div class="container">
        <div class="row align-items-center g-4">
            <div class="col-lg-6 col-md-5">
                <div class="content-area">
                    <div class="subscribe-small-text">@lang(@$news_letter['single']['heading'])</div>
                    <h1 class="subscribe-normal-text">@lang(@$news_letter['single']['sub_heading'])</h1>
                </div>

            </div>
            <div class="col-lg-6 col-md-7">
                <form class="newsletter-form" action="{{ route('admin.subscriber.store') }}" method="post">
                    @csrf
                    @error('email')<div class="text-danger">{{$message}}</div>@enderror
                    <input type="email" name="email" class="form-control" placeholder="@lang('Email Address')"
                        required />
                    <button class="subscribe-btn">@lang(@$news_letter['single']['button_name'])</button>
                </form>
            </div>
        </div>
    </div>
</section>
