@extends('layouts.app')
@section('title','Overview — DSM Intelligence')
@section('page-title','Dashboard Overview')
@section('breadcrumb')<li class="breadcrumb-item active">Overview</li>@endsection
@section('header-actions')
<form class="d-flex gap-2 align-items-center" method="GET">
    <select name="brand" class="form-select form-select-sm" style="width:130px;" onchange="this.form.submit()">
        <option value="all" {{ $brand==='all'?'selected':'' }}>Semua Brand</option>
        @foreach(['DTHREE','HURIM','ASFARA'] as $b)
        <option value="{{ $b }}" {{ $brand===$b?'selected':'' }}>{{ $b }}</option>
        @endforeach
    </select>
    <input type="month" name="month" class="form-control form-control-sm" value="{{ $month }}" style="width:150px;" onchange="this.form.submit()">
</form>
@endsection
@section('content')
<div class="row g-3 mb-4">
    <div class="col-6 col-xl-2"><div class="metric-card"><div class="metric-label mb-1"><i class="bi bi-bag me-1"></i>Total GMV</div><div class="metric-value num">{{ 'Rp '.number_format($totalGmv/1e6,1).'M' }}</div><div class="metric-delta {{ $gmvDelta >= 0 ? 'positive' : 'negative' }} mt-1"><i class="bi bi-arrow-{{ $gmvDelta >= 0 ? 'up' : 'down' }}"></i>{{ abs($gmvDelta) }}% vs bln lalu</div></div></div>
    <div class="col-6 col-xl-2"><div class="metric-card"><div class="metric-label mb-1"><i class="bi bi-megaphone me-1"></i>Total Spend</div><div class="metric-value num">{{ 'Rp '.number_format($totalSpend/1e6,1).'M' }}</div><div class="text-muted mt-1" style="font-size:0.75rem;">Iklan semua platform</div></div></div>
    <div class="col-6 col-xl-2"><div class="metric-card"><div class="metric-label mb-1"><i class="bi bi-graph-up me-1"></i>Blended ROAS</div><div class="metric-value num">{{ $blendedRoas }}x</div><div class="text-muted mt-1" style="font-size:0.75rem;">GMV iklan / Spend</div></div></div>
    <div class="col-6 col-xl-2"><div class="metric-card"><div class="metric-label mb-1"><i class="bi bi-x-circle me-1"></i>Cancel Rate</div><div class="metric-value num {{ $cancelRate > 5 ? 'text-danger' : ($cancelRate > 3 ? 'text-warning' : 'text-success') }}">{{ $cancelRate }}%</div><div class="text-muted mt-1" style="font-size:0.75rem;">Target: < 5%</div></div></div>
    <div class="col-6 col-xl-2"><div class="metric-card"><div class="metric-label mb-1"><i class="bi bi-funnel me-1"></i>CVR Rata-rata</div><div class="metric-value num {{ $avgCvr >= 2 ? 'text-success' : ($avgCvr >= 1 ? 'text-warning' : 'text-danger') }}">{{ $avgCvr }}%</div><div class="text-muted mt-1" style="font-size:0.75rem;">Target: ≥ 2%</div></div></div>
    <div class="col-6 col-xl-2"><div class="metric-card"><div class="metric-label mb-1"><i class="bi bi-calendar-check me-1"></i>Bulan</div><div class="metric-value" style="font-size:1.1rem;">{{ \Carbon\Carbon::parse($month.'-01')->format('M Y') }}</div><div class="text-muted mt-1" style="font-size:0.75rem;">Periode aktif</div></div></div>
