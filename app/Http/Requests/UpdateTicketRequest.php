<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesRichText;
use App\Models\Ticket;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Staff/admin cập nhật ticket. Các trường đều "sometimes" để dùng được cho cả form sửa
 * lẫn ô chọn nhanh trạng thái trên danh sách.
 *
 * Tiêu đề và nội dung là của khách hàng: nhân viên (staff) bị CẤM sửa (gửi lên là bị từ chối);
 * chỉ admin được sửa (xem TicketPolicy::updateContent).
 */
class UpdateTicketRequest extends FormRequest
{
    use ValidatesRichText;

    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('ticket')) ?? false;
    }

    private function canEditContent(): bool
    {
        return $this->user()?->can('updateContent', $this->route('ticket')) ?? false;
    }

    protected function prepareForValidation(): void
    {
        // Chỉ chuẩn hóa title / message cho người có quyền sửa; với staff giữ nguyên để rule "prohibited" từ chối.
        if (! $this->canEditContent()) {
            return;
        }

        $merge = [];

        if ($this->has('title')) {
            $merge['title'] = trim((string) preg_replace('/\s+/u', ' ', (string) $this->input('title')));
        }
        if ($this->has('message')) {
            $merge['message'] = $this->sanitizedMessage();
        }

        $this->merge($merge);
    }

    public function rules(): array
    {
        $content = $this->canEditContent();

        return [
            'title' => $content ? ['sometimes', 'required', 'string', 'max:255'] : ['prohibited'],
            'message' => $content ? ['sometimes', ...$this->richTextRules()] : ['prohibited'],
            'priority' => ['sometimes', 'required', Rule::in(array_keys(config('helpdesk.priority')))],
            'status' => ['sometimes', 'required', Rule::in(array_keys(config('helpdesk.status')))],
        ];
    }

    /** Ticket "Chờ giải quyết" chỉ được phép có phản hồi của khách hàng. */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            /** @var Ticket $ticket */
            $ticket = $this->route('ticket');

            if ($validator->errors()->has('status')
                || $this->input('status') !== 'new'
                || $ticket->status === 'new') {
                return;
            }

            if ($ticket->replies()->where('user_id', '!=', $ticket->customer_id)->exists()) {
                $validator->errors()->add('status', 'Ticket đã có phản hồi của nhân viên nên không thể chuyển về “Chờ giải quyết”.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Vui lòng nhập :attribute.',
            'title.max' => ':Attribute tối đa :max ký tự.',
            'title.prohibited' => 'Nhân viên không được sửa :attribute của ticket.',
            'message.prohibited' => 'Nhân viên không được sửa :attribute của ticket.',
            'priority.required' => 'Vui lòng chọn :attribute.',
            'priority.in' => ':Attribute không hợp lệ.',
            'status.required' => 'Vui lòng chọn :attribute.',
            'status.in' => ':Attribute không hợp lệ.',
        ];
    }

    public function attributes(): array
    {
        return [
            'title' => 'tiêu đề',
            'message' => 'nội dung',
            'priority' => 'Mức độ ưu tiên',
            'status' => 'Trạng thái',
        ];
    }
}
