<?php use App\Http\Controllers\Helper; ?>
@extends('layouts-side-bar.master')

@section('css')
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --brand: #5351e4;
            --brand-light: #2C29CA;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --text-primary: #1e293b;
            --text-secondary: #475569;
            --text-muted: #94a3b8;
            --border: #e2e8f0;
        }

        .glass-header {
            background: linear-gradient(135deg, #5351e4 0%, #2C29CA 100%);
            border-radius: 28px;
            padding: 2rem 2.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 20px 40px -12px rgba(83, 81, 228, 0.35);
            position: relative;
            overflow: hidden;
        }

        .glass-header::before {
            content: '';
            position: absolute;
            top: -40%;
            right: -10%;
            width: 280px;
            height: 280px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            border-radius: 50%;
        }

        .btn-glass {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(4px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
            border-radius: 12px;
            padding: 0.5rem 1.2rem;
            font-weight: 600;
            font-size: 0.85rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            transition: all 0.2s;
        }

        .btn-glass:hover {
            background: rgba(255, 255, 255, 0.25);
            color: white;
        }

        .wizard-card {
            background: white;
            border-radius: 24px;
            max-width: 680px;
            margin: 0 auto;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.07);
            border: 1px solid rgba(83, 81, 228, 0.08);
            overflow: hidden;
        }

        .wizard-header {
            background: linear-gradient(135deg, rgba(83, 81, 228, 0.04) 0%, rgba(44, 41, 202, 0.02) 100%);
            padding: 2rem;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .wizard-icon {
            width: 52px;
            height: 52px;
            border-radius: 16px;
            background: rgba(83, 81, 228, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            color: var(--brand);
        }

        .wizard-body {
            padding: 2rem;
        }

        .form-section {
            margin-bottom: 2rem;
        }

        .section-title {
            font-size: 0.72rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--brand);
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid rgba(83, 81, 228, 0.1);
        }

        .form-group {
            margin-bottom: 1.2rem;
        }

        .form-label {
            font-size: 0.8rem;
            font-weight: 700;
            color: var(--text-secondary);
            display: block;
            margin-bottom: 0.4rem;
        }

        .form-control-modern {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1.5px solid var(--border);
            border-radius: 14px;
            font-size: 0.875rem;
            color: var(--text-primary);
            background: #f8fafc;
            transition: all 0.2s;
            box-sizing: border-box;
            appearance: auto;
        }

        .form-control-modern:focus {
            outline: none;
            border-color: var(--brand);
            background: white;
            box-shadow: 0 0 0 3px rgba(83, 81, 228, 0.12);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        /* Class stream selector */
        .stream-selector {
            display: none;
            animation: fadeIn 0.3s ease;
        }

        .stream-selector.visible {
            display: block;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-6px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .hint-box {
            background: rgba(83, 81, 228, 0.05);
            border: 1px solid rgba(83, 81, 228, 0.15);
            border-radius: 12px;
            padding: 1rem 1.2rem;
            font-size: 0.82rem;
            color: var(--text-secondary);
            display: flex;
            gap: 0.6rem;
            align-items: flex-start;
            margin-top: 0.5rem;
        }

        .hint-box i {
            color: var(--brand);
            margin-top: 2px;
            flex-shrink: 0;
        }

        .wizard-footer {
            padding: 1.5rem 2rem;
            border-top: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .btn-create {
            background: var(--brand);
            color: white;
            border: none;
            border-radius: 14px;
            padding: 0.85rem 2rem;
            font-weight: 800;
            font-size: 0.95rem;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s;
            box-shadow: 0 4px 14px rgba(83, 81, 228, 0.3);
        }

        .btn-create:hover {
            background: var(--brand-light);
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(83, 81, 228, 0.35);
        }

        .btn-cancel-link {
            color: var(--text-muted);
            font-size: 0.875rem;
            font-weight: 600;
            text-decoration: none;
        }

        .btn-cancel-link:hover {
            color: var(--text-secondary);
        }

        .no-streams-msg {
            color: var(--danger);
            font-size: 0.8rem;
            font-weight: 600;
            margin-top: 0.4rem;
            display: none;
        }

        .toast-notif {
            position: fixed;
            bottom: 1.5rem;
            right: 1.5rem;
            z-index: 9999;
            background: #1e293b;
            color: white;
            padding: 0.85rem 1.5rem;
            border-radius: 14px;
            font-size: 0.875rem;
            font-weight: 600;
            transform: translateY(80px);
            opacity: 0;
            transition: all 0.3s;
        }

        .toast-notif.show {
            transform: translateY(0);
            opacity: 1;
        }

        .toast-notif.error {
            background: #dc2626;
        }
    </style>
@endsection

@section('page-header')
    <div class="glass-header">
        <div class="d-flex align-items-center justify-content-between flex-wrap" style="position:relative;z-index:1;">
            <div>
                <div class="d-flex align-items-center gap-2 mb-2">
                    <a href="{{ route('timetable.dashboard') }}" class="btn-glass"
                        style="padding:0.35rem 0.9rem;font-size:0.78rem;">
                        <i class="fas fa-arrow-left"></i> Timetable
                    </a>
                </div>
                <h1 style="font-size:1.9rem;font-weight:800;color:white;margin-bottom:0.3rem;">
                    <i class="fas fa-calendar-plus me-2"></i> Create Timetable
                </h1>
                <p style="color:rgba(255,255,255,0.82);margin:0;font-size:0.92rem;">
                    Set up a new timetable for a class stream
                </p>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="container-fluid px-0">
        <div class="wizard-card">
            <div class="wizard-header">
                <div class="wizard-icon"><i class="fas fa-calendar-alt"></i></div>
                <div>
                    <h5 style="font-weight:800;color:var(--text-primary);margin:0;">New Timetable</h5>
                    <p style="color:var(--text-muted);margin:0;font-size:0.85rem;">Select the class stream and configure the
                        timetable details</p>
                </div>
            </div>

            <div class="wizard-body">
                <!-- Section: Class Selection -->
                <div class="form-section">
                    <div class="section-title"><i class="fas fa-school me-1"></i> Class Selection</div>
                    <div class="form-group">
                        <label class="form-label">Class *</label>
                        <select id="classSelect" class="form-control-modern" onchange="loadStreams(this.value)">
                            <option value="">— Select a Class —</option>
                            @foreach($classrooms as $classroom)
                                <option value="{{ $classroom->class_name }}">{{ Helper::recordMdname($classroom->class_name) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="stream-selector" id="streamContainer">
                        <div class="form-group">
                            <label class="form-label">Stream *</label>
                            <select id="streamSelect" class="form-control-modern">
                                <option value="">— Select a Stream —</option>
                            </select>
                            <div class="no-streams-msg" id="noStreamsMsg">
                                <i class="fas fa-exclamation-circle"></i> No streams found for this class.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section: Timetable Details -->
                <div class="form-section">
                    <div class="section-title"><i class="fas fa-info-circle me-1"></i> Timetable Details</div>
                    <div class="form-group">
                        <label class="form-label">Timetable Name <span
                                style="color:var(--text-muted);font-weight:400;">(optional)</span></label>
                        <input type="text" id="ttName" class="form-control-modern"
                            placeholder="e.g. Term 2 2025 Timetable — auto-generated if blank">
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Academic Year</label>
                            <select id="yearSelect" class="form-control-modern">
                                <option value="">— None / Not Specified —</option>
                                @foreach($academicYears as $ay)
                                    <option value="{{ $ay->id }}" {{ $ay->is_active ? 'selected' : '' }}>{{ $ay->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Term</label>
                            <select id="termSelect" class="form-control-modern">
                                <option value="">— None / Not Specified —</option>
                                <option value="Term 1">Term 1</option>
                                <option value="Term 2">Term 2</option>
                                <option value="Term 3">Term 3</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="hint-box">
                    <i class="fas fa-lightbulb"></i>
                    <span>After creating the timetable, you'll be taken to the <strong>editor</strong> where you can drag
                        and assign subjects to each day and period slot.</span>
                </div>
            </div>

            <div class="wizard-footer">
                <a href="{{ route('timetable.dashboard') }}" class="btn-cancel-link">
                    <i class="fas fa-times me-1"></i> Cancel
                </a>
                <button class="btn-create" onclick="createTimetable()">
                    <i class="fas fa-rocket"></i> Create & Open Editor
                </button>
            </div>
        </div>
    </div>
    </div>
    </div>
    </div>

    <div class="toast-notif" id="toast"></div>
@endsection

@push('scripts')
    <script>
        const csrfToken = '{{ csrf_token() }}';

        async function loadStreams(classId) {
            const container = document.getElementById('streamContainer');
            const select = document.getElementById('streamSelect');
            const noMsg = document.getElementById('noStreamsMsg');

            if (!classId) { container.classList.remove('visible'); return; }

            select.innerHTML = '<option value="">Loading...</option>';
            container.classList.add('visible');
            noMsg.style.display = 'none';

            try {
                const res = await fetch(`{{ url('attendance/ajax/streams') }}/${classId}`);
                const data = await res.json();
                if (!data.length) {
                    select.innerHTML = '<option value="">No streams available</option>';
                    noMsg.style.display = 'block';
                } else {
                    select.innerHTML = '<option value="">— Select a Stream —</option>' +
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

            const btn = document.querySelector('.btn-create');
            btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating...';

            try {
                const res = await fetch('{{ route('timetable.store') }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({ class_id: classId, stream_id: streamId, academic_year_id: yearId || null, term: term || null, name: name || null })
                });
                const data = await res.json();
                if (data.success) {
                    showToast('Timetable created! Opening editor...', 'success');
                    setTimeout(() => window.location.href = data.redirect, 700);
                } else {
                    showToast(data.message || 'Failed to create timetable.', 'error');
                    btn.disabled = false; btn.innerHTML = '<i class="fas fa-rocket"></i> Create & Open Editor';
                }
            } catch (e) {
                showToast('Connection error.', 'error');
                btn.disabled = false; btn.innerHTML = '<i class="fas fa-rocket"></i> Create & Open Editor';
            }
        }

        function showToast(msg, type = 'success') {
            const t = document.getElementById('toast');
            t.textContent = msg; t.className = `toast-notif show ${type}`;
            clearTimeout(window._tt); window._tt = setTimeout(() => t.classList.remove('show'), 3000);
        }
    </script>
@endpush