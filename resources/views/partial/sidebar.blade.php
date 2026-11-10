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

<nav class="flex shrink-0 flex-row flex-wrap gap-x-8 gap-y-2 bg-primary px-6 py-4 text-xl text-white md:w-64 md:flex-col md:gap-y-5 md:px-5 md:pt-5">
    @foreach ($menu as [$label, $route, $pattern])
        <a href="{{ route($route) }}" class="{{ request()->routeIs($pattern) ? 'font-bold text-accent' : 'hover:text-accent' }}">{{ $label }}</a>
    @endforeach
</nav>
