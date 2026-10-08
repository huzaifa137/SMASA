<?php use App\Http\Controllers\Helper; ?>
@extends('layouts-side-bar.master')

@section('css')
    <style>
        .rc-hero { background: linear-gradient(135deg,#0a0a0f 0%,#14143a 40%,#1e1b8a 75%,#2C29CA 100%); border-radius: 1.75rem; padding: 1.5rem 2rem 2rem; margin-bottom: 1.5rem; }
        .rc-hero .hero-badge { background: rgba(44,41,202,.25); border: 1px solid rgba(107,105,232,.5); color: #c7c5ff; padding: .3rem .9rem; border-radius: 999px; font-size: .65rem; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; }
        .rc-hero .hero-title { font-size: 1.4rem; font-weight: 800; color: #fff; margin: .25rem 0; }
        .rc-hero .hero-subtitle { color: rgba(255,255,255,.68); font-size: .85rem; max-width: 780px; }
        .rc-panel { background: #fff; border-radius: 1.25rem; box-shadow: 0 4px 28px rgba(44,41,202,.08); margin-bottom: 1.4rem; overflow: hidden; }
        .rc-panel .ph { padding: 1rem 1.5rem; border-bottom: 2px solid #f0eeff; font-weight: 800; font-size: .95rem; color: #1a1a3a; display: flex; justify-content: space-between; align-items: center; gap: .6rem; flex-wrap: wrap; }
        .rc-panel .pb { padding: 1.3rem 1.5rem; }
        .cs-chips { display: flex; flex-wrap: wrap; gap: .5rem; }
        .cs-chip { border: 2px solid #e4e6ff; border-radius: .8rem; padding: .45rem .8rem; font-size: .8rem; font-weight: 700; cursor: pointer; user-select: none; color: #4a4a6a; background: #fafbff; }
        .cs-chip.on { border-color: #2C29CA; background: #eef0ff; color: #2C29CA; }
        .comp { border: 2px solid #e8eaff; border-radius: 1rem; padding: 1rem 1.1rem; margin-bottom: 1rem; background: #fcfcff; }
        .comp.is-exam { border-left: 5px solid #f59e0b; }
        .comp.is-assess { border-left: 5px solid #2C29CA; }
        .comp .tag { font-size: .65rem; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; }
        .comp .tag.assess { color: #2C29CA; } .comp .tag.exam { color: #b45309; }
        .pick-box { max-height: 340px; overflow: auto; border: 1px solid #e8eaff; border-radius: .8rem; padding: .6rem .8rem; background: #fff; }
        .pick-exam { font-weight: 800; font-size: .8rem; color: #1a1a3a; margin-top: .5rem; display: flex; align-items: center; gap: .5rem; }
        .pick-subject { font-weight: 700; font-size: .75rem; color: #6b6b8d; margin: .4rem 0 .15rem 1rem; }
        .pick-row { display: flex; align-items: flex-start; gap: .5rem; font-size: .78rem; margin-left: 1.6rem; padding: .15rem 0; }
        .pick-row .meta { color: #8b8baa; font-size: .7rem; }
        .total-pill { font-weight: 800; border-radius: 999px; padding: .3rem .9rem; font-size: .8rem; background: #eef0ff; color: #2C29CA; }
        .total-pill.off { background: #fff4e0; color: #b45309; }
        .hint { font-size: .75rem; color: #7a7a9a; }
    </style>
@endsection

@section('content')
    @php
        $isEdit = (bool) $card;
        $activeTermText = \App\Support\Term::activeText();
    @endphp
    <div class="container-fluid py-3">
        <div class="rc-hero">
            <span class="hero-badge"><i class="fas fa-graduation-cap me-1"></i> Secondary · Senior 1–4</span>
            <div class="hero-title">{{ $isEdit ? 'Edit' : 'New' }} O-Level Report Card</div>
            <div class="hero-subtitle">
                Add one or more parts to the card. <strong>Assessments</strong> are averaged on the 0–3 competency scale and shown out of the weight you give them;
                an <strong>Examination</strong> is shown out of its own weight. Together they form the card (for example 20 + 80 = 100).
            </div>
        </div>

        <div id="alertBox"></div>

        <div class="rc-panel">
            <div class="ph"><span><i class="fas fa-info-circle me-1 text-primary"></i> Report card details</span></div>
            <div class="pb">
                <div class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label fw-semibold">Name <span class="text-danger">*</span></label>
                        <input type="text" id="examName" class="form-control" maxlength="255"
                            placeholder="e.g. Term 2 Report Card" value="{{ $card->exam_name ?? '' }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Term <span class="text-danger">*</span></label>
                        <select id="term" class="form-select">
                            @foreach(['Term 1' => 'Term I', 'Term 2' => 'Term II', 'Term 3' => 'Term III'] as $v => $l)
                                <option value="{{ $v }}" {{ ($card->term ?? $activeTermText) === $v ? 'selected' : '' }}>{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Year <span class="text-danger">*</span></label>
                        <input type="number" id="academicYear" class="form-control" min="2000" max="2099"
                            value="{{ $card->academic_year ?? Helper::active_year() }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Grading scheme <span class="text-danger">*</span></label>
                        <select id="gradingScheme" class="form-select">
                            @foreach($gradingSchemes as $s)
                                <option value="{{ $s->id }}"
                                    {{ ($card->grading_scheme_id ?? null) == $s->id || (!$isEdit && $s->is_default) ? 'selected' : '' }}>
                                    {{ $s->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Notes (optional)</label>
                        <input type="text" id="description" class="form-control" value="{{ $card->description ?? '' }}">
                    </div>
                </div>
            </div>
        </div>

        <div class="rc-panel">
            <div class="ph">
                <span><i class="fas fa-users me-1 text-primary"></i> Classes</span>
                <span class="hint">Only Senior 1–4 streams are listed.</span>
            </div>
            <div class="pb">
                <div class="cs-chips" id="csChips">
                    @forelse($classStreams as $cs)
                        @php $val = $cs->class_id . '_' . $cs->stream_id; @endphp
                        <div class="cs-chip {{ in_array($val, $selectedClassStreams, true) ? 'on' : '' }}" data-value="{{ $val }}">
                            {{ Helper::recordMdname($cs->class_id) }} · {{ $cs->stream_id ?: 'No stream' }}
                        </div>
                    @empty
                        <div class="text-muted">No Senior 1–4 class streams are set up for this school.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="rc-panel">
            <div class="ph">
                <span><i class="fas fa-layer-group me-1 text-primary"></i> What goes on the card</span>
                <span>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="addAssess"><i class="fas fa-tasks me-1"></i> Add assessments</button>
                    <button type="button" class="btn btn-sm btn-outline-warning" id="addExam"><i class="fas fa-file-signature me-1"></i> Add examination</button>
                </span>
            </div>
            <div class="pb">
                <div id="components"></div>
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <span class="hint">
                        Each assessment can appear on any number of report cards. Subjects missing one part
                        (for example no exam mark) are scored out of the parts that exist.
                    </span>
                    <span class="total-pill" id="totalPill">Total: 0</span>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2 mb-5">
            <button type="button" class="btn btn-primary" id="saveBtn" style="border-radius:.7rem; font-weight:700;">
                <i class="fas fa-save me-1"></i> {{ $isEdit ? 'Save changes' : 'Create report card' }}
            </button>
            <a href="{{ route('olevel-report-cards.index') }}" class="btn btn-light" style="border-radius:.7rem;">Cancel</a>
        </div>
    </div>
@endsection

@section('js')
    <script>
        (function () {
            const IS_EDIT = @json($isEdit);
            const SAVE_URL = @json($isEdit ? route('olevel-report-cards.update', $card->id) : route('olevel-report-cards.store'));
            const ASSESS_URL = @json(route('olevel-report-cards.assessment-options'));
            const EXAM_URL = @json(route('olevel-report-cards.exam-options'));
            const CSRF = @json(csrf_token());

            let components = @json($components);   // [{type,label,weight,source_examination_id,assessment_ids}]
            let assessData = [];                    // [{exam_id, exam_name, term, year, subjects:[{subject, assessments:[...]}]}]
            let examData = [];                      // [{id, name, total_marks}]

            const $ = (id) => document.getElementById(id);
            const esc = (s) => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
            const selectedClasses = () => [...document.querySelectorAll('.cs-chip.on')].map(e => e.dataset.value);

            components.forEach(c => { c.assessment_ids = (c.assessment_ids || []).map(Number); });

            function alertBox(msg, type = 'danger') {
                $('alertBox').innerHTML = msg ? `<div class="alert alert-${type}">${esc(msg)}</div>` : '';
                if (msg) window.scrollTo({ top: 0, behavior: 'smooth' });
            }

            // ── load what can be picked for the ticked classes ──────────────
            let loadTimer = null;
            function loadOptions() {
                clearTimeout(loadTimer);
                loadTimer = setTimeout(async () => {
                    const classes = selectedClasses();
                    if (!classes.length) { assessData = []; examData = []; render(); return; }
                    const qs = classes.map(c => 'class_streams[]=' + encodeURIComponent(c)).join('&');
                    try {
                        const [a, e] = await Promise.all([
                            fetch(ASSESS_URL + '?' + qs, { headers: { 'Accept': 'application/json' } }).then(r => r.json()),
                            fetch(EXAM_URL + '?' + qs, { headers: { 'Accept': 'application/json' } }).then(r => r.json()),
                        ]);
                        assessData = a.exams || [];
                        examData = e.exams || [];
                        // drop ticks that no longer apply to the chosen classes
                        const valid = new Set(assessData.flatMap(x => x.subjects.flatMap(s => s.assessments.map(z => z.id))));
                        components.forEach(c => { if (c.type === 'assessments') c.assessment_ids = c.assessment_ids.filter(id => valid.has(id)); });
                        render();
                    } catch (err) { alertBox('Could not load assessments / examinations.'); }
                }, 250);
            }

            // ── render the component cards ──────────────────────────────────
            function render() {
                const host = $('components');
                if (!components.length) {
                    host.innerHTML = `<div class="text-center text-muted py-4">Nothing added yet — use <b>Add assessments</b> and/or <b>Add examination</b>.</div>`;
                    updateTotal(); return;
                }
                host.innerHTML = components.map((c, i) => c.type === 'exam' ? examCard(c, i) : assessCard(c, i)).join('');
                updateTotal();
            }

            function head(c, i, kind) {
                return `
                <div class="row g-2 align-items-end mb-2">
                    <div class="col-12 d-flex justify-content-between">
                        <span class="tag ${kind}">${kind === 'exam' ? 'Examination' : 'Assessments'}</span>
                        <button type="button" class="btn btn-sm btn-link text-danger p-0" data-act="remove" data-i="${i}"><i class="fas fa-times"></i> Remove</button>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label fw-semibold mb-1" style="font-size:.78rem;">Column title on the report card</label>
                        <input type="text" class="form-control form-control-sm" data-field="label" data-i="${i}" maxlength="80" value="${esc(c.label)}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold mb-1" style="font-size:.78rem;">Out of (marks)</label>
                        <input type="number" class="form-control form-control-sm" data-field="weight" data-i="${i}" min="1" max="1000" step="0.5" value="${esc(c.weight)}">
                    </div>
                </div>`;
            }

            function examCard(c, i) {
                const opts = examData.map(e => `<option value="${e.id}" ${Number(c.source_examination_id) === e.id ? 'selected' : ''}>${esc(e.name)} (out of ${e.total_marks})</option>`).join('');
                const empty = !examData.length ? `<div class="hint text-danger mt-1">No standard examination sits the ticked classes. Create one (Create Exam → “Standard examination”) first.</div>` : '';
                return `<div class="comp is-exam">${head(c, i, 'exam')}
                    <label class="form-label fw-semibold mb-1" style="font-size:.78rem;">Examination</label>
                    <select class="form-select form-select-sm" data-field="source_examination_id" data-i="${i}">
                        <option value="">-- Choose examination --</option>${opts}
                    </select>${empty}
                    <div class="hint mt-1">Each subject's mark is converted to the “Out of” value above.</div>
                </div>`;
            }

            function assessCard(c, i) {
                const set = new Set(c.assessment_ids);
                let body = '';
                assessData.forEach(ex => {
                    const ids = ex.subjects.flatMap(s => s.assessments.map(a => a.id));
                    const all = ids.length && ids.every(id => set.has(id));
                    body += `<div class="pick-exam">
                        <input type="checkbox" data-act="pick-exam" data-i="${i}" data-exam="${ex.exam_id}" ${all ? 'checked' : ''}>
                        <span>${esc(ex.exam_name)} <span class="meta text-muted" style="font-weight:600;">· ${esc(ex.term)} ${esc(ex.year)} · all ${ids.length}</span></span>
                    </div>`;
                    ex.subjects.forEach(s => {
                        body += `<div class="pick-subject">${esc(s.subject)}</div>`;
                        s.assessments.forEach(a => {
                            body += `<label class="pick-row">
                                <input type="checkbox" data-act="pick" data-i="${i}" data-id="${a.id}" ${set.has(a.id) ? 'checked' : ''}>
                                <span>${esc(a.type)} — ${esc(a.title)}
                                    <span class="meta d-block">${esc(a.class)}${a.max_marks ? ' · out of ' + esc(a.max_marks) : ''}${a.in_report ? '' : ' · excluded from reports by teacher'}</span>
                                </span>
                            </label>`;
                        });
                    });
                });
                if (!body) body = `<div class="hint text-danger">No assessments exist for the ticked classes yet.</div>`;
                return `<div class="comp is-assess">${head(c, i, 'assess')}
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="form-label fw-semibold mb-0" style="font-size:.78rem;">Choose assessments (${c.assessment_ids.length} selected)</label>
                        <button type="button" class="btn btn-sm btn-link p-0" data-act="clear" data-i="${i}">Clear</button>
                    </div>
                    <div class="pick-box">${body}</div>
                    <div class="hint mt-1">Scores (0–3) are averaged per subject and shown out of the “Out of” value above.</div>
                </div>`;
            }

            function updateTotal() {
                const t = components.reduce((s, c) => s + (parseFloat(c.weight) || 0), 0);
                const pill = $('totalPill');
                pill.textContent = 'Total: ' + (Math.round(t * 100) / 100);
                pill.classList.toggle('off', Math.abs(t - 100) > 0.001);
                pill.title = Math.abs(t - 100) > 0.001 ? 'Not 100 — fine if you want the card out of a different total.' : '';
            }

            // ── events ──────────────────────────────────────────────────────
            $('csChips').addEventListener('click', e => {
                const chip = e.target.closest('.cs-chip'); if (!chip) return;
                chip.classList.toggle('on'); loadOptions();
            });
            $('addAssess').onclick = () => { components.push({ type: 'assessments', label: 'Assessment', weight: 20, source_examination_id: null, assessment_ids: [] }); render(); };
            $('addExam').onclick = () => { components.push({ type: 'exam', label: 'Examination', weight: 80, source_examination_id: null, assessment_ids: [] }); render(); };

            $('components').addEventListener('input', e => {
                const f = e.target.dataset.field; if (!f) return;
                const c = components[+e.target.dataset.i];
                c[f] = f === 'source_examination_id' ? (e.target.value || null) : e.target.value;
                if (f === 'weight') updateTotal();
            });
            $('components').addEventListener('change', e => {
                const act = e.target.dataset.act; if (!act) return;
                const c = components[+e.target.dataset.i];
                const set = new Set(c.assessment_ids);
                if (act === 'pick') { e.target.checked ? set.add(+e.target.dataset.id) : set.delete(+e.target.dataset.id); }
                if (act === 'pick-exam') {
                    const ex = assessData.find(x => x.exam_id === +e.target.dataset.exam);
                    ex.subjects.forEach(s => s.assessments.forEach(a => e.target.checked ? set.add(a.id) : set.delete(a.id)));
                }
                c.assessment_ids = [...set]; render();
            });
            $('components').addEventListener('click', e => {
                const b = e.target.closest('[data-act]'); if (!b || b.tagName === 'INPUT') return;
                const i = +b.dataset.i;
                if (b.dataset.act === 'remove') { components.splice(i, 1); render(); }
                if (b.dataset.act === 'clear') { components[i].assessment_ids = []; render(); }
            });

            // ── save ────────────────────────────────────────────────────────
            $('saveBtn').onclick = async () => {
                alertBox('');
                const body = {
                    exam_name: $('examName').value.trim(),
                    term: $('term').value,
                    academic_year: $('academicYear').value,
                    grading_scheme_id: $('gradingScheme').value,
                    description: $('description').value.trim() || null,
                    class_streams: selectedClasses(),
                    components: components.map(c => ({
                        type: c.type, label: c.label, weight: c.weight,
                        source_examination_id: c.type === 'exam' ? c.source_examination_id : null,
                        assessment_ids: c.type === 'assessments' ? c.assessment_ids : [],
                    })),
                };
                if (!body.exam_name) return alertBox('Give the report card a name.');
                if (!body.class_streams.length) return alertBox('Tick at least one class.');
                if (!body.components.length) return alertBox('Add at least one part (assessments or an examination).');

                const btn = $('saveBtn'); btn.disabled = true;
                try {
                    const res = await fetch(SAVE_URL, {
                        method: IS_EDIT ? 'PUT' : 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
                        body: JSON.stringify(body),
                    });
                    const data = await res.json().catch(() => ({}));
                    if (res.ok && data.success) { window.location.href = data.redirect; return; }
                    const first = data.errors ? Object.values(data.errors)[0][0] : null;
                    alertBox(first || data.message || 'Could not save the report card.');
                } catch (err) { alertBox('Could not save the report card.'); }
                btn.disabled = false;
            };

            render();
            loadOptions();
        })();
    </script>
@endsection
