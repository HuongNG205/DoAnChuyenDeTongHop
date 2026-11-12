@php
    $menu = match (auth()->user()->role) {
        'admin' => [
            ['Danh sách Ticket', 'staff.tickets.index', 'staff.tickets.*'],
            ['Danh sách User', 'admin.users.index', 'admin.users.*'],
            ['Thống kê', 'staff.stats', 'staff.stats'],
            ['Lịch sử hoạt động', 'admin.activities.index', 'admin.activities.*'],
        ],
        'staff' => [
            ['Danh sách Ticket', 'staff.tickets.index', 'staff.tickets.*'],
            ['Thống kê', 'staff.stats', 'staff.stats'],
        ],
        default => [
            ['Danh sách Ticket', 'tickets.index', 'tickets.index'],
            ['Tạo Ticket', 'tickets.create', 'tickets.create'],
        ],
    };
@endphp

<nav class="sidebar">
    @foreach ($menu as [$label, $route, $pattern])
        <a href="{{ route($route) }}" class="{{ request()->routeIs($pattern) ? 'active' : '' }}">{{ $label }}</a>
    @endforeach
</nav>
