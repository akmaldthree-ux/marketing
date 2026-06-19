@extends('layouts.app')
@section('title', 'Demand Forecast — DSM Intelligence')
@section('page-title', 'Demand Forecast')
@section('breadcrumb')<li class="breadcrumb-item active">Demand Forecast</li>@endsection

@section('header-actions')
<form class="d-flex gap-2" method="GET">
    <select name="brand" class="form-select form-select-sm" style="width:120px;" onchange="this.form.submit()">
        <option value="all" {{ $brand==='all'?'selected':'' }}>Semua Brand</option>
        @foreach(['DTHREE','HURIM','ASFARA'] as $b)
        <option value="{{ $b }}" {{ $brand===$b?'selected':'' }}>{{ $b }}</option>
        @endforeach
    </select>
    <select name="store_id" class="form-select form-select-sm" style="width:160px;" onchange="this.form.submit()">
        <option value="all">Semua Toko</option>
        @foreach($allStores as $s)
        <option value="{{ $s->id }}" {{ $storeId==$s->id?'selected':'' }}>{{ $s->store_name }}</option>
        @endforeach
    </select>
</form>
@endsection

@section('content')
<div class="alert alert-info py-2 mb-4 small">
    <i class="bi bi-info-circle me-1"></i><strong>Disclaimer:</strong> Forecast bersifat indikatif berbasis Moving Average 4 Minggu (MA-4W). Akurasi bergantung pada kelengkapan data order yang diupload PIC. Tidak memperhitungkan faktor eksternal (flash sale, hari raya) secara otomatis.
</div>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h6 class="fw-bold mb-0">Rekomendasi Stok Minggu Depan</h6>
    <div class="d-flex gap-2">
        <form action="{{ route('demand-forecast.generate') }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-arrow-clockwise me-1"></i>Generate Forecast</button>
        </form>
        <a href="#" class="btn btn-sm btn-outline-success"><i class="bi bi-download me-1"></i>Download XLSX</a>
    </div>
</div>

<div class="section-card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>SKU</th>
                    <th>Produk</th>
                    <th>Toko</th>
                    <th class="text-end">Avg Jual/Hari (4W)</th>
                    <th class="text-end">Forecast Minggu (unit)</th>
                    <th class="text-end">Safety Stock (+20%)</th>
                    <th class="text-end">Total Rekomendasi</th>
                    <th class="text-center">Tren</th>
                    <th class="text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($forecasts as $f)
                @php
                    $isOverstock = $f->trend_direction === 'down' && abs($f->trend_change_pct) > 20;
                    $isStockout = false; // would check stock_levels if integrated
                @endphp
                <tr class="{{ $isOverstock ? 'table-warning' : '' }} {{ $isStockout ? 'table-danger' : '' }}">
                    <td><code class="text-primary">{{ $f->product_sku }}</code></td>
                    <td class="fw-semibold">{{ $f->product_name }}</td>
                    <td>
                        <div style="font-size:0.82rem;">{{ $f->store->store_name }}</div>
                        <small class="brand-{{ $f->store->brand }}">{{ $f->store->brand }}</small>
                    </td>
                    <td class="text-end num">{{ number_format($f->avg_daily_sales_4w,1) }}/hari</td>
                    <td class="text-end num fw-bold">{{ $f->forecast_qty }}</td>
                    <td class="text-end num text-muted">+{{ $f->safety_stock_qty }}</td>
                    <td class="text-end num fw-bold text-primary">{{ $f->recommended_order_qty }}</td>
                    <td class="text-center">
                        @if($f->trend_direction === 'up')
                            <span class="text-success fw-bold"><i class="bi bi-arrow-up-circle-fill"></i> +{{ number_format($f->trend_change_pct,1) }}%</span>
                        @elseif($f->trend_direction === 'down')
                            <span class="{{ abs($f->trend_change_pct) > 20 ? 'text-danger' : 'text-warning' }} fw-bold"><i class="bi bi-arrow-down-circle-fill"></i> {{ number_format($f->trend_change_pct,1) }}%</span>
                        @else
                            <span class="text-muted"><i class="bi bi-dash-circle"></i> Stabil</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($f->is_limited_data)
                            <span class="badge bg-warning text-dark">Data Terbatas</span>
                        @elseif($isOverstock)
                            <span class="badge bg-warning text-dark"><i class="bi bi-box"></i> Potensi Overstock</span>
                        @else
                            <span class="badge bg-success">Normal</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" class="text-center text-muted py-5">
                    <i class="bi bi-boxes display-4 d-block mb-2 text-muted opacity-50"></i>
                    Belum ada data forecast. Klik "Generate Forecast" untuk membuat prediksi berdasarkan data order.
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Legend -->
<div class="row g-2 mt-3">
    <div class="col-auto"><span class="badge bg-warning text-dark me-1"></span><small class="text-muted">Tren turun > 20% — potensi overstock</small></div>
    <div class="col-auto"><span class="badge bg-danger me-1"></span><small class="text-muted">Forecast > stok tersedia — risiko stockout</small></div>
    <div class="col-auto"><span class="badge bg-warning text-dark me-1">Data Terbatas</span><small class="text-muted">< 2 minggu data historis</small></div>
</div>
@endsection
