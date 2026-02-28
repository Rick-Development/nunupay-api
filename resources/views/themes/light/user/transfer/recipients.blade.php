@extends($theme.'layouts.user')
@section('title', trans('Recipient Details'))

@section('content')

    <div class="dashboard-wrapper">
        <div class="row">
            <div class="col-xxl-9 col-lg-11 mx-auto">
                <div class="row g-4 g-sm-5">
                    @include($theme.'partials.payment_step')

                    <div class="col-md-9 order-1 order-md-2">
                        <div class="recipient-section">
                            <h4 class="mb-30">@lang('Who do you want to send money to?')</h4>
                            <div class="d-flex align-items-center gap-3">
                                <div class="search-bar">
                                    <form class="search-form d-flex align-items-center" method="get" action="">
                                        <input name="search" type="text" class="form-control"
                                               value="{{old('search', request()->search)}}"
                                               placeholder="Name, email, service">
                                        <button type="submit" class="search-icon" title="Search"><i
                                                class="fa-regular fa-magnifying-glass"></i></button>
                                    </form>
                                </div>
                                <a href="#" class="cmn-btn2" data-bs-target="#addRecipient" data-bs-toggle="modal">
                                    <i class="fa-regular fa-user"></i>@lang('add new')</a>
                            </div>

                            <div class="row mt-3">
                                <div class="col">
                                    <div class="button-group filter-button-group isotope-btn-group">
                                        <button class=" active" data-filter="*">@lang('All')</button>
                                        <button class="" data-filter=".0">@lang('My Account')</button>
                                        <button class="" data-filter=".1">@lang('Others')</button>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-4  isotope-container">

                                @if(isset($users))
                                    @forelse($users as $user)
                                        <div class="col-lg-4 col-sm-6 recipient-item user">
                                            <form action="{{ route('user.recipient.userStore') }}" method="post"
                                                  id="recipientForm">
                                                @csrf
                                                <div type="submit" class="recipient-box user">
                                                    <div class="img-box">
                                                        @if(!$user->getImage())
                                                            <span class="recipient-avatar-name">{{ substr(ucfirst($user->username), 0, 1) }}</span>
                                                        @else
                                                            <img class="recipient-avatar" src="{{ $user->getImage() }}" alt="">
                                                        @endif
                                                        <img class="recipient-flag" src="{{ getFavicon() }}" alt="">

                                                        <input type="hidden" name="r_user_id" value="{{ $user->id }}">
                                                    </div>
                                                    <div class="text-box">
                                                        <h5 class="mb-1">@lang($user->fullname())</h5>
                                                        <span>@lang($user->email)</span>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    @empty
                                        <div class="container text-center mt-5">
                                            <img id="notFoundImage" src="" alt="@lang('No Data Found')" class="text-center w-25">
                                            <p class="mt-2">@lang('No Data Found')</p>
                                        </div>
                                    @endforelse
                                @else
                                    @forelse($recipients as $item)
                                        <div class="col-lg-4 col-sm-6 recipient-item {{ $item->type}}">
                                            <a href="{{ route('user.transferReview',['uuid'=>$item->uuid]) }}"
                                               class="recipient-box {{ $item->type }}">
                                                <div class="img-box">
                                                    @if(!$item->r_user_id)
                                                        <span
                                                            class="recipient-avatar-name">{{ substr(ucfirst($item->name), 0, 1) }}</span>
                                                        <img class="recipient-flag"
                                                             src="{{ $item->currency?->country?->getImage() }}" alt="">
                                                    @else
                                                        <img class="recipient-avatar"
                                                             src="{{ $item->recipientUser?->getImage() }}" alt="">
                                                        <img class="recipient-flag" src="{{ getFavicon() }}" alt="">

                                                    @endif
                                                </div>
                                                <div class="text-box">
                                                    <h5 class="mb-1">@lang($item->name)</h5>
                                                    <span>@lang($item->email)</span>
                                                </div>
                                            </a>
                                        </div>
                                    @empty
                                        <div class="container text-center mt-5">
                                            <img id="notFoundImage" src="" alt="@lang('No Data Found')" class="text-center w-25">
                                            <p class="mt-2">@lang('No Data Found')</p>
                                        </div>
                                    @endforelse
                                @endif
                            </div>

                            @if(isset($recipients))
                                {{ $recipients->appends($_GET)->links($theme.'partials.pagination') }}
                            @endif
                        </div>
                        <div class="col-12">
                            <div
                                class="d-flex align-items-sm-center justify-content-between gap-3 mt-40 flex-column flex-sm-row">
                                <div class="left-side order-2 order-sm-1">
                                    <a href="{{ route('user.dashboard') }}" class="cmn-btn4">@lang('Cancel')</a>
                                </div>
                                <div class="right-side d-flex align-items-center gap-3  order-1 order-sm-2">
                                    <a href="{{ route('user.transferAmount') }}" class="cmn-btn3">
                                        <i class="fa-regular fa-angle-left"></i>@lang('Back')</a>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('loadModal')
    <div class="modal fade" id="addRecipient" tabindex="-1" aria-labelledby="addRecipientLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="addRecipientLabel">
                        @lang('Create recipient')
                    </h4>
                    <button type="button" class="cmn-btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="fa-light fa-xmark"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="create-recipients-section">
                        <div class="list-container">
                            <a href="{{ route('user.recipient.create', ['type' => 'my-self','addNew' => 'transfer', 'countryName' => $countryName]) }}"
                               class="item">
                                <div class="item-left">
                                    <div class="thumb-area">
                                        <img src="{{ auth()->user()->getImage() }}" alt="">
                                    </div>
                                    <div class="content-area">
                                        <h5 class="mb-0">@lang('Myself')</h5>
                                    </div>
                                </div>
                                <div class="item-right">
                                    <div class="icon-area">
                                        <i class="fa-light fa-angle-right"></i>
                                    </div>
                                </div>
                            </a>
                            <a href="{{ route('user.recipient.create', ['type' => 'others','addNew' => 'transfer', 'countryName' => $countryName]) }} "
                               class="item">
                                <div class="item-left">
                                    <div class="thumb-area"><i class="fa-light fa-user-group"></i></div>
                                    <div class="content-area">
                                        <h5 class="mb-0">@lang('Some one else')</h5>
                                    </div>
                                </div>
                                <div class="item-right">
                                    <div class="icon-area">
                                        <i class="fa-light fa-angle-right"></i>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endpush


@push('js-lib')
    <script src="{{ asset($themeTrue.'js/isotope.pkgd.min.js') }}"></script>
@endpush

@push('script')
    <script>
        $(document).ready(function () {

            let $grid = $('.isotope-container').isotope({
                itemSelector: '.recipient-item',
                percentPosition: true,
                masonry: {
                    columnWidth: 1
                }
            });

            $('.filter-button-group').on('click', 'button', function () {
                let filterValue = $(this).attr('data-filter');
                $grid.isotope({filter: filterValue});
            });

            $('.filter-button-group button').on('click', function (event) {
                $(this).siblings('.active').removeClass('active');
                $(this).addClass('active');
                event.preventDefault();
            });
        });
    </script>

@endpush

