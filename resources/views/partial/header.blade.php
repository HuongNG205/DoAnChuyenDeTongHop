<header class="flex flex-wrap items-center gap-4 bg-primary px-6 py-4">
    <a href="{{ url('/') }}" class="logo">Guidance</a>

    <div class="min-w-48 max-w-3xl flex-1 md:ml-24">
        @include('partial.search_box', ['action' => route('search'), 'variant' => 'header'])
    </div>

    <div class="ml-auto flex items-center gap-5 text-white">
        @guest
            <a href="{{ route('login') }}" class="btn btn-accent">Đăng nhập</a>
            <a href="{{ route('register') }}" class="btn btn-accent">Đăng ký</a>
        @endguest

        @auth
            @php $items = $headerNotifications ?? []; @endphp

            {{-- Thông báo: lấy từ bảng activities (notify_user_id = người dùng hiện tại) --}}
            <details class="relative">
                <summary class="relative cursor-pointer list-none text-2xl">
                    <i class="fa-regular fa-bell"></i>
                    @if (($unreadCount ?? 0) > 0)
                        <span class="absolute -right-2 -top-2 rounded-full bg-gold px-1.5 text-xs font-bold text-primary">{{ $unreadCount }}</span>
                    @endif
                </summary>
                <div class="absolute right-0 z-20 mt-3 w-80 rounded-xl bg-white p-2 text-sm text-black shadow-lg">
                    @forelse ($items as $n)
                        <a href="{{ $n->ticket_id ? route('tickets.show', $n->ticket_id) : '#' }}"
                           class="block rounded-lg px-3 py-2 hover:bg-muted {{ $n->is_read ? 'text-gray-500' : 'font-medium' }}">
                            {{ $n->message }}
                            <span class="block text-xs text-gray-500">{{ $n->created_at->format('d/m/Y H:i') }}</span>
                        </a>
                    @empty
                        <p class="px-3 py-2 text-gray-500">Chưa có thông báo.</p>
                    @endforelse
                </div>
            </details>

            {{-- Chỉ hiển thị tên người dùng, không avatar --}}
            <details class="relative">
                <summary class="cursor-pointer list-none text-lg">{{ auth()->user()->name }}</summary>
                <div class="absolute right-0 z-20 mt-3 w-44 rounded-xl bg-white p-2 text-black shadow-lg">
                    <a href="{{ route('account.edit') }}" class="block rounded-lg px-3 py-2 hover:bg-muted">Tài khoản</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="block w-full rounded-lg px-3 py-2 text-left hover:bg-muted">Đăng xuất</button>
                    </form>
                </div>
            </details>
        @endauth
    </div>
</header>
