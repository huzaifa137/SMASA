{{-- resources/views/teacher/id-cards/create.blade.php --}}
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
            --r: #dc2626;
            --rl: rgba(220, 38, 38, .10);
            --g: #059669;
            --gl: rgba(5, 150, 105, .10);
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
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }

        *:not(i):not([class*="fa"]) {
            font-family: 'DM Sans', sans-serif;
        }

        body {
            background: var(--bg);
        }

        /* Hero Section - Blue Gradient */
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

        /* Grid Layout */
        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }

        @media(max-width: 768px) {
            .grid-2 {
                grid-template-columns: 1fr;
            }
        }

        /* Panel / Card */
        .panel {
            background: var(--surf);
            border-radius: var(--rad);
            box-shadow: var(--sh);
            overflow: hidden;
        }

        .panel-head {
            padding: 1.2rem 1.5rem;
            border-bottom: 1px solid var(--brd);
        }

        .panel-title {
            font-size: 1rem;
            font-weight: 700;
            color: var(--t1);
        }

        .panel-body {
            padding: 1.5rem;
        }

        /* Form Elements */
        .form-label {
            font-size: .8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: var(--t3);
            margin-bottom: .4rem;
            display: block;
        }

        .form-control {
            width: 100%;
            padding: .65rem .9rem;
            border: 1.5px solid var(--brd);
            border-radius: 10px;
            font-size: .9rem;
            color: var(--t1);
            outline: none;
            transition: .15s;
            background: var(--surf);
        }

        .form-control:focus {
            border-color: var(--b);
            box-shadow: 0 0 0 3px var(--bl);
        }

        .mb-4 {
            margin-bottom: 1rem;
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            padding: .6rem 1.3rem;
            border-radius: 10px;
            font-size: .9rem;
            font-weight: 600;
            cursor: pointer;
            border: none;
            text-decoration: none;
            transition: .15s;
        }

        .btn-primary,
        .btn-teal {
            background: var(--b);
            color: #fff;
            width: 100%;
            justify-content: center;
        }

        .btn-primary:hover,
        .btn-teal:hover {
            background: var(--b2);
        }

        .btn-outline {
            background: transparent;
            color: var(--b);
            border: 1.5px solid var(--b);
            width: 100%;
            justify-content: center;
        }

        .btn-outline:hover {
            background: var(--bl);
        }

        /* Search Input */
        .search-input-wrap {
            position: relative;
        }

        .search-input-wrap input {
            padding-left: 2.5rem;
        }

        .search-input-wrap .search-icon {
            position: absolute;
            left: .85rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--t3);
        }

        /* Search Results */
        .search-results {
            border: 1.5px solid var(--brd);
            border-radius: 12px;
            margin-top: .5rem;
            overflow: hidden;
            display: none;
        }

        .search-result-item {
            padding: .75rem 1rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            border-bottom: 1px solid var(--brd);
            cursor: pointer;
            transition: .1s;
        }

        .search-result-item:last-child {
            border-bottom: none;
        }

        .search-result-item:hover {
            background: var(--bg);
        }

        .result-info {
            flex: 1;
        }

        .result-name {
            font-weight: 700;
            font-size: .9rem;
            color: var(--t1);
        }

        .result-meta {
            font-size: .75rem;
            color: var(--t3);
        }

        /* Badges */
        .badge-sm {
            padding: .2rem .55rem;
            border-radius: 999px;
            font-size: .68rem;
            font-weight: 700;
        }

        .badge-active {
            background: var(--gl);
            color: var(--g);
        }

        .badge-revoked {
            background: var(--rl);
            color: var(--r);
        }

        .badge-expired {
            background: var(--al);
            color: var(--a);
        }

        /* Mini Buttons */
        .btn-mini {
            padding: .3rem .7rem;
            border-radius: 8px;
            font-size: .75rem;
            border: none;
            cursor: pointer;
            font-weight: 600;
        }

        .btn-gen {
            background: var(--b);
            color: #fff;
        }

        .btn-gen:hover {
            background: var(--b2);
        }

        .btn-view {
            background: var(--gl);
            color: var(--g);
        }

        .btn-view:hover {
            background: var(--g);
            color: #fff;
        }

        .btn-react {
            background: var(--al);
            color: var(--a);
        }

        .btn-react:hover {
            background: var(--a);
            color: #fff;
        }

        /* Bulk Info Section */
/* Bulk Info Section - Updated for better text flow */
.bulk-info {
    background: var(--bl);
    border: 1.5px solid rgba(47, 44, 203, .2);
    border-radius: 12px;
    padding: 1rem 1.2rem;
    margin-bottom: 1.25rem;
    font-size: .88rem;
    color: var(--b);
    display: block;  /* Changed from flex to block */
}

