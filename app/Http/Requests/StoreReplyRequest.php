<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesRichText;
use Illuminate\Foundation\Http\FormRequest;

class StoreReplyRequest extends FormRequest
{
    use ValidatesRichText;

    /** Chủ ticket hoặc staff/admin, và ticket chưa đóng (xem TicketPolicy::reply). */
    public function authorize(): bool
    {
        return $this->user()?->can('reply', $this->route('ticket')) ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['message' => $this->sanitizedMessage()]);
    }

    public function rules(): array
    {
        return ['message' => $this->richTextRules()];
    }

    public function messages(): array
    {
        return ['message.required' => 'Vui lòng nhập :attribute.'];
    }

    public function attributes(): array
    {
        return ['message' => 'Nội dung trả lời'];
    }
}
