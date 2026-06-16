@extends('layouts-side-bar.master')

@section('title', 'Send Notification')

@section('css')
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        /* ── Same design system ── */
        :root {
            --lib-blue: #2c29ca;
            --lib-blue-l: rgba(44, 41, 202, .12);
            --lib-blue-d: #2420a8;
            --lib-amber: #f59e0b;
            --lib-amber-l: rgba(245, 158, 11, .12);
            --lib-rose: #f43f5e;
            --lib-rose-l: rgba(244, 63, 94, .12);
            --lib-violet: #7c3aed;
            --lib-violet-l: rgba(124, 58, 237, .12);
            --lib-green: #10b981;
            --lib-green-l: rgba(16, 185, 129, .12);
            --surface: #ffffff;
            --bg: #f1f5f9;
            --border: #e2e8f0;
            --text-1: #0f172a;
            --text-2: #475569;
            --text-3: #94a3b8;
            --radius: 16px;
            --shadow: 0 1px 4px rgba(0, 0, 0, .06), 0 4px 20px rgba(0, 0, 0, .05);
            --shadow-lg: 0 8px 40px rgba(0, 0, 0, .10);
        }

        * {
            box-sizing: border-box;
        }

        body {
            background: var(--bg);
            /* font-family: 'Plus Jakarta Sans', sans-serif; */
        }

        /* ── Hero ── */
        .lib-hero-sm {
            background: linear-gradient(135deg, #1a1869 0%, #2c29ca 60%, #0d0c5e 100%);
            border-radius: 24px;
            padding: 1.5rem 2rem;
            margin-bottom: 1.75rem;
            margin-top: 1.75rem;
            position: relative;
            overflow: hidden;
            color: #fff;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .lib-hero-sm h4 {
            font-weight: 700;
            margin: 0;
        }

        .lib-hero-sm small {
            color: rgba(255, 255, 255, .7);
        }

        .lib-hero-sm .btn-outline-lib {
            border-color: rgba(255, 255, 255, .3);
            color: #fff;
        }

        .lib-hero-sm .btn-outline-lib:hover {
            background: rgba(255, 255, 255, .1);
            border-color: #fff;
            color: #fff;
        }

        /* ── Cards ── */
        .lib-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            margin-bottom: 1.5rem;
            overflow: hidden;
        }

        .lib-card-body {
            padding: 1.5rem;
        }

        .lib-card-footer {
            padding: 0.75rem 1.5rem;
            background: #fafbff;
            border-top: 1px solid var(--border);
            display: flex;
            justify-content: flex-end;
            gap: .75rem;
            flex-wrap: wrap;
        }

        /* ── Form Elements ── */
        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-label {
            display: block;
            font-weight: 600;
            font-size: .85rem;
            color: var(--text-1);
            margin-bottom: .35rem;
        }

        .form-label .text-danger {
            color: var(--lib-rose);
        }

        .form-label .text-muted {
            color: var(--text-3);
            font-weight: 400;
        }

        .form-control,
        .form-select {
            width: 100%;
            padding: .65rem 1rem;
            font-size: .875rem;
            border: 1.5px solid var(--border);
            border-radius: 10px;
            background: #fff;
            color: var(--text-1);
            transition: border-color .15s, box-shadow .15s;
            font-family: inherit;
        }

        .form-control:focus,
        .form-select:focus {
            outline: none;
            border-color: var(--lib-blue);
            box-shadow: 0 0 0 3px rgba(44, 41, 202, .12);
        }

        .form-control::placeholder {
            color: var(--text-3);
        }

        textarea.form-control {
            resize: vertical;
            min-height: 100px;
        }

        .form-text {
            display: block;
            margin-top: .25rem;
            font-size: .75rem;
            color: var(--text-3);
        }

        hr {
            border: none;
            border-top: 1px solid var(--border);
            margin: 1.5rem 0;
        }

        /* ── Buttons ── */
        .btn-lib {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            padding: .55rem 1.1rem;
            border-radius: 10px;
            font-size: .85rem;
            font-weight: 600;
            border: none;
            cursor: pointer;
            text-decoration: none;
            transition: all .18s;
            font-family: inherit;
        }

        .btn-primary-lib {
            background: linear-gradient(135deg, #2c29ca, #2420a8);
            color: #fff;
        }

        .btn-primary-lib:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 14px rgba(44, 41, 202, .4);
            color: #fff;
        }

        .btn-outline-lib {
            background: transparent;
            border: 1.5px solid var(--border);
            color: var(--text-2);
        }

        .btn-outline-lib:hover {
            border-color: var(--lib-blue);
            color: var(--lib-blue);
        }

        .btn-sm-lib {
            padding: .35rem .75rem;
            font-size: .78rem;
        }

        /* ── Alerts ── */
        .alert {
            border-radius: var(--radius);
            padding: 1rem 1.25rem;
            margin-bottom: 1.5rem;
            border: 1px solid transparent;
        }

        .alert-danger {
            background: var(--lib-rose-l);
            border-color: var(--lib-rose);
            color: var(--lib-rose);
        }

        .alert ul {
            margin: 0;
            padding-left: 1.2rem;
        }

        /* ── Chip Grid ── */
        .chip-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
            gap: 0.6rem;
            max-height: 200px;
            overflow-y: auto;
            padding: 0.5rem;
            border: 1.5px solid var(--border);
            border-radius: 12px;
            background: #fafbff;
        }

        .chip-item {
            background: var(--surface);
            border: 1.5px solid var(--border);
            border-radius: 30px;
            padding: 0.5rem 1rem;
            font-size: 0.85rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.15s;
            text-align: center;
            user-select: none;
        }

        .chip-item:hover {
            border-color: var(--lib-blue);
            background: var(--lib-blue-l);
        }

        .chip-item.selected {
            background: var(--lib-blue);
            color: #fff;
            border-color: var(--lib-blue);
            box-shadow: 0 2px 8px rgba(44, 41, 202, 0.25);
        }

        /* Search input inside chip groups */
        #teacherSearch,
        #studentSearch {
            border-radius: 10px;
        }

        /* ── Utilities ── */
        .d-flex {
            display: flex;
        }

        .align-items-center {
            align-items: center;
        }

        .justify-content-between {
            justify-content: space-between;
        }

        .justify-content-end {
            justify-content: flex-end;
        }

        .flex-wrap {
            flex-wrap: wrap;
        }

        .gap-2 {
            gap: .5rem;
        }

        .gap-3 {
            gap: 1rem;
        }

        .mb-0 {
            margin-bottom: 0;
        }

        .mb-3 {
            margin-bottom: 1rem;
        }

        .mb-4 {
            margin-bottom: 1.5rem;
        }

        .mt-2 {
            margin-top: .5rem;
        }

        .text-danger {
            color: var(--lib-rose) !important;
        }

        .text-muted {
            color: var(--text-3) !important;
        }

        .fw-semibold {
            font-weight: 600;
        }

        .row {
            display: flex;
            flex-wrap: wrap;
            margin: 0 -0.75rem;
        }

        .col-md-8,
        .col-md-4 {
            padding: 0 0.75rem;
            flex: 0 0 auto;
        }

        .col-md-8 {
            flex: 0 0 66.666%;
            max-width: 66.666%;
        }

        .col-md-4 {
            flex: 0 0 33.333%;
            max-width: 33.333%;
        }

        @media (max-width: 768px) {

            .col-md-8,
            .col-md-4 {
                flex: 0 0 100%;
                max-width: 100%;
            }
        }

        .container-fluid {
            padding: 0 1rem;
        }

        .px-0 {
            padding-left: 0;
            padding-right: 0;
        }

        /* Hide the specific boxes by default */
        #specificTeachersBox,
        #specificStudentsBox {
            display: none;
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid px-0">

        {{-- Hero Header --}}
        <div class="lib-hero-sm">
            <div>
                <h4><i class="fas fa-paper-plane me-2"></i> Send Notification</h4>
                <small>Compose and broadcast a notification to staff or students</small>
            </div>
            <a href="{{ route('notifications.index') }}" class="btn-lib btn-outline-lib">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
        </div>

        {{-- Validation Errors --}}
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Form --}}
        <form action="{{ route('notifications.store') }}" method="POST" id="notificationForm">
            @csrf

            <div class="lib-card">
                <div class="lib-card-body">

                    {{-- Title & Type --}}
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label class="form-label">Title <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control" maxlength="255"
                                    value="{{ old('title') }}" placeholder="e.g. Term 2 Fees Deadline" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label">Type <span class="text-danger">*</span></label>
                                <select name="type" id="typeSelect" class="form-select" required>
                                    @foreach($types as $key => $config)
                                        <option value="{{ $key }}" data-icon="{{ $config['icon'] }}"
                                            data-color="{{ $config['color'] }}" {{ old('type') == $key ? 'selected' : '' }}>
                                            {{ ucfirst($key) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Message --}}
                    <div class="form-group">
                        <label class="form-label">Message <span class="text-danger">*</span></label>
                        <textarea name="body" rows="4" class="form-control"
                            placeholder="Write the notification message...">{{ old('body') }}</textarea>
                    </div>

                    {{-- Action Link --}}
                    <div class="form-group">
                        <label class="form-label">Action Link <span class="text-muted">(optional)</span></label>
                        <input type="url" name="url" class="form-control" value="{{ old('url') }}"
                            placeholder="https://...">
                        <span class="form-text">If set, recipients can click through to this page.</span>
                    </div>

                    <hr>

                    {{-- Recipient Group --}}
                    <div class="form-group">
                        <label class="form-label">Send To <span class="text-danger">*</span></label>
                        <select name="recipient_group" id="recipientGroup" class="form-select" required>
                            <option value="">-- Select recipients --</option>
                            <option value="all">Everyone (Admins, Teachers & Students)</option>
                            <option value="all_admins">All Admins</option>
                            <option value="all_teachers">All Teachers</option>
                            <option value="all_students">All Students</option>
                            <option value="specific_teachers">Specific Teachers</option>
                            <option value="specific_students">Specific Students</option>
                        </select>
                    </div>

                    {{-- Specific Teachers (Chips) --}}
                    <div id="specificTeachersBox" class="form-group" style="display:none;">
                        <label class="form-label">Choose Teachers</label>
                        <div style="position:relative; margin-bottom:0.5rem;">
                            <i class="fas fa-search"
                                style="position:absolute; left:0.75rem; top:50%; transform:translateY(-50%); color:var(--text-3); font-size:0.75rem;"></i>
                            <input type="text" id="teacherSearch" class="form-control" placeholder="Search teacher..."
                                style="padding-left:2rem;" oninput="filterChips('teacher')">
                        </div>
                        <div id="teacherChipGrid" class="chip-grid">
                            @foreach($teachers as $teacher)
                                <div class="chip-item" data-id="{{ $teacher->id }}" onclick="toggleChip(this)">
                                    {{ $teacher->name }}
                                </div>
                            @endforeach
                        </div>
                        <div id="selectedTeachersContainer"></div>
                        <span class="form-text">Click on a teacher to toggle selection.</span>
                    </div>

                    {{-- Specific Students (Chips) --}}
                    <div id="specificStudentsBox" class="form-group" style="display:none;">
                        <label class="form-label">Choose Students</label>
                        <div style="position:relative; margin-bottom:0.5rem;">
                            <i class="fas fa-search"
                                style="position:absolute; left:0.75rem; top:50%; transform:translateY(-50%); color:var(--text-3); font-size:0.75rem;"></i>
                            <input type="text" id="studentSearch" class="form-control" placeholder="Search student..."
                                style="padding-left:2rem;" oninput="filterChips('student')">
                        </div>
                        <div id="studentChipGrid" class="chip-grid">
                            @foreach($students as $student)
                                <div class="chip-item" data-id="{{ $student->id }}" onclick="toggleChip(this)">
                                    {{ $student->name }}
                                </div>
                            @endforeach
                        </div>
                        <div id="selectedStudentsContainer"></div>
                        <span class="form-text">Click on a student to toggle selection.</span>
                    </div>
                </div>

                <div class="lib-card-footer">
                    <a href="{{ route('notifications.index') }}" class="btn-lib btn-outline-lib">Cancel</a>
                    <button type="submit" class="btn-lib btn-primary-lib" id="sendBtn">
                        <i class="fas fa-paper-plane me-1"></i> Send Notification
                    </button>
                </div>
            </div>
        </form>

    </div>
    </div>
    </div>
