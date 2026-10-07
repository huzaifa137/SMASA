@extends('layouts-side-bar.master')

@section('content')
    <style>
        .crs-page {
            --r: 16px;
            --line: #e5e8f0;
            --muted: #68718a;
            --ink: #141b2d;
            --bg: #f5f7fb;
            --brand: #3b5bfd;
            --brand-soft: #eaeefe;
            --ok: #12a150;
            --ok-soft: #e3f6ec;
        }

        .crs-page h3,
        .crs-page h5,
        .crs-page h6 {
            color: var(--ink);
        }

        .crs-hero {
            margin-bottom: 18px;
        }

        .crs-hero h3 {
            font-weight: 800;
            letter-spacing: -.02em;
            margin: 0 0 4px;
        }

        .crs-hero p {
            margin: 0;
            color: var(--muted);
            max-width: 680px;
        }

        /* ---------- Selection panel ---------- */
        .crs-panel {
            background: #fff;
            border: 1px solid var(--line);
            border-radius: var(--r);
            box-shadow: 0 1px 2px rgba(20, 27, 45, .04);
            margin-bottom: 20px;
            overflow: hidden;
        }

        /* Selection panel must not clip the dropdown, and must sit above the preview panel */
        .crs-panel.crs-panel-select {
            overflow: visible;
            position: relative;
            z-index: 20;
        }

        .crs-panel-select .crs-panel-head {
            border-radius: var(--r) var(--r) 0 0;
        }

        .crs-panel-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
            padding: 16px 22px;
            border-bottom: 1px solid var(--line);
            background: #eef2ff;
        }

        .crs-panel-head h5 {
            margin: 0;
            font-weight: 700;
            font-size: 1rem;
        }

        .crs-panel-head small {
            color: var(--muted);
        }

        .crs-steps {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 18px;
            padding: 22px;
        }

        @media (max-width: 1199px) {
            .crs-steps {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 575px) {
            .crs-steps {
                grid-template-columns: 1fr;
            }
        }

        .crs-step {
            position: relative;
            border: 1.5px solid var(--line);
            border-radius: 14px;
            padding: 14px 16px 16px;
            background: #fff;
            transition: border-color .15s, box-shadow .15s, background .15s;
        }

        .crs-step.is-active {
            border-color: var(--brand);
            box-shadow: 0 0 0 4px rgba(59, 91, 253, .10);
        }

        .crs-step.is-done {
            border-color: #bfe6d0;
            background: #fafefb;
        }

        .crs-step-top {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
        }

        .crs-num {
            width: 26px;
            height: 26px;
            border-radius: 50%;
            background: #e9edf5;
            color: var(--muted);
            font-size: .78rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .crs-step.is-active .crs-num {
            background: var(--brand);
            color: #fff;
        }

        .crs-step.is-done .crs-num {
            background: var(--ok);
            color: #fff;
        }

        .crs-label {
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .07em;
            color: var(--muted);
            margin: 0;
        }

        .crs-step select {
            height: 46px;
            border-radius: 10px;
            border: 1.5px solid #c3cbe0;
            font-weight: 500;
            color: var(--ink);
            background-color: #fff;
        }

        .crs-step select:hover:not(:disabled) {
            border-color: #8fa0d4;
        }

        .crs-step select:focus {
            border-color: var(--brand);
            box-shadow: 0 0 0 3px rgba(59, 91, 253, .14);
        }

        .crs-step select:disabled {
            background-color: #f3f5fa;
            color: #98a0b5;
            cursor: not-allowed;
        }

        /* ---------- Searchable select ---------- */
        .ss {
            position: relative;
        }

        .ss-btn {
            width: 100%;
            height: 46px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: 0 14px;
            background: #fff;
            border: 1.5px solid #c3cbe0;
            border-radius: 10px;
            font-weight: 500;
            font-size: 1rem;
            color: var(--ink);
            text-align: left;
            cursor: pointer;
            transition: border-color .15s, box-shadow .15s;
        }

        .ss-btn:hover {
            border-color: #8fa0d4;
        }

        .ss-btn:focus {
            outline: 0;
            border-color: var(--brand);
            box-shadow: 0 0 0 3px rgba(59, 91, 253, .14);
        }

        .ss.open .ss-btn {
            border-color: var(--brand);
            box-shadow: 0 0 0 3px rgba(59, 91, 253, .14);
        }

        .ss-val {
            flex: 1;
            min-width: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .ss-val.is-placeholder {
            color: #8a93ab;
            font-weight: 400;
        }

        .ss-caret {
            color: var(--muted);
            font-size: .75rem;
            transition: transform .15s;
        }

        .ss.open .ss-caret {
            transform: rotate(180deg);
            color: var(--brand);
        }

        .ss-panel {
            display: none;
            position: absolute;
            top: calc(100% + 6px);
            left: 0;
            min-width: 100%;
            width: 400px;
            max-width: 90vw;
            background: #fff;
            border: 1.5px solid var(--brand);
            border-radius: 14px;
            box-shadow: 0 16px 40px rgba(20, 27, 45, .18);
            z-index: 1000;
            overflow: hidden;
        }

        .ss.open .ss-panel {
            display: block;
        }

        .ss-search {
            position: relative;
            padding: 10px;
            background: #eef2ff;
            border-bottom: 1px solid var(--line);
        }

        .ss-search i {
            position: absolute;
            left: 22px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--brand);
            font-size: .85rem;
        }

        .ss-search input {
            width: 100%;
            height: 40px;
            padding: 0 12px 0 36px;
            border: 1.5px solid #b9c3dd;
            border-radius: 10px;
            background: #fff;
            font-size: .92rem;
            color: var(--ink);
        }

        .ss-search input:focus {
            outline: 0;
            border-color: var(--brand);
            box-shadow: 0 0 0 3px rgba(59, 91, 253, .14);
        }

        .ss-list {
            list-style: none;
            margin: 0;
            padding: 6px;
            max-height: 280px;
            overflow-y: auto;
        }

        .ss-item {
            padding: 10px 12px;
            border-radius: 9px;
            font-size: .9rem;
            font-weight: 500;
            color: var(--ink);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }

        .ss-item.is-hl {
            background: var(--brand-soft);
        }

        .ss-item.is-sel {
            color: var(--brand);
            font-weight: 700;
        }

        .ss-item.is-sel::after {
            content: "\f00c";
            font-family: "Font Awesome 5 Free", "Font Awesome 6 Free", FontAwesome;
            font-weight: 900;
            font-size: .75rem;
            flex-shrink: 0;
        }

        .ss-item mark {
            background: #fff0a8;
            color: inherit;
            padding: 0 1px;
            border-radius: 3px;
        }

        .ss-none {
            display: none;
            padding: 22px 16px;
            text-align: center;
            color: var(--muted);
            font-size: .88rem;
        }

        .ss-foot {
            padding: 8px 14px;
            border-top: 1px solid var(--line);
            background: var(--bg);
            font-size: .72rem;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: .05em;
        }

        /* ---------- Preview ---------- */
        .crs-preview-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
            padding: 12px 18px;
            border-bottom: 1px solid var(--line);
            background: var(--bg);
        }

        .crs-dots {
            display: flex;
            gap: 6px;
        }

        .crs-dots i {
            width: 11px;
            height: 11px;
            border-radius: 50%;
            background: #d5dae6;
            display: block;
        }

        .crs-title {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .crs-title b {
            font-size: .92rem;
            color: var(--ink);
        }

        .crs-pill {
            font-size: .72rem;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 99px;
            background: #e9edf5;
            color: var(--muted);
        }

        .crs-pill.is-ready {
            background: var(--ok-soft);
            color: var(--ok);
        }

        .crs-pill.is-wait {
            background: var(--brand-soft);
            color: var(--brand);
        }

        .crs-tools {
            display: flex;
            gap: 8px;
        }

        .crs-tools .btn {
            border-radius: 10px;
            font-weight: 600;
            height: 40px;
            padding: 0 18px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
        }

        .crs-stage {
            position: relative;
            background: #eef1f7;
            min-height: 460px;
        }

        #frame {
            display: block;
            width: 100%;
            height: 1250px;
            border: 0;
            background: #f1f5f9;
        }

        .crs-empty {
            position: absolute;
            inset: 0;
            z-index: 2;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            padding-top: 110px;
            text-align: center;
            color: var(--muted);
            background: repeating-linear-gradient(135deg, #f6f8fc, #f6f8fc 14px, #f1f4fa 14px, #f1f4fa 28px);
        }

        .crs-empty .ic {
            width: 72px;
            height: 72px;
            border-radius: 20px;
            background: #fff;
            border: 1px solid var(--line);
            box-shadow: 0 6px 20px rgba(20, 27, 45, .08);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.7rem;
            color: var(--brand);
            margin-bottom: 16px;
        }

        .crs-empty h6 {
            font-weight: 700;
            margin-bottom: 4px;
        }

        .crs-empty p {
            margin: 0;
            font-size: .88rem;
            max-width: 340px;
        }

        .crs-loading {
            position: absolute;
            inset: 0;
            z-index: 3;
            display: none;
            align-items: flex-start;
            justify-content: center;
            padding-top: 120px;
            background: rgba(255, 255, 255, .75);
            backdrop-filter: blur(2px);
        }

        .crs-loading.show {
            display: flex;
        }

        .crs-spin {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            border: 4px solid #dfe5f6;
            border-top-color: var(--brand);
            animation: crsSpin .8s linear infinite;
        }

        @keyframes crsSpin {
            to {
                transform: rotate(360deg);
            }
        }
    </style>

    <div class="container-fluid pt-4 crs-page">

        <div class="crs-hero">
            <h3>Custom Report Cards — Preview studio</h3>
            <p>See any design with a school's real exams and students. Edit the Blade file, then press Refresh.</p>
        </div>

        @include('Admin.custom-report-cards._nav')

        @if($ready)

            {{-- ============ Selection ============ --}}
            <div class="crs-panel crs-panel-select">
                <div class="crs-panel-head">
                    <h5><i class="fas fa-sliders-h mr-2 text-primary"></i>Preview settings</h5>
                    <small>Work through the four steps from left to right.</small>
                </div>

                <div class="crs-steps">
                    <div class="crs-step" data-step="design">
                        <div class="crs-step-top"><span class="crs-num">1</span><label class="crs-label"
                                for="sDesign">Design</label></div>
                        <select id="sDesign" class="form-control">
                            @foreach($templates as $t)
                                <option value="{{ $t->slug }}" data-level="{{ $t->level }}" {{ $preselectSlug === $t->slug ? 'selected' : '' }}>{{ $t->name }} ({{ $t->level }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="crs-step" data-step="school">
                        <div class="crs-step-top"><span class="crs-num">2</span><label class="crs-label"
                                for="sSchool">School</label></div>
                        <select id="sSchool" class="form-control">
                            <option value="">Choose…</option>
                            @foreach($schools as $s)
                                <option value="{{ $s->id }}" {{ (string) $preselectSchool === (string) $s->id ? 'selected' : '' }}>
                                    {{ $s->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="crs-step" data-step="exam">
                        <div class="crs-step-top"><span class="crs-num">3</span><label class="crs-label"
                                for="sExam">Examination</label></div>
                        <select id="sExam" class="form-control" disabled>
                            <option value="">Choose a school first</option>
                        </select>
                    </div>

                    <div class="crs-step" data-step="student">
                        <div class="crs-step-top"><span class="crs-num">4</span><label class="crs-label"
                                for="sStudent">Student</label></div>
                        <select id="sStudent" class="form-control" disabled>
                            <option value="">Choose an exam first</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- ============ Preview ============ --}}
            <div class="crs-panel">
                <div class="crs-preview-head">
                    <div class="crs-title">
                        <div class="crs-dots"><i></i><i></i><i></i></div>
                        <b>Live preview</b>
                        <span class="crs-pill" id="statusPill">Waiting for selection</span>
                    </div>
                    <div class="crs-tools">
                        <button id="btnRefresh" class="btn btn-primary" disabled><i class="fas fa-image mr-1"></i>Refresh
                            preview</button>
                        <a id="btnOpen" class="btn btn-outline-secondary disabled" target="_blank" href="#"><i
                                class="fas fa-external-link-alt mr-1"></i>Open full size</a>
                    </div>
                </div>

                <div class="crs-stage">
                    <div class="crs-empty" id="emptyState">
                        <div class="ic"><i class="fas fa-file-alt"></i></div>
                        <h6>Nothing to preview yet</h6>
                        <p>Pick a design, school, examination and student above. The report card appears here.</p>
                    </div>
                    <div class="crs-loading" id="loadingState">
                        <div class="crs-spin"></div>
                    </div>
                    <iframe id="frame" title="Preview"></iframe>
                </div>
            </div>

        @endif
    </div>
    </div>
    </div>
    </div>
@endsection

@section('js')
    <script>
        (function () {
            const $ = id => document.getElementById(id);
            if (!$('sDesign')) return;

            /* ---------------------------------------------------------
             * Searchable select: enhances a native <select>.
             * The native select stays in the DOM (hidden) and remains the
             * source of truth, so .value and 'change' listeners keep working.
             * ------------------------------------------------------- */
            function searchableSelect(select, opts) {
                opts = opts || {};
                const placeholder = opts.placeholder || 'Choose…';

                const wrap = document.createElement('div');
                wrap.className = 'ss';
                select.parentNode.insertBefore(wrap, select);
                wrap.appendChild(select);
                select.style.display = 'none';

                wrap.insertAdjacentHTML('beforeend',
                    '<button type="button" class="ss-btn" aria-haspopup="listbox" aria-expanded="false">' +
                    '<span class="ss-val is-placeholder"></span>' +
                    '<i class="fas fa-chevron-down ss-caret"></i>' +
                    '</button>' +
                    '<div class="ss-panel">' +
                    '<div class="ss-search"><i class="fas fa-search"></i>' +
                    '<input type="text" autocomplete="off" placeholder="' + (opts.searchPlaceholder || 'Type to search…') + '"></div>' +
                    '<ul class="ss-list" role="listbox"></ul>' +
                    '<div class="ss-none">' + (opts.emptyText || 'No results match.') + '</div>' +
                    '<div class="ss-foot"></div>' +
                    '</div>');

                const btn = wrap.querySelector('.ss-btn');
                const valEl = wrap.querySelector('.ss-val');
                const input = wrap.querySelector('.ss-search input');
                const list = wrap.querySelector('.ss-list');
                const none = wrap.querySelector('.ss-none');
                const foot = wrap.querySelector('.ss-foot');

                let shown = [];
                let hl = 0;

                const all = () => Array.from(select.options)
                    .filter(o => o.value !== '')
                    .map(o => ({ value: o.value, label: o.textContent.replace(/\s+/g, ' ').trim() }));

                function updateLabel() {
                    const cur = all().find(o => o.value === select.value);
                    valEl.textContent = cur ? cur.label : placeholder;
                    valEl.classList.toggle('is-placeholder', !cur);
                }

                function paintHl() {
                    const items = list.children;
                    for (let i = 0; i < items.length; i++) items[i].classList.toggle('is-hl', i === hl);
                    if (items[hl]) items[hl].scrollIntoView({ block: 'nearest' });
                }

                function render(q) {
                    const term = (q || '').trim().toLowerCase();
                    const everything = all();
                    shown = everything.filter(o => !term || o.label.toLowerCase().includes(term));
                    list.innerHTML = '';

                    shown.forEach(o => {
                        const li = document.createElement('li');
                        li.className = 'ss-item' + (o.value === select.value ? ' is-sel' : '');
                        li.setAttribute('role', 'option');
                        const idx = term ? o.label.toLowerCase().indexOf(term) : -1;
                        if (idx > -1) {
                            const span = document.createElement('span');
                            span.append(o.label.slice(0, idx));
                            const m = document.createElement('mark');
                            m.textContent = o.label.slice(idx, idx + term.length);
                            span.append(m, o.label.slice(idx + term.length));
                            li.appendChild(span);
                        } else {
                            const span = document.createElement('span');
                            span.textContent = o.label;
                            li.appendChild(span);
                        }
                        li.addEventListener('mousedown', e => { e.preventDefault(); choose(o.value); });
                        list.appendChild(li);
                    });

                    none.style.display = shown.length ? 'none' : 'block';
                    foot.textContent = 'Showing ' + shown.length + ' of ' + everything.length;

                    const selIdx = term ? -1 : shown.findIndex(o => o.value === select.value);
                    hl = selIdx > -1 ? selIdx : 0;
                    paintHl();
                }

                function open() {
                    if (wrap.classList.contains('open')) return;
                    wrap.classList.add('open');
                    btn.setAttribute('aria-expanded', 'true');
                    input.value = '';
                    render('');
                    setTimeout(() => input.focus(), 0);
                }

                function close() {
                    wrap.classList.remove('open');
                    btn.setAttribute('aria-expanded', 'false');
                }

                function choose(value) {
                    const changed = select.value !== value;
                    select.value = value;
                    updateLabel();
                    close();
                    btn.focus();
                    if (changed) select.dispatchEvent(new Event('change', { bubbles: true }));
                }

                btn.addEventListener('click', () => wrap.classList.contains('open') ? close() : open());
                btn.addEventListener('keydown', e => {
                    if (e.key === 'ArrowDown' || e.key === 'ArrowUp') { e.preventDefault(); open(); }
                });

                input.addEventListener('input', () => render(input.value));
                input.addEventListener('keydown', e => {
                    if (e.key === 'ArrowDown') { e.preventDefault(); if (shown.length) { hl = Math.min(hl + 1, shown.length - 1); paintHl(); } }
                    else if (e.key === 'ArrowUp') { e.preventDefault(); if (shown.length) { hl = Math.max(hl - 1, 0); paintHl(); } }
                    else if (e.key === 'Enter') { e.preventDefault(); if (shown[hl]) choose(shown[hl].value); }
                    else if (e.key === 'Escape') { e.preventDefault(); close(); btn.focus(); }
                    else if (e.key === 'Tab') { close(); }
                });

                document.addEventListener('mousedown', e => { if (!wrap.contains(e.target)) close(); });

                const lbl = document.querySelector('label[for="' + select.id + '"]');
                if (lbl) lbl.addEventListener('click', e => { e.preventDefault(); open(); });

                updateLabel();
            }

            searchableSelect($('sSchool'), {
                placeholder: 'Choose…',
                searchPlaceholder: 'Type a school name…',
                emptyText: 'No schools match your search.'
            });

            /* --------------------------------------------------------- */

            const urls = {
                exams: @json(route('admin.custom-report-cards.studio.exams')),
                students: @json(route('admin.custom-report-cards.studio.students')),
                preview: @json(route('admin.custom-report-cards.studio.preview'))
            };

            const fill = (sel, items, empty) => {
                sel.innerHTML = '<option value="">' + empty + '</option>' + items.map(i => `<option value="${i.id}">${String(i.label).replace(/</g, '&lt;')}</option>`).join('');
                sel.disabled = items.length === 0;
            };
            const level = () => $('sDesign').selectedOptions[0]?.dataset.level || '';
            const previewUrl = () => urls.preview + '?' + new URLSearchParams({ slug: $('sDesign').value, school_id: $('sSchool').value, exam_id: $('sExam').value, student_id: $('sStudent').value });

            // Step highlighting: green when filled, blue on the first unfilled step
            function paintSteps() {
                const order = [['design', 'sDesign'], ['school', 'sSchool'], ['exam', 'sExam'], ['student', 'sStudent']];
                let activeSet = false;
                order.forEach(([key, id]) => {
                    const el = document.querySelector('.crs-step[data-step="' + key + '"]');
                    const done = !!$(id).value;
                    el.classList.toggle('is-done', done);
                    const active = !done && !activeSet;
                    el.classList.toggle('is-active', active);
                    if (active) activeSet = true;
                });
            }

            function setStatus(text, cls) {
                const p = $('statusPill');
                p.textContent = text;
                p.className = 'crs-pill' + (cls ? ' ' + cls : '');
            }

            async function loadExams() {
                fill($('sExam'), [], 'Choose a school first'); fill($('sStudent'), [], 'Choose an exam first');
                paintSteps(); refresh();
                if (!$('sSchool').value) return;
                const r = await (await fetch(urls.exams + '?school_id=' + $('sSchool').value)).json();
                fill($('sExam'), r.exams, r.exams.length ? 'Choose…' : 'No exams for this school');
                paintSteps();
            }
            async function loadStudents() {
                fill($('sStudent'), [], 'Choose an exam first');
                paintSteps(); refresh();
                if (!$('sExam').value) return;
                const q = new URLSearchParams({ school_id: $('sSchool').value, exam_id: $('sExam').value, level: level() });
                const r = await (await fetch(urls.students + '?' + q)).json();
                fill($('sStudent'), r.students, r.students.length ? 'Choose…' : 'No students with marks at this level');
                paintSteps();
            }
            function refresh() {
                const ok = $('sDesign').value && $('sSchool').value && $('sExam').value && $('sStudent').value;
                $('btnRefresh').disabled = !ok; $('btnOpen').classList.toggle('disabled', !ok);
                paintSteps();
                if (ok) {
                    $('emptyState').style.display = 'none';
                    $('loadingState').classList.add('show');
                    setStatus('Loading…', 'is-wait');
                    $('frame').src = previewUrl(); $('btnOpen').href = previewUrl();
                } else {
                    setStatus('Waiting for selection', '');
                }
            }

            $('frame').addEventListener('load', function () {
                if (!$('frame').getAttribute('src')) return;
                $('loadingState').classList.remove('show');
                setStatus('Preview ready', 'is-ready');
            });

            $('sSchool').addEventListener('change', loadExams);
            $('sExam').addEventListener('change', loadStudents);
            $('sDesign').addEventListener('change', loadStudents);
            $('sStudent').addEventListener('change', refresh);
            $('btnRefresh').addEventListener('click', refresh);

            paintSteps();
            if ($('sSchool').value) loadExams();
        })();
    </script>
@endsection