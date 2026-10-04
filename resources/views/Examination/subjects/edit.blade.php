@extends('layouts-side-bar.master')

@section('css')
    <style>
        .es-card {
            background: #fff;
            border-radius: 1rem;
            box-shadow: 0 2px 14px rgba(20, 20, 80, .07);
            margin-bottom: 1.25rem;
            overflow: hidden;
        }

        .es-card-hd {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 1.25rem;
            background: #f4f5ff;
            border-bottom: 1px solid #e6e8fb;
        }

        .es-card-hd .title {
            font-weight: 800;
            color: #1a1a7a;
            font-size: 1.1rem;
        }

        .es-card-badge {
            display: none;
            margin-left: .6rem;
            background: #fef3c7;
            color: #b45309;
            border: 1px solid #fde68a;
            border-radius: 999px;
            font-size: .68rem;
            font-weight: 700;
            padding: .15rem .6rem;
            vertical-align: middle;
        }

        .es-mini-btn {
            border: 1px solid #c9ccf3;
            background: #fff;
            color: #2C29CA;
            border-radius: .6rem;
            font-size: .85rem;
            font-weight: 700;
            padding: .5rem 1rem;
            margin-right: .6rem;
            margin-bottom: .25rem;
            cursor: pointer;
            transition: all .2s ease;
            white-space: nowrap;
        }

        .es-mini-btn:last-child {
            margin-right: 0;
        }

        .es-mini-btn:hover {
            background: #eef0ff;
        }

        .es-mini-btn[disabled] {
            opacity: .6;
            cursor: not-allowed;
        }

        .es-table {
            width: 100%;
            margin: 0;
            font-size: .85rem;
        }

        .es-table th {
            font-size: .7rem;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: #fff;
            padding: .75rem 1rem;
            border-bottom: 1px solid #eee;
            white-space: nowrap;
        }

        .es-table td {
            padding: .6rem 1rem;
            border-bottom: 1px solid #f1f1f6;
            vertical-align: middle;
            transition: background .2s ease;
        }

        .es-table tr.es-off td.es-name {
            color: #9ca3af;
            text-decoration: line-through;
        }

        .es-table tr.es-hidden td.es-name {
            color: #b45309;
        }

        /* Row has been changed but not saved yet */
        .es-table tr.es-changed td {
            background: #fffbeb;
        }

        .es-table tr.es-changed td:first-child {
            box-shadow: inset 3px 0 0 #F59E0B;
        }

        .es-teacher {
            color: #6b7280;
            font-size: .75rem;
        }

        .es-marks-pill {
            display: inline-block;
            background: #eef2ff;
            color: #3730a3;
            border-radius: 999px;
            font-size: .68rem;
            font-weight: 700;
            padding: .1rem .5rem;
        }

        .es-note {
            background: #fffbeb;
            border: 1px solid #fde68a;
            color: #92400e;
            border-radius: .7rem;
            padding: .7rem 1rem;
            font-size: .8rem;
        }

        .es-help {
            background: #eef2ff;
            border: 1px solid #dfe3ff;
            color: #312e81;
            border-radius: .7rem;
            padding: .8rem 1rem;
            font-size: .8rem;
        }

        .es-savebar {
            position: sticky;
            bottom: 0;
            background: #fff;
            border-top: 1px solid #e5e7eb;
            padding: .8rem 1.2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            z-index: 20;
        }

        .es-status {
            font-size: .82rem;
            color: #6b7280;
            margin-right: 1rem;
        }

        .es-status.dirty {
            color: #92400e;
        }

        .es-save {
            background: linear-gradient(135deg, #2C29CA, #5351e4);
            color: #fff;
            border: 0;
            border-radius: .7rem;
            padding: .6rem 1.4rem;
            font-weight: 700;
            font-size: .85rem;
            cursor: pointer;
        }

        .es-save[disabled] {
            opacity: .6;
            cursor: not-allowed;
        }

        /* Toggle switch */
        .es-switch {
            position: relative;
            display: inline-block;
            width: 42px;
            height: 22px;
            margin: 0;
            vertical-align: middle;
        }

        .es-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .es-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #cbd5e1;
            transition: .25s ease;
            border-radius: 22px;
        }

        .es-slider:before {
            position: absolute;
            content: "";
            height: 16px;
            width: 16px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: .25s ease;
            border-radius: 50%;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .3);
        }

        .es-switch input:checked+.es-slider {
            background-color: #2C29CA;
        }

        .es-switch input:focus+.es-slider {
            box-shadow: 0 0 0 3px rgba(44, 41, 202, .2);
        }

        .es-switch input:checked+.es-slider:before {
            transform: translateX(20px);
        }

        .es-switch input:disabled+.es-slider {
            background-color: #e2e8f0;
            cursor: not-allowed;
            opacity: .6;
        }

        .es-switch input:disabled+.es-slider:before {
            box-shadow: none;
        }
    </style>
