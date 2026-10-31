{{--
    Shared "Papers" modal: set what each paper (P1, P2, ...) of a secondary
    subject is marked out of, for ONE examination.

    Used by the subject's Marks Entry page and by Examinations → Exam Subjects.
    Needs: $exam.

        ExamPapers.open({
            classId, streamId, subjectId, customSubjectId,   // identifies the class-stream subject
            title,                                            // e.g. "Physics — S.4 East"
            canEdit,                                          // false = read-only view
            onChange(papers)                                  // called after papers were saved / removed
        });

    Papers are added one at a time - nothing has to be configured up front.
    No Bootstrap/jQuery dependency, so it works on any page.
--}}
<style>
    .xp-overlay {
        position: fixed;
        inset: 0;
        background: rgba(15, 14, 26, .55);
        z-index: 2000;
        display: none;
        align-items: flex-start;
        justify-content: center;
        padding: 4vh 1rem;
        overflow-y: auto;
    }

    .xp-overlay.open {
        display: flex;
    }

    .xp-modal {
        background: #fff;
        width: 100%;
        max-width: 640px;
        border-radius: 1rem;
        box-shadow: 0 20px 60px rgba(0, 0, 0, .35);
        overflow: hidden;
    }

    .xp-hd {
        background: linear-gradient(135deg, #0F0E1A 0%, #1B1D28 40%, #2C29CA 100%);
        color: #fff;
        padding: 1rem 1.25rem;
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
    }

    .xp-hd h5 {
        margin: 0;
        font-weight: 800;
        font-size: 1.05rem;
        color: #fff;
    }

    .xp-hd small {
        color: rgba(255, 255, 255, .7);
    }

    .xp-x {
        background: none;
        border: 0;
        color: #fff;
        font-size: 1.5rem;
        line-height: 1;
        cursor: pointer;
        opacity: .8;
    }

    .xp-x:hover {
        opacity: 1;
    }

    .xp-body {
        padding: 1rem 1.25rem;
    }

    .xp-help {
        font-size: .82rem;
        color: #475569;
        background: #f5f4ff;
        border-radius: .6rem;
        padding: .6rem .8rem;
        margin-bottom: .9rem;
    }

    .xp-table {
        width: 100%;
        border-collapse: collapse;
    }

    .xp-table th {
        font-size: .7rem;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: #64748b;
        padding: .3rem .4rem;
        text-align: left;
    }

    .xp-table td {
        padding: .35rem .4rem;
        vertical-align: middle;
        border-top: 1px solid #eef0f6;
    }

    .xp-pill {
        display: inline-block;
        min-width: 2.2rem;
        text-align: center;
        font-weight: 800;
        font-size: .8rem;
        color: #2C29CA;
        background: #ede9ff;
        border-radius: .4rem;
        padding: .25rem .5rem;
    }

    .xp-in {
        width: 100%;
        border: 2px solid #ede9ff;
        border-radius: .5rem;
        padding: .3rem .5rem;
        font-size: .88rem;
    }

    .xp-in:focus {
        outline: none;
        border-color: #5351e4;
    }

    .xp-in.bad {
        border-color: #c0392b;
        background: #fef2f2;
    }

    .xp-in[disabled] {
        background: #f8fafc;
        color: #475569;
    }

    .xp-num {
        width: 90px;
        text-align: center;
        font-weight: 700;
    }

    .xp-meta {
        font-size: .74rem;
        color: #64748b;
        line-height: 1.2;
    }

    .xp-del {
        background: none;
        border: 0;
        color: #c0392b;
        cursor: pointer;
        font-size: .95rem;
        padding: .2rem .4rem;
    }

    .xp-add {
        margin-top: .7rem;
        background: #fff;
        color: #2C29CA;
        border: 2px dashed #b9b7f2;
        border-radius: .6rem;
        padding: .45rem .9rem;
        font-weight: 700;
        font-size: .85rem;
        cursor: pointer;
    }

    .xp-add:hover {
        background: #f5f4ff;
    }

    .xp-sum {
        margin-top: .9rem;
        font-size: .85rem;
        color: #1e293b;
        background: #f8fafc;
        border-radius: .6rem;
        padding: .55rem .8rem;
    }

    .xp-ft {
        padding: .8rem 1.25rem 1rem;
        display: flex;
        justify-content: flex-end;
        gap: .6rem;
        border-top: 1px solid #eef0f6;
    }

    .xp-btn {
        border: 0;
        border-radius: .6rem;
        padding: .55rem 1.2rem;
        font-weight: 700;
        font-size: .88rem;
        cursor: pointer;
    }

    .xp-btn.primary {
        background: linear-gradient(135deg, #2C29CA, #5351e4);
        color: #fff;
    }

    .xp-btn.ghost {
        background: #eef0f6;
        color: #334155;
    }

    .xp-btn[disabled] {
        opacity: .6;
        cursor: not-allowed;
    }

    .xp-err {
        color: #c0392b;
        font-size: .83rem;
        margin-top: .6rem;
        display: none;
    }
</style>

<div class="xp-overlay" id="xpOverlay" role="dialog" aria-modal="true" aria-labelledby="xpTitle">
    <div class="xp-modal">
        <div class="xp-hd">
            <div>
                <h5 id="xpTitle">Papers</h5>
                <small id="xpSub"></small>
            </div>
            <button type="button" class="xp-x" id="xpClose" aria-label="Close">&times;</button>
        </div>

        <div class="xp-body">
            <div class="xp-help">
                Set what each paper is <strong>marked out of</strong> in <strong>{{ $exam->exam_name }}</strong>
                (e.g. Paper 1 out of 20, Paper 2 out of 80). You can add papers one at a time, as you find out what each
                one is out of. Teachers then enter the <em>raw</em> mark for each paper, and the subject total is worked
                out automatically: all paper marks added up, divided by all paper totals, scaled to the
                examination's <strong>{{ rtrim(rtrim(number_format((float) $exam->total_marks, 2, '.', ''), '0'), '.') }}</strong>.
                A student's subject total shows once every paper has a mark.
            </div>

            <table class="xp-table">
                <thead>
                    <tr>
                        <th style="width:70px;">Paper</th>
                        <th>Name <span style="text-transform:none;">(optional)</span></th>
                        <th style="width:110px;">Out of</th>
                        <th style="width:110px;">Entered</th>
                        <th style="width:34px;"></th>
                    </tr>
                </thead>
                <tbody id="xpRows"></tbody>
            </table>

            <div id="xpEmpty" class="text-muted" style="display:none;font-size:.85rem;padding:.6rem .2rem;">
                No papers yet - this subject is marked as a single score out of
                {{ rtrim(rtrim(number_format((float) $exam->total_marks, 2, '.', ''), '0'), '.') }}.
                Add a paper to split it.
            </div>

            <button type="button" class="xp-add" id="xpAddBtn"><i class="fas fa-plus mr-1 me-1"></i> Add paper</button>

            <div class="xp-sum" id="xpSum" style="display:none;"></div>
            <div class="xp-err" id="xpErr"></div>
        </div>

        <div class="xp-ft">
            <button type="button" class="xp-btn ghost" id="xpCancel">Close</button>
            <button type="button" class="xp-btn primary" id="xpSave">Save papers</button>
        </div>
    </div>
</div>

<script>
    window.ExamPapers = (function() {
        const URLS = {
            show: @json(route('examination.papers.show', $exam->id)),
            save: @json(route('examination.papers.save', $exam->id)),
            del: @json(route('examination.papers.delete', $exam->id)),
        };
        const CSRF = @json(csrf_token());
        const EXAM_TOTAL = {{ (float) $exam->total_marks }};

        const $ = id => document.getElementById(id);
        const overlay = $('xpOverlay'),
            rowsEl = $('xpRows'),
            emptyEl = $('xpEmpty'),
            sumEl = $('xpSum'),
            errEl = $('xpErr'),
            addBtn = $('xpAddBtn'),
            saveBtn = $('xpSave');

        let ctx = null; // the subject being edited
        let state = []; // rows: {n, label, max, entered, setBy, isNew, origMax, origLabel}
        let maxPapers = 10;
        let changed = false; // something was saved/removed -> tell the opener on close

        const fmt = v => {
            const n = parseFloat(v);
            return isNaN(n) ? '' : String(Math.round(n * 100) / 100);
        };

        function confirmBox(title, html, okText) {
            if (window.Swal) {
                return Swal.fire({
                    icon: 'warning',
                    title,
                    html,
                    showCancelButton: true,
                    confirmButtonColor: '#2C29CA',
                    confirmButtonText: okText
                }).then(r => r.isConfirmed);
            }
            return Promise.resolve(window.confirm(title + '\n\n' + html.replace(/<[^>]+>/g, '')));
        }

        function showError(msg) {
            errEl.textContent = msg || '';
            errEl.style.display = msg ? 'block' : 'none';
        }

        async function api(url, options) {
            const res = await fetch(url, Object.assign({
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': CSRF
                },
                credentials: 'same-origin'
            }, options));
            let data = {};
            try {
                data = await res.json();
            } catch (e) {}
            if (!res.ok || data.success === false) {
                const first = data.errors ? Object.values(data.errors)[0][0] : null;
                const err = new Error(first || data.message || 'Something went wrong (' + res.status + ').');
                err.data = data;
                err.status = res.status;
                throw err;
            }
            return data;
        }

        function subjectParams() {
            const p = new URLSearchParams();
            p.set('class_id', ctx.classId);
            p.set('stream_id', ctx.streamId == null ? '' : ctx.streamId);
            if (ctx.subjectId) p.set('subject_id', ctx.subjectId);
            if (ctx.customSubjectId) p.set('custom_subject_id', ctx.customSubjectId);
            return p;
        }

        function subjectBody() {
            return {
                class_id: ctx.classId,
                stream_id: ctx.streamId == null ? '' : ctx.streamId,
                subject_id: ctx.subjectId || null,
                custom_subject_id: ctx.customSubjectId || null
            };
        }

        function load(papers) {
            state = papers.map(p => ({
                n: p.paper_number,
                label: p.label || '',
                max: fmt(p.max_score),
                origMax: fmt(p.max_score),
                origLabel: p.label || '',
                entered: p.entered,
                setBy: p.set_by,
                isNew: false
            }));
            render();
        }

        function nextNumber() {
            let n = 1;
            const used = new Set(state.map(r => r.n));
            while (used.has(n)) n++;
            return n;
        }

        function render() {
            rowsEl.innerHTML = '';
            emptyEl.style.display = state.length ? 'none' : 'block';
            const edit = ctx.canEdit;

            state.sort((a, b) => a.n - b.n).forEach((r, idx) => {
                const tr = document.createElement('tr');

                const tdN = document.createElement('td');
                const pill = document.createElement('span');
                pill.className = 'xp-pill';
                pill.textContent = 'P' + r.n;
                tdN.appendChild(pill);

                const tdL = document.createElement('td');
                const label = document.createElement('input');
                label.className = 'xp-in';
                label.maxLength = 60;
                label.placeholder = 'Paper ' + r.n;
                label.value = r.label;
                label.disabled = !edit;
                label.addEventListener('input', () => {
                    r.label = label.value;
                });
                tdL.appendChild(label);

                const tdM = document.createElement('td');
                const max = document.createElement('input');
                max.type = 'number';
                max.className = 'xp-in xp-num';
                max.min = '0.01';
                max.max = '1000';
                max.step = '0.5';
                max.placeholder = 'e.g. 20';
                max.value = r.max;
                max.disabled = !edit;
                max.addEventListener('input', () => {
                    r.max = max.value;
                    max.classList.remove('bad');
                    summary();
                });
                tdM.appendChild(max);
                if (r.isNew && edit) setTimeout(() => {
                    if (idx === state.length - 1) max.focus();
                }, 0);

                const tdE = document.createElement('td');
                const meta = document.createElement('div');
                meta.className = 'xp-meta';
                if (r.isNew) {
                    meta.textContent = 'new';
                } else {
                    meta.textContent = r.entered > 0 ? r.entered + ' mark(s)' : 'none yet';
                    if (r.setBy) {
                        const by = document.createElement('div');
                        by.textContent = 'set by ' + r.setBy;
                        meta.appendChild(by);
                    }
                }
                tdE.appendChild(meta);

                const tdD = document.createElement('td');
                if (edit) {
                    const del = document.createElement('button');
                    del.type = 'button';
                    del.className = 'xp-del';
                    del.title = 'Remove this paper';
                    del.innerHTML = '<i class="fas fa-trash"></i>';
                    del.addEventListener('click', () => removeRow(r));
                    tdD.appendChild(del);
                }

                tr.append(tdN, tdL, tdM, tdE, tdD);
                rowsEl.appendChild(tr);
            });

            addBtn.style.display = edit && state.length < maxPapers ? 'inline-block' : 'none';
            saveBtn.style.display = edit ? 'inline-block' : 'none';
            $('xpCancel').textContent = edit ? 'Cancel' : 'Close';
            summary();
        }

        function summary() {
            const vals = state.map(r => parseFloat(r.max)).filter(v => !isNaN(v) && v > 0);
            if (!vals.length) {
                sumEl.style.display = 'none';
                return;
            }
            const total = vals.reduce((a, b) => a + b, 0);
            const parts = state.filter(r => parseFloat(r.max) > 0).map(r => 'P' + r.n + '/' + fmt(r.max)).join(' + ');
            let html = '<strong>' + parts + ' = ' + fmt(total) + '</strong>. A student scoring every paper in full gets ' +
                fmt(EXAM_TOTAL) + '/' + fmt(EXAM_TOTAL) + '; the subject total is scaled to ' + fmt(EXAM_TOTAL) + '.';
            if (state.length !== vals.length) html += ' <em>(Papers with no "out of" yet are not counted.)</em>';
            sumEl.innerHTML = html;
            sumEl.style.display = 'block';
        }

        async function removeRow(r) {
            if (r.isNew) {
                state = state.filter(x => x !== r);
                render();
                return;
            }
            const warn = r.entered > 0 ?
                'This permanently deletes <strong>' + r.entered + '</strong> mark(s) already entered for this paper, and the subject totals are recalculated.' :
                'No marks have been entered for this paper.';
            const ok = await confirmBox('Remove P' + r.n + '?', warn, 'Yes, remove it');
            if (!ok) return;
            showError('');
            try {
                const data = await api(URLS.del, {
                    method: 'POST',
                    body: JSON.stringify(Object.assign(subjectBody(), {
                        paper_number: r.n
                    }))
                });
                changed = true;
                // keep any unsaved new rows the user is typing
                const pendingNew = state.filter(x => x.isNew);
                load(data.papers);
                state = state.concat(pendingNew);
                render();
            } catch (e) {
                showError(e.message);
            }
        }

        async function save(confirmReplace) {
            showError('');
            const rows = state.filter(r => r.isNew || r.max !== r.origMax || (r.label || '') !== (r.origLabel || ''));
            let bad = false;
            rowsEl.querySelectorAll('.xp-num').forEach((el, i) => {
                const r = state[i];
                const v = parseFloat(r.max);
                const needs = rows.includes(r) || r.isNew;
                if (needs && (isNaN(v) || v <= 0)) {
                    el.classList.add('bad');
                    bad = true;
                }
            });
            if (bad) {
                showError('Every paper needs a number for what it is marked out of (greater than 0).');
                return;
            }
            if (!rows.length) {
                close();
                return;
            }

            const addingToLive = rows.some(r => r.isNew) && state.some(r => !r.isNew && r.entered > 0);
            if (addingToLive) {
                const ok = await confirmBox(
                    'Add a paper to a subject that already has marks?',
                    'Students who have every <em>existing</em> paper entered will show a <strong>pending</strong> subject total again until their mark for the new paper is entered.',
                    'Yes, add it'
                );
                if (!ok) return;
            }

            saveBtn.disabled = true;
            saveBtn.textContent = 'Saving…';
            try {
                const data = await api(URLS.save, {
                    method: 'POST',
                    body: JSON.stringify(Object.assign(subjectBody(), {
                        confirm_replace: !!confirmReplace,
                        papers: rows.map(r => ({
                            paper_number: r.n,
                            label: (r.label || '').trim() || null,
                            max_score: parseFloat(r.max)
                        }))
                    }))
                });
                changed = true;
                load(data.papers);
                close();
            } catch (e) {
                if (e.data && e.data.needs_confirmation) {
                    saveBtn.disabled = false;
                    saveBtn.textContent = 'Save papers';
                    const ok = await confirmBox(
                        'Replace the single scores already entered?',
                        '<strong>' + e.data.count + '</strong> student(s) already have one score for this subject. ' +
                        'Splitting it into papers replaces those scores - they show as <strong>pending</strong> until each paper mark is entered.',
                        'Yes, split into papers'
                    );
                    if (ok) return await save(true);
                    return;
                }
                showError(e.message);
            } finally {
                saveBtn.disabled = false;
                saveBtn.textContent = 'Save papers';
            }
        }

        function close() {
            overlay.classList.remove('open');
            document.body.style.overflow = '';
            const cb = ctx && ctx.onChange;
            const didChange = changed;
            const papers = state.filter(r => !r.isNew);
            if (didChange && cb) cb(papers);
        }

        async function open(options) {
            ctx = Object.assign({
                canEdit: true
            }, options);
            changed = false;
            state = [];
            showError('');
            $('xpTitle').textContent = 'Papers';
            $('xpSub').textContent = ctx.title || '';
            rowsEl.innerHTML = '<tr><td colspan="5" class="text-muted" style="padding:1rem;">Loading…</td></tr>';
            emptyEl.style.display = 'none';
            addBtn.style.display = saveBtn.style.display = 'none';
            sumEl.style.display = 'none';
            overlay.classList.add('open');
            document.body.style.overflow = 'hidden';

            try {
                const data = await api(URLS.show + '?' + subjectParams().toString(), {
                    method: 'GET'
                });
                maxPapers = data.max_papers || 10;
                load(data.papers);
            } catch (e) {
                rowsEl.innerHTML = '';
                showError(e.message);
            }
        }

        addBtn.addEventListener('click', () => {
            if (state.length >= maxPapers) return;
            state.push({
                n: nextNumber(),
                label: '',
                max: '',
                origMax: '',
                origLabel: '',
                entered: 0,
                setBy: null,
                isNew: true
            });
            render();
        });
        saveBtn.addEventListener('click', () => save(false));
        $('xpCancel').addEventListener('click', close);
        $('xpClose').addEventListener('click', close);
        overlay.addEventListener('mousedown', e => {
            if (e.target === overlay) close();
        });
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape' && overlay.classList.contains('open')) close();
        });

        return {
            open
        };
    })();
</script>
