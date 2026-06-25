@extends('layouts.app')
@section('title','Forecasting Produk')
@section('page-title','Forecasting Produk')
@section('content')

{{-- Form Input --}}
<div class="card mb-4">
  <div class="card-header"><i class="bi bi-sliders me-2"></i>Parameter Forecast</div>
  <div class="card-body">
    <form method="GET" class="row g-3 align-items-end">

      <div class="col-sm-6 col-md-3">
        <label class="form-label small fw-semibold">Bulan Target</label>
        <input type="month" name="target_month" value="{{ $targetMonth }}"
          class="form-control form-control-sm">
      </div>

      <div class="col-sm-6 col-md-3">
        <label class="form-label small fw-semibold">Target GMV (Rp)</label>
        <input type="number" name="target_gmv" value="{{ $targetGmv ?: '' }}"
          class="form-control form-control-sm" min="0" step="1000000"
          placeholder="contoh: 150000000">
        @if($targetGmv > 0)
        <div class="form-text">Rp {{ number_format($targetGmv/1000000,1) }} juta</div>
        @endif
      </div>

      <div class="col-sm-6 col-md-2">
        <label class="form-label small fw-semibold">Rata-rata Harga (Rp)</label>
        <input type="number" name="avg_price" value="{{ $avgPrice }}"
          class="form-control form-control-sm" min="1" step="1000">
        <div class="form-text">Rp {{ number_format($avgPrice/1000,0) }}k/unit</div>
      </div>

      <div class="col-sm-6 col-md-2">
        <label class="form-label small fw-semibold">Periode Histori</label>
        <select name="period" class="form-select form-select-sm">
          @foreach([1=>'1 Bulan',3=>'3 Bulan',6=>'6 Bulan',12=>'12 Bulan'] as $v=>$l)
            <option value="{{ $v }}" {{ $period==$v?'selected':'' }}>{{ $l }}</option>
          @endforeach
        </select>
      </div>

      <div class="col-sm-6 col-md-2">
        <label class="form-label small fw-semibold">Brand</label>
        <select name="brand" class="form-select form-select-sm">
          <option value="all" {{ $brand==='all'?'selected':'' }}>Semua Brand</option>
          @foreach(['DTHREE','HURIM','ASFARA'] as $b)
            <option value="{{ $b }}" {{ $brand===$b?'selected':'' }}>{{ $b }}</option>
          @endforeach
        </select>
      </div>

      <div class="col-12 d-flex gap-2">
        <button class="btn btn-primary btn-sm px-4">
          <i class="bi bi-calculator me-1"></i>Hitung Forecast
        </button>
        @if($forecast->isNotEmpty())
        <a href="{{ route('forecast.export', request()->all()) }}" class="btn btn-success btn-sm">
          <i class="bi bi-download me-1"></i>Export CSV
        </a>
        @endif
      </div>
    </form>
  </div>
</div>

@if($targetGmv <= 0)
{{-- Empty state --}}
<div class="card">
  <div class="card-body text-center py-5 text-muted">
    <i class="bi bi-graph-up fs-1 d-block mb-3 opacity-25"></i>
    <div class="fw-semibold mb-1">Masukkan Target GMV untuk mulai forecast</div>
    <small>Sistem akan menghitung kebutuhan produk berdasarkan histori penjualan {{ $period }} bulan terakhir.</small>
  </div>
</div>

@elseif($forecast->isEmpty())
<div class="card">
  <div class="card-body text-center py-5 text-muted">
    <i class="bi bi-inbox fs-1 d-block mb-3 opacity-25"></i>
    <div class="fw-semibold mb-1">Tidak ada data histori penjualan</div>
    <small>Belum ada data order untuk brand/periode yang dipilih. Upload data terlebih dahulu.</small>
  </div>
</div>

@else
{{-- KPI Cards --}}
<div class="row g-3 mb-4">
  <div class="col-sm-6 col-xl-3">
    <div class="stat-card">
      <div class="label">Target GMV</div>
      <div class="value text-primary">Rp {{ number_format($targetGmv/1000000,1) }}jt</div>
      <div class="delta text-muted">{{ \Carbon\Carbon::createFromFormat('Y-m',$targetMonth)->translatedFormat('F Y') }}</div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="stat-card">
      <div class="label">Total Unit Target</div>
      <div class="value text-success">{{ number_format($totalUnitTarget) }} unit</div>
      <div class="delta text-muted">Rp {{ number_format($targetGmv/1000000,1) }}jt ÷ Rp {{ number_format($avgPrice/1000,0) }}k</div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="stat-card">
      <div class="label">Jumlah SKU</div>
      <div class="value">{{ $forecast->count() }}</div>
      <div class="delta text-muted">produk aktif terjual</div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="stat-card">
      <div class="label">Dasar Histori</div>
      <div class="value" style="font-size:1.1rem">{{ number_format($totalQtyHistori) }} unit</div>
      <div class="delta text-muted">{{ $histStart->format('d M') }} – {{ $histEnd->format('d M Y') }}</div>
    </div>
  </div>
