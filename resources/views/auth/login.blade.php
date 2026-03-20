<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Intervention System</title>
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

        /* subtle grid texture */
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

        /* large decorative circle */
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

        .brand {
            position: relative;
            z-index: 1;
        }

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

        .brand-icon svg {
            width: 22px;
            height: 22px;
            fill: var(--navy);
        }

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

        .panel-headline em {
            color: var(--gold-light);
            font-style: normal;
        }

        .panel-sub {
            font-size: 14px;
            color: rgba(255,255,255,.5);
            line-height: 1.7;
            max-width: 280px;
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

        .form-header {
            margin-bottom: 36px;
        }

        .form-header h2 {
            font-family: 'DM Serif Display', serif;
            font-size: 30px;
            color: var(--text-dark);
            margin-bottom: 8px;
        }

        .form-header p {
            font-size: 14px;
            color: var(--text-soft);
        }

        /* Error alert */
        .alert-error {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            background: #fdf2f2;
            border: 1px solid #f5c6c6;
            border-left: 3px solid var(--red);
            border-radius: 8px;
            padding: 12px 14px;
            margin-bottom: 24px;
            font-size: 13px;
            color: var(--red);
            line-height: 1.5;
        }

        .alert-error svg {
            flex-shrink: 0;
            margin-top: 1px;
        }

        /* Form fields */
        .field {
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-size: 13px;
            font-weight: 500;
            color: var(--text-dark);
            margin-bottom: 7px;
            letter-spacing: .2px;
        }

        .input-wrap {
            position: relative;
        }

        .input-wrap svg {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            width: 16px;
            height: 16px;
            color: var(--text-soft);
            pointer-events: none;
        }

        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px 14px 12px 42px;
            font-family: 'DM Sans', sans-serif;
            font-size: 14px;
            background: var(--input-bg);
            border: 1.5px solid var(--border);
            border-radius: 10px;
            color: var(--text-dark);
            transition: border-color .2s, box-shadow .2s, background .2s;
            outline: none;
        }

        input:focus {
            border-color: var(--gold);
            background: var(--white);
            box-shadow: 0 0 0 3px rgba(201,151,58,.12);
        }

        input.is-invalid {
            border-color: var(--red);
        }

        .field-error {
            font-size: 12px;
            color: var(--red);
            margin-top: 5px;
        }

        /* toggle password */
        .toggle-pw {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            padding: 4px;
            color: var(--text-soft);
            display: flex;
            align-items: center;
        }

        .toggle-pw:hover { color: var(--text-dark); }

        /* Remember / Forgot row */
        .row-options {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 26px;
        }

        .remember {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            font-size: 13px;
            color: var(--text-mid);
        }

        .remember input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: var(--gold);
            cursor: pointer;
            padding: 0;
            border: none;
        }

        .forgot {
            font-size: 13px;
            color: var(--gold);
            text-decoration: none;
            font-weight: 500;
        }

        .forgot:hover { color: var(--gold-light); }

        /* Submit button */
        .btn-login {
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
        }

        .btn-login::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, transparent 40%, rgba(201,151,58,.15));
            pointer-events: none;
        }

        .btn-login:hover  { background: var(--navy-soft); }
        .btn-login:active { transform: scale(.99); }

        /* Footer */
        .form-footer {
            margin-top: 32px;
            padding-top: 24px;
            border-top: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .footer-school {
            font-size: 12px;
            color: var(--text-soft);
        }

        .footer-school strong {
            display: block;
            color: var(--text-mid);
            font-weight: 500;
            margin-bottom: 2px;
        }

        .footer-year {
            font-size: 12px;
            color: var(--text-soft);
            text-align: right;
        }

        /* Responsive */
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
                    {{-- Shield / school icon --}}
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 2L3 7v5c0 5.25 3.75 10.15 9 11.35C17.25 22.15 21 17.25 21 12V7L12 2zm-1 13l-3-3 1.41-1.41L11 12.17l4.59-4.58L17 9l-6 6z"/>
                    </svg>
                </div>
                <span class="brand-name">Intervention System</span>
            </div>

            <h1 class="panel-headline">
                Track. <em>Intervene.</em><br>Improve.
            </h1>
            <p class="panel-sub">
                A faculty tool for monitoring student performance and flagging at-risk learners before it's too late.
            </p>
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

            <div class="form-header">
                <h2>Welcome back</h2>
                <p>Sign in to access your dashboard.</p>
            </div>

            {{-- Validation error block --}}
            @if ($errors->any())
                <div class="alert-error">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                    <div>
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                {{-- Email --}}
                <div class="field">
                    <label for="email">Email address</label>
                    <div class="input-wrap">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M4 4h16v16H4z" stroke="none"/><rect x="2" y="4" width="20" height="16" rx="2"/><polyline points="2,4 12,13 22,4"/>
                        </svg>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="{{ old('email') }}"
                            autocomplete="email"
                            autofocus
                            placeholder="you@school.edu"
                            class="{{ $errors->has('email') ? 'is-invalid' : '' }}"
                        >
                    </div>
                    @error('email')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Password --}}
                <div class="field">
                    <label for="password">Password</label>
                    <div class="input-wrap">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                        </svg>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            autocomplete="current-password"
                            placeholder="••••••••"
                            class="{{ $errors->has('password') ? 'is-invalid' : '' }}"
                        >
                        <button type="button" class="toggle-pw" onclick="togglePassword()" aria-label="Show password">
                            <svg id="eye-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Remember + Forgot --}}
                <div class="row-options">
                    <label class="remember">
                        <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                        Remember me
                    </label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="forgot">Forgot password?</a>
                    @endif
                </div>

                <button type="submit" class="btn-login">Sign in</button>
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

<script>
    function togglePassword() {
        const pw = document.getElementById('password');
        const icon = document.getElementById('eye-icon');
        const isHidden = pw.type === 'password';
        pw.type = isHidden ? 'text' : 'password';
        icon.innerHTML = isHidden
            ? '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>'
            : '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
    }
</script>

</body>
</html>