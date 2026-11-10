{{--
  Dùng chung cho Quản lý tài khoản (customer) và Chi tiết User (admin).
  Tham số: $user, $action, $admin (bool)
--}}
@php $admin = $admin ?? false; @endphp
<form method="POST" action="{{ $action }}" class="space-y-4">
    @csrf
    @method('PUT')

    @if ($admin)
        <div class="grid items-center gap-2 md:grid-cols-[14rem_1fr]">
            <label class="text-xl">Mã tài khoản:</label>
            <input class="input bg-muted" value="{{ $user->id }}" readonly>
        </div>
    @else
        <p class="text-xl">Email: {{ $user->email }}</p>
    @endif

    <div class="grid items-center gap-2 {{ $admin ? 'md:grid-cols-[14rem_1fr]' : 'sm:grid-cols-[10rem_1fr]' }}">
        <label for="name" class="text-xl">Tên tài khoản:</label>
        <input id="name" name="name" class="input" required value="{{ old('name', $user->name) }}">
    </div>

    @if ($admin)
        <div class="grid items-center gap-2 md:grid-cols-[14rem_1fr]">
            <label for="email" class="text-xl">Email:</label>
            <input id="email" type="email" name="email" class="input" required value="{{ old('email', $user->email) }}">
        </div>
        <div class="grid items-center gap-2 md:grid-cols-[14rem_1fr]">
            <label for="role" class="text-xl">Vai trò:</label>
            <select id="role" name="role" class="input">
                @foreach (config('helpdesk.roles') as $r)
                    <option value="{{ $r }}" @selected(old('role', $user->role) === $r)>{{ ucfirst($r) }}</option>
                @endforeach
            </select>
        </div>
        <div class="grid items-center gap-2 md:grid-cols-[14rem_1fr]">
            <label for="is_blocked" class="text-xl">Trạng thái:</label>
            <select id="is_blocked" name="is_blocked" class="input">
                <option value="0" @selected(!old('is_blocked', $user->is_blocked))>Hoạt động</option>
                <option value="1" @selected(old('is_blocked', $user->is_blocked))>Đã chặn</option>
            </select>
        </div>
    @endif

    <div class="flex justify-center gap-3 pt-2">
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
