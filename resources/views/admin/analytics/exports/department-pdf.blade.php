{{-- resources/views/admin/analytics/exports/department-pdf.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Department Report - {{ $department->department_name }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background: white;
        }
        .container { max-width: 900px; margin: 0 auto; padding: 20px; }
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
            font-size: 10px;
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
            margin-bottom: 15px;
        }
        .info-item {
            display: table-cell;
            width: 33.33%;
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
                <h1>Department Performance Report</h1>
                <p>{{ $department->department_name }}</p>
                <p>{{ $academicPeriod }}</p>
                <p>{{ $exportedAt->format('F j, Y') }}</p>
            </div>
        </div>

        {{-- Department Summary --}}
        <div class="section">
            <div class="section-title">Department Summary</div>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Overall Pass Rate</div>
                    <div class="info-value">{{ $metrics['total_students'] > 0 ? $metrics['pass_rate'] . '%' : 'No data' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Total Teachers</div>
                    <div class="info-value">{{ $metrics['total_teachers'] }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Total Students</div>
                    <div class="info-value">{{ $metrics['total_students'] }}</div>
                </div>
            </div>
        </div>

        {{-- Narrative --}}
        <div class="section">
            <div class="section-title">Professional Narrative Report</div>
            <div class="narrative">
                {{ $narrative }}
            </div>
        </div>

        {{-- Teacher Rankings --}}
        <div class="section">
            <div class="section-title">Teacher Performance Rankings</div>
            <table>
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>Teacher Name</th>
                        <th>Pass Rate</th>
                        <th>Failed Students</th>
                        <th>Total Students</th>
                        <th>Risk Level</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($teachers as $rank => $teacher)
                    @php($hasData = $teacher['total_students'] > 0)
                    @php($riskLevel = $teacher['risk_level'] ?? 'none')
                    @php($riskLabel = $teacher['risk_label'] ?? ($teacher['remark'] ?? 'No data'))
                    <tr>
                        <td>{{ $rank + 1 }}</td>
                        <td>{{ $teacher['name'] }}</td>
                        <td>{{ $hasData ? $teacher['pass_rate'] . '%' : 'No data' }}</td>
                        <td>{{ $teacher['failed_students'] }}</td>
                        <td>{{ $teacher['total_students'] }}</td>
                        <td>
                            <span class="badge risk-{{ $riskLevel }}">
                                {{ $riskLabel }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="text-align: center;">No teachers found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Footer --}}
        <div class="footer">
            <p>Generated by: {{ $generatedBy }} | {{ $exportedAt->format('F j, Y H:i:s') }}</p>
            <p>This report contains confidential academic performance data.</p>
        </div>
    </div>
</body>
</html>
