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
  --ink:#000;--body:#33332e;--mute:#62625b;--ash:#91918c;
  --canvas:#fff;--surface:#fbfbf9;--card:#f6f6f3;
  --hairline:#dadad3;--r-md:16px;--r-lg:28px;
}

/* ── Animated background ── */
body{
  min-height:100vh;
  font-family:'Inter',-apple-system,system-ui,sans-serif;
  display:flex;align-items:center;justify-content:center;
  padding:1.5rem;
  background:#0a0a0a;
  overflow:hidden;
  position:relative;
}

/* Mesh gradient base */
body::before{
  content:'';
  position:fixed;inset:0;
  background:
    radial-gradient(ellipse 80% 60% at 20% 10%, rgba(230,0,35,.22) 0%, transparent 60%),
    radial-gradient(ellipse 60% 50% at 80% 90%, rgba(67,94,229,.18) 0%, transparent 55%),
    radial-gradient(ellipse 50% 40% at 50% 50%, rgba(91,33,182,.1) 0%, transparent 60%),
    #0d0d0d;
  animation:meshShift 12s ease-in-out infinite alternate;
  z-index:0;
}
@keyframes meshShift{
  0%  {background-position:0% 0%;}
  50% {background-position:60% 40%;}
  100%{background-position:20% 80%;}
}

/* Floating orbs */
.orb{
  position:fixed;border-radius:50%;
  filter:blur(80px);pointer-events:none;z-index:0;
  animation:float linear infinite;
}
.orb-1{
  width:500px;height:500px;
  background:radial-gradient(circle,rgba(230,0,35,.35),transparent 70%);
  top:-120px;left:-100px;
  animation-duration:18s;animation-name:floatA;
}
.orb-2{
  width:400px;height:400px;
  background:radial-gradient(circle,rgba(67,94,229,.28),transparent 70%);
  bottom:-80px;right:-60px;
  animation-duration:22s;animation-name:floatB;
}
.orb-3{
  width:300px;height:300px;
  background:radial-gradient(circle,rgba(91,33,182,.2),transparent 70%);
  top:50%;left:60%;
  animation-duration:15s;animation-name:floatC;
}
@keyframes floatA{
  0%  {transform:translate(0,0) scale(1);}
  33% {transform:translate(60px,40px) scale(1.08);}
  66% {transform:translate(-30px,70px) scale(.95);}
  100%{transform:translate(0,0) scale(1);}
}
@keyframes floatB{
  0%  {transform:translate(0,0) scale(1);}
  50% {transform:translate(-50px,-60px) scale(1.1);}
  100%{transform:translate(0,0) scale(1);}
}
@keyframes floatC{
  0%  {transform:translate(0,0) scale(.9);}
  40% {transform:translate(-80px,30px) scale(1.1);}
  80% {transform:translate(40px,-50px) scale(.95);}
  100%{transform:translate(0,0) scale(.9);}
}

/* Moving grid */
.grid-bg{
  position:fixed;inset:0;z-index:0;
  background-image:
    linear-gradient(rgba(255,255,255,.03) 1px, transparent 1px),
    linear-gradient(90deg, rgba(255,255,255,.03) 1px, transparent 1px);
  background-size:40px 40px;
  animation:gridDrift 30s linear infinite;
}
@keyframes gridDrift{
  from{background-position:0 0;}
  to  {background-position:40px 40px;}
}

/* Floating particles */
.particles{position:fixed;inset:0;z-index:0;pointer-events:none;overflow:hidden;}
.particle{
  position:absolute;
  width:2px;height:2px;
  border-radius:50%;
  background:rgba(255,255,255,.4);
  animation:rise linear infinite;
}
@keyframes rise{
  0%  {opacity:0;transform:translateY(0) scale(0);}
  10% {opacity:1;}
  90% {opacity:.6;}
  100%{opacity:0;transform:translateY(-100vh) scale(1.5);}
}

/* ── Card wrapper ── */
.login-wrapper{
  position:relative;z-index:10;
  width:100%;max-width:940px;
  display:grid;grid-template-columns:1fr 1fr;
  background:rgba(255,255,255,.04);
  backdrop-filter:blur(2px);
  border-radius:var(--r-lg);
  border:1px solid rgba(255,255,255,.08);
  box-shadow:0 32px 100px rgba(0,0,0,.6),0 0 0 1px rgba(255,255,255,.04);
  overflow:hidden;
  min-height:560px;
  animation:cardIn .5s cubic-bezier(.16,1,.3,1) both;
}
@keyframes cardIn{
  from{opacity:0;transform:translateY(24px) scale(.97);}
  to  {opacity:1;transform:translateY(0) scale(1);}
}

