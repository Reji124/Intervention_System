<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Forgot Password — {{ config('app.name', 'Intervention System') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --navy:      #0f1c2e;
            --navy-mid:  #162540;
            --navy-soft: #1e3050;
            --gold:      #c9973a;
            --gold-light:#e8b45a;
            --cream:     #f5f0e8;
            --white:     #ffffff;
            --text-dark: #1a1a2e;
            --text-mid:  #4a5568;
            --text-soft: #718096;
            --border:    #e2d9cc;
            --input-bg:  #faf8f5;
            --red:       #c0392b;
            --green:     #1a7f5a;
            --green-bg:  #f0faf6;
            --green-border: #a8d8c5;
        }

        html, body {
            height: 100%;
            font-family: 'DM Sans', sans-serif;
            background: var(--cream);
        }

        .layout {
            display: flex;
            min-height: 100vh;
        }

        /* ── LEFT PANEL ───────────────────────────────── */
        .panel-left {
            width: 420px;
            flex-shrink: 0;
            background: var(--navy);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 52px 48px;
            position: relative;
            overflow: hidden;
        }

        .panel-left::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(201,151,58,.06) 1px, transparent 1px),
                linear-gradient(90deg, rgba(201,151,58,.06) 1px, transparent 1px);
            background-size: 40px 40px;
            pointer-events: none;
        }

        .panel-left::after {
            content: '';
            position: absolute;
            bottom: -120px;
            right: -120px;
            width: 380px;
            height: 380px;
            border-radius: 50%;
            border: 1px solid rgba(201,151,58,.15);
            pointer-events: none;
        }

        .brand { position: relative; z-index: 1; }

        .brand-badge {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 40px;
        }

        .brand-icon {
            width: 40px;
            height: 40px;
            background: var(--gold);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .brand-icon svg { width: 22px; height: 22px; fill: var(--navy); }

        .brand-name {
            font-family: 'DM Serif Display', serif;
            font-size: 17px;
            color: var(--white);
            letter-spacing: .3px;
        }

        .panel-headline {
            font-family: 'DM Serif Display', serif;
            font-size: 38px;
            line-height: 1.2;
            color: var(--white);
            margin-bottom: 20px;
        }

        .panel-headline em { color: var(--gold-light); font-style: normal; }

        .panel-sub {
            font-size: 14px;
            color: rgba(255,255,255,.5);
            line-height: 1.7;
            max-width: 280px;
        }

        .panel-motto {
            margin-top: 24px;
            font-family: 'DM Serif Display', serif;
            font-size: 13px;
            font-style: italic;
            color: var(--gold-light);
            opacity: 0.75;
            line-height: 1.6;
            max-width: 280px;
            padding-left: 12px;
            border-left: 2px solid rgba(201,151,58,.35);
        }

        .panel-stats {
            position: relative;
            z-index: 1;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .stat-card {
            background: rgba(255,255,255,.05);
            border: 1px solid rgba(255,255,255,.08);
            border-radius: 12px;
            padding: 18px 20px;
        }

        .stat-value {
            font-family: 'DM Serif Display', serif;
            font-size: 28px;
            color: var(--gold-light);
            line-height: 1;
            margin-bottom: 6px;
        }

        .stat-label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: rgba(255,255,255,.4);
        }

        /* ── RIGHT PANEL ──────────────────────────────── */
        .panel-right {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 32px;
        }

        .form-card {
            width: 100%;
            max-width: 420px;
            animation: fadeUp .5s ease both;
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(16px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* Back link */
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            color: var(--text-soft);
            text-decoration: none;
            margin-bottom: 32px;
            transition: color .2s;
        }

        .back-link svg { width: 14px; height: 14px; transition: transform .2s; }
        .back-link:hover { color: var(--gold); }
        .back-link:hover svg { transform: translateX(-2px); }

        /* Form header */
        .form-header { margin-bottom: 28px; }

        .icon-wrap {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, var(--navy-mid), var(--navy-soft));
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 16px;
            box-shadow: 0 4px 12px rgba(15,28,46,.2);
        }

        .icon-wrap svg { width: 22px; height: 22px; color: var(--gold-light); }

        .form-header h2 {
            font-family: 'DM Serif Display', serif;
            font-size: 30px;
            color: var(--text-dark);
            margin-bottom: 8px;
        }

        .form-header p {
            font-size: 14px;
            color: var(--text-soft);
            line-height: 1.6;
        }

        /* Notices */
        .notice {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            border-radius: 8px;
            padding: 12px 14px;
            margin-bottom: 24px;
            font-size: 13px;
            line-height: 1.55;
        }

        .notice svg { flex-shrink: 0; margin-top: 1px; }

        .notice-info {
            background: #fdf8ef;
            border: 1px solid rgba(201,151,58,.25);
            border-left: 3px solid var(--gold);
            color: var(--text-mid);
        }

        .notice-info svg { color: var(--gold); }

        .notice-success {
            background: var(--green-bg);
            border: 1px solid var(--green-border);
            border-left: 3px solid var(--green);
            color: var(--green);
            font-weight: 500;
        }

        /* Field */
        .field { margin-bottom: 20px; }

        label {
            display: block;
            font-size: 13px;
            font-weight: 500;
            color: var(--text-dark);
            margin-bottom: 7px;
            letter-spacing: .2px;
        }

        .input-wrap { position: relative; }

        .input-wrap > svg {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            width: 16px;
            height: 16px;
            color: var(--text-soft);
            pointer-events: none;
        }

        input[type="email"] {
            width: 100%;
            padding: 12px 16px 12px 42px;
            font-family: 'DM Sans', sans-serif;
            font-size: 14px;
            background: var(--input-bg);
            border: 1.5px solid var(--border);
            border-radius: 10px;
            color: var(--text-dark);
            transition: border-color .2s, box-shadow .2s, background .2s;
            outline: none;
        }

        input[type="email"]:focus {
            border-color: var(--gold);
            background: var(--white);
            box-shadow: 0 0 0 3px rgba(201,151,58,.12);
        }

        input.is-invalid { border-color: var(--red); }

        .field-error {
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: 12px;
            color: var(--red);
            margin-top: 5px;
        }

        /* Submit button */
        .btn-submit {
            width: 100%;
            padding: 13px;
            background: var(--navy);
            color: var(--white);
            font-family: 'DM Sans', sans-serif;
            font-size: 14px;
            font-weight: 600;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            letter-spacing: .4px;
            transition: background .2s, transform .1s;
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-submit::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, transparent 40%, rgba(201,151,58,.15));
            pointer-events: none;
        }

        .btn-submit:hover  { background: var(--navy-soft); }
        .btn-submit:active { transform: scale(.99); }

        /* Footer */
        .form-footer {
            margin-top: 32px;
            padding-top: 24px;
            border-top: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .footer-school { font-size: 12px; color: var(--text-soft); }
        .footer-school strong {
            display: block;
            color: var(--text-mid);
            font-weight: 500;
            margin-bottom: 2px;
        }

        .footer-year { font-size: 12px; color: var(--text-soft); text-align: right; }

        @media (max-width: 768px) {
            .panel-left { display: none; }
            .panel-right { padding: 32px 20px; }
        }
    </style>
</head>
<body>

<div class="layout">

    {{-- ── LEFT DECORATIVE PANEL ──────────────────── --}}
    <aside class="panel-left">
        <div class="brand">
            <div class="brand-badge">
                <div class="brand-icon">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 2L3 7v5c0 5.25 3.75 10.15 9 11.35C17.25 22.15 21 17.25 21 12V7L12 2zm-1 13l-3-3 1.41-1.41L11 12.17l4.59-4.58L17 9l-6 6z"/>
                    </svg>
                </div>
                <span class="brand-name">Teacher Performance Intervention System</span>
            </div>

            <h1 class="panel-headline">
                Track. <em>Intervene.</em><br>Improve.
            </h1>
            <p class="panel-sub">
                A faculty tool for monitoring teacher performance and flagging at-risk learners before it's too late.
            </p>
            <p class="panel-motto">"Data-Driven Insights for Better Teaching Outcomes."</p>
        </div>

        <div class="panel-stats">
            <div class="stat-card">
                <div class="stat-value">2</div>
                <div class="stat-label">Semesters tracked</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">4</div>
                <div class="stat-label">Exam types</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">100%</div>
                <div class="stat-label">Pass rate goal</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">S.Y.</div>
                <div class="stat-label">{{ now()->year }}</div>
            </div>
        </div>
    </aside>

    {{-- ── RIGHT FORM PANEL ────────────────────────── --}}
    <main class="panel-right">
        <div class="form-card">

            {{-- Back to login --}}
            <a href="{{ route('login') }}" class="back-link">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19 12H5M12 5l-7 7 7 7"/>
                </svg>
                Back to Login
            </a>

            {{-- Header --}}
            <div class="form-header">
                <div class="icon-wrap">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                    </svg>
                </div>
                <h2>Forgot password?</h2>
                <p>Enter your registered email and we'll send you a secure reset link right away.</p>
            </div>

            {{-- Success status --}}
            @if (session('status'))
                <div class="notice notice-success">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
                    </svg>
                    {{ session('status') }}
                </div>
            @else
                <div class="notice notice-info">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="8" x2="12" y2="12"/>
                        <line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                    No problem. Just provide your email address and we'll send you a link to choose a new password.
                </div>
            @endif

            {{-- Form --}}
            <form method="POST" action="{{ route('password.email') }}">
                @csrf

                <div class="field">
                    <label for="email">Email address</label>
                    <div class="input-wrap">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <rect x="2" y="4" width="20" height="16" rx="2"/>
                            <polyline points="2,4 12,13 22,4"/>
                        </svg>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="{{ old('email') }}"
                            autofocus
                            autocomplete="email"
                            placeholder="you@school.edu"
                            class="{{ $errors->has('email') ? 'is-invalid' : '' }}"
                        >
                    </div>
                    @error('email')
                        <p class="field-error">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"/>
                                <line x1="12" y1="8" x2="12" y2="12"/>
                                <line x1="12" y1="16" x2="12.01" y2="16"/>
                            </svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <button type="submit" class="btn-submit">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="22" y1="2" x2="11" y2="13"/>
                        <polygon points="22 2 15 22 11 13 2 9 22 2"/>
                    </svg>
                    Send Password Reset Link
                </button>
            </form>

            <div class="form-footer">
                <div class="footer-school">
                    <strong>School Portal</strong>
                    Academic Intervention System
                </div>
                <div class="footer-year">
                    S.Y. {{ now()->year }}
                </div>
            </div>

        </div>
    </main>

</div>

</body>
</html>