</div>

<div class="row g-3 mb-4">
  {{-- Tabel Forecast --}}
  <div class="col-lg-8">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span>Rincian Forecast per Produk</span>
        <small class="text-muted">Berdasarkan histori {{ $period }} bulan · {{ $brand === 'all' ? 'Semua Brand' : $brand }}</small>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-sm table-hover mb-0" style="font-size:.8rem;">
            <thead class="table-light">
              <tr>
                <th>#</th>
                <th>SKU</th>
                <th>Nama Produk</th>
                <th class="text-end">Histori Qty</th>
                <th class="text-end">% Share</th>
                <th class="text-end">Forecast Unit</th>
                <th class="text-end">Forecast GMV</th>
              </tr>
            </thead>
            <tbody>
            @foreach($forecast as $i => $row)
            <tr>
              <td class="text-muted">{{ $i+1 }}</td>
              <td><code>{{ $row->sku }}</code></td>
              <td>
                {{ $row->name }}
                @if($i === 0)<span class="badge bg-warning text-dark ms-1" style="font-size:.6rem">Top</span>@endif
              </td>
              <td class="text-end text-muted">{{ number_format($row->hist_qty) }}</td>
              <td class="text-end">
                <div class="d-flex align-items-center justify-content-end gap-1">
                  <div class="progress flex-grow-1" style="height:4px;width:50px">
                    <div class="progress-bar bg-primary" style="width:{{ min(100,$row->pct) }}%"></div>
                  </div>
                  <span>{{ $row->pct }}%</span>
                </div>
              </td>
              <td class="text-end fw-semibold text-success">{{ number_format($row->unit_forecast) }}</td>
              <td class="text-end fw-semibold text-primary">Rp {{ number_format($row->gmv_forecast/1000000,1) }}jt</td>
            </tr>
            @endforeach
            </tbody>
            <tfoot class="table-light fw-bold">
              <tr>
                <td colspan="5" class="text-end">Total</td>
                <td class="text-end text-success">{{ number_format($forecast->sum('unit_forecast')) }} unit</td>
                <td class="text-end text-primary">Rp {{ number_format($forecast->sum('gmv_forecast')/1000000,1) }}jt</td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
    </div>
  </div>

  {{-- Chart Donut Share --}}
  <div class="col-lg-4">
    <div class="card">
      <div class="card-header">Distribusi % Share Produk</div>
      <div class="card-body">
        <canvas id="shareChart"></canvas>
        <div class="mt-3" style="font-size:.75rem">
          @foreach($forecast->take(6) as $row)
          <div class="d-flex justify-content-between mb-1">
            <span class="text-truncate" style="max-width:140px" title="{{ $row->name }}">{{ $row->name }}</span>
            <span class="fw-semibold">{{ $row->pct }}%</span>
          </div>
          @endforeach
          @if($forecast->count() > 6)
          <div class="text-muted">+ {{ $forecast->count()-6 }} produk lainnya</div>
          @endif
        </div>
      </div>
    </div>
  </div>
</div>

{{-- Cara baca --}}
<div class="card border-0 bg-light">
  <div class="card-body py-3" style="font-size:.8rem">
    <div class="fw-semibold mb-2"><i class="bi bi-lightbulb me-1 text-warning"></i>Cara membaca hasil forecast:</div>
    <div class="row g-2">
      <div class="col-md-4">
        <strong>Total Unit Target</strong> = Target GMV ÷ Avg Price<br>
        <code>{{ number_format($targetGmv,0,',','.') }} ÷ {{ number_format($avgPrice,0,',','.') }} = {{ number_format($totalUnitTarget) }} unit</code>
      </div>
      <div class="col-md-4">
        <strong>% Share</strong> = Qty terjual produk X ÷ Total qty semua produk (histori {{ $period }} bln)
      </div>
      <div class="col-md-4">
        <strong>Forecast Unit produk X</strong> = Total Unit Target × % Share produk X
      </div>
    </div>
  </div>
</div>
@endif

@push('scripts')
@if($forecast->isNotEmpty())
<script>
const shareLabels = @json($forecast->pluck('name'));
const shareData   = @json($forecast->pluck('pct'));
const colors = [
  '#7c6ff7','#f59e0b','#10b981','#ef4444','#3b82f6','#ec4899',
  '#8b5cf6','#14b8a6','#f97316','#06b6d4','#84cc16','#a855f7'
];
new Chart(document.getElementById('shareChart'), {
  type: 'doughnut',
  data: {
    labels: shareLabels,
    datasets: [{
      data: shareData,
      backgroundColor: colors.slice(0, shareLabels.length),
      borderWidth: 2,
    }]
  },
  options: {
    responsive: true,
    plugins: {
      legend: { display: false },
      tooltip: {
        callbacks: {
          label: ctx => `${ctx.label}: ${ctx.parsed}%`
        }
      }
    }
  }
});
</script>
@endif
@endpush
@endsection
