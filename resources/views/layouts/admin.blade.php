{{-- resources/views/layouts/admin.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin') — Intervention System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;1,9..40,400&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --navy:       #0f1c2e;
            --navy-mid:   #162540;
            --navy-soft:  #1e3050;
            --navy-line:  rgba(255,255,255,.07);
            --gold:       #c9973a;
            --gold-light: #e8b45a;
            --gold-dim:   rgba(201,151,58,.12);
            --cream:      #f5f0e8;
            --white:      #ffffff;
            --page-bg:    #f0ece3;
            --card-bg:    #ffffff;
            --text-dark:  #1a1a2e;
            --text-mid:   #4a5568;
            --text-soft:  #718096;
            --border:     #e2d9cc;
            --sidebar-w:  240px;
            --green:      #2d7a4f;
            --green-bg:   #eaf4ee;
            --red:        #c0392b;
            --red-bg:     #fdf2f2;
            --amber:      #b7621a;
            --amber-bg:   #fef3e2;
            --blue:       #1a5fa8;
            --blue-bg:    #e8f1fb;
            --teal-light: #1d9e75;
        }

        html, body { height: 100%; font-family: 'DM Sans', sans-serif; background: var(--page-bg); color: var(--text-dark); }

        /* ── SIDEBAR ───────────────────────────────── */
        .sidebar {
            position: fixed; top: 0; left: 0; bottom: 0;
            width: var(--sidebar-w);
            background: var(--navy);
            display: flex; flex-direction: column;
            z-index: 100; overflow: hidden;
        }
        .sidebar::before {
            content: '';
            position: absolute; inset: 0;
            background-image:
                linear-gradient(rgba(201,151,58,.045) 1px, transparent 1px),
                linear-gradient(90deg, rgba(201,151,58,.045) 1px, transparent 1px);
            background-size: 36px 36px;
            pointer-events: none;
        }
        .sidebar::after {
            content: '';
            position: absolute; bottom: -80px; right: -80px;
            width: 260px; height: 260px; border-radius: 50%;
            border: 1px solid rgba(201,151,58,.1);
            pointer-events: none;
        }
        .sidebar-header {
            padding: 28px 24px 20px;
            border-bottom: 1px solid var(--navy-line);
            position: relative; z-index: 1;
        }
        .brand-row { display: flex; align-items: center; gap: 10px; }
        .brand-icon {
            width: 34px; height: 34px;
            background: var(--gold); border-radius: 8px;
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
        }
        .brand-icon svg { width: 18px; height: 18px; fill: var(--navy); }
        .brand-text { line-height: 1.2; }
        .brand-title { font-family: 'DM Serif Display', serif; font-size: 14px; color: var(--white); }
        .brand-sub { font-size: 10px; color: rgba(255,255,255,.4); letter-spacing: .5px; text-transform: uppercase; margin-top: 1px; }

        .sidebar-nav { flex: 1; padding: 16px 0; overflow-y: auto; position: relative; z-index: 1; }
        .nav-section-label {
            font-size: 9px; font-weight: 600; letter-spacing: 1.5px;
            text-transform: uppercase; color: rgba(255,255,255,.25);
            padding: 12px 24px 6px;
        }
        .nav-item {
            display: flex; align-items: center; gap: 10px;
            padding: 9px 20px 9px 24px;
            font-size: 13px; color: rgba(255,255,255,.55);
            text-decoration: none;
            border-left: 2px solid transparent;
            transition: all .15s;
        }
        .nav-item svg { width: 15px; height: 15px; flex-shrink: 0; opacity: .7; }
        .nav-item:hover { color: rgba(255,255,255,.9); background: rgba(255,255,255,.04); }
        .nav-item.active { color: var(--gold-light); background: var(--gold-dim); border-left-color: var(--gold); }
        .nav-item.active svg { opacity: 1; }
        .nav-badge {
            margin-left: auto; font-size: 10px; font-weight: 600;
            background: var(--red); color: #fff;
            padding: 1px 6px; border-radius: 10px; line-height: 1.6;
        }

        .sidebar-footer {
            padding: 16px 20px;
            border-top: 1px solid var(--navy-line);
            position: relative; z-index: 1;
        }
        .user-row { display: flex; align-items: center; gap: 10px; }
        .user-avatar {
            width: 32px; height: 32px; border-radius: 50%;
            background: var(--gold); flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
            font-family: 'DM Serif Display', serif; font-size: 13px; color: var(--navy);
        }
        .user-info { flex: 1; min-width: 0; }
        .user-name { font-size: 12px; font-weight: 500; color: var(--white); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .user-role { font-size: 10px; color: rgba(255,255,255,.35); text-transform: uppercase; letter-spacing: .5px; }
        .logout-btn { background: none; border: none; cursor: pointer; color: rgba(255,255,255,.3); padding: 4px; transition: color .15s; }
        .logout-btn:hover { color: rgba(255,255,255,.7); }
        .logout-btn svg { width: 14px; height: 14px; }

        /* ── MAIN ──────────────────────────────────── */
        .main { margin-left: var(--sidebar-w); min-height: 100vh; display: flex; flex-direction: column; }
        .topbar {
            background: var(--white);
            border-bottom: 1px solid var(--border);
            padding: 0 32px; height: 60px;
            display: flex; align-items: center; gap: 16px;
            position: sticky; top: 0; z-index: 50;
        }
        .topbar-title { font-family: 'DM Serif Display', serif; font-size: 18px; color: var(--text-dark); flex: 1; }
        .topbar-meta { font-size: 12px; color: var(--text-soft); }
        .topbar-sy {
            background: var(--navy); color: var(--gold-light);
            font-size: 11px; font-weight: 500;
            padding: 4px 10px; border-radius: 20px; letter-spacing: .3px;
        }
        .content { padding: 28px 32px; flex: 1; }

        /* ── ALERTS ────────────────────────────────── */
        .alert { display: flex; align-items: flex-start; gap: 10px; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 13px; line-height: 1.5; }
        .alert-success { background: var(--green-bg); border: 1px solid #b7dfc5; border-left: 3px solid var(--green); color: var(--green); }
        .alert-error   { background: var(--red-bg);   border: 1px solid #f5c6c6; border-left: 3px solid var(--red);   color: var(--red); }
        .alert svg { flex-shrink: 0; width: 16px; height: 16px; margin-top: 1px; }

        /* ── SHARED BADGES ─────────────────────────── */
        .badge { display: inline-block; font-size: 10px; font-weight: 600; padding: 2px 8px; border-radius: 20px; letter-spacing: .3px; }
        .badge-pass    { background: var(--green-bg); color: var(--green); }
        .badge-fail    { background: var(--red-bg);   color: var(--red); }
        .badge-mid     { background: var(--amber-bg); color: var(--amber); }
        .badge-prelim  { background: var(--amber-bg); color: var(--amber); }
        .badge-midterm { background: var(--blue-bg);  color: var(--blue); }
        .badge-final   { background: #f0ebfa;         color: #534ab7; }
        .badge-admin   { background: var(--gold-dim); color: var(--amber); }
        .badge-teacher { background: var(--blue-bg);  color: var(--blue); }

        /* ── SHARED BUTTONS ────────────────────────── */
        .btn { display: inline-flex; align-items: center; gap: 7px; padding: 9px 18px; border-radius: 8px; font-size: 13px; font-weight: 500; text-decoration: none; border: none; cursor: pointer; transition: all .15s; font-family: 'DM Sans', sans-serif; }
        .btn-primary   { background: var(--navy); color: var(--white); }
        .btn-primary:hover { background: #1e3050; }
        .btn-secondary { background: transparent; color: var(--text-mid); border: 1.5px solid var(--border); }
        .btn-secondary:hover { border-color: var(--text-mid); }
        .btn-link      { font-size: 12px; color: var(--gold); text-decoration: none; font-weight: 500; background: none; border: none; cursor: pointer; padding: 0; }
        .btn-link:hover { color: var(--gold-light); }
        .btn-link-danger { font-size: 12px; color: var(--red); background: none; border: none; cursor: pointer; padding: 0; font-weight: 500; }
        .btn-link-danger:hover { text-decoration: underline; }

        /* ── SHARED CARD ───────────────────────────── */
        .card { background: var(--card-bg); border: 1px solid var(--border); border-radius: 12px; overflow: hidden; }
        .card-header { padding: 18px 22px 14px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; }
        .card-title { font-family: 'DM Serif Display', serif; font-size: 16px; color: var(--text-dark); }
        .card-action { font-size: 12px; color: var(--gold); text-decoration: none; font-weight: 500; }
        .card-action:hover { color: var(--gold-light); }

        /* ── SHARED TABLE ──────────────────────────── */
        table { width: 100%; border-collapse: collapse; }
        thead th { font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .8px; color: var(--text-soft); padding: 10px 22px; text-align: left; background: #faf8f5; border-bottom: 1px solid var(--border); }
        tbody td { padding: 12px 22px; font-size: 13px; border-bottom: 1px solid #f3efe8; color: var(--text-mid); vertical-align: middle; }
        tbody tr:last-child td { border-bottom: none; }
        tbody tr:hover td { background: #faf8f5; }
        .td-main { font-weight: 500; color: var(--text-dark); }
        .td-code { font-size: 11px; color: var(--text-soft); margin-top: 2px; }
        .td-actions { text-align: right; white-space: nowrap; }
        .empty-cell { text-align: center; padding: 40px !important; color: var(--text-soft); font-style: italic; }

        /* ── SHARED FORM ───────────────────────────── */
        .form-card { background: var(--card-bg); border: 1px solid var(--border); border-radius: 12px; padding: 24px 28px; max-width: 680px; }
        .field { display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px; }
        .field-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .field label { font-size: 12px; font-weight: 500; color: var(--text-dark); }
        .req { color: var(--red); }
        .field input[type="text"],
        .field input[type="email"],
        .field input[type="password"],
        .field input[type="number"],
        .field select {
            padding: 10px 12px; font-family: 'DM Sans', sans-serif; font-size: 13px;
            background: #faf8f5; border: 1.5px solid var(--border); border-radius: 8px;
            color: var(--text-dark); outline: none; transition: border-color .2s, box-shadow .2s;
            width: 100%;
        }
        .field input:focus, .field select:focus {
            border-color: var(--gold); background: var(--white);
            box-shadow: 0 0 0 3px rgba(201,151,58,.1);
        }
        .field-error { font-size: 11px; color: var(--red); margin-top: 2px; }
        .check-label { display: flex; align-items: center; gap: 7px; font-size: 13px; color: var(--text-mid); cursor: pointer; }
        .check-label input { accent-color: var(--gold); width: 15px; height: 15px; }
        .form-actions { display: flex; align-items: center; justify-content: flex-end; gap: 10px; margin-top: 8px; padding-top: 16px; border-top: 1px solid var(--border); }
        .section-label { font-size: 13px; font-weight: 600; color: var(--text-dark); margin-bottom: 14px; padding-bottom: 8px; border-bottom: 1px solid var(--border); }

        /* ── DASHBOARD STATS ───────────────────────── */
        .stats-grid { display: grid; grid-template-columns: repeat(4,1fr); gap: 16px; margin-bottom: 28px; }
        .stat-card { background: var(--card-bg); border: 1px solid var(--border); border-radius: 12px; padding: 20px 22px; position: relative; overflow: hidden; animation: slideUp .4s ease both; }
        .stat-card:nth-child(1){animation-delay:.05s} .stat-card:nth-child(2){animation-delay:.10s}
        .stat-card:nth-child(3){animation-delay:.15s} .stat-card:nth-child(4){animation-delay:.20s}
        @keyframes slideUp { from{opacity:0;transform:translateY(12px)} to{opacity:1;transform:translateY(0)} }
        .stat-card::before { content:''; position:absolute; top:0; left:0; right:0; height:3px; border-radius:12px 12px 0 0; }
        .stat-card.c-blue::before  { background: var(--blue); }
        .stat-card.c-green::before { background: var(--green); }
        .stat-card.c-gold::before  { background: var(--gold); }
        .stat-card.c-red::before   { background: var(--red); }
        .stat-icon { width:36px; height:36px; border-radius:9px; display:flex; align-items:center; justify-content:center; margin-bottom:14px; }
        .stat-icon svg { width:18px; height:18px; }
        .c-blue  .stat-icon { background:var(--blue-bg);  color:var(--blue); }
        .c-green .stat-icon { background:var(--green-bg); color:var(--green); }
        .c-gold  .stat-icon { background:var(--amber-bg); color:var(--amber); }
        .c-red   .stat-icon { background:var(--red-bg);   color:var(--red); }
        .stat-value { font-family:'DM Serif Display',serif; font-size:32px; line-height:1; color:var(--text-dark); margin-bottom:4px; }
        .stat-label { font-size:12px; color:var(--text-soft); }
        .stat-change { position:absolute; top:18px; right:18px; font-size:11px; font-weight:500; padding:2px 7px; border-radius:20px; }
        .stat-change.up   { background:var(--green-bg); color:var(--green); }
        .stat-change.down { background:var(--red-bg);   color:var(--red); }

        /* ── DASHBOARD GRID ────────────────────────── */
        .bottom-grid { display:grid; grid-template-columns:1fr 360px; gap:20px; }
        .right-col { display:flex; flex-direction:column; gap:20px; }
        .activity-list { padding:6px 0; }
        .activity-item { display:flex; align-items:flex-start; gap:12px; padding:12px 22px; border-bottom:1px solid #f3efe8; }
        .activity-item:last-child { border-bottom:none; }
        .activity-dot { width:8px; height:8px; border-radius:50%; margin-top:5px; flex-shrink:0; }
        .dot-red   { background:var(--red); }
        .dot-green { background:var(--green); }
        .dot-gold  { background:var(--gold); }
        .dot-blue  { background:var(--blue); }
        .activity-text { font-size:12px; color:var(--text-mid); line-height:1.5; flex:1; }
        .activity-text strong { color:var(--text-dark); font-weight:500; }
        .activity-time { font-size:11px; color:var(--text-soft); margin-top:2px; }
        .quick-grid { display:grid; grid-template-columns:1fr 1fr; gap:10px; padding:16px 18px; }
        .quick-btn { display:flex; flex-direction:column; align-items:center; gap:7px; padding:14px 10px; border:1.5px solid var(--border); border-radius:10px; background:#faf8f5; font-size:11px; color:var(--text-mid); text-decoration:none; cursor:pointer; transition:all .15s; text-align:center; }
        .quick-btn svg { width:18px; height:18px; }
        .quick-btn:hover { border-color:var(--gold); background:var(--white); color:var(--amber); }
    </style>
    @stack('styles')
</head>
<body>

{{-- ── SIDEBAR ──────────────────────────────── --}}
<aside class="sidebar">
    <div class="sidebar-header">
        <div class="brand-row">
            <div class="brand-icon">
                <svg viewBox="0 0 24 24"><path d="M12 2L3 7v5c0 5.25 3.75 10.15 9 11.35C17.25 22.15 21 17.25 21 12V7L12 2zm-1 13l-3-3 1.41-1.41L11 12.17l4.59-4.58L17 9l-6 6z"/></svg>
            </div>
            <div class="brand-text">
                <div class="brand-title">Intervention System</div>
                <div class="brand-sub">VPAA</div>
            </div>
        </div>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-section-label">Overview</div>
        <a href="{{ route('admin.dashboard') }}" class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
            Dashboard
        </a>

        <div class="nav-section-label">Management</div>
        <a href="{{ route('admin.school-years.index') }}" class="nav-item {{ request()->routeIs('admin.school-years*') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            School Years
        </a>
        <a href="{{ route('admin.departments.index') }}" class="nav-item {{ request()->routeIs('admin.departments*') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            Departments
        </a>
        <a href="{{ route('admin.subjects.index') }}" class="nav-item {{ request()->routeIs('admin.subjects*') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
            Subjects
        </a>
        <a href="{{ route('admin.teachers.index') }}" class="nav-item {{ request()->routeIs('admin.teachers*') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            Teachers
        </a>
        <a href="{{ route('admin.users.index') }}" class="nav-item {{ request()->routeIs('admin.users*') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
            User Accounts
        </a>

        <div class="nav-section-label">Reports</div>
        <a href="{{ route('admin.interventions.index') }}" class="nav-item {{ request()->routeIs('admin.interventions*') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
            Interventions
            @if(isset($interventionCount) && $interventionCount > 0)
                <span class="nav-badge">{{ $interventionCount }}</span>
            @endif
        </a>
    </nav>

    {{-- ── SIDEBAR FOOTER — replace the existing sidebar-footer div ── --}}
    <div class="sidebar-footer">
        <a href="{{ route('admin.profile') }}" class="user-row" style="text-decoration:none;display:flex;align-items:center;gap:10px;padding:6px 8px;border-radius:8px;transition:background .15s" onmouseover="this.style.background='rgba(255,255,255,.06)'" onmouseout="this.style.background='transparent'">
            <div class="user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
            <div class="user-info" style="flex:1;min-width:0">
                <div class="user-name">{{ auth()->user()->name }}</div>
                <div class="user-role" style="display:flex;align-items:center;gap:4px">
                    Administrator
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:10px;height:10px;opacity:.4"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                </div>
            </div>
        </a>
        <form method="POST" action="{{ route('logout') }}" style="margin-top:6px">
            @csrf
            <button type="submit" style="width:100%;display:flex;align-items:center;gap:8px;padding:7px 8px;border-radius:8px;border:none;background:transparent;cursor:pointer;font-family:'DM Sans',sans-serif;font-size:12px;color:rgba(255,255,255,.3);transition:all .15s;text-align:left" onmouseover="this.style.background='rgba(255,255,255,.04)';this.style.color='rgba(255,255,255,.6)'" onmouseout="this.style.background='transparent';this.style.color='rgba(255,255,255,.3)'">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" style="width:13px;height:13px"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                Sign out
            </button>
        </form>
    </div>
</aside>

{{-- ── MAIN ─────────────────────────────────── --}}
<div class="main">
    <header class="topbar">
        <span class="topbar-title">@yield('page-title', 'Dashboard')</span>
        <span class="topbar-meta">{{ now()->format('l, F j, Y') }}</span>
        @if(isset($activeSemester))
            <span class="topbar-sy">
                S.Y. {{ $activeSemester->schoolYear->year_start }}–{{ $activeSemester->schoolYear->year_end }}
                &nbsp;·&nbsp;
                {{ $activeSemester->semester_name }} Sem
            </span>
        @endif
    </header>

    <div class="content">
        @if(session('success'))
            <div class="alert alert-success">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-error">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                {{ session('error') }}
            </div>
        @endif
        @yield('content')
    </div>
</div>

@stack('scripts')
</body>
</html>