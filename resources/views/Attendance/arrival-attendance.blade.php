{{-- resources/views/Attendance/arrival-attendance.blade.php --}}
<?php use App\Http\Controllers\Helper; ?>
@extends('layouts-side-bar.master')

@section('css')
    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --b: #2f2ccb;
            --bl: rgba(47, 44, 203, .1);
            --g: #059669;
            --gl: rgba(5, 150, 105, .1);
            --r: #dc2626;
            --rl: rgba(220, 38, 38, .1);
            --a: #d97706;
            --al: rgba(217, 119, 6, .1);
            --p: #7c3aed;
            --pl: rgba(124, 58, 237, .1);
            --c: #0891b2;
            --cl: rgba(8, 145, 178, .1);
            --surf: #fff;
            --bg: #f0f4f8;
            --brd: #e2e8f0;
            --t1: #0f172a;
            --t2: #475569;
            --t3: #94a3b8;
            --rad: 16px;
            --sh: 0 1px 3px rgba(0, 0, 0, .06), 0 4px 16px rgba(0, 0, 0, .05);
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box
        }

        *:not(i):not([class*="fa"]) {}

        body {
            background: var(--bg)
        }

        .arr-hero {
            background: linear-gradient(135deg, #0d0b5e 0%, #2f2ccb 50%, #5b58e0 100%);
            border-radius: 24px;
            padding: 2rem 2.5rem;
            margin-bottom: 1.75rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .hero-title {
            color: #fff;
            font-size: 1.6rem;
            font-weight: 700;
            margin: 0 0 .3rem
        }

        .hero-sub {
            color: rgba(255, 255, 255, .7);
            font-size: .9rem;
            margin: 0
        }

        .hero-badge {
            background: rgba(255, 255, 255, .15);
            color: #fff;
            padding: .35rem .8rem;
            border-radius: 20px;
            font-size: .8rem;
            font-weight: 600
        }

        .panel {
            background: var(--surf);
            border-radius: var(--rad);
            box-shadow: var(--sh);
            padding: 1.5rem;
            margin-bottom: 1.25rem
        }

        .panel-title {
            font-size: .95rem;
            font-weight: 700;
            color: var(--t1);
            margin-bottom: 1.25rem;
            display: flex;
            align-items: center;
            gap: .5rem
        }

        .panel-title i {
            color: var(--b)
        }

        /* Stats row */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
            gap: .75rem;
            margin-bottom: 1.25rem
        }

        .stat-box {
            background: var(--surf);
            border-radius: 12px;
            padding: 1rem;
            text-align: center;
            border: 1.5px solid var(--brd);
            box-shadow: var(--sh)
        }

        .stat-box .sv {
            font-size: 2rem;
            font-weight: 800;
            line-height: 1
        }

        .stat-box .sl {
            font-size: .75rem;
            font-weight: 600;
            color: var(--t2);
            margin-top: .3rem;
            text-transform: uppercase;
            letter-spacing: .04em
        }

        .stat-box.s-present {
            border-color: var(--g)
        }

        .stat-box.s-present .sv {
            color: var(--g)
        }

        .stat-box.s-late {
            border-color: var(--a)
        }

        .stat-box.s-late .sv {
            color: var(--a)
        }

        .stat-box.s-absent {
            border-color: var(--r)
        }

        .stat-box.s-absent .sv {
            color: var(--r)
        }

        .stat-box.s-excused {
            border-color: var(--c)
        }

        .stat-box.s-excused .sv {
            color: var(--c)
        }

        .stat-box.s-half {
            border-color: var(--p)
        }

        .stat-box.s-half .sv {
            color: var(--p)
        }

        .stat-box.s-total {
            border-color: var(--b)
        }

        .stat-box.s-total .sv {
            color: var(--b)
        }

        .stat-box.s-card {
            border-color: var(--b)
        }

        .stat-box.s-card .sv {
            color: var(--b)
        }

        /* Filter bar */
        .filter-bar {
            display: flex;
            flex-wrap: wrap;
            gap: .65rem;
            align-items: center;
            margin-bottom: 1.25rem
        }

        .filter-bar select,
        .filter-bar input[type=date] {
            padding: .5rem .85rem;
            border: 1.5px solid var(--brd);
            border-radius: 8px;
            font-size: .85rem;
            color: var(--t1);
            background: var(--bg);
        }

        .filter-bar select:focus,
        .filter-bar input:focus {
            outline: none;
            border-color: var(--b)
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            padding: .5rem 1rem;
            border-radius: 8px;
            font-size: .85rem;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: .15s;
            text-decoration: none
        }

        .btn-primary {
            background: var(--b);
            color: #fff
        }

        .btn-primary:hover {
            background: #2420a8
        }

        .btn-success {
            background: var(--g);
            color: #fff
        }

        .btn-success:hover {
            opacity: .88
        }

        .btn-outline {
            background: transparent;
            border: 1.5px solid var(--brd);
            color: var(--t2)
        }

        .btn-outline:hover {
            border-color: var(--b);
            color: var(--b)
        }

        .btn-sm {
            padding: .3rem .7rem;
            font-size: .78rem
        }

        /* Toggle tabs */
        .type-tabs {
            display: flex;
            gap: .35rem;
            margin-bottom: 1.25rem
        }

        .type-tab {
            padding: .45rem 1.1rem;
            border-radius: 8px;
            font-size: .85rem;
            font-weight: 600;
            cursor: pointer;
            border: 1.5px solid var(--brd);
            color: var(--t2);
            background: var(--surf);
            transition: .15s;
            text-decoration: none
        }

        .type-tab.active {
            background: var(--b);
            border-color: var(--b);
            color: #fff
        }

        /* Table */
        .att-table-wrap {
            overflow-x: auto;
            border-radius: 12px;
            border: 1px solid var(--brd)
        }

        table.att-table {
            width: 100%;
            border-collapse: collapse;
            font-size: .83rem
        }

        table.att-table thead th {
            background: #2c29ca;
            color: #fff;
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .05em;
            padding: .65rem 1rem;
            border-bottom: none;
            white-space: nowrap;
        }

        table.att-table tbody td {
            padding: .7rem 1rem;
            border-bottom: 1px solid var(--brd);
            color: var(--t1);
            vertical-align: middle
        }

        table.att-table tbody tr:last-child td {
            border-bottom: none
        }

        table.att-table tbody tr:hover {
            background: #f8fafc
        }

        .badge {
            display: inline-block;
            padding: .2rem .55rem;
            border-radius: 6px;
            font-size: .72rem;
            font-weight: 700
        }

        .badge-success {
            background: var(--gl);
            color: var(--g)
        }

        .badge-danger {
            background: var(--rl);
            color: var(--r)
        }

        .badge-warning {
            background: var(--al);
            color: var(--a)
        }

        .badge-info {
            background: var(--cl);
            color: var(--c)
        }

        .badge-purple {
            background: var(--pl);
            color: var(--p)
        }

        .badge-secondary {
            background: #f1f5f9;
            color: var(--t2)
        }

        .method-tag {
            font-size: .7rem;
            padding: .1rem .4rem;
            border-radius: 4px;
            font-weight: 600
        }

        .method-card {
            background: var(--bl);
            color: var(--b)
        }

        .method-manual {
            background: #f1f5f9;
            color: var(--t2)
        }

        .time-mono {
            font-family: 'DM Mono', monospace;
            font-size: .82rem;
            color: var(--t1)
        }

        .empty-state {
            text-align: center;
            padding: 3.5rem 1rem;
            color: var(--t3)
        }

        .empty-state i {
            font-size: 3rem;
            opacity: .3;
            display: block;
            margin-bottom: .75rem
        }

        /* Trend mini-bar */
        .trend-wrap {
            display: flex;
            align-items: flex-end;
            gap: 4px;
            height: 40px
        }

        .trend-bar {
            flex: 1;
            background: var(--bl);
            border-radius: 3px 3px 0 0;
            position: relative;
            min-height: 4px
        }

        .trend-bar .tb-p {
            background: var(--b);
            border-radius: 3px 3px 0 0;
            width: 100%;
            position: absolute;
            bottom: 0;
            transition: .3s
        }

        .trend-bar .tb-l {
            background: var(--a);
            position: absolute;
            bottom: 0;
            width: 100%;
            border-radius: 3px 3px 0 0;
            z-index: 1
        }

        .trend-label {
            font-size: .65rem;
            color: var(--t3);
            text-align: center;
            margin-top: .2rem
        }

        /* Manual add modal */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, .4);
            z-index: 1000;
            align-items: center;
            justify-content: center
        }

        .modal-overlay.show {
            display: flex
        }

        .modal-box {
            background: var(--surf);
            border-radius: var(--rad);
            width: 100%;
            max-width: 480px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, .2);
            overflow: hidden
        }

        .modal-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--brd);
            display: flex;
            align-items: center;
            justify-content: space-between
        }

        .modal-header h5 {
            font-size: 1rem;
            font-weight: 700;
            color: var(--t1);
            margin: 0
        }

        .modal-body {
            padding: 1.5rem
        }

        .modal-footer {
            padding: 1rem 1.5rem;
            border-top: 1px solid var(--brd);
            display: flex;
            justify-content: flex-end;
            gap: .5rem
        }

        .form-group {
            margin-bottom: 1rem
        }

        .form-label {
            font-size: .82rem;
            font-weight: 700;
            color: var(--t2);
            display: block;
            margin-bottom: .3rem
        }

        .form-control {
            width: 100%;
            padding: .55rem .85rem;
            border: 1.5px solid var(--brd);
            border-radius: 8px;
            font-size: .88rem;
            color: var(--t1);
            background: var(--bg);
            transition: .15s;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--b);
            background: #fff
        }

        .close-btn {
            background: none;
            border: none;
            font-size: 1.2rem;
            cursor: pointer;
            color: var(--t3);
            padding: .2rem
        }

        .close-btn:hover {
            color: var(--t1)
        }

        /* Time display notice */
        .time-notice {
            background: var(--bl);
            border: 1px solid var(--b);
            border-radius: 8px;
            padding: .6rem .9rem;
            font-size: .82rem;
            color: var(--b);
            font-weight: 600;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: .5rem
        }

        .method-tag i {
    margin-right: 0.25rem;
    font-size: 0.7rem;
}

    </style>
