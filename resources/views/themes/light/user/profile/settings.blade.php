@extends($theme.'layouts.user')
@section('title', 'Settings')

@section('content')
    <div class="dashboard-wrapper">
        <div class="col-xxl-8 col-lg-10 mx-auto">
            <div class="breadcrumb-area">
                <h3 class="title">@lang('Settings')</h3>
            </div>
            <div class="card">
                <div class="card-body">
                    <div class="settings-accordion">
                        <div class="accordion" id="accordionExample2">

                            <div class="accordion-item">
                                <h2 class="accordion-header" id="notification">
                                    <button class="accordion-button collapsed" type="button"
                                            data-bs-toggle="collapse" data-bs-target="#notificationPermission"
                                            aria-expanded="true"
                                            aria-controls="notificationPermission">
                                        <div class="sm-circle">
                                            <i class="fa-sharp fa-light fa-bell"></i>
                                        </div>
                                        <div class="content-area">
                                            <h6 class="title">{{ trans('Notifications') }}</h6>
                                            <p class="mb-0">{{ trans('Choose what we get in touch about') }}</p>
                                        </div>
                                    </button>
                                </h2>
                                <div id="notificationPermission" class="accordion-collapse collapse"
                                     aria-labelledby="notification" data-bs-parent="#accordionExample2">
                                    <div class="accordion-body">
                                        <form action="{{ route('user.notification.permission.store') }}" method="post">
                                            @csrf
                                            <div class="cmn-table">
                                                <div class="table-responsive overflow-hidden ">
                                                    <table class="table align-middle table-striped mx-2">
                                                        <thead>
                                                        <tr>
                                                            <th scope="col">{{ trans('Notifications') }}</th>
                                                            <th scope="col">{{ trans('Email') }}</th>
                                                            <th scope="col">{{ trans('SMS') }}</th>
                                                            <th scope="col">{{ trans('Push') }}</th>
                                                            <th scope="col">{{ trans('in App') }}</th>
                                                        </tr>
                                                        </thead>
                                                        <tbody>

                                                        @foreach($notification as $key=>$item)
                                                            <tr>
                                                                <td data-label="Name">
                                                                    <p>{{$item->name}}</p>
                                                                </td>
                                                                <td data-label="Email">
                                                                    <div class="form-check form-switch">
                                                                        <input class="form-check-input" type="checkbox"
                                                                               role="switch" name="email_key[]"
                                                                               value="{{$item->template_key ?? ""}}"
                                                                               {{ !$item->email ? 'disabled':'' }}
                                                                               id="emailSwitch"
                                                                            {{ in_array($item->template_key, $userNotificationPermission->template_email_key ?? []) ? 'checked' : '' }}
                                                                        >
                                                                    </div>
                                                                </td>
                                                                <td data-label="SMS">
                                                                    <div class="form-check form-switch">
                                                                        <input class="form-check-input" type="checkbox"
                                                                               role="switch" name="sms_key[]"
                                                                               value="{{$item->template_key ?? ""}}"
                                                                               {{ !$item->sms ? 'disabled':'' }}
                                                                               id="smsSwitch"
                                                                            {{ in_array($item->template_key, $userNotificationPermission->template_sms_key ?? []) ? 'checked' : '' }}
                                                                        >
                                                                    </div>
                                                                </td>
                                                                <td data-label="Push">
                                                                    <div class="form-check form-switch">
                                                                        <input class="form-check-input" type="checkbox"
                                                                               role="switch"
                                                                               name="push_key[]"
                                                                               value="{{ $item->template_key ?? "" }}"
                                                                               {{ !$item->push ? 'disabled' : '' }}
                                                                               id="pushSwitch"
                                                                            {{ in_array($item->template_key, $userNotificationPermission->template_push_key ?? []) ? 'checked' : '' }}
                                                                        >
                                                                    </div>
                                                                </td>
                                                                <td data-label="In App">
                                                                    <div class="form-check form-switch">
                                                                        <input class="form-check-input" type="checkbox"
                                                                               role="switch"
                                                                               name="in_app_key[]"
                                                                               value="{{$item->template_key ?? ""}}"
                                                                               id="appSwitch"
                                                                            {{!$item->in_app ? 'disabled':''}}
                                                                            {{ in_array($item->template_key, $userNotificationPermission->template_in_app_key ?? []) ? 'checked' : '' }}
                                                                        >
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                            <p class="mb-0 mt-15">
                                                {{ trans('There are some things that we will always need to tell you about – like
                                                changes to our T&Cs.') }}
                                            </p>
                                            <div class="mt-15">
                                                <button type="submit"
                                                        class="cmn-btn">{{ trans('Save Changes') }}</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingFive">
                                    <button class="accordion-button collapsed" type="button"
                                            data-bs-toggle="collapse" data-bs-target="#collapseFive"
                                            aria-expanded="false" aria-controls="collapseFive">
                                        <div class="sm-circle">
                                            <i class="fa-light fa-lock-keyhole"></i>
                                        </div>
                                        <div class="content-area">
                                            <h6 class="title">{{ trans('Change password') }}</h6>
                                            <p class="mb-0">********</p>
                                        </div>
                                    </button>
                                </h2>
                                <div id="collapseFive"
                                     class="accordion-collapse collapse {{ $errors->has('password') ? 'show' : '' }}"
                                     aria-labelledby="headingFive" data-bs-parent="#accordionExample2">
                                    <form action="{{ route('user.updatePassword') }}" method="post">
                                        @csrf
                                        <div class="accordion-body">
                                            <div class="alert alert-info alert-dismissible" role="alert">
                                                <div class="sm-circle"><i
                                                        class="fa-light fa-info-circle"></i></div>
                                                <div class="text-area">
                                                    <div class="title">{{ trans('info') }}</div>
                                                    <div
                                                        class="description">{{ trans('Enter a strong password!') }}</div>
                                                </div>
                                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                                        aria-label="Close"><i class="fa-regular fa-xmark"></i>
                                                </button>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label"
                                                       for="currentPassword">{{ trans('Current password') }}</label>
                                                <div class="password-box">
                                                    <input name="current_password" type="password"
                                                           id="currentPassword" class="form-control password"
                                                           value="{{ old('current_password') }}"
                                                           placeholder="{{ trans('Current Password') }}"
                                                           autocomplete="off">
                                                    <i class="password-icon fa-regular fa-eye toggle-password"></i>
                                                </div>
                                                @error('current_password')<span
                                                    class="error text-danger">{{ $message }}</span>@enderror
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label"
                                                       for="newPassword">{{ trans('Your password') }}</label>
                                                <div class="password-box">
                                                    <input name="password" type="password" id="newPassword"
                                                           class="form-control password"
                                                           value="{{ old('password') }}"
                                                           placeholder="{{ trans('Enter a new Password') }}"
                                                           autocomplete="off">
                                                    <i class="password-icon fa-regular fa-eye toggle-password"></i>
                                                </div>
                                                @error('password')<span
                                                    class="error text-danger">{{ $message }}</span>@enderror
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label"
                                                       for="confirmPassword">{{ trans('Confirm password') }}</label>
                                                <div class="password-box">
                                                    <input name="password_confirmation" type="password"
                                                           id="confirmPassword" class="form-control password"
                                                           placeholder="{{ trans('Confirm Your Password') }}"
                                                           autocomplete="off">
                                                    <i class="password-icon fa-regular fa-eye toggle-password"></i>
                                                </div>
                                                @error('password_confirmation')<span
                                                    class="error text-danger">{{ $message }}</span>@enderror
                                            </div>

                                            <button type="submit" class="cmn-btn mt-3">{{ trans('Update Password') }}</button>

                                        </div>
                                    </form>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headinOne">
                                    <button class="accordion-button collapsed" type="button"
                                            data-bs-toggle="collapse" data-bs-target="#collapseSeven"
                                            aria-expanded="true" aria-controls="collapseSeven">
                                        <div class="sm-circle">
                                            <i class="fa-light fa-mobile"></i>
                                        </div>
                                        <div class="content-area">
                                            <h6 class="title">{{ trans('2-step verification') }}</h6>
                                            <p class="mb-0">
                                                {{ trans('Manage your 2-step verification methods.') }}
                                            </p>
                                        </div>
                                    </button>
                                </h2>
                                <div id="collapseSeven" class="accordion-collapse collapse"
                                     aria-labelledby="headinOne" data-bs-parent="#accordionExample2">
                                    <div class="accordion-body">
                                        <div>
                                            <a href="{{ route('user.twostep.security') }}"
                                               class="cmn-btn">{{ trans('Change 2-step verification settings') }}</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headinOne">
                                    <button class="accordion-button collapsed" type="button"
                                            data-bs-toggle="collapse" data-bs-target="#collapseNine"
                                            aria-expanded="true" aria-controls="collapseNine">
                                        <div class="sm-circle">
                                            <i class="fa-sharp fa-light fa-right-from-bracket"></i>
                                        </div>
                                        <div class="content-area">
                                            <h6 class="title">{{ trans('Log out of all devices') }}</h6>
                                            <p class="mb-0">
                                                {{ trans('If you notice any suspicious activity, log out of across all devices and browsers') }}
                                            </p>
                                        </div>
                                    </button>
                                </h2>
                                <div id="collapseNine" class="accordion-collapse collapse"
                                     aria-labelledby="headinOne" data-bs-parent="#accordionExample2">
                                    <div class="accordion-body">
                                        <div>
                                            <form action="{{ route('user.logout.from.all.devices') }}" method="POST">
                                                @csrf
                                                <button type="submit" class="cmn-btn2">
                                                    <i class="fa-sharp fa-light fa-right-from-bracket"></i>
                                                    {{ trans('Logout from all devices') }}</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headinOne">
                                    <button class="accordion-button collapsed" type="button"
                                            data-bs-toggle="collapse" data-bs-target="#collapseTwelve"
                                            aria-expanded="true" aria-controls="collapseTwelve">
                                        <div class="sm-circle">
                                            <i class="fa-light fa-circle-xmark"></i>
                                        </div>
                                        <div class="content-area">
                                            <h6 class="title">{{ trans('Close your account') }}</h6>
                                            <p class="mb-0">
                                                @lang("Hey :user, we're sorry to see you leave.", ['user' => $user->fullname()])


                                            </p>
                                        </div>
                                    </button>
                                </h2>
                                <div id="collapseTwelve" class="accordion-collapse collapse"
                                     aria-labelledby="headinOne" data-bs-parent="#accordionExample2">
                                    <div class="accordion-body">
                                        <div class="d-flex align-items-center gap-3">
                                            <p class="mb-0">
                                                @lang("Please empty your wallets before close the account")
                                            </p>
                                            <a type="button" data-bs-target="#closeAccountModal"
                                               data-bs-toggle="modal" class=" cmn-btn2">{{ trans('CONTINUE') }}<i class="fa-regular fa-arrow-right"></i></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>

@endsection

@push('loadModal')
    <!-- Modal -->
    <div class="modal fade" id="closeAccountModal" tabindex="-1" aria-labelledby="closeAccountModalLabel"
         aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="closeAccountModalLabel">{{ trans('Close your account') }}</h5>
                    <button type="button" class="cmn-btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="fa-light fa-xmark"></i></button>
                </div>
                <div class="modal-body">
                    <p>@lang("Hey :user, are you sure, you want to delete this account?",['user' => $user->fullname()])</p>
                </div>

                <div class="modal-footer">
                    <button type="button" class="cmn-btn3" data-bs-dismiss="modal">{{ trans('No') }}</button>
                    <a href="{{ route('user.delete.account') }}" class="delete-btn">{{ trans('Yes') }}</a>
                </div>
            </div>
        </div>
    </div>


    <!-- Edit Modal -->
    <div id="editModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="edit" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="Edit-Blog-Category">@lang('Change Email Address')</h5>
                    <button type="button" class="cmn-btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="fa-light fa-xmark"></i></button>
                </div>
                <form action="{{ route('user.update.email', $user) }}" method="post">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="form-label">@lang('Email')</label>
                            <input type="text" name="email" value="{{ old('email',$user->email) }}"
                                   class="edit-name form-control @error('name') is-invalid @enderror">
                            @error('email')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="cmn-btn3"
                                data-bs-dismiss="modal">@lang('Close')</button>
                        <button type="submit" class="cmn-btn">@lang('Save changes')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- End Edit Modal -->

@endpush


@push('script')
    <script>

        $(document).ready(() => {
            $('.toggle-password').on('click', function () {
                const passwordInput = $(this).prev('.password');
                const passwordType = passwordInput.attr('type');

                passwordInput.attr('type', passwordType === 'password' ? 'text' : 'password');
                $(this).toggleClass('fa-eye-slash', passwordType === 'password');
            });
        });
    </script>
@endpush

