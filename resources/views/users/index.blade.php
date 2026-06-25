@extends('layouts.app')
@section('title','Manajemen User')
@section('page-title','Manajemen User')
@section('content')
<div class="d-flex justify-content-end mb-3">
  <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addModal"><i class="bi bi-plus-lg me-1"></i>Tambah User</button>
</div>
<div class="card">
  <div class="card-body p-0">
    <table class="table table-hover mb-0">
      <thead class="table-light"><tr><th>Nama</th><th>Email</th><th>Role</th><th>PIC</th><th class="text-end">Aksi</th></tr></thead>
      <tbody>
      @forelse($users as $user)
      <tr>
        <td class="fw-semibold">{{ $user->name }}</td>
        <td>{{ $user->email }}</td>
        <td>
          @if($user->role==='admin')<span class="badge bg-danger">admin</span>
          @elseif($user->role==='pic')<span class="badge bg-primary">pic</span>
          @else<span class="badge bg-secondary">viewer</span>@endif
        </td>
        <td>{{ $user->pic?->name ?? '-' }}</td>
        <td class="text-end">
          <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editModal{{ $user->id }}"><i class="bi bi-pencil"></i></button>
          @if($user->id !== auth()->id())
          <form method="POST" action="{{ route('users.destroy',$user) }}" class="d-inline" onsubmit="return confirm('Hapus user ini?')">
            @csrf @method('DELETE')
            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
          </form>
          @endif
        </td>
      </tr>
      <div class="modal fade" id="editModal{{ $user->id }}">
        <div class="modal-dialog"><div class="modal-content">
          <form method="POST" action="{{ route('users.update',$user) }}">@csrf @method('PUT')
          <div class="modal-header"><h6 class="modal-title">Edit User</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
          <div class="modal-body">
            <div class="mb-2"><label class="form-label small">Nama</label><input type="text" name="name" class="form-control form-control-sm" value="{{ $user->name }}" required></div>
            <div class="mb-2"><label class="form-label small">Email</label><input type="email" name="email" class="form-control form-control-sm" value="{{ $user->email }}" required></div>
            <div class="row g-2 mb-2">
              <div class="col"><label class="form-label small">Role</label>
                <select name="role" class="form-select form-select-sm" required>
                  @foreach(['admin','pic','viewer'] as $r)<option value="{{ $r }}" {{ $user->role==$r?'selected':'' }}>{{ $r }}</option>@endforeach
                </select>
              </div>
              <div class="col"><label class="form-label small">PIC</label>
                <select name="pic_id" class="form-select form-select-sm">
                  <option value="">— Tidak ada —</option>
                  @foreach($pics as $pic)<option value="{{ $pic->id }}" {{ $user->pic_id==$pic->id?'selected':'' }}>{{ $pic->name }}</option>@endforeach
                </select>
              </div>
            </div>
            <div class="mb-2"><label class="form-label small">Password Baru <span class="text-muted">(kosongkan jika tidak diganti)</span></label><input type="password" name="password" class="form-control form-control-sm" minlength="8"></div>
          </div>
          <div class="modal-footer"><button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button><button class="btn btn-sm btn-primary">Simpan</button></div>
          </form>
        </div></div>
      </div>
      @empty
      <tr><td colspan="5" class="text-center text-muted py-4"><i class="bi bi-people fs-3 d-block mb-2 opacity-50"></i>Belum ada user</td></tr>
      @endforelse
      </tbody>
    </table>
  </div>
</div>
<div class="modal fade" id="addModal">
  <div class="modal-dialog"><div class="modal-content">
    <form method="POST" action="{{ route('users.store') }}">@csrf
    <div class="modal-header"><h6 class="modal-title">Tambah User</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
      <div class="mb-2"><label class="form-label small">Nama</label><input type="text" name="name" class="form-control form-control-sm" required></div>
      <div class="mb-2"><label class="form-label small">Email</label><input type="email" name="email" class="form-control form-control-sm" required></div>
      <div class="mb-2"><label class="form-label small">Password</label><input type="password" name="password" class="form-control form-control-sm" required minlength="8"></div>
      <div class="row g-2 mb-2">
        <div class="col"><label class="form-label small">Role</label>
          <select name="role" class="form-select form-select-sm" required>
            <option value="viewer">viewer</option>
            <option value="pic">pic</option>
            <option value="admin">admin</option>
          </select>
        </div>
        <div class="col"><label class="form-label small">PIC</label>
          <select name="pic_id" class="form-select form-select-sm">
            <option value="">— Tidak ada —</option>
            @foreach($pics as $pic)<option value="{{ $pic->id }}">{{ $pic->name }}</option>@endforeach
          </select>
        </div>
      </div>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button><button class="btn btn-sm btn-primary">Tambah</button></div>
    </form>
  </div></div>
</div>
@endsection
