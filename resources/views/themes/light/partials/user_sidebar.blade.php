<aside id="sidebar" class="sidebar">

    <ul class="sidebar-nav" id="sidebar-nav">
        <li class="nav-item">
            <a class="nav-link {{ isMenuActive(['user.dashboard']) }}" href="{{ route('user.dashboard') }}">
                <i class="fal fa-grid"></i>@lang('Dashboard')</a>
        </li>

        <li class="nav-item">
            <a class="nav-link {{isMenuActive(['user.transferAmount','user.transferRecipient','user.transferReview','user.transferPay'])}}"
               href="{{ route("user.transferAmount") }}"><i class="fa-light fa-money-bill-alt"></i>@lang('Send Money')</a>
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
            <a class="nav-link {{ isMenuActive(['user.add.fund','user.payment.process'])}}" href="{{ route('user.add.fund') }}">
                <i class="fa-regular fa-wallet"></i>
                <span>@lang('Deposit')</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link collapsed {{ menuActive([
                            'user.transferList','user.fund.index','user.allTransaction','user.moneyRequestList' 
                            ], 3) }} "
               data-bs-target="#crm" data-bs-toggle="collapse" href="#">
                <i class="fa-regular fa-clock-rotate-left"></i><span>@lang('history')</span>
                <i class="fa-regular fa-angle-down ms-auto bi-chevron-down"></i>
            </a>
            <ul id="crm" data-bs-parent="#sidebar-nav" class="nav-content collapse
                {{ menuActive([ 'user.transferList','user.fund.index','user.allTransaction','user.moneyRequestList' ], 2) }}" >
                <li>
                    <a href="{{ route('user.allTransaction') }}" class="{{ isMenuActive('user.allTransaction') }}">
                        <i class="fa-regular fa-circle"></i><span>@lang('Transaction History')</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('user.fund.index') }}" class="{{ isMenuActive('user.fund.index') }}">
                        <i class="fa-regular fa-circle"></i><span>@lang('Deposit History')</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route("user.transferList") }}" class="{{ isMenuActive('user.transferList') }}">
                        <i class="fa-regular fa-circle"></i><span>@lang('Transfer History')</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route("user.moneyRequestList") }}" class="{{ isMenuActive('user.moneyRequestList') }}">
                        <i class="fa-regular fa-circle"></i><span>@lang('Request History')</span>
                    </a>
                </li>
                
            </ul>
        </li>
        <li class="nav-item">
            <a class="nav-link collapsed {{ menuActive([
                            'user.settings','user.ticket.list','user.ticket.create','user.ticket.view',
                            ], 3) }}" data-bs-target="#sales" data-bs-toggle="collapse" href="#">
                <i class="fa-regular fa-grid-2-plus"></i><span>@lang('More')</span>
                <i class="fa-regular fa-angle-down ms-auto bi-chevron-down"></i>
            </a>
            <ul id="sales" class="nav-content collapse {{ menuActive([
                            'user.settings','user.ticket.list','user.ticket.create','user.ticket.view',
                            ], 2) }}" data-bs-parent="#sidebar-nav">
                <li>
                    <a href="{{ route('user.settings') }}" class="{{ isMenuActive('user.settings') }}">
                        <i class="fa-light fa-gear"></i><span>@lang('Settings')</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('user.settings') }}" class="{{ isMenuActive(['user.ticket.list','user.ticket.create','user.ticket.view']) }}">
                        <i class="fa-regular fa-circle"></i><span>@lang('Support Ticket')</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('user.earn') }}" class="{{ isMenuActive(['user.earn','user.referList']) }}">
                        <i class="fa-regular fa-circle"></i><span>@lang('Refer & Earn')</span>
                    </a>
                </li>
            </ul>
        </li>

    </ul>

</aside>
