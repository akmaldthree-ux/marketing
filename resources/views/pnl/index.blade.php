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
  <small class="text-muted ms-2"><i class="bi bi-info-circle me-1"></i>GMV & HPP dihitung otomatis dari data upload. Klik <strong>Biaya Ops</strong> untuk input manual.</small>
</form>

@php
$months     = ['01'=>'Jan','02'=>'Feb','03'=>'Mar','04'=>'Apr','05'=>'Mei','06'=>'Jun','07'=>'Jul','08'=>'Ags','09'=>'Sep','10'=>'Okt','11'=>'Nov','12'=>'Des'];
$monthNums  = array_keys($months);
@endphp

<div class="card mb-4">
  <div class="card-header d-flex justify-content-between align-items-center">
    <span>Laporan P&L Bulanan {{ $year }}</span>
    <small class="text-muted">HPP dihitung dari: qty terjual × hpp/unit yang diset di menu HPP</small>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-sm table-hover mb-0" style="font-size:.8rem;">
        <thead class="table-light">
          <tr>
            <th style="min-width:180px">Komponen</th>
            @foreach($months as $m=>$ml)<th class="text-end">{{ $ml }}</th>@endforeach
            <th class="text-end fw-bold">Total</th>
          </tr>
        </thead>
        <tbody>
        @php
        $rows = [
            'gross_gmv'        => ['label'=>'Gross GMV',    'auto'=>true,  'separator_after'=>false],
            'net_gmv'          => ['label'=>'Net GMV',      'auto'=>true,  'separator_after'=>true],
            'cogs'             => ['label'=>'HPP / COGS',   'auto'=>true,  'separator_after'=>false],
            'ads_spend'        => ['label'=>'Ads Spend',    'auto'=>true,  'separator_after'=>false],
            'operational_cost' => ['label'=>'Biaya Ops',    'auto'=>false, 'separator_after'=>true],
            'gross_profit'     => ['label'=>'Gross Profit', 'auto'=>true,  'separator_after'=>false],
            'net_profit'       => ['label'=>'Net Profit',   'auto'=>true,  'separator_after'=>false],
        ];
        $profitKeys = ['gross_profit','net_profit'];
        @endphp

        @foreach($rows as $key=>$row)
        <tr class="{{ in_array($key,$profitKeys)?'fw-semibold table-active':'' }}">
          <td>
            {{ $row['label'] }}
            @if(!$row['auto'])
              <button type="button" class="btn btn-link btn-sm p-0 ms-1 text-primary"
                data-bs-toggle="modal" data-bs-target="#opsModal"
                title="Input Biaya Ops">
                <i class="bi bi-pencil-square" style="font-size:.75rem"></i>
              </button>
            @else
              <span class="badge bg-secondary-subtle text-secondary ms-1" style="font-size:.6rem">auto</span>
            @endif
          </td>

          @foreach($months as $m=>$ml)
          @php
            $period = $year.'-'.$m;
            $v = $byMonth[$period][$key] ?? 0;
          @endphp
          <td class="text-end {{ in_array($key,$profitKeys)&&$v<0?'text-danger':'' }}">
            @if($v != 0)
              {{ number_format($v/1000000,1) }}jt
            @else
              <span class="text-muted">—</span>
            @endif
          </td>
          @endforeach

          @php $total = collect($byMonth)->sum($key); @endphp
          <td class="text-end fw-bold {{ in_array($key,$profitKeys)?($total>=0?'text-success':'text-danger'):'' }}">
            {{ number_format($total/1000000,1) }}jt
          </td>
        </tr>
        @if($row['separator_after'])
          <tr><td colspan="15" class="p-0"><div style="border-top:2px solid #dee2e6"></div></td></tr>
        @endif
        @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>

{{-- Chart --}}
<div class="card">
  <div class="card-header">Tren Bulanan {{ $year }}</div>
  <div class="card-body"><canvas id="pnlChart" height="80"></canvas></div>
</div>

{{-- Modal Input Biaya Ops --}}
<div class="modal fade" id="opsModal">
  <div class="modal-dialog modal-lg"><div class="modal-content">
    <form method="POST" action="{{ route('pnl.saveOps') }}">@csrf
    <input type="hidden" name="year" value="{{ $year }}">
    <div class="modal-header">
      <h6 class="modal-title">Input Biaya Operasional — {{ $year }}</h6>
      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
    </div>
    <div class="modal-body">
      <div class="mb-3">
        <label class="form-label small">Toko / Cost Center</label>
        <select name="store_id" class="form-select form-select-sm" required>
          <option value="">Pilih Toko...</option>
          @foreach($stores as $s)
            <option value="{{ $s->id }}">{{ $s->name }} ({{ $s->brand }})</option>
          @endforeach
        </select>
        <div class="form-text">Biaya ops bisa dialokasikan per toko atau gunakan satu toko sebagai cost center brand.</div>
      </div>
      <div class="mb-3">
        <label class="form-label small">Bulan</label>
        <select name="month" class="form-select form-select-sm" required>
          @foreach($months as $m=>$ml)
            <option value="{{ $m }}">{{ $ml }}</option>
          @endforeach
        </select>
      </div>
      <div class="mb-3">
        <label class="form-label small">Biaya Operasional (Rp)</label>
        <input type="number" name="operational_cost" class="form-control form-control-sm" min="0" step="100000"
          placeholder="contoh: 5000000 untuk Rp 5jt" required>
        <div class="form-text">Termasuk: gaji, sewa gudang, utilitas, perlengkapan, dll.</div>
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
const pnlData = @json(array_values($byMonth));
const labels  = @json(array_values($months));

new Chart(document.getElementById('pnlChart'), {
  type: 'bar',
  data: {
    labels: labels,
    datasets: [
      {
        label: 'Gross GMV',
        data: pnlData.map(d => d.gross_gmv),
        backgroundColor: 'rgba(124,111,247,.5)',
        borderRadius: 3,
        order: 2,
      },
      {
        label: 'HPP+Ads',
        data: pnlData.map(d => d.cogs + d.ads_spend + d.operational_cost),
        backgroundColor: 'rgba(239,68,68,.4)',
        borderRadius: 3,
        order: 2,
      },
      {
        label: 'Net Profit',
        data: pnlData.map(d => d.net_profit),
        type: 'line',
        borderColor: '#10b981',
        backgroundColor: 'rgba(16,185,129,.1)',
        tension: .3,
        fill: true,
        order: 1,
      },
    ]
  },
  options: {
    responsive: true,
    scales: {
      y: { ticks: { callback: v => 'Rp ' + new Intl.NumberFormat('id').format(v) } }
    },
    plugins: { legend: { position: 'top' } }
  }
});
</script>
@endpush
@endsection
