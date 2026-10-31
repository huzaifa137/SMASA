@extends('layouts-side-bar.master')

@section('content')
    <style>
        .dr-hero { background: linear-gradient(135deg, #0a1628 0%, #1e293b 100%); border-radius: 16px; padding: 24px 28px; color: #fff; display: flex; align-items: center; gap: 18px; flex-wrap: wrap; }
        .dr-hero-icon { width: 54px; height: 54px; border-radius: 14px; background: rgba(224,192,104,.15); color: #e0c068; display: flex; align-items: center; justify-content: center; font-size: 24px; }
        .dr-hero h4 { margin: 0; color: #fff; font-weight: 700; }
        .dr-hero p { margin: 2px 0 0; color: #cbd5e1; font-size: 13px; }
        .dr-flash { background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 12px 16px; border-radius: 10px; margin: 16px 0 0; font-size: 14px; }

        .dr-tabs { display: grid; grid-template-columns: repeat(6, 1fr); gap: 12px; margin: 20px 0; }
        @media (max-width: 1100px) { .dr-tabs { grid-template-columns: repeat(3, 1fr); } }
        @media (max-width: 560px) { .dr-tabs { grid-template-columns: repeat(2, 1fr); } }
        .dr-tab { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 14px 16px; text-decoration: none; color: inherit; transition: all .18s ease; box-shadow: 0 1px 2px rgba(16,24,40,.04); border-top: 4px solid var(--c, #6b7280); }
        .dr-tab:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(16,24,40,.08); text-decoration: none; color: inherit; }
        .dr-tab.active { background: #f8fafc; border-color: var(--c, #6b7280); }
        .dr-tab .n { font-size: 24px; font-weight: 700; line-height: 1.1; }
        .dr-tab .l { font-size: 12px; color: #6b7280; margin-top: 2px; }

        .dr-search { display: flex; gap: 10px; margin-bottom: 16px; flex-wrap: wrap; }
        .dr-search input { flex: 1; min-width: 220px; border: 1px solid #d1d5db; border-radius: 10px; padding: 10px 14px; }
        .dr-search button { border: 0; background: #2C29CA; color: #fff; border-radius: 10px; padding: 10px 20px; font-weight: 600; }

        .dr-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 14px; padding: 18px 20px; margin-bottom: 14px; box-shadow: 0 1px 2px rgba(16,24,40,.04); border-left: 5px solid var(--c, #6b7280); }
        .dr-head { display: flex; justify-content: space-between; gap: 12px; flex-wrap: wrap; align-items: flex-start; }
        .dr-school { font-size: 17px; font-weight: 700; margin: 0; }
        .dr-ref { font-size: 12px; color: #6b7280; }
        .dr-pill { display: inline-block; padding: 3px 12px; border-radius: 999px; font-size: 12px; font-weight: 600; color: #fff; background: var(--c, #6b7280); }
        .dr-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 12px 20px; margin: 14px 0; font-size: 14px; }
        .dr-grid .k { font-size: 11px; text-transform: uppercase; letter-spacing: .06em; color: #9ca3af; display: block; }
        .dr-msg { background: #f9fafb; border-radius: 10px; padding: 10px 14px; font-size: 14px; white-space: pre-line; margin-bottom: 12px; }
        .dr-form { display: grid; grid-template-columns: 230px 1fr auto; gap: 12px; align-items: stretch; }
        @media (max-width: 760px) { .dr-form { grid-template-columns: 1fr; } }
        /* Status dropdown and the notes box are the same height and sit on the same line */
        .dr-form select, .dr-form textarea { border: 2px solid #d1d5db; border-radius: 10px; padding: 10px 14px; width: 100%; font-size: 15px; min-height: 96px; height: 100%; }
        .dr-form textarea { resize: vertical; }
        .dr-form select { font-weight: 700; cursor: pointer; text-align-last: center; }
        /* Coloured by the selected status (set inline + updated live in the script below) */
        .dr-form select.dr-status { background-color: var(--c, #fff); border-color: var(--c, #d1d5db); color: #fff; }
        .dr-form select.dr-status option { background: #fff; color: #111827; font-weight: 600; }
        .dr-btns { display: flex; flex-direction: column; gap: 8px; justify-content: stretch; }
        .dr-btns button { flex: 1; min-height: 44px; border: 0; color: #fff; border-radius: 10px; padding: 9px 18px; font-weight: 600; white-space: nowrap; }
        .dr-btns .dr-save { background: #16a34a; }
        .dr-btns .dr-del { background: #fff; color: #dc2626; border: 2px solid #fecaca; }
        .dr-btns .dr-del:hover { background: #dc2626; color: #fff; border-color: #dc2626; }
        .dr-bulk { display: flex; align-items: center; gap: 14px; flex-wrap: wrap; background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 10px 16px; margin-bottom: 14px; font-size: 14px; }
        .dr-bulk button { border: 0; background: #dc2626; color: #fff; border-radius: 8px; padding: 7px 16px; font-weight: 600; }
        .dr-bulk button:disabled { opacity: .45; cursor: not-allowed; }
        .dr-check { width: 18px; height: 18px; margin-right: 10px; cursor: pointer; vertical-align: middle; }
        .dr-warn { color: #b45309; font-size: 12px; margin-top: 6px; }
        .dr-actions a { margin-right: 14px; font-size: 13px; font-weight: 600; }
        .dr-empty { text-align: center; color: #6b7280; padding: 50px 0; background: #fff; border: 1px dashed #d1d5db; border-radius: 14px; }
    </style>

    <div class="dr-hero mt-5">
        <div class="dr-hero-icon"><i class="fas fa-envelope-open-text"></i></div>
        <div>
            <h4>Demo Requests</h4>
            <p>Schools that asked for a demo from the website. Each one is also emailed to {{ implode(', ', config('demo_requests.notify_to')) ?: 'no one (set DEMO_REQUEST_NOTIFY_TO)' }}.</p>
        </div>
    </div>

    @if(session('success'))<div class="dr-flash">{{ session('success') }}</div>@endif

    <div class="dr-tabs">
        <a href="{{ route('demo-requests.index') }}" class="dr-tab {{ !$activeStatus ? 'active' : '' }}" style="--c:#0a1628">
            <div class="n">{{ $total }}</div><div class="l">All requests</div>
        </a>
        @foreach($statuses as $key => $meta)
            <a href="{{ route('demo-requests.index', ['status' => $key]) }}" class="dr-tab {{ $activeStatus === $key ? 'active' : '' }}" style="--c:{{ $meta['color'] }}">
                <div class="n">{{ $counts[$key] ?? 0 }}</div><div class="l">{{ $meta['label'] }}</div>
            </a>
        @endforeach
    </div>

    <form method="GET" class="dr-search">
        @if($activeStatus)<input type="hidden" name="status" value="{{ $activeStatus }}">@endif
        <input type="text" name="q" value="{{ $search }}" placeholder="Search school, person, phone, email or reference">
        <button type="submit"><i class="fas fa-search mr-1"></i> Search</button>
    </form>

    @if($canDelete && $requests->count())
        <form id="drBulkForm" method="POST" action="{{ route('demo-requests.bulk-destroy') }}">@csrf</form>
        <div class="dr-bulk">
            <label class="mb-0"><input type="checkbox" class="dr-check" id="drCheckAll"> Select all on this page</label>
            <button type="submit" form="drBulkForm" id="drBulkBtn" disabled><i class="fas fa-trash mr-1"></i> Delete selected (<span id="drSel">0</span>)</button>
        </div>
    @endif

    @forelse($requests as $r)
        @php $meta = $r->statusMeta(); @endphp
        <div class="dr-card" style="--c:{{ $meta['color'] }}">
            <div class="dr-head">
                <div>
                    <h5 class="dr-school">
                        @if($canDelete)<input type="checkbox" class="dr-check dr-pick" name="ids[]" value="{{ $r->id }}" form="drBulkForm">@endif{{ $r->school_name }}
                    </h5>
                    <div class="dr-ref">{{ $r->reference }} &middot; {{ $r->created_at->format('d M Y, H:i') }} ({{ $r->created_at->diffForHumans() }})</div>
                </div>
                <span class="dr-pill">{{ $meta['label'] }}</span>
            </div>

            <div class="dr-grid">
                <div><span class="k">Contact person</span>{{ $r->contact_person }}</div>
                <div><span class="k">Phone</span><a href="tel:{{ preg_replace('/\s+/', '', $r->phone) }}">{{ $r->phone }}</a></div>
                <div><span class="k">Email</span><a href="mailto:{{ $r->email }}">{{ $r->email }}</a></div>
                <div><span class="k">School type</span>{{ $r->schoolTypeLabel() }}</div>
                <div><span class="k">Students</span>{{ $r->studentRangeLabel() }}</div>
                <div><span class="k">Team emailed</span>
                    @if($r->notified_at)<span class="text-success">Yes, {{ $r->notified_at->format('d M H:i') }}</span>
                    @else<span class="text-danger">No</span>@endif
                </div>
            </div>

            @if($r->message)<div class="dr-msg">{{ $r->message }}</div>@endif
            @if($r->notify_error)<div class="dr-warn"><i class="fas fa-triangle-exclamation"></i> Email to the team failed: {{ \Illuminate\Support\Str::limit($r->notify_error, 160) }}</div>@endif

            <div class="dr-actions mb-2">
                <a href="tel:{{ preg_replace('/\s+/', '', $r->phone) }}"><i class="fas fa-phone"></i> Call</a>
                <a href="https://wa.me/{{ preg_replace('/\D+/', '', $r->phone) }}" target="_blank" rel="noopener"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                <a href="mailto:{{ $r->email }}?subject={{ rawurlencode('Your SMASA demo (' . $r->reference . ')') }}"><i class="fas fa-envelope"></i> Email</a>
            </div>

            @if($canManage)
                <form method="POST" action="{{ route('demo-requests.update', $r->id) }}" class="dr-form">
                    @csrf
                    <select name="status" class="dr-status" style="--c:{{ $meta['color'] }}">
                        @foreach($statuses as $key => $m)
                            <option value="{{ $key }}" data-color="{{ $m['color'] }}" {{ $r->status === $key ? 'selected' : '' }}>{{ $m['label'] }}</option>
                        @endforeach
                    </select>
                    <textarea name="internal_notes" rows="3" placeholder="Internal notes (not visible to the school)">{{ $r->internal_notes }}</textarea>
                    <div class="dr-btns">
                        <button type="submit" class="dr-save"><i class="fas fa-save mr-1"></i> Save</button>
                        @if($canDelete)
                            <button type="submit" form="drDel{{ $r->id }}" class="dr-del" data-name="{{ $r->school_name }}"><i class="fas fa-trash mr-1"></i> Delete</button>
                        @endif
                    </div>
                </form>
                @if($canDelete)
                    <form id="drDel{{ $r->id }}" method="POST" action="{{ route('demo-requests.destroy', $r->id) }}" class="dr-del-form" data-name="{{ $r->school_name }}">@csrf @method('DELETE')</form>
                @endif
            @elseif($r->internal_notes)
                <div class="dr-msg"><strong>Notes:</strong> {{ $r->internal_notes }}</div>
            @endif
        </div>
    @empty
        <div class="dr-empty"><i class="fas fa-inbox fa-2x mb-2"></i><div>No demo requests {{ $activeStatus || $search ? 'match this filter' : 'yet' }}.</div></div>
    @endforelse

    <div class="mt-3">{{ $requests->links() }}</div>

    <script>
        (function () {
            // Colour the status dropdown by the selected status, live.
            document.querySelectorAll('select.dr-status').forEach(function (sel) {
                sel.addEventListener('change', function () {
                    var c = sel.options[sel.selectedIndex].dataset.color;
                    if (c) sel.style.setProperty('--c', c);
                });
            });

            // Confirm before deleting one request.
            document.querySelectorAll('.dr-del-form').forEach(function (f) {
                f.addEventListener('submit', function (e) {
                    if (!window.confirm('Permanently delete the demo request from "' + f.dataset.name + '"? This cannot be undone.')) e.preventDefault();
                });
            });

            // Bulk selection.
            var picks = Array.prototype.slice.call(document.querySelectorAll('.dr-pick'));
            var all = document.getElementById('drCheckAll'), btn = document.getElementById('drBulkBtn'), cnt = document.getElementById('drSel'), bulk = document.getElementById('drBulkForm');
            function refresh() {
                var n = picks.filter(function (p) { return p.checked; }).length;
                if (cnt) cnt.textContent = n;
                if (btn) btn.disabled = n === 0;
                if (all) all.checked = n > 0 && n === picks.length;
            }
            if (all) all.addEventListener('change', function () { picks.forEach(function (p) { p.checked = all.checked; }); refresh(); });
            picks.forEach(function (p) { p.addEventListener('change', refresh); });
            if (bulk) bulk.addEventListener('submit', function (e) {
                var n = picks.filter(function (p) { return p.checked; }).length;
                if (!n || !window.confirm('Permanently delete ' + n + ' selected demo request(s)? This cannot be undone.')) e.preventDefault();
            });
        })();
    </script>

    {{-- 3 extra closing divs push the footer down (same as the other modules) --}}
    </div>
    </div>
    </div>
@endsection
