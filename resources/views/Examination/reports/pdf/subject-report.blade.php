<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Subject Report - {{ $subjectRow->report_name ?? 'Subject' }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            color: #222;
            margin: 0;
            padding: 16px;
        }

        .header {
            text-align: center;
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 2px solid #5351e4;
        }

        .header h1 {
            color: #2C29CA;
            font-size: 17px;
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
            font-size: 9.5px;
            padding: 2px 4px;
        }

        table.meta .label {
            font-weight: bold;
            color: #5351e4;
        }

        table.stats {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }

        table.stats td {
            background: #f8f7ff;
            border: 1px solid #ede9ff;
            padding: 6px;
            text-align: center;
            font-size: 9px;
        }

        table.stats td strong {
            display: block;
            font-size: 13px;
            color: #2C29CA;
        }

        table.data {
            width: 100%;
            border-collapse: collapse;
        }

        table.data th {
            background: #ede9ff;
            color: #4b3fbf;
            font-size: 8px;
            text-transform: uppercase;
            padding: 5px 4px;
            border: 1px solid #d8d4ff;
        }

        table.data td {
            padding: 4px;
            border: 1px solid #eee;
            font-size: 9px;
        }

        table.data td.name {
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
        <p>Subject Performance Report — {{ $exam->exam_name }} ({{ $exam->exam_code }})</p>
        <p>{{ $exam->term }} • {{ $exam->academic_year }}</p>
    </div>

    <table class="meta">
        <tr>
            <td class="label">Class:</td>
            <td>{{ $className }} — {{ $streamLabel }}</td>
            <td class="label">Subject:</td>
            <td>{{ $subjectRow->report_name ?? '—' }}</td>
            <td class="label">Teacher:</td>
            <td>{{ $stats['teacher_name'] ?? '—' }}</td>
        </tr>
    </table>

    <table class="stats">
        <tr>
            <td>Students<br><strong>{{ $stats['total_students'] }}</strong></td>
            <td>Entered<br><strong>{{ $stats['entered_count'] }}</strong></td>
            <td>Average<br><strong>{{ $stats['average'] ?? '—' }}%</strong></td>
            <td>Highest<br><strong>{{ $stats['highest'] ?? '—' }}%</strong></td>
            <td>Lowest<br><strong>{{ $stats['lowest'] ?? '—' }}%</strong></td>
            <td>Pass Rate<br><strong>{{ $stats['pass_rate'] ?? 'N/A' }}{{ $stats['pass_rate'] !== null ? '%' : '' }}</strong></td>
        </tr>
    </table>

    <table class="data">
        <thead>
            <tr>
                <th>Rank</th>
                <th>Student</th>
                <th>Gender</th>
                <th>Marks</th>
                <th>%</th>
                <th>Grade</th>
                <th>Remark</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $row)
                <tr>
                    <td>{{ $row->rank ?? '—' }}</td>
                    <td class="name">{{ $row->student->firstname }} {{ $row->student->lastname }}</td>
                    <td>{{ $row->student->gender ?? '—' }}</td>
                    @if ($row->entered)
                        <td>{{ $row->marks }}/{{ $row->total }}</td>
                        <td>{{ $row->percentage }}%</td>
                        <td>{{ $row->grade }}</td>
                        <td>{{ $row->remark }}</td>
                    @else
                        <td colspan="4">Marks not entered</td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>

    <p class="footer-note">Generated on {{ $generatedAt }} — SMASA</p>
</body>

</html>
