<?php
use App\Http\Controllers\Helper;
?>
{{-- resources/views/Examination/passslips/customize.blade.php
     "Customize this design" — reached from the Design Template gallery
     on the pass slips index. Shows ONLY the toggles that actually affect
     the chosen template (Helper::passslipTogglesForTemplate), side by
     side with a live iframe preview that starts fully-featured and
     updates as toggles change — no reload needed to SEE the change
     (the iframe's own src is refreshed under the hood), and nothing is
     saved until "Save for selected classes" is clicked. --}}
@extends('layouts-side-bar.master')

@section('css')
    <style>
        :root {
            --brand: #2C29CA;
            --brand-mid: #5351e4;
            --radius-lg: 1.25rem;
            --radius-md: .875rem;
            --shadow-card: 0 4px 24px rgba(44, 41, 202, .10);
        }

        .cz-topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: .75rem;
            margin-bottom: 1rem;
        }

        .cz-back {
            font-size: .82rem;
            color: #64748b;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: .4rem;
        }

        .cz-back:hover {
            color: var(--brand);
        }

        .cz-title {
            font-size: 1.05rem;
            font-weight: 700;
            color: #1e1b4b;
            margin: 0;
        }

        .cz-grid {
            display: grid;
            grid-template-columns: 360px 1fr;
            gap: 1.25rem;
            align-items: start;
        }

        @media (max-width: 992px) {
            .cz-grid {
                grid-template-columns: 1fr;
            }
        }

        .cz-panel {
            background: #fff;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-card);
            padding: 1rem;
            max-height: calc(100vh - 120px);
            overflow-y: auto;
            position: sticky;
            top: 90px;
        }

        @media (max-width: 992px) {
            .cz-panel {
                position: static;
                max-height: none;
            }
        }

        .cz-preview-wrap {
            background: #fff;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-card);
            padding: .75rem;
            position: sticky;
            top: 90px;
        }

        @media (max-width: 992px) {
            .cz-preview-wrap {
                position: static;
            }
        }

        .cz-preview-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: .5rem;
            padding: 0 .25rem .6rem;
        }

        .cz-preview-head select {
            font-size: .75rem;
            border-radius: .5rem;
            border: 1.5px solid #e2e8f0;
            padding: .3rem .6rem;
        }

        .cz-live-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #10b981;
            display: inline-block;
            margin-right: .35rem;
            animation: cz-pulse 1.6s infinite;
        }

        @keyframes cz-pulse {
            0% { box-shadow: 0 0 0 0 rgba(16,185,129,.5); }
            70% { box-shadow: 0 0 0 6px rgba(16,185,129,0); }
            100% { box-shadow: 0 0 0 0 rgba(16,185,129,0); }
        }

        .cz-iframe-shell {
            border-radius: var(--radius-md);
            overflow: hidden;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            position: relative;
        }

        .cz-iframe-shell iframe {
            width: 100%;
            height: calc(100vh - 210px);
            min-height: 520px;
            border: 0;
            display: block;
            background: #fff;
        }

        .cz-loading {
            position: absolute;
            inset: 0;
            display: none;
            align-items: center;
            justify-content: center;
            background: rgba(255,255,255,.6);
            font-size: .8rem;
            color: #64748b;
        }

        .cz-loading.show { display: flex; }

        .cz-group-label {
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: #94a3b8;
            margin: 1rem 0 .4rem;
            padding: 0 .25rem;
        }

        .cz-group-label:first-of-type { margin-top: .25rem; }

        .cz-check-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: .4rem .25rem;
            font-size: .8rem;
            color: #334155;
        }

        .cz-switch {
            position: relative;
            display: inline-block;
            width: 38px;
            height: 21px;
            flex-shrink: 0;
        }

        .cz-switch input { opacity: 0; width: 0; height: 0; }

        .cz-switch-slider {
            position: absolute;
            cursor: pointer;
            inset: 0;
            background: #cbd5e1;
            border-radius: 999px;
            transition: .18s;
        }

        .cz-switch-slider::before {
            content: '';
            position: absolute;
            height: 15px;
            width: 15px;
            left: 3px;
            top: 3px;
            background: #fff;
            border-radius: 50%;
            transition: .18s;
        }

        .cz-switch input:checked + .cz-switch-slider { background: var(--brand); }
        .cz-switch input:checked + .cz-switch-slider::before { transform: translateX(17px); }

        .cz-tpl-mini {
            display: flex;
            gap: .5rem;
            margin-bottom: .5rem;
        }

        .cz-tpl-mini-card {
            flex: 1;
            border: 2px solid #e2e8f0;
            border-radius: .6rem;
            padding: .4rem;
            text-align: center;
            font-size: .68rem;
            font-weight: 600;
            color: #64748b;
            cursor: pointer;
            transition: all .15s;
        }

        .cz-tpl-mini-card:hover { border-color: var(--brand-mid); color: var(--brand); }

        .cz-tpl-mini-card.selected {
            border-color: var(--brand);
            background: #ede9ff;
            color: var(--brand);
        }

        .cz-color-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: .3rem .25rem;
        }

        .cz-color-swatch input[type="color"] {
            width: 34px;
            height: 34px;
            border: none;
            border-radius: 50%;
            cursor: pointer;
            padding: 0;
        }

        .cz-presets {
            display: flex;
            flex-wrap: wrap;
            gap: .4rem;
            padding: .4rem .25rem .2rem;
        }

        .cz-preset-dot {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            cursor: pointer;
            border: 2px solid transparent;
        }

        .cz-preset-dot.active { border-color: #1e1b4b; }

        .cz-class-chip {
            padding: .4rem .8rem;
            border-radius: 20px;
            font-size: .75rem;
            font-weight: 600;
            cursor: pointer;
            background: #fff;
            border: 2px solid #e2e8f0;
            color: #475569;
            user-select: none;
        }

        .cz-class-chip.selected {
            border-color: var(--brand);
            background: #ede9ff;
            color: var(--brand);
        }

        .cz-btn-primary {
            background: linear-gradient(135deg, #1e1b4b, #2f2ccb);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            font-size: .8rem;
            padding: .55rem 1rem;
        }

        .cz-btn-outline {
            background: #fff;
            color: #2f2ccb;
            border: 1.5px solid #2f2ccb;
            border-radius: 10px;
            font-weight: 600;
            font-size: .78rem;
            padding: .45rem 1rem;
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid" style="padding: 1.25rem 1.5rem;">

        <div class="cz-topbar">
            <a href="{{ route('examination.passslips.index', $exam->id) }}" class="cz-back">
                <i class="fas fa-arrow-left"></i> Back to Pass Slips
            </a>
            <h1 class="cz-title">
                Customize this design — {{ $exam->exam_name }} ({{ $exam->term }})
            </h1>
            <span></span>
        </div>

        <div class="cz-grid">

            {{-- ══════════ LEFT: filtered customise panel ══════════ --}}
            <div class="cz-panel" id="czPanel">

                <div class="cz-group-label" style="margin-top:0;"><i class="fas fa-swatchbook"></i> Design Template</div>
                <div class="cz-tpl-mini" id="czTplMini">
                    @foreach (['classic' => 'Classic', 'modern' => 'Modern', 'minimal' => 'Minimal'] as $key => $label)
                        <div class="cz-tpl-mini-card {{ $template === $key ? 'selected' : '' }}" data-template="{{ $key }}">
                            {{ $label }}
                        </div>
                    @endforeach
                </div>
                <div class="small text-muted" style="font-size:.68rem;padding:0 .25rem .6rem;">
                    Switching design reloads the panel with only the toggles that
                    design actually supports — your current choices carry over
                    wherever they still apply.
                </div>

                {{-- Accent colour — applies in every scenario of every
                     design template, so it's never gated behind a
                     capability check. --}}
                <div class="cz-group-label"><i class="fas fa-palette"></i> Accent Colour</div>
                <div class="cz-color-row">
                    <label for="czColorPicker">Accent colour</label>
                    <div class="cz-color-swatch">
                        <input type="color" id="czColorPicker" value="#f0a500">
                    </div>
                </div>
                <div class="cz-presets" id="czPresets">
                    @foreach ([
                        ['#f0a500', 'Amber (default)'], ['#c0392b', 'Ruby Red'], ['#2C29CA', 'Brand Blue'],
                        ['#10b981', 'Emerald'], ['#7c3aed', 'Violet'], ['#0f172a', 'Midnight'],
                        ['#e11d48', 'Rose'], ['#0ea5e9', 'Sky'], ['#15803d', 'Forest Green'],
                        ['#dc2626', 'Crimson'], ['#4338ca', 'Indigo'], ['#374151', 'Slate Gray'],
                    ] as [$hex, $label])
                        <div class="cz-preset-dot {{ $hex === '#f0a500' ? 'active' : '' }}"
                             style="background:{{ $hex }};" title="{{ $label }}" data-color="{{ $hex }}"></div>
                    @endforeach
                </div>

                {{-- ── Dynamically-filtered toggle groups ──
                     Only the sections/keys this template actually supports
                     are rendered here at all — this is the fix for the
                     "toggle does nothing" problem. --}}
                @foreach ($toggleGroups as $groupLabel => $toggles)
                    <div class="cz-group-label">{{ $groupLabel }}</div>
                    @foreach ($toggles as $key => $meta)
                        <div class="cz-check-row">
                            <label for="cb_{{ $key }}"><i class="fas {{ $meta['icon'] }}"></i> {{ $meta['label'] }}</label>
                            <label class="cz-switch">
                                <input type="checkbox" id="cb_{{ $key }}" class="cz-toggle-cb" data-key="{{ $key }}" checked>
                                <span class="cz-switch-slider"></span>
                            </label>
                        </div>
                    @endforeach
                @endforeach

                @if (isset($siblingExams) && $siblingExams->count() > 0)
                    <div class="cz-group-label"><i class="fas fa-layer-group"></i> Combine Examinations</div>
                    @foreach ($siblingExams as $se)
                        <div class="cz-check-row">
                            <label for="cz_exam_{{ $se->id }}">
                                <input type="checkbox" id="cz_exam_{{ $se->id }}" class="cz-exam-combine-cb" value="{{ $se->id }}" style="margin-right:.4rem;">
                                {{ $se->exam_name }} ({{ $se->term }})
                            </label>
                            <label class="cz-switch" title="Include in average">
                                <input type="checkbox" id="cz_avg_{{ $se->id }}" class="cz-exam-avg-cb" value="{{ $se->id }}" disabled>
                                <span class="cz-switch-slider"></span>
                            </label>
                        </div>
                    @endforeach
                @endif

                <div class="cz-group-label"><i class="fas fa-save"></i> Apply &amp; Save</div>
                <div class="small text-muted mb-2" style="font-size:.72rem;">
                    Pick which class(es) this customised design belongs to, then save.
                    It'll be applied automatically every time their pass slips are printed.
                </div>

                <div id="czClassSelector" style="display:flex;flex-wrap:wrap;gap:.5rem;padding:.75rem;background:#f8fafc;border-radius:12px;border:2px solid #e2e8f0;min-height:52px;margin-bottom:.6rem;">
                    @foreach ($examClasses->unique('class_id') as $ec)
                        <div class="cz-class-chip" data-class-id="{{ $ec->class_id }}">
                            {{ Helper::recordMdname($ec->class_id) }}
                        </div>
                    @endforeach
                </div>

                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.6rem;">
                    <span style="font-size:.72rem;color:#94a3b8;">
                        <span id="czSelectedCount">0</span> class(es) selected
                    </span>
                    <div style="display:flex;gap:.4rem;">
                        <button type="button" class="cz-btn-outline" id="czSelectAll" style="padding:.25rem .6rem;font-size:.68rem;">All</button>
                        <button type="button" class="cz-btn-outline" id="czSelectNone" style="padding:.25rem .6rem;font-size:.68rem;">None</button>
                    </div>
                </div>

                <button type="button" class="cz-btn-primary w-100" id="czSaveBtn">
                    <i class="fas fa-save me-1"></i> Save for selected classes
                </button>
                <div id="czSaveStatus" style="font-size:.72rem;margin-top:.5rem;min-height:1em;"></div>

            </div>

            {{-- ══════════ RIGHT: live preview ══════════ --}}
            <div class="cz-preview-wrap">
                <div class="cz-preview-head">
                    <div style="font-size:.8rem;color:#334155;font-weight:600;">
                        <span class="cz-live-dot"></span> Live preview
                    </div>
                    <select id="czPreviewClass">
                        <option value="">Preview with: any available student</option>
                        @foreach ($examClasses->unique('class_id') as $ec)
                            <option value="{{ $ec->class_id }}|{{ $ec->stream_id }}">
                                Preview with: {{ Helper::recordMdname($ec->class_id) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="cz-iframe-shell">
                    <div class="cz-loading" id="czLoading">Updating preview…</div>
                    <iframe id="czPreviewFrame" title="Live pass slip preview"></iframe>
                </div>
            </div>
        </div>
    </div>
    </div>
        </div>
    </div>

    <script>
        const EXAM_ID = {{ $exam->id }};
        const PREVIEW_URL = '{{ route('examination.passslips.preview', $exam->id) }}';
        const SAVE_URL = '{{ route('examination.passslips.settings.save', $exam->id) }}';
        const CSRF_TOKEN = '{{ csrf_token() }}';

        let refreshTimer = null;
        const selectedClassIds = new Set();

        function currentTemplate() {
            const sel = document.querySelector('.cz-tpl-mini-card.selected');
            return sel ? sel.dataset.template : 'classic';
        }

        /* ── Build the settings object every toggle/colour currently
           reflects — used both for the live preview query string and
           for the Save payload, so the two are always in sync. ── */
        function collectSettings() {
            const settings = { template: currentTemplate(), accent: document.getElementById('czColorPicker').value };
            document.querySelectorAll('.cz-toggle-cb').forEach(cb => {
                settings[cb.dataset.key] = cb.checked;
            });
            const extraExamIds = Array.from(document.querySelectorAll('.cz-exam-combine-cb:checked')).map(cb => cb.value);
            const avgExamIds = Array.from(document.querySelectorAll('.cz-exam-avg-cb:checked')).map(cb => cb.value);
            if (extraExamIds.length) settings.exam_ids = extraExamIds.join(',');
            if (avgExamIds.length) settings.avg_exam_ids = avgExamIds.join(',');
            return settings;
        }

        function buildPreviewUrl() {
            const settings = collectSettings();
            const p = new URLSearchParams();
            Object.entries(settings).forEach(([k, v]) => {
                p.set(k, typeof v === 'boolean' ? (v ? '1' : '0') : v);
            });
            const previewClass = document.getElementById('czPreviewClass').value;
            if (previewClass) {
                const [classId, streamId] = previewClass.split('|');
                p.set('class_id', classId);
                if (streamId) p.set('stream_id', streamId);
            }
            return PREVIEW_URL + '?' + p.toString();
        }

        function refreshPreviewNow() {
            const loading = document.getElementById('czLoading');
            loading.classList.add('show');
            const frame = document.getElementById('czPreviewFrame');
            frame.src = buildPreviewUrl();
            frame.onload = () => loading.classList.remove('show');
        }

        function scheduleRefresh() {
            clearTimeout(refreshTimer);
            refreshTimer = setTimeout(refreshPreviewNow, 250);
        }

        /* ── Toggle + colour wiring ── */
        document.querySelectorAll('.cz-toggle-cb').forEach(cb => cb.addEventListener('change', scheduleRefresh));
        document.querySelectorAll('.cz-exam-combine-cb').forEach(cb => cb.addEventListener('change', function () {
            const avgCb = document.getElementById('cz_avg_' + this.value);
            if (avgCb) {
                avgCb.disabled = !this.checked;
                avgCb.checked = this.checked;
            }
            scheduleRefresh();
        }));
        document.querySelectorAll('.cz-exam-avg-cb').forEach(cb => cb.addEventListener('change', scheduleRefresh));

        document.getElementById('czColorPicker').addEventListener('input', function () {
            document.querySelectorAll('.cz-preset-dot').forEach(d => d.classList.toggle('active', d.dataset.color === this.value));
            scheduleRefresh();
        });
        document.querySelectorAll('.cz-preset-dot').forEach(dot => dot.addEventListener('click', function () {
            document.getElementById('czColorPicker').value = this.dataset.color;
            document.querySelectorAll('.cz-preset-dot').forEach(d => d.classList.toggle('active', d === this));
            scheduleRefresh();
        }));

        document.getElementById('czPreviewClass').addEventListener('change', scheduleRefresh);

        /* ── Template switch: full reload, carrying current toggle
           values across as query params so nothing already set is lost —
           only the panel's available toggles change. ── */
        document.querySelectorAll('.cz-tpl-mini-card').forEach(card => card.addEventListener('click', function () {
            if (this.classList.contains('selected')) return;
            const settings = collectSettings();
            settings.template = this.dataset.template;
            const p = new URLSearchParams();
            Object.entries(settings).forEach(([k, v]) => {
                p.set(k, typeof v === 'boolean' ? (v ? '1' : '0') : v);
            });
            window.location.href = '{{ route('examination.passslips.customize', $exam->id) }}?' + p.toString();
        }));

        /* ── Class chip selection (which classes to save this profile for) ── */
        function updateSelectedCount() {
            document.getElementById('czSelectedCount').textContent = selectedClassIds.size;
        }
        document.querySelectorAll('.cz-class-chip').forEach(chip => chip.addEventListener('click', function () {
            const id = this.dataset.classId;
            if (selectedClassIds.has(id)) {
                selectedClassIds.delete(id);
                this.classList.remove('selected');
            } else {
                selectedClassIds.add(id);
                this.classList.add('selected');
            }
            updateSelectedCount();
        }));
        document.getElementById('czSelectAll').addEventListener('click', function () {
            document.querySelectorAll('.cz-class-chip').forEach(chip => {
                selectedClassIds.add(chip.dataset.classId);
                chip.classList.add('selected');
            });
            updateSelectedCount();
        });
        document.getElementById('czSelectNone').addEventListener('click', function () {
            selectedClassIds.clear();
            document.querySelectorAll('.cz-class-chip').forEach(chip => chip.classList.remove('selected'));
            updateSelectedCount();
        });

        /* ── Save ── */
        document.getElementById('czSaveBtn').addEventListener('click', function () {
            const statusEl = document.getElementById('czSaveStatus');
            if (selectedClassIds.size === 0) {
                statusEl.style.color = '#c0392b';
                statusEl.textContent = 'Select at least one class first.';
                return;
            }
            statusEl.style.color = '#666';
            statusEl.textContent = 'Saving…';

            fetch(SAVE_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    class_ids: Array.from(selectedClassIds).map(id => parseInt(id, 10)),
                    settings: collectSettings(),
                }),
            })
                .then(r => r.json())
                .then(res => {
                    statusEl.style.color = res.success ? '#1a7a4a' : '#c0392b';
                    statusEl.textContent = res.success
                        ? 'Saved for ' + selectedClassIds.size + ' class(es). ✓'
                        : (res.message || 'Failed to save.');
                })
                .catch(() => {
                    statusEl.style.color = '#c0392b';
                    statusEl.textContent = 'Failed to save — check your connection.';
                });
        });

        // Initial paint: the slip comes up fully-featured (every toggle
        // starts checked in the HTML above) before any customisation.
        refreshPreviewNow();
    </script>
@endsection