{{-- resources/views/teacher/id-cards/scanner.blade.php --}}
@extends('layouts-side-bar.master')

@section('css')
    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700;800&family=DM+Mono:wght@400;500&display=swap"
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
            --surf: #fff;
            --bg: #f0f4f8;
            --brd: #e2e8f0;
            --t1: #0f172a;
            --t2: #475569;
            --t3: #94a3b8;
            --rad: 16px;
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

        .scanner-wrap {
            max-width: 480px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1.5rem;
            padding: 2rem 1rem;
        }

        .scanner-title {
            font-size: 1.4rem;
            font-weight: 800;
            color: var(--t1);
            text-align: center;
        }

        .scanner-sub {
            color: var(--t3);
            font-size: .9rem;
            text-align: center;
            margin-top: .25rem;
        }

        #reader {
            width: 100%;
            max-width: 380px;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 8px 32px rgba(0, 0, 0, .12);
        }

        .manual-wrap {
            width: 100%;
            display: flex;
            gap: .6rem;
        }

        .manual-input {
            flex: 1;
            padding: .65rem 1rem;
            border: 1.5px solid var(--brd);
            border-radius: 10px;
            font-size: .9rem;
            color: var(--t1);
            outline: none;
            transition: .15s;
            font-family: 'DM Mono', monospace;
        }

        .manual-input:focus {
            border-color: var(--b);
            box-shadow: 0 0 0 3px var(--bl);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            padding: .6rem 1.2rem;
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
        }

        .btn-primary:hover,
        .btn-teal:hover {
            background: var(--b2);
        }

        .result-card {
            width: 100%;
            background: var(--surf);
            border-radius: var(--rad);
            box-shadow: 0 4px 24px rgba(0, 0, 0, .08);
            overflow: hidden;
            display: none;
        }

        .result-header {
            padding: 1rem 1.25rem;
            display: flex;
            align-items: center;
            gap: .75rem;
        }

        .result-header.valid {
            background: var(--gl);
        }

        .result-header.invalid {
            background: var(--rl);
        }

        .result-icon {
            font-size: 1.5rem;
        }

        .result-header.valid .result-icon {
            color: var(--g);
        }

        .result-header.invalid .result-icon {
            color: var(--r);
        }

        .result-status {
            font-size: 1rem;
            font-weight: 800;
        }

        .result-header.valid .result-status {
            color: var(--g);
        }

        .result-header.invalid .result-status {
            color: var(--r);
        }

        .result-body {
            padding: 1rem 1.25rem;
        }

        .result-row {
            display: flex;
            gap: .5rem;
            margin-bottom: .5rem;
            align-items: flex-start;
        }

        .result-lbl {
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: var(--t3);
            min-width: 90px;
        }

        .result-val {
            font-size: .88rem;
            font-weight: 600;
            color: var(--t1);
        }

        /* Additional blue theme elements */
        .scanner-wrap i:not(.result-icon) {
            color: var(--b);
        }

        .btn-outline {
            background: transparent;
            color: var(--b);
            border: 1.5px solid var(--b);
        }

        .btn-outline:hover {
            background: var(--bl);
        }

        /* Scanner Controls Styling */
        .scanner-controls {
            display: flex;
            gap: 0.75rem;
            justify-content: center;
            margin-top: 1rem;
        }

        .scanner-controls .btn {
            min-width: 140px;
            justify-content: center;
        }

        /* Hide default HTML5 QR code UI elements */
        #reader__dashboard_section_csr,
        #reader__dashboard_section,
        [class*="html5-qrcode"] button,
        [class*="html5-qrcode"] select {
            display: none !important;
        }

        /* Style the camera view */
        #reader {
            border: 2px solid var(--brd);
            border-radius: 20px;
            overflow: hidden;
            background: var(--surf);
        }

        #reader video {
            border-radius: 18px;
            width: 100%;
            height: auto;
        }

        /* Camera permission message styling */
        #reader__status_span {
            display: block;
            padding: 1rem;
            text-align: center;
            font-size: 0.85rem;
            color: var(--t2);
            background: var(--bg);
            border-radius: 12px;
        }

        /* Success/Error message styling */
        #reader__message {
            font-size: 0.8rem;
            color: var(--t3);
            text-align: center;
            margin-top: 0.5rem;
        }
    </style>
@endsection

