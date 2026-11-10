@extends('layouts.app')
@section('title', 'Danh sách User')
{{-- Biến: $users (paginator) --}}
@section('content')
<h1 class="text-3xl">Danh sách User</h1>
<div class="my-4">
    @include('partial.search_box', ['action' => route('admin.users.index')])
</div>

<div class="overflow-x-auto">
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
                    <td><a href="{{ route('admin.users.edit', $u) }}" class="hover:underline">{{ $u->name }}</a></td>
                    <td>{{ $u->email }}</td>
                    <td>{{ ucfirst($u->role) }}</td>
                    <td>{{ $u->is_blocked ? 'Đã chặn' : 'Hoạt động' }}</td>
                    <td>{{ $u->created_at->format('d/m/Y') }}</td>
                    <td class="whitespace-nowrap">
                        <a href="{{ route('admin.users.edit', $u) }}" class="underline">Sửa</a>
                        @if ($u->id !== auth()->id())
                            <form method="POST" action="{{ route('admin.users.destroy', $u) }}" class="ml-2 inline"
                                  onsubmit="return confirm('Xóa tài khoản này?')">
                                @csrf
                                @method('DELETE')
                                <button class="text-red-700 underline">Xóa</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="py-6 text-gray-500">Không có user nào.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-2">{{ $users->withQueryString()->links('partial.pagination') }}</div>
@endsection