@endsection

@section('content')
    <div class="container-fluid mt-5" style="max-width:1400px;">

        {{-- Hero --}}
        <div class="arr-hero">
            <div>
                <h2 class="hero-title"><i class="fas fa-school me-2"></i> School Arrival Attendance</h2>
                <p class="hero-sub">Track when students & teachers arrive at school — via card scan or manual entry.</p>
            </div>
            <div style="display:flex;gap:.5rem;flex-wrap:wrap;align-items:center">
                <span class="hero-badge"><i class="fas fa-calendar me-1"></i>
                    {{ \Carbon\Carbon::parse($date)->format('d M Y') }}</span>
                <a href="{{ route('card-scan.hub') }}" class="btn btn-outline"
                    style="background:rgba(255,255,255,.15);color:#fff;border-color:rgba(255,255,255,.3)">
                    <i class="fas fa-qrcode"></i> Scan Hub
                </a>
                <a href="{{ route('card-scan.arrival.report') }}" class="btn btn-outline"
                    style="background:rgba(255,255,255,.15);color:#fff;border-color:rgba(255,255,255,.3)">
                    <i class="fas fa-chart-bar"></i> Reports
                </a>
                <!-- <button class="btn btn-success btn-sm" onclick="openAddModal()">
                    <i class="fas fa-plus"></i> Add Manually
                </button> -->
            </div>
        </div>

        {{-- Stats --}}
        <div class="stats-row">
            <div class="stat-box s-total">
                <div class="sv">{{ $stats->total ?? 0 }}</div>
                <div class="sl">Recorded</div>
            </div>
            <div class="stat-box s-present">
                <div class="sv">{{ $stats->present ?? 0 }}</div>
                <div class="sl">Present</div>
            </div>
            <div class="stat-box s-late">
                <div class="sv">{{ $stats->late ?? 0 }}</div>
                <div class="sl">Late</div>
            </div>
            <div class="stat-box s-absent">
                <div class="sv">{{ ($totalEnrolled - ($stats->total ?? 0)) }}</div>
                <div class="sl">Not Arrived</div>
            </div>
            <div class="stat-box s-excused">
                <div class="sv">{{ $stats->excused ?? 0 }}</div>
                <div class="sl">Excused</div>
            </div>
            <div class="stat-box s-half">
                <div class="sv">{{ $stats->half_day ?? 0 }}</div>
                <div class="sl">Half Day</div>
            </div>
            <div class="stat-box s-card">
                <div class="sv">{{ $stats->card_scans ?? 0 }}</div>
                <div class="sl"><i class="fas fa-id-card" style="font-size:.7rem"></i> Via Card</div>
            </div>
        </div>

        {{-- 7-Day Trend --}}
        @if($trend->count())
            <div class="panel">
                <div class="panel-title"><i class="fas fa-chart-line"></i> 7-Day Arrival Trend</div>
                <div style="display:flex;align-items:flex-end;gap:1.5rem">
                    @php $maxTotal = $trend->max('total') ?: 1; @endphp
                    @foreach($trend as $t)
                        <div style="flex:1;text-align:center">
                            <div style="font-size:.72rem;font-weight:700;color:var(--t2);margin-bottom:.25rem">
                                {{ $t->present + $t->late }}<br>
                                <span style="color:var(--t3);font-weight:400">/ {{ $t->total }}</span>
                            </div>
                            <div class="trend-wrap">
                                <div class="trend-bar" style="height:100%">
                                    @php $pct = $maxTotal > 0 ? ($t->present / $maxTotal * 100) : 0;
                                    $lpct = $maxTotal > 0 ? (($t->late ?? 0) / $maxTotal * 100) : 0; @endphp
                                    <div class="tb-p" style="height:{{ $pct }}%"></div>
                                </div>
                            </div>
                            <div class="trend-label">{{ \Carbon\Carbon::parse($t->attendance_date)->format('D') }}</div>
                        </div>
                    @endforeach
                    <div style="font-size:.72rem;color:var(--t3);padding-bottom:.5rem;line-height:1.6">
                        <div><span
                                style="display:inline-block;width:10px;height:10px;background:var(--b);border-radius:2px;margin-right:.3rem"></span>Present
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Filters & Table --}}
        <div class="panel">
            <div class="panel-title"><i class="fas fa-users"></i> Attendance Records</div>

            {{-- Type Toggle --}}
            <div class="type-tabs">
                <a href="{{ route('card-scan.arrival', ['date' => $date, 'person_type' => 'student']) }}"
                    class="type-tab {{ $personType === 'student' ? 'active' : '' }}">
                    <i class="fas fa-user-graduate"></i> Students
                </a>
                <a href="{{ route('card-scan.arrival', ['date' => $date, 'person_type' => 'teacher']) }}"
                    class="type-tab {{ $personType === 'teacher' ? 'active' : '' }}">
                    <i class="fas fa-chalkboard-teacher"></i> Teachers
                </a>
            </div>

            {{-- Date filter --}}
            <form method="GET" action="{{ route('card-scan.arrival') }}" class="filter-bar">
                <input type="hidden" name="person_type" value="{{ $personType }}">
                <input type="date" name="date" value="{{ $date }}">
                <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i> Go</button>
                <a href="{{ route('card-scan.arrival', ['person_type' => $personType]) }}"
                    class="btn btn-outline btn-sm">Today</a>
            </form>

            @if($records->isEmpty())
                <div class="empty-state">
                    <i class="fas fa-calendar-times"></i>
                    No arrival records for {{ \Carbon\Carbon::parse($date)->format('l, d M Y') }} ({{ $personType }}s).
                    <br><small style="font-size:.8rem">Records are added via card scan or the "Add Manually" button.</small>
                </div>
            @else
                <div class="att-table-wrap">
                    <table class="att-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                @if($personType === 'student')
                                <th>Class</th> @else <th>Contact</th> @endif
                                <th>Arrival Time</th>
                                <th>Status</th>
                                <th>Method</th>
                                <th>Remarks</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($records as $i => $r)
                                        <tr>
                                            <td style="color:var(--t3);font-size:.75rem">{{ $i + 1 }}</td>
                                            <td>
                                                <div style="font-weight:600;color:var(--t1)">{{ $r->person_name }}</div>
                                                @if($r->card_number)
                                                    <div style="font-size:.7rem;color:var(--t3);font-family:'DM Mono',monospace">
                                                        {{ $r->card_number }}</div>
                                                @endif
                                            </td>
                                            <td style="color:var(--t2);font-size:.82rem">{{ $r->extra ?? '—' }}</td>
                                            <td><span
                                                    class="time-mono">{{ \Carbon\Carbon::parse($r->arrival_time)->format('h:i A') }}</span>
                                            </td>
                                            <td>
                                @php
                                    $badgeCls = match ($r->status) {
                                        'present' => 'badge-success',
                                        'late' => 'badge-warning',
                                        'absent' => 'badge-danger',
                                        'half_day' => 'badge-purple',
                                        'excused' => 'badge-info',
                                        default => 'badge-secondary',
                                    };
                                @endphp
                                                <span class="badge {{ $badgeCls }}">{{ strtoupper(str_replace('_', ' ', $r->status)) }}</span>
                                            </td>
                                            <td>
                                               <span class="method-tag {{ $r->method === 'card_scan' ? 'method-card' : 'method-manual' }}">
    <i class="{{ $r->method === 'card_scan' ? 'fas fa-id-card' : 'fas fa-pencil-alt' }}" style="margin-right: .25rem;"></i>
    {{ $r->method === 'card_scan' ? 'Card' : 'Manual' }}
