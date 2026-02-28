@extends($theme.'layouts.user')
@section('title',__('2 Step Security'))

@section('content')
    <div class="dashboard-wrapper">
        <a href="{{ route('user.settings') }}" class="back-btn">
            <i class="fa-regular fa-angle-left"></i>@lang('Back to Settings')
        </a>
        <div class="row">
            <div class="col-xxl-8 col-xl-10 mx-auto mt-30">
                <h4 class="mb-15">@yield('title')</h4>
                <div class="row">
                    @if(auth()->user()->two_fa)
                        <div class="col-lg-6 col-md-6 mb-3">
                            <div class="card text-center search-bar">
                                <div class="card-header">
                                    <h5>@lang('Two Factor Authenticator')</h5>
                                </div>
                                <div class="card-body">
                                    <div class="share-links-section">
                                        <div class="share-links">
                                            <div class="copy-box">
                                                <input type="text" class="form-control" id="referralURL"
                                                       value="{{$secret}}" readonly>
                                                <button type="button" class="copy-btn" onclick="copyToClipboard()">copy
                                                    <i class="fa fa-copy"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group mx-auto text-center mt-3">
                                        <img class="w-40 mx-auto"
                                             src="https://quickchart.io/chart?cht=qr&chs=150x150&chl=myqrcode={{$qrCodeUrl}}">
                                    </div>
                                    <div class="form-group mx-auto text-center mt-3">
                                        <a href="javascript:void(0)" class="w-100 cmn-btn"
                                           data-bs-toggle="modal"
                                           data-bs-target="#disableModal">@lang('Disable Two Factor Authenticator')</a>
                                    </div>
                                </div>

                            </div>
                        </div>
                    @else
                        <div class="col-lg-6 mb-3">
                            <div class="card search-bar text-center h-100">
                                <div class="card-header">
                                    <h5>@lang('Two Factor Authenticator')</h5>
                                </div>
                                <div class="card-body">
                                    <div class="form-group mx-auto text-center mt-3 ">
                                        <img class="mx-auto w-40"
                                             src="https://quickchart.io/chart?cht=qr&chs=150x150&chl=myqrcode={{$qrCodeUrl}}">
                                    </div>
                                    <div class="share-links-section">
                                        <div class="share-links">
                                            <div class="copy-box">
                                                <input type="text" class="form-control" id="referralURL"
                                                       value="{{$secret}}" readonly>
                                                <button type="button" class="copy-btn"
                                                        onclick="copyToClipboard()">{{ trans('copy') }} <i
                                                        class="fa fa-copy"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group mx-auto text-center mt-3">
                                        <a href="javascript:void(0)" class="cmn-btn w-100"
                                           data-bs-toggle="modal"
                                           data-bs-target="#enableModal">@lang('Enable Two Factor Authenticator')</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                    <div class="col-md-6 mb-3">
                        <div class="card text-center h-100">
                            <div class="card-header">
                                <h5>@lang('Google Authenticator')</h5>
                            </div>
                            <div class="card-body">
                                <h6 class="text-uppercase my-3">@lang('Use Google Authenticator to Scan the QR code  or use the code')</h6>
                                <p class="p-2">@lang('Google Authenticator is a multifactor app for mobile devices. It generates timed codes used during the 2-step verification process. To use Google Authenticator, install the Google Authenticator application on your mobile device.')</p>
                                <a class="cmn-btn mt-3"
                                   href="https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2&hl=en"
                                   target="_blank">@lang('DOWNLOAD APP')</a>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection


@push('loadModal')
    <!-- Enable Modal -->
    <div class="modal fade user-modal" id="enableModal" tabindex="-1" aria-labelledby="exampleModalLabel"
         aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">@lang('Verify Your OTP')</h5>
                    <button type="button" class="cmn-btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="fa-light fa-xmark"></i></button>
                </div>
                <form action="{{route('user.twoStepEnable')}}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="input-box col-md-12">
                            <input type="hidden" name="key" value="{{$secret}}">
                            <input type="text" class="form-control" name="code"
                                   placeholder="@lang('Enter Google Authenticator Code')" autocomplete="off">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="cmn-btn3" data-bs-dismiss="modal">@lang('Close')</button>
                        <button type="submit" class="cmn-btn">@lang('Verify')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Disable Modal -->
    <div class="modal fade user-modal" id="disableModal" tabindex="-1" aria-labelledby="exampleModalLabel"
         aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">@lang('Verify that\'s you, to Disable 2FA')</h5>
                    <button type="button" class="cmn-btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="fa-light fa-xmark"></i></button>
                </div>
                <form action="{{route('user.twoStepDisable')}}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="password-box">
                            <input name="password" type="password" class="form-control password"
                                   id="currentPassword"
                                   value="{{ old('password') }}"
                                   placeholder="{{ trans('Enter Your Password') }}"
                                   autocomplete="off">
                            <i class="password-icon fa-regular fa-eye toggle-password"></i>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="close" data-bs-dismiss="modal">@lang('Close')</button>
                        <button type="submit" class="cmn-btn">@lang('Verify')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endpush


@push('script')
    <script>
        $(document).ready(() => {
            $('.toggle-password').on('click', function () {
                const passwordInput = $(this).prev('.password');
                const passwordType = passwordInput.attr('type');
                passwordInput.attr('type', passwordType === 'password' ? 'text' : 'password');
                $(this).toggleClass('fa-eye-slash', passwordType === 'password');
            });
        });

        function copyToClipboard() {
            let shareLinkInput = document.getElementById('referralURL');

            shareLinkInput.select();
            document.execCommand('copy');
            Notiflix.Notify.success(`Copied: ${shareLinkInput.value}`);

            let copyButton = document.querySelector('.copy-btn');
            copyButton.innerHTML = 'copied !! <i class="fa fa-check-square"></i>';
            setTimeout(function () {
                copyButton.innerHTML = 'copy <i class="fa fa-copy"></i>';
            }, 5000);
        }
    </script>
@endpush

