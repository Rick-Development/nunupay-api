@extends($theme.'layouts.user')
@section('title', trans('Refer & Earn Money'))

@section('content')
    <div class="dashboard-wrapper">

        <div class="col-xxl-8 col-lg-10 mx-auto">
            <div class="breadcrumb-area mb-30">
                <h3 class="title">@yield('title')</h3>
            </div>
            <div class="earn-banner-section">
                <div class="row g-5">
                    <div class="col-md-8 order-2 order-md-1">
                        @if(basicControl()->refer_status == 1)
                            <h1 class="section-title">@lang(basicControl()->refer_title)</h1>
                        @else
                            <h1 class="section-title">@lang('Invite Your Friends to Join Us')</h1>
                        @endif
                        <div class="share-links-section">
                            <label class="form-control-label" for="share-link">@lang('Share your link')</label>
                            <div class="share-links">
                                <div class="copy-box">
                                    <input type="text" class="form-control" id="share-link" value="{{ $url }}" readonly>
                                    <button type="button" class="copy-btn" onclick="copyToClipboard()"><i class="fa fa-copy"></i> @lang('Copy')</button>
                                </div>
                                <button type="button" class="cmn-btn d-none d-sm-block" data-bs-toggle="modal"
                                        data-bs-target="#share" id="shareLink"><i class="fa-solid fa-share"></i> @lang('Share')</button>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 d-flex align-items-end justify-content-center order-1 order-md-2">
                        <div class="thumb-area">
                            <img src="{{asset($themeTrue.'img/icon/Envelope.png')}}" alt="">
                        </div>
                    </div>
                </div>
            </div>
            @if(basicControl()->refer_status == 1)
                <div class="theygetyouget-section mt-50">
                    <div class="row g-4">
                        <div class="col-sm-6">
                            <div class="theygetyouget-card">
                                <h4 class="title">{{ trans('They get') }}</h4>
                                <div class="instruction-container">
                                    <div class="item">
                                        <div class="icon-area yes">
                                            <i class="fa-sharp fa-solid fa-circle-check"></i>
                                        </div>
                                        <div class="content-area">
                                            <span>{{ trans('A fee-free transfer up to $:amount when they sign up through you', ['amount' => getAmount(basicControl()->refer_free_transfer)]) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="theygetyouget-card">
                                <h4 class="title">{{ trans('You get') }}</h4>
                                <div class="instruction-container">
                                    <div class="item">
                                        <div class="icon-area yes">
                                            <i class="fa-sharp fa-solid fa-circle-check"></i>
                                        </div>
                                        <div class="content-area">
                                            <span>{{ trans('Instant $:amount when they deposit any amount first time', ['amount' => getAmount(basicControl()->refer_earn_amount)]) }}</span>
                                        </div>
                                    </div>
                                    <div class="item">
                                        <div class="icon-area yes">
                                            <i class="fa-sharp fa-solid fa-circle-check"></i>
                                        </div>
                                        <div class="content-area">
                                            <span>{{ trans('A fee-free transfer up to $:amount when they sign up through you', ['amount' => getAmount(basicControl()->refer_free_transfer)]) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            <div class="invited-friend-section mt-50">
                <div class="card">
                    <div class="card-body">
                        <div class="list-container">
                            <div class="section-header-area">
                                <h6 class="title">{{ $referUser->count() ?? 0 }} {{ trans('invited friend') }}</h6>
                                <h6><a href="{{ route('user.referList') }}" class="link">{{ trans('See more') }}</a></h6>

                            </div>
                            @forelse($referUser->take(3) as $item)
                            <a href="{{ route('user.referDetails', $item->id) }}" class="item">
                                <div class="item-left">
                                    <div class="thumb-area">
                                        <i class="fa-light fa-timer"></i>
                                    </div>
                                    <div class="content-area">
                                        <h5 class="mb-0 title">{{ $item->fullname() }}</h5>
                                        <span>{{ $item->email }}.</span>
                                    </div>
                                </div>
                            </a>
                            @empty
                                <div class="container text-center mt-5">
                                    <img id="notFoundImage" src="" alt="@lang('No Data Found')" class="text-center w-25">
                                    <p class="mt-2">@lang('No Data Found')</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>

@endsection

@push('loadModal')
<div class="modal fade" id="share" tabindex="-1" aria-labelledby="share" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="share">@lang('Share Referral link')</h4>
                <button type="button" class="cmn-btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="fa-light fa-xmark"></i></button>
            </div>
            <div class="modal-body">
                <div class="create-recipients-section">
                    <div class="list-container">
                        <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($url) }}" class="item">
                            <div class="item-left">
                                <div class="thumb-area">
                                    <i class="fa-brands fa-facebook"></i>
                                </div>
                                <div class="content-area"><h5 class="mb-0">{{ trans('Share on Facebook') }}</h5></div>
                            </div>
                            <div class="item-right">
                                <div class="icon-area"><i class="fa-light fa-angle-right"></i></div>
                            </div>
                        </a>
                        <a href="https://twitter.com/intent/tweet?url={{ urlencode($url) }}"  class="item" >
                            <div class="item-left">
                                <div class="thumb-area"><i class="fa-brands fa-twitter"></i></div>
                                <div class="content-area"><h5 class="mb-0">{{ trans('Share on twitter') }}</h5></div></div>
                            <div class="item-right">
                                <div class="icon-area"><i class="fa-light fa-angle-right"></i></div>
                            </div>
                        </a>
                        <a href="https://www.linkedin.com/shareArticle?url={{ urlencode($url) }}" class="item">
                            <div class="item-left">
                                <div class="thumb-area"><i class="fa-brands fa-linkedin-in"></i></div>
                                <div class="content-area"><h5 class="mb-0">{{ trans('Share on Linkedin') }}</h5></div>
                            </div>
                            <div class="item-right">
                                <div class="icon-area"><i class="fa-light fa-angle-right"></i></div>
                            </div>
                        </a>

                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endpush

@push('script')
    <script>
        function copyToClipboard() {
            let shareLinkInput = document.getElementById('share-link');

            let text = $('#share-link').val();
            Notiflix.Notify.success('Copied: ' + text)
            shareLinkInput.select();
            document.execCommand('copy');

            let copyButton = document.querySelector('.copy-btn');
            copyButton.innerHTML = 'copied !! <i class="fa fa-check-square"></i>';
            setTimeout(function() {
                copyButton.innerHTML = 'copy <i class="fa fa-copy"></i>';
            }, 5000);
        }
    </script>

@endpush
