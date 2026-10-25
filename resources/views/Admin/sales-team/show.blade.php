@extends('layouts-side-bar.master')

@section('content')
    @include('Admin.sales-team._styles')
    @php
        $F = \App\Support\SalesFormat::class;
        $r = $row;
        $actions = '';
        if ($canTeam) $actions .= '<a class="st-btn" href="' . route('sales.index', $period->query()) . '"><i class="fas fa-arrow-left"></i> Leaders</a>';
        if ($canTransfer && $r['schools'] > 0) $actions .= '<a class="st-btn st-btn--primary" href="' . route('sales.transfers.create', ['from' => $r['user_id']]) . '"><i class="fas fa-right-left"></i> Transfer schools</a>';
        if ($canTarget && $r['is_active']) $actions .= '<button type="button" class="st-btn" onclick="stOpen(\'dlg-target\')"><i class="fas fa-bullseye"></i> Set target</button>';
        if ($canManage) $actions .= '<button type="button" class="st-btn" onclick="stOpen(\'dlg-edit\')"><i class="fas fa-pen"></i> Edit</button>';
        if ($canExport) $actions .= '<a class="st-btn" href="' . route('sales.export', ['type' => 'portfolio', 'rep' => $r['user_id']] + $period->query()) . '"><i class="fas fa-file-csv"></i> Export schools</a>';
    @endphp

    @include('Admin.sales-team._hero', [
        'title' => $isOwn ? 'My performance' : $r['name'],
        'subtitle' => ($isOwn ? $r['name'] . ' · ' : '') . $r['code'] . ($r['region'] ? ' · ' . $r['region'] : '') . ' · ' . ($r['is_active'] ? 'Active' : 'Inactive') . ' representative',
        'icon' => 'fa-user-tie',
        'actions' => $actions,
    ])
    @include('Admin.sales-team._nav', ['tab' => ''])

    @if(!$r['login_active'])<div class="st-flash err">This person's admin login is disabled, but they still own {{ $r['schools'] }} school(s). Transfer them so someone is following up.</div>@endif
    @if(!$r['is_active'] && $r['schools'] > 0)<div class="st-flash err">Inactive representative still owns {{ $r['schools'] }} school(s).</div>@endif

    <div class="st-bar">
        @include('Admin.sales-team._period', ['extra' => []])
        <span class="st-grow"></span>
        @if($r['email'])<span class="st-mute"><i class="fas fa-envelope"></i> {{ $r['email'] }}{{ $r['phone'] ? ' · ' . $r['phone'] : '' }}</span>@endif
    </div>

    <div class="st-kpis">
        <div class="st-kpi"><small>Schools owned</small><b>{{ $r['schools'] }}</b><span>{{ $r['live'] }} live · {{ $r['pending'] }} pending · {{ $r['blocked'] }} blocked</span></div>
        <div class="st-kpi"><small>Students</small><b>{{ number_format($r['students']) }}</b><span>+{{ number_format($r['new_students']) }} in period</span></div>
        <div class="st-kpi"><small>Enrolled in period</small><b>{{ $r['enrolled'] }}</b><span>credit stays with the enroller</span></div>
        <div class="st-kpi"><small>Expected</small><b>{{ $F::short($r['expected']) }}</b><span>{{ $r['rate'] !== null ? $r['rate'] . '% paid' : 'nothing invoiced' }}</span></div>
        <div class="st-kpi"><small>Cash credited</small><b>{{ $F::short($r['cash']) }}</b><span>{{ $r['commission'] ? 'Est. commission ' . $F::money($r['commission'], $cur) : 'payments dated in period' }}</span></div>
        <div class="st-kpi {{ $r['overdue'] > 0 ? 'st-kpi--bad' : '' }}"><small>Outstanding</small><b>{{ $F::short($r['outstanding']) }}</b><span>{{ $F::short($r['overdue']) }} overdue · {{ $r['overdue_schools'] }} school(s)</span></div>
    </div>

    <div class="st-grid2">
        <div class="st-card">
            <h5><i class="fas fa-bullseye"></i> Targets <small>{{ $r['attainment'] !== null ? 'attainment ' . $r['attainment'] . '%' : '' }}</small></h5>
            @forelse($targets as $t)
                @php $tg = $t['target']; $e = $t['eval']; @endphp
                <div class="st-tg">
                    <div><b>{{ $metrics[$tg->metric]['label'] ?? $tg->metric }}</b> <span class="st-mute">· {{ $tg->period_label }}</span></div>
                    <span class="st-pill {{ $e['status'] }}">{{ $e['label'] }}</span>
                    <div class="st-prog {{ $e['status'] }} full"><i style="width:{{ min(100, $e['pct']) }}%"></i></div>
                    <div class="full st-mute" style="font-size:.8rem">
                        {{ $F::value($tg->metric, $t['actual'], $cur) }} of {{ $F::value($tg->metric, $tg->target_value, $cur) }} ({{ $e['pct'] }}%)
                        @if(!in_array($e['status'], ['achieved', 'missed', 'upcoming'])) · {{ $F::value($tg->metric, $e['remaining'], $cur) }} to go · {{ $e['days_left'] }} day(s) left @endif
                    </div>
                </div>
            @empty
                <div class="st-empty"><i class="fas fa-bullseye"></i>No targets set yet.@if($canTarget && $r['is_active'])<br><a href="#" onclick="stOpen('dlg-target');return false">Set the first target</a>@endif</div>
            @endforelse
        </div>
        <div class="st-card"><h5>Trend <small>last {{ count($trend) }} months</small></h5>@include('Admin.sales-team._trend', ['trend' => $trend, 'cur' => $cur])</div>
    </div>

    <div class="st-card">
        <h5>Schools <small>{{ $schools->count() }} owned · invoices for {{ $period->label }}</small></h5>
        @if($schools->isEmpty())
            <div class="st-empty"><i class="fas fa-school"></i>This representative does not own any schools.</div>
        @else
            <div class="st-bar"><input type="text" placeholder="Filter schools…" oninput="var q=this.value.toLowerCase();document.querySelectorAll('#st-schools tbody tr').forEach(function(tr){tr.style.display=tr.textContent.toLowerCase().indexOf(q)>-1?'':'none'})"></div>
            <div class="st-scroll"><table class="st-table" id="st-schools">
                <thead><tr><th>School</th><th>Status</th><th>Usage</th><th class="num">Students</th><th class="num">Expected</th><th class="num">Paid</th><th class="num">Outstanding (all)</th><th class="num">Overdue</th><th>Owner since</th></tr></thead>
                <tbody>
                @foreach($schools->sortByDesc('overdue') as $s)
                    <tr>
                        <td><b>{{ $s['name'] }}</b><br><small class="st-mute">{{ $s['code'] }}{{ $s['type'] ? ' · ' . $s['type'] : '' }}@if($s['enrolled_by_id'] && $s['enrolled_by_id'] !== $r['user_id']) · enrolled by {{ $s['enrolled_by'] }}@endif</small></td>
                        <td><span class="st-pill {{ $s['status_tone'] }}">{{ $s['status_label'] }}</span></td>
                        <td class="st-mute">{{ $s['usage'] ? ucfirst(str_replace('_', ' ', $s['usage'])) : '—' }}</td>
                        <td class="num">{{ number_format($s['students']) }}</td>
                        <td class="num">{{ $F::money($s['expected'], '') }}</td>
                        <td class="num">{{ $F::money($s['paid'], '') }}</td>
                        <td class="num">{{ $F::money($s['outstanding'], '') }}</td>
                        <td class="num" style="{{ $s['overdue'] > 0 ? 'color:#b42318;font-weight:700' : '' }}">{{ $s['overdue'] > 0 ? $F::money($s['overdue'], '') . ' · ' . $s['days_overdue'] . 'd' : '—' }}</td>
                        <td class="st-mute">{{ $s['owner_since']?->format('j M Y') ?? '—' }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table></div>
        @endif
    </div>

    <div class="st-grid2">
        <div class="st-card"><h5>Ownership history <small>latest 30</small></h5>
            @forelse($history as $h)
                <div class="st-tg"><div><b>{{ $h->school_name }}</b>
                    <div class="st-mute" style="font-size:.78rem">{{ ucfirst($h->type) }}{{ $h->from_name ? ' from ' . $h->from_name : '' }} · {{ \Illuminate\Support\Carbon::parse($h->started_at)->format('j M Y') }}{{ $h->ended_at ? ' → ' . \Illuminate\Support\Carbon::parse($h->ended_at)->format('j M Y') : '' }}</div></div>
                    <span class="st-pill {{ $h->ended_at ? 'mute' : 'ok' }}">{{ $h->ended_at ? 'Ended' : 'Current' }}</span></div>
            @empty<div class="st-empty">No ownership history yet.</div>@endforelse
        </div>
        <div class="st-card"><h5>Schools handed over by this representative</h5>
            @forelse($outgoing as $b)
                <div class="st-tg"><div><a href="{{ route('sales.transfers.show', $b->id) }}"><b>{{ $b->reference }}</b></a>
                    <div class="st-mute" style="font-size:.78rem">{{ $b->school_count }} school(s) · {{ $b->created_at->format('j M Y') }} · {{ $b->reason_label }}</div></div>
                    <span class="st-mute">{{ $F::short($b->outstanding_amount) }}</span></div>
            @empty<div class="st-empty">No transfers out.</div>@endforelse
        </div>
    </div>

    @if($canManage)
        <dialog class="st-dlg" id="dlg-edit">
            <form method="POST" action="{{ route('sales.reps.update', $r['user_id']) }}">@csrf @method('PUT')
                <h5>Edit {{ $r['name'] }}</h5>
                <div class="row2"><div><label>Region</label><input name="region" value="{{ $profile->region }}" maxlength="120"></div>
                <div><label>Commission %</label><input name="commission_rate" type="number" step="0.01" min="0" max="100" value="{{ $profile->commission_rate }}"></div></div>
                <label>Joined on</label><input name="joined_on" type="date" value="{{ $profile->joined_on?->toDateString() }}">
                <label>Notes</label><textarea name="notes" rows="3" maxlength="2000">{{ $profile->notes }}</textarea>
                <div class="foot">
                    @if($r['is_active'])<button type="button" class="st-btn st-btn--danger" onclick="stClose(this);stOpen('dlg-deactivate')">Deactivate…</button>@endif
                    <span class="st-grow"></span>
                    <button type="button" class="st-btn" onclick="stClose(this)">Cancel</button><button class="st-btn st-btn--primary">Save</button>
                </div>
            </form>
        </dialog>
        @if($r['is_active'])
        <dialog class="st-dlg" id="dlg-deactivate">
            <form method="POST" action="{{ route('sales.reps.deactivate', $r['user_id']) }}" onsubmit="return confirm('Deactivate this representative?')">@csrf
                <h5>Deactivate {{ $r['name'] }}</h5>
                @if($r['schools'] > 0)
                    <div class="st-note">They own <b>{{ $r['schools'] }}</b> school(s) with {{ $F::money($r['outstanding'], $cur) }} outstanding. Choose who takes over everything about those schools.</div>
                    <label>Hand schools to</label>
                    <select name="destination" required><option value="">Choose…</option>
                        @foreach($repOptions as $uid => $label)@if($uid !== $r['user_id'])<option value="{{ $uid }}">{{ $label }}</option>@endif @endforeach
                        <option value="pool">Nobody — release to the Unassigned pool</option></select>
                @else<div class="st-note">They own no schools. Their history and targets are kept.</div>@endif
                <div class="foot"><button type="button" class="st-btn" onclick="stClose(this)">Cancel</button><button class="st-btn st-btn--danger">Deactivate</button></div>
            </form>
        </dialog>
        @endif
    @endif

    @if($canTarget && $r['is_active'])
        @include('Admin.sales-team._target-dialog', ['id' => 'dlg-target', 'fixedUser' => $r['user_id'], 'terms' => $terms, 'metrics' => $metrics, 'repOptions' => []])
    @endif
@endsection
