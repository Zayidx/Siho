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
                <i class="fa-solid fa-bed"></i>
                <span>Room Management</span>
            </a>
        </li>
        <li class="sidebar-item {{ Request::routeIs('admin.room-type.management')?'active':'' }} ">
            <a href="{{route('admin.room-type.management')}}" class='sidebar-link'>
                <i class="fa-solid fa-tags"></i>
                <span>Room Type Management</span>
            </a>
        </li>
        <li class="sidebar-item {{ Request::routeIs('admin.facility.management')?'active':'' }} ">
            <a href="{{route('admin.facility.management')}}" class='sidebar-link'>
                <i class="fa-solid fa-wifi"></i>
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

        <li class="sidebar-title">Analytics</li>

        <li class="sidebar-item {{ Request::routeIs('admin.reporting')?'active':'' }} ">
            <a href="{{route('admin.reporting')}}" class='sidebar-link'>
                <i class="bi bi-file-earmark-bar-graph-fill"></i>
                <span>Reporting</span>
            </a>
        </li>
        <livewire:auth.logout>
    </ul>
</div>