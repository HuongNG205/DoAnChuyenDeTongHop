<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMXPath;
use HTMLPurifier;
use HTMLPurifier_Config;

/**
 * Lọc HTML do rich editor (Quill 2) gửi lên TRƯỚC KHI lưu vào DB.
 *
 * Nội dung ticket/reply được hiển thị bằng {!! !!} và chèn vào email, nên bắt buộc
 * phải lọc ở server (không tin dữ liệu từ trình duyệt). Chỉ giữ lại đúng những thẻ
 * mà toolbar của partial.rich_editor có thể tạo ra.
 *
 * Quill 2 không xuất HTML "sạch" từ quill.root.innerHTML, nên có thêm bước
 * normalizeQuill() trước khi đưa qua HTMLPurifier:
 *   - danh sách: <ol><li data-list="bullet|ordered"> -> <ul>/<ol> đúng loại
 *   - code block: <div class="ql-code-block-container"> -> <pre>
 *   - bỏ <span class="ql-ui"> (chỉ dùng cho giao diện soạn thảo)
 */
class HtmlSanitizer
{
    private HTMLPurifier $purifier;

    public function __construct(?string $cachePath = null)
    {
        $cachePath ??= storage_path('app/purifier');

        // HTMLPurifier cần một thư mục ghi được để cache định nghĩa (nhanh hơn rất nhiều).
        if (! is_dir($cachePath)) {
            mkdir($cachePath, 0775, true);
        }

        $config = HTMLPurifier_Config::createDefault();
        $config->set('Core.Encoding', 'UTF-8');
        $config->set('Cache.SerializerPath', $cachePath);

        // Chỉ cho phép các thẻ mà toolbar Quill tạo ra.
        $config->set('HTML.Allowed', implode(',', [
            'p[class]', 'h1[class]', 'h2[class]', 'h3[class]', 'br',
            'strong', 'b', 'em', 'i', 'u', 's', 'strike',
            'span[style]', 'a[href|title]',
            'ul', 'ol', 'li[class]', 'blockquote[class]', 'pre', 'code',
        ]));

        // Màu chữ / màu nền (toolbar color, background) và căn lề (class của Quill).
        $config->set('CSS.AllowedProperties', ['color', 'background-color']);
        $config->set('Attr.AllowedClasses', ['ql-align-center', 'ql-align-right', 'ql-align-justify']);

        // Link: chỉ http, https, mailto (chặn javascript:, data:, vbscript:...).
        $config->set('URI.AllowedSchemes', ['http' => true, 'https' => true, 'mailto' => true]);
        $config->set('HTML.TargetBlank', true);   // mở tab mới + rel="noopener noreferrer"
        $config->set('HTML.Nofollow', true);

        $this->purifier = new HTMLPurifier($config);
    }

    /** Trả về HTML an toàn để lưu DB. Chuỗi rỗng nếu không có nội dung. */
    public function clean(mixed $html): string
    {
        if (! is_string($html) || trim($html) === '') {
            return '';
        }

        $html = $this->purifier->purify($this->normalizeQuill($html));

        // Bỏ các dòng trống thừa ở đầu và cuối (Quill sinh <p><br></p>).
        $empty = '<p>\s*<br\s*\/?>\s*<\/p>\s*';
        $html = preg_replace(['/^(\s*' . $empty . ')+/i', '/(' . $empty . ')+$/i'], '', $html) ?? $html;

        return trim($html);
    }

    /** true nếu HTML không có chữ nào (chỉ có thẻ rỗng, <br>, khoảng trắng, &nbsp;). */
    public function isBlank(string $html): bool
    {
        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim((string) preg_replace('/[\s\x{00A0}]+/u', ' ', $text)) === '';
    }

    // ------------------------------------------------------------------

    private function normalizeQuill(string $html): string
    {
        if (! str_contains($html, 'data-list')
            && ! str_contains($html, 'ql-code-block')
            && ! str_contains($html, 'ql-ui')) {
            return $html;
        }

        $dom = new DOMDocument();
        $previous = libxml_use_internal_errors(true);
        // Tiền tố <?xml encoding> để libxml hiểu chuỗi là UTF-8 (tiếng Việt không bị vỡ).
        $dom->loadHTML('<?xml encoding="UTF-8"><div id="__root">' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $xpath = new DOMXPath($dom);
        $hasClass = fn (string $c) => 'contains(concat(" ", normalize-space(@class), " "), " ' . $c . ' ")';

        // 1) Bỏ phần tử giao diện của editor.
        foreach (iterator_to_array($xpath->query('//span[' . $hasClass('ql-ui') . ']')) as $node) {
            $node->parentNode?->removeChild($node);
        }

        // 2) Danh sách: tách theo từng loại bullet / ordered.
        foreach (iterator_to_array($xpath->query('//ol[li[@data-list]]')) as $ol) {
            /** @var DOMElement $ol */
            $current = null;
            $currentTag = null;

            foreach (iterator_to_array($ol->childNodes) as $li) {
                if (! $li instanceof DOMElement || $li->nodeName !== 'li') {
                    continue;
                }

                $tag = $li->getAttribute('data-list') === 'ordered' ? 'ol' : 'ul';

                if ($tag !== $currentTag) {
                    $current = $dom->createElement($tag);
                    $ol->parentNode->insertBefore($current, $ol);
                    $currentTag = $tag;
                }

                $li->removeAttribute('data-list');
                $current->appendChild($li);
            }

            $ol->parentNode->removeChild($ol);
        }

        // 3) Code block: gom các dòng thành một <pre>.
        foreach (iterator_to_array($xpath->query('//div[' . $hasClass('ql-code-block-container') . ']')) as $box) {
            $lines = [];
            foreach ($xpath->query('.//div[' . $hasClass('ql-code-block') . ']', $box) as $line) {
                $lines[] = $line->textContent;
            }

            $pre = $dom->createElement('pre');
            $pre->appendChild($dom->createTextNode(implode("\n", $lines)));
            $box->parentNode->replaceChild($pre, $box);
        }

        $root = $xpath->query('//div[@id="__root"]')->item(0);
        $out = '';
        foreach ($root->childNodes as $child) {
            $out .= $dom->saveHTML($child);
        }

        return $out;
    }
}
