<!-- Nav section start -->
<nav class="navbar fixed-top navbar-expand-lg">
    <div class="container-fluid">
        <a class="navbar-brand logo" href="{{ url('/') }}">
            <img src="{{ getFile(basicControl()->logo_driver, basicControl()->logo) }}" alt=""/>
        </a>
        <button class="navbar-toggler d-none d-lg-block" type="button" data-bs-toggle="offcanvas"
                data-bs-target="#offcanvasNavbar" aria-controls="offcanvasNavbar">
            <i class="fa-light fa-list"></i>
        </button>
        <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasNavbar" aria-labelledby="offcanvasNavbar">
            <div class="offcanvas-header">
                <a class="navbar-brand" href="{{ url('/') }}">
                    <img src="{{ getFile(basicControl()->logo_driver, basicControl()->logo) }}" alt=""/>
                </a>
                <button type="button" class="cmn-btn-close" data-bs-dismiss="offcanvas" aria-label="Close">
                    <i class="fa-light fa-arrow-right"></i></button>
            </div>

            <div class="offcanvas-body align-items-center justify-content-between">
                <ul class="navbar-nav m-auto">
                    <li class="nav-item">
                        <a class="nav-link {{ isMenuActive(['user.dashboard']) }}" aria-current="page" href="{{ route('user.dashboard') }}">
                            <i class="fa-regular fa-grid-2"></i>@lang('Dashboard')</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{isMenuActive(['user.transferAmount','user.transferRecipient','user.transferReview','user.transferPay'])}}"
                           href="{{ route("user.transferAmount") }}"> <i class="fa-regular fa-money-bill-alt"></i>@lang('Send')</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{isMenuActive(['user.recipient.index','user.recipient.create','user.recipient.edit'])}}"
                           href="{{route('user.recipient.index')}}"><i class="fa-light fa-user-group"></i><span>@lang('Recipients')</span></a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ isMenuActive(['user.virtual.card','user.virtual.card.order','user.virtual.card.orderReSubmit'])}} "
                           href="{{route('user.virtual.card')}}"><i class="fa-light fa-credit-card"></i><span>@lang('Card')</span></a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ isMenuActive(['user.add.fund','user.payment.process'])}}"
                           href="{{ route('user.add.fund') }}"><i class="fa-regular fa-wallet"></i>@lang('Deposit')</a>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ menuActive([
                            'user.transferList','user.fund.index','user.allTransaction','user.moneyRequestList'
                            ], 3) }}" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fa-regular fa-timer"></i>
                            @lang('history')
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item {{ isMenuActive('user.allTransaction') }}" href="{{ route("user.allTransaction") }}">@lang('Transaction History')</a></li>
                            <li><a class="dropdown-item {{ isMenuActive('user.fund.index') }}" href="{{ route('user.fund.index') }}">@lang('Deposit History')</a></li>
                            <li><a class="dropdown-item {{ isMenuActive('user.transferList') }}" href="{{ route("user.transferList") }}">@lang('Transfer History')</a></li>
                            <li><a class="dropdown-item {{ isMenuActive('user.moneyRequestList') }}" href="{{ route("user.moneyRequestList") }}">@lang('Request History')</a></li>
                        </ul>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ menuActive([
                            'user.settings','user.ticket.list','user.ticket.create','user.ticket.view','user.earn'
                            ], 3) }}"
                           href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fa-regular fa-grid-2-plus"></i>
                            @lang('More')
                        </a>
                        <ul class="dropdown-menu ">
                            <li><a class="dropdown-item {{ isMenuActive('user.settings') }}" href="{{ route('user.settings') }}">
                                    <i class="fa-light fa-gear"></i>@lang('Settings')</a>
                            </li>
                            <li><a class="dropdown-item {{ isMenuActive('user.ticket.list') }}" href="{{ route("user.ticket.list") }}">
                                    <i class="fa-brands fa-rocketchat"></i>
                                    @lang('Support Ticket')
                                </a>
                            </li>
                            <li><a class="dropdown-item {{ isMenuActive(['user.earn','user.referList'])}}"
                                   href="{{ route('user.earn') }}">
                                    <i class="fa-regular fa-gift"></i>@lang('Refer & Earn')
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>

        <div class="nav-right ">
            <ul class="custom-nav header-nav">
                <li>
                    <a id="toggle-btn" class="nav-link d-flex toggle-btn">
                        <i class="fa-light fa-moon" id="moon"></i>
                        <i class="fa-light fa-sun-bright" id="sun"></i>
                    </a>
                </li>

                <li class="nav-item dropdown" id="pushNotificationArea">
                    <a class="nav-link nav-icon mt-2" href="#" data-bs-toggle="dropdown">
                        <i class="fa-light fa-bell"></i>
                        <span class="badge badge-number" v-cloak>@{{items.length}}</span>
                    </a>

                    <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow notifications">
                        <div class="dropdown-header">
                            <h6> {{ trans('You have') }} <span
                                    v-cloak>@{{items.length}}</span> {{ trans('new notifications') }}</h6>
                        </div>
                        <div class="dropdown-body">
                            <div class="notification-item" v-for="(item, index) in items"
                                 @click.prevent="readAt(item.id, item.description.link)">
                                <a href="javascript:void(0)">
                                    <i class="fa-light fa-bell text-warning"></i>
                                    <div>
                                        <p class="text-highlight-dark" v-cloak v-html="item.description.text"></p>
                                        <p v-cloak>@{{ item.formatted_date }}</p>
                                    </div>
                                </a>
                            </div>
                        </div>

                        <div class="dropdown-footer">
                            <a class="btn-view_notification" href="javascript:void(0)"
                               v-if="items.length == 0">@lang('You have no notifications')</a>
                            <a class="btn-clear" href="javascript:void(0)" v-if="items.length > 0"
                               @click.prevent="readAll">@lang('Clear all notification')</a>
                        </div>
                    </div>
                </li><!-- End Notification Nav -->



                <li class="nav-item dropdown">

                    <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
                        <img src="{{auth()->user()->getImage()}}" alt="Profile" class="rounded-circle">
                    </a>

                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
                        <li class="dropdown-header d-flex justify-content-center text-start">
                            <div class="profile-thum">
                                <img src="{{auth()->user()->getImage()}}" alt="">
                            </div>
                            <div class="profile-content">
                                <h6>{{ auth()->user()->fullname() }}</h6>
                                @<span>{{ Auth::user()->username }}</span>
                            </div>
                        </li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center" href="{{ route('user.dashboard') }}">
                                <i class="fa-light fa-house"></i>
                                <span>{{ trans('Dashboard') }}</span>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center" href="{{ route('user.profile') }}">
                                <i class="fa-light fa-gear"></i>
                                <span>{{ trans('Profile Settings') }}</span>
                            </a>
                        </li>

                        @auth
                            <li>
                                <a class="dropdown-item d-flex align-items-center" href="{{ route('logout') }}"
                                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();"><i
                                        class="fal fa-sign-out-alt"></i>@lang('Sign Out')</a>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                    @csrf
                                </form>
                            </li>
                        @endauth
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
<!-- Nav section end -->

