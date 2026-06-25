@extends('layouts.app')
@section('title','Manajemen Toko')
@section('page-title','Manajemen Toko')
@section('content')
<div class="d-flex justify-content-end mb-3">
  <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addModal"><i class="bi bi-plus-lg me-1"></i>Tambah Toko</button>
</div>
<div class="card">
  <div class="card-body p-0">
    <table class="table table-hover mb-0">
      <thead class="table-light"><tr><th>Nama Toko</th><th>Brand</th><th>Platform</th><th>Channel</th><th>PIC</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
      <tbody>
      @forelse($stores as $store)
      <tr>
        <td class="fw-semibold">{{ $store->name }}</td>
        <td><span class="badge badge-brand-{{ $store->brand }}">{{ $store->brand }}</span></td>
        <td><span class="badge bg-light text-dark border">{{ $store->platform }}</span></td>
        <td>{{ $store->channel_type === 'marketplace' ? 'Marketplace' : 'Non-MP' }}</td>
        <td>{{ $store->pic?->name ?? '-' }}</td>
        <td><span class="badge {{ $store->is_active ? 'bg-success' : 'bg-secondary' }}">{{ $store->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
        <td class="text-end">
          <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editModal{{ $store->id }}"><i class="bi bi-pencil"></i></button>
          <form method="POST" action="{{ route('stores.destroy',$store) }}" class="d-inline" onsubmit="return confirm('Hapus toko ini?')">
            @csrf @method('DELETE')
            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
          </form>
        </td>
      </tr>
      <div class="modal fade" id="editModal{{ $store->id }}">
        <div class="modal-dialog"><div class="modal-content">
          <form method="POST" action="{{ route('stores.update',$store) }}">@csrf @method('PUT')
          <div class="modal-header"><h6 class="modal-title">Edit Toko</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
          <div class="modal-body">
            <div class="mb-2"><label class="form-label small">Nama Toko</label><input type="text" name="name" class="form-control form-control-sm" value="{{ $store->name }}" required></div>
            <div class="row g-2 mb-2">
              <div class="col"><label class="form-label small">Brand</label>
                <select name="brand" class="form-select form-select-sm" required>
                  @foreach(['DTHREE','HURIM','ASFARA'] as $b)<option value="{{ $b }}" {{ $store->brand==$b?'selected':'' }}>{{ $b }}</option>@endforeach
                </select>
              </div>
              <div class="col"><label class="form-label small">Platform</label>
                <select name="platform" class="form-select form-select-sm" required>
                  @foreach(['Shopee','TikTok Shop','Meta Ads'] as $p)<option value="{{ $p }}" {{ $store->platform==$p?'selected':'' }}>{{ $p }}</option>@endforeach
                </select>
              </div>
            </div>
            <div class="row g-2 mb-2">
              <div class="col"><label class="form-label small">Channel</label>
                <select name="channel_type" class="form-select form-select-sm">
                  <option value="marketplace" {{ $store->channel_type=='marketplace'?'selected':'' }}>Marketplace</option>
                  <option value="non_marketplace" {{ $store->channel_type=='non_marketplace'?'selected':'' }}>Non-Marketplace</option>
                </select>
              </div>
              <div class="col"><label class="form-label small">PIC</label>
                <select name="pic_id" class="form-select form-select-sm">
                  <option value="">— Tidak ada —</option>
                  @foreach($pics as $pic)<option value="{{ $pic->id }}" {{ $store->pic_id==$pic->id?'selected':'' }}>{{ $pic->name }}</option>@endforeach
                </select>
              </div>
            </div>
            <div class="form-check"><input type="checkbox" name="is_active" value="1" class="form-check-input" {{ $store->is_active?'checked':'' }}><label class="form-check-label small">Aktif</label></div>
          </div>
          <div class="modal-footer"><button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button><button class="btn btn-sm btn-primary">Simpan</button></div>
          </form>
        </div></div>
      </div>
      @empty
      <tr><td colspan="7" class="text-center text-muted py-4"><i class="bi bi-shop fs-3 d-block mb-2 opacity-50"></i>Belum ada toko</td></tr>
      @endforelse
      </tbody>
    </table>
  </div>
</div>
<div class="modal fade" id="addModal">
  <div class="modal-dialog"><div class="modal-content">
    <form method="POST" action="{{ route('stores.store') }}">@csrf
    <div class="modal-header"><h6 class="modal-title">Tambah Toko</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
      <div class="mb-2"><label class="form-label small">Nama Toko</label><input type="text" name="name" class="form-control form-control-sm" required></div>
      <div class="row g-2 mb-2">
        <div class="col"><label class="form-label small">Brand</label>
          <select name="brand" class="form-select form-select-sm" required>
            <option value="">Pilih...</option>
            @foreach(['DTHREE','HURIM','ASFARA'] as $b)<option value="{{ $b }}">{{ $b }}</option>@endforeach
          </select>
        </div>
        <div class="col"><label class="form-label small">Platform</label>
          <select name="platform" class="form-select form-select-sm" required>
            <option value="">Pilih...</option>
            @foreach(['Shopee','TikTok Shop','Meta Ads'] as $p)<option value="{{ $p }}">{{ $p }}</option>@endforeach
          </select>
        </div>
      </div>
      <div class="row g-2 mb-2">
        <div class="col"><label class="form-label small">Channel</label>
          <select name="channel_type" class="form-select form-select-sm">
            <option value="marketplace">Marketplace</option>
            <option value="non_marketplace">Non-Marketplace</option>
          </select>
        </div>
        <div class="col"><label class="form-label small">PIC</label>
          <select name="pic_id" class="form-select form-select-sm">
            <option value="">— Tidak ada —</option>
            @foreach($pics as $pic)<option value="{{ $pic->id }}">{{ $pic->name }}</option>@endforeach
          </select>
        </div>
      </div>
      <div class="form-check"><input type="checkbox" name="is_active" value="1" class="form-check-input" checked><label class="form-check-label small">Aktif</label></div>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button><button class="btn btn-sm btn-primary">Tambah</button></div>
    </form>
  </div></div>
</div>
@endsection
