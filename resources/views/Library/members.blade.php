@extends('layouts-side-bar.master')

@section('css')
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        /* Your existing styles here (same as provided) */
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
            --lib-violet: #7c3aed;
            --lib-violet-l: rgba(124, 58, 237, .12);
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

        .btn-danger-lib {
            background: var(--lib-rose-l);
            color: var(--lib-rose);
        }

        .btn-danger-lib:hover {
            background: var(--lib-rose);
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

        .btn-warn-lib {
            background: var(--lib-amber-l);
            color: var(--lib-amber);
        }

        .lib-table {
            width: 100%;
            border-collapse: collapse;
        }

        .lib-table th {
            padding: .75rem 1rem;
            text-align: left;
            font-size: .75rem;
            font-weight: 700;
            color: #fff;
            background: #2c29ca;
            text-transform: uppercase;
            letter-spacing: .05em;
            border-bottom: none;
        }

        .lib-table td {
            padding: .85rem 1rem;
            border-bottom: 1px solid var(--border);
            font-size: .875rem;
            color: var(--text-1);
            vertical-align: middle;
        }

        .lib-table tr:last-child td {
            border-bottom: none;
        }

        .lib-table tr:hover td {
            background: #f8fafc;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            padding: .25rem .65rem;
            border-radius: 999px;
            font-size: .7rem;
            font-weight: 700;
        }

        .badge-active {
            background: var(--lib-green-l);
            color: var(--lib-green);
        }

        .badge-suspended {
            background: var(--lib-rose-l);
            color: var(--lib-rose);
        }

        .badge-expired {
            background: var(--lib-amber-l);
            color: var(--lib-amber);
        }

        .badge-student {
            background: var(--lib-blue-l);
            color: var(--lib-blue);
        }

        .badge-teacher {
            background: var(--lib-violet-l);
            color: var(--lib-violet);
        }

        .avatar-initials {
            width: 38px;
            height: 38px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .8rem;
            font-weight: 700;
            color: #fff;
            flex-shrink: 0;
        }

        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, .5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal-box {
            background: #fff;
            border-radius: 20px;
            padding: 2rem;
            width: 100%;
            max-width: 520px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, .2);
            animation: slideUp .25s ease;
            max-height: 90vh;
            overflow-y: auto;
        }

        @keyframes slideUp {
            from {
                transform: translateY(20px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .modal-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--text-1);
            margin-bottom: 1.25rem;
            display: flex;
            align-items: center;
            gap: .5rem;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-label {
            display: block;
            font-size: .8rem;
            font-weight: 600;
            color: var(--text-2);
            margin-bottom: .4rem;
        }

        .form-control {
            width: 100%;
            padding: .6rem .85rem;
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

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: .75rem;
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

        .filter-bar {
            display: flex;
            gap: .75rem;
            flex-wrap: wrap;
            align-items: center;
        }

        .filter-bar .form-control {
            width: auto;
            min-width: 140px;
        }

        .card-number {
            font-family: 'JetBrains Mono', monospace;
            font-size: .78rem;
            color: var(--text-3);
            background: #f8fafc;
            padding: .2rem .5rem;
            border-radius: 6px;
        }

        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: var(--text-3);
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            display: block;
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

        .tabs {
            display: flex;
            border-bottom: 2px solid var(--border);
            margin-bottom: 1.5rem;
        }

        .tab {
            padding: .6rem 1.25rem;
            font-size: .85rem;
            font-weight: 600;
            color: var(--text-2);
            cursor: pointer;
            border-bottom: 2px solid transparent;
            margin-bottom: -2px;
            transition: .2s;
        }

        .tab.active {
            color: var(--lib-blue);
            border-bottom-color: var(--lib-blue);
        }

        .type-pill {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: .5rem;
    padding: .65rem 1rem;
    border: 2px solid var(--border);
    border-radius: 12px;
    font-size: .85rem;
    font-weight: 600;
    color: var(--text-2);
    transition: all .2s;
    text-align: center;
}
.type-pill:hover {
    border-color: var(--lib-blue);
    color: var(--lib-blue);
}
input[type="radio"]:checked + .type-pill {
    background: var(--lib-blue-l);
    border-color: var(--lib-blue);
    color: var(--lib-blue);
}
.lib-sel-card {
    border: 1.5px solid var(--border);
    border-radius: 10px;
    padding: .6rem .5rem;
    text-align: center;
    cursor: pointer;
    transition: all .18s;
    background: var(--surface);
}
.lib-sel-card:hover {
    border-color: var(--lib-blue);
    background: var(--lib-blue-l);
    transform: translateY(-1px);
}
.lib-sel-card.selected {
    border-color: var(--lib-blue);
    background: var(--lib-blue-l);
}
.lib-student-chip {
    display: flex;
    align-items: center;
    gap: .6rem;
    padding: .65rem .75rem;
    border: 1.5px solid var(--border);
    border-radius: 12px;
    cursor: pointer;
    transition: all .18s;
    background: #f8fafc;
    user-select: none;
}
.lib-student-chip:hover {
    border-color: var(--lib-blue);
    background: var(--lib-blue-l);
    transform: translateY(-1px);
}
.lib-student-chip.selected {
    background: linear-gradient(135deg, var(--lib-blue) 0%, var(--lib-blue-d) 100%);
    color: #fff;
    border-color: var(--lib-blue);
    box-shadow: 0 4px 12px rgba(44,41,202,.25);
}
.lib-student-chip i {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: rgba(44,41,202,.1);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--lib-blue);
    flex-shrink: 0;
    font-size: .8rem;
}
.lib-student-chip.selected i {
    background: rgba(255,255,255,.2);
    color: #fff;
}
.lib-student-chip .chip-name { font-size: .82rem; font-weight: 600; line-height: 1.2; }
.lib-student-chip .chip-adm  { font-size: .7rem; opacity: .75; margin-top: .1rem; }

/* Library Members - Stack layout on mobile */

/* Main grid layout - side by side on large screens */
[style*="display:grid;grid-template-columns:1fr 360px;"] {
    display: grid;
    grid-template-columns: 1fr 360px;
    gap: 1.5rem;
    align-items: start;
}

/* Hero section */
.lib-hero {
    padding: 2rem 2.5rem;
}

/* Member table responsive */
.lib-table {
    min-width: 700px;
}

.lib-table th,
.lib-table td {
    padding: .75rem 1rem;
}

/* Filter bar */
.filter-bar {
    display: flex;
    gap: .75rem;
    flex-wrap: wrap;
    align-items: center;
}

.filter-bar .form-control {
    width: auto;
    min-width: 140px;
}

/* Type pills */
.type-pill {
    padding: .65rem 1rem;
    min-height: 48px;
}

/* Student chips grid */
#libStudentChips {
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: .6rem;
    max-height: 260px;
}

.lib-student-chip {
    padding: .65rem .75rem;
}

/* Quick stats panel */
[style*="display:flex;flex-direction:column;gap:1rem;"] {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

/* Tablet - reduce right column */
@media (max-width: 992px) {
    [style*="display:grid;grid-template-columns:1fr 360px;"] {
        grid-template-columns: 1fr 280px;
        gap: 1.25rem;
    }
    
    .lib-table th,
    .lib-table td {
        padding: .65rem .85rem;
        font-size: .8rem;
    }
    
    .filter-bar .form-control {
        min-width: 120px;
        font-size: .8rem;
    }
}

/* Tablet - stack vertically */
@media (max-width: 768px) {
    [style*="display:grid;grid-template-columns:1fr 360px;"] {
        grid-template-columns: 1fr !important;
        gap: 1.25rem;
    }
    
    /* Quick stats panel moves below */
    [style*="display:flex;flex-direction:column;gap:1rem;"] {
        flex-direction: row !important;
        flex-wrap: wrap;
    }
    
    [style*="display:flex;flex-direction:column;gap:1rem;"] .lib-card {
        flex: 1;
        min-width: 200px;
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
    
    .lib-table {
        min-width: 600px;
        font-size: .8rem;
    }
    
    .lib-table th,
    .lib-table td {
        padding: .5rem .75rem;
    }
    
    .lib-card-header {
        flex-direction: column;
        align-items: flex-start;
        gap: .75rem;
        padding: 1rem 1.25rem;
    }
    
    .lib-card-header h3 {
        font-size: .9rem;
    }
    
    .lib-card-header .btn-lib {
        width: 100%;
        justify-content: center;
    }
    
    .lib-card-body {
        padding: 1rem;
    }
    
    .filter-bar {
        flex-direction: column;
        align-items: stretch;
    }
    
    .filter-bar .form-control {
        width: 100%;
        min-width: auto;
    }
    
    .filter-bar .btn-lib {
        width: 100%;
        justify-content: center;
    }
    
    .avatar-initials {
        width: 32px;
        height: 32px;
        font-size: .7rem;
    }
}

/* Mobile landscape */
@media (max-width: 576px) {
    [style*="padding:1.5rem;"] {
        padding: 0.75rem !important;
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
    
    .lib-table {
        min-width: 500px;
        font-size: .75rem;
    }
    
    .lib-table th {
        font-size: .65rem;
        padding: .4rem .6rem;
    }
    
    .lib-table td {
        padding: .4rem .6rem;
        font-size: .75rem;
    }
    
    .lib-table td:first-child {
        min-width: 120px;
    }
    
    .lib-table td:nth-child(2) {
        min-width: 80px;
    }
    
    .badge {
        font-size: .6rem;
        padding: .15rem .5rem;
    }
    
    .avatar-initials {
        width: 28px;
        height: 28px;
        font-size: .6rem;
        border-radius: 8px;
    }
    
    .card-number {
        font-size: .7rem;
        padding: .1rem .4rem;
    }
    
    /* Stats panel on mobile */
    [style*="display:flex;flex-direction:column;gap:1rem;"] {
        flex-direction: column !important;
    }
    
    [style*="display:flex;flex-direction:column;gap:1rem;"] .lib-card {
        flex: none;
        width: 100%;
    }
    
    [style*="display:grid;grid-template-columns:1fr 1fr;gap:1rem;"] {
        grid-template-columns: 1fr 1fr !important;
        gap: .75rem !important;
    }
    
    [style*="display:grid;grid-template-columns:1fr 1fr;gap:1rem;"] > div {
        padding: .75rem !important;
    }
    
    [style*="display:grid;grid-template-columns:1fr 1fr;gap:1rem;"] > div div:first-child {
        font-size: 1.25rem !important;
    }
    
    /* Form elements */
    .form-control {
        font-size: 14px;
        padding: .5rem .75rem;
    }
    
    .form-label {
        font-size: .75rem;
    }
    
    /* Buttons */
    .btn-lib {
        font-size: .75rem;
        padding: .4rem .75rem;
    }
    
    .lib-table td:last-child .btn-lib {
        padding: .25rem .6rem;
        font-size: .7rem;
    }
    
    /* Modal */
    .modal-box {
        margin: 1rem;
        padding: 1.5rem;
        max-width: 100%;
        max-height: 95vh;
    }
    
    .modal-title {
        font-size: 1rem;
    }
    
    .form-row {
        grid-template-columns: 1fr;
        gap: .5rem;
    }
    
    /* Type pills in modal */
    .type-pill {
        font-size: .8rem;
        padding: .5rem .75rem;
        min-height: 42px;
    }
    
    /* Student selection chips */
    #libStudentChips {
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: .5rem;
        max-height: 200px;
        padding: .5rem;
    }
    
    .lib-student-chip {
        padding: .5rem .6rem;
        font-size: .75rem;
    }
    
    .lib-student-chip .chip-name {
        font-size: .75rem;
    }
    
    .lib-student-chip .chip-adm {
        font-size: .65rem;
    }
    
    .lib-student-chip i {
        width: 25px;
        height: 25px;
        font-size: .7rem;
    }
    
    /* Class/stream grids in modal */
    #libClassGrid,
    #libStreamGrid {
        grid-template-columns: repeat(auto-fill, minmax(70px, 1fr)) !important;
        gap: .4rem !important;
    }
    
    .lib-sel-card {
        padding: .4rem .3rem !important;
        font-size: .7rem !important;
    }
    
    .lib-sel-card i {
        font-size: .7rem !important;
    }
    
    .lib-sel-card div {
        font-size: .65rem !important;
    }
    
    /* Pagination */
    nav[role="navigation"] {
        font-size: .75rem;
    }
    
    nav[role="navigation"] .pagination {
        flex-wrap: wrap;
        justify-content: center;
    }
    
    nav[role="navigation"] .page-link {
        padding: .3rem .5rem;
        font-size: .7rem;
    }
}

/* Very small screens */
@media (max-width: 400px) {
    [style*="padding:1.5rem;"] {
        padding: 0.5rem !important;
    }
    
    .lib-hero {
        padding: .75rem 1rem;
        border-radius: 14px;
    }
    
    .lib-hero [style*="font-size:1.6rem;"] {
        font-size: 1rem !important;
    }
    
    .lib-table {
        min-width: 400px;
        font-size: .7rem;
    }
    
    .lib-table th,
    .lib-table td {
        padding: .3rem .5rem;
    }
    
    .lib-table td:first-child {
        min-width: 80px;
    }
    
    .lib-card-header {
        padding: .6rem .75rem;
    }
    
    .lib-card-body {
        padding: .6rem .75rem;
    }
    
    .avatar-initials {
        width: 24px;
        height: 24px;
        font-size: .5rem;
        border-radius: 6px;
    }
    
    .card-number {
        font-size: .6rem;
        padding: .1rem .3rem;
    }
    
    /* Modal on very small screens */
    .modal-box {
        padding: .75rem;
        margin: .5rem;
    }
    
    .modal-title {
        font-size: .85rem;
    }
    
    #libStudentChips {
        grid-template-columns: 1fr 1fr;
        gap: .4rem;
        max-height: 180px;
    }
    
    .lib-student-chip {
        padding: .4rem .5rem;
        font-size: .7rem;
    }
    
    .lib-student-chip .chip-name {
        font-size: .7rem;
    }
    
    .lib-student-chip .chip-adm {
        font-size: .6rem;
    }
    
    .lib-student-chip i {
        width: 22px;
        height: 22px;
        font-size: .6rem;
    }
    
    #libClassGrid,
    #libStreamGrid {
        grid-template-columns: repeat(auto-fill, minmax(60px, 1fr)) !important;
    }
    
    .lib-sel-card {
        padding: .3rem .2rem !important;
    }
    
    .lib-sel-card div {
        font-size: .6rem !important;
    }
}

