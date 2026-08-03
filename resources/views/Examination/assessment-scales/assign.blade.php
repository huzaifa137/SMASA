@extends('layouts-side-bar.master')
<?php use App\Http\Controllers\Helper; ?>

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
        --amber: #B4770B;
        --amber-bg: #FCF1DC;
        --red: #C4293A;
        --red-bg: #FCEAEC;
        --gray-bg: #F1F2F5;
    }

    * { box-sizing: border-box; }
    
    .aa-app { 
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; 
        color: var(--ink); 
        padding-bottom: 6rem; 
    }

    /* ── Top Bar ── */
    .aa-topbar {
        background: linear-gradient(135deg, #0F0E1A 0%, #1B1D28 40%, #2C29CA 100%);
        border-radius: 1.25rem;
        padding: 1.75rem 2.5rem;
        margin-bottom: 1.5rem;
        position: relative;
        overflow: hidden;
        box-shadow: 0 8px 40px rgba(44, 41, 202, .2);
        display: flex;
        flex-wrap: wrap;
        gap: 1.25rem;
        justify-content: space-between;
        align-items: center;
    }

    .aa-topbar::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-image: 
            radial-gradient(2px 2px at 20px 30px, rgba(255,255,255,.1), transparent),
            radial-gradient(2px 2px at 40px 70px, rgba(255,255,255,.08), transparent),
            radial-gradient(2px 2px at 50px 160px, rgba(255,255,255,.12), transparent),
            radial-gradient(2px 2px at 90px 40px, rgba(255,255,255,.06), transparent),
            radial-gradient(2px 2px at 130px 80px, rgba(255,255,255,.1), transparent),
            radial-gradient(2px 2px at 160px 30px, rgba(255,255,255,.08), transparent);
        background-size: 200px 200px;
        opacity: 0.5;
        pointer-events: none;
        animation: aaParticleMove 20s linear infinite;
    }

    @keyframes aaParticleMove {
        0% { transform: translate(0, 0); }
        100% { transform: translate(-20px, -20px); }
    }

    .aa-topbar::after {
        content: '';
        position: absolute;
        top: -30%;
        right: -10%;
        width: 500px;
        height: 500px;
        background: radial-gradient(circle, rgba(99, 102, 241, .15) 0%, transparent 70%);
        border-radius: 50%;
        pointer-events: none;
    }

    .aa-topbar-left {
        display: flex;
        align-items: flex-start;
        gap: 1.25rem;
        flex: 1;
        min-width: 200px;
        position: relative;
        z-index: 1;
    }

    .aa-topbar-icon {
        width: 54px;
        height: 54px;
        border-radius: .75rem;
        background: linear-gradient(135deg, #818CF8 0%, #6366F1 50%, #4F46E5 100%);
        color: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
        box-shadow: 0 4px 16px rgba(99, 102, 241, .3);
        transition: all .3s ease;
        flex-shrink: 0;
    }

    .aa-topbar-icon:hover {
        transform: scale(1.08) rotate(-5deg);
        box-shadow: 0 6px 24px rgba(99, 102, 241, .4);
    }

    .aa-topbar-title {
        font-weight: 800;
        font-size: 1.5rem;
        margin: 0;
        color: #ffffff !important;
        letter-spacing: -.02em;
        line-height: 1.2;
    }

    .aa-topbar-title .scale-name {
        background: rgba(255,255,255,.1);
        padding: .15rem .8rem;
        border-radius: 2rem;
        font-size: 1.1rem;
        display: inline-block;
        backdrop-filter: blur(4px);
        border: 1px solid rgba(255,255,255,.08);
    }

    .aa-topbar-subtitle {
        margin: 0;
        font-size: .85rem;
        color: rgba(255, 255, 255, .6);
        line-height: 1.4;
        max-width: 48ch;
    }

    .aa-topbar-actions {
        display: flex;
        align-items: center;
        gap: .6rem;
        flex-shrink: 0;
        flex-wrap: wrap;
        position: relative;
        z-index: 1;
    }

    .btn-aa-secondary {
        display: inline-flex;
        align-items: center;
        gap: .6rem;
        padding: .6rem 1.2rem;
        background: rgba(255, 255, 255, .06);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, .08);
        color: rgba(255, 255, 255, .8);
        font-weight: 600;
        font-size: .82rem;
        border-radius: .6rem;
        text-decoration: none;
        transition: all .25s ease;
        white-space: nowrap;
    }

    .btn-aa-secondary:hover {
        background: rgba(255, 255, 255, .12);
        color: #ffffff;
        text-decoration: none;
        transform: translateY(-2px);
        border-color: rgba(255, 255, 255, .15);
        box-shadow: 0 4px 12px rgba(0, 0, 0, .15);
    }

    .aa-scale-pill {
        display: inline-flex;
        align-items: center;
        gap: .5rem;
        background: rgba(255,255,255,.08);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255,255,255,.1);
        padding: .5rem 1.1rem;
        border-radius: 2rem;
        font-size: .8rem;
        font-weight: 700;
        color: rgba(255,255,255,.9);
    }

    .aa-scale-pill i {
        color: #818CF8;
    }

    /* ── Stats Bar ── */
    .aa-stats {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: .75rem;
        margin-bottom: 1.25rem;
    }

    .aa-stat {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: .6rem;
        padding: .85rem 1rem;
        transition: all .2s ease;
    }

    .aa-stat:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(44, 41, 202, .08);
    }

    .aa-stat .label {
        font-size: .7rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .03em;
        color: var(--muted);
        margin-bottom: .3rem;
    }

    .aa-stat .value {
        font-size: 1.25rem;
        font-weight: 800;
        color: var(--ink);
    }

    .aa-stat.accent .value {
        color: var(--primary);
    }

    /* ── Toolbar ── */
    .aa-toolbar {
        display: flex;
        gap: .75rem;
        flex-wrap: wrap;
        align-items: center;
        margin-bottom: 1.25rem;
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: .7rem;
        padding: .6rem 1rem;
    }

    .aa-search {
        flex: 1;
        min-width: 220px;
        border: 1.5px solid var(--border);
        border-radius: .5rem;
        padding: .5rem .8rem;
        font-size: .85rem;
        transition: all .2s ease;
        outline: none;
        background: #FAFBFC;
    }

    .aa-search:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(44, 41, 202, .08);
    }

    .aa-count {
        font-size: .78rem;
        color: var(--muted);
        font-weight: 600;
        white-space: nowrap;
        background: var(--bg);
        padding: .3rem .8rem;
        border-radius: 2rem;
    }

    /* ── Class Cards ── */
    .class-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: .7rem;
        margin-bottom: .75rem;
        overflow: hidden;
        transition: all .2s ease;
    }

    .class-card:hover {
        border-color: rgba(44, 41, 202, .2);
    }

    /* ── ACCORDION HEADER: Dark Theme ── */
    .class-card-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
        padding: .85rem 1.1rem;
        cursor: pointer;
        background: #2C29CA;
        transition: all .3s ease;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    }

    .class-card-head:hover {
        background: #0a02b0;
        transform: translateY(-1px);
        box-shadow: 0 4px 16px rgba(7, 1, 137, 0.3);
    }

    .class-title {
        font-weight: 700;
        font-size: .92rem;
        display: flex;
        align-items: center;
        gap: .5rem;
        color: #ffffff;
    }

    .class-title i.fa-chevron-right {
        font-size: .7rem;
        color: rgba(255, 255, 255, 0.5);
        transition: transform .25s ease;
    }

    .class-card.open .class-title i.fa-chevron-right {
        transform: rotate(90deg);
        color: #818CF8;
    }

    .class-meta {
        font-size: .72rem;
        color: rgba(255, 255, 255, 0.8);
        font-weight: 600;
        background: rgba(255, 255, 255, 0.1);
        padding: .2rem .7rem;
        border-radius: 1rem;
        border: 1px solid rgba(255, 255, 255, 0.08);
    }

    .class-meta i {
        color: #34D399 !important;
    }

    /* ── CLASS BODY: Light Theme ── */
    .class-card-body {
        display: none;
        padding: .3rem 1.1rem 1.1rem;
        background: #ffffff;
    }

    .class-card.open .class-card-body {
        display: block;
        animation: fadeSlideIn .25s ease;
    }

    @keyframes fadeSlideIn {
        from { opacity: 0; transform: translateY(-6px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* ── Select All Row ── */
    .select-all-row {
        display: flex;
        align-items: center;
        gap: .5rem;
        padding: .6rem 0;
        border-bottom: 1.5px dashed var(--border);
        margin-bottom: .6rem;
        font-size: .8rem;
        font-weight: 600;
        color: var(--muted);
    }

    .select-all-row input[type="checkbox"] {
        width: 16px;
        height: 16px;
        accent-color: var(--primary);
        cursor: pointer;
    }

    .select-all-row span {
        color: var(--ink);
    }

    /* ── Stream Label ── */
    .stream-block {
        margin-top: .6rem;
    }

    .stream-label {
        font-size: .7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: var(--muted);
        margin-bottom: .35rem;
        padding-left: .2rem;
    }

    .stream-label i {
        color: var(--muted);
    }

    /* ── Subject Row ── */
    .subject-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .6rem;
        padding: .45rem .5rem;
        border-radius: .4rem;
        transition: all .15s ease;
        color: var(--ink);
    }

    .subject-row:hover {
        background: var(--bg);
    }

    .subject-row label {
        display: flex;
        align-items: center;
        gap: .55rem;
        font-size: .85rem;
        cursor: pointer;
        margin: 0;
        flex: 1;
        color: var(--ink);
    }

    .subject-row input[type="checkbox"] {
        width: 17px;
        height: 17px;
        accent-color: var(--primary);
        cursor: pointer;
        transition: all .15s ease;
    }

    .subject-row input[type="checkbox"]:hover {
        transform: scale(1.1);
    }

    /* ── Badge Updates ── */
    .badge-current {
        font-size: .65rem;
        font-weight: 700;
        padding: .15rem .6rem;
        border-radius: 1rem;
        text-transform: uppercase;
        letter-spacing: .02em;
        white-space: nowrap;
        transition: all .2s ease;
    }

    .badge-this-scale {
        background: var(--green-bg);
        color: var(--green);
        border: 1px solid rgba(18, 135, 90, .15);
    }

    .badge-this-scale i {
        color: var(--green);
    }

    .badge-other-scale {
        background: var(--amber-bg);
        color: var(--amber);
        border: 1px solid rgba(180, 119, 11, .15);
    }

    .badge-other-scale i {
        color: var(--amber);
    }

    /* ── Empty State ── */
    .aa-empty {
        padding: 4rem 1rem;
        text-align: center;
        color: var(--muted);
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: .7rem;
    }

    .aa-empty i {
        color: var(--border);
        font-size: 2.5rem;
        margin-bottom: .75rem;
    }

    /* ── Save Bar ── */
    .aa-save-bar {
        position: fixed;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255,255,255,.92);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border-top: 1px solid var(--border);
        padding: .85rem 2rem;
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 1rem;
        box-shadow: 0 -4px 30px rgba(0,0,0,.06);
        z-index: 40;
        transition: all .3s ease;
    }

    .aa-save-bar .summary {
        margin-right: auto;
        font-size: .82rem;
        color: var(--muted);
        font-weight: 500;
    }

    .aa-save-bar .summary.has-changes {
        color: var(--amber);
        font-weight: 600;
    }

    .btn-aa-save {
        border: none;
        background: linear-gradient(135deg, var(--primary) 0%, #6366F1 50%, #818CF8 100%);
        color: #fff;
        font-weight: 700;
        font-size: .85rem;
        padding: .6rem 1.6rem;
        border-radius: .6rem;
        box-shadow: 0 4px 16px rgba(44, 41, 202, .25);
        transition: all .25s ease;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: .5rem;
    }

    .btn-aa-save:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 6px 24px rgba(44, 41, 202, .35);
    }

    .btn-aa-save:disabled {
        opacity: .6;
        cursor: not-allowed;
    }

    /* ── Responsive ── */
    @media (max-width: 768px) {
        .aa-topbar {
            padding: 1.5rem 1.5rem;
            border-radius: 1rem;
            flex-direction: column;
            align-items: stretch;
        }

        .aa-topbar-left {
            flex-direction: column;
            align-items: flex-start;
            gap: .75rem;
        }

        .aa-topbar-icon {
            width: 44px;
            height: 44px;
            font-size: 1.1rem;
        }

        .aa-topbar-title {
            font-size: 1.25rem;
        }

        .aa-topbar-title .scale-name {
            font-size: .95rem;
            padding: .1rem .6rem;
        }

        .aa-topbar-subtitle {
            font-size: .8rem;
            max-width: 100%;
        }

        .aa-topbar-actions {
            width: 100%;
            flex-direction: column;
        }

        .btn-aa-secondary {
            width: 100%;
            justify-content: center;
        }

        .aa-stats {
            grid-template-columns: repeat(2, 1fr);
        }

        .aa-save-bar {
            padding: .75rem 1rem;
            flex-wrap: wrap;
            gap: .5rem;
        }

        .aa-save-bar .summary {
            width: 100%;
            text-align: center;
            margin-right: 0;
        }

        .btn-aa-save {
            width: 100%;
            justify-content: center;
        }

        .class-card-head {
            flex-direction: column;
            align-items: flex-start;
            gap: .4rem;
        }

        .class-meta {
            font-size: .65rem;
            padding: .15rem .6rem;
        }
    }

    @media (max-width: 480px) {
        .aa-stats {
            grid-template-columns: 1fr 1fr;
            gap: .5rem;
        }

        .aa-stat .value {
            font-size: 1rem;
        }

        .aa-toolbar {
            flex-direction: column;
            align-items: stretch;
        }

        .aa-count {
            text-align: center;
        }
    }
</style>
@endsection

@section('content')
    <div class="side-app">
        <div class="aa-app">

            <!-- Premium Top Bar -->
            <div class="aa-topbar">
                <div class="aa-topbar-left">
                    <div class="aa-topbar-icon"><i class="fas fa-sitemap"></i></div>
                    <div>
                        <h3 class="aa-topbar-title">
                            Assign Scale : <span class="scale-name">{{ $scale->name }}</span>
                        </h3>
                        <p class="aa-topbar-subtitle">
                            Tick every subject (in whichever classes/streams you want) that should use this scale for marks entry. 
                            Subjects already on a different scale are flagged — ticking them here will switch them to this one.
                        </p>
                    </div>
                </div>
                <div class="aa-topbar-actions">
                    <a href="{{ route('examination.assessment-scales.index') }}" class="btn-aa-secondary">
                        <i class="fas fa-arrow-left"></i> <span>Back to Scales</span>
                    </a>
                    <span class="aa-scale-pill">
                        <i class="fas fa-comment-dots"></i>
                        {{ rtrim(rtrim(number_format($scale->min_score, 2), '0'), '.') }}–{{ rtrim(rtrim(number_format($scale->max_score, 2), '0'), '.') }}
                    </span>
                </div>
            </div>

            @php
                $totalSubjects = $classesData->sum(fn($entry) => $entry['streams']->sum(fn($s) => $s->subjects->count()));
                $onThisScaleCount = $classesData->sum(fn($entry) => $entry['streams']->sum(fn($s) => $s->subjects->where('assessment_scale_id', $scale->id)->count()));
                $classCount = $classesData->count();
            @endphp

            <!-- Stats -->
            <div class="aa-stats">
                <div class="aa-stat">
                    <div class="label">Total Classes</div>
                    <div class="value">{{ $classCount }}</div>
                </div>
                <div class="aa-stat">
                    <div class="label">Total Subjects</div>
                    <div class="value">{{ $totalSubjects }}</div>
                </div>
                <div class="aa-stat accent">
                    <div class="label">On This Scale</div>
                    <div class="value">{{ $onThisScaleCount }}</div>
                </div>
                <div class="aa-stat">
                    <div class="label">Scale Range</div>
                    <div class="value" style="font-size:.95rem;">
                        {{ rtrim(rtrim(number_format($scale->min_score, 2), '0'), '.') }} – {{ rtrim(rtrim(number_format($scale->max_score, 2), '0'), '.') }}
                    </div>
                </div>
            </div>

            @if ($classesData->isEmpty())
                <div class="aa-empty">
                    <i class="fas fa-inbox d-block"></i>
                    <p style="font-weight: 600; font-size: 1.1rem; margin: .5rem 0 .25rem;">No classes with subjects attached</p>
                    <p style="font-size: .85rem; margin: 0;">Add subjects to a class first (Create Class / Custom Subjects), then come back here.</p>
                </div>
            @else
                <!-- Toolbar -->
                <div class="aa-toolbar">
                    <input type="text" id="subjectSearch" class="aa-search" placeholder="Search by class, stream, or subject…">
                    <span class="aa-count" id="selectedCount">{{ $onThisScaleCount }} selected</span>
                </div>

<!-- ── Updated HTML ── -->
<div id="classAccordion">
    @foreach ($classesData as $entry)
        @php
            $classroom = $entry['classroom'];
            $streams = $entry['streams'];
            $subjectCount = $streams->sum(fn($s) => $s->subjects->count());
            $onThisScaleCountClass = $streams->sum(fn($s) => $s->subjects->where('assessment_scale_id', $scale->id)->count());
        @endphp
        <div class="class-card" data-class-card>
            <div class="class-card-head" data-toggle>
                <div class="class-title">
                    <i class="fas fa-chevron-right"></i>
                    {{ Helper::recordMdname($classroom->class_name) }}
                </div>
                <div class="class-meta">
                    <i class="fas fa-check-circle"></i>
                    {{ $onThisScaleCountClass }}/{{ $subjectCount }} subject(s) on this scale
                </div>
            </div>
            <div class="class-card-body">
                <label class="select-all-row">
                    <input type="checkbox" class="select-all-in-class" data-class-card-checkbox>
                    <span>Select all subjects in this class</span>
                </label>

                @foreach ($streams as $stream)
                    @if ($stream->subjects->isNotEmpty())
                        <div class="stream-block">
                            <div class="stream-label">
                                <i class="fas fa-layer-group"></i>
                                {{ $stream->stream_id === \App\Http\Controllers\ClassandSubjectController::NO_STREAM_SENTINEL ? 'No Stream' : $stream->stream_id }}
                            </div>
                            @foreach ($stream->subjects as $subject)
                                <div class="subject-row" data-subject-row data-search="{{ strtolower(Helper::recordMdname($classroom->class_name) . ' ' . $stream->stream_id . ' ' . $subject->display_name) }}">
                                    <label>
                                        <input type="checkbox" class="subject-checkbox"
                                            value="{{ $subject->id }}"
                                            {{ $subject->assessment_scale_id == $scale->id ? 'checked' : '' }}>
                                        {{ $subject->display_name }}
                                    </label>
                                    @if ($subject->assessment_scale_id == $scale->id)
                                        <span class="badge-current badge-this-scale"><i class="fas fa-check"></i> This scale</span>
                                    @elseif ($subject->assessmentScale)
                                        <span class="badge-current badge-other-scale"><i class="fas fa-exchange-alt"></i> {{ $subject->assessmentScale->name }}</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    @endforeach
</div>

                <!-- Save Bar -->
                <div class="aa-save-bar">
                    <span class="summary" id="saveSummary">No changes yet</span>
                    <button type="button" class="btn-aa-save" id="saveAssignmentsBtn">
                        <i class="fas fa-save"></i> Save Assignments
                    </button>
                </div>
            @endif

        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const csrfToken = '{{ csrf_token() }}';
        const scaleId = {{ $scale->id }};

        // Snapshot of which subjects were on THIS scale when the page loaded
        const originallyChecked = new Set();
        $('.subject-checkbox:checked').each(function () { 
            originallyChecked.add($(this).val()); 
        });

        function updateCounts() {
            const total = $('.subject-checkbox:checked').length;
            $('#selectedCount').text(total + ' selected');

            let changed = 0;
            $('.subject-checkbox').each(function () {
                const id = $(this).val();
                const isChecked = $(this).is(':checked');
                const wasChecked = originallyChecked.has(id);
                if (isChecked !== wasChecked) changed++;
            });
            
            const $summary = $('#saveSummary');
            if (changed > 0) {
                $summary.text(`${changed} change(s) not yet saved`).addClass('has-changes');
            } else {
                $summary.text('No changes yet').removeClass('has-changes');
            }
        }

        // Accordion open/close with animation
        $(document).on('click', '[data-toggle]', function (e) {
            const $card = $(this).closest('.class-card');
            $card.toggleClass('open');
        });

        // Open first class by default
        $('[data-class-card]').first().addClass('open');

        // "Select all in this class" checkbox
        $(document).on('change', '[data-class-card-checkbox]', function () {
            const checked = $(this).is(':checked');
            $(this).closest('.class-card').find('.subject-checkbox').prop('checked', checked).trigger('change');
            updateCounts();
        });

        // Keep "select all" in sync when subjects are toggled
        $(document).on('change', '.subject-checkbox', function () {
            const $card = $(this).closest('.class-card');
            const total = $card.find('.subject-checkbox').length;
            const checked = $card.find('.subject-checkbox:checked').length;
            $card.find('[data-class-card-checkbox]').prop('checked', total > 0 && total === checked);
            updateCounts();
        });

        // Live search
        function runSearch(q) {
            q = (q || '').trim().toLowerCase();
            let visibleCount = 0;
            
            $('[data-class-card]').each(function () {
                const $card = $(this);
                let anyMatch = false;
                
                $card.find('[data-subject-row]').each(function () {
                    const match = !q || $(this).data('search').includes(q);
                    $(this).toggle(match);
                    if (match) anyMatch = true;
                });
                
                $card.toggle(anyMatch);
                if (q && anyMatch) {
                    $card.addClass('open');
                    visibleCount++;
                }
            });
        }

        $('#subjectSearch').on('input', function () {
            runSearch($(this).val());
        });

        // Handle URL query parameter
        (function () {
            const params = new URLSearchParams(window.location.search);
            const q = params.get('q');
            if (q) {
                $('#subjectSearch').val(q);
                runSearch(q);
            }
        })();

        // Save with loader
        $('#saveAssignmentsBtn').on('click', function () {
            const $btn = $(this);
            const allIds = $('.subject-checkbox').map(function () { return $(this).val(); }).get();
            const checkedIds = $('.subject-checkbox:checked').map(function () { return $(this).val(); }).get();

            // Show loader on button
            const originalHtml = $btn.html();
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving…');

            $.ajax({
                url: `{{ url('examinations/assessment-scales') }}/${scaleId}/assign-bulk`,
                method: 'POST',
                data: {
                    _token: csrfToken,
                    all_class_subject_ids: allIds,
                    checked_class_subject_ids: checkedIds,
                },
                success: function (res) {
                    $btn.prop('disabled', false).html(originalHtml);
                    if (res.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Saved!',
                            text: res.message,
                            confirmButtonColor: '#2C29CA',
                            timer: 1500,
                            timerProgressBar: true
                        }).then(() => window.location.reload());
                    } else {
                        Swal.fire('Error', res.message, 'error');
                    }
                },
                error: function (xhr) {
                    $btn.prop('disabled', false).html(originalHtml);
                    const message = xhr.responseJSON?.message || 'Could not save assignments.';
                    Swal.fire('Error', message, 'error');
                }
            });
        });

        // Initial count update
        updateCounts();
    </script>
@endsection