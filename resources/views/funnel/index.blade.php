@extends('layouts.app')
@section('title','Funnel Analytics — DSM Intelligence')
@section('page-title','Funnel Analytics')
@section('breadcrumb')<li class="breadcrumb-item active">Funnel Analytics</li>@endsection
@section('header-actions')
<form class="d-flex gap-2" method="GET">
    <select name="brand" class="form-select form-select-sm" style="width:120px;" onchange="this.form.submit()"><option value="all" {{ $brand==='all'?'selected':'' }}>Semua Brand</option>@foreach(['DTHREE','HURIM','ASFARA'] as $b)<option value="{{ $b }}" {{ $brand===$b?'selected':'' }}>{{ $b }}</option>@endforeach</select>
    <select name="store_id" class="form-select form-select-sm" style="width:180px;" onchange="this.form.submit()"><option value="all">Semua Toko</option>@foreach($allStores as $s)<option value="{{ $s->id }}" {{ $storeId==$s->id?'selected':'' }}>{{ $s->store_name }}</option>@endforeach</select>
    <input type="month" name="month" class="form-control form-control-sm" value="{{ $month }}" onchange="this.form.submit()" style="width:150px;">
</form>
@endsection
@section('content')
<div class="row g-3 mb-4">
    <div class="col-md-4"><div class="metric-card border-danger"><div class="metric-label mb-1 text-danger"><i class="bi bi-exclamation-triangle me-1"></i>Total Potential Loss</div><div class="metric-value num text-danger">Rp {{ number_format($totalPotentialLoss/1e6,1) }}Jt</div><div class="text-muted mt-1" style="font-size:0.75rem;">Estimasi akumulasi semua gap konversi</div></div></div>
    <div class="col-md-4"><div class="metric-card"><div class="metric-label mb-1"><i class="bi bi-shop me-1"></i>Toko Dianalisis</div><div class="metric-value">{{ $funnelData->count() }}</div></div></div>
    <div class="col-md-4"><div class="metric-card"><div class="metric-label mb-1"><i class="bi bi-info-circle me-1"></i>Interpretasi CVR</div><div class="d-flex gap-2 mt-2"><span class="badge bg-success">≥2% BAIK</span><span class="badge bg-warning text-dark">1-2% CUKUP</span><span class="badge bg-danger">&lt;1% BURUK</span></div></div></div>
</div>
@foreach($funnelData as $fd)
@php $totalLoss=$fd['vtvLoss']+$fd['cvrLoss']+$fd['atcLoss']; @endphp
<div class="section-card mb-3">
    <div class="section-card-header">
        <div><h6 class="mb-0">{{ $fd['store']->store_name }}</h6><small class="text-muted">{{ $fd['store']->brand }} · {{ $fd['store']->platform }}</small></div>
        @if($totalLoss>0)<div class="text-danger small fw-bold"><i class="bi bi-exclamation-triangle me-1"></i>Potential Loss: Rp {{ number_format($totalLoss/1e6,1) }}Jt</div>@endif
    </div>
    <div class="p-3"><div class="row g-3">
        <div class="col-md-6">
            @foreach([['Views',$fd['views'],100],['Visitors',$fd['visitors'],$fd['views']>0?round($fd['visitors']/$fd['views']*100,1):0],['Add to Cart',$fd['atc'],$fd['views']>0?round($fd['atc']/$fd['views']*100,1):0],['Checkout',$fd['checkout'],$fd['views']>0?round($fd['checkout']/$fd['views']*100,1):0],['Pembeli Jadi',$fd['buyers'],$fd['views']>0?round($fd['buyers']/$fd['views']*100,1):0]] as [$label,$val,$pct])
            <div class="mb-2">
                <div class="d-flex justify-content-between mb-1"><small class="text-muted fw-semibold">{{ $label }}</small><small class="num">{{ number_format($val) }} ({{ $pct }}%)</small></div>
                <div class="progress" style="height:24px;border-radius:6px;"><div class="progress-bar" style="width:{{ max(5,$pct) }}%;border-radius:6px;font-size:0.75rem;line-height:24px;padding:0 8px;">{{ number_format($val) }}</div></div>
            </div>
            @endforeach
        </div>
        <div class="col-md-6"><div class="row g-2">
            <div class="col-6"><div class="p-3 rounded border text-center {{ $fd['vtvRate']>=$fd['t_vtv']?'border-success bg-success bg-opacity-10':'border-danger bg-danger bg-opacity-10' }}"><div class="small text-muted mb-1">Views→Visitor</div><div class="fw-bold num fs-5 {{ $fd['vtvRate']>=$fd['t_vtv']?'text-success':'text-danger' }}">{{ $fd['vtvRate'] }}%</div><div class="small text-muted">Target: {{ $fd['t_vtv'] }}%</div>@if($fd['vtvLoss']>0)<div class="small text-danger mt-1">-Rp {{ number_format($fd['vtvLoss']/1e6,1) }}Jt</div>@endif</div></div>
            <div class="col-6"><div class="p-3 rounded border text-center {{ $fd['atcRate']>=$fd['t_atc']?'border-success bg-success bg-opacity-10':'border-warning bg-warning bg-opacity-10' }}"><div class="small text-muted mb-1">ATC Rate</div><div class="fw-bold num fs-5 {{ $fd['atcRate']>=$fd['t_atc']?'text-success':'text-warning' }}">{{ $fd['atcRate'] }}%</div><div class="small text-muted">Target: {{ $fd['t_atc'] }}%</div>@if($fd['atcLoss']>0)<div class="small text-warning mt-1">-Rp {{ number_format($fd['atcLoss']/1e6,1) }}Jt</div>@endif</div></div>
            <div class="col-6"><div class="p-3 rounded border text-center {{ $fd['cvr']>=$fd['t_cvr']?'border-success bg-success bg-opacity-10':($fd['cvr']>=1?'border-warning bg-warning bg-opacity-10':'border-danger bg-danger bg-opacity-10') }}"><div class="small text-muted mb-1">CVR</div><div class="fw-bold num fs-5 {{ $fd['cvr']>=$fd['t_cvr']?'text-success':($fd['cvr']>=1?'text-warning':'text-danger') }}">{{ $fd['cvr'] }}%</div><div class="small text-muted">Target: {{ $fd['t_cvr'] }}%</div>@if($fd['cvrLoss']>0)<div class="small text-danger mt-1">-Rp {{ number_format($fd['cvrLoss']/1e6,1) }}Jt</div>@endif</div></div>
            <div class="col-6"><div class="p-3 rounded border text-center bg-light"><div class="small text-muted mb-1">AOV</div><div class="fw-bold num fs-5">Rp {{ number_format($fd['aov']/1000,0) }}K</div><div class="small text-muted">Per transaksi</div></div></div>
        </div></div>
    </div></div>
</div>
@endforeach
@endsection