/* Fix horizontal scroll on mobile */
@media (max-width: 768px) {
    [style*="overflow-x:auto;"] {
        -webkit-overflow-scrolling: touch;
        margin: 0 -0.5rem;
        padding: 0 0.5rem;
    }
}

/* Improve touch targets on mobile */
@media (max-width: 576px) {
    .btn-lib,
    .lib-table td .btn-lib,
    button.btn-lib,
    .type-pill,
    .lib-sel-card,
    .lib-student-chip {
        cursor: pointer;
        -webkit-tap-highlight-color: transparent;
        min-height: 40px;
    }
    
    .lib-card-header .btn-lib {
        width: 100%;
        justify-content: center;
    }
    
    .lib-table td:last-child button {
        padding: .3rem .7rem;
        min-height: 32px;
        min-width: 32px;
    }
    
    .lib-sel-card:active,
    .lib-student-chip:active {
        transform: scale(0.96);
    }
}

/* Smooth transitions */
.lib-card,
.lib-hero,
.btn-lib,
.lib-sel-card,
.lib-student-chip,
.type-pill {
    transition: all 0.2s ease;
}
    </style>
@endsection

@section('content')
    <div style="padding:1.5rem;">

        <div class="lib-hero mb-4">
            <div style="font-size:1.6rem;font-weight:800;margin:0 0 .25rem;">
                <i class="fas fa-users" style="color:#a5b4fc;margin-right:.5rem;"></i>Library Members
            </div>
            <div style="font-size:.875rem;opacity:.7;">Manage students and teachers registered as library members</div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 360px;gap:1.5rem;align-items:start;">

            {{-- Member List --}}
            <div class="lib-card">
                <div class="lib-card-header">
                    <h3><i class="fas fa-id-card" style="color:var(--lib-teal);"></i> All Members</h3>
                    <button onclick="openAddMemberModal()" class="btn-lib btn-primary-lib">
                        <i class="fas fa-plus"></i> Add Member
                    </button>
                </div>

                {{-- Filters --}}
                <div style="padding:1rem 1.5rem;border-bottom:1px solid var(--border);background:#f8fafc;">
                    <form method="GET" action="{{ route('library.members') }}" class="filter-bar" id="filterForm">
                        <input type="text" name="search" class="form-control" placeholder="Search by name or card no..."
                            value="{{ request('search') }}" style="flex:1;min-width:200px;">
                        <select name="type" class="form-control">
                            <option value="">All Types</option>
                            <option value="student" {{ request('type') == 'student' ? 'selected' : '' }}>Students</option>
                            <option value="teacher" {{ request('type') == 'teacher' ? 'selected' : '' }}>Teachers</option>
                        </select>
                        <select name="status" class="form-control">
                            <option value="">All Status</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>Suspended
                            </option>
                            <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expired</option>
                        </select>
                        <button type="submit" class="btn-lib btn-outline-lib"><i class="fas fa-search"></i></button>
                        @if(request()->hasAny(['search', 'type', 'status']))
                            <a href="{{ route('library.members') }}" class="btn-lib btn-outline-lib"><i
                                    class="fas fa-times"></i></a>
                        @endif
                    </form>
                </div>

                <div style="overflow-x:auto;">
                    @if($members->count())
                        <table class="lib-table">
                            <thead>
                                <tr>
                                    <th>Member</th>
                                    <th>Card No.</th>
                                    <th>Type</th>
                                    <th>Max Books</th>
                                    <th>Joined</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($members as $member)
                                    <tr>
                                        <td>
                                            <div style="display:flex;align-items:center;gap:.75rem;">
                                                <div class="avatar-initials"
                                                    style="background:{{ $member->member_type === 'teacher' ? 'var(--lib-violet)' : 'var(--lib-blue)' }};">
                                                    {{ strtoupper(substr($member->name, 0, 2)) }}
                                                </div>
                                                <div>
                                                    <div style="font-weight:600;">{{ $member->name }}</div>
                                                    <div style="font-size:.75rem;color:var(--text-3);">{{ $member->email }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td><span class="card-number">{{ $member->library_card_number }}</span></td>
                                        <td>
                                            <span
                                                class="badge {{ $member->member_type === 'teacher' ? 'badge-teacher' : 'badge-student' }}">
                                                <i class="fas {{ $member->member_type === 'teacher' ? 'fa-chalkboard-teacher' : 'fa-user-graduate' }}"
                                                    style="margin-right:.3rem;"></i>
                                                {{ ucfirst($member->member_type) }}
                                            </span>
                                        </td>
                                        <td style="text-align:center;">{{ $member->max_books_allowed }}</td>
                                        <td>{{ \Carbon\Carbon::parse($member->membership_date)->format('d M Y') }}</td>
                                        <td>
                                            <span class="badge badge-{{ $member->status }}">{{ ucfirst($member->status) }}</span>
                                        </td>
                                        <td>
                                            <button
                                                onclick="openEditMember({{ $member->id }}, {{ $member->max_books_allowed }}, {{ $member->max_days_allowed }}, '{{ $member->status }}', '{{ $member->expiry_date }}', '{{ addslashes($member->suspension_reason) }}')"
                                                class="btn-lib btn-outline-lib" style="padding:.35rem .75rem;">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button onclick="confirmDelete({{ $member->id }})" class="btn-lib btn-danger-lib"
                                                style="padding:.35rem .75rem;">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div style="padding:1rem 1.5rem;">{{ $members->links() }}</div>
                    @else
                        <div class="empty-state"><i class="fas fa-users"></i>No members found. Add a new member to get started.
                        </div>
                    @endif
                </div>
            </div>

            {{-- Quick Stats Panel --}}
            <div style="display:flex;flex-direction:column;gap:1rem;">
                @php
                    $total = $members->total();
                    $active = $members->getCollection()->where('status', 'active')->count();
                @endphp
                <div class="lib-card lib-card-body">
                    <div
                        style="font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text-3);margin-bottom:1rem;">
                        Member Overview</div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                        <div style="background:var(--lib-blue-l);border-radius:12px;padding:1rem;text-align:center;">
                            <div style="font-size:1.5rem;font-weight:800;color:var(--lib-blue);">{{ $members->total() }}
                            </div>
                            <div style="font-size:.75rem;color:var(--text-2);">Total</div>
                        </div>
                        <div style="background:var(--lib-green-l);border-radius:12px;padding:1rem;text-align:center;">
                            <div style="font-size:1.5rem;font-weight:800;color:var(--lib-green);">
                                {{ $members->getCollection()->where('status', 'active')->count() }}
                            </div>
                            <div style="font-size:.75rem;color:var(--text-2);">Active</div>
                        </div>
                        <div style="background:var(--lib-violet-l);border-radius:12px;padding:1rem;text-align:center;">
                            <div style="font-size:1.5rem;font-weight:800;color:var(--lib-violet);">
                                {{ $members->getCollection()->where('member_type', 'teacher')->count() }}
                            </div>
                            <div style="font-size:.75rem;color:var(--text-2);">Teachers</div>
                        </div>
                        <div style="background:var(--lib-amber-l);border-radius:12px;padding:1rem;text-align:center;">
                            <div style="font-size:1.5rem;font-weight:800;color:var(--lib-amber);">
                                {{ $members->getCollection()->where('member_type', 'student')->count() }}
                            </div>
                            <div style="font-size:.75rem;color:var(--text-2);">Students</div>
                        </div>
                    </div>
                </div>

                <div class="lib-card lib-card-body">
                    <div
                        style="font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text-3);margin-bottom:.75rem;">
                        Quick Links</div>
                    <div style="display:flex;flex-direction:column;gap:.5rem;">
                        <a href="{{ route('library.borrowings') }}" class="btn-lib"
                            style="background:var(--lib-violet-l);color:var(--lib-violet);"><i
                                class="fas fa-hand-holding-heart"></i> Manage Borrowings</a>
                        <a href="{{ route('library.fines') }}" class="btn-lib"
                            style="background:var(--lib-rose-l);color:var(--lib-rose);"><i class="fas fa-coins"></i> View
                            Fines</a>
                        <a href="{{ route('library.reservations') }}" class="btn-lib"
                            style="background:var(--lib-amber-l);color:var(--lib-amber);"><i class="fas fa-bookmark"></i>
                            Reservations</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

{{-- Add Member Modal --}}
<div class="modal-overlay" id="addMemberModal">
    <div class="modal-box" style="max-width:680px;">
        <div class="modal-title">
            <i class="fas fa-user-plus" style="color:var(--lib-blue);"></i> Register New Member
        </div>
        <form method="POST" id="addMemberForm">
            @csrf

            {{-- Member Type --}}
            <div class="form-group">
                <label class="form-label">Member Type *</label>
                <div style="display:flex;gap:.75rem;">
                    <label style="flex:1;cursor:pointer;">
                        <input type="radio" name="member_type" value="student" id="typeStudent"
                            onchange="onMemberTypeChange()" style="display:none;">
                        <div class="type-pill" id="pillStudent">
                            <i class="fas fa-user-graduate"></i> Student
                        </div>
                    </label>
                    <label style="flex:1;cursor:pointer;">
                        <input type="radio" name="member_type" value="teacher" id="typeTeacher"
                            onchange="onMemberTypeChange()" style="display:none;">
                        <div class="type-pill" id="pillTeacher">
                            <i class="fas fa-chalkboard-teacher"></i> Teacher
                        </div>
                    </label>
                </div>
            </div>

            {{-- STUDENT FLOW: Class → Stream → Students --}}
            <div id="studentFlow" style="display:none;">
                {{-- Step 1: Class --}}
                <div class="form-group" id="libStepClass">
                    <label class="form-label"><i class="fas fa-chalkboard" style="color:var(--lib-blue);"></i> 1. Select Class *</label>
                    <div id="libClassGrid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(90px,1fr));gap:.5rem;">
                        @foreach($classrooms as $cls)
                            @php $clsName = \App\Http\Controllers\Helper::recordMdname($cls->class_name) ?? $cls->class_name; @endphp
                            <div class="lib-sel-card" data-class-id="{{ $cls->class_name }}" data-class-name="{{ $clsName }}"
                                onclick="libSelectClass(this)">
                                <i class="fas fa-university" style="font-size:.85rem;color:var(--lib-blue);"></i>
                                <div style="font-size:.72rem;font-weight:700;margin-top:.25rem;">{{ $clsName }}</div>
                            </div>
                        @endforeach
                    </div>
                    <input type="hidden" id="libSelectedClassId">
                </div>

                {{-- Step 2: Stream --}}
                <div class="form-group" id="libStepStream" style="display:none;">
                    <label class="form-label">
                        <i class="fas fa-code-branch" style="color:var(--lib-blue);"></i> 2. Select Stream *
                        <button type="button" onclick="libResetToClass()"
                            style="background:none;border:none;color:var(--lib-rose);font-size:.72rem;cursor:pointer;margin-left:.5rem;">
                            <i class="fas fa-times"></i> Change Class
                        </button>
                    </label>
                    <div id="libStreamGrid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(90px,1fr));gap:.5rem;">
                        <div style="color:var(--text-3);font-size:.8rem;"><i class="fas fa-spinner fa-spin"></i> Loading...</div>
                    </div>
                    <input type="hidden" id="libSelectedStreamId">
                </div>

                {{-- Step 3: Students --}}
                <div class="form-group" id="libStepStudents" style="display:none;">
                    <label class="form-label">
                        <i class="fas fa-users" style="color:var(--lib-blue);"></i> 3. Select Students *
                        <button type="button" onclick="libResetToStream()"
                            style="background:none;border:none;color:var(--lib-rose);font-size:.72rem;cursor:pointer;margin-left:.5rem;">
                            <i class="fas fa-times"></i> Change Stream
                        </button>
                    </label>

                    {{-- Search --}}
                    <div style="position:relative;margin-bottom:.5rem;">
                        <i class="fas fa-search" style="position:absolute;left:.75rem;top:50%;transform:translateY(-50%);color:var(--text-3);font-size:.75rem;"></i>
                        <input type="text" id="libStudentSearch" class="form-control"
                            placeholder="Search student name or admission no..."
                            style="padding-left:2rem;" oninput="libFilterStudents(this.value)">
                    </div>

                    {{-- Select All --}}
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.5rem;">
                        <span style="font-size:.75rem;color:var(--text-3);" id="libStudentCount">0 students</span>
                        <button type="button" onclick="libSelectAll()"
                            style="background:var(--lib-blue-l);color:var(--lib-blue);border:none;border-radius:8px;padding:.25rem .75rem;font-size:.75rem;font-weight:600;cursor:pointer;">
                            <i class="fas fa-check-double"></i> Select All
                        </button>
                    </div>

                    {{-- Student Chips --}}
                    <div id="libStudentChips" style="
                        display:grid;
                        grid-template-columns:repeat(auto-fill,minmax(200px,1fr));
                        gap:.6rem;
                        max-height:260px;
                        overflow-y:auto;
                        border:1.5px solid var(--border);
                        border-radius:12px;
                        padding:.75rem;
                        background:#fff;
                    "></div>

                    <div id="libSelectedStudentsContainer"></div>
                    <small style="color:var(--text-3);font-size:.72rem;margin-top:.35rem;display:block;">
                        <i class="fas fa-info-circle"></i> Click chips to select/deselect. Selected count shown above.
                    </small>
                </div>
            </div>

            {{-- TEACHER FLOW: simple select --}}
            <div id="teacherFlow" style="display:none;">
                <div class="form-group">
                    <label class="form-label">Select Teacher *</label>
                    <select id="teacherSelect" class="form-control">
                        <option value="">— Select Teacher —</option>
                        @foreach($teachers as $t)
                            <option value="{{ $t->id }}">{{ $t->firstname }} {{ $t->surname }}</option>
                        @endforeach
                    </select>
                    <div id="libSelectedTeacherContainer"></div>
                </div>
            </div>

            {{-- Dates --}}
            <div id="libDatesSection" style="display:none;">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Membership Date *</label>
                        <input type="date" name="membership_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Expiry Date</label>
                        <input type="date" name="expiry_date" class="form-control">
                    </div>
                </div>
            </div>

            <div style="display:flex;gap:.75rem;justify-content:flex-end;margin-top:1.5rem;">
                <button type="button" onclick="closeAddMemberModal()" class="btn-lib btn-outline-lib">Cancel</button>
                <button type="submit" class="btn-lib btn-primary-lib" id="submitAddBtn">
                    <i class="fas fa-save"></i> Register
                </button>
            </div>
        </form>
    </div>
</div>

    {{-- Edit Member Modal --}}
    <div class="modal-overlay" id="editMemberModal">
        <div class="modal-box">
            <div class="modal-title"><i class="fas fa-edit" style="color:var(--lib-blue);"></i> Edit Member</div>
            <form method="POST" id="editMemberForm">
                @csrf @method('PUT')
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Max Books Allowed *</label>
                        <input type="number" name="max_books_allowed" id="editMaxBooks" class="form-control" min="1"
                            required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Max Loan Days *</label>
                        <input type="number" name="max_days_allowed" id="editMaxDays" class="form-control" min="1" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Expiry Date</label>
                        <input type="date" name="expiry_date" id="editExpiry" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Status *</label>
                        <select name="status" id="editMemberStatus" class="form-control" onchange="toggleSuspensionReason()"
                            required>
                            <option value="active">Active</option>
                            <option value="suspended">Suspended</option>
                            <option value="expired">Expired</option>
                        </select>
                    </div>
                </div>
                <div class="form-group" id="suspensionReasonGroup" style="display:none;">
                    <label class="form-label">Suspension Reason</label>
                    <textarea name="suspension_reason" id="editSuspensionReason" class="form-control" rows="2"
                        placeholder="Reason for suspension..."></textarea>
                </div>
                <div style="display:flex;gap:.75rem;justify-content:flex-end;margin-top:1.5rem;">
                    <button type="button" onclick="closeEditMemberModal()" class="btn-lib btn-outline-lib">Cancel</button>
                    <button type="submit" class="btn-lib btn-primary-lib" id="submitEditBtn"><i class="fas fa-save"></i>
                        Update</button>
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
const Toast = Swal.mixin({
    toast: true, position: 'top-end', showConfirmButton: false,
    timer: 3000, timerProgressBar: true,
    didOpen: t => { t.addEventListener('mouseenter', Swal.stopTimer); t.addEventListener('mouseleave', Swal.resumeTimer); }
});

// ── Modal open/close ──────────────────────────────────────────────
function openAddMemberModal() {
    libState = { classId: null, className: null, streamId: null, streamName: null };
    document.getElementById('addMemberForm').reset();
    document.getElementById('studentFlow').style.display = 'none';
    document.getElementById('teacherFlow').style.display  = 'none';
    document.getElementById('libDatesSection').style.display = 'none';
    document.getElementById('libStepStream').style.display   = 'none';
    document.getElementById('libStepStudents').style.display = 'none';
    document.getElementById('libSelectedStudentsContainer').innerHTML = '';
    document.getElementById('libSelectedTeacherContainer').innerHTML  = '';
    document.querySelectorAll('#libClassGrid .lib-sel-card').forEach(c => c.classList.remove('selected'));
    document.getElementById('pillStudent').style.cssText = '';
    document.getElementById('pillTeacher').style.cssText = '';
    document.getElementById('addMemberModal').classList.add('active');
}
function closeAddMemberModal() {
    document.getElementById('addMemberModal').classList.remove('active');
}

// ── Member type toggle ────────────────────────────────────────────
function onMemberTypeChange() {
    const type = document.querySelector('input[name="member_type"]:checked')?.value;
    document.getElementById('studentFlow').style.display = type === 'student' ? 'block' : 'none';
    document.getElementById('teacherFlow').style.display  = type === 'teacher' ? 'block' : 'none';
    document.getElementById('libDatesSection').style.display = type ? 'block' : 'none';
}

// ── Teacher select → hidden input ────────────────────────────────
document.getElementById('teacherSelect').addEventListener('change', function () {
    const container = document.getElementById('libSelectedTeacherContainer');
    container.innerHTML = '';
    if (this.value) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'member_id';
        input.value = this.value;
        container.appendChild(input);
    }
});

