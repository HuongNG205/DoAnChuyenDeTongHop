@extends('layouts.app')
@section('title', 'Lịch sử hoạt động')
{{-- Biến: $activities (paginator, đã load user). Bảng activities ghi hoạt động và thông báo tương ứng. --}}
@section('content')
<h1 class="page-title">Lịch sử hoạt động</h1>
<div class="toolbar">
    @include('partial.search_box', ['action' => route('admin.activities.index')])
    @include('partial.filter_box', [
        'action' => route('admin.activities.index'),
        'fields' => [
            ['name' => 'action', 'label' => 'Loại', 'options' => config('helpdesk.activities')],
            ['name' => 'created_from', 'label' => 'Từ ngày', 'type' => 'date'],
            ['name' => 'created_to', 'label' => 'Đến ngày', 'type' => 'date'],
        ],
    ])
</div>

<div class="table-wrap">
    <table class="data-table">
        <thead>
            <tr>
                @php $sortDefault = ['created_at', 'desc']; @endphp
                @include('partial.sort_th', ['key' => 'stt', 'label' => 'STT'])
                @include('partial.sort_th', ['key' => 'created_at', 'label' => 'Ngày'])
                @include('partial.sort_th', ['key' => 'user', 'label' => 'User'])
                @include('partial.sort_th', ['key' => 'action', 'label' => 'Loại'])
                @include('partial.sort_th', ['key' => 'message', 'label' => 'Thông báo'])
                <th>Hoạt động</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($activities as $a)
                <tr>
                    <td>{{ $activities->firstItem() + $loop->index }}</td>
                    <td>{{ $a->created_at->format('d/m/Y') }}</td>
                    <td>{{ $a->notifyUser->name ?? '-' }}</td>
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
