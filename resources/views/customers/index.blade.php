@extends('layouts.app')
@section('title','Customer')
@section('page-title','Analisis Customer')
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
      <div class="label">Customer Baru</div>
      <div class="value text-primary">{{ number_format($newCustomers) }}</div>
      <div class="delta text-muted">Bulan ini</div>
    </div>
  </div>
  <div class="col-sm-4">
    <div class="stat-card">
      <div class="label">Customer Lama</div>
      <div class="value text-success">{{ number_format($returningCustomers) }}</div>
      <div class="delta text-muted">Repeat buyer</div>
    </div>
  </div>
  <div class="col-sm-4">
    <div class="stat-card">
      <div class="label">Repeat Rate</div>
      <div class="value">{{ $repeatRate }}%</div>
      <div class="delta text-muted">dari total orders</div>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-header">Top Customer (by total orders)</div>
  <div class="card-body p-0">
    <table class="table table-hover mb-0" style="font-size:.85rem;">
      <thead class="table-light"><tr><th>#</th><th>Username</th><th>Platform</th><th class="text-end">Total Orders</th><th>Pertama Order</th><th>Terakhir Order</th></tr></thead>
      <tbody>
      @forelse($topCustomers as $i=>$c)
      <tr>
        <td class="text-muted">{{ $i+1 }}</td>
        <td class="fw-semibold">{{ $c->username }}</td>
        <td><span class="badge bg-light text-dark border">{{ $c->platform }}</span></td>
        <td class="text-end fw-bold">{{ $c->total_orders }}</td>
        <td>{{ $c->first_order_date?->format('d/m/Y') ?? '-' }}</td>
        <td>{{ $c->last_order_date?->format('d/m/Y') ?? '-' }}</td>
      </tr>
      @empty
      <tr><td colspan="6" class="text-center text-muted py-4">Tidak ada data customer</td></tr>
      @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
