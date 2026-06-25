<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>@yield('title','DSM Marketing Intelligence')</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
:root{--sidebar-w:240px;}
body{background:#f5f6fa;font-family:'Segoe UI',sans-serif;}
.sidebar{position:fixed;top:0;left:0;height:100vh;width:var(--sidebar-w);background:#1a1d2e;overflow-y:auto;z-index:1000;display:flex;flex-direction:column;}
.sidebar .brand{padding:1.25rem 1.5rem;color:#fff;font-weight:700;font-size:1.05rem;border-bottom:1px solid rgba(255,255,255,.08);letter-spacing:.3px;}
.sidebar .brand small{display:block;font-size:.65rem;font-weight:400;color:rgba(255,255,255,.4);margin-top:1px;}
.sidebar .nav-label{padding:.75rem 1.5rem .25rem;font-size:.65rem;font-weight:600;text-transform:uppercase;letter-spacing:1px;color:rgba(255,255,255,.3);}
.sidebar .nav-link{display:flex;align-items:center;gap:.6rem;padding:.5rem 1.5rem;color:rgba(255,255,255,.65);font-size:.82rem;border-left:3px solid transparent;transition:all .15s;}
.sidebar .nav-link:hover,.sidebar .nav-link.active{color:#fff;background:rgba(255,255,255,.07);border-left-color:#7c6ff7;}
.sidebar .nav-link i{width:18px;text-align:center;font-size:1rem;}
.topbar{position:fixed;top:0;left:var(--sidebar-w);right:0;height:56px;background:#fff;border-bottom:1px solid #e5e7eb;display:flex;align-items:center;padding:0 1.5rem;z-index:999;gap:1rem;}
.page-content{margin-left:var(--sidebar-w);margin-top:56px;padding:1.75rem 2rem;min-height:calc(100vh - 56px);}
.card{border:none;box-shadow:0 1px 3px rgba(0,0,0,.07);border-radius:.6rem;}
.card-header{background:#fff;border-bottom:1px solid #f0f0f0;font-weight:600;font-size:.85rem;}
.stat-card{background:#fff;border-radius:.6rem;padding:1.25rem 1.5rem;box-shadow:0 1px 3px rgba(0,0,0,.07);}
.stat-card .label{font-size:.72rem;color:#6b7280;text-transform:uppercase;letter-spacing:.5px;font-weight:600;}
.stat-card .value{font-size:1.6rem;font-weight:700;color:#111827;margin:.15rem 0;}
.stat-card .delta{font-size:.75rem;}
.badge-brand-DTHREE{background:#e0e7ff;color:#3730a3;}
.badge-brand-HURIM{background:#fce7f3;color:#9d174d;}
.badge-brand-ASFARA{background:#d1fae5;color:#065f46;}
@media(max-width:991px){
  .sidebar{transform:translateX(-100%);transition:transform .25s ease;}
  .sidebar.open{transform:translateX(0);}
  .sidebar-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.4);z-index:999;}
  .sidebar-overlay.show{display:block;}
  .page-content{margin-left:0;}
  .topbar{left:0;}
  .topbar-toggle{display:flex!important;}
}
@media(min-width:992px){.topbar-toggle{display:none!important;}}
</style>
@stack('styles')
</head>
<body>
<div class="sidebar">
  <div class="brand">DSM Intelligence<small>Marketing Platform</small></div>
  <nav class="py-2 flex-grow-1">
    <div class="nav-label">Analitik</div>
    <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"><i class="bi bi-grid-1x2"></i> Overview</a>
    <a href="{{ route('daily-sales') }}" class="nav-link {{ request()->routeIs('daily-sales') ? 'active' : '' }}"><i class="bi bi-bar-chart"></i> Daily Sales</a>
    <a href="{{ route('roas') }}" class="nav-link {{ request()->routeIs('roas') ? 'active' : '' }}"><i class="bi bi-graph-up-arrow"></i> ROAS & Iklan</a>
    <a href="{{ route('funnel') }}" class="nav-link {{ request()->routeIs('funnel') ? 'active' : '' }}"><i class="bi bi-funnel"></i> Funnel</a>
    <a href="{{ route('pnl') }}" class="nav-link {{ request()->routeIs('pnl') ? 'active' : '' }}"><i class="bi bi-calculator"></i> P&L</a>
    <a href="{{ route('customers') }}" class="nav-link {{ request()->routeIs('customers') ? 'active' : '' }}"><i class="bi bi-people"></i> Customer</a>
    <a href="{{ route('store-compare') }}" class="nav-link {{ request()->routeIs('store-compare') ? 'active' : '' }}"><i class="bi bi-columns-gap"></i> Perbandingan Toko</a>
    <a href="{{ route('product-analysis') }}" class="nav-link {{ request()->routeIs('product-analysis') ? 'active' : '' }}"><i class="bi bi-box-seam"></i> Analisis Produk</a>
    <div class="nav-label mt-2">Upload</div>
    <a href="{{ route('upload.index') }}" class="nav-link {{ request()->routeIs('upload.*') ? 'active' : '' }}"><i class="bi bi-cloud-upload"></i> Upload Data</a>
    @if(auth()->user()->isAdmin())
    <div class="nav-label mt-2">Master Data</div>
    <a href="{{ route('stores.index') }}" class="nav-link {{ request()->routeIs('stores.*') ? 'active' : '' }}"><i class="bi bi-shop"></i> Toko</a>
    <a href="{{ route('pics.index') }}" class="nav-link {{ request()->routeIs('pics.*') ? 'active' : '' }}"><i class="bi bi-person-badge"></i> PIC</a>
    <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}"><i class="bi bi-shield-person"></i> Users</a>
    <a href="{{ route('targets.index') }}" class="nav-link {{ request()->routeIs('targets.*') ? 'active' : '' }}"><i class="bi bi-bullseye"></i> Target GMV</a>
    <a href="{{ route('cogs.index') }}" class="nav-link {{ request()->routeIs('cogs.*') ? 'active' : '' }}"><i class="bi bi-tag"></i> HPP / COG</a>
    <a href="{{ route('funnel-targets.index') }}" class="nav-link {{ request()->routeIs('funnel-targets.*') ? 'active' : '' }}"><i class="bi bi-sliders"></i> Target Funnel</a>
    @endif
  </nav>
  <div class="p-3 border-top" style="border-color:rgba(255,255,255,.08)!important;">
    <a href="{{ route('settings') }}" class="nav-link"><i class="bi bi-gear"></i> Pengaturan</a>
  </div>
</div>
<div class="sidebar-overlay" id="sidebarOverlay"></div>
<div class="topbar">
  <button class="btn btn-sm btn-light topbar-toggle me-2" id="sidebarToggle" style="display:none">
    <i class="bi bi-list fs-5"></i>
  </button>
  <div class="fw-semibold text-dark fs-6 flex-grow-1">@yield('page-title','')</div>
  @php $alerts = \App\Http\Controllers\NotificationController::getTargetAlerts(); @endphp
  @if(count($alerts) > 0)
  <div class="dropdown me-2">
    <button class="btn btn-sm btn-warning position-relative" data-bs-toggle="dropdown">
      <i class="bi bi-bell-fill"></i>
      <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:.6rem">
        {{ count($alerts) }}
      </span>
    </button>
    <ul class="dropdown-menu dropdown-menu-end" style="min-width:300px;max-height:400px;overflow-y:auto">
      <li><h6 class="dropdown-header text-danger"><i class="bi bi-exclamation-triangle me-1"></i>Target Berisiko ({{ now()->format('M Y') }})</h6></li>
      @foreach($alerts as $a)
      <li>
        <div class="dropdown-item-text py-2 px-3 border-bottom">
          <div class="fw-semibold" style="font-size:.82rem">{{ $a['store'] }}</div>
          <div class="d-flex justify-content-between mt-1">
            <span class="badge badge-brand-{{ $a['brand'] }}">{{ $a['brand'] }}</span>
            <small class="text-danger fw-semibold">{{ $a['actual_pct'] }}% <span class="text-muted fw-normal">/ exp. {{ $a['expected_pct'] }}%</span></small>
          </div>
          <div class="progress mt-1" style="height:4px">
            <div class="progress-bar bg-danger" style="width:{{ $a['actual_pct'] }}%"></div>
          </div>
        </div>
      </li>
      @endforeach
    </ul>
  </div>
  @endif
  <div class="dropdown">
    <button class="btn btn-sm btn-light d-flex align-items-center gap-2" data-bs-toggle="dropdown">
      <i class="bi bi-person-circle fs-5"></i>
      <span class="d-none d-md-inline" style="font-size:.82rem;">{{ auth()->user()->name }}</span>
      <span class="badge bg-secondary" style="font-size:.6rem;">{{ auth()->user()->role }}</span>
    </button>
    <ul class="dropdown-menu dropdown-menu-end">
      <li><a class="dropdown-item" href="{{ route('settings') }}"><i class="bi bi-gear me-2"></i>Pengaturan</a></li>
      <li><hr class="dropdown-divider"></li>
      <li>
        <form method="POST" action="{{ route('logout') }}">
          @csrf
          <button class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2"></i>Logout</button>
        </form>
      </li>
    </ul>
  </div>
</div>
<div class="page-content">
  @if(session('success'))
  <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 mb-3" role="alert">
    <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
  </div>
  @endif
  @if(session('error'))
  <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 mb-3" role="alert">
    <i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}
    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
  </div>
  @endif
  @yield('content')
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el=>new bootstrap.Tooltip(el));
// Mobile sidebar toggle
const sidebar = document.querySelector('.sidebar');
const overlay = document.getElementById('sidebarOverlay');
const toggler = document.getElementById('sidebarToggle');
if (toggler) {
  toggler.addEventListener('click', () => { sidebar.classList.toggle('open'); overlay.classList.toggle('show'); });
  overlay.addEventListener('click', () => { sidebar.classList.remove('open'); overlay.classList.remove('show'); });
}
</script>
@stack('scripts')
</body>
</html>
