@extends('layouts.admin')
@section('title','School Years')
@section('page-title','School Years')
@section('content')
<div style="display:flex;justify-content:flex-end;margin-bottom:16px">
    <a href="{{ route('admin.school-years.create') }}" class="btn btn-primary">+ New school year</a>
</div>
<div class="card">
    <table>
        <thead><tr><th>School Year</th><th>Semesters</th><th>Created</th><th></th></tr></thead>
        <tbody>
        @forelse($schoolYears as $sy)
        <tr>
            <td><span class="td-main">S.Y. {{ $sy->year_start }}–{{ $sy->year_end }}</span></td>
            <td>
                @foreach($sy->semesters as $sem)
                    <span class="badge badge-mid">{{ $sem->semester_name }} Sem</span>
                @endforeach
            </td>
            <td>{{ $sy->created_at->format('M d, Y') }}</td>
            <td>
                <form method="POST" action="{{ route('admin.school-years.destroy', $sy) }}" onsubmit="return confirm('Delete this school year?')">
                    @csrf @method('DELETE')
                    <button class="btn-link-danger">Delete</button>
                </form>
            </td>
        </tr>
        @empty
        <tr><td colspan="4" class="empty-cell">No school years yet.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection