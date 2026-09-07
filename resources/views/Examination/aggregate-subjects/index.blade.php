@extends('layouts-side-bar.master')

@section('css')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #2C29CA;
            --primary-dark: #201ea0;
            --primary-light: #EEEDFC;
            --primary-soft: #F5F4FF;
            --ink: #1B1D28;
            --muted: #6B7280;
            --border: #E6E7EE;
            --surface: #FFFFFF;
            --bg: #F6F7FB;
            --green: #12875A;
            --green-bg: #E6F6EF;
        }

        * {
            box-sizing: border-box;
        }

        .ags-app {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            color: var(--ink);
        }

        .ags-topbar {
            position: relative;
            overflow: hidden;
            border-radius: 18px;
            padding: 1.6rem 1.8rem;
            margin-bottom: 1.4rem;
            background: linear-gradient(135deg, #1e1b4b, #2f2ccb 65%, #4338ca);
            color: #fff;
        }

        .ags-topbar-particles {
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at 90% -10%, rgba(255, 255, 255, .14) 0%, transparent 55%),
                radial-gradient(circle at 0% 120%, rgba(255, 255, 255, .1) 0%, transparent 55%);
            pointer-events: none;
        }

        .ags-topbar-inner {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .ags-topbar-icon {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            background: rgba(255, 255, 255, .15);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
            margin-right: 1rem;
            flex-shrink: 0;
        }

        .ags-topbar-left {
            display: flex;
            align-items: center;
        }

        .ags-topbar-badge {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            font-size: .68rem;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
            background: rgba(255, 255, 255, .15);
            padding: .25rem .6rem;
            border-radius: 999px;
            margin-bottom: .4rem;
        }

        .ags-topbar-title {
            font-size: 1.35rem;
            font-weight: 800;
            margin: 0 0 .25rem;
            color: #fff;
        }

        .ags-topbar-subtitle {
            font-size: .82rem;
            opacity: .88;
            margin: 0;
            max-width: 46rem;
        }

        .ags-back {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            background: rgba(255, 255, 255, .14);
            border: 1px solid rgba(255, 255, 255, .3);
            color: #fff;
            padding: .5rem .9rem;
            border-radius: 10px;
            font-size: .78rem;
            font-weight: 600;
            text-decoration: none;
            transition: background .15s ease;
        }

        .ags-back:hover {
            background: rgba(255, 255, 255, .24);
            color: #fff;
        }

        .ags-layout {
            display: grid;
            grid-template-columns: 280px 1fr;
            gap: 1.1rem;
            align-items: start;
        }

        @media (max-width: 860px) {
            .ags-layout {
                grid-template-columns: 1fr;
            }
        }

        .ags-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 16px;
            box-shadow: 0 2px 14px rgba(27, 29, 40, .04);
        }

        .ags-card-header {
            padding: 1rem 1.15rem .8rem;
            border-bottom: 1px solid var(--border);
            font-size: .8rem;
            font-weight: 700;
            color: var(--ink);
            display: flex;
            align-items: center;
            gap: .5rem;
        }

        .ags-class-list {
            max-height: 62vh;
            overflow-y: auto;
            padding: .5rem;
        }

        .ags-class-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .5rem;
            padding: .65rem .8rem;
            border-radius: 10px;
            cursor: pointer;
            font-size: .82rem;
            font-weight: 600;
            color: var(--ink);
            margin-bottom: .25rem;
            transition: background .15s ease;
        }

        .ags-class-item:hover {
            background: var(--primary-soft);
        }

        .ags-class-item.active {
            background: var(--primary);
            color: #fff;
        }

        .ags-class-item .ags-stream-name {
            font-weight: 500;
            opacity: .75;
            font-size: .74rem;
        }

        .ags-class-item.active .ags-stream-name {
            opacity: .85;
        }

        .ags-empty-state {
            padding: 3rem 1.5rem;
            text-align: center;
            color: var(--muted);
        }

        .ags-empty-state i {
            font-size: 2.2rem;
            color: var(--border);
            margin-bottom: .8rem;
            display: block;
        }

        .ags-panel-body {
            padding: 1.1rem 1.15rem 1.4rem;
        }

        .ags-info-banner {
            display: flex;
            gap: .6rem;
            align-items: flex-start;
            background: var(--primary-soft);
            border: 1px solid #dcdafa;
            border-radius: 12px;
            padding: .75rem .9rem;
            font-size: .76rem;
            color: #423f9e;
            margin-bottom: 1rem;
        }

        .ags-info-banner i {
            margin-top: .1rem;
        }

        .ags-subject-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .6rem;
            padding: .7rem .3rem;
            border-bottom: 1px solid var(--border);
        }

        .ags-subject-row:last-child {
            border-bottom: none;
        }

        .ags-subject-name {
            font-size: .84rem;
            font-weight: 600;
        }

        .ags-switch {
            position: relative;
            display: inline-block;
            width: 42px;
            height: 24px;
            flex-shrink: 0;
        }

        .ags-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .ags-switch-slider {
            position: absolute;
            inset: 0;
            background: #d9dbe4;
            border-radius: 999px;
            cursor: pointer;
            transition: background .15s ease;
        }

        .ags-switch-slider::before {
            content: "";
            position: absolute;
            width: 18px;
            height: 18px;
            left: 3px;
            top: 3px;
            background: #fff;
            border-radius: 50%;
            transition: transform .15s ease;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .25);
        }

        .ags-switch input:checked+.ags-switch-slider {
            background: var(--primary);
        }

        .ags-switch input:checked+.ags-switch-slider::before {
            transform: translateX(18px);
        }

        .ags-summary-strip {
            display: flex;
            gap: .6rem;
            flex-wrap: wrap;
            margin: 1rem 0 .3rem;
            font-size: .74rem;
        }

        .ags-summary-chip {
            background: var(--gray-bg, #F1F2F5);
            border-radius: 999px;
            padding: .35rem .75rem;
            font-weight: 600;
            color: var(--muted);
        }

        .ags-summary-chip strong {
            color: var(--ink);
        }

        .ags-save-row {
            display: flex;
            justify-content: flex-end;
            margin-top: 1rem;
        }

        .btn-ags-save {
            background: linear-gradient(135deg, #1e1b4b, #2f2ccb);
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: .6rem 1.3rem;
            font-size: .82rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: .5rem;
        }

        .btn-ags-save:hover {
            color: #fff;
            opacity: .92;
        }

        .ags-loading, .ags-select-prompt {
            padding: 3rem 1.5rem;
            text-align: center;
            color: var(--muted);
            font-size: .84rem;
        }
    </style>
@endsection

@section('content')
    <div class="side-app">
        <div class="ags-app">

            <div class="ags-topbar">
                <div class="ags-topbar-particles"></div>
                <div class="ags-topbar-inner">
                    <div class="ags-topbar-left">
                        <div class="ags-topbar-icon"><i class="fas fa-calculator"></i></div>
                        <div>
                            <div class="ags-topbar-badge"><i class="fas fa-layer-group"></i> Grading Management</div>
                            <h3 class="ags-topbar-title">Aggregate Subjects</h3>
                            <p class="ags-topbar-subtitle">
                                Choose which subjects count toward the PLE-style Aggregate/Division on each
                                class's pass slips — e.g. English, Mathematics, Science and Social Studies —
                                without changing how any other subject is graded or displayed.
                            </p>
                        </div>
                    </div>
                    <a href="{{ route('examination.grading-schemes.index') }}" class="ags-back">
                        <i class="fas fa-sort-amount-up"></i>
                        <span>Grading Scales</span>
                    </a>
                </div>
            </div>

            <div class="ags-layout">
                <div class="ags-card">
                    <div class="ags-card-header">
                        <i class="fas fa-school"></i> Classes
                    </div>
                    @if($classes->isEmpty())
                        <div class="ags-empty-state">
                            <i class="fas fa-inbox"></i>
                            No classes with streams set up yet.
                        </div>
                    @else
                        <div class="ags-class-list" id="agsClassList">
                            @foreach($classes as $c)
                                <div class="ags-class-item" data-class-id="{{ $c['class_id'] }}"
                                    data-stream-id="{{ $c['stream_id'] }}" data-class-name="{{ $c['class_name'] }}"
                                    onclick="selectClass(this)">
                                    <span>{{ $c['class_name'] }}</span>
                                    <span class="ags-stream-name">{{ $c['stream_name'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="ags-card">
                    <div class="ags-card-header">
                        <i class="fas fa-list-check"></i>
                        <span id="agsPanelTitle">Select a class</span>
                    </div>
                    <div class="ags-panel-body" id="agsPanelBody">
                        <div class="ags-select-prompt">
                            <i class="fas fa-hand-pointer" style="font-size:1.6rem;color:var(--border);display:block;margin-bottom:.6rem;"></i>
                            Pick a class on the left to see its subjects.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        const SUBJECTS_URL_BASE = '{{ url('examinations/aggregate-subjects') }}';
        const CSRF_TOKEN = '{{ csrf_token() }}';
        let currentClassId = null;
        let currentStreamId = null;

        function selectClass(el) {
            document.querySelectorAll('.ags-class-item').forEach(x => x.classList.remove('active'));
            el.classList.add('active');

            currentClassId = el.dataset.classId;
            currentStreamId = el.dataset.streamId;
            const className = el.dataset.className;

            document.getElementById('agsPanelTitle').textContent = className + ' — Aggregate Subjects';

            const body = document.getElementById('agsPanelBody');
            body.innerHTML = '<div class="ags-loading"><i class="fas fa-spinner fa-spin"></i> Loading subjects…</div>';

            fetch(`${SUBJECTS_URL_BASE}/${currentClassId}/${currentStreamId}/subjects`, {
                headers: { 'Accept': 'application/json' },
            })
                .then(r => r.json())
                .then(res => renderSubjects(res.subjects || []))
                .catch(() => {
                    body.innerHTML = '<div class="ags-loading">Couldn\'t load subjects. Please try again.</div>';
                });
        }

        function renderSubjects(subjects) {
            const body = document.getElementById('agsPanelBody');

            if (subjects.length === 0) {
                body.innerHTML = `
                    <div class="ags-empty-state">
                        <i class="fas fa-book-open"></i>
                        This class has no subjects assigned yet. Add subjects from
                        Classes &amp; Streams first, then come back here.
                    </div>`;
                return;
            }

            const rows = subjects.map(s => `
                <div class="ags-subject-row">
                    <span class="ags-subject-name">${s.name}</span>
                    <label class="ags-switch">
                        <input type="checkbox" class="ags-subject-cb" data-subject-id="${s.id}" ${s.counts_towards_aggregate ? 'checked' : ''} onchange="updateSummary()">
                        <span class="ags-switch-slider"></span>
                    </label>
                </div>
            `).join('');

            body.innerHTML = `
                <div class="ags-info-banner">
                    <i class="fas fa-circle-info"></i>
                    <div>
                        Only subjects switched on here are summed into the Aggregate number
                        (and the Division it maps to). Everything else stays graded and
                        visible on the pass slip exactly as before.
                    </div>
                </div>
                <div id="agsSubjectRows">${rows}</div>
                <div class="ags-summary-strip" id="agsSummaryStrip"></div>
                <div class="ags-save-row">
                    <button type="button" class="btn-ags-save" onclick="saveAggregateSubjects()">
                        <i class="fas fa-save"></i> Save
                    </button>
                </div>
            `;

            updateSummary();
        }

        function updateSummary() {
            const checked = document.querySelectorAll('.ags-subject-cb:checked');
            const strip = document.getElementById('agsSummaryStrip');
            if (!strip) return;

            if (checked.length === 0) {
                strip.innerHTML = `<span class="ags-summary-chip">No subjects selected — Aggregate/Division won't appear on this class's slips.</span>`;
                return;
            }

            const minAgg = checked.length;
            const maxAgg = checked.length * 9;
            strip.innerHTML = `
                <span class="ags-summary-chip"><strong>${checked.length}</strong> subject(s) selected</span>
                <span class="ags-summary-chip">Aggregate range: <strong>${minAgg}-${maxAgg}</strong> (on a 1-9 point scale)</span>
            `;
        }

        function saveAggregateSubjects() {
            const ids = Array.from(document.querySelectorAll('.ags-subject-cb:checked'))
                .map(cb => parseInt(cb.dataset.subjectId, 10));

            fetch(`${SUBJECTS_URL_BASE}/${currentClassId}/${currentStreamId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ subject_ids: ids }),
            })
                .then(r => r.json())
                .then(res => {
                    if (window.Swal) {
                        Swal.fire({
                            title: res.success ? 'Saved!' : 'Failed',
                            text: res.message || (res.success ? 'Aggregate subjects updated.' : 'Something went wrong.'),
                            icon: res.success ? 'success' : 'error',
                            timer: res.success ? 1800 : undefined,
                            showConfirmButton: !res.success,
                        });
                    }
                })
                .catch(() => {
                    if (window.Swal) {
                        Swal.fire({ title: 'Failed to save', text: 'Please check your connection and try again.', icon: 'error' });
                    }
                });
        }
    </script>
@endsection
