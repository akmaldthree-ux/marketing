@extends('layouts.app')
@section('title','HPP / COG')
@section('page-title','HPP per Produk')
@section('content')
<div class="d-flex justify-content-end mb-3">
  <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addModal"><i class="bi bi-plus-lg me-1"></i>Tambah HPP</button>
</div>
<div class="card">
  <div class="card-body p-0">
    <table class="table table-hover mb-0">
      <thead class="table-light"><tr><th>SKU</th><th>Nama Produk</th><th class="text-end">HPP/unit</th><th>Berlaku dari</th><th class="text-end">Aksi</th></tr></thead>
      <tbody>
      @forelse($cogs as $cog)
      <tr>
        <td><code>{{ $cog->product_sku }}</code></td>
        <td>{{ $cog->product_name }}</td>
        <td class="text-end fw-semibold">Rp {{ number_format($cog->hpp_per_unit,0,',','.') }}</td>
        <td>{{ $cog->effective_from?->format('d/m/Y') }}</td>
        <td class="text-end">
          <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editModal{{ $cog->id }}"><i class="bi bi-pencil"></i></button>
          <form method="POST" action="{{ route('cogs.destroy',$cog) }}" class="d-inline" onsubmit="return confirm('Hapus HPP ini?')">
            @csrf @method('DELETE')
            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
          </form>
        </td>
      </tr>
      <div class="modal fade" id="editModal{{ $cog->id }}">
        <div class="modal-dialog"><div class="modal-content">
          <form method="POST" action="{{ route('cogs.update',$cog) }}">@csrf @method('PUT')
          <div class="modal-header"><h6 class="modal-title">Edit HPP — {{ $cog->product_sku }}</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
          <div class="modal-body">
            <div class="mb-2"><label class="form-label small">SKU</label><input type="text" name="product_sku" class="form-control form-control-sm" value="{{ $cog->product_sku }}" required></div>
            <div class="mb-2"><label class="form-label small">Nama Produk</label><input type="text" name="product_name" class="form-control form-control-sm" value="{{ $cog->product_name }}" required></div>
            <div class="mb-2"><label class="form-label small">HPP per Unit (Rp)</label><input type="number" name="hpp_per_unit" class="form-control form-control-sm" value="{{ $cog->hpp_per_unit }}" min="0" required></div>
            <div class="mb-2"><label class="form-label small">Berlaku dari</label><input type="date" name="effective_from" class="form-control form-control-sm" value="{{ $cog->effective_from?->format('Y-m-d') }}" required></div>
          </div>
          <div class="modal-footer"><button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button><button class="btn btn-sm btn-primary">Simpan</button></div>
          </form>
        </div></div>
      </div>
      @empty
      <tr><td colspan="5" class="text-center text-muted py-4"><i class="bi bi-tag fs-3 d-block mb-2 opacity-50"></i>Belum ada data HPP</td></tr>
      @endforelse
      </tbody>
    </table>
  </div>
</div>

<div class="modal fade" id="addModal">
  <div class="modal-dialog"><div class="modal-content">
    <form method="POST" action="{{ route('cogs.store') }}">@csrf
    <div class="modal-header"><h6 class="modal-title">Tambah HPP</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
      <div class="mb-2"><label class="form-label small">SKU</label><input type="text" name="product_sku" class="form-control form-control-sm" placeholder="contoh: SKU-001" required></div>
      <div class="mb-2"><label class="form-label small">Nama Produk</label><input type="text" name="product_name" class="form-control form-control-sm" required></div>
      <div class="mb-2"><label class="form-label small">HPP per Unit (Rp)</label><input type="number" name="hpp_per_unit" class="form-control form-control-sm" min="0" required></div>
      <div class="mb-2"><label class="form-label small">Berlaku dari</label><input type="date" name="effective_from" class="form-control form-control-sm" required></div>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button><button class="btn btn-sm btn-primary">Tambah</button></div>
    </form>
  </div></div>
</div>
@endsection
