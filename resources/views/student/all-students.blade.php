<?php
use App\Http\Controllers\Helper;
use App\Helpers\PermissionHelper;
?>
@extends('layouts-side-bar.master')

@section('css')
    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        :root {
            --b: #2f2ccb;
            --bl: rgba(47, 44, 203, .10);
            --b2: #2420a8;
            --r: #dc2626;
            --rl: rgba(220, 38, 38, .10);
            --g: #059669;
            --gl: rgba(5, 150, 105, .10);
            --a: #d97706;
            --al: rgba(217, 119, 6, .10);
            --surf: #fff;
            --bg: #f0f4f8;
            --brd: #e2e8f0;
            --t1: #0f172a;
            --t2: #475569;
            --t3: #94a3b8;
            --rad: 16px;
            --rads: 10px;
            --sh: 0 1px 3px rgba(0, 0, 0, .06), 0 4px 16px rgba(0, 0, 0, .05);
            --sh-lg: 0 10px 40px rgba(0, 0, 0, .12);
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }

        *:not(i):not([class*="fa"]):not([class*="bi"]) {
            font-family: 'DM Sans', sans-serif;
        }

        body {
            background: var(--bg);
        }

        /* ── Hero ── */
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

        /* ── Cards ── */
        .card {
            background: var(--surf);
            border-radius: var(--rad);
            border: 1px solid var(--brd);
            box-shadow: var(--sh);
            overflow: hidden;
            margin-bottom: 1.5rem;
        }

        .card-hd {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--brd);
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #fafbff;
        }

        .card-hd-left {
            display: flex;
            align-items: center;
            gap: .6rem;
        }

        .card-hd h3 {
            margin: 0;
            font-size: .95rem;
            font-weight: 700;
            color: var(--t1);
        }

        /* ── Class / Stream grouping headers ── */
        .class-header {
            display: flex;
            align-items: center;
            gap: .75rem;
            padding: .9rem 1.25rem;
            background: linear-gradient(90deg, rgba(47, 44, 203, .07), transparent);
            border-left: 4px solid var(--b);
            border-radius: var(--rads);
            margin-bottom: 1.25rem;
        }

        .class-header h4 {
            margin: 0;
            font-size: 1rem;
            font-weight: 700;
            color: var(--t1);
        }

        .stream-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: .6rem 1rem;
            background: #f5f6ff;
            border-radius: 8px;
            margin-bottom: .85rem;
            border: 1px solid rgba(47, 44, 203, .12);
        }

        .stream-header h5 {
            margin: 0;
            font-size: .875rem;
            font-weight: 700;
            color: var(--b);
        }

        /* ── Table ── */
        .std-table {
            width: 100%;
            border-collapse: collapse;
        }

        .std-table thead tr {
            background: #f5f6ff;
        }

        .std-table th {
            padding: .7rem 1rem;
            font-size: .71rem;
            font-weight: 700;
            color: var(--b);
            text-transform: uppercase;
            letter-spacing: .06em;
            border-bottom: 2px solid var(--brd);
            white-space: nowrap;
        }

        .std-table td {
            padding: .75rem 1rem;
            border-bottom: 1px solid #f8fafc;
            font-size: .875rem;
            color: var(--t1);
            vertical-align: middle;
        }

        .std-table tbody tr:last-child td {
            border-bottom: none;
        }

        .std-table tbody tr:hover td {
            background: #f5f6ff;
        }

        /* ── Avatar ── */
        .std-avatar {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--brd);
            display: block;
        }

        .std-avatar-placeholder {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: var(--bl);
            border: 2px solid rgba(47, 44, 203, .2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .9rem;
            font-weight: 700;
            color: var(--b);
        }

        /* ── Badges ── */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: .25rem;
            padding: .22rem .65rem;
            border-radius: 20px;
            font-size: .73rem;
            font-weight: 600;
        }

        .badge-blue {
            background: var(--bl);
            color: var(--b);
        }

        .badge-green {
            background: var(--gl);
            color: var(--g);
        }

        .badge-pink {
            background: rgba(236, 72, 153, .1);
            color: #db2777;
        }

        .badge-gray {
            background: #f1f5f9;
            color: var(--t2);
        }

        /* ── Action Buttons ── */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            padding: .45rem 1rem;
            border-radius: 8px;
            font-size: .8rem;
            font-weight: 600;
            border: none;
            cursor: pointer;
            text-decoration: none;
            transition: all .18s;
        }

        .btn-icon {
            width: 32px;
            height: 32px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            transition: all .18s;
            font-size: .8rem;
        }

        .btn-view {
            background: var(--bl);
            color: var(--b);
        }

        .btn-view:hover {
            background: var(--b);
            color: #fff;
        }

        .btn-edit {
            background: var(--al);
            color: var(--a);
        }

        .btn-edit:hover {
            background: var(--a);
            color: #fff;
        }

        .btn-del {
            background: var(--rl);
            color: var(--r);
        }

        .btn-del:hover {
            background: var(--r);
            color: #fff;
        }

        .btn-primary {
            background: var(--b);
            color: #fff;
        }

        .btn-primary:hover {
            background: var(--b2);
            color: #fff;
            transform: translateY(-1px);
        }

        .btn-secondary {
            background: #f1f5f9;
            color: var(--t2);
            border: 1px solid var(--brd);
        }

        .btn-secondary:hover {
            border-color: var(--b);
            color: var(--b);
        }

        .btn-danger-solid {
            background: var(--r);
            color: #fff;
        }

        .btn-danger-solid:hover {
            background: #b91c1c;
            color: #fff;
        }

        /* ── Pagination ── */
        .pagination {
            display: flex;
            gap: .25rem;
            align-items: center;
            flex-wrap: wrap;
        }

        .pagination .page-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 32px;
            height: 32px;
            padding: 0 .5rem;
            border-radius: 8px;
            font-size: .8rem;
            font-weight: 600;
            color: var(--t2);
            background: var(--surf);
            border: 1px solid var(--brd);
            text-decoration: none;
            transition: all .15s;
        }

        .pagination .page-link:hover {
            background: var(--bl);
            color: var(--b);
            border-color: rgba(47, 44, 203, .3);
        }

        .pagination .page-item.active .page-link {
            background: var(--b);
            color: #fff;
            border-color: var(--b);
        }

        .pagination .page-item.disabled .page-link {
            opacity: .4;
            pointer-events: none;
        }

        /* ── Empty state ── */
        .empty-state {
            text-align: center;
            padding: 3rem 2rem;
            color: var(--t3);
        }

        .empty-icon-wrap {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background: var(--bl);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 1.5rem;
            color: var(--b);
        }

        /* ══════════════════════════════════
                                       MODALS
                                    ══════════════════════════════════ */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, .55);
            backdrop-filter: blur(4px);
            z-index: 9000;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .modal-overlay.open {
            display: flex;
        }

        .modal-box {
            background: var(--surf);
            border-radius: 20px;
            box-shadow: var(--sh-lg);
            width: 100%;
            max-width: 820px;
            max-height: 90vh;
            display: flex;
            flex-direction: column;
            animation: slideUp .25s ease;
        }

        .modal-box-sm {
            max-width: 480px;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .modal-hd {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--brd);
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: linear-gradient(135deg, #464592 0%, #1613c9 60%, #050352 100%);
            border-radius: 20px 20px 0 0;
        }

        .modal-hd h4 {
            margin: 0;
            font-size: 1rem;
            font-weight: 700;
            color: #fff;
        }

        .modal-close {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: rgba(255, 255, 255, .15);
            border: none;
            color: #fff;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .9rem;
            transition: background .15s;
        }

        .modal-close:hover {
            background: rgba(255, 255, 255, .3);
        }

        .modal-body {
            padding: 1.5rem;
            overflow-y: auto;
            flex: 1;
        }

        .modal-ft {
            padding: 1rem 1.5rem;
            border-top: 1px solid var(--brd);
            display: flex;
            gap: .75rem;
            justify-content: flex-end;
            background: #fafbff;
            border-radius: 0 0 20px 20px;
        }

        /* ── View modal ── */
        .student-banner {
            background: linear-gradient(135deg, #f5f7fa 0%, #e8eaf6 100%);
            border-radius: 12px;
            padding: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1.25rem;
            margin-bottom: 1.5rem;
        }

        .banner-photo {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #fff;
            box-shadow: 0 4px 12px rgba(0, 0, 0, .12);
            flex-shrink: 0;
        }

        .banner-photo-placeholder {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            background: var(--bl);
            border: 3px solid #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            font-weight: 700;
            color: var(--b);
            flex-shrink: 0;
        }

        .banner-name {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--t1);
            margin: 0 0 .35rem;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: .75rem;
        }

        .info-item {
            background: #f8fafc;
            border-radius: 10px;
            padding: .75rem 1rem;
            border: 1px solid var(--brd);
        }

        .info-item label {
            display: block;
            font-size: .7rem;
            font-weight: 700;
            color: var(--t3);
            text-transform: uppercase;
            letter-spacing: .05em;
            margin-bottom: .25rem;
        }

        .info-item p {
            margin: 0;
            font-size: .875rem;
            font-weight: 600;
            color: var(--t1);
        }

        .section-hd {
            font-size: .75rem;
            font-weight: 700;
            color: var(--b);
            text-transform: uppercase;
            letter-spacing: .07em;
            margin: 1.25rem 0 .75rem;
            display: flex;
            align-items: center;
            gap: .4rem;
            padding-bottom: .4rem;
            border-bottom: 1px solid var(--brd);
        }

        /* ── Edit form ── */
        .form-label {
            display: block;
            font-size: .75rem;
            font-weight: 700;
            color: var(--t2);
            margin-bottom: .35rem;
            text-transform: uppercase;
            letter-spacing: .03em;
        }

        .form-control {
            width: 100%;
            padding: .6rem .9rem;
            border: 1.5px solid var(--brd);
            border-radius: var(--rads);
            font-size: .875rem;
            color: var(--t1);
            background: #fff;
            transition: border-color .15s, box-shadow .15s;
            outline: none;
        }

        .form-control:focus {
            border-color: var(--b);
            box-shadow: 0 0 0 3px var(--bl);
        }

        .form-control[readonly] {
            background: #f8fafc;
            color: var(--t2);
        }

        .form-grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .form-grid-3 {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 1rem;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .edit-section {
            background: #fafbff;
            border: 1px solid var(--brd);
            border-radius: 12px;
            padding: 1.25rem;
            margin-bottom: 1rem;
        }

        /* ── Photo upload ── */
        .photo-upload-wrap {
            border: 2px dashed var(--brd);
            border-radius: 12px;
            padding: 1.5rem;
            text-align: center;
            cursor: pointer;
            transition: all .2s;
            position: relative;
        }

        .photo-upload-wrap:hover {
            border-color: var(--b);
            background: var(--bl);
        }

        .photo-upload-wrap input {
            position: absolute;
            inset: 0;
            opacity: 0;
            cursor: pointer;
            width: 100%;
            height: 100%;
        }

        .photo-preview-thumb {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--brd);
        }

        /* ── Spinner ── */
        .spinner {
            width: 40px;
            height: 40px;
            border: 3px solid var(--brd);
            border-top-color: var(--b);
            border-radius: 50%;
            animation: spin .7s linear infinite;
            margin: 2rem auto;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* ── Stat pills ── */
        .stat-pills {
            display: flex;
            gap: .75rem;
            flex-wrap: wrap;
            margin-bottom: 1.5rem;
        }

        .stat-pill {
            display: flex;
            align-items: center;
            gap: .5rem;
            background: var(--surf);
            border: 1px solid var(--brd);
            border-radius: 10px;
            padding: .5rem 1rem;
            font-size: .82rem;
            font-weight: 600;
            color: var(--t2);
            box-shadow: var(--sh);
        }

        select.form-control {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%2394a3b8' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right .75rem center;
            padding-right: 2.5rem;
        }

        @media(max-width:768px) {
            .fin-hero {
                padding: 1.5rem;
            }

            .fin-hero h1 {
                font-size: 1.3rem;
            }

            .info-grid,
            .form-grid-2,
            .form-grid-3 {
                grid-template-columns: 1fr;
            }

            .modal-box {
                max-width: 100%;
                margin: .5rem;
            }

            .student-banner {
                flex-direction: column;
                text-align: center;
            }

            .std-table th:nth-child(1),
            .std-table td:nth-child(1) {
                display: none;
            }
        }

        /* ── Modern Table Design ── */
        .table-wrapper {
            overflow-x: auto;
            margin: 0;
            border-radius: 12px;
        }

        .std-table {
            width: 100%;
            min-width: 800px;
            border-collapse: collapse;
        }

        .std-table thead tr {
            background: #2c29ca;
        }

        .std-table th {
            padding: 0.9rem 1rem;
            font-size: 0.72rem;
            font-weight: 700;
            color: #fff;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: none;
            text-align: left;
        }

        .std-table th:first-child {
            border-radius: 10px 0 0 0;
        }

        .std-table th:last-child {
            border-radius: 0 10px 0 0;
        }

        .std-table td {
            padding: 0.9rem 1rem;
            border-bottom: 1px solid #f8fafc;
            font-size: 0.85rem;
            color: var(--t1);
            vertical-align: middle;
        }

        .std-table tr:hover td {
            background: #f5f6ff;
        }

        .std-table tr:last-child td {
            border-bottom: none;
        }

        /* ── Custom Scrollbar for Table ── */
        .table-wrapper::-webkit-scrollbar {
            height: 8px;
            width: 8px;
        }

        .table-wrapper::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 10px;
            margin: 0 10px;
        }

        .table-wrapper::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
            transition: all 0.2s;
        }

        .table-wrapper::-webkit-scrollbar-thumb:hover {
            background: #2f2ccb;
        }

        /* Firefox scrollbar */
        .table-wrapper {
            scrollbar-width: thin;
            scrollbar-color: #cbd5e1 #f1f5f9;
        }

        /* ── Scroll Hint ── */
        .scroll-hint {
            display: none;
            text-align: center;
            font-size: 0.7rem;
            color: var(--t3);
            margin-top: 0.5rem;
            gap: 0.5rem;
            justify-content: center;
            align-items: center;
        }

        .scroll-hint i {
            font-size: 0.6rem;
            animation: bounce 1s infinite;
        }

        @keyframes bounce {

            0%,
            100% {
                transform: translateX(0);
                opacity: 0.5;
            }

            50% {
                transform: translateX(3px);
                opacity: 1;
            }
        }

        @media (max-width: 1199px) {
            .scroll-hint {
                display: flex;
            }
        }

        /* ── Updated Class Header ── */
        .class-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.9rem 1.25rem;
            background: linear-gradient(90deg, rgba(47, 44, 203, 0.07), transparent);
            border-left: 4px solid #2f2ccb;
            border-radius: 12px;
            margin-bottom: 1.25rem;
        }

        .class-header h4 {
            margin: 0;
            font-size: 1rem;
            font-weight: 700;
            color: var(--t1);
        }

        /* ── Updated Stream Header ── */
        .stream-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.6rem 1rem;
            background: #f5f6ff;
            border-radius: 10px;
            margin-bottom: 0.85rem;
            border: 1px solid rgba(47, 44, 203, 0.12);
        }

        .stream-header h5 {
            margin: 0;
            font-size: 0.875rem;
            font-weight: 700;
            color: #2f2ccb;
        }

        /* ── Responsive Table ── */
        @media (max-width: 768px) {
            .table-wrapper {
                margin: 0 -0.5rem;
                padding: 0 0.5rem;
            }

            .std-table {
                min-width: 650px;
            }

            .std-table th,
            .std-table td {
                padding: 0.7rem 0.75rem;
                font-size: 0.8rem;
            }
        }

        /* ID Card Status Badges */
        .badge-active {
            background: var(--gl);
            color: var(--g);
        }

        .badge-revoked {
            background: var(--rl);
            color: var(--r);
        }

        .badge-expired {
            background: var(--al);
            color: var(--a);
        }

        .badge-none {
            background: #e2e8f0;
            color: #64748b;
        }

        /* Print button style */
        .btn-print {
            background: var(--gl);
            color: var(--g);
        }

        .btn-print:hover {
            background: var(--g);
            color: #fff;
        }
    </style>
