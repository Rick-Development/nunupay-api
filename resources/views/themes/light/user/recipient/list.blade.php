@extends($theme.'layouts.user')
@section('title', trans('Recipients'))

@section('content')
    <div class="dashboard-wrapper">
        <div class="col-xxl-8 col-xl-10 col-lg-8 col-md-9 mx-auto">
            <div class="breadcrumb-area">
                <h3 class="title">@lang('Recipient List')</h3>
            </div>
            <div class="recipient-section">
                <div class="d-flex align-items-center gap-3">
                    <div class="search-bar">
                        <form class="search-form d-flex align-items-center" method="get" action="">
                            <input name="search" type="text" class="form-control"
                                   value="{{old('search', request()->search)}}" placeholder="@lang('Name, email, country') or {{ '@'.basicControl()->site_title.'tag' }}">
                            <button type="submit" class="search-icon" title="Search">
                                    <i class="fa-regular fa-magnifying-glass"></i></button>
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
                            <div class="col-xl-3 col-lg-4 col-sm-6 recipient-item user">
                                <form action="{{ route('user.recipient.userStore') }}" method="post" id="recipientForm">
                                    @csrf
                                    <div type="submit" class="recipient-box user">
                                        <div class="img-box">
                                            <img class="recipient-avatar" src="{{ $user->getImage() }}" alt="">
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
                            <div class="col-xl-3 col-lg-4 col-sm-6 recipient-item {{ $item->type}}">
                                <a href="{{ route('user.recipient.details',$item->uuid) }}" class="recipient-box {{ $item->type }}">
                                    <div class="img-box">
                                        @if(!$item->r_user_id)
                                        <span class="recipient-avatar-name">{{ substr(ucfirst($item->name), 0, 1) }}</span>
                                        <img class="recipient-flag" src="{{ $item->currency?->country?->getImage() }}" alt="">
                                        @else
                                            <img class="recipient-avatar" src="{{ $item->recipientUser?->getImage() }}" alt="">
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
            </div>
            @if(isset($recipients))
                {{ $recipients->appends($_GET)->links($theme.'partials.pagination') }}
            @endif

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
                            <a href="{{route('user.recipient.create',['type' => 'my-self'])}}" class="item">
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
                            <a href="{{route('user.recipient.create',['type' => 'others'])}}" class="item">
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

            $('#recipientForm').on('click', function(event) {
                $(this).submit();
            });

            let $grid = $('.isotope-container').isotope({
                itemSelector: '.recipient-item',
                percentPosition: true,
                masonry: {
                    columnWidth: 1
                }
            });

            $('.filter-button-group').on('click', 'button', function () {
                let filterValue = $(this).attr('data-filter');
                $grid.isotope({ filter: filterValue });
            });

            $('.filter-button-group button').on('click', function (event) {
                $(this).siblings('.active').removeClass('active');
                $(this).addClass('active');
                event.preventDefault();
            });
        });
    </script>

@endpush


