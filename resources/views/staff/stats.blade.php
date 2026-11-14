@extends('layouts.app')
@section('title', 'Báo cáo và thống kê')
{{--
  Biến: $month (1-12)
        $labels (mảng ngày), $created, $waiting, $inProgress, $solved (mảng số liệu theo ngày)
        $ratingCounts (5 phần tử: số ticket theo 1..5 sao)
        $priorityCounts (mảng: low, medium, high)
        $totalUsers, $totalTickets, $avgRating
--}}
@section('content')
{{-- ==================== HEADER ==================== --}}
<div class="stats-header">
    <div>
        <h1 class="page-title">Báo cáo và thống kê</h1>
        <p class="stats-description">
            Tổng hợp tình hình hoạt động hệ thống Helpdesk
        </p>
    </div>

    <form method="GET" action="{{ route('staff.stats') }}"
          class="stats-filter">

        <select name="month" class="month-select">
            @for ($m = 1; $m <= 12; $m++)
                <option value="{{ $m }}" @selected($month === $m)>
                    Tháng {{ $m }}
                </option>
            @endfor
        </select>

        <select name="year" class="month-select">
            @for ($y = now()->year - 3; $y <= now()->year + 1; $y++)
                <option value="{{ $y }}" @selected($year === $y)>
                    {{ $y }}
                </option>
            @endfor
        </select>

        <button type="submit" class="btn btn-accent">
            Xem báo cáo
        </button>
    </form>
</div>

{{-- ==================== TỔNG QUAN ==================== --}}
<h2 class="section-title">1. Tổng quan hệ thống</h2>

<div class="stats-overview">

    <div class="stat-item">
        <span>Tổng Ticket</span>
        <strong>{{ number_format($totalTickets) }}</strong>
        <small>Toàn hệ thống</small>
    </div>

    <div class="stat-item">
        <span>Ticket tháng này</span>
        <strong>{{ number_format($totalMonthlyTickets) }}</strong>
        <small>Tháng {{ $month }}/{{ $year }}</small>
    </div>

    <div class="stat-item">
        <span>Tỷ lệ giải quyết</span>
        <strong>{{ number_format($resolutionRate, 1) }}%</strong>
        <small>{{ $resolvedCount }} Ticket đã giải quyết/đóng</small>
    </div>

    <div class="stat-item">
        <span>Đánh giá trung bình</span>
        <strong>{{ number_format($avgRating, 2) }}/5</strong>
        <small>{{ $totalRatings }} lượt đánh giá</small>
    </div>

    <div class="stat-item">
        <span>Tổng User</span>
        <strong>{{ number_format($totalUsers) }}</strong>
        <small>{{ $customerCount }} Customer</small>
    </div>

    <div class="stat-item">
        <span>Tổng phản hồi</span>
        <strong>{{ number_format($totalReplies) }}</strong>
        <small>Trong tháng</small>
    </div>

</div>

{{-- ==================== BIỂU ĐỒ ==================== --}}
<h2 class="section-title mt-6">2. Báo cáo đồ thị</h2>

{{-- Biểu đồ Ticket theo ngày --}}
<div class="panel">
    <h3 class="chart-title">2.1. Thống kê Ticket theo ngày</h3>
    <p class="chart-description">
        So sánh số Ticket được tạo và trạng thái hiện tại
        của các Ticket được tạo trong tháng.
    </p>

    <div class="chart-lg">
        <canvas id="chart-line"></canvas>
    </div>
</div>

<div class="stats-chart-grid mt-4">

    {{-- Trạng thái --}}
    <div class="panel">
        <h3 class="chart-title">2.2. Ticket theo trạng thái</h3>

        <div class="chart-sm">
            <canvas id="chart-status"></canvas>
        </div>
    </div>

    {{-- Rating --}}
    <div class="panel">
        <h3 class="chart-title">2.3. Phân bố đánh giá</h3>

        <div class="chart-sm">
            <canvas id="chart-rating"></canvas>
        </div>
    </div>

    {{-- Priority --}}
    <div class="panel">
        <h3 class="chart-title">2.4. Mức độ ưu tiên</h3>

        <div class="chart-sm">
            <canvas id="chart-priority"></canvas>
        </div>
    </div>

    {{-- Reply --}}
    <div class="panel">
        <h3 class="chart-title">2.5. Số phản hồi theo ngày</h3>

        <div class="chart-sm">
            <canvas id="chart-reply"></canvas>
        </div>
    </div>

