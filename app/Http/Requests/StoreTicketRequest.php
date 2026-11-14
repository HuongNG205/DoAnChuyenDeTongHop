<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesRichText;
use App\Models\Ticket;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTicketRequest extends FormRequest
{
    use ValidatesRichText;

    /** Chỉ customer được tạo ticket (xem TicketPolicy::create). */
    public function authorize(): bool
    {
        return $this->user()?->can('create', Ticket::class) ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            // Tiêu đề là văn bản thuần: bỏ khoảng trắng thừa / xuống dòng.
            'title' => trim((string) preg_replace('/\s+/u', ' ', (string) $this->input('title'))),
            'message' => $this->sanitizedMessage(),
        ]);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'priority' => ['required', Rule::in(array_keys(config('helpdesk.priority')))],
            'message' => $this->richTextRules(),
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Vui lòng nhập :attribute.',
            'title.max' => ':Attribute tối đa :max ký tự.',
            'priority.required' => 'Vui lòng chọn :attribute.',
            'priority.in' => ':Attribute không hợp lệ.',
            'message.required' => 'Vui lòng nhập :attribute.',
        ];
    }

    public function attributes(): array
    {
        return [
            'title' => 'Tiêu đề',
            'priority' => 'Mức độ ưu tiên',
            'message' => 'Nội dung',
        ];
    }
}
