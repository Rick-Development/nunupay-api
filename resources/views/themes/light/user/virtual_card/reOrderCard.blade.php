@extends($theme.'layouts.user')
@section('title',__('Re Submit'))

@section('content')
    <div class="dashboard-wrapper">
        <a href="{{ route('user.virtual.card') }}" class="back-btn mb-20">
            <i class="fa-regular fa-angle-left"></i>@lang('Back To Card List')</a>
        <div class="col-xxl-6 col-xl-10 mx-auto">

            <div class="card mb-4 card-primary shadow">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h4 class="card-header-title">@lang('Re Order For') {{$virtualCardMethod->name}} @lang('Card')</h4>
                </div>
                <div class="card-body">
                    <form action="{{route('user.virtual.card.orderReSubmit')}}" method="post"
                          enctype="multipart/form-data">
                        @csrf
                        @if($virtualCardMethod)
                            <label class="form-label mt-2">@lang('Card Currency ')</label>
                            @foreach($virtualCardMethod->currency as $singleCurrency)
                                <input type="radio" class="btn-check" name="currency"
                                       id="currency_{{ $singleCurrency }}" value="{{ $singleCurrency }}"
                                       @if($cardOrder->currency == $singleCurrency) checked @endif>
                                <label class="btn btn-outline-success"
                                       for="currency_{{ $singleCurrency }}">{{ $singleCurrency }}</label>
                            @endforeach
                        @endif
                        @if(isset($cardOrder) && !empty($cardOrder->form_input))
                            <div class="col-md-12 custom-back mb-4">
                                <div class="dark-bg p-3">
                                    <div class="row">
                                        @forelse($cardOrder->form_input as $k => $v)
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="form-label mt-2">{{ $v->field_level }}
                                                        @if ($v->validation == 'required')
                                                            <span class="text-danger">*</span>
                                                        @endif
                                                    </label>
                                                    @if ($v->type == 'textarea')
                                                        <textarea name="{{ $k }}" class="form-control" @if ($v->validation == 'required') required @endif>{{ old($k) }}</textarea>
                                                    @elseif ($v->type == 'file')
                                                        <input name="{{ $k }}" type="file" class="form-control" @if ($v->validation == 'required') required @endif />
                                                    @else
                                                        <input name="{{ $k }}" type="{{ $v->type }}" class="form-control" value="{{ $v->field_value }}" @if ($v->validation == 'required') required @endif />
                                                    @endif
                                                    @error($k)
                                                    <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                        @empty
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        @endif
                        <button type="submit" class="cmn-btn ">@lang('Re Submit')</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

