@extends('layouts.app')
@section('title','Analisis Produk')
@section('page-title','Analisis Produk')
@section('content')

<form method="GET" class="d-flex gap-2 align-items-center mb-4 flex-wrap">
  <input type="month" name="month" value="{{ $month }}" class="form-control form-control-sm" style="width:160px" onchange="this.form.submit()">
  <select name="brand" class="form-select form-select-sm" style="width:140px" onchange="this.form.submit()">
    <option value="all" {{ $brand==='all'?'selected':'' }}>Semua Brand</option>
    @foreach(['DTHREE','HURIM','ASFARA'] as $b)<option value="{{ $b }}" {{ $brand===$b?'selected':'' }}>{{ $b }}</option>@endforeach
  </select>
</form>

@if($skus->isEmpty())
<div class="card"><div class="card-body text-center text-muted py-5">
  <i class="bi bi-box-seam fs-1 d-block mb-2 opacity-25"></i>
  Belum ada data produk untuk periode ini.
</div></div>
@else

{{-- Top 3 Produk Cards --}}
<div class="row g-3 mb-4">
@foreach($skus->take(3) as $i => $sku)
<div class="col-md-4">
  <div class="card h-100 {{ $i===0?'border-warning':'' }}">
    <div class="card-body">
      <div class="d-flex justify-content-between mb-2">
        <span class="badge bg-{{ $i===0?'warning text-dark':($i===1?'secondary':'light text-dark') }}">
          #{{ $i+1 }} {{ $i===0?'🏆':'' }}
        </span>
        @if($sku->has_hpp)
        <span class="badge {{ $sku->margin_pct>=40?'bg-success':($sku->margin_pct>=20?'bg-warning text-dark':'bg-danger') }}">
          Margin {{ $sku->margin_pct }}%
        </span>
        @endif
      </div>
      <div class="fw-semibold mb-1">{{ $sku->product_name }}</div>
      <code class="text-muted" style="font-size:.75rem">{{ $sku->product_sku }}</code>
      <div class="row g-1 mt-2 text-center">
        <div class="col-6">
          <div style="font-size:.65rem;color:#6b7280">GMV</div>
          <div class="fw-bold text-primary">Rp {{ number_format($sku->total_gmv/1000000,1) }}jt</div>
        </div>
        <div class="col-6">
          <div style="font-size:.65rem;color:#6b7280">Terjual</div>
          <div class="fw-bold">{{ number_format($sku->total_qty) }} pcs</div>
        </div>
        @if($sku->has_hpp)
        <div class="col-6">
          <div style="font-size:.65rem;color:#6b7280">Total HPP</div>
          <div class="fw-semibold text-danger">Rp {{ number_format($sku->total_hpp/1000000,1) }}jt</div>
        </div>
        <div class="col-6">
          <div style="font-size:.65rem;color:#6b7280">Profit</div>
          <div class="fw-semibold {{ $sku->total_profit>0?'text-success':'text-danger' }}">
            Rp {{ number_format($sku->total_profit/1000000,1) }}jt
          </div>
        </div>
        @endif
      </div>
    </div>
  </div>
</div>
@endforeach
</div>

{{-- Tabel lengkap --}}
<div class="card mb-4">
  <div class="card-header">Top Produk by GMV</div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-sm table-hover mb-0" style="font-size:.8rem;">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>SKU</th>
            <th>Nama Produk</th>
            <th class="text-end">Terjual</th>
            <th class="text-end">GMV</th>
            <th class="text-end">Avg Harga</th>
            <th class="text-end">Total HPP</th>
            <th class="text-end">Profit</th>
            <th class="text-end">Margin</th>
            <th class="text-end">Cancel%</th>
          </tr>
        </thead>
        <tbody>
        @foreach($skus as $i => $sku)
        <tr>
          <td class="text-muted">{{ $i+1 }}</td>
          <td><code>{{ $sku->product_sku }}</code></td>
          <td>{{ $sku->product_name }}</td>
          <td class="text-end">{{ number_format($sku->total_qty) }}</td>
          <td class="text-end fw-semibold text-primary">Rp {{ number_format($sku->total_gmv/1000000,1) }}jt</td>
          <td class="text-end">Rp {{ number_format($sku->avg_price/1000,0) }}k</td>
          <td class="text-end text-danger">
            {{ $sku->has_hpp ? 'Rp '.number_format($sku->total_hpp/1000000,1).'jt' : '<span class="text-muted">—</span>' }}
          </td>
          <td class="text-end {{ $sku->total_profit>0?'text-success fw-semibold':'text-danger' }}">
            {{ $sku->has_hpp ? 'Rp '.number_format($sku->total_profit/1000000,1).'jt' : '<span class="text-muted">—</span>' }}
          </td>
          <td class="text-end">
            @if($sku->has_hpp)
            <span class="badge {{ $sku->margin_pct>=40?'bg-success':($sku->margin_pct>=20?'bg-warning text-dark':'bg-danger') }}">
              {{ $sku->margin_pct }}%
            </span>
            @else
            <span class="text-muted small">No HPP</span>
            @endif
          </td>
          <td class="text-end {{ ($cancelRates[$sku->product_sku]??0)>10?'text-danger fw-semibold':'' }}">
            {{ $cancelRates[$sku->product_sku] ?? 0 }}%
          </td>
        </tr>
        @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>

{{-- Chart tren top 5 --}}
@if($trend->isNotEmpty())
<div class="card">
  <div class="card-header">Tren GMV 3 Bulan — Top 5 Produk</div>
  <div class="card-body"><canvas id="trendChart" height="80"></canvas></div>
</div>
@endif

@endif

@push('scripts')
@if(!$skus->isEmpty() && $trend->isNotEmpty())
<script>
const trendLabels = @json($trendLabels);
const trendData   = @json($trend);
const colors = ['#7c6ff7','#f59e0b','#10b981','#ef4444','#3b82f6'];
const skuKeys = Object.keys(trendData);

new Chart(document.getElementById('trendChart'), {
  type: 'line',
  data: {
    labels: trendLabels,
    datasets: skuKeys.map((sku, i) => ({
      label: sku,
      data: trendLabels.map(lbl => {
        const row = trendData[sku].find(r => r.period === lbl);
        return row ? row.gmv : 0;
      }),
      borderColor: colors[i],
      backgroundColor: colors[i] + '22',
      tension: .3,
      fill: false,
    }))
  },
  options: {
    responsive: true,
    scales: { y: { ticks: { callback: v => 'Rp ' + new Intl.NumberFormat('id').format(v) } } },
    plugins: { legend: { position: 'top' } }
  }
});
</script>
@endif
@endpush
@endsection
