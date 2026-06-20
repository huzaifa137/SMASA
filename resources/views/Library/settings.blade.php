@extends('layouts-side-bar.master')

@section('css')
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        :root {
            --lib-blue: #2c29ca;
            --lib-blue-l: rgba(44, 41, 202, .12);
            --lib-blue-d: #2420a8;
            --lib-rose: #f43f5e;
            --lib-rose-l: rgba(244, 63, 94, .12);
            --lib-green: #10b981;
            --lib-green-l: rgba(16, 185, 129, .12);
            --lib-amber: #f59e0b;
            --lib-amber-l: rgba(245, 158, 11, .12);
            --surface: #fff;
            --bg: #f1f5f9;
            --border: #e2e8f0;
            --text-1: #0f172a;
            --text-2: #475569;
            --text-3: #94a3b8;
            --radius: 16px;
            --shadow: 0 1px 4px rgba(0, 0, 0, .06), 0 4px 20px rgba(0, 0, 0, .05);
        }

        * {
            box-sizing: border-box;
        }

        body {
            background: var(--bg);
        }

        .lib-hero {
            background: linear-gradient(135deg, #1a1869 0%, #2c29ca 60%, #0d0c5e 100%);
            border-radius: 24px;
            padding: 2rem 2.5rem;
            margin-bottom: 1.75rem;
            position: relative;
            overflow: hidden;
            color: #fff;
        }

        .lib-hero::before {
            content: '';
            position: absolute;
            top: -80px;
            right: -80px;
            width: 280px;
            height: 280px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(255, 255, 255, .08) 0%, transparent 70%);
        }

        .lib-hero::after {
            content: '';
            position: absolute;
            bottom: -50px;
            left: 30%;
            width: 250px;
            height: 250px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(255, 255, 255, .05) 0%, transparent 70%);
        }

        .lib-card {
            background: var(--surface);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
        }

        .lib-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--border);
        }

        .lib-card-header h3 {
            font-size: 1rem;
            font-weight: 700;
            color: var(--text-1);
            margin: 0;
            display: flex;
            align-items: center;
            gap: .5rem;
        }

        .lib-card-header h3 i {
            color: var(--lib-blue);
        }

        .lib-card-body {
            padding: 1.5rem;
        }

        .btn-lib {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            padding: .5rem 1rem;
            border-radius: 10px;
            font-size: .8rem;
            font-weight: 600;
            cursor: pointer;
            border: none;
            text-decoration: none;
            transition: all .2s;
        }

        .btn-primary-lib {
            background: var(--lib-blue);
            color: #fff;
        }

        .btn-primary-lib:hover {
            background: var(--lib-blue-d);
            color: #fff;
        }

        .btn-outline-lib {
            background: transparent;
            color: var(--text-2);
            border: 1px solid var(--border);
        }

        .btn-outline-lib:hover {
            background: var(--bg);
            border-color: var(--lib-blue);
            color: var(--lib-blue);
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-label {
            display: block;
            font-size: .85rem;
            font-weight: 600;
            color: var(--text-2);
            margin-bottom: .4rem;
        }

        .form-hint {
            font-size: .75rem;
            color: var(--text-3);
            margin-top: .3rem;
        }

        .form-control {
            width: 100%;
            padding: .65rem .9rem;
            border: 1.5px solid var(--border);
            border-radius: 10px;
            font-size: .875rem;
            font-family: inherit;
            transition: border-color .2s;
            outline: none;
        }

        .form-control:focus {
            border-color: var(--lib-blue);
        }

        .alert {
            padding: .85rem 1rem;
            border-radius: 10px;
            font-size: .85rem;
            font-weight: 500;
            margin-bottom: 1rem;
        }

        .alert-success {
            background: var(--lib-green-l);
            color: var(--lib-green);
            border-left: 4px solid var(--lib-green);
        }

        .alert-error {
            background: var(--lib-rose-l);
            color: var(--lib-rose);
            border-left: 4px solid var(--lib-rose);
        }

        .lib-back-link {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            color: var(--text-2);
            text-decoration: none;
            font-size: .85rem;
            font-weight: 500;
            margin-bottom: 1.25rem;
        }

        .lib-back-link:hover {
            color: var(--lib-blue);
        }

        .settings-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }

        .section-title {
            font-size: .7rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: var(--text-3);
            margin: 0 0 1rem;
        }

        .toggle-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: .85rem 0;
            border-bottom: 1px solid var(--border);
        }

        .toggle-row:last-child {
            border-bottom: none;
        }

        .toggle-label {
            font-size: .875rem;
            font-weight: 600;
            color: var(--text-1);
        }

        .toggle-hint {
            font-size: .75rem;
            color: var(--text-3);
            margin-top: .15rem;
        }

        /* Toggle switch */
        .switch {
            position: relative;
            display: inline-block;
            width: 44px;
            height: 24px;
            flex-shrink: 0;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            inset: 0;
            background: #cbd5e1;
            border-radius: 999px;
            transition: .25s;
        }

        .slider:before {
            content: '';
            position: absolute;
            width: 18px;
            height: 18px;
            left: 3px;
            bottom: 3px;
            background: #fff;
            border-radius: 50%;
            transition: .25s;
        }

        input:checked+.slider {
            background: var(--lib-blue);
        }

        input:checked+.slider:before {
            transform: translateX(20px);
        }

        /* Library Settings - Stack layout on mobile */

        /* Hero section */
        .lib-hero {
            padding: 2rem 2.5rem;
        }

        /* Settings grid - 2 columns on large screens */
        .settings-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }

        /* Form elements */
        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-label {
            font-size: .85rem;
        }

        .form-hint {
            font-size: .75rem;
        }

        .form-control {
            padding: .65rem .9rem;
            font-size: .875rem;
        }

        /* Toggle rows */
        .toggle-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: .85rem 0;
            border-bottom: 1px solid var(--border);
        }

        .toggle-row:last-child {
            border-bottom: none;
        }

        .toggle-label {
            font-size: .875rem;
        }

        .toggle-hint {
            font-size: .75rem;
        }

        /* Switch */
        .switch {
            width: 44px;
            height: 24px;
        }

        .slider:before {
            width: 18px;
            height: 18px;
        }

        input:checked+.slider:before {
            transform: translateX(20px);
        }

        /* Tablet */
        @media (max-width: 992px) {
            .settings-grid {
                grid-template-columns: 1fr 1fr;
                gap: 1rem;
            }

            .lib-hero {
                padding: 1.25rem 1.5rem;
            }

            .lib-hero [style*="font-size:1.6rem;"] {
                font-size: 1.3rem !important;
            }

            .lib-hero [style*="font-size:.875rem;"] {
                font-size: .8rem !important;
            }

            .lib-card-header {
                padding: 1rem 1.25rem;
            }

            .lib-card-header h3 {
                font-size: .9rem;
            }

            .lib-card-body {
                padding: 1rem;
            }

            /* Two column form fields on tablet */
            [style*="display:grid;grid-template-columns:1fr 1fr;gap:1rem;"] {
                grid-template-columns: 1fr 1fr;
                gap: .75rem;
            }
        }

        /* Tablet - stack vertically */
        @media (max-width: 768px) {
            .settings-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .lib-hero {
                padding: 1rem 1.25rem;
                border-radius: 18px;
            }

            .lib-hero [style*="font-size:1.6rem;"] {
                font-size: 1.1rem !important;
            }

            .lib-hero [style*="font-size:.875rem;"] {
                font-size: .75rem !important;
            }

            .lib-card-header {
                padding: .75rem 1rem;
            }

            .lib-card-header h3 {
                font-size: .85rem;
            }

            .lib-card-body {
                padding: .75rem 1rem;
            }

            /* Form fields stack on mobile */
            [style*="display:grid;grid-template-columns:1fr 1fr;gap:1rem;"] {
                grid-template-columns: 1fr;
                gap: .5rem;
            }

            .form-group {
                margin-bottom: .75rem;
            }

            .form-label {
                font-size: .8rem;
            }

            .form-control {
                font-size: 14px;
                padding: .5rem .75rem;
            }

            /* Toggle rows on mobile */
            .toggle-row {
                padding: .65rem 0;
            }

            .toggle-label {
                font-size: .8rem;
            }

            .toggle-hint {
                font-size: .7rem;
            }

            /* Switch on mobile - slightly smaller */
            .switch {
                width: 40px;
                height: 22px;
            }

            .slider:before {
                width: 16px;
                height: 16px;
                left: 3px;
                bottom: 3px;
            }

            input:checked+.slider:before {
                transform: translateX(18px);
            }

            /* Buttons on mobile */
            [style*="display:flex;justify-content:flex-end;gap:1rem;margin-top:1.5rem;"] {
                flex-direction: column;
                gap: .75rem !important;
            }

            [style*="display:flex;justify-content:flex-end;gap:1rem;margin-top:1.5rem;"] .btn-lib {
                width: 100%;
                justify-content: center;
                padding: .6rem !important;
            }

            [style*="display:flex;justify-content:flex-end;gap:1rem;margin-top:1.5rem;"] .btn-lib i {
                margin-right: .5rem;
            }
        }

        /* Mobile landscape */
        @media (max-width: 576px) {
            [style*="padding:1.5rem;"] {
                padding: 0.75rem !important;
            }

            .lib-hero {
                padding: .75rem 1rem;
                border-radius: 14px;
            }

            .lib-hero [style*="font-size:1.6rem;"] {
                font-size: 1rem !important;
            }

            .lib-hero [style*="font-size:.875rem;"] {
                font-size: .7rem !important;
            }

            .lib-card-header {
                padding: .6rem .75rem;
            }

            .lib-card-header h3 {
                font-size: .8rem;
            }

            .lib-card-body {
                padding: .6rem .75rem;
            }

            .section-title {
                font-size: .6rem;
                margin-bottom: .75rem;
            }

            .form-group {
                margin-bottom: .6rem;
            }

            .form-label {
                font-size: .75rem;
                margin-bottom: .25rem;
            }

            .form-control {
                font-size: 13px;
                padding: .4rem .6rem;
                border-radius: 8px;
            }

            .form-hint {
                font-size: .65rem;
                margin-top: .2rem;
            }

            /* Toggle rows on small screens */
            .toggle-row {
                padding: .5rem 0;
                flex-wrap: wrap;
                gap: .5rem;
            }

            .toggle-row>div {
                flex: 1;
                min-width: 150px;
            }

            .toggle-label {
                font-size: .75rem;
            }

            .toggle-hint {
                font-size: .65rem;
            }

            .switch {
                width: 36px;
                height: 20px;
            }

            .slider:before {
                width: 14px;
                height: 14px;
                left: 3px;
                bottom: 3px;
            }

            input:checked+.slider:before {
                transform: translateX(16px);
            }
        }

        /* Very small screens */
        @media (max-width: 400px) {
            [style*="padding:1.5rem;"] {
                padding: 0.5rem !important;
            }

            .lib-hero {
                padding: .5rem .75rem;
                border-radius: 12px;
            }

            .lib-hero [style*="font-size:1.6rem;"] {
                font-size: .9rem !important;
            }

            .lib-card-header {
                padding: .5rem .6rem;
            }

            .lib-card-header h3 {
                font-size: .75rem;
            }

            .lib-card-body {
                padding: .5rem .6rem;
            }

            .form-label {
                font-size: .7rem;
            }

            .form-control {
                font-size: 12px;
                padding: .35rem .5rem;
            }

            .toggle-row>div {
                min-width: 120px;
            }

            .toggle-label {
                font-size: .7rem;
            }

            .toggle-hint {
                font-size: .6rem;
            }

            .switch {
                width: 32px;
                height: 18px;
            }

            .slider:before {
                width: 12px;
                height: 12px;
                left: 3px;
                bottom: 3px;
            }

            input:checked+.slider:before {
                transform: translateX(14px);
            }
        }

        /* Improve touch targets on mobile */
        @media (max-width: 576px) {
            .btn-lib {
                cursor: pointer;
                -webkit-tap-highlight-color: transparent;
                min-height: 40px;
            }

            .switch {
                cursor: pointer;
            }

            .slider {
                cursor: pointer;
            }

            /* Make form inputs easier to tap */
            .form-control {
                min-height: 40px;
            }

            /* Checkboxes easier to tap */
            input[type="checkbox"] {
                min-width: 20px;
                min-height: 20px;
            }
        }

        /* Smooth transitions */
        .lib-card,
        .lib-hero,
        .btn-lib,
        .toggle-row {
            transition: all 0.2s ease;
        }

        /* Fix for button group on mobile */
        @media (max-width: 576px) {

            #cancelBtn,
            #submitBtn {
                width: 100%;
                justify-content: center;
            }

            #cancelBtn i,
            #submitBtn i {
                margin-right: .5rem;
            }
        }
    </style>
