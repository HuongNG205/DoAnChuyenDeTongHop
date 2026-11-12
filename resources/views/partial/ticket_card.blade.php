{{-- Thẻ ticket (trang chủ, tìm kiếm, "Gần đây"). Tham số: $ticket (có rating, replies_count) --}}
<a href="{{ route('tickets.show', $ticket) }}" class="ticket-card">
    <h3>{{ $ticket->title }}</h3>
    <small>{{ $ticket->created_at->format('d/m/Y') }}</small>
    <div class="ticket-meta">
        @include('partial.stars', ['stars' => $ticket->rating->stars ?? 0])
        <span>{{ $ticket->replies_count ?? 0 }} bình luận</span>
    </div>
</a>
