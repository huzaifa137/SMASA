@extends('layouts-side-bar.master')

@section('css')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    :root {
        --brand: #5351e4;
        --brand-light: #2C29CA;
        --brand-dark: #2C29CA;
        --brand-muted: rgba(83, 81, 228, 0.08);
        --success: #10b981;
        --success-muted: rgba(16, 185, 129, 0.1);
        --warning: #f59e0b;
        --warning-muted: rgba(245, 158, 11, 0.1);
        --danger: #ef4444;
        --danger-muted: rgba(239, 68, 68, 0.1);
        --info: #3b82f6;
        --info-muted: rgba(59, 130, 246, 0.1);
        --text-primary: #1e293b;
        --text-secondary: #475569;
        --text-muted: #94a3b8;
        --border-light: #e2e8f0;
        --bg-surface: #f8fafc;
    }

    body {
        background: #f1f5f9;
    }

    /* Modern Glass Header */
    .hero-section {
        background: linear-gradient(135deg, #5351e4 0%, #2C29CA 100%);
        border-radius: 28px;
        padding: 2rem 2rem;
        margin-bottom: 2rem;
        position: relative;
        overflow: hidden;
        box-shadow: 0 20px 35px -12px rgba(83, 81, 228, 0.3);
    }

    .hero-section::before {
        content: '';
        position: absolute;
        top: -30%;
        right: -10%;
        width: 250px;
        height: 250px;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
        border-radius: 50%;
    }

    .hero-section::after {
        content: '';
        position: absolute;
        bottom: -20%;
        left: -5%;
        width: 200px;
        height: 200px;
        background: radial-gradient(circle, rgba(108, 63, 197, 0.15) 0%, transparent 70%);
        border-radius: 50%;
    }

    /* Class Grid */
    .class-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
        gap: 1.75rem;
    }

    /* Modern Card Design */
    .modern-card {
        background: white;
        border-radius: 24px;
        overflow: hidden;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
        border: 1px solid rgba(83, 81, 228, 0.08);
    }

    .modern-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 20px 25px -12px rgba(83, 81, 228, 0.15), 0 8px 10px -6px rgba(0, 0, 0, 0.02);
        border-color: rgba(83, 81, 228, 0.2);
    }

    /* Card Header */
    .card-header-modern {
        background: linear-gradient(135deg, #5351e4 0%, #2C29CA 100%);
        padding: 1.25rem 1.5rem;
        position: relative;
        overflow: hidden;
    }

    .card-header-modern::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 150px;
        height: 150px;
        background: radial-gradient(circle, rgba(255,255,255,0.08) 0%, transparent 70%);
        border-radius: 50%;
    }

    .class-title {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 0.75rem;
    }

    .class-name {
        font-size: 1.2rem;
        font-weight: 700;
        color: white;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .stream-count {
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(4px);
        padding: 0.25rem 0.75rem;
        border-radius: 99px;
        font-size: 0.7rem;
        font-weight: 600;
        color: white;
    }

    /* Stream Items - Modern Design */
    .stream-list {
        padding: 0.5rem;
    }

    .stream-card {
        background: var(--bg-surface);
        border-radius: 18px;
        padding: 1rem;
        margin-bottom: 0.5rem;
        transition: all 0.2s ease;
        border: 1px solid transparent;
    }

    .stream-card:hover {
        background: white;
        border-color: var(--brand-muted);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
    }

    .stream-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        margin-bottom: 0.75rem;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .stream-info {
        flex: 1;
    }

    .stream-name {
        font-size: 1rem;
        font-weight: 700;
        color: var(--text-primary);
        display: flex;
        align-items: center;
        gap: 0.5rem;
        flex-wrap: wrap;
        margin-bottom: 0.25rem;
    }

    .stream-badge {
        font-size: 0.65rem;
        font-weight: 600;
        padding: 0.2rem 0.6rem;
        border-radius: 99px;
    }

    .badge-success {
        background: var(--success-muted);
        color: var(--success);
    }

    .badge-pending {
        background: var(--warning-muted);
        color: var(--warning);
    }

    .stream-stats {
        display: flex;
        align-items: center;
        gap: 1rem;
        flex-wrap: wrap;
        margin-top: 0.5rem;
    }

    .stat-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        font-size: 0.75rem;
        color: var(--text-secondary);
    }

    .stat-chip i {
        font-size: 0.7rem;
        color: var(--brand);
    }

    /* Progress Ring */
    .progress-ring-container {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .progress-circle {
        width: 48px;
        height: 48px;
    }

    .progress-text {
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--text-primary);
    }

    .progress-label {
        font-size: 0.65rem;
        color: var(--text-muted);
    }

    /* Modern Progress Bar */
    .progress-bar-modern {
        background: #e2e8f0;
        border-radius: 99px;
        height: 6px;
        overflow: hidden;
        flex: 1;
    }

    .progress-fill {
        height: 100%;
        border-radius: 99px;
        transition: width 0.5s ease;
    }

    .fill-success { background: linear-gradient(90deg, #10b981, #059669); }
    .fill-warning { background: linear-gradient(90deg, #f59e0b, #d97706); }
    .fill-danger { background: linear-gradient(90deg, #ef4444, #dc2626); }

    /* Action Buttons */
    .action-group {
        display: flex;
        gap: 0.5rem;
        margin-top: 0.75rem;
        padding-top: 0.75rem;
        border-top: 1px solid var(--border-light);
        flex-wrap: wrap;
    }

    .btn-primary-modern {
        background: linear-gradient(135deg, #5351e4, #2C29CA);
        color: white;
        border: none;
        border-radius: 12px;
        padding: 0.5rem 1rem;
        font-size: 0.75rem;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.2s ease;
    }

    .btn-primary-modern:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 14px rgba(83, 81, 228, 0.3);
        color: white;
    }

    .btn-outline-modern {
        background: white;
        border: 1.5px solid var(--brand-muted);
        color: var(--brand);
        border-radius: 12px;
        padding: 0.5rem 1rem;
        font-size: 0.75rem;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.2s ease;
    }

    .btn-outline-modern:hover {
        background: var(--brand-muted);
        border-color: var(--brand);
        transform: translateY(-1px);
    }

    .btn-edit-modern {
        background: var(--success-muted);
        color: var(--success);
        border: none;
        border-radius: 10px;
        padding: 0.4rem 0.9rem;
        font-size: 0.7rem;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        transition: all 0.2s ease;
    }

    .btn-edit-modern:hover {
        background: var(--success);
        color: white;
    }

    /* Subject Tags */
    .subject-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-top: 0.75rem;
    }

    .subject-tag {
        background: var(--info-muted);
        color: var(--info);
        border-radius: 10px;
        padding: 0.3rem 0.8rem;
        font-size: 0.7rem;
        font-weight: 500;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        transition: all 0.2s ease;
    }

    .subject-tag:hover {
        background: var(--info);
        color: white;
        transform: translateY(-1px);
    }

    /* Empty State */
    .empty-state-modern {
        text-align: center;
        padding: 4rem 2rem;
        background: white;
        border-radius: 28px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
    }

    .empty-state-modern i {
        font-size: 4rem;
        color: var(--brand);
        opacity: 0.3;
        margin-bottom: 1rem;
        display: block;
    }

    .empty-state-modern h4 {
        font-size: 1.2rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 0.5rem;
    }

    .empty-state-modern p {
        color: var(--text-muted);
        font-size: 0.85rem;
    }

    /* Header Buttons */
    .header-buttons {
        display: flex;
        gap: 0.75rem;
        flex-wrap: wrap;
    }

    .btn-glass {
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(8px);
        color: white;
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 14px;
        padding: 0.5rem 1.2rem;
        font-size: 0.85rem;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.2s ease;
    }

    .btn-glass:hover {
        background: white;
        color: var(--brand);
        border-color: white;
    }

    .btn-glass-white {
        background: white;
        color: var(--brand);
        border: none;
    }

    .btn-glass-white:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 14px rgba(0, 0, 0, 0.15);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .class-grid {
            grid-template-columns: 1fr;
        }
        
        .hero-section {
            padding: 1.5rem;
        }
        
        .class-title {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .stream-header {
            flex-direction: column;
        }
    }
</style>
@endsection

@section('content')
<div class="side-app" style="padding: 1.5rem;">

    {{-- Modern Hero Section --}}
    <div class="hero-section">
        <div class="row align-items-center">
            <div class="col-lg-7">
                <div class="mb-2">
                    <span class="badge" style="background: #FFF; backdrop-filter: blur(4px); padding: 0.4rem 1rem; border-radius: 99px; font-size: 0.75rem;">
                        <i class="fas fa-calendar-alt me-2"></i> {{ \Carbon\Carbon::today()->format('l, F j, Y') }}
                    </span>
                </div>
                <h1 style="font-size: 2rem; font-weight: 800; color: white; margin-bottom: 0.5rem;">
                    <i class="fas fa-user-graduate me-3"></i> Student Attendance
                </h1>
                <p style="font-size: 0.95rem; color: rgba(255,255,255,0.85); margin-bottom: 0;">
                    Select a class to mark attendance or review today's records
                </p>
            </div>
            <div class="col-lg-5 text-lg-end mt-3 mt-lg-0">
                <div class="header-buttons">
                    <a href="{{ route('attendance.dashboard') }}" class="btn-glass">
                        <i class="fas fa-chart-line"></i> Dashboard
                    </a>
                    <a href="{{ route('attendance.students.report') }}" class="btn-glass btn-glass-white">
                        <i class="fas fa-chart-bar"></i> Reports
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if($classrooms->isEmpty())
    <div class="empty-state-modern">
        <i class="fas fa-school-circle-exclamation"></i>
        <h4>No Classes Assigned</h4>
        <p>You don't have any classes assigned yet. Please contact your administrator.</p>
    </div>
    @else
    <div class="class-grid">
        @foreach($classrooms as $classroom)
        <div class="modern-card">
            <div class="card-header-modern">
                <div class="class-title">
                    <div class="class-name">
                        <i class="fas fa-building" style="opacity: 0.9;"></i>
                        {{ $classroom->class_display }}
                    </div>
                    <span class="stream-count">
                        <i class="fas fa-layer-group me-1"></i> {{ $classroom->streams->count() }} Streams
                    </span>
                </div>
            </div>

            <div class="stream-list">
                @foreach($classroom->streams as $stream)
                @php 
                    $rate = $stream->total > 0 ? round($stream->present_count / $stream->total * 100) : 0;
                    $rateClass = $rate >= 80 ? 'fill-success' : ($rate >= 60 ? 'fill-warning' : 'fill-danger');
                    $rateColor = $rate >= 80 ? '#10b981' : ($rate >= 60 ? '#f59e0b' : '#ef4444');
                @endphp
                <div class="stream-card">
                    <div class="stream-header">
                        <div class="stream-info">
                            <div class="stream-name">
                                <i class="fas fa-graduation-cap" style="color: var(--brand); font-size: 0.85rem;"></i>
                                {{ $stream->stream_name ?? $stream->stream_id }}
                                @if($stream->taken)
                                    <span class="stream-badge badge-success">
                                        <i class="fas fa-check-circle"></i> {{ $rate }}% Complete
                                    </span>
                                @else
                                    <span class="stream-badge badge-pending">
                                        <i class="fas fa-clock"></i> Pending
                                    </span>
                                @endif
                            </div>
                            
                            <div class="stream-stats">
                                <span class="stat-chip">
                                    <i class="fas fa-users"></i> {{ $stream->total }} Students
                                </span>
                                @if($stream->taken)
                                    <span class="stat-chip">
                                        <i class="fas fa-user-check" style="color: var(--success);"></i> 
                                        <strong style="color: var(--success);">{{ $stream->present_count }}</strong> Present
                                    </span>
                                    <div class="progress-ring-container">
                                        <div class="progress-bar-modern">
                                            <div class="progress-fill {{ $rateClass }}" style="width: {{ $rate }}%;"></div>
                                        </div>
                                        <span class="progress-text" style="color: {{ $rateColor }};">{{ $rate }}%</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="action-group">
                        @if($stream->taken)
                            <a href="{{ route('attendance.take', [$classroom->class_name, $stream->stream_id]) }}?date={{ $today }}" 
                               class="btn-edit-modern">
                                <i class="fas fa-edit"></i> Edit Attendance
                            </a>
                            <a href="{{ route('attendance.take', [$classroom->class_name, $stream->stream_id]) }}?view=details&date={{ $today }}" 
                               class="btn-outline-modern">
                                <i class="fas fa-eye"></i> View Details
                            </a>
                        @else
                            <a href="{{ route('attendance.take', [$classroom->class_name, $stream->stream_id]) }}" 
                               class="btn-primary-modern">
                                <i class="fas fa-plus-circle"></i> Take Attendance
                            </a>
                        @endif
                    </div>

                    {{-- Subject-specific attendance links --}}
                    @if($stream->subjects && $stream->subjects->isNotEmpty())
                    <div class="subject-tags">
                        <span class="stat-chip" style="font-size: 0.7rem;">
                            <i class="fas fa-book"></i> By Subject:
                        </span>
                        @foreach($stream->subjects as $subj)
                        <a href="{{ route('attendance.take', [$classroom->class_name, $stream->stream_id]) }}?class_subject_id={{ $subj->id }}&date={{ $today }}"
                           class="subject-tag" 
                           title="Take attendance for {{ $subj->subject_name }}">
                            <i class="fas fa-book-open"></i> {{ Str::limit($subj->subject_name, 20) }}
                        </a>
                        @endforeach
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>
</div>
</div>
@endsection


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    // Animate progress bars on load
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.progress-fill').forEach(bar => {
            const width = bar.style.width;
            bar.style.width = '0%';
            setTimeout(() => {
                bar.style.width = width;
            }, 100);
        });
    });
</script>
