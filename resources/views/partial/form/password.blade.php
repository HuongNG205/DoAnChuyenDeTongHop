<form method="POST" action="{{ route('account.password') }}" class="space-y-4">
    @csrf
    @method('PUT')

    <div>
        <label for="current_password" class="mb-1 block text-xl">Nhập mật khẩu cũ:</label>
        <input id="current_password" type="password" name="current_password" class="input" required>
    </div>
    <div>
        <label for="password" class="mb-1 block text-xl">Nhập mật khẩu mới:</label>
        <input id="password" type="password" name="password" class="input" required>
    </div>
    <div>
        <label for="password_confirmation" class="mb-1 block text-xl">Nhập lại mật khẩu mới:</label>
        <input id="password_confirmation" type="password" name="password_confirmation" class="input" required>
    </div>

    <div class="text-center">
        <button class="btn">Xác nhận</button>
    </div>
</form>
