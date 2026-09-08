@extends('layouts.app')
@section('title','Upload Data')
@section('page-title','Upload Data')
@section('content')

<div class="row g-3 mb-4">
  <div class="col-6 col-sm-3">
    <div class="stat-card">
      <div class="label">Total File</div>
      <div class="value" style="font-size:1.4rem">{{ number_format($stats['total_files']) }}</div>
    </div>
  </div>
  <div class="col-6 col-sm-3">
    <div class="stat-card">
      <div class="label">Total Baris</div>
      <div class="value" style="font-size:1.4rem">{{ number_format($stats['total_rows']) }}</div>
    </div>
  </div>
  <div class="col-6 col-sm-3">
    <div class="stat-card {{ $stats['failed']>0?'accent-red':'' }}">
      <div class="label">Upload Gagal</div>
      <div class="value" style="font-size:1.4rem">{{ $stats['failed'] }}</div>
    </div>
  </div>
  <div class="col-6 col-sm-3">
    <div class="stat-card">
      <div class="label">Upload Terakhir</div>
      <div class="value" style="font-size:.95rem;font-weight:600">
        {{ $stats['last_upload'] ? \Carbon\Carbon::parse($stats['last_upload'])->diffForHumans() : '—' }}
      </div>
    </div>
  </div>
</div>

