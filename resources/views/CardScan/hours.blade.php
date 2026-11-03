{{-- resources/views/CardScan/hours.blade.php --}}
<?php use App\Helpers\PermissionHelper; ?>
@extends('layouts-side-bar.master')

@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .ah-wrap { max-width: 980px; margin: 0 auto; }
        .ah-hero { background: linear-gradient(135deg, #2f2ccb, #5351e4); color: #fff; border-radius: 16px; padding: 1.4rem 1.6rem; margin-bottom: 1.25rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: .75rem; }
        .ah-hero h2 { margin: 0; font-size: 1.35rem; font-weight: 700; color: #fff; }
        .ah-hero p { margin: .25rem 0 0; opacity: .85; font-size: .9rem; }
        .ah-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 1.25rem 1.4rem; margin-bottom: 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,.05); }
        .ah-card h3 { font-size: 1rem; font-weight: 700; margin: 0 0 .25rem; color: #0f172a; }
        .ah-card .sub { color: #64748b; font-size: .85rem; margin-bottom: 1rem; }
        .ah-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 1rem; }
        .ah-field label { display: block; font-size: .78rem; font-weight: 600; color: #475569; margin-bottom: .3rem; }
        .ah-field input[type=time], .ah-field input[type=number] { width: 100%; padding: .55rem .7rem; border: 1.5px solid #e2e8f0; border-radius: 10px; font-size: .95rem; }
        .ah-field input:focus { outline: none; border-color: #2f2ccb; }
        .ah-field small { color: #94a3b8; font-size: .72rem; }
        .ah-switch { display: flex; align-items: center; gap: .6rem; padding: .6rem .8rem; border: 1.5px solid #e2e8f0; border-radius: 10px; cursor: pointer; font-size: .88rem; font-weight: 600; color: #334155; }
        .ah-switch input { width: 18px; height: 18px; }
        .ah-btn { background: #2f2ccb; color: #fff; border: 0; border-radius: 10px; padding: .7rem 1.4rem; font-weight: 700; cursor: pointer; }
        .ah-btn:hover { background: #2421a8; }
        .ah-link { color: #fff; background: rgba(255,255,255,.18); border: 1px solid rgba(255,255,255,.3); border-radius: 10px; padding: .5rem .9rem; text-decoration: none; font-size: .85rem; }
        .ah-alert { padding: .7rem 1rem; border-radius: 10px; margin-bottom: 1rem; font-size: .9rem; }
        .ah-alert.ok { background: rgba(5,150,105,.1); color: #047857; }
        .ah-alert.err { background: rgba(220,38,38,.08); color: #b91c1c; }
        .ah-stats { display: flex; gap: .75rem; flex-wrap: wrap; margin-bottom: 1rem; }
        .ah-stat { padding: .5rem .9rem; border-radius: 10px; font-size: .85rem; font-weight: 700; }
        .ah-stat.ok { background: rgba(5,150,105,.1); color: #047857; }
        .ah-stat.bad { background: rgba(220,38,38,.08); color: #b91c1c; }
        table.ah-tbl { width: 100%; border-collapse: collapse; font-size: .83rem; }
        table.ah-tbl th { text-align: left; color: #64748b; font-weight: 600; padding: .45rem .5rem; border-bottom: 1px solid #e2e8f0; }
        table.ah-tbl td { padding: .55rem .5rem; border-bottom: 1px solid #f1f5f9; vertical-align: top; }
        .ah-table-wrap { overflow-x: auto; }
    </style>
@endsection

@section('content')
    <div class="container-fluid mt-5 ah-wrap">

        <div class="ah-hero">
            <div>
                <h2><i class="fas fa-clock"></i> Attendance Hours</h2>
                <p>Set when this school counts a student or teacher as late, when school closes, and who gets alerted.</p>
            </div>
            <div style="display:flex;gap:.5rem;flex-wrap:wrap">
                <a class="ah-link" href="{{ route('card-scan.hub') }}"><i class="fas fa-qrcode"></i> Scan Hub</a>
                <a class="ah-link" href="{{ route('card-scan.arrival') }}"><i class="fas fa-school"></i> Arrival Attendance</a>
            </div>
        </div>

        @if(session('success')) <div class="ah-alert ok"><i class="fas fa-check-circle"></i> {{ session('success') }}</div> @endif
        @if(session('error')) <div class="ah-alert err"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</div> @endif
        @if($errors->any())
            <div class="ah-alert err">
                @foreach($errors->all() as $e) <div>{{ $e }}</div> @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('card-scan.hours.save') }}">
            @csrf

            <div class="ah-card">
                <h3><i class="fas fa-school"></i> Arrival (when is someone late?)</h3>
                <div class="sub">Arriving at or before the time is <b>on time / early</b>. Arriving after it (plus grace) is <b>late</b>.</div>
                <div class="ah-grid">
                    <div class="ah-field">
                        <label>Students are late after</label>
                        <input type="time" name="student_late_time" required
                            value="{{ old('student_late_time', substr($settings->lateTimeFor('student'), 0, 5)) }}">
                    </div>
                    <div class="ah-field">
                        <label>Teachers are late after</label>
                        <input type="time" name="teacher_late_time" required
                            value="{{ old('teacher_late_time', substr($settings->lateTimeFor('teacher'), 0, 5)) }}">
                    </div>
                    <div class="ah-field">
                        <label>Grace period (minutes)</label>
                        <input type="number" name="grace_minutes" min="0" max="120" required
                            value="{{ old('grace_minutes', $settings->grace_minutes) }}">
                        <small>Extra minutes allowed after the time above.</small>
                    </div>
                </div>
            </div>

            <div class="ah-card">
                <h3><i class="fas fa-door-open"></i> Sign-Out (when does school close?)</h3>
                <div class="sub">Signing out <b>before</b> closing time is recorded as an <b>early departure</b>.</div>
                <div class="ah-grid">
                    <div class="ah-field">
                        <label>Students close at</label>
                        <input type="time" name="student_closing_time" required
                            value="{{ old('student_closing_time', substr($settings->closingTimeFor('student'), 0, 5)) }}">
                    </div>
                    <div class="ah-field">
                        <label>Teachers close at</label>
                        <input type="time" name="teacher_closing_time" required
                            value="{{ old('teacher_closing_time', substr($settings->closingTimeFor('teacher'), 0, 5)) }}">
                    </div>
                    <div class="ah-field">
                        <label>Sign-out opens after (minutes)</label>
                        <input type="number" name="min_minutes_before_signout" min="0" max="240" required
                            value="{{ old('min_minutes_before_signout', $settings->min_minutes_before_signout) }}">
                        <small>Blocks accidental double scans right after arrival. 0 = off.</small>
                    </div>
                </div>
            </div>

            <div class="ah-card">
                <h3><i class="fas fa-bell"></i> Parent alerts</h3>
                <div class="sub">Sent to the parent's phone (Parent Portal) using the Primary Contact number on the student's record.</div>
                <div class="ah-grid">
                    <label class="ah-switch">
                        <input type="checkbox" name="notify_parent_on_arrival" value="1"
                            {{ old('notify_parent_on_arrival', $settings->notify_parent_on_arrival) ? 'checked' : '' }}>
                        Notify parent when student arrives
                    </label>
                    <label class="ah-switch">
                        <input type="checkbox" name="notify_parent_on_signout" value="1"
                            {{ old('notify_parent_on_signout', $settings->notify_parent_on_signout) ? 'checked' : '' }}>
                        Notify parent when student signs out
                    </label>
                </div>
            </div>

            <button class="ah-btn" type="submit"><i class="fas fa-save"></i> Save Hours</button>
        </form>

        <div class="ah-card" style="margin-top:1.5rem">
            <h3><i class="fas fa-exclamation-triangle"></i> Parent alerts — last 7 days</h3>
            <div class="sub">Every alert that could not be delivered is listed here with the reason.</div>
            <div class="ah-stats">
                <span class="ah-stat ok"><i class="fas fa-check"></i> {{ $notifStats['sent'] }} delivered</span>
                <span class="ah-stat bad"><i class="fas fa-times"></i> {{ $notifStats['failed'] }} not delivered</span>
            </div>
            @if($recentIssues->isEmpty())
                <div style="color:#64748b;font-size:.85rem">No delivery problems recorded. 🎉</div>
            @else
                <div class="ah-table-wrap">
                    <table class="ah-tbl">
                        <thead><tr><th>When</th><th>Student</th><th>Event</th><th>Parent phone</th><th>Why it was not sent</th></tr></thead>
                        <tbody>
                            @foreach($recentIssues as $i)
                                <tr>
                                    <td style="white-space:nowrap">{{ $i->created_at->format('d M, h:i A') }}</td>
                                    <td>{{ $i->student ? trim($i->student->firstname . ' ' . $i->student->lastname) : '#' . $i->student_id }}</td>
                                    <td>{{ $i->event === 'signout' ? 'Sign-out' : 'Arrival' }}</td>
                                    <td>{{ $i->parent_phone ?: '—' }}</td>
                                    <td>{{ $i->reason }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection
