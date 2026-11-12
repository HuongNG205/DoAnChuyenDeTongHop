@extends('layouts.app')
@section('title', 'Lịch sử hoạt động')
{{-- Biến: $activities (paginator, đã load user). Bảng activities ghi hoạt động và thông báo tương ứng. --}}
@section('content')
<h1 class="page-title">Lịch sử hoạt động</h1>
<div class="toolbar">
    @include('partial.search_box', ['action' => route('admin.activities.index')])
</div>

<div class="table-wrap">
    <table class="data-table">
        <thead>
            <tr>
                <th>STT</th>
                <th>Ngày</th>
                <th>User</th>
                <th>Loại</th>
                <th>Thông báo</th>
                <th>Hoạt động</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($activities as $a)
                <tr>
                    <td>{{ $activities->firstItem() + $loop->index }}</td>
                    <td>{{ $a->created_at->format('d/m/Y') }}</td>
                    <td>{{ $a->user->name ?? '-' }}</td>
                    <td>{{ config('helpdesk.activities.' . $a->action, $a->action) }}</td>
                    <td>{{ $a->message }}</td>
                    <td>
                        <form method="POST" action="{{ route('admin.activities.destroy', $a) }}"
                              onsubmit="return confirm('Xóa dòng lịch sử này?')">
                            @csrf
                            @method('DELETE')
                            <button class="act-del">Xóa</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="empty">Chưa có hoạt động nào.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="pager">{{ $activities->withQueryString()->links('partial.pagination') }}</div>
@endsection
