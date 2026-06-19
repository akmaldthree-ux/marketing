@extends('layouts.app')
@section('title', 'PIC Performance — DSM Intelligence')
@section('page-title', 'PIC Performance')
@section('breadcrumb')
<li class="breadcrumb-item active">PIC Performance</li>
@endsection

@section('header-actions')
<form method="GET" class="d-flex">
    <input type="month" name="month" class="form-control form-control-sm" value="{{ $month }}" onchange="this.form.submit()" style="width:150px;">
</form>
@endsection

@section('content')
@if($hasBreach)
<div class="alert alert-danger alert-breach mb-4">
    <div class="d-flex align-items-center gap-2">
        <i class="bi bi-exclamation-triangle-fill fs-5"></i>
        <div>
            <strong>PERINGATAN RETURN RATE!</strong>
            @foreach($picScores as $ps)
                @if($ps['return_status'] === 'breach')
                <span class="ms-2">⚠ {{ $ps['pic']->name }} ({{ number_format($ps['return_rate'],2) }}%)</span>
                @endif
            @endforeach
            — melebihi batas maksimal 2%
        </div>
    </div>
</div>
@endif

<div class="row g-3 mb-4">
    @foreach($picScores as $ps)
    <div class="col-12 col-lg-6">
        <div class="section-card {{ $ps['return_status'] === 'breach' ? 'alert-breach' : ($ps['return_status'] === 'warning' ? 'alert-warning' : '') }}">
            <div class="section-card-header">
                <div class="d-flex align-items-center gap-3">
                    <div style="width:40px;height:40px;background:rgba(37,99,235,0.1);border-radius:50%;display:flex;align-items:center;justify-content:center;">
                        <i class="bi bi-person-fill text-primary"></i>
                    </div>
                    <div>
                        <div class="fw-bold">{{ $ps['pic']->name }}</div>
                        <div class="small text-muted">{{ count($ps['pic']->assigned_stores ?? []) }} toko assigned · {{ $ps['pic']->email }}</div>
                    </div>
                </div>
                <div class="d-flex gap-2 align-items-center">
                    @if($ps['return_status'] === 'breach')
                        <span class="badge-breach">BREACH >2%</span>
                    @elseif($ps['return_status'] === 'warning')
                        <span class="badge-warning-dsm">WARNING</span>
                    @else
                        <span class="badge-normal">NORMAL</span>
                    @endif
                    @if($ps['achievement_status'] === 'on_track')
                        <span class="badge-on-track">On Track</span>
                    @elseif($ps['achievement_status'] === 'at_risk')
                        <span class="badge-at-risk">At Risk</span>
                    @else
                        <span class="badge-behind">Behind</span>
                    @endif
                </div>
            </div>
            <div class="p-3">
                <div class="row g-2 mb-3">
                    <div class="col-4 text-center">
                        <div class="text-muted small">GMV</div>
                        <div class="fw-bold num" style="font-size:1rem;">Rp {{ number_format($ps['gmv']/1e6,1) }}Jt</div>
                    </div>
                    <div class="col-4 text-center border-start border-end">
                        <div class="text-muted small">Ad Spend</div>
                        <div class="fw-bold num" style="font-size:1rem;">Rp {{ number_format($ps['spend']/1e6,1) }}Jt</div>
                    </div>
                    <div class="col-4 text-center">
                        <div class="text-muted small">ROAS</div>
                        <div class="fw-bold num {{ $ps['roas'] >= 3 ? 'text-success' : ($ps['roas'] >= 2 ? 'text-warning' : 'text-danger') }}" style="font-size:1rem;">{{ $ps['roas'] }}x</div>
                    </div>
                </div>

                <!-- Target progress -->
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <small class="text-muted">Target Achievement</small>
                        <small class="num fw-bold">{{ $ps['achievement'] }}%</small>
                    </div>
                    <div class="progress" style="height:8px;border-radius:4px;">
                        <div class="progress-bar {{ $ps['achievement'] >= 85 ? 'bg-success' : ($ps['achievement'] >= 60 ? 'bg-warning' : 'bg-danger') }}"
                             style="width:{{ $ps['achievement'] }}%;border-radius:4px;"></div>
                    </div>
                    <div class="d-flex justify-content-between mt-1">
                        <small class="text-muted num">Aktual: Rp {{ number_format($ps['gmv']/1e6,1) }}Jt</small>
                        <small class="text-muted num">Target: Rp {{ number_format($ps['target']/1e6,1) }}Jt</small>
                    </div>
                </div>

                <!-- Return Rate -->
                <div class="d-flex align-items-center justify-content-between p-2 rounded {{ $ps['return_status'] === 'breach' ? 'bg-danger bg-opacity-10' : ($ps['return_status'] === 'warning' ? 'bg-warning bg-opacity-10' : 'bg-success bg-opacity-10') }}">
                    <span class="small fw-semibold">Return & Refund Rate</span>
                    <span class="fw-bold num {{ $ps['return_status'] === 'breach' ? 'text-danger' : ($ps['return_status'] === 'warning' ? 'text-warning' : 'text-success') }}">
                        {{ number_format($ps['return_rate'], 2) }}%
                        @if($ps['return_status'] === 'breach') <i class="bi bi-exclamation-triangle-fill"></i>
                        @endif
                    </span>
                </div>

                <!-- Stores handled -->
                <div class="mt-3">
                    <div class="small text-muted mb-1 fw-semibold">Toko yang Dihandle:</div>
                    <div class="d-flex flex-wrap gap-1">
                        @foreach($ps['stores'] as $s)
                        <span class="badge bg-light text-dark border" style="font-size:0.72rem;">{{ $s->store_name }}</span>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

