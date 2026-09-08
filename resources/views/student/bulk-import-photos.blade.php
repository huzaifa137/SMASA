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

        @media (max-width: 768px) {
            .card-box {
                padding: 20px;
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
            border-left: 4px solid var(--b);
        }

        @media (max-width: 640px) {
            .step {
                min-width: 100%;
            }
        }

        .step-num {
            font-size: .75rem;
            font-weight: 700;
            color: var(--b);
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

        .btn-primary-custom {
            background: var(--b);
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 11px 28px;
            font-weight: 600;
            font-size: .9rem;
            cursor: pointer;
            transition: .2s;
        }

        .btn-primary-custom:hover:not(:disabled) {
            background: var(--b2);
        }

        .btn-primary-custom:disabled {
            opacity: .5;
            cursor: not-allowed;
        }

        .btn-success-custom {
            background: var(--g);
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 11px 28px;
            font-weight: 600;
            font-size: .9rem;
            cursor: pointer;
            transition: .2s;
        }

        .btn-success-custom:hover:not(:disabled) {
            background: #047857;
        }

        .btn-success-custom:disabled {
            opacity: .5;
            cursor: not-allowed;
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

        @media (max-width: 576px) {

            .btn-primary-custom,
            .btn-success-custom,
            .btn-outline {
                width: 100%;
                justify-content: center;
            }
        }

        .match-option {
            border: 1.5px solid var(--brd);
            border-radius: var(--rads);
            padding: 14px 16px;
            cursor: pointer;
            transition: .15s;
            height: 100%;
        }

        .match-option:hover {
            border-color: var(--b);
        }

        .match-option.active {
            border-color: var(--b);
            background: var(--bl);
        }

        .match-option .mo-title {
            font-weight: 700;
            font-size: .9rem;
            color: var(--t1);
        }

        .match-option .mo-desc {
            font-size: .78rem;
            color: var(--t2);
            margin-top: 4px;
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
        }

        .upload-zone:hover,
        .upload-zone.drag-over {
            border-color: var(--b);
            background: var(--bl);
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

        .thumb-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(110px, 1fr));
            gap: 12px;
            margin-top: 20px;
        }

        .thumb-card {
            border: 1.5px solid var(--brd);
            border-radius: var(--rads);
            overflow: hidden;
            background: var(--surf);
            position: relative;
        }

        .thumb-card img {
            width: 100%;
            height: 90px;
            object-fit: cover;
            display: block;
            background: var(--bg);
        }

        .thumb-card .thumb-name {
            font-size: .68rem;
            color: var(--t2);
            padding: 5px 6px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .thumb-card .thumb-status {
            position: absolute;
            top: 4px;
            right: 4px;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .6rem;
            color: #fff;
        }

        .thumb-card .thumb-remove {
            position: absolute;
            top: 4px;
            left: 4px;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            background: rgba(15, 23, 42, .55);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .62rem;
            cursor: pointer;
            border: none;
        }

        .status-pending {
            background: var(--t3);
        }

        .status-ready {
            background: var(--g);
        }

        .status-imported {
            background: var(--g);
        }

        .status-error {
            background: var(--r);
        }

        .summary-strip {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin: 20px 0;
        }

        .summary-pill {
            flex: 1;
            min-width: 120px;
            background: var(--bg);
            border-radius: var(--rads);
            padding: 14px 16px;
            text-align: center;
        }

        .summary-pill .num {
            font-size: 1.4rem;
            font-weight: 800;
            color: var(--t1);
        }

        .summary-pill .lbl {
            font-size: .74rem;
            color: var(--t2);
            text-transform: uppercase;
            letter-spacing: .5px;
        }

        .summary-pill.ready .num {
            color: var(--g);
        }

        .summary-pill.errors .num {
            color: var(--r);
        }

        .result-table-wrap {
            max-height: 420px;
            overflow-y: auto;
            border: 1.5px solid var(--brd);
            border-radius: var(--rads);
            margin-top: 12px;
        }

        table.result-table {
            width: 100%;
            border-collapse: collapse;
            font-size: .84rem;
        }

        table.result-table th {
            background: #2f2ccb;
            color: var(--t2);
            text-align: left;
            padding: 10px 12px;
            position: sticky;
            top: 0;
            font-size: .74rem;
            text-transform: uppercase;
            letter-spacing: .4px;
        }

        table.result-table td {
            padding: 10px 12px;
            border-top: 1px solid var(--brd);
            vertical-align: top;
        }

        .badge-status {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: .72rem;
            font-weight: 700;
        }

        .badge-ready,
        .badge-imported {
            background: var(--gl);
            color: var(--g);
        }

        .badge-error {
            background: var(--rl);
            color: var(--r);
        }

        .container-fluid {
            width: 100%;
            padding-right: 20px;
            padding-left: 20px;
            margin-right: auto;
            margin-left: auto;
        }

        @media (min-width: 1400px) {
            .container-fluid {
                max-width: 1320px;
            }
        }
    </style>
@endsection

@section('content')
    <div class="side-app">
        <div class="container-fluid">
            <div class="page-header">
                <h2><i class="fas fa-portrait me-2" style="color:var(--b)"></i> Bulk Photo Import</h2>
                <p>Upload a batch of student photos for one class & stream in a single pass.</p>
            </div>

            {{-- Steps --}}
            <div class="steps">
                <div class="step">
                    <div class="step-num">Step 1</div>
                    <div class="step-title">Select Class, Stream & Match By</div>
                    <div class="step-desc">Choose who these photos belong to and how each file should be matched.</div>
                </div>
                <div class="step">
                    <div class="step-num">Step 2</div>
                    <div class="step-title">Check Photos</div>
                    <div class="step-desc">We match, and validate every photo — nothing is saved yet.</div>
                </div>
                <div class="step">
                    <div class="step-num">Step 3</div>
                    <div class="step-title">Fix & Import</div>
                    <div class="step-desc">Fix anything flagged, then import — only clean matches are saved.</div>
                </div>
            </div>

            <div class="card-box">
                <div class="section-title">Class & Stream</div>
                <div
                    style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); border-radius: 24px; padding: 32px;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
                        <div>
                            <label style="display: block; color: #cbd5e1; font-size: 13px; margin-bottom: 8px;">Class
                                <span style="color:#ef4444">*</span></label>
                            <select id="class_id"
                                style="width: 100%; padding: 12px; background: #334155; border: 1px solid #475569; border-radius: 12px; color: white;">
                                <option value="">-- Select Class --</option>
                                @foreach($classrooms as $c)
                                    <option value="{{ $c->class_name }}">
                                        {{ Helper::recordMdname($c->class_name) ?? $c->class_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label style="display: block; color: #cbd5e1; font-size: 13px; margin-bottom: 8px;">Stream
                                <span style="color:#ef4444">*</span></label>
                            <select id="stream_id"
                                style="width: 100%; padding: 12px; background: #334155; border: 1px solid #475569; border-radius: 12px; color: white;">
                                <option value="">-- Select Stream --</option>
                            </select>
                        </div>
                    </div>
                </div>

                <hr class="divider">

                <div class="section-title">Match Photos By</div>
                <p style="color:var(--t2);font-size:.9rem;margin-top:-8px;">
                    Name each photo file after the value you pick here (e.g. <code>194821.jpg</code> or
                    <code>Jane_Doe.jpg</code>) — one photo per student. Whichever you choose must be unique for
                    every student in this class/stream, or that file will be flagged for you to fix.
                </p>
                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-top:12px;" id="match-options">
                    <div class="match-option active" data-value="lin">
                        <div class="mo-title"><i class="fas fa-id-badge me-1"></i> LIN No.</div>
                        <div class="mo-desc">File names are each student's LIN Number, e.g. <code>1948210.jpg</code>.
                            Recommended — always unique.</div>
                    </div>
                    <div class="match-option" data-value="reg">
                        <div class="mo-title"><i class="fas fa-hashtag me-1"></i> Registration Number</div>
                        <div class="mo-desc">File names are each student's Registration Number, e.g.
                            <code>H1-STD-003-2026.jpg</code>. Always unique.</div>
                    </div>
                    <div class="match-option" data-value="name">
                        <div class="mo-title"><i class="fas fa-user me-1"></i> Full Name</div>
                        <div class="mo-desc">File names are each student's name, e.g. <code>Jane Doe.jpg</code>.
                            Rejected if two students share a name.</div>
                    </div>
                </div>
                <input type="hidden" id="match_by" value="lin">

                <hr class="divider">

                {{-- Upload Section --}}
                <div class="section-title">Upload Photos</div>
                <div class="upload-zone" id="upload-zone" onclick="document.getElementById('file-input').click()">
                    <i class="fas fa-images"></i>
                    <p>Click to browse or drag & drop student photos here</p>
                    <small>Supported: JPG, PNG, GIF, WEBP &nbsp;|&nbsp; Up to 300 photos &nbsp;|&nbsp; Auto-resized &
                        compressed — no need to prep them first.</small>
                </div>
                <input type="file" id="file-input" accept=".jpg,.jpeg,.png,.gif,.webp,image/*" multiple
                    style="display:none">

                <div class="thumb-grid" id="thumb-grid"></div>

                <div style="margin-top: 24px; display:flex; gap:12px; align-items:center; flex-wrap: wrap;">
                    <button type="button" class="btn-primary-custom" id="btn-check" disabled>
                        <span id="check-spinner" class="spinner-border spinner-border-sm me-2" style="display:none"></span>
                        <i class="fas fa-magnifying-glass me-1"></i> Check Photos
                    </button>
                    <button type="button" class="btn-success-custom" id="btn-import" disabled>
                        <span id="import-spinner" class="spinner-border spinner-border-sm me-2" style="display:none"></span>
                        <i class="fas fa-upload me-1"></i> Import Ready Photos
                    </button>
                    <span style="font-size:.82rem;color:var(--t2)">Only photos with no errors are ever saved.</span>
                </div>

                {{-- Summary + results --}}
                <div id="summary-strip" class="summary-strip" style="display:none;">
                    <div class="summary-pill"><div class="num" id="sum-total">0</div><div class="lbl">Total</div></div>
                    <div class="summary-pill ready"><div class="num" id="sum-ready">0</div><div class="lbl">Ready</div></div>
                    <div class="summary-pill errors"><div class="num" id="sum-errors">0</div><div class="lbl">Need Fixing</div></div>
                    <div class="summary-pill"><div class="num" id="sum-imported">0</div><div class="lbl">Imported</div></div>
                </div>

                <div style="display:flex;align-items:center;gap:14px;flex-wrap:wrap;margin-top:4px;">
                    <div id="result-message" style="font-weight:600;"></div>
                    <button type="button" class="btn-outline" id="btn-download-errors" style="display:none;padding:7px 16px;font-size:.82rem;">
                        <i class="fas fa-file-csv"></i> Download Error Report (CSV)
                    </button>
                </div>

                <div class="result-table-wrap" id="result-table-wrap" style="display:none;">
                    <table class="result-table">
                        <thead>
                            <tr>
                                <th>File</th>
                                <th>Detected</th>
                                <th>Matched Student</th>
                                <th>Status</th>
                                <th>Details</th>
                            </tr>
                        </thead>
                        <tbody id="result-tbody"></tbody>
                    </table>
                </div>
            </div>

            <a href="{{ route('students.all.students') }}" class="btn-outline" style="margin-bottom:32px; display: inline-flex;">
                <i class="fas fa-arrow-left"></i> Back to Students
            </a>
        </div>
    </div>
     </div>
    </div>
@endsection

@section('js')
    <script>
        const csrfToken = '{{ csrf_token() }}';
        const processUrl = '{{ route('students.bulk.photo.import') }}';
        let selectedFiles = []; // { file, previewUrl }
        let lastReportByFile = {}; // filename -> result entry, after a check
        let lastResults = []; // full results array from the last check/import, in order

        // Class -> Stream
        document.getElementById('class_id').addEventListener('change', function () {
            const classId = this.value;
            const streamSelect = document.getElementById('stream_id');
            streamSelect.innerHTML = '<option value="">Loading...</option>';
            if (!classId) { streamSelect.innerHTML = '<option value="">-- Select Stream --</option>'; return; }

            fetch(`/students/streams/by-class?class_id=${encodeURIComponent(classId)}`)
                .then(r => r.json())
                .then(data => {
                    streamSelect.innerHTML = '<option value="">-- Select Stream --</option>';
                    data.forEach(s => {
                        const opt = document.createElement('option');
                        opt.value = s.stream_id;
                        opt.textContent = s.display_name || s.stream_id;
                        streamSelect.appendChild(opt);
                    });
                    resetChecks();
                });
        });
        document.getElementById('stream_id').addEventListener('change', resetChecks);

        // Match-by picker
        document.querySelectorAll('.match-option').forEach(el => {
            el.addEventListener('click', function () {
                document.querySelectorAll('.match-option').forEach(o => o.classList.remove('active'));
                this.classList.add('active');
                document.getElementById('match_by').value = this.dataset.value;
                resetChecks();
            });
        });

        // Drag & drop / file picking
        const zone = document.getElementById('upload-zone');
        zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('drag-over'); });
        zone.addEventListener('dragleave', () => zone.classList.remove('drag-over'));
        zone.addEventListener('drop', e => {
            e.preventDefault();
            zone.classList.remove('drag-over');
            addFiles(e.dataTransfer.files);
        });
        document.getElementById('file-input').addEventListener('change', function () {
            addFiles(this.files);
            this.value = '';
        });

        function addFiles(fileList) {
            const existingNames = new Set(selectedFiles.map(f => f.file.name));
            Array.from(fileList).forEach(file => {
                if (existingNames.has(file.name)) return; // skip exact duplicate re-add
                selectedFiles.push({ file, previewUrl: URL.createObjectURL(file) });
            });
            resetChecks();
            renderThumbs();
        }

        function removeFile(name) {
            selectedFiles = selectedFiles.filter(f => f.file.name !== name);
            resetChecks();
            renderThumbs();
        }

        function renderThumbs() {
            const grid = document.getElementById('thumb-grid');
            grid.innerHTML = '';
            selectedFiles.forEach(({ file, previewUrl }) => {
                const result = lastReportByFile[file.name];
                let statusClass = 'status-pending';
                let statusIcon = '';
                if (result) {
                    statusClass = 'status-' + result.status;
                    statusIcon = result.status === 'error' ? 'fa-xmark' : 'fa-check';
                }

                const card = document.createElement('div');
                card.className = 'thumb-card';
                card.innerHTML = `
                    <button type="button" class="thumb-remove" title="Remove"><i class="fas fa-xmark"></i></button>
                    ${result ? `<div class="thumb-status ${statusClass}"><i class="fas ${statusIcon}"></i></div>` : ''}
                    <img src="${previewUrl}" alt="">
                    <div class="thumb-name" title="${file.name}">${file.name}</div>
                `;
                card.querySelector('.thumb-remove').addEventListener('click', (e) => {
                    e.stopPropagation();
                    removeFile(file.name);
                });
                grid.appendChild(card);
            });

            document.getElementById('btn-check').disabled = selectedFiles.length === 0 || !formReady();
        }

        function formReady() {
            return document.getElementById('class_id').value &&
                document.getElementById('stream_id').value &&
                document.getElementById('match_by').value;
        }

        function resetChecks() {
            lastReportByFile = {};
            lastResults = [];
            document.getElementById('btn-import').disabled = true;
            document.getElementById('summary-strip').style.display = 'none';
            document.getElementById('result-table-wrap').style.display = 'none';
            document.getElementById('result-message').textContent = '';
            document.getElementById('btn-download-errors').style.display = 'none';
            document.getElementById('btn-check').disabled = selectedFiles.length === 0 || !formReady();
            renderThumbStatusesOnly();
        }

        // Builds a CSV of every flagged row from the last check/import so an
        // admin can work through renames in Excel instead of on-screen.
        function csvField(value) {
            const str = (value ?? '').toString();
            if (/[",\n]/.test(str)) {
                return '"' + str.replace(/"/g, '""') + '"';
            }
            return str;
        }

        document.getElementById('btn-download-errors').addEventListener('click', function () {
            const errorRows = lastResults.filter(r => r.status === 'error');
            if (!errorRows.length) return;

            const header = ['File Name', 'Detected Value', 'Reason'];
            const lines = [header.map(csvField).join(',')];
            errorRows.forEach(r => {
                lines.push([r.file, r.identifier || '', r.reason || ''].map(csvField).join(','));
            });

            // Prefix with a UTF-8 BOM so Excel opens accented characters correctly.
            const blob = new Blob(['\ufeff' + lines.join('\r\n')], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            const stamp = new Date().toISOString().slice(0, 19).replace(/[:T]/g, '-');
            a.href = url;
            a.download = `photo-import-errors-${stamp}.csv`;
            document.body.appendChild(a);
            a.click();
            a.remove();
            URL.revokeObjectURL(url);
        });

        function renderThumbStatusesOnly() {
            document.querySelectorAll('#thumb-grid .thumb-card').forEach(card => {
                const badge = card.querySelector('.thumb-status');
                if (badge) badge.remove();
            });
        }

        function buildFormData(dryRun) {
            const fd = new FormData();
            fd.append('_token', csrfToken);
            fd.append('class_id', document.getElementById('class_id').value);
            fd.append('stream_id', document.getElementById('stream_id').value);
            fd.append('match_by', document.getElementById('match_by').value);
            fd.append('dry_run', dryRun ? '1' : '0');
            selectedFiles.forEach(({ file }) => fd.append('photos[]', file));
            return fd;
        }

        document.getElementById('btn-check').addEventListener('click', function () {
            if (!formReady() || !selectedFiles.length) { return; }
            runImport(true);
        });

        document.getElementById('btn-import').addEventListener('click', function () {
            if (!formReady() || !selectedFiles.length) { return; }
            runImport(false);
        });

        function runImport(dryRun) {
            const btn = dryRun ? document.getElementById('btn-check') : document.getElementById('btn-import');
            const spinner = dryRun ? document.getElementById('check-spinner') : document.getElementById('import-spinner');
            document.getElementById('btn-check').disabled = true;
            document.getElementById('btn-import').disabled = true;
            spinner.style.display = 'inline-block';

            fetch(processUrl, { method: 'POST', body: buildFormData(dryRun) })
                .then(r => r.json())
                .then(data => showReport(data, dryRun))
                .catch(err => {
                    document.getElementById('result-message').innerHTML =
                        `<span style="color:var(--r)"><i class="fas fa-triangle-exclamation me-1"></i>Something went wrong: ${err.message}</span>`;
                })
                .finally(() => {
                    spinner.style.display = 'none';
                    document.getElementById('btn-check').disabled = selectedFiles.length === 0 || !formReady();
                });
        }

        function showReport(data, dryRun) {
            if (data.status === 'error') {
                document.getElementById('result-message').innerHTML =
                    `<span style="color:var(--r)"><i class="fas fa-triangle-exclamation me-1"></i>${data.message}</span>`;
                return;
            }

            lastReportByFile = {};
            lastResults = data.results || [];
            lastResults.forEach(r => { lastReportByFile[r.file] = r; });
            renderThumbs();

            const s = data.summary || {};
            document.getElementById('summary-strip').style.display = 'flex';
            document.getElementById('sum-total').textContent = s.total ?? 0;
            document.getElementById('sum-ready').textContent = s.ready ?? 0;
            document.getElementById('sum-errors').textContent = s.errors ?? 0;
            document.getElementById('sum-imported').textContent = s.imported ?? 0;

            const color = (s.errors ?? 0) > 0 ? 'var(--a)' : 'var(--g)';
            document.getElementById('result-message').innerHTML = `<span style="color:${color}">${data.message}</span>`;

            const tbody = document.getElementById('result-tbody');
            tbody.innerHTML = '';
            (data.results || []).forEach(r => {
                const tr = document.createElement('tr');
                const studentCell = r.student
                    ? `${r.student.name}<br><span style="color:var(--t3);font-size:.74rem">LIN: ${r.student.admission_number ?? '—'} · Reg: ${r.student.registration_number ?? '—'}</span>`
                    : '—';
                const badgeClass = r.status === 'error' ? 'badge-error' : (r.status === 'imported' ? 'badge-imported' : 'badge-ready');
                const statusLabel = r.status === 'ready' ? 'Ready' : (r.status === 'imported' ? 'Imported' : 'Needs Fixing');
                tr.innerHTML = `
                    <td>${r.file}</td>
                    <td>${r.identifier || '—'}</td>
                    <td>${studentCell}</td>
                    <td><span class="badge-status ${badgeClass}">${statusLabel}</span></td>
                    <td>${r.reason ? `<span style="color:var(--r)">${r.reason}</span>` : ''}</td>
                `;
                tbody.appendChild(tr);
            });
            document.getElementById('result-table-wrap').style.display = 'block';

            const errorBtn = document.getElementById('btn-download-errors');
            errorBtn.style.display = (s.errors ?? 0) > 0 ? 'inline-flex' : 'none';

            if (dryRun) {
                document.getElementById('btn-import').disabled = (s.ready ?? 0) === 0;
            } else {
                // After a real import, re-checking is the safest next step
                // if the admin adds/replaces any files.
                document.getElementById('btn-import').disabled = true;
            }
        }
    </script>
@endsection