@endsection

@section('page-header')
    <div class="fin-hero mt-4">
        <div style="position:relative;z-index:1;">
            <div class="hero-badge"><i class="fas fa-user-graduate"></i> Students</div>
            <h1>All Students</h1>
            <p>Browse students by class and stream</p>
        </div>
        <div style="position:relative;z-index:1;margin-top:14px;display:flex;gap:10px;flex-wrap:wrap;">
            @if(PermissionHelper::canFeature('import_students'))
                <a href="{{ route('students.bulk.import.form') }}"
                    style="display:inline-flex;align-items:center;gap:7px;background:rgba(255,255,255,.18);color:#fff;border:1.5px solid rgba(255,255,255,.5);border-radius:9px;padding:8px 18px;font-weight:600;font-size:.85rem;text-decoration:none;backdrop-filter:blur(4px);transition:.2s;"
                    onmouseover="this.style.background='rgba(255,255,255,.3)'"
                    onmouseout="this.style.background='rgba(255,255,255,.18)'">
                    <i class="fas fa-file-import"></i> Bulk Import
                </a>
            @endif
            @if(PermissionHelper::canFeature('add_student'))
                <a href="{{ route('students.add.new.student') }}"
                    style="display:inline-flex;align-items:center;gap:7px;background:rgba(255,255,255,.18);color:#fff;border:1.5px solid rgba(255,255,255,.5);border-radius:9px;padding:8px 18px;font-weight:600;font-size:.85rem;text-decoration:none;backdrop-filter:blur(4px);transition:.2s;"
                    onmouseover="this.style.background='rgba(255,255,255,.3)'"
                    onmouseout="this.style.background='rgba(255,255,255,.18)'">
                    <i class="fas fa-user-plus"></i> Add Student
                </a>
            @endif
        </div>
    </div>
