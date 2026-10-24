@extends('layouts-side-bar.master')

@section('css')
    @include('audit.partials.styles')
@endsection

@section('content')
<div class="side-app">

    <div class="au-hero">
        <div>
            <h4><i class="fas fa-clipboard-list mr-2"></i>Audit Trail</h4>
            <small>
                A permanent, tamper-evident record of who did what, when and from where.
                @unless($isPlatform) Showing activity for your school only. @endunless
            </small>
        </div>
        <div>
            @if($canExport)
                <a href="{{ route('audit.export', $filters) }}" class="btn btn-sm btn-light"><i class="fas fa-file-csv mr-1"></i>Export CSV</a>
            @endif
            @if($canVerify)
                <form method="POST" action="{{ route('audit.verify') }}" class="d-inline">
                    @csrf
                    <button class="btn btn-sm btn-outline-light"><i class="fas fa-shield-alt mr-1"></i>Verify integrity</button>
                </form>
            @endif
        </div>
    </div>

    @if($verifyResult)
        <div class="alert {{ count($verifyResult['tampered']) ? 'alert-danger' : 'alert-success' }}">
            <strong>Integrity check:</strong> {{ number_format($verifyResult['checked']) }} entries checked.
            @if(count($verifyResult['tampered']))
                <strong>{{ count($verifyResult['tampered']) }} entries were modified outside the application</strong>
                (ids: {{ implode(', ', array_slice($verifyResult['tampered'], 0, 25)) }}).
            @else
                All signatures are valid.
            @endif
            @if(count($verifyResult['gaps']))
                <br><small>{{ count($verifyResult['gaps']) }} gap(s) in the id sequence (can come from rolled-back transactions or retention pruning).</small>
            @endif
        </div>
    @endif

    <div class="au-stats">
        <div class="au-stat"><div class="n">{{ number_format($stats['events_24h']) }}</div><div class="l">Events (24h)</div></div>
        <div class="au-stat ok"><div class="n">{{ number_format($stats['actors_24h']) }}</div><div class="l">Active users (24h)</div></div>
        <div class="au-stat {{ $stats['failed_logins_24h'] ? 'warn' : '' }}"><div class="n">{{ number_format($stats['failed_logins_24h']) }}</div><div class="l">Failed sign-ins (24h)</div></div>
        <div class="au-stat {{ $stats['denied_24h'] ? 'warn' : '' }}"><div class="n">{{ number_format($stats['denied_24h']) }}</div><div class="l">Access denied (24h)</div></div>
        <div class="au-stat {{ $stats['deletions_7d'] ? 'bad' : '' }}"><div class="n">{{ number_format($stats['deletions_7d']) }}</div><div class="l">Deletions (7 days)</div></div>
    </div>

    @php $max = max(1, collect($stats['daily'])->max('count')); @endphp
    <div class="au-card">
        <div class="small text-muted mb-2"><strong>Activity — last 14 days</strong></div>
        <div class="au-bars">
            @foreach($stats['daily'] as $d)
                <div style="height: {{ max(4, round($d['count'] / $max * 100)) }}%" title="{{ $d['date'] }}: {{ $d['count'] }} events"></div>
            @endforeach
        </div>
        <div class="au-bars-l">@foreach($stats['daily'] as $d)<span>{{ $loop->index % 2 === 0 ? $d['date'] : '' }}</span>@endforeach</div>
    </div>

    <div class="au-card au-filters">
        <form method="GET" action="{{ route('audit.index') }}">
            <div class="form-row">
                <div class="form-group col-md-3"><label>Search</label>
                    <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" class="form-control form-control-sm" placeholder="Name, record, IP, reference…"></div>
                <div class="form-group col-md-2"><label>Category</label>
                    <select name="category" class="form-control form-control-sm"><option value="">All</option>
                        @foreach($categories as $k => $v)<option value="{{ $k }}" @selected(($filters['category'] ?? '') === $k)>{{ $v }}</option>@endforeach</select></div>
                <div class="form-group col-md-2"><label>Event</label>
                    <select name="event" class="form-control form-control-sm"><option value="">All</option>
                        @foreach($eventLabels as $k => $v)<option value="{{ $k }}" @selected(($filters['event'] ?? '') === $k)>{{ $v }}</option>@endforeach</select></div>
                <div class="form-group col-md-2"><label>Severity</label>
                    <select name="severity" class="form-control form-control-sm"><option value="">All</option>
                        @foreach($severities as $v)<option value="{{ $v }}" @selected(($filters['severity'] ?? '') === $v)>{{ ucfirst($v) }}</option>@endforeach</select></div>
                <div class="form-group col-md-1"><label>Outcome</label>
                    <select name="outcome" class="form-control form-control-sm"><option value="">All</option>
                        <option value="success" @selected(($filters['outcome'] ?? '') === 'success')>OK</option>
                        <option value="failure" @selected(($filters['outcome'] ?? '') === 'failure')>Failed</option></select></div>
                <div class="form-group col-md-2"><label>User type</label>
                    <select name="actor_type" class="form-control form-control-sm"><option value="">All</option>
                        @foreach($actorTypes as $k => $v)<option value="{{ $k }}" @selected(($filters['actor_type'] ?? '') === $k)>{{ $v }}</option>@endforeach</select></div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-2"><label>From</label><input type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="form-control form-control-sm"></div>
                <div class="form-group col-md-2"><label>To</label><input type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="form-control form-control-sm"></div>
                <div class="form-group col-md-2"><label>IP address</label><input type="text" name="ip" value="{{ $filters['ip'] ?? '' }}" class="form-control form-control-sm"></div>
                @if($isPlatform)
                    <div class="form-group col-md-3"><label>School</label>
                        <select name="school_id" class="form-control form-control-sm"><option value="">All schools</option>
                            @foreach($schools as $s)<option value="{{ $s->id }}" @selected(($filters['school_id'] ?? '') == $s->id)>{{ $s->name }}</option>@endforeach</select></div>
                @endif
                <div class="form-group col-md-1"><label>Rows</label>
                    <select name="per_page" class="form-control form-control-sm">
                        @foreach([25, 50, 100] as $n)<option value="{{ $n }}" @selected(($filters['per_page'] ?? 25) == $n)>{{ $n }}</option>@endforeach</select></div>
                <div class="form-group col-md-2 d-flex align-items-end">
                    <button class="btn btn-sm btn-primary mr-2"><i class="fas fa-filter mr-1"></i>Filter</button>
                    <a href="{{ route('audit.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                </div>
            </div>
            @foreach(['actor_id', 'entity_type', 'entity_id', 'request_id'] as $hidden)
                @if(isset($filters[$hidden]))<input type="hidden" name="{{ $hidden }}" value="{{ $filters[$hidden] }}">@endif
            @endforeach
        </form>
        @if(isset($filters['actor_id']) || isset($filters['entity_type']) || isset($filters['request_id']))
            <div class="small mt-1"><span class="text-muted">Also filtered by:</span>
                @isset($filters['actor_id']) <span class="au-badge au-c-request">user #{{ $filters['actor_id'] }}</span> @endisset
                @isset($filters['entity_type']) <span class="au-badge au-c-data">{{ $filters['entity_type'] }}{{ isset($filters['entity_id']) ? ' #' . $filters['entity_id'] : '' }}</span> @endisset
                @isset($filters['request_id']) <span class="au-badge au-c-system">request {{ \Illuminate\Support\Str::limit($filters['request_id'], 8, '…') }}</span> @endisset
            </div>
        @endif
    </div>

    <div class="au-card p-0">
        <div class="table-responsive">
            <table class="table au-table mb-0">
                <thead><tr>
                    <th>When</th><th>Who</th><th>Category</th><th>What happened</th>
                    @if($isPlatform)<th>School</th>@endif
                    <th>IP</th><th></th>
                </tr></thead>
                <tbody>
                @forelse($logs as $log)
                    <tr class="au-row" onclick="window.location='{{ route('audit.show', $log->id) }}'">
                        <td style="white-space:nowrap" title="{{ $log->occurred_local->format('Y-m-d H:i:s') }}">
                            <div>{{ $log->occurred_local->format('d M Y') }}</div>
                            <small class="text-muted">{{ $log->occurred_local->format('H:i:s') }} · {{ $log->occurred_local->diffForHumans(null, true, true) }}</small>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <span class="au-av {{ $log->actor_type }} mr-2">{{ $log->actor_initials }}</span>
                                <div><div>{{ \Illuminate\Support\Str::limit($log->actor_name ?: 'Unknown', 28) }}</div>
                                    <small class="text-muted">{{ $log->actor_role ?: ucfirst($log->actor_type) }}</small></div>
                            </div>
                        </td>
                        <td><span class="au-badge au-c-{{ $log->category }}">{{ $categories[$log->category] ?? ucfirst($log->category) }}</span></td>
                        <td style="max-width:420px">
                            <span class="au-s-{{ $log->severity }}" title="{{ ucfirst($log->severity) }}"><span class="au-dot"></span></span>
                            <strong>{{ $log->event_label }}</strong>
                            @if($log->outcome === 'failure')<span class="au-fail ml-1">failed</span>@endif
                            <div class="text-muted small">{{ \Illuminate\Support\Str::limit($log->description, 140) }}</div>
                            @if($log->change_count)<span class="au-badge au-c-data mt-1">{{ $log->change_count }} field{{ $log->change_count > 1 ? 's' : '' }}</span>@endif
                        </td>
                        @if($isPlatform)<td>{{ \Illuminate\Support\Str::limit($log->school_name ?: '—', 22) }}</td>@endif
                        <td><small>{{ $log->ip_address ?: '—' }}</small></td>
                        <td class="text-right"><i class="fas fa-chevron-right text-muted"></i></td>
                    </tr>
                @empty
                    <tr><td colspan="{{ $isPlatform ? 7 : 6 }}" class="text-center text-muted py-5">
                        <i class="fas fa-search fa-2x mb-2 d-block"></i>No audit entries match these filters.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3">{{ $logs->links() }}</div>
    </div>
</div> </div>
</div>
@endsection