</div>

{{-- ==================== BÁO CÁO CHỮ ==================== --}}
<h2 class="section-title mt-6">3. Báo cáo và nhận xét</h2>

<div class="report-text">

    <div class="report-item">
        <h3>Tình hình xử lý Ticket</h3>
        <p>{{ $textReports['status'] }}</p>
    </div>

    <div class="report-item">
        <h3>Hiệu quả giải quyết</h3>
        <p>{{ $textReports['resolution'] }}</p>
    </div>

    <div class="report-item">
        <h3>Mức độ hài lòng của khách hàng</h3>
        <p>{{ $textReports['rating'] }}</p>
    </div>

    <div class="report-item">
        <h3>Mức độ ưu tiên</h3>
        <p>{{ $textReports['priority'] }}</p>
    </div>

</div>

{{-- ==================== BẢNG CHI TIẾT ==================== --}}
<h2 class="section-title mt-6">4. Bảng báo cáo chi tiết</h2>

{{-- Trạng thái --}}
<div class="panel report-table-panel">
    <h3 class="chart-title">4.1. Ticket theo trạng thái</h3>

    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Trạng thái</th>
                    <th>Số lượng</th>
                    <th>Tỷ lệ</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($statuses as $key => $label)
                    @php
                        $count = $statusCounts[$key];
                        $rate = $totalMonthlyTickets > 0
                            ? $count / $totalMonthlyTickets * 100
                            : 0;
                    @endphp

                    <tr>
                        <td>{{ $label }}</td>
                        <td>{{ number_format($count) }}</td>
                        <td>{{ number_format($rate, 1) }}%</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Ưu tiên --}}
<div class="panel report-table-panel mt-4">
    <h3 class="chart-title">4.2. Ticket theo mức độ ưu tiên</h3>

    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Mức độ</th>
                    <th>Số lượng</th>
                    <th>Tỷ lệ</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($priorities as $key => $label)
                    @php
                        $count = $priorityCounts[$key];
                        $rate = $totalMonthlyTickets > 0
                            ? $count / $totalMonthlyTickets * 100
                            : 0;
                    @endphp

                    <tr>
                        <td>{{ $label }}</td>
                        <td>{{ number_format($count) }}</td>
                        <td>{{ number_format($rate, 1) }}%</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Rating --}}
<div class="panel report-table-panel mt-4">
    <h3 class="chart-title">4.3. Phân bố đánh giá khách hàng</h3>

    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Mức đánh giá</th>
                    <th>Số lượng</th>
                    <th>Tỷ lệ</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($ratingCounts as $index => $count)
                    <tr>
                        <td>{{ $index + 1 }} sao</td>
                        <td>{{ number_format($count) }}</td>
                        <td>
                            {{ $totalRatings > 0
                                ? number_format($count / $totalRatings * 100, 1)
                                : 0 }}%
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- ==================== THÔNG TIN BỔ SUNG ==================== --}}
<h2 class="section-title mt-6">5. Thông tin bổ sung</h2>

<div class="panel">
    <div class="stats-extra-grid">
        <div>
            <span>Customer</span>
            <strong>{{ number_format($customerCount) }}</strong>
        </div>

        <div>
            <span>Staff</span>
            <strong>{{ number_format($staffCount) }}</strong>
        </div>

        <div>
            <span>Admin</span>
            <strong>{{ number_format($adminCount) }}</strong>
        </div>

        <div>
            <span>User bị khóa</span>
            <strong>{{ number_format($blockedUsers) }}</strong>
        </div>

        <div>
            <span>Hoạt động trong tháng</span>
            <strong>{{ number_format($totalActivities) }}</strong>
        </div>

        <div>
            <span>Thời gian xử lý trung bình</span>
            <strong>{{ number_format($avgResolutionHours, 1) }} giờ</strong>
        </div>

        <div>
            <span>Đánh giá từ 4 sao trở lên</span>
            <strong>{{ number_format($positiveRate, 1) }}%</strong>
        </div>
    </div>
