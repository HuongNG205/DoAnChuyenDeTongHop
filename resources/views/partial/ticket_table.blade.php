{{--
  Bảng ticket dùng chung (theo bảng tickets, ratings, users).
  Tham số: $tickets (paginator), $mode ('customer' | 'staff')
  Cột "Hoạt động": Sửa (chuyển sang trang Edit) và Xóa.
  Danh sách của staff/admin: đổi nhanh Trạng thái ngay trên bảng (partial.inline_select).
--}}
@php
    $staff = ($mode ?? 'customer') === 'staff';
    // Sắp xếp mặc định của từng danh sách (khớp với controller) để tô sáng nút sort
    $sortDefault = $staff ? ['updated_at', 'desc'] : ['created_at', 'desc'];
@endphp
<div class="table-wrap">
    <table class="data-table">
        <thead>
            <tr>
                @include('partial.sort_th', ['key' => 'track_id', 'label' => 'Mã Ticket'])
                @include('partial.sort_th', ['key' => 'title', 'label' => 'Tiêu đề'])
                @if ($staff)
                    @include('partial.sort_th', ['key' => 'customer', 'label' => 'Người tạo'])
                @else
                    @include('partial.sort_th', ['key' => 'status', 'label' => 'Trạng thái'])
                @endif
                @include('partial.sort_th', ['key' => 'priority', 'label' => 'Mức độ ưu tiên'])
                @include('partial.sort_th', ['key' => 'created_at', 'label' => 'Ngày tạo'])
                @if ($staff)
                    @include('partial.sort_th', ['key' => 'status', 'label' => 'Trạng thái'])
                    @include('partial.sort_th', ['key' => 'updated_at', 'label' => 'Ngày cập nhật'])
                @endif
                @include('partial.sort_th', ['key' => 'rating', 'label' => 'Rating'])
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
                    @else
                        <td>@include('partial.status_badge', ['status' => $t->status])</td>
                    @endif
                    <td>{{ config('helpdesk.priority.' . $t->priority) }}</td>
                    <td>{{ $t->created_at->format('d/m/Y') }}</td>
                    @if ($staff)
                        <td>
                            @can('update', $t)
                                @include('partial.inline_select', [
                                    'action' => route('tickets.update', $t),
                                    'name' => 'status',
                                    'value' => $t->status,
                                    'options' => config('helpdesk.status'),
                                    'label' => 'Trạng thái ' . $t->track_id,
                                    'badge' => true,
                                ])
                            @else
                                @include('partial.status_badge', ['status' => $t->status])
                            @endcan
                        </td>
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
