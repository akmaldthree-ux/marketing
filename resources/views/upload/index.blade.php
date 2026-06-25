@extends('layouts.app')
@section('title','Upload Data')
@section('page-title','Upload Data')
@section('content')
<div class="row g-4">
  <div class="col-md-5">
    <div class="card">
      <div class="card-header"><i class="bi bi-cloud-upload me-2"></i>Upload File Laporan</div>
      <div class="card-body">
        <form method="POST" action="{{ route('upload.store') }}" enctype="multipart/form-data">
          @csrf
          <div class="mb-3">
            <label class="form-label small fw-semibold">Toko</label>
            <select name="store_id" class="form-select form-select-sm" required>
              <option value="">Pilih Toko...</option>
              @foreach($stores as $store)
              <option value="{{ $store->id }}">{{ $store->name }} — {{ $store->brand }} / {{ $store->platform }}</option>
              @endforeach
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label small fw-semibold">Tipe Laporan</label>
            <select name="report_type" class="form-select form-select-sm" required>
              <option value="">Pilih Tipe...</option>
              <option value="orders">Orders (Pesanan)</option>
              <option value="financials">Financials (Keuangan)</option>
              <option value="ads">Ads Performance (Iklan)</option>
              <option value="metrics">Store Metrics (Funnel)</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label small fw-semibold">File (CSV / Excel)</label>
            <input type="file" name="file" class="form-control form-control-sm" accept=".csv,.xlsx,.xls" required>
            <div class="form-text">Maks. 20 MB. Format: CSV atau Excel (.xlsx/.xls)</div>
          </div>
          @if($errors->any())
          <div class="alert alert-danger py-2 small">{{ $errors->first() }}</div>
          @endif
          <button class="btn btn-primary w-100 btn-sm fw-semibold"><i class="bi bi-upload me-1"></i>Upload & Proses</button>
        </form>
      </div>
    </div>
  </div>
  <div class="col-md-7">
    <div class="card">
      <div class="card-header"><i class="bi bi-clock-history me-2"></i>Riwayat Upload (30 terakhir)</div>
      <div class="card-body p-0">
        <table class="table table-sm table-hover mb-0" style="font-size:.8rem;">
          <thead class="table-light"><tr><th>Toko</th><th>Tipe</th><th>File</th><th>Baris</th><th>Status</th><th>Waktu</th><th></th></tr></thead>
          <tbody>
          @forelse($logs as $log)
          <tr>
            <td>{{ $log->store?->name ?? '-' }}</td>
            <td><span class="badge bg-light text-dark border">{{ $log->report_type }}</span></td>
            <td class="text-truncate" style="max-width:120px" title="{{ $log->filename }}">{{ $log->filename }}</td>
            <td>{{ $log->rows_imported }}</td>
            <td>
              @if($log->status==='success')<span class="badge bg-success">Berhasil</span>
              @elseif($log->status==='failed')<span class="badge bg-danger" title="{{ $log->error_message }}" data-bs-toggle="tooltip">Gagal</span>
              @else<span class="badge bg-warning text-dark">Proses</span>@endif
            </td>
            <td class="text-muted">{{ $log->uploaded_at?->diffForHumans() ?? '-' }}</td>
            <td>
              <form method="POST" action="{{ route('upload.destroy',$log) }}" class="d-inline" onsubmit="return confirm('Hapus log ini?')">
                @csrf @method('DELETE')
                <button class="btn btn-sm btn-link text-danger p-0"><i class="bi bi-trash"></i></button>
              </form>
            </td>
          </tr>
          @if($log->status==='failed' && $log->error_message)
          <tr class="table-danger"><td colspan="7" class="small text-danger py-1 px-3"><i class="bi bi-exclamation-circle me-1"></i>{{ $log->error_message }}</td></tr>
          @endif
          @empty
          <tr><td colspan="7" class="text-center text-muted py-4">Belum ada upload</td></tr>
          @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection
