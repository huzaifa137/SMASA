<?php
use App\Http\Controllers\Helper;
?>
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
        --purple: #8b5cf6;
        --purple-muted: rgba(139, 92, 246, 0.1);
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
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
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

    /* Stats Row */
    .stats-row {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1rem;
        margin-bottom: 1.75rem;
    }

    .stat-card-premium {
        background: white;
        border-radius: 20px;
        padding: 1.25rem;
        transition: all 0.3s ease;
        border: 1px solid rgba(83, 81, 228, 0.08);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
        position: relative;
        overflow: hidden;
    }

    .stat-card-premium:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 24px rgba(83, 81, 228, 0.1);
        border-color: rgba(83, 81, 228, 0.15);
    }

    .stat-icon-wrapper {
        width: 48px;
        height: 48px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 0.75rem;
    }

    .stat-value {
        font-size: 1.8rem;
        font-weight: 800;
        line-height: 1.2;
    }

    .stat-label {
        font-size: 0.7rem;
        color: var(--text-muted);
        margin-top: 0.25rem;
        font-weight: 500;
    }

    /* Content Grid */
    .content-grid {
        display: grid;
        grid-template-columns: 1.6fr 1fr;
        gap: 1.5rem;
    }

    /* Cards */
    .data-card {
        background: white;
        border-radius: 24px;
        margin-bottom: 1.5rem;
        border: 1px solid rgba(83, 81, 228, 0.08);
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
    }

    .card-header-modern {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid var(--border-light);
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 1rem;
        background: var(--bg-surface);
    }

    .card-title {
        font-size: 0.9rem;
        font-weight: 700;
        color: var(--text-primary);
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin: 0;
    }

    .count-badge {
        background: var(--brand-muted);
        color: var(--brand);
        border-radius: 99px;
        padding: 0.2rem 0.7rem;
        font-size: 0.7rem;
        font-weight: 600;
    }

    /* Timetable Item */
    .tt-item {
        display: flex;
        align-items: center;
        padding: 1rem 1.5rem;
        border-bottom: 1px solid var(--border-light);
        gap: 1rem;
        transition: all 0.2s ease;
    }

    .tt-item:hover {
        background: var(--bg-surface);
    }

    .tt-item:last-child {
        border-bottom: none;
    }

    .tt-badge {
        width: 48px;
        height: 48px;
        border-radius: 16px;
        background: linear-gradient(135deg, var(--brand), var(--brand-light));
        color: white;
        font-size: 1rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        box-shadow: 0 4px 10px rgba(83, 81, 228, 0.15);
    }

    .tt-badge-draft {
        background: linear-gradient(135deg, var(--warning), #d97706);
    }

    .tt-info {
        flex: 1;
    }

    .tt-name {
        font-size: 0.9rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 0.25rem;
    }

    .tt-meta {
        font-size: 0.7rem;
        color: var(--text-muted);
        display: flex;
        align-items: center;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        padding: 0.25rem 0.7rem;
        border-radius: 99px;
        font-size: 0.65rem;
        font-weight: 600;
    }

    .status-active {
        background: var(--success-muted);
        color: var(--success);
    }

    .status-draft {
        background: var(--warning-muted);
        color: var(--warning);
    }

    .tt-actions {
        display: flex;
        gap: 0.5rem;
    }

    .btn-icon {
        width: 32px;
        height: 32px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
        text-decoration: none;
    }

    .btn-icon-view {
        background: var(--info-muted);
        color: var(--info);
    }
    .btn-icon-view:hover {
        background: var(--info);
        color: white;
    }

    .btn-icon-edit {
        background: var(--brand-muted);
        color: var(--brand);
    }
    .btn-icon-edit:hover {
        background: var(--brand);
        color: white;
    }

    .btn-icon-activate {
        background: var(--success-muted);
        color: var(--success);
    }
    .btn-icon-activate:hover {
        background: var(--success);
        color: white;
    }

    .btn-icon-delete {
        background: var(--danger-muted);
        color: var(--danger);
    }
    .btn-icon-delete:hover {
        background: var(--danger);
        color: white;
    }

    /* Today's Schedule */
    .today-slot {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 0.9rem 1.5rem;
        border-bottom: 1px solid var(--border-light);
        transition: all 0.2s ease;
    }

    .today-slot:hover {
        background: var(--bg-surface);
    }

    .slot-time {
        min-width: 70px;
        font-size: 0.75rem;
        font-weight: 700;
        color: var(--brand);
    }

    .slot-color {
        width: 4px;
        height: 40px;
        border-radius: 4px;
        flex-shrink: 0;
    }

    .slot-info {
        flex: 1;
    }

    .slot-subject {
        font-size: 0.85rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 0.15rem;
    }

    .slot-class {
        font-size: 0.7rem;
        color: var(--text-muted);
    }

    .slot-room {
        font-size: 0.65rem;
        color: var(--text-muted);
        margin-top: 0.15rem;
    }

    /* Period Item */
    .period-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.75rem 1.5rem;
        border-bottom: 1px solid var(--border-light);
        transition: all 0.2s ease;
    }

    .period-item:hover {
        background: var(--bg-surface);
    }

    .period-name {
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--text-primary);
    }

    .period-type {
        font-size: 0.6rem;
        background: var(--bg-surface);
        color: var(--text-muted);
        border-radius: 99px;
        padding: 0.2rem 0.6rem;
        margin-left: 0.5rem;
    }

    .period-time {
        font-size: 0.7rem;
        color: var(--text-muted);
    }

    /* Buttons */
    .btn-primary-premium {
        background: linear-gradient(135deg, var(--brand), var(--brand-light));
        color: white;
        border: none;
        border-radius: 14px;
        padding: 0.6rem 1.5rem;
        font-size: 0.85rem;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.2s ease;
    }

    .btn-primary-premium:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 14px rgba(83, 81, 228, 0.3);
        color: white;
    }

    .btn-outline-premium {
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(8px);
        color: white;
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 14px;
        padding: 0.5rem 1.2rem;
        font-size: 0.8rem;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.2s ease;
    }

    .btn-outline-premium:hover {
        background: white;
        color: var(--brand);
        border-color: white;
    }

    .empty-state {
        text-align: center;
        padding: 3rem 1.5rem;
        color: var(--text-muted);
    }

    .empty-state i {
        font-size: 3rem;
        opacity: 0.3;
        margin-bottom: 0.75rem;
        display: block;
    }

    /* Responsive */
    @media (max-width: 1024px) {
        .stats-row {
            grid-template-columns: repeat(2, 1fr);
        }
        .content-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .glass-header {
            padding: 1.25rem;
        }
        .tt-item {
            flex-wrap: wrap;
        }
        .tt-actions {
            width: 100%;
            justify-content: flex-end;
        }
    }
