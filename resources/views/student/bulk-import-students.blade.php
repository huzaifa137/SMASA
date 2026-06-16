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

        /* Make steps stack on smaller screens */
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

        label.form-label {
            font-weight: 600;
            font-size: .85rem;
            color: var(--t1);
            margin-bottom: 6px;
        }

        .form-control,
        .form-select {
            border-radius: 8px;
            border: 1.5px solid var(--brd);
            font-size: .9rem;
            padding: 10px 14px;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--b);
            box-shadow: 0 0 0 3px var(--bl);
            outline: none;
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

        .btn-primary-custom:hover {
            background: var(--b2);
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

        /* Make buttons full width on mobile */
        @media (max-width: 576px) {

            .btn-primary-custom,
            .btn-outline {
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
            color: var(--b);
            margin-top: 8px;
            font-weight: 500;
        }

        .spinner-border-sm {
            width: 1rem;
            height: 1rem;
            border-width: .15em;
        }

        /* Additional responsive adjustments */
        @media (max-width: 768px) {
            .page-header h2 {
                font-size: 1.3rem;
            }

            .page-header p {
                font-size: .85rem;
            }
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
    </style>
@endsection

@section('content')
    <div class="side-app">
        <div class="container-fluid">
            <div class="page-header">
                <h2><i class="fas fa-file-import me-2" style="color:var(--b)"></i> Bulk Import Students</h2>
                <p>Import multiple students at once using an Excel spreadsheet template.</p>
            </div>

            {{-- Steps --}}
            <div class="steps">
                <div class="step">
                    <div class="step-num">Step 1</div>
                    <div class="step-title">Select Class & Stream</div>
                    <div class="step-desc">Choose the class, stream and year for the import.</div>
                </div>
                <div class="step">
                    <div class="step-num">Step 2</div>
                    <div class="step-title">Download Template</div>
                    <div class="step-desc">Get the pre-filled Excel template for that class.</div>
                </div>
                <div class="step">
                    <div class="step-num">Step 3</div>
                    <div class="step-title">Fill & Upload</div>
                    <div class="step-desc">Enter student names, then upload the completed file.</div>
                </div>
            </div>

            <div class="card-box">
                <div class="section-title">Class & Stream Configuration</div>
                <div
                    style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); border-radius: 24px; padding: 32px;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
                        <div>
                            <div
                                style="color: #94a3b8; font-size: 12px; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 20px;">
                                Basic Configuration</div>
                            <div style="margin-bottom: 20px;">
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
                        <div>
                            <div
                                style="color: #94a3b8; font-size: 12px; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 20px;">
                                Academic Details</div>
                            <div style="margin-bottom: 20px;">
                                <label
                                    style="display: block; color: #cbd5e1; font-size: 13px; margin-bottom: 8px;">Admission
                                    Year <span style="color:#ef4444">*</span></label>
                                <select id="year"
                                    style="width: 100%; padding: 12px; background: #334155; border: 1px solid #475569; border-radius: 12px; color: white;">
                                    @for($y = date('Y'); $y >= date('Y') - 3; $y--)
                                        <option value="{{ $y }}" @if($y == date('Y')) selected @endif>{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div>
                                <label style="display: block; color: #cbd5e1; font-size: 13px; margin-bottom: 8px;">School
                                    Category <span style="color:#ef4444">*</span></label>
                                <select id="category"
                                    style="width: 100%; padding: 12px; background: #334155; border: 1px solid #475569; border-radius: 12px; color: white;">
                                    <option value="">-- Select Category --</option>
                                    @if ($schoolProduct === 'Idaad And Thanawi')
                                        <option value="ID">Idaad - ID</option>
                                        <option value="TH">Thanawi - TH</option>
                                    @elseif($schoolProduct === 'Primary Theology')
                                        <option value="PRT">Primary Theology - PRT</option>
                                    @elseif($schoolProduct === 'Primary Secular')
                                        <option value="PRS">Primary Secular - PRS</option>
                                    @elseif($schoolProduct === 'Both Primary Theology and Secular')
                                        <option value="BPT-BPS">Both Primary Theology and Secular - BPT-BPS</option>
                                    @endif
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <hr class="divider">

                {{-- Download Template --}}
                <div class="section-title">Download Import Template</div>
                <p style="color:var(--t2);font-size:.9rem;">Download the Excel template for the selected class and stream.
                    The template includes sample rows to guide your data entry.</p>
                <button type="button" class="btn-outline" id="btn-download-template">
                    <i class="fas fa-download"></i> Download Template
                </button>

                <hr class="divider">

                {{-- Upload Section --}}
                <div class="section-title">Upload Completed File</div>
                <div class="upload-zone" id="upload-zone" onclick="document.getElementById('file-input').click()">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <p>Click to browse or drag & drop your Excel file here</p>
                    <small>Supported: .xlsx, .xls &nbsp;|&nbsp; Required columns: firstname, lastname, gender</small>
                </div>
                <input type="file" id="file-input" accept=".xlsx,.xls" style="display:none">
                <div id="file-name-display"></div>

                <div style="margin-top: 20px; display:flex; gap:12px; align-items:center; flex-wrap: wrap;">
                    <button type="button" class="btn-primary-custom" id="btn-import" disabled>
                        <span id="import-spinner" class="spinner-border spinner-border-sm me-2" style="display:none"></span>
                        <i class="fas fa-upload me-1"></i> Import Students
                    </button>
                    <span style="font-size:.82rem;color:var(--t2)">Only filled rows will be imported.</span>
                </div>

                {{-- Result --}}
                <div id="result-box" class="result-box">
                    <div id="result-message"></div>
                    <div class="err-list" id="err-list"></div>
                </div>
            </div>

            <a href="{{ route('students.all.students') }}" class="btn-outline"
                style="margin-bottom:32px; display: inline-flex;">
                <i class="fas fa-arrow-left"></i> Back to Students
            </a>
        </div>
    </div>
    </div>
    </div>
@endsection

@section('js')
    <script>
        const schoolId = {{ $schoolId }};
        const csrfToken = '{{ csrf_token() }}';
        let selectedFile = null;

        // Load streams when class changes
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
                });
        });

        // Download template
        document.getElementById('btn-download-template').addEventListener('click', function () {
            const classId = document.getElementById('class_id').value;
            const streamId = document.getElementById('stream_id').value;
            const year = document.getElementById('year').value;
            if (!classId || !streamId) { alert('Please select both a class and stream first.'); return; }
            const categoryId = document.getElementById('category').value;
            if (!categoryId) { alert('Please select a category first.'); return; }
            const url = `/students/download-template?class_id=${encodeURIComponent(classId)}&stream_id=${encodeURIComponent(streamId)}&year=${year}&category=${encodeURIComponent(categoryId)}`;
            window.location.href = url;
        });

        // File drag & drop
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

        // Import
        document.getElementById('btn-import').addEventListener('click', function () {
            if (!selectedFile) return;
            const classId = document.getElementById('class_id').value;
            const streamId = document.getElementById('stream_id').value;
            const year = document.getElementById('year').value;
            const categoryId = document.getElementById('category').value;
            if (!classId || !streamId || !year || !categoryId) { alert('Please select class, stream, year and category.'); return; }

            const spinner = document.getElementById('import-spinner');
            spinner.style.display = 'inline-block';
            this.disabled = true;

            const fd = new FormData();
            fd.append('_token', csrfToken);
            fd.append('class_id', classId);
            fd.append('stream_id', streamId);
            fd.append('year', year);
            fd.append('category', categoryId);
            fd.append('file', selectedFile);

            fetch('/students/bulk-import', { method: 'POST', body: fd })
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