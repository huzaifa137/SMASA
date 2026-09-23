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

        .collapsible-section {
            border: 1.5px solid var(--brd);
            border-radius: var(--rads);
            overflow: hidden;
        }

        .collapsible-toggle {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            background: var(--bg);
            border: none;
            padding: 14px 18px;
            cursor: pointer;
            text-align: left;
        }

        .collapsible-toggle:hover {
            background: var(--bl);
        }

        .collapsible-toggle-left {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .collapsible-chevron {
            font-size: .8rem;
            color: var(--b);
            transition: transform .2s;
        }

        .collapsible-toggle[aria-expanded="true"] .collapsible-chevron {
            transform: rotate(90deg);
        }

        .collapsible-badge {
            font-size: .75rem;
            font-weight: 700;
            color: var(--b);
            background: var(--bl);
            border-radius: 999px;
            padding: 3px 12px;
            white-space: nowrap;
        }

        .collapsible-body {
            padding: 0 18px 18px;
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
    {{-- SweetAlert2 CSS --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
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
                                    @elseif($schoolProduct === 'Secondary')
                                        <option value="SEC-OL" data-secondary-level="olevel">Secondary O-Level - SEC-OL</option>
                                        <option value="SEC-AL" data-secondary-level="alevel">Secondary A-Level - SEC-AL</option>
                                    @endif
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ===== A-LEVEL / O-LEVEL SUBJECT GUIDANCE =====
                Shown only when the selected Category is Secondary A-Level
                or O-Level — explains the extra template columns
                StudentBulkTemplate adds for those levels, and lists the
                exact subject names StudentBulkImport will match against
                (master_datas + this school's own alevel-combinations /
                o-level-electives additions). Hidden otherwise. --}}
                <div id="aLevelGuidance" style="display:none; margin-top:20px; padding:18px 20px; background:#eef0ff; border:1.5px solid #d6d9ff; border-radius:14px;">
                    <div style="font-weight:700; color:#1e1b4b; font-size:.95rem; margin-bottom:6px;">
                        <i class="fas fa-graduation-cap me-1" style="color:var(--b)"></i> Importing A-Level students
                    </div>
                    <p style="font-size:.85rem; color:#4b4880; margin-bottom:10px;">
                        The template will include <strong>principal_1</strong>, <strong>principal_2</strong>,
                        <strong>principal_3</strong> and <strong>subsidiary</strong> columns. Fill each with the exact
                        subject name (see the "Valid Subjects" tab in the downloaded file) and every student's
                        combination will be assigned automatically — no separate trip to
                        <a href="{{ route('alevel.combinations.entry') }}" target="_blank">A-Level Combinations</a> needed.
                        General Paper is compulsory and doesn't need a column. Leave subsidiary blank if not applicable.
                    </p>
                    <div id="aLevelSubjectPills" style="display:flex; flex-wrap:wrap; gap:6px; max-height:110px; overflow-y:auto;"></div>
                </div>

                <div id="oLevelGuidance" style="display:none; margin-top:20px; padding:18px 20px; background:#eef0ff; border:1.5px solid #d6d9ff; border-radius:14px;">
                    <div style="font-weight:700; color:#1e1b4b; font-size:.95rem; margin-bottom:6px;">
                        <i class="fas fa-graduation-cap me-1" style="color:var(--b)"></i> Importing O-Level students
                    </div>
                    <p style="font-size:.85rem; color:#4b4880; margin-bottom:10px;">
                        The template will include <strong>elective_1</strong> and <strong>elective_2</strong> columns
                        (up to 2 electives per student, on top of the class's compulsory subjects set on
                        <a href="{{ route('school.create-class') }}" target="_blank">Create Class</a>). Fill in
                        the exact elective name (see the "Valid Subjects" tab in the downloaded file) and each
                        student's electives will be assigned automatically — no separate trip to
                        <a href="{{ route('olevel.electives.entry') }}" target="_blank">O-Level Electives</a> needed.
                        Leave blank if a student hasn't decided yet.
                    </p>
                    <div id="oLevelSubjectPills" style="display:flex; flex-wrap:wrap; gap:6px; max-height:110px; overflow-y:auto;"></div>
                </div>

                <hr class="divider">

                {{-- ===== IMPORT MODE ===== --}}
                <div class="section-title">Import Mode</div>
                <p style="color:var(--t2);font-size:.9rem;margin-bottom:14px;">Choose whether this template/upload
                    creates new students, or updates bio-data on students already in this class/stream.</p>
                <div style="display:flex; gap:16px; flex-wrap:wrap; margin-bottom:10px;">
                    <label style="display:flex; align-items:center; gap:8px; cursor:pointer; background:var(--bg); border:1.5px solid var(--brd); border-radius:10px; padding:12px 16px; flex:1; min-width:220px;">
                        <input type="radio" name="import_mode" id="mode_create" value="create" checked>
                        <span>
                            <strong style="font-size:.88rem;color:var(--t1);">Add new students</strong><br>
                            <span style="font-size:.78rem;color:var(--t2);">Blank template — one row per new admission.</span>
                        </span>
                    </label>
                    <label style="display:flex; align-items:center; gap:8px; cursor:pointer; background:var(--bg); border:1.5px solid var(--brd); border-radius:10px; padding:12px 16px; flex:1; min-width:220px;">
                        <input type="radio" name="import_mode" id="mode_update" value="update">
                        <span>
                            <strong style="font-size:.88rem;color:var(--t1);">Update existing students</strong><br>
                            <span style="font-size:.78rem;color:var(--t2);">Template is pre-filled with current students — fill in the blanks.</span>
                        </span>
                    </label>
                </div>

                <div id="updateModeOptions" style="display:none; margin-top:12px; padding:16px 18px; background:#eef0ff; border:1.5px solid #d6d9ff; border-radius:14px;">
                    <label class="form-label" style="margin-bottom:8px;">Match existing students by</label>
                    <select id="match_by" style="width:100%; max-width:320px; padding:10px 14px; border-radius:8px; border:1.5px solid var(--brd); font-size:.9rem;">
                        <option value="reg" selected>Registration No. (recommended)</option>
                        <option value="lin">LIN No.</option>
                        <option value="name">Firstname + Lastname</option>
                    </select>
                    <p style="font-size:.8rem;color:#4b4880;margin:10px 0 0;">
                        Registration No. and LIN No. are unique per student, so they're the safest way to match.
                        Matching by name only works reliably if no two students in this class/stream share the exact
                        same first and last name. A blank cell in the uploaded file never erases existing data —
                        only cells you actually fill in are applied.
                    </p>
                </div>

                <hr class="divider">

                {{-- ===== OPTIONAL BIO-DATA COLUMNS (collapsible) ===== --}}
                <div class="collapsible-section" id="optionalFieldsSection">
                    <button type="button" class="collapsible-toggle" id="optionalFieldsToggle" aria-expanded="false">
                        <span class="collapsible-toggle-left">
                            <i class="fas fa-chevron-right collapsible-chevron" id="optionalFieldsChevron"></i>
                            <span class="section-title" style="margin-bottom:0;">Optional Columns to Include</span>
                        </span>
                        <span class="collapsible-badge" id="optionalFieldsBadge" style="display:none;">0 selected</span>
                    </button>
                    <div class="collapsible-body" id="optionalFieldsBody" style="display:none;">
                        <p style="color:var(--t2);font-size:.9rem;margin:14px 0;">Tick any extra bio-data you already
                            have on hand (e.g. from a spreadsheet like your school's existing student register) so it
                            can be entered in the same pass as names — instead of a separate trip to each student's
                            profile later.</p>
                        <div style="display:flex; gap:10px; margin-bottom:14px;">
                            <button type="button" class="btn-outline" id="btn-fields-all" style="padding:6px 16px;font-size:.8rem;">Select All</button>
                            <button type="button" class="btn-outline" id="btn-fields-none" style="padding:6px 16px;font-size:.8rem;">Clear</button>
                        </div>
                        <div id="optionalFieldsGrid" style="display:grid; grid-template-columns:repeat(auto-fill, minmax(220px, 1fr)); gap:8px 18px; margin-bottom:6px;">
                            @foreach($optionalFieldGroups ?? [] as $group => $fields)
                                <div style="grid-column:1/-1; font-size:.75rem; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:var(--t3); margin-top:8px;">{{ $group }}</div>
                                @foreach($fields as $field)
                                    <label style="display:flex; align-items:center; gap:8px; font-size:.86rem; color:var(--t1); cursor:pointer;">
                                        <input type="checkbox" class="optional-field-checkbox" value="{{ $field['key'] }}">
                                        {{ $field['label'] }}
                                    </label>
                                @endforeach
                            @endforeach
                        </div>
                    </div>
                </div>

                <hr class="divider">


                {{-- Download Template --}}
                <div class="section-title">Download Import Template</div>
                <p style="color:var(--t2);font-size:.9rem;" id="templateHelpText">Download the Excel template for the
                    selected class and stream. The template includes sample rows to guide your data entry.</p>
                <button type="button" class="btn-outline" id="btn-download-template">
                    <i class="fas fa-download"></i> Download Template
                </button>

                <hr class="divider">

                {{-- Upload Section --}}
                <div class="section-title">Upload Completed File</div>
                <div class="upload-zone" id="upload-zone" onclick="document.getElementById('file-input').click()">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <p>Click to browse or drag & drop your Excel file here</p>
                    <small id="uploadHint">Supported: .xlsx, .xls &nbsp;|&nbsp; Required columns: firstname, lastname, gender</small>
                </div>
                <input type="file" id="file-input" accept=".xlsx,.xls" style="display:none">
                <div id="file-name-display"></div>

                <div style="margin-top: 20px; display:flex; gap:12px; align-items:center; flex-wrap: wrap;">
                    <button type="button" class="btn-primary-custom" id="btn-import" disabled>
                        <span id="import-spinner" class="spinner-border spinner-border-sm me-2" style="display:none"></span>
                        <i class="fas fa-upload me-1"></i> <span id="btn-import-label">Import Students</span>
                    </button>
                    <span style="font-size:.82rem;color:var(--t2)" id="importHelpText">Only filled rows will be imported.</span>
                </div>

                {{-- Result --}}
                <div id="result-box" class="result-box">
                    <div id="result-message"></div>
                    <div class="err-list" id="err-list"></div>
                </div>
            </div>


            <div style="display:flex;gap:12px;flex-wrap:wrap;margin-bottom:32px;">
                <a href="{{ route('students.all.students') }}" class="btn-outline" style="display: inline-flex;">
                    <i class="fas fa-arrow-left"></i> Back to Students
                </a>
                <a href="{{ route('students.bulk.photo.import.form') }}" class="btn-outline" style="display: inline-flex;">
                    <i class="fas fa-portrait"></i> Bulk Photo Import
                </a>
            </div>
        </div>
    </div>
    </div>
    </div>
@endsection

@section('js')
    {{-- SweetAlert2 JS --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

    <script>
        const schoolId = {{ $schoolId }};
        const csrfToken = '{{ csrf_token() }}';
        let selectedFile = null;

        // SweetAlert2 toast mixin for quick info messages
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            }
        });

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

        // Secondary O-Level (Senior 1-4) vs A-Level (Senior 5/6) — narrow
        // the Category dropdown down to just the one option that actually
        // applies to whichever class was picked, instead of always
        // offering both regardless of class. Only relevant for schools on
        // the 'Secondary' product — every category select option is
        // tagged data-secondary-level="olevel"/"alevel" for that product
        // only (see bulk-import-students.blade.php's @@elseif above), so
        // every other product's options are left untouched.
        const secondaryOLevelClassIds = @json($secondaryOLevelClassIds ?? []);
        const secondaryALevelClassIds = @json($secondaryALevelClassIds ?? []);
        (function () {
            const categorySelect = document.getElementById('category');
            const secondaryOptions = categorySelect
                ? Array.from(categorySelect.querySelectorAll('option[data-secondary-level]'))
                : [];

            if (!categorySelect || secondaryOptions.length === 0) {
                return; // not a 'Secondary' product school — nothing to filter
            }

            document.getElementById('class_id').addEventListener('change', function () {
                const classId = this.value;
                const isOLevel = secondaryOLevelClassIds.some(id => String(id) === String(classId));
                const isALevel = secondaryALevelClassIds.some(id => String(id) === String(classId));

                secondaryOptions.forEach(opt => {
                    const level = opt.dataset.secondaryLevel;
                    const shouldShow = !classId || (level === 'olevel' && isOLevel) || (level === 'alevel' && isALevel);
                    opt.hidden = !shouldShow;
                    opt.disabled = !shouldShow;
                });

                // If the currently selected category no longer applies to
                // this class, clear it so the school can't accidentally
                // submit a mismatched category/class pair.
                const selectedOption = categorySelect.selectedOptions[0];
                if (selectedOption && selectedOption.dataset.secondaryLevel && selectedOption.disabled) {
                    categorySelect.value = '';
                }
            });
        })();

        // ===== A-Level / O-Level subject guidance panel =====
        // Shows the right panel (with the exact subject names
        // StudentBulkImport will match against) once the school picks
        // Secondary A-Level / O-Level as the Category — see
        // StudentController::resolveALevelSubjectOptions() /
        // resolveOLevelSubjectOptions() for where this data comes from.
        (function () {
            const aLevelSubjects = @json($aLevelSubjectOptions ?? ['principals' => [], 'subsidiaries' => []]);
            const oLevelSubjects = @json($oLevelSubjectOptions ?? ['electives' => []]);

            const aLevelGuidance = document.getElementById('aLevelGuidance');
            const oLevelGuidance = document.getElementById('oLevelGuidance');
            const aLevelPills = document.getElementById('aLevelSubjectPills');
            const oLevelPills = document.getElementById('oLevelSubjectPills');
            const categorySelect = document.getElementById('category');
            if (!categorySelect || !aLevelGuidance || !oLevelGuidance) return;

            function pill(name) {
                const span = document.createElement('span');
                span.textContent = name;
                span.style.cssText = 'background:#fff; border:1px solid #d6d9ff; color:#2C29CA; font-size:.72rem; font-weight:600; padding:.2rem .6rem; border-radius:999px;';
                return span;
            }

            function renderPills(container, names) {
                container.innerHTML = '';
                names.forEach(name => container.appendChild(pill(name)));
            }

            renderPills(aLevelPills, [
                ...aLevelSubjects.principals.map(s => s.name),
                ...aLevelSubjects.subsidiaries.map(s => s.name),
            ]);
            renderPills(oLevelPills, oLevelSubjects.electives.map(s => s.name));

            function updateGuidance() {
                const level = categorySelect.selectedOptions[0]?.dataset.secondaryLevel || '';
                aLevelGuidance.style.display = level === 'alevel' ? 'block' : 'none';
                oLevelGuidance.style.display = level === 'olevel' ? 'block' : 'none';
            }

            categorySelect.addEventListener('change', updateGuidance);
            updateGuidance();
        })();

        // ===== Import mode (create vs update) =====
        function currentMode() {
            return document.querySelector('input[name="import_mode"]:checked')?.value || 'create';
        }

        function selectedFields() {
            return Array.from(document.querySelectorAll('.optional-field-checkbox:checked')).map(cb => cb.value);
        }

        function updateModeUI() {
            const isUpdate = currentMode() === 'update';
            document.getElementById('updateModeOptions').style.display = isUpdate ? 'block' : 'none';
            document.getElementById('templateHelpText').textContent = isUpdate
                ? 'Downloads a template pre-filled with this class/stream\'s current students — fill in the blanks for the columns you ticked below, then re-upload.'
                : 'Download the Excel template for the selected class and stream. The template includes sample rows to guide your data entry.';
            document.getElementById('uploadHint').textContent = isUpdate
                ? 'Supported: .xlsx, .xls  |  Only cells you fill in are applied — blanks never erase existing data.'
                : 'Supported: .xlsx, .xls  |  Required columns: firstname, lastname, gender';
            document.getElementById('btn-import-label').textContent = isUpdate ? 'Update Students' : 'Import Students';
            document.getElementById('importHelpText').textContent = isUpdate
                ? 'Existing students are matched and updated — no new students are created.'
                : 'Only filled rows will be imported.';
        }

        document.getElementById('mode_create').addEventListener('change', updateModeUI);
        document.getElementById('mode_update').addEventListener('change', updateModeUI);
        updateModeUI();

        // ===== Optional Columns — collapsible panel =====
        // Collapsed by default; a click expands/collapses it, and the
        // toggle's badge always shows how many are ticked so the count
        // is visible even while the panel is closed.
        (function () {
            const toggle = document.getElementById('optionalFieldsToggle');
            const body = document.getElementById('optionalFieldsBody');
            const chevron = document.getElementById('optionalFieldsChevron');
            const badge = document.getElementById('optionalFieldsBadge');

            function setExpanded(expanded) {
                toggle.setAttribute('aria-expanded', expanded ? 'true' : 'false');
                body.style.display = expanded ? 'block' : 'none';
            }

            toggle.addEventListener('click', function () {
                setExpanded(toggle.getAttribute('aria-expanded') !== 'true');
            });

            function updateBadge() {
                const count = selectedFields().length;
                badge.textContent = count + ' selected';
                badge.style.display = count > 0 ? 'inline-block' : 'none';
            }

            document.getElementById('optionalFieldsGrid').addEventListener('change', updateBadge);
            document.getElementById('btn-fields-all').addEventListener('click', updateBadge);
            document.getElementById('btn-fields-none').addEventListener('click', updateBadge);
            updateBadge();
        })();

        document.getElementById('btn-fields-all').addEventListener('click', function () {
            document.querySelectorAll('.optional-field-checkbox').forEach(cb => cb.checked = true);
        });
        document.getElementById('btn-fields-none').addEventListener('click', function () {
            document.querySelectorAll('.optional-field-checkbox').forEach(cb => cb.checked = false);
        });

        // Download template
        document.getElementById('btn-download-template').addEventListener('click', function () {
            const classId = document.getElementById('class_id').value;
            const streamId = document.getElementById('stream_id').value;
            const year = document.getElementById('year').value;
            if (!classId || !streamId) { 
                Swal.fire({
                    icon: 'warning',
                    title: 'Missing Selection',
                    text: 'Please select both a class and stream first.',
                    confirmButtonColor: '#2f2ccb'
                });
                return; 
            }
            const categoryId = document.getElementById('category').value;
            if (!categoryId) { 
                Swal.fire({
                    icon: 'warning',
                    title: 'Missing Category',
                    text: 'Please select a category first.',
                    confirmButtonColor: '#2f2ccb'
                });
                return; 
            }

            const params = new URLSearchParams();
            params.set('class_id', classId);
            params.set('stream_id', streamId);
            params.set('year', year);
            params.set('category', categoryId);
            params.set('mode', currentMode());
            if (currentMode() === 'update') {
                params.set('match_by', document.getElementById('match_by').value);
            }
            selectedFields().forEach(f => params.append('fields[]', f));

            window.location.href = `/students/download-template?${params.toString()}`;
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
            if (!classId || !streamId || !year || !categoryId) { 
                Swal.fire({
                    icon: 'warning',
                    title: 'Incomplete Configuration',
                    text: 'Please select class, stream, year and category.',
                    confirmButtonColor: '#2f2ccb'
                });
                return; 
            }

            const spinner = document.getElementById('import-spinner');
            spinner.style.display = 'inline-block';
            this.disabled = true;

            const fd = new FormData();
            fd.append('_token', csrfToken);
            fd.append('class_id', classId);
            fd.append('stream_id', streamId);
            fd.append('year', year);
            fd.append('category', categoryId);
            fd.append('mode', currentMode());
            if (currentMode() === 'update') {
                fd.append('match_by', document.getElementById('match_by').value);
            }
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

            const successCount = data.mode === 'update' ? (data.updated ?? 0) : (data.imported ?? 0);

            if (data.status === 'success' || successCount > 0) {
                box.classList.add('result-success');
                msg.innerHTML = `<strong style="color:var(--g)"><i class="fas fa-check-circle me-1"></i>${data.message}</strong>`;
            } else {
                box.classList.add('result-error');
                msg.innerHTML = `<strong style="color:var(--r)"><i class="fas fa-times-circle me-1"></i>${data.message}</strong>`;
            }

            if (data.errors && data.errors.length) {
                if (successCount > 0) box.className = 'result-box result-warn';
                data.errors.forEach(e => {
                    const d = document.createElement('div');
                    d.className = 'err-item';
                    d.textContent = e;
                    errList.appendChild(d);
                });
            }

            // Additionally, show a SweetAlert2 popup for the overall result
            const iconType = data.status === 'success' || successCount > 0 ? 'success' : 'error';
            Swal.fire({
                icon: iconType,
                title: data.status === 'success' || successCount > 0 ? 'Completed' : 'Import Failed',
                html: `<p style="font-size:.9rem;color:var(--t2);">${data.message}</p>`,
                confirmButtonColor: '#2f2ccb'
            });
        }
    </script>
@endsection