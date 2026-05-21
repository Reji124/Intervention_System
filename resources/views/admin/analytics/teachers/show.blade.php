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

    .performance-table tbody tr:hover {
        background: #faf8f5;
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
        cursor: pointer;
        transition: all 0.2s;
    }

    .subject-card:hover {
        border-color: var(--gold);
        background: #fffbf2;
    }

    .subject-card-content {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .subject-card-name {
        font-weight: 600;
        color: var(--text-dark);
        margin-bottom: 4px;
    }

    .subject-card-meta {
        font-size: 11px;
        color: var(--text-soft);
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

    .remark-badge {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 16px;
        font-size: 11px;
        font-weight: 600;
    }

    .remark-badge.excellent {
        background: #d4edda;
        color: #155724;
    }

    .remark-badge.very-good {
        background: #d1ecf1;
        color: #0c5460;
    }

    .remark-badge.good {
        background: #e2e3e5;
        color: #383d41;
    }

    .remark-badge.fair {
        background: #fff3cd;
        color: #856404;
    }

    .remark-badge.poor {
        background: #f8d7da;
        color: #721c24;
    }

    .remark-badge.very-poor {
        background: #fad2e1;
        color: #c0392b;
    }

    .remark-badge.none {
        background: #eef0f3;
        color: var(--text-soft);
    }

    .chart-container {
        position: relative;
        height: 300px;
        margin-bottom: 20px;
    }

    .subject-filter {
        margin-bottom: 16px;
    }

    .subject-filter select {
        padding: 8px 12px;
        border: 1.5px solid var(--border);
        border-radius: 6px;
        background: var(--card-bg);
        color: var(--text-dark);
        font-size: 13px;
        cursor: pointer;
        font-family: 'DM Sans', sans-serif;
    }

    .subject-filter select:hover {
        border-color: var(--gold);
    }

    .factor-analysis-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        z-index: 1000;
        align-items: center;
        justify-content: center;
    }

    .factor-analysis-modal.show {
        display: flex;
    }

    .factor-modal-content {
        background: var(--card-bg);
        border-radius: 12px;
        padding: 28px;
        max-width: 500px;
        width: 90%;
        border: 1px solid var(--border);
        max-height: 80vh;
        overflow-y: auto;
    }

    .factor-modal-header {
        margin-bottom: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .factor-modal-title {
        font-family: 'DM Serif Display', serif;
        font-size: 18px;
        color: var(--text-dark);
        margin: 0;
    }

    .factor-modal-close {
        background: none;
        border: none;
        font-size: 24px;
        cursor: pointer;
        color: var(--text-soft);
        padding: 0;
        line-height: 1;
    }

    .factor-item {
        margin-bottom: 20px;
    }

    .factor-label {
        font-weight: 600;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--text-soft);
        margin-bottom: 8px;
        display: flex;
        justify-content: space-between;
    }

    .factor-bar {
        height: 8px;
        border-radius: 4px;
        background: #e8e8e8;
        overflow: hidden;
        margin-bottom: 8px;
    }

    .factor-fill {
        height: 100%;
        background: var(--gold);
        border-radius: 4px;
    }

    .factor-summary {
        font-size: 12px;
        line-height: 1.6;
        color: var(--text-mid);
        background: #faf8f5;
        padding: 12px;
        border-radius: 6px;
        margin-top: 16px;
        border-left: 3px solid var(--gold);
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
                <div class="info-value">{{ count($subjectBreakdown) }}</div>
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
                    <th>Remark</th>
                </tr>
            </thead>
            <tbody>
                @foreach($summary as $examType => $metrics)
                @php($hasExamData = $metrics['total_students'] > 0)
                <tr onclick="showExamTypeFactorAnalysis('{{ $examType }}', {{ json_encode($summary[$examType]) }})" style="cursor: pointer;">
                    <td style="font-weight: 600;">{{ $examType }}</td>
                    <td>{{ $hasExamData ? $metrics['pass_rate'] . '%' : 'No data' }}</td>
                    <td>{{ $metrics['failed_students'] }}</td>
                    <td>{{ $hasExamData ? $metrics['mean_score'] . '%' : 'No data' }}</td>
                    <td>
                        @if($hasExamData)
                            <span class="remark-badge {{ $metrics['remark_class'] }}">{{ $metrics['remark'] }}</span>
                        @else
                            <span class="remark-badge none">No data</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Overall Factor Analysis --}}
        <div class="info-grid" style="margin-top: 24px; padding-top: 20px; border-top: 1px solid var(--border);">
            <div class="info-item">
                <div class="info-label">Overall Pass Rate</div>
                <div class="info-value">{{ $overall['total_students'] > 0 ? $overall['pass_rate'] . '%' : 'No data' }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Overall Remark</div>
                <div class="info-value">
                    <span class="remark-badge {{ $overall['remark_class'] }}">{{ $overall['remark'] }}</span>
                </div>
            </div>
            <div class="info-item">
                <div class="info-label">Overall Failure Rate</div>
                <div class="info-value">{{ $overall['total_students'] > 0 ? $overall['failure_rate'] . '%' : 'No data' }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Mean Score</div>
                <div class="info-value">{{ $overall['mean_score'] }}%</div>
            </div>
        </div>

        {{-- Overall Factor Analysis Display --}}
        <div style="margin-top: 20px; padding: 16px; background: #faf8f5; border-radius: 8px; border: 1px solid var(--border);">
            <h4 style="margin-top: 0; font-family: 'DM Serif Display', serif; font-size: 14px; color: var(--text-dark);">Overall Teaching Performance Factors</h4>
            
            <div class="factor-item">
                <div class="factor-label">
                    <span>Exam Quality</span>
                    <span>{{ round($overallFactorAnalysis['exam_factor']) }}%</span>
                </div>
                <div class="factor-bar">
                    <div class="factor-fill" style="width: {{ round($overallFactorAnalysis['exam_factor']) }}%;"></div>
                </div>
                <div style="font-size: 12px; color: var(--text-mid);">{{ $overallFactorAnalysis['summaries']['exam_factor'] }}</div>
            </div>

            <div class="factor-item">
                <div class="factor-label">
                    <span>Teaching Consistency</span>
                    <span>{{ round($overallFactorAnalysis['teacher_factor']) }}%</span>
                </div>
                <div class="factor-bar">
                    <div class="factor-fill" style="width: {{ round($overallFactorAnalysis['teacher_factor']) }}%;"></div>
                </div>
                <div style="font-size: 12px; color: var(--text-mid);">{{ $overallFactorAnalysis['summaries']['teacher_factor'] }}</div>
            </div>

            <div class="factor-item">
                <div class="factor-label">
                    <span>Student Performance</span>
                    <span>{{ round($overallFactorAnalysis['student_factor']) }}%</span>
                </div>
                <div class="factor-bar">
                    <div class="factor-fill" style="width: {{ round($overallFactorAnalysis['student_factor']) }}%;"></div>
                </div>
                <div style="font-size: 12px; color: var(--text-mid);">{{ $overallFactorAnalysis['summaries']['student_factor'] }}</div>
            </div>
        </div>
    </div>
</div>

{{-- Year-over-Year Comparison --}}
@if($yoyData['previous_year'] !== null)
<div class="report-section">
    <div class="section-header">
        <h3>Year-over-Year Performance Comparison</h3>
    </div>
    <div class="section-body">
        <div class="subject-filter">
            <label style="font-size: 12px; color: var(--text-soft); font-weight: 600; margin-right: 8px;">Overall Comparison (All Subjects)</label>
        </div>
        <div class="chart-container">
            <canvas id="yoyChart"></canvas>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const yoyData = @json($yoyData);
    const examTypes = yoyData.exam_types;
    const sections = yoyData.sections || [];
    
    // Build dataset labels and data
    const datasets = [];
    const colors = ['#1f77b4', '#aec7e8', '#d62728', '#ff9896'];
    let colorIndex = 0;
    
    // Current year
    if (yoyData.current_year && yoyData.current_year.sections) {
        sections.forEach(section => {
            const sectionData = yoyData.current_year.sections[section] || {};
            const data = examTypes.map(exam => sectionData[exam] || 0);
            
            datasets.push({
                label: `Section ${section} - ${yoyData.current_year.school_year}`,
                data: data,
                borderColor: colors[colorIndex],
                backgroundColor: colors[colorIndex] + '20',
                borderWidth: 2.5,
                tension: 0.4,
                fill: false,
            });
            colorIndex++;
        });
    }
    
    // Previous year
    if (yoyData.previous_year && yoyData.previous_year.sections) {
        sections.forEach(section => {
            const sectionData = yoyData.previous_year.sections[section] || {};
            const data = examTypes.map(exam => sectionData[exam] || 0);
            
            datasets.push({
                label: `Section ${section} - ${yoyData.previous_year.school_year}`,
                data: data,
                borderColor: colors[colorIndex],
                backgroundColor: colors[colorIndex] + '20',
                borderWidth: 2.5,
                tension: 0.4,
                fill: false,
                borderDash: [5, 5],
            });
            colorIndex++;
        });
    }
    
    const ctx = document.getElementById('yoyChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: examTypes,
            datasets: datasets,
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        font: { size: 12 },
                        padding: 15,
                        usePointStyle: true,
                    }
                },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    title: {
                        display: true,
                        text: 'Pass Rate (%)',
                    },
                    ticks: {
                        font: { size: 11 },
                    }
                },
                x: {
                    ticks: {
                        font: { size: 11 },
                    }
                }
            },
            interaction: {
                intersect: false,
                mode: 'index',
            },
        }
    });
});
</script>
@endif

