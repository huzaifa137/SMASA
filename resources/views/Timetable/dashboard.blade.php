<?php
use App\Http\Controllers\Helper;
use App\Helpers\PermissionHelper;
?>
@extends('layouts-side-bar.master')

@section('css')
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --brand: #5351e4;
            --brand-light: #2C29CA;
            --brand-dark: #2C29CA;
            --brand-muted: rgba(83, 81, 228, 0.08);
            --brand-gradient: linear-gradient(135deg, #5351e4, #2C29CA);
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

        /* Stats Row - Enhanced with gradient backgrounds */
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

        .stat-card-premium::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--brand-gradient);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .stat-card-premium:hover::before {
            opacity: 1;
        }

        .stat-card-premium:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 24px rgba(83, 81, 228, 0.1);
            border-color: rgba(83, 81, 228, 0.15);
        }

        .stat-card-premium:nth-child(1)::before {
            background: linear-gradient(135deg, #5351e4, #2C29CA);
        }
        .stat-card-premium:nth-child(2)::before {
            background: linear-gradient(135deg, #10b981, #059669);
        }
        .stat-card-premium:nth-child(3)::before {
            background: linear-gradient(135deg, #f59e0b, #d97706);
        }
        .stat-card-premium:nth-child(4)::before {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
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

        /* Cards - Enhanced with colored headers */
        .data-card {
            background: white;
            border-radius: 24px;
            margin-bottom: 1.5rem;
            border: 1px solid rgba(83, 81, 228, 0.08);
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
            transition: all 0.3s ease;
        }

        .data-card:hover {
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
            transform: translateY(-2px);
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
            position: relative;
            overflow: hidden;
        }

        .card-header-modern::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--brand-gradient);
            opacity: 0.5;
        }

        .card-header-modern.success::after {
            background: linear-gradient(135deg, #10b981, #059669);
        }
        .card-header-modern.warning::after {
            background: linear-gradient(135deg, #f59e0b, #d97706);
        }
        .card-header-modern.info::after {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
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
            transition: all 0.3s ease;
        }

        .data-card:hover .count-badge {
            background: var(--brand);
            color: white;
            transform: scale(1.05);
        }

        /* Timetable Item - Enhanced with hover colors */
        .tt-item {
            display: flex;
            align-items: center;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--border-light);
            gap: 1rem;
            transition: all 0.3s ease;
            position: relative;
        }

        .tt-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 3px;
            background: var(--brand-gradient);
            transform: scaleY(0);
            transition: transform 0.3s ease;
        }

        .tt-item:hover::before {
            transform: scaleY(1);
        }

        .tt-item:hover {
            background: linear-gradient(90deg, var(--brand-muted), transparent);
            transform: translateX(3px);
        }

        .tt-item:last-child {
            border-bottom: none;
        }

        .tt-badge {
            width: 48px;
            height: 48px;
            border-radius: 16px;
            background: var(--brand-gradient);
            color: white;
            font-size: 1rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            box-shadow: 0 4px 10px rgba(83, 81, 228, 0.15);
            transition: all 0.3s ease;
        }

        .tt-item:hover .tt-badge {
            transform: scale(1.05) rotate(3deg);
            box-shadow: 0 6px 14px rgba(83, 81, 228, 0.25);
        }

        .tt-badge-draft {
            background: linear-gradient(135deg, #f59e0b, #d97706);
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

        .tt-meta span {
            transition: color 0.3s ease;
        }

        .tt-item:hover .tt-meta span {
            color: var(--text-secondary);
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            padding: 0.25rem 0.7rem;
            border-radius: 99px;
            font-size: 0.65rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .tt-item:hover .status-badge {
            transform: scale(1.05);
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
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .btn-icon:hover {
            transform: translateY(-2px) scale(1.1);
        }

        .btn-icon-view {
            background: var(--info-muted);
            color: var(--info);
        }

        .btn-icon-view:hover {
            background: var(--info);
            color: white;
            box-shadow: 0 4px 8px rgba(59, 130, 246, 0.3);
        }

        .btn-icon-edit {
            background: var(--brand-muted);
            color: var(--brand);
        }

        .btn-icon-edit:hover {
            background: var(--brand);
            color: white;
            box-shadow: 0 4px 8px rgba(83, 81, 228, 0.3);
        }

        .btn-icon-activate {
            background: var(--success-muted);
            color: var(--success);
        }

        .btn-icon-activate:hover {
            background: var(--success);
            color: white;
            box-shadow: 0 4px 8px rgba(16, 185, 129, 0.3);
        }

        .btn-icon-delete {
            background: var(--danger-muted);
            color: var(--danger);
        }

        .btn-icon-delete:hover {
            background: var(--danger);
            color: white;
            box-shadow: 0 4px 8px rgba(239, 68, 68, 0.3);
        }

        /* Today's Schedule - Enhanced with colored time slots */
        .today-slot {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.9rem 1.5rem;
            border-bottom: 1px solid var(--border-light);
            transition: all 0.3s ease;
            position: relative;
        }

        .today-slot::after {
            content: '';
            position: absolute;
            right: 0;
            top: 0;
            bottom: 0;
            width: 3px;
            background: var(--brand-gradient);
            transform: scaleY(0);
            transition: transform 0.3s ease;
        }

        .today-slot:hover::after {
            transform: scaleY(1);
        }

        .today-slot:hover {
            background: linear-gradient(90deg, transparent, var(--brand-muted));
        }

        .slot-time {
            min-width: 70px;
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--brand);
            transition: color 0.3s ease;
        }

        .today-slot:hover .slot-time {
            color: var(--brand-light);
        }

        .slot-color {
            width: 4px;
            height: 40px;
            border-radius: 4px;
            flex-shrink: 0;
            transition: all 0.3s ease;
        }

        .today-slot:hover .slot-color {
            transform: scaleX(1.5);
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

        /* Period Item - Enhanced with colored indicators */
        .period-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.75rem 1.5rem;
            border-bottom: 1px solid var(--border-light);
            transition: all 0.3s ease;
            position: relative;
        }

        .period-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 3px;
            background: var(--brand-gradient);
            transform: scaleY(0);
            transition: transform 0.3s ease;
        }

        .period-item:hover::before {
            transform: scaleY(1);
        }

        .period-item:hover {
            background: linear-gradient(90deg, var(--info-muted), transparent);
            transform: translateX(3px);
        }

        .period-name {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-primary);
            transition: color 0.3s ease;
        }

        .period-item:hover .period-name {
            color: var(--brand);
        }

        .period-type {
            font-size: 0.6rem;
            background: var(--bg-surface);
            color: var(--text-muted);
            border-radius: 99px;
            padding: 0.2rem 0.6rem;
            margin-left: 0.5rem;
            transition: all 0.3s ease;
        }

        .period-item:hover .period-type {
            background: var(--brand-muted);
            color: var(--brand);
        }

        .period-time {
            font-size: 0.7rem;
            color: var(--text-muted);
            transition: color 0.3s ease;
        }

        .period-item:hover .period-time {
            color: var(--text-secondary);
        }

        .empty-state {
            text-align: center;
            padding: 3rem 1.5rem;
            color: var(--text-muted);
            transition: all 0.3s ease;
        }

        .empty-state:hover {
            background: linear-gradient(135deg, var(--brand-muted), transparent);
            transform: scale(1.01);
        }

        .empty-state i {
            font-size: 3rem;
            opacity: 0.3;
            margin-bottom: 0.75rem;
            display: block;
            transition: all 0.3s ease;
        }

        .empty-state:hover i {
            opacity: 0.5;
            color: var(--brand);
            transform: scale(1.1);
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
            .stats-row {
                grid-template-columns: repeat(2, 1fr) !important;
                gap: 0.75rem !important;
            }

            .stat-card-premium {
                padding: 1rem !important;
            }

            .stat-icon-wrapper {
                width: 40px !important;
                height: 40px !important;
            }

            .stat-value {
                font-size: 1.4rem !important;
            }

            .stat-label {
                font-size: 0.65rem !important;
            }

            .content-grid {
                gap: 1rem !important;
            }

            .card-header-modern {
                padding: 1rem !important;
            }

            .card-title {
                font-size: 0.85rem !important;
            }

            .tt-item {
                padding: 0.75rem 1rem !important;
                flex-wrap: wrap !important;
            }

            .tt-badge {
                width: 40px !important;
                height: 40px !important;
                font-size: 0.85rem !important;
            }

            .tt-name {
                font-size: 0.85rem !important;
            }

            .tt-meta {
                font-size: 0.65rem !important;
                gap: 0.3rem !important;
            }

            .status-badge {
                font-size: 0.6rem !important;
                padding: 0.2rem 0.6rem !important;
            }

            .tt-actions {
                width: 100% !important;
                justify-content: flex-end !important;
                margin-top: 0.5rem !important;
            }

            .today-slot {
                padding: 0.75rem 1rem !important;
                gap: 0.75rem !important;
            }

            .slot-time {
                min-width: 60px !important;
                font-size: 0.7rem !important;
            }

            .slot-subject {
                font-size: 0.8rem !important;
            }

            .slot-class {
                font-size: 0.65rem !important;
            }

            .period-item {
                padding: 0.75rem 1rem !important;
                flex-direction: column !important;
                align-items: flex-start !important;
                gap: 0.5rem !important;
            }

            .period-name {
                font-size: 0.8rem !important;
            }

            .period-type {
                font-size: 0.55rem !important;
                padding: 0.15rem 0.5rem !important;
            }

            .period-time {
                font-size: 0.65rem !important;
            }

            .side-app {
                padding: 1rem !important;
            }
        }

        @media (max-width: 480px) {
            .stats-row {
                grid-template-columns: 1fr !important;
            }

            .empty-state {
                padding: 2rem 1rem !important;
            }

            .empty-state i {
                font-size: 2rem !important;
            }

            .empty-state p {
                font-size: 0.8rem !important;
            }

            .btn-icon {
                width: 28px !important;
                height: 28px !important;
                font-size: 0.7rem !important;
            }
        }

        /* RPT Hero Card Styles */
        .rpt-hero-card {
            background: linear-gradient(135deg, #000000 0%, #070189 100%);
            border-radius: 20px;
            padding: 1.75rem 2.25rem;
            margin-bottom: 2.5rem;
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 4px 24px rgba(7, 1, 137, 0.3);
        }

        .rpt-hero-main {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
            margin-bottom: 1.25rem;
            padding-bottom: 1.25rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }

        .rpt-hero-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .rpt-hero-left {
            display: flex;
            align-items: center;
            gap: 1.25rem;
        }

        .rpt-hero-icon-wrapper {
            width: 56px;
            height: 56px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 1.5rem;
            flex-shrink: 0;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }

        .rpt-hero-info h4 {
            font-size: 1.35rem;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 0.15rem;
        }

        .rpt-hero-info p {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.9rem;
            margin: 0;
        }

        .rpt-hero-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 0.5rem;
            width: 100%;
        }

        .rpt-hero-btn-primary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.6rem 1.25rem;
            background: #ffffff;
            color: #070189;
            border-radius: 10px;
            font-size: 0.85rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
            border: none;
            text-align: center;
        }

        .rpt-hero-btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 14px rgba(255, 255, 255, 0.25);
            color: #070189;
            text-decoration: none;
        }

        .rpt-hero-btn-secondary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.6rem 1.1rem;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 10px;
            color: rgba(255, 255, 255, 0.9);
            font-size: 0.8rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s ease;
            backdrop-filter: blur(10px);
            text-align: center;
        }

        .rpt-hero-btn-secondary:hover {
            background: rgba(255, 255, 255, 0.2);
            color: #ffffff;
            text-decoration: none;
            border-color: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
        }

        .rpt-hero-meta {
            padding-top: 0.25rem;
        }

        .rpt-meta-items {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 0.5rem 0.75rem;
            width: 100%;
        }

        .rpt-meta-item {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            color: rgba(255, 255, 255, 0.65);
            font-size: 0.78rem;
            padding: 0.35rem 0.75rem;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 99px;
            border: 1px solid rgba(255, 255, 255, 0.06);
            text-align: center;
            transition: all 0.3s ease;
        }

        .rpt-meta-item:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-1px);
        }

        .rpt-meta-item i {
            font-size: 0.7rem;
            color: rgba(255, 255, 255, 0.4);
        }

        .rpt-meta-highlight {
            background: rgba(102, 126, 234, 0.15);
            border-color: rgba(102, 126, 234, 0.2);
            color: rgba(255, 255, 255, 0.9);
        }

        .rpt-meta-highlight i {
            color: #818cf8;
        }

        @media (max-width: 768px) {
            .rpt-hero-card {
                padding: 1.25rem;
            }
            
            .rpt-hero-top {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .rpt-hero-actions {
                grid-template-columns: 1fr 1fr;
            }
            
            .rpt-meta-items {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 480px) {
            .rpt-hero-left {
                flex-direction: column;
                text-align: center;
                width: 100%;
            }
            
            .rpt-hero-info h4 {
                font-size: 1.2rem;
            }
            
            .rpt-hero-info p {
                font-size: 0.8rem;
            }
            
            .rpt-hero-actions {
                grid-template-columns: 1fr;
            }
            
            .rpt-meta-items {
                grid-template-columns: 1fr;
            }
            
            .rpt-hero-btn-primary,
            .rpt-hero-btn-secondary {
                width: 100%;
                justify-content: center;
            }
        }

        /* Additional color enhancements */
        .bg-gradient-brand {
            background: var(--brand-gradient);
        }

        .text-gradient-brand {
            background: var(--brand-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* View Full Weekly Schedule button enhancement */
        .btn-weekly-schedule {
            background: var(--brand-gradient) !important;
            color: white !important;
            border: none !important;
            transition: all 0.3s ease !important;
        }

        .btn-weekly-schedule:hover {
            transform: translateY(-2px) !important;
            box-shadow: 0 6px 14px rgba(83, 81, 228, 0.3) !important;
            color: white !important;
        }

        /* Manage button enhancement */
        .btn-manage {
            background: var(--brand-gradient) !important;
            color: white !important;
            border: none !important;
            transition: all 0.3s ease !important;
        }

        .btn-manage:hover {
            transform: translateY(-2px) !important;
            box-shadow: 0 4px 10px rgba(83, 81, 228, 0.3) !important;
            color: white !important;
        }
    </style>
@endsection

@section('content')
    <div class="side-app" style="padding: 1.5rem;">

        {{-- New Dark Gradient Header --}}
        <div class="rpt-hero-card">
            <div class="rpt-hero-main">
                <div class="rpt-hero-top">
                    <div class="rpt-hero-left">
                        <div class="rpt-hero-icon-wrapper">
                            <i class="fas fa-calendar-week"></i>
                        </div>
                        <div class="rpt-hero-info">
                            <h4>Timetable Manager</h4>
                            <p>Create, manage, and publish class timetables for your school</p>
                        </div>
                    </div>
                </div>
                <div class="rpt-hero-actions">
                    @if(PermissionHelper::canFeature('create_timetable'))
                    <a href="{{ route('timetable.create') }}" class="rpt-hero-btn-primary">
                        <i class="fas fa-plus-circle"></i> New Timetable
                    </a>
                    @endif
                    <a href="{{ route('timetable.periods.index') }}" class="rpt-hero-btn-secondary">
                        <i class="fas fa-clock"></i> Periods
                    </a>
                    <a href="{{ route('timetable.master') }}" class="rpt-hero-btn-secondary">
                        <i class="fas fa-th-large"></i> General
                    </a>
                    <a href="{{ route('timetable.teachers-summary') }}" class="rpt-hero-btn-secondary">
                        <i class="fas fa-chalkboard-teacher"></i> Teachers
                    </a>
                    @if(PermissionHelper::canFeature('view_teacher_schedule'))
                    <a href="{{ route('timetable.teacher') }}" class="rpt-hero-btn-secondary">
                        <i class="fas fa-chalkboard-user"></i> My Schedule
                    </a>
                    @endif
                </div>
            </div>
            <div class="rpt-hero-meta">
                <div class="rpt-meta-items">
                    <div class="rpt-meta-item rpt-meta-highlight">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Academic Scheduling</span>
                    </div>
                    <div class="rpt-meta-item">
                        <i class="fas fa-clock"></i>
                        <span>{{ $allTimetables->count() }} Total Timetables</span>
                    </div>
                    <div class="rpt-meta-item" style="color: rgba(16, 185, 129, 0.9);">
                        <i class="fas fa-check-circle"></i>
                        <span>{{ $activeTimetables->count() }} Active</span>
                    </div>
                    <div class="rpt-meta-item" style="color: rgba(245, 158, 11, 0.9);">
                        <i class="fas fa-pen-fancy"></i>
                        <span>{{ $draftTimetables->count() }} Draft</span>
                    </div>
                    <div class="rpt-meta-item" style="color: rgba(59, 130, 246, 0.9);">
                        <i class="fas fa-hourglass-half"></i>
                        <span>{{ $periods->count() }} Periods</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Stats Row --}}
<div class="stats-row">
    <div class="stat-card-premium" style="background: linear-gradient(135deg, #f8f9ff, #eef2ff); border-color: rgba(83, 81, 228, 0.15);">
        <div class="stat-icon-wrapper" style="background: linear-gradient(135deg, #5351e4, #2C29CA);">
            <i class="fas fa-calendar-alt" style="color: white; font-size: 1.2rem;"></i>
        </div>
        <div class="stat-value" style="color: #2C29CA;">{{ $allTimetables->count() }}</div>
        <div class="stat-label" style="color: #4a4a8a; font-weight: 600;">Total Timetables</div>
        <div style="position: absolute; bottom: 0; left: 0; right: 0; height: 3px; background: linear-gradient(135deg, #5351e4, #2C29CA);"></div>
    </div>
    <div class="stat-card-premium" style="background: linear-gradient(135deg, #f0fdf4, #dcfce7); border-color: rgba(16, 185, 129, 0.15);">
        <div class="stat-icon-wrapper" style="background: linear-gradient(135deg, #10b981, #059669);">
            <i class="fas fa-check-circle" style="color: white; font-size: 1.2rem;"></i>
        </div>
        <div class="stat-value" style="color: #059669;">{{ $activeTimetables->count() }}</div>
        <div class="stat-label" style="color: #065f46; font-weight: 600;">Active Timetables</div>
        <div style="position: absolute; bottom: 0; left: 0; right: 0; height: 3px; background: linear-gradient(135deg, #10b981, #059669);"></div>
    </div>
    <div class="stat-card-premium" style="background: linear-gradient(135deg, #fffbeb, #fef3c7); border-color: rgba(245, 158, 11, 0.15);">
        <div class="stat-icon-wrapper" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
            <i class="fas fa-pen-fancy" style="color: white; font-size: 1.2rem;"></i>
        </div>
        <div class="stat-value" style="color: #d97706;">{{ $draftTimetables->count() }}</div>
        <div class="stat-label" style="color: #92400e; font-weight: 600;">In Draft</div>
        <div style="position: absolute; bottom: 0; left: 0; right: 0; height: 3px; background: linear-gradient(135deg, #f59e0b, #d97706);"></div>
    </div>
    <div class="stat-card-premium" style="background: linear-gradient(135deg, #eff6ff, #dbeafe); border-color: rgba(59, 130, 246, 0.15);">
        <div class="stat-icon-wrapper" style="background: linear-gradient(135deg, #3b82f6, #2563eb);">
            <i class="fas fa-hourglass-half" style="color: white; font-size: 1.2rem;"></i>
        </div>
        <div class="stat-value" style="color: #2563eb;">{{ $periods->count() }}</div>
        <div class="stat-label" style="color: #1e40af; font-weight: 600;">Period Definitions</div>
        <div style="position: absolute; bottom: 0; left: 0; right: 0; height: 3px; background: linear-gradient(135deg, #3b82f6, #2563eb);"></div>
    </div>
</div>

        <div class="content-grid">
            {{-- Left Column: Timetables --}}
            <div>
                {{-- Active Timetables --}}
                <div class="data-card">
                    <div class="card-header-modern success" style="background: #2C29CA; border-bottom: none;">
    <div class="card-title" style="color: #ffffff;">
        <i class="fas fa-check-circle" style="color: #ffffff;"></i>
        Active Timetables
    </div>
    <span class="count-badge" style="background: rgba(255, 255, 255, 0.2); color: #ffffff;">{{ $activeTimetables->count() }}</span>
</div>

<style>
    /* Update the card-header-modern styles */
.card-header-modern {
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid var(--border-light);
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1rem;
    background: var(--bg-surface);
    position: relative;
    overflow: hidden;
}

/* Remove the after pseudo-element for colored headers since we're using inline styles */
.card-header-modern::after {
    display: none;
}

/* Or keep it for non-colored headers */
.card-header-modern:not([style*="background"])::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: var(--brand-gradient);
    opacity: 0.5;
}

/* Update count-badge for dark backgrounds */
.count-badge {
    background: var(--brand-muted);
    color: var(--brand);
    border-radius: 99px;
    padding: 0.2rem 0.7rem;
    font-size: 0.7rem;
    font-weight: 600;
    transition: all 0.3s ease;
}

/* Card title for dark backgrounds */
.card-header-modern[style*="background"] .card-title {
    color: #ffffff !important;
}

.card-header-modern[style*="background"] .card-title i {
    color: #ffffff !important;
}

.card-header-modern[style*="background"] .count-badge {
    background: rgba(255, 255, 255, 0.2) !important;
    color: #ffffff !important;
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
    transform: translateY(-5px);
    box-shadow: 0 12px 24px rgba(0, 0, 0, 0.08);
}

.stat-card-premium:hover .stat-icon-wrapper {
    transform: scale(1.1) rotate(5deg);
}

.stat-icon-wrapper {
    width: 48px;
    height: 48px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 0.75rem;
    transition: all 0.3s ease;
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
</style>
                    @forelse($activeTimetables as $tt)
                        <div class="tt-item">
                            <div class="tt-badge">
                                {{ strtoupper(substr($tt->class_name ?? '', 0, 2)) }}
                            </div>
                            <div class="tt-info">
                                <div class="tt-name">{{ $tt->name ?? ($tt->class_name . ' – ' . $tt->stream_name) }}</div>
                                <div class="tt-meta">
                                    <span><i class="fas fa-school me-1"></i> {{ $tt->class_name }}</span>
                                    <span><i class="fas fa-users me-1"></i> {{ $tt->stream_name }}</span>
                                    <span><i class="fas fa-layer-group me-1"></i> {{ $tt->slot_count }} slots</span>
                                </div>
                            </div>
                            <span class="status-badge status-active">
                                <i class="fas fa-circle" style="font-size: 0.5rem;"></i> Active
                            </span>
                            <div class="tt-actions">
                                <a href="{{ route('timetable.view', $tt->id) }}" class="btn-icon btn-icon-view" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if(PermissionHelper::canFeature('edit_timetable'))
                                <a href="{{ route('timetable.edit', $tt->id) }}" class="btn-icon btn-icon-edit" title="Edit">
                                    <i class="fas fa-pencil-alt"></i>
                                </a>
                                @endif
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
                        <div class="card-header-modern warning">
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
                                        <span><i class="fas fa-school me-1"></i> {{ $tt->class_name }}</span>
                                        <span><i class="fas fa-users me-1"></i>{{ $tt->stream_name }}</span>
                                        <span><i class="fas fa-layer-group me-1"></i> {{ $tt->slot_count }} slots</span>
                                    </div>
                                </div>
                                <span class="status-badge status-draft">
                                    <i class="fas fa-circle" style="font-size: 0.5rem;"></i> Draft
                                </span>
                                <div class="tt-actions">
                                    @if(PermissionHelper::canFeature('edit_timetable'))
                                    <a href="{{ route('timetable.edit', $tt->id) }}" class="btn-icon btn-icon-edit" title="Edit">
                                        <i class="fas fa-pencil-alt"></i>
                                    </a>
                                    <button onclick="quickActivate({{ $tt->id }})" class="btn-icon btn-icon-activate"
                                        title="Activate">
                                        <i class="fas fa-check-circle"></i>
                                    </button>
                                    @endif
                                    @if(PermissionHelper::canFeature('delete_timetable'))
                                    <button onclick="confirmDelete({{ $tt->id }})" class="btn-icon btn-icon-delete" title="Delete">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                    @endif
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
                    <div class="card-header-modern" style="background: #2C29CA; border-bottom: none;">
    <div class="card-title" style="color: #ffffff;">
        <i class="fas fa-calendar-day" style="color: #ffffff;"></i>
        Today's Schedule
    </div>
    <span style="font-size: 0.7rem; color: rgba(255, 255, 255, 0.8);">
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
                                <div class="slot-color" style="background: {{ $slot->color ?? '#5351e4' }}"></div>
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
                        <a href="{{ route('timetable.teacher') }}" class="btn btn-sm w-100 btn-weekly-schedule">
                            <i class="fas fa-calendar-week me-2"></i> View Full Weekly Schedule
                        </a>
                    </div>
                </div>

                {{-- Period Definitions --}}
                <div class="data-card">
                    <div class="card-header-modern info" style="background: #2C29CA; border-bottom: none;">
    <div class="card-title" style="color: #ffffff;">
        <i class="fas fa-hourglass-half" style="color: #ffffff;"></i>
        Period Definitions
    </div>
    <a href="{{ route('timetable.periods.index') }}" class="btn btn-sm" style="background: rgba(255, 255, 255, 0.2); color: #ffffff; border: 1px solid rgba(255, 255, 255, 0.3); border-radius: 10px; padding: 0.3rem 0.8rem; font-size: 0.7rem; font-weight: 600; transition: all 0.3s ease; text-decoration: none;">
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
                                    {{ \Carbon\Carbon::parse($period->start_time)->format('h:i A') }} –
                                    {{ \Carbon\Carbon::parse($period->end_time)->format('h:i A') }}
                                </div>
                            </div>
                        @endforeach
                        @if($periods->isEmpty())
                            <div class="empty-state">
                                <i class="fas fa-clock"></i>
                                <p>No periods defined. <a href="{{ route('timetable.periods.index') }}"
                                        style="color: var(--brand); font-weight: 600;">Add periods</a> first.</p>
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

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function quickActivate(id) {
        Swal.fire({
            title: 'Activate Timetable?',
            text: 'This timetable will be visible to all teachers.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#5351e4',
            confirmButtonText: 'Yes, Activate',
            cancelButtonText: 'Cancel'
        }).then(function (result) {
            if (!result.isConfirmed) return;

            Swal.fire({
                title: 'Activating...',
                allowOutsideClick: false,
                didOpen: function () { Swal.showLoading(); }
            });

            $.ajax({
                url: '/timetable/manage/' + id + '/status',
                method: 'PATCH',
                data: {
                    status: 'active',
                    _token: '{{ csrf_token() }}'
                },
                success: function (data) {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Activated!',
                            text: data.message || 'Timetable has been activated successfully.',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(function () {
                            window.location.reload();
                        });
                    } else {
                        let detail = data.message || 'Could not activate.';
                        if (data.conflicts && data.conflicts.length) {
                            detail += '<br><br><ul style="text-align:left; font-size:0.85rem;">';
                            data.conflicts.forEach(function (c) {
                                detail += '<li><strong>' + c.teacher + '</strong> — ' + c.day + ', ' + c.period + '</li>';
                            });
                            detail += '</ul>';
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Cannot Activate',
                            html: detail
                        });
                    }
                },
                error: function (data) {
                    $('body').html(data.responseText);
                }
            });
        });
    }

    function confirmDelete(id) {
        Swal.fire({
            title: 'Delete Timetable?',
            text: 'This action cannot be undone. All slots will be permanently deleted.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'Yes, Delete',
            cancelButtonText: 'Cancel'
        }).then(function (result) {
            if (!result.isConfirmed) return;

            Swal.fire({
                title: 'Deleting...',
                allowOutsideClick: false,
                didOpen: function () { Swal.showLoading(); }
            });

            $.ajax({
                url: '/timetable/manage/' + id,
                method: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function (data) {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
                            text: data.message || 'Timetable has been deleted.',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(function () {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message || 'Could not delete.'
                        });
                    }
                },
                error: function (data) {
                    $('body').html(data.responseText);
                }
            });
        });
    }
</script>