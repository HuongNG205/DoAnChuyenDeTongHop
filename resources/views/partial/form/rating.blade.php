{{--
  Đánh giá ticket bằng sao (không có nhận xét). Dùng cho thêm mới, cập nhật và xóa.
  Tham số: $ticket, $rating (null nếu chưa có)
--}}
@php $exists = !empty($rating); @endphp
<form method="POST" action="{{ $exists ? route('tickets.rating.update', $ticket) : route('tickets.rating.store', $ticket) }}"
      class="flex flex-wrap items-center gap-3">
    @csrf
    @if ($exists) @method('PUT') @endif

    <span class="text-lg">Đánh giá:</span>
    <div class="rate">
        @for ($i = 5; $i >= 1; $i--)
            <input type="radio" id="star{{ $i }}" name="stars" value="{{ $i }}" @checked(old('stars', $rating->stars ?? 0) == $i) required>
            <label for="star{{ $i }}" title="{{ $i }} sao"><i class="fa-solid fa-star"></i></label>
        @endfor
    </div>

    <button class="btn">{{ $exists ? 'Cập nhật' : 'Gửi đánh giá' }}</button>
    @if ($exists)
        <button type="submit" form="delete-rating" class="btn btn-danger">Xóa đánh giá</button>
    @endif
</form>

@if ($exists)
    <form id="delete-rating" method="POST" action="{{ route('tickets.rating.destroy', $ticket) }}"
          onsubmit="return confirm('Xóa đánh giá?')">
        @csrf
        @method('DELETE')
    </form>
@endif
