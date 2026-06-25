@extends('layouts.app')
@section('title','Customer Retention')
@section('page-title','Customer & Retention')
@section('content')

<form method="GET" class="d-flex gap-2 align-items-center mb-4 flex-wrap">
  <input type="month" name="month" value="{{ $month }}" class="form-control form-control-sm" style="width:160px">
  <select name="platform" class="form-select form-select-sm" style="width:160px">
    <option value="all" {{ $platform==='all'?'selected':'' }}>Semua Platform</option>
    @foreach(['Shopee','TikTok Shop','Meta Ads'] as $p)
      <option value="{{ $p }}" {{ $platform===$p?'selected':'' }}>{{ $p }}</option>
    @endforeach
  </select>
  <button class="btn btn-primary btn-sm px-3">Filter</button>
</form>

{{-- KPI Cards --}}
<div class="row g-3 mb-4">
  <div class="col-sm-6 col-xl-3">
    <div class="stat-card">
      <div class="label">Customer Baru</div>
      <div class="value text-primary">{{ number_format($newCustomers) }}</div>
      <div class="delta text-muted">First-time buyer bulan ini</div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="stat-card">
      <div class="label">Repeat Buyer</div>
      <div class="value text-success">{{ number_format($returningCustomers) }}</div>
      <div class="delta text-muted">Customer yang kembali</div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="stat-card">
      <div class="label">Repeat Rate</div>
      <div class="value {{ $repeatRate >= 30 ? 'text-success' : ($repeatRate >= 15 ? 'text-warning' : 'text-danger') }}">
        {{ $repeatRate }}%
      </div>
      <div class="delta text-muted">
        {{ $repeatRate >= 30 ? 'Sangat baik' : ($repeatRate >= 15 ? 'Cukup baik' : 'Perlu ditingkatkan') }}
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="stat-card">
      <div class="label">Total Orders</div>
      <div class="value">{{ number_format($newCustomers + $returningCustomers) }}</div>
      <div class="delta text-muted">Orders bulan ini</div>
    </div>
  </div>
</div>

<div class="row g-3 mb-4">
  {{-- Donut chart new vs returning --}}
  <div class="col-lg-4">
    <div class="card h-100">
      <div class="card-header">Komposisi Customer</div>
      <div class="card-body d-flex align-items-center justify-content-center">
        <canvas id="retentionChart" height="200"></canvas>
      </div>
    </div>
  </div>

  {{-- Tren repeat rate 6 bulan --}}
  <div class="col-lg-8">
    <div class="card h-100">
      <div class="card-header">Tren Repeat Rate 6 Bulan</div>
      <div class="card-body"><canvas id="trendChart" height="120"></canvas></div>
    </div>
  </div>
</div>

{{-- Top Customer --}}
<div class="card">
  <div class="card-header">Top Customer (by Total Orders)</div>
  <div class="card-body p-0">
    <table class="table table-hover mb-0" style="font-size:.85rem;">
      <thead class="table-light">
        <tr><th>#</th><th>Username</th><th>Platform</th><th class="text-end">Total Orders</th><th>Pertama Order</th><th>Terakhir Order</th></tr>
      </thead>
      <tbody>
      @forelse($topCustomers as $i => $c)
      <tr>
        <td class="text-muted">{{ $i+1 }}</td>
        <td class="fw-semibold">{{ $c->username }}</td>
        <td><span class="badge bg-light text-dark border">{{ $c->platform }}</span></td>
        <td class="text-end fw-bold">{{ $c->total_orders }}</td>
        <td>{{ $c->first_order_date?->format('d/m/Y') ?? '—' }}</td>
        <td>{{ $c->last_order_date?->format('d/m/Y') ?? '—' }}</td>
      </tr>
      @empty
      <tr><td colspan="6" class="text-center text-muted py-4">Belum ada data customer</td></tr>
      @endforelse
      </tbody>
    </table>
  </div>
</div>

@push('scripts')
<script>
new Chart(document.getElementById('retentionChart'), {
  type: 'doughnut',
  data: {
    labels: ['Customer Baru','Repeat Buyer'],
    datasets: [{
      data: [{{ $newCustomers }}, {{ $returningCustomers }}],
      backgroundColor: ['#7c6ff7','#10b981'],
      borderWidth: 2,
    }]
  },
  options: {
    responsive: true,
    plugins: {
      legend: { position: 'bottom' },
      tooltip: { callbacks: { label: ctx => ctx.label + ': ' + ctx.parsed + ' orders' } }
    }
  }
});

const trendMonths = @json($trendMonths ?? []);
if (trendMonths.length > 0) {
  new Chart(document.getElementById('trendChart'), {
    type: 'line',
    data: {
      labels: trendMonths.map(d => d.label),
      datasets: [
        {label:'Repeat Rate (%)', data:trendMonths.map(d=>d.repeat_rate), borderColor:'#10b981', backgroundColor:'rgba(16,185,129,.1)', tension:.3, fill:true, yAxisID:'y'},
        {label:'Customer Baru', data:trendMonths.map(d=>d.new_customers), borderColor:'#7c6ff7', tension:.3, yAxisID:'y1'},
      ]
    },
    options: {
      responsive:true,
      scales:{
        y:{ position:'left', ticks:{callback:v=>v+'%'}, max:100 },
        y1:{ position:'right', grid:{drawOnChartArea:false} }
      },
      plugins:{ legend:{ position:'top' } }
    }
  });
}
</script>
@endpush
@endsection
