@extends($theme.'layouts.user')
@section('title', trans('Payment Review'))

@section('content')

    <div class="dashboard-wrapper">
        <div class="row">
            <div class="col-xxl-9 col-lg-10 mx-auto">
                <div class="row g-4 g-sm-5">
                    @include($theme.'partials.payment_step')

                    <div class="col-xxl-6 col-xl-7 col-lg-8 col-md-9 order-1 order-md-2">
                        <div class="transfer-details-section" id="app">
                            <h4 class="mb-30">@lang('Please Complete Your Kyc Verification')</h4>
                            <div class="recipient-box2">
                                <div class="left-side">
                                    <div class="img-box">
                                        <span class="recipient-avatar-name">N</span>
                                    </div>
                                    <div class="text-box">
                                        <h6 class="fw-bold">NID</h6>
                                    </div>
                                </div>
                                <div class="right-side">
                                    <i class="fa-regular fa-check-circle"></i>
                                </div>
                            </div>


                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('script')
    @include('partials.calculationScript')
@endpush
