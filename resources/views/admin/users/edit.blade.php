@extends('layouts.admin')
@section('title','Edit Assistant Account')
@section('page-title','Edit Assistant Account')
@section('content')
 
<div class="form-card">
    <form method="POST" action="{{ route('admin.users.update', $user) }}">
    @csrf @method('PUT')
 
    <div class="field">
        <label>Full name <span class="req">*</span></label>
        <input type="text" name="name"
               value="{{ old('name', $user->name) }}" required>
        @error('name')<p class="field-error">{{ $message }}</p>@enderror
    </div>
 
    <div class="field">
        <label>Email address <span class="req">*</span></label>
        <input type="email" name="email"
               value="{{ old('email', $user->email) }}" required>
        @error('email')<p class="field-error">{{ $message }}</p>@enderror
    </div>
 
    <div class="field-row">
        <div class="field">
            <label>
                New password
                <span style="color:var(--text-soft);font-weight:400">(leave blank to keep current)</span>
            </label>
            <input type="password" name="password" placeholder="Minimum 8 characters">
            @error('password')<p class="field-error">{{ $message }}</p>@enderror
        </div>
        <div class="field">
            <label>Confirm new password</label>
            <input type="password" name="password_confirmation" placeholder="Re-enter new password">
        </div>
    </div>
 
    <div class="form-actions">
        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary">Update account</button>
    </div>
    </form>
</div>
@endsection
 