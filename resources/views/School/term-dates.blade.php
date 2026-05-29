<?php
use App\Http\Controllers\Helper;
use App\Http\Controllers\Controller;
$controller = new Controller();
?>
@extends('layouts-side-bar.master')
@section('css')
    <link href="{{ URL::asset('assets/plugins/jvectormap/jqvmap.css') }}" rel="stylesheet" />
    <link href="{{ URL::asset('assets/plugins/datatable/dataTables.bootstrap4.min.css') }}" rel="stylesheet" />
    <link href="{{ URL::asset('assets/plugins/bootstrap-daterangepicker/daterangepicker.css') }}" rel="stylesheet" />
    <style>
        :root {
            --primary: #2C29CA;
            --primary-dark: #14136e;
            --primary-light: #eeedfb;
            --success: #16a34a;
            --success-light: #dcfce7;
            --danger: #dc2626;
            --danger-light: #fee2e2;
            --warning: #d97706;
            --warning-light: #fef3c7;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-400: #9ca3af;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-800: #1f2937;
            --gray-900: #111827;
            --white: #ffffff;
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 16px;
            --radius-xl: 20px;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
            --shadow-md: 0 4px 12px rgba(0,0,0,0.08), 0 2px 6px rgba(0,0,0,0.04);
            --shadow-lg: 0 10px 30px rgba(0,0,0,0.1), 0 4px 12px rgba(0,0,0,0.06);
            --transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .td-page-wrap { padding: 0; }

        /* ── PAGE HEADER ── */
        .td-page-header {
            background: var(--white);
            border-bottom: 1px solid var(--gray-200);
            padding: 20px 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 24px;
        }
        .td-page-header-left { display: flex; align-items: center; gap: 14px; }
        .td-page-icon {
            width: 44px; height: 44px; border-radius: var(--radius-md);
            background: var(--primary-light);
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .td-page-icon i { font-size: 20px; color: var(--primary); }
        .td-page-title { font-size: 20px; font-weight: 700; color: var(--gray-900); margin: 0; line-height: 1.2; }
        .td-page-subtitle { font-size: 13px; color: var(--gray-500); margin: 0; margin-top: 2px; }
        .td-header-actions { display: flex; gap: 10px; flex-wrap: wrap; }
        .td-btn-header {
            display: inline-flex; align-items: center; gap: 7px;
            padding: 9px 16px; border-radius: var(--radius-md);
            font-size: 13px; font-weight: 600; text-decoration: none;
            border: 1.5px solid transparent; transition: var(--transition); cursor: pointer;
        }
        .td-btn-header-outline {
            background: var(--white); color: var(--primary);
            border-color: var(--primary); 
        }
        .td-btn-header-outline:hover { background: var(--primary-light); color: var(--primary-dark); }

        /* ── LAYOUT ── */
        .td-layout { display: grid; grid-template-columns: 380px 1fr; gap: 24px; padding: 0 24px 32px; align-items: start; }
        @media (max-width: 1024px) { .td-layout { grid-template-columns: 1fr; } }

        /* ── CARDS ── */
        .td-card {
            background: var(--white); border-radius: var(--radius-xl);
            border: 1px solid var(--gray-200); box-shadow: var(--shadow-sm);
            overflow: hidden;
        }
        .td-card-header {
            padding: 20px 24px 16px;
            border-bottom: 1px solid var(--gray-100);
            display: flex; align-items: center; justify-content: space-between; gap: 12px;
        }
        .td-card-header-title { display: flex; align-items: center; gap: 10px; }
        .td-card-header-icon {
            width: 34px; height: 34px; border-radius: var(--radius-sm);
            background: var(--primary-light); display: flex; align-items: center; justify-content: center;
        }
        .td-card-header-icon i { font-size: 16px; color: var(--primary); }
        .td-card-title { font-size: 15px; font-weight: 700; color: var(--gray-800); margin: 0; }
        .td-card-subtitle { font-size: 12px; color: var(--gray-500); margin: 2px 0 0; }
        .td-card-body { padding: 24px; }

        /* ── FORM ── */
        .td-form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
        @media (max-width: 540px) { .td-form-grid { grid-template-columns: 1fr; } }
        .td-form-group { display: flex; flex-direction: column; gap: 6px; }
        .td-form-group.full { grid-column: 1 / -1; }
        .td-form-label {
            font-size: 12px; font-weight: 700; color: var(--gray-600);
            text-transform: uppercase; letter-spacing: 0.5px;
        }
        .td-form-control {
            width: 100%; padding: 10px 14px;
            border: 1.5px solid var(--gray-200); border-radius: var(--radius-md);
            font-size: 14px; color: var(--gray-800); background: var(--white);
            transition: var(--transition); font-family: inherit;
            appearance: none; -webkit-appearance: none;
        }
        .td-form-control:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(44,41,202,0.1); }
        .td-form-control:hover:not(:focus) { border-color: var(--gray-300); }
        .td-form-control.is-invalid { border-color: var(--danger); box-shadow: 0 0 0 3px rgba(220,38,38,0.1); }
        .td-invalid-feedback { font-size: 12px; color: var(--danger); font-weight: 500; margin-top: 4px; display: none; }
        .td-form-control.is-invalid ~ .td-invalid-feedback { display: block; }

        .td-select-wrap { position: relative; }
        .td-select-wrap::after {
            content: ''; position: absolute; right: 14px; top: 50%; transform: translateY(-50%);
            width: 0; height: 0; border-left: 5px solid transparent;
            border-right: 5px solid transparent; border-top: 6px solid var(--gray-400);
            pointer-events: none;
        }
        .td-select-wrap .td-form-control { padding-right: 36px; cursor: pointer; }

        /* ── SUBMIT BTN ── */
        .td-btn-submit {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 11px 24px; border-radius: var(--radius-md);
            background: var(--primary); color: var(--white);
            font-size: 14px; font-weight: 700; border: none; cursor: pointer;
            transition: var(--transition); box-shadow: 0 4px 12px rgba(44,41,202,0.25);
            width: 100%; justify-content: center; margin-top: 8px;
        }
        .td-btn-submit:hover:not(:disabled) { background: var(--primary-dark); transform: translateY(-1px); box-shadow: 0 6px 16px rgba(44,41,202,0.3); }
        .td-btn-submit:disabled { opacity: 0.65; cursor: not-allowed; transform: none; }

        /* ── YEAR SELECTOR ── */
        .td-year-selector {
            display: flex; align-items: center; gap: 8px;
            background: var(--gray-50); border: 1.5px solid var(--gray-200);
            border-radius: var(--radius-md); padding: 8px 12px 8px 14px;
            cursor: pointer; transition: var(--transition); font-size: 13px; font-weight: 600; color: var(--gray-700);
            appearance: none; -webkit-appearance: none;
        }
        .td-year-selector:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(44,41,202,0.1); }

        /* ── TABLE ── */
        .td-table-wrap { overflow-x: auto; -webkit-overflow-scrolling: touch; }
        .td-table { width: 100%; border-collapse: separate; border-spacing: 0; font-size: 14px; }
        .td-table thead th {
            padding: 12px 16px; font-size: 11px; font-weight: 700;
            text-transform: uppercase; letter-spacing: 0.6px; color: var(--gray-500);
            background: var(--gray-50); border-bottom: 1px solid var(--gray-200);
            white-space: nowrap;
        }
        .td-table thead th:first-child { border-radius: var(--radius-sm) 0 0 0; }
        .td-table thead th:last-child { border-radius: 0 var(--radius-sm) 0 0; }
        .td-table tbody tr { transition: var(--transition); }
        .td-table tbody tr:hover td { background: var(--gray-50); }
        .td-table tbody td {
            padding: 14px 16px; color: var(--gray-700); vertical-align: middle;
            border-bottom: 1px solid var(--gray-100); white-space: nowrap;
        }
        .td-table tbody tr:last-child td { border-bottom: none; }
        .td-table tbody tr.active-term-row td { background: rgba(22,163,74,0.04); }
        .td-table tbody tr.active-term-row td:first-child { border-left: 3px solid var(--success); }

        /* ── TABLE BADGES ── */
        .td-badge {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 4px 10px; border-radius: 100px; font-size: 12px; font-weight: 600;
        }
        .td-badge-num {
            width: 26px; height: 26px; border-radius: 50%; background: var(--primary-light);
            color: var(--primary); font-size: 12px; font-weight: 700;
            display: flex; align-items: center; justify-content: center;
        }
        .td-term-label {
            font-weight: 600; color: var(--gray-800);
        }
        .td-date-text { color: var(--gray-600); font-size: 13px; }
        .td-day-badge {
            display: inline-flex; align-items: center; gap: 5px;
            background: var(--primary-light); color: var(--primary-dark);
            padding: 4px 10px; border-radius: 100px; font-size: 12px; font-weight: 600;
        }

        /* ── STATUS BUTTONS ── */
        .btn-activate-term, .btn-deactivate-term {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 6px 14px; border-radius: 100px;
            font-size: 12px; font-weight: 700; border: none; cursor: pointer;
            transition: var(--transition);
        }
        .btn-deactivate-term {
            background: var(--success-light); color: var(--success);
            border: 1.5px solid rgba(22,163,74,0.2);
        }
        .btn-deactivate-term:hover { background: #bbf7d0; }
        .btn-activate-term {
            background: var(--gray-100); color: var(--gray-500);
            border: 1.5px solid var(--gray-200);
        }
        .btn-activate-term:hover { background: var(--gray-200); color: var(--gray-700); }

        /* ── DELETE BTN ── */
        .td-btn-delete {
            width: 34px; height: 34px; border-radius: var(--radius-sm);
            background: var(--white); border: 1.5px solid var(--gray-200);
            color: var(--gray-400); display: flex; align-items: center; justify-content: center;
            cursor: pointer; transition: var(--transition); font-size: 14px;
        }
        .td-btn-delete:hover { background: var(--danger-light); border-color: rgba(220,38,38,0.2); color: var(--danger); }

        /* ── EMPTY STATE ── */
        .td-empty {
            padding: 56px 24px; text-align: center; color: var(--gray-400);
        }
        .td-empty-icon { font-size: 40px; margin-bottom: 12px; opacity: 0.4; }
        .td-empty-title { font-size: 15px; font-weight: 600; color: var(--gray-500); margin-bottom: 4px; }
        .td-empty-sub { font-size: 13px; color: var(--gray-400); }

        /* ── MOBILE CARD VIEW ── */
        .td-mobile-cards { display: none; }
        @media (max-width: 768px) {
            .td-table-wrap { display: none; }
            .td-mobile-cards { display: flex; flex-direction: column; gap: 12px; padding: 16px; }
        }
        .td-mobile-term-card {
            background: var(--white); border: 1px solid var(--gray-200);
            border-radius: var(--radius-lg); padding: 16px; position: relative;
        }
        .td-mobile-term-card.is-active { border-color: rgba(22,163,74,0.3); background: rgba(22,163,74,0.02); }
        .td-mobile-term-card.is-active::before {
            content: ''; position: absolute; left: 0; top: 0; bottom: 0;
            width: 3px; background: var(--success); border-radius: var(--radius-lg) 0 0 var(--radius-lg);
        }
        .td-mobile-term-header { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 12px; gap: 8px; }
        .td-mobile-term-name { font-size: 15px; font-weight: 700; color: var(--gray-800); }
        .td-mobile-term-dates { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 14px; }
        .td-mobile-date-item { display: flex; flex-direction: column; gap: 2px; }
        .td-mobile-date-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: var(--gray-400); }
        .td-mobile-date-val { font-size: 13px; font-weight: 600; color: var(--gray-700); }
        .td-mobile-term-footer { display: flex; align-items: center; justify-content: space-between; }

        .td-table thead th {
    background-color: var(--primary);
    color: var(--white); /* or use var(--primary-light) if you want lighter text */
}
    </style>
