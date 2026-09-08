<?php
use App\Http\Controllers\Helper;
?>
{{-- resources/views/Examination/passslips/customize.blade.php
"Customize this design" — reached from the Design Template gallery
on the pass slips index. Shows ONLY the toggles that actually affect
the chosen template (Helper::passslipTogglesForTemplate), side by
side with a live iframe preview that starts fully-featured — except
Minimal (Performance Summary / Discipline / Signatures start off so
the slip fits one A4 page by default) and Classic (Signatures starts
off) — see $offByDefaultKeys below — and updates as toggles change —
no reload needed to SEE the change (the iframe's own src is
refreshed under the hood), and nothing is saved until "Save for
selected classes" is clicked. --}}
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
            0% {
                box-shadow: 0 0 0 0 rgba(16, 185, 129, .5);
            }

            70% {
                box-shadow: 0 0 0 6px rgba(16, 185, 129, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(16, 185, 129, 0);
            }
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
            background: rgba(255, 255, 255, .6);
            font-size: .8rem;
            color: #64748b;
        }

        .cz-loading.show {
            display: flex;
        }

        .cz-group-label {
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: #94a3b8;
            margin: 1rem 0 .4rem;
            padding: 0 .25rem;
        }

        .cz-group-label:first-of-type {
            margin-top: .25rem;
        }

        /* ── Toggle search bar ── */
        .cz-search-wrap {
            position: relative;
            margin: .35rem .25rem .95rem;
        }

        .cz-search-icon {
            position: absolute;
            left: 13px;
            top: 50%;
            transform: translateY(-50%);
            font-size: .78rem;
            color: #94a3b8;
            pointer-events: none;
        }

        .cz-search-input {
            width: 100%;
            padding: .6rem .85rem .6rem 2.2rem;
            font-size: .8rem;
            color: #1e1b4b;
            background: #f8fafc;
            border: 1.5px solid #e2e8f0;
            border-radius: .7rem;
            outline: none;
            transition: border-color .15s ease, box-shadow .15s ease, background-color .15s ease;
        }

        .cz-search-input::placeholder {
            color: #94a3b8;
        }

        .cz-search-input:focus {
            background: #fff;
            border-color: var(--brand-mid);
            box-shadow: 0 0 0 3px rgba(44, 41, 202, .12);
        }

        .cz-search-clear {
            position: absolute;
            right: 7px;
            top: 50%;
            transform: translateY(-50%);
            width: 20px;
            height: 20px;
            border: none;
            background: #e2e8f0;
            color: #64748b;
            border-radius: 50%;
            font-size: .6rem;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            padding: 0;
            transition: background-color .15s ease, color .15s ease;
        }

        .cz-search-clear:hover {
            background: #cbd5e1;
            color: #334155;
        }

        .cz-toggle-group[hidden] {
            display: none;
        }

        .cz-no-results {
            display: none;
            align-items: center;
            justify-content: center;
            gap: .45rem;
            padding: 1.1rem .5rem;
            font-size: .78rem;
            color: #94a3b8;
            text-align: center;
        }

        .cz-no-results.show {
            display: flex;
        }

        .cz-no-results i {
            font-size: .85rem;
        }

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

        .cz-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

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

        .cz-switch input:checked+.cz-switch-slider {
            background: var(--brand);
        }

        .cz-switch input:checked+.cz-switch-slider::before {
            transform: translateX(17px);
        }

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

        .cz-tpl-mini-card:hover {
            border-color: var(--brand-mid);
            color: var(--brand);
        }

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

        .cz-preset-dot.active {
            border-color: #1e1b4b;
        }

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

        /* Small dot on a class chip that already has a saved profile —
           same treatment as the index page's picker, so it's visible at
           a glance which classes will actually load something. */
        .cz-class-chip.has-saved::after {
            content: "";
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #16a34a;
            display: inline-block;
            margin-left: .3rem;
        }

        .cz-class-chip.selected.has-saved::after {
            background: var(--brand);
        }

        /* ── Check All / Uncheck All row ── */
        .cz-check-all-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: .4rem .25rem .7rem;
            font-size: .75rem;
            font-weight: 600;
            color: #475569;
        }

        .cz-check-all-btns {
            display: flex;
            gap: .4rem;
        }

        .cz-check-all-btns .cz-btn-sm {
            background: #fff;
            color: #2f2ccb;
            border: 1.5px solid #e2e8f0;
            border-radius: 8px;
            font-size: .68rem;
            font-weight: 600;
            padding: .25rem .6rem;
            cursor: pointer;
            transition: border-color .15s ease, color .15s ease;
        }

        .cz-check-all-btns .cz-btn-sm:hover {
            border-color: var(--brand-mid);
            color: var(--brand);
        }

        /* ── Saved Customisations — tab strip ──
           One tab per class that already has a saved profile for this
           exam. Mirrors the tab strip that used to live on the pass
           slips index page, now that saving/loading/removing profiles
           happens exclusively here. */
        .cz-saved-tab {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            padding: .35rem .5rem .35rem .8rem;
            border-radius: 999px;
            font-size: .72rem;
            font-weight: 600;
            cursor: pointer;
            background: #eef2ff;
            border: 1.5px solid #c7d2fe;
            color: #3730a3;
            transition: all .15s ease;
            user-select: none;
        }

        .cz-saved-tab:hover {
            border-color: var(--brand-mid);
        }

        .cz-saved-tab.active {
            background: linear-gradient(135deg, #1e1b4b, #2f2ccb);
            border-color: var(--brand);
            color: #fff;
            box-shadow: 0 3px 10px rgba(47, 44, 203, .25);
        }

        .cz-saved-tab .cz-saved-tab-remove {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            font-size: .6rem;
            opacity: .65;
        }

        .cz-saved-tab .cz-saved-tab-remove:hover {
            opacity: 1;
            background: rgba(0, 0, 0, .12);
        }

        .cz-saved-tab.active .cz-saved-tab-remove:hover {
            background: rgba(255, 255, 255, .25);
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

        #czPreviewClass.form-control {
            background-color: #f8f9fa;
            border: 2px solid #2C29CA;
            border-radius: 8px;
            padding: 6px 12px;
            height: auto;
            min-height: 38px;
            font-weight: 500;
            color: #333;
            transition: all 0.3s ease;
            -webkit-appearance: auto;
            appearance: auto;
            cursor: pointer;
            font-size: 0.8rem;
            line-height: 1.5;
            width: auto;
            max-width: 300px;
            display: inline-block;
        }

        #czPreviewClass.form-control:focus {
            border-color: #0056b3;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
            outline: none;
        }

        #czPreviewClass.form-control option {
            padding: 4px 8px;
            font-size: 0.8rem;
        }

        #czPreviewClass.form-control {
            background-color: #f8f9fa;
            border: 2px solid #2C29CA;
            border-radius: 8px;
            padding: 12px 16px;
            font-weight: 500;
            color: #333;
            transition: all 0.3s ease;
            -webkit-appearance: auto;
            appearance: auto;
            cursor: pointer;
        }

        #czPreviewClass.form-control:focus {
            border-color: #0056b3;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
            outline: none;
        }

        #czPreviewClass.form-control option {
            padding: 8px 12px;
        }

        .cz-color-swatch {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    overflow: hidden;
    flex-shrink: 0;
}

