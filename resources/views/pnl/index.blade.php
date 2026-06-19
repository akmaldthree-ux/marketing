@extends('layouts.app')
@section('title', 'P&L Report — DSM Intelligence')
@section('page-title', 'Profit & Loss Report')
@section('breadcrumb')<li class="breadcrumb-item active">P&L Report</li>@endsection

@section('header-actions')
<form class="d-flex gap-2" method="GET">
    <select name="view" class="form-select form-select-sm" onchange="this.form.submit()" style="width:130px;">
        <option value="store" {{ $view==='store'?'selected':'' }}>Per Toko</option>
        <option value="brand" {{ $view==='brand'?'selected':'' }}>Per Brand</option>
        <option value="pic" {{ $view==='pic'?'selected':'' }}>Per PIC</option>
    </select>
    <select name="brand" class="form-select form-select-sm" style="width:120px;" onchange="this.form.submit()">
        <option value="all" {{ $brand==='all'?'selected':'' }}>Semua Brand</option>
        @foreach(['DTHREE','HURIM','ASFARA'] as $b)
        <option value="{{ $b }}" {{ $brand===$b?'selected':'' }}>{{ $b }}</option>
        @endforeach
    </select>
    <input type="month" name="period" class="form-control form-control-sm" value="{{ $period }}" onchange="this.form.submit()" style="width:150px;">
</form>
@endsection

@section('content')
<!-- Summary Row -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="metric-card">
            <div class="metric-label mb-1">Total Gross GMV</div>
            <div class="metric-value num">Rp {{ number_format($totalGrossGmv/1e9,2) }}M</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="metric-card">
            <div class="metric-label mb-1">Total Net Profit</div>
            <div class="metric-value num {{ $totalNetProfit >= 0 ? 'text-success' : 'text-danger' }}">
                Rp {{ number_format(abs($totalNetProfit)/1e6,1) }}Jt {{ $totalNetProfit < 0 ? '(LOSS)' : '' }}
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="metric-card">
            <div class="metric-label mb-1">Profit Margin</div>
            <div class="metric-value num {{ $totalGrossGmv > 0 && ($totalNetProfit/$totalGrossGmv)*100 > 10 ? 'text-success' : 'text-warning' }}">
                {{ $totalGrossGmv > 0 ? number_format(($totalNetProfit/$totalGrossGmv)*100,1) : 0 }}%
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="metric-card">
            <div class="metric-label mb-1">Periode</div>
            <div class="metric-value" style="font-size:1.1rem;">{{ \Carbon\Carbon::parse($period.'-01')->format('F Y') }}</div>
            <div class="text-muted small">View: {{ ucfirst($view) }}</div>
        </div>
    </div>
</div>

