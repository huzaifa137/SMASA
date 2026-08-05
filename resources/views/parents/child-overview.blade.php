@extends('parents.layout')

@section('title', $student->firstname . ' ' . $student->lastname)

@section('content')
    @include('parents.partials.child-nav', ['active' => 'overview'])

    <div class="pp-stat-grid mb-3" style="margin-bottom:1.25rem;">
        <div class="pp-stat success">
            <div class="pp-stat-label">Present</div>
            <div class="pp-stat-value">{{ $attendanceSummary->get('present', 0) }}</div>
        </div>
        <div class="pp-stat warning">
            <div class="pp-stat-label">Late</div>
            <div class="pp-stat-value">{{ $attendanceSummary->get('late', 0) }}</div>
        </div>
        <div class="pp-stat danger">
            <div class="pp-stat-label">Absent</div>
            <div class="pp-stat-value">{{ $attendanceSummary->get('absent', 0) }}</div>
        </div>
        <div class="pp-stat">
            <div class="pp-stat-label">Released Results</div>
            <div class="pp-stat-value">{{ $releasedResultsCount }}</div>
        </div>
    </div>

    <div class="pp-card">
        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:0.75rem;">
            <div>
                <div style="font-weight:700;margin-bottom:0.2rem;"><i class="fas fa-wallet me-1" style="color:var(--brand);"></i> Fees at a glance</div>
                <div style="font-size:0.82rem;color:var(--gray-500);">
                    Total charged: <strong>{{ number_format($feeSummary->charged ?? 0) }}</strong>
                    &bull; Outstanding balance:
                    <strong style="color: {{ ($feeSummary->owing ?? 0) > 0 ? 'var(--danger)' : 'var(--success)' }};">
                        {{ number_format($feeSummary->owing ?? 0) }}
                    </strong>
                </div>
            </div>
            <a href="{{ route('parents.finance', $student->id) }}" class="pp-btn pp-btn-primary">
                View Full Statement <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>

    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1rem;">
        <a href="{{ route('parents.results', $student->id) }}" class="pp-card" style="text-decoration:none;color:inherit;display:block;">
            <i class="fas fa-file-lines" style="font-size:1.4rem;color:var(--brand);"></i>
            <div style="font-weight:700;margin-top:0.5rem;">Exam Results</div>
            <div style="font-size:0.8rem;color:var(--gray-500);">View released report cards</div>
        </a>
        <a href="{{ route('parents.attendance', $student->id) }}" class="pp-card" style="text-decoration:none;color:inherit;display:block;">
            <i class="fas fa-calendar-check" style="font-size:1.4rem;color:var(--brand);"></i>
            <div style="font-weight:700;margin-top:0.5rem;">Attendance</div>
            <div style="font-size:0.8rem;color:var(--gray-500);">Daily attendance record</div>
        </a>
        <a href="{{ route('parents.finance', $student->id) }}" class="pp-card" style="text-decoration:none;color:inherit;display:block;">
            <i class="fas fa-wallet" style="font-size:1.4rem;color:var(--brand);"></i>
            <div style="font-weight:700;margin-top:0.5rem;">Fee Statement</div>
            <div style="font-size:0.8rem;color:var(--gray-500);">Charges, payments &amp; balance</div>
        </a>
    </div>
@endsection
