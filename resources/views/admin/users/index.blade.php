@extends('layouts.app')
@section('title', 'Danh sách User')
{{-- Biến: $users (paginator) --}}
@section('content')
<h1 class="page-title">Danh sách User</h1>
<div class="toolbar">
    @include('partial.search_box', ['action' => route('admin.users.index')])
</div>

<div class="table-wrap">
    <table class="data-table">
        <thead>
            <tr>
                <th>Mã User</th>
                <th>Tên</th>
                <th>Email</th>
                <th>Vai trò</th>
                <th>Trạng thái</th>
                <th>Ngày tạo</th>
                <th>Hoạt động</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($users as $u)
                <tr>
                    <td>{{ $u->id }}</td>
                    <td><a href="{{ route('admin.users.edit', $u) }}">{{ $u->name }}</a></td>
                    <td>{{ $u->email }}</td>
                    <td>{{ ucfirst($u->role) }}</td>
                    <td>{{ $u->is_blocked ? 'Đã chặn' : 'Hoạt động' }}</td>
                    <td>{{ $u->created_at->format('d/m/Y') }}</td>
                    <td class="nowrap">
                        <a href="{{ route('admin.users.edit', $u) }}" class="act-edit">Sửa</a>
                        @if ($u->id !== auth()->id())
                            <form method="POST" action="{{ route('admin.users.destroy', $u) }}" class="inline-form"
                                  onsubmit="return confirm('Xóa tài khoản này?')">
                                @csrf
                                @method('DELETE')
                                <button class="act-del">Xóa</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="empty">Không có user nào.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="pager">{{ $users->withQueryString()->links('partial.pagination') }}</div>
@endsection
