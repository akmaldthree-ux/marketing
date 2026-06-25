<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>@yield('title','DSM Marketing Intelligence')</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
/* ── Design Tokens ─────────────────────────────────────────────────── */
:root{
  --color-primary:        #e60023;
  --color-primary-pressed:#cc001f;
  --color-canvas:         #ffffff;
  --color-surface-soft:   #fbfbf9;
  --color-surface-card:   #f6f6f3;
  --color-secondary-bg:   #e5e5e0;
  --color-secondary-pressed:#c8c8c1;
  --color-surface-dark:   #262622;
  --color-hairline:       #dadad3;
  --color-hairline-soft:  #e5e5e0;
  --color-ink:            #000000;
  --color-ink-soft:       #211922;
  --color-body:           #33332e;
  --color-charcoal:       #262622;
  --color-mute:           #62625b;
  --color-ash:            #91918c;
  --color-stone:          #c8c8c1;
  --color-on-dark:        #ffffff;
  --color-error:          #9e0a0a;
  --color-focus-outer:    #435ee5;
  --rounded-md:           16px;
  --rounded-lg:           32px;
  --rounded-full:         9999px;
  --sidebar-w:            248px;
  --topbar-h:             60px;
  --spacing-sm:           8px;
  --spacing-md:           12px;
  --spacing-lg:           16px;
  --spacing-xl:           24px;
  --spacing-xxl:          32px;
  --spacing-section:      64px;
}

/* ── Base ──────────────────────────────────────────────────────────── */
*,*::before,*::after{box-sizing:border-box;}
body{
  background:var(--color-surface-soft);
  font-family:'Inter',-apple-system,system-ui,'Segoe UI',Roboto,'Helvetica Neue',Arial,sans-serif;
  font-size:14px;
  color:var(--color-body);
  line-height:1.4;
  margin:0;
}

