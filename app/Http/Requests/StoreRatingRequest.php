<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRatingRequest extends FormRequest
{
    /** Chỉ customer là chủ ticket, ticket đã giải quyết/đóng (xem TicketPolicy::rate). */
    public function authorize(): bool
    {
        return $this->user()?->can('rate', $this->route('ticket')) ?? false;
    }

    public function rules(): array
    {
        return [
            'stars' => ['required', 'integer', 'between:1,5'],
        ];
    }

    public function messages(): array
    {
        return [
            'stars.required' => 'Vui lòng chọn số sao.',
            'stars.integer' => ':Attribute không hợp lệ.',
            'stars.between' => ':Attribute phải từ :min đến :max.',
        ];
    }

    public function attributes(): array
    {
        return ['stars' => 'Số sao'];
    }
}
