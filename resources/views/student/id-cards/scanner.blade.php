{{-- resources/views/student/id-cards/scanner.blade.php --}}
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
            box-sizing: border-box;
        }

        *:not(i):not([class*="fa"]) {
            font-family: 'DM Sans', sans-serif;
        }

        body {
            background: var(--bg);
        }

        .scanner-hero {
            background: linear-gradient(135deg, #1a1869 0%, #2f2ccb 60%, #0d0b5e 100%);
            border-radius: 24px;
            padding: 2rem 2.5rem;
            margin-bottom: 1.75rem;
        }

        .hero-title {
            color: #fff;
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0 0 .3rem;
        }

        .hero-sub {
            color: rgba(255, 255, 255, .7);
            font-size: .9rem;
        }

        .scan-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        @media(max-width:700px) {
            .scan-grid {
                grid-template-columns: 1fr;
            }
        }

        .card-box {
            background: var(--surf);
            border-radius: var(--rad);
            box-shadow: var(--sh);
            padding: 1.5rem;
        }

        .box-title {
            font-size: .95rem;
            font-weight: 700;
            color: var(--t1);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: .5rem;
        }

        .box-title i {
            color: var(--b);
        }

        /* Camera */
        #reader {
            width: 100%;
            border-radius: 12px;
            overflow: hidden;
            border: 2px dashed var(--brd);
        }

        /* Manual input */
        .input-row {
            display: flex;
            gap: .5rem;
        }

        .input-row input {
            flex: 1;
            padding: .55rem .8rem;
            border: 1.5px solid var(--brd);
            border-radius: 8px;
            font-size: .9rem;
            color: var(--t1);
            background: var(--bg);
        }

        .input-row input:focus {
            outline: none;
            border-color: var(--b);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            padding: .55rem 1.1rem;
            border-radius: 8px;
            font-size: .88rem;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: .15s;
        }

        .btn-primary {
            background: var(--b);
            color: #fff;
        }

        .btn-primary:hover {
            background: #2420a8;
        }

        /* Result card */
        #resultBox {
            display: none;
            margin-top: 1rem;
        }

        .result-card {
            border-radius: 12px;
            overflow: hidden;
            border: 2px solid var(--brd);
        }

        .result-header {
            padding: .75rem 1rem;
            display: flex;
            align-items: center;
            gap: .5rem;
            font-weight: 700;
            font-size: .9rem;
        }

        .result-header.valid {
            background: var(--gl);
            color: var(--g);
        }

        .result-header.invalid {
            background: var(--rl);
            color: var(--r);
        }

        .result-body {
            padding: 1rem;
            background: #fff;
        }

        .result-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: .5rem;
        }

        .result-field-label {
            font-size: .68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: var(--t3);
        }

        .result-field-val {
            font-size: .88rem;
            font-weight: 600;
            color: var(--t1);
            margin-top: .1rem;
        }

        .scan-history {
            margin-top: 1rem;
        }

        .hist-title {
            font-size: .78rem;
            font-weight: 700;
            color: var(--t3);
            text-transform: uppercase;
            letter-spacing: .05em;
            margin-bottom: .5rem;
        }

        .hist-item {
            display: flex;
            align-items: center;
            gap: .5rem;
            padding: .5rem .75rem;
            border-radius: 8px;
            margin-bottom: .3rem;
            font-size: .82rem;
        }

        .hist-item.ok {
            background: var(--gl);
            color: var(--g);
        }

        .hist-item.bad {
            background: var(--rl);
            color: var(--r);
        }

        .hist-time {
            font-size: .7rem;
            opacity: .7;
            margin-left: auto;
        }

        .empty-history {
            font-size: .82rem;
            color: var(--t3);
            text-align: center;
            padding: 1rem;
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid mt-5" style="max-width:1200px;">

        <div class="scanner-hero">
            <h2 class="hero-title"><i class="fas fa-qrcode me-2"></i> ID Card QR Scanner</h2>
            <p class="hero-sub">Scan a student's ID card QR code or enter the card number manually to verify.</p>
        </div>

        <div class="scan-grid">
            {{-- Left: Camera scanner --}}
            <div>
                <div class="card-box">
                    <div class="box-title"><i class="fas fa-camera"></i> Camera Scanner</div>
                    <div id="reader"></div>
                    <div style="text-align:center;margin-top:.75rem;">
                        <button class="btn btn-primary" onclick="startScanner()" id="btnStart">
                            <i class="fas fa-play"></i> Start Camera
                        </button>
                        <button class="btn" style="background:var(--rl);color:var(--r);display:none;" id="btnStop"
                            onclick="stopScanner()">
                            <i class="fas fa-stop"></i> Stop
                        </button>
                    </div>
                </div>

                {{-- Manual Entry --}}
                <div class="card-box" style="margin-top:1rem;">
                    <div class="box-title"><i class="fas fa-keyboard"></i> Manual Lookup</div>
                    <div class="input-row">
                        <input type="text" id="manualInput" placeholder="Enter card number e.g. ID-XYZ-123-…"
                            onkeydown="if(event.key==='Enter') verifyManual()">
                        <button class="btn btn-primary" onclick="verifyManual()"><i class="fas fa-search"></i>
                            Verify</button>
                    </div>
                </div>
            </div>

            {{-- Right: Result + History --}}
            <div>
                <div class="card-box">
                    <div class="box-title"><i class="fas fa-check-circle"></i> Verification Result</div>
                    <div id="resultBox">
                        <div class="result-card">
                            <div class="result-header" id="resultHeader">
                                <i class="fas fa-check-circle" id="resultIcon"></i>
                                <span id="resultTitle">—</span>
                            </div>
                            <div class="result-body">
                                <div class="result-grid" id="resultGrid"></div>
                            </div>
                        </div>
                    </div>
                    <div id="noResultMsg" style="text-align:center;padding:2rem;color:var(--t3);font-size:.9rem;">
                        <i class="fas fa-id-card"
                            style="font-size:2.5rem;margin-bottom:.5rem;display:block;opacity:.35;"></i>
                        Scan or enter a card number to see results here.
                    </div>
                </div>

                {{-- Scan History --}}
                <div class="card-box mb-3" style="margin-top:1rem;">
                    <div class="box-title"><i class="fas fa-history"></i> Scan History</div>
                    <div class="scan-history">
                        <div id="historyList">
                            <div class="empty-history">No scans yet.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
    </div>
    </div>
    </div>
@endsection

@section('js')
    {{-- Html5-QrCode library --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.8/html5-qrcode.min.js"></script>
    <script>
        let html5QrcodeScanner = null;
        let scanHistory = [];

        function startScanner() {
            document.getElementById('btnStart').style.display = 'none';
            document.getElementById('btnStop').style.display = 'inline-flex';

            html5QrcodeScanner = new Html5QrcodeScanner('reader', {
                fps: 10,
                qrbox: { width: 230, height: 230 },
                rememberLastUsedCamera: true,
                supportedScanTypes: [Html5QrcodeScanType.SCAN_TYPE_CAMERA]
            });

            html5QrcodeScanner.render((decodedText) => {
                handleScan(decodedText);
            }, (err) => { /* ignore frame errors */ });
        }

        function stopScanner() {
            if (html5QrcodeScanner) {
                html5QrcodeScanner.clear();
                html5QrcodeScanner = null;
            }
            document.getElementById('btnStart').style.display = 'inline-flex';
            document.getElementById('btnStop').style.display = 'none';
            document.getElementById('reader').innerHTML = '';
        }

        function handleScan(rawText) {
            // rawText might be JSON from our QR or just the card_number
            let cardNumber = rawText;
            try {
                const parsed = JSON.parse(rawText);
                if (parsed.card) cardNumber = parsed.card;
            } catch (e) { }
            verifyCard(cardNumber);
        }

        function verifyManual() {
            const val = document.getElementById('manualInput').value.trim();
            if (!val) return;
            verifyCard(val);
        }

        function verifyCard(cardNumber) {
            fetch(`/student-id-cards/verify/${encodeURIComponent(cardNumber)}`)
                .then(r => r.json())
                .then(data => showResult(data, cardNumber))
                .catch(() => showResult({ valid: false, message: 'Network error.' }, cardNumber));
        }

        function showResult(data, cardNumber) {
            document.getElementById('noResultMsg').style.display = 'none';
            document.getElementById('resultBox').style.display = 'block';

            const header = document.getElementById('resultHeader');
            const icon = document.getElementById('resultIcon');
            const title = document.getElementById('resultTitle');

            if (data.valid) {
                header.className = 'result-header valid';
                icon.className = 'fas fa-check-circle';
                title.textContent = 'Valid Card – Student Verified';
            } else {
                header.className = 'result-header invalid';
                icon.className = 'fas fa-times-circle';
                title.textContent = data.message || 'Invalid / Revoked Card';
            }

            const grid = document.getElementById('resultGrid');
            grid.innerHTML = '';
            const fields = [
                ['Student Name', data.student_name ?? '—'],
                ['Class', data.class ?? '—'],
                ['Stream', data.stream ?? '—'],
                ['Gender', data.gender ?? '—'],
                ['School', data.school ?? '—'],
                ['Academic Year', data.academic_year ?? '—'],
                ['Card Status', data.status ? data.status.toUpperCase() : '—'],
                ['Card No.', data.card_number ?? cardNumber],
                ['Issue Date', data.issue_date ?? '—'],
                ['Expiry Date', data.expiry_date ?? '—'],
            ];
            fields.forEach(([lbl, val]) => {
                grid.innerHTML += `
                    <div>
                        <div class="result-field-label">${lbl}</div>
                        <div class="result-field-val">${val}</div>
                    </div>`;
            });

            // Add to history
            const now = new Date().toLocaleTimeString();
            scanHistory.unshift({ cardNumber, valid: data.valid, name: data.student_name ?? cardNumber, time: now });
            renderHistory();
        }

        function renderHistory() {
            const list = document.getElementById('historyList');
            if (!scanHistory.length) {
                list.innerHTML = '<div class="empty-history">No scans yet.</div>';
                return;
            }
            list.innerHTML = scanHistory.slice(0, 8).map(h => `
                <div class="hist-item ${h.valid ? 'ok' : 'bad'}">
                    <i class="fas ${h.valid ? 'fa-check-circle' : 'fa-times-circle'}"></i>
                    <span>${h.name}</span>
                    <span class="hist-time">${h.time}</span>
                </div>
            `).join('');
        }
    </script>
@endsection