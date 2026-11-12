<header class="header">
    <a href="{{ url('/') }}" class="logo">Guidance</a>

    <div class="header-search">
        @include('partial.search_box', ['action' => route('search'), 'variant' => 'header'])
    </div>

    <div class="header-right">
        @guest
            <a href="{{ route('login') }}" class="btn btn-accent">Đăng nhập</a>
            <a href="{{ route('register') }}" class="btn btn-accent">Đăng ký</a>
        @endguest

        @auth
            @php $items = $headerNotifications ?? []; @endphp

            {{-- Thông báo: lấy từ bảng activities (notify_user_id = người dùng hiện tại) --}}
            <details class="dropdown">
                <summary class="bell">
                    <i class="fa-regular fa-bell"></i>
                    @if (($unreadCount ?? 0) > 0)
                        <span class="bell-count">{{ $unreadCount }}</span>
                    @endif
                </summary>
                <div class="dropdown-menu dropdown-menu-wide">
                    @forelse ($items as $n)
                        <a href="{{ $n->ticket_id ? route('tickets.show', $n->ticket_id) : '#' }}"
                           class="dropdown-item {{ $n->is_read ? 'read' : 'unread' }}">
                            {{ $n->message }}
                            <small>{{ $n->created_at->format('d/m/Y H:i') }}</small>
                        </a>
                    @empty
                        <p class="dropdown-empty">Chưa có thông báo.</p>
                    @endforelse
                </div>
            </details>

            {{-- Chỉ hiển thị tên người dùng, không avatar --}}
            <details class="dropdown">
                <summary class="username">{{ auth()->user()->name }}</summary>
                <div class="dropdown-menu">
                    <a href="{{ route('account.edit') }}" class="dropdown-item">Tài khoản</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="dropdown-item">Đăng xuất</button>
                    </form>
                </div>
            </details>
        @endauth
    </div>
</header>
