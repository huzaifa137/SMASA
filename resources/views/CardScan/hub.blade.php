{{-- resources/views/CardScan/hub.blade.php --}}
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
            --bl: rgba(47, 44, 203, .1);
            --g: #059669;
            --gl: rgba(5, 150, 105, .1);
            --r: #dc2626;
            --rl: rgba(220, 38, 38, .1);
            --a: #d97706;
            --al: rgba(217, 119, 6, .1);
            --surf: #fff;
            --bg: #f0f4f8;
            --brd: #e2e8f0;
            --t1: #0f172a;
            --t2: #475569;
            --t3: #94a3b8;
            --rad: 16px;
            --sh: 0 1px 3px rgba(0, 0, 0, .06), 0 4px 16px rgba(0, 0, 0, .05);
            --sh-lg: 0 10px 40px rgba(0, 0, 0, .12);
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box
        }

        *:not(i):not([class*="fa"]) {
            font-family: 'DM Sans', sans-serif
        }

        body {
            background: var(--bg)
        }

        .scan-hero {
            background: linear-gradient(135deg, #0d0b5e 0%, #2f2ccb 50%, #5b58e0 100%);
            border-radius: 24px;
            padding: 2rem 2.5rem;
            margin-bottom: 1.75rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .hero-title {
            color: #fff;
            font-size: 1.6rem;
            font-weight: 700;
            margin: 0 0 .3rem
        }

        .hero-sub {
            color: rgba(255, 255, 255, .7);
            font-size: .9rem;
            margin: 0
        }

        .hero-badge {
            background: rgba(255, 255, 255, .15);
            color: #fff;
            padding: .35rem .8rem;
            border-radius: 20px;
            font-size: .8rem;
            font-weight: 600;
            backdrop-filter: blur(6px)
        }

        .main-grid {
            display: grid;
            grid-template-columns: 1fr 1.4fr;
            gap: 1.5rem
        }

        @media(max-width:1000px) {
            .main-grid {
                grid-template-columns: 1fr
            }
        }

        .panel {
            background: var(--surf);
            border-radius: var(--rad);
            box-shadow: var(--sh);
            padding: 1.5rem
        }

        .panel-title {
            font-size: .95rem;
            font-weight: 700;
            color: var(--t1);
            margin-bottom: 1.25rem;
            display: flex;
            align-items: center;
            gap: .5rem
        }

        .panel-title i {
            color: var(--b)
        }

        /* Category grid */
        .cat-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: .65rem
        }

        .cat-btn {
            border: 2px solid var(--brd);
            border-radius: 12px;
            padding: .85rem;
            cursor: pointer;
            transition: .15s;
            background: var(--surf);
            text-align: left;
            position: relative;
        }

        .cat-btn:hover {
            transform: translateY(-1px);
            box-shadow: var(--sh)
        }

        .cat-btn.active {
            border-color: var(--cat-color, var(--b));
            background: color-mix(in srgb, var(--cat-color, var(--b)) 8%, #fff)
        }

        .cat-btn .cat-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: color-mix(in srgb, var(--cat-color, var(--b)) 15%, #fff);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--cat-color, var(--b));
            font-size: 1rem;
            margin-bottom: .5rem;
        }

        .cat-btn .cat-label {
            font-size: .83rem;
            font-weight: 700;
            color: var(--t1)
        }

        .cat-btn .cat-desc {
            font-size: .7rem;
            color: var(--t2);
            margin-top: .2rem;
            line-height: 1.3
        }

        /* Scanner area */
        .scanner-wrap {
            border: 2px dashed var(--brd);
            border-radius: 12px;
            overflow: hidden;
            min-height: 200px
        }

        #reader {
            width: 100%
        }

        .input-row {
            display: flex;
            gap: .5rem;
            margin-top: .75rem
        }

        .input-row input {
            flex: 1;
            padding: .6rem .9rem;
            border: 1.5px solid var(--brd);
            border-radius: 8px;
            font-size: .9rem;
            color: var(--t1);
            background: var(--bg);
        }

        .input-row input:focus {
            outline: none;
            border-color: var(--b)
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            padding: .6rem 1.1rem;
            border-radius: 8px;
            font-size: .88rem;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: .15s;
        }

        .btn-primary {
            background: var(--b);
            color: #fff
        }

        .btn-primary:hover {
            background: #2420a8
        }

        .btn-success {
            background: var(--g);
            color: #fff
        }

        .btn-danger {
            background: var(--r);
            color: #fff
        }

        .btn-sm {
            padding: .35rem .75rem;
            font-size: .8rem
        }

        .btn-outline {
            background: transparent;
            border: 1.5px solid var(--brd);
            color: var(--t2)
        }

        .btn-outline:hover {
            border-color: var(--b);
            color: var(--b)
        }

        /* Result panel */
        .result-empty {
            text-align: center;
            padding: 3rem 1rem;
            color: var(--t3);
            font-size: .9rem
        }

        .result-empty i {
            font-size: 3rem;
            opacity: .3;
            display: block;
            margin-bottom: .75rem
        }

        .result-card {
            border-radius: 12px;
            overflow: hidden;
            border: 2px solid var(--brd)
        }

        .result-header {
            padding: .85rem 1rem;
            display: flex;
            align-items: center;
            gap: .6rem;
            font-weight: 700;
            font-size: .9rem
        }

        .result-header.success {
            background: var(--gl);
            color: var(--g)
        }

        .result-header.failed {
            background: var(--rl);
            color: var(--r)
        }

        .result-body {
            padding: 1.25rem;
            background: #fff
        }

        .person-row {
            display: flex;
            align-items: center;
            gap: .9rem;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--brd)
        }

        .person-photo {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--brd)
        }

        .person-photo-placeholder {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: var(--bl);
            color: var(--b);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .person-name {
            font-size: 1.05rem;
            font-weight: 700;
            color: var(--t1)
        }

        .person-meta {
            font-size: .8rem;
            color: var(--t2);
            margin-top: .15rem
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: .6rem
        }

        .info-item .lbl {
            font-size: .68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: var(--t3)
        }

        .info-item .val {
            font-size: .88rem;
            font-weight: 600;
            color: var(--t1);
            margin-top: .1rem
        }

        .badge-status {
            display: inline-block;
            padding: .2rem .55rem;
            border-radius: 6px;
            font-size: .72rem;
            font-weight: 700;
        }

        .badge-success {
            background: var(--gl);
            color: var(--g)
        }

        .badge-danger {
            background: var(--rl);
            color: var(--r)
        }

        .badge-warning {
            background: var(--al);
            color: var(--a)
        }

        .badge-info {
            background: rgba(14, 165, 233, .1);
            color: #0284c7
        }

        /* Action panel inside result */
        .action-panel {
            margin-top: 1rem;
            border-radius: 10px;
            background: var(--bg);
            padding: 1rem;
            border: 1px solid var(--brd);
        }

        .action-panel .ap-title {
            font-size: .82rem;
            font-weight: 700;
            color: var(--t1);
            margin-bottom: .65rem
        }

        .book-search-wrap input {
            width: 100%;
            padding: .55rem .8rem;
            border: 1.5px solid var(--brd);
            border-radius: 8px;
            font-size: .85rem;
            margin-bottom: .5rem;
        }

        .book-results-list {
            max-height: 200px;
            overflow-y: auto
        }

        .book-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: .5rem .7rem;
            border-radius: 8px;
            background: #fff;
            border: 1px solid var(--brd);
            margin-bottom: .35rem;
            cursor: pointer;
            font-size: .82rem;
        }

        .book-item:hover {
            border-color: var(--g);
            background: var(--gl)
        }

        .borrow-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: .45rem .7rem;
            border-radius: 8px;
            background: #fff;
            border: 1px solid var(--brd);
            margin-bottom: .35rem;
            font-size: .82rem;
        }

        .borrow-row.overdue {
            border-color: var(--r);
            background: var(--rl)
        }

        /* Recent scans */
        .scan-list {
            display: flex;
            flex-direction: column;
            gap: .4rem
        }

        .scan-item {
            display: flex;
            align-items: center;
            gap: .6rem;
            padding: .55rem .8rem;
            border-radius: 8px;
            font-size: .82rem;
            border: 1px solid var(--brd);
            background: #fff;
        }

        .scan-item .si-icon {
            width: 30px;
            height: 30px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .82rem;
            flex-shrink: 0;
        }

        .scan-item .si-name {
            flex: 1;
            font-weight: 600;
            color: var(--t1)
        }

        .scan-item .si-time {
            font-size: .7rem;
            color: var(--t3)
        }

        .scan-item .si-cat {
            font-size: .7rem;
            color: var(--t2)
        }

        .scan-item.ok .si-icon {
            background: var(--gl);
            color: var(--g)
        }

        .scan-item.bad .si-icon {
            background: var(--rl);
            color: var(--r)
        }

        /* Loading overlay */
        .scan-loading {
            position: absolute;
            inset: 0;
            background: rgba(255, 255, 255, .85);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 10;
            border-radius: 12px;
            flex-direction: column;
            gap: .5rem;
        }

        .spin {
            width: 36px;
            height: 36px;
            border: 3px solid var(--brd);
            border-top-color: var(--b);
            border-radius: 50%;
            animation: spin .7s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg)
            }
        }

        /* Stats row */
        .stat-chips {
            display: flex;
            flex-wrap: wrap;
            gap: .5rem;
            margin-bottom: 1.25rem
        }

        .stat-chip {
            background: var(--bg);
            border: 1px solid var(--brd);
            border-radius: 8px;
            padding: .35rem .75rem;
            font-size: .8rem;
            font-weight: 600;
            color: var(--t2);
            display: flex;
            align-items: center;
            gap: .35rem;
        }

        .stat-chip i {
            font-size: .75rem
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid mt-5" style="max-width:1400px;">

        {{-- Hero --}}
        <div class="scan-hero">
            <div>
                <h2 class="hero-title"><i class="fas fa-qrcode me-2"></i> Card Scan Hub</h2>
                <p class="hero-sub">Scan student or teacher ID cards to take attendance, manage library, check fees, and
                    more.</p>
            </div>
            <div style="display:flex;gap:.5rem;flex-wrap:wrap;align-items:center">
                <span class="hero-badge"><i class="fas fa-chart-bar me-1"></i> {{ $todayTotal }} scans today</span>
                <a href="{{ route('card-scan.logs') }}" class="btn btn-outline"
                    style="background:rgba(255,255,255,.15);color:#fff;border-color:rgba(255,255,255,.3)">
                    <i class="fas fa-history"></i> View Logs
                </a>
                <a href="{{ route('card-scan.arrival') }}" class="btn btn-outline"
                    style="background:rgba(255,255,255,.15);color:#fff;border-color:rgba(255,255,255,.3)">
                    <i class="fas fa-school"></i> Arrival Attendance
                </a>
            </div>
        </div>

        {{-- Today Stats Chips --}}
        <div class="stat-chips">
            @foreach($categories as $cat)
                @php $s = $todayStats[$cat['key']] ?? null; @endphp
                @if($s && $s->total > 0)
                    <span class="stat-chip" style="border-color:{{ $cat['color'] }}22;color:{{ $cat['color'] }}">
                        <i class="fas {{ $cat['icon'] }}"></i> {{ $cat['label'] }}: {{ $s->total }}
                    </span>
                @endif
            @endforeach
            @if($todayTotal === 0)
                <span class="stat-chip"><i class="fas fa-info-circle"></i> No scans yet today</span>
            @endif
        </div>

        <div class="main-grid">
            {{-- LEFT: Category + Scanner --}}
            <div>
                {{-- Scan Category Selection --}}
                <div class="panel" style="margin-bottom:1rem">
                    <div class="panel-title"><i class="fas fa-th-large"></i> Select Scan Category</div>
                    <div class="cat-grid">
                        @foreach($categories as $cat)
                            <button class="cat-btn {{ $loop->first ? 'active' : '' }}" style="--cat-color:{{ $cat['color'] }}"
                                data-cat="{{ $cat['key'] }}" onclick="selectCategory('{{ $cat['key'] }}', this)">
                                <div class="cat-icon"><i class="fas {{ $cat['icon'] }}"></i></div>
                                <div class="cat-label">{{ $cat['label'] }}</div>
                                <div class="cat-desc">{{ $cat['desc'] }}</div>
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- Camera Scanner --}}
                <div class="panel" style="margin-bottom:1rem;position:relative">
                    <div class="panel-title"><i class="fas fa-camera"></i> Camera Scanner</div>
                    <div class="scanner-wrap">
                        <div id="reader"></div>
                    </div>
                    <div style="display:flex;gap:.5rem;margin-top:.75rem;justify-content:center">
                        <button class="btn btn-primary" onclick="startScanner()" id="btnStart">
                            <i class="fas fa-play"></i> Start Camera
                        </button>
                        <button class="btn btn-danger" id="btnStop" style="display:none" onclick="stopScanner()">
                            <i class="fas fa-stop"></i> Stop
                        </button>
                    </div>
                    <div class="scan-loading" id="scanLoading">
                        <div class="spin"></div>
                        <span style="font-size:.85rem;font-weight:600;color:var(--t2)">Processing scan…</span>
                    </div>
                </div>

                {{-- Manual Entry --}}
                <div class="panel">
                    <div class="panel-title"><i class="fas fa-keyboard"></i> Manual Card Entry</div>
                    <div class="input-row">
                        <input type="text" id="manualInput" placeholder="Enter card number e.g. ID-XYZ-1-…"
                            onkeydown="if(event.key==='Enter') triggerScan(document.getElementById('manualInput').value)">
                        <button class="btn btn-primary" onclick="triggerScan(document.getElementById('manualInput').value)">
                            <i class="fas fa-search"></i> Scan
                        </button>
                    </div>
                </div>
            </div>

            {{-- RIGHT: Result + Recent --}}
            <div>
                {{-- Result Panel --}}
                <div class="panel" style="margin-bottom:1rem;position:relative">
                    <div class="panel-title"><i class="fas fa-poll-h"></i> Scan Result</div>
                    <div id="resultEmpty" class="result-empty">
                        <i class="fas fa-id-card"></i>
                        Select a category and scan a card to see results here.
                    </div>
                    <div id="resultContent" style="display:none"></div>
                    <div class="scan-loading" id="resultLoading">
                        <div class="spin"></div>
                        <span style="font-size:.85rem;font-weight:600;color:var(--t2)">Loading…</span>
                    </div>
                </div>

                {{-- Recent Scans --}}
                <div class="panel">
                    <div class="panel-title"><i class="fas fa-history"></i> Recent Scans</div>
                    <div id="recentList">
                        @if($recentScans->isEmpty())
                            <div class="result-empty" style="padding:1.5rem">
                                <i class="fas fa-clock"></i> No scans yet.
                            </div>
                        @else
                            <div class="scan-list">
                                @foreach($recentScans->take(10) as $scan)
                                    <div class="scan-item {{ $scan->scan_result === 'success' ? 'ok' : 'bad' }}">
                                        <div class="si-icon">
                                            <i class="fas fa-{{ $scan->scan_result === 'success' ? 'check' : 'times' }}"></i>
                                        </div>
                                        <div style="flex:1;min-width:0">
                                            <div class="si-name">{{ Str::limit($scan->result_message, 55) }}</div>
                                            <div class="si-cat">{{ \App\Models\CardScanLog::categoryLabel($scan->scan_category) }}
                                            </div>
                                        </div>
                                        <div class="si-time">{{ $scan->created_at->format('h:i A') }}</div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
            </div>
    </div>
