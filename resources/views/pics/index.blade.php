@extends('layouts.app')
@section('title','Manajemen PIC')
@section('page-title','Manajemen PIC')
@section('content')
<div class="d-flex justify-content-end mb-3">
  <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addModal"><i class="bi bi-plus-lg me-1"></i>Tambah PIC</button>
</div>
<div class="card">
  <div class="card-body p-0">
    <table class="table table-hover mb-0">
      <thead class="table-light"><tr><th>Nama</th><th>Email</th><th>Toko</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
      <tbody>
      @forelse($pics as $pic)
      <tr>
        <td class="fw-semibold">{{ $pic->name }}</td>
        <td>{{ $pic->email }}</td>
        <td><span class="badge bg-light text-dark border">{{ $pic->stores->count() }} toko</span></td>
        <td><span class="badge {{ $pic->is_active ? 'bg-success' : 'bg-secondary' }}">{{ $pic->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
        <td class="text-end">
          <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editModal{{ $pic->id }}"><i class="bi bi-pencil"></i></button>
          <form method="POST" action="{{ route('pics.destroy',$pic) }}" class="d-inline" onsubmit="return confirm('Hapus PIC ini?')">
            @csrf @method('DELETE')
            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
          </form>
        </td>
      </tr>
      <div class="modal fade" id="editModal{{ $pic->id }}">
        <div class="modal-dialog"><div class="modal-content">
          <form method="POST" action="{{ route('pics.update',$pic) }}">@csrf @method('PUT')
          <div class="modal-header"><h6 class="modal-title">Edit PIC</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
          <div class="modal-body">
            <div class="mb-2"><label class="form-label small">Nama</label><input type="text" name="name" class="form-control form-control-sm" value="{{ $pic->name }}" required></div>
            <div class="mb-2"><label class="form-label small">Email</label><input type="email" name="email" class="form-control form-control-sm" value="{{ $pic->email }}" required></div>
            <div class="mb-2"><label class="form-label small">Password Baru <span class="text-muted">(kosongkan jika tidak diganti)</span></label><input type="password" name="password" class="form-control form-control-sm" minlength="8"></div>
            <div class="form-check"><input type="checkbox" name="is_active" value="1" class="form-check-input" {{ $pic->is_active?'checked':'' }}><label class="form-check-label small">Aktif</label></div>
          </div>
          <div class="modal-footer"><button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button><button class="btn btn-sm btn-primary">Simpan</button></div>
          </form>
        </div></div>
      </div>
      @empty
      <tr><td colspan="5" class="text-center text-muted py-4"><i class="bi bi-person-badge fs-3 d-block mb-2 opacity-50"></i>Belum ada PIC</td></tr>
      @endforelse
      </tbody>
    </table>
  </div>
</div>
<div class="modal fade" id="addModal">
  <div class="modal-dialog"><div class="modal-content">
    <form method="POST" action="{{ route('pics.store') }}">@csrf
    <div class="modal-header"><h6 class="modal-title">Tambah PIC</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
      <div class="mb-2"><label class="form-label small">Nama</label><input type="text" name="name" class="form-control form-control-sm" required></div>
      <div class="mb-2"><label class="form-label small">Email</label><input type="email" name="email" class="form-control form-control-sm" required></div>
      <div class="mb-2"><label class="form-label small">Password</label><input type="password" name="password" class="form-control form-control-sm" required minlength="8"></div>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button><button class="btn btn-sm btn-primary">Tambah</button></div>
    </form>
  </div></div>
</div>
@endsection
