@extends($theme.'layouts.user')
@section('title',trans('Support Ticket'))
@section('content')
    <div class="dashboard-wrapper">
        <div class="col-xxl-8 col-lg-10 mx-auto">
            <div class="breadcrumb-area">
                <h4 class="title">@lang('Support Ticket')</h4>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between  g-4">
                    <h5 class="title">{{ trans('Ticket List') }}</h5>
                    <div class="btn-area d-flex justify-content-between gap-2">
                        <a href="{{route('user.ticket.create')}}" class="cmn-btn2 g-2">
                            <i class="fa-regular fa-plus-circle"></i>{{ trans('Create ticket') }}
                        </a>

                        <button type="button" class="cmn-btn"
                                data-bs-toggle="offcanvas"
                                data-bs-target="#offCanvas"
                                aria-controls="offCanvas"><i class="fa-light fa-magnifying-glass"></i>{{ trans('Filter') }}
                        </button>

                    </div>
                </div>
                <div class="card-body">
                    <div class="cmn-table">
                        <div class="table-responsive overflow-hidden">

                            <table class="table table-striped">
                                <thead>
                                <tr>
                                    <th scope="col">@lang('Ticket No.')</th>
                                    <th scope="col">@lang('Subject')</th>
                                    <th scope="col">@lang('Status')</th>
                                    <th scope="col">@lang('Last Reply')</th>
                                    <th scope="col">@lang('Action')</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($tickets as $key => $ticket)
                                    <tr>
                                        <td data-label="@lang('Ticket No.')"><h6 >{{ trans('Ticket #').$ticket->ticket }}</h6></td>
                                        <td data-label="@lang('Subject')">@lang($ticket->subject)</td>
                                        <td data-label="@lang('Status')">
                                            {!! $ticket->getStatusBadge() !!}
                                        </td>
                                        <td data-label="@lang('Last Reply')">
                                            {{diffForHumans($ticket->last_reply) }}
                                        </td>
                                        <td data-label="@lang('Action')">
                                            <a href="{{ route('user.ticket.view', $ticket->ticket) }}"
                                               class="cmn-btn3" title="{{ trans('view') }}">
                                                <i class="fa-regular fa-eye"></i> @lang('view')
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <td colspan="10" class="text-center">
                                        <img id="notFoundImage" src="" alt="@lang('No Data Found')" class="text-center w-25">
                                        <p class="mt-2">@lang('No Data Found')</p>
                                    </td>
                                @endforelse
                                </tbody>
                            </table>
                            {{ $tickets->appends($_GET)->links($theme.'partials.pagination') }}

                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- offCanvas sidebar start -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offCanvas" aria-labelledby="offCanvasLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="offCanvasLabel"><i class="fa-light fa-magnifying-glass me-2"></i>{{ trans('Search') }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <form action="" method="get">
                <div class="row g-4">
                    <div>
                        <label for="ticket" class="form-label">{{ trans('ticket No.') }}</label>
                        <input name="ticket" type="text" class="form-control" id="ticket"
                               value="{{ old('ticket', request()->ticket) }}" placeholder="497757"
                               onkeyup="this.value = this.value.replace (/^\.|[^\d\.]/g, '')">
                    </div>
                    <div>
                        <label for="subject" class="form-label">{{ trans('Subject') }}</label>
                        <input name="subject" value="{{ old('subject', request()->subject) }}" type="text"
                               class="form-control" id="subject" placeholder="transfer not working">
                    </div>
                    <div>
                        <label class="form-label">{{ trans('status') }}</label>
                        <select class="cmn-select2" name="status">
                            <option value="">{{ trans('All status') }}</option>
                            <option value="0"
                                    @if(request()->status == '0') selected @endif>{{ trans('Open ') }}</option>
                            <option value="1"
                                    @if(request()->status == '1') selected @endif>{{ trans('Answered') }}</option>
                            <option value="2"
                                    @if(request()->status == '2') selected @endif>{{ trans('Replied') }}</option>
                            <option value="3"
                                    @if(request()->status == '3') selected @endif>{{ trans('Closed') }}</option>
                        </select>
                    </div>
                    <div class="btn-area">
                        <button type="submit" class="cmn-btn">
                            <i class="fa-light fa-magnifying-glass me-2"></i>{{ trans('Search') }}</button>
                    </div>
                </div>

            </form>
        </div>
    </div>
    <!-- Offcanvas sidebar end -->
@endsection