@endsection

@section('content')
<div class="td-page-wrap mt-4">

    <!-- PAGE HEADER -->
    <div class="td-page-header">
        <div class="td-page-header-left">
            <div class="td-page-icon">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <div>
                <h1 class="td-page-title">Term Dates</h1>
                <p class="td-page-subtitle">Configure and manage school term start and end dates</p>
            </div>
        </div>
        @if (session('LoggedAdmin'))
        <div class="td-header-actions">
            <a href="{{ route('add-academic-year') }}" class="td-btn-header td-btn-header-outline">
                <i class="fas fa-calendar-alt"></i> Academic Years
            </a>
            <a href="{{ route('school.allSchools') }}" class="td-btn-header td-btn-header-outline">
                <i class="fas fa-school"></i> All Schools
            </a>
        </div>
        @endif
    </div>

    <!-- TWO-COLUMN LAYOUT -->
    <div class="td-layout">

        <!-- LEFT: FORM CARD -->
        <div class="td-card">
            <div class="td-card-header">
                <div class="td-card-header-title">
                    <div class="td-card-header-icon"><i class="fas fa-plus-circle"></i></div>
                    <div>
                        <div class="td-card-title">Set Term Dates</div>
                        <div class="td-card-subtitle">Add a new term period</div>
                    </div>
                </div>
            </div>
            <div class="td-card-body">
                <form id="createSchoolTerm">
                    @csrf
                    <input type="hidden" name="school_id" value="{{ $school_id }}">

                    <div class="td-form-grid">
                        <!-- Academic Year -->
                        <div class="td-form-group full">
                            <label class="td-form-label">Academic Year</label>
                            <div class="td-select-wrap">
                                <select name="academic_year_id" id="academic_year" class="td-form-control" required>
                                    @if ($academicYears->isEmpty())
                                        <option value="" disabled selected>Not set — contact SMASA Admins</option>
                                    @else
                                        @foreach ($academicYears as $year)
                                            <option value="{{ $year->id }}">{{ $year->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <span class="td-invalid-feedback">Please select an academic year.</span>
                        </div>

                        <!-- Term -->
                        <div class="td-form-group full">
                            <label class="td-form-label">Term</label>
                            <div class="td-select-wrap">
                                <?php echo Helper::DropMasterData(config('constants.options.SCHOOL_TERMS'), 'td-form-control', 'term', 1); ?>
                            </div>
                            <span class="td-invalid-feedback">Please select a term.</span>
                        </div>

                        @php
                            $currentYear = date('Y');
                            $minDate = $currentYear . '-01-01';
                            $maxDate = $currentYear . '-12-31';
                        @endphp

                        <!-- Start Date -->
                        <div class="td-form-group">
                            <label class="td-form-label">Start Date</label>
                            <input type="date" name="start_date" id="start_date" class="td-form-control"
                                required min="{{ $minDate }}" max="{{ $maxDate }}">
                            <span class="td-invalid-feedback">Required.</span>
                        </div>

                        <!-- End Date -->
                        <div class="td-form-group">
                            <label class="td-form-label">End Date</label>
                            <input type="date" name="end_date" id="end_date" class="td-form-control"
                                required min="{{ $minDate }}" max="{{ $maxDate }}">
                            <span class="td-invalid-feedback">Required.</span>
                        </div>

                        <!-- Week Starts On -->
                        <div class="td-form-group full">
                            <label class="td-form-label">Week Starts On</label>
                            <div class="td-select-wrap">
                                <select name="week_starts_on" id="week_starts_on" class="td-form-control">
                                    <option value="1">Sunday</option>
                                    <option value="2">Monday</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="td-btn-submit" id="tdSubmitBtn">
                        <i class="fas fa-paper-plane"></i> Save Term Dates
                    </button>
                </form>
            </div>
        </div>

        <!-- RIGHT: TABLE CARD -->
        <div class="td-card">
            <div class="td-card-header">
                <div class="td-card-header-title">
                    <div class="td-card-header-icon"><i class="fas fa-list-ul"></i></div>
                    <div>
                        <div class="td-card-title">Term Dates</div>
                        <div class="td-card-subtitle">All configured term periods</div>
                    </div>
                </div>
                <div>
                    <select id="yearSelector" class="td-year-selector">
                        @foreach ($academicYears as $year)
                            <option value="{{ $year->id }}" {{ $selectedYearId == $year->id ? 'selected' : '' }}>
                                {{ $year->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- DESKTOP TABLE -->
            <div class="td-table-wrap">
                <table class="td-table" id="termDatesTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Term</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Week Start</th>
                            <th style="text-align:center">Status</th>
                            <th style="text-align:center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($termDates as $key => $term)
                        @php
                            $daysOfWeek = [1=>'Sunday',2=>'Monday',3=>'Tuesday',4=>'Wednesday',5=>'Thursday',6=>'Friday',7=>'Saturday'];
                        @endphp
                        <tr data-id="{{ $term->id }}" class="{{ $term->is_active ? 'active-term-row' : '' }}">
                            <td><span class="td-badge-num">{{ $key + 1 }}</span></td>
                            <td><span class="td-term-label">{{ Helper::recordMdname($term->term) }}</span></td>
                            <td><span class="td-date-text">{{ $term->start_date }}</span></td>
                            <td><span class="td-date-text">{{ $term->end_date }}</span></td>
                            <td><span class="td-day-badge"><i class="fas fa-calendar-week" style="font-size:10px"></i>{{ $daysOfWeek[$term->week_starts_on] ?? 'Unknown' }}</span></td>
                            <td style="text-align:center">
                                @if ($term->is_active)
                                    <button class="btn-deactivate-term" data-term-id="{{ $term->id }}" data-term-name="{{ Helper::recordMdname($term->term) }}">
                                        <i class="fas fa-check-circle" style="font-size:11px"></i> Active
                                    </button>
                                @else
                                    <button class="btn-activate-term" data-term-id="{{ $term->id }}" data-term-name="{{ Helper::recordMdname($term->term) }}">
                                        <i class="fas fa-circle" style="font-size:9px"></i> Set Active
                                    </button>
                                @endif
                            </td>
                            <td style="text-align:center">
                                <button class="td-btn-delete btn-delete-term-date" data-id="{{ $term->id }}" title="Delete term">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" style="padding:0; border:none">
                                <div class="td-empty">
                                    <div class="td-empty-icon"><i class="fas fa-calendar-times"></i></div>
                                    <div class="td-empty-title">No term dates found</div>
                                    <div class="td-empty-sub">Use the form to add your first term period.</div>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- MOBILE CARDS VIEW -->
            <div class="td-mobile-cards">
                @forelse ($termDates as $key => $term)
                @php $daysOfWeek = [1=>'Sunday',2=>'Monday',3=>'Tuesday',4=>'Wednesday',5=>'Thursday',6=>'Friday',7=>'Saturday']; @endphp
                <div class="td-mobile-term-card {{ $term->is_active ? 'is-active' : '' }}" data-id="{{ $term->id }}">
                    <div class="td-mobile-term-header">
                        <div class="td-mobile-term-name">{{ Helper::recordMdname($term->term) }}</div>
                        @if ($term->is_active)
                            <button class="btn-deactivate-term" data-term-id="{{ $term->id }}" data-term-name="{{ Helper::recordMdname($term->term) }}">
                                <i class="fas fa-check-circle" style="font-size:11px"></i> Active
                            </button>
                        @else
                            <button class="btn-activate-term" data-term-id="{{ $term->id }}" data-term-name="{{ Helper::recordMdname($term->term) }}">
                                <i class="fas fa-circle" style="font-size:9px"></i> Set Active
                            </button>
                        @endif
                    </div>
                    <div class="td-mobile-term-dates">
                        <div class="td-mobile-date-item">
                            <span class="td-mobile-date-label">Start Date</span>
                            <span class="td-mobile-date-val">{{ $term->start_date }}</span>
                        </div>
                        <div class="td-mobile-date-item">
                            <span class="td-mobile-date-label">End Date</span>
                            <span class="td-mobile-date-val">{{ $term->end_date }}</span>
                        </div>
                    </div>
                    <div class="td-mobile-term-footer">
                        <span class="td-day-badge" style="font-size:11px"><i class="fas fa-calendar-week" style="font-size:9px"></i>{{ $daysOfWeek[$term->week_starts_on] ?? '' }}</span>
                        <button class="td-btn-delete btn-delete-term-date" data-id="{{ $term->id }}" title="Delete">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                </div>
                @empty
                <div class="td-empty">
                    <div class="td-empty-icon"><i class="fas fa-calendar-times"></i></div>
                    <div class="td-empty-title">No term dates found</div>
                    <div class="td-empty-sub">Use the form to add your first term period.</div>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
$(document).ready(function() {

    // ── FORM: apply td-form-control class to Helper-generated selects ──
    $('#createSchoolTerm select:not(.td-form-control)').each(function() {
        $(this).addClass('td-form-control');
        if (!$(this).parent().hasClass('td-select-wrap')) {
            $(this).wrap('<div class="td-select-wrap"></div>');
        }
    });

    // ── FORM SUBMIT ──
    $('#createSchoolTerm').on('submit', function(e) {
        e.preventDefault();
        let isValid = true;
        let $form = $(this);
        let $submitBtn = $('#tdSubmitBtn');

        $form.find('.td-form-control').removeClass('is-invalid');

        $form.find('input[required], select[required]').each(function() {
            if (!$(this).val() || !$(this).val().trim()) {
                $(this).addClass('is-invalid');
                isValid = false;
            }
        });

        if (!isValid) {
            Swal.fire({ icon: 'error', title: 'Incomplete Form', text: 'Please fill in all required fields.', confirmButtonColor: '#2C29CA' });
            return;
        }

        Swal.fire({
            title: 'Save Term Dates?', text: 'You are about to save this term period.', icon: 'question',
            showCancelButton: true, confirmButtonColor: '#2C29CA', cancelButtonColor: '#6b7280',
            confirmButtonText: 'Yes, save it', cancelButtonText: 'Cancel'
        }).then((result) => {
            if (!result.isConfirmed) return;

            const originalHtml = $submitBtn.html();
            $submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

            $.ajax({
                url: '{{ route("term-dates.store") }}',
                method: 'POST',
                data: $form.serialize(),
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                success: function(response) {
                    Swal.fire({ icon: 'success', title: 'Saved!', text: response.message, confirmButtonColor: '#2C29CA' })
                        .then(() => location.reload());
                    $form[0].reset();
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;
                        for (const field in errors) {
                            $form.find(`[name="${field}"]`).addClass('is-invalid');
                        }
                        Swal.fire({ icon: 'error', title: 'Validation Error', text: 'Please check the highlighted fields.', confirmButtonColor: '#2C29CA' });
                    } else if (xhr.status === 409) {
                        Swal.fire({ icon: 'warning', title: 'Duplicate Entry', text: xhr.responseJSON.message, confirmButtonColor: '#2C29CA' });
                    } else {
                        Swal.fire({ icon: 'error', title: 'Error', text: 'An unexpected error occurred. Please try again.', confirmButtonColor: '#2C29CA' });
                    }
                },
                complete: function() {
                    $submitBtn.prop('disabled', false).html(originalHtml);
                }
            });
        });
    });

    // ── DELETE ──
    $('tbody, .td-mobile-cards').on('click', '.btn-delete-term-date', function() {
        var yearId = $(this).data('id');
        var row = $(this).closest('tr, .td-mobile-term-card');

        Swal.fire({
            title: 'Delete Term?', text: 'This cannot be undone.', icon: 'warning',
            showCancelButton: true, confirmButtonColor: '#dc2626', cancelButtonColor: '#6b7280',
            confirmButtonText: 'Yes, delete it'
        }).then((result) => {
            if (!result.isConfirmed) return;
            $.ajax({
                url: '/academic-years/' + yearId, type: 'DELETE',
                data: { _token: '{{ csrf_token() }}' },
                success: function(response) {
                    row.remove();
                    Swal.fire({ icon: 'success', title: 'Deleted!', text: 'Term has been removed.', confirmButtonColor: '#2C29CA' });
                },
                error: function(data) { $('body').html(data.responseText); }
            });
        });
    });

    // ── YEAR SELECTOR ──
    const schoolId = "{{ $school_id }}";
    document.getElementById('yearSelector').addEventListener('change', function() {
        const selectedYear = this.value;
        Swal.fire({
            title: 'Change Year?', text: 'The displayed terms will update.', icon: 'question',
            showCancelButton: true, confirmButtonColor: '#2C29CA', cancelButtonColor: '#6b7280',
            confirmButtonText: 'Continue'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `/term-dates/${schoolId}?year=${selectedYear}`;
            } else {
                this.value = "{{ $selectedYearId }}";
            }
        });
    });

    // ── TOAST ──
    const Toast = Swal.mixin({
        toast: true, position: 'top-end', showConfirmButton: false,
        timer: 3000, timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer);
            toast.addEventListener('mouseleave', Swal.resumeTimer);
        }
    });

    function showToast(icon, title) { Toast.fire({ icon, title }); }

    const savedMsg = sessionStorage.getItem('toastMessage');
    const savedType = sessionStorage.getItem('toastType');
    if (savedMsg) {
        showToast(savedType, savedMsg);
        sessionStorage.removeItem('toastMessage');
        sessionStorage.removeItem('toastType');
    }

    // ── ACTIVE TERM LOGIC ──
    function rebindButtonEvents() {
        $(document).off('click', '.btn-activate-term').off('click', '.btn-deactivate-term');

        $(document).on('click', '.btn-activate-term', function() {
            const $btn = $(this);
            const termId = $btn.data('term-id');
            const termName = $btn.data('term-name');
            Swal.fire({
                title: 'Activate Term?', text: `Activate ${termName}? Any other active term will be deactivated.`,
                icon: 'question', showCancelButton: true,
                confirmButtonColor: '#16a34a', cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, activate'
            }).then((result) => {
                if (result.isConfirmed) activateTerm($btn, termId, termName);
            });
        });

        $(document).on('click', '.btn-deactivate-term', function() {
            const $btn = $(this);
            const termId = $btn.data('term-id');
            const termName = $btn.data('term-name');
            Swal.fire({
                title: 'Deactivate Term?', text: `Deactivate ${termName}?`,
                icon: 'warning', showCancelButton: true,
                confirmButtonColor: '#dc2626', cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, deactivate'
            }).then((result) => {
                if (result.isConfirmed) deactivateTerm($btn, termId, termName);
            });
        });
    }

    function activateTerm($button, termId, termName) {
        const originalHtml = $button.html();
        $button.html('<i class="fas fa-spinner fa-spin" style="font-size:11px"></i> Activating...').prop('disabled', true);
        $.ajax({
            url: '/term-dates/toggle-active', method: 'POST',
            data: { _token: '{{ csrf_token() }}', term_id: termId, is_active: 1 },
            success: function(response) {
                if (response.success) {
                    sessionStorage.setItem('toastMessage', `${termName} activated successfully`);
                    sessionStorage.setItem('toastType', 'success');
                    location.reload();
                } else {
                    $button.html(originalHtml).prop('disabled', false);
                    showToast('error', response.message || 'An error occurred');
                }
            },
            error: function(xhr) {
                $button.html(originalHtml).prop('disabled', false);
                showToast('error', xhr.responseJSON?.message || 'An error occurred');
            }
        });
    }

    function deactivateTerm($button, termId, termName) {
        const originalHtml = $button.html();
        $button.html('<i class="fas fa-spinner fa-spin" style="font-size:11px"></i> Deactivating...').prop('disabled', true);
        $.ajax({
            url: '/term-dates/toggle-active', method: 'POST',
            data: { _token: '{{ csrf_token() }}', term_id: termId, is_active: 0 },
            success: function(response) {
                if (response.success) {
                    sessionStorage.setItem('toastMessage', `${termName} deactivated`);
                    sessionStorage.setItem('toastType', 'info');
                    location.reload();
                } else {
                    $button.html(originalHtml).prop('disabled', false);
                    showToast('error', response.message || 'An error occurred');
                }
            },
            error: function(xhr) {
                $button.html(originalHtml).prop('disabled', false);
                showToast('error', xhr.responseJSON?.message || 'An error occurred');
            }
        });
    }

    rebindButtonEvents();
});
</script>
@endsection

