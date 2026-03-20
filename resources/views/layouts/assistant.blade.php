{{-- resources/views/layouts/assistant.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Assistant') — Intervention System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --navy:#0f1c2e; --navy-line:rgba(255,255,255,.07);
            --teal:#0f6e56; --teal-light:#1d9e75; --teal-dim:rgba(29,158,117,.12);
            --gold:#c9973a; --gold-light:#e8b45a;
            --cream:#f5f0e8; --white:#fff; --page-bg:#f0ece3; --card-bg:#fff;
            --text-dark:#1a1a2e; --text-mid:#4a5568; --text-soft:#718096; --border:#e2d9cc;
            --sidebar-w:240px;
            --green:#2d7a4f; --green-bg:#eaf4ee;
            --red:#c0392b; --red-bg:#fdf2f2;
            --amber:#b7621a; --amber-bg:#fef3e2;
            --blue:#1a5fa8; --blue-bg:#e8f1fb;
        }
        html, body { height:100%; font-family:'DM Sans',sans-serif; background:var(--page-bg); color:var(--text-dark); }

        .sidebar { position:fixed; top:0; left:0; bottom:0; width:var(--sidebar-w); background:var(--navy); display:flex; flex-direction:column; z-index:100; overflow:hidden; }
        .sidebar::before { content:''; position:absolute; inset:0; background-image:linear-gradient(rgba(29,158,117,.05) 1px,transparent 1px),linear-gradient(90deg,rgba(29,158,117,.05) 1px,transparent 1px); background-size:36px 36px; pointer-events:none; }
        .sidebar::after { content:''; position:absolute; bottom:-80px; right:-80px; width:260px; height:260px; border-radius:50%; border:1px solid rgba(29,158,117,.15); pointer-events:none; }
        .sb-header { padding:28px 24px 20px; border-bottom:1px solid var(--navy-line); position:relative; z-index:1; }
        .brand-row { display:flex; align-items:center; gap:10px; }
        .brand-icon { width:34px; height:34px; background:var(--teal-light); border-radius:8px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
        .brand-icon svg { width:18px; height:18px; fill:var(--white); }
        .brand-title { font-family:'DM Serif Display',serif; font-size:14px; color:var(--white); }
        .brand-sub { font-size:10px; color:rgba(255,255,255,.4); letter-spacing:.5px; text-transform:uppercase; margin-top:1px; }
        .sb-nav { flex:1; padding:16px 0; overflow-y:auto; position:relative; z-index:1; }
        .nav-sec { font-size:9px; font-weight:600; letter-spacing:1.5px; text-transform:uppercase; color:rgba(255,255,255,.25); padding:12px 24px 6px; }
        .nav-item { display:flex; align-items:center; gap:10px; padding:9px 20px 9px 24px; font-size:13px; color:rgba(255,255,255,.55); text-decoration:none; border-left:2px solid transparent; transition:all .15s; }
        .nav-item svg { width:15px; height:15px; flex-shrink:0; opacity:.7; }
        .nav-item:hover { color:rgba(255,255,255,.9); background:rgba(255,255,255,.04); }
        .nav-item.active { color:#5dcaa5; background:var(--teal-dim); border-left-color:var(--teal-light); }
        .nav-item.active svg { opacity:1; }
        .sb-footer { padding:16px 20px; border-top:1px solid var(--navy-line); position:relative; z-index:1; }
        .user-row { display:flex; align-items:center; gap:10px; }
        .user-av { width:32px; height:32px; border-radius:50%; background:var(--teal-light); flex-shrink:0; display:flex; align-items:center; justify-content:center; font-family:'DM Serif Display',serif; font-size:13px; color:var(--white); }
        .user-name { font-size:12px; font-weight:500; color:var(--white); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .user-role { font-size:10px; color:rgba(255,255,255,.35); text-transform:uppercase; letter-spacing:.5px; }
        .logout-btn { background:none; border:none; cursor:pointer; color:rgba(255,255,255,.3); padding:4px; margin-left:auto; transition:color .15s; }
        .logout-btn:hover { color:rgba(255,255,255,.7); }
        .logout-btn svg { width:14px; height:14px; }

        .main { margin-left:var(--sidebar-w); min-height:100vh; display:flex; flex-direction:column; }
        .topbar { background:var(--white); border-bottom:1px solid var(--border); padding:0 32px; height:60px; display:flex; align-items:center; gap:16px; position:sticky; top:0; z-index:50; }
        .topbar-title { font-family:'DM Serif Display',serif; font-size:18px; color:var(--text-dark); flex:1; }
        .topbar-meta { font-size:12px; color:var(--text-soft); }
        .topbar-sy { background:var(--navy); color:#5dcaa5; font-size:11px; font-weight:500; padding:4px 10px; border-radius:20px; }
        .content { padding:28px 32px; flex:1; }

        .alert { display:flex; align-items:flex-start; gap:10px; padding:12px 16px; border-radius:10px; margin-bottom:20px; font-size:13px; line-height:1.5; }
        .alert-error   { background:var(--red-bg);   border:1px solid #f5c6c6; border-left:3px solid var(--red);   color:var(--red); }
        .alert-success { background:var(--green-bg); border:1px solid #b7dfc5; border-left:3px solid var(--green); color:var(--green); }
        .alert svg { flex-shrink:0; width:16px; height:16px; margin-top:1px; }
    </style>
    @stack('styles')
</head>
<body>
<aside class="sidebar">
    <div class="sb-header">
        <div class="brand-row">
            <div class="brand-icon">
                <svg viewBox="0 0 24 24"><path d="M12 3L1 9l4 2.18v6L12 21l7-3.82v-6L23 9 12 3zm6.82 6L12 12.72 5.18 9 12 5.28 18.82 9zM17 15.99l-5 2.73-5-2.73v-3.72L12 15l5-2.73v3.72z"/></svg>
            </div>
            <div>
                <div class="brand-title">Intervention System</div>
                <div class="brand-sub">Student Assistant</div>
            </div>
        </div>
    </div>
    <nav class="sb-nav">
        <div class="nav-sec">Overview</div>
        <a href="{{ route('assistant.dashboard') }}" class="nav-item {{ request()->routeIs('assistant.dashboard') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
            Dashboard
        </a>
        <div class="nav-sec">Exam Results</div>
        <a href="{{ route('assistant.upload.index') }}" class="nav-item {{ request()->routeIs('assistant.upload*') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
            Upload PDF
        </a>
        <a href="{{ route('assistant.subjects.index') }}" class="nav-item {{ request()->routeIs('assistant.subjects*') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
            All Subjects
        </a>
        <div class="nav-sec">Reports</div>
        <a href="{{ route('assistant.interventions.index') }}" class="nav-item {{ request()->routeIs('assistant.interventions*') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
            Failing Students
        </a>
    </nav>
    <div class="sb-footer">
        <div class="user-row">
            <div class="user-av">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
            <div style="flex:1;min-width:0">
                <div class="user-name">{{ auth()->user()->name }}</div>
                <div class="user-role">Student Assistant</div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="logout-btn" title="Sign out">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                </button>
            </form>
        </div>
    </div>
</aside>
<div class="main">
    <header class="topbar">
        <span class="topbar-title">@yield('page-title', 'Dashboard')</span>
        <span class="topbar-meta">{{ now()->format('l, F j, Y') }}</span>
        @if(isset($activeSemester))
            <span class="topbar-sy">S.Y. {{ $activeSemester->schoolYear->year_start }}–{{ $activeSemester->schoolYear->year_end }} · {{ $activeSemester->semester_name }} Sem</span>
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