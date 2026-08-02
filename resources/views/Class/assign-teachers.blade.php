<?php
use App\Http\Controllers\Helper;
use App\Helpers\PermissionHelper;
?>
@extends('layouts-side-bar.master')
@section('css')
    <link href="{{ URL::asset('assets/plugins/jvectormap/jqvmap.css') }}" rel="stylesheet" />
    <link href="{{ URL::asset('assets/plugins/datatable/dataTables.bootstrap4.min.css') }}" rel="stylesheet" />
    <link href="{{ URL::asset('assets/plugins/bootstrap-daterangepicker/daterangepicker.css') }}" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
@endsection

@section('content')
    <div class="side-app">
        <style>
            * {
                font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            }

            :root {
                --surface: #ffffff;
                --surface-secondary: #f8fafc;
                --border: #e2e8f0;
                --text-primary: #0f172a;
                --text-secondary: #475569;
                --text-muted: #94a3b8;
                --accent: #6366f1;
                --accent-light: #e0e7ff;
                --success: #10b981;
                --success-light: #d1fae5;
                --warning: #f59e0b;
                --warning-light: #fef3c7;
                --danger: #ef4444;
                --danger-light: #fee2e2;
                --shadow-sm: 0 1px 2px rgba(0, 0, 0, 0.05);
                --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.1);
                --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -4px rgba(0, 0, 0, 0.1);
                --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
                --radius-sm: 8px;
                --radius-md: 12px;
                --radius-lg: 16px;
                --radius-xl: 20px;
                --radius-2xl: 24px;
            }

            /* Main Container */
            .assign-dashboard {
                padding: 8px;
                max-width: 1440px;
                margin: 0 auto;
            }

            /* Premium Header Section */
            .dashboard-header {
                background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
                border-radius: var(--radius-2xl);
                padding: 32px 36px;
                margin-bottom: 28px;
                position: relative;
                overflow: hidden;
                box-shadow: var(--shadow-xl);
            }

            .dashboard-header::before {
                content: '';
                position: absolute;
                top: -50%;
                right: -20%;
                width: 500px;
                height: 500px;
                background: radial-gradient(circle, rgba(99, 102, 241, 0.15) 0%, transparent 70%);
                border-radius: 50%;
            }

            .dashboard-header::after {
                content: '';
                position: absolute;
                bottom: -30%;
                left: -10%;
                width: 400px;
                height: 400px;
                background: radial-gradient(circle, rgba(16, 185, 129, 0.1) 0%, transparent 70%);
                border-radius: 50%;
            }

            .header-content {
                position: relative;
                z-index: 1;
                display: flex;
                align-items: center;
                justify-content: space-between;
                flex-wrap: wrap;
                gap: 24px;
            }

            .header-title-section h2 {
                color: white;
                font-weight: 800;
                margin: 0;
                font-size: 28px;
                letter-spacing: -0.5px;
                display: flex;
                align-items: center;
                gap: 16px;
            }

            .header-title-section h2 .icon-circle {
                width: 48px;
                height: 48px;
                border-radius: var(--radius-lg);
                background: linear-gradient(135deg, rgba(99, 102, 241, 0.3), rgba(99, 102, 241, 0.1));
                backdrop-filter: blur(10px);
                border: 1px solid rgba(255, 255, 255, 0.1);
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .header-subtitle {
                color: rgba(255, 255, 255, 0.7);
                margin-top: 8px;
                font-size: 14px;
                font-weight: 500;
                display: flex;
                align-items: center;
                gap: 20px;
            }

            .header-stats {
                display: flex;
                gap: 16px;
                flex-wrap: wrap;
            }

            .stat-card {
                background: rgba(255, 255, 255, 0.08);
                backdrop-filter: blur(20px);
                border: 1px solid rgba(255, 255, 255, 0.1);
                border-radius: var(--radius-lg);
                padding: 16px 24px;
                color: white;
                min-width: 140px;
            }

            .stat-value {
                font-size: 28px;
                font-weight: 800;
                letter-spacing: -1px;
            }

            .stat-label {
                font-size: 12px;
                text-transform: uppercase;
                letter-spacing: 1px;
                color: rgba(255, 255, 255, 0.6);
                margin-top: 4px;
                font-weight: 600;
            }

            /* Floating Action Bar */
            .floating-action-bar {
                position: sticky;
                top: 20px;
                z-index: 100;
                background: rgba(255, 255, 255, 0.9);
                backdrop-filter: blur(30px) saturate(180%);
                border-radius: var(--radius-xl);
                padding: 20px 24px;
                margin-bottom: 28px;
                box-shadow: var(--shadow-xl);
                border: 1px solid rgba(255, 255, 255, 0.5);
                display: flex;
                align-items: center;
                gap: 20px;
                flex-wrap: wrap;
                transition: all 0.3s ease;
            }

            .floating-action-bar.scrolled {
                box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
                background: rgba(255, 255, 255, 0.98);
            }

            .search-container {
                position: relative;
                flex: 1;
                min-width: 300px;
            }

            .search-container input {
                width: 100%;
                padding: 14px 20px 14px 50px;
                border: 2px solid transparent;
                border-radius: var(--radius-lg);
                font-size: 14px;
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                background: var(--surface-secondary);
                color: var(--text-primary);
                font-weight: 500;
            }

            .search-container input:focus {
                outline: none;
                border-color: var(--accent);
                background: white;
                box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
            }

            .search-container .search-icon-wrapper {
                position: absolute;
                left: 16px;
                top: 50%;
                transform: translateY(-50%);
                width: 24px;
                height: 24px;
                display: flex;
                align-items: center;
                justify-content: center;
                color: var(--text-muted);
                transition: all 0.3s ease;
            }

            .search-container input:focus~.search-icon-wrapper {
                color: var(--accent);
            }

            .changes-badge {
                display: flex;
                align-items: center;
                gap: 12px;
                padding: 10px 20px;
                border-radius: 50px;
                font-weight: 700;
                font-size: 13px;
                white-space: nowrap;
                transition: all 0.3s ease;
                cursor: default;
            }

            .changes-badge.clean {
                background: var(--success-light);
                color: #065f46;
                border: 1px solid #a7f3d0;
            }

            .changes-badge.dirty {
                background: var(--warning-light);
                color: #92400e;
                border: 1px solid #fde68a;
                animation: gentle-pulse 2s infinite;
            }

            @keyframes gentle-pulse {

                0%,
                100% {
                    box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.4);
                }

                50% {
                    box-shadow: 0 0 0 8px rgba(245, 158, 11, 0);
                }
            }

            .btn-save-all {
                background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
                border: none;
                color: white;
                font-weight: 700;
                padding: 14px 32px;
                border-radius: 50px;
                font-size: 14px;
                letter-spacing: 0.3px;
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                box-shadow: 0 4px 20px rgba(99, 102, 241, 0.4);
                white-space: nowrap;
                position: relative;
                overflow: hidden;
            }

            .btn-save-all::before {
                content: '';
                position: absolute;
                top: 0;
                left: -100%;
                width: 100%;
                height: 100%;
                background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
                transition: left 0.5s ease;
            }

            .btn-save-all:hover::before {
                left: 100%;
            }

            .btn-save-all:hover {
                transform: translateY(-2px);
                box-shadow: 0 8px 30px rgba(99, 102, 241, 0.6);
            }

            .btn-save-all:active {
                transform: translateY(0) scale(0.98);
            }

            .btn-save-all:disabled {
                opacity: 0.6;
                cursor: not-allowed;
                transform: none;
            }

            /* Class Cards Grid */
            .classes-grid {
                display: grid;
                gap: 20px;
            }

            .class-card-premium {
                background: white;
                border-radius: var(--radius-xl);
                overflow: hidden;
                box-shadow: var(--shadow-md);
                transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
                border: 1px solid var(--border);
                position: relative;
            }

            .class-card-premium:hover {
                box-shadow: var(--shadow-xl);
                transform: translateY(-3px);
                border-color: #cbd5e1;
            }

            .class-card-premium::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                height: 3px;
                background: linear-gradient(90deg, #6366f1, #8b5cf6, #a855f7);
                opacity: 0;
                transition: opacity 0.3s ease;
            }

            .class-card-premium:hover::before {
                opacity: 1;
            }

            .class-card-header-premium {
                padding: 20px 28px;
                display: flex;
                align-items: center;
                justify-content: space-between;
                cursor: pointer;
                background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);
                border-bottom: 1px solid #f1f5f9;
                transition: all 0.3s ease;
                user-select: none;
            }

            .class-card-header-premium:hover {
                background: linear-gradient(135deg, #eef2ff 0%, #f8fafc 100%);
            }

            .class-identity {
                display: flex;
                align-items: center;
                gap: 16px;
            }

            .class-avatar {
                width: 52px;
                height: 52px;
                border-radius: var(--radius-lg);
                background: linear-gradient(135deg, #6366f1, #8b5cf6);
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                font-size: 22px;
                font-weight: 800;
                box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
                position: relative;
            }

            .class-avatar .status-dot {
                position: absolute;
                bottom: -2px;
                right: -2px;
                width: 14px;
                height: 14px;
                border-radius: 50%;
                border: 3px solid white;
                background: #10b981;
            }

            .class-avatar .status-dot.has-changes {
                background: #f59e0b;
                animation: gentle-pulse 2s infinite;
            }

            .class-info h5 {
                margin: 0 0 4px 0;
                font-weight: 700;
                color: var(--text-primary);
                font-size: 18px;
                letter-spacing: -0.3px;
            }

            .class-info .class-meta {
                display: flex;
                gap: 16px;
                font-size: 13px;
                color: var(--text-muted);
                font-weight: 500;
            }

            .class-info .class-meta span {
                display: flex;
                align-items: center;
                gap: 4px;
            }

            .header-actions {
                display: flex;
                align-items: center;
                gap: 12px;
            }

            .badge-pill {
                padding: 6px 14px;
                border-radius: 50px;
                font-size: 12px;
                font-weight: 700;
                letter-spacing: 0.5px;
                text-transform: uppercase;
            }

            .badge-pill.changes {
                background: var(--warning-light);
                color: #92400e;
            }

            .toggle-btn {
                width: 40px;
                height: 40px;
                border-radius: 50%;
                background: white;
                border: 2px solid #e2e8f0;
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                color: #64748b;
            }

            .toggle-btn.rotated {
                transform: rotate(180deg);
                background: var(--accent);
                color: white;
                border-color: var(--accent);
            }

            .class-card-body-premium {
                padding: 0;
                max-height: 0;
                overflow: hidden;
                transition: max-height 0.5s cubic-bezier(0.4, 0, 0.2, 1), padding 0.3s ease;
            }

            .class-card-body-premium.expanded {
                max-height: 5000px;
                padding: 28px;
            }

            /* Assignment Sections */
            .assignment-section {
                background: var(--surface-secondary);
                border-radius: var(--radius-lg);
                padding: 20px;
                margin-bottom: 20px;
                border: 1px solid #f1f5f9;
                transition: all 0.3s ease;
            }

            .assignment-section:hover {
                border-color: #e2e8f0;
                box-shadow: var(--shadow-sm);
            }

            .assignment-section:last-child {
                margin-bottom: 0;
            }

            .section-label {
                display: flex;
                align-items: center;
                gap: 10px;
                margin-bottom: 16px;
            }

            .section-label .indicator {
                width: 8px;
                height: 8px;
                border-radius: 50%;
                background: var(--accent);
            }

            .section-label span {
                font-weight: 700;
                color: var(--text-primary);
                font-size: 14px;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }

            .assignment-field-row {
                display: grid;
                grid-template-columns: 200px 1fr;
                gap: 20px;
                align-items: center;
                padding: 16px;
                background: white;
                border-radius: var(--radius-md);
                margin-bottom: 12px;
                transition: all 0.3s ease;
                border: 2px solid transparent;
            }

            .assignment-field-row:last-child {
                margin-bottom: 0;
            }

            .assignment-field-row:hover {
                border-color: #e2e8f0;
                transform: translateX(4px);
            }

            .assignment-field-row.is-dirty {
                border-color: #f59e0b;
                background: #fffbeb;
                animation: slide-in 0.3s ease;
            }

            @keyframes slide-in {
                from {
                    transform: translateX(-10px);
                    opacity: 0.5;
                }

                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }

            .field-label-group {
                display: flex;
                align-items: center;
                gap: 12px;
            }

            .field-icon {
                width: 36px;
                height: 36px;
                border-radius: var(--radius-sm);
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 14px;
            }

            .field-icon.supervisor {
                background: #e0e7ff;
                color: #6366f1;
            }

            .field-icon.teacher {
                background: #d1fae5;
                color: #059669;
            }

            .field-icon.subject {
                background: #fce7f3;
                color: #db2777;
            }

            .field-text {
                font-weight: 600;
                color: var(--text-primary);
                font-size: 14px;
            }

            .field-text .field-subtitle {
                display: block;
                font-size: 12px;
                color: var(--text-muted);
                font-weight: 500;
                margin-top: 2px;
            }

            /* ---- Teacher/Supervisor select boxes ----
                   NOTE: renamed from ".custom-select" -> ".teacher-select".
                   Bootstrap 4 (loaded via dataTables.bootstrap4.min.css) ships its
                   OWN ".custom-select" rule with its own height/line-height/padding
                   and a native background-image chevron. That rule was colliding
                   with this one and squashing the text inside the box, which is
                   what caused the "cut-through" look in the dropdowns. Renaming
                   the class avoids the collision entirely. */
            .teacher-select-wrapper {
                position: relative;
            }

            .teacher-select {
                width: 100%;
                height: 46px;
                box-sizing: border-box;
                padding: 0 40px 0 16px;
                border: 2px solid #e2e8f0;
                border-radius: var(--radius-sm);
                font-size: 14px;
                line-height: 1.4;
                font-weight: 500;
                transition: all 0.3s ease;
                background-color: white;
                background-image: none;
                cursor: pointer;
                appearance: none;
                -webkit-appearance: none;
                -moz-appearance: none;
                color: var(--text-primary);
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            .teacher-select:focus {
                outline: none;
                border-color: var(--accent);
                box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
            }

            .teacher-select-wrapper::after {
                content: '\f107';
                font-family: 'Font Awesome 6 Free';
                font-weight: 900;
                position: absolute;
                right: 16px;
                top: 50%;
                transform: translateY(-50%);
                color: var(--text-muted);
                pointer-events: none;
                transition: all 0.3s ease;
            }

            /* Stream Section */
            .stream-container {
                border-left: 3px solid var(--accent);
                padding-left: 24px;
                margin: 24px 0;
            }

            .stream-header {
                display: flex;
                align-items: center;
                gap: 12px;
                margin-bottom: 16px;
                padding: 12px 16px;
                background: white;
                border-radius: var(--radius-md);
                box-shadow: var(--shadow-sm);
            }

            .stream-badge {
                background: var(--accent);
                color: white;
                padding: 4px 12px;
                border-radius: 20px;
                font-size: 12px;
                font-weight: 700;
                letter-spacing: 0.5px;
            }

            .stream-name {
                font-weight: 700;
                color: var(--text-primary);
                font-size: 15px;
            }

            .subjects-grid {
                display: grid;
                gap: 12px;
            }

            .subject-card {
                background: white;
                border-radius: var(--radius-md);
                padding: 16px;
                border: 1px solid #f1f5f9;
                transition: all 0.3s ease;
            }

            .subject-card:hover {
                box-shadow: var(--shadow-md);
                border-color: #e2e8f0;
            }

            .subject-header {
                display: flex;
                align-items: center;
                gap: 10px;
                margin-bottom: 12px;
                padding-bottom: 12px;
                border-bottom: 1px solid #f1f5f9;
            }

            .subject-dot {
                width: 10px;
                height: 10px;
                border-radius: 50%;
                background: #ec4899;
            }

            .subject-name {
                font-weight: 700;
                color: var(--text-primary);
                font-size: 14px;
            }

            .subject-teachers {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 12px;
            }

            /* Save Button */
            .save-section {
                display: flex;
                justify-content: flex-end;
                padding-top: 20px;
                margin-top: 20px;
                border-top: 2px solid #f1f5f9;
            }

            .btn-save-class-premium {
                background: linear-gradient(135deg, #10b981 0%, #059669 100%);
                border: none;
                color: white;
                font-weight: 700;
                padding: 12px 28px;
                border-radius: 50px;
                font-size: 14px;
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                box-shadow: 0 4px 15px rgba(16, 185, 129, 0.4);
                position: relative;
                overflow: hidden;
            }

            .btn-save-class-premium::before {
                content: '';
                position: absolute;
                top: 0;
                left: -100%;
                width: 100%;
                height: 100%;
                background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
                transition: left 0.5s ease;
            }

            .btn-save-class-premium:hover:not(:disabled)::before {
                left: 100%;
            }

            .btn-save-class-premium:hover:not(:disabled) {
                transform: translateY(-2px);
                box-shadow: 0 8px 25px rgba(16, 185, 129, 0.5);
            }

            .btn-save-class-premium:disabled {
                opacity: 0.5;
                cursor: not-allowed;
                filter: grayscale(50%);
            }

            /* Empty States */
            .empty-state {
                text-align: center;
                padding: 60px 20px;
            }

            .empty-state .empty-icon {
                font-size: 64px;
                margin-bottom: 20px;
                display: block;
                background: linear-gradient(135deg, #e0e7ff, #c7d2fe);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
            }

            .empty-state h4 {
                color: var(--text-primary);
                font-weight: 700;
                margin-bottom: 8px;
            }

            .empty-state p {
                color: var(--text-muted);
                font-weight: 500;
            }

            /* Toast Customization */
            .colored-toast {
                border-radius: var(--radius-lg) !important;
                font-family: 'Inter', sans-serif !important;
                font-weight: 600 !important;
            }

            /* Responsive */
            @media (max-width: 968px) {
                .assignment-field-row {
                    grid-template-columns: 1fr;
                    gap: 12px;
                }

                .header-content {
                    flex-direction: column;
                    align-items: flex-start;
                }

                .subject-teachers {
                    grid-template-columns: 1fr;
                }

                .floating-action-bar {
                    flex-direction: column;
                }
            }
        </style>

        <div class="assign-dashboard">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card bg-primary">
                        @include('layouts.class-buttons')
                        <div class="card-body bg-light">

                            <!-- Premium Header -->
                            <div class="dashboard-header">
                                <div class="header-content">
                                    <div class="header-title-section">
                                        <h2>
                                            <div class="icon-circle">
                                                <i class="fas fa-users-cog" style="color: white;"></i>
                                            </div>
                                            Teacher Assignment Center
                                        </h2>
                                        <div class="header-subtitle">
                                            <span><i class="fas fa-layer-group me-1"></i> {{ count($classesData) }}
                                                Classes</span>
                                            <span><i class="fas fa-chalkboard-teacher me-1"></i> {{ count($Teachers) }}
                                                Teachers</span>
                                        </div>
                                    </div>
                                    <div class="header-stats">
                                        <div class="stat-card">
                                            <div class="stat-value" id="totalClassesCount">{{ count($classesData) }}</div>
                                            <div class="stat-label">Total Classes</div>
                                        </div>
                                        <div class="stat-card">
                                            <div class="stat-value" id="totalChangesCount">0</div>
                                            <div class="stat-label">Pending Changes</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Floating Action Bar -->
                            <div class="floating-action-bar" id="floatingBar">
                                <div class="search-container">
                                    <input type="text" id="classSearch" placeholder="Search classes by name or stream...">
                                    <div class="search-icon-wrapper">
                                        <i class="fas fa-search"></i>
                                    </div>
                                </div>
                                <div class="changes-badge clean" id="changesBadge">
                                    <i class="fas fa-check-circle"></i>
                                    <span>All Changes Saved</span>
                                </div>
                                <button type="button" class="btn-save-all" id="btnSaveAll">
                                    <i class="fas fa-cloud-upload-alt me-2"></i> Save All Changes
                                </button>
                            </div>

                            <!-- Classes Grid -->
                            <div class="classes-grid" id="classesGrid">
                                @forelse ($classesData as $data)
                                                        <?php    $classroom = $data['classroom']; ?>
                                                        <div class="class-card-premium"
                                                            data-class-name="{{ strtolower(Helper::recordMdname($classroom->class_name)) }}">
                                                            <div class="class-card-header-premium" data-toggle="collapse"
                                                                data-target="#classBody{{ $classroom->id }}">
                                                                <div class="class-identity">
                                                                    <div class="class-avatar">
                                                                        {{ substr(Helper::recordMdname($classroom->class_name), 0, 1) }}
                                                                        <div class="status-dot" id="statusDot{{ $classroom->id }}"></div>
                                                                    </div>
                                                                    <div class="class-info">
                                                                        <h5>{{ Helper::recordMdname($classroom->class_name) }}</h5>
                                                                        <div class="class-meta">
                                                                            <span><i class="fas fa-stream"></i> {{ count($data['streams']) }}
                                                                                Streams</span>
                                                                            <span><i class="fas fa-book"></i>
                                                                                {{ collect($data['streams'])->sum(function ($s) {
                                    return count($s->subjects); }) }}
                                                                                Subjects</span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="header-actions">
                                                                    <span class="badge-pill changes" id="badge{{ $classroom->id }}"
                                                                        style="display:none;">
                                                                        <i class="fas fa-exclamation-circle me-1"></i> Unsaved
                                                                    </span>
                                                                    <div class="toggle-btn" id="toggleBtn{{ $classroom->id }}">
                                                                        <i class="fas fa-chevron-down"></i>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="class-card-body-premium" id="classBody{{ $classroom->id }}">
                                                                <div data-class-card-id="{{ $classroom->id }}">

                                                                    @if(PermissionHelper::canFeature('assign_class_teacher'))
                                                                        <div class="assignment-section">
                                                                            <div class="section-label">
                                                                                <div class="indicator"></div>
                                                                                <span>Class Supervisor</span>
                                                                            </div>
                                                                            <div class="assignment-field-row">
                                                                                <div class="field-label-group">
                                                                                    <div class="field-icon supervisor">
                                                                                        <i class="fas fa-user-shield"></i>
                                                                                    </div>
                                                                                    <div class="field-text">
                                                                                        Supervisor
                                                                                        <span class="field-subtitle">Overall class oversight</span>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="teacher-select-wrapper">
                                                                                    <select class="teacher-select assign-field" data-kind="supervisor"
                                                                                        data-entity-id="{{ $classroom->id }}"
                                                                                        data-original="{{ $classroom->class_supervisor ?? '' }}">
                                                                                        <option value="">-- Select Supervisor --</option>
                                                                                        @foreach ($Teachers as $teacher)
                                                                                            <option value="{{ $teacher->id }}" {{ $classroom->class_supervisor == $teacher->id ? 'selected' : '' }}>
                                                                                                {{ $teacher->surname }} {{ $teacher->firstname }}
                                                                                            </option>
                                                                                        @endforeach
                                                                                    </select>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    @endif

                                                                    @forelse ($data['streams'] as $stream)
                                                                        <div class="stream-container">
                                                                            <div class="stream-header">
                                                                                <span class="stream-badge">
                                                                                    <i class="fas fa-layer-group me-1"></i> Stream
                                                                                </span>
                                                                                <span class="stream-name">
                                                                                    {{ $stream->stream_id === \App\Http\Controllers\ClassandSubjectController::NO_STREAM_SENTINEL ? 'Default Stream' : $stream->stream_id }}
                                                                                </span>
                                                                            </div>

                                                                            @if(PermissionHelper::canFeature('assign_class_teacher'))
                                                                                <div class="assignment-section" style="margin-bottom: 16px;">
                                                                                    <div class="assignment-field-row">
                                                                                        <div class="field-label-group">
                                                                                            <div class="field-icon teacher">
                                                                                                <i class="fas fa-chalkboard-teacher"></i>
                                                                                            </div>
                                                                                            <div class="field-text">
                                                                                                Class Teacher
                                                                                                <span class="field-subtitle">Stream-level teacher</span>
                                                                                            </div>
                                                                                        </div>
                                                                                        <div class="teacher-select-wrapper">
                                                                                            <select class="teacher-select assign-field"
                                                                                                data-kind="class-teacher" data-entity-id="{{ $stream->id }}"
                                                                                                data-original="{{ $stream->class_teacher ?? '' }}">
                                                                                                <option value="">-- Select Class Teacher --</option>
                                                                                                @foreach ($Teachers as $teacher)
                                                                                                    <option value="{{ $teacher->id }}" {{ $stream->class_teacher == $teacher->id ? 'selected' : '' }}>
                                                                                                        {{ $teacher->surname }} {{ $teacher->firstname }}
                                                                                                    </option>
                                                                                                @endforeach
                                                                                            </select>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            @endif

                                                                            @if(PermissionHelper::canFeature('assign_subject_teachers'))
                                                                                <div class="subjects-grid">
                                                                                    @forelse ($stream->subjects as $subject)
                                                                                        <div class="subject-card">
                                                                                            <div class="subject-header">
                                                                                                <div class="subject-dot"></div>
                                                                                                <div class="subject-name">
                                                                                                    <i class="fas fa-book-open me-2"></i>
                                                                                                    {{ $subject->display_name }}
                                                                                                </div>
                                                                                            </div>
                                                                                            <div class="subject-teachers">
                                                                                                <div class="teacher-select-wrapper">
                                                                                                    <select class="teacher-select assign-field"
                                                                                                        data-kind="subject-teacher-1"
                                                                                                        data-entity-id="{{ $subject->id }}"
                                                                                                        data-original="{{ $subject->subject_teacher_1 ?? '' }}">
                                                                                                        <option value="">Teacher 1: None</option>
                                                                                                        @foreach ($Teachers as $teacher)
                                                                                                            <option value="{{ $teacher->id }}" {{ $subject->subject_teacher_1 == $teacher->id ? 'selected' : '' }}>
                                                                                                                {{ $teacher->surname }} {{ $teacher->firstname }}
                                                                                                            </option>
                                                                                                        @endforeach
                                                                                                    </select>
                                                                                                </div>
                                                                                                <div class="teacher-select-wrapper">
                                                                                                    <select class="teacher-select assign-field"
                                                                                                        data-kind="subject-teacher-2"
                                                                                                        data-entity-id="{{ $subject->id }}"
                                                                                                        data-original="{{ $subject->subject_teacher_2 ?? '' }}">
                                                                                                        <option value="">Teacher 2: None</option>
                                                                                                        @foreach ($Teachers as $teacher)
                                                                                                            <option value="{{ $teacher->id }}" {{ $subject->subject_teacher_2 == $teacher->id ? 'selected' : '' }}>
                                                                                                                {{ $teacher->surname }} {{ $teacher->firstname }}
                                                                                                            </option>
                                                                                                        @endforeach
                                                                                                    </select>
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>
                                                                                    @empty
                                                                                        <p
                                                                                            style="color: var(--text-muted); padding: 16px; text-align: center; background: white; border-radius: var(--radius-md);">
                                                                                            <i class="fas fa-inbox me-2"></i> No subjects assigned yet
                                                                                        </p>
                                                                                    @endforelse
                                                                                </div>
                                                                            @endif
                                                                        </div>
                                                                    @empty
                                                                        <div class="empty-state">
                                                                            <i class="fas fa-cubes empty-icon"></i>
                                                                            <h4>No Streams Yet</h4>
                                                                            <p>Add streams to this class to start assigning teachers.</p>
                                                                        </div>
                                                                    @endforelse

                                                                    <div class="save-section">
                                                                        <button type="button" class="btn-save-class-premium" disabled>
                                                                            <i class="fas fa-save me-2"></i> Save Class Changes
                                                                        </button>
                                                                    </div>

                                                                </div>
                                                            </div>
                                                        </div>
                                @empty
                                    <div class="empty-state">
                                        <i class="fas fa-school empty-icon"></i>
                                        <h4>Welcome to Assignment Center</h4>
                                        <p>Create your first class to begin assigning teachers and supervisors.</p>
                                    </div>
                                @endforelse
                            </div>

                            <div class="empty-state" id="noSearchResults" style="display:none;">
                                <i class="fas fa-search empty-icon"></i>
                                <h4>No Classes Found</h4>
                                <p>Try adjusting your search terms.</p>
                            </div>

                        </div>
                    </div>
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
        const ASSIGN_ENDPOINTS = {
            'supervisor': {
                assignUrl: "{{ route('class.assignSupervisor') }}",
                removeUrl: "{{ route('class.removeSupervisor') }}",
                idParam: 'class_id'
            },
            'class-teacher': {
                assignUrl: "{{ route('class.assignClassTeacher') }}",
                removeUrl: "{{ route('class.removeClassTeacher') }}",
                idParam: 'class_id'
            },
            'subject-teacher-1': {
                assignUrl: "{{ route('class.assignSubjectTeacher1') }}",
                removeUrl: "{{ route('class.removeSubjectTeacher1') }}",
                idParam: 'subject_id'
            },
            'subject-teacher-2': {
                assignUrl: "{{ route('class.assignSubjectTeacher2') }}",
                removeUrl: "{{ route('class.removeSubjectTeacher2') }}",
                idParam: 'subject_id'
            }
        };

        const CSRF_TOKEN = "{{ csrf_token() }}";

        $(document).ready(function () {

            // Handle card collapse with smooth animation
            $(document).on('click', '.class-card-header-premium', function (e) {
                const target = $(this).data('target');
                const $body = $(target);
                const $btn = $(this).find('.toggle-btn');

                if ($body.hasClass('expanded')) {
                    $body.removeClass('expanded');
                    $btn.removeClass('rotated');
                } else {
                    $body.addClass('expanded');
                    $btn.addClass('rotated');
                }
            });

            // Expand first card by default
            $('.class-card-body-premium').first().addClass('expanded');
            $('.toggle-btn').first().addClass('rotated');

            function refreshDirtyState($select) {
                const original = String($select.data('original') ?? '');
                const current = String($select.val() ?? '');
                const isDirty = original !== current;
                $select.toggleClass('is-dirty', isDirty);
                $select.closest('.assignment-field-row').toggleClass('is-dirty', isDirty);
            }

            function updateAllBadges() {
                const totalDirty = $('.assign-field').filter(function () {
                    return $(this).hasClass('is-dirty');
                }).length;

                // Update floating badge
                const $badge = $('#changesBadge');
                if (totalDirty === 0) {
                    $badge.removeClass('dirty').addClass('clean');
                    $badge.html('<i class="fas fa-check-circle"></i><span>All Changes Saved</span>');
                } else {
                    $badge.removeClass('clean').addClass('dirty');
                    $badge.html(`<i class="fas fa-exclamation-triangle"></i><span>${totalDirty} Pending Change${totalDirty > 1 ? 's' : ''}</span>`);
                }

                // Update header stats
                $('#totalChangesCount').text(totalDirty);

                // Update per-class badges and dots
                $('.class-card-premium').each(function () {
                    const classId = $(this).find('[data-class-card-id]').data('class-card-id');
                    const hasDirty = $(this).find('.assign-field.is-dirty').length > 0;

                    $(`#badge${classId}`).toggle(hasDirty);
                    $(this).find('.btn-save-class-premium').prop('disabled', !hasDirty);

                    const $dot = $(`#statusDot${classId}`);
                    if (hasDirty) {
                        $dot.addClass('has-changes');
                    } else {
                        $dot.removeClass('has-changes');
                    }
                });
            }

            $(document).on('change', '.assign-field', function () {
                refreshDirtyState($(this));
                updateAllBadges();
            });

            window.addEventListener('beforeunload', function (e) {
                if ($('.assign-field.is-dirty').length > 0) {
                    e.preventDefault();
                    e.returnValue = '';
                }
            });

            // Live search
            $('#classSearch').on('keyup', function () {
                const term = $(this).val().toLowerCase().trim();
                let visibleCount = 0;

                $('.class-card-premium').each(function () {
                    const matches = $(this).data('class-name').includes(term);
                    $(this).toggle(matches);
                    if (matches) visibleCount++;
                });

                $('#noSearchResults').toggle(visibleCount === 0);
                $('.classes-grid').toggle(visibleCount > 0 || $('.class-card-premium').length === 0);
            });

            // Floating bar scroll effect
            $(window).on('scroll', function () {
                if ($(window).scrollTop() > 50) {
                    $('#floatingBar').addClass('scrolled');
                } else {
                    $('#floatingBar').removeClass('scrolled');
                }
            });

            function callEndpoint(url, idParam, entityId, teacherId) {
                let data = { _token: CSRF_TOKEN };
                data[idParam] = entityId;
                if (teacherId !== null) {
                    data.teacher_id = teacherId;
                }
                return $.ajax({ url: url, type: 'POST', data: data });
            }

            async function saveDirtyFields($scope, $triggerBtn) {
                const $fields = $scope.find('.assign-field.is-dirty');

                if ($fields.length === 0) {
                    return;
                }

                const originalBtnHtml = $triggerBtn.html();
                $triggerBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i> Saving...');

                let assigned = 0, removed = 0, failed = [];

                for (const el of $fields.toArray()) {
                    const $select = $(el);
                    const kind = $select.data('kind');
                    const entityId = $select.data('entity-id');
                    const original = String($select.data('original') ?? '');
                    const current = String($select.val() ?? '');
                    const endpoints = ASSIGN_ENDPOINTS[kind];
                    const label = $select.closest('.assignment-field-row, .subject-card').find('.field-text, .subject-name').first().text().trim();

                    try {
                        if (original !== '' && current !== original) {
                            await callEndpoint(endpoints.removeUrl, endpoints.idParam, entityId, null);
                            removed++;
                        }

                        if (current !== '') {
                            await callEndpoint(endpoints.assignUrl, endpoints.idParam, entityId, current);
                            assigned++;
                        } else if (original !== '' && current === '') {
                            await callEndpoint(endpoints.removeUrl, endpoints.idParam, entityId, null);
                            removed++;
                        }

                        $select.data('original', current);
                        $select.removeClass('is-dirty');
                        $select.closest('.assignment-field-row').removeClass('is-dirty');
                    } catch (xhr) {
                        const msg = (xhr.responseJSON && (xhr.responseJSON.message)) || 'Failed to save this assignment.';
                        failed.push(`${label || kind}: ${msg}`);
                    }
                }

                $triggerBtn.prop('disabled', false).html(originalBtnHtml);
                updateAllBadges();

                if (failed.length === 0) {
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true,
                        customClass: {
                            popup: 'colored-toast'
                        }
                    });
                    Toast.fire({
                        icon: 'success',
                        title: `${assigned} saved, ${removed} removed`
                    });
                } else {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Partially Saved',
                        html: `${assigned} saved, ${removed} removed.<br><br><strong>Issues:</strong><br>` + failed.join('<br>'),
                        customClass: {
                            popup: 'colored-toast'
                        }
                    });
                }
            }

            $(document).on('click', '.btn-save-class-premium', function () {
                const $card = $(this).closest('[data-class-card-id]');
                saveDirtyFields($card, $(this));
            });

            $('#btnSaveAll').on('click', function () {
                const $allDirty = $('.assign-field.is-dirty');
                if ($allDirty.length === 0) {
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 2000,
                        timerProgressBar: true,
                        customClass: {
                            popup: 'colored-toast'
                        }
                    });
                    Toast.fire({
                        icon: 'info',
                        title: 'Everything is already saved!'
                    });
                    return;
                }
                saveDirtyFields($('#classesGrid'), $(this));
            });

            // Initialize
            updateAllBadges();
        });
    </script>
@endsection

@section('js')
    <script src="{{ URL::asset('assets/js/index1.js') }}"></script>
@endsection