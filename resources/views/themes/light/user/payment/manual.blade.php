@extends($theme.'layouts.user')
@section('title')
    {{ 'Pay with '.optional($deposit->gateway)->name ?? '' }}
@endsection

@section('content')
    <div class="dashboard-wrapper">

        <div class="col-xxl-8 col-lg-10 mx-auto">
        <div class="breadcrumb-area"><h3 class="title">@yield('title')</h3></div>


                <div class="card">
                    <div class="card-header">
                        <h4 class="title d-flex justify-content-center">{{trans('Please follow the instruction below')}}</h4>
                        <p class="text-center mt-2 ">{{trans('You have requested to deposit')}}
                            <b class="text-base">{{currencyPosition($deposit->payable_amount_in_base_currency)}}</b> , {{trans('Please pay')}}
                            <b class="text-base">{{ getAmount($deposit->amount) }} {{$deposit->payment_method_currency}}</b> , {{trans('for successful payment')}}
                        </p>
                        <p class="text-justify mt-2 ">
                            <?php echo optional($deposit->gateway)->note; ?>
                        </p>
                    </div>
                    <div class="card-body">
                        <form action="{{route('addFund.fromSubmit',$deposit->trx_id)}}" method="post"
                              enctype="multipart/form-data"
                              class="form-row  preview-form">
                            @csrf
                            @if(optional($deposit->gateway)->parameters)
                                @foreach($deposit->gateway->parameters as $k => $v)
                                    @if($v->type == "text")
                                        <div class="col-md-12 mt-2">
                                            <div class="form-group  ">
                                                <label
                                                    class="form-label">{{trans($v->field_label)}} @if($v->validation == 'required')
                                                        <span class="text-danger">*</span>
                                                    @endif </label>
                                                <input type="text" name="{{$k}}"
                                                       class="form-control bg-transparent"
                                                       @if($v->validation == "required") required @endif>
                                                @if ($errors->has($k))
                                                    <span
                                                        class="text-danger">{{ trans($errors->first($k)) }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    @elseif($v->type == "number")
                                        <div class="col-md-12 mt-2">
                                            <div class="form-group  ">
                                                <label
                                                    class="form-label">{{trans($v->field_label)}} @if($v->validation == 'required')
                                                        <span class="text-danger">*</span>
                                                    @endif </label>
                                                <input type="text" name="{{$k}}"
                                                       class="form-control bg-transparent"
                                                       @if($v->validation == "required") required @endif>
                                                @if ($errors->has($k))
                                                    <span
                                                        class="text-danger">{{ trans($errors->first($k)) }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    @elseif($v->type == "textarea")
                                        <div class="col-md-12 mt-2">
                                            <div class="form-group">
                                                <label
                                                    class="form-label">{{trans($v->field_label)}} @if($v->validation == 'required')
                                                        <span class="text-danger">*</span>
                                                    @endif </label>
                                                <textarea name="{{$k}}" class="form-control bg-transparent"
                                                          rows="3"
                                                          @if($v->validation == "required") required @endif></textarea>
                                                @if ($errors->has($k))
                                                    <span class="text-danger">{{ trans($errors->first($k)) }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    @elseif($v->type == "file")
                                        <div class="col-md-12">
                                            <div class="upload-image w-25 mt-4 mb-3">
                                                <div class="btn-area">
                                                    <div class="btn-area-inner d-flex">
                                                        <div class="cmn-file-input">
                                                            <label for="image" class="form-label d-inline">
                                                                <i class="fal fa-camera-alt"></i>
                                                                {{ trans('Upload') }} {{trans($v->field_label)}}</label>
                                                            @if($v->validation == 'required')
                                                                <span class="text-danger">*</span>
                                                            @endif
                                                            <input name="{{$k}}" class="form-control" type="file"
                                                                   id="image"
                                                                   @if($v->validation == "required") required @endif
                                                                   onchange="previewImage('profile')">
                                                        </div>
                                                    </div>
                                                    <div class="preview-img">
                                                        <div class="image-area">
                                                            <img id="profile"
                                                                 src="{{asset(config('filelocation.default2'))}}"
                                                                 alt="image" class="rounded-1">
                                                        </div>
                                                    </div>
                                                    @error($k)
                                                    <span class="text-danger">@lang($message)</span>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            @endif

                            <div class="col-md-12 ">
                                <div class=" form-group">
                                    <button type="submit" class="btn cmn-btn w-100 mt-3">
                                        <span>@lang('Confirm Now')</span>
                                    </button>
                                </div>
                            </div>

                        </form>
                    </div>
                </div>

        </div>
    </div>
@endsection

@push('script')
    <script>
        'use strict'
        $(document).on("change", '#image', function () {
            let reader = new FileReader();
            reader.onload = (e) => {
                $('#image_preview_container').attr('src', e.target.result);
            }
            reader.readAsDataURL(this.files[0]);
        });
    </script>
@endpush
