{{-- resources/views/admin/analytics/teachers/show.blade.php --}}
@extends('layouts.analytics')

@section('title', $teacher->teacher_name . ' - Teacher Report')
@section('page-title', $teacher->teacher_name)

@section('analytics-content')

<style>
    .report-section {
        background: var(--card-bg);
        border: 1px solid var(--border);
        border-radius: 12px;
        margin-bottom: 20px;
        overflow: hidden;
        animation: slideUp 0.35s ease both;
    }

    .section-header {
        padding: 16px 20px;
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: #faf8f5;
    }

    .section-header h3 {
        margin: 0;
        font-family: 'DM Serif Display', serif;
        font-size: 14px;
        color: var(--text-dark);
    }

    .section-body {
        padding: 20px;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 16px;
        margin-bottom: 20px;
    }

    .info-item {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .info-label {
        font-size: 10px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.7px;
        color: var(--text-soft);
    }

    .info-value {
        font-size: 14px;
        font-weight: 600;
        color: var(--text-dark);
    }

    .performance-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 16px;
    }

    .performance-table thead th {
        background: #faf8f5;
        border-bottom: 1px solid var(--border);
        padding: 10px 12px;
        text-align: left;
        font-size: 10px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.7px;
        color: var(--text-soft);
    }

    .performance-table tbody td {
        padding: 10px 12px;
        border-bottom: 1px solid #f3efe8;
        font-size: 12px;
    }

    .subject-card {
        background: #faf8f5;
        border: 1px solid var(--border);
        border-radius: 8px;
        padding: 14px;
        margin-bottom: 12px;
        display: grid;
        grid-template-columns: 1fr auto auto;
        gap: 16px;
        align-items: center;
    }

    .factor-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid #f3efe8;
    }

    .factor-item:last-child {
        border-bottom: none;
    }

    .factor-bar {
        height: 8px;
        border-radius: 4px;
        background: #e8e8e8;
        overflow: hidden;
        flex: 1;
        margin: 0 12px;
    }

    .factor-fill {
        height: 100%;
        background: var(--gold);
        border-radius: 4px;
    }

    .narrative-box {
        background: #f9f7f2;
        border-left: 3px solid var(--gold);
        padding: 14px;
        border-radius: 4px;
        font-size: 13px;
        line-height: 1.6;
        color: var(--text-mid);
        margin-bottom: 16px;
    }

    .export-buttons {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .export-btn {
        padding: 8px 16px;
        font-size: 12px;
        font-weight: 500;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .export-btn-primary {
        background: var(--navy);
        color: var(--white);
    }

    .export-btn-primary:hover {
        background: var(--navy-soft);
    }

    .export-btn-secondary {
        background: var(--card-bg);
        color: var(--navy);
        border: 1px solid var(--border);
    }

    .export-btn-secondary:hover {
        border-color: var(--navy);
        background: #faf8f5;
    }

    .risk-badge.none {
        background: #eef0f3;
        color: var(--text-soft);
    }
</style>

{{-- Teacher Info Section --}}
<div class="report-section">
    <div class="section-header">
        <h3>Teacher Information</h3>
        <div class="export-buttons">
            <form method="POST" action="{{ route('admin.analytics.teachers.export', $teacher) }}" style="display: inline;">
                @csrf
                <input type="hidden" name="format" value="pdf">
                <button type="submit" class="export-btn export-btn-primary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                    Export PDF
                </button>
            </form>
            <form method="POST" action="{{ route('admin.analytics.teachers.export', $teacher) }}" style="display: inline;">
                @csrf
                <input type="hidden" name="format" value="csv">
                <button type="submit" class="export-btn export-btn-secondary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                    Excel
                </button>
            </form>
        </div>
    </div>
    <div class="section-body">
        <div class="info-grid">
            <div class="info-item">
                <div class="info-label">Full Name</div>
                <div class="info-value">{{ $teacher->teacher_name }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Teacher Code</div>
                <div class="info-value">{{ $teacher->teacher_code ?? 'N/A' }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Email</div>
                <div class="info-value" style="font-size: 12px;">{{ $teacher->email ?? 'N/A' }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Subjects Handled</div>
                <div class="info-value">{{ $teacher->teacherSubjects->count() }}</div>
            </div>
        </div>
    </div>
</div>

{{-- Performance Summary --}}
<div class="report-section">
    <div class="section-header">
        <h3>Performance Summary by Exam Type</h3>
    </div>
    <div class="section-body">
        <table class="performance-table">
            <thead>
                <tr>
                    <th>Exam Type</th>
                    <th>Pass Rate</th>
                    <th>Failed Students</th>
                    <th>Mean Score</th>
                    <th>Difficulty</th>
                </tr>
            </thead>
            <tbody>
                @foreach($summary as $examType => $metrics)
                @php($hasExamData = $metrics['total_students'] > 0)
                <tr>
                    <td style="font-weight: 600;">{{ $examType }}</td>
                    <td>{{ $hasExamData ? $metrics['pass_rate'] . '%' : 'No data' }}</td>
                    <td>{{ $metrics['failed_students'] }}</td>
                    <td>{{ $hasExamData ? $metrics['mean_score'] . '%' : 'No data' }}</td>
                    <td>{{ $metrics['difficulty'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="info-grid">
            <div class="info-item">
                <div class="info-label">Overall Pass Rate</div>
                <div class="info-value">{{ $overall['total_students'] > 0 ? $overall['pass_rate'] . '%' : 'No data' }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Overall Failure Rate</div>
                <div class="info-value">{{ $overall['total_students'] > 0 ? $overall['failure_rate'] . '%' : 'No data' }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Total Failed Students</div>
                <div class="info-value">{{ $overall['failed_students'] }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Mean Score</div>
                <div class="info-value">{{ $overall['mean_score'] }}%</div>
            </div>
        </div>
    </div>
</div>

{{-- Subject Breakdown --}}
<div class="report-section">
    <div class="section-header">
        <h3>Subject Performance Breakdown</h3>
    </div>
    <div class="section-body">
        @forelse($subjectBreakdown as $subject)
        @php($hasSubjectData = ($subject['total_results'] ?? 0) > 0)
        <div class="subject-card">
            <div>
                <div style="font-weight: 600; color: var(--text-dark); margin-bottom: 4px;">
                    {{ $subject['subject_name'] }} ({{ $subject['subject_code'] }})
                </div>
                <div style="font-size: 11px; color: var(--text-soft);">
                    {{ $subject['total_students'] }} students
                </div>
            </div>
            <div style="text-align: right;">
                <div style="font-size: 14px; font-weight: 600; color: var(--text-dark);">{{ $hasSubjectData ? $subject['pass_rate'] . '%' : 'No data' }}</div>
                <div style="font-size: 10px; color: var(--text-soft);">Pass Rate</div>
            </div>
            <span class="risk-badge {{ $subject['risk_level'] }}">
                {{ $subject['risk_label'] }}
            </span>
        </div>
        @empty
        <p style="color: var(--text-soft); text-align: center; padding: 20px;">No subject data available.</p>
        @endforelse
    </div>
</div>

{{-- Factor Analysis --}}
<div class="report-section">
    <div class="section-header">
        <h3>Factor Analysis</h3>
    </div>
    <div class="section-body">
        <div style="margin-bottom: 20px;">
            @foreach(['exam_factor' => 'Exam Factor', 'teacher_factor' => 'Teacher Factor', 'student_factor' => 'Student Factor', 'curriculum_factor' => 'Curriculum Factor'] as $key => $label)
            <div class="factor-item">
                <div style="font-size: 12px; font-weight: 500; color: var(--text-dark);">{{ $label }}</div>
                <div style="display: flex; align-items: center; gap: 12px; min-width: 200px;">
                    <div class="factor-bar">
                        <div class="factor-fill" style="width: {{ $analysis['factors'][$key] }}%"></div>
                    </div>
                    <div style="font-weight: 600; color: var(--text-dark); min-width: 40px; text-align: right;">
                        {{ $analysis['factors'][$key] }}%
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <div class="narrative-box">
            <strong>Analysis Observations:</strong><br><br>
            @foreach($analysis['summaries'] as $summary)
                {{ $summary }}<br><br>
            @endforeach
        </div>
    </div>
</div>

{{-- Auto-Generated Narrative --}}
<div class="report-section">
    <div class="section-header">
        <h3>Professional Narrative Report</h3>
    </div>
    <div class="section-body">
        <div class="narrative-box">
            {{ $narrative }}
        </div>
    </div>
</div>

@endsection
