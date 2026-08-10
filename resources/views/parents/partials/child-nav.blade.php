{{-- Expects: $student, $active ('overview'|'results'|'attendance'|'finance') --}}

<div class="pp-card" style="display:flex; align-items:center; gap:1rem; flex-wrap:wrap;">
    <div style="width:52px;height:52px;border-radius:50%;background:var(--brand);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:1.1rem;flex-shrink:0;">
        {{ strtoupper(substr($student->firstname, 0, 1) . substr($student->lastname, 0, 1)) }}
    </div>
    <div style="flex:1;min-width:180px;">
        <div style="font-weight:700;font-size:1.1rem;">{{ $student->firstname }} {{ $student->lastname }}</div>
        <div style="font-size:0.8rem;color:var(--gray-500);">
            {{ $student->admission_number }} &bull; {{ $student->class_name }}
            @if ($student->stream_name) — {{ $student->stream_name }} @endif
            &bull; {{ $student->school_name }}
        </div>
    </div>
</div>

<div class="pp-tabs">
    <a href="{{ route('parents.child', $student->id) }}" class="pp-tab {{ $active === 'overview' ? 'active' : '' }}">
        <i class="fas fa-gauge me-1"></i> Overview
    </a>
    <a href="{{ route('parents.results', $student->id) }}" class="pp-tab {{ $active === 'results' ? 'active' : '' }}">
        <i class="fas fa-file-lines me-1"></i> Results
    </a>
    <a href="{{ route('parents.attendance', $student->id) }}" class="pp-tab {{ $active === 'attendance' ? 'active' : '' }}">
        <i class="fas fa-calendar-check me-1"></i> Attendance
    </a>
    <a href="{{ route('parents.finance', $student->id) }}" class="pp-tab {{ $active === 'finance' ? 'active' : '' }}">
        <i class="fas fa-wallet me-1"></i> Fees
    </a>
</div>
