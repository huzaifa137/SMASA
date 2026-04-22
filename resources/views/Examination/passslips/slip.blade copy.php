<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pass Slip — {{ $exam->exam_name }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=Cairo:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #003366;
            --secondary: #0055a4;
            --accent: #d4af37;
            --pass: #2e7d32;
            --fail: #c62828;
            --border: #b0bec5;
            --bg: #f5f5f5;
            --text: #263238;
            --light: #f9f9f9;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Cairo', sans-serif;
            background: var(--bg);
            color: var(--text);
            font-size: 12px;
            line-height: 1.5;
        }

        .pass-slip {
            max-width: 800px;
            margin: 20px auto;
            background: white;
            border: 1px solid var(--border);
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        /* Header */
        .header {
            text-align: center;
            padding: 15px;
            border-bottom: 2px solid var(--primary);
            background: var(--light);
        }

        .school-logo {
            width: 80px;
            height: 80px;
            margin: 0 auto 10px;
            border-radius: 50%;
            background: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
        }

        .school-name {
            font-size: 18px;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 5px;
        }

        .exam-title {
            font-size: 16px;
            font-weight: 600;
            color: var(--secondary);
        }

        /* Student Info */
        .student-info {
            display: flex;
            padding: 15px;
            border-bottom: 1px solid var(--border);
            gap: 20px;
        }

        .student-photo {
            width: 120px;
            height: 140px;
            border: 1px solid var(--border);
            background: var(--light);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .student-photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .info-grid {
            flex: 1;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }

        .info-row {
            display: flex;
            gap: 5px;
        }

        .info-label {
            font-weight: 600;
            color: var(--primary);
            min-width: 80px;
        }

        .info-value {
            color: var(--text);
        }

        /* Marks Table */
        .marks-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }

        .marks-table th,
        .marks-table td {
            padding: 8px 10px;
            text-align: center;
            border: 1px solid var(--border);
        }

        .marks-table th {
            background: var(--primary);
            color: white;
            font-weight: 600;
        }

        .marks-table tr:nth-child(even) {
            background: var(--light);
        }

        .grade-pass {
            color: var(--pass);
            font-weight: 600;
        }

        .grade-fail {
            color: var(--fail);
            font-weight: 600;
        }

        /* Summary */
        .summary {
            display: flex;
            padding: 15px;
            border-top: 2px solid var(--primary);
            border-bottom: 2px solid var(--primary);
            margin: 10px 0;
        }

        .summary-item {
            flex: 1;
            text-align: center;
        }

        .summary-label {
            font-size: 11px;
            color: var(--primary);
            font-weight: 600;
            margin-bottom: 5px;
        }

        .summary-value {
            font-size: 18px;
            font-weight: 700;
            color: var(--secondary);
        }

        /* Footer */
        .footer {
            display: flex;
            justify-content: space-between;
            padding: 10px 15px;
            background: var(--light);
            border-top: 1px solid var(--border);
            font-size: 11px;
            color: var(--primary);
        }

        /* Print Styles */
        @media print {
            body {
                background: white;
                font-size: 11px;
            }

            .pass-slip {
                box-shadow: none;
                border: none;
                margin: 0;
                page-break-after: always;
            }

            .pass-slip:last-child {
                page-break-after: avoid;
            }
        }
    </style>
</head>
<body>
    @php
        // Ordinal suffix function
        $ord = function($n) {
            if (!is_numeric($n)) return $n;
            $s = ['th','st','nd','rd'];
            $v = $n % 100;
            return $n.($s[($v-20)%10] ?? $s[$v] ?? $s[0]);
        };
    @endphp

    <div class="pass-slip">
        <!-- Header -->
        <div class="header">
            <div class="school-logo">
                @if(file_exists(public_path('images/school_logo.png')))
                    <img src="{{ asset('images/school_logo.png') }}" alt="School Logo">
                @else
                    <i class="fas fa-school"></i>
                @endif
            </div>
            <div class="school-name">
                {{ $schoolName }}
            </div>
            <div class="exam-title">
                {{ $exam->exam_name }} — Official Examination Result Slip
            </div>
        </div>

        <!-- Student Info -->
        <div class="student-info">
            <div class="student-photo">
                @if($photo)
                    <img src="{{ $photo }}" alt="Student Photo">
                @else
                    <div style="text-align: center; color: var(--border);">
                        <i class="fas fa-user-circle" style="font-size: 48px;"></i>
                        <div>Photo</div>
                    </div>
                @endif
            </div>
            <div class="info-grid">
                <div class="info-row">
                    <div class="info-label">Name:</div>
                    <div class="info-value">{{ $student->lastname }} {{ $student->firstname }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Index No:</div>
                    <div class="info-value">{{ $student->adm_no ?? ($student->index_no ?? '—') }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Level:</div>
                    <div class="info-value">{{ \App\Http\Controllers\Helper::recordMdname($student->senior) }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Term:</div>
                    <div class="info-value">{{ $exam->term }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Year:</div>
                    <div class="info-value">{{ $exam->academic_year }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Gender:</div>
                    <div class="info-value">{{ ucfirst($student->gender ?? '—') }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Position:</div>
                    <div class="info-value">
                        @if(is_numeric($classRank))
                            {{ $ord($classRank) }} of {{ $classTotal }} students
                        @else
                            —
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Marks Table -->
        <table class="marks-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Code</th>
                    <th>Subject</th>
                    <th>Score</th>
                    <th>Max</th>
                    <th>%</th>
                    <th>Grade</th>
                    <th>Remark</th>
                </tr>
            </thead>
            <tbody>
                @php $rn = 0; @endphp
                @foreach($subjectMarks as $sm)
                    @php
                        $rn++;
                        $subCode = $sm->subject_code ?? ('S-'.str_pad($sm->subject_id,3,'0',STR_PAD_LEFT));
                    @endphp
                    <tr>
                        <td>{{ $rn }}</td>
                        <td>{{ $subCode }}</td>
                        <td>{{ $sm->subject_name }}</td>
                        <td>{{ $sm->marks_obtained ?? '—' }}</td>
                        <td>{{ $sm->total_marks }}</td>
                        <td>{{ $sm->percentage }}%</td>
                        <td class="{{ $sm->grade === 'F' ? 'grade-fail' : 'grade-pass' }}">
                            {{ $sm->grade ?? '—' }}
                        </td>
                        <td>{{ $sm->grade_remark ?? '—' }}</td>
                    </tr>
                @endforeach
                <tr style="font-weight: 600; background: var(--light);">
                    <td colspan="3" style="text-align: right;">TOTAL MARK:</td>
                    <td>{{ number_format($totalObtained, 1) }}</td>
                    <td>{{ $totalMax }}</td>
                    <td>{{ $percentage }}%</td>
                    <td class="{{ $overallGrade === 'F' ? 'grade-fail' : 'grade-pass' }}">
                        {{ $overallGrade }}
                    </td>
                    <td>
                        <strong style="color: {{ $passed ? 'var(--pass)' : 'var(--fail)' }}">
                            {{ strtoupper($overallRemark) }}
                        </strong>
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Summary -->
        <div class="summary">
            <div class="summary-item">
                <div class="summary-label">Total Score</div>
                <div class="summary-value">{{ number_format($totalObtained, 1) }}/{{ $totalMax }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Average</div>
                <div class="summary-value" style="color: {{ $percentage >= 80 ? 'var(--pass)' : ($percentage >= $exam->pass_mark ? 'var(--accent)' : 'var(--fail)') }}">
                    {{ $percentage }}%
                </div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Grade</div>
                <div class="summary-value">{{ $overallGrade }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Result</div>
                <div class="summary-value" style="color: {{ $passed ? 'var(--pass)' : 'var(--fail)' }}">
                    {{ $passed ? 'PASS' : 'FAIL' }}
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div>Generated: {{ now()->format('d M Y, H:i') }}</div>
            <div>CONFIDENTIAL</div>
            <div>{{ $exam->exam_code }} • {{ $exam->term }} {{ $exam->academic_year }}</div>
        </div>
    </div>
</body>
</html>
</html>