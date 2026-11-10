@extends('layouts.app')
@section('title', 'Thống kê')
{{--
  Biến: $month (1-12)
        $labels (mảng ngày), $created, $waiting, $inProgress, $resolved (mảng số liệu theo ngày)
        $ratingCounts (5 phần tử: số ticket theo 1..5 sao)
        $priorityCounts (mảng: low, medium, high)
        $totalUsers, $totalTickets, $avgRating
--}}
@section('content')
<div class="card p-6">
    <div class="flex items-center justify-between">
        <h1 class="text-xl font-bold">Thống kê Ticket</h1>
        <form method="GET" action="{{ route('staff.stats') }}">
            <select name="month" onchange="this.form.submit()" class="rounded border border-black px-2 py-1 text-sm">
                @for ($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}" @selected((int) request('month', $month) === $m)>Tháng {{ $m }}</option>
                @endfor
            </select>
        </form>
    </div>
    <div class="mt-4 h-72"><canvas id="chart-line"></canvas></div>
</div>

<div class="mt-4 grid gap-4 lg:grid-cols-[1fr_auto_1fr] lg:items-start">
    <div class="rounded-2xl bg-neutral-50 p-4">
        <h2 class="mb-2 text-sm font-bold">Ticket theo rating</h2>
        <div class="h-52"><canvas id="chart-rating"></canvas></div>
    </div>

    <div class="space-y-1 text-xl">
        <p>Tổng user: {{ $totalUsers }}</p>
        <p>Tổng Ticket: {{ $totalTickets }}</p>
        <p>Trung bình đánh giá: {{ number_format($avgRating, 1) }}</p>
    </div>

    <div class="rounded-2xl bg-neutral-50 p-4">
        <h2 class="mb-2 text-sm font-bold">Ticket theo mức độ ưu tiên</h2>
        <div class="h-52"><canvas id="chart-priority"></canvas></div>
    </div>
</div>

<div class="mt-6 text-center">
    <a href="{{ route('staff.stats.export', ['month' => request('month', $month)]) }}" class="btn">Xuất báo cáo</a>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
    Chart.defaults.font.family = 'Roboto, sans-serif';
    var common = { responsive: true, maintainAspectRatio: false };

    function area(label, data, color) {
        return { label: label, data: data, borderColor: color, backgroundColor: color + '99', fill: true, tension: 0.4, pointRadius: 0 };
    }

    new Chart(document.getElementById('chart-line'), {
        type: 'line',
        data: {
            labels: @json($labels),
            datasets: [
                area('Được tạo', @json($created), '#FA8F7A'),
                area('Chờ giải quyết', @json($waiting), '#D8B4FE'),
                area('Đang giải quyết', @json($inProgress), '#FFF176'),
                area('Đã giải quyết', @json($resolved), '#4DAA94')
            ]
        },
        options: Object.assign({}, common, { plugins: { legend: { position: 'bottom' } } })
    });

    new Chart(document.getElementById('chart-rating'), {
        type: 'bar',
        data: {
            labels: ['1', '2', '3', '4', '5'],
            datasets: [{ data: @json($ratingCounts), backgroundColor: ['#A5BDE8', '#6EE7D0', '#000000', '#38CFFF', '#B79AEA'], borderRadius: 6 }]
        },
        options: Object.assign({}, common, { plugins: { legend: { display: false } } })
    });

    new Chart(document.getElementById('chart-priority'), {
        type: 'pie',
        data: {
            labels: ['Trung bình', 'Cao', 'Thấp'],
            datasets: [{ data: @json($priorityCounts), backgroundColor: ['#B8FF8F', '#FF8FB8', '#7FF5EE'] }]
        },
        options: Object.assign({}, common, { plugins: { legend: { position: 'right' } } })
    });
</script>
@endpush
