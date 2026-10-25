@extends('layouts-side-bar.master')

@section('content')
    @include('Admin.sales-team._styles')
    @php
        $F = \App\Support\SalesFormat::class;
        $sortUrl = fn ($s) => request()->fullUrlWithQuery(['sort' => $s]);
        $top = $rows->filter(fn ($r) => $r['rank'] && $r['rank'] <= 3)->take(3);
        $sortLabels = ['cash' => 'Cash collected', 'enrolled' => 'Schools enrolled', 'schools' => 'Portfolio size', 'students' => 'Students onboarded', 'rate' => 'Collection rate', 'attainment' => 'Target attainment', 'outstanding' => 'Outstanding', 'name' => 'Name'];
    @endphp

    @include('Admin.sales-team._hero', [
        'title' => 'Leaders Dashboard',
        'subtitle' => 'Who is enrolling schools, collecting money and hitting targets — ' . $period->label,
        'actions' => ($canManage ? '<a class="st-btn st-btn--primary" href="' . route('sales.reps') . '"><i class="fas fa-user-plus"></i> Add representative</a>' : '')
            . ($canTransfer ? '<a class="st-btn" href="' . route('sales.transfers.create') . '"><i class="fas fa-right-left"></i> Transfer schools</a>' : '')
            . ($canExport ? '<a class="st-btn" href="' . route('sales.export', ['type' => 'leaderboard'] + request()->query()) . '"><i class="fas fa-file-csv"></i> Export</a>' : ''),
    ])
    @include('Admin.sales-team._nav', ['tab' => 'leaders'])

    <div class="st-bar">
        @include('Admin.sales-team._period', ['extra' => array_filter(['sort' => $sort, 'status' => $status, 'q' => $q])])
        <span class="st-grow"></span>
        <form method="GET">
            @foreach($period->query() as $k => $v)<input type="hidden" name="{{ $k }}" value="{{ $v }}">@endforeach
            <input type="hidden" name="sort" value="{{ $sort }}">
            <input type="text" name="q" value="{{ $q }}" placeholder="Search name, code, region…">
            <select name="status" onchange="this.form.submit()">
                <option value="active" @selected($status === 'active')>Active</option>
                <option value="inactive" @selected($status === 'inactive')>Inactive</option>
                <option value="all" @selected($status === 'all')>All</option>
            </select>
        </form>
    </div>

    @if($totals['unassigned_schools'] > 0)
        <div class="st-flash err" style="background:#fff4dc;color:#a15c00">
            <i class="fas fa-triangle-exclamation"></i>
            {{ number_format($totals['unassigned_schools']) }} school(s) have no representative responsible for them
            @if($totals['unassigned_outstanding'] > 0) and {{ $F::money($totals['unassigned_outstanding'], $cur) }} is owed on them @endif.
            <a href="{{ route('sales.unassigned') }}"><b>Assign them →</b></a>
        </div>
    @endif

    <div class="st-kpis">
        <div class="st-kpi"><small>Active representatives</small><b>{{ $totals['reps'] }}</b><span>{{ number_format($totals['schools']) }} schools · {{ number_format($totals['students']) }} students</span></div>
        <div class="st-kpi"><small>Schools enrolled</small><b>{{ number_format($totals['enrolled']) }}</b><span>in {{ $period->label }}</span></div>
        <div class="st-kpi"><small>Expected</small><b>{{ $F::short($totals['expected']) }}</b><span>{{ $F::money($totals['expected'], $cur) }}</span></div>
        <div class="st-kpi"><small>Cash collected</small><b>{{ $F::short($totals['cash']) }}</b><span>payments dated in period{{ $totals['rate'] !== null ? ' · ' . $totals['rate'] . '% of expected paid' : '' }}</span></div>
        <div class="st-kpi {{ $totals['overdue'] > 0 ? 'st-kpi--bad' : '' }}"><small>Outstanding</small><b>{{ $F::short($totals['outstanding']) }}</b><span>{{ $F::short($totals['overdue']) }} overdue</span></div>
        <div class="st-kpi"><small>Target attainment</small><b>{{ $totals['attainment'] !== null ? $totals['attainment'] . '%' : '—' }}</b><span>{{ $totals['reps_with_targets'] }} rep(s) with targets</span></div>
    </div>

    @if($top->isNotEmpty())
        <div class="st-podium">
            @foreach($top as $r)
                <div class="st-pod r{{ $r['rank'] }}">
                    <span class="st-rank r{{ $r['rank'] }}">{{ $r['rank'] }}</span>
                    <span class="st-avatar st-avatar--lg">{{ $r['initials'] }}</span>
                    <h6><a href="{{ route('sales.show', $r['user_id']) }}">{{ $r['name'] }}</a></h6>
                    <div class="sub">{{ $r['code'] }}{{ $r['region'] ? ' · ' . $r['region'] : '' }}</div>
                    <div class="big">
                        @switch($sort)
                            @case('enrolled'){{ $r['enrolled'] }} enrolled @break
                            @case('schools'){{ $r['schools'] }} schools @break
                            @case('students'){{ number_format($r['new_students']) }} students @break
                            @case('rate'){{ $r['rate'] }}% rate @break
                            @case('attainment'){{ $r['attainment'] }}% of target @break
                            @case('outstanding'){{ $F::short($r['outstanding']) }} owed @break
                            @default {{ $F::short($r['cash']) }}
                        @endswitch
                    </div>
                    <div class="sub">ranked by {{ strtolower($sortLabels[$sort] ?? 'cash collected') }}</div>
                </div>
            @endforeach
        </div>
    @endif

    <div class="st-card">
        <h5>Leaderboard <small>ranked by {{ strtolower($sortLabels[$sort] ?? '') }} — click a column to re-rank</small></h5>
        @if($rows->isEmpty())
            <div class="st-empty"><i class="fas fa-users"></i>
                No representatives match.
                @if($canManage)<br><a href="{{ route('sales.reps') }}">Add your first representative</a>@endif
            </div>
        @else
            <div class="st-scroll"><table class="st-table">
                <thead><tr>
                    <th>#</th><th>Representative</th>
                    <th class="num"><a href="{{ $sortUrl('schools') }}">Schools</a></th>
                    <th class="num"><a href="{{ $sortUrl('enrolled') }}">Enrolled</a></th>
                    <th class="num"><a href="{{ $sortUrl('students') }}">New students</a></th>
                    <th class="num">Expected</th>
                    <th class="num"><a href="{{ $sortUrl('cash') }}">Cash collected</a></th>
                    <th class="num"><a href="{{ $sortUrl('rate') }}">Rate</a></th>
                    <th class="num"><a href="{{ $sortUrl('outstanding') }}">Outstanding</a></th>
                    <th class="num">Overdue</th>
                    <th><a href="{{ $sortUrl('attainment') }}">Target attainment</a></th>
                </tr></thead>
                <tbody>
                @foreach($rows as $r)
                    <tr>
                        <td>@if($r['rank'])<span class="st-rank r{{ $r['rank'] <= 3 ? $r['rank'] : '' }}">{{ $r['rank'] }}</span>@else<span class="st-mute">–</span>@endif</td>
                        <td><div class="st-who"><span class="st-avatar">{{ $r['initials'] }}</span>
                            <div><a href="{{ route('sales.show', $r['user_id']) }}">{{ $r['name'] }}</a>
                            <small>{{ $r['code'] }}{{ $r['region'] ? ' · ' . $r['region'] : '' }}@if(!$r['is_active']) · <b style="color:#b42318">inactive</b>@endif</small></div></div></td>
                        <td class="num">{{ $r['schools'] }} <small class="st-mute">({{ $r['live'] }} live)</small></td>
                        <td class="num">{{ $r['enrolled'] }}</td>
                        <td class="num">{{ number_format($r['new_students']) }}</td>
                        <td class="num">{{ $F::money($r['expected'], '') }}</td>
                        <td class="num"><b>{{ $F::money($r['cash'], '') }}</b>@if($r['commission']) <small class="st-mute" title="Estimated commission">· {{ $F::short($r['commission']) }} comm.</small>@endif</td>
                        <td class="num">{{ $r['rate'] !== null ? $r['rate'] . '%' : '—' }}</td>
                        <td class="num">{{ $F::money($r['outstanding'], '') }}</td>
                        <td class="num" style="{{ $r['overdue'] > 0 ? 'color:#b42318;font-weight:700' : '' }}">{{ $F::money($r['overdue'], '') }}</td>
                        <td style="min-width:150px">
                            @if($r['attainment'] === null)<span class="st-mute">No target set</span>
                            @else
                                @php $worst = collect($r['targets'])->pluck('eval.status')->contains('behind') ? 'behind' : (collect($r['targets'])->pluck('eval.status')->contains('at_risk') ? 'at_risk' : 'on_track'); @endphp
                                <div style="display:flex;align-items:center;gap:.5rem"><div class="st-prog {{ $r['attainment'] >= 100 ? 'achieved' : $worst }}" style="flex:1"><i style="width:{{ min(100, $r['attainment']) }}%"></i></div><b>{{ $r['attainment'] }}%</b></div>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table></div>
        @endif
    </div>

    <div class="st-card">
        <h5>Team trend <small>last {{ count($trend) }} months</small></h5>
        @include('Admin.sales-team._trend', ['trend' => $trend, 'cur' => $cur])
    </div>

    <div class="st-note">
        <b>How credit works.</b> Schools, students, expected, outstanding and overdue follow the representative who owns the school <i>now</i>.
        Enrolment credit always stays with whoever enrolled the school. Cash is credited to whoever owned the school on the payment date,
        so a transfer never takes away money someone already collected.
    </div>
@endsection
