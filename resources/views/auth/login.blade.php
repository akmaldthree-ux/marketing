<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — DSM Marketing Intelligence Platform</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { background: #0D1526; font-family: 'DM Sans', sans-serif; min-height: 100vh; display: flex; align-items: center; }
        .login-card { background: #fff; border-radius: 16px; padding: 2.5rem; width: 100%; max-width: 420px; }
        .brand-badge { background: #2563EB; color: #fff; width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; margin: 0 auto 1rem; }
        .form-control:focus { border-color: #2563EB; box-shadow: 0 0 0 3px rgba(37,99,235,0.15); }
        .btn-login { background: #2563EB; border-color: #2563EB; font-weight: 600; padding: 0.65rem; }
        .btn-login:hover { background: #1D4ED8; border-color: #1D4ED8; }
        .demo-creds { background: #F8FAFC; border-radius: 8px; padding: 0.75rem 1rem; font-size: 0.8rem; }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="login-card shadow">
                    <div class="text-center mb-4">
                        <div class="brand-badge"><i class="bi bi-graph-up-arrow"></i></div>
                        <h4 class="fw-bold mb-0">DSM Intelligence</h4>
                        <p class="text-muted small mt-1">Marketing Analytics Platform</p>
                        <p class="small" style="color:#64748B;">DTHREE® · HURIM · ASFARA</p>
                    </div>

                    @if($errors->any())
                    <div class="alert alert-danger py-2">
                        <i class="bi bi-exclamation-circle me-1"></i>{{ $errors->first() }}
                    </div>
                    @endif

                    <form action="/login" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Email</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-envelope text-muted"></i></span>
                                <input type="email" name="email" class="form-control" placeholder="email@dsm.co.id" value="{{ old('email') }}" required autofocus>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-semibold small">Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock text-muted"></i></span>
                                <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                            </div>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" name="remember" id="remember">
                            <label class="form-check-label small" for="remember">Ingat saya</label>
                        </div>
                        <button type="submit" class="btn btn-primary btn-login w-100 text-white">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Masuk
                        </button>
                    </form>

                    <div class="demo-creds mt-4">
                        <div class="fw-semibold mb-1 text-muted" style="font-size:0.72rem;text-transform:uppercase;letter-spacing:0.05em;">Demo Credentials</div>
                        <div><b>Admin:</b> admin@dsm.co.id / password</div>
                        <div><b>PIC:</b> sinta@dsm.co.id / password</div>
                        <div><b>Viewer:</b> viewer@dsm.co.id / password</div>
                    </div>
                </div>
                <p class="text-center mt-3" style="color:rgba(255,255,255,0.3);font-size:0.78rem;">CONFIDENTIAL — INTERNAL USE ONLY</p>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
