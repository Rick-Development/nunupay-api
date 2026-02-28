@extends($theme.'layouts.user')
@section('title', trans('Referral List'))

@section('content')
    <div class="dashboard-wrapper">
        <a href="{{ route('user.earn') }}" class="back-btn mb-50">
            <i class="fa-regular fa-angle-left"></i>@lang('Back to Refer Page')
        </a>
        <div class="row">
            <div class="col-xxl-8 col-xl-10 mx-auto">

                <div class="recipient-section">
                    <div class="card">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <h4 class="mb-0">@lang('Referral users')</h4>
                        </div>
                        <div class="card-body">
                            <form action="" method="get">
                                <div class="search-box">
                                    <input name="search" type="text" class="form-control"
                                           value="{{old('search', request()->search)}}" placeholder="@lang('Name, email, country')">
                                    <button type="submit" class="search-btn"><i class="far fa-search"></i></button>
                                </div>
                            </form>

                            <div class="list-container mt-30">
                                @forelse($referUser as $item)
                                <a href="{{ route('user.referDetails', $item->id) }}" class="item">
                                    <div class="item-left">
                                        <div class="img-box">
                                            @if($item->image && $item->image_driver)
                                                <img class="recipient-avatar" src="{{ $item?->getImage() }}" alt="">
                                            @else
                                                <span class="recipient-avatar-name">
                                                {{ substr(ucfirst($item->firstname), 0, 1) }}{{ substr(ucfirst($item->lastname), 0, 1) }}
                                            </span>
                                            @endif
                                        </div>
                                        <div class="content-area">
                                            <h5 class="mb-0">@lang($item->fullname())</h5>
                                            <span>@lang($item->email)</span>
                                        </div>
                                    </div>
                                    <div class="item-right">
                                        <div class="icon-area">
                                            <i class="fa-light fa-angle-right"></i>
                                        </div>
                                    </div>
                                </a>
                                @empty
                                    <div class="container text-center mt-5">
                                        <img id="notFoundImage" src="" alt="@lang('No Data Found')" class="text-center w-25">
                                        <p class="mt-2">@lang('No Data Found')</p>
                                    </div>
                                @endforelse
                                {{ $referUser->appends($_GET)->links($theme.'partials.pagination') }}
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

@endsection





