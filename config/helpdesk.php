<?php

return [
    'support_email' => env('SUPPORT_EMAIL', '*@gmail.com'),

    'roles' => ['customer', 'staff', 'admin'],

    // Giá trị cột tickets.status
    'status' => [
        'new' => 'Chờ giải quyết',
        'in_progress' => 'Đang giải quyết',
        'resolved' => 'Đã giải quyết',
        'closed' => 'Đóng',
    ],

    // Giá trị cột tickets.priority
    'priority' => [
        'low' => 'Thấp',
        'medium' => 'Trung bình',
        'high' => 'Cao',
    ],

    // Giá trị cột activities.action
    'activities' => [
        'register' => 'Đăng ký',
        'login' => 'Đăng nhập',
        'logout' => 'Đăng xuất',
        'create_ticket' => 'Tạo Ticket',
        'update_ticket' => 'Cập nhật Ticket',
        'delete_ticket' => 'Xóa Ticket',
        'reply_ticket' => 'Phản hồi',
        'rate_ticket' => 'Đánh giá',
        'update_account' => 'Cập nhật tài khoản',
        'change_password' => 'Đổi mật khẩu',
        'block_user' => 'Ban',
        'unblock_user' => 'Bỏ ban',
        'delete_user' => 'Xóa tài khoản',
    ],
];
