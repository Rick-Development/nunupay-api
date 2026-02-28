@extends($theme.'layouts.user')
@section('title')
	{{ __('Pay with ').__(optional($deposit->gateway)->name) }}
@endsection
@section('content')
    <div class="dashboard-wrapper">
        <div class="col-xxl-8 col-lg-10 mx-auto">
        <div class="breadcrumb-area"><h3 class="title">@yield('title')</h3></div>
            <div class="card card-primary shadow">
                <div class="card-header">@lang('Payment Preview')</div>
                <div class="card-body text-center">
                    <h4 class="text-color"> @lang('PLEASE SEND EXACTLY') <span class="text-success"> {{ getAmount($data->amount) }}</span> {{ __($data->currency) }}</h4>
                    <h5>@lang('TO') <span class="text-success"> {{ __($data->sendto) }}</span></h5>
                    <img src="{{ $data->img }}">
                    <h4 class="text-color bold">@lang('SCAN TO SEND')</h4>
                </div>
            </div>
        </div>
    </div>
@endsection