<!-- Bottom Mobile Tab nav section start -->

<ul class="nav bottom-nav fixed-bottom d-lg-none">
    <li class="nav-item">
        <a onclick="toggleSideMenu()" class="nav-link toggle-sidebar" aria-current="page">
            <i class="fa-light fa-list"></i></a></li>

    <li class="nav-item">
        <a class="nav-link @active('user.add.fund')" href="{{ route('user.add.fund') }}" title="@lang('Deposit')"><i class="fa-light fa-chart-line-up"></i></a></li>

    <li class="nav-item">
        <a class="nav-link @active('user.dashboard')" href="{{ route('user.dashboard') }}" title="@lang('Dashboard')">
            <i class="fa-light fa-house"></i></a></li>

    <li class="nav-item">
        <a class="nav-link @active('user.transferAmount','user.transferRecipient','user.transferReview','user.transferPay')"
           href="{{ route('user.transferAmount') }}" title="@lang('Money Transfer')">
            <i class="fa-regular fa-money-bill-1-wave"></i></a></li>

    <li class="nav-item">
        <a class="nav-link @active('user.profile')" href="{{ route('user.profile') }}" title="@lang('Profile')">
            <i class="fa-light fa-user"></i></a>
    </li>
</ul>


@push('script')
    <script>
        let pushNotificationArea = new Vue({
            el: "#pushNotificationArea",
            data: {
                items: [],
            },
            beforeMount() {
                this.getNotifications();
                this.pushNewItem();
            },
            methods: {
                getNotifications() {
                    let app = this;
                    axios.get("{{ route('user.push.notification.show') }}")
                        .then(function (res) {
                            app.items = res.data;
                        })
                },
                readAt(id, link) {
                    let app = this;
                    let url = "{{ route('user.push.notification.readAt', 0) }}";
                    url = url.replace(/.$/, id);
                    axios.get(url)
                        .then(function (res) {
                            if (res.status) {
                                app.getNotifications();
                                if (link !== '#') {
                                    window.location.href = link
                                }
                            }
                        })
                },
                readAll() {
                    let app = this;
                    let url = "{{ route('user.push.notification.readAll') }}";
                    axios.get(url)
                        .then(function (res) {
                            if (res.status) {
                                app.items = [];
                            }
                        })
                },
                pushNewItem() {
                    let app = this;
                    Pusher.logToConsole = false;
                    let pusher = new Pusher("{{ env('PUSHER_APP_KEY') }}", {
                        encrypted: true,
                        cluster: "{{ env('PUSHER_APP_CLUSTER') }}"
                    });
                    let channel = pusher.subscribe('user-notification.' + "{{ Auth::id() }}");
                    channel.bind('App\\Events\\UserNotification', function (data) {
                        app.items.unshift(data.message);
                    });
                    channel.bind('App\\Events\\UpdateUserNotification', function (data) {
                        app.getNotifications();
                    });
                }
            }
        });
    </script>
@endpush
