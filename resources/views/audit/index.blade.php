@extends('layouts-side-bar.master')

@section('css')
    @include('audit.partials.styles')
    <style>
        /* Enhanced Filter Card */
        .au-filters.enhanced {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,.06);
            padding: 1.25rem 1.5rem;
            margin-bottom: 1.5rem;
            border: 1px solid #eef0f3;
        }

        .au-filters.enhanced .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 0.85rem 1rem;
            align-items: end;
        }

        .au-filters.enhanced .filter-group {
            display: flex;
            flex-direction: column;
        }

        .au-filters.enhanced .filter-group label {
            font-size: .7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: #8896ab;
            margin-bottom: .3rem;
        }

        .au-filters.enhanced .filter-group .form-control-sm,
        .au-filters.enhanced .filter-group select.form-control-sm {
            border-radius: 8px;
            border: 1px solid #dfe4ea;
            background: #f8fafc;
            font-size: .82rem;
            padding: .45rem .65rem;
            height: auto;
            transition: border-color .2s, box-shadow .2s;
        }

        .au-filters.enhanced .filter-group .form-control-sm:focus,
        .au-filters.enhanced .filter-group select.form-control-sm:focus {
            border-color: #4f7cff;
            box-shadow: 0 0 0 3px rgba(79,124,255,.12);
            background: #fff;
            outline: none;
        }

        /* Custom Searchable Select */
        .ss-wrapper {
            position: relative;
        }

        .ss-trigger {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border: 1px solid #dfe4ea;
            border-radius: 8px;
            background: #f8fafc;
            padding: .45rem .65rem;
            font-size: .82rem;
            cursor: pointer;
            transition: border-color .2s, box-shadow .2s;
            min-height: 36px;
            color: #2d3748;
        }

        .ss-trigger:hover {
            border-color: #c3cbd6;
        }

        .ss-wrapper.open .ss-trigger {
            border-color: #4f7cff;
            box-shadow: 0 0 0 3px rgba(79,124,255,.12);
            background: #fff;
        }

        .ss-trigger .ss-value {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            flex: 1;
        }

        .ss-trigger .ss-value.placeholder {
            color: #a0aec0;
        }

        .ss-trigger .ss-arrow {
            font-size: .6rem;
            color: #a0aec0;
            margin-left: .4rem;
            transition: transform .2s;
        }

        .ss-wrapper.open .ss-trigger .ss-arrow {
            transform: rotate(180deg);
        }

        .ss-dropdown {
            display: none;
            position: absolute;
            top: calc(100% + 4px);
            left: 0;
            right: 0;
            z-index: 1050;
            background: #fff;
            border: 1px solid #dfe4ea;
            border-radius: 8px;
            box-shadow: 0 8px 24px rgba(0,0,0,.12);
            overflow: hidden;
            animation: ssFadeIn .15s ease;
        }

        .ss-wrapper.open .ss-dropdown {
            display: block;
        }

        @keyframes ssFadeIn {
            from { opacity: 0; transform: translateY(-4px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .ss-search {
            padding: .45rem .55rem;
            border-bottom: 1px solid #eef0f3;
        }

        .ss-search input {
            width: 100%;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: .35rem .55rem;
            font-size: .78rem;
            outline: none;
            background: #f8fafc;
            transition: border-color .2s;
        }

        .ss-search input:focus {
            border-color: #4f7cff;
            background: #fff;
        }

        .ss-options {
            max-height: 220px;
            overflow-y: auto;
            padding: .25rem 0;
        }

        .ss-option {
            padding: .45rem .75rem;
            font-size: .82rem;
            cursor: pointer;
            color: #2d3748;
            transition: background .12s;
            display: flex;
            align-items: center;
            gap: .4rem;
        }

        .ss-option:hover {
            background: #f1f5ff;
        }

        .ss-option.selected {
            background: #eef2ff;
            font-weight: 600;
            color: #3b5bdb;
        }

        .ss-option .check-icon {
            visibility: hidden;
            color: #4f7cff;
            font-size: .7rem;
        }

        .ss-option.selected .check-icon {
            visibility: visible;
        }

        .ss-no-results {
            padding: .75rem;
            text-align: center;
            font-size: .8rem;
            color: #a0aec0;
        }

        /* Action buttons row */
        .au-filters.enhanced .filter-actions {
            display: flex;
            gap: .5rem;
            align-items: center;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #eef0f3;
        }

        .au-filters.enhanced .filter-actions .btn {
            border-radius: 8px;
            font-size: .82rem;
            padding: .45rem 1rem;
            font-weight: 500;
        }

        .au-filters.enhanced .filter-actions .btn-primary {
            background: linear-gradient(135deg, #4f7cff, #3b5bdb);
            border: none;
            box-shadow: 0 2px 8px rgba(79,124,255,.3);
        }

        .au-filters.enhanced .filter-actions .btn-primary:hover {
            box-shadow: 0 4px 12px rgba(79,124,255,.4);
            transform: translateY(-1px);
        }

        .au-filters.enhanced .filter-actions .btn-outline-secondary {
            border-color: #dfe4ea;
            color: #64748b;
        }

        .au-filters.enhanced .filter-actions .btn-outline-secondary:hover {
            background: #f8fafc;
            border-color: #c3cbd6;
        }

        /* Active filter chips */
        .au-filters.enhanced .active-filters {
            display: flex;
            flex-wrap: wrap;
            gap: .35rem;
            margin-top: .75rem;
            align-items: center;
        }

        .au-filters.enhanced .active-filters .label-sm {
            font-size: .7rem;
            color: #a0aec0;
            margin-right: .2rem;
        }

        /* Scrollbar for options */
        .ss-options::-webkit-scrollbar {
            width: 6px;
        }
        .ss-options::-webkit-scrollbar-track {
            background: transparent;
        }
        .ss-options::-webkit-scrollbar-thumb {
            background: #d0d7e2;
            border-radius: 3px;
        }
        .ss-options::-webkit-scrollbar-thumb:hover {
            background: #b0bac9;
        }
    </style>
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

    {{-- Enhanced Filters --}}
    <div class="au-card au-filters enhanced">
        <form method="GET" action="{{ route('audit.index') }}" id="auditFilterForm">
            <div class="filter-grid">

                {{-- Search --}}
                <div class="filter-group">
                    <label>Search</label>
                    <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" class="form-control form-control-sm" placeholder="Name, record, IP, reference…">
                </div>

                {{-- Category (searchable) --}}
                <div class="filter-group">
                    <label>Category</label>
                    <div class="ss-wrapper" data-name="category" data-placeholder="All categories">
                        <div class="ss-trigger" tabindex="0">
                            <span class="ss-value placeholder">All categories</span>
                            <i class="fas fa-chevron-down ss-arrow"></i>
                        </div>
                        <div class="ss-dropdown">
                            <div class="ss-search"><input type="text" placeholder="Type to search…"></div>
                            <div class="ss-options">
                                <div class="ss-option" data-value="">All categories <i class="fas fa-check check-icon ml-auto"></i></div>
                                @foreach($categories as $k => $v)
                                    <div class="ss-option" data-value="{{ $k }}" @if(($filters['category'] ?? '') === $k) data-selected="true" @endif>
                                        {{ $v }} <i class="fas fa-check check-icon ml-auto"></i>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <input type="hidden" name="category" value="{{ $filters['category'] ?? '' }}">
                    </div>
                </div>

                {{-- Event (searchable) --}}
                <div class="filter-group">
                    <label>Event</label>
                    <div class="ss-wrapper" data-name="event" data-placeholder="All events">
                        <div class="ss-trigger" tabindex="0">
                            <span class="ss-value placeholder">All events</span>
                            <i class="fas fa-chevron-down ss-arrow"></i>
                        </div>
                        <div class="ss-dropdown">
                            <div class="ss-search"><input type="text" placeholder="Type to search…"></div>
                            <div class="ss-options">
                                <div class="ss-option" data-value="">All events <i class="fas fa-check check-icon ml-auto"></i></div>
                                @foreach($eventLabels as $k => $v)
                                    <div class="ss-option" data-value="{{ $k }}" @if(($filters['event'] ?? '') === $k) data-selected="true" @endif>
                                        {{ $v }} <i class="fas fa-check check-icon ml-auto"></i>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <input type="hidden" name="event" value="{{ $filters['event'] ?? '' }}">
                    </div>
                </div>

                {{-- Severity (searchable) --}}
                <div class="filter-group">
                    <label>Severity</label>
                    <div class="ss-wrapper" data-name="severity" data-placeholder="All severities">
                        <div class="ss-trigger" tabindex="0">
                            <span class="ss-value placeholder">All severities</span>
                            <i class="fas fa-chevron-down ss-arrow"></i>
                        </div>
                        <div class="ss-dropdown">
                            <div class="ss-search"><input type="text" placeholder="Type to search…"></div>
                            <div class="ss-options">
                                <div class="ss-option" data-value="">All severities <i class="fas fa-check check-icon ml-auto"></i></div>
                                @foreach($severities as $v)
                                    <div class="ss-option" data-value="{{ $v }}" @if(($filters['severity'] ?? '') === $v) data-selected="true" @endif>
                                        {{ ucfirst($v) }} <i class="fas fa-check check-icon ml-auto"></i>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <input type="hidden" name="severity" value="{{ $filters['severity'] ?? '' }}">
                    </div>
                </div>

                {{-- Outcome (searchable) --}}
                <div class="filter-group">
                    <label>Outcome</label>
                    <div class="ss-wrapper" data-name="outcome" data-placeholder="All outcomes">
                        <div class="ss-trigger" tabindex="0">
                            <span class="ss-value placeholder">All outcomes</span>
                            <i class="fas fa-chevron-down ss-arrow"></i>
                        </div>
                        <div class="ss-dropdown">
                            <div class="ss-search"><input type="text" placeholder="Type to search…"></div>
                            <div class="ss-options">
                                <div class="ss-option" data-value="">All outcomes <i class="fas fa-check check-icon ml-auto"></i></div>
                                <div class="ss-option" data-value="success" @if(($filters['outcome'] ?? '') === 'success') data-selected="true" @endif>OK <i class="fas fa-check check-icon ml-auto"></i></div>
                                <div class="ss-option" data-value="failure" @if(($filters['outcome'] ?? '') === 'failure') data-selected="true" @endif>Failed <i class="fas fa-check check-icon ml-auto"></i></div>
                            </div>
                        </div>
                        <input type="hidden" name="outcome" value="{{ $filters['outcome'] ?? '' }}">
                    </div>
                </div>

                {{-- User Type (searchable) --}}
                <div class="filter-group">
                    <label>User type</label>
                    <div class="ss-wrapper" data-name="actor_type" data-placeholder="All types">
                        <div class="ss-trigger" tabindex="0">
                            <span class="ss-value placeholder">All types</span>
                            <i class="fas fa-chevron-down ss-arrow"></i>
                        </div>
                        <div class="ss-dropdown">
                            <div class="ss-search"><input type="text" placeholder="Type to search…"></div>
                            <div class="ss-options">
                                <div class="ss-option" data-value="">All types <i class="fas fa-check check-icon ml-auto"></i></div>
                                @foreach($actorTypes as $k => $v)
                                    <div class="ss-option" data-value="{{ $k }}" @if(($filters['actor_type'] ?? '') === $k) data-selected="true" @endif>
                                        {{ $v }} <i class="fas fa-check check-icon ml-auto"></i>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <input type="hidden" name="actor_type" value="{{ $filters['actor_type'] ?? '' }}">
                    </div>
                </div>

                {{-- From --}}
                <div class="filter-group">
                    <label>From</label>
                    <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="form-control form-control-sm">
                </div>

                {{-- To --}}
                <div class="filter-group">
                    <label>To</label>
                    <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="form-control form-control-sm">
                </div>

                {{-- IP --}}
                <div class="filter-group">
                    <label>IP address</label>
                    <input type="text" name="ip" value="{{ $filters['ip'] ?? '' }}" class="form-control form-control-sm" placeholder="e.g. 192.168.1.1">
                </div>

                {{-- School (platform only) --}}
                @if($isPlatform)
                    <div class="filter-group">
                        <label>School</label>
                        <div class="ss-wrapper" data-name="school_id" data-placeholder="All schools">
                            <div class="ss-trigger" tabindex="0">
                                <span class="ss-value placeholder">All schools</span>
                                <i class="fas fa-chevron-down ss-arrow"></i>
                            </div>
                            <div class="ss-dropdown">
                                <div class="ss-search"><input type="text" placeholder="Type to search…"></div>
                                <div class="ss-options">
                                    <div class="ss-option" data-value="">All schools <i class="fas fa-check check-icon ml-auto"></i></div>
                                    @foreach($schools as $s)
                                        <div class="ss-option" data-value="{{ $s->id }}" @if(($filters['school_id'] ?? '') == $s->id) data-selected="true" @endif>
                                            {{ $s->name }} <i class="fas fa-check check-icon ml-auto"></i>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <input type="hidden" name="school_id" value="{{ $filters['school_id'] ?? '' }}">
                        </div>
                    </div>
                @endif

                {{-- Rows per page --}}
                <div class="filter-group">
                    <label>Rows</label>
                    <div class="ss-wrapper" data-name="per_page" data-placeholder="25">
                        <div class="ss-trigger" tabindex="0">
                            <span class="ss-value placeholder">25</span>
                            <i class="fas fa-chevron-down ss-arrow"></i>
                        </div>
                        <div class="ss-dropdown">
                            <div class="ss-search"><input type="text" placeholder="Type to search…"></div>
                            <div class="ss-options">
                                @foreach([25, 50, 100] as $n)
                                    <div class="ss-option" data-value="{{ $n }}" @if(($filters['per_page'] ?? 25) == $n) data-selected="true" @endif>
                                        {{ $n }} <i class="fas fa-check check-icon ml-auto"></i>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <input type="hidden" name="per_page" value="{{ $filters['per_page'] ?? 25 }}">
                    </div>
                </div>

            </div>

            {{-- Hidden filters --}}
            @foreach(['actor_id', 'entity_type', 'entity_id', 'request_id'] as $hidden)
                @if(isset($filters[$hidden]))<input type="hidden" name="{{ $hidden }}" value="{{ $filters[$hidden] }}">@endif
            @endforeach

            <div class="filter-actions">
                <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-filter mr-1"></i>Apply Filters</button>
                <a href="{{ route('audit.index') }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-undo mr-1"></i>Reset</a>
                <span class="ml-auto" style="font-size:.72rem;color:#a0aec0;" id="filterResultCount">
                    {{ $logs->total() }} {{ Str::plural('entry', $logs->total()) }} found
                </span>
            </div>

            @if(isset($filters['actor_id']) || isset($filters['entity_type']) || isset($filters['request_id']))
                <div class="active-filters">
                    <span class="label-sm">Also filtered by:</span>
                    @isset($filters['actor_id']) <span class="au-badge au-c-request">user #{{ $filters['actor_id'] }}</span> @endisset
                    @isset($filters['entity_type']) <span class="au-badge au-c-data">{{ $filters['entity_type'] }}{{ isset($filters['entity_id']) ? ' #' . $filters['entity_id'] : '' }}</span> @endisset
                    @isset($filters['request_id']) <span class="au-badge au-c-system">request {{ \Illuminate\Support\Str::limit($filters['request_id'], 8, '…') }}</span> @endisset
                </div>
            @endif
        </form>
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
</div>

{{-- Searchable Select JS --}}

<script>
(function() {
    'use strict';

    const wrappers = document.querySelectorAll('.ss-wrapper');

    function initSearchableSelect(wrapper) {
        const trigger    = wrapper.querySelector('.ss-trigger');
        const dropdown   = wrapper.querySelector('.ss-dropdown');
        const searchInput= wrapper.querySelector('.ss-search input');
        const optionsBox = wrapper.querySelector('.ss-options');
        const options    = Array.from(optionsBox.querySelectorAll('.ss-option'));
        const hiddenInput= wrapper.querySelector('input[type="hidden"]');
        const valueSpan  = trigger.querySelector('.ss-value');
        const placeholder= wrapper.dataset.placeholder || 'Select…';

        // Set initial display
        let selectedOption = options.find(o => o.dataset.selected === 'true') || options[0];
        if (selectedOption) {
            selectedOption.classList.add('selected');
            hiddenInput.value = selectedOption.dataset.value;
            const text = selectedOption.textContent.replace(/\s+/g, ' ').trim();
            valueSpan.textContent = text;
            valueSpan.classList.toggle('placeholder', selectedOption.dataset.value === '');
        } else {
            valueSpan.textContent = placeholder;
            valueSpan.classList.add('placeholder');
        }

        // Toggle open/close
        trigger.addEventListener('click', function(e) {
            e.stopPropagation();
            const isOpen = wrapper.classList.contains('open');
            closeAll();
            if (!isOpen) {
                wrapper.classList.add('open');
                searchInput.value = '';
                filterOptions('');
                searchInput.focus();
            }
        });

        // Search filtering
        searchInput.addEventListener('input', function() {
            filterOptions(this.value);
        });

        // Stop propagation on search click
        searchInput.addEventListener('click', function(e) {
            e.stopPropagation();
        });

        function filterOptions(query) {
            const q = query.toLowerCase().trim();
            let visibleCount = 0;
            options.forEach(opt => {
                const text = opt.textContent.toLowerCase();
                const match = text.includes(q);
                opt.style.display = match ? '' : 'none';
                if (match) visibleCount++;
            });

            // Remove old no-results
            const oldNo = optionsBox.querySelector('.ss-no-results');
            if (oldNo) oldNo.remove();

            if (visibleCount === 0) {
                const noDiv = document.createElement('div');
                noDiv.className = 'ss-no-results';
                noDiv.textContent = 'No matches found';
                optionsBox.appendChild(noDiv);
            }
        }

        // Select option
        options.forEach(opt => {
            opt.addEventListener('click', function(e) {
                e.stopPropagation();
                selectOption(this);
            });
        });

        function selectOption(opt) {
            options.forEach(o => o.classList.remove('selected'));
            opt.classList.add('selected');
            hiddenInput.value = opt.dataset.value;
            const text = opt.textContent.replace(/\s+/g, ' ').trim();
            valueSpan.textContent = text;
            valueSpan.classList.toggle('placeholder', opt.dataset.value === '');
            wrapper.classList.remove('open');
        }

        // Close on outside click
        document.addEventListener('click', function() {
            wrapper.classList.remove('open');
        });

        // Keyboard nav
        trigger.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                trigger.click();
            }
        });
    }

    function closeAll() {
        wrappers.forEach(w => w.classList.remove('open'));
    }

    wrappers.forEach(initSearchableSelect);
})();
</script>

@endsection 