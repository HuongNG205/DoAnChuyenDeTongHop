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
            <details class="dropdown" id="notification-dropdown">
                <summary class="bell" aria-label="Thông báo">
                    <i class="fa-regular fa-bell"></i>
                    @if (($unreadCount ?? 0) > 0)
                        <span class="bell-count">{{ $unreadCount }}</span>
                    @endif
                </summary>
                <div class="dropdown-menu dropdown-menu-wide">
                    @forelse ($items as $n)
                        {{-- Ticket đã bị xóa (mềm) thì không link, tránh 404 --}}
                        <a href="{{ $n->ticket && ! $n->ticket->trashed() ? route('tickets.show', $n->ticket_id) : '#' }}"
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

{{-- Bấm chuông (mở danh sách thông báo) => cập nhật is_read = true cho mọi thông báo chưa đọc.
     Dòng chưa đọc vẫn được in đậm cho đến khi đóng danh sách, để người dùng còn thấy đâu là thông báo mới. --}}
@auth
    @if (($unreadCount ?? 0) > 0)
        @push('scripts')
            <script>
                (function () {
                    var menu = document.getElementById('notification-dropdown');
                    if (!menu) return;

                    var sent = false;

                    menu.addEventListener('toggle', function () {
                        if (!menu.open) {
                            menu.querySelectorAll('.dropdown-item.unread').forEach(function (el) {
                                el.classList.replace('unread', 'read');
                            });
                            return;
                        }
                        if (sent) return;
                        sent = true;

                        var token = document.querySelector('meta[name="csrf-token"]');
                        fetch(@json(route('notifications.read')), {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': token ? token.content : '', 'Accept': 'application/json' },
                            credentials: 'same-origin'
                        }).then(function (res) {
                            if (!res.ok) throw new Error(res.status);
                            var badge = menu.querySelector('.bell-count');
                            if (badge) badge.remove();
                        }).catch(function () {
                            sent = false; // lần mở sau sẽ thử lại
                        });
                    });
                })();
            </script>
        @endpush
    @endif
@endauth
