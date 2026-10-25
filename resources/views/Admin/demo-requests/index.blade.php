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
        .dr-form { display: grid; grid-template-columns: 200px 1fr auto; gap: 10px; align-items: start; }
        @media (max-width: 760px) { .dr-form { grid-template-columns: 1fr; } }
        .dr-form select, .dr-form textarea { border: 1px solid #d1d5db; border-radius: 10px; padding: 8px 12px; width: 100%; font-size: 14px; }
        .dr-form button { border: 0; background: #16a34a; color: #fff; border-radius: 10px; padding: 9px 18px; font-weight: 600; }
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

    @forelse($requests as $r)
        @php $meta = $r->statusMeta(); @endphp
        <div class="dr-card" style="--c:{{ $meta['color'] }}">
            <div class="dr-head">
                <div>
                    <h5 class="dr-school">{{ $r->school_name }}</h5>
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
                    <select name="status">
                        @foreach($statuses as $key => $m)
                            <option value="{{ $key }}" {{ $r->status === $key ? 'selected' : '' }}>{{ $m['label'] }}</option>
                        @endforeach
                    </select>
                    <textarea name="internal_notes" rows="2" placeholder="Internal notes (not visible to the school)">{{ $r->internal_notes }}</textarea>
                    <button type="submit">Save</button>
                </form>
            @elseif($r->internal_notes)
                <div class="dr-msg"><strong>Notes:</strong> {{ $r->internal_notes }}</div>
            @endif
        </div>
    @empty
        <div class="dr-empty"><i class="fas fa-inbox fa-2x mb-2"></i><div>No demo requests {{ $activeStatus || $search ? 'match this filter' : 'yet' }}.</div></div>
    @endforelse

    <div class="mt-3">{{ $requests->links() }}</div>

    {{-- 3 extra closing divs push the footer down (same as the other modules) --}}
    </div>
    </div>
    </div>
@endsection