.cz-color-swatch input[type="color"] {
    width: 40px;
    height: 40px;
    border: none;
    border-radius: 50%;
    cursor: pointer;
    padding: 0;
    display: block;
    margin: -3px 0 0 -3px; /* Center the larger picker inside the container */
    background: none;
    border: none;
    outline: none;
}

/* Hide the default color picker's square appearance in some browsers */
.cz-color-swatch input[type="color"]::-webkit-color-swatch-wrapper {
    padding: 0;
}

.cz-color-swatch input[type="color"]::-webkit-color-swatch {
    border: none;
    border-radius: 50%;
}

.cz-color-swatch input[type="color"]::-moz-color-swatch {
    border: none;
    border-radius: 50%;
}
    </style>
@endsection

@section('content')
    <div class="container-fluid" style="padding: 1.25rem 1.5rem;">

        <div class="cz-topbar">
            <a href="{{ route('examination.passslips.index', $exam->id) }}" class="btn btn-primary cz-back">
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

                <div class="cz-group-label" style="margin-top:0;">
                    <i class="fas fa-swatchbook"></i> {{ $isNurseryTemplate ?? false ? 'Nursery' : 'Primary' }} Design Template
                </div>
                {{-- Only this template's own family (Primary or Nursery) is
                offered here — switching families entirely (e.g. Classic
                Primary → Classic Nursery) isn't a "swap the design" action
                the way switching within a family is, since it also changes
                which classes this page applies to; that's done from the
                pass slips index's two separate galleries instead. --}}
                <div class="cz-tpl-mini" id="czTplMini" data-family="{{ $isNurseryTemplate ?? false ? 'nursery' : 'primary' }}">
                    @php
                        $tplFamilyOptions = ($isNurseryTemplate ?? false)
                            ? ['nursery-classic' => 'Classic', 'nursery-modern' => 'Modern', 'nursery-minimal' => 'Minimal']
                            : ['classic' => 'Classic', 'modern' => 'Modern', 'minimal' => 'Minimal'];
                    @endphp
                    @foreach ($tplFamilyOptions as $key => $label)
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

                                {{-- Saved Customisations — one tab per class that already has a
                     saved profile for this exam. Click a tab to load ONLY that
                     class's settings into the panel above for review/editing;
                     the trash icon removes it. Moved here from the pass slips
                     index page, which no longer has its own toggle/save panel. --}}
                <div id="czSavedTabsWrap" class="mb-2" style="display:none;">
                    <div class="text-muted mb-1" style="font-size:.72rem;font-weight:600;">
                        <i class="fas fa-folder-open me-1"></i> Saved Classes
                    </div>
                    <div id="czSavedTabs" style="display:flex;flex-wrap:wrap;gap:.4rem;"></div>
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
                            ['#f0a500', 'Amber (default)'],
                            ['#c0392b', 'Ruby Red'],
                            ['#2C29CA', 'Brand Blue'],
                            ['#10b981', 'Emerald'],
                            ['#7c3aed', 'Violet'],
                            ['#0f172a', 'Midnight'],
                            ['#e11d48', 'Rose'],
                            ['#0ea5e9', 'Sky'],
                            ['#15803d', 'Forest Green'],
                            ['#dc2626', 'Crimson'],
                            ['#4338ca', 'Indigo'],
                            ['#374151', 'Slate Gray'],
                        ] as [$hex, $label])
                        <div class="cz-preset-dot {{ $hex === '#f0a500' ? 'active' : '' }}" style="background:{{ $hex }};"
                            title="{{ $label }}" data-color="{{ $hex }}"></div>
                    @endforeach
                </div>

                {{-- ── Dynamically-filtered toggle groups ──
                Only the sections/keys this template actually supports
                are rendered here at all — this is the fix for the
                "toggle does nothing" problem. --}}
                @php
                    // Minimal defaults these three OFF (see slip-minimal.blade.php)
                    // so a fresh/direct link fits on one A4 page; Classic
                    // defaults just Signatures off (see slip-classic.blade.php).
                    // The panel's switches should reflect that instead of
                    // always starting checked, or the preview and the
                    // switches would disagree the moment the page loads.
                    $offByDefaultKeys = match ($template) {
                        'minimal' => ['show_section_summary', 'show_discipline', 'show_signatures'],
                        'classic' => ['show_signatures'],
                        default => [],
                    };
                @endphp
                {{-- Check All / Uncheck All — applies to every toggle this
                template supports (i.e. everything currently rendered in
                the groups below), same "quick select" affordance the
                pass slips index page used to have before its own toggle
                panel was removed in favour of this page. --}}
                <div class="cz-check-all-row">
                    <span><i class="fas fa-check-square me-1"></i> Quick select</span>
                    <div class="cz-check-all-btns">
                        <button type="button" class="cz-btn-sm" id="czCheckAll">Check All</button>
                        <button type="button" class="cz-btn-sm" id="czUncheckAll">Uncheck All</button>
                    </div>
                </div>

                {{-- ── Toggle search ── the option list can run long once
                every section is shown, so let people jump straight to the
                switch they're after instead of scanning by eye. Filters
                by label text only; doesn't touch any checkbox state. --}}
                <div class="cz-search-wrap">
                    <i class="fas fa-search cz-search-icon"></i>
                    <input type="text" id="czToggleSearch" class="cz-search-input" autocomplete="off"
                        placeholder="Search options — e.g. QR code, signatures, logo…">
                    <button type="button" id="czSearchClear" class="cz-search-clear" aria-label="Clear search"
                        style="display:none;">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="cz-no-results" id="czNoResults">
                    <i class="fas fa-circle-info"></i>
                    No options match “<span id="czNoResultsTerm"></span>”
                </div>

                @foreach ($toggleGroups as $groupLabel => $toggles)
                    <div class="cz-toggle-group" data-toggle-group>
                        <div class="cz-group-label">{{ $groupLabel }}</div>
                        @foreach ($toggles as $key => $meta)
                            <div class="cz-check-row" data-toggle-row data-search-text="{{ strtolower($meta['label']) }}">
                                <label for="cb_{{ $key }}"><i class="fas {{ $meta['icon'] }}"></i> {{ $meta['label'] }}</label>
                                <label class="cz-switch">
                                    <input type="checkbox" id="cb_{{ $key }}" class="cz-toggle-cb" data-key="{{ $key }}"
                                        {{ in_array($key, $offByDefaultKeys, true) ? '' : 'checked' }}>
                                    <span class="cz-switch-slider"></span>
                                </label>
                            </div>
                        @endforeach
                    </div>
                @endforeach

                {{-- "Combine Examinations" (BOT | MID | END averaging) is a
Primary-only concept — Nursery report cards don't carry a
numeric average across exams the same way, so this whole
group is skipped entirely for the Nursery family instead
of rendering empty/irrelevant checkboxes. --}}
@if (!($isNurseryTemplate ?? false) && isset($siblingExams) && $siblingExams->count() > 0)
                    <div class="cz-group-label"><i class="fas fa-layer-group"></i> Combine Examinations</div>
                    @foreach ($siblingExams as $se)
                        <div class="cz-check-row">
                            <label for="cz_exam_{{ $se->id }}">
                                <input type="checkbox" id="cz_exam_{{ $se->id }}" class="cz-exam-combine-cb" value="{{ $se->id }}"
                                    style="margin-right:.4rem;">
                                {{ $se->exam_name }} ({{ $se->term }})
                            </label>
                            <label class="cz-switch" title="Include in average">
                                <input type="checkbox" id="cz_avg_{{ $se->id }}" class="cz-exam-avg-cb" value="{{ $se->id }}"
                                    disabled>
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

                {{-- Saved Customisations — one tab per class that already has a
                     saved profile for this exam. Click a tab to load ONLY that
                     class's settings into the panel above for review/editing;
                     the trash icon removes it. Moved here from the pass slips
                     index page, which no longer has its own toggle/save panel. --}}
                <div id="czSavedTabsWrap" class="mb-2" style="display:none;">
                    <div class="text-muted mb-1" style="font-size:.72rem;font-weight:600;">
                        <i class="fas fa-folder-open me-1"></i> Saved customisations
                    </div>
                    <div id="czSavedTabs" style="display:flex;flex-wrap:wrap;gap:.4rem;"></div>
                </div>

                <div id="czClassSelector"
                    style="display:flex;flex-wrap:wrap;gap:.5rem;padding:.75rem;background:#f8fafc;border-radius:12px;border:2px solid #e2e8f0;min-height:52px;margin-bottom:.6rem;">
                    @forelse ($examClasses->unique('class_id') as $ec)
    <div class="cz-class-chip" data-class-id="{{ $ec->class_id }}">
        {{ Helper::recordMdname($ec->class_id) }}
    </div>
@empty
    <div style="font-size:.72rem;color:#94a3b8;">
        @if ($isNurseryTemplate ?? false)
            No Nursery classes (Baby / Middle / Top Class) are attached to
            this examination yet — add them to this exam first.
        @else
            No classes are attached to this examination yet.
        @endif
    </div>
@endforelse
                </div>

                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.6rem;">
                    <span style="font-size:.72rem;color:#94a3b8;">
                        <span id="czSelectedCount">0</span> class(es) selected
                    </span>
                    <div style="display:flex;gap:.4rem;">
                        <button type="button" class="cz-btn-outline" id="czSelectAll"
                            style="padding:.25rem .6rem;font-size:.68rem;">All</button>
                        <button type="button" class="cz-btn-outline" id="czSelectNone"
                            style="padding:.25rem .6rem;font-size:.68rem;">None</button>
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

                    <select id="czPreviewClass" class="form-control">
    @forelse ($examClasses->unique('class_id') as $ec)
        <option value="{{ $ec->class_id }}|{{ $ec->stream_id }}">
            Preview with: {{ Helper::recordMdname($ec->class_id) }}
        </option>
    @empty
        <option value="" disabled selected>No classes available to preview</option>
    @endforelse
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
        const GET_URL = '{{ route('examination.passslips.settings.get', $exam->id) }}';
// "template" tells the backend which family (Primary vs Nursery)
// of classes to list saved customisations for, so a Primary
// class's saved profile never shows up as a tab on the Nursery
// page, and vice versa.
const LIST_URL = '{{ route('examination.passslips.settings.list', $exam->id) }}?template={{ $template }}';
        const DELETE_URL_BASE = '{{ url('examinations/'.$exam->id.'/passslips/settings') }}';
        const CSRF_TOKEN = '{{ csrf_token() }}';
        // Same per-template "off unless saved otherwise" keys the PHP side
        // uses when rendering the actual slip (see $offByDefaultKeys above),
        // mirrored here so a loaded class's MISSING keys fall back to the
        // correct default instead of always assuming "on".
        const OFF_BY_DEFAULT = @json($offByDefaultKeys);

        let refreshTimer = null;
        const selectedClassIds = new Set();
        let savedTabsCache = []; // [{class_id, class_name, settings}] — which classes already have a saved profile

        function currentTemplate() {
            const sel = document.querySelector('.cz-tpl-mini-card.selected');
            if (sel) return sel.dataset.template;
            // Fall back within whichever family this page was opened for,
            // never across to the other family's default.
            const family = document.getElementById('czTplMini')?.dataset.family;
            return family === 'nursery' ? 'nursery-classic' : 'classic';
        }

        function currentPreviewClassId() {
            const val = document.getElementById('czPreviewClass').value;
            return val ? val.split('|')[0] : null;
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

        /* ── Toggle search ── filters the switch rows below by label
           text as the user types. Hides a whole group's header too
           when nothing inside it matches, and shows a "no matches"
           note if the search comes up empty across every group.
           Purely visual — it never touches checkbox state, so a
           filtered-out toggle keeps whatever value it was set to. ── */
        (function () {
            const searchInput = document.getElementById('czToggleSearch');
            const clearBtn = document.getElementById('czSearchClear');
            const noResults = document.getElementById('czNoResults');
            const noResultsTerm = document.getElementById('czNoResultsTerm');
            if (!searchInput) return;

            function applyToggleSearch() {
                const raw = searchInput.value.trim();
                const term = raw.toLowerCase();
                clearBtn.style.display = raw ? 'flex' : 'none';

                let anyGroupVisible = false;
                document.querySelectorAll('[data-toggle-group]').forEach(group => {
                    let groupHasMatch = false;
                    group.querySelectorAll('[data-toggle-row]').forEach(row => {
                        const matches = !term || row.dataset.searchText.includes(term);
                        row.style.display = matches ? '' : 'none';
                        if (matches) groupHasMatch = true;
                    });
                    group.hidden = !groupHasMatch;
                    if (groupHasMatch) anyGroupVisible = true;
                });

                noResultsTerm.textContent = raw;
                noResults.classList.toggle('show', !!term && !anyGroupVisible);
            }

            searchInput.addEventListener('input', applyToggleSearch);
            clearBtn.addEventListener('click', function () {
                searchInput.value = '';
                applyToggleSearch();
                searchInput.focus();
            });
        })();

        /* ── Check All / Uncheck All ── applies to every toggle this
           template currently supports, regardless of the search filter
           above (a filtered-out row is still a real setting). ── */
        document.getElementById('czCheckAll')?.addEventListener('click', function () {
            document.querySelectorAll('.cz-toggle-cb').forEach(cb => { cb.checked = true; });
            scheduleRefresh();
        });
        document.getElementById('czUncheckAll')?.addEventListener('click', function () {
            document.querySelectorAll('.cz-toggle-cb').forEach(cb => { cb.checked = false; });
            scheduleRefresh();
        });

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

        document.getElementById('czPreviewClass').addEventListener('change', function () {
            const classId = currentPreviewClassId();
            if (classId) {
                loadClassCustomisation(classId, true);
            } else {
                scheduleRefresh();
            }
        });

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

        /* ── Fetch/apply a class's saved profile ──────────────────────
           This is the piece the page was missing entirely: it could
           SAVE settings but never loaded them back, so refreshing (or
           just re-opening the page later) always showed defaults again
           even though the save had genuinely gone into passslip_settings
           — indistinguishable, from here, from "the save didn't work". */
        function applySettingsToPanel(saved) {
            if (saved.accent) {
                document.getElementById('czColorPicker').value = saved.accent;
                document.querySelectorAll('.cz-preset-dot').forEach(d => {
                    d.classList.toggle('active', d.dataset.color === saved.accent);
                });
            }
            document.querySelectorAll('.cz-toggle-cb').forEach(cb => {
                const key = cb.dataset.key;
                cb.checked = key in saved ? !!saved[key] : !OFF_BY_DEFAULT.includes(key);
            });
            const extraIds = typeof saved.exam_ids === 'string' ? saved.exam_ids.split(',').filter(Boolean) : [];
            document.querySelectorAll('.cz-exam-combine-cb').forEach(cb => {
                cb.checked = extraIds.includes(cb.value);
                const avgCb = document.getElementById('cz_avg_' + cb.value);
                if (avgCb) avgCb.disabled = !cb.checked;
            });
            if (typeof saved.avg_exam_ids === 'string') {
                const avgIds = saved.avg_exam_ids.split(',').filter(Boolean);
                document.querySelectorAll('.cz-exam-avg-cb').forEach(cb => {
                    if (!cb.disabled) cb.checked = avgIds.includes(cb.value);
                });
            }
            refreshPreviewNow();
        }

        /* pending: true from the initial page-load call and from the
           "Preview with" dropdown, so the iframe still gets its first
           src (or a refresh) even when the class has nothing saved —
           false from a chip click, where doing nothing on a miss and
           leaving the panel as-is is the right call (existing chip
           behaviour, unchanged). */
        function loadClassCustomisation(classId, pending = false) {
            fetch(GET_URL + '?class_id=' + classId, { headers: { 'Accept': 'application/json' } })
                .then(r => r.json())
                .then(res => {
                    const usable = res.success && res.settings && Object.keys(res.settings).length > 0
                        && (!res.settings.template || res.settings.template === currentTemplate());
                    if (usable) {
                        applySettingsToPanel(res.settings);
                    } else if (pending) {
                        refreshPreviewNow();
                    }
                })
                .catch(() => { if (pending) refreshPreviewNow(); });
        }

        function fetchSavedList() {
            return fetch(LIST_URL, { headers: { 'Accept': 'application/json' } })
                .then(r => r.json())
                .then(res => {
                    savedTabsCache = (res.success && Array.isArray(res.items)) ? res.items : [];
                    document.querySelectorAll('.cz-class-chip').forEach(chip => {
                        const has = savedTabsCache.some(it => String(it.class_id) === chip.dataset.classId);
                        chip.classList.toggle('has-saved', has);
                    });
                    renderSavedTabs();
                })
                .catch(() => { /* silent — dots/tabs just won't show this time */ });
        }

        /* ─────────────────────────────────────────────
           SAVED CUSTOMISATIONS — tab strip
           One tab per class that already has a saved profile for this
           exam. Moved here from the pass slips index page (which no
           longer has its own toggle/save panel) so it's available for
           every template this page serves. ───────────────────────── */
        function renderSavedTabs() {
            const wrap = document.getElementById('czSavedTabsWrap');
            const holder = document.getElementById('czSavedTabs');
            if (!wrap || !holder) return;

            if (savedTabsCache.length === 0) {
                wrap.style.display = 'none';
                holder.innerHTML = '';
                return;
            }

            wrap.style.display = '';
            holder.innerHTML = savedTabsCache.map(it => `
                <span class="cz-saved-tab" data-class-id="${it.class_id}" onclick="selectSavedTab(${it.class_id})">
                    <i class="fas fa-sliders-h" style="font-size:.62rem;"></i>
                    <span>${it.class_name}</span>
                    <span class="cz-saved-tab-remove" title="Remove this class's saved customisation"
                          onclick="removeSavedTab(event, ${it.class_id}, '${(it.class_name + '').replace(/'/g, "\\'")}')">
                        <i class="fas fa-times"></i>
                    </span>
                </span>
            `).join('');
        }

        function setActiveSavedTab(classId) {
            document.querySelectorAll('.cz-saved-tab').forEach(tab => {
                tab.classList.toggle('active', String(classId) === tab.dataset.classId);
            });
        }

        /* Click a Saved Customisation tab: load THAT class's settings
           into the panel and put the class picker into single-select
           mode on just this class, so a subsequent Save re-saves the
           same class instead of fanning out to whatever else was still
           ticked in the chip list. */
/* Click a Saved Customisation tab: toggle selection.
   If the tab is already active, deselect it (clear the panel).
   If it's not active, load THAT class's settings into the panel. */
function selectSavedTab(classId) {
    const tab = document.querySelector(`.cz-saved-tab[data-class-id="${classId}"]`);
    const isActive = tab ? tab.classList.contains('active') : false;
    
    if (isActive) {
        // Deselect: clear selection, reset panel to defaults
        selectedClassIds.clear();
        document.querySelectorAll('.cz-class-chip').forEach(chip => {
            chip.classList.remove('selected');
        });
        updateSelectedCount();
        setActiveSavedTab(null);
        
        // Reset panel to template defaults
        applySettingsToPanel({});
        refreshPreviewNow();
    } else {
        // Select: load the class's settings
        const entry = savedTabsCache.find(it => String(it.class_id) === String(classId));
        if (!entry) return;

        selectedClassIds.clear();
        selectedClassIds.add(String(classId));
        document.querySelectorAll('.cz-class-chip').forEach(chip => {
            chip.classList.toggle('selected', chip.dataset.classId === String(classId));
        });
        updateSelectedCount();

        applySettingsToPanel(entry.settings || {});
        refreshPreviewNow();
        setActiveSavedTab(classId);
    }
}

        /* Delete a saved profile. Doesn't touch other classes' saved
           data — only the row for this one class. */
        async function removeSavedTab(evt, classId, className) {
            evt.stopPropagation(); // don't also trigger selectSavedTab()

            const result = await Swal.fire({
                title: 'Remove customisation?',
                text: `Remove the saved customisation for "${className}"? Its pass slips will go back to the default look next time they're printed.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, remove it',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                reverseButtons: true,
            });
            if (!result.isConfirmed) return;

            fetch(DELETE_URL_BASE + '/' + classId, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                    'Accept': 'application/json',
                },
            })
                .then(r => r.json())
                .then(() => {
                    const wasActive = document
                        .querySelector(`.cz-saved-tab[data-class-id="${classId}"]`)
                        ?.classList.contains('active');

                    fetchSavedList();

                    const chip = document.querySelector(`.cz-class-chip[data-class-id="${classId}"]`);
                    if (chip) chip.classList.remove('selected');
                    selectedClassIds.delete(String(classId));
                    updateSelectedCount();

                    if (wasActive) {
                        // The class we just deleted was loaded in the panel —
                        // repaint it back to the template's plain defaults so
                        // the panel matches reality.
                        applySettingsToPanel({});
                        refreshPreviewNow();
                    }
                    setActiveSavedTab(null);

                    Swal.fire({
                        title: 'Removed!',
                        text: `The saved customisation for "${className}" has been removed.`,
                        icon: 'success',
                        timer: 1800,
                        showConfirmButton: false,
                    });
                })
                .catch(() => {
                    Swal.fire({
                        title: 'Failed to remove',
                        text: 'Please check your connection and try again.',
                        icon: 'error',
                        confirmButtonText: 'OK',
                    });
                });
        }

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
                // Load THIS class's saved profile into the panel — same
                // "select a class → see what's actually saved for it"
                // behaviour as the main pass slips page. Only fires on
                // a fresh selection (not on deselect), and only for the
                // chip just clicked, so picking several classes at once
                // to batch-save doesn't fight over whose settings to show.
                loadClassCustomisation(id);
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

        fetchSavedList();

/* ── Save ── */
document.getElementById('czSaveBtn').addEventListener('click', function () {
    const statusEl = document.getElementById('czSaveStatus');
    const saveBtn = this;
    
    if (selectedClassIds.size === 0) {
        statusEl.style.color = '#c0392b';
        statusEl.textContent = 'Select at least one class first.';
        return;
    }
    
    // Disable button and show loading state
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Saving...';
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
            if (res.success) fetchSavedList();
        })
        .catch(() => {
            statusEl.style.color = '#c0392b';
            statusEl.textContent = 'Failed to save — check your connection.';
        })
        .finally(() => {
            // Re-enable button and restore original text
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i class="fas fa-save me-1"></i> Save for selected classes';
        });
});

        // Initial paint: the slip comes up fully-featured (every toggle
        // starts checked in the HTML above) before any customisation —
        // except Minimal's Performance Summary / Discipline / Signatures
        // and Classic's Signatures, which start unchecked per-template
        // (see $offByDefaultKeys above). If the class the page opens on
        // (the first "Preview with" option) already has a saved profile,
        // load it straight away instead of showing "fully-featured"
        // defaults that don't match what's actually saved for it — the
        // whole point being you shouldn't have to also click that same
        // class's chip below just to see its real settings.
        const initialPreviewClassId = currentPreviewClassId();
        if (initialPreviewClassId) {
            loadClassCustomisation(initialPreviewClassId, true);
        } else {
            refreshPreviewNow();
        }

        // ── Persist selected preview class across page reloads ──
(function() {
    const previewSelect = document.getElementById('czPreviewClass');
    const STORAGE_KEY = 'selectedPreviewClass_' + EXAM_ID; // Unique per exam
    
    // Load saved selection on page load
    const savedValue = localStorage.getItem(STORAGE_KEY);
    if (savedValue && previewSelect) {
        // Check if the saved value still exists as an option
        let optionExists = false;
        for (let i = 0; i < previewSelect.options.length; i++) {
            if (previewSelect.options[i].value === savedValue) {
                optionExists = true;
                break;
            }
        }
        if (optionExists) {
            previewSelect.value = savedValue;
            // Trigger change event to load the class's customisation
            const event = new Event('change');
            previewSelect.dispatchEvent(event);
        } else {
            // If the saved value doesn't exist, clear it from storage
            localStorage.removeItem(STORAGE_KEY);
        }
    }
    
    // Save selection when it changes
    if (previewSelect) {
        previewSelect.addEventListener('change', function() {
            if (this.value) {
                localStorage.setItem(STORAGE_KEY, this.value);
            } else {
                localStorage.removeItem(STORAGE_KEY);
            }
        });
    }
})();
    </script>
@endsection