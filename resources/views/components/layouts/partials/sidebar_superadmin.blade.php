<div class="sidebar-menu">
    <ul class="menu ">
        <li class="sidebar-title">Menu</li>
        <li class="sidebar-item {{ Request::routeIs('admin.dashboard') ? 'active' : '' }} ">
            <a href="{{route('admin.dashboard')}}" class='sidebar-link'>
                <i class="bi bi-grid-fill"></i>
                <span>Dashboard</span>
            </a>
        </li>
        <li class="sidebar-item {{ Request::routeIs('admin.user.management')?'active':'' }} ">
            <a href="{{route('admin.user.management')}}" class='sidebar-link'>
                <i class="bi bi-person-plus-fill"></i>
                <span>User Management</span>
            </a>
        </li>
         <li class="sidebar-item {{ Request::routeIs('admin.room.management')?'active':'' }} ">
            <a href="{{route('admin.room.management')}}" class='sidebar-link'>
                <i class="bi bi-door-open-fill"></i>
                <span>Room Management</span>
            </a>
        </li>
        <li class="sidebar-item {{ (Request::routeIs('admin.room-type.management') || Request::routeIs('admin.room-type.images')) ? 'active' : '' }} ">
            <a href="{{route('admin.room-type.management')}}" class='sidebar-link'>
                <i class="bi bi-tags-fill"></i>
                <span>Room Type Management</span>
            </a>
        </li>
        <li class="sidebar-item {{ Request::routeIs('admin.facility.management')?'active':'' }} ">
            <a href="{{route('admin.facility.management')}}" class='sidebar-link'>
                <i class="bi bi-tools"></i>
                <span>Facility Management</span>
            </a>
        </li>
        <li class="sidebar-item {{ Request::routeIs('admin.guest.management')?'active':'' }} ">
            <a href="{{route('admin.guest.management')}}" class='sidebar-link'>
                <i class="bi bi-person-heart"></i>
                <span>Guest Management</span>
            </a>

        </li>
        <li class="sidebar-item {{ Request::routeIs('admin.reservation.management')?'active':'' }} ">
            <a href="{{route('admin.reservation.management')}}" class='sidebar-link'>
                <i class="bi bi-calendar-check"></i>
                <span>Reservation Management</span>
            </a>
        </li>
        <li class="sidebar-item {{ Request::routeIs('admin.availability.calendar')?'active':'' }} ">
            <a href="{{route('admin.availability.calendar')}}" class='sidebar-link'>
                <i class="bi bi-calendar3-week"></i>
                <span>Availability Calendar</span>
            </a>
        </li>


        <li class="sidebar-title">Operations</li>

        <li class="sidebar-item {{ Request::routeIs('admin.housekeeping.management')?'active':'' }} ">
            <a href="{{route('admin.housekeeping.management')}}" class='sidebar-link'>
                <i class="bi bi-door-closed-fill"></i>
                <span>Housekeeping</span>
            </a>
        </li>

        <li class="sidebar-item {{ Request::routeIs('admin.payments')?'active':'' }} ">
            <a href="{{ route('admin.payments') }}" class='sidebar-link'>
                <i class="bi bi-credit-card"></i>
                <span>Pembayaran</span>
                @php($pendingPayments = \App\Models\Bill::whereNull('paid_at')->where('payment_review_status','pending')->count())
                @if($pendingPayments)
                    <span class="badge bg-danger ms-2">{{ $pendingPayments }}</span>
                @endif
            </a>
        </li>

        <li class="sidebar-item {{ Request::routeIs('admin.contacts')?'active':'' }} ">
            <a href="{{ route('admin.contacts') }}" class='sidebar-link d-flex align-items-center'>
                <i class="bi bi-envelope"></i>
                <span class="ms-1">Pesan Kontak</span>
                @php($unreadContacts = \App\Models\ContactMessage::whereNull('read_at')->count())
                @if($unreadContacts)
                    <span class="badge bg-danger ms-2">{{ $unreadContacts }}</span>
                @endif
            </a>
        </li>

        <li class="sidebar-item {{ Request::routeIs('admin.gallery') ? 'active' : '' }} ">
            <a href="{{ route('admin.gallery') }}" class='sidebar-link d-flex align-items-center'>
                <i class="bi bi-images"></i>
                <span class="ms-1">Galeri</span>
            </a>
        </li>

        <li class="sidebar-item {{ Request::routeIs('admin.inventory') ? 'active' : '' }} ">
            <a href="{{ route('admin.inventory') }}" class='sidebar-link d-flex align-items-center'>
                <i class="bi bi-list-check"></i>
                <span class="ms-1">Inventory</span>
            </a>
        </li>

        @php($roleName = optional(Auth::user()->role)->name)
        @if(in_array($roleName, ['superadmin','cashier']))
        <li class="sidebar-title">F&amp;B</li>
        <li class="sidebar-item {{ Request::routeIs('cashier.fnb.orders') ? 'active' : '' }} ">
            <a href="{{ route('cashier.fnb.orders') }}" class='sidebar-link d-flex align-items-center'>
                <i class="bi bi-bag"></i>
                <span class="ms-1">Kasir F&amp;B</span>
            </a>
        </li>
        <li class="sidebar-item {{ Request::routeIs('cashier.fnb.menu') ? 'active' : '' }} ">
            <a href="{{ route('cashier.fnb.menu') }}" class='sidebar-link d-flex align-items-center'>
                <i class="bi bi-egg-fried"></i>
                <span class="ms-1">Kelola Menu F&amp;B</span>
            </a>
        </li>
        @endif

        <li class="sidebar-title">Analytics</li>

        <li class="sidebar-item {{ Request::routeIs('admin.reporting')?'active':'' }} ">
            <a href="{{route('admin.reporting')}}" class='sidebar-link'>
                <i class="bi bi-file-earmark-bar-graph-fill"></i>
                <span>Reporting</span>
            </a>
        </li>
        <li class="sidebar-item {{ Request::routeIs('admin.promos')?'active':'' }} ">
            <a href="{{ route('admin.promos') }}" class='sidebar-link'>
                <i class="bi bi-percent"></i>
                <span>Promo</span>
            </a>
        </li>
        <livewire:auth.logout>
    </ul>
</div>