</span>
                                            </td>
                                            <td style="color:var(--t2);font-size:.82rem">{{ $r->remarks ?: '—' }}</td>
                                            <td>
                                                <button class="btn btn-outline btn-sm"
                                                    onclick="openEditModal({{ $r->person_id }}, '{{ $r->status }}', '{{ $r->remarks }}', '{{ $personType }}')">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            </td>
                                        </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

    </div>

    {{-- Add/Edit Manual Attendance Modal --}}
    <div class="modal-overlay" id="addModal">
        <div class="modal-box">
            <div class="modal-header">
                <h5><i class="fas fa-user-check me-2" style="color:var(--b)"></i> <span id="modalTitle">Add Arrival
                        Attendance</span></h5>
                <button class="close-btn" onclick="closeModal()"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <div class="time-notice">
                    <i class="fas fa-clock"></i>
                    Arrival time will be set automatically to <strong id="currentTimeDisplay">—</strong> (current time).
                </div>
                <form id="arrivalForm">
                    @csrf
                    <input type="hidden" id="m_person_id" name="person_id">
                    <input type="hidden" id="m_person_type" name="person_type" value="{{ $personType }}">
                    <input type="hidden" name="attendance_date" value="{{ $date }}">

                    <div class="form-group">
                        <label class="form-label">Search {{ ucfirst($personType) }}</label>
                        <input type="text" class="form-control" id="personSearch" placeholder="Type name…"
                            oninput="searchPerson(this.value)" autocomplete="off">
                        <div id="personResults"
                            style="background:#fff;border:1.5px solid var(--brd);border-radius:8px;margin-top:.3rem;max-height:180px;overflow-y:auto;display:none">
                        </div>
                        <div id="selectedPerson" style="margin-top:.5rem;font-size:.85rem;font-weight:600;color:var(--g)">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <select class="form-control" id="m_status" name="status">
                            <option value="present">Present</option>
                            <option value="late">Late</option>
                            <option value="absent">Absent</option>
                            <option value="half_day">Half Day</option>
                            <option value="excused">Excused</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Remarks (Optional)</label>
                        <input type="text" class="form-control" id="m_remarks" name="remarks" placeholder="Any notes…">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline" onclick="closeModal()">Cancel</button>
                <button class="btn btn-primary" onclick="saveArrival()"><i class="fas fa-save"></i> Save</button>
            </div>
        </div>
    </div>
    </div>
    </div>
    </div>
