@extends('layouts.app')
@section('title', 'Danh sách User')
{{-- Biến: $users (paginator) --}}
@section('content')
<h1 class="page-title">Danh sách User</h1>
<div class="toolbar">
    @include('partial.search_box', ['action' => route('admin.users.index')])
    @include('partial.filter_box', [
        'action' => route('admin.users.index'),
        'fields' => [
            ['name' => 'role', 'label' => 'Vai trò', 'options' => config('helpdesk.roles')],
            ['name' => 'status', 'label' => 'Trạng thái', 'options' => config('helpdesk.user_status')],
            ['name' => 'created_from', 'label' => 'Tạo từ ngày', 'type' => 'date'],
            ['name' => 'created_to', 'label' => 'Đến ngày', 'type' => 'date'],
        ],
    ])
</div>

<div class="table-wrap">
    <table class="data-table">
        <thead>
            <tr>
                @php $sortDefault = ['id', 'asc']; @endphp
                @include('partial.sort_th', ['key' => 'id', 'label' => 'Mã User'])
                @include('partial.sort_th', ['key' => 'name', 'label' => 'Tên'])
                @include('partial.sort_th', ['key' => 'email', 'label' => 'Email'])
                @include('partial.sort_th', ['key' => 'role', 'label' => 'Vai trò'])
                @include('partial.sort_th', ['key' => 'status', 'label' => 'Trạng thái'])
                @include('partial.sort_th', ['key' => 'created_at', 'label' => 'Ngày tạo'])
                <th>Hoạt động</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($users as $u)
                <tr>
                    <td>{{ $u->id }}</td>
                    <td><a href="{{ route('admin.users.edit', $u) }}">{{ $u->name }}</a></td>
                    <td>{{ $u->email }}</td>
                    {{-- Đổi nhanh vai trò / trạng thái ngay trên danh sách; không tự đổi được chính mình --}}
                    @if ($u->id === auth()->id())
                        <td>{{ config('helpdesk.roles.' . $u->role) }}</td>
                        <td>{{ config('helpdesk.user_status.' . (int) $u->is_blocked) }}</td>
                    @else
                        <td>
                            @include('partial.inline_select', [
                                'action' => route('admin.users.update', $u),
                                'name' => 'role',
                                'value' => $u->role,
                                'options' => config('helpdesk.roles'),
                                'label' => 'Vai trò của ' . $u->name,
                            ])
                        </td>
                        <td>
                            @include('partial.inline_select', [
                                'action' => route('admin.users.update', $u),
                                'name' => 'is_blocked',
                                'value' => (int) $u->is_blocked,
                                'options' => config('helpdesk.user_status'),
                                'label' => 'Trạng thái của ' . $u->name,
                            ])
                        </td>
                    @endif
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
