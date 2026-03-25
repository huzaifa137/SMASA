{{-- resources/views/itemGrading/grading-results.blade.php --}}
@extends('layouts-side-bar.master')

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

@php
    use App\Models\StudentBasic;
    use App\Http\Controllers\Helper;
@endphp

@section('content')
<style>
    :root {
        --primary-green: #287c44;
        --dark-green: #0d4b1e;
        --deep-green: #0d4b1f;
        --muted-green: #253f2d;
        --light-green: #3a9b5a;
        --bg-light: #f8fafc;
        --text-dark: #1e293b;
        --text-muted: #64748b;
        --border-light: #e2e8f0;
        --card-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.02);
        --hover-shadow: 0 20px 30px -10px rgba(40, 124, 68, 0.15);
    }

    * {
        font-family: 'Plus Jakarta Sans', sans-serif;
    }

    body {
        background: var(--bg-light);
        color: var(--text-dark);
    }

    .side-app {
        padding: 2rem;
        min-height: 100vh;
    }

    .stats-container {
        max-width: 1600px;
        margin: 0 auto;
    }

    .section-wrapper {
        margin-bottom: 2rem;
    }

    .modern-card {
        background: white;
        border-radius: 24px;
        border: 1px solid var(--border-light);
        box-shadow: var(--card-shadow);
        overflow: hidden;
        transition: all 0.3s ease;
        margin-bottom: 1.5rem;
    }

    .modern-card:hover {
        box-shadow: var(--hover-shadow);
        border-color: var(--primary-green);
    }

    .modern-card .card-header {
        background: linear-gradient(135deg, var(--primary-green), var(--dark-green));
        padding: 1.25rem 2rem;
        border-bottom: none;
    }

    .modern-card .card-header h4,
    .modern-card .card-header h5,
    .modern-card .card-header h6 {
        color: white;
        font-weight: 700;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-size: 1.1rem;
        letter-spacing: 0.02em;
    }

    .modern-card .card-header h4 i,
    .modern-card .card-header h5 i,
    .modern-card .card-header h6 i {
        color: rgba(255, 255, 255, 0.9);
    }

    .modern-card .card-body {
        padding: 2rem;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: white;
        border-radius: 20px;
        padding: 1.75rem;
        border: 1px solid var(--border-light);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        box-shadow: var(--card-shadow);
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--primary-green), var(--light-green));
    }

    .stat-card:hover {
        transform: translateY(-4px);
        border-color: var(--primary-green);
        box-shadow: var(--hover-shadow);
    }

    .stat-icon {
        width: 56px;
        height: 56px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.75rem;
        margin-bottom: 1.25rem;
        background: rgba(40, 124, 68, 0.1);
        color: var(--primary-green);
    }

    .stat-label {
        color: var(--text-muted);
        font-size: 0.875rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 0.5rem;
    }

    .stat-value {
        font-size: 2rem;
        font-weight: 800;
        color: var(--text-dark);
        line-height: 1.2;
        margin-bottom: 0.25rem;
    }

    .stat-trend {
        color: var(--text-muted);
        font-size: 0.875rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .section-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--text-dark);
        margin: 2rem 0 1.25rem 0;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding-left: 0.5rem;
        border-left: 4px solid var(--primary-green);
    }

    .section-title i {
        color: var(--primary-green);
        font-size: 1.1rem;
    }

    .section-title.d-flex {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .export-btn {
        padding: 0.5rem 1.5rem;
        border-radius: 12px;
        font-size: 0.875rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.3s ease;
        border: 2px solid var(--primary-green);
        background: white;
        color: var(--primary-green);
        cursor: pointer;
    }

    .export-btn:hover {
        background: var(--primary-green);
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(40, 124, 68, 0.2);
    }

    .export-btn i {
        font-size: 0.9rem;
    }

    .badge-level {
        background: rgba(255, 255, 255, 0.2);
        color: white;
        padding: 0.5rem 1.25rem;
        border-radius: 30px;
        font-weight: 600;
        font-size: 0.9rem;
        border: 1px solid rgba(255, 255, 255, 0.3);
        backdrop-filter: blur(5px);
    }

    .distribution-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .modern-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        background: white;
        border-radius: 16px;
        overflow: hidden;
    }

    .modern-table thead th {
        background: linear-gradient(135deg, var(--primary-green), var(--dark-green));
        color: white;
        font-weight: 600;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        padding: 1rem 1.25rem;
        border-right: 1px solid rgba(255, 255, 255, 0.1);
        white-space: nowrap;
    }

    .modern-table thead th:last-child {
        border-right: none;
    }

    .modern-table tbody tr {
        transition: all 0.2s ease;
        border-bottom: 1px solid var(--border-light);
    }

    .modern-table tbody tr:hover {
        background: rgba(40, 124, 68, 0.02);
    }

    .modern-table tbody td {
        padding: 1rem 1.25rem;
        color: var(--text-dark);
        border-bottom: 1px solid var(--border-light);
        vertical-align: middle;
    }

    .modern-badge {
        padding: 0.4rem 1rem;
        border-radius: 100px;
        font-weight: 500;
        font-size: 0.8rem;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        white-space: nowrap;
    }

    .modern-badge.success {
        background: rgba(40, 124, 68, 0.1);
        color: var(--primary-green);
        border: 1px solid rgba(40, 124, 68, 0.2);
    }

    .modern-badge.danger {
        background: #fef2f2;
        color: #dc2626;
        border: 1px solid #fee2e2;
    }

    .modern-badge.warning {
        background: #fffbeb;
        color: #d97706;
        border: 1px solid #fef3c7;
    }

    .modern-badge.info {
        background: #eff6ff;
        color: #2563eb;
        border: 1px solid #dbeafe;
    }

    .grade-badge {
        display: inline-block;
        padding: 0.4rem 1rem;
        border-radius: 30px;
        font-weight: 600;
        font-size: 0.85rem;
        min-width: 60px;
        text-align: center;
    }

    .grade-badge.excellent {
        background: linear-gradient(135deg, #10b981, #059669);
        color: white;
    }

    .grade-badge.very-good {
        background: linear-gradient(135deg, #3b82f6, #2563eb);
        color: white;
    }

    .grade-badge.good {
        background: linear-gradient(135deg, #f59e0b, #d97706);
        color: white;
    }

    .grade-badge.pass {
        background: linear-gradient(135deg, #8b5cf6, #7c3aed);
        color: white;
    }

    .grade-badge.fail {
        background: linear-gradient(135deg, #ef4444, #dc2626);
        color: white;
    }

    .gender-badge {
        padding: 0.3rem 0.8rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 500;
    }

    .gender-badge.male {
        background: #dbeafe;
        color: #1e40af;
        border: 1px solid #bfdbfe;
    }

    .gender-badge.female {
        background: #fce7f3;
        color: #9d174d;
        border: 1px solid #fbcfe8;
    }

    .action-btn {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(40, 124, 68, 0.1);
        color: var(--primary-green);
        border: 1px solid rgba(40, 124, 68, 0.2);
        transition: all 0.2s ease;
        cursor: pointer;
    }

    .action-btn:hover {
        background: var(--primary-green);
        color: white;
        transform: scale(1.1);
    }

    .dataTables_wrapper .dataTables_length select,
    .dataTables_wrapper .dataTables_filter input {
        border: 1px solid var(--border-light);
        border-radius: 12px;
        padding: 0.5rem;
        font-family: 'Plus Jakarta Sans', sans-serif;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button {
        border-radius: 10px;
        margin: 0 3px;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: linear-gradient(135deg, var(--primary-green), var(--dark-green));
        border: none;
        color: white !important;
    }

    .distribution-table {
        width: 100%;
        border-collapse: collapse;
    }

    .distribution-table th {
        background: #f8fafc;
        color: var(--text-dark);
        font-weight: 600;
        padding: 1rem;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        border-bottom: 2px solid var(--border-light);
    }

    .distribution-table td {
        padding: 1rem;
        border-bottom: 1px solid var(--border-light);
    }

    .distribution-table tbody tr:hover {
        background: rgba(40, 124, 68, 0.02);
    }

    .percentage-bar {
        width: 100%;
        height: 8px;
        background: var(--border-light);
        border-radius: 4px;
        overflow: hidden;
        margin-top: 5px;
    }

    .percentage-fill {
        height: 100%;
        background: linear-gradient(90deg, var(--primary-green), var(--light-green));
        border-radius: 4px;
    }

    @media (max-width: 992px) {
        .distribution-grid {
            grid-template-columns: 1fr;
        }
        
        .side-app {
            padding: 1rem;
        }
        
        .section-title.d-flex {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .export-btn {
            width: 100%;
            justify-content: center;
        }
    }

    /* Modal Styles */
    .modern-modal .modal-content {
        border-radius: 24px;
        border: none;
        box-shadow: var(--hover-shadow);
    }

    .modern-modal .modal-header {
        background: linear-gradient(135deg, var(--primary-green), var(--dark-green));
        color: white;
        border-radius: 24px 24px 0 0;
        padding: 1.25rem 1.5rem;
        border-bottom: none;
    }

    .modern-modal .modal-header .btn-close {
        filter: brightness(0) invert(1);
        opacity: 0.8;
    }

    .modern-modal .modal-body {
        padding: 2rem;
    }

    .modern-modal .modal-footer {
        border-top: 1px solid var(--border-light);
        padding: 1.25rem 1.5rem;
    }

    .subject-marks-table {
        border: 1px solid var(--border-light);
        border-radius: 16px;
        overflow: hidden;
    }

    .subject-marks-table th {
        background: #f8fafc;
        color: var(--text-dark);
        font-weight: 600;
        padding: 0.75rem 1rem;
    }

    .subject-marks-table td {
        padding: 0.75rem 1rem;
        border-bottom: 1px solid var(--border-light);
    }
</style>

<div class="side-app">
    <div class="stats-container">
        <!-- Header Card -->
        <div class="modern-card section-wrapper">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>
                    <i class="fas fa-star"></i>
                    <span>Grading Results</span>
                </h4>
                <span class="badge-level">
                    <i class="fas fa-school me-2"></i>{{ $schoolName }} | 
                    <i class="fas fa-tag ms-2 me-1"></i>{{ $category }} | 
                    <i class="fas fa-calendar ms-2 me-1"></i>{{ $year }}
                    @if($level)
                        | <i class="fas fa-layer-group ms-2 me-1"></i>Level {{ $level }}
                    @endif
                </span>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-grid section-wrapper">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-label">Total Students</div>
                <div class="stat-value">{{ $statistics['count'] }}</div>
                <div class="stat-trend">
                    <i class="fas fa-user-graduate" style="color: var(--primary-green);"></i>
                    Enrolled students
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="color: #3b82f6;">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="stat-label">Average Percentage</div>
                <div class="stat-value">{{ $statistics['average'] }}%</div>
                <div class="stat-trend">
                    <i class="fas fa-arrow-up" style="color: #3b82f6;"></i>
                    Class average
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="color: #f59e0b;">
                    <i class="fas fa-trophy"></i>
                </div>
                <div class="stat-label">Highest Score</div>
                <div class="stat-value">{{ $statistics['highest'] }}%</div>
                <div class="stat-trend">
                    <i class="fas fa-crown" style="color: #f59e0b;"></i>
                    Top performer
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="color: #ef4444;">
                    <i class="fas fa-chart-bar"></i>
                </div>
                <div class="stat-label">Lowest Score</div>
                <div class="stat-value">{{ $statistics['lowest'] }}%</div>
                <div class="stat-trend">
                    <i class="fas fa-arrow-down" style="color: #ef4444;"></i>
                    Needs attention
                </div>
            </div>
        </div>

        <!-- Distribution Charts Row -->
        <div class="distribution-grid section-wrapper">
            <!-- Grade Distribution Card -->
            <div class="modern-card">
                <div class="card-header" style="background: linear-gradient(135deg, #3b82f6, #1e40af);">
                    <h6>
                        <i class="fas fa-chart-pie me-2"></i>
                        Grade Distribution (Marks)
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="distribution-table">
                            <thead>
                                <tr>
                                    <th>Grade</th>
                                    <th class="text-center">Count</th>
                                    <th class="text-center">Percentage</th>
                                    <th>Distribution</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($statistics['grade_distribution'] as $grade => $count)
                                    @php
                                        $percentage = $statistics['count'] > 0 ? round(($count / $statistics['count']) * 100, 1) : 0;
                                        $gradeClass = '';
                                        if($grade >= 80) $gradeClass = 'excellent';
                                        elseif($grade >= 70) $gradeClass = 'very-good';
                                        elseif($grade >= 60) $gradeClass = 'good';
                                        elseif($grade >= 50) $gradeClass = 'pass';
                                        else $gradeClass = 'fail';
                                    @endphp
                                    <tr>
                                        <td>
                                            <span class="grade-badge {{ $gradeClass }}">
                                                {{ $grade }}%
                                            </span>
                                        </td>
                                        <td class="text-center fw-bold">{{ $count }}</td>
                                        <td class="text-center">
                                            <span class="modern-badge {{ $percentage >= 50 ? 'success' : 'warning' }}">
                                                {{ $percentage }}%
                                            </span>
                                        </td>
                                        <td style="width: 150px;">
                                            <div class="percentage-bar">
                                                <div class="percentage-fill" style="width: {{ $percentage }}%"></div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Classification Distribution Card -->
            <div class="modern-card">
                <div class="card-header" style="background: linear-gradient(135deg, #10b981, #059669);">
                    <h6>
                        <i class="fas fa-layer-group me-2"></i>
                        Classification Distribution (Points)
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="distribution-table">
                            <thead>
                                <tr>
                                    <th>Classification</th>
                                    <th class="text-center">Count</th>
                                    <th class="text-center">Percentage</th>
                                    <th>Distribution</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach (array_reverse($statistics['class_distribution'], true) as $class => $count)
                                    @php
                                        $percentage = $statistics['count'] > 0 ? round(($count / $statistics['count']) * 100, 1) : 0;
                                        $classColor = match($class) {
                                            'FIRST CLASS' => 'success',
                                            'SECOND CLASS UPPER' => 'info',
                                            'SECOND CLASS LOWER' => 'warning',
                                            'THIRD CLASS' => 'warning',
                                            'FAIL' => 'danger',
                                            default => 'secondary'
                                        };
                                    @endphp
                                    <tr>
                                        <td class="fw-bold">{{ $class }}</td>
                                        <td class="text-center">{{ $count }}</td>
                                        <td class="text-center">
                                            <span class="modern-badge {{ $classColor }}">
                                                {{ $percentage }}%
                                            </span>
                                        </td>
                                        <td style="width: 150px;">
                                            <div class="percentage-bar">
                                                <div class="percentage-fill" style="width: {{ $percentage }}%"></div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Results Table -->
        <div class="modern-card section-wrapper">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5>
                    <i class="fas fa-table me-2"></i>
                    Student Results
                </h5>
                <div class="d-flex gap-2">
                    <button class="export-btn" onclick="exportToExcel()">
                        <i class="fas fa-file-excel"></i>
                        Export
                    </button>
                    <button class="export-btn" onclick="printResults()">
                        <i class="fas fa-print"></i>
                        Print
                    </button>
                </div>
            </div>
            <div class="card-body">
                <form id="saveResultsForm" method="POST" action="{{ route('iteb.save.grading') }}">
                    @csrf
                    <input type="hidden" name="year" value="{{ $year }}">
                    <input type="hidden" name="category" value="{{ $category }}">
                    <input type="hidden" name="school_number" value="{{ $schoolNumber }}">
                    <input type="hidden" name="level" value="{{ $level }}">

                    <div class="table-responsive">
                        <table class="modern-table" id="resultsTable">
                            <thead>
                                <tr>
                                    <th width="50">#</th>
                                    <th>Index Number</th>
                                    <th>Student Name</th>
                                    <th>School</th>
                                    <th>Gender</th>
                                    <th>Percentage</th>
                                    <th>Grade</th>
                                    <th width="80">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($results as $studentId => $result)
                                    @php
                                        $StudentSex = StudentBasic::where('Student_ID', $studentId)->value('StudentSex');
                                        $percentage = $result['percentage'];
                                        $gradeClass = $percentage >= 80 ? 'excellent' : 
                                                     ($percentage >= 70 ? 'very-good' : 
                                                     ($percentage >= 60 ? 'good' : 
                                                     ($percentage >= 50 ? 'pass' : 'fail')));
                                    @endphp
                                    <tr>
                                        <td>
                                            <span class="rank-star default">
                                                {{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="fw-bold">{{ $studentId }}</div>
                                            <input type="hidden" name="results[{{ $studentId }}][total_marks]" value="{{ $result['total_marks'] }}">
                                            <input type="hidden" name="results[{{ $studentId }}][percentage]" value="{{ $result['percentage'] }}">
                                            <input type="hidden" name="results[{{ $studentId }}][grade]" value="{{ $result['grade'] }}">
                                            <input type="hidden" name="results[{{ $studentId }}][classification]" value="{{ $result['classification'] }}">
                                        </td>
                                        <td>
                                            <div class="fw-bold">{{ Helper::parseStudentId($studentId, 'student') }}</div>
                                        </td>
                                        <td>{{ Helper::parseStudentId($studentId, 'school') }}</td>
                                        <td>
                                            @if(strtolower($StudentSex) == 'male')
                                                <span class="gender-badge male">
                                                    <i class="fas fa-mars"></i> Male
                                                </span>
                                            @else
                                                <span class="gender-badge female">
                                                    <i class="fas fa-venus"></i> Female
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="grade-badge {{ $gradeClass }}">
                                                {{ $percentage }}%
                                            </span>
                                        </td>
                                        <td>
                                            <div>
                                                <strong>{{ $result['grade'] }}</strong>
                                                <small class="d-block text-muted">{{ $result['grade_comment'] }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            <button type="button" class="action-btn view-details"
                                                data-bs-toggle="modal" data-bs-target="#studentDetailsModal"
                                                data-student-id="{{ $studentId }}"
                                                data-marks-details='{{ json_encode($result['marks_details']) }}'
                                                data-total-marks="{{ $result['total_marks'] }}"
                                                data-total-possible="{{ $result['total_possible'] }}"
                                                data-percentage="{{ $result['percentage'] }}"
                                                data-grade="{{ $result['grade'] }}"
                                                data-grade-comment="{{ $result['grade_comment'] }}"
                                                data-classification="{{ $result['classification'] }}"
                                                data-classification-comment="{{ $result['classification_comment'] }}">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </form>

                <div class="row mt-4">
                    <div class="col-12 text-center">
                        <a href="{{ route('iteb.grading.summary') }}" class="export-btn" style="border-color: var(--text-muted); color: var(--text-muted);">
                            <i class="fas fa-arrow-left me-2"></i> Back to Filters
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Student Details Modal -->
<div class="modal fade modern-modal" id="studentDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-user-graduate me-2"></i>
                    Student Performance Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modalContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-success" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="export-btn" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Close
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function () {
        // Initialize DataTable
        if (typeof $.fn.DataTable !== 'undefined' && !$.fn.DataTable.isDataTable('#resultsTable')) {
            $('#resultsTable').DataTable({
                pageLength: 25,
                order: [[5, 'desc']], // Sort by percentage column
                dom: 'Bfrtip',
                buttons: [
                    {
                        extend: 'excelHtml5',
                        text: '<i class="fas fa-file-excel"></i> Excel',
                        className: 'export-btn',
                        title: 'Grading_Results_{{ $schoolName }}_{{ $category }}_{{ $year }}',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6]
                        }
                    },
                    {
                        extend: 'print',
                        text: '<i class="fas fa-print"></i> Print',
                        className: 'export-btn',
                        title: 'Grading Results - {{ $schoolName }} - {{ $category }} - {{ $year }}',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6]
                        }
                    }
                ],
                language: {
                    search: "Search:",
                    searchPlaceholder: "Search students...",
                    lengthMenu: "Show _MENU_ entries",
                    info: "Showing _START_ to _END_ of _TOTAL_ students",
                    paginate: {
                        first: '<i class="fas fa-angle-double-left"></i>',
                        previous: '<i class="fas fa-angle-left"></i>',
                        next: '<i class="fas fa-angle-right"></i>',
                        last: '<i class="fas fa-angle-double-right"></i>'
                    }
                }
            });
        }

        // View Details Modal
        $('.view-details').on('click', function () {
            const studentId = $(this).data('student-id');
            const marksDetails = $(this).data('marks-details');
            const totalMarks = $(this).data('total-marks');
            const totalPossible = $(this).data('total-possible');
            const percentage = $(this).data('percentage');
            const grade = $(this).data('grade');
            const gradeComment = $(this).data('grade-comment');
            const classification = $(this).data('classification');
            const classificationComment = $(this).data('classification-comment');

            // Build modal content
            let modalContent = `
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="bg-light p-3 rounded-3">
                            <h6 class="mb-2 text-muted">Student Index Number</h6>
                            <h5 class="fw-bold text-success">${studentId}</h5>
                        </div>
                    </div>
                </div>
                
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="stat-card p-3" style="margin-bottom: 0;">
                            <div class="stat-icon" style="width: 40px; height: 40px; font-size: 1.25rem; margin-bottom: 0.5rem;">
                                <i class="fas fa-star"></i>
                            </div>
                            <div class="stat-label" style="font-size: 0.7rem;">Total Marks</div>
                            <div class="stat-value" style="font-size: 1.5rem;">${totalMarks} <span style="font-size: 0.9rem; color: var(--text-muted);">/ ${totalPossible}</span></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-card p-3" style="margin-bottom: 0;">
                            <div class="stat-icon" style="width: 40px; height: 40px; font-size: 1.25rem; margin-bottom: 0.5rem; color: #3b82f6;">
                                <i class="fas fa-percent"></i>
                            </div>
                            <div class="stat-label" style="font-size: 0.7rem;">Percentage</div>
                            <div class="stat-value" style="font-size: 1.5rem;">${percentage}%</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-card p-3" style="margin-bottom: 0;">
                            <div class="stat-icon" style="width: 40px; height: 40px; font-size: 1.25rem; margin-bottom: 0.5rem; color: #10b981;">
                                <i class="fas fa-tag"></i>
                            </div>
                            <div class="stat-label" style="font-size: 0.7rem;">Grade</div>
                            <div class="stat-value" style="font-size: 1.5rem;">${grade}</div>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="bg-light p-3 rounded-3">
                            <small class="text-muted d-block mb-1">Grade Comment</small>
                            <span class="fw-bold">${gradeComment || 'N/A'}</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="bg-light p-3 rounded-3">
                            <small class="text-muted d-block mb-1">Classification</small>
                            <span class="fw-bold text-success">${classification}</span>
                            <small class="d-block text-muted mt-1">${classificationComment || ''}</small>
                        </div>
                    </div>
                </div>

                <h6 class="fw-bold mb-3">
                    <i class="fas fa-book-open text-success me-2"></i>
                    Subject Marks
                </h6>
                <div class="subject-marks-table">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Subject</th>
                                <th class="text-center">Marks Obtained</th>
                            </tr>
                        </thead>
                        <tbody>
            `;

            // Add subject marks
            if (marksDetails && marksDetails.length > 0) {
                marksDetails.forEach(mark => {
                    const subjectName = mark.subject_name || 'Unknown Subject';
                    const markValue = mark.mark || 'N/A';
                    const markClass = markValue >= 50 ? 'text-success' : 'text-danger';
                    
                    modalContent += `
                        <tr>
                            <td>${subjectName}</td>
                            <td class="text-center">
                                <span class="fw-bold ${markClass}">${markValue}</span>
                            </td>
                        </tr>
                    `;
                });
            } else {
                modalContent += `
                    <tr>
                        <td colspan="2" class="text-center text-muted py-4">
                            <i class="fas fa-exclamation-circle me-2"></i>No subject marks available
                        </td>
                    </tr>
                `;
            }

            modalContent += `
                        </tbody>
                    </table>
                </div>
            `;

            // Update modal content
            $('#modalContent').html(modalContent);
        });

        // Clean up modal backdrops
        $('#studentDetailsModal').on('hidden.bs.modal', function () {
            document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
            document.body.classList.remove('modal-open');
        });
    });

    // Export functions
    window.exportToExcel = function () {
        if ($.fn.DataTable && $.fn.DataTable.isDataTable('#resultsTable')) {
            $('#resultsTable').DataTable().button('.buttons-excel').trigger();
        }
    }

    window.printResults = function () {
        if ($.fn.DataTable && $.fn.DataTable.isDataTable('#resultsTable')) {
            $('#resultsTable').DataTable().button('.buttons-print').trigger();
        }
    }

    // Save results function (if needed)
    window.saveResults = function () {
        Swal.fire({
            title: 'Save Grading Results?',
            text: "Are you sure you want to save these grading results?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#287c44',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, save them!'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.getElementById('saveResultsForm');
                const formData = new FormData(form);

                fetch('{{ route('iteb.save.grading') }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Saved!',
                            text: data.message,
                            confirmButtonColor: '#287c44'
                        }).then(() => location.reload());
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: data.message,
                            confirmButtonColor: '#287c44'
                        });
                    }
                })
                .catch(err => {
                    console.error(err);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'An error occurred while saving results.',
                        confirmButtonColor: '#287c44'
                    });
                });
            }
        });
    }
</script>
@endsection