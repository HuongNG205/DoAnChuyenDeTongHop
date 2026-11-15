<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'current_password' => ['required', 'string', 'current_password'],
            // min/max giống đăng ký (AuthRequest)
            'password' => ['required', 'string', 'min:6', 'max:255', 'confirmed', 'different:current_password'],
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.required' => 'Vui lòng nhập :attribute.',
            'current_password.current_password' => ':Attribute không đúng.',
            'password.required' => 'Vui lòng nhập :attribute.',
            'password.min' => ':Attribute phải có ít nhất :min ký tự.',
            'password.max' => ':Attribute tối đa :max ký tự.',
            'password.confirmed' => 'Mật khẩu nhập lại không khớp.',
            'password.different' => ':Attribute phải khác mật khẩu cũ.',
        ];
    }

    public function attributes(): array
    {
        return [
            'current_password' => 'Mật khẩu cũ',
            'password' => 'Mật khẩu mới',
        ];
    }
}
