@extends('layouts-side-bar.master')

@section('content')
<style>
    .crc-page { --r:16px; --line:#e5e8f0; --muted:#68718a; --ink:#141b2d; --bg:#f5f7fb; --brand:#3b5bfd; --brand-soft:#eaeefe; --ok:#12a150; --ok-soft:#e3f6ec; --warn:#d98200; --warn-soft:#fff3d9; --danger:#d93a3a; }
    .crc-page h3, .crc-page h5, .crc-page h6 { color:var(--ink); }

    /* ---------- Hero + stats ---------- */
    .crc-hero { display:flex; justify-content:space-between; align-items:flex-end; flex-wrap:wrap; gap:16px; margin-bottom:18px; }
    .crc-hero h3 { font-weight:800; letter-spacing:-.02em; margin:0 0 4px; }
    .crc-hero p { margin:0; color:var(--muted); max-width:640px; }

    .crc-stats { display:grid; grid-template-columns:repeat(4, minmax(0,1fr)); gap:14px; }
    .crc-stat { background:#fff; border:1px solid var(--line); border-radius:14px; padding:16px 20px; width:100%; box-shadow:0 1px 2px rgba(20,27,45,.04); }
    .crc-stat b { display:block; font-size:1.7rem; line-height:1.1; color:var(--ink); }
    .crc-stat span { display:block; margin-top:4px; font-size:.72rem; text-transform:uppercase; letter-spacing:.06em; color:var(--muted); font-weight:600; }
    @media (max-width: 767px){ .crc-stats { grid-template-columns:repeat(2, minmax(0,1fr)); } }
    @media (max-width: 420px){ .crc-stats { grid-template-columns:1fr; } }

    /* ---------- Shell ---------- */
    .crc-shell { display:grid; grid-template-columns:340px minmax(0,1fr); gap:20px; align-items:start; margin-top:6px; }
    @media (max-width: 991px){ .crc-shell { grid-template-columns:1fr; } .crc-side { position:static !important; } .crc-list { max-height:280px !important; } }

    /* ---------- Sidebar ---------- */
    .crc-side { position:sticky; top:16px; background:#fff; border:1px solid var(--line); border-radius:var(--r); overflow:hidden; box-shadow:0 1px 2px rgba(20,27,45,.04); }
    .crc-side-head { padding:16px; border-bottom:1px solid var(--line); background:#eef2ff; }
    .crc-search-label { font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:var(--brand); margin-bottom:6px; display:block; }
    .crc-search { position:relative; }
    .crc-search i { position:absolute; left:14px; top:50%; transform:translateY(-50%); color:var(--brand); font-size:.9rem; }
    .crc-search input { padding-left:40px; border-radius:12px; border:2px solid #b9c3dd; background:#fff; height:46px; font-weight:500; color:var(--ink); box-shadow:0 2px 6px rgba(59,91,253,.10); }
    .crc-search input::placeholder { color:#8a93ab; font-weight:400; }
    .crc-search input:hover { border-color:#8fa0d4; }
    .crc-search input:focus { border-color:var(--brand); box-shadow:0 0 0 4px rgba(59,91,253,.18); outline:0; }
    .crc-seg { display:flex; background:#dfe5f6; border-radius:10px; padding:3px; margin-top:12px; }
    .crc-seg button { flex:1; border:0; background:transparent; padding:7px 4px; font-size:.76rem; font-weight:600; color:var(--muted); border-radius:8px; cursor:pointer; transition:.15s; }
    .crc-seg button.active { background:#fff; color:var(--brand); box-shadow:0 1px 3px rgba(20,27,45,.12); }
    .crc-count { font-size:.72rem; color:var(--muted); padding:10px 16px 0; text-transform:uppercase; letter-spacing:.05em; }

    .crc-list { max-height:calc(100vh - 360px); min-height:260px; overflow-y:auto; padding:8px; }
    .crc-item { width:100%; text-align:left; border:1px solid transparent; background:transparent; border-radius:12px; padding:10px 12px; display:flex; align-items:center; gap:12px; cursor:pointer; transition:.12s; }
    .crc-item:hover { background:var(--bg); }
    .crc-item.active { background:var(--brand-soft); border-color:#cfd8ff; }
    .crc-av { width:38px; height:38px; border-radius:11px; flex-shrink:0; display:flex; align-items:center; justify-content:center; color:#fff; font-weight:700; text-transform:uppercase; background:linear-gradient(135deg,#3b5bfd,#8a4dff); }
    .crc-item-main { min-width:0; flex:1; }
    .crc-item-name { font-weight:600; color:var(--ink); font-size:.92rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .crc-dots { display:flex; gap:4px; margin-top:6px; flex-wrap:wrap; }
    .crc-dot { width:9px; height:9px; border-radius:50%; background:#d5dae6; }
    .crc-dot.on { background:var(--ok); } .crc-dot.paused { background:var(--warn); }
    .crc-item-n { font-size:.74rem; color:var(--muted); font-weight:600; white-space:nowrap; }

    /* ---------- Workspace ---------- */
    .crc-work { min-width:0; }
    .crc-ws-head { background:#fff; border:1px solid var(--line); border-radius:var(--r); padding:20px 22px; margin-bottom:16px; display:flex; align-items:center; gap:16px; flex-wrap:wrap; box-shadow:0 1px 2px rgba(20,27,45,.04); }
    .crc-ws-head .crc-av { width:52px; height:52px; border-radius:15px; font-size:1.25rem; }
    .crc-ws-title { margin:0; font-weight:700; letter-spacing:-.01em; }
    .crc-ws-sub { color:var(--muted); font-size:.85rem; }
    .crc-prog { margin-left:auto; min-width:200px; }
    .crc-prog-top { display:flex; justify-content:space-between; font-size:.74rem; color:var(--muted); font-weight:600; margin-bottom:6px; }
    .crc-bar { height:8px; background:#e9edf5; border-radius:99px; overflow:hidden; }
    .crc-bar i { display:block; height:100%; background:linear-gradient(90deg,var(--brand),#8a4dff); border-radius:99px; }

    /* ---------- Level rows ---------- */
    .crc-levels { display:flex; flex-direction:column; gap:12px; }
    .crc-group-label { font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.08em; color:var(--muted); margin:10px 2px 0; display:flex; align-items:center; gap:10px; }
    .crc-group-label:first-child { margin-top:0; }
    .crc-group-label::after { content:""; flex:1; height:1px; background:var(--line); }

    .crc-card { background:#fff; border:1px solid var(--line); border-left:5px solid #d5dae6; border-radius:var(--r); box-shadow:0 1px 2px rgba(20,27,45,.04); transition:box-shadow .15s; }
    .crc-card:hover { box-shadow:0 8px 24px rgba(20,27,45,.08); }
    .crc-card.st-on { border-left-color:var(--ok); }
    .crc-card.st-paused { border-left-color:var(--warn); }
    .crc-card.st-empty { border-left-color:#c3cbe0; background:#fcfdff; }

    /* Row layout (flex, wraps naturally) */
    .crc-a { display:flex; flex-wrap:wrap; align-items:center; gap:16px 26px; padding:18px 22px; margin:0; }
    .crc-a-main { display:flex; align-items:center; gap:14px; flex:1 1 300px; min-width:0; }
    .crc-a-icon { width:46px; height:46px; border-radius:13px; flex-shrink:0; display:flex; align-items:center; justify-content:center; font-size:1.15rem; background:var(--brand-soft); color:var(--brand); }
    .crc-a-icon.is-ok { background:var(--ok-soft); color:var(--ok); }
    .crc-a-icon.is-warn { background:var(--warn-soft); color:var(--warn); }
    .crc-a-icon.is-none { background:#eef1f8; color:var(--muted); }
    .crc-a-info { min-width:0; }
    .crc-label { font-size:.68rem; text-transform:uppercase; letter-spacing:.07em; color:var(--muted); font-weight:700; margin-bottom:3px; }
    .crc-design-name { font-size:1.08rem; font-weight:700; color:var(--ink); line-height:1.25; word-break:break-word; }
    .crc-design-name.is-muted { color:var(--muted); font-weight:600; }
    .crc-chips { display:flex; flex-wrap:wrap; gap:6px; margin-top:8px; }

    .crc-a-pick { flex:1 1 220px; max-width:380px; min-width:0; }
    .crc-a-pick select { width:100%; border-radius:10px; border:1.5px solid #c3cbe0; height:44px; }
    .crc-a-pick select:focus { border-color:var(--brand); box-shadow:0 0 0 3px rgba(59,91,253,.14); }

    .crc-a-controls { display:flex; flex-wrap:wrap; align-items:center; gap:10px; margin-left:auto; }
    .crc-btns { display:flex; gap:8px; }
    .crc-btns .btn, .crc-a-controls > .btn { border-radius:10px; font-weight:600; height:44px; padding:0 18px; }

    /* Self-contained switch (no Bootstrap dependency) */
    .crc-sw { position:relative; display:flex; align-items:center; gap:10px; border:1px solid var(--line); border-radius:12px; padding:8px 14px; background:var(--bg); cursor:pointer; margin:0; user-select:none; min-height:44px; }
    .crc-sw:hover { border-color:#c3cbe0; }
    .crc-sw input { position:absolute; opacity:0; pointer-events:none; }
    .crc-sw-track { width:38px; height:22px; border-radius:99px; background:#cbd2e3; position:relative; flex-shrink:0; transition:background .15s; }
    .crc-sw-track::after { content:""; position:absolute; top:3px; left:3px; width:16px; height:16px; border-radius:50%; background:#fff; box-shadow:0 1px 3px rgba(20,27,45,.3); transition:transform .15s; }
    .crc-sw input:checked + .crc-sw-track { background:var(--brand); }
    .crc-sw input:checked + .crc-sw-track::after { transform:translateX(16px); }
    .crc-sw input:focus-visible + .crc-sw-track { box-shadow:0 0 0 3px rgba(59,91,253,.25); }
    .crc-sw-text { display:flex; flex-direction:column; line-height:1.2; }
    .crc-sw-text b { font-size:.84rem; font-weight:700; color:var(--ink); }
    .crc-sw-text small { font-size:.7rem; color:var(--muted); display:block; }

    /* Compact "no design registered" row */
    .crc-a.crc-a-compact { padding:14px 22px; }
    .crc-empty-title { font-weight:600; color:var(--ink); font-size:.95rem; }
    .crc-empty-sub { color:var(--muted); font-size:.82rem; margin-top:2px; }

    @media (max-width: 767px){
        .crc-a-controls { margin-left:0; width:100%; }
        .crc-a-pick { max-width:none; flex-basis:100%; }
    }

    .crc-placeholder { display:none; text-align:center; background:#fff; border:1px dashed var(--line); border-radius:var(--r); padding:70px 20px; color:var(--muted); }
    .crc-placeholder i { font-size:2.4rem; opacity:.5; display:block; margin-bottom:10px; }
    .crc-panel[hidden] { display:none; }
</style>

<div class="container-fluid pt-4 crc-page">

    <div class="crc-hero">
        <div>
            <h3>Custom Report Cards — School assignments</h3>
            <p>A school with a custom design sees it as the default in its Pass Slips designer, on every print route and in the parent portal.</p>
        </div>
    </div>

    @include('Admin.custom-report-cards._nav')

    @if($ready)

        @php
            $levelCount     = count($levels);
            $schoolsCustom  = 0;
            $coverage       = [];
            foreach ($schools as $school) {
                $n = 0;
                foreach ($levels as $lk => $ll) {
                    if ($assignments->get($school->id . '|' . $lk)) { $n++; }
                }
                $coverage[$school->id] = $n;
                if ($n > 0) { $schoolsCustom++; }
            }
            $assignCount = $assignments->count();
            $activeCount = $assignments->filter(fn($a) => $a->is_active)->count();
            $lockedCount = $assignments->filter(fn($a) => $a->lock_to_custom)->count();
        @endphp

        <div class="crc-stats mb-3">
            <div class="crc-stat"><b>{{ count($schools) }}</b><span>Schools</span></div>
            <div class="crc-stat"><b>{{ $schoolsCustom }}</b><span>With custom design</span></div>
            <div class="crc-stat"><b>{{ $activeCount }}<small class="text-muted" style="font-size:.9rem;"> / {{ $assignCount }}</small></b><span>Active assignments</span></div>
            <div class="crc-stat"><b>{{ $lockedCount }}</b><span>Locked</span></div>
        </div>

        <div class="crc-shell">

            {{-- ============ SIDEBAR: school list ============ --}}
            <aside class="crc-side">
                <div class="crc-side-head">
                    <label for="schoolFilter" class="crc-search-label">Find a school</label>
                    <div class="crc-search">
                        <i class="fas fa-search"></i>
                        <input type="search" id="schoolFilter" class="form-control" placeholder="Type a school name…" autocomplete="off">
                    </div>
                    <div class="crc-seg" id="schoolPills">
                        <button type="button" class="active" data-mode="all">All</button>
                        <button type="button" data-mode="custom">Custom</button>
                        <button type="button" data-mode="standard">Standard only</button>
                    </div>
                </div>
                <div class="crc-count"><span id="visibleCount">{{ count($schools) }}</span> school(s)</div>

                <div class="crc-list" id="schoolList">
                    @foreach($schools as $school)
                        <button type="button" class="crc-item"
                                data-target="school-{{ $school->id }}"
                                data-name="{{ strtolower($school->name) }}"
                                data-assigned="{{ $coverage[$school->id] }}">
                            <div class="crc-av">{{ mb_substr($school->name, 0, 1) }}</div>
                            <div class="crc-item-main">
                                <div class="crc-item-name">{{ $school->name }}</div>
                                <div class="crc-dots">
                                    @foreach($levels as $lk => $ll)
                                        @php $d = $assignments->get($school->id . '|' . $lk); @endphp
                                        <span class="crc-dot {{ $d ? ($d->is_active ? 'on' : 'paused') : '' }}"
                                              title="{{ $ll }}: {{ $d ? ($d->template->name ?? 'Custom') . ($d->is_active ? '' : ' (paused)') : 'Standard' }}"></span>
                                    @endforeach
                                </div>
                            </div>
                            <div class="crc-item-n">{{ $coverage[$school->id] }}/{{ $levelCount }}</div>
                        </button>
                    @endforeach
                </div>
            </aside>

            {{-- ============ WORKSPACE: selected school ============ --}}
            <main class="crc-work">

                <div class="crc-placeholder" id="noResults">
                    <i class="fas fa-search"></i>
                    <p class="mb-0">No schools match your filter.</p>
                </div>

                @foreach($schools as $school)
                    @php
                        // Levels with a design set come first, then the rest (original order kept inside each group)
                        $withDesign = [];
                        $withoutDesign = [];
                        foreach ($levels as $lk => $ll) {
                            if ($assignments->get($school->id . '|' . $lk)) { $withDesign[$lk] = $ll; }
                            else { $withoutDesign[$lk] = $ll; }
                        }
                        $orderedLevels = $withDesign + $withoutDesign;
                        $prevAssigned  = null;
                    @endphp

                    <section class="crc-panel" id="school-{{ $school->id }}" hidden>

                        <div class="crc-ws-head">
                            <div class="crc-av">{{ mb_substr($school->name, 0, 1) }}</div>
                            <div>
                                <h5 class="crc-ws-title">{{ $school->name }}</h5>
                                <div class="crc-ws-sub">Choose which design each level uses.</div>
                            </div>
                            <div class="crc-prog">
                                <div class="crc-prog-top">
                                    <span>Custom coverage</span>
                                    <span>{{ $coverage[$school->id] }} of {{ $levelCount }} levels</span>
                                </div>
                                <div class="crc-bar"><i style="width:{{ $levelCount ? round($coverage[$school->id] / $levelCount * 100) : 0 }}%"></i></div>
                            </div>
                        </div>

                        <div class="crc-levels">
                            @foreach($orderedLevels as $lk => $ll)
                                @php
                                    $a = $assignments->get($school->id . '|' . $lk);
                                    $isAssigned = (bool) $a;
                                @endphp

                                @if($prevAssigned !== $isAssigned)
                                    <div class="crc-group-label">{{ $isAssigned ? 'Custom design set' : 'Using standard design' }}</div>
                                @endif

                                @if($a)
                                    {{-- ===== Assigned level ===== --}}
                                    <div class="crc-card {{ $a->is_active ? 'st-on' : 'st-paused' }}">
                                        <form method="POST" action="{{ route('admin.custom-report-cards.assignments.update', $a->id) }}" class="crc-a">
                                            @csrf @method('PUT')

                                            <div class="crc-a-main">
                                                <div class="crc-a-icon {{ $a->is_active ? 'is-ok' : 'is-warn' }}"><i class="fas fa-file-alt"></i></div>
                                                <div class="crc-a-info">
                                                    <div class="crc-label">Assigned design</div>
                                                    <div class="crc-design-name">{{ $a->template->name ?? '—' }}</div>
                                                    <div class="crc-chips">
                                                        <span class="crc-badge crc-b-{{ $lk }}">{{ $ll }}</span>
                                                        <span class="crc-badge {{ $a->is_active ? 'crc-b-on' : 'crc-b-off' }}">{{ $a->is_active ? 'Active' : 'Paused' }}</span>
                                                        @if($a->lock_to_custom)<span class="crc-badge crc-b-lock"><i class="fas fa-lock"></i> Locked</span>@endif
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="crc-a-controls">
                                                <label class="crc-sw" for="act-{{ $a->id }}">
                                                    <input type="checkbox" id="act-{{ $a->id }}" name="is_active" value="1" {{ $a->is_active ? 'checked' : '' }}>
                                                    <span class="crc-sw-track"></span>
                                                    <span class="crc-sw-text"><b>Active</b><small>Use this design</small></span>
                                                </label>
                                                <label class="crc-sw" for="lock-{{ $a->id }}">
                                                    <input type="checkbox" id="lock-{{ $a->id }}" name="lock_to_custom" value="1" {{ $a->lock_to_custom ? 'checked' : '' }}>
                                                    <span class="crc-sw-track"></span>
                                                    <span class="crc-sw-text"><b>Locked</b><small>Can't switch back</small></span>
                                                </label>
                                                <div class="crc-btns">
                                                    <button type="button" class="btn btn-success" onclick="confirmAssignmentSave(this)">
                                                        <i class="fas fa-save mr-1"></i>Save
                                                    </button>
                                                    <button type="button" class="btn btn-outline-danger"
                                                            onclick="confirmAssignmentRemove({{ $a->id }}, '{{ addslashes($a->template->name ?? 'this design') }}', '{{ addslashes($school->name) }}', '{{ addslashes($ll) }}')"
                                                            title="Remove assignment">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                        <form id="un-{{ $a->id }}" method="POST" action="{{ route('admin.custom-report-cards.assignments.delete', $a->id) }}">@csrf @method('DELETE')</form>
                                    </div>

                                @else
                                    {{-- ===== Unassigned level ===== --}}
                                    @php $opts = $templates->where('level', $lk); @endphp
                                    <div class="crc-card st-empty">
                                        @if($opts->isEmpty())
                                            <div class="crc-a crc-a-compact">
                                                <div class="crc-a-main">
                                                    <div class="crc-a-icon is-none"><i class="fas fa-info"></i></div>
                                                    <div class="crc-a-info">
                                                        <div class="crc-empty-title">Standard design</div>
                                                        <div class="crc-empty-sub">No {{ strtolower($ll) }} custom design registered</div>
                                                    </div>
                                                </div>
                                                <div class="crc-a-controls">
                                                    <span class="crc-badge crc-b-{{ $lk }}">{{ $ll }}</span>
                                                </div>
                                            </div>
                                        @else
                                            <form method="POST" action="{{ route('admin.custom-report-cards.assign') }}" class="crc-a">
                                                @csrf
                                                <input type="hidden" name="school_id" value="{{ $school->id }}">
                                                <input type="hidden" name="level" value="{{ $lk }}">

                                                <div class="crc-a-main">
                                                    <div class="crc-a-icon is-none"><i class="fas fa-file"></i></div>
                                                    <div class="crc-a-info">
                                                        <div class="crc-label">Currently using</div>
                                                        <div class="crc-design-name is-muted">Standard design</div>
                                                        <div class="crc-chips"><span class="crc-badge crc-b-{{ $lk }}">{{ $ll }}</span></div>
                                                    </div>
                                                </div>

                                                <div class="crc-a-pick">
                                                    <div class="crc-label">Custom design</div>
                                                    <select name="template_id" class="form-control" required>
                                                        <option value="">Standard design</option>
                                                        @foreach($opts as $o)<option value="{{ $o->id }}">{{ $o->name }}</option>@endforeach
                                                    </select>
                                                </div>

                                                <div class="crc-a-controls">
                                                    <label class="crc-sw" for="newlock-{{ $school->id }}-{{ $lk }}">
                                                        <input type="checkbox" id="newlock-{{ $school->id }}-{{ $lk }}" name="lock_to_custom" value="1" checked>
                                                        <span class="crc-sw-track"></span>
                                                        <span class="crc-sw-text"><b>Lock to custom</b><small>Can't switch back</small></span>
                                                    </label>
                                                    <button type="button" class="btn btn-primary" onclick="confirmAssign(this)">
                                                        <i class="fas fa-plus mr-1"></i>Assign design
                                                    </button>
                                                </div>
                                            </form>
                                        @endif
                                    </div>
                                @endif

                                @php $prevAssigned = $isAssigned; @endphp
                            @endforeach
                        </div>
                    </section>
                @endforeach
            </main>
        </div>
    @endif
</div>
</div>
</div>
</div>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
(function () {
    const KEY    = 'crc_assign_school';
    const input  = document.getElementById('schoolFilter');
    const pills  = document.querySelectorAll('#schoolPills button');
    const items  = Array.from(document.querySelectorAll('#schoolList .crc-item'));
    const panels = Array.from(document.querySelectorAll('.crc-panel'));
    const noRes  = document.getElementById('noResults');
    const countEl = document.getElementById('visibleCount');
    let mode = 'all';

    function select(targetId, remember) {
        items.forEach(i => i.classList.toggle('active', i.dataset.target === targetId));
        panels.forEach(p => { p.hidden = (p.id !== targetId); });
        if (noRes) noRes.style.display = targetId ? 'none' : 'block';
        if (targetId && remember !== false) { try { sessionStorage.setItem(KEY, targetId); } catch (e) {} }
    }

    function apply() {
        const q = (input ? input.value : '').toLowerCase();
        let visible = [];
        items.forEach(item => {
            const assigned = parseInt(item.dataset.assigned, 10) > 0;
            const ok = item.dataset.name.includes(q) &&
                       (mode === 'all' || (mode === 'custom' && assigned) || (mode === 'standard' && !assigned));
            item.style.display = ok ? '' : 'none';
            if (ok) visible.push(item);
        });
        if (countEl) countEl.textContent = visible.length;

        const current = items.find(i => i.classList.contains('active'));
        if (!current || current.style.display === 'none') {
            select(visible.length ? visible[0].dataset.target : null, false);
        }
    }

    items.forEach(i => i.addEventListener('click', () => select(i.dataset.target)));
    input?.addEventListener('input', apply);
    pills.forEach(p => p.addEventListener('click', function () {
        pills.forEach(x => x.classList.remove('active'));
        this.classList.add('active');
        mode = this.dataset.mode;
        apply();
    }));

    // Restore last selected school (survives Save / Assign / Remove redirects)
    let saved = null;
    try { saved = sessionStorage.getItem(KEY); } catch (e) {}
    const start = items.find(i => i.dataset.target === saved) || items[0];
    if (start) {
        select(start.dataset.target, false);
        start.scrollIntoView({ block: 'nearest' });
    } else if (noRes) {
        noRes.style.display = 'block';
    }
})();

/* ---------- SweetAlert confirmation helpers ---------- */

function confirmAssignmentSave(button) {
    const form = button.closest('form');
    Swal.fire({
        title: 'Save Assignment?',
        text: 'Save the changes to this level assignment?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#12a150',
        cancelButtonColor: '#68718a',
        confirmButtonText: 'Yes, save it',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            form.submit();
        }
    });
}

function confirmAssignmentRemove(id, designName, schoolName, levelName) {
    Swal.fire({
        title: 'Remove Assignment?',
        html: 'Remove the <strong>' + designName + '</strong> design from <strong>' + schoolName + '</strong> (' + levelName + ')?<br><small class="text-muted">This will switch this level back to the standard design.</small>',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d93a3a',
        cancelButtonColor: '#68718a',
        confirmButtonText: 'Yes, remove it',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('un-' + id).submit();
        }
    });
}

function confirmAssign(button) {
    const form = button.closest('form');
    const select = form.querySelector('select[name="template_id"]');
    if (!select.value) {
        Swal.fire({
            title: 'No design selected',
            text: 'Please choose a custom design from the dropdown first.',
            icon: 'info',
            confirmButtonColor: '#3b5bfd',
            confirmButtonText: 'OK'
        });
        return;
    }
    const designName = select.options[select.selectedIndex].text;
    Swal.fire({
        title: 'Assign Design?',
        html: 'Assign <strong>' + designName + '</strong> to this level?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3b5bfd',
        cancelButtonColor: '#68718a',
        confirmButtonText: 'Yes, assign it',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            form.submit();
        }
    });
}
</script>
@endsection