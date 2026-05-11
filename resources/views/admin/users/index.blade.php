@extends('layouts.admin')
@section('title','User Accounts')
@section('page-title','Student Assistant Accounts')
@section('content')

<style>
    .action-group {
        display: flex;
        gap: 8px;
        align-items: center;
        justify-content: flex-end;
    }

    .btn-action {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 14px;
        border-radius: 8px;
        font-size: 0.8rem;
        font-weight: 600;
        letter-spacing: 0.01em;
        cursor: pointer;
        border: 1.5px solid transparent;
        text-decoration: none;
        transition: background 0.15s, border-color 0.15s, box-shadow 0.15s, transform 0.1s;
        white-space: nowrap;
    }

    .btn-action:active { transform: scale(0.96); }

    .btn-action-edit {
        background: #f5f5f5;
        border-color: #ddd;
        color: #444;
    }
    .btn-action-edit:hover {
        background: #ebebeb;
        border-color: #bbb;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        color: #222;
        text-decoration: none;
    }

    .btn-action-delete {
        background: #fff5f5;
        border-color: #ffc9c9;
        color: #e03131;
    }
    .btn-action-delete:hover {
        background: #ffe3e3;
        border-color: #ff9a9a;
        box-shadow: 0 2px 8px rgba(224,49,49,0.13);
        color: #c92a2a;
    }

    .btn-action svg { flex-shrink: 0; }
    .delete-form { display: inline; margin: 0; padding: 0; }
</style>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
    <p style="font-size:13px;color:var(--text-soft)">
        Accounts that can log in and upload exam results on behalf of teachers.
    </p>
    <a href="{{ route('admin.users.create') }}" class="btn btn-primary">+ New assistant</a>
</div>

<div class="card">
    <div class="card-table">
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
            <td>
                <div class="action-group">
                    {{-- Edit --}}
                    <a href="{{ route('admin.users.edit', $user) }}" class="btn-action btn-action-edit">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                        </svg>
                        Edit
                    </a>

                    {{-- Delete --}}
                    <form class="delete-form" method="POST" action="{{ route('admin.users.destroy', $user) }}"
                          onsubmit="return confirm('Delete {{ $user->name }}\'s account?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-action btn-action-delete">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="3 6 5 6 21 6"/>
                                <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                                <path d="M10 11v6"/><path d="M14 11v6"/>
                                <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                            </svg>
                            Delete
                        </button>
                    </form>
                </div>
            </td>
        </tr>
        @empty
        <tr><td colspan="4" class="empty-cell">No assistant accounts yet.</td></tr>
        @endforelse
        </tbody>
    </table>
    </div>
</div>

@endsection