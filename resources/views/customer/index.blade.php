@extends('layouts.app')
@section('title', 'Customer & ROR — DSM Intelligence')
@section('page-title', 'Customer Classification & Repeat Order Rate')
@section('breadcrumb')<li class="breadcrumb-item active">Customer & ROR</li>@endsection

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
<!-- Metric Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="metric-card">
            <div class="metric-label mb-1"><i class="bi bi-people me-1"></i>Total Orders</div>
            <div class="metric-value num">{{ number_format($totalOrders) }}</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="metric-card">
            <div class="metric-label mb-1"><i class="bi bi-person-plus me-1"></i>New Customer Orders</div>
            <div class="metric-value num text-primary">{{ number_format($newOrders) }}</div>
            <div class="text-muted small">{{ $totalOrders > 0 ? number_format(($newOrders/$totalOrders)*100,1) : 0 }}% dari total</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="metric-card">
            <div class="metric-label mb-1"><i class="bi bi-arrow-repeat me-1"></i>Returning Orders</div>
            <div class="metric-value num text-success">{{ number_format($returningOrders) }}</div>
            <div class="text-muted small">{{ $totalOrders > 0 ? number_format(($returningOrders/$totalOrders)*100,1) : 0 }}% dari total</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="metric-card {{ $ror < 15 ? 'border-warning' : '' }}">
            <div class="metric-label mb-1"><i class="bi bi-heart me-1"></i>Repeat Order Rate</div>
            <div class="metric-value num {{ $ror >= 15 ? 'text-success' : 'text-warning' }}">{{ $ror }}%</div>
            <div class="small {{ $ror >= 15 ? 'text-success' : 'text-warning' }}">Target: ≥ 15%</div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <!-- New vs Returning Chart -->
    <div class="col-md-4">
        <div class="section-card">
            <div class="section-card-header"><h6><i class="bi bi-pie-chart me-2"></i>Komposisi Customer</h6></div>
            <div class="p-3 text-center">
                <canvas id="customerDonut" height="220"></canvas>
            </div>
        </div>
    </div>

    <!-- Cohort -->
    <div class="col-md-8">
        <div class="section-card">
            <div class="section-card-header">
                <h6><i class="bi bi-grid-3x3 me-2"></i>New Customer per Bulan</h6>
            </div>
            <div class="p-3">
                <canvas id="cohortChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Per Store ROR -->
<div class="section-card">
    <div class="section-card-header">
        <h6><i class="bi bi-table me-2"></i>Repeat Order Rate per Toko</h6>
    </div>
    <div class="table-responsive">
        <table class="table mb-0">
            <thead><tr><th>Toko</th><th>Brand</th><th class="text-end">Total Orders</th><th class="text-end">Returning Orders</th><th class="text-end">ROR</th><th class="text-center">Status</th></tr></thead>
            <tbody>
                @foreach($storeData->sortByDesc('ror') as $sd)
                <tr>
                    <td class="fw-semibold">{{ $sd['store']->store_name }}</td>
                    <td><span class="brand-{{ $sd['store']->brand }}">{{ $sd['store']->brand }}</span></td>
                    <td class="text-end num">{{ number_format($sd['total']) }}</td>
                    <td class="text-end num">{{ number_format($sd['returning']) }}</td>
                    <td class="text-end num fw-bold {{ $sd['ror'] >= 15 ? 'text-success' : 'text-warning' }}">{{ $sd['ror'] }}%</td>
                    <td class="text-center">
                        @if($sd['ror'] >= 15) <span class="badge-normal">On Target</span>
                        @else <span class="badge-warning-dsm">Di Bawah Target</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="alert alert-info mt-3 py-2 small">
    <i class="bi bi-info-circle me-1"></i>Konsumen yang sama berbelanja di Shopee dan TikTok Shop dianggap sebagai entitas terpisah karena perbedaan identifier antar platform.
</div>
@endsection

@section('scripts')
<script>
new Chart(document.getElementById('customerDonut'), {
    type: 'doughnut',
    data: {
        labels: ['New Customer', 'Returning Customer'],
        datasets: [{ data: [{{ $newOrders }}, {{ $returningOrders }}], backgroundColor: ['#2563EB','#16A34A'], borderWidth: 0, hoverOffset: 8 }]
    },
    options: { cutout: '65%', plugins: { legend: { position: 'bottom' }}}
});

const cohort = @json($cohort);
new Chart(document.getElementById('cohortChart'), {
    type: 'bar',
    data: {
        labels: cohort.map(c => c.month),
        datasets: [{ label: 'New Customers', data: cohort.map(c => c.new_customers), backgroundColor: '#2563EB', borderRadius: 4 }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false }},
        scales: { y: { grid: { color: '#F1F5F9' }}, x: { grid: { display: false }}}
    }
});
</script>
@endsection
