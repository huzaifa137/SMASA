@extends('layouts-side-bar.master')

@section('content')
    <div class="side-app">
        <style>
            .sp-wrap { display: grid; grid-template-columns: 300px minmax(0, 1fr); gap: 1.25rem; align-items: start; }
            @media (max-width: 991px) { .sp-wrap { grid-template-columns: 1fr; } }

            .sp-hero { background: linear-gradient(135deg, #0F0E1A 0%, #1B1D28 40%, #2C29CA 100%); color: #fff; border-radius: 1rem; padding: 1.1rem 1.4rem; margin-bottom: 1.25rem; }
            .sp-hero h3 { margin: 0; font-weight: 800; font-size: 1.25rem; color: #fff; }
            .sp-hero p { margin: .35rem 0 0; font-size: .85rem; color: rgba(255, 255, 255, .8); max-width: 62rem; }

            .sp-panel { background: #fff; border-radius: 1rem; box-shadow: 0 2px 10px rgba(15, 14, 26, .07); }
            .sp-panel-hd { padding: .9rem 1.1rem; border-bottom: 1px solid #eef0f5; display: flex; align-items: center; justify-content: space-between; gap: .75rem; flex-wrap: wrap; }
            .sp-panel-hd h5 { margin: 0; font-size: 1rem; font-weight: 800; color: #1B1D28; }
            .sp-panel-hd small { color: #6b7280; display: block; font-weight: 500; font-size: .76rem; }
            .sp-panel-bd { padding: 1rem 1.1rem; }

            .sp-search { width: 100%; border: 1px solid #d9dce6; border-radius: .6rem; padding: .5rem .75rem; font-size: .88rem; margin-bottom: .6rem; }
            .sp-list { max-height: 68vh; overflow-y: auto; }
            .sp-subject { display: block; width: 100%; text-align: left; background: #fff; border: 1px solid #e5e7eb; border-radius: .7rem; padding: .6rem .75rem; margin-bottom: .45rem; cursor: pointer; transition: all .15s; }
            .sp-subject:hover { border-color: #5351e4; background: #f5f5ff; }
            .sp-subject.active { border-color: #2C29CA; background: #eeeeff; box-shadow: 0 0 0 2px rgba(44, 41, 202, .12); }
            .sp-subject strong { display: block; font-size: .9rem; color: #1B1D28; }
            .sp-badges { margin-top: .25rem; display: flex; gap: .3rem; flex-wrap: wrap; }
            .sp-badge { font-size: .68rem; font-weight: 700; padding: .12rem .5rem; border-radius: 999px; background: #eef0f5; color: #4b5563; }
            .sp-badge.ok { background: #dcfce7; color: #166534; }
            .sp-badge.warn { background: #fef3c7; color: #92400e; }
            .sp-badge.teal { background: #ccfbf1; color: #0f766e; }

            .sp-empty { text-align: center; color: #6b7280; padding: 3rem 1rem; }
            .sp-empty i { font-size: 2rem; color: #c7c9d9; margin-bottom: .6rem; display: block; }

            .sp-table { width: 100%; border-collapse: collapse; }
            .sp-table th { font-size: .7rem; text-transform: uppercase; letter-spacing: .04em; color: #6b7280; font-weight: 800; padding: .45rem .5rem; text-align: left; white-space: nowrap; }
            .sp-table td { padding: .4rem .5rem; vertical-align: middle; border-top: 1px solid #f0f1f6; }
            .sp-in { width: 100%; border: 1px solid #d9dce6; border-radius: .5rem; padding: .38rem .55rem; font-size: .84rem; background: #fff; }
            .sp-in:focus { border-color: #5351e4; outline: none; box-shadow: 0 0 0 3px rgba(83, 81, 228, .12); }
            .sp-in:disabled { background: #f3f4f6; color: #6b7280; }
            .sp-pill { display: inline-block; min-width: 2.1rem; text-align: center; font-weight: 800; font-size: .8rem; border-radius: .5rem; padding: .25rem .5rem; background: #2C29CA; color: #fff; }

            .sp-btn { border: 0; border-radius: .6rem; padding: .48rem 1rem; font-weight: 700; font-size: .83rem; cursor: pointer; background: #eef0f5; color: #1B1D28; }
            .sp-btn:hover { background: #e2e5ef; }
            .sp-btn.primary { background: linear-gradient(135deg, #2C29CA, #5351e4); color: #fff; }
            .sp-btn.primary:hover { filter: brightness(1.08); }
            .sp-btn.teal { background: linear-gradient(135deg, #14b8a6, #0f766e); color: #fff; }
            .sp-btn.danger { background: #fee2e2; color: #b91c1c; }
            .sp-btn.sm { padding: .3rem .6rem; font-size: .75rem; }
            .sp-btn:disabled { opacity: .55; cursor: not-allowed; }

            .sp-note { background: #eff6ff; border: 1px solid #bfdbfe; color: #1e3a8a; border-radius: .7rem; padding: .6rem .85rem; font-size: .8rem; margin-bottom: .8rem; }
            .sp-note.warn { background: #fffbeb; border-color: #fde68a; color: #92400e; }

            .sp-matrix-wrap { overflow-x: auto; }
            .sp-matrix th, .sp-matrix td { min-width: 210px; }
            .sp-matrix th:first-child, .sp-matrix td:first-child { min-width: 190px; position: sticky; left: 0; background: #fff; z-index: 2; box-shadow: 2px 0 4px rgba(0, 0, 0, .04); }
            .sp-cell { border: 1px solid #e5e7eb; border-radius: .6rem; padding: .4rem .5rem; background: #fafafa; }
            .sp-cell.on { background: #f0fdfa; border-color: #99f6e4; }
            .sp-cell label { display: flex; align-items: center; gap: .4rem; font-size: .78rem; font-weight: 700; margin: 0 0 .3rem; cursor: pointer; }
            .sp-cell .sp-in { margin-top: .25rem; padding: .25rem .4rem; font-size: .76rem; }
            .sp-colhd { font-size: .78rem; text-transform: none; letter-spacing: 0; color: #1B1D28; }
            .sp-colhd .sp-in { margin-top: .25rem; font-size: .72rem; padding: .2rem .35rem; }
            .sp-rowname strong { display: block; font-size: .86rem; color: #1B1D28; }
            .sp-rowname small { color: #6b7280; font-size: .72rem; }
            .sp-savebar { position: sticky; bottom: 0; background: #fff; border-top: 1px solid #e5e7eb; padding: .7rem 1.1rem; display: flex; justify-content: space-between; align-items: center; gap: .75rem; flex-wrap: wrap; border-radius: 0 0 1rem 1rem; z-index: 5; }
            .sp-status { font-size: .8rem; color: #6b7280; }
            .sp-status.dirty { color: #b45309; font-weight: 700; }
        </style>

        <div class="row">
            <div class="col-lg-12">
                <div class="card bg-primary">
                    @include('layouts.class-buttons')
                    <div class="card-body bg-light">

                        <div class="sp-hero">
                            <h3><i class="fas fa-layer-group mr-2 me-2"></i> Subject Papers</h3>
                            <p>
                                Define a subject's papers <strong>once</strong> (Physics: P1, P2, P3 Practical), attach them to
                                many classes and streams in one go, and choose which teacher marks which paper. What each paper is
                                marked <em>out of</em> is still set per examination &mdash; the usual total you enter here only
                                pre-fills it.
                            </p>
                            <p>
                                Papers are used by <strong>standard</strong> examinations. An O-Level examination that runs on
                                <strong>Assessments (new curriculum)</strong> is marked through <em>Create Assessment</em> instead
                                (Activities of Integration, Projects, Subject Achievement &mdash; each with its own maximum), and a
                                subject graded on an <strong>Assessment Scale</strong> is comment-driven, so neither uses papers.
                            </p>
                        </div>

                        @if (!$canManage && !$canAssign)
                            <div class="sp-note warn"><i class="fas fa-eye mr-1"></i> You can view subject papers but not change them.</div>
                        @endif

                        <div class="sp-wrap">
                            {{-- Subject list --}}
                            <aside class="sp-panel">
                                <div class="sp-panel-hd">
                                    <h5>Subjects <small>Secondary subjects taught in your classes</small></h5>
                                </div>
                                <div class="sp-panel-bd">
                                    <input type="search" class="sp-search" id="spSearch" placeholder="Search subjects…">
                                    <div class="sp-list" id="spList">
                                        @forelse ($subjects as $s)
                                            <button type="button" class="sp-subject" data-key="{{ $s->key }}" data-name="{{ mb_strtolower($s->name) }}">
                                                <strong>{{ $s->name }}</strong>
                                                <span class="sp-badges">
                                                    @if ($s->blocked)
                                                        <span class="sp-badge warn" title="{{ implode(', ', $s->scale_names) }}">assessment scale</span>
                                                    @elseif ($s->papers->isEmpty())
                                                        <span class="sp-badge">single score</span>
                                                    @else
                                                        <span class="sp-badge ok">{{ $s->papers->count() }} {{ \Illuminate\Support\Str::plural('paper', $s->papers->count()) }}</span>
                                                        <span class="sp-badge {{ $s->attached >= $s->offerings ? 'ok' : 'warn' }}">on {{ $s->attached }}/{{ $s->offerings }} classes</span>
                                                        @if ($s->with_teachers)
                                                            <span class="sp-badge teal">paper teachers</span>
                                                        @endif
                                                    @endif
                                                    @if (!$s->blocked && $s->scaled)
                                                        <span class="sp-badge warn" title="{{ implode(', ', $s->scale_names) }}">{{ $s->scaled }} on a scale</span>
                                                    @endif
                                                </span>
                                            </button>
                                        @empty
                                            <div class="sp-empty"><i class="fas fa-book-open"></i>No secondary subjects found. Add subjects to a Secondary class first.</div>
                                        @endforelse
                                    </div>
                                </div>
                            </aside>

                            {{-- Detail --}}
                            <section id="spDetail">
                                <div class="sp-panel">
                                    <div class="sp-empty"><i class="fas fa-hand-pointer"></i>Pick a subject on the left to set up its papers.</div>
                                </div>
                            </section>
                        </div>

                    </div>
                </div>
            </div>
        </div></div>
            </div>
        </div>

        <script>
            (function () {
                const URLS = {
                    show: key => @json(url('subject-papers')) + '/' + key,
                    papers: key => @json(url('subject-papers')) + '/' + key + '/papers',
                    assign: key => @json(url('subject-papers')) + '/' + key + '/assignments'
                };
                const CSRF = @json(csrf_token());
                const TEACHERS = @json($teachers);
                const TYPES = @json($types);
                const CAN_MANAGE = @json($canManage);
                const CAN_ASSIGN = @json($canAssign);
                const MAX_PAPERS = @json($maxPapers);

                const detailEl = document.getElementById('spDetail');
                const listEl = document.getElementById('spList');

                let current = null;      // last detail from the server
                let paperRows = [];      // editable standard papers
                let cells = {};          // 'class|stream|paperId' -> {on, t1, t2}
                let papersDirty = false;
                let cellsDirty = false;
                let activeKey = null;

                const esc = t => String(t == null ? '' : t).replace(/[&<>"']/g, c => ({'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'}[c]));
                const fmt = v => (v === null || v === '' || isNaN(parseFloat(v))) ? '' : String(Math.round(parseFloat(v) * 100) / 100);
                const teacherOptions = (selected, placeholder) =>
                    '<option value="">' + esc(placeholder) + '</option>' +
                    TEACHERS.map(t => '<option value="' + t.id + '"' + (String(selected) === String(t.id) ? ' selected' : '') + '>' + esc(t.name) + '</option>').join('');

                async function ensureSwal() {
                    if (window.Swal) return;
                    await new Promise(resolve => {
                        const s = document.createElement('script');
                        s.src = 'https://cdn.jsdelivr.net/npm/sweetalert2@11';
                        s.onload = resolve;
                        document.head.appendChild(s);
                    });
                }

                async function api(url, options) {
                    const res = await fetch(url, Object.assign({
                        headers: {'Accept': 'application/json', 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': CSRF},
                        credentials: 'same-origin'
                    }, options));
                    let data = {};
                    try { data = await res.json(); } catch (e) {}
                    if (!res.ok || data.success === false) {
                        const first = data.errors ? Object.values(data.errors)[0][0] : null;
                        const err = new Error(first || data.message || 'Something went wrong (' + res.status + ').');
                        err.data = data;
                        throw err;
                    }
                    return data;
                }

                async function notify(icon, title, text) {
                    await ensureSwal();
                    return Swal.fire({icon, title, text, confirmButtonColor: '#2C29CA'});
                }

                async function confirmBox(title, html, yes) {
                    await ensureSwal();
                    const r = await Swal.fire({icon: 'question', title, html, showCancelButton: true, confirmButtonColor: '#2C29CA', confirmButtonText: yes});
                    return r.isConfirmed;
                }

                // ── Subject list ────────────────────────────────────────────
                document.getElementById('spSearch').addEventListener('input', function () {
                    const q = this.value.trim().toLowerCase();
                    listEl.querySelectorAll('.sp-subject').forEach(b => {
                        b.style.display = !q || b.dataset.name.includes(q) ? '' : 'none';
                    });
                });

                listEl.addEventListener('click', async function (e) {
                    const btn = e.target.closest('.sp-subject');
                    if (!btn || btn.dataset.key === activeKey) return;

                    if ((papersDirty || cellsDirty) && !(await confirmBox('Discard unsaved changes?', 'You have changes on this subject that are not saved yet.', 'Yes, discard'))) return;

                    activeKey = btn.dataset.key;
                    listEl.querySelectorAll('.sp-subject').forEach(b => b.classList.toggle('active', b === btn));
                    detailEl.innerHTML = '<div class="sp-panel"><div class="sp-empty"><i class="fas fa-spinner fa-spin"></i>Loading…</div></div>';

                    try {
                        load(await api(URLS.show(activeKey), {method: 'GET'}));
                    } catch (err) {
                        detailEl.innerHTML = '<div class="sp-panel"><div class="sp-empty"><i class="fas fa-exclamation-triangle"></i>' + esc(err.message) + '</div></div>';
                    }
                });

                window.addEventListener('beforeunload', function (e) {
                    if (papersDirty || cellsDirty) { e.preventDefault(); e.returnValue = ''; }
                });

                // ── State from the server ───────────────────────────────────
                function load(detail) {
                    current = detail;
                    paperRows = detail.papers.map(p => ({id: p.id, n: p.paper_number, label: p.label || '', type: p.paper_type, max: fmt(p.default_max_score)}));
                    cells = {};
                    detail.offerings.forEach(o => {
                        detail.papers.forEach(p => {
                            const c = o.cells[p.id];
                            cells[ck(o, p.id)] = {on: !!c, t1: c ? c.t1 : null, t2: c ? c.t2 : null};
                        });
                    });
                    papersDirty = cellsDirty = false;
                    render();
                }

                const ck = (o, paperId) => o.class_id + '|' + o.stream_id + '|' + paperId;

                function markDirty() {
                    const bar = document.getElementById('spStatus');
                    if (!bar) return;
                    const dirty = papersDirty || cellsDirty;
                    bar.className = 'sp-status' + (dirty ? ' dirty' : '');
                    bar.innerHTML = dirty ? '<i class="fas fa-exclamation-circle mr-1"></i> Unsaved changes' : '<i class="fas fa-check-circle text-success mr-1"></i> Everything saved';
                }

                // ── Rendering ───────────────────────────────────────────────
                function scaledNote(d) {
                    if (!d.scaled_offerings || !d.scaled_offerings.length) return '';
                    return '<div class="sp-note warn"><i class="fas fa-info-circle mr-1"></i> ' +
                        '<strong>Not listed below:</strong> ' +
                        d.scaled_offerings.map(o => esc(o.label) + ' (scale: ' + esc(o.scale) + ')').join('; ') +
                        '. These class-streams grade this subject on an Assessment Scale (comment-driven), so papers cannot be attached there. ' +
                        'To use papers, set the subject back to <em>Numeric marks</em> under <strong>Examinations &rarr; Assessment Scales &rarr; Assign</strong>.</div>';
                }

                function render() {
                    const d = current;

                    if (d.blocked) {
                        detailEl.innerHTML =
                            '<div class="sp-panel"><div class="sp-panel-hd"><h5>' + esc(d.name) + ' &mdash; papers not available</h5></div>' +
                            '<div class="sp-panel-bd">' + scaledNote(d) +
                            '<div class="sp-note">Every class that teaches <strong>' + esc(d.name) + '</strong> grades it on an Assessment Scale, ' +
                            'so it is marked with comments rather than plain scores and cannot be split into papers (P1, P2 &hellip;). ' +
                            'If that scale was set by mistake, open <strong>Examinations &rarr; Assessment Scales</strong>, choose the scale, press <em>Assign</em>, and set this subject back to ' +
                            '<em>Numeric marks</em>. It will then be ready for papers here.</div></div></div>';
                        papersDirty = cellsDirty = false;
                        return;
                    }

                    detailEl.innerHTML =
                        scaledNote(d) +
                        '<div class="sp-panel mb-3">' +
                            '<div class="sp-panel-hd"><h5>' + esc(d.name) + ' &mdash; standard papers<small>The papers this subject has. Leave a paper\'s "usually out of" blank if it changes every exam.</small></h5>' +
                            '<span id="spStatus" class="sp-status"></span></div>' +
                            '<div class="sp-panel-bd" id="spPapersBox"></div>' +
                        '</div>' +
                        '<div class="sp-panel">' +
                            '<div class="sp-panel-hd"><h5>Classes &amp; paper teachers<small>Tick a paper to attach it to a class-stream, then (optionally) choose who marks it there.</small></h5>' +
                            '<div id="spMatrixTools"></div></div>' +
                            '<div class="sp-panel-bd" id="spMatrixBox"></div>' +
                            '<div class="sp-savebar"><span class="text-muted" style="font-size:.78rem;">A paper with <strong>no</strong> teacher is marked by the subject\'s own teachers.</span>' +
                            '<button type="button" class="sp-btn teal" id="spSaveCells"><i class="fas fa-save mr-1"></i> Save classes &amp; teachers</button></div>' +
                        '</div>';
                    renderPapers();
                    renderMatrix();
                    markDirty();
                }

                function renderPapers() {
                    const box = document.getElementById('spPapersBox');
                    const edit = CAN_MANAGE;
                    let html = '';

                    if (!paperRows.length) {
                        html += '<div class="sp-note"><i class="fas fa-info-circle mr-1"></i> This subject has no papers, so it is marked as <strong>one score</strong> in every exam. Add papers below if it is sat as P1, P2 &hellip;</div>';
                    } else {
                        html += '<div class="sp-matrix-wrap"><table class="sp-table"><thead><tr><th>#</th><th>Name (optional)</th><th>Type</th><th>Usually out of</th><th></th></tr></thead><tbody>';
                        paperRows.sort((a, b) => a.n - b.n).forEach((r, i) => {
                            html += '<tr>' +
                                '<td><span class="sp-pill">P' + r.n + '</span></td>' +
                                '<td><input class="sp-in" data-i="' + i + '" data-f="label" maxlength="60" placeholder="Paper ' + r.n + '" value="' + esc(r.label) + '"' + (edit ? '' : ' disabled') + '></td>' +
                                '<td><select class="sp-in" data-i="' + i + '" data-f="type"' + (edit ? '' : ' disabled') + '>' +
                                    Object.entries(TYPES).map(([k, v]) => '<option value="' + k + '"' + (r.type === k ? ' selected' : '') + '>' + esc(v) + '</option>').join('') +
                                '</select></td>' +
                                '<td style="max-width:130px"><input class="sp-in" type="number" min="0.01" max="1000" step="0.5" data-i="' + i + '" data-f="max" placeholder="e.g. 80" value="' + esc(r.max) + '"' + (edit ? '' : ' disabled') + '></td>' +
                                '<td class="text-right">' + (edit ? '<button type="button" class="sp-btn danger sm" data-del="' + i + '" title="Remove this paper"><i class="fas fa-trash"></i></button>' : '') + '</td>' +
                            '</tr>';
                        });
                        html += '</tbody></table></div>';
                    }

                    if (edit) {
                        html += '<div class="mt-3 d-flex" style="gap:.5rem;flex-wrap:wrap;">' +
                            (paperRows.length < MAX_PAPERS ? '<button type="button" class="sp-btn" id="spAddPaper"><i class="fas fa-plus mr-1"></i> Add paper</button>' : '') +
                            '<button type="button" class="sp-btn primary" id="spSavePapers"><i class="fas fa-save mr-1"></i> Save papers</button>' +
                        '</div>';
                    }
                    box.innerHTML = html;
                }

                function renderMatrix() {
                    const box = document.getElementById('spMatrixBox');
                    const tools = document.getElementById('spMatrixTools');
                    const d = current;
                    const blocked = papersDirty;

                    if (!d.papers.length) {
                        box.innerHTML = '<div class="sp-empty" style="padding:1.4rem;"><i class="fas fa-layer-group"></i>Save at least one paper above, then attach it to classes here.</div>';
                        tools.innerHTML = '';
                        document.getElementById('spSaveCells').style.display = 'none';
                        return;
                    }
                    document.getElementById('spSaveCells').style.display = (CAN_MANAGE || CAN_ASSIGN) ? '' : 'none';

                    tools.innerHTML = CAN_MANAGE
                        ? '<button type="button" class="sp-btn sm" id="spAttachAll"><i class="fas fa-check-double mr-1"></i> Attach all papers to all classes</button>'
                        : '';

                    let html = '';
                    if (blocked) {
                        html += '<div class="sp-note warn"><i class="fas fa-exclamation-triangle mr-1"></i> You changed the papers above. Save the papers first, then attach them to classes.</div>';
                    }

                    html += '<div class="sp-matrix-wrap"><table class="sp-table sp-matrix"><thead><tr><th>Class &amp; stream</th>';
                    d.papers.forEach(p => {
                        html += '<th class="sp-colhd"><label class="mb-0" style="display:flex;gap:.4rem;align-items:center;cursor:pointer;">' +
                            (CAN_MANAGE ? '<input type="checkbox" data-colall="' + p.id + '" title="Attach to every class">' : '') +
                            '<span class="sp-pill">P' + p.paper_number + '</span> ' + esc(p.label || '') + '</label>' +
                            (CAN_ASSIGN ? '<select class="sp-in" data-colt="' + p.id + ':t1" title="Set teacher 1 for every class that has this paper">' + teacherOptions('', 'Set teacher 1 for all…') + '</select>' : '') +
                        '</th>';
                    });
                    html += '</tr></thead><tbody>';

                    d.offerings.forEach(o => {
                        html += '<tr data-class="' + o.class_id + '" data-stream="' + esc(o.stream_id) + '"><td class="sp-rowname">' +
                            '<strong>' + esc(o.label) + '</strong>' +
                            '<small>' + (o.subject_teachers.length ? 'Subject teacher: ' + esc(o.subject_teachers.join(', ')) : 'No subject teacher yet') + '</small>' +
                            (CAN_MANAGE || CAN_ASSIGN
                                ? '<div class="mt-1" style="display:flex;gap:.3rem;flex-wrap:wrap;">' +
                                    (CAN_MANAGE ? '<button type="button" class="sp-btn sm" data-rowall="' + esc(o.class_id + '|' + o.stream_id) + '" title="Attach every paper to this class-stream">All papers</button>' : '') +
                                    '<button type="button" class="sp-btn sm" data-copy="' + esc(o.class_id + '|' + o.stream_id) + '" title="Copy this row\'s papers and teachers to the other streams of the same class">Copy to streams</button>' +
                                  '</div>'
                                : '') +
                            '</td>';
                        d.papers.forEach(p => {
                            const k = ck(o, p.id);
                            const c = cells[k];
                            html += '<td><div class="sp-cell' + (c.on ? ' on' : '') + '">' +
                                '<label><input type="checkbox" data-cell="' + esc(k) + '"' + (c.on ? ' checked' : '') + (CAN_MANAGE ? '' : ' disabled') + '> Attached</label>' +
                                (c.on
                                    ? '<select class="sp-in" data-t="' + esc(k) + ':t1"' + (CAN_ASSIGN ? '' : ' disabled') + '>' + teacherOptions(c.t1, 'Subject teacher marks it') + '</select>' +
                                      '<select class="sp-in" data-t="' + esc(k) + ':t2"' + (CAN_ASSIGN ? '' : ' disabled') + '>' + teacherOptions(c.t2, '+ second teacher (optional)') + '</select>'
                                    : '') +
                            '</div></td>';
                        });
                        html += '</tr>';
                    });
                    html += '</tbody></table></div>';
                    box.innerHTML = html;

                    // tick column "all" boxes whose column is fully attached
                    d.papers.forEach(p => {
                        const cb = box.querySelector('[data-colall="' + p.id + '"]');
                        if (cb) cb.checked = d.offerings.length > 0 && d.offerings.every(o => cells[ck(o, p.id)].on);
                    });
                }

                // ── Events (delegated) ──────────────────────────────────────
                detailEl.addEventListener('input', function (e) {
                    const t = e.target;
                    if (t.dataset.f !== undefined) {
                        paperRows[+t.dataset.i][t.dataset.f] = t.value;
                        const was = papersDirty;
                        papersDirty = true;
                        if (!was) renderMatrix(); // shows the "save the papers first" note
                        markDirty();
                    }
                });

                detailEl.addEventListener('change', function (e) {
                    const t = e.target;

                    if (t.dataset.f === 'type') {
                        paperRows[+t.dataset.i].type = t.value;
                        const was = papersDirty;
                        papersDirty = true;
                        if (!was) renderMatrix();
                        markDirty();
                    } else if (t.dataset.cell !== undefined) {
                        cells[t.dataset.cell].on = t.checked;
                        if (!t.checked) { cells[t.dataset.cell].t1 = cells[t.dataset.cell].t2 = null; }
                        cellsDirty = true; renderMatrix(); markDirty();
                    } else if (t.dataset.t !== undefined) {
                        const [k, slot] = t.dataset.t.split(/:(?=t[12]$)/);
                        const c = cells[k];
                        c[slot] = t.value ? parseInt(t.value, 10) : null;
                        if (c.t1 && c.t1 === c.t2) {
                            c[slot] = null; t.value = '';
                            notify('warning', 'Same teacher twice', 'One paper cannot have the same teacher as both first and second teacher.');
                        }
                        cellsDirty = true; markDirty();
                    } else if (t.dataset.colall !== undefined) {
                        const pid = parseInt(t.dataset.colall, 10);
                        current.offerings.forEach(o => {
                            const c = cells[ck(o, pid)];
                            c.on = t.checked;
                            if (!t.checked) c.t1 = c.t2 = null;
                        });
                        cellsDirty = true; renderMatrix(); markDirty();
                    } else if (t.dataset.colt !== undefined) {
                        const [pid, slot] = t.dataset.colt.split(':');
                        const teacher = t.value ? parseInt(t.value, 10) : null;
                        if (teacher) {
                            current.offerings.forEach(o => {
                                const c = cells[ck(o, parseInt(pid, 10))];
                                if (c.on) { c.t1 = teacher; if (c.t2 === teacher) c.t2 = null; }
                            });
                            cellsDirty = true; renderMatrix(); markDirty();
                        }
                    }
                });

                detailEl.addEventListener('click', async function (e) {
                    const b = e.target.closest('button');
                    if (!b) return;

                    if (b.id === 'spAddPaper') {
                        let n = 1; const used = new Set(paperRows.map(r => r.n));
                        while (used.has(n)) n++;
                        paperRows.push({id: null, n, label: '', type: 'theory', max: ''});
                        papersDirty = true; renderPapers(); renderMatrix(); markDirty();
                    } else if (b.dataset.del !== undefined) {
                        paperRows.splice(+b.dataset.del, 1);
                        papersDirty = true; renderPapers(); renderMatrix(); markDirty();
                    } else if (b.id === 'spSavePapers') {
                        savePapers(false);
                    } else if (b.id === 'spSaveCells') {
                        saveCells();
                    } else if (b.id === 'spAttachAll') {
                        current.offerings.forEach(o => current.papers.forEach(p => { cells[ck(o, p.id)].on = true; }));
                        cellsDirty = true; renderMatrix(); markDirty();
                    } else if (b.dataset.rowall !== undefined) {
                        const [cl, st] = b.dataset.rowall.split('|');
                        current.papers.forEach(p => { cells[cl + '|' + st + '|' + p.id].on = true; });
                        cellsDirty = true; renderMatrix(); markDirty();
                    } else if (b.dataset.copy !== undefined) {
                        const [cl, st] = b.dataset.copy.split('|');
                        const siblings = current.offerings.filter(o => String(o.class_id) === cl && o.stream_id !== st);
                        if (!siblings.length) { notify('info', 'No other streams', 'This class has no other stream teaching this subject.'); return; }
                        siblings.forEach(o => current.papers.forEach(p => {
                            const src = cells[cl + '|' + st + '|' + p.id];
                            cells[ck(o, p.id)] = {on: src.on, t1: src.t1, t2: src.t2};
                        }));
                        cellsDirty = true; renderMatrix(); markDirty();
                    }
                });

                // ── Saving ──────────────────────────────────────────────────
                async function savePapers(confirmDetach) {
                    const btn = document.getElementById('spSavePapers');
                    for (const r of paperRows) {
                        if (r.max !== '' && !(parseFloat(r.max) > 0)) {
                            notify('error', 'Check the totals', 'P' + r.n + ': "usually out of" must be a number above 0, or left blank.');
                            return;
                        }
                    }
                    btn.disabled = true;
                    try {
                        const data = await api(URLS.papers(activeKey), {
                            method: 'POST',
                            body: JSON.stringify({
                                confirm_detach: !!confirmDetach,
                                papers: paperRows.map(r => ({paper_number: r.n, label: (r.label || '').trim() || null, paper_type: r.type, default_max_score: r.max === '' ? null : parseFloat(r.max)}))
                            })
                        });
                        load(data);
                        refreshListBadges();
                        notify('success', 'Papers saved', '');
                    } catch (err) {
                        if (err.data && err.data.needs_confirmation) {
                            btn.disabled = false;
                            if (await confirmBox('Remove papers that are in use?', esc(err.data.message), 'Yes, remove them')) return savePapers(true);
                            return;
                        }
                        notify('error', 'Could not save papers', err.message);
                    } finally {
                        const again = document.getElementById('spSavePapers');
                        if (again) again.disabled = false;
                    }
                }

                async function saveCells() {
                    const btn = document.getElementById('spSaveCells');
                    const out = [];
                    current.offerings.forEach(o => current.papers.forEach(p => {
                        const c = cells[ck(o, p.id)];
                        out.push({class_id: o.class_id, stream_id: o.stream_id, subject_paper_id: p.id, attached: !!c.on, teacher_1_id: c.on ? c.t1 : null, teacher_2_id: c.on ? c.t2 : null});
                    }));

                    if (papersDirty) { notify('warning', 'Save the papers first', 'You changed the papers above. Save them before attaching them to classes.'); return; }

                    btn.disabled = true;
                    try {
                        const data = await api(URLS.assign(activeKey), {method: 'POST', body: JSON.stringify({cells: out})});
                        load(data);
                        refreshListBadges();
                        notify('success', 'Saved', data.message);
                    } catch (err) {
                        notify('error', 'Could not save', err.message);
                    } finally {
                        const again = document.getElementById('spSaveCells');
                        if (again) again.disabled = false;
                    }
                }

                // Keep the left-hand badges honest after a save (no page reload needed).
                function refreshListBadges() {
                    const btn = listEl.querySelector('.sp-subject[data-key="' + activeKey + '"]');
                    if (!btn || !current) return;
                    const total = current.offerings.length;
                    const attached = current.offerings.filter(o => Object.keys(o.cells).length > 0).length;
                    const withT = current.offerings.some(o => Object.values(o.cells).some(c => c.t1 || c.t2));
                    btn.querySelector('.sp-badges').innerHTML = current.papers.length
                        ? '<span class="sp-badge ok">' + current.papers.length + ' paper' + (current.papers.length === 1 ? '' : 's') + '</span>' +
                          '<span class="sp-badge ' + (attached >= total ? 'ok' : 'warn') + '">on ' + attached + '/' + total + ' classes</span>' +
                          (withT ? '<span class="sp-badge teal">paper teachers</span>' : '')
                        : '<span class="sp-badge">single score</span>';
                }
            })();
        </script>
    </div>
@endsection
