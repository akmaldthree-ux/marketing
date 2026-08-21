@extends('layouts.app')
@section('title','Daily Sales')
@section('page-title','Daily Sales')
@section('content')
<x-date-range-filter :date-from="$dateFrom" :date-to="$dateTo">
  <select name="brand" class="form-select form-select-sm" style="width:130px;border-radius:var(--rounded-full)">
    <option value="all" {{ $brand==='all'?'selected':'' }}>Semua Brand</option>
    @foreach(['DTHREE','HURIM','ASFARA'] as $b)<option value="{{ $b }}" {{ $brand===$b?'selected':'' }}>{{ $b }}</option>@endforeach
  </select>
  <select name="store_id" class="form-select form-select-sm" style="width:180px;border-radius:var(--rounded-full)">
    <option value="all" {{ $storeId==='all'?'selected':'' }}>Semua Toko</option>
    @foreach($stores as $s)<option value="{{ $s->id }}" {{ $storeId==$s->id?'selected':'' }}>{{ $s->name }}</option>@endforeach
  </select>
  <a href="{{ route('export.daily-sales', ['date_from'=>$dateFrom,'date_to'=>$dateTo,'brand'=>$brand]) }}"
     class="btn btn-sm" style="border-radius:var(--rounded-full);border:1px solid var(--color-hairline);background:var(--color-surface-card)">
    <i class="bi bi-download me-1"></i>Export CSV
  </a>
</x-date-range-filter>

{{-- Summary strip --}}
<div class="row g-3 mb-4">
  <div class="col-6 col-sm-3">
    <div class="stat-card">
      <div class="label">Hari Ini</div>
      <div class="value" style="font-size:1.3rem">Rp {{ number_format($summary['today']/1000000,1) }}jt</div>
    </div>
  </div>
  <div class="col-6 col-sm-3">
    <div class="stat-card">
      <div class="label">Kemarin</div>
      <div class="value" style="font-size:1.3rem">Rp {{ number_format($summary['yesterday']/1000000,1) }}jt</div>
    </div>
  </div>
  <div class="col-6 col-sm-3">
    <div class="stat-card">
      <div class="label">Rata-rata/hari</div>
      <div class="value" style="font-size:1.3rem">Rp {{ number_format($summary['avg_daily']/1000000,1) }}jt</div>
      <div class="delta delta-neutral">Periode dipilih</div>
    </div>
  </div>
  <div class="col-6 col-sm-3">
    <div class="stat-card">
      <div class="label">{{ \Carbon\Carbon::now()->translatedFormat('F Y') }}</div>
      <div class="value" style="font-size:1.3rem">Rp {{ number_format($summary['this_month']/1000000,1) }}jt</div>
    </div>
  </div>
</div>

{{-- Charts: Bar + Running Total --}}
<div class="row g-3 mb-4">
  <div class="col-lg-7">
    <div class="card h-100">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span>GMV Harian</span>
        <small class="text-muted">Total: Rp {{ number_format($summary['total_range']/1000000,1) }}jt &bull; {{ number_format($summary['total_orders']) }} orders</small>
      </div>
      <div class="card-body"><canvas id="dailyChart" height="130"></canvas></div>
    </div>
  </div>
  <div class="col-lg-5">
    <div class="card h-100">
      <div class="card-header">Kumulatif GMV</div>
      <div class="card-body"><canvas id="cumulativeChart" height="130"></canvas></div>
    </div>
  </div>
</div>

{{-- Daily table --}}
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <span>Detail Harian</span>
    @if($summary['best_day'])
    <small class="text-muted">Hari terbaik: <strong>{{ $summary['best_day']->day }}</strong> — Rp {{ number_format($summary['best_day']->gmv/1000000,1) }}jt</small>
    @endif
  </div>
  <div class="card-body p-0" style="overflow-x:auto">
    <table class="table table-hover table-sm mb-0" style="font-size:.82rem;min-width:600px">
      <thead class="table-light">
        <tr>
          <th>Tanggal</th>
          <th class="text-end">GMV</th>
          <th class="text-end">Orders</th>
          <th class="text-end">AOV</th>
          <th class="text-center" style="width:100px">vs Hari Sebelumnya</th>
          <th class="text-end">Kumulatif</th>
        </tr>
      </thead>
      <tbody>
      @forelse($dailyWithDelta as $row)
      <tr>
        <td class="fw-semibold">{{ \Carbon\Carbon::parse($row->day)->translatedFormat('D, d M') }}</td>
        <td class="text-end">Rp {{ number_format($row->gmv/1000000,2) }}jt</td>
        <td class="text-end">{{ number_format($row->orders) }}</td>
        <td class="text-end">Rp {{ number_format($row->aov/1000,0) }}rb</td>
        <td class="text-center">
          @if($row->delta !== null)
            <span class="badge {{ $row->delta >= 0 ? 'bg-success' : 'bg-danger' }} bg-opacity-10 {{ $row->delta >= 0 ? 'text-success' : 'text-danger' }}" style="font-size:.75rem">
              {{ $row->delta >= 0 ? '▲' : '▼' }} {{ abs($row->delta) }}%
            </span>
          @else
            <span class="text-muted">—</span>
          @endif
        </td>
        <td class="text-end text-muted">Rp {{ number_format($row->running/1000000,1) }}jt</td>
      </tr>
      @empty
      <tr><td colspan="6" class="text-center text-muted py-4">Tidak ada data untuk periode ini</td></tr>
      @endforelse
      </tbody>
      @if($dailyWithDelta->count() > 1)
      <tfoot class="table-light fw-semibold">
        <tr>
          <td>Total</td>
          <td class="text-end">Rp {{ number_format($summary['total_range']/1000000,2) }}jt</td>
          <td class="text-end">{{ number_format($summary['total_orders']) }}</td>
          <td class="text-end">Rp {{ number_format($summary['total_orders']>0?$summary['total_range']/$summary['total_orders']:0/1000,0) }}rb</td>
          <td></td>
          <td></td>
        </tr>
      </tfoot>
      @endif
    </table>
  </div>
</div>

@push('scripts')
<script>
const d = @json($dailyWithDelta->values());
const labels = d.map(x => x.day);

// Bar chart
new Chart(document.getElementById('dailyChart'), {
  type: 'bar',
  data: {
    labels,
    datasets: [{
      label: 'GMV',
      data: d.map(x => x.gmv),
      backgroundColor: d.map(x => x.delta !== null && x.delta < 0 ? 'rgba(239,68,68,.7)' : 'rgba(124,111,247,.7)'),
      borderRadius: 4
    }]
  },
  options: {
    responsive: true,
    plugins: { legend: { display: false } },
    scales: { y: { ticks: { callback: v => 'Rp ' + new Intl.NumberFormat('id').format(v) } } }
  }
});

// Cumulative line chart
new Chart(document.getElementById('cumulativeChart'), {
  type: 'line',
  data: {
    labels,
    datasets: [{
      label: 'Kumulatif',
      data: d.map(x => x.running),
      borderColor: '#10b981',
      backgroundColor: 'rgba(16,185,129,.1)',
      tension: .3,
      fill: true,
      pointRadius: 3
    }]
  },
  options: {
    responsive: true,
    plugins: { legend: { display: false } },
    scales: { y: { ticks: { callback: v => 'Rp ' + new Intl.NumberFormat('id').format(v) } } }
  }
});
</script>
@endpush
@endsection