<div class="row g-4">
  {{-- Upload form --}}
  <div class="col-md-4">
    <div class="card">
      <div class="card-header"><i class="bi bi-cloud-upload me-2"></i>Upload File Baru</div>
      <div class="card-body">
        <form method="POST" action="{{ route('upload.store') }}" enctype="multipart/form-data">
          @csrf
          <div class="mb-3">
            <label class="form-label small fw-semibold">Toko</label>
            <select name="store_id" class="form-select form-select-sm" required>
              <option value="">Pilih Toko...</option>
              @foreach($stores as $store)
              <option value="{{ $store->id }}">{{ $store->name }} — {{ $store->brand }}</option>
              @endforeach
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label small fw-semibold">Tipe Laporan</label>
            <select name="report_type" class="form-select form-select-sm" required>
              <option value="">Pilih Tipe...</option>
              <option value="orders">📦 Orders (Pesanan)</option>
              <option value="financials">💰 Financials (Keuangan)</option>
              <option value="ads">📢 Ads Performance (Iklan)</option>
              <option value="metrics">📊 Store Metrics (Funnel)</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label small fw-semibold">File</label>
            <input type="file" name="file" id="fileInput" class="form-control form-control-sm" accept=".csv,.xlsx,.xls" required>
            <div class="form-text">Maks. 20 MB. Format: .xlsx / .xls / .csv</div>
            {{-- Download template link, muncul saat tipe dipilih --}}
            <div id="templateLinks" class="mt-2" style="display:none">
              <a id="templateLink" href="#" class="btn btn-sm btn-outline-secondary w-100" style="font-size:.75rem;border-radius:var(--rounded-full)">
                <i class="bi bi-download me-1"></i>Download Template Excel
              </a>
            </div>
          </div>
          @if($errors->any())
          <div class="alert alert-danger py-2 small">{{ $errors->first() }}</div>
          @endif
          <button class="btn btn-primary w-100 btn-sm fw-semibold">
            <i class="bi bi-upload me-1"></i>Upload & Proses
          </button>
        </form>
      </div>
    </div>

    @if(auth()->user()?->role === 'admin')
    <div class="card mt-3 border-danger-subtle">
      <div class="card-header text-danger"><i class="bi bi-exclamation-triangle me-2"></i>Zona Berbahaya</div>
      <div class="card-body">
        <p class="small text-muted mb-2">Hapus semua data transaksi (orders, ads, metrics, financials, customers). Data master tidak terpengaruh.</p>
        <form method="POST" action="{{ route('admin.clear-reports') }}"
          onsubmit="return confirm('⚠️ HAPUS SEMUA DATA TRANSAKSI?\n\nIni tidak bisa dibatalkan.\n\nLanjutkan?')">
          @csrf
          <button type="submit" class="btn btn-sm btn-outline-danger w-100">
            <i class="bi bi-trash3 me-1"></i>Hapus Semua Data
          </button>
        </form>
      </div>
    </div>

    @endif
  </div>

  {{-- File manager --}}
  <div class="col-md-8">
    <div class="card">
      <div class="card-header d-flex align-items-center gap-2 flex-wrap">
        <span class="fw-semibold me-auto"><i class="bi bi-folder2-open me-2"></i>Manajemen File</span>
        {{-- Filter --}}
        <form method="GET" class="d-flex gap-2 align-items-center">
          <select name="filter_store" class="form-select form-select-sm" style="width:150px" onchange="this.form.submit()">
            <option value="all" {{ $filterStore==='all'?'selected':'' }}>Semua Toko</option>
            @foreach($stores as $s)
            <option value="{{ $s->id }}" {{ $filterStore==$s->id?'selected':'' }}>{{ $s->name }}</option>
            @endforeach
          </select>
          <select name="filter_type" class="form-select form-select-sm" style="width:130px" onchange="this.form.submit()">
            <option value="all" {{ $filterType==='all'?'selected':'' }}>Semua Tipe</option>
            <option value="orders"    {{ $filterType==='orders'?'selected':'' }}>Orders</option>
            <option value="financials"{{ $filterType==='financials'?'selected':'' }}>Financials</option>
            <option value="ads"       {{ $filterType==='ads'?'selected':'' }}>Ads</option>
            <option value="metrics"   {{ $filterType==='metrics'?'selected':'' }}>Metrics</option>
          </select>
        </form>
      </div>
      <div class="card-body p-0">
        <table class="table table-sm table-hover mb-0" style="font-size:.8rem;">
          <thead class="table-light">
            <tr>
              <th>File</th>
              <th>Toko</th>
              <th>Tipe</th>
              <th class="text-end">Baris</th>
              <th>Status</th>
              <th>Waktu</th>
              <th>Uploader</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
          @forelse($logs as $log)
          <tr>
            <td style="max-width:140px">
              <div class="text-truncate fw-semibold" title="{{ $log->filename }}">{{ $log->filename }}</div>
            </td>
            <td class="text-nowrap">{{ $log->store?->name ?? '—' }}</td>
            <td>
              @php
                $typeMeta = [
                  'orders'     => ['bg'=>'#e0e7ff','color'=>'#3730a3'],
                  'financials' => ['bg'=>'#dcfce7','color'=>'#166534'],
                  'ads'        => ['bg'=>'#fef9c3','color'=>'#854d0e'],
                  'metrics'    => ['bg'=>'#e0f2fe','color'=>'#075985'],
                ];
                $tm = $typeMeta[$log->report_type] ?? ['bg'=>'#f3f4f6','color'=>'#374151'];
              @endphp
              <span style="background:{{ $tm['bg'] }};color:{{ $tm['color'] }};border-radius:6px;padding:2px 8px;font-size:.75rem;font-weight:600;white-space:nowrap">
                {{ $log->report_type }}
              </span>
            </td>
            <td class="text-end fw-semibold">{{ $log->rows_imported ? number_format($log->rows_imported) : '—' }}</td>
            <td>
              @if($log->status==='success')
                <span class="badge bg-success">Berhasil</span>
              @elseif($log->status==='failed')
                <span class="badge bg-danger" title="{{ $log->error_message }}" data-bs-toggle="tooltip">Gagal</span>
              @else
                <span class="badge bg-warning text-dark">Proses</span>
              @endif
            </td>
            <td class="text-muted text-nowrap">{{ $log->uploaded_at?->format('d M Y H:i') ?? '—' }}</td>
            <td class="text-muted">{{ $log->user?->name ?? '—' }}</td>
            <td>
              <form method="POST" action="{{ route('upload.destroy',$log) }}" class="d-inline"
                onsubmit="return confirm('Hapus file ini dan semua datanya?\n\nFile: {{ $log->filename }}\nBaris: {{ number_format($log->rows_imported??0) }}\n\nLanjutkan?')">
                @csrf @method('DELETE')
                <button class="btn btn-sm btn-link text-danger p-0" title="Hapus file &amp; data">
                  <i class="bi bi-trash"></i>
                </button>
              </form>
            </td>
          </tr>
          @if($log->status==='failed' && $log->error_message)
          <tr class="table-danger">
            <td colspan="8" class="small text-danger py-1 px-3">
              <i class="bi bi-exclamation-circle me-1"></i>{{ $log->error_message }}
            </td>
          </tr>
          @endif
          @empty
          <tr><td colspan="8" class="text-center text-muted py-4">Belum ada file diupload</td></tr>
          @endforelse
          </tbody>
        </table>
      </div>
      @if($logs->hasPages())
      <div class="card-footer d-flex justify-content-between align-items-center" style="font-size:.8rem">
        <span class="text-muted">{{ $logs->firstItem() }}–{{ $logs->lastItem() }} dari {{ $logs->total() }} file</span>
        {{ $logs->links('pagination::bootstrap-5') }}
      </div>
      @endif
    </div>
  </div>
</div>
@push('scripts')
<script>
(function(){
  const templates = {
    orders:     '{{ route("template.orders") }}',
    ads:        '{{ route("template.ads") }}',
    metrics:    '{{ route("template.metrics") }}',
    financials: '{{ route("template.financials") }}',
  };
  const typeLabels = {
    orders: 'Template Orders (WA/Manual)',
    ads: 'Template Ads Performance',
    metrics: 'Template Store Metrics',
    financials: 'Template Financials',
  };
  const sel  = document.querySelector('select[name="report_type"]');
  const box  = document.getElementById('templateLinks');
  const link = document.getElementById('templateLink');
  if (sel && box && link) {
    sel.addEventListener('change', function() {
      const v = this.value;
      if (templates[v]) {
        link.href = templates[v];
        link.querySelector('span, i').nextSibling && null;
        link.innerHTML = '<i class="bi bi-download me-1"></i>' + typeLabels[v];
        box.style.display = '';
      } else {
        box.style.display = 'none';
      }
    });
  }
})();
</script>
@endpush
@endsection
