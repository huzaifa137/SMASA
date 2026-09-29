@extends('layouts-side-bar.master')

@section('css')
    <style>
        .es-topbar {
            background: linear-gradient(135deg, #1a1a7a 0%, #2C29CA 60%, #6b69e8 100%);
            border-radius: 0 0 1.5rem 1.5rem;
            padding: 1.4rem 2rem;
            color: #fff;
        }

        .es-topbar .es-label {
            font-size: .72rem;
            letter-spacing: .08em;
            text-transform: uppercase;
            opacity: .8;
        }

        .es-topbar h1 {
            font-size: 1.35rem;
            font-weight: 800;
            margin: 0;
            color: #fff;
        }

        .es-back {
            background: rgba(255, 255, 255, .15);
            color: #fff;
            border-radius: .6rem;
            padding: .45rem .9rem;
            font-size: .8rem;
            font-weight: 600;
            text-decoration: none;
        }

        .es-back:hover {
            background: rgba(255, 255, 255, .28);
            color: #fff;
        }

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
            gap: .5rem;
            padding: .85rem 1.1rem;
            background: #f4f5ff;
            border-bottom: 1px solid #e6e8fb;
        }

        .es-card-hd .title {
            font-weight: 800;
            color: #1a1a7a;
            font-size: .95rem;
        }

        .es-mini-btn {
            border: 1px solid #c9ccf3;
            background: #fff;
            color: #2C29CA;
            border-radius: .5rem;
            font-size: .72rem;
            font-weight: 700;
            padding: .3rem .65rem;
            cursor: pointer;
        }

        .es-mini-btn:hover {
            background: #eef0ff;
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
            color: #6b7280;
            padding: .6rem 1rem;
            border-bottom: 1px solid #eee;
            white-space: nowrap;
        }

        .es-table td {
            padding: .6rem 1rem;
            border-bottom: 1px solid #f1f1f6;
            vertical-align: middle;
        }

        .es-table tr.es-off td.es-name {
            color: #9ca3af;
            text-decoration: line-through;
        }

        .es-table tr.es-hidden td.es-name {
            color: #b45309;
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
            gap: 1rem;
            z-index: 20;
        }

        .es-save {
            background: linear-gradient(135deg, #2C29CA, #5351e4);
            color: #fff;
            border: 0;
            border-radius: .7rem;
            padding: .6rem 1.4rem;
            font-weight: 700;
            font-size: .85rem;
        }

        .es-save[disabled] {
            opacity: .6;
        }
    </style>
@endsection

@section('content')
    <div class="es-topbar mb-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <div class="es-label">Exam Subjects</div>
                <h1>{{ $exam->exam_name }} — {{ $exam->term }} {{ $exam->academic_year }}</h1>
            </div>
            <a href="{{ route('examination.index') }}" class="es-back"><i class="fas fa-arrow-left me-1"></i> Back to
                Examinations</a>
        </div>
    </div>

    <div class="px-3 px-md-4">

        <div class="es-help mb-3">
            <strong>Choose what takes part in this examination.</strong> By default every subject a class has is sat in
            every examination. Untick <em>Sat in this exam</em> for a subject that is not being examined this time — it
            disappears from marks entry and no longer holds up releasing results. Untick <em>Show on pass slip /
                report card</em> to let a subject still receive marks but keep it off the printed slip (it is then also
            left out of that slip's totals and class position, so the slip always adds up).
        </div>

        @if(in_array($exam->status, ['closed', 'results_released']))
            <div class="es-note mb-3">
                <i class="fas fa-triangle-exclamation me-1"></i>
                This examination is <strong>{{ str_replace('_', ' ', $exam->status) }}</strong>. Changes made here will
                immediately change the pass slips and report cards that are printed from now on.
            </div>
        @endif

        @forelse($classes as $ci => $class)
            <div class="es-card" data-class-card data-class-id="{{ $class->class_id }}"
                data-stream-id="{{ $class->stream_key }}">
                <div class="es-card-hd">
                    <div class="title"><i class="fas fa-chalkboard me-2"></i>{{ $class->label }}</div>
                    <div class="d-flex flex-wrap gap-2">
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
                            <tr>
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
                                        <input type="checkbox" class="form-check-input es-sat" {{ $sub->sat ? 'checked' : '' }}>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" class="form-check-input es-show" {{ $sub->show ? 'checked' : '' }}>
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
                <span class="text-muted" style="font-size:.8rem;">Nothing changes until you press Save.</span>
                <button type="button" class="es-save" id="esSaveBtn"><i class="fas fa-save me-1"></i> Save subjects</button>
            </div>
        @endif
    </div>

    <script>
        (function () {
            const SAVE_URL = @json(route('examination.subjects.save', $exam->id));
            const CSRF = @json(csrf_token());

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

            document.querySelectorAll('[data-subject-row]').forEach(tr => {
                refreshRow(tr);
                tr.querySelector('.es-sat').addEventListener('change', function () {
                    // Ticking "sat" back on restores "show" to on (the default).
                    if (this.checked) tr.querySelector('.es-show').checked = true;
                    refreshRow(tr);
                });
                tr.querySelector('.es-show').addEventListener('change', () => refreshRow(tr));
            });

            document.querySelectorAll('[data-class-card]').forEach(card => {
                card.querySelectorAll('[data-act]').forEach(btn => {
                    btn.addEventListener('click', function () {
                        const act = this.dataset.act;

                        if (act === 'all-on' || act === 'all-off') {
                            const on = act === 'all-on';
                            card.querySelectorAll('[data-subject-row]').forEach(tr => {
                                tr.querySelector('.es-sat').checked = on;
                                tr.querySelector('.es-show').checked = on;
                                refreshRow(tr);
                            });
                            return;
                        }

                        // copy-all: same subject (by id) in every other class.
                        const source = {};
                        card.querySelectorAll('[data-subject-row]').forEach(tr => {
                            source[tr.dataset.key.split('|').slice(2).join('|')] = {
                                sat: tr.querySelector('.es-sat').checked,
                                show: tr.querySelector('.es-show').checked,
                            };
                        });

                        document.querySelectorAll('[data-class-card]').forEach(other => {
                            if (other === card) return;
                            other.querySelectorAll('[data-subject-row]').forEach(tr => {
                                const s = source[tr.dataset.key.split('|').slice(2).join('|')];
                                if (!s) return;
                                tr.querySelector('.es-sat').checked = s.sat;
                                tr.querySelector('.es-show').checked = s.show;
                                refreshRow(tr);
                            });
                        });
                    });
                });
            });

            document.getElementById('esSaveBtn')?.addEventListener('click', async function () {
                // Warn once if a subject that already has marks is being
                // switched off / hidden — the marks are kept, but they stop
                // counting toward slips.
                let touched = 0;
                document.querySelectorAll('[data-subject-row]').forEach(tr => {
                    const pill = tr.querySelector('[data-marks]');
                    if (pill && (!tr.querySelector('.es-sat').checked || !tr.querySelector('.es-show').checked)) touched++;
                });

                if (touched > 0 && !confirm(touched + ' subject(s) that already have marks saved will be switched off or hidden. The marks are kept, but they will no longer appear on slips or count toward totals and positions. Continue?')) {
                    return;
                }

                const rows = [];
                document.querySelectorAll('[data-class-card]').forEach(card => {
                    card.querySelectorAll('[data-subject-row]').forEach(tr => {
                        rows.push({
                            class_id: parseInt(card.dataset.classId, 10),
                            stream_id: card.dataset.streamId,
                            subject_id: tr.dataset.subjectId ? parseInt(tr.dataset.subjectId, 10) : null,
                            custom_subject_id: tr.dataset.customSubjectId ? parseInt(tr.dataset.customSubjectId, 10) : null,
                            sat: tr.querySelector('.es-sat').checked,
                            show: tr.querySelector('.es-show').checked,
                        });
                    });
                });

                const btn = this;
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Saving...';

                try {
                    const res = await fetch(SAVE_URL, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': CSRF,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ rows }),
                    });
                    const data = await res.json();

                    if (data.success) {
                        btn.innerHTML = '<i class="fas fa-check me-1"></i> Saved';
                        setTimeout(() => { btn.disabled = false; btn.innerHTML = '<i class="fas fa-save me-1"></i> Save subjects'; }, 1500);
                    } else {
                        alert(data.message || 'Could not save.');
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fas fa-save me-1"></i> Save subjects';
                    }
                } catch (e) {
                    alert('Network error — nothing was saved.');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-save me-1"></i> Save subjects';
                }
            });
        })();
    </script>
@endsection