@endsection

@section('content')
    <div style="padding:1.5rem;">

        <div class="lib-hero mb-4">
            <div style="font-size:1.6rem;font-weight:800;margin:0 0 .25rem;">
                <i class="fas fa-sliders-h" style="color:#a5b4fc;margin-right:.5rem;"></i>Library Settings
            </div>
            <div style="font-size:.875rem;opacity:.7;">Configure borrowing rules, fines, and features</div>
        </div>

        <form id="settingsForm">
            @csrf

            <div class="settings-grid">
                {{-- Borrowing Rules --}}
                <div class="lib-card">
                    <div class="lib-card-header">
                        <h3><i class="fas fa-book-reader" style="color:var(--lib-blue);"></i> Borrowing Rules</h3>
                    </div>
                    <div class="lib-card-body">
                        <p class="section-title">Students</p>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                            <div class="form-group">
                                <label class="form-label">Max Books</label>
                                <input type="number" name="student_max_books" class="form-control" min="1" max="20"
                                    value="{{ $settings->student_max_books ?? 3 }}" required>
                                <p class="form-hint">Simultaneous loans allowed</p>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Loan Days</label>
                                <input type="number" name="student_loan_days" class="form-control" min="1" max="90"
                                    value="{{ $settings->student_loan_days ?? 14 }}" required>
                                <p class="form-hint">Default loan period</p>
                            </div>
                        </div>

                        <p class="section-title" style="margin-top:.5rem;">Teachers</p>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                            <div class="form-group">
                                <label class="form-label">Max Books</label>
                                <input type="number" name="teacher_max_books" class="form-control" min="1" max="50"
                                    value="{{ $settings->teacher_max_books ?? 5 }}" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Loan Days</label>
                                <input type="number" name="teacher_loan_days" class="form-control" min="1" max="180"
                                    value="{{ $settings->teacher_loan_days ?? 30 }}" required>
                            </div>
                        </div>

                        <div class="form-group" style="margin-top:.5rem;">
                            <label class="form-label">Max Renewals Allowed</label>
                            <input type="number" name="max_renewals" class="form-control" min="0" max="10"
                                value="{{ $settings->max_renewals ?? 2 }}" required>
                            <p class="form-hint">Set to 0 to disable renewals entirely</p>
                        </div>
                    </div>
                </div>

                {{-- Fines --}}
                <div class="lib-card">
                    <div class="lib-card-header">
                        <h3><i class="fas fa-coins" style="color:var(--lib-amber);"></i> Fines & Features</h3>
                    </div>
                    <div class="lib-card-body">
                        <p class="section-title">Fine Configuration</p>
                        <div class="form-group">
                            <label class="form-label">Fine Per Day (overdue)</label>
                            <div style="position:relative;">
                                <span
                                    style="position:absolute;left:.85rem;top:50%;transform:translateY(-50%);color:var(--text-3);font-size:.9rem;">$</span>
                                <input type="number" name="fine_per_day" class="form-control" style="padding-left:1.75rem;"
                                    min="0" step="0.01" value="{{ $settings->fine_per_day ?? 0.50 }}" required>
                            </div>
                            <p class="form-hint">Amount charged per overdue day</p>
                        </div>

                        <p class="section-title" style="margin-top:1.25rem;">Feature Toggles</p>
                        <div>
                            <div class="toggle-row">
                                <div>
                                    <div class="toggle-label">Reservations</div>
                                    <div class="toggle-hint">Allow members to reserve books</div>
                                </div>
                                <label class="switch">
                                    <input type="checkbox" name="enable_reservations" value="1" {{ ($settings->enable_reservations ?? true) ? 'checked' : '' }}>
                                    <span class="slider"></span>
                                </label>
                            </div>
                            <div class="toggle-row">
                                <div>
                                    <div class="toggle-label">E-Books</div>
                                    <div class="toggle-hint">Enable digital book downloads</div>
                                </div>
                                <label class="switch">
                                    <input type="checkbox" name="enable_ebooks" value="1" {{ ($settings->enable_ebooks ?? false) ? 'checked' : '' }}>
                                    <span class="slider"></span>
                                </label>
                            </div>
                            <div class="toggle-row">
                                <div>
                                    <div class="toggle-label">Recommendations</div>
                                    <div class="toggle-hint">Show personalised book suggestions</div>
                                </div>
                                <label class="switch">
                                    <input type="checkbox" name="enable_recommendations" value="1" {{ ($settings->enable_recommendations ?? true) ? 'checked' : '' }}>
                                    <span class="slider"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div style="display:flex;justify-content:flex-end;gap:1rem;margin-top:1.5rem;">
                <a href="{{ route('library.dashboard') }}" class="btn-lib btn-primary" id="cancelBtn">
                    <i class="fas fa-times"></i> Cancel
                </a>
                <button type="submit" class="btn-lib btn-primary-lib" style="padding:.65rem 1.75rem;font-size:.9rem;"
                    id="submitBtn">
                    <i class="fas fa-save"></i> Save Settings
                </button>
            </div>
        </form>
    </div>
    </div>
    </div>
    </div>
