{{-- Thẻ ticket (trang chủ, tìm kiếm, "Gần đây"). Tham số: $ticket (có rating, replies_count) --}}
<a href="{{ route('tickets.show', $ticket) }}" class="card block px-6 py-3 hover:shadow-lg">
    <h3 class="text-2xl font-medium">{{ $ticket->subject }}</h3>
    <div class="text-xs text-gray-500">{{ $ticket->created_at->format('d/m/Y') }}</div>
    <div class="flex items-center gap-2 text-xs text-gray-500">
        @include('partial.stars', ['stars' => $ticket->rating->stars ?? 0])
        <span>{{ $ticket->replies_count ?? 0 }} bình luận</span>
    </div>
</a>