// ── Class / Stream / Student state ───────────────────────────────
let libState = { classId: null, className: null, streamId: null, streamName: null };
let libAllStudents = [];

function libSelectClass(card) {
    document.querySelectorAll('#libClassGrid .lib-sel-card').forEach(c => c.classList.remove('selected'));
    card.classList.add('selected');
    libState.classId   = card.dataset.classId;
    libState.className = card.dataset.className;
    document.getElementById('libSelectedClassId').value = libState.classId;
    document.getElementById('libStepStream').style.display   = 'block';
    document.getElementById('libStepStudents').style.display = 'none';
    libLoadStreams(libState.classId);
}

function libResetToClass() {
    libState.streamId = null; libState.streamName = null;
    document.querySelectorAll('#libClassGrid .lib-sel-card').forEach(c => c.classList.remove('selected'));
    document.getElementById('libStepStream').style.display   = 'none';
    document.getElementById('libStepStudents').style.display = 'none';
    document.getElementById('libSelectedStudentsContainer').innerHTML = '';
}

async function libLoadStreams(classId) {
    const grid = document.getElementById('libStreamGrid');
    grid.innerHTML = '<div style="color:var(--text-3);font-size:.8rem;"><i class="fas fa-spinner fa-spin"></i> Loading streams...</div>';
    try {
        const r = await fetch(`{{ route('finance.streams-by-class') }}?class_id=${classId}`);
        const data = await r.json();
        if (!data.length) { grid.innerHTML = '<div style="color:var(--text-3);font-size:.8rem);">No streams found.</div>'; return; }
        grid.innerHTML = data.map(s => `
            <div class="lib-sel-card" data-stream-id="${s.stream_id}" data-stream-name="${s.stream_name || s.stream_id}"
                onclick="libSelectStream(this)">
                <div style="font-size:.85rem;">📚</div>
                <div style="font-size:.72rem;font-weight:700;margin-top:.2rem;">${s.stream_name || s.stream_id}</div>
            </div>`).join('');
    } catch(e) {
        grid.innerHTML = '<div style="color:var(--lib-rose);font-size:.8rem;">Failed to load streams.</div>';
    }
}

