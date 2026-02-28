@php
    $isAmount = request()->routeIs('user.transferAmount');
    $isRecipient = request()->routeIs('user.transferRecipient');
    $isReview = request()->routeIs('user.transferReview');
    $isPay = request()->routeIs('user.transferPay');
@endphp

<div class="col-md-3 order-2 order-md-1">
    <div class="progress-section">
        <div class="item">
            <div class="icon-box {{ $isAmount ? 'running' : 'active' }}">
                <i class="fa-regular {{ $isAmount ? 'fa-horizontal-rule' : 'fa-check' }}"></i>
            </div>
            <h6>@lang('Amount')</h6>
        </div>
        <div class="item">
            <div class="icon-box {{ $isRecipient ? 'running' : ($isReview || $isPay ? 'active' : '') }}">
                <i class="fa-regular {{ $isRecipient ? 'fa-horizontal-rule' : ($isReview || $isPay ? 'fa-check' : 'fa-horizontal-rule fa-2xs') }}"></i>
            </div>
            <h6>@lang('Recipient')</h6>
        </div>
        <div class="item">
            <div class="icon-box {{ $isReview ? 'running' : ($isPay ? 'active' : '') }}">
                <i class="fa-regular {{ $isReview ? 'fa-horizontal-rule' : ($isPay ? 'fa-check' : 'fa-horizontal-rule fa-2xs') }}"></i>
            </div>
            <h6>@lang('Review')</h6>
        </div>
        <div class="item">
            <div class="icon-box">
                <i class="fa-regular fa-horizontal-rule fa-2xs"></i>
            </div>
            <h6>@lang('Pay')</h6>
        </div>
    </div>
</div>

