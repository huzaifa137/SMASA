{{-- resources/views/parent_portal/settings.blade.php --}}
@extends('layouts-side-bar.master')

@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .pa-wrap { max-width: 1000px; margin: 0 auto; }
        .pa-hero { background: linear-gradient(135deg, #2f2ccb, #5351e4); color: #fff; border-radius: 16px; padding: 1.4rem 1.6rem; margin-bottom: 1.25rem; }
        .pa-hero h2 { margin: 0; font-size: 1.35rem; font-weight: 700; color: #fff; }
        .pa-hero p { margin: .3rem 0 0; opacity: .88; font-size: .9rem; max-width: 740px; }
        .pa-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 1.15rem 1.35rem; margin-bottom: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,.05); }
        .pa-card h3 { margin: 0 0 .2rem; font-size: 1.02rem; font-weight: 700; color: #0f172a; }
        .pa-card .desc { color: #64748b; font-size: .83rem; margin-bottom: .9rem; }
        .pa-row { display: flex; align-items: center; justify-content: space-between; gap: 1rem; padding: .6rem 0; border-top: 1px solid #f1f5f9; font-size: .9rem; }
        .pa-row:first-of-type { border-top: 0; }
        .pa-row small { display: block; color: #94a3b8; font-size: .76rem; }
        .pa-input, .pa-select, .pa-textarea { border: 1.5px solid #e2e8f0; border-radius: 10px; padding: .45rem .7rem; font-size: .88rem; }
        .pa-textarea { width: 100%; }
        .pa-btn { background: #2f2ccb; color: #fff; border: 0; border-radius: 10px; padding: .6rem 1.3rem; font-weight: 700; cursor: pointer; font-size: .88rem; }
        .pa-btn.sm { padding: .35rem .8rem; font-size: .78rem; }
        .pa-btn.ghost { background: #f1f5f9; color: #334155; }
        .pa-btn.danger { background: #fee2e2; color: #b91c1c; }
        .pa-alert { padding: .7rem 1rem; border-radius: 10px; margin-bottom: 1rem; font-size: .9rem; }
        .pa-alert.ok { background: rgba(5,150,105,.1); color: #047857; }
        .pa-alert.err { background: rgba(220,38,38,.08); color: #b91c1c; }
        .pa-table { width: 100%; font-size: .85rem; border-collapse: collapse; }
        .pa-table th { text-align: left; color: #64748b; font-size: .74rem; text-transform: uppercase; letter-spacing: .03em; padding: .4rem .5rem; }
        .pa-table td { padding: .55rem .5rem; border-top: 1px solid #f1f5f9; vertical-align: middle; }
        .pa-badge { display: inline-block; padding: .12rem .55rem; border-radius: 99px; font-size: .72rem; font-weight: 700; }
        .pa-badge.green { background: rgba(5,150,105,.12); color: #047857; }
        .pa-badge.amber { background: rgba(217,119,6,.14); color: #b45309; }
        .pa-badge.red { background: rgba(220,38,38,.1); color: #b91c1c; }
        .pa-badge.grey { background: #f1f5f9; color: #475569; }
        .pa-inline { display: flex; gap: .4rem; flex-wrap: wrap; align-items: center; }
    </style>
@endsection

@section('content')
    <div class="pa-wrap">
        <div class="pa-hero">
            <h2><i class="fas fa-user-shield"></i> Parent Portal Access</h2>
            <p>Decide what parents can see, when report cards are released to them, and whether an unpaid fee balance
                holds results back. Until you change anything, the portal behaves as it always has.</p>
        </div>

        @if(session('success')) <div class="pa-alert ok"><i class="fas fa-check-circle"></i> {{ session('success') }}</div> @endif
        @if($errors->any()) <div class="pa-alert err">{{ $errors->first() }}</div> @endif

        {{-- ── School-wide settings ── --}}
        <form method="POST" action="{{ route('parent-portal.update') }}">
            @csrf
            <div class="pa-card">
                <h3>What parents can see</h3>
                <div class="desc">Switch whole sections on or off for every parent of this school.</div>
                @foreach([
                    ['show_results', 'Exam results & report cards', ''],
                    ['show_attendance', 'Attendance', ''],
                    ['show_fees', 'Fee statement & balance', ''],
                    ['show_arrival_time', 'Arrival time on attendance', 'Time the student was scanned in.'],
                    ['show_signout_time', 'Sign-out time on attendance', 'Time the student was scanned out at the gate. Off by default.'],
                ] as [$key, $label, $hint])
                    <div class="pa-row">
                        <div>{{ $label }} @if($hint)<small>{{ $hint }}</small>@endif</div>
                        <label><input type="checkbox" name="{{ $key }}" value="1" {{ old($key, $settings->$key) ? 'checked' : '' }}> Visible</label>
                    </div>
                @endforeach
            </div>

            <div class="pa-card">
                <h3>When results become visible</h3>
                <div class="desc">Results must first be released in the school portal. This is an extra step on top of that.</div>
                <div class="pa-row">
                    <div>Default for every exam
                        <small>Automatic: parents see results as soon as they are released. Manual: you publish each exam to parents yourself (below).</small></div>
                    <select name="results_release_mode" class="pa-select">
                        <option value="auto" {{ $settings->results_release_mode === 'auto' ? 'selected' : '' }}>Automatic</option>
                        <option value="manual" {{ $settings->results_release_mode === 'manual' ? 'selected' : '' }}>Manual publish</option>
                    </select>
                </div>
                <div class="pa-row">
                    <div>Hold results for unpaid fees
                        <small>A held report card is still listed to the parent, locked, with your message.</small></div>
                    <select name="fee_hold_mode" class="pa-select" id="feeMode">
                        <option value="none" {{ $settings->fee_hold_mode === 'none' ? 'selected' : '' }}>Never hold</option>
                        <option value="any_balance" {{ $settings->fee_hold_mode === 'any_balance' ? 'selected' : '' }}>Any outstanding balance</option>
                        <option value="above_amount" {{ $settings->fee_hold_mode === 'above_amount' ? 'selected' : '' }}>Balance above an amount</option>
                    </select>
                </div>
                <div class="pa-row" id="feeAmountRow">
                    <div>Hold when balance is above</div>
                    <input type="number" step="1" min="0" name="fee_hold_amount" class="pa-input" value="{{ old('fee_hold_amount', $settings->fee_hold_amount) }}">
                </div>
                <div class="pa-row">
                    <div>Which fees count
                        <small>All fees of the student, or only the term and year of the exam being opened.</small></div>
                    <select name="fee_hold_scope" class="pa-select">
                        <option value="all" {{ $settings->fee_hold_scope === 'all' ? 'selected' : '' }}>All fees</option>
                        <option value="exam_term" {{ $settings->fee_hold_scope === 'exam_term' ? 'selected' : '' }}>Only the exam's term</option>
                    </select>
                </div>
                <div style="margin-top:.6rem;">
                    <label style="font-size:.85rem;font-weight:600;">Message shown to the parent (optional)</label>
                    <textarea name="hold_message" class="pa-textarea" rows="2" maxlength="500" placeholder="Results are on hold until the outstanding fee balance is cleared. Please visit the bursar.">{{ old('hold_message', $settings->hold_message) }}</textarea>
                </div>
            </div>
            <button class="pa-btn" type="submit"><i class="fas fa-save"></i> Save settings</button>
        </form>

        {{-- ── Per-exam release ── --}}
        <div class="pa-card" style="margin-top:1.25rem;">
            <h3>Release by exam</h3>
            <div class="desc">Override the default for one exam: publish it now, hold it back, or schedule when parents get access.</div>
            <div style="overflow-x:auto;">
                <table class="pa-table">
                    <thead><tr><th>Exam</th><th>Parents see it</th><th>Change</th></tr></thead>
                    <tbody>
                    @forelse($exams as $exam)
                        @php
                            $rel = $releases[$exam->id] ?? null;
                            $mode = $rel->mode ?? $settings->results_release_mode;
                            if ($mode === 'auto') { $badge = ['green', 'Automatically']; }
                            elseif ($mode === 'scheduled') { $badge = ['amber', 'Scheduled ' . ($rel->publish_at ? \Carbon\Carbon::parse($rel->publish_at)->format('d M Y H:i') : '')]; }
                            elseif (!empty($rel->published_at)) { $badge = ['green', 'Published']; }
                            else { $badge = ['red', 'Held until published']; }
                        @endphp
                        <tr>
                            <td><strong>{{ $exam->exam_name }}</strong>
                                <div style="font-size:.72rem;color:#94a3b8;">{{ \App\Support\Term::label($exam->term) }} {{ $exam->academic_year }}{{ $rel ? '' : ' · school default' }}</div></td>
                            <td><span class="pa-badge {{ $badge[0] }}">{{ $badge[1] }}</span></td>
                            <td>
                                <form method="POST" action="{{ route('parent-portal.exam-release') }}" class="pa-inline">
                                    @csrf
                                    <input type="hidden" name="examination_id" value="{{ $exam->id }}">
                                    <select name="action" class="pa-select" onchange="this.form.querySelector('.when').style.display = this.value === 'schedule' ? '' : 'none'">
                                        <option value="inherit">Follow school default</option>
                                        <option value="auto">Automatic</option>
                                        <option value="publish">Publish now</option>
                                        <option value="hold">Hold back</option>
                                        <option value="schedule">Schedule…</option>
                                    </select>
                                    <input type="datetime-local" name="publish_at" class="pa-input when" style="display:none;">
                                    <button class="pa-btn sm ghost" type="submit">Apply</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" style="color:#94a3b8;">No exams yet.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ── Per-student overrides ── --}}
        <div class="pa-card">
            <h3>Individual students</h3>
            <div class="desc"><b>Always allow</b> lets a student's parents see results despite a fee hold or manual release (results must still be released in the school portal).
                <b>Hold</b> blocks that student's results with your message.</div>

            <form method="GET" action="{{ route('parent-portal.index') }}" class="pa-inline" style="margin-bottom:.8rem;">
                <input type="text" name="q" value="{{ $q }}" class="pa-input" placeholder="Search name or admission number" style="min-width:260px;">
                <button class="pa-btn sm" type="submit"><i class="fas fa-search"></i> Find student</button>
            </form>

            @if($found->isNotEmpty())
                @foreach($found as $st)
                    <form method="POST" action="{{ route('parent-portal.override.store') }}" class="pa-inline" style="padding:.45rem 0;border-top:1px solid #f1f5f9;">
                        @csrf
                        <input type="hidden" name="student_id" value="{{ $st->id }}">
                        <div style="min-width:200px;"><strong>{{ $st->firstname }} {{ $st->lastname }}</strong>
                            <small style="color:#94a3b8;">{{ $st->admission_number }}</small></div>
                        <select name="results_access" class="pa-select">
                            <option value="allow">Always allow</option>
                            <option value="hold">Hold results</option>
                        </select>
                        <input type="text" name="message" class="pa-input" maxlength="500" placeholder="Message to parent (optional)" style="flex:1;min-width:200px;">
                        <button class="pa-btn sm" type="submit">Save</button>
                    </form>
                @endforeach
            @elseif($q !== '')
                <div style="color:#94a3b8;font-size:.85rem;margin-bottom:.6rem;">No students matched "{{ $q }}".</div>
            @endif

            <table class="pa-table" style="margin-top:.6rem;">
                <thead><tr><th>Student</th><th>Setting</th><th>Message</th><th></th></tr></thead>
                <tbody>
                @forelse($overrides as $o)
                    <tr>
                        <td>{{ $o->firstname }} {{ $o->lastname }} <small style="color:#94a3b8;">{{ $o->admission_number }}</small></td>
                        <td><span class="pa-badge {{ $o->results_access === 'allow' ? 'green' : 'red' }}">{{ $o->results_access === 'allow' ? 'Always allow' : 'Held' }}</span></td>
                        <td style="color:#64748b;">{{ $o->message ?: '—' }}</td>
                        <td style="text-align:right;">
                            <form method="POST" action="{{ route('parent-portal.override.delete', $o->id) }}" onsubmit="return confirm('Remove this override?')">
                                @csrf @method('DELETE')
                                <button class="pa-btn sm danger" type="submit">Remove</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" style="color:#94a3b8;">No individual overrides.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@section('js')
    <script>
        (function () {
            var mode = document.getElementById('feeMode'), row = document.getElementById('feeAmountRow');
            function sync() { row.style.display = mode.value === 'above_amount' ? '' : 'none'; }
            mode.addEventListener('change', sync); sync();
        })();
    </script>
@endsection
