@extends('layouts.app')
@section('title','Dashboard')
@section('page-title','Overview Dashboard')
@section('content')
{{-- Filters --}}
<x-date-range-filter :date-from="$dateFrom" :date-to="$dateTo">
  <select name="brand" class="form-select form-select-sm" style="width:130px;border-radius:var(--rounded-full)">
    <option value="all" {{ $brand==='all'?'selected':'' }}>Semua Brand</option>
    @foreach(['DTHREE','HURIM','ASFARA'] as $b)<option value="{{ $b }}" {{ $brand===$b?'selected':'' }}>{{ $b }}</option>@endforeach
  </select>
</x-date-range-filter>

{{-- KPI Cards --}}
<div class="row g-3 mb-4">
  <div class="col-sm-6 col-xl-3">
    <div class="stat-card accent-red" data-icon="&#xf63b">
      <div class="sc-icon"><i class="bi bi-currency-dollar"></i></div>
      <div class="label">Total GMV</div>
      <div class="value">Rp {{ number_format($totalGmv/1000000,1) }}jt</div>
      @if($gmvDelta !== null)
      <div class="delta {{ $gmvDelta>=0?'delta-up':'delta-down' }}">
        <i class="bi bi-arrow-{{ $gmvDelta>=0?'up':'down' }}-short"></i>{{ abs($gmvDelta) }}% vs {{ $prevLabel }}
      </div>
      @endif
    </div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="stat-card accent-blue" data-icon="&#xf3e4">
      <div class="sc-icon"><i class="bi bi-bag-check"></i></div>
      <div class="label">Total Orders</div>
      <div class="value">{{ number_format($totalOrders) }}</div>
      <div class="delta delta-neutral"><i class="bi bi-x-circle me-1"></i>Cancel {{ $cancelRate }}%</div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="stat-card accent-green" data-icon="&#xf568">
      <div class="sc-icon"><i class="bi bi-graph-up-arrow"></i></div>
      <div class="label">Blended ROAS</div>
      <div class="value">{{ $blendedRoas }}x</div>
      <div class="delta delta-neutral">GMV dari iklan</div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="stat-card accent-purple" data-icon="&#xf4a1">
      <div class="sc-icon"><i class="bi bi-people"></i></div>
      <div class="label">Avg CVR</div>
      <div class="value">{{ $avgCvr }}%</div>
      <div class="delta delta-neutral">Visitor ke pembeli</div>
    </div>
  </div>
</div>

<div class="row g-3 mb-4">
  {{-- GMV Trend --}}
  <div class="col-lg-8">
    <div class="card">
      <div class="card-header">GMV Harian — {{ $year }}/{{ str_pad($mon,2,'0',STR_PAD_LEFT) }}</div>
      <div class="card-body"><canvas id="gmvChart" height="100"></canvas></div>
    </div>
  </div>
  {{-- Brand Progress --}}
  <div class="col-lg-4">
    <div class="card h-100">
      <div class="card-header">Progress Target Brand</div>
      <div class="card-body">
        @foreach($brandProgress as $brandName=>$bp)
        <div class="mb-3">
          <div class="d-flex justify-content-between mb-1">
            <span class="badge badge-brand-{{ $brandName }}">{{ $brandName }}</span>
            <small class="text-muted">{{ $bp['pct'] }}%</small>
          </div>
          <div class="progress" style="height:8px">
            <div class="progress-bar {{ $bp['pct']>=100?'bg-success':($bp['pct']>=70?'bg-warning':'bg-danger') }}" style="width:{{ $bp['pct'] }}%"></div>
          </div>
          <small class="text-muted d-block mt-1">Rp {{ number_format($bp['actual']/1000000,1) }}jt / Rp {{ number_format($bp['target']/1000000,1) }}jt</small>
        </div>
        @endforeach
      </div>
    </div>
  </div>
</div>

<div class="row g-3">
  {{-- Channel Trend --}}
  <div class="col-lg-7">
    <div class="card">
      <div class="card-header">Tren GMV 6 Bulan: Marketplace vs Non-MP</div>
      <div class="card-body"><canvas id="trendChart" height="100"></canvas></div>
    </div>
  </div>
  {{-- Store Leaderboard --}}
  <div class="col-lg-5">
    <div class="card">
      <div class="card-header">Top Toko by GMV</div>
      <div class="card-body p-0">
        <table class="table table-sm mb-0" style="font-size:.8rem;">
          <thead class="table-light"><tr><th>#</th><th>Toko</th><th class="text-end">GMV</th></tr></thead>
          <tbody>
          @foreach($leaderboard->take(10) as $i=>$item)
          <tr>
            <td class="text-muted">{{ $i+1 }}</td>
            <td>
              <div class="fw-semibold">{{ $item['store']->name }}</div>
              <small class="text-muted">{{ $item['store']->platform }}</small>
            </td>
            <td class="text-end fw-semibold">Rp {{ number_format($item['gmv']/1000000,1) }}jt</td>
          </tr>
          @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
const gmvData = @json($gmvTrend);
new Chart(document.getElementById('gmvChart'), {
  type: 'bar',
  data: {
    labels: gmvData.map(d=>d.day),
    datasets: [{label:'GMV',data:gmvData.map(d=>d.total),backgroundColor:'rgba(124,111,247,.7)',borderRadius:4}]
  },
  options: {responsive:true,plugins:{legend:{display:false}},scales:{y:{ticks:{callback:v=>'Rp '+new Intl.NumberFormat('id').format(v)}}}}
});

const trendData = @json($trendMonths);
new Chart(document.getElementById('trendChart'), {
  type: 'line',
  data: {
    labels: trendData.map(d=>d.label),
    datasets: [
      {label:'Marketplace',data:trendData.map(d=>d.mp),borderColor:'#7c6ff7',backgroundColor:'rgba(124,111,247,.1)',tension:.3,fill:true},
      {label:'Non-Marketplace',data:trendData.map(d=>d.non_mp),borderColor:'#f59e0b',backgroundColor:'rgba(245,158,11,.1)',tension:.3,fill:true}
    ]
  },
  options: {responsive:true,scales:{y:{ticks:{callback:v=>'Rp '+new Intl.NumberFormat('id').format(v)}}}}
});
</script>
@endpush
@endsection
