<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /** Tên là văn bản thuần: bỏ khoảng trắng thừa. Email không cho tự đổi, và role/is_blocked không nhận từ form. */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim((string) preg_replace('/\s+/u', ' ', (string) $this->input('name'))),
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Vui lòng nhập :attribute.',
            'name.max' => ':Attribute tối đa :max ký tự.',
        ];
    }

    public function attributes(): array
    {
        return ['name' => 'Tên tài khoản'];
    }
}
