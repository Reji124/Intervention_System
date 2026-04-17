{{-- resources/views/assistant/profile/index.blade.php --}}
@extends('layouts.assistant')
@section('title', 'My Profile')
@section('page-title', 'My Profile')

@push('styles')
<style>
.profile-wrap { max-width:720px; display:flex; flex-direction:column; gap:20px; }

.section-card { background:var(--card-bg); border:1px solid var(--border); border-radius:12px; overflow:hidden; animation:slideUp .3s ease both; }
@keyframes slideUp { from{opacity:0;transform:translateY(8px)} to{opacity:1;transform:translateY(0)} }
.section-card:nth-child(2) { animation-delay:.08s; }

.sc-header { padding:16px 22px 14px; border-bottom:1px solid var(--border); display:flex; align-items:center; gap:12px; }
.sc-icon { width:34px; height:34px; border-radius:8px; background:var(--green-bg); display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.sc-icon svg { width:16px; height:16px; stroke:var(--green); }
.sc-title { font-family:'DM Serif Display',serif; font-size:15px; color:var(--text-dark); }
.sc-sub { font-size:11px; color:var(--text-soft); margin-top:2px; }

.sc-body { padding:20px 22px; }
.sc-footer { padding:12px 22px; border-top:1px solid var(--border); display:flex; justify-content:flex-end; }

.field { display:flex; flex-direction:column; gap:5px; margin-bottom:14px; }
.field:last-of-type { margin-bottom:0; }
.field label { font-size:10px; font-weight:600; text-transform:uppercase; letter-spacing:.7px; color:var(--text-soft); }
.field input[type=text],
.field input[type=password] { padding:10px 12px; font-family:'DM Sans',sans-serif; font-size:13px; background:#faf8f5; border:1.5px solid var(--border); border-radius:8px; color:var(--text-dark); outline:none; transition:border-color .2s,box-shadow .2s; width:100%; }
.field input:focus { border-color:var(--teal-light); background:var(--white); box-shadow:0 0 0 3px rgba(29,158,117,.1); }
.field-error { font-size:11px; color:var(--red); margin-top:3px; }

/* Email display — read-only pill */
.email-display { display:flex; align-items:center; gap:8px; padding:10px 12px; background:#f3f4f6; border:1.5px solid var(--border); border-radius:8px; font-size:13px; color:var(--text-mid); }
.email-lock { display:inline-flex; align-items:center; gap:4px; font-size:10px; font-weight:600; color:var(--text-soft); background:#ebebeb; border-radius:20px; padding:2px 8px; margin-left:auto; letter-spacing:.4px; }
.email-lock svg { width:10px; height:10px; }

.divider { border:none; border-top:1px solid var(--border); margin:16px 0; }
.hint { font-size:11px; color:var(--text-soft); margin-bottom:14px; line-height:1.5; }

.btn { display:inline-flex; align-items:center; gap:7px; padding:9px 20px; border-radius:8px; font-size:13px; font-weight:500; border:none; cursor:pointer; transition:all .15s; font-family:'DM Sans',sans-serif; }
.btn-green { background:var(--green); color:var(--white); }
.btn-green:hover { background:#256642; }
.btn-green svg { width:13px; height:13px; }

/* Identity banner */
.identity-banner { display:flex; align-items:center; gap:16px; padding:16px 22px; border-bottom:1px solid var(--border); background:#fafdf9; }
.id-avatar { width:46px; height:46px; border-radius:50%; background:var(--green); display:flex; align-items:center; justify-content:center; font-family:'DM Serif Display',serif; font-size:20px; color:var(--white); flex-shrink:0; }
.id-name { font-weight:600; font-size:15px; color:var(--text-dark); }
.id-role { font-size:11px; color:var(--text-soft); text-transform:uppercase; letter-spacing:.5px; margin-top:2px; }
</style>
@endpush

@section('content')
<div class="profile-wrap">

    {{-- ── Name update ──────────────────────────────────────────────────────── --}}
    <div class="section-card">
        <div class="identity-banner">
            <div class="id-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
            <div>
                <div class="id-name">{{ auth()->user()->name }}</div>
                <div class="id-role">Student Assistant</div>
            </div>
        </div>
        <div class="sc-header">
            <div class="sc-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            </div>
            <div>
                <div class="sc-title">Account details</div>
                <div class="sc-sub">Update your display name</div>
            </div>
        </div>
        <form method="POST" action="{{ route('assistant.profile.update') }}">
            @csrf @method('PATCH')
            <div class="sc-body">

                <div class="field">
                    <label>Full name</label>
                    <input type="text" name="name" value="{{ old('name', auth()->user()->name) }}" required>
                    @error('name')<span class="field-error">{{ $message }}</span>@enderror
                </div>

                <div class="field">
                    <label>Email address</label>
                    <div class="email-display">
                        {{ auth()->user()->email }}
                        <span class="email-lock">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            School email
                        </span>
                    </div>
                    <span style="font-size:11px;color:var(--text-soft);margin-top:4px">Your school-issued email cannot be changed.</span>
                </div>

            </div>
            <div class="sc-footer">
                <button type="submit" class="btn btn-green">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                    Save name
                </button>
            </div>
        </form>
    </div>

    {{-- ── Password update ──────────────────────────────────────────────────── --}}
    <div class="section-card">
        <div class="sc-header">
            <div class="sc-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            </div>
            <div>
                <div class="sc-title">Change password</div>
                <div class="sc-sub">Keep your account secure</div>
            </div>
        </div>
        <form method="POST" action="{{ route('assistant.profile.password') }}">
            @csrf @method('PATCH')
            <div class="sc-body">

                <p class="hint">Leave fields blank if you don't want to change your password.</p>

                <div class="field">
                    <label>Current password</label>
                    <input type="password" name="current_password" autocomplete="current-password" placeholder="Enter current password">
                    @error('current_password')<span class="field-error">{{ $message }}</span>@enderror
                </div>

                <hr class="divider">

                <div class="field">
                    <label>New password</label>
                    <input type="password" name="password" autocomplete="new-password" placeholder="Min. 8 characters">
                    @error('password')<span class="field-error">{{ $message }}</span>@enderror
                </div>

                <div class="field">
                    <label>Confirm new password</label>
                    <input type="password" name="password_confirmation" autocomplete="new-password" placeholder="Repeat new password">
                </div>

            </div>
            <div class="sc-footer">
                <button type="submit" class="btn btn-green">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    Update password
                </button>
            </div>
        </form>
    </div>

</div>
@endsection