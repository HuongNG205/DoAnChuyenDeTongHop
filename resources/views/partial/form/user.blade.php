{{--
  Dùng chung cho Quản lý tài khoản (customer) và Chi tiết User (admin).
  Tham số: $user, $action, $admin (bool)
--}}
@php
    $admin = $admin ?? false;
    $grid = $admin ? 'form-grid-wide' : 'form-grid-narrow';
    // Admin không tự đổi vai trò / chặn / xóa chính mình (UpdateUserRequest và UserController cũng chặn ở server).
    $isSelf = $admin && auth()->id() === $user->id;
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
            <select id="role" name="role" class="input" @disabled($isSelf)>
                @foreach (config('helpdesk.roles') as $key => $label)
                    <option value="{{ $key }}" @selected(old('role', $user->role) === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-grid {{ $grid }}">
            <label for="is_blocked" class="label-lg">Trạng thái:</label>
            <select id="is_blocked" name="is_blocked" class="input" @disabled($isSelf)>
                @foreach (config('helpdesk.user_status') as $key => $label)
                    <option value="{{ $key }}" @selected((int) old('is_blocked', $user->is_blocked) === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-grid {{ $grid }}">
            <label for="blocked_reason" class="label-lg">Lý do chặn:</label>
            <input id="blocked_reason" name="blocked_reason" class="input" maxlength="255" @disabled($isSelf)
                   placeholder="Chỉ áp dụng khi trạng thái là Đã chặn"
                   value="{{ old('blocked_reason', $user->blocked_reason) }}">
        </div>
    @endif

    <div class="btn-row">
        <button class="btn">Xác nhận</button>
        @if ($admin && ! $isSelf)
            <button type="submit" form="delete-user" class="btn btn-danger">Xóa tài khoản</button>
        @endif
    </div>
</form>

@if ($admin && ! $isSelf)
    <form id="delete-user" method="POST" action="{{ route('admin.users.destroy', $user) }}"
          onsubmit="return confirm('Xóa tài khoản này?')">
        @csrf
        @method('DELETE')
    </form>
@endif