function libSelectStream(card) {
    document.querySelectorAll('#libStreamGrid .lib-sel-card').forEach(c => c.classList.remove('selected'));
    card.classList.add('selected');
    libState.streamId   = card.dataset.streamId;
    libState.streamName = card.dataset.streamName;
    document.getElementById('libSelectedStreamId').value = libState.streamId;
    document.getElementById('libStepStudents').style.display = 'block';
    document.getElementById('libStudentSearch').value = '';
    document.getElementById('libSelectedStudentsContainer').innerHTML = '';
    libLoadStudents(libState.classId, libState.streamId);
}

function libResetToStream() {
    libState.streamId = null; libState.streamName = null;
    document.querySelectorAll('#libStreamGrid .lib-sel-card').forEach(c => c.classList.remove('selected'));
    document.getElementById('libStepStudents').style.display = 'none';
    document.getElementById('libSelectedStudentsContainer').innerHTML = '';
}

async function libLoadStudents(classId, streamId) {
    const chips = document.getElementById('libStudentChips');
    chips.innerHTML = '<div style="color:var(--text-3);font-size:.8rem;"><i class="fas fa-spinner fa-spin"></i> Loading students...</div>';
    try {
        const r = await fetch(`{{ route('finance.students-by-stream') }}?class_id=${classId}&stream_id=${streamId}`);
        libAllStudents = await r.json();
        document.getElementById('libStudentCount').textContent = `${libAllStudents.length} students`;
        libRenderChips(libAllStudents);
    } catch(e) {
        chips.innerHTML = '<div style="color:var(--lib-rose);font-size:.8rem;">Failed to load students.</div>';
    }
}

