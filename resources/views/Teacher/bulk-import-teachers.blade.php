<?php use App\Http\Controllers\Helper; ?>
@extends('layouts-side-bar.master')

@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --b: #2f2ccb;
            --b2: #2420a8;
            --bl: rgba(47, 44, 203, .10);
            --g: #059669;
            --gl: rgba(5, 150, 105, .10);
            --a: #d97706;
            --al: rgba(217, 119, 6, .10);
            --r: #dc2626;
            --rl: rgba(220, 38, 38, .10);
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

        body {
            background: var(--bg);
        }

        .page-header {
            padding: 28px 0 8px;
        }

        .page-header h2 {
            font-size: 1.55rem;
            font-weight: 700;
            color: var(--t1);
        }

        .page-header p {
            color: var(--t2);
            font-size: .93rem;
        }

        .card-box {
            background: var(--surf);
            border-radius: var(--rad);
            box-shadow: var(--sh);
            padding: 32px;
            margin-bottom: 24px;
        }

        /* Responsive padding for smaller devices */
        @media (max-width: 768px) {
            .card-box {
                padding: 20px;
            }

            .page-header h2 {
                font-size: 1.3rem;
            }

            .page-header p {
                font-size: .85rem;
            }
        }

        .steps {
            display: flex;
            gap: 12px;
            margin-bottom: 32px;
            flex-wrap: wrap;
        }

        .step {
            flex: 1;
            min-width: 160px;
            background: var(--bg);
            border-radius: var(--rads);
            padding: 16px 18px;
            border-left: 4px solid #2f2ccb;
        }

        /* Make steps stack on smaller screens */
        @media (max-width: 640px) {
            .step {
                min-width: 100%;
            }
        }

        .step-num {
            font-size: .75rem;
            font-weight: 700;
            color: #2f2ccb;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .step-title {
            font-size: .9rem;
            font-weight: 600;
            color: var(--t1);
            margin-top: 4px;
        }

        .step-desc {
            font-size: .8rem;
            color: var(--t2);
            margin-top: 2px;
        }

        .btn-primary-custom {
            background: #2f2ccb;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 11px 28px;
            font-weight: 600;
            font-size: .9rem;
            cursor: pointer;
            transition: .2s;
        }

        .btn-primary-custom:hover {
            background: #6d28d9;
        }

        .btn-outline {
            background: transparent;
            color: var(--b);
            border: 2px solid var(--b);
            border-radius: 8px;
            padding: 9px 24px;
            font-weight: 600;
            font-size: .9rem;
            cursor: pointer;
            transition: .2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-outline:hover {
            background: var(--bl);
        }

        .btn-purple-outline {
            background: transparent;
            color: #2f2ccb;
            border: 2px solid #2f2ccb;
            border-radius: 8px;
            padding: 9px 24px;
            font-weight: 600;
            font-size: .9rem;
            cursor: pointer;
            transition: .2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-purple-outline:hover {
            background: rgba(124, 58, 237, .08);
        }

        /* Make buttons full width on mobile */
        @media (max-width: 576px) {

            .btn-primary-custom,
            .btn-outline,
            .btn-purple-outline {
                width: 100%;
                justify-content: center;
            }
        }

        .upload-zone {
            border: 2.5px dashed var(--brd);
            border-radius: var(--rad);
            padding: 48px 24px;
            text-align: center;
            background: var(--bg);
            cursor: pointer;
            transition: .2s;
        }

        /* Adjust upload zone padding on mobile */
        @media (max-width: 576px) {
            .upload-zone {
                padding: 32px 16px;
            }

            .upload-zone i {
                font-size: 2rem;
            }

            .upload-zone p {
                font-size: .85rem;
            }

            .upload-zone small {
                font-size: .75rem;
            }
        }

        .upload-zone:hover,
        .upload-zone.drag-over {
            border-color: #2f2ccb;
            background: rgba(124, 58, 237, .06);
        }

        .upload-zone i {
            font-size: 2.5rem;
            color: var(--t3);
        }

        .upload-zone p {
            color: var(--t2);
            margin: 12px 0 0;
            font-size: .92rem;
        }

        .upload-zone small {
            color: var(--t3);
        }

        .result-box {
            border-radius: var(--rads);
            padding: 20px;
            margin-top: 20px;
            display: none;
        }

        .result-success {
            background: var(--gl);
            border: 1.5px solid var(--g);
        }

        .result-error {
            background: var(--rl);
            border: 1.5px solid var(--r);
        }

        .result-warn {
            background: var(--al);
            border: 1.5px solid var(--a);
        }

        .err-list {
            max-height: 220px;
            overflow-y: auto;
            margin-top: 12px;
        }

        .err-item {
            font-size: .82rem;
            color: var(--r);
            padding: 4px 0;
            border-bottom: 1px solid var(--rl);
        }

        .section-title {
            font-size: 1rem;
            font-weight: 700;
            color: var(--t1);
            margin-bottom: 16px;
        }

        .divider {
            border: none;
            border-top: 1.5px solid var(--brd);
            margin: 28px 0;
        }

        #file-name-display {
            font-size: .85rem;
            color: #2f2ccb;
            margin-top: 8px;
            font-weight: 500;
        }

        .col-req {
            background: #fef9c3;
            border: 1px solid #fbbf24;
            border-radius: 6px;
            padding: 12px 16px;
            font-size: .85rem;
            color: var(--t2);
        }

        /* Make col-req responsive */
        @media (max-width: 576px) {
            .col-req {
                font-size: .8rem;
                padding: 10px 12px;
            }

            .col-req strong {
                display: block;
                margin-bottom: 6px;
            }
        }

        .col-req strong {
            color: var(--t1);
        }

        .spinner-border-sm {
            width: 1rem;
            height: 1rem;
            border-width: .15em;
        }

        /* Container takes full width but with comfortable margins */
        .container-fluid {
            width: 100%;
            padding-right: 20px;
            padding-left: 20px;
            margin-right: auto;
            margin-left: auto;
        }

        /* For larger screens, add some max-width for better readability */
        @media (min-width: 1400px) {
            .container-fluid {
                max-width: 1320px;
            }
        }

        /* Button container responsive */
        .button-container {
            margin-top: 20px;
            display: flex;
            gap: 12px;
            align-items: center;
            flex-wrap: wrap;
        }

        @media (max-width: 576px) {
            .button-container {
                flex-direction: column;
                align-items: stretch;
            }

            .button-container span {
                text-align: center;
                margin-top: 8px;
            }
        }

        /* Back button responsive */
        .back-button {
            margin-bottom: 32px;
            display: inline-flex;
        }

        @media (max-width: 576px) {
            .back-button {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
@endsection

@section('content')
    <div class="side-app">
        <div class="container-fluid">
            <div class="page-header">
                <h2><i class="fas fa-user-friends me-2" style="color:#2f2ccb"></i> Bulk Import Teachers</h2>
                <p>Import multiple teachers at once using an Excel spreadsheet template.</p>
            </div>

            <div class="steps">
                <div class="step">
                    <div class="step-num">Step 1</div>
                    <div class="step-title">Download Template</div>
                    <div class="step-desc">Get the Excel template for this school.</div>
                </div>
                <div class="step">
                    <div class="step-num">Step 2</div>
                    <div class="step-title">Fill In Data</div>
                    <div class="step-desc">Enter surname, firstname, email, phone — required for each teacher.</div>
                </div>
                <div class="step">
                    <div class="step-num">Step 3</div>
                    <div class="step-title">Upload & Import</div>
                    <div class="step-desc">Upload the completed file. Duplicates are skipped automatically.</div>
                </div>
            </div>

            <div class="card-box">
                <div class="section-title">Download Import Template</div>
                <div class="col-req mb-3">
                    <strong>Required columns:</strong> surname, firstname, email, phonenumber<br>
                    <strong>Optional columns:</strong> gender, othername, national_id, address<br>
                    <em>All imported teachers get a default password of <code>123456789</code> and are prompted to change it
                        on first login.</em>
                </div>
                <a href="{{ route('teachers.download.template') }}" class="btn-purple-outline">
                    <i class="fas fa-download"></i> Download Template
                </a>

                <hr class="divider">

                <div class="section-title">Upload Completed File</div>
                <div class="upload-zone" id="upload-zone" onclick="document.getElementById('file-input').click()">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <p>Click to browse or drag & drop your Excel file here</p>
                    <small>Supported formats: .xlsx, .xls</small>
                </div>
                <input type="file" id="file-input" accept=".xlsx,.xls" style="display:none">
                <div id="file-name-display"></div>

                <div class="button-container">
                    <button type="button" class="btn-primary-custom" id="btn-import" disabled>
                        <span id="import-spinner" class="spinner-border spinner-border-sm me-2" style="display:none"></span>
                        <i class="fas fa-upload me-1"></i> Import Teachers
                    </button>
                    <span style="font-size:.82rem;color:var(--t2)">Existing teachers (same email in this school) are
                        skipped.</span>
                </div>

                <div id="result-box" class="result-box">
                    <div id="result-message"></div>
                    <div class="err-list" id="err-list"></div>
                </div>
            </div>

            <a href="{{ route('school.teachers') }}" class="back-button btn-outline">
                <i class="fas fa-arrow-left"></i> Back to Teachers
            </a>
        </div>
    </div>
            </div>
    </div>
@endsection

@section('js')
    <script>
        const csrfToken = '{{ csrf_token() }}';
        let selectedFile = null;

        const zone = document.getElementById('upload-zone');
        zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('drag-over'); });
        zone.addEventListener('dragleave', () => zone.classList.remove('drag-over'));
        zone.addEventListener('drop', e => { e.preventDefault(); zone.classList.remove('drag-over'); handleFile(e.dataTransfer.files[0]); });
        document.getElementById('file-input').addEventListener('change', function () { handleFile(this.files[0]); });

        function handleFile(file) {
            if (!file) return;
            selectedFile = file;
            document.getElementById('file-name-display').textContent = '📎 ' + file.name;
            document.getElementById('btn-import').disabled = false;
        }

        document.getElementById('btn-import').addEventListener('click', function () {
            if (!selectedFile) return;
            const spinner = document.getElementById('import-spinner');
            spinner.style.display = 'inline-block';
            this.disabled = true;

            const fd = new FormData();
            fd.append('_token', csrfToken);
            fd.append('file', selectedFile);

            fetch('/teachers/bulk-import', { method: 'POST', body: fd })
                .then(r => r.json())
                .then(data => {
                    spinner.style.display = 'none';
                    document.getElementById('btn-import').disabled = false;
                    showResult(data);
                })
                .catch(err => {
                    spinner.style.display = 'none';
                    document.getElementById('btn-import').disabled = false;
                    showResult({ status: 'error', message: 'An error occurred: ' + err.message, errors: [] });
                });
        });

        function showResult(data) {
            const box = document.getElementById('result-box');
            const msg = document.getElementById('result-message');
            const errList = document.getElementById('err-list');
            box.className = 'result-box';
            box.style.display = 'block';
            errList.innerHTML = '';

            if (data.status === 'success' || data.imported > 0) {
                box.classList.add('result-success');
                msg.innerHTML = `<strong style="color:var(--g)"><i class="fas fa-check-circle me-1"></i>${data.message}</strong>`;
            } else {
                box.classList.add('result-error');
                msg.innerHTML = `<strong style="color:var(--r)"><i class="fas fa-times-circle me-1"></i>${data.message}</strong>`;
            }

            if (data.errors && data.errors.length) {
                const hasSuccess = (data.imported ?? 0) > 0;
                if (hasSuccess) box.className = 'result-box result-warn';
                data.errors.forEach(e => {
                    const d = document.createElement('div');
                    d.className = 'err-item';
                    d.textContent = e;
                    errList.appendChild(d);
                });
            }
        }
    </script>
@endsection