</div>
<div class="row g-3 mb-4">
    <div class="col-lg-8"><div class="section-card"><div class="section-card-header"><h6><i class="bi bi-bar-chart me-2"></i>GMV Trend Harian</h6><span class="text-muted small">{{ \Carbon\Carbon::parse($month.'-01')->format('F Y') }}</span></div><div class="p-3"><canvas id="gmvTrendChart" height="200"></canvas></div></div></div>
    <div class="col-lg-4"><div class="section-card h-100"><div class="section-card-header"><h6><i class="bi bi-trophy me-2"></i>Brand Progress</h6></div><div class="p-3">
        @foreach($brandProgress as $b => $bp)
        <div class="mb-3">
            <div class="d-flex justify-content-between mb-1"><span class="fw-semibold brand-{{ $b }}" style="font-size:0.85rem;">{{ $b }}</span><span class="small text-muted num">{{ number_format($bp['pct'],1) }}%</span></div>
            <div class="progress mb-1" style="height:10px;border-radius:6px;"><div class="progress-bar {{ $bp['pct']>=85?'bg-success':($bp['pct']>=60?'bg-warning':'bg-danger') }}" style="width:{{ min(100,$bp['pct']) }}%;border-radius:6px;"></div></div>
            <div class="d-flex justify-content-between"><small class="text-muted num">Rp {{ number_format($bp['actual']/1e9,2) }}M</small><small class="text-muted num">Target: Rp {{ number_format($bp['target']/1e9,2) }}M</small></div>
        </div>
        @endforeach
    </div></div></div>
</div>
<div class="row g-3 mb-4">
    <div class="col-lg-8"><div class="section-card"><div class="section-card-header"><h6><i class="bi bi-pie-chart me-2"></i>Marketplace vs Non-Marketplace GMV</h6><span class="badge bg-primary bg-opacity-10 text-primary small">6 Bulan Terakhir</span></div><div class="p-3"><canvas id="mpNonMpChart" height="180"></canvas></div></div></div>
    <div class="col-lg-4"><div class="section-card"><div class="section-card-header"><h6><i class="bi bi-list-ol me-2"></i>Store Leaderboard</h6></div><div class="p-0">
        @foreach($leaderboard->take(7) as $i => $item)
        <div class="d-flex align-items-center gap-2 px-3 py-2 {{ $i > 0 ? 'border-top' : '' }}">
            <div class="fw-bold text-muted" style="width:22px;font-size:0.85rem;">{{ $i+1 }}</div>
            <div class="flex-grow-1"><div class="fw-semibold" style="font-size:0.82rem;">{{ $item['store']->store_name }}</div><div class="small text-muted">{{ $item['store']->brand }} · {{ $item['store']->platform }}</div></div>
            <div class="text-end"><div class="num fw-bold" style="font-size:0.82rem;">{{ 'Rp '.number_format($item['gmv']/1e6,1).'Jt' }}</div></div>
        </div>
        @endforeach
    </div></div></div>
</div>
@endsection
@section('scripts')
<script>
const gmvData=@json($gmvTrend);
const mpData=@json($mpVsNonMp);
new Chart(document.getElementById('gmvTrendChart'),{type:'line',data:{labels:gmvData.map(d=>d.day),datasets:[{label:'GMV (Rp)',data:gmvData.map(d=>d.total),borderColor:'#2563EB',backgroundColor:'rgba(37,99,235,0.08)',borderWidth:2,fill:true,tension:0.4,pointRadius:3}]},options:{responsive:true,plugins:{legend:{display:false},tooltip:{callbacks:{label:ctx=>'Rp '+ctx.raw.toLocaleString('id-ID')}}},scales:{y:{ticks:{callback:v=>'Rp '+(v/1e6).toFixed(0)+'Jt'},grid:{color:'#F1F5F9'}},x:{grid:{display:false}}}}});
new Chart(document.getElementById('mpNonMpChart'),{type:'bar',data:{labels:mpData.map(d=>d.month),datasets:[{label:'Marketplace GMV',data:mpData.map(d=>d.mp),backgroundColor:'#2563EB',borderRadius:4},{label:'Non-Marketplace GMV',data:mpData.map(d=>d.non_mp),backgroundColor:'#16A34A',borderRadius:4}]},options:{responsive:true,plugins:{legend:{position:'bottom'},tooltip:{callbacks:{label:ctx=>ctx.dataset.label+': Rp '+(ctx.raw/1e6).toFixed(1)+'Jt'}}},scales:{y:{ticks:{callback:v=>'Rp '+(v/1e9).toFixed(1)+'M'},grid:{color:'#F1F5F9'}},x:{grid:{display:false}}}}});
</script>
@endsection