@section('js')
    <script src="{{ URL::asset('assets/plugins/charts-c3/d3.v5.min.js') }}"></script>
    <script src="{{ URL::asset('assets/plugins/charts-c3/c3-chart.js') }}"></script>
    <script src="{{ URL::asset('assets/js/charts.js') }}"></script>
    <script src="{{ URL::asset('assets/plugins/echarts/echarts.js') }}"></script>
    <script src="{{ URL::asset('assets/plugins/peitychart/jquery.peity.min.js') }}"></script>
    <script src="{{ URL::asset('assets/plugins/peitychart/peitychart.init.js') }}"></script>
    <script src="{{ URL::asset('assets/js/apexcharts.js') }}"></script>
    <script src="{{ URL::asset('assets/plugins/moment/moment.js') }}"></script>
    <script src="{{ URL::asset('assets/plugins/bootstrap-daterangepicker/daterangepicker.js') }}"></script>
    <script src="{{ URL::asset('assets/js/daterange.js') }}"></script>
    <script src="{{ URL::asset('assets/plugins/jvectormap/jquery.vmap.js') }}"></script>
    <script src="{{ URL::asset('assets/plugins/jvectormap/jquery.vmap.world.js') }}"></script>
    <script src="{{ URL::asset('assets/plugins/jvectormap/jquery.vmap.sampledata.js') }}"></script>
    <script src="{{ URL::asset('assets/js/index1.js') }}"></script>
    <script src="{{ URL::asset('assets/plugins/datatable/js/jquery.dataTables.js') }}"></script>
    <script src="{{ URL::asset('assets/plugins/datatable/js/dataTables.bootstrap4.js') }}"></script>
    <script src="{{ URL::asset('assets/plugins/datatable/js/dataTables.buttons.min.js') }}"></script>
    <script src="{{ URL::asset('assets/plugins/datatable/js/buttons.bootstrap4.min.js') }}"></script>
    <script src="{{ URL::asset('assets/plugins/datatable/js/jszip.min.js') }}"></script>
    <script src="{{ URL::asset('assets/plugins/datatable/js/pdfmake.min.js') }}"></script>
    <script src="{{ URL::asset('assets/plugins/datatable/js/vfs_fonts.js') }}"></script>
    <script src="{{ URL::asset('assets/plugins/datatable/js/buttons.html5.min.js') }}"></script>
    <script src="{{ URL::asset('assets/plugins/datatable/js/buttons.print.min.js') }}"></script>
    <script src="{{ URL::asset('assets/plugins/datatable/js/buttons.colVis.min.js') }}"></script>
    <script src="{{ URL::asset('assets/plugins/datatable/dataTables.responsive.min.js') }}"></script>
    <script src="{{ URL::asset('assets/plugins/datatable/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ URL::asset('assets/js/datatables.js') }}"></script>
    <script src="{{ URL::asset('assets/plugins/counters/counterup.min.js') }}"></script>
    <script src="{{ URL::asset('assets/plugins/counters/waypoints.min.js') }}"></script>
    <script src="{{ URL::asset('assets/plugins/chart/chart.bundle.js') }}"></script>
    <script src="{{ URL::asset('assets/plugins/chart/utils.js') }}"></script>
@endsection