function libRenderChips(students) {
    const chips = document.getElementById('libStudentChips');
    if (!students.length) {
        chips.innerHTML = '<div style="color:var(--text-3);font-size:.8rem;">No students found.</div>';
        return;
    }
    // Preserve currently selected IDs
    const selectedIds = new Set(
        Array.from(document.querySelectorAll('#libSelectedStudentsContainer input'))
             .map(i => i.value)
    );
    chips.innerHTML = students.map(s => `
        <div class="lib-student-chip ${selectedIds.has(String(s.id)) ? 'selected' : ''}"
            data-id="${s.id}" data-name="${s.firstname} ${s.lastname}" data-adm="${s.admission_number ?? 'N/A'}"
            onclick="libToggleStudent(this)">
            <i class="fas fa-user-graduate"></i>
            <div>
                <div class="chip-name">${s.firstname} ${s.lastname}</div>
                <div class="chip-adm">ADM: ${s.admission_number ?? 'N/A'}</div>
            </div>
        </div>`).join('');
    libUpdateSelectedCount();
}

function libToggleStudent(chip) {
    chip.classList.toggle('selected');
    const container = document.getElementById('libSelectedStudentsContainer');
    const id = chip.dataset.id;
    if (chip.classList.contains('selected')) {
        const inp = document.createElement('input');
        inp.type = 'hidden'; inp.name = 'member_ids[]'; inp.value = id;
        inp.setAttribute('data-sid', id);
        container.appendChild(inp);
    } else {
        container.querySelectorAll(`input[data-sid="${id}"]`).forEach(i => i.remove());
    }
    libUpdateSelectedCount();
}