@endsection

@section('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.8/html5-qrcode.min.js"></script>
    <script>
        let html5QrcodeScanner = null;
        let selectedCategory = '{{ $categories[0]["key"] ?? "attendance_arrival" }}';
        let lastScannedCard = null;
        let scanCooldown = false;
        const csrfToken = '{{ csrf_token() }}';

        // ──────────────────────────────────────────────
        //  Category selection
        // ──────────────────────────────────────────────
        function selectCategory(key, btn) {
            selectedCategory = key;
            document.querySelectorAll('.cat-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            // Clear result when category changes
            document.getElementById('resultEmpty').style.display = 'block';
            document.getElementById('resultContent').style.display = 'none';
            document.getElementById('resultContent').innerHTML = '';
        }

        // ──────────────────────────────────────────────
        //  Camera Scanner
        // ──────────────────────────────────────────────
        function startScanner() {
            document.getElementById('btnStart').style.display = 'none';
            document.getElementById('btnStop').style.display = 'inline-flex';
            html5QrcodeScanner = new Html5QrcodeScanner('reader', {
                fps: 10,
                qrbox: { width: 250, height: 250 },
                rememberLastUsedCamera: true,
                supportedScanTypes: [Html5QrcodeScanType.SCAN_TYPE_CAMERA]
            });
            html5QrcodeScanner.render((decodedText) => {
                if (scanCooldown) return;
                scanCooldown = true;
                setTimeout(() => scanCooldown = false, 3000);
                triggerScan(decodedText);
            }, () => { });
        }

        function stopScanner() {
            if (html5QrcodeScanner) { html5QrcodeScanner.clear(); html5QrcodeScanner = null; }
            document.getElementById('btnStart').style.display = 'inline-flex';
            document.getElementById('btnStop').style.display = 'none';
            document.getElementById('reader').innerHTML = '';
        }

        // ──────────────────────────────────────────────
        //  Core: send scan to server
        // ──────────────────────────────────────────────
        function triggerScan(rawCard) {
            rawCard = (rawCard || '').trim();
            if (!rawCard) { alert('Please enter or scan a card number.'); return; }

            document.getElementById('scanLoading').style.display = 'flex';
            document.getElementById('resultLoading').style.display = 'flex';
            document.getElementById('resultEmpty').style.display = 'none';
            document.getElementById('resultContent').style.display = 'none';

            fetch('{{ route("card-scan.scan") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify({ card_number: rawCard, category: selectedCategory })
            })
                .then(r => r.json())
                .then(data => {
                    document.getElementById('scanLoading').style.display = 'none';
                    document.getElementById('resultLoading').style.display = 'none';
                    renderResult(data);
                    addToRecentList(data, rawCard);
                    // Clear manual input
                    document.getElementById('manualInput').value = '';
                })
                .catch(err => {
                    document.getElementById('scanLoading').style.display = 'none';
                    document.getElementById('resultLoading').style.display = 'none';
                    renderResult({ success: false, result: 'failed', message: 'Network error: ' + err.message, data: {} });
                });
        }

        // ──────────────────────────────────────────────
        //  Render result
        // ──────────────────────────────────────────────
        function renderResult(resp) {
            document.getElementById('resultEmpty').style.display = 'none';
            document.getElementById('resultContent').style.display = 'block';

            const d = resp.data || {};
            let html = `
                <div class="result-card">
                    <div class="result-header ${resp.success ? 'success' : 'failed'}">
                        <i class="fas ${resp.success ? 'fa-check-circle' : 'fa-times-circle'}"></i>
                        <span>${escHtml(resp.message || '—')}</span>
                    </div>
                    <div class="result-body">`;

            // Person row (photo + name + type)
            if (d.person_name) {
                html += `<div class="person-row">`;
                if (d.photo) {
                    html += `<img src="${d.photo}" class="person-photo" alt="">`;
                } else {
                    html += `<div class="person-photo-placeholder"><i class="fas fa-user"></i></div>`;
                }
                html += `<div>
                    <div class="person-name">${escHtml(d.person_name)}</div>
                    <div class="person-meta">
                        ${d.person_type ? `<span class="badge-status badge-info">${d.person_type.toUpperCase()}</span>` : ''}
                        ${d.class ? ` &nbsp; ${escHtml(d.class)}` : ''}
                        ${d.stream ? ` / ${escHtml(d.stream)}` : ''}
                    </div>
                </div></div>`;
            }

            // Category-specific content
            const cat = resp.category || '';

            if (cat === 'attendance_arrival') {
                html += renderArrivalResult(d);
            } else if (cat === 'finance_balance') {
                html += renderBalanceResult(d);
            } else if (cat === 'finance_payment') {
                html += renderPaymentResult(d);
            } else if (cat === 'library_issue') {
                html += renderLibraryIssueResult(d);
            } else if (cat === 'library_return') {
                html += renderLibraryReturnResult(d);
            } else if (cat === 'library_reserve') {
                html += renderLibraryReserveResult(d);
            } else if (cat === 'attendance_class') {
                html += renderClassAttResult(d);
            } else {
                // info / generic
                html += renderInfoGrid(d);
            }

            html += `</div></div>`;
            document.getElementById('resultContent').innerHTML = html;
        }

        // ──────────────────────────────────────────────
        //  Sub-renderers per category
        // ──────────────────────────────────────────────
        function renderArrivalResult(d) {
            let html = `<div class="info-grid">
                <div class="info-item"><div class="lbl">Arrival Time</div><div class="val">${escHtml(d.arrival_time || '—')}</div></div>
                <div class="info-item"><div class="lbl">Status</div><div class="val">
                    <span class="badge-status ${d.status === 'present' ? 'badge-success' : d.status === 'late' ? 'badge-warning' : 'badge-danger'}">${escHtml((d.status || '').toUpperCase())}</span>
                </div></div>
                <div class="info-item"><div class="lbl">Date</div><div class="val">${escHtml(d.attendance_date || '—')}</div></div>
                <div class="info-item"><div class="lbl">Already Scanned</div><div class="val">${d.already_scanned ? 'YES (duplicate)' : 'No'}</div></div>
            </div>`;
            return html;
        }

        function renderBalanceResult(d) {
            const payStatus = d.payment_status || '';
            const badgeClass = payStatus === 'FULLY PAID' ? 'badge-success' : (payStatus === 'PARTIAL' ? 'badge-warning' : 'badge-danger');
            let html = `<div class="info-grid" style="margin-bottom:.75rem">
                <div class="info-item"><div class="lbl">Total Billed</div><div class="val">UGX ${nf(d.total_billed)}</div></div>
                <div class="info-item"><div class="lbl">Total Paid</div><div class="val">UGX ${nf(d.total_paid)}</div></div>
                <div class="info-item"><div class="lbl">Balance</div><div class="val" style="color:${d.total_balance > 0 ? 'var(--r)' : 'var(--g)'}">UGX ${nf(d.total_balance)}</div></div>
                <div class="info-item"><div class="lbl">Status</div><div class="val"><span class="badge-status ${badgeClass}">${payStatus}</span></div></div>
                <div class="info-item"><div class="lbl">Academic Year</div><div class="val">${escHtml(d.academic_year || '—')}</div></div>
            </div>`;
            if (d.allocations && d.allocations.length) {
                html += `<div style="font-size:.75rem;font-weight:700;color:var(--t3);text-transform:uppercase;margin-bottom:.4rem">By Term</div>`;
                d.allocations.forEach(a => {
                    html += `<div class="borrow-row" style="${a.balance > 0 ? 'border-color:var(--r)' : ''}">
                        <span>Term ${escHtml(a.term || '')}</span>
                        <span>Bal: UGX ${nf(a.balance)}</span>
                        <span class="badge-status ${a.status === 'paid' ? 'badge-success' : a.status === 'partial' ? 'badge-warning' : 'badge-danger'}">${(a.status || '').toUpperCase()}</span>
                    </div>`;
                });
            }
            return html;
        }

        function renderPaymentResult(d) {
            if (d.fully_paid) {
                return `<div style="text-align:center;padding:1rem;color:var(--g);font-weight:700"><i class="fas fa-check-circle fa-2x mb-2 d-block"></i>Fully Paid – No Outstanding Fees</div>`;
            }
            let html = `<div class="info-grid" style="margin-bottom:.75rem">
                <div class="info-item"><div class="lbl">Outstanding</div><div class="val" style="color:var(--r)">UGX ${nf(d.total_outstanding)}</div></div>
                <div class="info-item"><div class="lbl">Academic Year</div><div class="val">${escHtml(d.academic_year || '—')}</div></div>
            </div>`;
            html += `<div class="action-panel">
                <div class="ap-title"><i class="fas fa-cash-register me-1"></i> Proceed to Payment</div>
                <a href="${d.payment_url || '#'}" class="btn btn-success btn-sm">
                    <i class="fas fa-arrow-right"></i> Open Payment Form
                </a>
            </div>`;
            return html;
        }

        function renderLibraryIssueResult(d) {
            if (!d.action_required) return '';
            let html = `<div class="info-grid" style="margin-bottom:.75rem">
                <div class="info-item"><div class="lbl">Library Card</div><div class="val">${escHtml(d.library_card || '—')}</div></div>
                <div class="info-item"><div class="lbl">Books Left</div><div class="val">${d.books_left ?? '—'}</div></div>
                <div class="info-item"><div class="lbl">Unpaid Fines</div><div class="val" style="${d.unpaid_fines > 0 ? 'color:var(--r)' : ''}">UGX ${nf(d.unpaid_fines || 0)}</div></div>
            </div>`;
            html += `<div class="action-panel">
                <div class="ap-title"><i class="fas fa-book me-1"></i> Search Book to Issue</div>
                <div class="book-search-wrap">
                    <input type="text" id="bookSearchInput" placeholder="Type book title or ISBN…" oninput="searchBooks(this.value, ${d.member_id})">
                    <div class="book-results-list" id="bookResultsList"></div>
                </div>
            </div>`;
            return html;
        }

        function renderLibraryReturnResult(d) {
            if (!d.active_borrowings || !d.active_borrowings.length) {
                return `<div style="text-align:center;color:var(--t3);padding:1rem">No books to return.</div>`;
            }
            let html = `<div style="font-size:.78rem;font-weight:700;color:var(--t3);text-transform:uppercase;margin-bottom:.4rem">Books to Return</div>`;
            d.active_borrowings.forEach(b => {
                html += `<div class="borrow-row ${b.overdue ? 'overdue' : ''}">
                    <span>${escHtml(b.book || '—')}</span>
                    <span>Due: ${b.due_date}</span>
                    ${b.overdue ? `<span class="badge-status badge-danger">OVERDUE ${b.overdue_days}d</span>` : ''}
                    <button class="btn btn-primary btn-sm" onclick="returnBook(${b.id}, '${escHtml(b.book || '')}')">Return</button>
                </div>`;
            });
            return html;
        }

        function renderLibraryReserveResult(d) {
            let html = `<div class="info-grid" style="margin-bottom:.75rem">
                <div class="info-item"><div class="lbl">Library Card</div><div class="val">${escHtml(d.library_card || '—')}</div></div>
                <div class="info-item"><div class="lbl">Active Reservations</div><div class="val">${d.active_reservations?.length || 0}</div></div>
            </div>`;
            if (d.active_reservations && d.active_reservations.length) {
                d.active_reservations.forEach(r => {
                    html += `<div class="borrow-row">
                        <span>${escHtml(r.book || '—')}</span>
                        <span class="badge-status ${r.status === 'ready' ? 'badge-success' : 'badge-warning'}">${r.status.toUpperCase()}</span>
                        <span>${r.expiry_date}</span>
                    </div>`;
                });
            }
            html += `<div class="action-panel">
                <div class="ap-title"><i class="fas fa-bookmark me-1"></i> Search Book to Reserve</div>
                <div class="book-search-wrap">
                    <input type="text" id="reserveSearchInput" placeholder="Type book title or ISBN…" oninput="searchBooksForReserve(this.value, ${d.member_id})">
                    <div class="book-results-list" id="reserveResultsList"></div>
                </div>
            </div>`;
            return html;
        }

        function renderClassAttResult(d) {
            let html = `<div class="info-grid" style="margin-bottom:.75rem">
                <div class="info-item"><div class="lbl">Class</div><div class="val">${escHtml(d.class || '—')}</div></div>
                <div class="info-item"><div class="lbl">Stream</div><div class="val">${escHtml(d.stream || '—')}</div></div>
            </div>`;
            if (d.attendance_url) {
                html += `<div class="action-panel">
                    <div class="ap-title">Take Class Attendance</div>
                    <a href="${d.attendance_url}" class="btn btn-primary btn-sm"><i class="fas fa-clipboard-list me-1"></i>Open Attendance Form</a>
                </div>`;
            }
            return html;
        }

        function renderInfoGrid(d) {
            const fields = [
                ['Card No.', d.card_number], ['Card Status', d.card_status],
                ['Academic Year', d.academic_year], ['Issue Date', d.issue_date],
                ['Expiry Date', d.expiry_date],
            ].filter(f => f[1]);
            let html = '<div class="info-grid">';
            fields.forEach(([l, v]) => {
                html += `<div class="info-item"><div class="lbl">${l}</div><div class="val">${escHtml(v || '—')}</div></div>`;
            });
            return html + '</div>';
        }

        // ──────────────────────────────────────────────
        //  Library: Book search & actions
        // ──────────────────────────────────────────────
        let bookSearchTimer = null;
        function searchBooks(q, memberId) {
            clearTimeout(bookSearchTimer);
            if (q.length < 2) { document.getElementById('bookResultsList').innerHTML = ''; return; }
            bookSearchTimer = setTimeout(() => {
                fetch(`/library/books/search-ajax?q=${encodeURIComponent(q)}&school_id={{ session("LoggedSchool") }}`)
                    .then(r => r.json())
                    .then(books => {
                        const list = document.getElementById('bookResultsList');
                        if (!books.length) { list.innerHTML = '<div style="font-size:.8rem;color:var(--t3);padding:.4rem">No books found.</div>'; return; }
                        list.innerHTML = books.map(b => `
                            <div class="book-item" onclick="issueBook(${b.id}, '${escHtml(b.title)}', ${memberId})">
                                <span>${escHtml(b.title)} ${b.isbn ? `<span style="color:var(--t3)">(${b.isbn})</span>` : ''}</span>
                                <span style="color:${b.available_copies > 0 ? 'var(--g)' : 'var(--r)'}">
                                    ${b.available_copies > 0 ? b.available_copies + ' avail.' : 'Unavailable'}
                                </span>
                            </div>`).join('');
                    });
            }, 300);
        }

        function issueBook(bookId, bookTitle, memberId) {
            if (!confirm(`Issue "${bookTitle}" to this member?`)) return;
            fetch('/library/borrowings', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify({ book_id: bookId, member_id: memberId })
            })
                .then(r => r.json())
                .then(res => {
                    alert(res.success ? '✅ Book issued successfully!' : '❌ ' + (res.message || 'Failed'));
                    if (res.success) { document.getElementById('resultContent').innerHTML = ''; document.getElementById('resultEmpty').style.display = 'block'; }
                });
        }

        function returnBook(borrowingId, bookTitle) {
            if (!confirm(`Return "${bookTitle}"?`)) return;
            fetch(`/library/borrowings/${borrowingId}/return`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify({})
            })
                .then(r => r.json())
                .then(res => {
                    alert(res.success ? '✅ Book returned successfully!' : '❌ ' + (res.message || 'Failed'));
                    if (res.success) { document.getElementById('resultContent').innerHTML = ''; document.getElementById('resultEmpty').style.display = 'block'; }
                });
        }

        function searchBooksForReserve(q, memberId) {
            clearTimeout(bookSearchTimer);
            if (q.length < 2) { document.getElementById('reserveResultsList').innerHTML = ''; return; }
            bookSearchTimer = setTimeout(() => {
                fetch(`/library/books/search-ajax?q=${encodeURIComponent(q)}&school_id={{ session("LoggedSchool") }}`)
                    .then(r => r.json())
                    .then(books => {
                        const list = document.getElementById('reserveResultsList');
                        list.innerHTML = books.map(b => `
                            <div class="book-item" onclick="reserveBook(${b.id}, '${escHtml(b.title)}', ${memberId})">
                                <span>${escHtml(b.title)}</span>
                                <span style="color:var(--a)">${b.available_copies > 0 ? 'Available' : 'Reserve'}</span>
                            </div>`).join('') || '<div style="font-size:.8rem;color:var(--t3);padding:.4rem">No books found.</div>';
                    });
            }, 300);
        }

        function reserveBook(bookId, bookTitle, memberId) {
            if (!confirm(`Reserve "${bookTitle}"?`)) return;
            fetch('/library/reservations', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify({ book_id: bookId, member_id: memberId })
            })
                .then(r => r.json())
                .then(res => alert(res.success ? '✅ Reserved successfully!' : '❌ ' + (res.message || 'Failed')));
        }

        // ──────────────────────────────────────────────
        //  Recent scan list update
        // ──────────────────────────────────────────────
        let localHistory = [];
        function addToRecentList(resp, rawCard) {
            localHistory.unshift({ message: resp.message, ok: resp.success, time: new Date().toLocaleTimeString(), cat: resp.category });
            const list = document.getElementById('recentList');
            list.innerHTML = `<div class="scan-list">` +
                localHistory.slice(0, 10).map(h => `
                    <div class="scan-item ${h.ok ? 'ok' : 'bad'}">
                        <div class="si-icon"><i class="fas ${h.ok ? 'fa-check' : 'fa-times'}"></i></div>
                        <div style="flex:1;min-width:0">
                            <div class="si-name">${escHtml(h.message?.substring(0, 60) || '—')}</div>
                            <div class="si-cat">${h.cat ? h.cat.replace('_', ' ') : ''}</div>
                        </div>
                        <div class="si-time">${h.time}</div>
                    </div>`).join('') + `</div>`;
        }

        // ──────────────────────────────────────────────
        //  Utils
        // ──────────────────────────────────────────────
        function escHtml(str) {
            return String(str || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        }
        function nf(n) {
            return Number(n || 0).toLocaleString();
        }
    </script>
@endsection