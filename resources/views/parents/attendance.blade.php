@extends('parents.layout')

@section('title', $student->firstname . ' — Attendance')

@section('content')
    @include('parents.partials.child-nav', ['active' => 'attendance'])

    <div class="pp-card">
        <form method="GET" style="display:flex;align-items:center;gap:0.75rem;flex-wrap:wrap;margin-bottom:1.25rem;">
            <label style="font-size:0.75rem;font-weight:700;text-transform:uppercase;color:var(--gray-500);">Month</label>
            <select name="month" onchange="this.form.submit()" style="border:1px solid var(--brand-pale);border-radius:0.5rem;padding:0.5rem 0.7rem;font-size:0.85rem;">
                @if ($availableMonths->isEmpty())
                    <option value="{{ $month }}">{{ \Carbon\Carbon::parse($month . '-01')->format('F Y') }}</option>
                @else
                    @foreach ($availableMonths as $m)
                        <option value="{{ $m }}" {{ $m === $month ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::parse($m . '-01')->format('F Y') }}
                        </option>
                    @endforeach
                @endif
            </select>
        </form>

        <div class="pp-stat-grid" style="margin-bottom:1.25rem;">
            <div class="pp-stat success">
                <div class="pp-stat-label">Present</div>
                <div class="pp-stat-value">{{ $summary->get('present', 0) }}</div>
            </div>
            <div class="pp-stat warning">
                <div class="pp-stat-label">Late</div>
                <div class="pp-stat-value">{{ $summary->get('late', 0) }}</div>
            </div>
            <div class="pp-stat danger">
                <div class="pp-stat-label">Absent</div>
                <div class="pp-stat-value">{{ $summary->get('absent', 0) }}</div>
            </div>
            <div class="pp-stat">
                <div class="pp-stat-label">Excused</div>
                <div class="pp-stat-value">{{ $summary->get('excused', 0) }}</div>
            </div>
            <div class="pp-stat" style="border-left-color: {{ $attendanceRate !== null && $attendanceRate < 80 ? 'var(--danger)' : 'var(--success)' }};">
                <div class="pp-stat-label">Attendance Rate</div>
                <div class="pp-stat-value">{{ $attendanceRate !== null ? $attendanceRate . '%' : '—' }}</div>
            </div>
        </div>

        @if ($records->isEmpty())
            <div class="pp-empty">
                <i class="fas fa-calendar-xmark"></i>
                <h6>No attendance recorded for this month</h6>
            </div>
        @else
            <div style="overflow-x:auto;">
                <table class="pp-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Arrival Time</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($records as $record)
                            @php
                                $badgeColor = match($record->status) {
                                    'present' => 'var(--success)',
                                    'late' => 'var(--warning)',
                                    'absent' => 'var(--danger)',
                                    'excused' => 'var(--brand-mid)',
                                    default => 'var(--gray-500)',
                                };
                            @endphp
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($record->attendance_date)->format('D, d M Y') }}</td>
                                <td>
                                    <span style="display:inline-block;padding:0.15rem 0.6rem;border-radius:99px;font-size:0.72rem;font-weight:700;color:#fff;background:{{ $badgeColor }};">
                                        {{ \App\Models\StudentAttendance::statusLabel($record->status) }}
                                    </span>
                                </td>
                                <td>{{ $record->arrival_time ?? '—' }}</td>
                                <td>{{ $record->remarks ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
