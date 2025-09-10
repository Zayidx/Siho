<div class="sidebar-menu">
    <ul class="menu">
        <li class="sidebar-title">Menu Pengguna</li>

        <li class="sidebar-item {{ Request::routeIs('user.dashboard') ? 'active' : '' }}">
            <a href="{{ route('user.dashboard') }}" class="sidebar-link">
                <i class="bi bi-speedometer2"></i>
                <span>Dashboard</span>
            </a>
        </li>

        <li class="sidebar-item {{ Request::routeIs('user.bills') ? 'active' : '' }}">
            <a href="{{ route('user.bills') }}" class="sidebar-link d-flex align-items-center">
                <i class="bi bi-receipt"></i>
                <span class="ms-1">Tagihan</span>
                @php($unpaid = \App\Models\Bill::whereHas('reservation', fn($q) => $q->where('guest_id', \Illuminate\Support\Facades\Auth::id()))->whereNull('paid_at')->count())
                @if ($unpaid)
                    <span class="badge bg-danger ms-2">{{ $unpaid }}</span>
                @endif
            </a>
        </li>

        <li class="sidebar-item {{ Request::routeIs('user.reservations') ? 'active' : '' }}">
            <a href="{{ route('user.reservations') }}" class="sidebar-link">
                <i class="bi bi-journal-check"></i>
                <span>Reservasi Saya</span>
            </a>
        </li>

        <li class="sidebar-item {{ Request::routeIs('user.fnb.orders') ? 'active' : '' }}">
            <a href="{{ route('user.fnb.orders') }}" class="sidebar-link">
                <i class="bi bi-bag-check"></i>
                <span>Pesanan Makanan</span>
            </a>
        </li>

        <li class="sidebar-item {{ Request::routeIs('user.profile') ? 'active' : '' }}">
            <a href="{{ route('user.profile') }}" class="sidebar-link">
                <i class="bi bi-person"></i>
                <span>Profil</span>
            </a>
        </li>

        <li class="sidebar-title">Akun</li>
        <li class="sidebar-item">
            <a href="{{ route('booking') }}" class="sidebar-link">
                <i class="bi bi-calendar2-check"></i>
                <span>Pesan Kamar</span>
            </a>
        </li>
        <li class="sidebar-item">
            <a href="{{ route('menu') }}" class="sidebar-link">
                <i class="bi bi-restaurant"></i>
                <span>Pesan Makanan</span>
            </a>
        </li>
        <livewire:auth.logout />
    </ul>
</div>
