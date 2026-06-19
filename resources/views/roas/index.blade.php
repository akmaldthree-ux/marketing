@extends('layouts.app')
@section('title','ROAS Detail — DSM Intelligence')
@section('page-title','ROAS Detail')
@section('breadcrumb')<li class="breadcrumb-item active">ROAS Detail</li>@endsection
@section('header-actions')
<form class="d-flex gap-2" method="GET">
    <select name="brand" class="form-select form-select-sm" style="width:120px;" onchange="this.form.submit()"><option value="all" {{ $brand==='all'?'selected':'' }}>Semua Brand</option>@foreach(['DTHREE','HURIM','ASFARA'] as $b)<option value="{{ $b }}" {{ $brand===$b?'selected':'' }}>{{ $b }}</option>@endforeach</select>
    <input type="month" name="month" class="form-control form-control-sm" value="{{ $month }}" onchange="this.form.submit()" style="width:150px;">
</form>
@endsection
@section('content')
<div class="row g-3 mb-4">
@foreach($platformBreakdown as $plat=>$pb)
<div class="col-md-4"><div class="metric-card"><div class="metric-label mb-2">{{ $plat }}</div><div class="row">
    <div class="col-4 text-center"><div class="small text-muted">Spend</div><div class="num fw-bold" style="font-size:0.9rem;">Rp {{ number_format($pb['spend']/1e6,1) }}Jt</div></div>
    <div class="col-4 text-center border-start border-end"><div class="small text-muted">GMV Ads</div><div class="num fw-bold" style="font-size:0.9rem;">Rp {{ number_format($pb['gmv']/1e6,1) }}Jt</div></div>
    <div class="col-4 text-center"><div class="small text-muted">ROAS</div><div class="num fw-bold fs-5 {{ $pb['roas']>=3?'text-success':($pb['roas']>=2?'text-warning':'text-danger') }}">{{ $pb['roas'] }}x</div></div>
</div></div></div>
@endforeach
</div>
<div class="section-card"><div class="section-card-header"><h6><i class="bi bi-table me-2"></i>ROAS Per Toko</h6><span class="text-muted small">Target ROAS: ≥ 3x</span></div>
<div class="table-responsive"><table class="table mb-0">
    <thead><tr><th>Toko</th><th>Brand</th><th>Platform</th><th class="text-end">Spend</th><th class="text-end">GMV Iklan</th><th class="text-end">ROAS</th><th class="text-end">Status</th></tr></thead>
    <tbody>@foreach($roasData->sortByDesc('roas') as $rd)<tr>
        <td class="fw-semibold">{{ $rd['store']->store_name }}</td>
        <td><span class="brand-{{ $rd['store']->brand }}">{{ $rd['store']->brand }}</span></td>
        <td><span class="badge bg-light text-dark border">{{ $rd['store']->platform }}</span></td>
        <td class="text-end num">Rp {{ number_format($rd['spend']/1e6,1) }}Jt</td>
        <td class="text-end num">Rp {{ number_format($rd['gmvFromAds']/1e6,1) }}Jt</td>
        <td class="text-end num fw-bold fs-6 {{ $rd['roas']>=3?'text-success':($rd['roas']>=2?'text-warning':'text-danger') }}">{{ $rd['roas'] }}x</td>
        <td class="text-end">@if($rd['roas']>=3)<span class="badge bg-success">On Target</span>@elseif($rd['roas']>=2)<span class="badge bg-warning text-dark">At Risk</span>@else<span class="badge bg-danger">Below Target</span>@endif</td>
    </tr>@endforeach</tbody>
</table></div></div>
@endsection