</style>
@endsection

@section('content')
<div class="side-app" style="padding: 1.5rem;">

{{-- Modern Glass Header --}}
<div class="glass-header">
    <div class="row align-items-center">
        <div class="col-lg-7">
            <div class="mb-4">
                <span class="badge" style="background: rgba(255,255,255,0.2); backdrop-filter: blur(4px); padding: 0.5rem 1rem; border-radius: 99px; font-size: 1rem; color: #FFF; display: inline-block;">
                    <i class="fas fa-calendar-alt me-2"></i> Academic Scheduling
                </span>
            </div>
            <h1 style="font-size: 2rem; font-weight: 800; color: white; margin-bottom: 0.5rem;">
                <i class="fas fa-calendar-week me-3"></i> Timetable Manager
            </h1>
            <p style="font-size: 0.95rem; color: rgba(255,255,255,0.85); margin-bottom: 0;">
                Create, manage, and publish class timetables for your school
            </p>
        </div>
        <div class="col-lg-5 text-lg-end mt-3 mt-lg-0">
            <div class="d-flex gap-3 justify-content-lg-end" style="gap: 1rem;">
                <a href="{{ route('timetable.create') }}" class="btn-primary-premium"
                   style="background: white; color: var(--brand); border-radius: 8px; padding: 0.6rem 1.5rem; font-size: 1rem; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem; transition: all 0.3s ease;">
                    <i class="fas fa-plus-circle"></i> New Timetable
                </a>
                <a href="{{ route('timetable.periods.index') }}" class="btn-outline-premium"
                   style="background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.3); color: white; border-radius: 8px; padding: 0.6rem 1.5rem; font-size: 1rem; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem; transition: all 0.3s ease;">
                    <i class="fas fa-clock"></i> Periods
                </a>
                <a href="{{ route('timetable.teacher') }}" class="btn-outline-premium"
                   style="background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.3); color: white; border-radius: 8px; padding: 0.6rem 1.5rem; font-size: 1rem; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem; transition: all 0.3s ease;">
                    <i class="fas fa-chalkboard-user"></i> My Schedule
                </a>
            </div>
        </div>
    </div>
