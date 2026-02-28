@extends($theme.'layouts.user')
@section('title',trans('Create Ticket'))
@section('content')
    <div class="dashboard-wrapper">
        <a href="{{ route('user.ticket.list') }}" class="back-btn mb-30">
            <i class="fa-regular fa-angle-left"></i>@lang('Back to Ticket List')
        </a>

        <div class="col-xxl-8 col-lg-10 mx-auto">

            <div class="card">
                <div class="card-header">
                    <h4 class="title">{{ trans('create a new ticket') }}</h4>
                </div>
                <div class="card-body">
                    <form class="form-row" action="{{route('user.ticket.store')}}" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="col-md-12">
                            <div class="form-group mb-2 mx-5">
                                <label class="form-label">@lang('Subject')</label>
                                <input class="form-control" type="text" name="subject"
                                       value="{{old('subject')}}" placeholder="@lang('Money Transfer Problem')">
                                @error('subject')<div class="error text-danger">@lang($message) </div>@enderror
                            </div>

                            <div class="form-group mb-2 mx-5">
                                <label class="form-label">@lang('Message')</label>
                                <textarea class="form-control ticket-box" name="message" rows="5" id="textarea"
                                          placeholder="@lang('Describe your problem... ')">{{old('message')}}</textarea>
                                @error('message')<div class="error text-danger">@lang($message) </div>@enderror
                            </div>

                            <h5 class="card-header-title mb-2 mx-5">@lang('Media')</h5>
                            <div class="input-field  mx-5">
                                <div class="input-images @error('images') is-invalid @enderror" id="image"
                                     name="images[]">
                                </div>

                                @error('images')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            </div>

                            <div class="form-group mt-3 mx-5">
                                <button type="submit" class="cmn-btn">{{ trans('submit') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
@endsection

@push('style')
    <link rel="stylesheet" href="{{ asset('assets/global/css/image-uploader.css') }}"/>
@endpush

@push('js-lib')
    <script src="{{ asset('assets/global/js/image-uploader.js') }}"></script>
@endpush

@push('script')
    <script>
        $(document).ready(function () {
            $('.input-images').imageUploader();
        });
    </script>
@endpush
