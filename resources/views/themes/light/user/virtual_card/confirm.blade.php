@extends($theme.'layouts.user')
@section('title',__('Confirm Card Request'))

@section('content')
    <div class="dashboard-wrapper">
        <a href="{{ route('user.virtual.card') }}" class="back-btn mb-20">
            <i class="fa-regular fa-angle-left"></i>@lang('Back To Card List')</a>
        <div class="col-xxl-6 col-xl-10 mx-auto">
            <div class="card mb-4 ">
                <div class="card-header  d-flex flex-row align-items-center justify-content-center">
                    <h4 class="m-0 font-weight-bold ">{{optional($order->cardMethod)->name}} @lang('Card Request')</h4>
                </div>
                <div class="card-body details-section ">
                    <form action="{{ route('user.order.confirm',$orderId) }}" method="post">
                        @csrf
                    <ul class="transfer-list">
                        <li class="item">
                            <span> {{ trans('Currency') }} :</span>
                            <span class="fw-semibold">{{ trans($order->currency) }}</span>
                        </li>
                        @if($order->form_input)
                            @forelse($order->form_input as $k => $v)

                                @if ($v->type == 'text')
                                    <li class="item">
                                        <span> {{ @$v->field_level }} :</span>
                                        <span class="fw-semibold">{{ @$v->field_value }}</span>
                                    </li>
                                @elseif($v->type == 'textarea')
                                    <li class="item">
                                        <span> {{ @$v->field_level }} :</span>
                                        <span class="fw-semibold">{{ @$v->field_value }}</span>
                                    </li>
                                @elseif($v->type == 'file')
                                    <li class="item">
                                        <span> {{ @$v->field_level }} :</span>
                                    </li>
                                @elseif($v->type == 'date')
                                    <li class="item">
                                        <span> {{ @$v->field_level }} :</span>
                                        <span class="fw-semibold">{{ @$v->field_value }}</span>
                                    </li>
                                @endif
                            @empty
                            @endforelse
                        @endif

                        <button type="submit" id="submit"
                                class="mt-2 cmn-btn btn-security w-100">@lang('Confirm')</button>
                    </ul>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
