@extends('layouts.app')
@section('title','Perbandingan Toko')
@section('page-title','Perbandingan Toko')
@section('content')

<form method="GET" class="d-flex gap-2 align-items-center mb-4 flex-wrap">
  <input type="month" name="month" value="{{ $month }}" class="form-control form-control-sm" style="width:160px" onchange="this.form.submit()">
  <select name="brand" class="form-select form-select-sm" style="width:140px" onchange="this.form.submit()">
    <option value="all" {{ $brand==='all'?'selected':'' }}>Semua Brand</option>
    @foreach(['DTHREE','HURIM','ASFARA'] as $b)<option value="{{ $b }}" {{ $brand===$b?'selected':'' }}>{{ $b }}</option>@endforeach
  </select>
</form>

{{-- Kartu ringkasan per toko --}}
<div class="row g-3 mb-4">
@foreach($data as $d)
<div class="col-md-6 col-xl-4">
  <div class="card h-100">
    <div class="card-body">
      <div class="d-flex justify-content-between align-items-start mb-2">
        <div>
          <div class="fw-semibold">{{ $d['store']->name }}</div>
          <span class="badge badge-brand-{{ $d['store']->brand }}">{{ $d['store']->brand }}</span>
          <small class="text-muted ms-1">{{ $d['store']->platform }}</small>
        </div>
        @if($d['pct'] !== null)
        <span class="badge {{ $d['pct']>=100?'bg-success':($d['pct']>=70?'bg-warning text-dark':'bg-danger') }}">
          {{ $d['pct'] }}% target
        </span>
        @endif
      </div>

      <div class="row g-2 text-center mt-1">
        <div class="col-6">
          <div style="font-size:.7rem;color:#6b7280">GMV</div>
          <div class="fw-bold text-primary" style="font-size:1rem">Rp {{ number_format($d['gmv']/1000000,1) }}jt</div>
        </div>
        <div class="col-6">
          <div style="font-size:.7rem;color:#6b7280">Orders</div>
          <div class="fw-bold" style="font-size:1rem">{{ number_format($d['orders']) }}</div>
        </div>
        <div class="col-4">
          <div style="font-size:.7rem;color:#6b7280">AOV</div>
          <div class="fw-semibold" style="font-size:.85rem">Rp {{ number_format($d['aov']/1000,0) }}k</div>
        </div>
        <div class="col-4">
          <div style="font-size:.7rem;color:#6b7280">ROAS</div>
          <div class="fw-semibold {{ $d['roas']>=3?'text-success':($d['roas']>0?'text-warning':'text-muted') }}" style="font-size:.85rem">
            {{ $d['roas'] > 0 ? $d['roas'].'x' : '—' }}
          </div>
        </div>
        <div class="col-4">
          <div style="font-size:.7rem;color:#6b7280">Cancel</div>
          <div class="fw-semibold {{ $d['cancel_rate']>5?'text-danger':'' }}" style="font-size:.85rem">{{ $d['cancel_rate'] }}%</div>
        </div>
      </div>

      @if($d['target'] > 0)
      <div class="mt-3">
        <div class="progress" style="height:6px">
          <div class="progress-bar {{ $d['pct']>=100?'bg-success':($d['pct']>=70?'bg-warning':'bg-danger') }}"
            style="width:{{ min(100,$d['pct']) }}%"></div>
        </div>
        <small class="text-muted d-block mt-1">
          Rp {{ number_format($d['gmv']/1000000,1) }}jt / Rp {{ number_format($d['target']/1000000,1) }}jt target
        </small>
      </div>
      @endif
    </div>
  </div>
</div>
@endforeach
</div>

{{-- Tabel perbandingan --}}
<div class="card">
  <div class="card-header">Tabel Perbandingan</div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-sm table-hover mb-0" style="font-size:.8rem;">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>Toko</th>
            <th>Brand</th>
            <th class="text-end">GMV</th>
            <th class="text-end">Orders</th>
            <th class="text-end">AOV</th>
            <th class="text-end">ROAS</th>
            <th class="text-end">Cancel %</th>
            <th class="text-end">Ads Spend</th>
            <th class="text-end">% Target</th>
          </tr>
        </thead>
        <tbody>
        @foreach($data as $i => $d)
        <tr>
          <td class="text-muted">{{ $i+1 }}</td>
          <td class="fw-semibold">{{ $d['store']->name }}</td>
          <td><span class="badge badge-brand-{{ $d['store']->brand }}">{{ $d['store']->brand }}</span></td>
          <td class="text-end fw-semibold text-primary">Rp {{ number_format($d['gmv']/1000000,1) }}jt</td>
          <td class="text-end">{{ number_format($d['orders']) }}</td>
          <td class="text-end">Rp {{ number_format($d['aov']/1000,0) }}k</td>
          <td class="text-end {{ $d['roas']>=3?'text-success fw-semibold':'' }}">{{ $d['roas'] > 0 ? $d['roas'].'x' : '—' }}</td>
          <td class="text-end {{ $d['cancel_rate']>5?'text-danger fw-semibold':'' }}">{{ $d['cancel_rate'] }}%</td>
          <td class="text-end">{{ $d['ads_spend'] > 0 ? 'Rp '.number_format($d['ads_spend']/1000000,1).'jt' : '—' }}</td>
          <td class="text-end">
            @if($d['pct'] !== null)
            <span class="badge {{ $d['pct']>=100?'bg-success':($d['pct']>=70?'bg-warning text-dark':'bg-danger') }}">
              {{ $d['pct'] }}%
            </span>
            @else
            <span class="text-muted">—</span>
            @endif
          </td>
        </tr>
        @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>

@push('scripts')
<script>
// Chart perbandingan GMV
const labels = @json($data->pluck('store')->pluck('name'));
const gmvs   = @json($data->pluck('gmv'));
const roas   = @json($data->pluck('roas'));

new Chart(document.createElement('canvas'), {}); // preload
</script>
@endpush
@endsection
