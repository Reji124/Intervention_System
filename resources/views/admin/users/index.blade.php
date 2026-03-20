@extends('layouts.admin')
@section('title','User Accounts')
@section('page-title','Student Assistant Accounts')
@section('content')
 
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
    <p style="font-size:13px;color:var(--text-soft)">
        Accounts that can log in and upload exam results on behalf of teachers.
    </p>
    <a href="{{ route('admin.users.create') }}" class="btn btn-primary">+ New assistant</a>
</div>
 
<div class="card">
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Created</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        @forelse($users as $user)
        <tr>
            <td><span class="td-main">{{ $user->name }}</span></td>
            <td>{{ $user->email }}</td>
            <td>{{ $user->created_at->format('M d, Y') }}</td>
            <td class="td-actions">
                <a href="{{ route('admin.users.edit', $user) }}" class="btn-link">Edit</a>
                <form method="POST"
                      action="{{ route('admin.users.destroy', $user) }}"
                      style="display:inline"
                      onsubmit="return confirm('Delete {{ $user->name }}\'s account?')">
                    @csrf @method('DELETE')
                    <button class="btn-link-danger">Delete</button>
                </form>
            </td>
        </tr>
        @empty
        <tr><td colspan="4" class="empty-cell">No assistant accounts yet.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
 