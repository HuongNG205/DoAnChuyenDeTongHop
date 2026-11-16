<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Admin cập nhật user. Mọi trường đều "sometimes" để dùng được cả cho form đầy đủ (trang sửa user)
 * lẫn ô chọn nhanh vai trò / trạng thái ngay trên danh sách.
 */
class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    protected function prepareForValidation(): void
    {
        $merge = [];

        if ($this->has('name')) {
            $merge['name'] = trim((string) preg_replace('/\s+/u', ' ', (string) $this->input('name')));
        }
        if ($this->has('email')) {
            $merge['email'] = Str::lower(trim((string) $this->input('email'))); // giống AuthRequest
        }
        if ($this->has('blocked_reason')) {
            $merge['blocked_reason'] = trim((string) $this->input('blocked_reason')) ?: null;
        }

        $this->merge($merge);
    }

    public function rules(): array
    {
        /** @var User $target */
        $target = $this->route('user');

        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($target->id)],
            'role' => ['sometimes', 'required', Rule::in(array_keys(config('helpdesk.roles')))],
            'is_blocked' => ['sometimes', 'required', 'boolean'],
            'blocked_reason' => ['nullable', 'string', 'max:255'],
        ];
    }

    /** Admin không được tự hạ vai trò hoặc tự chặn chính mình (tránh khóa mất quyền quản trị). */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            /** @var User $target */
            $target = $this->route('user');

            if (! $target->is($this->user()) || $validator->errors()->isNotEmpty()) {
                return;
            }

            if ($this->has('role') && $this->input('role') !== $target->role) {
                $validator->errors()->add('role', 'Bạn không thể thay đổi vai trò của chính mình.');
            }
            if ($this->has('is_blocked') && $this->boolean('is_blocked')) {
                $validator->errors()->add('is_blocked', 'Bạn không thể chặn chính mình.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Vui lòng nhập :attribute.',
            'name.max' => ':Attribute tối đa :max ký tự.',
            'email.required' => 'Vui lòng nhập :attribute.',
            'email.email' => ':Attribute không hợp lệ.',
            'email.max' => ':Attribute tối đa :max ký tự.',
            'email.unique' => ':Attribute đã được sử dụng.',
            'role.in' => ':Attribute không hợp lệ.',
            'is_blocked.boolean' => ':Attribute không hợp lệ.',
            'blocked_reason.max' => ':Attribute tối đa :max ký tự.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'Tên tài khoản',
            'email' => 'Email',
            'role' => 'Vai trò',
            'is_blocked' => 'Trạng thái',
            'blocked_reason' => 'Lý do chặn',
        ];
    }
}
