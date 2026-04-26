<?php
use App\Http\Controllers\Helper;
?>
@extends('layouts-side-bar.master')

@section('css')
    <link href="{{ URL::asset('assets/plugins/datatable/dataTables.bootstrap4.min.css') }}" rel="stylesheet" />
    <style>
        /* ── Hero Section ─────────────────────────────────────────────────── */
        .dashboard-hero {
            background: linear-gradient(135deg, #2C29CA 0%, #5351e4 60%, #7c7aec 100%);
            border-radius: 0 0 2rem 2rem;
            padding: 2rem 2rem 3rem;
            margin-bottom: -1.5rem;
            position: relative;
            overflow: hidden;
        }

        .dashboard-hero::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            border-radius: 50%;
        }

        .dashboard-hero::after {
            content: '';
            position: absolute;
            right: 2rem;
            bottom: 1rem;
            width: 140px;
            height: 140px;
            background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='rgba(255,255,255,0.08)'%3E%3Cpath d='M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zM9 17H7v-7h2v7zm4 0h-2V7h2v10zm4 0h-2v-4h2v4z'/%3E%3C/svg%3E") no-repeat center;
            background-size: contain;
            opacity: 0.15;
        }

        /* ── Form Card Style (matching create page) ────────────────────────── */
        .form-card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 4px 24px rgba(44, 41, 202, .10);
            background: #fff;
        }

        .section-header {
            display: flex;
            align-items: center;
            gap: .6rem;
            font-weight: 700;
            font-size: .85rem;
            letter-spacing: .06em;
            text-transform: uppercase;
            color: #2C29CA;
            margin-bottom: 1.2rem;
            padding-bottom: .5rem;
            border-bottom: 2px solid #ede9ff;
        }

        .section-header i {
            font-size: 1rem;
        }

        .step-badge {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: #2C29CA;
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: .75rem;
            font-weight: 700;
            flex-shrink: 0;
        }

        /* ── Statistics Cards ──────────────────────────────────────────────── */
        .stat-card {
            background: #fff;
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: 0 4px 24px rgba(44, 41, 202, .08);
            transition: all .25s ease;
            cursor: pointer;
            border: 2px solid transparent;
            position: relative;
            overflow: hidden;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 32px rgba(44, 41, 202, .15);
            border-color: #5351e4;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #2C29CA, #7c7aec);
            transform: scaleX(0);
            transition: transform .3s ease;
        }

        .stat-card:hover::before {
            transform: scaleX(1);
        }

        .stat-icon-box {
            width: 50px;
            height: 50px;
            border-radius: .6rem;
            background: #ede9ff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            color: #5351e4;
            margin-bottom: 1rem;
            transition: all .25s;
        }

        .stat-card:hover .stat-icon-box {
            background: #2C29CA;
            color: #fff;
        }

        .stat-value-lg {
            font-size: 2.2rem;
            font-weight: 800;
            color: #1a1a2e;
            line-height: 1.1;
        }

        .stat-label-sm {
            font-size: .82rem;
            color: #6c757d;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: .04em;
            margin-top: .3rem;
        }

        .stat-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            padding: .2rem .7rem;
            border-radius: 99px;
            font-size: .7rem;
            font-weight: 600;
        }

        /* ── Timeline ──────────────────────────────────────────────────────── */
        .timeline-item {
            padding: 1rem;
            margin-bottom: .8rem;
            border-radius: .8rem;
            background: #f8f7ff;
            border-left: 4px solid;
            transition: all .2s;
            cursor: pointer;
            border: 1px solid #ede9ff;
            border-left-width: 4px;
        }

        .timeline-item:hover {
            background: #ede9ff;
            transform: translateX(5px);
            box-shadow: 0 4px 12px rgba(44, 41, 202, .1);
        }

        .timeline-date-box {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            padding: .3rem .8rem;
            background: #fff;
            border-radius: .5rem;
            font-weight: 600;
            font-size: .8rem;
            color: #2C29CA;
            border: 1px solid #ede9ff;
        }

        /* ── Calendar ──────────────────────────────────────────────────────── */
        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: .4rem;
        }

        .cal-day-header {
            text-align: center;
            font-size: .7rem;
            color: #6c757d;
            font-weight: 600;
            text-transform: uppercase;
            padding: .3rem 0;
        }

        .cal-date {
            aspect-ratio: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: .5rem;
            font-size: .85rem;
            font-weight: 500;
            cursor: pointer;
            transition: all .2s;
        }

        .cal-date:hover {
            background: #ede9ff;
        }

        .cal-date.has-exam {
            background: linear-gradient(135deg, #2C29CA, #5351e4);
            color: #fff;
            font-weight: 700;
            position: relative;
        }

        .cal-date.has-exam::after {
            content: '';
            position: absolute;
            bottom: 3px;
            width: 4px;
            height: 4px;
            background: #fff;
            border-radius: 50%;
        }

        .cal-date.today {
            border: 2px solid #5351e4;
        }

        /* ── Status Pills ──────────────────────────────────────────────────── */
        .status-pill {
            display: inline-block;
            padding: .2rem .8rem;
            border-radius: 99px;
            font-size: .72rem;
            font-weight: 600;
        }

        .status-draft {
            background: #e2e3e5;
            color: #495057;
        }

        .status-active {
            background: #d1f2eb;
            color: #0a7a4a;
        }

        .status-marks_entry {
            background: #fff3cd;
            color: #856404;
        }

        .status-closed {
            background: #f8d7da;
            color: #721c24;
        }

        .status-results_released {
            background: #ede9ff;
            color: #2C29CA;
        }

        /* ── Table ─────────────────────────────────────────────────────────── */
        .exam-table {
            width: 100%;
            border-collapse: collapse;
        }

        .exam-table th {
            padding: .8rem 1rem;
            font-size: .78rem;
            color: #FFF;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .04em;
            border-bottom: 2px solid #ede9ff;
            background: #2C29CA;
        }

        .exam-table td {
            padding: .8rem 1rem;
            border-bottom: 1px solid #f0efff;
            font-size: .85rem;
        }

        .exam-table tbody tr {
            cursor: pointer;
            transition: all .2s;
        }

        .exam-table tbody tr:hover {
            background: #f8f7ff;
        }

        /* ── Progress Circle ───────────────────────────────────────────────── */
        .progress-circle {
            position: relative;
            width: 130px;
            height: 130px;
            margin: 0 auto;
        }

        .progress-circle svg {
            transform: rotate(-90deg);
        }

        .progress-bg {
            fill: none;
            stroke: #ede9ff;
            stroke-width: 10;
        }

        .progress-fill {
            fill: none;
            stroke: url(#progressGradient);
            stroke-width: 10;
            stroke-linecap: round;
            transition: stroke-dashoffset .6s ease;
        }

        /* ── Buttons ───────────────────────────────────────────────────────── */
        .btn-primary-grad {
            background: linear-gradient(135deg, #2C29CA, #5351e4);
            border: none;
            border-radius: .6rem;
            padding: .7rem 2rem;
            font-weight: 600;
            color: #fff;
            transition: all .2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: .5rem;
        }

        .btn-primary-grad:hover {
            opacity: .9;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(44, 41, 202, .3);
            color: #fff;
        }

        .btn-outline-purple {
            background: #fff;
            border: 2px solid #ede9ff;
            color: #2C29CA;
            border-radius: .6rem;
            padding: .5rem 1.2rem;
            font-weight: 600;
            font-size: .85rem;
            transition: all .2s;
            cursor: pointer;
        }

        .btn-outline-purple:hover,
        .btn-outline-purple.active {
            background: #ede9ff;
            border-color: #5351e4;
        }

        /* ── Tooltip ───────────────────────────────────────────────────────── */
        .info-tip {
            color: #6c757d;
            cursor: help;
            transition: color .2s;
        }

        .info-tip:hover {
            color: #2C29CA;
        }

        /* ── Responsive ────────────────────────────────────────────────────── */
        @media (max-width: 992px) {
            .stat-card {
                padding: 1.2rem;
            }

            .stat-value-lg {
                font-size: 1.8rem;
            }
        }

        @media (max-width: 768px) {
            .dashboard-hero {
                padding: 1.5rem 1rem 2rem;
                border-radius: 0 0 1.5rem 1.5rem;
            }
        }

        /* ── Search Input ──────────────────────────────────────────────────── */
        .search-input {
            border: 1px solid #e0ddff;
            border-radius: .5rem;
            padding: .5rem 1rem;
            font-size: .85rem;
            transition: all .2s;
            width: 250px;
        }

        .search-input:focus {
            outline: none;
            border-color: #5351e4;
            box-shadow: 0 0 0 3px rgba(83, 81, 228, .1);
        }

        /* ── Recent Activity ────────────────────────────────────────────────── */
        .activity-feed {
            position: relative;
            padding-left: 0;
        }

        .activity-item-new {
            display: flex;
            gap: 1rem;
            padding: 0.85rem 1rem;
            margin-bottom: 0.5rem;
            border-radius: 0.75rem;
            transition: all 0.25s ease;
            cursor: pointer;
            position: relative;
            background: #fafbff;
            border: 1px solid #f0efff;
        }

        .activity-item-new:hover {
            background: #f8f7ff;
            border-color: #d4d0ff;
            transform: translateX(4px);
            box-shadow: 0 4px 12px rgba(44, 41, 202, 0.08);
        }

        .activity-item-new:last-child {
            margin-bottom: 0;
        }

        .activity-icon-circle {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            flex-shrink: 0;
            transition: all 0.25s ease;
        }

        .activity-item-new:hover .activity-icon-circle {
            transform: scale(1.1);
        }

        .activity-icon-draft {
            background: #f1f3f5;
            color: #6c757d;
        }

        .activity-icon-active {
            background: #d3f4e4;
            color: #0a7a4a;
        }

        .activity-icon-marks_entry {
            background: #fff4de;
            color: #f59e0b;
        }

        .activity-icon-closed {
            background: #ffe0e0;
            color: #ef4444;
        }

        .activity-icon-results_released {
            background: #ede9ff;
            color: #2C29CA;
        }

        .activity-content-new {
            flex: 1;
            min-width: 0;
        }

        .activity-title-new {
            font-weight: 600;
            font-size: 0.82rem;
            color: #1a1a2e;
            margin-bottom: 0.2rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .activity-meta-new {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 0.7rem;
            color: #94a3b8;
        }

        .activity-time-new {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }

        .activity-badge-new {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.15rem 0.5rem;
            border-radius: 99px;
            font-size: 0.65rem;
            font-weight: 600;
        }

        .activity-empty-state {
            text-align: center;
            padding: 2rem 1rem;
            color: #94a3b8;
        }

        .activity-empty-state i {
            font-size: 2rem;
            margin-bottom: 0.5rem;
            opacity: 0.4;
            display: block;
        }

        .activity-empty-state p {
            font-size: 0.8rem;
            margin: 0;
        }

        /* Pulse animation for live indicator */
        @keyframes activityPulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }
        }

        .activity-live-indicator {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #10B981;
            display: inline-block;
            animation: activityPulse 2s ease-in-out infinite;
        }

        /* ── Enhanced Table ─────────────────────────────────────────────────── */
        .table-container-card {
            background: #fff;
            border-radius: 1rem;
            overflow: hidden;
        }

        .table-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.25rem 1.5rem;
            background: linear-gradient(135deg, #fafbff 0%, #f6f7ff 100%);
            border-bottom: 2px solid #ede9ff;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .table-toolbar-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .table-toolbar-badge {
            background: #ede9ff;
            color: #2C29CA;
            padding: 0.3rem 0.8rem;
            border-radius: 99px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .search-box {
            position: relative;
            min-width: 260px;
        }

        .search-box-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 0.85rem;
            pointer-events: none;
            transition: color 0.2s;
        }

        .search-input-enhanced {
            width: 100%;
            padding: 10px 14px 10px 38px;
            border: 1.5px solid #e2e8f0;
            border-radius: 12px;
            font-size: 0.82rem;
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: #1a1a2e;
            background: #ffffff;
            transition: all 0.25s ease;
            outline: none;
        }

        .search-input-enhanced:focus {
            border-color: #5351e4;
            box-shadow: 0 0 0 3px rgba(83, 81, 228, 0.08);
        }

        .search-input-enhanced:focus+.search-box-icon,
        .search-input-enhanced:focus~.search-box-icon {
            color: #5351e4;
        }

        .search-input-enhanced::placeholder {
            color: #b0b8c4;
        }

        .exam-table-enhanced {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .exam-table-enhanced thead th {
            padding: 1rem 1.25rem;
            font-size: 0.72rem;
            font-weight: 700;
            color: #FFF;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            background: #3431ca;
            border-bottom: 2px solid #ede9ff;
            white-space: nowrap;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .exam-table-enhanced thead th:first-child {
            padding-left: 1.5rem;
            border-radius: 0;
        }

        .exam-table-enhanced thead th:last-child {
            padding-right: 1.5rem;
        }

        .exam-table-enhanced tbody td {
            padding: 0.9rem 1.25rem;
            border-bottom: 1px solid #f0efff;
            font-size: 0.84rem;
            color: #1a1a2e;
            transition: all 0.2s ease;
            vertical-align: middle;
        }

        .exam-table-enhanced tbody td:first-child {
            padding-left: 1.5rem;
        }

        .exam-table-enhanced tbody td:last-child {
            padding-right: 1.5rem;
        }

        .exam-table-enhanced tbody tr {
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
        }

        .exam-table-enhanced tbody tr:hover {
            background: linear-gradient(90deg, #f8f7ff 0%, #fafbff 100%);
            transform: translateX(2px);
        }

        .exam-table-enhanced tbody tr:hover td {
            border-bottom-color: #d4d0ff;
        }

        .exam-table-enhanced tbody tr:last-child td {
            border-bottom: none;
        }

        /* Row Number */
        .row-number {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            background: linear-gradient(135deg, #ede9ff 0%, #f6f4ff 100%);
            color: #2C29CA;
            border-radius: 8px;
            font-weight: 700;
            font-size: 0.78rem;
            font-family: 'Courier New', monospace;
            transition: all 0.3s ease;
        }

        .exam-table-enhanced tbody tr:hover .row-number {
            background: linear-gradient(135deg, #2C29CA 0%, #5351e4 100%);
            color: #ffffff;
            transform: scale(1.1);
        }

        /* Exam Name Cell */
        .exam-name-cell {
            display: flex;
            flex-direction: column;
            gap: 0.2rem;
        }

        .exam-name-text {
            font-weight: 600;
            color: #1a1a2e;
            font-size: 0.85rem;
            line-height: 1.3;
        }

        .exam-name-sub {
            font-size: 0.7rem;
            color: #94a3b8;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        .exam-name-sub i {
            font-size: 0.6rem;
            color: #2C29CA;
        }

        /* Term Badge */
        .term-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.25rem 0.65rem;
            background: #f0f4ff;
            color: #4a6cf7;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 600;
            border: 1px solid #e0e7ff;
        }

        .term-badge i {
            font-size: 0.65rem;
        }

        /* Period Display */
        .period-display {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.8rem;
        }

        .period-date {
            font-weight: 500;
            color: #1a1a2e;
        }

        .period-arrow {
            color: #2C29CA;
            font-size: 0.65rem;
        }

        .period-duration {
            font-size: 0.68rem;
            color: #94a3b8;
            background: #f8f7ff;
            padding: 0.15rem 0.5rem;
            border-radius: 6px;
            font-weight: 500;
        }

        /* Status Pill Enhanced */
        .status-pill-enhanced {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.3rem 0.75rem;
            border-radius: 99px;
            font-size: 0.7rem;
            font-weight: 600;
            white-space: nowrap;
            border: 1px solid transparent;
            transition: all 0.2s ease;
        }

        .status-pill-enhanced i {
            font-size: 0.6rem;
        }

        .exam-table-enhanced tbody tr:hover .status-pill-enhanced {
            transform: scale(1.05);
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 0.4rem;
            opacity: 0.85;
            transition: opacity 0.2s ease;
        }

        .exam-table-enhanced tbody tr:hover .action-buttons {
            opacity: 1;
        }

        .btn-action {
            width: 34px;
            height: 34px;
            border-radius: 10px;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
        }

        .btn-action:hover {
            transform: translateY(-2px);
        }

        .btn-action:active {
            transform: translateY(0);
        }

        .btn-action-view {
            background: #2C29CA;
            color: #FFF;
        }

        .btn-action-view:hover {
            background: #2C29CA;
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(44, 41, 202, 0.3);
        }

        .btn-action-edit {
            background: #d97706;
            color: #fff8e6;
        }

        .btn-action-edit:hover {
            background: #d97706;
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
        }

        .action-btn-delete {
            background: #fff8e6;
            color: red;
        }

        .action-btn-delete:hover {
            background: red;
            color: #ffffff;
            box-shadow: 0 4px 12px #f80202fd;
        }

        .btn-action-marks {
            background: #0ea5e9;
            color: #FFF;
        }

        .btn-action-marks:hover {
            background: #0ea5e9;
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(14, 165, 233, 0.3);
        }

        /* Tooltip for action buttons */
        .btn-action[title] {
            position: relative;
        }

        /* Empty State */
        .empty-state-enhanced {
            text-align: center;
            padding: 4rem 2rem;
        }

        .empty-state-enhanced .empty-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 1.5rem;
            background: linear-gradient(135deg, #ede9ff 0%, #f6f4ff 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: #2C29CA;
            opacity: 0.6;
        }

        .empty-state-enhanced h4 {
            color: #1a1a2e;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .empty-state-enhanced p {
            color: #94a3b8;
            font-size: 0.85rem;
            margin-bottom: 1.5rem;
        }

        /* Pagination info */
        .table-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 1.5rem;
            background: #fafbff;
            border-top: 2px solid #ede9ff;
            font-size: 0.78rem;
            color: #6c757d;
        }

        .table-footer-count {
            font-weight: 600;
            color: #2C29CA;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .table-toolbar {
                flex-direction: column;
                align-items: stretch;
            }

            .search-box {
                min-width: 100%;
            }

            .exam-table-enhanced thead th,
            .exam-table-enhanced tbody td {
                padding: 0.7rem 0.75rem;
                font-size: 0.75rem;
            }
        }

        /* ── Responsive Table Container ─────────────────────────────────────────── */
        .table-responsive-wrapper {
            width: 100%;
            overflow-x: auto;
            overflow-y: hidden;
            -webkit-overflow-scrolling: touch;
            scroll-behavior: smooth;
            position: relative;
        }

        .table-responsive-wrapper::-webkit-scrollbar {
            height: 6px;
            -webkit-appearance: none;
        }

        .table-responsive-wrapper::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .table-responsive-wrapper::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #2C29CA, #5351e4);
            border-radius: 10px;
        }

        .table-responsive-wrapper::-webkit-scrollbar-thumb:hover {
            background: #2C29CA;
        }

        /* Scroll indicator for better UX on tablets */
        .scroll-indicator {
            display: none;
            text-align: center;
            padding: 8px 0;
            font-size: 0.7rem;
            color: #2C29CA;
            background: linear-gradient(90deg, transparent, #ede9ff, transparent);
            margin-top: 4px;
            border-radius: 20px;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .scroll-indicator i {
            margin: 0 4px;
            font-size: 0.6rem;
        }

        .scroll-indicator.visible {
            opacity: 1;
        }

        /* Show indicator only on devices that need scrolling */
        @media (max-width: 992px) {
            .scroll-indicator {
                display: block;
            }
        }

        /* Enhanced table for better readability on all devices */
        .exam-table-enhanced {
            min-width: 780px;
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            white-space: nowrap;
        }

        /* On smaller tablets, allow text wrapping for exam names */
        @media (max-width: 992px) and (min-width: 768px) {
            .exam-table-enhanced {
                min-width: auto;
                white-space: normal;
            }

            .exam-name-cell {
                min-width: 180px;
            }

            .period-display {
                flex-wrap: wrap;
                gap: 0.3rem;
            }
        }

        /* On mobile phones, ensure minimum width for horizontal scroll */
        @media (max-width: 767px) {
            .exam-table-enhanced {
                min-width: 680px;
            }

            .exam-name-cell {
                min-width: 160px;
            }
        }

        /* Make action buttons easier to tap on touch devices */
        @media (max-width: 992px) {
            .btn-action {
                width: 40px;
                height: 40px;
                font-size: 0.9rem;
            }

            .action-buttons {
                gap: 0.5rem;
            }

            .status-pill-enhanced {
                padding: 0.35rem 0.85rem;
                font-size: 0.72rem;
                white-space: nowrap;
            }

            .term-badge {
                white-space: nowrap;
            }

            .period-date {
                font-size: 0.75rem;
            }

            .period-duration {
                font-size: 0.65rem;
                white-space: nowrap;
            }
        }

        /* Improved responsive toolbar */
        @media (max-width: 768px) {
            .table-toolbar {
                flex-direction: column;
                align-items: stretch;
                padding: 1rem;
                gap: 0.75rem;
            }

            .table-toolbar-info {
                flex-wrap: wrap;
                justify-content: center;
            }

            .search-box {
                min-width: auto;
                width: 100%;
            }

            .search-input-enhanced {
                width: 100%;
            }

            .table-footer {
                flex-direction: column;
                text-align: center;
                gap: 0.5rem;
                padding: 0.75rem 1rem;
            }

            .section-header {
                flex-wrap: wrap;
            }
        }

        /* Optional: Add gradient fade on edges for better UX on scrollable tables */
        .table-responsive-wrapper {
            position: relative;
        }

        /* For tablets, add a subtle shadow to indicate scrollability */
        @media (max-width: 992px) {
            .table-container-card {
                position: relative;
            }

            .table-responsive-wrapper::before,
            .table-responsive-wrapper::after {
                display: none;
            }
        }

        /* ── Pending Marks Entry Section (Updated to Blue Theme) ───────────────────── */
        .pending-marks-section {
            margin-bottom: 2rem;
        }

        .pending-header {
            background: linear-gradient(135deg, #2C29CA 0%, #5351e4 100%);
            border-radius: 1rem 1rem 0 0;
            padding: 1rem 1.5rem;
            border-bottom: 3px solid #7c7aec;
        }

        .pending-header h5 {
            color: white;
            font-weight: 700;
            margin: 0;
        }

        .pending-header h5 i {
            color: rgba(255, 255, 255, 0.9);
        }

        .pending-header .badge {
            background: rgba(255, 255, 255, 0.2) !important;
            color: white !important;
            backdrop-filter: blur(4px);
        }

        .pending-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(380px, 1fr));
            gap: 1.25rem;
            padding: 1.25rem;
            background: #f8f7ff;
            border-radius: 0 0 1rem 1rem;
        }

        .pending-exam-card {
            background: #ffffff;
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(44, 41, 202, 0.08);
            transition: all 0.3s ease;
            border: 1px solid #5351e4;
        }

        .pending-exam-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 24px rgba(44, 41, 202, 0.15);
            border-color: #5351e4;
        }

        .pending-exam-header {
            padding: 1rem 1.25rem;
            background: linear-gradient(135deg, #fafbff, #f6f7ff);
            border-bottom: 2px solid #ede9ff;
        }

        .pending-exam-title {
            font-weight: 700;
            font-size: 1rem;
            color: #1a1a2e;
            margin-bottom: 0.5rem;
        }

        .pending-exam-title i {
            color: #2C29CA;
            margin-right: 0.5rem;
        }

        .pending-exam-code {
            font-size: 0.7rem;
            color: #5351e4;
            font-family: monospace;
            background: #ede9ff;
            padding: 0.2rem 0.5rem;
            border-radius: 0.5rem;
            display: inline-block;
        }

        .pending-deadline {
            font-size: 0.7rem;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .pending-deadline.urgent {
            color: #dc2626;
        }

        .pending-deadline.warning {
            color: #f59e0b;
        }

        .pending-deadline.normal {
            color: #10b981;
        }

        .pending-progress-section {
            padding: 1rem 1.25rem;
        }

        .overall-progress {
            margin-bottom: 1rem;
        }

        .progress-label {
            display: flex;
            justify-content: space-between;
            font-size: 0.7rem;
            margin-bottom: 0.3rem;
            color: #1a1a2e;
            font-weight: 600;
        }

        .progress-bar-bg {
            height: 8px;
            background: #ede9ff;
            border-radius: 99px;
            overflow: hidden;
        }

        .progress-bar-fill-orange {
            height: 100%;
            background: linear-gradient(90deg, #2C29CA, #5351e4);
            border-radius: 99px;
            transition: width 0.5s ease;
        }

        .subject-list {
            max-height: 200px;
            overflow-y: auto;
            margin-top: 0.75rem;
        }

        .subject-list::-webkit-scrollbar {
            width: 4px;
        }

        .subject-list::-webkit-scrollbar-track {
            background: #ede9ff;
            border-radius: 99px;
        }

        .subject-list::-webkit-scrollbar-thumb {
            background: #2C29CA;
            border-radius: 99px;
        }

        .subject-list-title {
            font-size: 0.7rem;
            font-weight: 600;
            color: #2C29CA;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .subject-item {
            padding: 0.5rem;
            margin-bottom: 0.5rem;
            background: #fafbff;
            border-radius: 0.5rem;
            transition: all 0.2s ease;
            cursor: pointer;
            border: 1px solid #ede9ff;
        }

        .subject-item:hover {
            background: #ede9ff;
            transform: translateX(3px);
            border-color: #5351e4;
        }

        .subject-name {
            font-weight: 600;
            font-size: 0.8rem;
            color: #1a1a2e;
            margin-bottom: 0.25rem;
        }

        .subject-meta {
            font-size: 0.65rem;
            color: #6c757d;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.25rem;
        }

        .subject-progress-bar {
            flex: 1;
            height: 4px;
            background: #ede9ff;
            border-radius: 99px;
            overflow: hidden;
        }

        .subject-progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #2C29CA, #5351e4);
            border-radius: 99px;
        }

        .subject-stats {
            font-size: 0.65rem;
            color: #2C29CA;
            white-space: nowrap;
            font-weight: 600;
        }

        .pending-empty-state {
            text-align: center;
            padding: 2rem;
            background: #f8f7ff;
            border-radius: 1rem;
        }

        .pending-empty-state i {
            font-size: 3rem;
            color: #ede9ff;
            margin-bottom: 1rem;
        }

        .pending-empty-state h6 {
            color: #1a1a2e;
            font-weight: 600;
        }

        .pending-empty-state p {
            color: #6c757d;
            font-size: 0.8rem;
        }

        .pending-footer {
            padding: 0.75rem 1.25rem;
            background: #fafbff;
            border-top: 1px solid #ede9ff;
            text-align: center;
        }

        .btn-continue-marks {
            background: linear-gradient(135deg, #2C29CA, #5351e4);
            border: none;
            border-radius: 0.5rem;
            padding: 0.5rem 1rem;
            font-size: 0.75rem;
            font-weight: 600;
            color: white;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-continue-marks:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(44, 41, 202, 0.4);
            color: white;
        }

        @media (max-width: 768px) {
            .pending-grid {
                grid-template-columns: 1fr;
                padding: 0.75rem;
            }

            .pending-exam-card {
                margin-bottom: 0;
            }
        }
    </style>
@endsection

@section('content')
    <div class="side-app">

        {{-- ═══════════ HERO BANNER ════════════════════════════════════════════════ --}}
        <div class="dashboard-hero mb-4" style="position: relative; z-index: 1;">
            <div
                class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2">

                <div class="mb-2 mb-md-0">

                    {{-- BIGGER BADGE --}}
                    <span class="badge bg-white text-primary mb-3 d-inline-flex align-items-center"
                        style="font-size: .95rem; padding: .6rem 1.2rem; border-radius: .6rem; font-weight: 600; box-shadow: 0 2px 6px rgba(0,0,0,0.08);">
                        <i class="fas fa-tachometer-alt me-2"></i> &nbsp;
                        Dashboard Overview
                    </span>

                    <h3 class="text-white mb-1 fw-bold">
                        <i class="fas fa-graduation-cap me-2"></i> Examination Hub
                    </h3>

                    <p class="text-white mb-0 opacity-75" style="font-size:.9rem;">
                        Central command center for managing all academic assessments
                    </p>
                </div>

                <div class="d-flex gap-2">
                    <a href="{{ route('examination.create') }}"
                        class="btn btn-white text-primary fw-bold d-inline-flex align-items-center"
                        style="border-radius: .5rem; text-decoration: none; z-index: 10; position: relative;">
                        <i class="fas fa-plus-circle me-2"></i> &nbsp; New Examination
                    </a>
                </div>

            </div>
        </div>

        {{-- ═══════════ MAIN CONTENT AREA ════════════════════════════════════════ --}}

        <div class="row g-3 mb-4">

            <div class="col-lg-12">
                {{-- ═══════════ PENDING MARKS ENTRY SECTION ═══════════════════════════════════ --}}
                @if (isset($pendingMarksProgress) && count($pendingMarksProgress) > 0)
                    <div class="pending-marks-section">
                        <div class="pending-header">
                            <h5>
                                <i class="fas fa-clock me-2"></i>
                                Pending Marks Entry <span
                                    class="badge bg-warning text-dark ms-2">{{ count($pendingMarksProgress) }}</span>
                            </h5>
                        </div>
                        <div class="pending-grid">
                            @foreach ($pendingMarksProgress as $progress)
                                @php
                                    $exam = $progress->exam;
                                    $urgencyClass = $progress->urgency;
                                    $urgencyIcon =
                                        $progress->urgency == 'urgent'
                                            ? 'fa-exclamation-triangle'
                                            : ($progress->urgency == 'primary'
                                                ? 'fa-hourglass-half'
                                                : 'fa-clock');
                                @endphp
                                <div class="pending-exam-card" data-exam-id="{{ $exam->id }}">
                                    <div class="pending-exam-header">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <div class="pending-exam-title">
                                                    <i class="fas fa-file-alt"></i>
                                                    {{ Str::limit($exam->exam_name, 40) }}
                                                </div>
                                                <div class="pending-exam-code mt-1">
                                                    <i class="fas fa-code me-1"></i> {{ $exam->exam_code }}
                                                </div>
                                            </div>
                                            @php
                                                $deadline = \Carbon\Carbon::parse(
                                                    $exam->marks_entry_deadline,
                                                )->startOfDay();
                                                $today = now()->startOfDay();
                                                $daysLeft = $today->diffInDays($deadline, false);

                                                // urgency (override backend if needed)
                                                $urgencyClass =
                                                    $daysLeft <= 0
                                                        ? 'urgent'
                                                        : ($daysLeft <= 2
                                                            ? 'text-primary'
                                                            : 'normal');
                                                $urgencyIcon =
                                                    $daysLeft <= 0
                                                        ? 'fa-exclamation-triangle'
                                                        : ($daysLeft <= 2
                                                            ? 'fa-clock'
                                                            : 'fa-clock');
                                            @endphp

                                            <div class="pending-deadline {{ $urgencyClass }}">
                                                <i class="fas {{ $urgencyIcon }}"></i>

                                                @if ($daysLeft > 2)
                                                    {{ $daysLeft }} day{{ $daysLeft > 1 ? 's' : '' }} left
                                                @elseif ($daysLeft > 0)
                                                    <span class="text-primary fw-bold">
                                                        {{ $daysLeft }} day{{ $daysLeft > 1 ? 's' : '' }} left
                                                    </span>
                                                @elseif ($daysLeft === 0)
                                                    <span class="text-danger fw-bold">Due today</span>
                                                @else
                                                    <span class="text-danger fw-bold">Deadline passed</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                <div class="pending-progress-section">
                                    @forelse ($pendingMarksProgress as $progress)
                                        <div class="exam-progress-container mb-4">
                                            <div class="overall-progress">
                                                <div class="progress-label d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <i class="fas fa-chalkboard-teacher me-1"></i>
                                                        <strong>{{ $progress->exam->exam_name }}</strong>

                                                        @php
                                                            $daysLeft = (int) ceil($progress->days_left);
                                                        @endphp

                                                        <span class="ms-2 badge text-white
                                                            @if ($progress->is_deadline_passed) bg-secondary
                                                            @elseif($progress->urgency == 'urgent') bg-danger
                                                            @elseif($progress->urgency == 'warning') bg-warning
                                                            @else bg-info @endif">

                                                            @if ($progress->is_deadline_passed)
                                                                Deadline passed
                                                            @else
                                                                @if($daysLeft <= 0)
                                                                    Due today
                                                                @else
                                                                    {{ $daysLeft }} day{{ $daysLeft > 1 ? 's' : '' }} left
                                                                @endif
                                                            @endif
                                                        </span>
                                                    </div>
                                                    <span>{{ $progress->submitted_subjects }}/{{ $progress->total_subjects }} subjects</span>
                                                </div>
                                                <div class="progress-bar-bg">
                                                    <div class="progress-bar-fill-orange" style="width: {{ $progress->overall_progress }}%"></div>
                                                </div>
                                            </div>

                                            @if (count($progress->subject_progress) > 0)
                                                <div class="subject-list mt-3">
                                                    <!-- Subjects to complete (only show if not all completed or deadline not passed) -->
                                                    @if ($progress->overall_progress < 100 || !$progress->is_deadline_passed)
                                                        <div class="subject-list-title">
                                                            <i class="fas fa-book-open me-1"></i>
                                                            @if ($progress->is_deadline_passed)
                                                                Subjects with pending marks
                                                            @else
                                                                Subjects to complete
                                                            @endif
                                                        </div>

                                                        @foreach ($progress->subject_progress as $subject)
                                                            @if ($subject->progress < 100)
                                                                @php
                                                                    $cursorStyle = $progress->is_deadline_passed ? 'not-allowed' : 'pointer';
                                                                @endphp
                                                                <div class="subject-item pending-subject"
                                                                    @if (!$progress->is_deadline_passed)
                                                                        onclick="window.location.href='{{ route('examination.marks.subject', [$progress->exam->id, $subject->class_subject_id]) }}'"
                                                                        style="cursor: {{ $cursorStyle }};"
                                                                    @else
                                                                        style="cursor: {{ $cursorStyle }}; opacity: 0.8;"
                                                                    @endif>
                                                                    <div class="subject-name">
                                                                        {{ $subject->subject_name }}
                                                                    </div>
                                                                    <div class="subject-meta">
                                                                        <span>
                                                                            <i class="fas fa-users me-1"></i>
                                                                            {{ $subject->class_name }} @if ($subject->stream) • {{ $subject->stream }} @endif
                                                                        </span>
                                                                        <div class="d-flex align-items-center gap-2 flex-grow-1 ms-2">
                                                                            <div class="subject-progress-bar flex-grow-1">
                                                                                <div class="subject-progress-fill" style="width: {{ $subject->progress }}%"></div>
                                                                            </div>
                                                                            <span class="subject-stats">
                                                                                {{ $subject->entered_marks }}/{{ $subject->total_students }}
                                                                            </span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            @endif
                                                        @endforeach
                                                    @endif

                                                    <!-- Completed subjects (always show if deadline not passed) -->
                                                    @php
                                                        $completedSubjects = array_filter($progress->subject_progress, function($subject) {
                                                            return $subject->progress == 100;
                                                        });
                                                    @endphp

                                                    @if (count($completedSubjects) > 0 && (!$progress->is_deadline_passed || $progress->has_pending_marks))
                                                        <div class="subject-list-title mt-3" style="color: #28a745;">
                                                            <i class="fas fa-check-circle me-1"></i>
                                                            @if ($progress->is_deadline_passed)
                                                                Completed subjects
                                                            @else
                                                                Completed subjects (click to review)
                                                            @endif
                                                        </div>

                                                        @foreach ($completedSubjects as $subject)
                                                            @php
                                                                $cursorStyle = $progress->is_deadline_passed ? 'not-allowed' : 'pointer';
                                                            @endphp
                                                            <div class="subject-item completed-subject"
                                                                @if (!$progress->is_deadline_passed)
                                                                    onclick="window.location.href='{{ route('examination.marks.subject', [$progress->exam->id, $subject->class_subject_id]) }}'"
                                                                    style="cursor: {{ $cursorStyle }};"
                                                                @else
                                                                    style="cursor: {{ $cursorStyle }}; opacity: 0.8;"
                                                                @endif>
                                                                <div class="subject-name">
                                                                    {{ $subject->subject_name }}
                                                                </div>
                                                                <div class="subject-meta">
                                                                    <span>
                                                                        <i class="fas fa-users me-1"></i>
                                                                        {{ $subject->class_name }} @if ($subject->stream) • {{ $subject->stream }} @endif
                                                                    </span>
                                                                    <div class="d-flex align-items-center gap-2 flex-grow-1 ms-2">
                                                                        <div class="subject-progress-bar flex-grow-1">
                                                                            <div class="subject-progress-fill" style="width: 100%; background-color: #28a745;"></div>
                                                                        </div>
                                                                        <span class="subject-stats" style="color: #28a745;">
                                                                            {{ $subject->entered_marks }}/{{ $subject->total_students }}
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    @endif
                                                </div>
                                            @else
                                                <div class="alert alert-info mt-3" role="alert">
                                                    <i class="fas fa-info-circle me-2"></i>
                                                    No subjects assigned for this examination.
                                                </div>
                                            @endif
                                        </div>
                                    @empty
                                        <div class="alert alert-success" role="alert">
                                            <i class="fas fa-check-circle me-2"></i>
                                            All examinations have been completed or have no pending marks entry.
                                        </div>
                                    @endforelse
                                </div>

                                    <style>
                                        /* Add these styles to your existing CSS */
                                        .exam-progress-container {
                                            background: white;
                                            border-radius: 0.5rem;
                                            padding: 1.25rem;
                                            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
                                            margin-bottom: 1.5rem;
                                        }

                                        .pending-subject {
                                            background-color: rgba(255, 193, 7, 0.05);
                                            border-left: 3px solid #ffc107;
                                            transition: all 0.2s ease;
                                        }

                                        .pending-subject:hover:not([style*="not-allowed"]) {
                                            background-color: rgba(255, 193, 7, 0.1);
                                        }

                                        .completed-subject {
                                            background-color: rgba(40, 167, 69, 0.05);
                                            border-left: 3px solid #28a745;
                                            transition: all 0.2s ease;
                                        }

                                        .completed-subject:hover:not([style*="not-allowed"]) {
                                            background-color: rgba(40, 167, 69, 0.1);
                                        }

                                        .subject-progress-fill {
                                            background-color: #ffc107;
                                            height: 6px;
                                            border-radius: 3px;
                                        }

                                        .subject-list-title {
                                            font-weight: 600;
                                            margin-bottom: 0.5rem;
                                            padding-bottom: 0.25rem;
                                            border-bottom: 1px solid #eee;
                                            display: flex;
                                            align-items: center;
                                            gap: 0.5rem;
                                        }

                                        .subject-item {
                                            transition: all 0.2s ease;
                                            border-radius: 0.35rem;
                                            padding: 0.75rem;
                                            margin-bottom: 0.5rem;
                                        }

                                        .progress-bar-bg {
                                            background-color: #f0f0f0;
                                            height: 6px;
                                            border-radius: 3px;
                                            margin-top: 0.5rem;
                                            overflow: hidden;
                                        }

                                        .progress-bar-fill-orange {
                                            background-color: #ffc107;
                                            height: 100%;
                                            border-radius: 3px;
                                        }

                                        .overall-progress {
                                            margin-bottom: 1rem;
                                        }

                                        .progress-label {
                                            margin-bottom: 0.5rem;
                                            font-size: 0.9rem;
                                        }
                                    </style>

                                    <div class="pending-footer">
                                        <a href="{{ route('examination.marks.entry', $exam->id) }}"
                                            class="btn-continue-marks">
                                            <i class="fas fa-pen-alt"></i>
                                            Continue Entering Marks
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            {{-- ── LEFT: Timeline + Table ──────────────────────────────────────── --}}
            <div class="col-lg-8">

                {{-- Timeline Card --}}
                <div class="card form-card mb-3">
                    <div class="card-body p-4">
                        <div class="section-header">
                            <span class="step-badge">1</span>
                            <i class="fas fa-stream me-2"></i>Examination Timeline
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <p class="text-muted mb-0" style="font-size:.83rem;">
                                Chronological view of upcoming and recent Examinations
                            </p>

                            <div class="d-flex">
                                <button class="btn-outline-purple active" onclick="filterTimeline('all', this)">All</button>
                                <button class="btn-outline-purple ml-2" onclick="filterTimeline('draft', this)">
                                    Draft ({{ $stats['draft'] }})
                                </button>
                                <button class="btn-outline-purple ml-2" onclick="filterTimeline('active', this)">
                                    Active ({{ $stats['active'] }})
                                </button>
                                <button class="btn-outline-purple ml-2" onclick="filterTimeline('marks_entry', this)">
                                    Marks Entry ({{ $stats['marks_entry'] }})
                                </button>
                                <button class="btn-outline-purple ml-2"
                                    onclick="filterTimeline('results_released', this)">
                                    Results ({{ $stats['results_released'] }})
                                </button>
                            </div>

                        </div>

                        <div style="max-height: 450px; overflow-y: auto; padding-right: .5rem;" id="timelineContainer">
                            @forelse ($timelineExams as $exam)
                                @php
                                    $statusColors = [
                                        'draft' => '#6c757d',
                                        'active' => '#0a7a4a',
                                        'marks_entry' => '#856404',
                                        'closed' => '#721c24',
                                        'results_released' => '#2C29CA',
                                    ];
                                    $statusLabels = [
                                        'draft' => 'Draft',
                                        'active' => 'Active',
                                        'marks_entry' => 'Marks Entry',
                                        'closed' => 'Closed',
                                        'results_released' => 'Results Released',
                                    ];
                                    $isUpcoming = \Carbon\Carbon::parse($exam->start_date)->isFuture();
                                    $isOngoing =
                                        \Carbon\Carbon::parse($exam->start_date)->isPast() &&
                                        \Carbon\Carbon::parse($exam->end_date)->isFuture();
                                @endphp
                                <div class="timeline-item" style="border-left-color: {{ $statusColors[$exam->status] }};"
                                    data-status="{{ $exam->status }}"
                                    data-upcoming="{{ $isUpcoming ? 'true' : 'false' }}"
                                    data-ongoing="{{ $isOngoing ? 'true' : 'false' }}"
                                    onclick="showExamDetails({{ $exam->id }})">
                                    <div class="row align-items-center">
                                        <div class="col-md-3">
                                            <div class="timeline-date-box">
                                                <i class="fas fa-calendar-alt"></i>
                                                {{ \Carbon\Carbon::parse($exam->start_date)->format('d M, Y') }}
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="fw-bold" style="color:#1a1a2e; font-size:.9rem;">
                                                {{ $exam->exam_name }}
                                            </div>
                                            <small class="text-muted">
                                                <i class="fas fa-code me-1"></i> {{ $exam->exam_code }}
                                                &bull; {{ $exam->exam_type }}
                                                &bull; {{ $exam->term }}
                                            </small>
                                        </div>
                                        <div class="col-md-3 text-end">
                                            <span class="status-pill status-{{ $exam->status }}">
                                                {{ $statusLabels[$exam->status] }}
                                            </span>
                                            @if ($exam->status === 'marks_entry')
                                                @php
                                                    $deadline = \Carbon\Carbon::parse(
                                                        $exam->marks_entry_deadline,
                                                    )->startOfDay();
                                                    $today = now()->startOfDay();
                                                    $daysLeft = $today->diffInDays($deadline, false);
                                                @endphp

                                                <div class="mt-1">
                                                    <small
                                                        class="text-{{ $daysLeft <= 3 ? 'danger' : 'warning' }} fw-bold">
                                                        <i class="fas fa-clock"></i>

                                                        @if ($daysLeft > 0)
                                                            {{ $daysLeft }} day{{ $daysLeft > 1 ? 's' : '' }} left
                                                        @elseif($daysLeft === 0)
                                                            Due today
                                                        @else
                                                            Deadline passed
                                                        @endif
                                                    </small>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center text-muted py-5">
                                    <i class="fas fa-calendar-times fa-3x mb-3 d-block" style="opacity:.3;"></i>
                                    <p class="mb-0">No examinations found in the timeline.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="card form-card">
                    <div class="card-body p-0">
                        <div class="section-header mb-2" style="margin: 0; padding: 1.5rem 1.5rem 0 1.5rem;">
                            <span class="step-badge mb-1">2</span>
                            <i class="fas fa-table me-2"></i>All Examinations
                        </div>

                        <!-- Toolbar -->
                        <div class="table-toolbar">
                            <div class="table-toolbar-info">
                                <span class="table-toolbar-badge">
                                    <i class="fas fa-list-ol me-1"></i>
                                    {{ $examinations->count() }} Total
                                </span>
                                @if ($stats['active'] > 0)
                                    <span class="table-toolbar-badge" style="background: #d3f4e4; color: #0a7a4a;">
                                        <i class="fas fa-play-circle me-1"></i>
                                        {{ $stats['active'] }} Active
                                    </span>
                                @endif
                                @if ($stats['marks_entry'] > 0)
                                    <span class="table-toolbar-badge" style="background: #fff4de; color: #f59e0b;">
                                        <i class="fas fa-edit me-1"></i>
                                        {{ $stats['marks_entry'] }} Grading
                                    </span>
                                @endif
                            </div>
                            <div class="search-box">
                                <i class="fas fa-search search-box-icon"></i>
                                <input type="text" class="search-input-enhanced"
                                    placeholder="Search by name, code, or term..." id="tableSearch"
                                    onkeyup="searchTable()">
                            </div>
                        </div>

                        <!-- Responsive Table Wrapper with horizontal scroll -->
                        <div class="table-responsive-wrapper" id="tableScrollWrapper">
                            <table class="exam-table-enhanced" id="examTable">
                                <thead>
                                    <tr>
                                        <th style="width: 60px;">#</th>
                                        <th>Examination</th>
                                        <th style="width: 100px;">Term</th>
                                        <th style="min-width: 200px;">Schedule</th>
                                        <th style="width: 130px;">Status</th>
                                        <th style="width: 140px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($examinations as $count => $exam)
                                        @php
                                            $statusIcons = [
                                                'draft' => 'fa-pen-fancy',
                                                'active' => 'fa-play-circle',
                                                'marks_entry' => 'fa-edit',
                                                'closed' => 'fa-lock',
                                                'results_released' => 'fa-trophy',
                                            ];
                                            $statusIcon = $statusIcons[$exam->status] ?? 'fa-circle';

                                            $startDate = \Carbon\Carbon::parse($exam->start_date);
                                            $endDate = \Carbon\Carbon::parse($exam->end_date);
                                            $duration = $startDate->diffInDays($endDate) + 1;
                                        @endphp
                                        <tr>
                                            {{-- Row Number --}}
                                            <td>
                                                <span class="row-number">{{ $count + 1 }}</span>
                                            </td>

                                            {{-- Exam Name & Code --}}
                                            <td>
                                                <div class="exam-name-cell">
                                                    <span class="exam-name-text">
                                                        {{ Str::limit($exam->exam_name, 35) }}
                                                    </span>
                                                    <span class="exam-name-sub">
                                                        <i class="fas fa-hashtag"></i>
                                                        {{ $exam->exam_code }}
                                                        @if ($exam->exam_type)
                                                            <span style="margin-left: 0.5rem;">
                                                                <i class="fas fa-tag"></i> {{ $exam->exam_type }}
                                                            </span>
                                                        @endif
                                                    </span>
                                                </div>
                                            </td>

                                            {{-- Term --}}
                                            <td>
                                                <span class="term-badge">
                                                    <i class="fas fa-calendar-alt"></i>
                                                    {{ $exam->term }}
                                                </span>
                                            </td>

                                            {{-- Schedule --}}
                                            <td>
                                                <div class="period-display">
                                                    <span class="period-date">
                                                        <i class="far fa-calendar-check"
                                                            style="color: #10B981; font-size: 0.7rem;"></i>
                                                        {{ $startDate->format('M d') }}
                                                    </span>
                                                    <i class="fas fa-long-arrow-alt-right period-arrow"></i>
                                                    <span class="period-date">
                                                        {{ $endDate->format('M d, Y') }}
                                                    </span>
                                                    <span
                                                        class="period-duration 
                                                        {{ $duration == 1 ? 'text-danger fw-bold' : ($duration <= 2 ? 'text-warning fw-bold' : 'text-info') }}">

                                                        @if ($duration > 0)
                                                            {{ $duration }} {{ Str::plural('day', $duration) }} left
                                                        @elseif($duration === 0)
                                                            <span class="text-danger fw-bold">Due today</span>
                                                        @else
                                                            <span class="text-danger fw-bold">Deadline passed</span>
                                                        @endif
                                                    </span>
                                                </div>
                                            </td>

                                            {{-- Status --}}
                                            <td>
                                                <span class="status-pill-enhanced status-{{ $exam->status }}">
                                                    <i class="fas {{ $statusIcon }}"></i>
                                                    {{ ucfirst(str_replace('_', ' ', $exam->status)) }}
                                                </span>
                                            </td>

                                            {{-- Actions --}}
                                            <td>
                                                <div class="action-buttons">
                                                    <button class="btn-action btn-action-view"
                                                        onclick="showExamDetails({{ $exam->id }})"
                                                        title="View Details">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button class="btn-action btn-action-edit"
                                                        onclick="editExam({{ $exam->id }})" title="Edit Examination">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn-action action-delete"
                                                        onclick="deleteExam({{ $exam->id }})"
                                                        style="background: #EF4444; color: white; border: none;"
                                                        title="Delete Examination">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                    @if ($exam->status === 'active' || $exam->status === 'marks_entry')
                                                        <button class="btn-action btn-action-marks"
                                                            onclick="window.location.href='/examinations/{{ $exam->id }}/marks'"
                                                            title="Enter Marks">
                                                            <i class="fas fa-file-signature"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6">
                                                <div class="empty-state-enhanced">
                                                    <div class="empty-icon">
                                                        <i class="fas fa-clipboard-list"></i>
                                                    </div>
                                                    <h4>No Examinations Found</h4>
                                                    <p>Get started by creating your first examination</p>
                                                    <a href="{{ route('examination.create') }}" class="btn-primary-grad"
                                                        style="display: inline-flex; text-decoration: none;">
                                                        <i class="fas fa-plus-circle me-2"></i> Create New Examination
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Scroll Indicator for better UX (shows on tablets) -->
                        <div class="scroll-indicator" id="scrollIndicator">
                            <i class="fas fa-chevron-left"></i> Swipe to see more columns <i
                                class="fas fa-chevron-right"></i>
                        </div>

                        <!-- Table Footer -->
                        @if ($examinations->count() > 0)
                            <div class="table-footer">
                                <span>
                                    Showing <span class="table-footer-count">{{ $examinations->count() }}</span>
                                    examination{{ $examinations->count() !== 1 ? 's' : '' }}
                                </span>
                                <span>
                                    <i class="fas fa-sync-alt me-1" style="color: #2C29CA;"></i>
                                    Last updated: {{ now()->format('M d, Y h:i A') }}
                                </span>
                            </div>
                        @endif
                    </div>
                </div>

                <script>
                    // ── Enhanced Responsive Table Functions ──────────────────────────────────
                    document.addEventListener('DOMContentLoaded', function() {
                        const scrollWrapper = document.getElementById('tableScrollWrapper');
                        const scrollIndicator = document.getElementById('scrollIndicator');

                        if (scrollWrapper && scrollIndicator) {
                            // Function to check if scrolling is needed and show/hide indicator
                            function checkScrollNeeded() {
                                if (scrollWrapper.scrollWidth > scrollWrapper.clientWidth) {
                                    scrollIndicator.classList.add('visible');
                                } else {
                                    scrollIndicator.classList.remove('visible');
                                }
                            }

                            // Check on load and window resize
                            checkScrollNeeded();
                            window.addEventListener('resize', checkScrollNeeded);

                            // Optional: Hide indicator when user starts scrolling
                            scrollWrapper.addEventListener('scroll', function() {
                                if (scrollIndicator.classList.contains('visible')) {
                                    // Fade out after 2 seconds of no scroll
                                    clearTimeout(scrollIndicator.timeout);
                                    scrollIndicator.style.opacity = '0.5';
                                    scrollIndicator.timeout = setTimeout(function() {
                                        if (scrollWrapper.scrollWidth > scrollWrapper.clientWidth) {
                                            scrollIndicator.style.opacity = '1';
                                        }
                                    }, 2000);
                                }
                            });
                        }
                    });

                    // Enhanced search function with better performance
                    function searchTable() {
                        const query = document.getElementById('tableSearch').value.toLowerCase().trim();
                        const rows = document.querySelectorAll('#examTable tbody tr');
                        let visibleCount = 0;

                        rows.forEach(row => {
                            // Skip empty state row
                            if (row.querySelector('.empty-state-enhanced')) {
                                row.style.display = '';
                                return;
                            }

                            const text = row.textContent.toLowerCase();
                            const shouldShow = text.includes(query);

                            if (shouldShow) {
                                row.style.display = '';
                                visibleCount++;
                            } else {
                                row.style.display = 'none';
                            }
                        });

                        // Optional: Show message when no results found
                        const existingNoResult = document.querySelector('#examTable tbody .no-result-row');
                        if (visibleCount === 0 && rows.length > 0 && !document.querySelector('.empty-state-enhanced')) {
                            if (!existingNoResult) {
                                const noResultRow = document.createElement('tr');
                                noResultRow.className = 'no-result-row';
                                noResultRow.innerHTML = `
                                                                        <td colspan="6">
                                                                            <div class="empty-state-enhanced" style="padding: 2rem;">
                                                                                <div class="empty-icon" style="width: 60px; height: 60px;">
                                                                                    <i class="fas fa-search"></i>
                                                                                </div>
                                                                                <h4>No matching examinations</h4>
                                                                                <p>Try a different search term</p>
                                                                            </div>
                                                                        </td>
                                                                    `;
                                document.querySelector('#examTable tbody').appendChild(noResultRow);
                            }
                        } else if (existingNoResult) {
                            existingNoResult.remove();
                        }
                    }

                    // Initialize pending marks section enhancements
                    document.addEventListener('DOMContentLoaded', function() {
                        // Add hover animation for subject items
                        const subjectItems = document.querySelectorAll('.subject-item');
                        subjectItems.forEach(item => {
                            item.addEventListener('mouseenter', function() {
                                this.style.transform = 'translateX(5px)';
                            });
                            item.addEventListener('mouseleave', function() {
                                this.style.transform = 'translateX(0)';
                            });
                        });

                    });
                </script>
            </div>

            {{-- ── RIGHT: Calendar + Progress + Quick Actions ──────────────────── --}}
            <div class="col-lg-4">

                {{-- Calendar Card --}}
                <div class="card form-card mb-3">
                    <div class="card-body p-4">
                        <div class="section-header">
                            <span class="step-badge">3</span>
                            <i class="fas fa-calendar-alt me-2"></i>Calendar View
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <button class="btn-outline-purple" onclick="changeMonth(-1)" style="padding:.3rem .6rem;">
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <span class="fw-bold" style="color:#1a1a2e;" id="currentMonthYear"></span>
                            <button class="btn-outline-purple" onclick="changeMonth(1)" style="padding:.3rem .6rem;">
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                        <div id="calendarContainer"></div>
                        <div class="mt-3 pt-3" style="border-top: 2px solid #ede9ff;">
                            <small class="text-muted">
                                <i class="fas fa-circle text-primary me-1" style="font-size:.4rem;"></i>
                                Highlighted dates have examinations
                            </small>
                        </div>
                    </div>
                </div>

                {{-- Progress Card --}}
                <div class="card form-card mb-3">
                    <div class="card-body p-4">
                        <div class="section-header">
                            <span class="step-badge">4</span>
                            <i class="fas fa-chart-pie me-2"></i>Overall Progress
                        </div>
                        <div class="text-center">
                            <div class="progress-circle">
                                <svg width="130" height="130" viewBox="0 0 130 130">
                                    <defs>
                                        <linearGradient id="progressGradient" x1="0%" y1="0%"
                                            x2="100%" y2="100%">
                                            <stop offset="0%" style="stop-color:#2C29CA;stop-opacity:1" />
                                            <stop offset="100%" style="stop-color:#7c7aec;stop-opacity:1" />
                                        </linearGradient>
                                    </defs>
                                    <circle class="progress-bg" cx="65" cy="65" r="58"></circle>
                                    <circle class="progress-fill" cx="65" cy="65" r="58"
                                        stroke-dasharray="364.425"
                                        stroke-dashoffset="{{ 364.425 * (1 - $completionRate / 100) }}"></circle>
                                </svg>
                                <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">
                                    <div class="stat-value-lg" style="font-size:1.8rem;">{{ $completionRate }}%</div>
                                    <small class="text-muted">Complete</small>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-around mt-3 pt-3" style="border-top: 2px solid #ede9ff;">
                            <div class="text-center">
                                <div class="fw-bold" style="color:#0a7a4a;">{{ $stats['results_released'] }}</div>
                                <small class="text-muted">Released</small>
                            </div>
                            <div class="text-center">
                                <div class="fw-bold" style="color:#856404;">
                                    {{ $stats['active'] + $stats['marks_entry'] }}
                                </div>
                                <small class="text-muted">In Progress</small>
                            </div>
                            <div class="text-center">
                                <div class="fw-bold" style="color:#6c757d;">{{ $stats['draft'] }}</div>
                                <small class="text-muted">Draft</small>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Quick Actions Card --}}
                <div class="card form-card">
                    <div class="card-body p-4">
                        <div class="section-header">
                            <span class="step-badge">5</span>
                            <i class="fas fa-bolt me-2"></i>Quick Actions
                        </div>
                        <div class="d-grid gap-2">
                            <a href="{{ route('examination.create') }}" class="btn-primary-grad w-100 mb-2">
                                <i class="fas fa-plus-circle"></i> Create New Examination
                            </a>
                            <button class="btn-outline-purple w-100 mb-2" onclick="window.location.reload()">
                                <i class="fas fa-sync-alt me-2"></i> Refresh Dashboard
                            </button>
                            <button class="btn-outline-purple w-100" onclick="filterByStatus('all')">
                                <i class="fas fa-border-all me-2 mb-2"></i> Generate Report Cards
                            </button>
                        </div>

                        {{-- Recent Activity --}}
                        <div class="mt-4 pt-3" style="border-top: 2px solid #ede9ff;">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="fw-bold mb-0" style="color:#1a1a2e;">
                                    <i class="fas fa-stream me-2" style="color:#2C29CA;"></i> Recent Activity
                                </h6>
                                @if ($recentActivities->count() > 0)
                                    <span class="activity-badge-new" style="background: #ede9ff; color: #2C29CA;">
                                        <span class="activity-live-indicator"></span>
                                        Live
                                    </span>
                                @endif
                            </div>

                            <div class="activity-feed">
                                @forelse ($recentActivities->take(5) as $activity)
                                    @php
                                        $iconMap = [
                                            'draft' => 'fa-pencil-alt',
                                            'active' => 'fa-play-circle',
                                            'marks_entry' => 'fa-edit',
                                            'closed' => 'fa-lock',
                                            'results_released' => 'fa-trophy',
                                        ];
                                        $icon = $iconMap[$activity->status] ?? 'fa-circle';

                                        $iconBgClass =
                                            [
                                                'draft' => 'activity-icon-draft',
                                                'active' => 'activity-icon-active',
                                                'marks_entry' => 'activity-icon-marks_entry',
                                                'closed' => 'activity-icon-closed',
                                                'results_released' => 'activity-icon-results_released',
                                            ][$activity->status] ?? 'activity-icon-draft';

                                        $badgeConfig = [
                                            'draft' => ['bg' => '#f1f3f5', 'color' => '#6c757d', 'label' => 'Draft'],
                                            'active' => ['bg' => '#d3f4e4', 'color' => '#0a7a4a', 'label' => 'Active'],
                                            'marks_entry' => [
                                                'bg' => '#fff4de',
                                                'color' => '#f59e0b',
                                                'label' => 'Marks Entry',
                                            ],
                                            'closed' => ['bg' => '#ffe0e0', 'color' => '#ef4444', 'label' => 'Closed'],
                                            'results_released' => [
                                                'bg' => '#ede9ff',
                                                'color' => '#2C29CA',
                                                'label' => 'Done',
                                            ],
                                        ][$activity->status] ?? [
                                            'bg' => '#f1f3f5',
                                            'color' => '#6c757d',
                                            'label' => 'Unknown',
                                        ];
                                    @endphp
                                    <div class="activity-item-new" onclick="showExamDetails({{ $activity->id }})"
                                        title="Click to view details">
                                        <div class="activity-icon-circle {{ $iconBgClass }}">
                                            <i class="fas {{ $icon }}"></i>
                                        </div>
                                        <div class="activity-content-new">
                                            <div class="activity-title-new">
                                                {{ Str::limit($activity->exam_name, 28) }}
                                            </div>
                                            <div class="activity-meta-new">
                                                <span class="activity-time-new">
                                                    <i class="far fa-clock"></i>
                                                    {{ \Carbon\Carbon::parse($activity->updated_at)->diffForHumans() }}
                                                </span>
                                                <span class="activity-badge-new"
                                                    style="background: {{ $badgeConfig['bg'] }}; color: {{ $badgeConfig['color'] }};">
                                                    {{ $badgeConfig['label'] }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center" style="color: #c4c4d4;">
                                            <i class="fas fa-chevron-right" style="font-size: 0.7rem;"></i>
                                        </div>
                                    </div>
                                @empty
                                    <div class="activity-empty-state">
                                        <i class="far fa-bell-slash"></i>
                                        <p>No recent activity</p>
                                        <small style="font-size: 0.7rem;">Activities will appear here when examinations are
                                            created or updated</small>
                                    </div>
                                @endforelse

                                @if ($recentActivities->count() > 5)
                                    <div class="text-center mt-2">
                                        <small style="color: #2C29CA; cursor: pointer; font-weight: 600;"
                                            onclick="filterByStatus('all')">
                                            <i class="fas fa-list me-1"></i> View all {{ $recentActivities->count() }}
                                            activities
                                        </small>
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
    </div>

    {{-- ═══════════ MODALS ═══════════════════════════════════════════════════════ --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ URL::asset('assets/plugins/datatable/js/jquery.dataTables.js') }}"></script>
    <script src="{{ URL::asset('assets/plugins/datatable/js/dataTables.bootstrap4.js') }}"></script>
    <script src="{{ URL::asset('assets/js/datatables.js') }}"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    @include('sweetalert::alert')

    <script>
        // ── Calendar Logic ──────────────────────────────────────────────────────
        let currentMonth = new Date({{ date('Y') }}, {{ date('m') - 1 }}, 1);
        const examDates = @json($calendarExams);

        function renderCalendar() {
            const year = currentMonth.getFullYear();
            const month = currentMonth.getMonth();
            const firstDay = new Date(year, month, 1).getDay();
            const daysInMonth = new Date(year, month + 1, 0).getDate();
            const today = new Date();

            document.getElementById('currentMonthYear').textContent =
                currentMonth.toLocaleString('default', {
                    month: 'long',
                    year: 'numeric'
                });

            let html = '<div class="calendar-grid">';
            const dayHeaders = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
            dayHeaders.forEach(day => {
                html += `<div class="cal-day-header">${day}</div>`;
            });

            for (let i = 0; i < firstDay; i++) {
                html += '<div class="cal-date"></div>';
            }

            for (let day = 1; day <= daysInMonth; day++) {
                const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                const hasExam = examDates.some(exam => {
                    const start = new Date(exam.start_date);
                    const end = new Date(exam.end_date);
                    const current = new Date(dateStr);
                    return current >= start && current <= end;
                });

                const isToday = today.getFullYear() === year &&
                    today.getMonth() === month &&
                    today.getDate() === day;

                html += `
                                                                                            <div class="cal-date ${hasExam ? 'has-exam' : ''} ${isToday ? 'today' : ''}"
                                                                                                 ${hasExam ? `onclick="showDateExams('${dateStr}')"` : ''}
                                                                                                 title="${hasExam ? 'Click to view exams' : ''}">
                                                                                                ${day}
                                                                                            </div>
                                                                                        `;
            }

            html += '</div>';
            document.getElementById('calendarContainer').innerHTML = html;
        }

        function changeMonth(delta) {
            currentMonth.setMonth(currentMonth.getMonth() + delta);
            renderCalendar();
        }

        function showDateExams(dateStr) {
            const dateExams = examDates.filter(exam => {
                const start = new Date(exam.start_date);
                const end = new Date(exam.end_date);
                const current = new Date(dateStr);
                return current >= start && current <= end;
            });

            if (dateExams.length === 0) return;

            let html = '<div style="text-align:left;">';
            dateExams.forEach(exam => {
                const statusColors = {
                    'draft': '#6c757d',
                    'active': '#0a7a4a',
                    'marks_entry': '#856404',
                    'closed': '#721c24',
                    'results_released': '#2C29CA'
                };
                html += `
                            <div class="timeline-item mb-2" style="border-left-color: ${statusColors[exam.status]}; cursor:pointer;" 
                                    onclick="Swal.close(); showExamDetails(${exam.id})">
                                <strong>${exam.exam_code}</strong>
                                <br><small>${exam.name}</small>
                            </div>
                        `;
            });
            html += '</div>';

            Swal.fire({
                title: `<span style="font-size: 1.3rem; font-weight: 700; color: #1a1a2e;">
                                                                                            <i class="fas fa-calendar-check me-2" style="color: #2C29CA;"></i>
                                                                                            ${new Date(dateStr).toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' })}
                                                                                        </span>`,
                html: html,
                showConfirmButton: false,
                showCloseButton: true,
                width: '550px',
                padding: '1.5rem',
            });
        }

        // ── Filter Functions ────────────────────────────────────────────────────
        function filterByStatus(status) {
            const rows = document.querySelectorAll('#examTable tbody tr');
            rows.forEach(row => {
                if (status === 'all') {
                    row.style.display = '';
                } else {
                    const statusCell = row.querySelector('.status-pill');
                    if (statusCell) {
                        row.style.display = statusCell.classList.contains(`status-${status}`) ? '' : 'none';
                    }
                }
            });
        }

        function searchTable() {
            const query = document.getElementById('tableSearch').value.toLowerCase();
            const rows = document.querySelectorAll('#examTable tbody tr');
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(query) ? '' : 'none';
            });
        }

        function filterTimeline(status, btn) {
            document.querySelectorAll('.btn-outline-purple').forEach(b => b.classList.remove('active'));
            if (btn) btn.classList.add('active');

            const items = document.querySelectorAll('.timeline-item');
            items.forEach(item => {
                const itemStatus = item.getAttribute('data-status');
                if (status === 'all' || itemStatus === status) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
        }

        // ── Professional Exam Details Modal ─────────────────────────────────────
        function showExamDetails(examId) {
            Swal.fire({
                title: 'Loading Examination Details...',
                html: `
                    <div style="text-align: center; padding: 1.5rem;">
                        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;"></div>
                    </div>
                `,
                allowOutsideClick: false,
                showConfirmButton: false,
                showCloseButton: false,
                showCancelButton: false,
            });

            fetch(`/examinations/${examId}/details`)
                .then(response => response.json())
                .then(exam => {
                    let statusColor, statusIcon, statusLabel;
                    switch (exam.status) {
                        case 'draft':
                            statusColor = '#6c757d';
                            statusIcon = 'fa-pen-fancy';
                            statusLabel = 'Draft';
                            break;
                        case 'active':
                            statusColor = '#10B981';
                            statusIcon = 'fa-play-circle';
                            statusLabel = 'Active';
                            break;
                        case 'marks_entry':
                            statusColor = '#F59E0B';
                            statusIcon = 'fa-edit';
                            statusLabel = 'Marks Entry';
                            break;
                        case 'closed':
                            statusColor = '#EF4444';
                            statusIcon = 'fa-lock';
                            statusLabel = 'Closed';
                            break;
                        case 'results_released':
                            statusColor = '#2C29CA';
                            statusIcon = 'fa-trophy';
                            statusLabel = 'Results Released';
                            break;
                        default:
                            statusColor = '#2C29CA';
                            statusIcon = 'fa-clipboard';
                            statusLabel = exam.status_label;
                    }

                    let actionButtonsHTML = '';
                    if (exam.status === 'draft') {
                        actionButtonsHTML = `
                                                                                                    <button onclick="Swal.close(); updateExamStatus(${examId}, 'active')" 
                                                                                                        style="background: #10B981; color: white; border: none; padding: 10px 20px; border-radius: 10px; font-weight: 600; cursor: pointer; margin: 5px;">
                                                                                                        <i class="fas fa-play me-2"></i> Activate
                                                                                                    </button>
                                                                                                    <button onclick="Swal.close(); deleteExam(${examId})" 
                                                                                                        style="background: #EF4444; color: white; border: none; padding: 10px 20px; border-radius: 10px; font-weight: 600; cursor: pointer; margin: 5px;">
                                                                                                        <i class="fas fa-trash-alt me-2"></i> Delete
                                                                                                    </button>
                                                                                                `;
                    } else if (exam.status === 'active') {
                        actionButtonsHTML = `
                                                                                                    <button onclick="Swal.close(); updateExamStatus(${examId}, 'marks_entry')" 
                                                                                                        style="background: #F59E0B; color: white; border: none; padding: 10px 20px; border-radius: 10px; font-weight: 600; cursor: pointer; margin: 5px;">
                                                                                                        <i class="fas fa-edit me-2"></i> Open Marks Entry
                                                                                                    </button>
                                                                                                    <button onclick="Swal.close(); updateExamStatus(${examId}, 'closed')" 
                                                                                                        style="background: #EF4444; color: white; border: none; padding: 10px 20px; border-radius: 10px; font-weight: 600; cursor: pointer; margin: 5px;">
                                                                                                        <i class="fas fa-lock me-2"></i> Close Exam
                                                                                                    </button>
                                                                                                `;
                    } else if (exam.status === 'marks_entry') {
                        actionButtonsHTML = `
                                                                                                    <button onclick="window.location.href='/examinations/${examId}/marks'" 
                                                                                                        style="background: #2C29CA; color: white; border: none; padding: 10px 20px; border-radius: 10px; font-weight: 600; cursor: pointer; margin: 5px;">
                                                                                                        <i class="fas fa-pen me-2"></i> Enter Marks
                                                                                                    </button>
                                                                                                    <button onclick="Swal.close(); updateExamStatus(${examId}, 'closed')" 
                                                                                                        style="background: #EF4444; color: white; border: none; padding: 10px 20px; border-radius: 10px; font-weight: 600; cursor: pointer; margin: 5px;">
                                                                                                        <i class="fas fa-lock me-2"></i> Close Exam
                                                                                                    </button>
                                                                                                `;
                    } else if (exam.status === 'closed') {
                        actionButtonsHTML = `
                                                                                                    <button onclick="Swal.close(); updateExamStatus(${examId}, 'results_released')" 
                                                                                                        style="background: #2C29CA; color: white; border: none; padding: 10px 20px; border-radius: 10px; font-weight: 600; cursor: pointer; margin: 5px;">
                                                                                                        <i class="fas fa-trophy me-2"></i> Release Results
                                                                                                    </button>
                                                                                                `;
                    } else if (exam.status === 'results_released') {
                        actionButtonsHTML = `
                                                                                                    <button disabled 
                                                                                                        style="background: #94A3B8; color: white; border: none; padding: 10px 20px; border-radius: 10px; font-weight: 600; cursor: not-allowed; margin: 5px;">
                                                                                                        <i class="fas fa-check-circle me-2"></i> Completed
                                                                                                    </button>
                                                                                                `;
                    }

                    let deadlineBadge = '';
                    if (exam.days_until_deadline > 0) {
                        const isUrgent = exam.days_until_deadline <= 3;
                        deadlineBadge = `
                                                                                                    <span style="background: ${isUrgent ? '#FEF3C7' : '#D1FAE5'}; color: ${isUrgent ? '#D97706' : '#059669'}; padding: 4px 12px; border-radius: 99px; font-size: 0.75rem; font-weight: 600; display: inline-flex; align-items: center; gap: 5px;">
                                                                                                        <i class="fas fa-clock"></i> ${exam.days_until_deadline} day${exam.days_until_deadline !== 1 ? 's' : ''} left
                                                                                                    </span>
                                                                                                `;
                    } else {
                        deadlineBadge = `
                                                                                                    <span style="background: #FEE2E2; color: #DC2626; padding: 4px 12px; border-radius: 99px; font-size: 0.75rem; font-weight: 600; display: inline-flex; align-items: center; gap: 5px;">
                                                                                                        <i class="fas fa-ban"></i> Expired
                                                                                                    </span>
                                                                                                `;
                    }

                    Swal.fire({
                        title: exam.exam_name,
                        html: `
                                                                                                    <div style="text-align: left; margin-top: 10px;">
                                                                                                        <div style="text-align: center; margin-bottom: 20px;">
                                                                                                            <span style="display: inline-flex; align-items: center; gap: 8px; background: ${statusColor}15; color: ${statusColor}; padding: 6px 16px; border-radius: 99px; font-size: 0.85rem; font-weight: 600; border: 1px solid ${statusColor}30;">
                                                                                                                <i class="fas ${statusIcon}"></i>
                                                                                                                ${statusLabel}
                                                                                                            </span>
                                                                                                        </div>

                                                                                                        <div style="background: linear-gradient(135deg, #2C29CA 0%, #5351e4 100%); border-radius: 16px; padding: 16px; margin-bottom: 20px; text-align: center;">
                                                                                                            <div style="color: rgba(255,255,255,0.7); font-size: 0.7rem; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 5px;">Examination Code</div>
                                                                                                            <div style="color: white; font-size: 1.3rem; font-weight: 700; font-family: 'Courier New', monospace;">${exam.exam_code}</div>
                                                                                                        </div>

                                                                                                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 20px;">
                                                                                                            <div style="background: #F8FAFC; border-radius: 12px; padding: 14px; border: 1px solid #E2E8F0;">
                                                                                                                <div style="color: #94A3B8; font-size: 0.7rem; text-transform: uppercase; margin-bottom: 4px;"><i class="fas fa-layer-group me-1"></i> Type</div>
                                                                                                                <div style="font-weight: 600; color: #1E293B;">${exam.exam_type}</div>
                                                                                                            </div>
                                                                                                            <div style="background: #F8FAFC; border-radius: 12px; padding: 14px; border: 1px solid #E2E8F0;">
                                                                                                                <div style="color: #94A3B8; font-size: 0.7rem; text-transform: uppercase; margin-bottom: 4px;"><i class="fas fa-calendar-alt me-1"></i> Term</div>
                                                                                                                <div style="font-weight: 600; color: #1E293B;">${exam.term}</div>
                                                                                                            </div>
                                                                                                        </div>

                                                                                                        <div style="background: #F8FAFC; border-radius: 12px; padding: 16px; margin-bottom: 12px; border: 1px solid #E2E8F0;">
                                                                                                            <div style="color: #2C29CA; font-size: 0.75rem; font-weight: 600; margin-bottom: 10px;"><i class="fas fa-calendar-week me-1"></i> Examination Period</div>
                                                                                                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                                                                                                <div><div style="font-size: 0.7rem; color: #94A3B8;">Start Date</div><div style="font-weight: 600; color: #1E293B;">${exam.start_date}</div></div>
                                                                                                                <i class="fas fa-arrow-right" style="color: #2C29CA;"></i>
                                                                                                                <div><div style="font-size: 0.7rem; color: #94A3B8;">End Date</div><div style="font-weight: 600; color: #1E293B;">${exam.end_date}</div></div>
                                                                                                            </div>
                                                                                                        </div>

                                                                                                        <div style="background: ${exam.days_until_deadline <= 3 && exam.days_until_deadline > 0 ? '#FFFBEB' : '#F8FAFC'}; border-radius: 12px; padding: 16px; margin-bottom: 12px; border: 1px solid ${exam.days_until_deadline <= 3 && exam.days_until_deadline > 0 ? '#FDE68A' : '#E2E8F0'};">
                                                                                                            <div style="color: ${exam.days_until_deadline <= 3 && exam.days_until_deadline > 0 ? '#D97706' : '#2C29CA'}; font-size: 0.75rem; font-weight: 600; margin-bottom: 10px;"><i class="fas fa-hourglass-half me-1"></i> Marks Entry Deadline</div>
                                                                                                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                                                                                                <div style="font-weight: 600; color: #1E293B;">${exam.marks_entry_deadline}</div>
                                                                                                                ${deadlineBadge}
                                                                                                            </div>
                                                                                                        </div>

                                                                                                        ${exam.description ? `
                                                                                                                                    <div style="background: #F8FAFC; border-radius: 12px; padding: 16px; margin-bottom: 12px; border: 1px solid #E2E8F0;">
                                                                                                                                        <div style="color: #2C29CA; font-size: 0.75rem; font-weight: 600; margin-bottom: 8px;"><i class="fas fa-align-left me-1"></i> Description</div>
                                                                                                                                        <div style="font-size: 0.85rem; color: #475569; line-height: 1.5;">${exam.description}</div>
                                                                                                                                    </div>
                                                                                                                                ` : ''}

                                                                                                        <div style="display: flex; flex-wrap: wrap; gap: 10px; justify-content: center; margin-top: 20px; padding-top: 15px; border-top: 2px solid #E2E8F0;">
                                                                                                            ${actionButtonsHTML}
                                                                                                        </div>
                                                                                                    </div>
                                                                                                `,
                        showConfirmButton: false,
                        showCloseButton: true,
                        width: '600px',
                        padding: '2rem',
                        customClass: {
                            popup: 'swal-custom-popup',
                            title: 'swal-custom-title',
                            closeButton: 'swal-custom-close'
                        }
                    });
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error Loading Details',
                        text: 'Unable to load examination details. Please try again later.',
                        confirmButtonColor: '#2C29CA',
                    });
                });
        }

        // ── Professional Status Update Modal ────────────────────────────────────
        function updateExamStatus(examId, newStatus) {
            const configs = {
                active: {
                    title: 'Activate Examination',
                    text: 'Make this examination live? Teachers and students will be able to access it.',
                    icon: 'question',
                    confirmColor: '#10B981',
                    confirmText: 'Yes, Activate'
                },
                marks_entry: {
                    title: 'Open Marks Entry',
                    text: 'Allow teachers to enter marks for this examination?',
                    icon: 'info',
                    confirmColor: '#F59E0B',
                    confirmText: 'Yes, Open Entry'
                },
                closed: {
                    title: 'Close Examination',
                    text: 'Close this examination permanently? This action cannot be easily reversed.',
                    icon: 'warning',
                    confirmColor: '#EF4444',
                    confirmText: 'Yes, Close'
                },
                results_released: {
                    title: 'Release Results',
                    text: 'Release results to students? They will be able to view their performance immediately.',
                    icon: 'success',
                    confirmColor: '#2C29CA',
                    confirmText: 'Yes, Release'
                }
            };

            const config = configs[newStatus];

            Swal.fire({
                title: config.title,
                text: config.text,
                icon: config.icon,
                showCancelButton: true,
                confirmButtonColor: config.confirmColor,
                cancelButtonColor: '#6c757d',
                confirmButtonText: config.confirmText,
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then((result) => {
                if (!result.isConfirmed) return;

                Swal.fire({
                    title: 'Processing...',
                    html: '<div class="spinner-border text-primary" role="status"></div>',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    showCloseButton: false,
                    showCancelButton: false,
                });

                $.ajax({
                    url: `/examinations/${examId}/update-status`,
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        status: newStatus
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Updated Successfully!',
                                text: response.message,
                                timer: 2000,
                                showConfirmButton: false,
                            }).then(() => location.reload());
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message,
                                confirmButtonColor: '#2C29CA',
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Operation Failed',
                            text: 'Failed to update status. Please try again.',
                            confirmButtonColor: '#2C29CA',
                        });
                    }
                });
            });
        }

        // ── Professional Delete Modal ───────────────────────────────────────────
        function deleteExam(examId) {
            Swal.fire({
                title: 'Delete Examination?',
                html: `
                                        <div style="text-align: center; margin: 1rem 0;">
                                            <i class="fas fa-exclamation-triangle" style="font-size: 3rem; color: #EF4444; margin-bottom: 15px;"></i>
                                            <p style="color: #475569; font-size: 0.95rem;">This action <strong style="color: #EF4444;">cannot be undone</strong>. All associated data will be permanently removed.</p>

                                    `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#EF4444',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Delete',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then((result) => {
                if (!result.isConfirmed) return;

                Swal.fire({
                    title: 'Deleting...',
                    html: '<div class="spinner-border text-danger" role="status"></div>',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    showCloseButton: false,
                    showCancelButton: false,
                });

                $.ajax({
                    url: `/examinations/${examId}`,
                    method: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted Successfully!',
                                text: response.message,
                                timer: 2000,
                                showConfirmButton: false,
                            }).then(() => location.reload());
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                html: response.message, // use html for formatting
                                confirmButtonColor: '#2C29CA',
                            });
                        }
                    },
                    error: function(xhr) {
                        let errorMsg = 'Failed to delete examination. Please try again.';

                        if (xhr.responseJSON) {
                            if (xhr.responseJSON.message) {
                                errorMsg = xhr.responseJSON.message;
                            }

                            // Handle validation errors (if any)
                            if (xhr.responseJSON.errors) {
                                errorMsg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                            }
                        }

                        Swal.fire({
                            icon: 'error',
                            title: '<span style="color:#EF4444;">Deletion Failed</span>',
                            html: errorMsg,
                            confirmButtonColor: '#2C29CA',
                        });
                    }
                });
            });
        }

        // ── Initialize ──────────────────────────────────────────────────────────
        document.addEventListener('DOMContentLoaded', function() {
            renderCalendar();

            // Minimal SweetAlert2 styling that won't interfere with functionality
            const swalStyles = document.createElement('style');
            swalStyles.textContent = `
                                                                                        .swal2-popup {
                                                                                            font-family: 'Plus Jakarta Sans', sans-serif;
                                                                                            border-radius: 20px;
                                                                                        }
                                                                                        .swal2-title {
                                                                                            font-family: 'Plus Jakarta Sans', sans-serif;
                                                                                            font-weight: 700;
                                                                                            color: #1a1a2e;
                                                                                        }
                                                                                        .swal2-html-container {
                                                                                            font-family: 'Plus Jakarta Sans', sans-serif;
                                                                                        }
                                                                                        .swal2-confirm {
                                                                                            border-radius: 10px !important;
                                                                                            padding: 10px 24px !important;
                                                                                            font-weight: 600 !important;
                                                                                            font-size: 0.9rem !important;
                                                                                        }
                                                                                        .swal2-cancel {
                                                                                            border-radius: 10px !important;
                                                                                            padding: 10px 24px !important;
                                                                                            font-weight: 600 !important;
                                                                                            font-size: 0.9rem !important;
                                                                                        }
                                                                                        .swal2-close {
                                                                                            outline: none !important;
                                                                                        }
                                                                                        .swal2-close:focus {
                                                                                            box-shadow: none !important;
                                                                                        }
                                                                                    `;
            document.head.appendChild(swalStyles);
        });


        // ── Edit Examination Modal ──────────────────────────────────────────────
        // ── Edit Examination Modal ──────────────────────────────────────────────
        function editExam(examId) {
            Swal.fire({
                title: 'Loading Examination Data...',
                html: `
                                                                        <div style="text-align: center; padding: 2rem;">
                                                                            <div class="spinner-border" role="status" style="width: 3rem; height: 3rem; color: #2C29CA;"></div>
                                                                            <p style="margin-top: 1rem; color: #6c757d; font-size: 0.85rem;">Fetching examination details...</p>
                                                                        </div>
                                                                    `,
                allowOutsideClick: false,
                showConfirmButton: false,
                showCloseButton: false,
                showCancelButton: false,
            });

            fetch(`/examinations/${examId}/edit-details`)
                .then(response => response.json())
                .then(exam => {
                    const statusOptions = [{
                            value: 'draft',
                            label: 'Draft',
                            color: '#6c757d',
                            icon: 'fa-pencil-alt'
                        },
                        {
                            value: 'active',
                            label: 'Active',
                            color: '#10B981',
                            icon: 'fa-play-circle'
                        },
                        {
                            value: 'marks_entry',
                            label: 'Marks Entry',
                            color: '#F59E0B',
                            icon: 'fa-edit'
                        },
                        {
                            value: 'closed',
                            label: 'Closed',
                            color: '#EF4444',
                            icon: 'fa-lock'
                        },
                        {
                            value: 'results_released',
                            label: 'Results Released',
                            color: '#2C29CA',
                            icon: 'fa-trophy'
                        }
                    ];

                    const currentStatus = statusOptions.find(s => s.value === exam.status);
                    const statusOptionsHTML = statusOptions.map(s => `
                                                                            <option value="${s.value}" ${exam.status === s.value ? 'selected' : ''}>
                                                                                ${s.label}
                                                                            </option>
                                                                        `).join('');

                    Swal.fire({
                        title: '',
                        html: `
                                                                                <style>
                                                                                    .edit-modal-header {
                                                                                        background: linear-gradient(135deg, #2C29CA 0%, #5351e4 100%);
                                                                                        margin: -2rem -2rem 0 -2rem;
                                                                                        padding: 2rem 2rem 1.5rem 2rem;
                                                                                        border-radius: 20px 20px 0 0;
                                                                                        text-align: center;
                                                                                        position: relative;
                                                                                        overflow: hidden;
                                                                                    }
                                                                                    .edit-modal-header::before {
                                                                                        content: '';
                                                                                        position: absolute;
                                                                                        top: -50%;
                                                                                        right: -20%;
                                                                                        width: 200px;
                                                                                        height: 200px;
                                                                                        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
                                                                                        border-radius: 50%;
                                                                                    }
                                                                                    .edit-modal-header .exam-code-badge {
                                                                                        display: inline-block;
                                                                                        background: rgba(255,255,255,0.2);
                                                                                        color: white;
                                                                                        padding: 4px 12px;
                                                                                        border-radius: 99px;
                                                                                        font-size: 0.7rem;
                                                                                        font-weight: 600;
                                                                                        font-family: 'Courier New', monospace;
                                                                                        margin-bottom: 8px;
                                                                                        backdrop-filter: blur(10px);
                                                                                    }
                                                                                    .edit-modal-header .exam-title {
                                                                                        color: white;
                                                                                        font-size: 1.1rem;
                                                                                        font-weight: 700;
                                                                                        margin-bottom: 4px;
                                                                                    }
                                                                                    .edit-modal-header .edit-label {
                                                                                        color: rgba(255,255,255,0.7);
                                                                                        font-size: 0.7rem;
                                                                                        text-transform: uppercase;
                                                                                        letter-spacing: 1px;
                                                                                    }
                                                                                    .edit-section {
                                                                                        background: #ffffff;
                                                                                        border: 1px solid #ede9ff;
                                                                                        border-radius: 12px;
                                                                                        padding: 16px;
                                                                                        margin-bottom: 12px;
                                                                                        transition: all 0.2s ease;
                                                                                    }
                                                                                    .edit-section:hover {
                                                                                        border-color: #d4d0ff;
                                                                                        box-shadow: 0 4px 12px rgba(44, 41, 202, 0.06);
                                                                                    }
                                                                                    .edit-section-title {
                                                                                        display: flex;
                                                                                        align-items: center;
                                                                                        gap: 8px;
                                                                                        font-weight: 700;
                                                                                        font-size: 0.78rem;
                                                                                        color: #2C29CA;
                                                                                        text-transform: uppercase;
                                                                                        letter-spacing: 0.5px;
                                                                                        margin-bottom: 14px;
                                                                                        padding-bottom: 10px;
                                                                                        border-bottom: 2px solid #ede9ff;
                                                                                    }
                                                                                    .edit-section-title i {
                                                                                        width: 26px;
                                                                                        height: 26px;
                                                                                        border-radius: 8px;
                                                                                        background: #ede9ff;
                                                                                        display: inline-flex;
                                                                                        align-items: center;
                                                                                        justify-content: center;
                                                                                        font-size: 0.7rem;
                                                                                    }
                                                                                    .edit-form-grid {
                                                                                        display: grid;
                                                                                        grid-template-columns: 1fr 1fr;
                                                                                        gap: 12px;
                                                                                    }
                                                                                    .edit-form-group {
                                                                                        display: flex;
                                                                                        flex-direction: column;
                                                                                    }
                                                                                    .edit-form-group.full-width {
                                                                                        grid-column: 1 / -1;
                                                                                    }
                                                                                    .edit-label {
                                                                                        font-size: 0.72rem;
                                                                                        font-weight: 600;
                                                                                        color: #4a5568;
                                                                                        margin-bottom: 5px;
                                                                                        display: flex;
                                                                                        align-items: center;
                                                                                        gap: 4px;
                                                                                    }
                                                                                    .edit-label .required {
                                                                                        color: #EF4444;
                                                                                    }
                                                                                    .edit-input {
                                                                                        width: 100%;
                                                                                        padding: 10px 12px;
                                                                                        border: 1.5px solid #e2e8f0;
                                                                                        border-radius: 10px;
                                                                                        font-size: 0.82rem;
                                                                                        font-family: 'Plus Jakarta Sans', sans-serif;
                                                                                        color: #1a1a2e;
                                                                                        background: #fafbff;
                                                                                        transition: all 0.2s ease;
                                                                                        outline: none;
                                                                                    }
                                                                                    .edit-input:focus {
                                                                                        border-color: #5351e4;
                                                                                        box-shadow: 0 0 0 3px rgba(83, 81, 228, 0.08);
                                                                                        background: #ffffff;
                                                                                    }
                                                                                    .edit-input:hover {
                                                                                        border-color: #c4c0ff;
                                                                                    }
                                                                                    select.edit-input {
                                                                                        cursor: pointer;
                                                                                        appearance: none;
                                                                                        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath d='M6 8L1 3h10z' fill='%236c757d'/%3E%3C/svg%3E");
                                                                                        background-repeat: no-repeat;
                                                                                        background-position: right 12px center;
                                                                                        padding-right: 32px;
                                                                                    }
                                                                                    textarea.edit-input {
                                                                                        resize: vertical;
                                                                                        min-height: 80px;
                                                                                    }
                                                                                    .status-indicator {
                                                                                        display: inline-flex;
                                                                                        align-items: center;
                                                                                        gap: 6px;
                                                                                        padding: 4px 10px;
                                                                                        border-radius: 99px;
                                                                                        font-size: 0.7rem;
                                                                                        font-weight: 600;
                                                                                        margin-top: 6px;
                                                                                    }
                                                                                </style>

                                                                                <div style="margin-top: 1rem;">
                                                                                    <!-- Header -->
                                                                                    <div class="edit-modal-header">
                                                                                        <div class="edit-label">
                                                                                            <i class="fas fa-pen me-1"></i> Editing Examination
                                                                                        </div>
                                                                                        <div class="exam-code-badge">${exam.exam_code}</div>
                                                                                        <div class="exam-title">${exam.exam_name}</div>
                                                                                    </div>

                                                                                    <form id="editExamForm" style="margin-top: 20px;">
                                                                                        <input type="hidden" name="exam_id" value="${exam.id}">

                                                                                        <!-- Dates Section -->
                                                                                        <div class="edit-section">
                                                                                            <div class="edit-section-title">
                                                                                                <i class="fas fa-calendar-alt"></i> Dates & Timeline
                                                                                            </div>
                                                                                            <div class="edit-form-grid">
                                                                                                <div class="edit-form-group">
                                                                                                    <label class="edit-label">
                                                                                                        <span class="required">*</span> Start Date
                                                                                                    </label>
                                                                                                    <input type="date" name="start_date" value="${exam.start_date}" class="edit-input">
                                                                                                </div>
                                                                                                <div class="edit-form-group">
                                                                                                    <label class="edit-label">
                                                                                                        <span class="required">*</span> End Date
                                                                                                    </label>
                                                                                                    <input type="date" name="end_date" value="${exam.end_date}" class="edit-input">
                                                                                                </div>
                                                                                                <div class="edit-form-group full-width">
                                                                                                    <label class="edit-label">
                                                                                                        Marks Entry Deadline
                                                                                                    </label>
                                                                                                    <input type="date" name="marks_entry_deadline" value="${exam.marks_entry_deadline}" class="edit-input">
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>

                                                                                        <!-- Marks Section -->
                                                                                        <div class="edit-section">
                                                                                            <div class="edit-section-title">
                                                                                                <i class="fas fa-percent"></i> Marks & Grading
                                                                                            </div>
                                                                                            <div class="edit-form-grid">
                                                                                                <div class="edit-form-group">
                                                                                                    <label class="edit-label">Total Marks</label>
                                                                                                    <input type="number" name="total_marks" value="${exam.total_marks}" min="1" max="1000" class="edit-input" placeholder="100">
                                                                                                </div>
                                                                                                <div class="edit-form-group">
                                                                                                    <label class="edit-label">Pass Mark</label>
                                                                                                    <input type="number" name="pass_mark" value="${exam.pass_mark}" min="1" class="edit-input" placeholder="50">
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>

                                                                                        <!-- Status Section -->
                                                                                        <div class="edit-section">
                                                                                            <div class="edit-section-title">
                                                                                                <i class="fas fa-toggle-on"></i> Examination Status
                                                                                            </div>
                                                                                            <div class="edit-form-group">
                                                                                                <select name="status" class="edit-input">
                                                                                                    ${statusOptionsHTML}
                                                                                                </select>
                                                                                                <div class="status-indicator mt-2" style="background: ${currentStatus.color}15; color: ${currentStatus.color};">
                                                                                                    <i class="fas ${currentStatus.icon}"></i>
                                                                                                    Current: ${currentStatus.label}
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>

                                                                                        <!-- Description Section -->
                                                                                        <div class="edit-section">
                                                                                            <div class="edit-section-title">
                                                                                                <i class="fas fa-align-left"></i> Description & Notes
                                                                                            </div>
                                                                                            <div class="edit-form-group">
                                                                                                <textarea name="description" rows="3" class="edit-input" placeholder="Add any additional notes or description about this examination...">${exam.description || ''}</textarea>
                                                                                            </div>
                                                                                        </div>
                                                                                    </form>
                                                                                </div>
                                                                            `,
                        showCancelButton: true,
                        showConfirmButton: true,
                        confirmButtonText: '<i class="fas fa-save me-2"></i> Save Changes',
                        cancelButtonText: '<i class="fas fa-times me-2"></i> Cancel',
                        confirmButtonColor: '#2C29CA',
                        cancelButtonColor: '#6c757d',
                        width: '680px',
                        padding: '0',
                        customClass: {
                            popup: 'swal-edit-popup',
                            title: 'swal-edit-title',
                            htmlContainer: 'swal-edit-html',
                            confirmButton: 'swal-edit-confirm',
                            cancelButton: 'swal-edit-cancel',
                            actions: 'swal-edit-actions'
                        },
                        didOpen: () => {
                            // Add custom button styles
                            const style = document.createElement('style');
                            style.textContent = `
                                                                                    .swal-edit-popup {
                                                                                        border-radius: 20px !important;
                                                                                        overflow: hidden;
                                                                                    }
                                                                                    .swal-edit-html {
                                                                                        margin: 0 !important;
                                                                                        padding: 0 2rem 1.5rem 2rem !important;
                                                                                    }
                                                                                    .swal-edit-actions {
                                                                                        padding: 1rem 2rem 1.5rem 2rem !important;
                                                                                        border-top: 1px solid #ede9ff;
                                                                                        margin: 0 !important;
                                                                                    }
                                                                                    .swal-edit-confirm {
                                                                                        border-radius: 10px !important;
                                                                                        padding: 12px 28px !important;
                                                                                        font-weight: 600 !important;
                                                                                        font-size: 0.85rem !important;
                                                                                        background: linear-gradient(135deg, #2C29CA, #5351e4) !important;
                                                                                        transition: all 0.3s ease !important;
                                                                                    }
                                                                                    .swal-edit-confirm:hover {
                                                                                        transform: translateY(-2px) !important;
                                                                                        box-shadow: 0 8px 20px rgba(44, 41, 202, 0.3) !important;
                                                                                    }
                                                                                    .swal-edit-cancel {
                                                                                        border-radius: 10px !important;
                                                                                        padding: 12px 28px !important;
                                                                                        font-weight: 600 !important;
                                                                                        font-size: 0.85rem !important;
                                                                                        transition: all 0.3s ease !important;
                                                                                    }
                                                                                    .swal-edit-cancel:hover {
                                                                                        background: #f1f3f5 !important;
                                                                                    }
                                                                                    .swal2-close:focus {
                                                                                        box-shadow: none !important;
                                                                                    }
                                                                                `;
                            document.head.appendChild(style);
                        },
                        preConfirm: () => {
                            const formData = new FormData(document.getElementById('editExamForm'));
                            const data = Object.fromEntries(formData.entries());

                            // Validate dates
                            if (!data.start_date) {
                                Swal.showValidationMessage('Start date is required');
                                return false;
                            }
                            if (!data.end_date) {
                                Swal.showValidationMessage('End date is required');
                                return false;
                            }

                            const startDate = new Date(data.start_date);
                            const endDate = new Date(data.end_date);

                            if (endDate < startDate) {
                                Swal.showValidationMessage('End date must be on or after start date');
                                return false;
                            }

                            if (data.marks_entry_deadline) {
                                const deadline = new Date(data.marks_entry_deadline);
                                if (deadline < endDate) {
                                    Swal.showValidationMessage(
                                        'Marks entry deadline must be on or after end date');
                                    return false;
                                }
                            }

                            if (data.total_marks && parseInt(data.total_marks) < 1) {
                                Swal.showValidationMessage('Total marks must be at least 1');
                                return false;
                            }

                            if (data.pass_mark && parseInt(data.pass_mark) < 1) {
                                Swal.showValidationMessage('Pass mark must be at least 1');
                                return false;
                            }

                            return data;
                        }
                    }).then((result) => {
                        if (!result.isConfirmed || !result.value) return;

                        Swal.fire({
                            title: 'Updating Examination...',
                            html: `
                                                                                    <div style="text-align: center; padding: 2rem;">
                                                                                        <div class="spinner-border" role="status" style="width: 3rem; height: 3rem; color: #2C29CA;"></div>
                                                                                        <p style="margin-top: 1rem; color: #6c757d; font-size: 0.85rem;">Saving your changes...</p>
                                                                                    </div>
                                                                                `,
                            allowOutsideClick: false,
                            showConfirmButton: false,
                            showCloseButton: false,
                            showCancelButton: false,
                        });

                        $.ajax({
                            url: `/examinations/${examId}/update-details`,
                            method: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                ...result.value
                            },
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: '<span style="font-size: 1.2rem; font-weight: 700;">Updated Successfully!</span>',
                                        html: `
                                                                                                <div style="text-align: center;">
                                                                                                    <i class="fas fa-check-circle" style="font-size: 3rem; color: #10B981; margin-bottom: 10px;"></i>
                                                                                                    <p style="color: #6c757d;">${response.message}</p>
                                                                                                </div>
                                                                                            `,
                                        timer: 2000,
                                        showConfirmButton: false,
                                    }).then(() => location.reload());
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Update Error',
                                        text: response.message || 'Failed to update',
                                        confirmButtonColor: '#2C29CA',
                                    });
                                }
                            },
                            error: function(xhr) {
                                let errorMsg = 'Failed to update examination. Please try again.';
                                if (xhr.responseJSON && xhr.responseJSON.errors) {
                                    const errors = xhr.responseJSON.errors;
                                    errorMsg = Object.values(errors).flat().join('<br>');
                                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                                    errorMsg = xhr.responseJSON.message;
                                }
                                Swal.fire({
                                    icon: 'error',
                                    title: '<span style="color: #EF4444;">Update Failed</span>',
                                    html: `<div style="text-align: center;">${errorMsg}</div>`,
                                    confirmButtonColor: '#2C29CA',
                                });
                            }
                        });
                    });
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error Loading Data',
                        text: 'Unable to load examination data. Please try again later.',
                        confirmButtonColor: '#2C29CA',
                    });
                });
        }
    </script>
@endsection

@section('js')
@endsection
