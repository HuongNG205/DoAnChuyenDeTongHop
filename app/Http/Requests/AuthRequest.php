<?php

namespace App\Http\Requests;

// use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AuthRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /** Email luôn được chuẩn hóa về chữ thường và bỏ khoảng trắng thừa trước khi validate. */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => Str::lower(trim((string) $this->input('email'))),
        ]);
    }

    private function isRegister(): bool
    {
        return $this->is('register');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        if ($this->isRegister()) {
            return [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
                'password' => ['required', 'string', 'min:6', 'max:255'],
            ];
        }

        return [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Vui lòng nhập :attribute.',
            'name.max' => ':Attribute tối đa :max ký tự.',
            'email.required' => 'Vui lòng nhập :attribute.',
            'email.email' => ':Attribute không hợp lệ.',
            'email.max' => ':Attribute tối đa :max ký tự.',
            'email.unique' => ':Attribute này đã được sử dụng.',
            'password.required' => 'Vui lòng nhập :attribute.',
            'password.min' => ':Attribute phải có ít nhất :min ký tự.',
            'password.max' => ':Attribute tối đa :max ký tự.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'Tên người dùng',
            'email' => 'Email',
            'password' => 'Mật khẩu',
        ];
    }
}
