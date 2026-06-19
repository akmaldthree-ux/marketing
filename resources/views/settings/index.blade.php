@extends('layouts.app')
@section('title','Settings — DSM Intelligence')
@section('page-title','Settings')
@section('breadcrumb')<li class="breadcrumb-item active">Settings</li>@endsection
@section('content')
<div class="row g-4">
    <div class="col-md-4"><div class="section-card"><div class="section-card-header"><h6><i class="bi bi-person me-2"></i>Profil Akun</h6></div><div class="p-3">
        <form action="{{ route('settings.profile') }}" method="POST">@csrf
            <div class="mb-3"><label class="form-label fw-semibold small">Nama</label><input type="text" name="name" class="form-control" value="{{ auth()->user()->name }}" required></div>
            <div class="mb-3"><label class="form-label fw-semibold small">Email</label><input type="email" name="email" class="form-control" value="{{ auth()->user()->email }}" required></div>
            <div class="mb-3"><label class="form-label fw-semibold small">Role</label><input type="text" class="form-control" value="{{ ucfirst(auth()->user()->role) }}" readonly></div>
            <div class="mb-3"><label class="form-label fw-semibold small">Password Baru (opsional)</label><input type="password" name="password" class="form-control" placeholder="Kosongkan jika tidak ubah"></div>
            <button type="submit" class="btn btn-primary w-100">Simpan Perubahan</button>
        </form>
    </div></div></div>
    <div class="col-md-8">
        <div class="section-card mb-4"><div class="section-card-header"><h6><i class="bi bi-people me-2"></i>Daftar User</h6><span class="badge bg-primary bg-opacity-10 text-primary">{{ $users->count() }} user</span></div>
        <div class="table-responsive"><table class="table mb-0"><thead><tr><th>Nama</th><th>Email</th><th>Role</th><th>PIC</th></tr></thead>
        <tbody>@foreach($users as $u)<tr><td class="fw-semibold">{{ $u->name }}</td><td class="text-muted small">{{ $u->email }}</td><td><span class="badge {{ $u->role==='admin'?'bg-danger':($u->role==='pic'?'bg-primary':'bg-secondary') }}">{{ ucfirst($u->role) }}</span></td><td class="small text-muted">{{ $u->pic?->name??'-' }}</td></tr>@endforeach</tbody>
        </table></div></div>
        <div class="section-card"><div class="section-card-header"><h6><i class="bi bi-shop me-2"></i>Daftar Toko</h6><span class="badge bg-success bg-opacity-10 text-success">{{ $stores->count() }} toko</span></div>
        <div class="table-responsive"><table class="table mb-0"><thead><tr><th>Nama Toko</th><th>Brand</th><th>Platform</th><th>PIC</th><th>Kanal</th><th>Status</th></tr></thead>
        <tbody>@foreach($stores as $s)<tr><td class="fw-semibold" style="font-size:0.85rem;">{{ $s->store_name }}</td><td><span class="brand-{{ $s->brand }}">{{ $s->brand }}</span></td><td><span class="badge bg-light text-dark border">{{ $s->platform }}</span></td><td class="small text-muted">{{ $s->pic?->name??'-' }}</td><td><span class="badge {{ $s->channel_type==='mp'?'bg-primary':'bg-success' }} bg-opacity-10 {{ $s->channel_type==='mp'?'text-primary':'text-success' }}">{{ strtoupper($s->channel_type) }}</span></td><td><span class="badge {{ $s->is_active?'bg-success':'bg-secondary' }}">{{ $s->is_active?'Aktif':'Nonaktif' }}</span></td></tr>@endforeach</tbody>
        </table></div></div>
    </div>
</div>
@endsection
