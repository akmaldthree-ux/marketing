@extends('layouts.app')
@section('title', 'Daily Sales — DSM Intelligence')
@section('page-title', 'Daily Sales')
@section('breadcrumb')<li class="breadcrumb-item active">Daily Sales</li>@endsection

@section('header-actions')
<form class="d-flex gap-2" method="GET">
    <select name="brand" class="form-select form-select-sm" style="width:120px;" onchange="this.form.submit()">
        <option value="all" {{ $brand==='all'?'selected':'' }}>Semua Brand</option>
        @foreach(['DTHREE','HURIM','ASFARA'] as $b)
        <option value="{{ $b }}" {{ $brand===$b?'selected':'' }}>{{ $b }}</option>
        @endforeach
    </select>
    <input type="month" name="month" class="form-control form-control-sm" value="{{ $month }}" onchange="this.form.submit()" style="width:150px;">
</form>
@endsection

@section('content')
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="metric-card">
            <div class="metric-label mb-1"><i class="bi bi-sun me-1"></i>Hari Ini</div>
            <div class="metric-value num">Rp {{ number_format($today/1e6,1) }}Jt</div>
            @php $dayDelta = $yesterday > 0 ? round((($today-$yesterday)/$yesterday)*100,1) : 0; @endphp
            <div class="metric-delta {{ $dayDelta >= 0 ? 'positive' : 'negative' }} mt-1">
                <i class="bi bi-arrow-{{ $dayDelta >= 0 ? 'up' : 'down' }}"></i>{{ abs($dayDelta) }}% vs kemarin
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="metric-card">
            <div class="metric-label mb-1"><i class="bi bi-calendar3 me-1"></i>Kemarin</div>
            <div class="metric-value num">Rp {{ number_format($yesterday/1e6,1) }}Jt</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="metric-card">
            <div class="metric-label mb-1"><i class="bi bi-calendar-week me-1"></i>Minggu Ini</div>
            <div class="metric-value num">Rp {{ number_format($thisWeek/1e6,1) }}Jt</div>
            @php $weekDelta = $lastWeek > 0 ? round((($thisWeek-$lastWeek)/$lastWeek)*100,1) : 0; @endphp
            <div class="metric-delta {{ $weekDelta >= 0 ? 'positive' : 'negative' }} mt-1">
                <i class="bi bi-arrow-{{ $weekDelta >= 0 ? 'up' : 'down' }}"></i>{{ abs($weekDelta) }}% vs minggu lalu
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="metric-card">
            <div class="metric-label mb-1"><i class="bi bi-calendar-check me-1"></i>Minggu Lalu</div>
            <div class="metric-value num">Rp {{ number_format($lastWeek/1e6,1) }}Jt</div>
        </div>
    </div>
</div>

<!-- Chart -->
<div class="section-card mb-4">
    <div class="section-card-header">
        <h6><i class="bi bi-bar-chart-line me-2"></i>GMV Harian — {{ \Carbon\Carbon::parse($month.'-01')->format('F Y') }}</h6>
    </div>
    <div class="p-3">
        <canvas id="dailyChart" height="200"></canvas>
    </div>
</div>

<!-- Store Breakdown -->
<div class="section-card">
    <div class="section-card-header">
        <h6><i class="bi bi-grid me-2"></i>Breakdown per Toko</h6>
    </div>
    <div class="table-responsive">
        <table class="table mb-0">
            <thead><tr><th>#</th><th>Toko</th><th>Brand</th><th>Platform</th><th class="text-end">GMV</th><th class="text-end">Orders</th><th class="text-end">AOV</th></tr></thead>
            <tbody>
                @foreach($storeBreakdown as $i => $sb)
                <tr>
                    <td class="text-muted">{{ $i+1 }}</td>
                    <td class="fw-semibold">{{ $sb['store']->store_name }}</td>
                    <td><span class="brand-{{ $sb['store']->brand }}">{{ $sb['store']->brand }}</span></td>
                    <td><span class="badge bg-light text-dark border">{{ $sb['store']->platform }}</span></td>
                    <td class="text-end num fw-bold">Rp {{ number_format($sb['gmv']/1e6,1) }}Jt</td>
                    <td class="text-end num">{{ number_format($sb['orders']) }}</td>
                    <td class="text-end num">{{ $sb['orders'] > 0 ? 'Rp '.number_format($sb['gmv']/$sb['orders']/1000,0).'K' : '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('scripts')
<script>
const daily = @json($dailySales);
new Chart(document.getElementById('dailyChart'), {
    type: 'bar',
    data: {
        labels: daily.map(d => d.day.substring(8)),
        datasets: [
            { label: 'GMV (Rp)', data: daily.map(d => d.total), backgroundColor: '#2563EB', borderRadius: 4, yAxisID: 'y' },
            { label: 'Orders', data: daily.map(d => d.orders), type: 'line', borderColor: '#16A34A', pointRadius: 3, yAxisID: 'y1' }
        ]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'bottom' }},
        scales: {
            y: { ticks: { callback: v => 'Rp '+(v/1e6).toFixed(0)+'Jt' }, grid: { color: '#F1F5F9' } },
            y1: { position: 'right', grid: { drawOnChartArea: false }, ticks: { color: '#16A34A' } },
            x: { grid: { display: false } }
        }
    }
});
</script>
@endsection
