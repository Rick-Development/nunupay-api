

<section class="faq-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <div class="faq-thum">
                    <img src="{{ getFile(@$faq['single']['media']->image->driver,@$faq['single']['media']->image->path) }}" alt="">
                </div>
            </div>
            <div class="col-md-6">
                <div class="faq-content">
                    <div class="section-subtitle">@lang($faq['single']['heading'])</div>
                    <h2>@lang($faq['single']['sub_heading'])</h2>
                    <p class="cmn-para-text mx-auto">@lang($faq['single']['title'])</p>
                    <div class="accordion" id="accordionParent">
                        @foreach(collect($faq['multiple'])->toArray() as $key=>$item)
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="question{{$key}}">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#collapse{{$key}}" aria-expanded="true"
                                        aria-controls="collapse{{$key}}">
                                    @lang($item['question'])
                                </button>
                            </h2>
                            <div id="collapse{{$key}}" class="accordion-collapse collapse"
                                 aria-labelledby="question{{$key}}"
                                 data-bs-parent="#accordionParent">
                                <div class="accordion-body">
                                    <div class="table-responsive">
                                        <p>@lang(strip_tags($item['answer']))</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @endforeach
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>

