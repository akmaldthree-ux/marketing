@extends('layouts.app')
@section('title','Daily Sales')
@section('page-title','Daily Sales')
@section('content')
<form method="GET" class="d-flex gap-2 align-items-center mb-4 flex-wrap">
  <input type="month" name="month" value="{{ $month }}" class="form-control form-control-sm" style="width:160px">
  <select name="brand" class="form-select form-select-sm" style="width:140px">
    <option value="all" {{ $brand==='all'?'selected':'' }}>Semua Brand</option>
    @foreach(['DTHREE','HURIM','ASFARA'] as $b)<option value="{{ $b }}" {{ $brand===$b?'selected':'' }}>{{ $b }}</option>@endforeach
  </select>
  <select name="store_id" class="form-select form-select-sm" style="width:200px">
    <option value="all" {{ $storeId==='all'?'selected':'' }}>Semua Toko</option>
    @foreach($stores as $s)<option value="{{ $s->id }}" {{ $storeId==$s->id?'selected':'' }}>{{ $s->name }}</option>@endforeach
  </select>
  <button class="btn btn-primary btn-sm px-3">Filter</button>
  <a href="{{ route('export.daily-sales', ['month'=>$month,'brand'=>$brand]) }}" class="btn btn-success btn-sm">
    <i class="bi bi-download me-1"></i>Export CSV
  </a>
</form>

<div class="row g-3 mb-4">
  <div class="col-sm-4">
    <div class="stat-card">
      <div class="label">Hari Ini</div>
      <div class="value">Rp {{ number_format($summary['today']/1000000,1) }}jt</div>
    </div>
  </div>
  <div class="col-sm-4">
    <div class="stat-card">
      <div class="label">Kemarin</div>
      <div class="value">Rp {{ number_format($summary['yesterday']/1000000,1) }}jt</div>
    </div>
  </div>
  <div class="col-sm-4">
    <div class="stat-card">
      <div class="label">Bulan Ini</div>
      <div class="value">Rp {{ number_format($summary['this_month']/1000000,1) }}jt</div>
    </div>
  </div>
</div>

<div class="card mb-4">
  <div class="card-header">Tren GMV Harian</div>
  <div class="card-body"><canvas id="dailyChart" height="80"></canvas></div>
</div>

<div class="card">
  <div class="card-header">Breakdown per Toko</div>
  <div class="card-body p-0">
    <table class="table table-hover mb-0">
      <thead class="table-light"><tr><th>Toko</th><th>Brand</th><th>Platform</th><th class="text-end">GMV</th><th class="text-end">Orders</th></tr></thead>
      <tbody>
      @forelse($storeBreakdown as $row)
      <tr>
        <td class="fw-semibold">{{ $row['store']->name }}</td>
        <td><span class="badge badge-brand-{{ $row['store']->brand }}">{{ $row['store']->brand }}</span></td>
        <td>{{ $row['store']->platform }}</td>
        <td class="text-end">Rp {{ number_format($row['gmv']/1000000,2) }}jt</td>
        <td class="text-end">{{ number_format($row['orders']) }}</td>
      </tr>
      @empty
      <tr><td colspan="5" class="text-center text-muted py-4">Tidak ada data</td></tr>
      @endforelse
      </tbody>
    </table>
  </div>
</div>

@push('scripts')
<script>
const d = @json($daily);
new Chart(document.getElementById('dailyChart'), {
  type: 'bar',
  data: {
    labels: d.map(x=>x.day),
    datasets: [{label:'GMV',data:d.map(x=>x.gmv),backgroundColor:'rgba(124,111,247,.7)',borderRadius:4}]
  },
  options: {responsive:true,plugins:{legend:{display:false}},scales:{y:{ticks:{callback:v=>'Rp '+new Intl.NumberFormat('id').format(v)}}}}
});
</script>
@endpush
@endsection
