@extends('layouts.app')
@section('title','P&L')
@section('page-title','Profit & Loss')
@section('content')

<form method="GET" class="d-flex gap-2 align-items-center mb-4 flex-wrap">
  <select name="year" class="form-select form-select-sm" style="width:120px" onchange="this.form.submit()">
    @for($y=2024;$y<=2027;$y++)<option value="{{ $y }}" {{ $year==$y?'selected':'' }}>{{ $y }}</option>@endfor
  </select>
  <select name="brand" class="form-select form-select-sm" style="width:140px" onchange="this.form.submit()">
    <option value="all" {{ $brand==='all'?'selected':'' }}>Semua Brand</option>
    @foreach(['DTHREE','HURIM','ASFARA'] as $b)<option value="{{ $b }}" {{ $brand===$b?'selected':'' }}>{{ $b }}</option>@endforeach
  </select>
  <button class="btn btn-primary btn-sm px-3">Filter</button>
  <a href="{{ route('export.pnl', ['year'=>$year,'brand'=>$brand]) }}" class="btn btn-success btn-sm ms-auto">
    <i class="bi bi-download me-1"></i>Export CSV
  </a>
  <small class="text-muted"><i class="bi bi-info-circle me-1"></i>GMV & HPP otomatis. Klik ✏️ di Biaya Ops untuk input manual.</small>
</form>

@php
$months = ['01'=>'Jan','02'=>'Feb','03'=>'Mar','04'=>'Apr','05'=>'Mei','06'=>'Jun','07'=>'Jul','08'=>'Ags','09'=>'Sep','10'=>'Okt','11'=>'Nov','12'=>'Des'];
$rows = [
    ['key'=>'grossGmv',    'label'=>'Gross GMV',    'auto'=>true,  'sep'=>false],
    ['key'=>'netGmv',      'label'=>'Net GMV',      'auto'=>true,  'sep'=>true],
    ['key'=>'cogs',        'label'=>'HPP / COGS',   'auto'=>true,  'sep'=>false],
    ['key'=>'adsSpend',    'label'=>'Ads Spend',    'auto'=>true,  'sep'=>false],
    ['key'=>'opsCost',     'label'=>'Biaya Ops',    'auto'=>false, 'sep'=>true],
    ['key'=>'grossProfit', 'label'=>'Gross Profit', 'auto'=>true,  'sep'=>false],
    ['key'=>'netProfit',   'label'=>'Net Profit',   'auto'=>true,  'sep'=>false],
];
$profitKeys = ['grossProfit','netProfit'];
@endphp

<div class="card mb-4">
  <div class="card-header d-flex justify-content-between align-items-center">
    <span>Laporan P&L Bulanan {{ $year }}</span>
    <small class="text-muted">HPP = qty terjual × hpp/unit (dari menu HPP)</small>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-sm table-hover mb-0" style="font-size:.8rem;">
        <thead class="table-light">
          <tr>
            <th style="min-width:180px">Komponen</th>
            @foreach($months as $ml)<th class="text-end">{{ $ml }}</th>@endforeach
            <th class="text-end fw-bold">Total</th>
          </tr>
        </thead>
        <tbody>
        @foreach($rows as $row)
        @php
          $key   = $row['key'];
          $total = collect($byMonth)->sum($key);
          $isProfit = in_array($key, $profitKeys);
        @endphp
        <tr class="{{ $isProfit ? 'fw-semibold table-active' : '' }}">
          <td>
            {{ $row['label'] }}
            @if(!$row['auto'])
              <button type="button" class="btn btn-link btn-sm p-0 ms-1 text-primary"
                data-bs-toggle="modal" data-bs-target="#opsModal" title="Input Biaya Ops">
                <i class="bi bi-pencil-square" style="font-size:.75rem"></i>
              </button>
            @else
              <span class="badge bg-secondary-subtle text-secondary ms-1" style="font-size:.6rem">auto</span>
            @endif
          </td>
          @foreach($months as $m=>$ml)
          @php $v = $byMonth[$year.'-'.$m][$key] ?? 0; @endphp
          <td class="text-end {{ $isProfit && $v < 0 ? 'text-danger' : '' }}">
            @if($v != 0)
              {{ number_format($v/1000000,1) }}jt
            @else
              <span class="text-muted">—</span>
            @endif
          </td>
          @endforeach
          <td class="text-end fw-bold {{ $isProfit ? ($total >= 0 ? 'text-success' : 'text-danger') : '' }}">
            {{ number_format($total/1000000,1) }}jt
          </td>
        </tr>
        @if($row['sep'])
          <tr><td colspan="15" class="p-0"><div style="border-top:2px solid #dee2e6"></div></td></tr>
        @endif
        @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-header">Tren Bulanan {{ $year }}</div>
  <div class="card-body"><canvas id="pnlChart" height="80"></canvas></div>
</div>

{{-- Modal Input Biaya Ops --}}
<div class="modal fade" id="opsModal">
  <div class="modal-dialog"><div class="modal-content">
    <form method="POST" action="{{ route('pnl.saveOps') }}">@csrf
    <input type="hidden" name="year" value="{{ $year }}">
    <div class="modal-header">
      <h6 class="modal-title">Input Biaya Operasional — {{ $year }}</h6>
      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
    </div>
    <div class="modal-body">
      <div class="mb-3">
        <label class="form-label small fw-semibold">Toko / Cost Center</label>
        <select name="store_id" class="form-select form-select-sm" required>
          <option value="">Pilih Toko...</option>
          @foreach($stores as $s)
            <option value="{{ $s->id }}">{{ $s->name }} ({{ $s->brand }})</option>
          @endforeach
        </select>
        <div class="form-text">Bisa dialokasikan per toko atau satu toko sebagai cost center brand.</div>
      </div>
      <div class="mb-3">
        <label class="form-label small fw-semibold">Bulan</label>
        <select name="month" class="form-select form-select-sm" required>
          @foreach($months as $m=>$ml)<option value="{{ $m }}">{{ $ml }}</option>@endforeach
        </select>
      </div>
      <div class="mb-3">
        <label class="form-label small fw-semibold">Biaya Operasional (Rp)</label>
        <input type="number" name="operational_cost" class="form-control form-control-sm"
          min="0" step="100000" placeholder="contoh: 5000000" required>
        <div class="form-text">Gaji, sewa gudang, utilitas, perlengkapan, dll.</div>
      </div>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
      <button type="submit" class="btn btn-sm btn-primary">Simpan</button>
    </div>
    </form>
  </div></div>
</div>

@push('scripts')
<script>
const pnlRaw   = @json(array_values($byMonth));
const pnlLabel = @json(array_values($months));
new Chart(document.getElementById('pnlChart'), {
  type: 'bar',
  data: {
    labels: pnlLabel,
    datasets: [
      {label:'Gross GMV', data:pnlRaw.map(d=>d.grossGmv), backgroundColor:'rgba(124,111,247,.5)', borderRadius:3, order:2},
      {label:'Total Biaya', data:pnlRaw.map(d=>d.cogs+d.adsSpend+d.opsCost), backgroundColor:'rgba(239,68,68,.4)', borderRadius:3, order:2},
      {label:'Net Profit', data:pnlRaw.map(d=>d.netProfit), type:'line', borderColor:'#10b981', backgroundColor:'rgba(16,185,129,.1)', tension:.3, fill:true, order:1},
    ]
  },
  options:{responsive:true,scales:{y:{ticks:{callback:v=>'Rp '+new Intl.NumberFormat('id').format(v)}}},plugins:{legend:{position:'top'}}}
});
</script>
@endpush
@endsection
