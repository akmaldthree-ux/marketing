<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'DSM Marketing Intelligence Platform')</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --sidebar-bg: #0D1526;
            --sidebar-width: 240px;
            --primary: #2563EB;
            --success: #16A34A;
            --danger: #DC2626;
            --warning: #D97706;
            --font-main: 'DM Sans', sans-serif;
            --font-mono: 'DM Mono', monospace;
        }
        * { font-family: var(--font-main); }
        body { background: #F1F5F9; min-height: 100vh; }

        /* Sidebar */
        .sidebar {
            width: var(--sidebar-width);
            background: var(--sidebar-bg);
            min-height: 100vh;
            position: fixed;
            top: 0; left: 0;
            z-index: 1000;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
        }
        .sidebar-brand {
            padding: 1.5rem 1.25rem 1rem;
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }
        .sidebar-brand .brand-name { color: #fff; font-weight: 700; font-size: 1rem; line-height: 1.2; }
        .sidebar-brand .brand-sub { color: rgba(255,255,255,0.4); font-size: 0.7rem; letter-spacing: 0.05em; }
        .sidebar-section { padding: 0.75rem 1rem 0.25rem; color: rgba(255,255,255,0.3); font-size: 0.65rem; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase; }
        .sidebar-nav .nav-item { margin: 1px 0.5rem; }
        .sidebar-nav .nav-link {
            color: rgba(255,255,255,0.65);
            border-radius: 8px;
            padding: 0.55rem 0.85rem;
            font-size: 0.85rem;
            font-weight: 500;
            display: flex; align-items: center; gap: 0.6rem;
            transition: all 0.15s;
        }
        .sidebar-nav .nav-link:hover { background: rgba(255,255,255,0.08); color: #fff; }
        .sidebar-nav .nav-link.active { background: var(--primary); color: #fff; }
        .sidebar-nav .nav-link i { font-size: 1rem; width: 1.1rem; text-align: center; }

        /* Main layout */
        .main-wrapper { margin-left: var(--sidebar-width); min-height: 100vh; }

        /* Header */
        .main-header {
            background: #fff;
            border-bottom: 1px solid #E2E8F0;
            padding: 0.75rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky; top: 0; z-index: 500;
        }
        .main-header .page-title { font-weight: 700; font-size: 1.1rem; color: #0F172A; }
        .breadcrumb { font-size: 0.78rem; margin: 0; }

        /* Cards */
        .metric-card { background: #fff; border-radius: 12px; padding: 1.25rem 1.5rem; border: 1px solid #E2E8F0; }
        .metric-value { font-family: var(--font-mono); font-size: 1.6rem; font-weight: 500; color: #0F172A; }
        .metric-label { font-size: 0.78rem; color: #64748B; font-weight: 500; text-transform: uppercase; letter-spacing: 0.04em; }
        .metric-delta { font-size: 0.78rem; font-weight: 600; }
        .metric-delta.positive { color: var(--success); }
        .metric-delta.negative { color: var(--danger); }

        /* Alert pulse */
        @keyframes pulse-border { 0%,100%{border-color:var(--danger);} 50%{border-color:transparent;} }
        .alert-breach { background: #FEF2F2; border: 2px solid var(--danger); animation: pulse-border 1.5s infinite; }
        .alert-warning { background: #FFFBEB; border: 2px solid var(--warning); }

        /* Badges */
        .badge-breach { background: #DC2626; color: #fff; font-size: 0.7rem; padding: 3px 8px; border-radius: 20px; }
        .badge-warning-dsm { background: #D97706; color: #fff; font-size: 0.7rem; padding: 3px 8px; border-radius: 20px; }
        .badge-normal { background: #16A34A; color: #fff; font-size: 0.7rem; padding: 3px 8px; border-radius: 20px; }
        .badge-on-track { background: #DCFCE7; color: #15803D; font-size: 0.72rem; padding: 3px 10px; border-radius: 20px; font-weight: 600; }
        .badge-at-risk { background: #FEF3C7; color: #B45309; font-size: 0.72rem; padding: 3px 10px; border-radius: 20px; font-weight: 600; }
        .badge-behind { background: #FEE2E2; color: #B91C1C; font-size: 0.72rem; padding: 3px 10px; border-radius: 20px; font-weight: 600; }

        /* Brand colors */
        .brand-DTHREE { color: #2563EB; }
        .brand-HURIM { color: #7C3AED; }
        .brand-ASFARA { color: #059669; }

        /* CVR color */
        .cvr-good { color: var(--success); font-weight: 600; }
        .cvr-warn { color: var(--warning); font-weight: 600; }
        .cvr-bad { color: var(--danger); font-weight: 600; }

        /* Table */
        .table th { font-size: 0.72rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: #64748B; background: #F8FAFC; }
        .table td { font-size: 0.85rem; vertical-align: middle; }

        /* Number formatting */
        .num { font-family: var(--font-mono); }

        /* Content padding */
        .content-area { padding: 1.5rem; }

        /* Section card */
        .section-card { background: #fff; border-radius: 12px; border: 1px solid #E2E8F0; overflow: hidden; }
        .section-card-header { padding: 1rem 1.25rem; border-bottom: 1px solid #E2E8F0; display: flex; align-items: center; justify-content: space-between; }
        .section-card-header h6 { font-weight: 700; font-size: 0.88rem; color: #0F172A; margin: 0; }

        /* Notification badge */
        .notif-badge { position: absolute; top: -4px; right: -4px; background: var(--danger); color: #fff; font-size: 0.65rem; width: 16px; height: 16px; border-radius: 50%; display: flex; align-items: center; justify-content: center; }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); transition: transform 0.3s; }
            .sidebar.open { transform: translateX(0); }
            .main-wrapper { margin-left: 0; }
        }

        /* Funnel bar */
        .funnel-stage { position: relative; margin-bottom: 0.5rem; }
        .funnel-bar { height: 36px; border-radius: 6px; display: flex; align-items: center; padding: 0 12px; font-size: 0.82rem; font-weight: 600; color: #fff; transition: width 0.6s ease; }
        .funnel-bar.good { background: var(--success); }
        .funnel-bar.warn { background: var(--warning); }
        .funnel-bar.bad { background: var(--danger); }
    </style>
    @yield('head')
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <div class="d-flex align-items-center gap-2 mb-1">
                <div style="width:32px;height:32px;background:var(--primary);border-radius:8px;display:flex;align-items:center;justify-content:center;">
                    <i class="bi bi-graph-up-arrow text-white" style="font-size:1rem;"></i>
                </div>
                <div>
                    <div class="brand-name">DSM Intelligence</div>
                    <div class="brand-sub">DTHREE · HURIM · ASFARA</div>
                </div>
            </div>
        </div>

        <div class="sidebar-nav mt-2 flex-grow-1">
            <div class="sidebar-section">Utama</div>
            <div class="nav-item">
                <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2"></i> Overview
                </a>
            </div>
            <div class="nav-item">
                <a href="{{ route('daily-sales') }}" class="nav-link {{ request()->routeIs('daily-sales') ? 'active' : '' }}">
                    <i class="bi bi-bar-chart-line"></i> Daily Sales
                </a>
            </div>
            <div class="nav-item">
                <a href="{{ route('roas') }}" class="nav-link {{ request()->routeIs('roas') ? 'active' : '' }}">
                    <i class="bi bi-bullseye"></i> ROAS Detail
                </a>
            </div>

            <div class="sidebar-section mt-2">Performa</div>
            <div class="nav-item">
                <a href="{{ route('pic-performance') }}" class="nav-link {{ request()->routeIs('pic-performance') ? 'active' : '' }}">
                    <i class="bi bi-people"></i> PIC Performance
                </a>
            </div>
            <div class="nav-item">
                <a href="{{ route('funnel') }}" class="nav-link {{ request()->routeIs('funnel') ? 'active' : '' }}">
                    <i class="bi bi-filter"></i> Funnel Analytics
                </a>
            </div>
            <div class="nav-item">
                <a href="{{ route('pnl') }}" class="nav-link {{ request()->routeIs('pnl') ? 'active' : '' }}">
                    <i class="bi bi-calculator"></i> P&L Report
                </a>
            </div>

            <div class="sidebar-section mt-2">Perencanaan</div>
            <div class="nav-item">
                <a href="{{ route('demand-forecast') }}" class="nav-link {{ request()->routeIs('demand-forecast') ? 'active' : '' }}">
                    <i class="bi bi-boxes"></i> Demand Forecast
                </a>
            </div>
            <div class="nav-item">
                <a href="{{ route('customer') }}" class="nav-link {{ request()->routeIs('customer') ? 'active' : '' }}">
                    <i class="bi bi-person-heart"></i> Customer & ROR
                </a>
            </div>

            <div class="sidebar-section mt-2">Manajemen</div>
            <div class="nav-item">
                <a href="{{ route('upload') }}" class="nav-link {{ request()->routeIs('upload') ? 'active' : '' }}">
                    <i class="bi bi-cloud-upload"></i> Upload Data
                </a>
            </div>
            @if(auth()->user()->isAdmin())
            <div class="nav-item">
                <a href="{{ route('targets') }}" class="nav-link {{ request()->routeIs('targets') ? 'active' : '' }}">
                    <i class="bi bi-trophy"></i> Target Management
                </a>
            </div>
            <div class="nav-item">
                <a href="{{ route('settings') }}" class="nav-link {{ request()->routeIs('settings') ? 'active' : '' }}">
                    <i class="bi bi-gear"></i> Settings
                </a>
            </div>
            @endif
        </div>

        <div class="p-3 border-top" style="border-color:rgba(255,255,255,0.08)!important;">
            <div class="d-flex align-items-center gap-2">
                <div style="width:32px;height:32px;background:rgba(255,255,255,0.1);border-radius:50%;display:flex;align-items:center;justify-content:center;">
                    <i class="bi bi-person text-white" style="font-size:0.9rem;"></i>
                </div>
                <div>
                    <div style="color:#fff;font-size:0.8rem;font-weight:600;">{{ auth()->user()->name }}</div>
                    <div style="color:rgba(255,255,255,0.4);font-size:0.68rem;">{{ ucfirst(auth()->user()->role) }}</div>
                </div>
                <form action="{{ route('logout') }}" method="POST" class="ms-auto">
                    @csrf
                    <button type="submit" class="btn btn-sm p-1" style="color:rgba(255,255,255,0.4);" title="Logout">
                        <i class="bi bi-box-arrow-right"></i>
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <!-- Main -->
    <div class="main-wrapper">
        <header class="main-header">
            <div>
                <div class="page-title">@yield('page-title', 'Dashboard')</div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-muted text-decoration-none">Home</a></li>
                        @yield('breadcrumb')
                    </ol>
                </nav>
            </div>
            <div class="d-flex align-items-center gap-3">
                <!-- Period filter slot -->
                @yield('header-actions')

                <!-- Notifications -->
                <div class="position-relative">
                    <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="offcanvas" data-bs-target="#notifPanel">
                        <i class="bi bi-bell"></i>
                        @php $unread = \App\Models\AppNotification::where('is_read', false)->count(); @endphp
                        @if($unread > 0)
                        <span class="notif-badge">{{ $unread }}</span>
                        @endif
                    </button>
                </div>

                <!-- Mobile menu -->
                <button class="btn btn-sm btn-outline-secondary d-md-none" onclick="document.getElementById('sidebar').classList.toggle('open')">
                    <i class="bi bi-list"></i>
                </button>
            </div>
        </header>

        <div class="content-area">
            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif
            @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
                <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif
            @yield('content')
        </div>
    </div>

    <!-- Notifications Offcanvas -->
    <div class="offcanvas offcanvas-end" id="notifPanel" style="width:360px;">
        <div class="offcanvas-header border-bottom">
            <h6 class="offcanvas-title fw-bold">Notifikasi</h6>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body p-0">
            @php $notifs = \App\Models\AppNotification::orderByDesc('created_at')->limit(20)->get(); @endphp
            @forelse($notifs as $n)
            <div class="p-3 border-bottom {{ !$n->is_read ? 'bg-light' : '' }}">
                <div class="d-flex gap-2">
                    <span class="mt-1">
                        @if($n->type === 'alert') <i class="bi bi-exclamation-circle-fill text-danger"></i>
                        @elseif($n->type === 'warning') <i class="bi bi-exclamation-triangle-fill text-warning"></i>
                        @elseif($n->type === 'success') <i class="bi bi-check-circle-fill text-success"></i>
                        @else <i class="bi bi-info-circle-fill text-primary"></i>
                        @endif
                    </span>
                    <div>
                        <div class="fw-semibold" style="font-size:0.83rem;">{{ $n->title }}</div>
                        <div class="text-muted" style="font-size:0.78rem;">{{ $n->message }}</div>
                        <div class="text-muted mt-1" style="font-size:0.72rem;">{{ $n->created_at->diffForHumans() }}</div>
                    </div>
                </div>
            </div>
            @empty
            <div class="text-center text-muted p-4">Tidak ada notifikasi</div>
            @endforelse
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <script>
        // Format numbers as IDR
        function formatRupiah(n) {
            if (n >= 1e9) return 'Rp ' + (n/1e9).toFixed(1) + 'M';
            if (n >= 1e6) return 'Rp ' + (n/1e6).toFixed(1) + 'Jt';
            return 'Rp ' + n.toLocaleString('id-ID');
        }
        Chart.defaults.font.family = 'DM Sans';
        Chart.defaults.color = '#64748B';
    </script>
    @yield('scripts')
</body>
</html>