@section('content')
    <div class="scanner-wrap">
        <div>
            <div class="scanner-title"><i class="fas fa-qrcode mr-2" style="color:var(--b);"></i>Scan Teacher ID</div>
            <div class="scanner-sub">Point camera at the QR code on the teacher's card</div>
        </div>

        <div id="reader"></div>

        <div class="scanner-controls">
            <button class="btn btn-primary" id="startScannerBtn" onclick="startScanner()">
                <i class="fas fa-camera"></i> Start Scanner
            </button>
            <button class="btn btn-outline" id="stopScannerBtn" onclick="stopScanner()" style="display:none;">
                <i class="fas fa-stop"></i> Stop Scanner
            </button>
        </div>

        <div class="manual-wrap">
            <input type="text" class="manual-input" id="manualInput" placeholder="Or type card number manually…">
            <button class="btn btn-teal" onclick="verifyCard(document.getElementById('manualInput').value.trim())">
                <i class="fas fa-search"></i> Verify
            </button>
        </div>

        <div class="result-card" id="resultCard">
            <div class="result-header" id="resultHeader">
                <span class="result-icon" id="resultIcon"></span>
                <span class="result-status" id="resultStatus"></span>
            </div>
            <div class="result-body" id="resultBody"></div>
        </div>
    </div>
    </div>
    </div>
    </div>
@endsection

@section('js')
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <script>

        let html5QrCode;

        function startScanner() {
            document.getElementById('startScannerBtn').style.display = 'none';
            document.getElementById('stopScannerBtn').style.display = 'inline-flex';

            html5QrCode = new Html5Qrcode("reader");
            html5QrCode.start(
                { facingMode: "environment" },
                { fps: 10, qrbox: { width: 250, height: 250 } },
                onScan,
                (error) => { /* silent */ }
            );
        }

        function stopScanner() {
            if (html5QrCode) {
                html5QrCode.stop().then(() => {
                    document.getElementById('startScannerBtn').style.display = 'inline-flex';
                    document.getElementById('stopScannerBtn').style.display = 'none';
                });
            }
        }

        const scanner = new Html5QrcodeScanner("reader", { fps: 10, qrbox: 250 });
        scanner.render(onScan);

        function onScan(decoded) {
            let cardNumber = decoded;
            try {
                const obj = JSON.parse(decoded);
                if (obj.card) cardNumber = obj.card;
            } catch (e) { }
            verifyCard(cardNumber);
        }

        function verifyCard(cardNumber) {
            if (!cardNumber) return;
            const rc = document.getElementById('resultCard');
            rc.style.display = 'none';

            fetch(`/teacher-id-cards/verify/${encodeURIComponent(cardNumber)}`)
                .then(r => r.json())
                .then(d => {
                    const header = document.getElementById('resultHeader');
                    const icon = document.getElementById('resultIcon');
                    const status = document.getElementById('resultStatus');
                    const body = document.getElementById('resultBody');

                    if (d.valid) {
                        header.className = 'result-header valid';
                        icon.innerHTML = '<i class="fas fa-check-circle" style="color:#166534;"></i>';
                        status.innerHTML = '<span style="color:#166534;">VALID CARD</span>';
                    } else {
                        header.className = 'result-header invalid';
                        icon.innerHTML = '<i class="fas fa-times-circle" style="color:#991b1b;"></i>';
                        status.innerHTML = '<span style="color:#991b1b;">' + (d.status ?? 'INVALID').toUpperCase() + ' CARD</span>';
                    }

                    body.innerHTML = `
                                            <div class="result-row"><span class="result-lbl">Teacher</span><span class="result-val">${d.teacher_name ?? '—'}</span></div>
                                            <div class="result-row"><span class="result-lbl">Employee No.</span><span class="result-val">${d.employee_no ?? '—'}</span></div>
                                            <div class="result-row"><span class="result-lbl">Phone</span><span class="result-val">${d.phone ?? '—'}</span></div>
                                            <div class="result-row"><span class="result-lbl">Gender</span><span class="result-val">${d.gender ?? '—'}</span></div>
                                            <div class="result-row"><span class="result-lbl">School</span><span class="result-val">${d.school ?? '—'}</span></div>
                                            <div class="result-row"><span class="result-lbl">Acad. Year</span><span class="result-val">${d.academic_year ?? '—'}</span></div>
                                            <div class="result-row"><span class="result-lbl">Issue Date</span><span class="result-val">${d.issue_date ?? '—'}</span></div>
                                            <div class="result-row"><span class="result-lbl">Expiry</span><span class="result-val">${d.expiry_date ?? '—'}</span></div>
                                            <div class="result-row"><span class="result-lbl">Card No.</span><span class="result-val" style="font-family:monospace;font-size:.78rem;">${d.card_number ?? '—'}</span></div>
                                        `;

                    rc.style.display = 'block';
                })
                .catch(() => {
                    document.getElementById('resultCard').style.display = 'none';
                });
        }
    </script>
@endsection