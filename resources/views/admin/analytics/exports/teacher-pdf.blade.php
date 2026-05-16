{{-- resources/views/admin/analytics/exports/teacher-pdf.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Teacher Report - {{ $teacher->teacher_name }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background: white;
        }
        .container { max-width: 800px; margin: 0 auto; padding: 20px; }
        .header {
            border-bottom: 3px solid #0f1c2e;
            padding-bottom: 20px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        .report-logo-wrap {
            width: 80px;
            height: 80px;
            border: 1px solid #e2d9cc;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            background: #fff;
        }
        .report-logo {
            width: 76px;
            height: 76px;
            object-fit: contain;
        }
        .logo-placeholder {
            font-size: 9px;
            color: #718096;
        }
        .header-text h1 { font-size: 20px; color: #0f1c2e; margin-bottom: 5px; }
        .header-text p { font-size: 12px; color: #718096; }
        .section {
            margin-bottom: 30px;
            page-break-inside: avoid;
        }
        .section-title {
            background: #faf8f5;
            padding: 10px 15px;
            border-left: 4px solid #c9973a;
            margin-bottom: 15px;
            font-size: 14px;
            font-weight: bold;
            color: #0f1c2e;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        th {
            background: #f5f0e8;
            padding: 8px 10px;
            text-align: left;
            font-size: 11px;
            font-weight: bold;
            color: #4a5568;
            border-bottom: 1px solid #e2d9cc;
        }
        td {
            padding: 8px 10px;
            border-bottom: 1px solid #f0ece3;
            font-size: 11px;
        }
        .info-grid {
            display: table;
            width: 100%;
        }
        .info-item {
            display: table-cell;
            width: 25%;
            padding: 10px;
            background: #faf8f5;
            border: 1px solid #e2d9cc;
        }
        .info-label {
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            color: #718096;
            margin-bottom: 3px;
        }
        .info-value {
            font-size: 12px;
            font-weight: bold;
            color: #0f1c2e;
        }
        .narrative {
            background: #fff9f5;
            border-left: 3px solid #c9973a;
            padding: 12px;
            font-size: 11px;
            line-height: 1.6;
            margin-bottom: 15px;
        }
        .footer {
            border-top: 1px solid #e2d9cc;
            padding-top: 10px;
            margin-top: 30px;
            font-size: 9px;
            color: #718096;
            text-align: right;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 9px;
            font-weight: bold;
        }
        .risk-low { background: #eaf4ee; color: #2d7a4f; }
        .risk-moderate { background: #fef3e2; color: #b7621a; }
        .risk-high { background: #fdf2f2; color: #c0392b; }
        .risk-none { background: #eef0f3; color: #718096; }
    </style>
</head>
<body>
    <div class="container">
        {{-- Header --}}
        <div class="header">
            <div class="report-logo-wrap">
                @if(!empty($reportLogoPath) && file_exists($reportLogoPath))
                    <img class="report-logo" src="{{ $reportLogoPath }}" alt="HCDC logo">
                @else
                    <span class="logo-placeholder">Logo</span>
                @endif
            </div>
            <div class="header-text">
                <h1>Teacher Performance Report</h1>
                <p>{{ $academicPeriod }}</p>
                <p>{{ $exportedAt->format('F j, Y') }}</p>
            </div>
        </div>

        {{-- Teacher Information --}}
        <div class="section">
            <div class="section-title">Teacher Information</div>
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
                    <div class="info-value">{{ $teacher->email ?? 'N/A' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Subjects</div>
                    <div class="info-value">{{ $teacher->teacherSubjects->count() }}</div>
                </div>
            </div>
        </div>

        {{-- Performance Summary --}}
        <div class="section">
            <div class="section-title">Performance Summary</div>
            <table>
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
                    @php($hasExamData = $metrics['total_students'] > 0)
                    <tr>
                        <td>{{ $examType }}</td>
                        <td>{{ $hasExamData ? $metrics['pass_rate'] . '%' : 'No data' }}</td>
                        <td>{{ $metrics['failed_students'] }}</td>
                        <td>{{ $hasExamData ? $metrics['mean_score'] . '%' : 'No data' }}</td>
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
                    <div class="info-label">Total Failed</div>
                    <div class="info-value">{{ $overall['failed_students'] }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Mean Score</div>
                    <div class="info-value">{{ $overall['mean_score'] }}%</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Total Students</div>
                    <div class="info-value">{{ $overall['total_students'] }}</div>
                </div>
            </div>
        </div>

        {{-- Subject Breakdown --}}
        <div class="section">
            <div class="section-title">Subject Performance Breakdown</div>
            <table>
                <thead>
                    <tr>
                        <th>Subject</th>
                        <th>Students</th>
                        <th>Pass Rate</th>
                        <th>Risk Level</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($subjectBreakdown as $subject)
                    @php($hasSubjectData = ($subject['total_results'] ?? 0) > 0)
                    <tr>
                        <td>{{ $subject['subject_name'] }}</td>
                        <td>{{ $subject['total_students'] }}</td>
                        <td>{{ $hasSubjectData ? $subject['pass_rate'] . '%' : 'No data' }}</td>
                        <td>
                            <span class="badge risk-{{ $subject['risk_level'] }}">
                                {{ $subject['risk_label'] }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Factor Analysis --}}
        <div class="section">
            <div class="section-title">Factor Analysis</div>
            <table>
                <thead>
                    <tr>
                        <th>Factor</th>
                        <th>Confidence</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Exam Factor</td>
                        <td>{{ $analysis['factors']['exam_factor'] }}%</td>
                    </tr>
                    <tr>
                        <td>Teacher Factor</td>
                        <td>{{ $analysis['factors']['teacher_factor'] }}%</td>
                    </tr>
                    <tr>
                        <td>Student Factor</td>
                        <td>{{ $analysis['factors']['student_factor'] }}%</td>
                    </tr>
                    <tr>
                        <td>Curriculum Factor</td>
                        <td>{{ $analysis['factors']['curriculum_factor'] }}%</td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- Professional Narrative --}}
        <div class="section">
            <div class="section-title">Professional Narrative Report</div>
            <div class="narrative">
                {{ $narrative }}
            </div>
        </div>

        {{-- Footer --}}
        <div class="footer">
            <p>Generated by: {{ $generatedBy }} | {{ $exportedAt->format('F j, Y H:i:s') }}</p>
            <p>This report contains confidential academic performance data.</p>
        </div>
    </div>
</body>
</html>
