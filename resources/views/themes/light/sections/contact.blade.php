<section class="contact-section pt-0">
    <div class="contact-top">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h3>@lang($contact['single']['heading'])</h3>
                    <p>@lang($contact['single']['sub_heading'])</p>
                </div>
                <div class="col-md-6 d-none d-md-block">
                    <div class="contact-top-thum">
                        <img src="{{ @getFile($contact['single']['media']->image->driver,$contact['single']['media']->image->path) }}" alt="">
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="container">
        <div class="contact-inner">
            <div class="row">
                <div class="col-xl-4 col-lg-6">
                    <div class="contact-area">
                        <div class="section-header mb-0">
                            <h3>@lang(@$contact['single']['title'])</h3>
                        </div>
                        <p class="para_text">@lang(@$contact['single']['sub_title'])</p>

                        @foreach(collect($contact['multiple'])->take(3)->toArray() as $item)
                            <h6 class="mt-30 mb-0">@lang($item['name']):</h6>
                            <p>@lang($item['value_one']) <br> @lang($item['value_two'])</p>
                        @endforeach
                    </div>
                </div>
                <div class="col-xl-8 col-lg-6">
                    <div class="contact-message-area">
                        <form action="{{route('contact.send')}}" method="post">
                            @csrf
                            <div class="row">
                                <div class="mb-3 col-md-6">
                                    <input type="text" name="name" class="form-control" placeholder="@lang('Your Name')">
                                    @error('name')<span class="text-danger">{{$message}}</span>@enderror
                                </div>
                                <div class="mb-3 col-md-6">
                                    <input type="email" name="email" class="form-control" placeholder="@lang('Email Address')">
                                    @error('email')<span class="text-danger">{{$message}}</span>@enderror
                                </div>
                                <div class="mb-3 col-md-12">
                                    <input type="text" name="subject" class="form-control" placeholder="@lang('Your Subject')">
                                    @error('subject')<span class="text-danger">{{$message}}</span>@enderror
                                </div>
                                <div class="mb-3 col-12">
                                    <textarea name="message" class="form-control" rows="8" placeholder="@lang('Your Message')"></textarea>
                                    @error('message')<span class="text-danger">{{$message}}</span>@enderror
                                </div>
                            </div>
                            <div class="btn-area d-flex justify-content-end">
                                <button type="submit" class="cmn-btn mt-30">@lang('Send a massage')</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>


    </div>
</section>
