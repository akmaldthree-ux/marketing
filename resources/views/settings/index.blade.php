@extends('layouts.app')
@section('title','Pengaturan')
@section('page-title','Pengaturan Akun')
@section('content')
<div class="row g-4">
  <div class="col-md-6">
    <div class="card">
      <div class="card-header"><i class="bi bi-person me-2"></i>Profil Saya</div>
      <div class="card-body">
        <form method="POST" action="{{ route('settings.profile') }}">
          @csrf @method('PUT')
          <div class="mb-3">
            <label class="form-label small fw-semibold">Nama</label>
            <input type="text" name="name" class="form-control" value="{{ auth()->user()->name }}" required>
            @error('name')<div class="text-danger small">{{ $message }}</div>@enderror
          </div>
          <div class="mb-3">
            <label class="form-label small fw-semibold">Email</label>
            <input type="email" name="email" class="form-control" value="{{ auth()->user()->email }}" required>
            @error('email')<div class="text-danger small">{{ $message }}</div>@enderror
          </div>
          <div class="mb-3">
            <label class="form-label small fw-semibold">Role</label>
            <input type="text" class="form-control" value="{{ auth()->user()->role }}" readonly>
          </div>
          <button class="btn btn-primary btn-sm">Simpan Profil</button>
        </form>
      </div>
    </div>
  </div>
  <div class="col-md-6">
    <div class="card">
      <div class="card-header"><i class="bi bi-lock me-2"></i>Ganti Password</div>
      <div class="card-body">
        <form method="POST" action="{{ route('settings.password') }}">
          @csrf @method('PUT')
          <div class="mb-3">
            <label class="form-label small fw-semibold">Password Lama</label>
            <input type="password" name="current_password" class="form-control" required>
            @error('current_password')<div class="text-danger small">{{ $message }}</div>@enderror
          </div>
          <div class="mb-3">
            <label class="form-label small fw-semibold">Password Baru</label>
            <input type="password" name="password" class="form-control" required minlength="8">
            @error('password')<div class="text-danger small">{{ $message }}</div>@enderror
          </div>
          <div class="mb-3">
            <label class="form-label small fw-semibold">Konfirmasi Password Baru</label>
            <input type="password" name="password_confirmation" class="form-control" required>
          </div>
          <button class="btn btn-warning btn-sm">Ganti Password</button>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