@endsection

@section('content')

    @php
        $totalStudents = 0;
        $totalClasses = count($groupedStudents);
        foreach ($groupedStudents as $senior => $streams) {
            foreach ($streams as $stream => $students) {
                $totalStudents += $students->total();
            }
        }
    @endphp

    {{-- Stat pills --}}
    <div class="stat-pills">
        <div class="stat-pill"><i class="fas fa-user-graduate" style="color:var(--b);"></i> {{ $totalStudents }} Students
        </div>
        <div class="stat-pill"><i class="fas fa-chalkboard" style="color:var(--a);"></i> {{ $totalClasses }} Classes</div>
    </div>

    @if(empty($groupedStudents))
        <div class="card">
            <div class="empty-state">
                <div class="empty-icon-wrap"><i class="fas fa-user-slash"></i></div>
                <h4 style="font-size:1rem;font-weight:700;color:var(--t2);margin:0 0 .4rem;">No Students Found</h4>
                <p style="font-size:.875rem;color:var(--t3);margin:0;">No students have been enrolled in this school yet.</p>
            </div>
        </div>
    @else
        @foreach($groupedStudents as $senior => $streams)
            <div class="card" style="margin-bottom:2rem;">
                <div class="card-hd">
                    <div class="card-hd-left">
                        <i class="fas fa-chalkboard-teacher" style="color:var(--b);font-size:1rem;"></i>
                        <h3>{{ \App\Http\Controllers\Helper::item_md_name($senior) ?? $senior }}</h3>
                    </div>
                    <span class="badge badge-blue">{{ count($streams) }} Stream{{ count($streams) !== 1 ? 's' : '' }}</span>
                </div>

                <div style="padding:1.25rem 1.5rem;">
                    @foreach($streams as $stream => $students)
                        <div style="margin-bottom:2rem;">
                            {{-- Stream header --}}
                            <div class="stream-header">
                                <div style="display:flex;align-items:center;gap:.6rem;">
                                    <i class="fas fa-code-branch" style="color:var(--b);font-size:.85rem;"></i>
                                    <h5>Stream: {{ \App\Http\Controllers\Helper::recordMdname($stream) ?? $stream }}</h5>
                                </div>

                                <div style="display:flex;align-items:center;gap:.6rem;">
                                    <div style="position:relative;">
                                        <i class="fas fa-search"
                                            style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:var(--t3);font-size:.75rem;"></i>
                                        <input type="text" class="form-control stream-search-input" data-senior="{{ $senior }}"
                                            data-stream="{{ $stream }}" placeholder="Search name or adm no…"
                                            style="padding-left:2rem;height:34px;font-size:.8rem;width:200px;">
                                    </div>
                                    <span class="badge badge-blue">
                                        <i class="fas fa-users" style="font-size:.65rem;"></i>
                                        <span class="stream-count">{{ $students->total() }}</span>
                                        student{{ $students->total() !== 1 ? 's' : '' }}
                                    </span>
                                </div>
                            </div>

                            <table class="std-table">
                                <thead>
                                    <tr>
                                        <th width="4%">#</th>
                                        <th width="7%">Photo</th>
                                        <th>First Name</th>
                                        <th>Last Name</th>
                                        <th width="12%">Adm No.</th>
                                        <th width="10%">Gender</th>
                                        <th width="12%">ID Card Status</th>
                                        <th width="18%">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-{{ $senior }}-{{ $stream }}">
                                    @include('student.partials.student-rows', ['students' => $students, 'senior' => $senior, 'stream' => $stream])
                                </tbody>
                            </table>

                            <div id="pagination-{{ $senior }}-{{ $stream }}">
                                @include('student.partials.student-pagination', ['students' => $students])
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    @endif
    </div>
    </div>

    {{-- ══════════════════════════════════
    VIEW STUDENT MODAL
    ══════════════════════════════════ --}}
    <div class="modal-overlay" id="viewModal">
        <div class="modal-box">
            <div class="modal-hd">
                <h4><i class="fas fa-id-card" style="margin-right:.5rem;opacity:.8;"></i> Student Details</h4>
                <button class="modal-close" onclick="closeModal('viewModal')"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body" id="viewBody">
                <div class="spinner"></div>
            </div>
            <div class="modal-ft">
                <button class="btn btn-secondary" onclick="closeModal('viewModal')"><i class="fas fa-times"></i>
                    Close</button>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════
    EDIT STUDENT MODAL
    ══════════════════════════════════ --}}
    <div class="modal-overlay" id="editModal">
        <div class="modal-box">
            <div class="modal-hd">
                <h4><i class="fas fa-pen" style="margin-right:.5rem;opacity:.8;"></i> Edit Student</h4>
                <button class="modal-close" onclick="closeModal('editModal')"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body" id="editBody">
                <div class="spinner"></div>
            </div>
            <div class="modal-ft" id="editFooter" style="display:none;">
                <button class="btn btn-danger" onclick="closeModal('editModal')"><i class="fas fa-times"></i>
                    Cancel</button>
                <button class="btn btn-primary" id="saveBtn" onclick="saveStudent()">
                    <i class="fas fa-save"></i> Save Changes
                </button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const CSRF = '{{ csrf_token() }}';
        let currentEditId = null;

        // ── Modal helpers ──────────────────────────────────────────────────
        function openModal(id) { document.getElementById(id).classList.add('open'); document.body.style.overflow = 'hidden'; }
        function closeModal(id) { document.getElementById(id).classList.remove('open'); document.body.style.overflow = ''; }
        document.querySelectorAll('.modal-overlay').forEach(m => {
            m.addEventListener('click', e => { if (e.target === m) closeModal(m.id); });
        });

        // ── Avatar helpers ──────────────────────────────────────────────────
        const COLORS = ['#2f2ccb', '#059669', '#d97706', '#7c3aed', '#0891b2', '#dc2626'];
        function avatarColor(name) {
            return COLORS[(name.charCodeAt(0) || 0) % COLORS.length];
        }
        function initials(fn, ln) {
            return ((fn?.[0] ?? '') + (ln?.[0] ?? '')).toUpperCase();
        }

        // ── VIEW STUDENT ───────────────────────────────────────────────────
        function viewStudent(id) {
            document.getElementById('viewBody').innerHTML = '<div class="spinner"></div>';
            openModal('viewModal');

            fetch(`{{ url('/students/view') }}/${id}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            })
                .then(r => r.json())
                .then(data => {
                    const s = data.student || data;
                    const color = avatarColor(s.firstname || 'A');
                    const ini = initials(s.firstname, s.lastname);

                    const photoHtml = s.photo_url
                        ? `<img src="${s.photo_url}" class="banner-photo" alt="${s.firstname}">`
                        : `<div class="banner-photo-placeholder" style="background:${color}1a;color:${color};">${ini}</div>`;

                    const genderBadge = s.gender === 'Male'
                        ? `<span class="badge badge-blue"><i class="fas fa-mars"></i> Male</span>`
                        : s.gender === 'Female'
                            ? `<span class="badge badge-pink"><i class="fas fa-venus"></i> Female</span>`
                            : `<span class="badge badge-gray">${s.gender || '—'}</span>`;

                    document.getElementById('viewBody').innerHTML = `
                                                <div class="student-banner">
                                                    ${photoHtml}
                                                    <div>
                                                        <div class="banner-name">${s.firstname || ''} ${s.lastname || ''}</div>
                                                        <div style="display:flex;flex-wrap:wrap;gap:.4rem;margin-top:.35rem;">
                                                            ${genderBadge}
                                                            ${s.admission_number ? `<span class="badge badge-blue"><i class="fas fa-id-badge"></i> ${s.admission_number}</span>` : ''}
                                                            ${s.senior ? `<span class="badge badge-gray"><i class="fas fa-chalkboard"></i> ${s.senior}</span>` : ''}
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="section-hd"><i class="fas fa-user"></i> Personal Information</div>
                                                <div class="info-grid">
                                                    ${infoItem('Full Name', (s.firstname || '') + ' ' + (s.lastname || ''))}
                                                    ${infoItem('Date of Birth', fmtDate(s.date_of_birth))}
                                                    ${infoItem('Place of Birth', s.place_of_birth)}
                                                    ${infoItem('Nationality', s.nationality)}
                                                    ${infoItem('Birth Certificate No.', s.birth_certificate_entry_number)}
                                                    ${infoItem('Gender', s.gender)}
                                                </div>

                                                <div class="section-hd"><i class="fas fa-graduation-cap"></i> Academic Information</div>
                                                <div class="info-grid">
                                                    ${infoItem('Admission Number', s.admission_number)}
                                                    ${infoItem('Registration Number', s.registration_number)}
                                                    ${infoItem('Date of Admission', fmtDate(s.date_of_admission))}
                                                    ${infoItem('Admission Year', s.admission_year)}
                                                    ${infoItem('Class / Senior', s.senior)}
                                                    ${infoItem('Stream', s.stream)}
                                                    ${infoItem('PLE Score', s.ple_score)}
                                                    ${infoItem('UCE Score', s.uce_score)}
                                                    ${infoItem('Previous School', s.previous_school)}
                                                    ${infoItem('Primary School', s.primary_school_name)}
                                                </div>

                                                <div class="section-hd"><i class="fas fa-phone"></i> Contact Information</div>
                                                <div class="info-grid">
                                                    ${infoItem('Primary Contact', s.primary_contact)}
                                                    ${infoItem('Other Contact', s.other_contact)}
                                                    ${infoItem('Home Address', s.home_address, true)}
                                                </div>

                                                <div class="section-hd"><i class="fas fa-users"></i> Guardian Information</div>
                                                <div class="info-grid">
                                                    ${infoItem('Guardian Names', s.guardian_names)}
                                                    ${infoItem('Relation', s.relation)}
                                                    ${infoItem('Guardian Phone', s.guardian_phone)}
                                                    ${infoItem('Guardian Email', s.guardian_email)}
                                                </div>

                                                <div class="section-hd"><i class="fas fa-info-circle"></i> Additional Information</div>
                                                <div class="info-grid">
                                                    ${infoItem('Medical History', s.medical_history, true)}
                                                    ${infoItem('Comments', s.comments, true)}
                                                </div>
                                            `;
                })
                .catch(() => {
                    document.getElementById('viewBody').innerHTML = `<div class="empty-state"><div class="empty-icon-wrap"><i class="fas fa-times-circle"></i></div><p>Failed to load student data.</p></div>`;
                });
        }

        function infoItem(label, val, full = false) {
            const v = val || '<span style="color:var(--t3);">—</span>';
            return `<div class="info-item" ${full ? 'style="grid-column:1/-1"' : ''}><label>${label}</label><p>${v}</p></div>`;
        }
        function fmtDate(d) {
            if (!d) return null;
            return new Date(d).toLocaleDateString('en-GB', { day: 'numeric', month: 'long', year: 'numeric' });
        }

        // ── EDIT STUDENT ───────────────────────────────────────────────────
        function editStudent(id) {
            currentEditId = id;
            document.getElementById('editBody').innerHTML = '<div class="spinner"></div>';
            document.getElementById('editFooter').style.display = 'none';
            openModal('editModal');

            fetch(`{{ url('/students/Information') }}/${id}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            })
                .then(r => r.json())
                .then(s => {
                    document.getElementById('editFooter').style.display = 'flex';
                    document.getElementById('editBody').innerHTML = buildEditForm(s);
                    initPhotoUpload();
                })
                .catch(() => {
                    document.getElementById('editBody').innerHTML = `<div class="empty-state"><p>Failed to load student data.</p></div>`;
                });
        }

        function buildEditForm(s) {
            const photoHtml = s.photo_url
                ? `<img src="${s.photo_url}" class="photo-preview-thumb" id="currentThumb"> <small style="color:var(--t3);display:block;margin-top:.35rem;">Upload new to replace</small>`
                : `<small style="color:var(--t3);">No photo uploaded yet</small>`;

            return `
                                        <div class="edit-section">
                                            <div class="section-hd"><i class="fas fa-user"></i> Personal Information</div>
                                            <div class="form-grid-2">
                                                <div class="form-group"><label class="form-label">First Name *</label><input type="text" class="form-control" id="ef_firstname" value="${esc(s.firstname)}"></div>
                                                <div class="form-group"><label class="form-label">Last Name *</label><input type="text" class="form-control" id="ef_lastname" value="${esc(s.lastname)}"></div>
                                                <div class="form-group"><label class="form-label">Gender *</label>
                                                    <select class="form-control" id="ef_gender">
                                                        <option value="">Select</option>
                                                        <option value="Male" ${s.gender === 'Male' ? 'selected' : ''}>Male</option>
                                                        <option value="Female" ${s.gender === 'Female' ? 'selected' : ''}>Female</option>
                                                        <option value="Other" ${s.gender === 'Other' ? 'selected' : ''}>Other</option>
                                                    </select>
                                                </div>
                                                <div class="form-group"><label class="form-label">Date of Birth</label><input type="date" class="form-control" id="ef_dob" value="${(s.date_of_birth || '').split('T')[0]}"></div>
                                                <div class="form-group"><label class="form-label">Place of Birth</label><input type="text" class="form-control" id="ef_pob" value="${esc(s.place_of_birth)}"></div>
                                                <div class="form-group"><label class="form-label">Nationality</label><input type="text" class="form-control" id="ef_nat" value="${esc(s.nationality)}"></div>
                                                <div class="form-group" style="grid-column:1/-1"><label class="form-label">Birth Certificate No.</label><input type="text" class="form-control" id="ef_bc" value="${esc(s.birth_certificate_entry_number)}"></div>
                                            </div>
                                        </div>

                                        <div class="edit-section">
                                            <div class="section-hd"><i class="fas fa-graduation-cap"></i> Academic Information</div>
                                            <div class="form-grid-3">
                                                <div class="form-group"><label class="form-label">Registration No.</label><input type="text" class="form-control" id="ef_reg" value="${esc(s.registration_number)}"></div>
                                                <div class="form-group"><label class="form-label">Admission No.</label><input type="text" class="form-control" id="ef_adm" value="${esc(s.admission_number)}"></div>
                                                <div class="form-group"><label class="form-label">Admission Year</label><input type="number" class="form-control" id="ef_admyr" value="${esc(s.admission_year)}"></div>
                                                <div class="form-group"><label class="form-label">Date of Admission</label><input type="date" class="form-control" id="ef_admdt" value="${(s.date_of_admission || '').split('T')[0]}"></div>
                                                <div class="form-group"><label class="form-label">Class / Senior</label><input type="text" class="form-control" id="ef_senior" value="${esc(s.senior)}" readonly></div>
                                                <div class="form-group"><label class="form-label">Stream</label><input type="text" class="form-control" id="ef_stream" value="${esc(s.stream)}" readonly></div>
                                                <div class="form-group"><label class="form-label">PLE Score</label><input type="text" class="form-control" id="ef_ple" value="${esc(s.ple_score)}"></div>
                                                <div class="form-group"><label class="form-label">UCE Score</label><input type="text" class="form-control" id="ef_uce" value="${esc(s.uce_score)}"></div>
                                            </div>
                                            <div class="form-grid-2">
                                                <div class="form-group"><label class="form-label">Previous School</label><input type="text" class="form-control" id="ef_prev" value="${esc(s.previous_school)}"></div>
                                                <div class="form-group"><label class="form-label">Primary School</label><input type="text" class="form-control" id="ef_prim" value="${esc(s.primary_school_name)}"></div>
                                            </div>
                                        </div>

                                        <div class="edit-section">
                                            <div class="section-hd"><i class="fas fa-phone"></i> Contact & Guardian</div>
                                            <div class="form-grid-3">
                                                <div class="form-group"><label class="form-label">Primary Contact</label><input type="text" class="form-control" id="ef_pc" value="${esc(s.primary_contact)}"></div>
                                                <div class="form-group"><label class="form-label">Other Contact</label><input type="text" class="form-control" id="ef_oc" value="${esc(s.other_contact)}"></div>
                                                <div class="form-group"><label class="form-label">Home Address</label><input type="text" class="form-control" id="ef_addr" value="${esc(s.home_address)}"></div>
                                            </div>
                                            <div class="form-grid-2">
                                                <div class="form-group"><label class="form-label">Guardian Names</label><input type="text" class="form-control" id="ef_gn" value="${esc(s.guardian_names)}"></div>
                                                <div class="form-group"><label class="form-label">Relation</label><input type="text" class="form-control" id="ef_rel" value="${esc(s.relation)}"></div>
                                                <div class="form-group"><label class="form-label">Guardian Phone</label><input type="text" class="form-control" id="ef_gph" value="${esc(s.guardian_phone)}"></div>
                                                <div class="form-group"><label class="form-label">Guardian Email</label><input type="email" class="form-control" id="ef_gem" value="${esc(s.guardian_email)}"></div>
                                            </div>
                                        </div>

                                        <div class="edit-section">
                                            <div class="section-hd"><i class="fas fa-info-circle"></i> Additional</div>
                                            <div class="form-grid-2">
                                                <div class="form-group"><label class="form-label">Medical History</label><textarea class="form-control" id="ef_med" rows="3">${esc(s.medical_history)}</textarea></div>
                                                <div class="form-group"><label class="form-label">Comments</label><textarea class="form-control" id="ef_com" rows="3">${esc(s.comments)}</textarea></div>
                                            </div>
                                        </div>

                                        <div class="edit-section">
                                            <div class="section-hd"><i class="fas fa-camera"></i> Student Photo</div>
                                            <div style="margin-bottom:.75rem;">${photoHtml}</div>
                                            <div class="photo-upload-wrap" id="photoWrap">
                                                <input type="file" id="ef_photo" accept="image/jpg,image/jpeg,image/png,image/gif">
                                                <i class="fas fa-cloud-upload-alt" style="font-size:1.5rem;color:var(--t3);display:block;margin-bottom:.5rem;"></i>
                                                <p style="margin:0;font-size:.85rem;font-weight:600;color:var(--t2);">Click to upload photo</p>
                                                <p style="margin:.2rem 0 0;font-size:.75rem;color:var(--t3);">JPG, PNG or GIF · max 5MB</p>
                                            </div>
                                            <div id="photoErr" style="font-size:.75rem;color:var(--r);margin-top:.35rem;"></div>
                                        </div>`;
        }

        function esc(v) { return (v ?? '').toString().replace(/"/g, '&quot;').replace(/</g, '&lt;'); }

        let selectedPhotoFile = null; // 👈 store file here, outside DOM

        function initPhotoUpload() {
            selectedPhotoFile = null; // reset on each edit modal open
            const input = document.getElementById('ef_photo');
            const wrap = document.getElementById('photoWrap');
            if (!input) return;

            input.addEventListener('change', function () {
                const file = this.files[0];
                if (!file) return;

                if (file.size > 5 * 1024 * 1024) {
                    document.getElementById('photoErr').textContent = 'File too large (max 5MB)';
                    return;
                }

                selectedPhotoFile = file; // 👈 save reference BEFORE DOM changes

                const reader = new FileReader();
                reader.onload = e => {
                    wrap.innerHTML = `
                                            <img src="${e.target.result}" style="max-height:120px;border-radius:8px;object-fit:cover;display:block;margin:0 auto;">
                                            <button type="button" onclick="resetPhotoUpload()" style="position:absolute;top:8px;right:8px;width:28px;height:28px;border-radius:50%;background:var(--r);color:#fff;border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;">
                                                <i class='fas fa-times' style='font-size:.7rem;'></i>
                                            </button>`;
                    wrap.style.position = 'relative';
                };
                reader.readAsDataURL(file);
            });
        }

        function resetPhotoUpload() {
            selectedPhotoFile = null; // 👈 clear saved file
            document.getElementById('photoWrap').innerHTML = `
                                    <input type="file" id="ef_photo" accept="image/jpg,image/jpeg,image/png,image/gif">
                                    <i class="fas fa-cloud-upload-alt" style="font-size:1.5rem;color:var(--t3);display:block;margin-bottom:.5rem;"></i>
                                    <p style="margin:0;font-size:.85rem;font-weight:600;color:var(--t2);">Click to upload photo</p>
                                    <p style="margin:.2rem 0 0;font-size:.75rem;color:var(--t3);">JPG, PNG or GIF · max 5MB</p>`;
            initPhotoUpload();
        }

        // ── SAVE STUDENT ───────────────────────────────────────────────────
        function saveStudent() {
            const fn = document.getElementById('ef_firstname')?.value.trim();
            const ln = document.getElementById('ef_lastname')?.value.trim();
            const gn = document.getElementById('ef_gender')?.value;

            if (!fn || !ln || !gn) {
                Swal.fire({ icon: 'warning', title: 'Missing Fields', text: 'First name, last name and gender are required.', confirmButtonColor: '#2f2ccb' });
                return;
            }

            Swal.fire({
                title: 'Save Changes?',
                html: `Update record for <strong>${fn} ${ln}</strong>?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#2f2ccb',
                cancelButtonColor: '#94a3b8',
                confirmButtonText: '<i class="fas fa-save"></i> Save',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then(result => {
                if (!result.isConfirmed) return;

                const saveBtn = document.getElementById('saveBtn');
                saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving…';
                saveBtn.disabled = true;

                const fd = new FormData();
                fd.append('student_id', currentEditId);
                fd.append('firstname', fn);
                fd.append('lastname', ln);
                fd.append('gender', gn);
                fd.append('date_of_birth', document.getElementById('ef_dob')?.value || '');
                fd.append('place_of_birth', document.getElementById('ef_pob')?.value || '');
                fd.append('nationality', document.getElementById('ef_nat')?.value || '');
                fd.append('birth_certificate_entry_number', document.getElementById('ef_bc')?.value || '');
                fd.append('registration_number', document.getElementById('ef_reg')?.value || '');
                fd.append('admission_number', document.getElementById('ef_adm')?.value || '');
                fd.append('admission_year', document.getElementById('ef_admyr')?.value || '');
                fd.append('date_of_admission', document.getElementById('ef_admdt')?.value || '');
                fd.append('ple_score', document.getElementById('ef_ple')?.value || '');
                fd.append('uce_score', document.getElementById('ef_uce')?.value || '');
                fd.append('previous_school', document.getElementById('ef_prev')?.value || '');
                fd.append('primary_school_name', document.getElementById('ef_prim')?.value || '');
                fd.append('primary_contact', document.getElementById('ef_pc')?.value || '');
                fd.append('other_contact', document.getElementById('ef_oc')?.value || '');
                fd.append('home_address', document.getElementById('ef_addr')?.value || '');
                fd.append('guardian_names', document.getElementById('ef_gn')?.value || '');
                fd.append('relation', document.getElementById('ef_rel')?.value || '');
                fd.append('guardian_phone', document.getElementById('ef_gph')?.value || '');
                fd.append('guardian_email', document.getElementById('ef_gem')?.value || '');
                fd.append('medical_history', document.getElementById('ef_med')?.value || '');
                fd.append('comments', document.getElementById('ef_com')?.value || '');

                if (selectedPhotoFile) {
                    fd.append('student_photo', selectedPhotoFile);
                }

                fetch(`{{ url('/students/update') }}/${currentEditId}`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                    body: fd
                })
                    .then(r => r.json())
                    .then(data => {
                        saveBtn.innerHTML = '<i class="fas fa-save"></i> Save Changes';
                        saveBtn.disabled = false;
                        closeModal('editModal');
                        Swal.fire({ icon: 'success', title: 'Updated!', text: data.message || 'Student updated successfully.', confirmButtonColor: '#2f2ccb', timer: 2500, timerProgressBar: true })
                            .then(() => location.reload());
                    })
                    .catch(() => {
                        saveBtn.innerHTML = '<i class="fas fa-save"></i> Save Changes';
                        saveBtn.disabled = false;
                        Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to update student.', confirmButtonColor: '#2f2ccb' });
                    });
            });
        }

        // ── DELETE STUDENT ──────────────────────────────────────────────────
        function deleteStudent(id, name) {
            Swal.fire({
                title: 'Delete Student?',
                html: `This will permanently remove <strong>${name}</strong> from the system. This cannot be undone.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#94a3b8',
                confirmButtonText: 'Yes, Delete',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then(result => {
                if (!result.isConfirmed) return;

                fetch(`{{ url('/students/delete') }}/${id}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            const row = document.getElementById('row-' + id);
                            if (row) {
                                row.style.transition = 'opacity .3s, transform .3s';
                                row.style.opacity = '0';
                                row.style.transform = 'translateX(20px)';
                                setTimeout(() => row.remove(), 300);
                            }
                            Swal.fire({ icon: 'success', title: 'Deleted!', text: data.message, confirmButtonColor: '#2f2ccb', timer: 2000, timerProgressBar: true });
                        } else {
                            Swal.fire({ icon: 'error', title: 'Error', text: data.message, confirmButtonColor: '#2f2ccb' });
                        }
                    })
                    .catch(() => Swal.fire({ icon: 'error', title: 'Error', text: 'Something went wrong.', confirmButtonColor: '#2f2ccb' }));
            });
        }

        // ── ID CARD FUNCTIONS ───────────────────────────────────────────────────

        // View existing active card
        function viewCard(cardId) {
            window.open(`/student-id-cards/preview/${cardId}`, '_blank');
        }

        // // Print ID card
        // function printCard(cardId) {
        //     window.open(`/student-id-cards/print/${cardId}`, '_blank');
        // }

        // Generate single card for student
        function generateSingleCard(studentId, studentName) {
            Swal.fire({
                title: 'Generate ID Card?',
                text: `Generate a new ID card for ${studentName}? This will revoke any existing active card.`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#2f2ccb',
                confirmButtonText: 'Yes, generate',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Generating...',
                        text: 'Please wait',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading()
                    });

                    fetch('/student-id-cards/generate-single', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': CSRF
                        },
                        body: JSON.stringify({ student_id: studentId })
                    })
                        .then(r => r.json())
                        .then(data => {
                            if (data.status === 'success') {
                                Swal.fire({
                                    title: 'Success!',
                                    text: data.message,
                                    icon: 'success',
                                    confirmButtonColor: '#2f2ccb'
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire('Error', data.message, 'error');
                            }
                        })
                        .catch((err) => {
                            console.error('Generate error:', err);
                            Swal.fire('Error', 'Failed to generate ID card', 'error');
                        });
                }
            });
        }

        // Reactivate a revoked card
        function reactivateCard(cardId, studentName) {
            Swal.fire({
                title: 'Reactivate ID Card?',
                text: `Reactivate the ID card for ${studentName}? This will make the card valid again.`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#d97706',
                confirmButtonText: 'Yes, reactivate',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Reactivating...',
                        text: 'Please wait',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading()
                    });

                    fetch(`/student-id-cards/reactivate/${cardId}`, {
                        method: 'PATCH',
                        headers: {
                            'X-CSRF-TOKEN': CSRF,
                            'Content-Type': 'application/json'
                        }
                    })
                        .then(r => r.json())
                        .then(data => {
                            if (data.status === 'success') {
                                Swal.fire({
                                    title: 'Reactivated!',
                                    text: data.message,
                                    icon: 'success',
                                    confirmButtonColor: '#2f2ccb'
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire('Error', data.message, 'error');
                            }
                        })
                        .catch((err) => {
                            console.error('Reactivate error:', err);
                            Swal.fire('Error', 'Failed to reactivate ID card', 'error');
                        });
                }
            });
        }

        // ── STREAM SEARCH ──────────────────────────────────────────────
        const searchTimers = {};

        document.querySelectorAll('.stream-search-input').forEach(input => {
            input.addEventListener('input', function () {
                const senior = this.dataset.senior;
                const stream = this.dataset.stream;
                const key = senior + '|' + stream;

                clearTimeout(searchTimers[key]);
                searchTimers[key] = setTimeout(() => {
                    loadStreamPage(senior, stream, this.value.trim(), 1);
                }, 350); // debounce
            });
        });

        function loadStreamPage(senior, stream, q, page) {
            const tbody = document.getElementById(`tbody-${senior}-${stream}`);
            const paginationWrap = document.getElementById(`pagination-${senior}-${stream}`);

            tbody.innerHTML = `<tr><td colspan="8" style="text-align:center;padding:2rem;"><div class="spinner" style="margin:0 auto;"></div></td></tr>`;

            const params = new URLSearchParams({ senior, stream, q, page });

            fetch(`{{ route('students.search') }}?${params.toString()}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            })
                .then(r => r.json())
                .then(data => {
                    tbody.innerHTML = data.rows;
                    paginationWrap.innerHTML = data.pagination;
                    attachPaginationHandlers(senior, stream);

                    if (data.total === 0) {
                        tbody.innerHTML = `<tr><td colspan="8"><div class="empty-state"><div class="empty-icon-wrap"><i class="fas fa-user-slash"></i></div><p style="margin:0;font-size:.85rem;">No students match your search.</p></div></td></tr>`;
                    }
                })
                .catch(() => {
                    tbody.innerHTML = `<tr><td colspan="8" style="text-align:center;color:var(--r);padding:1.5rem;">Failed to load results.</td></tr>`;
                });
        }

        // Intercept pagination link clicks so they don't full-reload the page
        function attachPaginationHandlers(senior, stream) {
            const wrap = document.getElementById(`pagination-${senior}-${stream}`);
            if (!wrap) return;

            wrap.querySelectorAll('a.page-link').forEach(link => {
                link.addEventListener('click', function (e) {
                    e.preventDefault();
                    const url = new URL(this.href);
                    const page = url.searchParams.get(`page_${senior}_${stream}`) || 1;
                    const searchInput = document.querySelector(
                        `.stream-search-input[data-senior="${senior}"][data-stream="${stream}"]`
                    );
                    loadStreamPage(senior, stream, searchInput ? searchInput.value.trim() : '', page);
                });
            });
        }

        // Wire up pagination handlers on initial page load too
        document.querySelectorAll('[id^="pagination-"]').forEach(wrap => {
            const [_, senior, stream] = wrap.id.split('-');
            attachPaginationHandlers(senior, stream);
        });
    </script>
@endsection