<!-- GMV Contribution -->
<div class="section-card">
    <div class="section-card-header">
        <h6><i class="bi bi-pie-chart me-2"></i>Kontribusi GMV per PIC</h6>
    </div>
    <div class="row p-3 align-items-center">
        <div class="col-md-5">
            <canvas id="picGmvChart" height="250"></canvas>
        </div>
        <div class="col-md-7">
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead><tr><th>PIC</th><th>GMV</th><th>% Kontribusi</th><th>Return Rate</th></tr></thead>
                    <tbody>
                        @foreach($picScores as $ps)
                        <tr>
                            <td class="fw-semibold">{{ $ps['pic']->name }}</td>
                            <td class="num">Rp {{ number_format($ps['gmv']/1e6,1) }}Jt</td>
                            <td class="num">{{ $totalGmv > 0 ? number_format(($ps['gmv']/$totalGmv)*100,1) : 0 }}%</td>
                            <td>
                                @if($ps['return_status'] === 'breach')
                                    <span class="badge-breach">{{ number_format($ps['return_rate'],2) }}% BREACH</span>
                                @elseif($ps['return_status'] === 'warning')
                                    <span class="badge-warning-dsm">{{ number_format($ps['return_rate'],2) }}%</span>
                                @else
                                    <span class="badge-normal">{{ number_format($ps['return_rate'],2) }}%</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
const picData = @json($picScores->map(fn($p) => ['name' => $p['pic']->name, 'gmv' => $p['gmv']]));
new Chart(document.getElementById('picGmvChart'), {
    type: 'doughnut',
    data: {
        labels: picData.map(p => p.name),
        datasets: [{ data: picData.map(p => p.gmv), backgroundColor: ['#2563EB','#7C3AED','#059669','#D97706'], borderWidth: 0, hoverOffset: 8 }]
    },
    options: {
        cutout: '65%',
        plugins: { legend: { position: 'bottom' }, tooltip: {
            callbacks: { label: ctx => ctx.label + ': Rp ' + (ctx.raw/1e6).toFixed(1) + 'Jt' }
        }}
    }
});
</script>
@endsection
