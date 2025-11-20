@extends($theme.'layouts.user')
@section('title',__('Request New Card'))

@section('content')
    <div class="dashboard-wrapper">
        <a href="{{ route('user.virtual.card') }}" class="back-btn mb-20">
            <i class="fa-regular fa-angle-left"></i>@lang('Back To Card List')</a>
        <div class="col-xxl-6 col-xl-10 mx-auto">
                    <!------ info text------>
            <div class="alert alert-info alert-dismissible" role="alert">
                <div class="icon-area"><i class="fa-light fa-info-circle"></i></div>
                <div class="text-area">
                    <div class="description">@lang(strip_tags($virtualCardMethod->info_box))</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"><i class="fa-regular fa-xmark"></i></button>
            </div>
            <div class="card mb-4 card-primary shadow">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h4 class="m-0 font-weight-bold ">@lang('Request For') {{$virtualCardMethod->name}} @lang('Card')</h4>
                </div>
                <div class="card-body">
                    <form action="{{route('user.virtual.card.orderSubmit')}}" method="post"
                          enctype="multipart/form-data">
                        @csrf

                        @if($virtualCardMethod)
                        <label class="form-label">@lang('Card Currency ')</label>
                            @foreach($virtualCardMethod->currency as $singleCurrency)
                                <input type="radio" class="btn-check" name="currency" id="currency_{{ $singleCurrency }}" value="{{ $singleCurrency }}" checked>
                                <label class="btn btn-outline-success" for="currency_{{ $singleCurrency }}">{{ $singleCurrency }}</label>
                            @endforeach
                        @endif
                        @if(isset($virtualCardMethod->form_field))
                            <div class="row">
                                @forelse($virtualCardMethod->form_field as $k => $v)
                                    <div class="col-md-6 mt-3">
                                        <div class="form-group">
                                            <label class="form-label">
                                                {{ trans($v->field_level) }}
                                                @if ($v->validation == 'required')
                                                    <span class="text-danger">*</span>
                                                @endif
                                            </label>
                                            @if ($v->type == 'textarea')
                                                <textarea name="{{ $k }}" class="form-control" @if ($v->validation == 'required') required @endif>{{ old($k) }}</textarea>
                                            @elseif ($v->type == 'file')
                                                <input name="{{ $k }}" type="file" class="form-control" placeholder="{{ trans($v->field_place) }}"
                                                       @if ($v->validation == 'required') required @endif />
                                            @else
                                                <input name="{{ $k }}" type="{{ $v->type }}" class="form-control" value="{{ old($k) }}" placeholder="{{ trans($v->field_place) }}"
                                                       @if ($v->validation == 'required') required @endif />
                                            @endif
                                            @error($k)
                                            <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                @empty
                                @endforelse
                            </div>
                        @endif
                        <button type="submit" class="cmn-btn mt-4">@lang('Apply')</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection

