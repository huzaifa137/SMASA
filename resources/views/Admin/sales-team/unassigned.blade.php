@extends('layouts-side-bar.master')

@section('content')
    @include('Admin.sales-team._styles')
    @php $F = \App\Support\SalesFormat::class; @endphp
    @include('Admin.sales-team._hero', ['title' => 'Unassigned schools', 'subtitle' => 'Schools no representative is responsible for yet', 'icon' => 'fa-inbox'])
    @include('Admin.sales-team._nav', ['tab' => 'unassigned'])

    <div class="st-kpis">
        <div class="st-kpi {{ $summary['schools'] ? 'st-kpi--warn' : '' }}"><small>Schools without an owner</small><b>{{ number_format($summary['schools']) }}</b></div>
        <div class="st-kpi {{ $summary['outstanding'] > 0 ? 'st-kpi--bad' : '' }}"><small>Owed on them</small><b>{{ $F::short($summary['outstanding']) }}</b><span>nobody is following this up</span></div>
    </div>

    <div class="st-bar"><form method="GET"><input type="text" name="q" value="{{ $q }}" placeholder="Search schools…"><button class="st-btn st-btn--sm">Search</button></form></div>

    <form method="POST" action="{{ route('sales.unassigned.assign') }}" class="st-card">@csrf
        @if($schools->isEmpty())
            <div class="st-empty"><i class="fas fa-circle-check"></i>Every school has a representative.</div>
        @else
            @if($canAssign)
            <div class="st-bar">
                <select name="user_id" required><option value="">Assign selected to…</option>@foreach($repOptions as $uid => $l)<option value="{{ $uid }}">{{ $l }}</option>@endforeach</select>
                <label style="margin:0;font-size:.82rem"><input type="checkbox" name="credit_enrollment" value="1"> Also credit enrolment to them (only where nobody has it)</label>
                <input type="text" name="note" maxlength="500" placeholder="Note (optional)">
                <button class="st-btn st-btn--primary st-btn--sm">Assign</button>
            </div>
            @endif
            <div class="st-scroll"><table class="st-table">
                <thead><tr>@if($canAssign)<th><input type="checkbox" onclick="document.querySelectorAll('.st-u').forEach(c=>c.checked=this.checked)"></th>@endif<th>School</th><th>Status</th><th>Enrolled</th><th>Enrolled by</th><th class="num">Students</th><th class="num">Outstanding</th></tr></thead>
                <tbody>@foreach($schools as $s)
                    <tr>@if($canAssign)<td><input type="checkbox" class="st-u" name="school_ids[]" value="{{ $s['id'] }}"></td>@endif
                        <td><b>{{ $s['name'] }}</b><br><small class="st-mute">{{ $s['code'] }}</small></td>
                        <td><span class="st-pill {{ $s['status_tone'] }}">{{ $s['status_label'] }}</span></td>
                        <td>{{ $s['created_at']?->format('j M Y') }}</td><td class="st-mute">{{ $s['enrolled_by'] ?? 'Unknown' }}</td>
                        <td class="num">{{ $s['students'] }}</td><td class="num">{{ $F::money($s['outstanding'], '') }}</td></tr>
                @endforeach</tbody>
            </table></div>
            <div style="margin-top:1rem">{{ $page->links() }}</div>
        @endif
    </form>
@endsection
