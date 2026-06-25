<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Login — DSM Intelligence</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
*{box-sizing:border-box;margin:0;padding:0;}
:root{
  --red:#e60023;--red-dark:#cc001f;
  --ink:#000000;--body:#33332e;--mute:#62625b;--ash:#91918c;
  --canvas:#ffffff;--surface:#fbfbf9;--card:#f6f6f3;
  --hairline:#dadad3;--r-md:16px;--r-lg:28px;
}
body{
  min-height:100vh;display:flex;align-items:center;justify-content:center;
  background:var(--surface);
  font-family:'Inter',-apple-system,system-ui,sans-serif;
  padding:1.5rem;
}

/* Outer card wrapper */
.login-wrapper{
  width:100%;max-width:920px;
  background:var(--canvas);
  border-radius:var(--r-lg);
  border:1px solid var(--hairline);
  box-shadow:0 20px 80px rgba(0,0,0,.09);
  overflow:hidden;
  display:grid;grid-template-columns:1fr 1fr;
  min-height:540px;
}

/* ── Left panel (feature showcase) ── */
.panel-left{
  background:var(--ink);
  padding:2.5rem;
  display:flex;flex-direction:column;
  position:relative;overflow:hidden;
}
/* subtle grid texture */
.panel-left::before{
  content:'';position:absolute;inset:0;
  background-image:
    linear-gradient(rgba(255,255,255,.03) 1px,transparent 1px),
    linear-gradient(90deg,rgba(255,255,255,.03) 1px,transparent 1px);
  background-size:28px 28px;
  pointer-events:none;
}
/* red accent blob */
.panel-left::after{
  content:'';position:absolute;
  width:320px;height:320px;border-radius:50%;
  background:radial-gradient(circle,rgba(230,0,35,.28) 0%,transparent 70%);
  bottom:-80px;right:-60px;pointer-events:none;
}
.brand-mark{
  display:flex;align-items:center;gap:.6rem;margin-bottom:2.5rem;
  position:relative;z-index:1;
}
.brand-icon{
  width:36px;height:36px;border-radius:50%;
  background:var(--red);
  display:flex;align-items:center;justify-content:center;
  flex-shrink:0;
}
.brand-icon i{color:#fff;font-size:.9rem;}
.brand-name{font-size:.9rem;font-weight:700;color:#fff;letter-spacing:-.2px;}
.brand-tagline{font-size:.68rem;color:rgba(255,255,255,.4);font-weight:400;}

.panel-headline{
  font-size:1.45rem;font-weight:700;color:#fff;
  letter-spacing:-.5px;line-height:1.2;
  margin-bottom:.6rem;position:relative;z-index:1;
}
.panel-headline span{color:var(--red);}
.panel-sub{
  font-size:.8rem;color:rgba(255,255,255,.5);
  line-height:1.55;margin-bottom:2rem;
  position:relative;z-index:1;
}

/* Feature list */
.feature-list{
  list-style:none;display:flex;flex-direction:column;gap:.75rem;
  position:relative;z-index:1;flex-grow:1;
}
.feature-item{
  display:flex;align-items:flex-start;gap:.75rem;
}
.feature-icon{
  width:30px;height:30px;border-radius:10px;
  background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.1);
  display:flex;align-items:center;justify-content:center;
  flex-shrink:0;margin-top:1px;
}
.feature-icon i{font-size:.8rem;color:rgba(255,255,255,.7);}
.feature-text strong{display:block;font-size:.8rem;font-weight:600;color:#fff;margin-bottom:1px;}
.feature-text span{font-size:.72rem;color:rgba(255,255,255,.4);line-height:1.4;}

/* Brand strip at bottom */
.panel-brands{
  display:flex;align-items:center;gap:.5rem;
  margin-top:auto;padding-top:1.5rem;
  position:relative;z-index:1;
}
.brand-pill{
  background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.12);
  border-radius:9999px;padding:3px 10px;
  font-size:.65rem;font-weight:600;color:rgba(255,255,255,.5);
  letter-spacing:.3px;
}

/* ── Right panel (form) ── */
.panel-right{
  padding:2.5rem;
  display:flex;flex-direction:column;justify-content:center;
}
.form-heading{font-size:1.1rem;font-weight:700;color:var(--ink);letter-spacing:-.3px;margin-bottom:.25rem;}
.form-subheading{font-size:.78rem;color:var(--ash);margin-bottom:2rem;}

.field{margin-bottom:1.1rem;}
.field label{display:block;font-size:.78rem;font-weight:600;color:var(--body);margin-bottom:.4rem;}
.field input{
  width:100%;border:1px solid var(--hairline);
  border-radius:var(--r-md);
  font-size:.85rem;padding:10px 14px;
  color:var(--ink);background:var(--canvas);
  outline:none;font-family:inherit;
  transition:border-color .12s,box-shadow .12s;
}
.field input:focus{
  border-color:var(--ink);
  box-shadow:0 0 0 3px rgba(67,94,229,.15);
}
.field input::placeholder{color:var(--ash);}

.remember-row{
  display:flex;align-items:center;gap:.5rem;
  margin-bottom:1.5rem;
}
.remember-row input[type=checkbox]{
  accent-color:var(--red);width:15px;height:15px;cursor:pointer;
}
.remember-row label{
  font-size:.76rem;color:var(--mute);cursor:pointer;
}

.btn-login{
  width:100%;background:var(--red);color:#fff;
  border:none;border-radius:var(--r-md);
  padding:12px;font-size:.88rem;font-weight:700;
  cursor:pointer;font-family:inherit;
  transition:background .12s,transform .08s;
  letter-spacing:-.1px;
}
.btn-login:hover{background:var(--red-dark);}
.btn-login:active{transform:scale(.99);}

.alert-error{
  background:#fee2e2;border:1px solid #fca5a5;
  border-radius:var(--r-md);
  color:#9e0a0a;font-size:.78rem;
  padding:.65rem .9rem;margin-bottom:1.25rem;
  display:flex;align-items:center;gap:.5rem;
}

.form-footer{
  margin-top:1.25rem;text-align:center;
  font-size:.72rem;color:var(--ash);
  line-height:1.6;
}

/* Mobile — stack vertically */
@media(max-width:640px){
  .login-wrapper{grid-template-columns:1fr;}
  .panel-left{display:none;}
  .panel-right{padding:2rem 1.5rem;}
}
</style>
</head>
<body>

<div class="login-wrapper">

  {{-- Left: Feature showcase --}}
  <div class="panel-left">
    <div class="brand-mark">
      <div class="brand-icon"><i class="bi bi-bar-chart-fill"></i></div>
      <div>
        <div class="brand-name">DSM Intelligence</div>
        <div class="brand-tagline">Marketing Platform</div>
      </div>
    </div>

    <div class="panel-headline">Data-driven decisions<br>for <span>DSM brands</span></div>
    <p class="panel-sub">Platform analitik terpadu untuk memantau performa seluruh channel penjualan secara real-time.</p>

    <ul class="feature-list">
      <li class="feature-item">
        <div class="feature-icon"><i class="bi bi-bar-chart-line"></i></div>
        <div class="feature-text">
          <strong>Daily Sales & ROAS</strong>
          <span>Monitor GMV, order, dan efisiensi iklan harian per toko dan brand</span>
        </div>
      </li>
      <li class="feature-item">
        <div class="feature-icon"><i class="bi bi-calculator"></i></div>
        <div class="feature-text">
          <strong>P&L Otomatis</strong>
          <span>Laporan laba rugi dihitung real-time dari data order, HPP, dan biaya iklan</span>
        </div>
      </li>
      <li class="feature-item">
        <div class="feature-icon"><i class="bi bi-box-seam"></i></div>
        <div class="feature-text">
          <strong>Analisis Produk</strong>
          <span>Top SKU by GMV, margin per produk, cancel rate, dan tren 3 bulan</span>
        </div>
      </li>
      <li class="feature-item">
        <div class="feature-icon"><i class="bi bi-magic"></i></div>
        <div class="feature-text">
          <strong>Forecasting Produk</strong>
          <span>Otomatis breakdown target unit per SKU berdasarkan histori penjualan</span>
        </div>
      </li>
      <li class="feature-item">
        <div class="feature-icon"><i class="bi bi-people"></i></div>
        <div class="feature-text">
          <strong>Customer Retention</strong>
          <span>Segmentasi pelanggan baru vs returning dengan tren 6 bulan</span>
        </div>
      </li>
    </ul>

    <div class="panel-brands">
      <div class="brand-pill">DTHREE</div>
      <div class="brand-pill">HURIM</div>
      <div class="brand-pill">ASFARA</div>
    </div>
  </div>

  {{-- Right: Login form --}}
  <div class="panel-right">
    <div class="form-heading">Selamat datang</div>
    <div class="form-subheading">Masuk ke akun DSM Intelligence Anda</div>

    @if($errors->any())
    <div class="alert-error"><i class="bi bi-exclamation-circle"></i>{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="/login">
      @csrf
      <div class="field">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="{{ old('email') }}"
          placeholder="nama@perusahaan.com" required autofocus>
      </div>
      <div class="field">
        <label for="password">Password</label>
        <input type="password" id="password" name="password"
          placeholder="••••••••" required>
      </div>
      <div class="remember-row">
        <input type="checkbox" id="remember" name="remember">
        <label for="remember">Ingat saya selama 30 hari</label>
      </div>
      <button type="submit" class="btn-login">Masuk</button>
    </form>

    <div class="form-footer">
      Lupa password? Hubungi admin sistem.<br>
      &copy; {{ date('Y') }} DSM — All rights reserved.
    </div>
  </div>

</div>

</body>
</html>
