{{--
  Bảng ticket dùng chung (theo bảng tickets, ratings, users).
  Tham số: $tickets (paginator), $mode ('customer' | 'staff')
  Cột "Hoạt động": Sửa (chuyển sang trang Edit) và Xóa.
--}}
@php $staff = ($mode ?? 'customer') === 'staff'; @endphp
<div class="table-wrap">
    <table class="data-table">
        <thead>
            <tr>
                <th>Mã Ticket</th>
                <th>Tiêu đề</th>
                @if ($staff)
                    <th>Người tạo</th>
                    <th>Phụ trách</th>
                @else
                    <th>Trạng thái</th>
                @endif
                <th>Mức độ ưu tiên</th>
                <th>Ngày tạo</th>
                @if ($staff)
                    <th>Trạng thái</th>
                    <th>Ngày cập nhật</th>
                @endif
                <th>Rating</th>
                <th>Hoạt động</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($tickets as $t)
                <tr>
                    <td>{{ $t->track_id }}</td>
                    <td><a href="{{ route('tickets.show', $t) }}">{{ $t->title }}</a></td>
                    @if ($staff)
                        <td>{{ $t->customer->name }}</td>
                        <td>{{ $t->owner->name ?? '-' }}</td>
                    @else
                        <td>@include('partial.status_badge', ['status' => $t->status])</td>
                    @endif
                    <td>{{ config('helpdesk.priority.' . $t->priority) }}</td>
                    <td>{{ $t->created_at->format('d/m/Y') }}</td>
                    @if ($staff)
                        <td>@include('partial.status_badge', ['status' => $t->status])</td>
                        <td>{{ $t->updated_at->format('d/m/Y') }}</td>
                    @endif
                    <td>@include('partial.stars', ['stars' => $t->rating->stars ?? 0])</td>
                    <td class="nowrap">
                        @can('update', $t)
                            <a href="{{ route('tickets.edit', $t) }}" class="act-edit">Sửa</a>
                        @endcan
                        @can('delete', $t)
                            <form method="POST" action="{{ route('tickets.destroy', $t) }}" class="inline-form"
                                  onsubmit="return confirm('Xóa ticket này?')">
                                @csrf
                                @method('DELETE')
                                <button class="act-del">Xóa</button>
                            </form>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="10" class="empty">Không có ticket nào.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="pager">{{ $tickets->withQueryString()->links('partial.pagination') }}</div>