{{-- Subject Breakdown --}}
<div class="report-section">
    <div class="section-header">
        <h3>Subject Performance Breakdown</h3>
    </div>
    <div class="section-body">
        @forelse($subjectBreakdown as $subject)
        @php($hasSubjectData = ($subject['total_results'] ?? 0) > 0)
        <div class="subject-card" onclick="showFactorAnalysis({{ $subject['subject_id'] }}, '{{ $subject['subject_name'] }}')">
            <div class="subject-card-content">
                <div class="subject-card-name">
                    {{ $subject['subject_name'] }} ({{ $subject['subject_code'] }})
                </div>
                <div class="subject-card-meta">
                    {{ $subject['total_students'] }} students • {{ $subject['total_results'] }} exam results
                </div>
            </div>
            <div style="text-align: right;">
                <div style="font-size: 14px; font-weight: 600; color: var(--text-dark);">{{ $hasSubjectData ? $subject['pass_rate'] . '%' : 'No data' }}</div>
                <div style="font-size: 10px; color: var(--text-soft);">Pass Rate</div>
            </div>
            <div>
                @if($hasSubjectData)
                    <span class="remark-badge {{ $subject['remark_class'] }}">{{ $subject['remark'] }}</span>
                @else
                    <span class="remark-badge none">No data</span>
                @endif
            </div>
        </div>
        @empty
        <p style="color: var(--text-soft); text-align: center; padding: 20px;">No subject data available.</p>
        @endforelse
    </div>
