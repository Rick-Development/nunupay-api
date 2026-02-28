@extends($theme.'layouts.user')
@section('title',trans('View Ticket'))
@section('content')
    <div class="dashboard-wrapper">
        <a href="{{ route('user.ticket.list') }}" class="back-btn mb-30">
            <i class="fa-regular fa-angle-left"></i>@lang('Back to Ticket List')
        </a>

        <div class="col-xxl-8 col-lg-10 mx-auto">

            <div class="card message_section">
                <div class="card-header">
                    <h4 class="float-start title">Ticket #{{$ticket->ticket}} | {{$ticket->subject}}</h4>
                    <div class="float-end">
                        {!! $ticket->getStatusBadge() !!}
                        @if($ticket->status != 3)
                        <form method="post" action="{{ route('user.ticket.reply',$ticket->id) }}" class="d-inline">
                            @csrf
                            @method('PUT')
                            <button class="mx-2 btn btn-outline-danger" type="submit" name="replyTicket" value="2">
                                <i class="bi bi-x-square"></i> @lang('Close')
                            </button>
                        </form>
                        @endif

                    </div>
                </div>

                <div class="message-wrapper">
                    <div class="row g-lg-0">
                        <div class="col-12">
                            <div class="inbox-wrapper">

                                <!-- chat -->
                                @if(count($ticket->messages) > 0)
                                    <div class="chats">
                                        @foreach($ticket->messages as $item)
                                            @if($item->admin_id == null)
                                                <div class="chat-box this-side">
                                                    <div class="chat-box this-side">
                                                        <div class="text-wrapper">
                                                            <div class="text mx-2">
                                                                <p>{{$item->message}}</p>
                                                            </div>
                                                            @if(0 < count($item->attachments))
                                                                <div class="attachment-wrapper">
                                                                    @foreach($item->attachments as $k=> $file)
                                                                        <a class="attachment"
                                                                           href="{{route('user.ticket.download',encrypt($file->id))}}"
                                                                           data-fancybox="gallery">
                                                                            <i class="fa fa-file"></i> @lang('File') {{++$k}}
                                                                        </a>
                                                                    @endforeach
                                                                </div>
                                                            @endif
                                                            <small
                                                                class="time"> {{dateTime($item->created_at, 'd M, y h:i A')}}</small>
                                                        </div>
                                                        <div class="img">
                                                            <img height="50" width="50" class="img-fluid"
                                                                 src="{{ getFile($user->image_driver, $user->image) }}"
                                                                 alt=""/>
                                                        </div>
                                                    </div>
                                                </div>
                                            @else
                                                <div class="chat-box opposite-side">
                                                    <div class="img">
                                                        <img class="img-fluid" height="50" width="50"
                                                             src="{{ getFile($item->admin->image_driver, $item->admin->image) }}"
                                                             alt=""/>
                                                    </div>
                                                    <div class="text-wrapper">
                                                        <div class="text">
                                                            <p>{{$item->message}}</p>
                                                        </div>
                                                        <div class="attachment-wrapper">
                                                            @foreach($item->attachments as $k=> $file)
                                                                <a class="attachment"
                                                                   href="{{route('user.ticket.download',encrypt($file->id))}}"
                                                                   data-fancybox="gallery">
                                                                    <i class="fa fa-file"></i> @lang('File') {{++$k}}
                                                                </a>
                                                            @endforeach
                                                        </div>
                                                        <small
                                                            class="time">{{dateTime($item->created_at, 'd M, y h:i A')}}</small>
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif

                                <!-- type area -->
                                <div class="typing-area">
                                    <form class="form-row "
                                          action="{{ route('user.ticket.reply', $ticket->id)}}"
                                          method="post"
                                          enctype="multipart/form-data">
                                        @csrf
                                        @method('PUT')

                                        <div class="input-field  col-4 h-auto mb-2">
                                            <div class="input-images @error('images') is-invalid @enderror" id="image"
                                                 name="images[]">
                                            </div>

                                            @error('images')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                                        </div>
                                        <div class="input-group">

                                            <input type="text"
                                                   class="form-control @error('reply_ticket') is-invalid @enderror"
                                                   name="message" value="{{old('message')}}"
                                                   autocomplete="off"/>
                                            <button type="submit" class="submit-btn" name="replyTicket"
                                                    value="1">
                                                <i class="fal fa-paper-plane text-success"></i>
                                            </button>
                                        </div>
                                        @error('message')
                                        <span class="invalid-feedback d-block">{{ $message }}</span>
                                        @enderror
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
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
