@extends('layouts-side-bar.master')

@section('css')
    <link href="{{ URL::asset('assets/plugins/datatable/dataTables.bootstrap4.min.css') }}" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    <style>
        :root {
            --brand: #5351e4;
            --brand-light: #2C29CA;
            --brand-dark: #2C29CA;
            --brand-muted: rgba(83, 81, 228, .12);
            --accent: #5351e4;
            --accent-muted: rgba(83, 81, 228, .12);
            --purple: #6c3fc5;
            --purple-muted: rgba(108, 63, 197, .12);
            --danger: #ef4444;
            --danger-muted: rgba(239, 68, 68, .12);
            --info: #3b82f6;
            --info-muted: rgba(59, 130, 246, .12);
            --success: #10b981;
            --success-muted: rgba(16, 185, 129, .12);
            --warning: #f59e0b;
            --warning-muted: rgba(245, 158, 11, .12);
            --surface: #ffffff;
            --surface-2: #f7f9f7;
            --surface-3: #eef3ef;
            --border: rgba(83, 81, 228, .14);
            --text-primary: #0f1f14;
            --text-secondary: #4b6356;
            --text-muted: #8ca898;
            --shadow-sm: 0 2px 8px rgba(0, 0, 0, .06);
            --shadow-md: 0 4px 20px rgba(0, 0, 0, .09);
            --shadow-lg: 0 8px 40px rgba(0, 0, 0, .12);
            --radius-sm: 10px;
            --radius-md: 16px;
            --radius-lg: 24px;
            --radius-xl: 32px;
            --font: 'Sora', sans-serif;
            --mono: 'JetBrains Mono', monospace;
        }

        * {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        body {
            background: #F8FAFC;
        }

        /* Modern Glassmorphism Header with Blue Gradient */
        .glass-header {
            background: linear-gradient(135deg, rgba(83, 81, 228, 0.98) 0%, rgba(44, 41, 202, 0.95) 100%);
            backdrop-filter: blur(10px);
            border-radius: 32px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 20px 40px -12px rgba(83, 81, 228, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.2);
            position: relative;
            overflow: hidden;
        }

        .glass-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            border-radius: 50%;
        }

        .glass-header::after {
            content: '';
            position: absolute;
            bottom: -30%;
            left: -10%;
            width: 250px;
            height: 250px;
            background: radial-gradient(circle, rgba(108, 63, 197, 0.15) 0%, transparent 70%);
            border-radius: 50%;
        }

        /* Animated Stats Cards */
        .stat-card-new {
            background: white;
            border-radius: 28px;
            padding: 1.5rem;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            cursor: pointer;
            border: 1px solid rgba(83, 81, 228, 0.1);
        }

        .stat-card-new::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(83, 81, 228, 0.1), transparent);
            transition: left 0.5s;
        }

        .stat-card-new:hover::before {
            left: 100%;
        }

        .stat-card-new:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px -12px rgba(83, 81, 228, 0.25);
            border-color: var(--brand);
        }

        .stat-gradient-bg {
            position: absolute;
            top: -50%;
            right: -20%;
            width: 150px;
            height: 150px;
            border-radius: 50%;
            opacity: 0.1;
            filter: blur(40px);
        }

        /* Kanban Board Layout */
        .kanban-board {
            display: flex;
            gap: 1.5rem;
            overflow-x: auto;
            padding-bottom: 1rem;
            min-height: 600px;
        }

        .kanban-column {
            flex: 1;
            min-width: 320px;
            background: #F1F5F9;
            border-radius: 24px;
            padding: 1rem;
            position: relative;
        }

        .column-header {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 16px;
            background: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-left: 4px solid var(--brand);
        }

        .column-title {
            font-weight: 700;
            font-size: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .column-count {
            background: linear-gradient(135deg, var(--brand), var(--brand-light));
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 99px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .exam-card {
            background: white;
            border-radius: 20px;
            padding: 1rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
            border: 1px solid #E2E8F0;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .exam-card:hover {
            transform: translateX(4px);
            box-shadow: 0 10px 25px -5px rgba(83, 81, 228, 0.2);
            border-color: var(--brand);
        }

        .exam-code-badge {
            display: inline-block;
            background: linear-gradient(135deg, var(--brand-muted), #DBEAFE);
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            font-size: 0.7rem;
            font-weight: 600;
            color: var(--brand);
            font-family: monospace;
        }

        .deadline-timer {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.25rem 0.5rem;
            border-radius: 10px;
            font-size: 0.7rem;
            font-weight: 600;
        }

        .timer-urgent {
            background: #FEF3C7;
            color: #D97706;
        }

        .timer-normal {
            background: #D1FAE5;
            color: #059669;
        }

        .timer-expired {
            background: #FEE2E2;
            color: #DC2626;
        }

        /* Floating Action Button - Blue */
        .fab-create {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            width: 56px;
            height: 56px;
            border-radius: 28px;
            background: linear-gradient(135deg, var(--brand), var(--brand-light));
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 25px -5px rgba(83, 81, 228, 0.5);
            cursor: pointer;
            transition: all 0.3s ease;
            z-index: 100;
        }

        .fab-create:hover {
            transform: scale(1.1);
            box-shadow: 0 20px 35px -8px rgba(83, 81, 228, 0.7);
        }

        /* Modal Styles */
        .detail-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(8px);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal-content-custom {
            background: white;
            border-radius: 32px;
            max-width: 600px;
            width: 90%;
            max-height: 85vh;
            overflow-y: auto;
            animation: modalSlideIn 0.3s ease;
        }

        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: scale(0.9);
            }

            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        /* Status Colors with Blue accents */
        .status-draft {
            border-left: 4px solid #94A3B8;
        }

        .status-active {
            border-left: 4px solid var(--success);
        }

        .status-marks_entry {
            border-left: 4px solid var(--warning);
        }

        .status-closed {
            border-left: 4px solid var(--danger);
        }

        .status-results_released {
            border-left: 4px solid var(--brand);
        }

        /* Blue Gradient Buttons */
        .btn-primary-blue {
            background: linear-gradient(135deg, var(--brand), var(--brand-light));
            color: white;
            border: none;
            transition: all 0.3s ease;
        }

        .btn-primary-blue:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(83, 81, 228, 0.3);
            color: white;
        }

        /* Blue Progress Bar */
        .progress-bar-blue {
            background: linear-gradient(90deg, var(--brand), var(--brand-light));
        }

        /* Responsive */
        @media (max-width: 768px) {
            .kanban-board {
                flex-direction: column;
            }

            .kanban-column {
                min-width: 100%;
            }
        }

        /* Scrollbar Styling - Blue */
        .kanban-board::-webkit-scrollbar {
            height: 8px;
        }

        .kanban-board::-webkit-scrollbar-track {
            background: #E2E8F0;
            border-radius: 99px;
        }

        .kanban-board::-webkit-scrollbar-thumb {
            background: var(--brand);
            border-radius: 99px;
        }

        /* Blue Card Headers */
        .blue-card-header {
            background: linear-gradient(135deg, var(--brand-muted), rgba(83, 81, 228, 0.05));
            border-bottom: 2px solid var(--brand);
        }

        /* Stats Card Icons - Blue */
        .stat-icon-blue {
            background: linear-gradient(135deg, var(--brand-muted), rgba(83, 81, 228, 0.15));
            color: var(--brand);
        }
    </style>
@endsection

@section('content')
    <div class="side-app" style="padding: 1.5rem;">

        <!-- Modern Glass Header with Blue Theme -->
        <div class="glass-header">
            <div class="row align-items-center">
                <div class="col-lg-7">
                    <div class="mb-2">
                        <span class="badge"
                            style="background: rgba(255,255,255,0.2); color: white; padding: 0.5rem 1rem; border-radius: 99px; backdrop-filter: blur(8px);">
                            <i class="fas fa-chart-line me-2"></i> Dashboard Overview
                        </span>
                    </div>
                    <h1 style="font-size: 2.5rem; font-weight: 800; color: white; margin-bottom: 0.5rem;">
                        <i class="fas fa-layer-group me-2"></i> Examination Hub
                    </h1>
                    <p class="mb-0" style="font-size: 1.1rem; color: rgba(255,255,255,0.9);">
                        Centralized command center for all academic assessments
                    </p>
                </div>
                <div class="col-lg-5 text-lg-end mt-3 mt-lg-0">
                    <div class="d-flex gap-2 justify-content-lg-end">

                        <button class="btn"
                            style="background: #FFF; color: #2C29CA; border-radius: 16px; padding: 0.5rem 1.5rem; backdrop-filter: blur(8px);"
                            style="border-radius: 16px; padding: 0.5rem 1.5rem;" onclick="openCreateModal()">
                            <i class="fas fa-plus me-2"></i> New Exam
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enhanced Statistics with Blue Theme -->
        @php
            $total = $examinations->count();
            $active = $examinations->where('status', 'active')->count();
            $entry = $examinations->where('status', 'marks_entry')->count();
            $released = $examinations->where('status', 'results_released')->count();
            $draft = $examinations->where('status', 'draft')->count();
        @endphp

        <div class="row g-4 mb-5">
            <div class="col-md-6 col-lg-3">
                <div class="stat-card-new" onclick="filterByStatus('all')">
                    <div class="stat-gradient-bg" style="background: var(--brand);"></div>
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="stat-icon-blue"
                            style="width: 48px; height: 48px; border-radius: 20px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-clipboard-list fs-4" style="color: var(--brand);"></i>
                        </div>
                        <span class="badge" style="background: var(--brand-muted); color: var(--brand);">Total</span>
                    </div>
                    <h2 class="fw-bold mb-1" style="font-size: 2.5rem; color: var(--brand);">{{ $total }}</h2>
                    <p class="text-muted mb-0">Total Examinations</p>
                    <div class="mt-3">
                        <small class="text-primary">
                            <i class="fas fa-notes-medical me-1"></i> Total Examinations
                        </small>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="stat-card-new" onclick="filterByStatus('active')">
                    <div class="stat-gradient-bg" style="background: var(--success);"></div>
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div
                            style="width: 48px; height: 48px; background: var(--success-muted); border-radius: 20px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-play-circle fs-4" style="color: var(--success);"></i>
                        </div>
                        <span class="badge bg-light">Live</span>
                    </div>
                    <h2 class="fw-bold mb-1" style="font-size: 2.5rem;">{{ $active }}</h2>
                    <p class="text-muted mb-0">Ongoing Examinations</p>
                    <div class="mt-3">
                        <small class="text-success">
                            <i class="fas fa-chart-line me-1"></i> In Progress
                        </small>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="stat-card-new" onclick="filterByStatus('marks_entry')">
                    <div class="stat-gradient-bg" style="background: var(--warning);"></div>
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div
                            style="width: 48px; height: 48px; background: var(--warning-muted); border-radius: 20px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-pen-alt fs-4" style="color: var(--warning);"></i>
                        </div>
                        <span class="badge bg-light">Entry</span>
                    </div>
                    <h2 class="fw-bold mb-1" style="font-size: 2.5rem;">{{ $entry }}</h2>
                    <p class="text-muted mb-0">Marks Entry Phase</p>
                    <div class="mt-3">
                        <small class="text-warning">
                            <i class="fas fa-hourglass-half me-1"></i> Pending Grading
                        </small>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="stat-card-new" onclick="filterByStatus('results_released')">
                    <div class="stat-gradient-bg" style="background: var(--brand);"></div>
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div
                            style="width: 48px; height: 48px; background: var(--brand-muted); border-radius: 20px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-trophy fs-4" style="color: var(--brand);"></i>
                        </div>
                        <span class="badge" style="background: var(--brand-muted); color: var(--brand);">Done</span>
                    </div>
                    <h2 class="fw-bold mb-1" style="font-size: 2.5rem; color: var(--brand);">{{ $released }}</h2>
                    <p class="text-muted mb-0">Results Released</p>
                    <div class="mt-3">
                        <small style="color: var(--brand);">
                            <i class="fas fa-check-circle me-1"></i> Completed
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kanban Board View -->
        <div class="kanban-board" id="kanbanBoard">
            <!-- Draft Column -->
            <div class="kanban-column" data-status="draft">
                <div class="column-header">
                    <div class="column-title">
                        <i class="fas fa-pen-fancy" style="color: #94A3B8;"></i>
                        Draft 
                        <span class="column-count"
                            id="draftCount">{{ $examinations->where('status', 'draft')->count() }}</span>
                    </div>
                    {{-- <i class="fas fa-ellipsis-h text-muted"></i> --}}
                </div>
                <div class="column-cards" id="draftCards">
                    @foreach($examinations->where('status', 'draft') as $exam)
                        @include('examination.partials.exam-card', ['exam' => $exam])
                    @endforeach
                </div>
            </div>

            <!-- Active Column -->
            <div class="kanban-column" data-status="active">
                <div class="column-header">
                    <div class="column-title">
                        <i class="fas fa-play-circle" style="color: var(--success);"></i>
                        Active
                        <span class="column-count"
                            id="activeCount">{{ $examinations->where('status', 'active')->count() }}</span>
                    </div>
                    {{-- <i class="fas fa-ellipsis-h text-muted"></i> --}}
                </div>
                <div class="column-cards" id="activeCards">
                    @foreach($examinations->where('status', 'active') as $exam)
                        @include('examination.partials.exam-card', ['exam' => $exam])
                    @endforeach
                </div>
            </div>

            <!-- Marks Entry Column -->
            <div class="kanban-column" data-status="marks_entry">
                <div class="column-header">
                    <div class="column-title">
                        <i class="fas fa-edit" style="color: var(--warning);"></i>
                        Marks Entry
                        <span class="column-count"
                            id="entryCount">{{ $examinations->where('status', 'marks_entry')->count() }}</span>
                    </div>
                    {{-- <i class="fas fa-ellipsis-h text-muted"></i> --}}
                </div>
                <div class="column-cards" id="entryCards">
                    @foreach($examinations->where('status', 'marks_entry') as $exam)
                        @include('examination.partials.exam-card', ['exam' => $exam])
                    @endforeach
                </div>
            </div>

            <!-- Closed Column -->
            <div class="kanban-column" data-status="closed">
                <div class="column-header">
                    <div class="column-title">
                        <i class="fas fa-lock" style="color: var(--danger);"></i>
                        Closed
                        <span class="column-count"
                            id="closedCount">{{ $examinations->where('status', 'closed')->count() }}</span>
                    </div>
                    {{-- <i class="fas fa-ellipsis-h text-muted"></i> --}}
                </div>
                <div class="column-cards" id="closedCards">
                    @foreach($examinations->where('status', 'closed') as $exam)
                        @include('examination.partials.exam-card', ['exam' => $exam])
                    @endforeach
                </div>
            </div>

            <!-- Results Released Column -->
            <div class="kanban-column" data-status="results_released">
                <div class="column-header">
                    <div class="column-title">
                        <i class="fas fa-award" style="color: var(--brand);"></i>
                        Results Released
                        <span class="column-count"
                            id="releasedCount">{{ $examinations->where('status', 'results_released')->count() }}</span>
                    </div>
                    {{-- <i class="fas fa-ellipsis-h text-muted"></i> --}}
                </div>
                <div class="column-cards" id="releasedCards">
                    @foreach($examinations->where('status', 'results_released') as $exam)
                        @include('examination.partials.exam-card', ['exam' => $exam])
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Floating Action Button -->
        <div class="fab-create" onclick="openCreateModal()">
            <i class="fas fa-plus fs-4"></i>
        </div>

        <!-- Detail Modal -->
        <div id="detailModal" class="detail-modal">
            <div class="modal-content-custom">
                <div id="modalContent"></div>
            </div>
        </div>
    </div>
    </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>

    <script>
        let currentFilter = 'all';

        function openCreateModal() {
            window.location.href = '{{ route("examination.create") }}';
        }

        function viewExamDetails(examId) {
            Swal.fire({
                title: 'Loading...',
                text: 'Fetching examination details',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: `/examinations/${examId}/details`,
                type: 'GET',
                success: function (response) {
                    // Determine status color
                    let statusColor = '';
                    let statusIcon = '';
                    switch (response.status) {
                        case 'draft':
                            statusColor = '#94A3B8';
                            statusIcon = 'fa-pen-fancy';
                            break;
                        case 'active':
                            statusColor = '#10B981';
                            statusIcon = 'fa-play-circle';
                            break;
                        case 'marks_entry':
                            statusColor = '#F59E0B';
                            statusIcon = 'fa-edit';
                            break;
                        case 'closed':
                            statusColor = '#EF4444';
                            statusIcon = 'fa-lock';
                            break;
                        case 'results_released':
                            statusColor = '#5351e4';
                            statusIcon = 'fa-trophy';
                            break;
                        default:
                            statusColor = '#5351e4';
                            statusIcon = 'fa-clipboard';
                    }

                    // Generate action buttons HTML based on status
                    let actionButtonsHTML = '';

                    // Status control buttons
                    if (response.status === 'draft') {
                        actionButtonsHTML += `
                                <button class="action-btn-status" data-status="active" style="background: #10B981; color: white; border: none; padding: 10px 20px; border-radius: 12px; font-weight: 600; margin: 5px; cursor: pointer; transition: all 0.3s ease;">
                                    <i class="fas fa-play me-2"></i> Activate Examination
                                </button>
                                <button class="action-btn-delete" style="background: #EF4444; color: white; border: none; padding: 10px 20px; border-radius: 12px; font-weight: 600; margin: 5px; cursor: pointer; transition: all 0.3s ease;">
                                    <i class="fas fa-trash-alt me-2"></i>Delete Examination
                                </button>
                            `;
                    } else if (response.status === 'active') {
                        actionButtonsHTML += `
                                <button class="action-btn-status" data-status="marks_entry" style="background: #F59E0B; color: white; border: none; padding: 10px 20px; border-radius: 12px; font-weight: 600; margin: 5px; cursor: pointer; transition: all 0.3s ease;">
                                    <i class="fas fa-edit me-2"></i> Open Marks Entry
                                </button>
            <button class="action-btn-close" style="background: #EF4444; color: white; border: none; padding: 10px 20px; border-radius: 12px; font-weight: 600; margin: 5px; cursor: pointer; transition: all 0.3s ease;">
                <i class="fas fa-times me-2"></i> Close
            </button>
                            `;
                    } else if (response.status === 'marks_entry') {
                        actionButtonsHTML += `
                                <button class="action-btn-status" data-status="closed" style="background: #EF4444; color: white; border: none; padding: 10px 20px; border-radius: 12px; font-weight: 600; margin: 5px; cursor: pointer; transition: all 0.3s ease;">
                                    <i class="fas fa-lock me-2"></i> Close Examination
                                </button>
                                <button class="action-btn-marks" style="background: #5351e4; color: white; border: none; padding: 10px 20px; border-radius: 12px; font-weight: 600; margin: 5px; cursor: pointer; transition: all 0.3s ease;">
                                    <i class="fas fa-table me-2"></i> Enter Marks
                                </button>
                            `;
                    } else if (response.status === 'closed') {
                        actionButtonsHTML += `
                                <button class="action-btn-status" data-status="results_released" style="background: #5351e4; color: white; border: none; padding: 10px 20px; border-radius: 12px; font-weight: 600; margin: 5px; cursor: pointer; transition: all 0.3s ease;">
                                    <i class="fas fa-award me-2"></i>Release Results
                                </button>
                            `;
                    } else if (response.status === 'results_released') {
                        actionButtonsHTML += `
                                <button disabled style="background: #94A3B8; color: white; border: none; padding: 10px 20px; border-radius: 12px; font-weight: 600; margin: 5px; cursor: not-allowed;">
                                    <i class="fas fa-check-circle me-2"></i>Completed
                                </button>
                            `;
                    }

                    Swal.fire({
                        title: '<span style="font-size: 1.8rem; font-weight: 800;">' + response.exam_name + '</span>',
                        html: `
                                <div style="text-align: left; margin-top: 10px;">
                                    <!-- Header Badge -->
                                    <div style="text-align: center; margin-bottom: 20px;">
                                        <span style="display: inline-flex; align-items: center; gap: 8px; background: ${statusColor}15; color: ${statusColor}; padding: 6px 16px; border-radius: 99px; font-size: 0.85rem; font-weight: 600;">
                                            <i class="fas ${statusIcon}"></i>
                                            ${response.status_label}
                                        </span>
                                    </div>

                                    <!-- Exam Code Card -->
                                    <div style="background: linear-gradient(135deg, #5351e4 0%, #2C29CA 100%); border-radius: 16px; padding: 16px; margin-bottom: 20px; text-align: center;">
                                        <div style="color: rgba(255,255,255,0.7); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 5px;">Exam Code</div>
                                        <div style="color: white; font-size: 1.5rem; font-weight: 700; font-family: monospace;">${response.exam_code}</div>
                                    </div>

                                    <!-- Details Grid -->
                                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 20px;">
                                        <div style="background: #F8FAFC; border-radius: 12px; padding: 12px;">
                                            <div style="color: #94A3B8; font-size: 0.7rem; text-transform: uppercase; margin-bottom: 4px;">
                                                <i class="fas fa-layer-group me-1"></i> Type
                                            </div>
                                            <div style="font-weight: 600; color: #1E293B;">${response.exam_type}</div>
                                        </div>
                                        <div style="background: #F8FAFC; border-radius: 12px; padding: 12px;">
                                            <div style="color: #94A3B8; font-size: 0.7rem; text-transform: uppercase; margin-bottom: 4px;">
                                                <i class="fas fa-calendar-alt me-1"></i> Term
                                            </div>
                                            <div style="font-weight: 600; color: #1E293B;">${response.term}</div>
                                        </div>
                                    </div>

                                    <!-- Period Section -->
                                    <div style="background: #F8FAFC; border-radius: 12px; padding: 14px; margin-bottom: 12px;">
                                        <div style="color: #5351e4; font-size: 0.75rem; font-weight: 600; margin-bottom: 8px;">
                                            <i class="fas fa-calendar-week me-1"></i> Examination Period
                                        </div>
                                        <div style="display: flex; justify-content: space-between; align-items: center;">
                                            <div>
                                                <div style="font-size: 0.7rem; color: #94A3B8;">Start Date</div>
                                                <div style="font-weight: 600; color: #1E293B;">${response.start_date}</div>
                                            </div>
                                            <i class="fas fa-arrow-right" style="color: #94A3B8;"></i>
                                            <div>
                                                <div style="font-size: 0.7rem; color: #94A3B8;">End Date</div>
                                                <div style="font-weight: 600; color: #1E293B;">${response.end_date}</div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Deadline Section -->
                                    <div style="background: ${response.days_until_deadline <= 3 ? '#FEF3C7' : '#F8FAFC'}; border-radius: 12px; padding: 14px; margin-bottom: 12px;">
                                        <div style="color: ${response.days_until_deadline <= 3 ? '#D97706' : '#5351e4'}; font-size: 0.75rem; font-weight: 600; margin-bottom: 8px;">
                                            <i class="fas fa-hourglass-half me-1"></i> Marks Entry Deadline
                                        </div>
                                        <div style="display: flex; justify-content: space-between; align-items: center;">
                                            <div style="font-weight: 600; color: #1E293B;">${response.marks_entry_deadline}</div>
                                            ${response.days_until_deadline > 0 ?
                                `<span style="background: ${response.days_until_deadline <= 3 ? '#FEF3C7' : '#D1FAE5'}; color: ${response.days_until_deadline <= 3 ? '#D97706' : '#059669'}; padding: 4px 10px; border-radius: 99px; font-size: 0.7rem; font-weight: 600;">
                                                    <i class="fas fa-clock me-1"></i>${response.days_until_deadline} days left
                                                </span>` :
                                `<span style="background: #FEE2E2; color: #DC2626; padding: 4px 10px; border-radius: 99px; font-size: 0.7rem; font-weight: 600;">
                                                    <i class="fas fa-ban me-1"></i>Expired
                                                </span>`
                            }
                                        </div>
                                    </div>

                                    ${response.description ? `
                                    <div style="background: #F8FAFC; border-radius: 12px; padding: 14px; margin-bottom: 12px;">
                                        <div style="color: #5351e4; font-size: 0.75rem; font-weight: 600; margin-bottom: 8px;">
                                            <i class="fas fa-align-left me-1"></i> Description
                                        </div>
                                        <div style="font-size: 0.85rem; color: #475569; line-height: 1.5;">${response.description}</div>
                                    </div>
                                    ` : ''}

                                    <!-- Action Buttons Section -->
                                    <div style="display: flex; flex-wrap: wrap; gap: 10px; justify-content: center; margin-top: 20px; padding-top: 15px; border-top: 2px solid #E2E8F0;">
                                        ${actionButtonsHTML}
                                    </div>
                                </div>
                            `,
                        icon: 'info',
                        showCloseButton: true,
                        showConfirmButton: false,
                        showCancelButton: false,
                        width: '600px',
                        padding: '1.5rem',
                        backdrop: 'rgba(0,0,0,0.6)',
                        didOpen: () => {
                            // Attach event handlers after modal is opened
                            // Status change buttons
                            document.querySelectorAll('.action-btn-status').forEach(btn => {
                                btn.addEventListener('click', function (e) {
                                    e.preventDefault();
                                    e.stopPropagation();
                                    const newStatus = this.dataset.status;
                                    Swal.close();
                                    updateExamStatus(examId, newStatus);
                                });
                            });

                            // Delete button
                            document.querySelectorAll('.action-btn-delete').forEach(btn => {
                                btn.addEventListener('click', function (e) {
                                    e.preventDefault();
                                    e.stopPropagation();
                                    Swal.close();
                                    deleteExam(examId);
                                });
                            });

                            // Marks entry button
                            document.querySelectorAll('.action-btn-marks').forEach(btn => {
                                btn.addEventListener('click', function (e) {
                                    e.preventDefault();
                                    e.stopPropagation();
                                    if (response.status === 'active' || response.status === 'marks_entry') {
                                        window.location.href = `/examinations/${examId}/marks`;
                                    }
                                });
                            });

                            // Close button
                            document.querySelectorAll('.action-btn-close').forEach(btn => {
                                btn.addEventListener('click', function (e) {
                                    e.preventDefault();
                                    e.stopPropagation();
                                    Swal.close();
                                });
                            });
                        }
                    });
                },
                error: function (xhr) {
                    Swal.fire({
                        title: '<span style="color: #EF4444;">Error Loading Details</span>',
                        html: `
                                <div style="text-align: center; padding: 20px;">
                                    <i class="fas fa-exclamation-triangle" style="font-size: 3rem; color: #EF4444; margin-bottom: 15px;"></i>
                                    <p style="color: #475569;">Unable to load examination details. Please try again later.</p>
                                    <p style="font-size: 0.75rem; color: #94A3B8; margin-top: 10px;">Error: ${xhr.status} - ${xhr.statusText}</p>
                                </div>
                            `,
                        icon: 'error',
                        confirmButtonColor: '#5351e4',
                        confirmButtonText: 'OK',
                        showCloseButton: true
                    });
                }
            });
        }

        function updateExamStatus(examId, newStatus) {
            const configs = {
                active: { title: 'Activate', text: 'Make this examination live?', icon: 'question' },
                marks_entry: { title: 'Open Marks Entry', text: 'Allow teachers to enter marks?', icon: 'info' },
                closed: { title: 'Close', text: 'Close this examination permanently?', icon: 'warning' },
                results_released: { title: 'Release Results', text: 'Release results to students?', icon: 'success' }
            };

            const config = configs[newStatus];

            Swal.fire({
                title: config.title,
                text: config.text,
                icon: config.icon,
                showCancelButton: true,
                confirmButtonColor: '#5351e4',
                confirmButtonText: `Yes, ${config.title}`,
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (!result.isConfirmed) return;

                Swal.fire({
                    title: 'Processing...',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                $.ajax({
                    url: `/examinations/${examId}/status`,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        status: newStatus
                    },
                    success: function (res) {
                        if (res.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Updated!',
                                text: res.message,
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => location.reload());
                        } else {
                            Swal.fire('Error', res.message, 'error');
                        }
                    },
                    // error: function () {
                    //     Swal.fire('Error', 'Failed to update status', 'error');
                    // }
                    error: function (data) {
                        $('body').html(data.responseText);
                    }

                });
            });
        }

        function deleteExam(examId) {
            Swal.fire({
                title: 'Delete Examination?',
                text: 'This action cannot be undone!',
                icon: 'error',
                showCancelButton: true,
                confirmButtonColor: '#EF4444',
                confirmButtonText: 'Yes, Delete',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (!result.isConfirmed) return;

                Swal.fire({
                    title: 'Deleting...',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                $.ajax({
                    url: `/examinations/${examId}`,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        _method: 'DELETE'
                    },
                    success: function (res) {
                        if (res.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: res.message,
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => location.reload());
                        } else {
                            Swal.fire('Error', res.message, 'error');
                        }
                    },
                    error: function () {
                        Swal.fire('Error', 'Failed to delete examination', 'error');
                    }
                });
            });
        }

        function filterByStatus(status) {
            currentFilter = status;
            const columns = document.querySelectorAll('.kanban-column');

            columns.forEach(column => {
                if (status === 'all' || column.dataset.status === status) {
                    column.style.display = 'block';
                } else {
                    column.style.display = 'none';
                }
            });
        }

        // Initialize Sortable for drag-and-drop
        document.addEventListener('DOMContentLoaded', function () {
            const columns = document.querySelectorAll('.kanban-column');

            columns.forEach(column => {
                new Sortable(column.querySelector('.column-cards'), {
                    group: 'shared',
                    animation: 150,
                    ghostClass: 'exam-card-ghost',
                    onEnd: function (evt) {
                        const examId = evt.item.dataset.id;
                        const newStatus = evt.to.closest('.kanban-column').dataset.status;

                        // Update status via AJAX
                        $.ajax({
                            url: `/examinations/${examId}/status`,
                            type: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                status: newStatus
                            },
                            success: function (res) {
                                if (res.success) {
                                    // Update counts
                                    updateColumnCounts();
                                }
                            }
                        });
                    }
                });
            });
        });

        function updateColumnCounts() {
            const columns = document.querySelectorAll('.kanban-column');
            columns.forEach(column => {
                const count = column.querySelectorAll('.exam-card').length;
                const countSpan = column.querySelector('.column-count');
                if (countSpan) countSpan.textContent = count;
            });
        }

        // Real-time clock update for deadlines
        function updateDeadlines() {
            document.querySelectorAll('.deadline-timer').forEach(timer => {
                const deadline = new Date(timer.dataset.deadline);
                const now = new Date();
                const diff = deadline - now;
                const days = Math.ceil(diff / (1000 * 60 * 60 * 24));

                if (days > 0) {
                    timer.innerHTML = `<i class="fas fa-clock me-1"></i>${days} day${days !== 1 ? 's' : ''} left`;
                    timer.className = 'deadline-timer timer-normal';
                } else if (days === 0) {
                    timer.innerHTML = `<i class="fas fa-hourglass-end me-1"></i>Due today`;
                    timer.className = 'deadline-timer timer-urgent';
                } else {
                    timer.innerHTML = `<i class="fas fa-ban me-1"></i>Expired`;
                    timer.className = 'deadline-timer timer-expired';
                }
            });
        }

        setInterval(updateDeadlines, 60000);
        updateDeadlines();
    </script>

    <!-- Create the exam-card partial -->
@endsection

@section('js')
    <script src="{{ URL::asset('assets/plugins/datatable/js/jquery.dataTables.js') }}"></script>
    <script src="{{ URL::asset('assets/plugins/datatable/js/dataTables.bootstrap4.js') }}"></script>
    <script src="{{ URL::asset('assets/js/datatables.js') }}"></script>
@endsection