</div>

{{-- Factor Analysis Modals --}}
<div class="factor-analysis-modal" id="factorModal">
    <div class="factor-modal-content">
        <div class="factor-modal-header">
            <h2 class="factor-modal-title">Factor Analysis</h2>
            <button class="factor-modal-close" onclick="closeFactorAnalysis()">&times;</button>
        </div>
        <div id="factorModalBody" style="min-height: 200px;">
            <p style="text-align: center; color: var(--text-soft);">Loading analysis...</p>
        </div>
    </div>
</div>

<script>
function showFactorAnalysis(subjectId, subjectName) {
    const modal = document.getElementById('factorModal');
    const body = document.getElementById('factorModalBody');
    
    // Fetch factor analysis via AJAX
    fetch('{{ route("admin.analytics.teachers.show", $teacher) }}/factor-analysis', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ subject_id: subjectId })
    })
    .then(response => response.json())
    .then(data => {
        let html = `
            <div style="margin-bottom: 20px;">
                <h3 style="font-size: 16px; margin: 0 0 16px 0; color: var(--text-dark);">${subjectName} - Factor Analysis</h3>
                
                <div class="factor-item">
                    <div class="factor-label">
                        Exam Factor
                        <span style="font-weight: normal; font-size: 13px;">${data.exam_factor}%</span>
                    </div>
                    <div class="factor-bar">
                        <div class="factor-fill" style="width: ${data.exam_factor}%"></div>
                    </div>
                </div>
                
                <div class="factor-item">
                    <div class="factor-label">
                        Teacher Factor
                        <span style="font-weight: normal; font-size: 13px;">${data.teacher_factor}%</span>
                    </div>
                    <div class="factor-bar">
                        <div class="factor-fill" style="width: ${data.teacher_factor}%"></div>
                    </div>
                </div>
                
                <div class="factor-item">
                    <div class="factor-label">
                        Student Factor
                        <span style="font-weight: normal; font-size: 13px;">${data.student_factor}%</span>
                    </div>
                    <div class="factor-bar">
                        <div class="factor-fill" style="width: ${data.student_factor}%"></div>
                    </div>
                </div>
                
                ${data.summaries && data.summaries.length > 0 ? `
                    <div class="factor-summary">
                        <strong>Analysis Summary:</strong><br>
                        ${data.summaries.join('<br><br>')}
                    </div>
                ` : ''}
            </div>
        `;
        body.innerHTML = html;
    })
    .catch(error => {
        body.innerHTML = '<p style="color: var(--text-soft);">Error loading analysis. Please try again.</p>';
        console.error('Error:', error);
    });
    
    modal.classList.add('show');
}

