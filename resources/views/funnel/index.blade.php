@extends('layouts.app')
@section('title','Funnel')
@section('page-title','Analisis Funnel')
@section('content')
<form method="GET" class="d-flex gap-2 align-items-center mb-4 flex-wrap">
  <input type="month" name="month" value="{{ $month }}" class="form-control form-control-sm" style="width:160px">
  <select name="store_id" class="form-select form-select-sm" style="width:200px">
    <option value="all" {{ $storeId==='all'?'selected':'' }}>Semua Toko</option>
    @foreach($stores as $s)<option value="{{ $s->id }}" {{ $storeId==$s->id?'selected':'' }}>{{ $s->name }}</option>@endforeach
  </select>
  <button class="btn btn-primary btn-sm px-3">Filter</button>
</form>

<div class="row g-3 mb-4">
  <div class="col">
    <div class="stat-card text-center">
      <div class="label">Views</div>
      <div class="value">{{ number_format($funnel['views']) }}</div>
    </div>
  </div>
  <div class="col-auto d-flex align-items-center"><i class="bi bi-arrow-right text-muted fs-4"></i><div class="ms-2 small text-center"><div class="fw-bold text-primary">{{ $funnel['vtr'] }}%</div><div class="text-muted" style="font-size:.7rem">VTR</div></div></div>
  <div class="col">
    <div class="stat-card text-center">
      <div class="label">Visitors</div>
      <div class="value">{{ number_format($funnel['visitors']) }}</div>
    </div>
  </div>
  <div class="col-auto d-flex align-items-center"><i class="bi bi-arrow-right text-muted fs-4"></i><div class="ms-2 small text-center"><div class="fw-bold text-primary">{{ $funnel['atc_rate'] }}%</div><div class="text-muted" style="font-size:.7rem">ATC</div></div></div>
  <div class="col">
    <div class="stat-card text-center">
      <div class="label">Add to Cart</div>
      <div class="value">{{ number_format($funnel['atc']) }}</div>
    </div>
  </div>
  <div class="col-auto d-flex align-items-center"><i class="bi bi-arrow-right text-muted fs-4"></i></div>
  <div class="col">
    <div class="stat-card text-center">
      <div class="label">Checkout</div>
      <div class="value">{{ number_format($funnel['checkout']) }}</div>
    </div>
  </div>
  <div class="col-auto d-flex align-items-center"><i class="bi bi-arrow-right text-muted fs-4"></i><div class="ms-2 small text-center"><div class="fw-bold text-success">{{ $funnel['cvr'] }}%</div><div class="text-muted" style="font-size:.7rem">CVR</div></div></div>
  <div class="col">
    <div class="stat-card text-center">
      <div class="label">Buyers</div>
      <div class="value text-success">{{ number_format($funnel['buyers']) }}</div>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-header">Visualisasi Funnel</div>
  <div class="card-body"><canvas id="funnelChart" height="80"></canvas></div>
</div>

@push('scripts')
<script>
new Chart(document.getElementById('funnelChart'), {
  type: 'bar',
  data: {
    labels: ['Views','Visitors','Add to Cart','Checkout','Buyers'],
    datasets: [{
      data: [{{ $funnel['views'] }},{{ $funnel['visitors'] }},{{ $funnel['atc'] }},{{ $funnel['checkout'] }},{{ $funnel['buyers'] }}],
      backgroundColor: ['rgba(99,102,241,.8)','rgba(124,111,247,.8)','rgba(168,85,247,.8)','rgba(236,72,153,.8)','rgba(34,197,94,.8)'],
      borderRadius: 6
    }]
  },
  options: {
    indexAxis: 'y',
    responsive: true,
    plugins: {legend:{display:false}},
    scales: {x:{ticks:{callback:v=>new Intl.NumberFormat('id').format(v)}}}
  }
});
</script>
@endpush
@endsection
