{{-- resources/views/student/id-cards/create.blade.php --}}
<?php use App\Http\Controllers\Helper; ?>
@extends('layouts-side-bar.master')

@section('css')
    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --b: #2f2ccb;
            --bl: rgba(47, 44, 203, .10);
            --b2: #2420a8;
            --g: #059669;
            --gl: rgba(5, 150, 105, .10);
            --r: #dc2626;
            --rl: rgba(220, 38, 38, .10);
            --a: #d97706;
            --al: rgba(217, 119, 6, .10);
            --surf: #fff;
            --bg: #f0f4f8;
            --brd: #e2e8f0;
            --t1: #0f172a;
            --t2: #475569;
            --t3: #94a3b8;
            --rad: 16px;
            --rads: 10px;
            --sh: 0 1px 3px rgba(0, 0, 0, .06), 0 4px 16px rgba(0, 0, 0, .05);
            --sh-lg: 0 10px 40px rgba(0, 0, 0, .12);
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }

        *:not(i):not([class*="fa"]):not([class*="bi"]) {
            font-family: 'DM Sans', sans-serif;
        }

        body {
            background: var(--bg);
        }

        .fin-hero {
            background: linear-gradient(135deg, #1a1869 0%, #2f2ccb 60%, #0d0c5e 100%);
            border-radius: 24px;
            padding: 2rem 2.5rem;
            margin-bottom: 1.75rem;
            position: relative;
            overflow: hidden;
        }

        .fin-hero::before {
            content: '';
            position: absolute;
            top: -60px;
            right: -60px;
            width: 220px;
            height: 220px;
            background: rgba(255, 255, 255, .06);
            border-radius: 50%;
        }

        .fin-hero::after {
            content: '';
            position: absolute;
            bottom: -40px;
            right: 120px;
            width: 140px;
            height: 140px;
            background: rgba(255, 255, 255, .04);
            border-radius: 50%;
        }

        .hero-title {
            color: #fff;
            font-size: 1.6rem;
            font-weight: 700;
            margin: 0 0 .3rem;
        }

        .hero-sub {
            color: rgba(255, 255, 255, .7);
            font-size: .93rem;
            margin: 0;
        }

        .hero-actions {
            display: flex;
            gap: .75rem;
            margin-top: 1.25rem;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            padding: .48rem 1rem;
            border-radius: 8px;
            font-size: .85rem;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: .15s;
            text-decoration: none;
        }

        .btn-primary {
            background: var(--b);
            color: #fff;
        }

        .btn-primary:hover {
            background: var(--b2);
            color: #fff;
        }

        .btn-outline {
            background: transparent;
            color: var(--b);
            border: 1.5px solid var(--b);
        }

        .btn-outline:hover {
            background: var(--bl);
        }

        .btn-secondary {
            background: #6c757d;
            color: #fff;
        }

        .btn-secondary:hover {
            background: #5a6268;
        }

        .btn-success {
            background: var(--g);
            color: #fff;
        }

        .btn-success:hover {
            background: #047857;
        }

        .form-card {
            background: var(--surf);
            border-radius: var(--rad);
            box-shadow: var(--sh);
            overflow: hidden;
            margin-bottom: 1.5rem;
        }

        .form-header {
            padding: 1rem 1.5rem;
            border-bottom: 1.5px solid var(--brd);
            background: #fff;
        }

        .form-header h5 {
            margin: 0;
            font-size: 1rem;
            font-weight: 700;
            color: var(--t1);
        }

        .form-header p {
            margin: .25rem 0 0;
            font-size: .8rem;
            color: var(--t3);
        }

        .form-body {
            padding: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-group label {
            font-size: .82rem;
            font-weight: 600;
            color: var(--t2);
            display: block;
            margin-bottom: .35rem;
        }

        .form-group label .required {
            color: var(--r);
            margin-left: 2px;
        }

        .form-group select,
        .form-group input {
            width: 100%;
            padding: .6rem .8rem;
            border: 1.5px solid var(--brd);
            border-radius: 8px;
            font-size: .88rem;
            background: var(--bg);
            color: var(--t1);
            transition: all .15s;
        }

        .form-group select:focus,
        .form-group input:focus {
            outline: none;
            border-color: var(--b);
            background: #fff;
        }

        .info-box {
            background: var(--bl);
            border-radius: 10px;
            padding: .85rem 1rem;
            margin-bottom: 1.5rem;
            border-left: 4px solid var(--b);
        }

        .info-box p {
            font-size: .82rem;
            color: var(--b);
            margin: 0 0 .25rem 0;
        }

        .info-box i {
            margin-right: .5rem;
        }

        .info-box .info-title {
            font-weight: 700;
            margin-bottom: .5rem;
        }

        .form-actions {
            display: flex;
            gap: .75rem;
            justify-content: flex-end;
            padding-top: .5rem;
        }

        .student-preview-table {
            margin-top: 1.5rem;
            border-top: 1.5px solid var(--brd);
            padding-top: 1.5rem;
        }

        .student-preview-table h6 {
            font-size: .85rem;
            font-weight: 700;
            color: var(--t1);
            margin-bottom: .75rem;
        }

        .preview-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: .5rem;
            max-height: 200px;
            overflow-y: auto;
            padding: .5rem;
            background: var(--bg);
            border-radius: 10px;
        }

        .preview-item {
            display: flex;
            align-items: center;
            gap: .5rem;
            padding: .4rem .6rem;
            background: var(--surf);
            border-radius: 8px;
            font-size: .75rem;
            color: var(--t2);
            border: 1px solid var(--brd);
        }

        .preview-item i {
            color: var(--g);
            font-size: .7rem;
        }

        .loading-spinner {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255, 255, 255, .3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 0.6s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        @media(max-width:640px) {
            .fin-hero {
                padding: 1.5rem;
            }

            .hero-title {
                font-size: 1.25rem;
            }

            .form-body {
                padding: 1rem;
            }
        }

        th {
            background: #2c29ca;
            color: #ffffff;
            font-size: .73rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .05em;
            padding: .7rem 1rem;
            text-align: left;
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid mt-5" style="max-width:1200px;">
        {{-- Hero --}}
        <div class="fin-hero">
            <p class="hero-sub mb-1">Academic Year: <strong style="color:#fff;">{{ Helper::active_year() }}</strong></p>
            <h2 class="hero-title"><i class="fas fa-id-card me-2"></i> Generate ID Cards</h2>
            <p class="hero-sub">Bulk generate student identity cards by class and stream</p>
            <div class="hero-actions">
                <a href="{{ route('id-cards.index') }}" class="btn btn-outline"
                    style="color:#fff;border-color:rgba(255,255,255,.5);">
                    <i class="fas fa-arrow-left"></i> Back to Cards
                </a>
            </div>
        </div>

        {{-- Generate Form --}}
        <div class="form-card">
            <div class="form-header">
                <h5><i class="fas fa-magic me-2" style="color:var(--b);"></i> Generate New ID Cards</h5>
                <p>Select a class and optional stream to generate ID cards for students who don't already have an active
                    card</p>
            </div>
            <div class="form-body">
                <div class="info-box">
                    <div class="info-title">
                        <i class="fas fa-info-circle"></i> Important Information
                    </div>
                    <p>• Students who already have an <strong>ACTIVE</strong> ID card for {{ Helper::active_year() }} will
                        be automatically skipped.</p>
                    <p>• Cards are valid until the end of the current academic year
                        ({{ Carbon\Carbon::now()->endOfYear()->format('d M Y') }}).</p>
                    <p>• Each card gets a unique QR code for verification and scanning.</p>
                    <p>• Generated cards can be printed individually or in bulk after generation.</p>
                </div>

                <div class="form-group">
                    <label>Select Class <span class="required">*</span></label>
                    <select id="classSelect" onchange="loadStreamsAndPreview()">
                        <option value="">— Select a Class —</option>
                        @foreach($classrooms as $cls)
                            <option value="{{ $cls->class_name }}">{{ Helper::recordMdname($cls->class_name) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>Filter by Stream <small style="color:var(--t3);font-weight:normal;">(optional - leave blank for
                            all streams)</small></label>
                    <select id="streamSelect" onchange="loadStudentsPreview()">
                        <option value="">— All Streams —</option>
                    </select>
                </div>

                {{-- Student Preview --}}
                <div class="student-preview-table" id="studentPreviewContainer" style="display:none;">
                    <h6><i class="fas fa-users me-2" style="color:var(--b);"></i> Students to be processed</h6>
                    <div id="studentPreviewList" class="preview-grid">
                        <!-- Students will be loaded here -->
                    </div>
                    <div style="margin-top: 1rem; font-size: .78rem; color: var(--t3);" id="previewCountMsg"></div>
                </div>

                <div class="form-actions">
                    <a href="{{ route('id-cards.index') }}" class="btn btn-outline">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                    <button class="btn btn-primary" onclick="submitBulkGenerate()" id="generateBtn">
                        <i class="fas fa-magic"></i> Generate ID Cards
                    </button>
                </div>
            </div>
        </div>

        {{-- Single Student Generate Section (Optional) --}}
        <div class="form-card">
            <div class="form-header">
                <h5><i class="fas fa-user-plus me-2" style="color:var(--g);"></i> Generate for Individual Student</h5>
                <p>Generate a new ID card for a specific student (replaces any existing active card)</p>
            </div>
            <div class="form-body">
                <div class="form-group">
                    <label>Search Student</label>
                    <div style="display: flex; gap: .5rem;">
                        <input type="text" id="studentSearch" placeholder="Enter student name or admission number..."
                            style="flex:1;" onkeyup="searchStudents()">
                        <button class="btn btn-outline" type="button" onclick="searchStudents()">
                            <i class="fas fa-search"></i> Search
                        </button>
                    </div>
                </div>
                <div id="studentSearchResults" style="display:none;">
                    <div
                        style="margin-top: .75rem; max-height: 350px; overflow-y: auto; border: 1px solid var(--brd); border-radius: 8px;">
                        <table style="width:100%; font-size: .8rem;">
                            <thead>
                                <tr style="background: var(--bg); position: sticky; top: 0;">
                                    <th style="padding: .5rem .75rem;">Adm No.</th>
                                    <th style="padding: .5rem .75rem;">Name</th>
                                    <th style="padding: .5rem .75rem;">Class</th>
                                    <th style="padding: .5rem .75rem;">Stream</th>
                                    <th style="padding: .5rem .75rem;">Status</th>
                                    <th style="padding: .5rem .75rem;">Action</th>
                                </tr>
                            </thead>
                            <tbody id="searchResultsBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Toast --}}
    <div id="toast"
        style="display:none;position:fixed;bottom:1.5rem;right:1.5rem;z-index:9999;background:#1e293b;color:#fff;padding:.75rem 1.25rem;border-radius:12px;font-size:.88rem;box-shadow:var(--sh-lg);min-width:260px;">
    </div>

    </div>
    </div>
    </div>
@endsection

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const CSRF = document.querySelector('meta[name="csrf-token"]').content;

        function showToast(msg, type = 'info') {
            const el = document.getElementById('toast');
            el.textContent = msg;
            el.style.background = type === 'success' ? '#059669' : type === 'error' ? '#dc2626' : '#1e293b';
            el.style.display = 'block';
            setTimeout(() => el.style.display = 'none', 3500);
        }

        // Load streams based on selected class AND load preview
        function loadStreamsAndPreview() {
            const classId = document.getElementById('classSelect').value;
            const streamSelect = document.getElementById('streamSelect');

            if (!classId) {
                streamSelect.innerHTML = '<option value="">— All Streams —</option>';
                document.getElementById('studentPreviewContainer').style.display = 'none';
                return;
            }

            // Load streams
            fetch(`{{ route('id-cards.streams.by.senior') }}?class_id=${classId}`)
                .then(r => r.json())
                .then(streams => {
                    streamSelect.innerHTML = '<option value="">— All Streams —</option>';
                    streams.forEach(s => {
                        const opt = document.createElement('option');
                        opt.value = s.stream_id ?? s.id;
                        opt.textContent = s.display_name ?? s.stream_id;
                        streamSelect.appendChild(opt);
                    });
                    // After loading streams, load student preview
                    loadStudentsPreview();
                })
                .catch(() => {
                    streamSelect.innerHTML = '<option value="">— All Streams —</option>';
                    loadStudentsPreview();
                });
        }

        // Load student preview
        let currentStudents = [];

        function loadStudentsPreview() {
            const senior = document.getElementById('classSelect').value;
            const stream = document.getElementById('streamSelect').value;
            const previewContainer = document.getElementById('studentPreviewContainer');
            const previewList = document.getElementById('studentPreviewList');
            const previewMsg = document.getElementById('previewCountMsg');

            if (!senior) {
                previewContainer.style.display = 'none';
                return;
            }

            previewContainer.style.display = 'block';
            previewList.innerHTML = '<div class="preview-item"><i class="fas fa-spinner fa-spin"></i> Loading students...</div>';

            // FIXED: Use the correct route for preview
            let url = `{{ route('id-cards.generate.preview') }}?senior=${encodeURIComponent(senior)}`;
            if (stream) url += `&stream=${encodeURIComponent(stream)}`;

            fetch(url)
                .then(r => r.json())
                .then(data => {
                    currentStudents = data.students || [];
                    if (currentStudents.length === 0) {
                        previewList.innerHTML = '<div class="preview-item"><i class="fas fa-info-circle"></i> No students found for this selection.</div>';
                        previewMsg.textContent = '';
                        return;
                    }

                    // Show only first 20
                    const displayStudents = currentStudents.slice(0, 20);
                    previewList.innerHTML = displayStudents.map(s => `
                                <div class="preview-item">
                                    <i class="fas fa-user-graduate"></i>
                                    <span><strong>${escapeHtml(s.firstname)} ${escapeHtml(s.lastname)}</strong> (${escapeHtml(s.admission_number || 'N/A')})</span>
                                    ${s.has_active_card ? '<span style="color:var(--r);margin-left:auto;"><i class="fas fa-id-card"></i> Has card</span>' : ''}
                                </div>
                            `).join('');

                    const hasExisting = currentStudents.filter(s => s.has_active_card).length;
                    const toGenerate = currentStudents.filter(s => !s.has_active_card).length;

                    previewMsg.innerHTML = `
                                <i class="fas fa-users"></i> Total: ${currentStudents.length} students · 
                                <span style="color:var(--g);">To generate: ${toGenerate}</span> · 
                                <span style="color:var(--a);">Already have active card: ${hasExisting}</span>
                            `;
                })
                .catch((err) => {
                    console.error('Preview error:', err);
                    previewList.innerHTML = '<div class="preview-item"><i class="fas fa-exclamation-triangle"></i> Error loading students.</div>';
                });
        }

        // Escape HTML to prevent XSS
        function escapeHtml(str) {
            if (!str) return '';
            return str.replace(/[&<>]/g, function (m) {
                if (m === '&') return '&amp;';
                if (m === '<') return '&lt;';
                if (m === '>') return '&gt;';
                return m;
            });
        }

        // Bulk generate
        function submitBulkGenerate() {
            const senior = document.getElementById('classSelect').value;
            const stream = document.getElementById('streamSelect').value;

            if (!senior) {
                showToast('Please select a class.', 'error');
                return;
            }

            const btn = document.getElementById('generateBtn');
            const originalHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating...';

            fetch('{{ route("id-cards.generate") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF
                },
                body: JSON.stringify({ senior, stream })
            })
                .then(r => r.json())
                .then(data => {
                    Swal.fire({
                        title: data.status === 'success' ? 'Generation Complete!' : 'Generation Issue',
                        html: data.message,
                        icon: data.status === 'success' ? 'success' : 'warning',
                        confirmButtonColor: '#2f2ccb'
                    }).then(() => {
                        if (data.status === 'success') {
                            window.location.href = '{{ route("id-cards.index") }}';
                        } else {
                            btn.disabled = false;
                            btn.innerHTML = originalHtml;
                        }
                    });
                })
                .catch(err => {
                    console.error('Generate error:', err);
                    showToast('Server error. Please try again.', 'error');
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                });
        }

        // Search students for single generation
        // Search students for single generation
        let searchTimeout;

        function searchStudents() {
            clearTimeout(searchTimeout);
            const query = document.getElementById('studentSearch').value.trim();

            if (query.length < 2) {
                document.getElementById('studentSearchResults').style.display = 'none';
                return;
            }

            searchTimeout = setTimeout(() => {
                fetch(`{{ route("id-cards.search.students") }}?q=${encodeURIComponent(query)}`)
                    .then(r => r.json())
                    .then(students => {
                        const container = document.getElementById('studentSearchResults');
                        const tbody = document.getElementById('searchResultsBody');

                        if (!students.length) {
                            tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:1rem;">No students found</td></tr>';
                        } else {
                            tbody.innerHTML = students.map(s => {
                                // Determine status badge HTML
                                let statusHtml = '';
                                let buttonHtml = '';

                                if (s.card_status === 'active') {
                                    statusHtml = '<span style="background: var(--gl); color: var(--g); padding: .2rem .5rem; border-radius: 999px; font-size: .7rem; font-weight: 600;"><i class="fas fa-check-circle"></i> Active</span>';
                                    buttonHtml = `<button class="btn btn-sm" style="background:var(--g);color:#fff;padding:.25rem .6rem;font-size:.7rem;" onclick="viewCard(${s.card_id})">
                                                <i class="fas fa-eye"></i> View Card
                                            </button>`;
                                } else if (s.card_status === 'revoked') {
                                    statusHtml = '<span style="background: var(--rl); color: var(--r); padding: .2rem .5rem; border-radius: 999px; font-size: .7rem; font-weight: 600;"><i class="fas fa-ban"></i> Revoked</span>';
                                    buttonHtml = `<button class="btn btn-sm" style="background:var(--a);color:#fff;padding:.25rem .6rem;font-size:.7rem;" onclick="reactivateCard(${s.card_id}, '${escapeHtml(s.firstname).replace(/'/g, "\\'")} ${escapeHtml(s.lastname).replace(/'/g, "\\'")}')">
                                                <i class="fas fa-sync-alt"></i> Reactivate Card
                                            </button>`;
                                } else if (s.card_status === 'expired') {
                                    statusHtml = '<span style="background: var(--al); color: var(--a); padding: .2rem .5rem; border-radius: 999px; font-size: .7rem; font-weight: 600;"><i class="fas fa-clock"></i> Expired</span>';
                                    buttonHtml = `<button class="btn btn-sm btn-primary" style="padding:.25rem .6rem;font-size:.7rem;" onclick="generateSingle(${s.id}, '${escapeHtml(s.firstname).replace(/'/g, "\\'")} ${escapeHtml(s.lastname).replace(/'/g, "\\'")}')">
                                                <i class="fas fa-plus"></i> Generate New
                                            </button>`;
                                } else {
                                    statusHtml = '<span style="background: #e2e8f0; color: #64748b; padding: .2rem .5rem; border-radius: 999px; font-size: .7rem; font-weight: 600;"><i class="fas fa-id-card"></i> No Card</span>';
                                    buttonHtml = `<button class="btn btn-sm btn-primary" style="padding:.25rem .6rem;font-size:.7rem;" onclick="generateSingle(${s.id}, '${escapeHtml(s.firstname).replace(/'/g, "\\'")} ${escapeHtml(s.lastname).replace(/'/g, "\\'")}')">
                                                <i class="fas fa-magic"></i> Generate Card
                                            </button>`;
                                }

                                return `
                                <tr>
                                    <td style="padding: .5rem .75rem;">${escapeHtml(s.admission_number || '—')}</td>
                                    <td style="padding: .5rem .75rem;">
                                        <div style="display:flex;align-items:center;gap:.4rem;">
                                            <div style="width:28px;height:28px;border-radius:50%;background:var(--bl);color:var(--b);display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:bold;">
                                                ${escapeHtml((s.firstname?.charAt(0) || 'S') + (s.lastname?.charAt(0) || 'T')).toUpperCase()}
                                            </div>
                                            <span>${escapeHtml(s.firstname)} ${escapeHtml(s.lastname)}</span>
                                        </div>
                                    </td>
                                    <td style="padding: .5rem .75rem;">${escapeHtml(s.class_name || s.senior || '—')}</td>
                                    <td style="padding: .5rem .75rem;">${escapeHtml(s.stream || '—')}</td>
                                    <td style="padding: .5rem .75rem;">${statusHtml}</td>
                                    <td style="padding: .5rem .75rem;">${buttonHtml}</td>
                                </tr>
                            `;
                            }).join('');
                        }
                        container.style.display = 'block';
                    })
                    .catch((err) => {
                        console.error('Search error:', err);
                        const tbody = document.getElementById('searchResultsBody');
                        tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:1rem;">Error searching students</td></tr>';
                        document.getElementById('studentSearchResults').style.display = 'block';
                    });
            }, 300);
        }

// Reactivate a revoked card
function reactivateCard(cardId, studentName) {
    Swal.fire({
        title: 'Reactivate ID Card?',
        text: `Reactivate the ID card for ${studentName}? This will make the card valid again.`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#d97706',
        confirmButtonText: 'Yes, reactivate',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Reactivating...',
                text: 'Please wait',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            fetch(`/student-id-cards/reactivate/${cardId}`, {
                method: 'PATCH',  // Make sure this matches the route method
                headers: {
                    'X-CSRF-TOKEN': CSRF,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            .then(async response => {
                const data = await response.json();
                
                if (!response.ok) {
                    throw { 
                        status: response.status, 
                        message: data.message || data.error || `HTTP ${response.status}: ${response.statusText}`,
                        details: data
                    };
                }
                return data;
            })
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire({
                        title: 'Reactivated!',
                        text: data.message,
                        icon: 'success',
                        confirmButtonColor: '#2f2ccb'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'Error',
                        text: data.message || 'Failed to reactivate ID card',
                        icon: 'error',
                        confirmButtonColor: '#dc2626'
                    });
                }
            })
            .catch((error) => {
                console.error('Reactivate error:', error);
                
                let errorMessage = 'Failed to reactivate ID card';
                
                if (error.message) {
                    errorMessage = error.message;
                }
                
                Swal.fire({
                    title: 'Error',
                    html: `<div style="text-align: left;">
                        <p><strong>${errorMessage}</strong></p>
                        ${error.details && error.details.message ? `<p style="margin-top: 8px; color: #dc2626;">${error.details.message}</p>` : ''}
                        ${error.details && error.details.exception ? `<pre style="background: #f8f9fa; padding: 8px; border-radius: 6px; font-size: 11px; overflow-x: auto; margin-top: 10px;">${error.details.exception}</pre>` : ''}
                    </div>`,
                    icon: 'error',
                    confirmButtonColor: '#dc2626',
                    width: '600px'
                });
            });
        }
    });
}

        // View existing active card
        function viewCard(cardId) {
            window.open(`/student-id-cards/preview/${cardId}`, '_blank');
        }

        function generateSingle(studentId, studentName) {
            Swal.fire({
                title: 'Generate ID Card?',
                text: `Generate a new ID card for ${studentName}? This will revoke any existing active card.`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#2f2ccb',
                confirmButtonText: 'Yes, generate',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Generating...',
                        text: 'Please wait',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading()
                    });

                    fetch('{{ route("id-cards.generate.single") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': CSRF
                        },
                        body: JSON.stringify({ student_id: studentId })
                    })
                        .then(r => r.json())
                        .then(data => {
                            if (data.status === 'success') {
                                Swal.fire({
                                    title: 'Success!',
                                    text: data.message,
                                    icon: 'success',
                                    confirmButtonColor: '#2f2ccb'
                                }).then(() => {
                                    window.location.href = '{{ route("id-cards.index") }}';
                                });
                            } else {
                                Swal.fire('Error', data.message, 'error');
                            }
                        })
                        .catch((err) => {
                            console.error('Generate single error:', err);
                            Swal.fire('Error', 'Failed to generate ID card', 'error');
                        });
                }
            });
        }

        // Clear search results when clicking outside
        document.addEventListener('click', function (e) {
            const searchInput = document.getElementById('studentSearch');
            const results = document.getElementById('studentSearchResults');
            if (searchInput && results && !searchInput.contains(e.target) && !results.contains(e.target)) {
                results.style.display = 'none';
            }
        });
    </script>
@endsection