/* ── Sidebar ───────────────────────────────────────────────────────── */
.sidebar{
  position:fixed;top:0;left:0;height:100vh;
  width:var(--sidebar-w);
  background:var(--color-canvas);
  border-right:1px solid var(--color-hairline);
  overflow-y:auto;z-index:1000;
  display:flex;flex-direction:column;
}
.sidebar .brand{
  padding:1.25rem 1.5rem;
  display:flex;align-items:center;gap:.6rem;
  border-bottom:1px solid var(--color-hairline);
}
.sidebar .brand .brand-wordmark{
  font-size:.95rem;font-weight:700;
  color:var(--color-ink);letter-spacing:-.3px;
}
.sidebar .brand .brand-sub{
  font-size:.67rem;font-weight:400;
  color:var(--color-ash);display:block;margin-top:1px;
}
.sidebar .brand .brand-dot{
  width:28px;height:28px;border-radius:var(--rounded-full);
  background:var(--color-primary);
  display:flex;align-items:center;justify-content:center;
  flex-shrink:0;
}
.sidebar .brand .brand-dot i{color:#fff;font-size:13px;}

.sidebar .nav-label{
  padding:.9rem 1.5rem .3rem;
  font-size:.65rem;font-weight:600;
  text-transform:uppercase;letter-spacing:1px;
  color:var(--color-ash);
}
.sidebar .nav-link{
  display:flex;align-items:center;gap:.55rem;
  padding:.48rem 1.25rem .48rem 1.5rem;
  color:var(--color-mute);
  font-size:.82rem;font-weight:500;
  border-left:3px solid transparent;
  transition:color .12s,background .12s,border-color .12s;
  text-decoration:none;
  border-radius:0;
}
.sidebar .nav-link:hover{
  color:var(--color-ink);
  background:var(--color-surface-card);
  border-left-color:var(--color-stone);
}
.sidebar .nav-link.active{
  color:var(--color-primary);
  background:var(--color-surface-card);
  border-left-color:var(--color-primary);
  font-weight:600;
}
.sidebar .nav-link i{width:16px;text-align:center;font-size:.95rem;flex-shrink:0;}
.sidebar .sidebar-footer{
  padding:.75rem 1rem;
  border-top:1px solid var(--color-hairline);
}

/* ── Topbar ────────────────────────────────────────────────────────── */
.topbar{
  position:fixed;top:0;left:var(--sidebar-w);right:0;
  height:var(--topbar-h);
  background:var(--color-canvas);
  border-bottom:1px solid var(--color-hairline);
  display:flex;align-items:center;
  padding:0 1.5rem;z-index:999;gap:1rem;
}
.topbar .page-title{
  font-size:.9rem;font-weight:600;
  color:var(--color-ink);letter-spacing:-.2px;
  flex-grow:1;
}

/* ── Page Content ──────────────────────────────────────────────────── */
.page-content{
  margin-left:var(--sidebar-w);
  margin-top:var(--topbar-h);
  padding:1.5rem 2rem;
  min-height:calc(100vh - var(--topbar-h));
}

/* ── Cards ─────────────────────────────────────────────────────────── */
.card{
  border:1px solid var(--color-hairline);
  border-radius:var(--rounded-md);
  box-shadow:none;
  background:var(--color-canvas);
}
.card-header{
  background:var(--color-canvas);
  border-bottom:1px solid var(--color-hairline);
  font-weight:600;font-size:.82rem;
  color:var(--color-ink);
  border-radius:var(--rounded-md) var(--rounded-md) 0 0 !important;
  padding:.75rem 1rem;
}
.card-body{padding:1.25rem;}

/* ── Stat Cards ─────────────────────────────────────────────────────── */
.stat-card{
  background:var(--color-canvas);
  border:1px solid var(--color-hairline);
  border-radius:var(--rounded-md);
  padding:1.25rem 1.5rem;
}
.stat-card .label{
  font-size:.68rem;color:var(--color-ash);
  text-transform:uppercase;letter-spacing:.6px;font-weight:600;
}
.stat-card .value{
  font-size:1.55rem;font-weight:700;
  color:var(--color-ink);margin:.2rem 0;
  letter-spacing:-.5px;
}
.stat-card .delta{font-size:.75rem;font-weight:500;}

/* ── Buttons ────────────────────────────────────────────────────────── */
.btn-primary,.btn-primary:focus{
  background:var(--color-primary);
  border-color:var(--color-primary);
  color:#fff;
  font-weight:700;font-size:.8rem;
  border-radius:var(--rounded-md);
  padding:6px 16px;
}
.btn-primary:hover{background:var(--color-primary-pressed);border-color:var(--color-primary-pressed);}
.btn-primary:active{background:var(--color-primary-pressed);}

.btn-secondary,.btn-secondary:focus{
  background:var(--color-secondary-bg);
  border-color:var(--color-secondary-bg);
  color:var(--color-ink);
  font-weight:700;font-size:.8rem;
  border-radius:var(--rounded-md);
  padding:6px 16px;
}
.btn-secondary:hover{background:var(--color-secondary-pressed);border-color:var(--color-secondary-pressed);color:var(--color-ink);}

.btn-outline-primary{
  border-color:var(--color-primary);color:var(--color-primary);
  font-weight:600;font-size:.8rem;
  border-radius:var(--rounded-md);padding:5px 14px;
}
.btn-outline-primary:hover{background:var(--color-primary);color:#fff;}

.btn-outline-secondary{
  border-color:var(--color-hairline);color:var(--color-mute);
  font-weight:600;font-size:.8rem;
  border-radius:var(--rounded-md);padding:5px 14px;
}
.btn-outline-secondary:hover{background:var(--color-surface-card);color:var(--color-ink);border-color:var(--color-stone);}

.btn-outline-danger{
  border-color:var(--color-hairline);color:var(--color-error);
  font-size:.8rem;border-radius:var(--rounded-md);padding:5px 12px;
}
.btn-outline-danger:hover{background:var(--color-error);color:#fff;border-color:var(--color-error);}

.btn-outline-success{
  border-color:var(--color-hairline);color:#103c25;
  font-weight:600;font-size:.8rem;
  border-radius:var(--rounded-md);padding:5px 14px;
}
.btn-outline-success:hover{background:#103c25;color:#fff;}

.btn-sm{padding:4px 12px;font-size:.76rem;}

/* ── Inputs & Forms ─────────────────────────────────────────────────── */
.form-control,.form-select{
  border:1px solid var(--color-ash);
  border-radius:var(--rounded-md);
  font-size:.82rem;padding:9px 13px;
  color:var(--color-ink);
  background:var(--color-canvas);
  box-shadow:none;
}
.form-control:focus,.form-select:focus{
  border-color:var(--color-ink);
  box-shadow:0 0 0 3px rgba(67,94,229,.18);
  outline:none;
}
.form-control::placeholder{color:var(--color-ash);}
.form-control-sm,.form-select-sm{padding:6px 11px;font-size:.78rem;}
.form-label{font-size:.8rem;font-weight:600;color:var(--color-charcoal);margin-bottom:.3rem;}
.form-text{font-size:.73rem;color:var(--color-ash);}
.input-group .form-control{border-radius:var(--rounded-md);}

/* ── Tables ─────────────────────────────────────────────────────────── */
.table{font-size:.8rem;color:var(--color-body);}
.table th{
  font-size:.7rem;font-weight:600;
  text-transform:uppercase;letter-spacing:.5px;
  color:var(--color-ash);border-bottom:1px solid var(--color-hairline);
}
.table-light{background:var(--color-surface-card);}
.table-light th{background:var(--color-surface-card);}
.table>:not(caption)>*>*{border-color:var(--color-hairline-soft);}
.table-hover tbody tr:hover td{background:var(--color-surface-soft);}

/* ── Badges ─────────────────────────────────────────────────────────── */
.badge{border-radius:var(--rounded-full);font-weight:600;font-size:.65rem;padding:3px 9px;letter-spacing:.2px;}
.badge-brand-DTHREE{background:#ede9fe;color:#5b21b6;}
.badge-brand-HURIM{background:#fce7f3;color:#9d174d;}
.badge-brand-ASFARA{background:#d1fae5;color:#065f46;}

/* ── Modals ─────────────────────────────────────────────────────────── */
.modal-content{
  border:none;
  border-radius:var(--rounded-lg);
  box-shadow:0 16px 64px rgba(0,0,0,.12);
}
.modal-header{
  border-bottom:1px solid var(--color-hairline);
  padding:1.25rem 1.5rem;border-radius:var(--rounded-lg) var(--rounded-lg) 0 0;
}
.modal-title{font-size:.9rem;font-weight:700;color:var(--color-ink);}
.modal-body{padding:1.25rem 1.5rem;}
.modal-footer{
  border-top:1px solid var(--color-hairline);
  padding:.75rem 1.5rem;border-radius:0 0 var(--rounded-lg) var(--rounded-lg);
}
.modal-backdrop.show{opacity:.45;}

/* ── Progress ───────────────────────────────────────────────────────── */
.progress{border-radius:var(--rounded-full);background:var(--color-surface-card);}
.progress-bar{border-radius:var(--rounded-full);}

/* ── Dropdowns ──────────────────────────────────────────────────────── */
.dropdown-menu{
  border:1px solid var(--color-hairline);
  border-radius:var(--rounded-md);
  box-shadow:0 8px 32px rgba(0,0,0,.09);
  font-size:.82rem;
  color:var(--color-body);
}
.dropdown-item{color:var(--color-body);font-size:.82rem;padding:.45rem .9rem;border-radius:var(--rounded-md);}
.dropdown-item:hover{background:var(--color-surface-card);color:var(--color-ink);}
.dropdown-divider{border-color:var(--color-hairline);}
.dropdown-header{font-size:.7rem;color:var(--color-ash);text-transform:uppercase;letter-spacing:.5px;}

/* ── Alerts ─────────────────────────────────────────────────────────── */
.alert{border-radius:var(--rounded-md);font-size:.82rem;border:1px solid;}
.alert-success{background:#d1fae5;border-color:#a7f3d0;color:#065f46;}
.alert-danger{background:#fee2e2;border-color:#fca5a5;color:var(--color-error);}
.alert-warning{background:#fff7ed;border-color:#fed7aa;color:#92400e;}

/* ── Nav Tabs / Pills ────────────────────────────────────────────────── */
.nav-tabs{border-bottom:1px solid var(--color-hairline);}
.nav-tabs .nav-link{
  color:var(--color-mute);font-size:.82rem;font-weight:500;
  border:none;border-bottom:2px solid transparent;
  padding:.5rem 1rem;border-radius:0;
}
.nav-tabs .nav-link:hover{color:var(--color-ink);border-bottom-color:var(--color-stone);}
.nav-tabs .nav-link.active{color:var(--color-primary);border-bottom-color:var(--color-primary);font-weight:600;}

/* ── Topbar icon buttons ────────────────────────────────────────────── */
.topbar-icon-btn{
  width:36px;height:36px;border-radius:var(--rounded-full);
  background:var(--color-surface-card);
  border:1px solid var(--color-hairline);
  display:flex;align-items:center;justify-content:center;
  cursor:pointer;color:var(--color-ink);transition:background .12s;
  flex-shrink:0;
}
.topbar-icon-btn:hover{background:var(--color-secondary-bg);}

/* ── User chip ──────────────────────────────────────────────────────── */
.user-chip{
  display:flex;align-items:center;gap:.5rem;
  padding:4px 12px 4px 4px;
  border:1px solid var(--color-hairline);
  border-radius:var(--rounded-full);
  cursor:pointer;background:var(--color-canvas);
  font-size:.8rem;font-weight:600;color:var(--color-ink);
  transition:background .12s;
}
.user-chip:hover{background:var(--color-surface-card);}
.user-chip .avatar{
  width:28px;height:28px;border-radius:var(--rounded-full);
  background:var(--color-primary);color:#fff;
  display:flex;align-items:center;justify-content:center;
  font-size:.75rem;font-weight:700;flex-shrink:0;
}

/* ── Filter chips ───────────────────────────────────────────────────── */
.filter-chip{
  display:inline-flex;align-items:center;
  background:var(--color-surface-card);color:var(--color-ink);
  border:1px solid var(--color-hairline);
  border-radius:var(--rounded-full);
  padding:5px 14px;font-size:.76rem;font-weight:600;
  cursor:pointer;transition:background .12s,color .12s;
  text-decoration:none;
}
.filter-chip:hover,.filter-chip.active{
  background:var(--color-ink);color:var(--color-on-dark);
  border-color:var(--color-ink);
}

/* ── Sidebar overlay (mobile) ───────────────────────────────────────── */
.sidebar-overlay{
  display:none;position:fixed;inset:0;
  background:rgba(38,38,34,.45);z-index:999;
}
.sidebar-overlay.show{display:block;}

/* ── Mobile responsive ──────────────────────────────────────────────── */
@media(max-width:991px){
  .sidebar{transform:translateX(-100%);transition:transform .22s ease;border-right:none;}
  .sidebar.open{transform:translateX(0);box-shadow:8px 0 32px rgba(0,0,0,.12);}
  .page-content{margin-left:0;padding:1rem;}
  .topbar{left:0;}
  .topbar-toggle{display:flex!important;}
}
@media(min-width:992px){.topbar-toggle{display:none!important;}}
@media(max-width:576px){
  .page-content{padding:.75rem;}
  .stat-card{padding:1rem 1.1rem;}
}
</style>
@stack('styles')
</head>
<body>

{{-- Sidebar --}}
<div class="sidebar" id="sidebar">
  <div class="brand">
    <div class="brand-dot"><i class="bi bi-bar-chart-fill"></i></div>
    <div>
      <div class="brand-wordmark">DSM Intelligence</div>
      <span class="brand-sub">Marketing Platform</span>
    </div>
  </div>
  <nav class="py-2 flex-grow-1">
    <div class="nav-label">Analitik</div>
    <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"><i class="bi bi-grid-1x2"></i> Overview</a>
    <a href="{{ route('daily-sales') }}" class="nav-link {{ request()->routeIs('daily-sales') ? 'active' : '' }}"><i class="bi bi-bar-chart"></i> Daily Sales</a>
    <a href="{{ route('roas') }}" class="nav-link {{ request()->routeIs('roas') ? 'active' : '' }}"><i class="bi bi-graph-up-arrow"></i> ROAS & Iklan</a>
    <a href="{{ route('funnel') }}" class="nav-link {{ request()->routeIs('funnel') ? 'active' : '' }}"><i class="bi bi-funnel"></i> Funnel</a>
    <a href="{{ route('pnl') }}" class="nav-link {{ request()->routeIs('pnl') ? 'active' : '' }}"><i class="bi bi-calculator"></i> P&amp;L</a>
    <a href="{{ route('customers') }}" class="nav-link {{ request()->routeIs('customers') ? 'active' : '' }}"><i class="bi bi-people"></i> Customer</a>
    <a href="{{ route('store-compare') }}" class="nav-link {{ request()->routeIs('store-compare') ? 'active' : '' }}"><i class="bi bi-columns-gap"></i> Perbandingan Toko</a>
    <a href="{{ route('product-analysis') }}" class="nav-link {{ request()->routeIs('product-analysis') ? 'active' : '' }}"><i class="bi bi-box-seam"></i> Analisis Produk</a>
    <div class="nav-label mt-2">Perencanaan</div>
    <a href="{{ route('forecast') }}" class="nav-link {{ request()->routeIs('forecast') ? 'active' : '' }}"><i class="bi bi-magic"></i> Forecasting Produk</a>
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
    <a href="{{ route('products.index') }}" class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}"><i class="bi bi-collection"></i> Master Produk</a>
    @endif
  </nav>
  <div class="sidebar-footer">
    <a href="{{ route('settings') }}" class="nav-link {{ request()->routeIs('settings') ? 'active' : '' }}"><i class="bi bi-gear"></i> Pengaturan</a>
  </div>
</div>

<div class="sidebar-overlay" id="sidebarOverlay"></div>

{{-- Topbar --}}
<div class="topbar">
  <button class="topbar-icon-btn topbar-toggle me-1" id="sidebarToggle" style="display:none" aria-label="Menu">
    <i class="bi bi-list" style="font-size:1.1rem"></i>
  </button>
  <div class="page-title">@yield('page-title','')</div>

  {{-- Notifications --}}
  @php $alerts = \App\Http\Controllers\NotificationController::getTargetAlerts(); @endphp
  @if(count($alerts) > 0)
  <div class="dropdown">
    <button class="topbar-icon-btn position-relative" data-bs-toggle="dropdown" aria-label="Notifikasi">
      <i class="bi bi-bell" style="font-size:.95rem"></i>
      <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill" style="background:var(--color-primary);font-size:.55rem;padding:2px 5px">
        {{ count($alerts) }}
      </span>
    </button>
    <ul class="dropdown-menu dropdown-menu-end" style="min-width:300px;max-height:420px;overflow-y:auto">
      <li><div class="px-3 py-2 border-bottom" style="border-color:var(--color-hairline)!important">
        <span style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--color-ash)">Target Berisiko</span>
        <span class="ms-1" style="font-size:.72rem;color:var(--color-mute)">{{ now()->format('M Y') }}</span>
      </div></li>
      @foreach($alerts as $a)
      <li>
        <div class="px-3 py-2" style="border-bottom:1px solid var(--color-hairline-soft)">
          <div style="font-size:.8rem;font-weight:600;color:var(--color-ink)">{{ $a['store'] }}</div>
          <div class="d-flex justify-content-between align-items-center mt-1">
            <span class="badge badge-brand-{{ $a['brand'] }}">{{ $a['brand'] }}</span>
            <small style="color:var(--color-error);font-weight:600;font-size:.75rem">{{ $a['actual_pct'] }}% <span style="color:var(--color-ash);font-weight:400">/ exp. {{ $a['expected_pct'] }}%</span></small>
          </div>
          <div class="progress mt-1" style="height:3px">
            <div class="progress-bar" style="width:{{ $a['actual_pct'] }}%;background:var(--color-primary)"></div>
          </div>
        </div>
      </li>
      @endforeach
    </ul>
  </div>
  @endif

  {{-- User menu --}}
  <div class="dropdown">
    <div class="user-chip" data-bs-toggle="dropdown" role="button">
      <div class="avatar">{{ strtoupper(substr(auth()->user()->name,0,1)) }}</div>
      <span class="d-none d-md-inline" style="font-size:.78rem;font-weight:600;color:var(--color-ink)">{{ auth()->user()->name }}</span>
      <i class="bi bi-chevron-down d-none d-md-inline" style="font-size:.65rem;color:var(--color-ash)"></i>
    </div>
    <ul class="dropdown-menu dropdown-menu-end mt-1">
      <li><div class="px-3 py-2" style="border-bottom:1px solid var(--color-hairline-soft)">
        <div style="font-size:.8rem;font-weight:600;color:var(--color-ink)">{{ auth()->user()->name }}</div>
        <div style="font-size:.72rem;color:var(--color-ash)">{{ auth()->user()->email }}</div>
        <span class="badge mt-1" style="background:var(--color-surface-card);color:var(--color-mute);border:1px solid var(--color-hairline)">{{ auth()->user()->role }}</span>
      </div></li>
      <li><a class="dropdown-item" href="{{ route('settings') }}"><i class="bi bi-gear me-2"></i>Pengaturan</a></li>
      <li><hr class="dropdown-divider" style="border-color:var(--color-hairline)"></li>
      <li>
        <form method="POST" action="{{ route('logout') }}">
          @csrf
          <button class="dropdown-item" style="color:var(--color-error)"><i class="bi bi-box-arrow-right me-2"></i>Logout</button>
        </form>
      </li>
    </ul>
  </div>
</div>

{{-- Page content --}}
<div class="page-content">
  @if(session('success'))
  <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 mb-3">
    <i class="bi bi-check-circle-fill flex-shrink-0"></i>
    <span>{{ session('success') }}</span>
    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
  </div>
  @endif
  @if(session('error'))
  <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 mb-3">
    <i class="bi bi-exclamation-triangle-fill flex-shrink-0"></i>
    <span>{{ session('error') }}</span>
    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
  </div>
  @endif
  @yield('content')
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el=>new bootstrap.Tooltip(el));
const sidebar  = document.getElementById('sidebar');
const overlay  = document.getElementById('sidebarOverlay');
const toggler  = document.getElementById('sidebarToggle');
if(toggler){
  toggler.addEventListener('click',()=>{sidebar.classList.toggle('open');overlay.classList.toggle('show');});
  overlay.addEventListener('click',()=>{sidebar.classList.remove('open');overlay.classList.remove('show');});
}
// Chart.js global defaults — warm ink palette
if(typeof Chart !== 'undefined'){
  Chart.defaults.font.family = "'Inter',system-ui,sans-serif";
  Chart.defaults.font.size   = 11;
  Chart.defaults.color       = '#62625b';
  Chart.defaults.plugins.legend.labels.boxWidth = 10;
  Chart.defaults.plugins.legend.labels.padding  = 14;
}
</script>
@stack('scripts')
</body>
</html>
