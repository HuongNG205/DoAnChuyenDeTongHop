<?php

namespace App\Http\Requests\Concerns;

use App\Support\HtmlSanitizer;
use Closure;

/**
 * Dùng chung cho các request có trường "message" là HTML từ rich editor
 * (tạo ticket, trả lời ticket, sau này là sửa ticket).
 */
trait ValidatesRichText
{
    /** Lọc HTML ngay trước khi validate -> dữ liệu validated() luôn là HTML đã sạch. */
    protected function sanitizedMessage(): string
    {
        return app(HtmlSanitizer::class)->clean($this->input('message'));
    }

    /** Các rule cho trường message (đã lọc HTML). */
    protected function richTextRules(): array
    {
        return [
            'bail',
            'required',
            'string',
            function (string $attribute, mixed $value, Closure $fail) {
                if (app(HtmlSanitizer::class)->isBlank($value)) {
                    $fail('Vui lòng nhập nội dung.');
                }

                // Cột message là TEXT (tối đa 65.535 byte); chừa dư cho an toàn.
                if (strlen($value) > 60000) {
                    $fail('Nội dung quá dài, vui lòng rút gọn.');
                }
            },
        ];
    }
}
