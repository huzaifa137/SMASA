{{-- resources/views/Finance/payroll-period-form.blade.php --}}
@extends('layouts-side-bar.master')

@section('css')
    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --fin-green: #059669;
            --fin-green-l: rgba(5, 150, 105, .10);
            --fin-red: #dc2626;
            --fin-red-l: rgba(220, 38, 38, .10);
            --fin-blue: #2f2ccb;
            --fin-blue-l: rgba(47, 44, 203, .10);
            --fin-amber: #d97706;
            --fin-amber-l: rgba(217, 119, 6, .10);
            --fin-purple: #7c3aed;
            --fin-purple-l: rgba(124, 58, 237, .10);
            --fin-teal: #0d9488;
            --fin-teal-l: rgba(13, 148, 136, .10);
            --surface: #ffffff;
            --bg: #f0f4f8;
            --border: #e2e8f0;
            --text-1: #0f172a;
            --text-2: #475569;
            --text-3: #94a3b8;
            --radius: 16px;
            --radius-sm: 12px;
            --shadow: 0 1px 3px rgba(0, 0, 0, .06), 0 4px 16px rgba(0, 0, 0, .05);
            --shadow-lg: 0 8px 32px rgba(0, 0, 0, .10);
        }

        * {
            font-family: 'DM Sans', sans-serif;
            box-sizing: border-box;
        }

        body {
            background: var(--bg);
        }

        /* Hero Section */
        .fin-hero {
            background: linear-gradient(135deg, #464592 0%, #1613c9 60%, #050352 100%);
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
            width: 260px;
            height: 260px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(165, 180, 252, .2) 0%, transparent 70%);
        }

        .fin-hero h1 {
            color: #fff;
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0;
        }

        .fin-hero p {
            color: #c7d2fe;
            margin: .2rem 0 0;
            font-size: .88rem;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            background: rgba(47, 44, 203, .25);
            border: 1px solid rgba(165, 180, 252, .4);
            color: #a5b4fc;
            padding: .25rem .75rem;
            border-radius: 20px;
            font-size: .75rem;
            font-weight: 600;
            margin-bottom: .6rem;
        }

        /* Cards */
        .fin-card {
            background: var(--surface);
            border-radius: var(--radius);
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
            overflow: hidden;
            margin-bottom: 1.5rem;
        }

        .fin-card-header {
            padding: 1.1rem 1.5rem;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #fafbff;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .fin-card-header h3 {
            margin: 0;
            font-size: .95rem;
            font-weight: 700;
            color: var(--text-1);
            display: flex;
            align-items: center;
            gap: .6rem;
        }

        /* Form Container */
        .form-container {
            padding: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-group label {
            display: block;
            margin-bottom: .5rem;
            font-weight: 600;
            color: var(--text-2);
            font-size: .85rem;
        }

        .form-group label .required {
            color: var(--fin-red);
            margin-left: .2rem;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: .7rem .9rem;
            border-radius: 12px;
            border: 1.5px solid var(--border);
            font-size: .9rem;
            transition: all .2s;
            background: var(--surface);
            color: var(--text-1);
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #2f2ccb;
            box-shadow: 0 0 0 3px rgba(47, 44, 203, .1);
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }

        /* Buttons */
        .btn-fin {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            padding: .6rem 1.25rem;
            border-radius: 10px;
            font-size: .875rem;
            font-weight: 600;
            border: none;
            cursor: pointer;
            text-decoration: none;
            transition: all .18s;
        }

        .btn-sm {
            padding: .4rem .85rem;
            font-size: .8rem;
        }

        .btn-primary-fin {
            background: #2f2ccb;
            color: #fff;
        }

        .btn-primary-fin:hover {
            background: #2420a8;
            transform: translateY(-1px);
            box-shadow: 0 4px 14px rgba(47, 44, 203, .35);
        }

        .btn-outline-fin {
            background: transparent;
            border: 1.5px solid var(--border);
            color: var(--text-2);
        }

        .btn-outline-fin:hover {
            border-color: #2f2ccb;
            color: #2f2ccb;
        }

        .form-actions {
            display: flex;
            gap: .75rem;
            justify-content: flex-end;
            padding-top: 1rem;
            border-top: 1px solid var(--border);
            margin-top: 1rem;
        }

        /* Info Note */
        .info-note {
            background: rgba(47, 44, 203, .08);
            border-radius: 12px;
            padding: .8rem 1rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: .5rem;
            font-size: .8rem;
            color: #2f2ccb;
            border: 1px solid rgba(47, 44, 203, .15);
        }

        .info-note i {
            font-size: 1rem;
        }

        /* Preview Card */
        .preview-card {
            background: #f8fafc;
            border-radius: 12px;
            padding: 1rem;
            margin-top: 1rem;
            border: 1px solid var(--border);
        }

        .preview-title {
            font-weight: 700;
            font-size: .85rem;
            margin-bottom: .5rem;
            color: var(--text-1);
            display: flex;
            align-items: center;
            gap: .5rem;
        }

        .preview-item {
            display: flex;
            justify-content: space-between;
            padding: .3rem 0;
            font-size: .8rem;
        }

        .preview-label {
            color: var(--text-3);
        }

        .preview-value {
            font-weight: 600;
            color: var(--text-1);
        }

        /* Alert Warning */
        .alert-warning {
            background: var(--fin-amber-l);
            border-left: 3px solid var(--fin-amber);
            padding: .8rem;
            margin-bottom: 1rem;
            border-radius: 8px;
            font-size: .8rem;
            color: #78350f;
        }

        /* Loading Overlay */
        .loading {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, .5);
            backdrop-filter: blur(4px);
            z-index: 9999;
            align-items: center;
            justify-content: center;
        }

        .loading-spinner {
            width: 50px;
            height: 50px;
            border: 4px solid var(--border);
            border-top-color: #2f2ccb;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* Responsive */
        @media(max-width:768px) {
            .fin-hero {
                padding: 1.5rem;
            }

            .fin-hero h1 {
                font-size: 1.3rem;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .form-actions {
                flex-direction: column-reverse;
            }

            .form-actions .btn-fin {
                width: 100%;
                justify-content: center;
            }

            .fin-card-header {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
@endsection

@section('page-header')
    <div class="fin-hero mt-5">
        <div style="position:relative;z-index:1;">
            <div class="hero-badge"><i class="fas fa-plus-circle"></i> HR & Payroll — New Payroll Period</div>
            <h1>Create Payroll Period</h1>
            <p>Define a payroll period and generate payslips for all teachers</p>
        </div>
    </div>
@endsection

@section('content')
    <div class="fin-card">
        <div class="fin-card-header">
            <h3><i class="fas fa-calendar-plus"></i> Payroll Period Information</h3>
            <span style="font-size:.75rem;color:var(--text-3);">New Period</span>
        </div>

        <form method="POST" action="{{ route('finance.payroll.store') }}" id="payrollForm">
            @csrf

            <div class="form-container">
                {{-- Info Note --}}
                <div class="info-note">
                    <i class="fas fa-info-circle"></i>
                    <span>Creating a payroll period will automatically generate payslips for all teachers with active salary
                        structures.</span>
                </div>

                {{-- Period Name --}}
                <div class="form-group">
                    <label>Period Name <span class="required">*</span></label>
                    <input type="text" name="period_name" id="period_name" required value="{{ old('period_name') }}"
                        placeholder="e.g., January 2026, Term 1 Salaries">
                    <small style="color:var(--text-3);display:block;margin-top:.3rem;">A descriptive name for this payroll
                        period</small>
                </div>

                {{-- Academic Year & Term --}}
                <div class="form-row">
                    <div class="form-group">
                        <label>Academic Year <span class="required">*</span></label>
                        <select name="academic_year" id="academic_year" required>
                            <option value="{{ date('Y') }}" {{ old('academic_year') == date('Y') ? 'selected' : '' }}>
                                {{ date('Y') }}</option>
                            <option value="{{ date('Y') - 1 }}" {{ old('academic_year') == date('Y') - 1 ? 'selected' : '' }}>
                                {{ date('Y') - 1 }}</option>
                            <option value="{{ date('Y') - 2 }}" {{ old('academic_year') == date('Y') - 2 ? 'selected' : '' }}>
                                {{ date('Y') - 2 }}</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Term (Optional)</label>
                        <select name="term" id="term">
                            <option value="">-- Not Applicable --</option>
                            <option value="1" {{ old('term') == '1' ? 'selected' : '' }}>Term 1</option>
                            <option value="2" {{ old('term') == '2' ? 'selected' : '' }}>Term 2</option>
                            <option value="3" {{ old('term') == '3' ? 'selected' : '' }}>Term 3</option>
                        </select>
                        <small style="color:var(--text-3);display:block;margin-top:.3rem;">Link to academic term if
                            applicable</small>
                    </div>
                </div>

                {{-- Period Dates --}}
                <div class="form-row">
                    <div class="form-group">
                        <label>Period Start Date <span class="required">*</span></label>
                        <input type="date" name="period_start" id="period_start" required
                            value="{{ old('period_start', date('Y-m-01')) }}">
                    </div>
                    <div class="form-group">
                        <label>Period End Date <span class="required">*</span></label>
                        <input type="date" name="period_end" id="period_end" required
                            value="{{ old('period_end', date('Y-m-t')) }}">
                    </div>
                </div>

                {{-- Preview Section (dynamic) --}}
                <div id="previewSection" class="preview-card" style="display:none;">
                    <div class="preview-title"><i class="fas fa-chart-line" style="color:#2f2ccb;"></i> Period Preview</div>
                    <div class="preview-item">
                        <span class="preview-label">Period Duration:</span>
                        <span class="preview-value" id="previewDuration">—</span>
                    </div>
                    <div class="preview-item">
                        <span class="preview-label">Teachers with Salary Structures:</span>
                        <span class="preview-value" id="previewTeacherCount">Loading...</span>
                    </div>
                    <div class="preview-item">
                        <span class="preview-label">Estimated Gross Payroll:</span>
                        <span class="preview-value" id="previewEstimatedGross">—</span>
                    </div>
                </div>

                {{-- Warning if no salary structures --}}
                <div id="noStructuresWarning" class="alert-warning" style="display:none;">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>No active salary structures found!</strong> Please set up teacher salary structures before
                    creating a payroll period.
                    <div style="margin-top:.5rem;">
                        <a href="{{ route('finance.salary-structures') }}" class="btn-fin btn-sm"
                            style="background:var(--fin-amber);color:#fff;padding:.3rem .8rem;">
                            <i class="fas fa-plus"></i> Go to Salary Structures
                        </a>
                    </div>
                </div>

                {{-- Form Actions --}}
                <div class="form-actions">
                    <a href="{{ route('finance.payroll.index') }}" class="btn-fin btn-outline-fin">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                    <button type="submit" class="btn-fin btn-primary-fin" id="submitBtn">
                        <i class="fas fa-save"></i> Create Payroll Period
                    </button>
                </div>
            </div>
        </form>
    </div>
    </div>
            </div>

    {{-- Loading Overlay --}}
    <div id="loadingOverlay" class="loading">
        <div class="loading-spinner"></div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // DOM Elements
        const periodStart = document.getElementById('period_start');
        const periodEnd = document.getElementById('period_end');
        const previewSection = document.getElementById('previewSection');
        const previewDuration = document.getElementById('previewDuration');
        const previewTeacherCount = document.getElementById('previewTeacherCount');
        const previewEstimatedGross = document.getElementById('previewEstimatedGross');
        const noStructuresWarning = document.getElementById('noStructuresWarning');
        const submitBtn = document.getElementById('submitBtn');
        const academicYear = document.getElementById('academic_year');
        const periodName = document.getElementById('period_name');

        // Calculate days between dates
        function calculateDuration(startDate, endDate) {
            if (!startDate || !endDate) return null;
            const start = new Date(startDate);
            const end = new Date(endDate);
            const diffTime = Math.abs(end - start);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            return diffDays + 1;
        }

        // Format date
        function formatDate(dateString) {
            if (!dateString) return '—';
            const date = new Date(dateString);
            return date.toLocaleDateString('en-UG', { year: 'numeric', month: 'short', day: 'numeric' });
        }

        // Update preview
        function updatePreview() {
            const start = periodStart.value;
            const end = periodEnd.value;

            if (start && end) {
                const duration = calculateDuration(start, end);
                previewDuration.innerHTML = `${formatDate(start)} — ${formatDate(end)} <span style="color:var(--text-3);">(${duration} days)</span>`;
                previewSection.style.display = 'block';
            } else {
                previewSection.style.display = 'none';
            }
        }

        // Fetch teacher stats via AJAX
       // Use server-side data passed from controller — no AJAX needed
function fetchTeacherStats() {
    const count = {{ $teachersWithStructure->count() }};
    const gross = {{ $totalGross }};

    if (count > 0) {
        previewTeacherCount.innerHTML = `<span style="color:#059669;font-weight:700;">${count}</span> teacher${count !== 1 ? 's' : ''}`;
        previewEstimatedGross.innerHTML = `UGX ${gross.toLocaleString()}`;
        noStructuresWarning.style.display = 'none';
        submitBtn.disabled = false;
    } else {
        previewTeacherCount.innerHTML = '<span style="color:#dc2626;">0 teachers found</span>';
        previewEstimatedGross.innerHTML = '—';
        noStructuresWarning.style.display = 'block';
        submitBtn.disabled = true;
    }
}
        // Validate dates
        function validateDates() {
            const start = periodStart.value;
            const end = periodEnd.value;

            if (start && end) {
                if (new Date(end) < new Date(start)) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid Dates',
                        text: 'End date cannot be earlier than start date',
                        confirmButtonColor: '#2f2ccb'
                    });
                    periodEnd.value = periodStart.value;
                    updatePreview();
                    return false;
                }
            }
            return true;
        }

        // Show loading
        function showLoading() {
            document.getElementById('loadingOverlay').style.display = 'flex';
        }

        // Event Listeners
        periodStart.addEventListener('change', () => {
            updatePreview();
            validateDates();
        });

        periodEnd.addEventListener('change', () => {
            updatePreview();
            validateDates();
        });


        // Form submit with SweetAlert confirmation
        document.getElementById('payrollForm').addEventListener('submit', function (e) {
            e.preventDefault();

            const start = periodStart.value;
            const end = periodEnd.value;
            const name = periodName.value.trim();

            if (!name) {
                Swal.fire({
                    icon: 'error',
                    title: 'Missing Information',
                    text: 'Please enter a period name',
                    confirmButtonColor: '#2f2ccb'
                });
                periodName.focus();
                return;
            }

            if (!start || !end) {
                Swal.fire({
                    icon: 'error',
                    title: 'Missing Information',
                    text: 'Please select period start and end dates',
                    confirmButtonColor: '#2f2ccb'
                });
                return;
            }

            if (!validateDates()) return;

            const duration = calculateDuration(start, end);

            Swal.fire({
                title: 'Create Payroll Period?',
                html: `
                <div style="text-align: left; padding: 10px 0;">
                    <p><strong>Period Name:</strong> ${name}</p>
                    <p><strong>Duration:</strong> ${formatDate(start)} — ${formatDate(end)} (${duration} days)</p>
                    <p><strong>Academic Year:</strong> ${academicYear.value}</p>
                </div>
                <hr>
                <p style="color: #dc2626; font-size: 0.85rem;">
                    <i class="fas fa-exclamation-triangle"></i> This will generate payslips for all teachers with active salary structures.
                </p>
            `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#2f2ccb',
                cancelButtonColor: '#94a3b8',
                confirmButtonText: 'Yes, create period',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Processing...',
                        text: 'Creating payroll period and generating payslips...',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                            document.getElementById('payrollForm').submit();
                        }
                    });
                }
            });
        });

        // Initialize on page load
        updatePreview();
        fetchTeacherStats();

        // Set default end date to last day of month if not set
        if (!periodEnd.value && periodStart.value) {
            const startDate = new Date(periodStart.value);
            const lastDay = new Date(startDate.getFullYear(), startDate.getMonth() + 1, 0);
            periodEnd.value = lastDay.toISOString().split('T')[0];
            updatePreview();
        }

        @if($errors->any())
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                html: `{!! implode('<br>', $errors->all()) !!}`,
                confirmButtonColor: '#2f2ccb'
            });
        @endif
    </script>
@endsection