</div>

{{-- ==================== EXPORT ==================== --}}
<div class="export stats-export">
    <a href="{{ route('staff.stats.export', [
        'month' => $month,
        'year' => $year
    ]) }}" class="btn btn-accent">
        Xuất báo cáo CSV
    </a>
</div>

@endsection

{{-- ==================== CHART JS ==================== --}}
@push('scripts')

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>

<script>
    Chart.defaults.font.family = 'Roboto, sans-serif';

    const labels = @json($labels);

    const commonOptions = {
        responsive: true,
        maintainAspectRatio: false
    };

    // 1. Ticket theo ngày
    new Chart(document.getElementById('chart-line'), {
        type: 'line',

        data: {
            labels: labels,

            datasets: [
                {
                    label: 'Ticket tạo mới',
                    data: @json($created),
                    borderColor: '#FA8F7A',
                    backgroundColor: '#FA8F7A22',
                    fill: true,
                    tension: 0.3
                },
                {
                    label: 'Chờ giải quyết',
                    data: @json($dailyStatus['new']),
                    borderColor: '#D8B4FE',
                    tension: 0.3
                },
                {
                    label: 'Đang giải quyết',
                    data: @json($dailyStatus['in_progress']),
                    borderColor: '#EAB308',
                    tension: 0.3
                },
                {
                    label: 'Đã giải quyết',
                    data: @json($dailyStatus['solved']),
                    borderColor: '#4DAA94',
                    tension: 0.3
                },
                {
                    label: 'Đã đóng',
                    data: @json($dailyStatus['closed']),
                    borderColor: '#64748B',
                    tension: 0.3
                }
            ]
        },

        options: {
            ...commonOptions,
            interaction: {
                intersect: false,
                mode: 'index'
            },
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // 2. Ticket theo trạng thái
    new Chart(document.getElementById('chart-status'), {
        type: 'doughnut',

        data: {
            labels: [
                'Chờ giải quyết',
                'Đang giải quyết',
                'Đã giải quyết',
                'Đã đóng'
            ],

            datasets: [{
                data: @json(array_values($statusCounts)),
                backgroundColor: [
                    '#D8B4FE',
                    '#FFF176',
                    '#4DAA94',
                    '#64748B'
                ]
            }]
        },

        options: {
            ...commonOptions,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // 3. Đánh giá
    new Chart(document.getElementById('chart-rating'), {
        type: 'bar',

        data: {
            labels: ['1 sao', '2 sao', '3 sao', '4 sao', '5 sao'],

            datasets: [{
                label: 'Số lượt đánh giá',
                data: @json($ratingCounts),
                backgroundColor: [
                    '#A5BDE8',
                    '#6EE7D0',
                    '#000000',
                    '#38CFFF',
                    '#B79AEA'
                ],
                borderRadius: 6
            }]
        },

        options: {
            ...commonOptions,
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });

    // 4. Mức độ ưu tiên
    new Chart(document.getElementById('chart-priority'), {
        type: 'pie',

        data: {
            labels: ['Thấp', 'Trung bình', 'Cao'],

            datasets: [{
                data: @json(array_values($priorityCounts)),
                backgroundColor: [
                    '#7FF5EE',
                    '#B8FF8F',
                    '#FF8FB8'
                ]
            }]
        },

        options: {
            ...commonOptions,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // 5. Phản hồi theo ngày
    new Chart(document.getElementById('chart-reply'), {
        type: 'bar',

        data: {
            labels: labels,

            datasets: [{
                label: 'Số phản hồi',
                data: @json($dailyReplies),
                backgroundColor: '#60A5FA',
                borderRadius: 5
            }]
        },

        options: {
            ...commonOptions,
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
</script>

@endpush
