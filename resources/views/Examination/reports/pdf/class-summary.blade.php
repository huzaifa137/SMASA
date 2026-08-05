<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Class Summary - {{ $className }}</title>
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
        <p>Class Performance Summary — {{ $exam->exam_name }} ({{ $exam->exam_code }})</p>
        <p>{{ $exam->term }} • {{ $exam->academic_year }}</p>
    </div>

    <table class="meta">
        <tr>
            <td class="label">Class:</td>
            <td>{{ $className }} — {{ $streamLabel }}</td>
            <td class="label">Students:</td>
            <td>{{ $report->count() }}</td>
            <td class="label">Class Average:</td>
            <td>{{ $classAverage }}%</td>
        </tr>
    </table>

    <table class="data">
        <thead>
            <tr>
                <th>#</th>
                <th style="text-align:left;">Student</th>
                <th>Gender</th>
                @foreach ($subjects as $subj)
                    <th>{{ Str::limit($subj->report_name, 8, '') }}</th>
                @endforeach
                <th>Total</th>
                <th>Avg %</th>
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
                        @php $cell = $row->cells[$subj->report_key] ?? null; @endphp
                        <td>{{ $cell ? $cell->marks . '/' . $cell->total : '—' }}</td>
                    @endforeach
                    <td>{{ $row->total_obtained }}/{{ $row->total_max }}</td>
                    <td>{{ $row->average }}%</td>
                    <td>{{ $row->grade }}</td>
                    <td>{{ $row->rank ?? '—' }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3">Subject Average</td>
                @foreach ($subjects as $subj)
                    @php $avg = $subjectAverages[$subj->report_key] ?? null; @endphp
                    <td>{{ $avg && $avg['average'] !== null ? $avg['average'] . '%' : '—' }}</td>
                @endforeach
                <td colspan="4"></td>
            </tr>
        </tfoot>
    </table>

    <p class="footer-note">Generated on {{ $generatedAt }} — SMASA</p>
</body>

</html>