<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Cumulative Analysis - {{ $className }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 9px;
            color: #222;
            margin: 0;
            padding: 14px;
        }

        .header {
            text-align: center;
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 2px solid #5351e4;
        }

        .header h1 {
            color: #2C29CA;
            font-size: 16px;
            margin: 0 0 3px;
        }

        .header p {
            color: #555;
            margin: 2px 0;
            font-size: 10px;
        }

        table.meta {
            width: 100%;
            margin-bottom: 10px;
            border-collapse: collapse;
        }

        table.meta td {
            font-size: 9px;
            padding: 2px 4px;
        }

        table.meta .label {
            font-weight: bold;
            color: #5351e4;
        }

        h3.section-title {
            color: #2C29CA;
            font-size: 11px;
            margin: 14px 0 6px;
            padding-bottom: 4px;
            border-bottom: 1px solid #ede9ff;
        }

        table.data {
            width: 100%;
            border-collapse: collapse;
        }

        table.data th {
            background: #ede9ff;
            color: #4b3fbf;
            font-size: 7.5px;
            text-transform: uppercase;
            padding: 4px 3px;
            border: 1px solid #d8d4ff;
            text-align: center;
        }

        table.data td {
            padding: 3px;
            border: 1px solid #eee;
            text-align: center;
            font-size: 8px;
        }

        table.data td.name {
            text-align: left;
            font-weight: bold;
        }

        table.data tfoot td {
            background: #f8f7ff;
            font-weight: bold;
        }

        .footer-note {
            margin-top: 10px;
            font-size: 7.5px;
            color: #888;
            text-align: right;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>{{ $schoolName }}</h1>
        <p>Cumulative Performance Analysis — {{ $academicYear }}</p>
        <p>
            Includes:
            {{ $selectedExams->map(fn($e) => str_replace('-', ' ', $e->exam_type) . ' (' . $e->term . ')')->implode(' | ') }}
        </p>
    </div>

    <table class="meta">
        <tr>
            <td class="label">Class:</td>
            <td>{{ $className }} — {{ $streamLabel }}</td>
            <td class="label">Students:</td>
            <td>{{ $report->count() }}</td>
            <td class="label">Cumulative Class Average:</td>
            <td>{{ $classCumulativeAverage !== null ? $classCumulativeAverage . '%' : '—' }}</td>
        </tr>
    </table>

    <h3 class="section-title">Cumulative Overview — Average % per Subject</h3>
    <table class="data">
        <thead>
            <tr>
                <th>#</th>
                <th style="text-align:left;">Student</th>
                <th>Gender</th>
                @foreach ($subjects as $subj)
                    <th>{{ Str::limit($subj->report_name, 8, '') }}</th>
                @endforeach
                <th>Cumulative Avg %</th>
                <th>Grade</th>
                <th>Rank</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($report as $row)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td class="name">{{ $row->student->firstname }} {{ $row->student->lastname }}</td>
                    <td>{{ $row->student->gender ?? '—' }}</td>
                    @foreach ($subjects as $subj)
                        @php $cell = $row->subjectAverages[$subj->report_key] ?? null; @endphp
                        <td>{{ $cell && $cell->average !== null ? $cell->average . '%' : '—' }}</td>
                    @endforeach
                    <td>{{ $row->cumulativeAverage !== null ? $row->cumulativeAverage . '%' : '—' }}</td>
                    <td>{{ $row->grade }}</td>
                    <td>{{ $row->rank ?? '—' }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3">Cumulative Subject Average</td>
                @foreach ($subjects as $subj)
                    @php $avg = $subjectCumulativeAverages[$subj->report_key] ?? null; @endphp
                    <td>{{ $avg !== null ? $avg . '%' : '—' }}</td>
                @endforeach
                <td colspan="3"></td>
            </tr>
        </tfoot>
    </table>

    @if ($subjectDetail)
        <h3 class="section-title">Subject Deep-Dive — {{ $subjectDetail['subject']->report_name }} (mark per exam, before average)</h3>
        <table class="data">
            <thead>
                <tr>
                    <th style="text-align:left;">Student</th>
                    @foreach ($selectedExams as $exam)
                        <th>{{ str_replace('-', ' ', $exam->exam_type) }} {{ $exam->term }}</th>
                    @endforeach
                    <th>Average %</th>
                    <th>Grade</th>
                    <th>Rank</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($subjectDetail['rows'] as $row)
                    <tr>
                        <td class="name">{{ $row->student->firstname }} {{ $row->student->lastname }}</td>
                        @foreach ($selectedExams as $exam)
                            @php $entry = $row->exams[$exam->id] ?? null; @endphp
                            <td>{{ $entry ? $entry->marks . '/' . $entry->total . ' (' . $entry->percentage . '%)' : '—' }}</td>
                        @endforeach
                        <td>{{ $row->average !== null ? $row->average . '%' : '—' }}</td>
                        <td>{{ $row->grade }}</td>
                        <td>{{ $row->rank ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <p class="footer-note">Generated on {{ $generatedAt }} — SMASA</p>
</body>

</html>
