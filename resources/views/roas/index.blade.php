@extends('layouts.app')
@section('title','ROAS & Iklan')
@section('page-title','ROAS & Performa Iklan')
@section('content')
<form method="GET" class="d-flex gap-2 align-items-center mb-4 flex-wrap">
  <input type="month" name="month" value="{{ $month }}" class="form-control form-control-sm" style="width:160px">
  <select name="platform" class="form-select form-select-sm" style="width:160px">
    <option value="all" {{ $platform==='all'?'selected':'' }}>Semua Platform</option>
    @foreach(['Shopee','TikTok Shop','Meta Ads'] as $p)<option value="{{ $p }}" {{ $platform===$p?'selected':'' }}>{{ $p }}</option>@endforeach
  </select>
  <button class="btn btn-primary btn-sm px-3">Filter</button>
</form>

<div class="row g-3 mb-4">
  <div class="col-sm-4">
    <div class="stat-card">
      <div class="label">Total Spend</div>
      <div class="value">Rp {{ number_format($totalSpend/1000000,2) }}jt</div>
    </div>
  </div>
  <div class="col-sm-4">
    <div class="stat-card">
      <div class="label">GMV dari Iklan</div>
      <div class="value">Rp {{ number_format($totalGmv/1000000,2) }}jt</div>
    </div>
  </div>
  <div class="col-sm-4">
    <div class="stat-card">
      <div class="label">Blended ROAS</div>
      <div class="value">{{ $blendedRoas }}x</div>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-header">Performa Iklan per Toko</div>
  <div class="card-body p-0">
    <table class="table table-hover mb-0">
      <thead class="table-light"><tr><th>Toko</th><th>Brand</th><th class="text-end">Spend</th><th class="text-end">GMV Ads</th><th class="text-end">ROAS</th><th class="text-end">Impressi</th><th class="text-end">Klik</th><th class="text-end">Konversi</th></tr></thead>
      <tbody>
      @forelse($byStore as $row)
      <tr>
        <td class="fw-semibold">{{ $row['store']?->name ?? '-' }}</td>
        <td><span class="badge badge-brand-{{ $row['store']?->brand }}">{{ $row['store']?->brand }}</span></td>
        <td class="text-end">Rp {{ number_format($row['spend']/1000000,2) }}jt</td>
        <td class="text-end">Rp {{ number_format($row['gmv']/1000000,2) }}jt</td>
        <td class="text-end fw-bold {{ $row['roas']>=3?'text-success':($row['roas']>=1.5?'text-warning':'text-danger') }}">{{ $row['roas'] }}x</td>
        <td class="text-end">{{ number_format($row['impressions']) }}</td>
        <td class="text-end">{{ number_format($row['clicks']) }}</td>
        <td class="text-end">{{ number_format($row['conversions']) }}</td>
      </tr>
      @empty
      <tr><td colspan="8" class="text-center text-muted py-4">Tidak ada data iklan</td></tr>
      @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