function showExamTypeFactorAnalysis(examType, examMetrics) {
    const modal = document.getElementById('factorModal');
    const body = document.getElementById('factorModalBody');
    
    // Fetch factor analysis via AJAX
    fetch('{{ route("admin.analytics.teachers.exam-type-factor-analysis", $teacher) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ exam_type: examType })
    })
    .then(response => response.json())
    .then(data => {
        let html = `
            <div style="margin-bottom: 20px;">
                <h3 style="font-size: 16px; margin: 0 0 16px 0; color: var(--text-dark);">${examType} Exam - Factor Analysis</h3>
                <p style="font-size: 12px; color: var(--text-soft); margin: 0 0 16px 0;">Pass Rate: ${examMetrics.pass_rate}% | Students: ${examMetrics.total_students}</p>
                
                <div class="factor-item">
                    <div class="factor-label">
                        Exam Quality
                        <span style="font-weight: normal; font-size: 13px;">${Math.round(data.exam_factor)}%</span>
                    </div>
                    <div class="factor-bar">
                        <div class="factor-fill" style="width: ${Math.round(data.exam_factor)}%"></div>
                    </div>
                </div>
                
                <div class="factor-item">
                    <div class="factor-label">
                        Teaching Consistency
                        <span style="font-weight: normal; font-size: 13px;">${Math.round(data.teacher_factor)}%</span>
                    </div>
                    <div class="factor-bar">
                        <div class="factor-fill" style="width: ${Math.round(data.teacher_factor)}%"></div>
                    </div>
                </div>
                
                <div class="factor-item">
                    <div class="factor-label">
                        Student Performance
                        <span style="font-weight: normal; font-size: 13px;">${Math.round(data.student_factor)}%</span>
                    </div>
                    <div class="factor-bar">
                        <div class="factor-fill" style="width: ${Math.round(data.student_factor)}%"></div>
                    </div>
                </div>
                
                ${data.summaries ? `
                    <div style="margin-top: 16px; padding: 12px; background: #faf8f5; border-radius: 6px; border-left: 3px solid var(--gold);">
                        <div style="font-size: 12px; color: var(--text-mid); line-height: 1.6;">
                            <strong style="color: var(--text-dark);">Exam Quality:</strong> ${data.summaries.exam_factor}<br><br>
                            <strong style="color: var(--text-dark);">Teaching Consistency:</strong> ${data.summaries.teacher_factor}<br><br>
                            <strong style="color: var(--text-dark);">Student Performance:</strong> ${data.summaries.student_factor}
                        </div>
                    </div>
                ` : ''}
            </div>
        `;
        body.innerHTML = html;
    })
    .catch(error => {
        body.innerHTML = '<p style="color: var(--text-soft);">Error loading analysis. Please try again.</p>';
        console.error('Error:', error);
    });
    
    modal.classList.add('show');
}

function closeFactorAnalysis() {
    document.getElementById('factorModal').classList.remove('show');
}

// Close modal when clicking outside
document.getElementById('factorModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeFactorAnalysis();
    }
});
</script>

{{-- Professional Narrative Report --}}
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
