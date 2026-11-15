{{-- Bộ lọc dùng chung cho các danh sách ticket. Tham số: $action --}}
@include('partial.filter_box', [
    'action' => $action,
    'fields' => [
        ['name' => 'status', 'label' => 'Trạng thái', 'options' => config('helpdesk.status')],
        ['name' => 'priority', 'label' => 'Mức độ ưu tiên', 'options' => config('helpdesk.priority')],
        ['name' => 'rating', 'label' => 'Rating', 'options' => ['none' => 'Chưa đánh giá', 5 => '5 sao', 4 => '4 sao', 3 => '3 sao', 2 => '2 sao', 1 => '1 sao']],
        ['name' => 'created_from', 'label' => 'Tạo từ ngày', 'type' => 'date'],
        ['name' => 'created_to', 'label' => 'Đến ngày', 'type' => 'date'],
    ],
])
