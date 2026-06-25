<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Login — DSM Intelligence</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>body{background:#f5f6fa;min-height:100vh;display:flex;align-items:center;justify-content:center;}</style>
</head>
<body>
<div class="card shadow-sm border-0" style="width:100%;max-width:400px;">
  <div class="card-body p-4">
    <div class="text-center mb-4">
      <h4 class="fw-bold mb-0">DSM Intelligence</h4>
      <small class="text-muted">Marketing Platform</small>
    </div>
    @if($errors->any())
    <div class="alert alert-danger py-2"><i class="bi bi-exclamation-circle me-1"></i>{{ $errors->first() }}</div>
    @endif
    <form method="POST" action="/login">
      @csrf
      <div class="mb-3">
        <label class="form-label small fw-semibold">Email</label>
        <input type="email" name="email" class="form-control" value="{{ old('email') }}" required autofocus>
      </div>
      <div class="mb-3">
        <label class="form-label small fw-semibold">Password</label>
        <input type="password" name="password" class="form-control" required>
      </div>
      <div class="d-flex align-items-center mb-3">
        <input type="checkbox" name="remember" class="form-check-input me-2" id="remember">
        <label for="remember" class="form-check-label small">Ingat saya</label>
      </div>
      <button class="btn btn-primary w-100 fw-semibold">Masuk</button>
    </form>
  </div>
</div>
</body>
</html>
