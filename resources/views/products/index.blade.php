@extends('layouts.app')
@section('title','Master Produk')
@section('page-title','Master Produk')
@section('content')

<div class="d-flex gap-2 align-items-center mb-3 flex-wrap">
  <form method="GET" class="d-flex gap-2 flex-grow-1">
    <input type="text" name="q" value="{{ $search }}" class="form-control form-control-sm" style="max-width:220px" placeholder="Cari SKU / nama...">
    <select name="brand" class="form-select form-select-sm" style="width:140px" onchange="this.form.submit()">
      <option value="all" {{ $brand==='all'?'selected':'' }}>Semua Brand</option>
      @foreach(['DTHREE','HURIM','ASFARA'] as $b)<option value="{{ $b }}" {{ $brand===$b?'selected':'' }}>{{ $b }}</option>@endforeach
    </select>
    <button class="btn btn-outline-secondary btn-sm">Cari</button>
  </form>
  <a href="{{ route('products.template') }}" class="btn btn-outline-success btn-sm"><i class="bi bi-download me-1"></i>Template</a>
  <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#importModal"><i class="bi bi-file-earmark-arrow-up me-1"></i>Import</button>
  <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addModal"><i class="bi bi-plus-lg me-1"></i>Tambah Produk</button>
</div>

@if(session('import_errors'))
<div class="alert alert-warning alert-dismissible fade show mb-3" style="font-size:.82rem">
  <strong>Beberapa baris dilewati:</strong>
  <ul class="mb-0 mt-1">@foreach(session('import_errors') as $e)<li>{{ $e }}</li>@endforeach</ul>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="card">
  <div class="card-body p-0">
    <table class="table table-sm table-hover mb-0" style="font-size:.82rem;">
      <thead class="table-light">
        <tr>
          <th>SKU</th>
          <th>Canonical Name <small class="text-muted fw-normal">(nama resmi)</small></th>
          <th>Brand</th>
          <th>Kategori</th>
          <th class="text-center">Status</th>
          <th class="text-end">Aksi</th>
        </tr>
      </thead>
      <tbody>
      @forelse($products as $p)
      <tr class="{{ $p->is_active ? '' : 'opacity-50' }}">
        <td><code>{{ $p->product_sku }}</code></td>
        <td class="fw-semibold">{{ $p->canonical_name }}</td>
        <td>
          @if($p->brand)<span class="badge badge-brand-{{ $p->brand }}">{{ $p->brand }}</span>@else<span class="text-muted">—</span>@endif
        </td>
        <td>{{ $p->category ?: '—' }}</td>
        <td class="text-center">
          <span class="badge {{ $p->is_active ? 'bg-success' : 'bg-secondary' }}">
            {{ $p->is_active ? 'Aktif' : 'Nonaktif' }}
          </span>
        </td>
        <td class="text-end">
          <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editModal{{ $p->id }}">
            <i class="bi bi-pencil"></i>
          </button>
          <form method="POST" action="{{ route('products.destroy', $p) }}" class="d-inline" onsubmit="return confirm('Hapus produk ini?')">
            @csrf @method('DELETE')
            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
          </form>
        </td>
      </tr>

      {{-- Edit Modal --}}
      <div class="modal fade" id="editModal{{ $p->id }}">
        <div class="modal-dialog"><div class="modal-content">
          <form method="POST" action="{{ route('products.update', $p) }}">@csrf @method('PUT')
          <div class="modal-header">
            <h6 class="modal-title">Edit Produk — <code>{{ $p->product_sku }}</code></h6>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="mb-2">
              <label class="form-label small fw-semibold">SKU</label>
              <input type="text" name="product_sku" value="{{ $p->product_sku }}" class="form-control form-control-sm" required>
            </div>
            <div class="mb-2">
              <label class="form-label small fw-semibold">Canonical Name <span class="text-danger">*</span></label>
              <input type="text" name="canonical_name" value="{{ $p->canonical_name }}" class="form-control form-control-sm" required>
              <div class="form-text">Nama ini yang akan dipakai di seluruh sistem (analitik, forecast, P&L)</div>
            </div>
            <div class="row g-2">
              <div class="col">
                <label class="form-label small fw-semibold">Brand</label>
                <select name="brand" class="form-select form-select-sm">
                  <option value="">—</option>
                  @foreach(['DTHREE','HURIM','ASFARA'] as $b)
                    <option value="{{ $b }}" {{ $p->brand===$b?'selected':'' }}>{{ $b }}</option>
                  @endforeach
                </select>
              </div>
              <div class="col">
                <label class="form-label small fw-semibold">Kategori</label>
                <input type="text" name="category" value="{{ $p->category }}" class="form-control form-control-sm" placeholder="contoh: Gamis">
              </div>
            </div>
            <div class="mt-2">
              <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" name="is_active" value="1" {{ $p->is_active?'checked':'' }}>
                <label class="form-check-label small">Produk Aktif</label>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
            <button class="btn btn-sm btn-primary">Simpan</button>
          </div>
          </form>
        </div></div>
      </div>

      @empty
      <tr>
        <td colspan="6" class="text-center text-muted py-5">
          <i class="bi bi-box-seam fs-2 d-block mb-2 opacity-25"></i>
          Belum ada produk. Tambah manual atau import via Excel.
        </td>
      </tr>
      @endforelse
      </tbody>
    </table>
  </div>
</div>

{{-- Add Modal --}}
<div class="modal fade" id="addModal">
  <div class="modal-dialog"><div class="modal-content">
    <form method="POST" action="{{ route('products.store') }}">@csrf
    <div class="modal-header">
      <h6 class="modal-title">Tambah Produk</h6>
      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
    </div>
    <div class="modal-body">
      <div class="mb-2">
        <label class="form-label small fw-semibold">SKU <span class="text-danger">*</span></label>
        <input type="text" name="product_sku" class="form-control form-control-sm" placeholder="contoh: DTH-001" required>
      </div>
      <div class="mb-2">
        <label class="form-label small fw-semibold">Canonical Name <span class="text-danger">*</span></label>
        <input type="text" name="canonical_name" class="form-control form-control-sm" placeholder="Nama resmi produk" required>
        <div class="form-text">Nama ini yang dipakai di seluruh sistem</div>
      </div>
      <div class="row g-2">
        <div class="col">
          <label class="form-label small fw-semibold">Brand</label>
          <select name="brand" class="form-select form-select-sm">
            <option value="">—</option>
            @foreach(['DTHREE','HURIM','ASFARA'] as $b)<option value="{{ $b }}">{{ $b }}</option>@endforeach
          </select>
        </div>
        <div class="col">
          <label class="form-label small fw-semibold">Kategori</label>
          <input type="text" name="category" class="form-control form-control-sm" placeholder="contoh: Gamis">
        </div>
      </div>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
      <button class="btn btn-sm btn-primary">Tambah</button>
    </div>
    </form>
  </div></div>
</div>

<x-import-modal
  id="importModal"
  title="Import Master Produk"
  action="{{ route('products.import') }}"
  template-route="{{ route('products.template') }}"
  template-label="Download Template Produk"
/>
@endsection
