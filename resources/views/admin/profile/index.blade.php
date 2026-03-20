{{-- resources/views/admin/profile/index.blade.php --}}
@extends('layouts.admin')
@section('title', 'My Profile')
@section('page-title', 'My Profile')

@push('styles')
<style>
.profile-grid { display:grid; grid-template-columns:1fr 1fr; gap:24px; align-items:start; }
.section-card { background:var(--card-bg);border:1px solid var(--border);border-radius:12px;overflow:hidden;animation:slideUp .35s ease both; }
@keyframes slideUp{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:translateY(0)}}
.section-card:nth-child(2){animation-delay:.07s}
.section-card:nth-child(3){animation-delay:.12s}
.sc-header { padding:18px 22px 14px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between; }
.sc-title { font-family:'DM Serif Display',serif;font-size:16px;color:var(--text-dark); }
.sc-sub { font-size:12px;color:var(--text-soft);margin-top:2px; }
.sc-body { padding:22px; }
.field { display:flex;flex-direction:column;gap:5px;margin-bottom:14px; }
.field:last-of-type { margin-bottom:0; }
.field label { font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.7px;color:var(--text-soft); }
.field input { padding:10px 12px;font-family:'DM Sans',sans-serif;font-size:13px;background:#faf8f5;border:1.5px solid var(--border);border-radius:8px;color:var(--text-dark);outline:none;transition:border-color .2s,box-shadow .2s;width:100%; }
.field input:focus { border-color:var(--gold);background:var(--white);box-shadow:0 0 0 3px rgba(201,151,58,.1); }
.field-error { font-size:11px;color:var(--red);margin-top:3px; }
.divider { border:none;border-top:1px solid var(--border);margin:18px 0; }
.sc-footer { padding:14px 22px;border-top:1px solid var(--border);display:flex;justify-content:flex-end;gap:10px; }
.btn { display:inline-flex;align-items:center;gap:7px;padding:9px 20px;border-radius:8px;font-size:13px;font-weight:500;text-decoration:none;border:none;cursor:pointer;transition:all .15s;font-family:'DM Sans',sans-serif; }
.btn-primary { background:var(--navy);color:var(--white); }
.btn-primary:hover { background:#1e3050; }
.btn-danger { background:var(--red-bg);color:var(--red);border:1px solid #f5c6c6; }
.btn-danger:hover { background:#fde8e8; }
.btn-sm { padding:5px 12px;font-size:11px; }

/* Admin accounts table */
.admin-table { width:100%;border-collapse:collapse; }
.admin-table thead th { font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.7px;color:var(--text-soft);padding:9px 16px;text-align:left;background:#faf8f5;border-bottom:1px solid var(--border); }
.admin-table tbody td { padding:11px 16px;font-size:13px;border-bottom:1px solid #f3efe8;color:var(--text-mid);vertical-align:middle; }
.admin-table tbody tr:last-child td { border-bottom:none; }
.admin-table tbody tr:hover td { background:#faf8f5; }
.you-badge { display:inline-flex;align-items:center;gap:3px;font-size:10px;font-weight:600;background:var(--amber-bg);color:var(--amber);padding:1px 7px;border-radius:10px;margin-left:6px; }
.avatar-sm { width:30px;height:30px;background:var(--navy);border-radius:50%;display:flex;align-items:center;justify-content:center;font-family:'DM Serif Display',serif;font-size:12px;color:var(--gold-light);flex-shrink:0; }
.user-cell { display:flex;align-items:center;gap:10px; }
.section-full { grid-column:1 / -1; }
</style>
@endpush

@section('content')

<div class="profile-grid">

    {{-- ── Own profile ──────────────────────────────────────────────────────── --}}
    <div class="section-card">
        <div class="sc-header">
            <div>
                <div class="sc-title">My profile</div>
                <div class="sc-sub">Update your name, email and password</div>
            </div>
        </div>
        <form method="POST" action="{{ route('admin.profile.update') }}">
            @csrf @method('PATCH')
            <div class="sc-body">

                <div class="field">
                    <label>Full name</label>
                    <input type="text" name="name" value="{{ old('name', auth()->user()->name) }}" required>
                    @error('name')<span class="field-error">{{ $message }}</span>@enderror
                </div>

                <div class="field">
                    <label>Email address</label>
                    <input type="email" name="email" value="{{ old('email', auth()->user()->email) }}" required>
                    @error('email')<span class="field-error">{{ $message }}</span>@enderror
                </div>

                <hr class="divider">
                <div style="font-size:12px;color:var(--text-soft);margin-bottom:14px">
                    Leave password fields blank to keep your current password.
                </div>

                <div class="field">
                    <label>Current password</label>
                    <input type="password" name="current_password" autocomplete="current-password" placeholder="Required to change password">
                    @error('current_password')<span class="field-error">{{ $message }}</span>@enderror
                </div>

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
                <button type="submit" class="btn btn-primary">Save changes</button>
            </div>
        </form>
    </div>

    {{-- ── Add admin account ────────────────────────────────────────────────── --}}
    <div class="section-card">
        <div class="sc-header">
            <div>
                <div class="sc-title">Add admin account</div>
                <div class="sc-sub">Grant another person access to the admin portal</div>
            </div>
        </div>
        <form method="POST" action="{{ route('admin.profile.admins.store') }}">
            @csrf
            <div class="sc-body">

                <div class="field">
                    <label>Full name</label>
                    <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. Juan dela Cruz">
                    @error('name')<span class="field-error">{{ $message }}</span>@enderror
                </div>

                <div class="field">
                    <label>Email address</label>
                    <input type="email" name="email" value="{{ old('email') }}" required placeholder="e.g. juan@hcdc.edu.ph">
                    @error('email')<span class="field-error">{{ $message }}</span>@enderror
                </div>

                <div class="field">
                    <label>Password</label>
                    <input type="password" name="password" required placeholder="Min. 8 characters">
                    @error('password')<span class="field-error">{{ $message }}</span>@enderror
                </div>

                <div class="field">
                    <label>Confirm password</label>
                    <input type="password" name="password_confirmation" required placeholder="Repeat password">
                </div>

            </div>
            <div class="sc-footer">
                <button type="submit" class="btn btn-primary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:13px;height:13px"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
                    Create admin
                </button>
            </div>
        </form>
    </div>

    {{-- ── Admin accounts list ──────────────────────────────────────────────── --}}
    <div class="section-card section-full">
        <div class="sc-header">
            <div>
                <div class="sc-title">Admin accounts</div>
                <div class="sc-sub">{{ $admins->count() }} account(s) with admin access</div>
            </div>
        </div>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Created</th>
                    <th style="text-align:right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($admins as $admin)
                <tr>
                    <td>
                        <div class="user-cell">
                            <div class="avatar-sm">{{ strtoupper(substr($admin->name, 0, 1)) }}</div>
                            <div>
                                <div style="font-weight:500;color:var(--text-dark)">
                                    {{ $admin->name }}
                                    @if($admin->id === auth()->id())
                                        <span class="you-badge">You</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </td>
                    <td style="font-size:12px">{{ $admin->email }}</td>
                    <td style="font-size:12px;color:var(--text-soft)">{{ $admin->created_at->format('M j, Y') }}</td>
                    <td style="text-align:right">
                        @if($admin->id !== auth()->id())
                        <form method="POST" action="{{ route('admin.profile.admins.destroy', $admin) }}"
                              onsubmit="return confirm('Remove {{ $admin->name }} from admin accounts?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:11px;height:11px"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
                                Remove
                            </button>
                        </form>
                        @else
                        <span style="font-size:11px;color:var(--text-soft)">Current session</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</div>
@endsection 