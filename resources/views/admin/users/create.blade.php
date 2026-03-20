@extends('layouts.admin')
@section('title','New Assistant Account')
@section('page-title','New Student Assistant Account')
@section('content')
 
<div class="form-card">
 
    <div style="display:flex;gap:10px;padding:12px 14px;background:var(--blue-bg);border:1px solid #b5d4f4;border-radius:8px;margin-bottom:20px;font-size:12px;color:var(--blue);line-height:1.6">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:15px;height:15px;flex-shrink:0;margin-top:1px"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <span>This account will log in as a <strong>Student Assistant</strong> and can upload exam results for any teacher.</span>
    </div>
 
    <form method="POST" action="{{ route('admin.users.store') }}">
    @csrf
 
    <div class="field">
        <label>Full name <span class="req">*</span></label>
        <input type="text" name="name"
               value="{{ old('name') }}"
               placeholder="e.g. Maria Santos"
               required>
        @error('name')<p class="field-error">{{ $message }}</p>@enderror
    </div>
 
    <div class="field">
        <label>Email address <span class="req">*</span></label>
        <input type="email" name="email"
               value="{{ old('email') }}"
               placeholder="e.g. assistant@school.com"
               required>
        @error('email')<p class="field-error">{{ $message }}</p>@enderror
    </div>
 
    <div class="field-row">
        <div class="field">
            <label>Password <span class="req">*</span></label>
            <input type="password" name="password"
                   placeholder="Minimum 8 characters"
                   required>
            @error('password')<p class="field-error">{{ $message }}</p>@enderror
        </div>
        <div class="field">
            <label>Confirm password <span class="req">*</span></label>
            <input type="password" name="password_confirmation"
                   placeholder="Re-enter password"
                   required>
        </div>
    </div>
 
    <div class="form-actions">
        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary">Create account</button>
    </div>
    </form>
</div>
@endsection
 