@endsection

@section('js')
    <script>
        let selectedPersonId = null;
        let personSearchTimer = null;
        const csrfToken = '{{ csrf_token() }}';
        const personType = '{{ $personType }}';

        // Update clock display
        function updateClock() {
            const now = new Date();
            document.getElementById('currentTimeDisplay').textContent = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', hour12: true });
        }
        updateClock();
        setInterval(updateClock, 1000);

        function openAddModal() {
            selectedPersonId = null;
            document.getElementById('modalTitle').textContent = 'Add Arrival Attendance';
            document.getElementById('personSearch').value = '';
            document.getElementById('selectedPerson').textContent = '';
            document.getElementById('m_person_id').value = '';
            document.getElementById('m_status').value = 'present';
            document.getElementById('m_remarks').value = '';
            document.getElementById('personResults').style.display = 'none';
            document.getElementById('addModal').classList.add('show');
        }

        function openEditModal(personId, status, remarks, pType) {
            selectedPersonId = personId;
            document.getElementById('modalTitle').textContent = 'Edit Arrival Attendance';
            document.getElementById('m_person_id').value = personId;
            document.getElementById('m_status').value = status;
            document.getElementById('m_remarks').value = remarks || '';
            document.getElementById('personSearch').value = '(editing existing record)';
            document.getElementById('personSearch').disabled = true;
            document.getElementById('selectedPerson').textContent = '✓ Record selected';
            document.getElementById('personResults').style.display = 'none';
            document.getElementById('addModal').classList.add('show');
        }

        function closeModal() {
            document.getElementById('addModal').classList.remove('show');
            document.getElementById('personSearch').disabled = false;
        }

        function searchPerson(q) {
            clearTimeout(personSearchTimer);
            const results = document.getElementById('personResults');
            if (q.length < 2) { results.style.display = 'none'; return; }
            personSearchTimer = setTimeout(() => {
                fetch(`/attendance/arrival/search-person?q=${encodeURIComponent(q)}&type=${personType}&school_id={{ session("LoggedSchool") }}`)
                    .then(r => r.json())
                    .then(people => {
                        if (!people.length) {
                            results.innerHTML = '<div style="padding:.6rem 1rem;font-size:.82rem;color:var(--t3)">No results found.</div>';
                        } else {
                            results.innerHTML = people.map(p => `
                                <div onclick="selectPerson(${p.id}, '${p.name.replace(/'/g, "\\'")}' )"
                                    style="padding:.6rem 1rem;font-size:.85rem;cursor:pointer;border-bottom:1px solid var(--brd)"
                                    onmouseover="this.style.background='var(--bl)'" onmouseout="this.style.background=''">
                                    <strong>${p.name}</strong>
                                    <span style="color:var(--t3);font-size:.75rem;margin-left:.4rem">${p.extra || ''}</span>
                                </div>`).join('');
                        }
                        results.style.display = 'block';
                    });
            }, 300);
        }

        function selectPerson(id, name) {
            selectedPersonId = id;
            document.getElementById('m_person_id').value = id;
            document.getElementById('personSearch').value = name;
            document.getElementById('selectedPerson').textContent = '✓ Selected: ' + name;
            document.getElementById('personResults').style.display = 'none';
        }

        function saveArrival() {
            if (!document.getElementById('m_person_id').value) {
                alert('Please search and select a person first.');
                return;
            }
            const formData = {
                person_id: document.getElementById('m_person_id').value,
                person_type: document.getElementById('m_person_type').value,
                attendance_date: '{{ $date }}',
                status: document.getElementById('m_status').value,
                remarks: document.getElementById('m_remarks').value,
            };
            fetch('{{ route("card-scan.arrival.save") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify(formData)
            })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        closeModal();
                        location.reload();
                    } else {
                        alert('Error: ' + (res.message || 'Could not save.'));
                    }
                });
        }
    </script>
@endsection