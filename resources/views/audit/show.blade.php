@extends('layouts-side-bar.master')

@section('css')
    @include('audit.partials.styles')
@endsection

@section('content')
@php
    $meta = $log->meta_array;
    $fmt = function ($v) {
        if ($v === null) return '—';
        if (is_bool($v)) return $v ? 'true' : 'false';
        if (is_array($v)) return json_encode($v, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        return (string) $v;
    };
@endphp
<div class="side-app">

    <div class="au-hero">
        <div class="au-hero-main">
            <div class="au-hero-left">
                <div class="au-hero-icon"><i class="fas fa-file-signature"></i></div>
                <div class="au-hero-info">
                    <h4>
                        {{ $log->event_label }}
                        @if($log->outcome === 'failure')
                            <span class="au-hero-chip au-hero-chip--bad">Failed</span>
                        @else
                            <span class="au-hero-chip au-hero-chip--ok">Success</span>
                        @endif
                    </h4>
                    <p>{{ $log->description }}</p>
                </div>
            </div>
            <div class="au-hero-actions">
                <a href="{{ route('audit.index') }}" class="au-hero-btn au-hero-btn--primary">
                    <i class="fas fa-arrow-left"></i><span>Back to audit trail</span>
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="au-card">
                <h6 class="mb-3"><i class="fas fa-user mr-1"></i> Who &amp; when</h6>
                <dl class="au-kv">
                    <dt>Time</dt><dd>{{ $log->occurred_local->format('l, d M Y H:i:s') }} <small class="text-muted">({{ config('app.timezone') }})</small></dd>
                    <dt>User</dt><dd>{{ $log->actor_name ?: '—' }} <small class="text-muted">· {{ $log->actor_role ?: ucfirst($log->actor_type) }}</small>
                        @if($log->actor_id)<a class="small ml-1" href="{{ route('audit.index', ['actor_type' => $log->actor_type, 'actor_id' => $log->actor_id]) }}">all activity</a>@endif</dd>
                    <dt>School</dt><dd>{{ $log->school_name ?: '—' }}</dd>
                    <dt>IP address</dt><dd>{{ $log->ip_address ?: '—' }}
                        @if($log->ip_address)<a class="small ml-1" href="{{ route('audit.index', ['ip' => $log->ip_address]) }}">same IP</a>@endif</dd>
                    <dt>Device</dt><dd><small>{{ $log->user_agent ?: '—' }}</small></dd>
                </dl>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="au-card">
                <h6 class="mb-3"><i class="fas fa-info-circle mr-1"></i> Details</h6>
                <dl class="au-kv">
                    <dt>Category</dt><dd><span class="au-badge au-c-{{ $log->category }}">{{ ucfirst($log->category) }}</span>
                        <span class="au-s-{{ $log->severity }} ml-2"><span class="au-dot"></span>{{ ucfirst($log->severity) }}</span></dd>
                    <dt>Record</dt><dd>@if($log->entity_type){{ $log->entity_type }} #{{ $log->entity_id }} @if($log->entity_label)— {{ $log->entity_label }}@endif
                        <a class="small ml-1" href="{{ route('audit.index', ['entity_type' => $log->entity_type, 'entity_id' => $log->entity_id]) }}">full history</a>@else — @endif</dd>
                    <dt>Request</dt><dd>{{ $log->method }} <small>{{ \Illuminate\Support\Str::limit($log->url, 90) }}</small>
                        @if($log->status_code) <span class="au-badge au-c-request">{{ $log->status_code }}</span>@endif</dd>
                    <dt>Route</dt><dd>{{ $log->route_name ?: '—' }}@if($log->duration_ms) <small class="text-muted">· {{ $log->duration_ms }} ms</small>@endif</dd>
                    <dt>Reference</dt><dd><small>{{ $log->uuid }}</small></dd>
                    <dt>Integrity</dt><dd>@if($intact)<span class="au-ok"><i class="fas fa-check-circle"></i> Signature valid — unmodified</span>
                        @else<span class="au-bad"><i class="fas fa-exclamation-triangle"></i> Signature mismatch — this entry was modified outside the application</span>@endif</dd>
                </dl>
            </div>
        </div>
    </div>

    @if(count($log->diff))
        <div class="au-card">
            <h6 class="mb-3"><i class="fas fa-exchange-alt mr-1"></i> {{ $log->event === 'deleted' ? 'Data that was deleted' : 'Changes' }}</h6>
            <div class="table-responsive">
            <table class="au-diff">
                <thead><tr><th style="width:22%">Field</th><th>Before</th><th>After</th></tr></thead>
                <tbody>
                @foreach($log->diff as $c)
                    <tr><td><code>{{ $c['field'] }}</code></td>
                        <td class="{{ $c['kind'] !== 'added' ? 'au-old' : '' }}">{{ $c['kind'] === 'added' ? '' : $fmt($c['old']) }}</td>
                        <td class="{{ $c['kind'] !== 'removed' ? 'au-new' : '' }}">{{ $c['kind'] === 'removed' ? '' : $fmt($c['new']) }}</td></tr>
                @endforeach
                </tbody>
            </table>
            </div>
        </div>
    @endif

    @if($showContext && !empty($meta))
        <div class="au-card">
            <h6 class="mb-2"><i class="fas fa-database mr-1"></i> Request context</h6>
            @if(!empty($meta['db_writes']))
                <div class="small text-muted mb-1">Tables written during this request:</div>
                <div class="mb-3">@foreach($meta['db_writes'] as $table => $w)
                    <span class="au-badge au-c-data mr-1 mb-1">{{ $table }}:
                        @foreach($w as $verb => $n) @if($n){{ $verb }} {{ $n }} @endif @endforeach</span>
                @endforeach</div>
            @endif
            <pre class="au-json mb-0">{{ json_encode($meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-6">
            <div class="au-card">
                <h6 class="mb-3"><i class="fas fa-link mr-1"></i> Same request ({{ $related->count() }})</h6>
                @if($related->isEmpty())<div class="text-muted small">No other entries were produced by this request.</div>@endif
                <ul class="au-tl pl-3 mb-0">@foreach($related as $r)
                    <li><a href="{{ route('audit.show', $r->id) }}"><strong>{{ $r->event_label }}</strong></a>
                        <div class="small text-muted">{{ \Illuminate\Support\Str::limit($r->description, 110) }}</div></li>
                @endforeach</ul>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="au-card">
                <h6 class="mb-3"><i class="fas fa-history mr-1"></i> History of this record</h6>
                @if($history->isEmpty())<div class="text-muted small">No other entries for this record.</div>@endif
                <ul class="au-tl pl-3 mb-0">@foreach($history as $h)
                    <li><a href="{{ route('audit.show', $h->id) }}"><strong>{{ $h->event_label }}</strong></a>
                        <small class="text-muted"> · {{ $h->occurred_local->format('d M Y H:i') }} · {{ $h->actor_name }}</small></li>
                @endforeach</ul>
            </div>
        </div>
    </div>
</div>
</div>
    </div>
</div>
@endsection
