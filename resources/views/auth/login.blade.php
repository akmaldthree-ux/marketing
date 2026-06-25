<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Login — DSM Intelligence</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
:root{
  --color-primary:#e60023;--color-canvas:#ffffff;
  --color-surface-soft:#fbfbf9;--color-surface-card:#f6f6f3;
  --color-secondary-bg:#e5e5e0;--color-hairline:#dadad3;
  --color-ink:#000000;--color-body:#33332e;--color-ash:#91918c;
  --color-mute:#62625b;--color-error:#9e0a0a;
  --rounded-md:16px;--rounded-lg:32px;
}
*{box-sizing:border-box;}
body{
  background:var(--color-surface-soft);
  min-height:100vh;display:flex;align-items:center;justify-content:center;
  font-family:'Inter',-apple-system,system-ui,'Segoe UI',Roboto,'Helvetica Neue',Arial,sans-serif;
  color:var(--color-body);padding:1rem;
}
.login-card{
  width:100%;max-width:400px;
  background:var(--color-canvas);
  border-radius:var(--rounded-lg);
  border:1px solid var(--color-hairline);
  box-shadow:0 16px 64px rgba(0,0,0,.08);
  padding:2rem 2rem 1.75rem;
}
.brand-logo{
  width:40px;height:40px;border-radius:50%;
  background:var(--color-primary);
  display:flex;align-items:center;justify-content:center;
  margin:0 auto .75rem;
}
.brand-logo i{color:#fff;font-size:1.1rem;}
h2{font-size:1.1rem;font-weight:700;letter-spacing:-.3px;color:var(--color-ink);margin:0 0 .25rem;}
.subtitle{font-size:.8rem;color:var(--color-ash);}
.form-label{font-size:.8rem;font-weight:600;color:var(--color-ink);margin-bottom:.3rem;display:block;}
.form-control{
  width:100%;border:1px solid var(--color-ash);
  border-radius:var(--rounded-md);
  font-size:.85rem;padding:10px 13px;
  color:var(--color-ink);background:var(--color-canvas);
  outline:none;transition:border-color .12s,box-shadow .12s;
}
.form-control:focus{
  border-color:var(--color-ink);
  box-shadow:0 0 0 3px rgba(67,94,229,.18);
}
.btn-login{
  width:100%;background:var(--color-primary);
  color:#fff;border:none;
  border-radius:var(--rounded-md);
  padding:11px;font-size:.85rem;font-weight:700;
  cursor:pointer;transition:background .12s;
}
.btn-login:hover{background:#cc001f;}
.alert-error{
  background:#fee2e2;border:1px solid #fca5a5;
  border-radius:var(--rounded-md);
  color:var(--color-error);font-size:.8rem;
  padding:.6rem .9rem;margin-bottom:1rem;
  display:flex;align-items:center;gap:.5rem;
}
.remember-row{display:flex;align-items:center;gap:.5rem;}
.remember-row input[type=checkbox]{accent-color:var(--color-primary);width:15px;height:15px;}
.remember-row label{font-size:.78rem;color:var(--color-mute);cursor:pointer;}
</style>
</head>
<body>
<div class="login-card">
  <div class="text-center mb-4">
    <div class="brand-logo"><i class="bi bi-bar-chart-fill"></i></div>
    <h2>DSM Intelligence</h2>
    <div class="subtitle">Marketing Platform — Masuk ke akun Anda</div>
  </div>

  @if($errors->any())
  <div class="alert-error"><i class="bi bi-exclamation-circle"></i>{{ $errors->first() }}</div>
  @endif

  <form method="POST" action="/login">
    @csrf
    <div class="mb-3">
      <label class="form-label">Email</label>
      <input type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="nama@perusahaan.com" required autofocus>
    </div>
    <div class="mb-4">
      <label class="form-label">Password</label>
      <input type="password" name="password" class="form-control" placeholder="••••••••" required>
    </div>
    <div class="remember-row mb-4">
      <input type="checkbox" name="remember" id="remember">
      <label for="remember">Ingat saya</label>
    </div>
    <button type="submit" class="btn-login">Masuk</button>
  </form>
</div>
</body>
</html>