function libSelectAll() {
    const container = document.getElementById('libSelectedStudentsContainer');
    document.querySelectorAll('#libStudentChips .lib-student-chip').forEach(chip => {
        if (!chip.classList.contains('selected')) {
            chip.classList.add('selected');
            const inp = document.createElement('input');
            inp.type = 'hidden'; inp.name = 'member_ids[]'; inp.value = chip.dataset.id;
            inp.setAttribute('data-sid', chip.dataset.id);
            container.appendChild(inp);
        }
    });
    libUpdateSelectedCount();
}

function libUpdateSelectedCount() {
    const total   = document.querySelectorAll('#libStudentChips .lib-student-chip').length;
    const selected = document.querySelectorAll('#libStudentChips .lib-student-chip.selected').length;
    document.getElementById('libStudentCount').textContent =
        selected > 0 ? `${selected} of ${total} selected` : `${total} students`;
}

function libFilterStudents(query) {
    const term = query.toLowerCase();
    const filtered = libAllStudents.filter(s =>
        (s.firstname + ' ' + s.lastname).toLowerCase().includes(term) ||
        (s.admission_number ?? '').toLowerCase().includes(term)
    );
    libRenderChips(filtered);
}

// ── Form submission ───────────────────────────────────────────────
document.getElementById('addMemberForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const type = document.querySelector('input[name="member_type"]:checked')?.value;
    if (!type) { Toast.fire({ icon: 'error', title: 'Please select member type' }); return; }

    if (type === 'student') {
        const selected = document.querySelectorAll('#libSelectedStudentsContainer input[name="member_ids[]"]');
        if (!selected.length) { Toast.fire({ icon: 'error', title: 'Please select at least one student' }); return; }
    } else {
        const teacherId = document.querySelector('#libSelectedTeacherContainer input[name="member_id"]')?.value;
        if (!teacherId) { Toast.fire({ icon: 'error', title: 'Please select a teacher' }); return; }
    }

    const count = type === 'student'
        ? document.querySelectorAll('#libSelectedStudentsContainer input').length
        : 1;

    Swal.fire({
        title: 'Register Member(s)?',
        text: `Register ${count} ${type}(s) as library member(s)?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#2c29ca', cancelButtonColor: '#f43f5e',
        confirmButtonText: 'Yes, register!', cancelButtonText: 'Cancel'
    }).then(result => {
        if (!result.isConfirmed) return;

        Swal.fire({ title: 'Registering...', allowOutsideClick: false, allowEscapeKey: false,
            showConfirmButton: false, didOpen: () => Swal.showLoading() });

        const submitBtn = document.getElementById('submitAddBtn');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Registering...';

        fetch('{{ route("library.members.store") }}', {
            method: 'POST',
            body: new FormData(this),
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                Swal.fire({ icon: 'success', title: 'Registered!', text: data.message || 'Members registered successfully.',
                    showConfirmButton: false, timer: 1500 }).then(() => location.reload());
            } else {
                throw new Error(data.message || 'Failed to register');
            }
        })
        .catch(err => {
            Swal.fire({ icon: 'error', title: 'Oops...', text: err.message });
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-save"></i> Register';
        });
    });
});

// ── Edit / Delete (unchanged) ─────────────────────────────────────
function openEditMember(id, maxBooks, maxDays, status, expiry, suspReason) {
    document.getElementById('editMemberForm').action = `/library/members/${id}`;
    document.getElementById('editMaxBooks').value = maxBooks;
    document.getElementById('editMaxDays').value  = maxDays;
    document.getElementById('editMemberStatus').value = status;
    document.getElementById('editExpiry').value   = expiry;
    document.getElementById('editSuspensionReason').value = suspReason;
    toggleSuspensionReason();
    document.getElementById('editMemberModal').classList.add('active');
}
function closeEditMemberModal() { document.getElementById('editMemberModal').classList.remove('active'); }
function toggleSuspensionReason() {
    document.getElementById('suspensionReasonGroup').style.display =
        document.getElementById('editMemberStatus').value === 'suspended' ? 'block' : 'none';
}

document.getElementById('editMemberForm').addEventListener('submit', function(e) {
    e.preventDefault();
    Swal.fire({ title: 'Update Member?', icon: 'question', showCancelButton: true,
        confirmButtonColor: '#2c29ca', cancelButtonColor: '#f43f5e',
        confirmButtonText: 'Yes, update!', cancelButtonText: 'Cancel'
    }).then(result => {
        if (!result.isConfirmed) return;
        Swal.fire({ title: 'Updating...', allowOutsideClick: false, showConfirmButton: false, didOpen: () => Swal.showLoading() });
        const btn = document.getElementById('submitEditBtn');
        btn.disabled = true;
        fetch(this.action, { method: 'POST', body: new FormData(this), headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({ icon: 'success', title: 'Updated!', showConfirmButton: false, timer: 1500 })
                        .then(() => location.reload());
                } else throw new Error(data.message || 'Failed to update');
            })
            .catch(err => { Swal.fire({ icon: 'error', title: 'Oops...', text: err.message }); btn.disabled = false; });
    });
});

function confirmDelete(memberId) {
    Swal.fire({ title: 'Delete Member?', text: 'This cannot be undone!', icon: 'warning',
        showCancelButton: true, confirmButtonColor: '#f43f5e', cancelButtonColor: '#2c29ca',
        confirmButtonText: 'Yes, delete!', cancelButtonText: 'Cancel'
    }).then(result => {
        if (!result.isConfirmed) return;
        Swal.fire({ title: 'Deleting...', allowOutsideClick: false, showConfirmButton: false, didOpen: () => Swal.showLoading() });
        fetch(`/library/members/${memberId}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                Swal.fire({ icon: 'success', title: 'Deleted!', showConfirmButton: false, timer: 1500 })
                    .then(() => location.reload());
            } else throw new Error(data.message);
        })
        .catch(err => Swal.fire({ icon: 'error', title: 'Oops...', text: err.message }));
    });
}

document.querySelectorAll('.modal-overlay').forEach(m => {
    m.addEventListener('click', function(e) { if (e.target === this) this.classList.remove('active'); });
});
</script>
@endsection