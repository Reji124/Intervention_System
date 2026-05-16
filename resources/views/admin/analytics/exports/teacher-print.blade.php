{{-- resources/views/admin/analytics/exports/teacher-print.blade.php --}}
@extends('layouts.admin')

@section('title', 'Print - ' . $teacher->teacher_name)
@section('page-title', 'Print Report')

@section('content')

<style>
    @media print {
        .no-print { display: none !important; }
        body { background: white; }
        .main { margin-left: 0 !important; }
        .topbar { display: none; }
        .sidebar { display: none; }
    }

    .print-container {
        max-width: 900px;
        margin: 0 auto;
        background: white;
        padding: 40px;
        font-family: 'Times New Roman', serif;
        color: #333;
        line-height: 1.6;
    }

    .print-header {
        border-bottom: 3px solid #0f1c2e;
        margin-bottom: 30px;
        padding-bottom: 20px;
        display: flex;
        justify-content: space-between;
    }

    .print-logo {
        width: 80px;
        height: 80px;
        background: #f0ece3;
        border: 2px dashed #c9973a;
        border-radius: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 9px;
        color: #666;
    }

    .print-title {
        text-align: right;
    }

    .print-title h1 {
        font-size: 22px;
        color: #0f1c2e;
        margin-bottom: 5px;
    }

    .print-section {
        margin-bottom: 35px;
        page-break-inside: avoid;
    }

    .print-section-title {
        background: #f5f0e8;
        padding: 10px 15px;
        border-left: 4px solid #c9973a;
        margin-bottom: 15px;
        font-size: 14px;
        font-weight: bold;
        color: #0f1c2e;
    }

    .print-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 15px;
        font-size: 12px;
    }

    .print-table th {
        background: #e8e4d8;
        padding: 8px;
        text-align: left;
        border-bottom: 1px solid #999;
        font-weight: bold;
    }

    .print-table td {
        padding: 8px;
        border-bottom: 1px solid #ddd;
    }

    .print-narrative {
        background: #faf8f5;
        border-left: 3px solid #c9973a;
        padding: 12px;
        font-size: 12px;
        line-height: 1.7;
    }

    .print-footer {
        margin-top: 40px;
        border-top: 1px solid #999;
        padding-top: 15px;
        font-size: 10px;
        text-align: right;
        color: #666;
    }

    .no-print {
        margin-bottom: 20px;
        padding: 15px;
        background: #fff9f5;
        border: 1px solid #e2d9cc;
        border-radius: 8px;
        display: flex;
        gap: 10px;
        align-items: center;
        justify-content: space-between;
    }

    .print-btn {
        padding: 10px 20px;
        background: #0f1c2e;
        color: white;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 12px;
        font-weight: bold;
        transition: all 0.2s;
    }

    .print-btn:hover {
        background: #162540;
    }
</style>

<div class="no-print">
    <span style="font-size: 12px; color: var(--text-mid);">Ready to print? Adjust margins to 0.5" and select "Background graphics" in print settings.</span>
    <button class="print-btn" onclick="window.print()">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px; display: inline; margin-right: 5px;">
            <polyline points="6 9 6 2 18 2 18 9"></polyline>
            <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
            <rect x="6" y="14" width="12" height="8"></rect>
        </svg>
        Print Report
    </button>
</div>

<div class="print-container">
    {{-- Header --}}
    <div class="print-header">
        <div class="print-logo">LOGO</div>
        <div class="print-title">
            <h1>Teacher Performance Report</h1>
            <p>{{ $exportedAt->format('F j, Y') }}</p>
        </div>
    </div>

    {{-- Teacher Info --}}
    <div class="print-section">
        <div class="print-section-title">Teacher Information</div>
        <table class="print-table" style="font-size: 11px;">
            <tr>
                <td style="width: 25%; font-weight: bold;">Full Name</td>
                <td>{{ $teacher->teacher_name }}</td>
                <td style="width: 25%; font-weight: bold;">Teacher Code</td>
                <td>{{ $teacher->teacher_code ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Email</td>
                <td>{{ $teacher->email ?? 'N/A' }}</td>
                <td style="font-weight: bold;">Subjects Taught</td>
                <td>{{ $teacher->teacherSubjects->count() }}</td>
            </tr>
        </table>
    </div>

    {{-- Performance Summary --}}
    <div class="print-section">
        <div class="print-section-title">Exam Type Performance Summary</div>
        <table class="print-table">
            <thead>
                <tr>
                    <th>Exam Type</th>
                    <th>Pass Rate</th>
                    <th>Failed Students</th>
                    <th>Mean Score</th>
                </tr>
            </thead>
            <tbody>
                @foreach($summary as $examType => $metrics)
                <tr>
                    <td>{{ $examType }}</td>
                    <td>{{ $metrics['pass_rate'] }}%</td>
                    <td>{{ $metrics['failed_students'] }}</td>
                    <td>{{ $metrics['mean_score'] }}%</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <table class="print-table" style="font-size: 11px; margin-top: 10px;">
            <tr>
                <td><strong>Overall Pass Rate</strong></td>
                <td>{{ $overall['pass_rate'] }}%</td>
                <td><strong>Overall Failure Rate</strong></td>
                <td>{{ $overall['failure_rate'] }}%</td>
            </tr>
            <tr>
                <td><strong>Total Failed Students</strong></td>
                <td>{{ $overall['failed_students'] }}</td>
                <td><strong>Mean Score</strong></td>
                <td>{{ $overall['mean_score'] }}%</td>
            </tr>
        </table>
    </div>

    {{-- Narrative --}}
    <div class="print-section">
        <div class="print-section-title">Professional Narrative Assessment</div>
        <div class="print-narrative">
            {{ $narrative }}
        </div>
    </div>

    {{-- Footer --}}
    <div class="print-footer">
        <p>Generated by: {{ $generatedBy }} | {{ $exportedAt->format('F j, Y H:i') }}</p>
        <p style="font-size: 9px; margin-top: 5px;">Confidential: Academic Performance Data</p>
    </div>
</div>

@endsection
