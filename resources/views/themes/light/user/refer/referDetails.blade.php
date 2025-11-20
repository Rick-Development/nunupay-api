@extends($theme.'layouts.user')
@section('title', trans('Referral User Details'))

@section('content')
    <div class="dashboard-wrapper">
        <a href="{{ route('user.referList') }}" class="back-btn">
            <i class="fa-regular fa-angle-left"></i>@lang('Back To Referral List')</a>
        <div class="row">
            <div class="col-xxl-8 col-xl-10 mx-auto mt-50">
                <h4 class="mb-30">@lang('Referral details')</h4>
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex align-items-center gap-3">
                            <div class="img-box">
                                @if($referUser->image && $referUser->image_driver)
                                    <img class="recipient-avatar" src="{{ $referUser?->getImage() }}" alt="">
                                @else
                                    <span class="recipient-avatar-name">
                                            {{ substr(ucfirst($referUser->firstname), 0, 1) }}{{ substr(ucfirst($referUser->lastname), 0, 1) }}
                                        </span>
                                @endif
                            </div>
                            <div class="text-box">
                                <h6 class="fw-bold">@lang($referUser->fullname())</h6>
                                <span>{{ $referUser->email }}</span>
                            </div>

                        </div>
                    </div>
                    <div class="details-section card-body">

                        <h5 class="mt-10">@lang('Basic Information')</h5>
                        <hr class="cmn-hr2">
                        <div class="transfer-list pt-0 pb-0">
                            <div class="item">
                                <span>@lang('User Name')</span>
                                <h6>{{'@'. $referUser->username }}</h6>
                            </div>
                            <div class="item">
                                <span>@lang('Phone Number')</span>
                                <h6>{{ $referUser->phone_code }} {{ $referUser->phone }}</h6>
                            </div>
                            <div class="item">
                                <span>@lang('Country Name')</span>
                                <h6>{{ $referUser->country ?? 'N/A' }}</h6>
                            </div>
                            <div class="item">
                                <span>@lang('State Name')</span>
                                <h6>{{ $referUser->state ?? 'N/A' }}</h6>
                            </div>
                            <div class="item">
                                <span>@lang('Address One')</span>
                                <h6>{{ $referUser->address_one ?? 'N/A' }}</h6>
                            </div>
                            <div class="item">
                                <span>@lang('Address Two')</span>
                                <h6>{{ $referUser->address_two ?? 'N/A' }}</h6>
                            </div>
                            <div class="item">
                                <span>@lang('Join Date')</span>
                                <h6>{{ dateTime($referUser->created_at) }}</h6>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection





