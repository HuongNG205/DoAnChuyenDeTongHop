{{--
  Bảng ticket dùng chung (theo bảng tickets, ratings, users).
  Tham số: $tickets (paginator), $mode ('customer' | 'staff')
  Cột "Hoạt động": Sửa (chuyển sang trang Edit) và Xóa.
--}}
@php $staff = ($mode ?? 'customer') === 'staff'; @endphp
<div class="overflow-x-auto">
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
                    <td><a href="{{ route('tickets.show', $t) }}" class="hover:underline">{{ $t->title }}</a></td>
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
                    <td class="whitespace-nowrap">
                        @can('update', $t)
                            <a href="{{ route('tickets.edit', $t) }}" class="underline">Sửa</a>
                        @endcan
                        @can('delete', $t)
                            <form method="POST" action="{{ route('tickets.destroy', $t) }}" class="ml-2 inline"
                                  onsubmit="return confirm('Xóa ticket này?')">
                                @csrf
                                @method('DELETE')
                                <button class="text-red-700 underline">Xóa</button>
                            </form>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="10" class="py-6 text-gray-500">Không có ticket nào.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-2">{{ $tickets->withQueryString()->links('partial.pagination') }}</div>
