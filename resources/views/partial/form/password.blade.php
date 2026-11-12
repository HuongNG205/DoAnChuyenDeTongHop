<form method="POST" action="{{ route('account.password') }}" class="form-stack">
    @csrf
    @method('PUT')

    <div class="field">
        <label for="current_password" class="label-lg">Nhập mật khẩu cũ:</label>
        <input id="current_password" type="password" name="current_password" class="input" required>
    </div>
    <div class="field">
        <label for="password" class="label-lg">Nhập mật khẩu mới:</label>
        <input id="password" type="password" name="password" class="input" required>
    </div>
    <div class="field">
        <label for="password_confirmation" class="label-lg">Nhập lại mật khẩu mới:</label>
        <input id="password_confirmation" type="password" name="password_confirmation" class="input" required>
    </div>

    <div class="center">
        <button class="btn">Xác nhận</button>
    </div>
</form>