/* ── Left panel ── */
.panel-left{
  padding:2.5rem;
  display:flex;flex-direction:column;
  background:rgba(0,0,0,.25);
  border-right:1px solid rgba(255,255,255,.06);
  position:relative;overflow:hidden;
}
/* shine sweep */
.panel-left::after{
  content:'';
  position:absolute;
  top:-50%;left:-60%;
  width:80%;height:200%;
  background:linear-gradient(105deg,transparent 40%,rgba(255,255,255,.04) 50%,transparent 60%);
  animation:shine 6s ease-in-out infinite;
}
@keyframes shine{
  0%,100%{left:-60%;}
  50%{left:120%;}
}

.brand-mark{
  display:flex;align-items:center;gap:.65rem;
  margin-bottom:2.5rem;position:relative;z-index:1;
}
.brand-icon{
  width:38px;height:38px;border-radius:50%;
  background:var(--red);
  display:flex;align-items:center;justify-content:center;
  box-shadow:0 0 20px rgba(230,0,35,.5);
  animation:iconPulse 3s ease-in-out infinite;
}
@keyframes iconPulse{
  0%,100%{box-shadow:0 0 20px rgba(230,0,35,.5);}
  50%    {box-shadow:0 0 35px rgba(230,0,35,.8),0 0 60px rgba(230,0,35,.3);}
}
.brand-icon i{color:#fff;font-size:.95rem;}
.brand-name{font-size:.95rem;font-weight:700;color:#fff;letter-spacing:-.2px;}
.brand-tagline{font-size:.68rem;color:rgba(255,255,255,.4);}

.panel-headline{
  font-size:1.45rem;font-weight:700;
  color:#fff;letter-spacing:-.5px;line-height:1.2;
  margin-bottom:.5rem;position:relative;z-index:1;
}
.panel-headline span{
  color:transparent;
  background:linear-gradient(135deg,#e60023,#ff6b6b);
  -webkit-background-clip:text;background-clip:text;
}
.panel-sub{
  font-size:.78rem;color:rgba(255,255,255,.45);
  line-height:1.6;margin-bottom:2rem;
  position:relative;z-index:1;
}

.feature-list{
  list-style:none;display:flex;flex-direction:column;gap:.7rem;
  position:relative;z-index:1;flex-grow:1;
}
.feature-item{
  display:flex;align-items:flex-start;gap:.7rem;
  animation:fadeSlide .4s ease both;
}
.feature-item:nth-child(1){animation-delay:.1s;}
.feature-item:nth-child(2){animation-delay:.18s;}
.feature-item:nth-child(3){animation-delay:.26s;}
.feature-item:nth-child(4){animation-delay:.34s;}
.feature-item:nth-child(5){animation-delay:.42s;}
@keyframes fadeSlide{
  from{opacity:0;transform:translateX(-12px);}
  to  {opacity:1;transform:translateX(0);}
}
.feature-icon{
  width:30px;height:30px;border-radius:9px;
  background:rgba(255,255,255,.07);
  border:1px solid rgba(255,255,255,.1);
  display:flex;align-items:center;justify-content:center;
  flex-shrink:0;margin-top:1px;
  transition:background .2s,border-color .2s;
}
.feature-item:hover .feature-icon{
  background:rgba(230,0,35,.2);
  border-color:rgba(230,0,35,.4);
}
.feature-icon i{font-size:.8rem;color:rgba(255,255,255,.65);}
.feature-text strong{display:block;font-size:.78rem;font-weight:600;color:#fff;margin-bottom:1px;}
.feature-text span{font-size:.7rem;color:rgba(255,255,255,.38);line-height:1.4;}

.panel-brands{
  display:flex;gap:.45rem;margin-top:auto;padding-top:1.5rem;
  position:relative;z-index:1;flex-wrap:wrap;
}
.brand-pill{
  background:rgba(255,255,255,.06);
  border:1px solid rgba(255,255,255,.1);
  border-radius:9999px;padding:3px 10px;
  font-size:.63rem;font-weight:600;
  color:rgba(255,255,255,.45);letter-spacing:.4px;
  transition:background .15s,color .15s;
}
.brand-pill:hover{background:rgba(230,0,35,.2);color:rgba(255,255,255,.8);}

/* ── Right panel (form) ── */
.panel-right{
  padding:2.5rem;
  display:flex;flex-direction:column;justify-content:center;
  background:rgba(255,255,255,.97);
}

.form-heading{
  font-size:1.1rem;font-weight:700;
  color:var(--ink);letter-spacing:-.3px;margin-bottom:.2rem;
}
.form-subheading{font-size:.76rem;color:var(--ash);margin-bottom:2rem;}

.field{margin-bottom:1rem;}
.field label{
  display:block;font-size:.78rem;font-weight:600;
  color:var(--body);margin-bottom:.4rem;
}
.field input{
  width:100%;border:1px solid var(--hairline);
  border-radius:var(--r-md);
  font-size:.85rem;padding:10px 14px;
  color:var(--ink);background:var(--canvas);
  outline:none;font-family:inherit;
  transition:border-color .15s,box-shadow .15s;
}
.field input:hover:not(:focus){border-color:var(--ash);}
.field input:focus{
  border-color:var(--ink);
  box-shadow:0 0 0 3px rgba(67,94,229,.15);
}
.field input::placeholder{color:var(--ash);}

.remember-row{
  display:flex;align-items:center;gap:.5rem;margin-bottom:1.5rem;
}
.remember-row input[type=checkbox]{accent-color:var(--red);width:15px;height:15px;cursor:pointer;}
.remember-row label{font-size:.75rem;color:var(--mute);cursor:pointer;}

.btn-login{
  width:100%;background:var(--red);color:#fff;
  border:none;border-radius:var(--r-md);
  padding:12px;font-size:.88rem;font-weight:700;
  cursor:pointer;font-family:inherit;
  position:relative;overflow:hidden;
  transition:background .15s,box-shadow .15s,transform .1s;
}
.btn-login::after{
  content:'';
  position:absolute;inset:0;
  background:linear-gradient(135deg,rgba(255,255,255,.15),transparent);
  pointer-events:none;
}
.btn-login:hover{
  background:var(--red-dark);
  box-shadow:0 6px 20px rgba(230,0,35,.4);
  transform:translateY(-1px);
}
.btn-login:active{transform:scale(.98);box-shadow:none;}

.alert-error{
  background:#fee2e2;border:1px solid #fca5a5;
  border-radius:var(--r-md);
  color:#9e0a0a;font-size:.78rem;
  padding:.65rem .9rem;margin-bottom:1.25rem;
  display:flex;align-items:center;gap:.5rem;
}

.form-footer{
  margin-top:1.25rem;text-align:center;
  font-size:.7rem;color:var(--ash);line-height:1.7;
}

/* Mobile */
@media(max-width:640px){
  .login-wrapper{grid-template-columns:1fr;min-height:auto;}
  .panel-left{display:none;}
  .panel-right{padding:2rem 1.5rem;background:rgba(255,255,255,.98);}
}
</style>
</head>
<body>

<!-- Animated background layers -->
<div class="orb orb-1"></div>
<div class="orb orb-2"></div>
<div class="orb orb-3"></div>
<div class="grid-bg"></div>
<div class="particles" id="particles"></div>

<div class="login-wrapper">

  <!-- Left panel -->
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
          <span>Monitor GMV, order, dan efisiensi iklan harian per toko</span>
        </div>
      </li>
      <li class="feature-item">
        <div class="feature-icon"><i class="bi bi-calculator"></i></div>
        <div class="feature-text">
          <strong>P&L Otomatis</strong>
          <span>Laporan laba rugi real-time dari order, HPP, dan biaya iklan</span>
        </div>
      </li>
      <li class="feature-item">
        <div class="feature-icon"><i class="bi bi-box-seam"></i></div>
        <div class="feature-text">
          <strong>Analisis Produk</strong>
          <span>Top SKU by GMV, margin, cancel rate, dan tren 3 bulan</span>
        </div>
      </li>
      <li class="feature-item">
        <div class="feature-icon"><i class="bi bi-magic"></i></div>
        <div class="feature-text">
          <strong>Forecasting Produk</strong>
          <span>Breakdown target unit per SKU dari histori penjualan</span>
        </div>
      </li>
      <li class="feature-item">
        <div class="feature-icon"><i class="bi bi-people"></i></div>
        <div class="feature-text">
          <strong>Customer Retention</strong>
          <span>Segmentasi pelanggan baru vs returning + tren 6 bulan</span>
        </div>
      </li>
    </ul>

    <div class="panel-brands">
      <div class="brand-pill">DTHREE</div>
      <div class="brand-pill">HURIM</div>
      <div class="brand-pill">ASFARA</div>
    </div>
  </div>

  <!-- Right panel (form) -->
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

<script>
// Generate floating particles
(function(){
  const container = document.getElementById('particles');
  const count = 28;
  for(let i = 0; i < count; i++){
    const p = document.createElement('div');
    p.className = 'particle';
    const size  = Math.random() * 2.5 + 1;
    const left  = Math.random() * 100;
    const delay = Math.random() * 14;
    const dur   = Math.random() * 12 + 10;
    const opacity = Math.random() * 0.4 + 0.1;
    p.style.cssText = `
      width:${size}px;height:${size}px;
      left:${left}%;bottom:${Math.random()*20}%;
      opacity:${opacity};
      animation-duration:${dur}s;
      animation-delay:-${delay}s;
    `;
    container.appendChild(p);
  }
})();
</script>
</body>
</html>
