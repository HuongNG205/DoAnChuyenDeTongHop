{{-- Khung chung cho email hệ thống. Dùng bảng + style nội tuyến để hiển thị ổn định trên Gmail/Outlook. --}}
@php
    $supportEmail = \App\Mail\HelpdeskMail::supportEmail();
@endphp
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title')</title>
    <style>
        /* Nội dung rich text (HTML do Quill tạo, đã lọc ở server) */
        .rich p { margin: 0 0 .6em; }
        .rich h1, .rich h2, .rich h3 { margin: .6em 0; }
        .rich ul, .rich ol { margin: .5em 0; padding-left: 1.5em; }
        .rich a { color: #0A84FF; }
        .rich blockquote { margin: .6em 0; padding-left: 12px; border-left: 4px solid #EAB308; color: #4B5563; }
        .rich pre { margin: .6em 0; padding: 10px; background: #262626; color: #fff; border-radius: 6px; white-space: pre-wrap; }
        .rich code { background: #E8E8E8; padding: 0 3px; border-radius: 3px; }
        .rich .ql-align-center { text-align: center; }
        .rich .ql-align-right { text-align: right; }
        .rich .ql-align-justify { text-align: justify; }
    </style>
</head>
<body style="margin:0;padding:0;background:#f3f4f6;font-family:Arial,Helvetica,sans-serif;color:#111827;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f3f4f6;padding:24px 12px;">
    <tr>
        <td align="center">
            <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="width:100%;max-width:600px;background:#ffffff;border-radius:10px;overflow:hidden;">
                <tr>
                    <td style="background:#002D13;padding:18px 24px;color:#F4D35E;font-size:22px;font-weight:bold;">
                        {{ config('app.name') }}
                    </td>
                </tr>
                <tr>
                    <td style="padding:24px;font-size:15px;line-height:1.6;">
                        @yield('body')
                    </td>
                </tr>
                <tr>
                    <td style="padding:16px 24px;background:#f9fafb;color:#6b7280;font-size:12px;line-height:1.5;">
                        Đây là email tự động từ hệ thống hỗ trợ {{ config('app.name') }}.
                        @if ($supportEmail)
                            Nếu cần hỗ trợ thêm, hãy liên hệ <a href="mailto:{{ $supportEmail }}" style="color:#6b7280;">{{ $supportEmail }}</a>.
                        @endif
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