@endsection

@section('content')

    <div class="px-3 px-md-4">

        <div
            style="background: linear-gradient(135deg, #000000, #070189); border: 1px solid rgba(255, 255, 255, 0.1); border-left: 5px solid #0108de; border-radius: 12px; padding: 1.25rem 1.75rem; margin-top: 1.5rem; margin-bottom: 1.5rem; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);">
            <div class="d-flex flex-wrap align-items-center justify-content-between">
                <div class="mb-2 mb-md-0">
                    <div
                        style="font-size: 0.78rem; color: #94a3b8; margin-bottom: 0.3rem; display: flex; align-items: center;">
                        <span style="color: #FFF; font-weight: 600;"><i class="fas fa-graduation-cap mr-1"></i>
                            Examinations</span>
                        <span class="mx-2">/</span>
                        <span style="color: #cbd5e1; font-weight: 500;">Exam Subjects</span>
                    </div>
                    <h1
                        style="color: #ffffff; font-size: 1.35rem; font-weight: 800; margin: 0; display: flex; align-items: center; flex-wrap: wrap;">
                        <span class="mr-2">{{ $exam->exam_name }}</span>
                        <span
                            style="background: rgba(255, 255, 255, 0.1); color: #93c5fd; border: 1px solid rgba(147, 197, 253, 0.3); font-size: 0.75rem; font-weight: 600; padding: 0.2rem 0.6rem; border-radius: 6px;">
                            {{ \App\Support\Term::label($exam->term) }} {{ $exam->academic_year }}
                        </span>
                    </h1>
                </div>

                <a href="{{ route('examination.index') }}"
                    style="background: rgba(255, 255, 255, 0.1); color: #f8fafc; border: 1px solid rgba(255, 255, 255, 0.2); border-radius: 8px; padding: 0.625rem 1.25rem; font-size: 0.9rem; font-weight: 600; text-decoration: none; transition: all 0.2s ease; display: inline-flex; align-items: center;"
                    onmouseover="this.style.background='rgba(255, 255, 255, 0.2)'; this.style.color='#ffffff';"
                    onmouseout="this.style.background='rgba(255, 255, 255, 0.1)'; this.style.color='#f8fafc';">
                    <i class="fas fa-arrow-left mr-2" style="color: #94a3b8;"></i>
                    <span>Back to Examinations</span>
                </a>
            </div>
        </div>

        <div class="es-help mb-3">
            <strong>Choose what takes part in this examination.</strong> By default every subject a class has is sat in
            every examination. Toggle <em>Sat in this exam</em> off for a subject that is not being examined this time — it
            disappears from marks entry and no longer holds up releasing results. Toggle <em>Show on pass slip /
                report card</em> off to let a subject still receive marks but keep it off the printed slip (it is then also
            left out of that slip's totals and class position, so the slip always adds up).
        </div>

        @if(in_array($exam->status, ['closed', 'results_released']))
            <div class="es-note mb-3">
                <i class="fas fa-triangle-exclamation mr-1"></i>
                This examination is <strong>{{ str_replace('_', ' ', $exam->status) }}</strong>. Changes made here will
                immediately change the pass slips and report cards that are printed from now on.
            </div>
        @endif

        @forelse($classes as $ci => $class)
            <div class="es-card" data-class-card data-class-id="{{ $class->class_id }}"
                data-stream-id="{{ $class->stream_key }}">
                <div class="es-card-hd">
                    <div class="title">
                        <i class="fas fa-chalkboard mr-2"></i><span data-class-label>{{ $class->label }}</span>
                        <span class="es-card-badge" data-card-badge></span>
                    </div>
                    <div class="d-flex flex-wrap align-items-center">
                        <button type="button" class="es-mini-btn" data-act="all-on">Select all</button>
                        <button type="button" class="es-mini-btn" data-act="all-off">Clear all</button>
                        <button type="button" class="es-mini-btn" data-act="copy-all"
                            title="Copy this class's choices to every other class in this exam (matching subjects only)">
                            Copy to all classes
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="es-table">
                        <thead>
                            <tr style="background-color: #2C29CA;">
                                <th>Subject</th>
                                <th class="text-center">Sat in this exam</th>
                                <th class="text-center">Show on pass slip / report card</th>
                                <th class="text-center">Marks saved</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($class->subjects as $sub)
                                <tr data-subject-row data-key="{{ $sub->key }}" data-subject-id="{{ $sub->subject_id }}"
                                    data-custom-subject-id="{{ $sub->custom_subject_id }}"
                                    data-subject-name="{{ strtolower($sub->name) }}">
                                    <td class="es-name">
                                        <strong>{{ $sub->name }}</strong>
                                        @if($sub->teacher)
                                            <div class="es-teacher">{{ $sub->teacher }}</div>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <label class="es-switch">
                                            <input type="checkbox" class="es-sat" {{ $sub->sat ? 'checked' : '' }}>
                                            <span class="es-slider"></span>
                                        </label>
                                    </td>
                                    <td class="text-center">
                                        <label class="es-switch">
                                            <input type="checkbox" class="es-show" {{ $sub->show ? 'checked' : '' }}>
                                            <span class="es-slider"></span>
                                        </label>
                                    </td>
                                    <td class="text-center">
                                        @if($sub->marks > 0)
                                            <span class="es-marks-pill" data-marks="{{ $sub->marks }}">{{ $sub->marks }}</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @empty
            <div class="es-card p-4 text-center text-muted">
                <i class="fas fa-book-open fa-2x mb-2"></i>
                <p class="mb-0">No classes with subjects are attached to this examination yet.</p>
            </div>
        @endforelse

        @if(count($classes))
            <div class="es-savebar">
                <span class="es-status" id="esStatus">
                    <i class="fas fa-check-circle text-success mr-1"></i> No unsaved changes.
                </span>
                <button type="button" class="es-save" id="esSaveBtn">
                    <i class="fas fa-save mr-1"></i> Save subjects
                </button>
            </div>
        @endif
    </div>
    </div>
    </div>
    </div>

    <script>
        (function () {
            const SAVE_URL = @json(route('examination.subjects.save', $exam->id));
            const CSRF = @json(csrf_token());
            const SAVE_LABEL = '<i class="fas fa-save mr-1"></i> Save subjects';

            // Load SweetAlert2 only if the layout hasn't already.
            function ensureSwal(cb) {
                if (window.Swal) return cb();
                const s = document.createElement('script');
                s.src = 'https://cdn.jsdelivr.net/npm/sweetalert2@11';
                s.onload = cb;
                document.head.appendChild(s);
            }

            ensureSwal(function () {

                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 2800,
                    timerProgressBar: true,
                });

                const rows = Array.from(document.querySelectorAll('[data-subject-row]'));
                const statusEl = document.getElementById('esStatus');
                const saveBtn = document.getElementById('esSaveBtn');

                // What is currently saved on the server (updated after each successful save).
                const baseline = new Map();
                let lastSavedAt = null;
                let busy = false;

                function state(tr) {
                    return { sat: tr.querySelector('.es-sat').checked, show: tr.querySelector('.es-show').checked };
                }

                function isChanged(tr) {
                    const b = baseline.get(tr.dataset.key);
                    const s = state(tr);
                    return !b || b.sat !== s.sat || b.show !== s.show;
                }

                function refreshRow(tr) {
                    const sat = tr.querySelector('.es-sat');
                    const show = tr.querySelector('.es-show');

                    // Not sat => cannot be shown on the report.
                    if (!sat.checked) {
                        show.checked = false;
                        show.disabled = true;
                    } else {
                        show.disabled = false;
                    }

                    tr.classList.toggle('es-off', !sat.checked);
                    tr.classList.toggle('es-hidden', sat.checked && !show.checked);
                }

                function plural(n, word) {
                    return n + ' ' + word + (n === 1 ? '' : 's');
                }

                // Recalculate changed rows, class badges and the footer message.
                function updateDirty() {
                    let total = 0;
                    const touchedCards = new Set();

                    rows.forEach(tr => {
                        const changed = isChanged(tr);
                        tr.classList.toggle('es-changed', changed);
                        if (changed) {
                            total++;
                            touchedCards.add(tr.closest('[data-class-card]'));
                        }
                    });

                    document.querySelectorAll('[data-class-card]').forEach(card => {
                        const n = card.querySelectorAll('[data-subject-row].es-changed').length;
                        const badge = card.querySelector('[data-card-badge]');
                        badge.style.display = n ? 'inline-block' : 'none';
                        badge.textContent = n + ' changed';
                    });

                    if (!statusEl) return;

                    if (total > 0) {
                        statusEl.classList.add('dirty');
                        statusEl.innerHTML =
                            '<i class="fas fa-exclamation-circle mr-1" style="color:#d97706"></i> ' +
                            '<strong>' + plural(total, 'unsaved change') + '</strong> in ' +
                            plural(touchedCards.size, 'class') + ' — press <em>Save subjects</em> to apply.';
                    } else {
                        statusEl.classList.remove('dirty');
                        statusEl.innerHTML = lastSavedAt
                            ? '<i class="fas fa-check-circle text-success mr-1"></i> All changes saved at ' + lastSavedAt + '.'
                            : '<i class="fas fa-check-circle text-success mr-1"></i> No unsaved changes.';
                    }
                }

                function setActionButtons(disabled) {
                    document.querySelectorAll('[data-act]').forEach(b => b.disabled = disabled);
                }

                // Shows a spinner on the clicked button, runs the work, then toasts the result.
                function runAction(btn, busyText, work) {
                    if (busy) return;
                    busy = true;
                    const original = btn.innerHTML;
                    setActionButtons(true);
                    saveBtn.disabled = true;
                    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> ' + busyText;

                    setTimeout(function () {
                        let result = null;
                        try {
                            result = work();
                        } catch (e) {
                            console.error(e);
                            result = { icon: 'error', title: 'Something went wrong. Nothing was changed.' };
                        }
                        btn.innerHTML = original;
                        setActionButtons(false);
                        saveBtn.disabled = false;
                        busy = false;
                        updateDirty();
                        if (result) Toast.fire(result);
                    }, 450);
                }

                // ── Initial state ───────────────────────────────────────────────
                rows.forEach(tr => {
                    refreshRow(tr);
                    baseline.set(tr.dataset.key, state(tr));

                    tr.querySelector('.es-sat').addEventListener('change', function () {
                        // Switching "sat" back on restores "show" to on (the default).
                        if (this.checked) tr.querySelector('.es-show').checked = true;
                        refreshRow(tr);
                        updateDirty();
                    });
                    tr.querySelector('.es-show').addEventListener('change', function () {
                        refreshRow(tr);
                        updateDirty();
                    });
                });
                updateDirty();

                // ── Select all / Clear all / Copy to all classes ────────────────
                document.querySelectorAll('[data-class-card]').forEach(card => {
                    const label = card.querySelector('[data-class-label]').textContent.trim();

                    card.querySelectorAll('[data-act]').forEach(btn => {
                        btn.addEventListener('click', function () {
                            const act = this.dataset.act;

                            if (act === 'all-on' || act === 'all-off') {
                                const on = act === 'all-on';
                                runAction(this, on ? 'Selecting...' : 'Clearing...', function () {
                                    const cardRows = card.querySelectorAll('[data-subject-row]');
                                    cardRows.forEach(tr => {
                                        tr.querySelector('.es-sat').checked = on;
                                        tr.querySelector('.es-show').checked = on;
                                        refreshRow(tr);
                                    });
                                    return {
                                        icon: on ? 'success' : 'info',
                                        title: on
                                            ? plural(cardRows.length, 'subject') + ' selected in ' + label
                                            : plural(cardRows.length, 'subject') + ' cleared in ' + label
                                    };
                                });
                                return;
                            }

                            // copy-all: same subject (by id) in every other class.
                            runAction(this, 'Copying...', function () {
                                const otherCards = Array.from(document.querySelectorAll('[data-class-card]'))
                                    .filter(c => c !== card);

                                if (otherCards.length === 0) {
                                    return { icon: 'info', title: 'There are no other classes in this examination.' };
                                }

                                const source = {};
                                card.querySelectorAll('[data-subject-row]').forEach(tr => {
                                    source[tr.dataset.key.split('|').slice(2).join('|')] = state(tr);
                                });

                                let updatedRows = 0;
                                otherCards.forEach(other => {
                                    other.querySelectorAll('[data-subject-row]').forEach(tr => {
                                        const s = source[tr.dataset.key.split('|').slice(2).join('|')];
                                        if (!s) return;
                                        tr.querySelector('.es-sat').checked = s.sat;
                                        tr.querySelector('.es-show').checked = s.show;
                                        refreshRow(tr);
                                        updatedRows++;
                                    });
                                });

                                if (updatedRows === 0) {
                                    return { icon: 'warning', title: 'No matching subjects found in the other classes.' };
                                }
                                return {
                                    icon: 'success',
                                    title: 'Copied ' + label + ' choices to ' + plural(otherCards.length, 'other class') +
                                        ' (' + plural(updatedRows, 'subject') + ' updated)'
                                };
                            });
                        });
                    });
                });

                // ── Save ────────────────────────────────────────────────────────
                saveBtn && saveBtn.addEventListener('click', async function () {
                    if (busy) return;

                    const changedRows = rows.filter(isChanged);
                    if (changedRows.length === 0) {
                        Toast.fire({ icon: 'info', title: 'No changes to save.' });
                        return;
                    }

                    // Warn only about subjects that have marks AND are being switched off / hidden now.
                    const touched = changedRows.filter(tr => {
                        const s = state(tr);
                        return tr.querySelector('[data-marks]') && (!s.sat || !s.show);
                    }).length;

                    if (touched > 0) {
                        const res = await Swal.fire({
                            icon: 'warning',
                            title: 'Subjects with marks affected',
                            html: '<strong>' + plural(touched, 'subject') + '</strong> that already ' +
                                (touched === 1 ? 'has' : 'have') + ' marks saved will be switched off or hidden.<br>' +
                                'The marks are kept, but they will no longer appear on slips or count toward totals and positions.',
                            showCancelButton: true,
                            confirmButtonText: 'Yes, save changes',
                            cancelButtonText: 'Cancel',
                            confirmButtonColor: '#2C29CA',
                            cancelButtonColor: '#6c757d',
                            reverseButtons: true,
                        });
                        if (!res.isConfirmed) return;
                    }

                    const payload = [];
                    document.querySelectorAll('[data-class-card]').forEach(card => {
                        card.querySelectorAll('[data-subject-row]').forEach(tr => {
                            const s = state(tr);
                            payload.push({
                                class_id: parseInt(card.dataset.classId, 10),
                                stream_id: card.dataset.streamId,
                                subject_id: tr.dataset.subjectId ? parseInt(tr.dataset.subjectId, 10) : null,
                                custom_subject_id: tr.dataset.customSubjectId ? parseInt(tr.dataset.customSubjectId, 10) : null,
                                sat: s.sat,
                                show: s.show,
                            });
                        });
                    });

                    busy = true;
                    setActionButtons(true);
                    saveBtn.disabled = true;
                    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Saving...';

                    function finish() {
                        busy = false;
                        setActionButtons(false);
                        saveBtn.disabled = false;
                        saveBtn.innerHTML = SAVE_LABEL;
                    }

                    try {
                        const res = await fetch(SAVE_URL, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': CSRF,
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({ rows: payload }),
                        });

                        let data = {};
                        try { data = await res.json(); } catch (e) { /* non-JSON response */ }

                        if (res.ok && data.success) {
                            rows.forEach(tr => baseline.set(tr.dataset.key, state(tr)));
                            lastSavedAt = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                            updateDirty();

                            saveBtn.innerHTML = '<i class="fas fa-check mr-1"></i> Saved';
                            Toast.fire({
                                icon: 'success',
                                title: 'Exam subjects saved',
                                text: data.exceptions
                                    ? plural(data.exceptions, 'subject') + ' customised for this examination.'
                                    : 'All subjects take part in this examination.',
                            });
                            setTimeout(finish, 1200);
                        } else {
                            finish();
                            Swal.fire({
                                icon: 'error',
                                title: 'Could not save',
                                text: data.message || 'The server rejected the request (status ' + res.status + '). Nothing was saved.',
                                confirmButtonColor: '#2C29CA',
                            });
                        }
                    } catch (e) {
                        finish();
                        Swal.fire({
                            icon: 'error',
                            title: 'Network error',
                            text: 'Nothing was saved. Check your connection and try again.',
                            confirmButtonColor: '#2C29CA',
                        });
                    }
                });

                // Warn before leaving the page with unsaved changes.
                window.addEventListener('beforeunload', function (e) {
                    if (rows.some(isChanged)) {
                        e.preventDefault();
                        e.returnValue = '';
                    }
                });
            });
        })();
    </script>
@endsection