{{-- resources/views/admin/dashboard.blade.php --}}
@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')

{{-- ── STAT CARDS ───────────────────────────────────── --}}
<div class="stats-grid">

    <div class="stat-card c-blue">
        <span class="stat-change up">+{{ $newTeachersThisMonth ?? 0 }} this month</span>
        <div class="stat-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
        </div>
        <div class="stat-value">{{ $totalTeachers ?? 0 }}</div>
        <div class="stat-label">Total Teachers</div>
    </div>

    <div class="stat-card c-green">
        <span class="stat-change up">Active</span>
        <div class="stat-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>
            </svg>
        </div>
        <div class="stat-value">{{ $totalSubjects ?? 0 }}</div>
        <div class="stat-label">Total Subjects</div>
    </div>

    <div class="stat-card c-gold">
        <span class="stat-change up">This semester</span>
        <div class="stat-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
            </svg>
        </div>
        <div class="stat-value">{{ $totalStudents ?? 0 }}</div>
        <div class="stat-label">Enrolled Students</div>
    </div>

    <div class="stat-card c-red">
        <span class="stat-change down">Needs attention</span>
        <div class="stat-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
        </div>
        <div class="stat-value">{{ $failingStudents ?? 0 }}</div>
        <div class="stat-label">Failing Students</div>
    </div>

</div>

{{-- ── BOTTOM GRID ──────────────────────────────────── --}}
<div class="bottom-grid">

    {{-- Left: Recent failing students table --}}
    <div class="card">
        <div class="card-header">
            <span class="card-title">Recent Exam Results</span>
            <a href="{{ route('admin.interventions.index') }}" class="card-action">View all →</a>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Subject</th>
                    <th>Exam</th>
                    <th>Score</th>
                    <th>Remark</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentResults ?? [] as $result)
                    <tr>
                        <td>
                            <div class="td-name">{{ $result->student->student_name }}</div>
                            <div class="td-code">{{ $result->student->student_code }}</div>
                        </td>
                        <td>{{ $result->exam->teacherSubject->subject->subject_code }}</td>
                        <td>
                            <span class="badge {{ $result->exam->exam_type === 'midterm' ? 'badge-mid' : 'badge-final' }}">
                                {{ ucfirst($result->exam->exam_type) }}
                            </span>
                        </td>
                        <td>{{ $result->percentage }}%</td>
                        <td>
                            <span class="badge {{ $result->remark === 'pass' ? 'badge-pass' : 'badge-fail' }}">
                                {{ ucfirst($result->remark) }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align:center; color:var(--text-soft); padding: 32px;">
                            No exam results recorded yet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Right column --}}
    <div class="right-col">
        {{-- Quick Actions --}}
        <div class="card">
            <div class="card-header">
                <span class="card-title">Quick Actions</span>
            </div>
            <div class="quick-grid">
                <a href="{{ route('admin.teachers.create') }}" class="quick-btn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>
                        <line x1="19" y1="8" x2="19" y2="14"/><line x1="16" y1="11" x2="22" y2="11"/>
                    </svg>
                    Add Teacher
                </a>
                <a href="{{ route('admin.subjects.create') }}" class="quick-btn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>
                        <line x1="12" y1="7" x2="12" y2="13"/><line x1="9" y1="10" x2="15" y2="10"/>
                    </svg>
                    Add Subject
                </a>
                <a href="{{ route('admin.departments.create') }}" class="quick-btn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                        <line x1="12" y1="14" x2="12" y2="20"/><line x1="9" y1="17" x2="15" y2="17"/>
                    </svg>
                    Add Department
                </a>
                <a href="{{ route('admin.school-years.create') }}" class="quick-btn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/>
                        <line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                        <line x1="12" y1="14" x2="12" y2="18"/><line x1="10" y1="16" x2="14" y2="16"/>
                    </svg>
                    New School Year
                </a>
            </div>
        </div>

    </div>
</div>

@endsection