.bulk-info i {
    display: inline-block;
    margin-right: 0.6rem;
    font-size: 1rem;
    vertical-align: middle;
}

.bulk-info strong {
    font-weight: 700;
    color: var(--b);
}

/* Alternative: If you want to keep flex but allow text wrapping */
.bulk-info-flex {
    background: var(--bl);
    border: 1.5px solid rgba(47, 44, 203, .2);
    border-radius: 12px;
    padding: 1rem 1.2rem;
    margin-bottom: 1.25rem;
    font-size: .88rem;
    color: var(--b);
    display: flex;
    align-items: flex-start;  /* Changed from center to flex-start */
    gap: .6rem;
}

.bulk-info-flex i {
    flex-shrink: 0;
    margin-top: 0.1rem;
}

.bulk-info-flex span,
.bulk-info-flex .bulk-text {
    flex: 1;
    line-height: 1.4;
}

        /* Spinner */
        .spinner {
            display: none;
            width: 18px;
            height: 18px;
            border: 2px solid rgba(255, 255, 255, .3);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin .6s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* Toast Notifications */
        .toast-stack {
            position: fixed;
            bottom: 1.5rem;
            right: 1.5rem;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: .5rem;
        }

        .toast {
            background: #1e293b;
            color: #fff;
            padding: .75rem 1.2rem;
            border-radius: 12px;
            font-size: .88rem;
            font-weight: 600;
            box-shadow: 0 8px 24px rgba(0, 0, 0, .2);
            display: flex;
            align-items: center;
            gap: .5rem;
            animation: slideIn .25s;
        }

        .toast.success {
            background: #065f46;
            border-left: 3px solid var(--g);
        }

        .toast.error {
            background: #991b1b;
            border-left: 3px solid var(--r);
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(12px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid mt-5" style="max-width:1100px;">

        <div class="fin-hero">
            <div style="position:relative;z-index:2;">
                <p class="hero-title"><i class="fas fa-id-card mr-2"></i>Generate Teacher ID Cards</p>
                <p class="hero-sub">Generate individual or bulk ID cards for all staff</p>
            </div>
        </div>

        <div class="grid-2">

            {{-- Bulk Generation --}}
            <div class="panel">
                <div class="panel-head">
                    <div class="panel-title"><i class="fas fa-users mr-2" style="color:var(--b);"></i>Bulk Generation</div>
                </div>
                <div class="panel-body">
                    <div class="bulk-info">
                        <i class="fas fa-info-circle"></i>
                        This will generate ID cards for <strong>all teachers</strong> in this school. Teachers who already
                        have an active card for the current academic year will be skipped.
                    </div>

                    <button class="btn btn-teal" id="bulkBtn" onclick="generateBulk()">
                        <i class="fas fa-bolt"></i>
                        <span>Generate All Teacher Cards</span>
                        <div class="spinner" id="bulkSpinner"></div>
                    </button>

                    <div style="margin-top:1rem;">
                        <a href="{{ route('teacher-id-cards.print.bulk') }}" class="btn btn-outline" target="_blank">
                            <i class="fas fa-print"></i> Print All Active Cards
                        </a>
                    </div>
                </div>
            </div>

            {{-- Individual Search --}}
            <div class="panel">
                <div class="panel-head">
                    <div class="panel-title"><i class="fas fa-user-plus mr-2" style="color:var(--b);"></i>Individual Teacher
                    </div>
                </div>
                <div class="panel-body">
                    <div class="mb-4">
                        <label class="form-label">Search Teacher</label>
                        <div class="search-input-wrap">
                            <i class="fas fa-search search-icon"></i>
                            <input type="text" class="form-control" id="teacherSearch"
                                placeholder="Name, employee no, phone…" autocomplete="off">
                        </div>
                        <div class="search-results" id="searchResults"></div>
                    </div>
                    <p style="font-size:.82rem;color:var(--t3);text-align:center;margin-top:2rem;">
                        <i class="fas fa-keyboard mr-1"></i>Type at least 2 characters to search
                    </p>
                </div>
            </div>
        </div>
    </div>
    </div>
    </div>
    </div>

    <div class="toast-stack" id="toastStack"></div>
@endsection

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const CSRF = '{{ csrf_token() }}';

        // ── Bulk generate ──────────────────────────────────────────────────────
        function generateBulk() {
            const btn = document.getElementById('bulkBtn');
            const sp = document.getElementById('bulkSpinner');
            btn.disabled = true; sp.style.display = 'block';

            fetch('{{ route("teacher-id-cards.generate") }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json' },
                body: JSON.stringify({})
            })
                .then(r => r.json())
                .then(d => {
                    btn.disabled = false; sp.style.display = 'none';
                    Swal.fire({
                        icon: d.status === 'success' ? 'success' : 'error',
                        title: d.status === 'success' ? 'Done!' : 'Error',
                        text: d.message,
                        confirmButtonColor: '#0f766e'
                    });
                })
                .catch(() => { btn.disabled = false; sp.style.display = 'none'; toast('Network error', 'error'); });
        }

        // ── Teacher search ─────────────────────────────────────────────────────
        let searchTimer;
        document.getElementById('teacherSearch').addEventListener('input', function () {
            clearTimeout(searchTimer);
            const q = this.value.trim();
            const box = document.getElementById('searchResults');
            if (q.length < 2) { box.style.display = 'none'; return; }
            searchTimer = setTimeout(() => {
                fetch(`{{ route("teacher-id-cards.search.teachers") }}?q=${encodeURIComponent(q)}`)
                    .then(r => r.json())
                    .then(data => renderTeacherResults(data));
            }, 280);
        });

        function renderTeacherResults(data) {
            const box = document.getElementById('searchResults');
            if (!data.length) {
                box.innerHTML = '<div style="padding:1rem;text-align:center;color:#94a3b8;font-size:.88rem;">No teachers found</div>';
                box.style.display = 'block';
                return;
            }

            box.innerHTML = data.map(t => {
                let badge = '';
                let actionBtn = '';

                if (t.button_type === 'active') {
                    badge = `<span class="badge-sm badge-active">Active</span>`;
                    actionBtn = `<button class="btn-mini btn-view" onclick="openPreviewById(${t.card_id})"><i class="fas fa-eye"></i> View</button>`;
                } else if (t.button_type === 'reactivate') {
                    badge = `<span class="badge-sm badge-revoked">Revoked</span>`;
                    actionBtn = `<button class="btn-mini btn-react" onclick="reactivateCard(${t.card_id})"><i class="fas fa-redo"></i> Reactivate</button>`;
                } else if (t.button_type === 'expired') {
                    badge = `<span class="badge-sm badge-expired">Expired</span>`;
                    actionBtn = `<button class="btn-mini btn-gen" onclick="generateSingle(${t.id})"><i class="fas fa-redo"></i> Renew</button>`;
                } else {
                    actionBtn = `<button class="btn-mini btn-gen" onclick="generateSingle(${t.id})"><i class="fas fa-bolt"></i> Generate</button>`;
                }

                return `
                    <div class="search-result-item">
                        <div class="result-info">
                            <div class="result-name">${t.firstname} ${t.surname} ${badge}</div>
                            <div class="result-meta">Emp: ${t.employee_number} &bull; Tel: ${t.phonenumber}</div>
                        </div>
                        ${actionBtn}
                    </div>`;
            }).join('');

            box.style.display = 'block';
        }

        function generateSingle(teacherId) {
            fetch('{{ route("teacher-id-cards.generate.single") }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json' },
                body: JSON.stringify({ teacher_id: teacherId })
            })
                .then(r => r.json())
                .then(d => {
                    toast(d.message, d.status === 'success' ? 'success' : 'error');
                    if (d.status === 'success') {
                        document.getElementById('teacherSearch').value = '';
                        document.getElementById('searchResults').style.display = 'none';
                        if (d.card_id) setTimeout(() => openPreviewById(d.card_id), 600);
                    }
                });
        }

        function openPreviewById(cardId) {
            window.location.href = `{{ url('teacher-id-cards/preview') }}/${cardId}`;
        }

        function reactivateCard(cardId) {
            fetch(`/teacher-id-cards/reactivate/${cardId}`, {
                method: 'PATCH',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json' }
            })
                .then(r => r.json())
                .then(d => {
                    toast(d.message, d.status === 'success' ? 'success' : 'error');
                    if (d.status === 'success') {
                        document.getElementById('teacherSearch').dispatchEvent(new Event('input'));
                    }
                });
        }

        // Toast helper
        function toast(msg, type = 'success') {
            const stack = document.getElementById('toastStack');
            const el = document.createElement('div');
            el.className = `toast ${type}`;
            el.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i> ${msg}`;
            stack.appendChild(el);
            setTimeout(() => el.remove(), 4000);
        }

        document.addEventListener('click', function (e) {
            if (!e.target.closest('#teacherSearch') && !e.target.closest('#searchResults')) {
                document.getElementById('searchResults').style.display = 'none';
            }
        });
    </script>
@endsection