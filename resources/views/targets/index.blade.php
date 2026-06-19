@extends('layouts.app')
@section('title','Target Management — DSM Intelligence')
@section('page-title','Target Management')
@section('breadcrumb')<li class="breadcrumb-item active">Target Management</li>@endsection
@section('header-actions')
<form class="d-flex gap-2" method="GET"><select name="year" class="form-select form-select-sm" style="width:100px;" onchange="this.form.submit()">@foreach([2025,2026,2027] as $y)<option value="{{ $y }}" {{ $year==$y?'selected':'' }}>{{ $y }}</option>@endforeach</select></form>
@endsection
@section('content')
<div class="section-card mb-4"><div class="section-card-header"><h6><i class="bi bi-graph-up me-2"></i>Timeline Aktual vs Target (Apr 2026 – Mar 2027)</h6><span class="badge bg-success bg-opacity-10 text-success small">Target GMV Rp 42.5M</span></div><div class="p-3"><canvas id="timelineChart" height="160"></canvas></div></div>
<div class="section-card"><div class="section-card-header"><h6><i class="bi bi-table me-2"></i>Target GMV per Toko — {{ $year }}</h6><button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#targetModal"><i class="bi bi-pencil me-1"></i>Set Target</button></div>
<div class="table-responsive"><table class="table table-sm mb-0">
    <thead><tr><th>Toko</th>@foreach(['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Ags','Sep','Okt','Nov','Des'] as $mn)<th class="text-end" style="min-width:80px;font-size:0.68rem;">{{ $mn }}</th>@endforeach<th class="text-end">Total</th></tr></thead>
    <tbody>@foreach($data as $row)@php $annualTarget=array_sum(array_column($row['targets'],'target')); @endphp
    <tr><td><div class="fw-semibold" style="font-size:0.82rem;">{{ $row['store']->store_name }}</div><small class="brand-{{ $row['store']->brand }}">{{ $row['store']->brand }}</small></td>
    @foreach($months as $m)@php $t=$row['targets'][$m]; @endphp
    <td class="text-end p-1"><div class="num" style="font-size:0.73rem;">{{ $t['target']>0?'Rp '.number_format($t['target']/1e6,0).'Jt':'-' }}</div>@if($t['actual']>0)<div class="num {{ $t['pct']>=85?'text-success':($t['pct']>=60?'text-warning':'text-danger') }}" style="font-size:0.68rem;">{{ $t['pct'] }}%</div>@endif</td>
    @endforeach
    <td class="text-end fw-bold num" style="font-size:0.78rem;">{{ $annualTarget>0?'Rp '.number_format($annualTarget/1e9,2).'M':'-' }}</td></tr>
    @endforeach</tbody>
</table></div></div>
<div class="modal fade" id="targetModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">Set Target GMV</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <form action="{{ route('targets.update') }}" method="POST">@csrf
    <div class="modal-body">
        <div class="mb-3"><label class="form-label fw-semibold">Toko</label><select name="store_id" class="form-select" required><option value="">Pilih toko...</option>@foreach($stores as $s)<option value="{{ $s->id }}">{{ $s->store_name }}</option>@endforeach</select></div>
        <div class="row g-2"><div class="col-6"><label class="form-label fw-semibold">Bulan</label><select name="month" class="form-select">@foreach(['Jan'=>1,'Feb'=>2,'Mar'=>3,'Apr'=>4,'Mei'=>5,'Jun'=>6,'Jul'=>7,'Ags'=>8,'Sep'=>9,'Okt'=>10,'Nov'=>11,'Des'=>12] as $l=>$v)<option value="{{ $v }}">{{ $l }}</option>@endforeach</select></div><div class="col-6"><label class="form-label fw-semibold">Tahun</label><select name="year" class="form-select">@foreach([2025,2026,2027] as $y)<option value="{{ $y }}" {{ $y==$year?'selected':'' }}>{{ $y }}</option>@endforeach</select></div></div>
        <div class="mt-3"><label class="form-label fw-semibold">Target GMV (Rp)</label><input type="number" name="gmv_target" class="form-control" placeholder="contoh: 5000000000" required></div>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary">Simpan</button></div>
    </form></div></div></div>
@endsection
@section('scripts')
<script>
const tl=@json($timeline);
new Chart(document.getElementById('timelineChart'),{type:'bar',data:{labels:tl.map(t=>t.label),datasets:[{label:'Aktual GMV',data:tl.map(t=>t.actual),backgroundColor:'#2563EB',borderRadius:4},{label:'Target GMV',data:tl.map(t=>t.target),type:'line',borderColor:'#D97706',borderDash:[4,4],pointRadius:4,fill:false,borderWidth:2}]},options:{responsive:true,plugins:{legend:{position:'bottom'},tooltip:{callbacks:{label:ctx=>ctx.dataset.label+': Rp '+(ctx.raw/1e9).toFixed(2)+'M'}}},scales:{y:{ticks:{callback:v=>'Rp '+(v/1e9).toFixed(1)+'M'},grid:{color:'#F1F5F9'}},x:{grid:{display:false}}}}});
</script>
@endsection
