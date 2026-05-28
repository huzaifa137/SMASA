<?php use App\Http\Controllers\Helper; ?>
@extends('layouts-side-bar.master')

@section('css')
    <link
        href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&family=Fraunces:opsz,wght@9..144,300;9..144,400;9..144,700;9..144,900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* ── Reset ── */
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        .tt-root {
            font-family: 'Instrument Sans', sans-serif;
            min-height: 100vh;
            display: flex;
            background: #f1f5f9;
            overflow-x: hidden;
            margin-top: 2em;
        }

        /* ════════════════════════════════════
               LEFT PANEL
           ════════════════════════════════════ */
        .tt-left {
            width: 420px;
            flex-shrink: 0;
            background: linear-gradient(160deg, #1a18a0 0%, #2C29CA 45%, #5351e4 100%);
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 3rem 2.5rem;
            overflow: hidden;
            min-height: 100vh;
        }

        /* animated dot-grid */
        .tt-left::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image: radial-gradient(circle, rgba(255, 255, 255, 0.13) 1px, transparent 1px);
            background-size: 28px 28px;
            animation: gridDrift 18s linear infinite;
        }

        /* radial glow top-right */
        .tt-left::after {
            content: '';
            position: absolute;
            top: -25%;
            right: -20%;
            width: 380px;
            height: 380px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.09) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
        }

        @keyframes gridDrift {
            0% {
                background-position: 0 0;
            }

            100% {
                background-position: 28px 28px;
            }
        }

        /* decorative calendar shapes */
        .tt-deco-cal {
            position: absolute;
            bottom: -2rem;
            right: -3rem;
            width: 260px;
            height: 260px;
            border: 2px solid rgba(255, 255, 255, 0.13);
            border-radius: 28px;
            transform: rotate(12deg);
            animation: floatCal 6s ease-in-out infinite;
        }

        .tt-deco-cal::before {
            content: '';
            position: absolute;
            top: 1.5rem;
            left: 1.5rem;
            right: 1.5rem;
            bottom: 1.5rem;
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 20px;
        }

        .tt-deco-cal-2 {
            position: absolute;
            top: 6rem;
            right: -5rem;
            width: 160px;
            height: 160px;
            border: 1.5px solid rgba(255, 255, 255, 0.07);
            border-radius: 20px;
            transform: rotate(-8deg);
            animation: floatCal 8s ease-in-out infinite reverse;
        }

        @keyframes floatCal {

            0%,
            100% {
                transform: rotate(12deg) translateY(0);
            }

            50% {
                transform: rotate(12deg) translateY(-12px);
            }
        }

        .tt-left-top {
            position: relative;
            z-index: 2;
        }

        .tt-logo-area {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            margin-bottom: 3.5rem;
        }

        .tt-logo-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.9);
            box-shadow: 0 0 10px rgba(255, 255, 255, 0.35);
        }

        .tt-logo-text {
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.5);
        }

        .tt-left-eyebrow {
            font-size: 0.7rem;
            font-weight: 600;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.75);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .tt-left-eyebrow::before {
            content: '';
            display: block;
            width: 24px;
            height: 2px;
            background: rgba(255, 255, 255, 0.55);
            border-radius: 99px;
        }

        .tt-left-title {
            font-family: 'Fraunces', serif;
            font-size: 3rem;
            font-weight: 900;
            line-height: 1.05;
            color: #ffffff;
            letter-spacing: -0.03em;
        }

        .tt-left-title em {
            font-style: italic;
            color: rgba(255, 255, 255, 0.88);
            text-shadow: 0 0 40px rgba(255, 255, 255, 0.2);
        }

        .tt-left-desc {
            margin-top: 1.5rem;
            font-size: 0.86rem;
            line-height: 1.7;
            color: rgba(255, 255, 255, 0.5);
            max-width: 280px;
        }

        /* step pills */
        .tt-left-steps {
            position: relative;
            z-index: 2;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .tt-pill {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            padding: 0.75rem 1rem;
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(255, 255, 255, 0.04);
            transition: all 0.2s;
        }

        .tt-pill.active {
            background: rgba(255, 255, 255, 0.14);
            border-color: rgba(255, 255, 255, 0.32);
            backdrop-filter: blur(8px);
        }

        .tt-pill-num {
            width: 1.8rem;
            height: 1.8rem;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.08);
            color: rgba(255, 255, 255, 0.35);
            font-size: 0.72rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .tt-pill.active .tt-pill-num {
            background: #ffffff;
            color: #2C29CA;
        }

        .tt-pill-label {
            font-size: 0.8rem;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.35);
        }

        .tt-pill.active .tt-pill-label {
            color: rgba(255, 255, 255, 0.95);
        }

        .tt-pill-check {
            margin-left: auto;
            width: 1.2rem;
            height: 1.2rem;
            border-radius: 50%;
            border: 1.5px solid rgba(255, 255, 255, 0.18);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.55rem;
            color: transparent;
        }

        /* ════════════════════════════════════
               RIGHT PANEL
           ════════════════════════════════════ */
        .tt-right {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: #f1f5f9;
            overflow-y: auto;
        }

        /* topbar */
        .tt-topbar {
            padding: 1.25rem 2.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid rgba(83, 81, 228, 0.1);
            background: #ffffff;
            position: sticky;
            top: 0;
            z-index: 50;
            box-shadow: 0 2px 10px rgba(83, 81, 228, 0.06);
        }

        .tt-back {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            font-size: 0.8rem;
            font-weight: 600;
            color: #5351e4;
            text-decoration: none;
            padding: 0.45rem 1rem;
            border-radius: 99px;
            border: 1.5px solid rgba(83, 81, 228, 0.25);
            background: rgba(83, 81, 228, 0.06);
            transition: all 0.2s;
        }

        .tt-back:hover {
            color: #ffffff;
            background: linear-gradient(135deg, #2C29CA, #5351e4);
            border-color: transparent;
            box-shadow: 0 4px 12px rgba(83, 81, 228, 0.3);
        }

        .tt-topbar-status {
            font-size: 0.75rem;
            font-weight: 600;
            color: #64748b;
            letter-spacing: 0.05em;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .tt-status-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #5351e4;
            box-shadow: 0 0 0 3px rgba(83, 81, 228, 0.2);
            animation: statusPulse 2.5s ease-in-out infinite;
        }

        @keyframes statusPulse {

            0%,
            100% {
                box-shadow: 0 0 0 3px rgba(83, 81, 228, 0.2);
            }

            50% {
                box-shadow: 0 0 0 6px rgba(83, 81, 228, 0.08);
            }
        }

        /* form area */
        .tt-form-area {
            flex: 1;
            padding: 2.5rem;
            max-width: 540px;
            width: 100%;
            animation: formIn 0.5s cubic-bezier(0.22, 1, 0.36, 1) both;
        }

        @keyframes formIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* field group */
        .tt-fgroup {
            margin-bottom: 2.2rem;
        }

        .tt-fgroup-label {
            font-size: 0.65rem;
            font-weight: 700;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: #2C29CA;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }

        .tt-fgroup-label::after {
            content: '';
            flex: 1;
            height: 1.5px;
            background: rgba(83, 81, 228, 0.15);
            border-radius: 99px;
        }

        /* float-label fields */
        .tt-float {
            position: relative;
            margin-bottom: 1rem;
        }

        .tt-float label {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            font-size: 0.84rem;
            font-weight: 500;
            color: #94a3b8;
            pointer-events: none;
            transition: all 0.2s;
            background: transparent;
            padding: 0 0.2rem;
        }

        .tt-float input:not(:placeholder-shown)~label,
        .tt-float input:focus~label,
        .tt-float select:focus~label,
        .tt-float.has-value label {
            top: 0;
            font-size: 0.68rem;
            font-weight: 700;
            color: #2C29CA;
            letter-spacing: 0.06em;
            background: #f1f5f9;
        }

        .tt-float input,
        .tt-float select {
            width: 100%;
            padding: 1rem 1rem 0.6rem;
            border: 1.5px solid rgba(83, 81, 228, 0.18);
            border-radius: 14px;
            font-size: 0.875rem;
            font-family: 'Instrument Sans', sans-serif;
            font-weight: 500;
            color: #1e293b;
            background: #ffffff;
            transition: border-color 0.18s, box-shadow 0.18s;
            appearance: auto;
        }

        .tt-float input::placeholder {
            color: transparent;
        }

        .tt-float input:focus,
        .tt-float select:focus {
            outline: none;
            border-color: #5351e4;
            box-shadow: 0 0 0 3px rgba(83, 81, 228, 0.1);
        }

        .tt-float input:hover,
        .tt-float select:hover {
            border-color: rgba(83, 81, 228, 0.35);
        }

        .tt-grid2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        /* stream reveal */
        .tt-stream-wrap {
            display: none;
            animation: formIn 0.3s cubic-bezier(0.22, 1, 0.36, 1) both;
        }

        .tt-stream-wrap.show {
            display: block;
        }

        .tt-no-streams {
            display: none;
            font-size: 0.75rem;
            font-weight: 600;
            color: #DC2626;
            margin-top: 0.4rem;
            padding-left: 0.3rem;
        }

        /* hint box */
        .tt-hint {
            display: flex;
            gap: 0.75rem;
            align-items: flex-start;
            padding: 1rem 1.2rem;
            background: #ffffff;
            border-radius: 14px;
            border: 1.5px solid rgba(83, 81, 228, 0.12);
            margin-bottom: 2rem;
            font-size: 0.8rem;
            line-height: 1.6;
            color: #475569;
            box-shadow: 0 2px 12px rgba(83, 81, 228, 0.06);
        }

        .tt-hint-icon {
            width: 2rem;
            height: 2rem;
            background: linear-gradient(135deg, #2C29CA, #5351e4);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            color: #ffffff;
            flex-shrink: 0;
            margin-top: 1px;
            box-shadow: 0 4px 10px rgba(83, 81, 228, 0.35);
        }

        /* submit row */
        .tt-submit-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-top: 0.5rem;
        }

        .tt-cancel-link {
            font-size: 0.82rem;
            font-weight: 600;
            color: #64748b;
            text-decoration: none;
            transition: color 0.15s;
        }

        .tt-cancel-link:hover {
            color: #2C29CA;
        }

        /* primary action button */
        .tt-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            background: linear-gradient(135deg, #2C29CA, #5351e4);
            color: white;
            border: none;
            border-radius: 14px;
            padding: 0.9rem 2rem;
            font-size: 0.875rem;
            font-family: 'Instrument Sans', sans-serif;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.22s cubic-bezier(0.22, 1, 0.36, 1);
            letter-spacing: -0.01em;
            box-shadow: 0 4px 16px rgba(83, 81, 228, 0.38);
            position: relative;
            overflow: hidden;
        }

        /* shimmer sweep on hover */
        .tt-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.22), transparent);
            transition: left 0.5s ease;
        }

        .tt-btn:hover:not(:disabled)::before {
            left: 100%;
        }

        .tt-btn .btn-arrow {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 1.6rem;
            height: 1.6rem;
            background: rgba(255, 255, 255, 0.22);
            border-radius: 8px;
            color: #ffffff;
            font-size: 0.75rem;
            transition: transform 0.2s;
            flex-shrink: 0;
        }

        .tt-btn:hover:not(:disabled) .btn-arrow {
            transform: translateX(3px);
        }

        .tt-btn:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 12px 32px rgba(83, 81, 228, 0.45);
        }

        .tt-btn:active:not(:disabled) {
            transform: translateY(0);
            box-shadow: 0 4px 16px rgba(83, 81, 228, 0.38);
        }

        .tt-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        /* ── Toast ── */
        .tt-toast {
            position: fixed;
            bottom: 1.5rem;
            left: 50%;
            transform: translateX(-50%) translateY(80px);
            z-index: 9999;
            background: #1e293b;
            color: white;
            padding: 0.75rem 1.4rem 0.75rem 0.75rem;
            border-radius: 99px;
            font-size: 0.82rem;
            font-weight: 600;
            font-family: 'Instrument Sans', sans-serif;
            opacity: 0;
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            pointer-events: none;
            display: flex;
            align-items: center;
            gap: 0.6rem;
            white-space: nowrap;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.18);
        }

        .tt-toast.show {
            opacity: 1;
            transform: translateX(-50%) translateY(0);
        }

        .tt-toast-pip {
            width: 1.6rem;
            height: 1.6rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.68rem;
            background: rgba(255, 255, 255, 0.12);
            flex-shrink: 0;
        }

        .tt-toast.error {
            background: #7F1D1D;
        }

        .tt-toast.success .tt-toast-pip {
            background: linear-gradient(135deg, #2C29CA, #5351e4);
            color: #ffffff;
            box-shadow: 0 2px 8px rgba(83, 81, 228, 0.5);
        }

        /* ── Responsive ── */
        @media (max-width: 900px) {
            .tt-root {
                flex-direction: column;
            }

            .tt-left {
                width: 100%;
                min-height: auto;
                padding: 2rem 1.5rem;
                flex-direction: row;
                align-items: flex-start;
                flex-wrap: wrap;
                gap: 1.5rem;
            }

            .tt-left-steps {
                flex-direction: row;
                flex-wrap: wrap;
            }

            .tt-deco-cal,
            .tt-deco-cal-2 {
                display: none;
            }

            .tt-left-title {
                font-size: 2rem;
            }

            .tt-topbar {
                padding: 1rem 1.5rem;
            }

            .tt-form-area {
                padding: 1.5rem;
                max-width: 100%;
            }

            .tt-grid2 {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 576px) {
            .tt-topbar {
                padding: 1rem;
            }

            .tt-form-area {
                padding: 1.25rem 1rem;
            }

            .tt-left {
                padding: 1.5rem 1rem;
            }

            .tt-left-title {
                font-size: 1.7rem;
            }

            .tt-btn {
                padding: 0.8rem 1.4rem;
                font-size: 0.82rem;
            }

            .tt-submit-row {
                flex-direction: column;
                gap: 1rem;
                align-items: stretch;
            }

            .tt-btn {
                justify-content: center;
            }
        }
    </style>
@endsection

@section('page-header'){{-- empty --}}@endsection

@section('content')
    <div class="tt-root">

        <!-- ── LEFT PANEL ── -->
        <aside class="tt-left">
            <div class="tt-left-top">
                <div class="tt-logo-area">
                    <div class="tt-logo-dot"></div>
                    <span class="tt-logo-text">Timetable Studio</span>
                </div>
                <div class="tt-left-eyebrow">New Timetable</div>
                <h1 class="tt-left-title">
                    Build your<br>
                    <em>perfect</em><br>
                    schedule.
                </h1>
                <p class="tt-left-desc">
                    Choose a class stream, name your timetable, and step into the slot editor — all in under a minute.
                </p>
            </div>

            <div class="tt-left-steps">
                <div class="tt-pill active">
                    <div class="tt-pill-num">1</div>
                    <span class="tt-pill-label">Configure timetable</span>
                    <div class="tt-pill-check"><i class="fas fa-check"></i></div>
                </div>
                <div class="tt-pill">
                    <div class="tt-pill-num">2</div>
                    <span class="tt-pill-label">Assign subjects &amp; slots</span>
                    <div class="tt-pill-check"></div>
                </div>
                <div class="tt-pill">
                    <div class="tt-pill-num">3</div>
                    <span class="tt-pill-label">Review &amp; publish</span>
                    <div class="tt-pill-check"></div>
                </div>
            </div>

            <!-- decorative shapes -->
            <div class="tt-deco-cal"></div>
            <div class="tt-deco-cal-2"></div>
        </aside>

        <!-- ── RIGHT PANEL ── -->
        <main class="tt-right">

            <!-- topbar -->
            <div class="tt-topbar">
                <a href="{{ route('timetable.dashboard') }}" class="tt-back"
                    style="background:#000; color:#fff; border-color:#000;">
                    <i class="fas fa-arrow-left"></i> Back to Timetable
                </a>
                <span class="tt-topbar-status">
                    <span class="tt-status-dot"></span>
                    Step 1 of 3
                </span>
            </div>

            <!-- form -->
            <div class="tt-form-area">

                <!-- CLASS -->
                <div class="tt-fgroup">
                    <div class="tt-fgroup-label">Class &amp; Stream</div>

                    <div class="tt-float has-value" id="classFloat">
                        <select id="classSelect" onchange="loadStreams(this.value)">
                            <option value="">— Select a class —</option>
                            @foreach($classrooms as $classroom)
                                <option value="{{ $classroom->class_name }}">{{ Helper::recordMdname($classroom->class_name) }}
                                </option>
                            @endforeach
                        </select>
                        <label>Class *</label>
                    </div>

                    <div class="tt-stream-wrap" id="streamContainer">
                        <div class="tt-float has-value">
                            <select id="streamSelect">
                                <option value="">— Select a stream —</option>
                            </select>
                            <label>Stream *</label>
                        </div>
                        <div class="tt-no-streams" id="noStreamsMsg">
                            <i class="fas fa-exclamation-circle me-1"></i> No streams found for this class.
                        </div>
                    </div>
                </div>

                <!-- DETAILS -->
                <div class="tt-fgroup">
                    <div class="tt-fgroup-label">Timetable Details</div>

                    <div class="tt-float">
                        <input type="text" id="ttName" placeholder=" ">
                        <label>Timetable Name (optional)</label>
                    </div>

                    <div class="tt-grid2">
                        <div class="tt-float has-value">
                            <select id="yearSelect">
                                <option value="">— None —</option>
                                @foreach($academicYears as $ay)
                                    <option value="{{ $ay->id }}" {{ $ay->is_active ? 'selected' : '' }}>{{ $ay->name }}
                                    </option>
                                @endforeach
                            </select>
                            <label>Academic Year</label>
                        </div>
                        <div class="tt-float has-value">
                            <select id="termSelect">
                                <option value="">— None —</option>
                                <option value="Term 1">Term 1</option>
                                <option value="Term 2">Term 2</option>
                                <option value="Term 3">Term 3</option>
                            </select>
                            <label>Term</label>
                        </div>
                    </div>
                </div>

                <!-- HINT -->
                <div class="tt-hint">
                    <div class="tt-hint-icon"><i class="fas fa-bolt text-white"></i></div>
                    <span>Once created, you'll land straight in the <strong>slot editor</strong> to drag subjects onto each
                        period.</span>
                </div>

                <!-- ACTIONS -->
                <div class="tt-submit-row">
                    <a href="{{ route('timetable.dashboard') }}" class="tt-cancel-link" style="background:#000 !important;
                  color:#fff !important;
                  border:1px solid #000 !important;
                  padding:8px 16px;
                  border-radius:5px;
                  text-decoration:none;
                  display:inline-block;">

                        Cancel
                    </a>
                    <button class="tt-btn" id="createBtn" onclick="createTimetable()">
                        Create Timetable
                        <span class="btn-arrow"><i class="fas fa-arrow-right"></i></span>
                    </button>
                </div>

            </div><!-- /form-area -->
        </main>

    </div>

    <!-- Toast -->
    <div class="tt-toast" id="toast">
        <div class="tt-toast-pip" id="toastIcon"></div>
        <span id="toastMsg"></span>
    </div>
    </div>
    </div>

    <script>
        const csrfToken = '{{ csrf_token() }}';

        async function loadStreams(classId) {
            const container = document.getElementById('streamContainer');
            const select = document.getElementById('streamSelect');
            const noMsg = document.getElementById('noStreamsMsg');

            if (!classId) { container.classList.remove('show'); return; }

            select.innerHTML = '<option value="">Loading…</option>';
            container.classList.add('show');
            noMsg.style.display = 'none';

            try {
                const res = await fetch(`{{ url('attendance/ajax/streams') }}/${classId}`);
                const data = await res.json();
                if (!data.length) {
                    select.innerHTML = '<option value="">No streams available</option>';
                    noMsg.style.display = 'block';
                } else {
                    select.innerHTML = '<option value="">— Select a stream —</option>' +
                        data.map(s => `<option value="${s.stream_id}">${s.stream_name || s.stream_id}</option>`).join('');
                }
            } catch (e) {
                select.innerHTML = '<option value="">Error loading streams</option>';
            }
        }

        async function createTimetable() {
            const classId = document.getElementById('classSelect').value;
            const streamId = document.getElementById('streamSelect').value;
            const yearId = document.getElementById('yearSelect').value;
            const term = document.getElementById('termSelect').value;
            const name = document.getElementById('ttName').value.trim();

            if (!classId) { showToast('Please select a class.', 'error'); return; }
            if (!streamId) { showToast('Please select a stream.', 'error'); return; }

            const btn = document.getElementById('createBtn');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating…';

            try {
                const res = await fetch('{{ route('timetable.store') }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({
                        class_id: classId,
                        stream_id: streamId,
                        academic_year_id: yearId || null,
                        term: term || null,
                        name: name || null
                    })
                });
                const data = await res.json();

                if (data.success) {
                    showToast('Timetable created! Opening editor…', 'success');
                    setTimeout(() => window.location.href = data.redirect, 900);
                } else {
                    showToast(data.message || 'Failed to create timetable.', 'error');
                    btn.disabled = false;
                    btn.innerHTML = 'Create Timetable <span class="btn-arrow"><i class="fas fa-arrow-right"></i></span>';
                }
            } catch (e) {
                showToast('Connection error. Please try again.', 'error');
                btn.disabled = false;
                btn.innerHTML = 'Create Timetable <span class="btn-arrow"><i class="fas fa-arrow-right"></i></span>';
            }
        }

        function showToast(msg, type = 'success') {
            const toast = document.getElementById('toast');
            const icon = document.getElementById('toastIcon');
            const msgEl = document.getElementById('toastMsg');

            icon.innerHTML = type === 'error' ? '<i class="fas fa-times"></i>' : '<i class="fas fa-check"></i>';
            msgEl.textContent = msg;
            toast.className = `tt-toast show ${type}`;

            clearTimeout(window._tTimer);
            window._tTimer = setTimeout(() => toast.classList.remove('show'), 3500);
        }
    </script>
@endsection