<!-- P&L Table -->
<div class="section-card">
    <div class="section-card-header">
        <h6><i class="bi bi-table me-2"></i>Laporan P&L — {{ \Carbon\Carbon::parse($period.'-01')->format('F Y') }}</h6>
        <span class="badge bg-primary bg-opacity-10 text-primary small">{{ ucfirst($view) }}</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>{{ $view === 'store' ? 'Toko' : ($view === 'brand' ? 'Brand' : 'PIC') }}</th>
                    <th class="text-end">Gross GMV</th>
                    <th class="text-end">Admin Fee</th>
                    <th class="text-end">Promo Xtra</th>
                    <th class="text-end">Ongkir Fee</th>
                    <th class="text-end">Net GMV</th>
                    <th class="text-end">HPP</th>
                    <th class="text-end">Gross Profit</th>
                    <th class="text-end">Ads Spend</th>
                    <th class="text-end">Ops Cost</th>
                    <th class="text-end">Net Profit</th>
                    <th class="text-end">Margin</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pnlData as $row)
                @php $d = $row['data']; $margin = $d->gross_gmv > 0 ? ($d->net_profit/$d->gross_gmv)*100 : 0; @endphp
                <tr>
                    <td>
                        <div class="fw-semibold">{{ $row['label'] }}</div>
                        @if($row['brand']) <small class="text-muted brand-{{ $row['brand'] }}">{{ $row['brand'] }}</small> @endif
                        @if($row['pic'] && $view === 'store') <small class="text-muted"> · {{ $row['pic'] }}</small> @endif
                    </td>
                    <td class="text-end num">{{ number_format($d->gross_gmv/1e6,1) }}Jt</td>
                    <td class="text-end num text-danger">({{ number_format($d->admin_fee/1e6,1) }}Jt)</td>
                    <td class="text-end num text-danger">({{ number_format($d->promo_xtra/1e6,1) }}Jt)</td>
                    <td class="text-end num text-danger">({{ number_format($d->ongkir_fee/1e6,1) }}Jt)</td>
                    <td class="text-end num fw-semibold">{{ number_format($d->net_gmv/1e6,1) }}Jt</td>
                    <td class="text-end num text-danger">({{ number_format($d->hpp_total/1e6,1) }}Jt)</td>
                    <td class="text-end num {{ $d->gross_profit >= 0 ? 'text-success fw-bold' : 'text-danger fw-bold' }}">{{ number_format($d->gross_profit/1e6,1) }}Jt</td>
                    <td class="text-end num text-danger">({{ number_format($d->ads_spend/1e6,1) }}Jt)</td>
                    <td class="text-end num text-danger">({{ number_format($d->operational_cost/1e6,1) }}Jt)</td>
                    <td class="text-end num fw-bold {{ $d->net_profit >= 0 ? 'text-success' : 'text-danger' }}">
                        {{ $d->net_profit >= 0 ? '' : '-' }}Rp{{ number_format(abs($d->net_profit)/1e6,1) }}Jt
                    </td>
                    <td class="text-end">
                        <span class="badge {{ $margin >= 15 ? 'bg-success' : ($margin >= 5 ? 'bg-warning text-dark' : 'bg-danger') }}">
                            {{ number_format($margin,1) }}%
                        </span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="12" class="text-center text-muted py-4">Tidak ada data untuk periode ini. Upload laporan keuangan terlebih dahulu.</td></tr>
                @endforelse
            </tbody>
            @if(count($pnlData) > 1)
            <tfoot class="table-secondary fw-bold">
                <tr>
                    <td>TOTAL</td>
                    <td class="text-end num">{{ number_format(collect($pnlData)->sum(fn($r)=>$r['data']->gross_gmv)/1e9,2) }}M</td>
                    <td class="text-end num text-danger">({{ number_format(collect($pnlData)->sum(fn($r)=>$r['data']->admin_fee)/1e6,1) }}Jt)</td>
                    <td class="text-end num text-danger">({{ number_format(collect($pnlData)->sum(fn($r)=>$r['data']->promo_xtra)/1e6,1) }}Jt)</td>
                    <td class="text-end num text-danger">({{ number_format(collect($pnlData)->sum(fn($r)=>$r['data']->ongkir_fee)/1e6,1) }}Jt)</td>
                    <td class="text-end num">{{ number_format(collect($pnlData)->sum(fn($r)=>$r['data']->net_gmv)/1e9,2) }}M</td>
                    <td class="text-end num text-danger">({{ number_format(collect($pnlData)->sum(fn($r)=>$r['data']->hpp_total)/1e6,1) }}Jt)</td>
                    <td class="text-end num text-success">{{ number_format(collect($pnlData)->sum(fn($r)=>$r['data']->gross_profit)/1e6,1) }}Jt</td>
                    <td class="text-end num text-danger">({{ number_format(collect($pnlData)->sum(fn($r)=>$r['data']->ads_spend)/1e6,1) }}Jt)</td>
                    <td class="text-end num text-danger">({{ number_format(collect($pnlData)->sum(fn($r)=>$r['data']->operational_cost)/1e6,1) }}Jt)</td>
                    <td class="text-end num {{ $totalNetProfit >= 0 ? 'text-success' : 'text-danger' }}">Rp {{ number_format(abs($totalNetProfit)/1e6,1) }}Jt</td>
                    <td class="text-end"><span class="badge {{ $totalGrossGmv > 0 && ($totalNetProfit/$totalGrossGmv)*100 >= 10 ? 'bg-success' : 'bg-warning text-dark' }}">{{ $totalGrossGmv > 0 ? number_format(($totalNetProfit/$totalGrossGmv)*100,1) : 0 }}%</span></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>

<div class="alert alert-info mt-3 py-2 small">
    <i class="bi bi-info-circle me-1"></i>P&L ini adalah <strong>Marketing P&L</strong>, bukan laporan keuangan resmi perusahaan. HPP diinput manual oleh Admin. Biaya operasional merupakan estimasi bulanan.
</div>
@endsection
