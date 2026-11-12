{{--
  Dùng chung cho Quản lý tài khoản (customer) và Chi tiết User (admin).
  Tham số: $user, $action, $admin (bool)
--}}
@php
    $admin = $admin ?? false;
    $grid = $admin ? 'form-grid-wide' : 'form-grid-narrow';
@endphp
<form method="POST" action="{{ $action }}" class="form-stack">
    @csrf
    @method('PUT')

    @if ($admin)
        <div class="form-grid {{ $grid }}">
            <label class="label-lg">Mã tài khoản:</label>
            <input class="input" value="{{ $user->id }}" readonly>
        </div>
    @else
        <p class="label-lg">Email: {{ $user->email }}</p>
    @endif

    <div class="form-grid {{ $grid }}">
        <label for="name" class="label-lg">Tên tài khoản:</label>
        <input id="name" name="name" class="input" required value="{{ old('name', $user->name) }}">
    </div>

    @if ($admin)
        <div class="form-grid {{ $grid }}">
            <label for="email" class="label-lg">Email:</label>
            <input id="email" type="email" name="email" class="input" required value="{{ old('email', $user->email) }}">
        </div>
        <div class="form-grid {{ $grid }}">
            <label for="role" class="label-lg">Vai trò:</label>
            <select id="role" name="role" class="input">
                @foreach (config('helpdesk.roles') as $r)
                    <option value="{{ $r }}" @selected(old('role', $user->role) === $r)>{{ ucfirst($r) }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-grid {{ $grid }}">
            <label for="is_blocked" class="label-lg">Trạng thái:</label>
            <select id="is_blocked" name="is_blocked" class="input">
                <option value="0" @selected(!old('is_blocked', $user->is_blocked))>Hoạt động</option>
                <option value="1" @selected(old('is_blocked', $user->is_blocked))>Đã chặn</option>
            </select>
        </div>
    @endif

    <div class="btn-row">
        <button class="btn">Xác nhận</button>
        @if ($admin)
            <button type="submit" form="delete-user" class="btn btn-danger">Xóa tài khoản</button>
        @endif
    </div>
</form>

@if ($admin)
    <form id="delete-user" method="POST" action="{{ route('admin.users.destroy', $user) }}"
          onsubmit="return confirm('Xóa tài khoản này?')">
        @csrf
        @method('DELETE')
    </form>
@endif