@endsection

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function toggleRecipientBoxes() {
            const group = document.getElementById('recipientGroup').value;
            document.getElementById('specificTeachersBox').style.display = (group === 'specific_teachers') ? 'block' : 'none';
            document.getElementById('specificStudentsBox').style.display = (group === 'specific_students') ? 'block' : 'none';
        }

        // ── Chip toggle functions ──
        function toggleChip(element) {
            element.classList.toggle('selected');
            const container = element.closest('.form-group').querySelector('[id$="Container"]');
            const id = element.dataset.id;
            const name = element.textContent.trim();

            if (element.classList.contains('selected')) {
                // Add hidden input
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'recipient_ids[]';
                input.value = id;
                input.setAttribute('data-chip-id', id);
                container.appendChild(input);
            } else {
                // Remove hidden input
                const inputs = container.querySelectorAll(`input[data-chip-id="${id}"]`);
                inputs.forEach(inp => inp.remove());
            }
        }

        function filterChips(type) {
            const searchInput = document.getElementById(type + 'Search');
            const query = searchInput.value.toLowerCase();
            const grid = document.getElementById(type + 'ChipGrid');
            const chips = grid.querySelectorAll('.chip-item');

            chips.forEach(chip => {
                const text = chip.textContent.toLowerCase();
                chip.style.display = text.includes(query) ? '' : 'none';
            });
        }

        // ── When recipient group changes, reset selections ──
        const originalToggle = toggleRecipientBoxes;
        toggleRecipientBoxes = function () {
            originalToggle();

            // Clear any previously selected chips and hidden inputs when switching groups
            document.querySelectorAll('#specificTeachersBox .chip-item, #specificStudentsBox .chip-item')
                .forEach(chip => chip.classList.remove('selected'));

            document.querySelectorAll('#specificTeachersBox input[data-chip-id], #specificStudentsBox input[data-chip-id]')
                .forEach(inp => inp.remove());

            // Reset search
            const tSearch = document.getElementById('teacherSearch');
            const sSearch = document.getElementById('studentSearch');
            if (tSearch) { tSearch.value = ''; }
            if (sSearch) { sSearch.value = ''; }
            document.querySelectorAll('#teacherChipGrid .chip-item, #studentChipGrid .chip-item')
                .forEach(chip => chip.style.display = '');
        };

        // ── Ensure toggle works after DOM load ──
        document.addEventListener('DOMContentLoaded', function () {
            // Re-bind the recipient group change to our extended version
            const groupSelect = document.getElementById('recipientGroup');
            groupSelect.removeEventListener('change', toggleRecipientBoxes);
            groupSelect.addEventListener('change', toggleRecipientBoxes);

            // Initialize visibility
            toggleRecipientBoxes();
        });

        // ── SweetAlert Confirmation before sending ──
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('notificationForm');

            form.addEventListener('submit', function (e) {
                e.preventDefault(); // Stop normal submission

                // Gather data for the confirmation modal
                const title = document.querySelector('input[name="title"]').value.trim();
                const type = document.querySelector('select[name="type"] option:checked').textContent;
                const recipientGroup = document.querySelector('select[name="recipient_group"] option:checked').textContent;
                const body = document.querySelector('textarea[name="body"]').value.trim();

                // Count selected recipients if specific group
                let recipientCount = 'All';
                const groupValue = document.getElementById('recipientGroup').value;
                if (groupValue === 'specific_teachers') {
                    const selected = document.querySelectorAll('#selectedTeachersContainer input[data-chip-id]');
                    recipientCount = selected.length + ' teacher(s)';
                } else if (groupValue === 'specific_students') {
                    const selected = document.querySelectorAll('#selectedStudentsContainer input[data-chip-id]');
                    recipientCount = selected.length + ' student(s)';
                }

                // Build confirmation message
                let html = `
                            <div style="text-align:left; font-size:0.9rem; line-height:1.6;">
                                <p><strong>Title:</strong> ${title || '—'}</p>
                                <p><strong>Type:</strong> ${type}</p>
                                <p><strong>Recipients:</strong> ${recipientGroup} (${recipientCount})</p>
                                <p><strong>Message:</strong><br>${body || '—'}</p>
                            </div>
                        `;

                Swal.fire({
                    title: 'Send Notification?',
                    html: html,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#2c29ca',
                    cancelButtonColor: '#dc2626',
                    confirmButtonText: 'Yes, send it!',
                    cancelButtonText: 'Cancel',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Show loading state
                        Swal.fire({
                            title: 'Sending...',
                            text: 'Please wait while we send the notification.',
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            showConfirmButton: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        // Submit the form
                        form.submit();
                    }
                });
            });
        });
    </script>
@endsection