</div>

    {{-- Stats Row --}}
    <div class="stats-row">
        <div class="stat-card-premium">
            <div class="stat-icon-wrapper" style="background: var(--brand-muted);">
                <i class="fas fa-calendar-alt" style="color: var(--brand); font-size: 1.2rem;"></i>
            </div>
            <div class="stat-value" style="color: var(--text-primary);">{{ $allTimetables->count() }}</div>
            <div class="stat-label">Total Timetables</div>
        </div>
        <div class="stat-card-premium">
            <div class="stat-icon-wrapper" style="background: var(--success-muted);">
                <i class="fas fa-check-circle" style="color: var(--success); font-size: 1.2rem;"></i>
            </div>
            <div class="stat-value" style="color: var(--success);">{{ $activeTimetables->count() }}</div>
            <div class="stat-label">Active Timetables</div>
        </div>
        <div class="stat-card-premium">
            <div class="stat-icon-wrapper" style="background: var(--warning-muted);">
                <i class="fas fa-pen-fancy" style="color: var(--warning); font-size: 1.2rem;"></i>
            </div>
            <div class="stat-value" style="color: var(--warning);">{{ $draftTimetables->count() }}</div>
            <div class="stat-label">In Draft</div>
        </div>
        <div class="stat-card-premium">
            <div class="stat-icon-wrapper" style="background: var(--info-muted);">
                <i class="fas fa-hourglass-half" style="color: var(--info); font-size: 1.2rem;"></i>
            </div>
            <div class="stat-value" style="color: var(--info);">{{ $periods->count() }}</div>
            <div class="stat-label">Period Definitions</div>
        </div>
    </div>

    <div class="content-grid">
        {{-- Left Column: Timetables --}}
        <div>
            {{-- Active Timetables --}}
            <div class="data-card">
                <div class="card-header-modern">
                    <div class="card-title">
                        <i class="fas fa-check-circle" style="color: var(--success);"></i>
                        Active Timetables
                    </div>
                    <span class="count-badge">{{ $activeTimetables->count() }}</span>
                </div>
                @forelse($activeTimetables as $tt)
                <div class="tt-item">
                    <div class="tt-badge">
                        {{ strtoupper(substr($tt->class_name ?? '', 0, 2)) }}
                    </div>
                    <div class="tt-info">
                        <div class="tt-name">{{ $tt->name ?? ($tt->class_name . ' – ' . $tt->stream_name) }}</div>
                        <div class="tt-meta">
                            <span><i class="fas fa-school me-1"></i>{{ $tt->class_name }}</span>
                            <span><i class="fas fa-users me-1"></i>{{ $tt->stream_name }}</span>
                            <span><i class="fas fa-layer-group me-1"></i>{{ $tt->slot_count }} slots</span>
                        </div>
                    </div>
                    <span class="status-badge status-active">
                        <i class="fas fa-circle" style="font-size: 0.5rem;"></i> Active
                    </span>
                    <div class="tt-actions">
                        <a href="{{ route('timetable.view', $tt->id) }}" class="btn-icon btn-icon-view" title="View">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="{{ route('timetable.edit', $tt->id) }}" class="btn-icon btn-icon-edit" title="Edit">
                            <i class="fas fa-pencil-alt"></i>
                        </a>
                    </div>
                </div>
                @empty
                <div class="empty-state">
                    <i class="fas fa-calendar-times"></i>
                    <p>No active timetables</p>
                </div>
                @endforelse
            </div>

            {{-- Draft Timetables --}}
            @if($draftTimetables->isNotEmpty())
            <div class="data-card">
                <div class="card-header-modern">
                    <div class="card-title">
                        <i class="fas fa-pen-fancy" style="color: var(--warning);"></i>
                        Draft Timetables
                    </div>
                    <span class="count-badge">{{ $draftTimetables->count() }}</span>
                </div>
                @foreach($draftTimetables as $tt)
                <div class="tt-item">
                    <div class="tt-badge tt-badge-draft">
                        {{ strtoupper(substr($tt->class_name ?? '', 0, 2)) }}
                    </div>
                    <div class="tt-info">
                        <div class="tt-name">{{ $tt->name ?? ($tt->class_name . ' – ' . $tt->stream_name) }}</div>
                        <div class="tt-meta">
                            <span><i class="fas fa-school me-1"></i>{{ $tt->class_name }}</span>
                            <span><i class="fas fa-users me-1"></i>{{ $tt->stream_name }}</span>
                            <span><i class="fas fa-layer-group me-1"></i>{{ $tt->slot_count }} slots</span>
                        </div>
                    </div>
                    <span class="status-badge status-draft">
                        <i class="fas fa-circle" style="font-size: 0.5rem;"></i> Draft
                    </span>
                    <div class="tt-actions">
                        <a href="{{ route('timetable.edit', $tt->id) }}" class="btn-icon btn-icon-edit" title="Edit">
                            <i class="fas fa-pencil-alt"></i>
                        </a>
                        <button onclick="quickActivate({{ $tt->id }})" class="btn-icon btn-icon-activate" title="Activate">
                            <i class="fas fa-check-circle"></i>
                        </button>
                        <button onclick="confirmDelete({{ $tt->id }})" class="btn-icon btn-icon-delete" title="Delete">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Right Column: Today's Schedule & Periods --}}
        <div>
            {{-- Today's Schedule --}}
            <div class="data-card">
                <div class="card-header-modern">
                    <div class="card-title">
                        <i class="fas fa-calendar-day" style="color: var(--brand);"></i>
                        Today's Schedule
                    </div>
                    <span style="font-size: 0.7rem; color: var(--text-muted);">
                        <i class="fas fa-calendar-alt me-1"></i>
                        {{ \Carbon\Carbon::today()->format('l, F j, Y') }}
                    </span>
                </div>
                <div>
                    @forelse($todaySchedule as $slot)
                    <div class="today-slot">
                        <div class="slot-time">
                            {{ $slot->period ? \Carbon\Carbon::parse($slot->period->start_time)->format('h:i A') : '—' }}
                        </div>
                        <div class="slot-color" style="background: {{ $slot->color ?? $brand }}"></div>
                        <div class="slot-info">
                            <div class="slot-subject">{{ $slot->subject_name }}</div>
                            <div class="slot-class">
                                <i class="fas fa-school me-1"></i>{{ $slot->class_name }} · {{ $slot->stream_name }}
                            </div>
                            @if($slot->room)
                            <div class="slot-room">
                                <i class="fas fa-door-open me-1"></i>{{ $slot->room }}
                            </div>
                            @endif
                        </div>
                    </div>
                    @empty
                    <div class="empty-state">
                        <i class="fas fa-calendar-week"></i>
                        <p>No classes scheduled today</p>
                    </div>
                    @endforelse
                </div>
                <div style="padding: 1rem 1.5rem; border-top: 1px solid var(--border-light);">
                    <a href="{{ route('timetable.teacher') }}" class="btn btn-sm w-100" style="background: var(--brand-muted); color: var(--brand); border-radius: 12px; font-weight: 600;">
                        <i class="fas fa-calendar-week me-2"></i> View Full Weekly Schedule
                    </a>
                </div>
            </div>

            {{-- Period Definitions --}}
            <div class="data-card">
                <div class="card-header-modern">
                    <div class="card-title">
                        <i class="fas fa-hourglass-half" style="color: var(--info);"></i>
                        Period Definitions
                    </div>
                    <a href="{{ route('timetable.periods.index') }}" class="btn" style="background: var(--brand-muted); color: var(--brand); border-radius: 10px; padding: 0.3rem 0.8rem; font-size: 0.7rem; font-weight: 600;">
                        <i class="fas fa-cog me-1"></i> Manage
                    </a>
                </div>
                <div>
                    @foreach($periods as $period)
                    <div class="period-item">
                        <div>
                            <span class="period-name">{{ $period->name }}</span>
                            @if($period->type !== 'lesson')
                            <span class="period-type">{{ ucfirst($period->type) }}</span>
                            @endif
                        </div>
                        <div class="period-time">
                            <i class="far fa-clock me-1"></i>
                            {{ \Carbon\Carbon::parse($period->start_time)->format('h:i A') }} – {{ \Carbon\Carbon::parse($period->end_time)->format('h:i A') }}
                        </div>
                    </div>
                    @endforeach
                    @if($periods->isEmpty())
                    <div class="empty-state">
                        <i class="fas fa-clock"></i>
                        <p>No periods defined. <a href="{{ route('timetable.periods.index') }}" style="color: var(--brand);">Add periods</a> first.</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

        </div>
    </div>
</div>
@endsection


<script>
async function quickActivate(id) {
    Swal.fire({
        title: 'Activate Timetable?',
        text: 'This timetable will be visible to all teachers.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#5351e4',
        confirmButtonText: 'Yes, Activate',
        cancelButtonText: 'Cancel'
    }).then(async (result) => {
        if (!result.isConfirmed) return;

        Swal.fire({
            title: 'Activating...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        try {
            const response = await fetch(`/timetable/${id}/status`, {
                method: 'PATCH',
                headers: { 
                    'Content-Type': 'application/json', 
                    'X-CSRF-TOKEN': '{{ csrf_token() }}' 
                },
                body: JSON.stringify({ status: 'active' })
            });
            const data = await response.json();
            
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Activated!',
                    text: 'Timetable has been activated successfully.',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => window.location.reload());
            } else {
                Swal.fire('Error', data.message || 'Could not activate.', 'error');
            }
        } catch(e) {
            Swal.fire('Error', 'Network error. Please try again.', 'error');
        }
    });
}

async function confirmDelete(id) {
    Swal.fire({
        title: 'Delete Timetable?',
        text: 'This action cannot be undone. All slots will be permanently deleted.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        confirmButtonText: 'Yes, Delete',
        cancelButtonText: 'Cancel'
    }).then(async (result) => {
        if (!result.isConfirmed) return;

        Swal.fire({
            title: 'Deleting...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        try {
            const response = await fetch(`/timetable/${id}`, {
                method: 'DELETE',
                headers: { 
                    'X-CSRF-TOKEN': '{{ csrf_token() }}' 
                }
            });
            const data = await response.json();
            
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Deleted!',
                    text: 'Timetable has been deleted.',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => window.location.reload());
            } else {
                Swal.fire('Error', data.message || 'Could not delete.', 'error');
            }
        } catch(e) {
            Swal.fire('Error', 'Network error. Please try again.', 'error');
        }
    });
}
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