@endsection

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // SweetAlert Toast configuration
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });

        // Form validation
        function validateSettings() {
            const studentMaxBooks = parseInt(document.querySelector('input[name="student_max_books"]').value);
            const studentLoanDays = parseInt(document.querySelector('input[name="student_loan_days"]').value);
            const teacherMaxBooks = parseInt(document.querySelector('input[name="teacher_max_books"]').value);
            const teacherLoanDays = parseInt(document.querySelector('input[name="teacher_loan_days"]').value);
            const maxRenewals = parseInt(document.querySelector('input[name="max_renewals"]').value);
            const finePerDay = parseFloat(document.querySelector('input[name="fine_per_day"]').value);

            if (studentMaxBooks < 1) {
                Toast.fire({ icon: 'error', title: 'Student max books must be at least 1' });
                return false;
            }

            if (studentLoanDays < 1) {
                Toast.fire({ icon: 'error', title: 'Student loan days must be at least 1' });
                return false;
            }

            if (teacherMaxBooks < 1) {
                Toast.fire({ icon: 'error', title: 'Teacher max books must be at least 1' });
                return false;
            }

            if (teacherLoanDays < 1) {
                Toast.fire({ icon: 'error', title: 'Teacher loan days must be at least 1' });
                return false;
            }

            if (maxRenewals < 0) {
                Toast.fire({ icon: 'error', title: 'Max renewals cannot be negative' });
                return false;
            }

            if (finePerDay < 0) {
                Toast.fire({ icon: 'error', title: 'Fine per day cannot be negative' });
                return false;
            }

            return true;
        }

        // Get changed settings summary
        function getSettingsSummary() {
            const changes = [];

            const studentMaxBooks = document.querySelector('input[name="student_max_books"]').value;
            const originalStudentMaxBooks = '{{ $settings->student_max_books ?? 3 }}';
            if (studentMaxBooks != originalStudentMaxBooks) {
                changes.push(`Student Max Books: ${originalStudentMaxBooks} → ${studentMaxBooks}`);
            }

            const studentLoanDays = document.querySelector('input[name="student_loan_days"]').value;
            const originalStudentLoanDays = '{{ $settings->student_loan_days ?? 14 }}';
            if (studentLoanDays != originalStudentLoanDays) {
                changes.push(`Student Loan Days: ${originalStudentLoanDays} → ${studentLoanDays}`);
            }

            const teacherMaxBooks = document.querySelector('input[name="teacher_max_books"]').value;
            const originalTeacherMaxBooks = '{{ $settings->teacher_max_books ?? 5 }}';
            if (teacherMaxBooks != originalTeacherMaxBooks) {
                changes.push(`Teacher Max Books: ${originalTeacherMaxBooks} → ${teacherMaxBooks}`);
            }

            const teacherLoanDays = document.querySelector('input[name="teacher_loan_days"]').value;
            const originalTeacherLoanDays = '{{ $settings->teacher_loan_days ?? 30 }}';
            if (teacherLoanDays != originalTeacherLoanDays) {
                changes.push(`Teacher Loan Days: ${originalTeacherLoanDays} → ${teacherLoanDays}`);
            }

            const maxRenewals = document.querySelector('input[name="max_renewals"]').value;
            const originalMaxRenewals = '{{ $settings->max_renewals ?? 2 }}';
            if (maxRenewals != originalMaxRenewals) {
                changes.push(`Max Renewals: ${originalMaxRenewals} → ${maxRenewals}`);
            }

            const finePerDay = document.querySelector('input[name="fine_per_day"]').value;
            const originalFinePerDay = '{{ $settings->fine_per_day ?? 0.50 }}';
            if (finePerDay != originalFinePerDay) {
                changes.push(`Fine Per Day: $${originalFinePerDay} → $${finePerDay}`);
            }

            return changes;
        }

        // Save Settings Form Submission
        document.getElementById('settingsForm').addEventListener('submit', function (e) {
            e.preventDefault();

            if (!validateSettings()) {
                return;
            }

            const changes = getSettingsSummary();

            let confirmationMessage = 'Are you sure you want to update the library settings?';
            if (changes.length > 0) {
                confirmationMessage = '<strong>Changes to be saved:</strong><br><ul style="text-align:left;margin-top:10px;">';
                changes.forEach(change => {
                    confirmationMessage += `<li>${change}</li>`;
                });
                confirmationMessage += '</ul>';
            }

            Swal.fire({
                title: 'Save Settings?',
                html: confirmationMessage,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#2c29ca',
                cancelButtonColor: '#f43f5e',
                confirmButtonText: 'Yes, save settings!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Saving Settings...',
                        text: 'Please wait while we update the library settings',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        allowEnterKey: false,
                        showConfirmButton: false,
                        didOpen: () => { Swal.showLoading(); }
                    });

                    const submitBtn = document.getElementById('submitBtn');
                    const originalHtml = submitBtn.innerHTML;
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';

                    // Get all form data including checkboxes
                    const formData = new FormData(this);

                    // Ensure checkboxes are properly handled
                    const checkboxes = ['enable_reservations', 'enable_ebooks', 'enable_recommendations'];
                    checkboxes.forEach(cb => {
                        const checkbox = document.querySelector(`input[name="${cb}"]`);
                        if (checkbox && !checkbox.checked) {
                            formData.append(cb, '0');
                        } else if (checkbox && checkbox.checked) {
                            formData.append(cb, '1');
                        }
                    });

                    fetch('{{ route("library.settings.update") }}', {
                        method: 'POST',
                        body: formData,
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Saved!',
                                    text: data.message,
                                    showConfirmButton: false,
                                    timer: 1500
                                }).then(() => {
                                    window.location.href = '{{ route("library.settings") }}';
                                });
                            } else {
                                throw new Error(data.message || 'Failed to save settings');
                            }
                        })
                        .catch(error => {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: error.message
                            });
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = originalHtml;
                        });
                }
            });
        });

        // Cancel confirmation
        document.getElementById('cancelBtn').addEventListener('click', function (e) {
            e.preventDefault();
            const href = this.getAttribute('href');

            // Check if there are unsaved changes
            const changes = getSettingsSummary();

            if (changes.length > 0) {
                Swal.fire({
                    title: 'Leave Page?',
                    text: 'You have unsaved changes. Are you sure you want to leave? Any changes will be lost.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#f43f5e',
                    cancelButtonColor: '#2c29ca',
                    confirmButtonText: 'Yes, leave',
                    cancelButtonText: 'Stay'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = href;
                    }
                });
            } else {
                window.location.href = href;
            }
        });
    </script>
@endsection