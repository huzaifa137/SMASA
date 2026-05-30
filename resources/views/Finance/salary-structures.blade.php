{{-- resources/views/Finance/salary-structures.blade.php --}}
@extends('layouts-side-bar.master')

@section('css')
    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --fin-green: #059669;
            --fin-green-l: rgba(5, 150, 105, .10);
            --fin-red: #dc2626;
            --fin-red-l: rgba(220, 38, 38, .10);
            --fin-blue: #2f2ccb;
            --fin-blue-l: rgba(47, 44, 203, .10);
            --fin-amber: #d97706;
            --fin-amber-l: rgba(217, 119, 6, .10);
            --fin-purple: #7c3aed;
            --fin-purple-l: rgba(124, 58, 237, .10);
            --fin-teal: #0d9488;
            --fin-teal-l: rgba(13, 148, 136, .10);
            --surface: #ffffff;
            --bg: #f0f4f8;
            --border: #e2e8f0;
            --text-1: #0f172a;
            --text-2: #475569;
            --text-3: #94a3b8;
            --radius: 16px;
            --radius-sm: 12px;
            --shadow: 0 1px 3px rgba(0, 0, 0, .06), 0 4px 16px rgba(0, 0, 0, .05);
            --shadow-lg: 0 8px 32px rgba(0, 0, 0, .10);
        }

        * {
            font-family: 'DM Sans', sans-serif;
            box-sizing: border-box;
        }

        body {
            background: var(--bg);
        }

        /* Hero Section */
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

        /* Cards */
        .fin-card {
            background: var(--surface);
            border-radius: var(--radius);
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
            overflow: hidden;
            margin-bottom: 1.5rem;
        }

        .fin-card-header {
            padding: 1.1rem 1.5rem;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #fafbff;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .fin-card-header h3 {
            margin: 0;
            font-size: .95rem;
            font-weight: 700;
            color: var(--text-1);
            display: flex;
            align-items: center;
            gap: .6rem;
        }

        /* Stat Cards */
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .stat-card {
            background: var(--surface);
            border-radius: var(--radius);
            border: 1px solid var(--border);
            padding: 1.2rem;
            text-align: center;
            transition: all .2s;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }

        .stat-card .value {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--text-1);
            font-family: 'DM Mono', monospace;
        }

        .stat-card .label {
            font-size: .75rem;
            color: var(--text-3);
            margin-top: .3rem;
            font-weight: 500;
        }

        /* Buttons */
        .btn-fin {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            padding: .6rem 1.25rem;
            border-radius: 10px;
            font-size: .875rem;
            font-weight: 600;
            border: none;
            cursor: pointer;
            text-decoration: none;
            transition: all .18s;
        }

        .btn-sm {
            padding: .4rem .85rem;
            font-size: .8rem;
        }

        .btn-primary-fin {
            background: #2f2ccb;
            color: #fff;
        }

        .btn-primary-fin:hover {
            background: #2420a8;
            transform: translateY(-1px);
            box-shadow: 0 4px 14px rgba(47, 44, 203, .35);
        }

        .btn-outline-fin {
            background: transparent;
            border: 1.5px solid var(--border);
            color: var(--text-2);
        }

        .btn-outline-fin:hover {
            border-color: #2f2ccb;
            color: #2f2ccb;
        }

        .btn-success-fin {
            background: var(--fin-green);
            color: #fff;
        }

        .btn-success-fin:hover {
            background: #047857;
        }

        /* Badges */
        .badge-fin {
            display: inline-flex;
            align-items: center;
            gap: .3rem;
            padding: .25rem .7rem;
            border-radius: 20px;
            font-size: .74rem;
            font-weight: 600;
        }

        .badge-green {
            background: var(--fin-green-l);
            color: var(--fin-green);
        }

        .badge-amber {
            background: var(--fin-amber-l);
            color: var(--fin-amber);
        }

        .badge-blue {
            background: rgba(47, 44, 203, .1);
            color: #2f2ccb;
        }

        .badge-purple {
            background: rgba(124, 58, 237, .1);
            color: #7c3aed;
        }

        .badge-teal {
            background: rgba(13, 148, 136, .1);
            color: #0d9488;
        }

        .amount-mono {
            font-family: 'DM Mono', monospace;
            font-weight: 600;
        }

        /* Table */
        .table-wrapper {
            overflow-x: auto;
            margin: 0;
            border-radius: 12px;
        }

        .data-table {
            width: 100%;
            min-width: 900px;
            border-collapse: collapse;
        }

        .data-table th {
            background: #2c29ca;
            padding: .8rem 1rem;
            font-size: .72rem;
            font-weight: 700;
            color: #fff;
            text-transform: uppercase;
            letter-spacing: .05em;
            border-bottom: none;
            text-align: left;
        }

        .data-table th:first-child {
            border-radius: 10px 0 0 0;
        }

        .data-table th:last-child {
            border-radius: 0 10px 0 0;
        }

        .data-table td {
            padding: .9rem 1rem;
            border-bottom: 1px solid #f8fafc;
            font-size: .85rem;
            color: var(--text-1);
            vertical-align: middle;
        }

        .data-table tr:hover td {
            background: #f5f6ff;
        }

        .data-table tr:last-child td {
            border-bottom: none;
        }

        /* Search Bar */
        .search-bar {
            display: flex;
            gap: .75rem;
            margin-bottom: 1rem;
            align-items: center;
        }

        .search-bar input {
            flex: 1;
            padding: .65rem .9rem;
            border-radius: 12px;
            border: 1.5px solid var(--border);
            font-size: .85rem;
            transition: all .15s;
            outline: none;
        }

        .search-bar input:focus {
            border-color: #2f2ccb;
            box-shadow: 0 0 0 3px rgba(47, 44, 203, .1);
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.65);
            z-index: 9999;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: var(--surface);
            border-radius: 24px;
            max-width: 550px;
            width: 90%;
            padding: 0;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
            animation: modalFadeIn 0.25s ease-out;
            max-height: 85vh;
            display: flex;
            flex-direction: column;
        }

        @keyframes modalFadeIn {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--border);
            background: linear-gradient(135deg, #2f2ccb 0%, #2420a8 100%) !important;
            border-radius: 24px 24px 0 0;
        }

        .modal-header h3 {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 700;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .close-modal {
            cursor: pointer;
            font-size: 1.5rem;
            color: rgba(255, 255, 255, 0.7);
            transition: all 0.2s;
            line-height: 1;
        }

        .close-modal:hover {
            color: #fff;
            transform: scale(1.1);
        }

        .modal-body {
            flex: 1;
            overflow-y: auto;
            padding: 1.5rem;
        }

        .modal-actions {
            display: flex;
            gap: .75rem;
            justify-content: flex-end;
            padding: 1rem 1.5rem 1.5rem;
            border-top: 1px solid var(--border);
            background: #fafbff;
            border-radius: 0 0 24px 24px;
        }

        /* Form */
        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-group label {
            display: block;
            margin-bottom: .5rem;
            font-weight: 600;
            color: var(--text-2);
            font-size: .85rem;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: .7rem .9rem;
            border-radius: 12px;
            border: 1.5px solid var(--border);
            font-size: .9rem;
            transition: all .2s;
            background: var(--surface);
            color: var(--text-1);
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #2f2ccb;
            box-shadow: 0 0 0 3px rgba(47, 44, 203, .1);
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: .5rem;
            margin-bottom: .5rem;
        }

        .checkbox-group input {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: #2f2ccb;
        }

        .checkbox-group label {
            margin: 0;
            font-weight: 500;
            cursor: pointer;
        }

        .teacher-info-card {
            background: rgba(47, 44, 203, .08);
            padding: .8rem 1rem;
            border-radius: 12px;
            margin-bottom: 1rem;
            font-size: .85rem;
            display: flex;
            align-items: center;
            gap: .5rem;
            border: 1px solid rgba(47, 44, 203, .15);
            color: #2f2ccb;
        }

        .gross-preview {
            background: #f5f6ff;
            padding: .8rem;
            border-radius: 12px;
            margin: 1rem 0;
            text-align: center;
            border: 1px solid rgba(47, 44, 203, .15);
        }

        /* Responsive */
        @media(max-width:900px) {
            .stat-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media(max-width:768px) {
            .fin-hero {
                padding: 1.5rem;
            }

            .fin-hero h1 {
                font-size: 1.3rem;
            }

            .stat-grid {
                grid-template-columns: 1fr;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .search-bar {
                flex-direction: column;
            }

            .search-bar button {
                width: 100%;
                justify-content: center;
            }

            .data-table {
                min-width: 750px;
            }
        }

        /* Modal Styling */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.65);
            z-index: 9999;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: var(--surface);
            border-radius: 24px;
            max-width: 550px;
            width: 90%;
            padding: 0;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
            animation: modalFadeIn 0.25s ease-out;
            max-height: 85vh;
            display: flex;
            flex-direction: column;
        }

        @keyframes modalFadeIn {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--border);
            background: linear-gradient(135deg, #2f2ccb 0%, #2420a8 100%);
            border-radius: 24px 24px 0 0;
            flex-shrink: 0;
        }

        .modal-header h3 {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 700;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .close-modal {
            cursor: pointer;
            font-size: 1.5rem;
            color: rgba(255, 255, 255, 0.7);
            transition: all 0.2s;
            line-height: 1;
        }

        .close-modal:hover {
            color: #fff;
            transform: scale(1.1);
        }

        .modal-body {
            flex: 1;
            overflow-y: auto;
            padding: 1.5rem;
            background: var(--surface);
        }

        .modal-body::-webkit-scrollbar {
            width: 6px;
        }

        .modal-body::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 10px;
        }

        .modal-body::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        .modal-body::-webkit-scrollbar-thumb:hover {
            background: #2f2ccb;
        }

        .modal-actions {
            flex-shrink: 0;
            display: flex;
            gap: 0.75rem;
            justify-content: flex-end;
            padding: 1rem 1.5rem 1.5rem;
            border-top: 1px solid var(--border);
            background: #fafbff;
            border-radius: 0 0 24px 24px;
        }

        /* Teacher Info Card */
        .teacher-info-card {
            background: rgba(47, 44, 203, 0.08);
            padding: 0.8rem 1rem;
            border-radius: 12px;
            margin-bottom: 1rem;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            border: 1px solid rgba(47, 44, 203, 0.15);
            color: #2f2ccb;
        }

        /* Gross Preview */
        .gross-preview {
            background: #f5f6ff;
            padding: 0.8rem;
            border-radius: 12px;
            margin: 1rem 0;
            text-align: center;
            border: 1px solid rgba(47, 44, 203, 0.15);
        }
    </style>
@endsection

@section('page-header')
    <div class="fin-hero mt-5">
        <div style="position:relative;z-index:1;">
            <div class="hero-badge"><i class="fas fa-coins"></i> HR & Payroll — Salary Structures</div>
            <h1>Teacher Salary Structures</h1>
            <p>Define and manage teacher salary components, allowances, and tax settings</p>
        </div>
    </div>
@endsection

@section('content')

    @php
        $totalTeachers = $teachers->count();
        $withStructures = $teachers->filter(function ($t) {
            return $t->salaryStructure;
        })->count();
        $totalMonthlyGross = $teachers->sum(function ($t) {
            return $t->salaryStructure ? $t->salaryStructure->grossPay() : 0;
        });
    @endphp

    {{-- Stats Summary --}}
    <div class="stat-grid">
        <div class="stat-card">
            <div class="value">{{ $totalTeachers }}</div>
            <div class="label">Total Teachers</div>
        </div>
        <div class="stat-card">
            <div class="value">{{ $withStructures }}</div>
            <div class="label">With Salary Structure</div>
        </div>
        <div class="stat-card">
            <div class="value">{{ $totalTeachers - $withStructures }}</div>
            <div class="label">Missing Structure</div>
        </div>
        <div class="stat-card">
            <div class="value">UGX {{ number_format($totalMonthlyGross, 0) }}</div>
            <div class="label">Monthly Gross Payroll</div>
        </div>
    </div>

    {{-- Search Bar --}}
    <div class="search-bar">
        <input type="text" id="searchInput" placeholder="Search teacher by name or employee number..."
            onkeyup="filterTable()">
        <button class="btn-fin btn-outline-fin" onclick="clearSearch()">
            <i class="fas fa-times"></i> Clear
        </button>
    </div>

    {{-- Teachers Table --}}
    <div class="fin-card">
        <div class="fin-card-header">
            <h3><i class="fas fa-chalkboard-user"></i> Teacher Salary Structures</h3>
            <span style="font-size:.75rem;color:var(--text-3);">{{ $teachers->count() }} teachers</span>
        </div>
        <div class="table-wrapper">
            <table class="data-table" id="teacherTable">
                <thead>
                    <tr>
                        <th>Teacher Name</th>
                        <th>Employee #</th>
                        <th>Basic Salary</th>
                        <th>Housing Allow.</th>
                        <th>Transport Allow.</th>
                        <th>Other Allow.</th>
                        <th>Gross Pay</th>
                        <th>Tax Settings</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($teachers as $teacher)
                        @php
                            $structure = $teacher->salaryStructure;
                            $hasStructure = $structure !== null;
                            $gross = $hasStructure ? $structure->grossPay() : 0;
                        @endphp
                        <tr class="teacher-row" data-name="{{ strtolower($teacher->firstname . ' ' . $teacher->lastname) }}"
                            data-number="{{ $teacher->employee_number ?? '' }}">
                            <td>
                                <div style="font-weight:600;">{{ $teacher->firstname }} {{ $teacher->lastname }}</div>
                                <div style="font-size:.7rem;color:var(--text-3);">{{ $teacher->email ?? '—' }}</div>
                            </td>
                            <td>{{ $teacher->employee_number ?? '—' }}</td>
                            <td>
                                @if($hasStructure)
                                    <span class="amount-mono">UGX {{ number_format($structure->basic_salary, 0) }}</span>
                                @else
                                    <span class="badge-fin badge-amber">Not set</span>
                                @endif
                            </td>
                            <td class="amount-mono">@if($hasStructure) UGX {{ number_format($structure->housing_allowance, 0) }}
                            @else — @endif</td>
                            <td class="amount-mono">@if($hasStructure) UGX
                            {{ number_format($structure->transport_allowance, 0) }} @else — @endif
                            </td>
                            <td class="amount-mono">@if($hasStructure) UGX {{ number_format($structure->other_allowances, 0) }}
                            @else — @endif</td>
                            <td class="amount-mono" style="font-weight:700;color:#2f2ccb;">@if($hasStructure) UGX
                            {{ number_format($gross, 0) }} @else — @endif
                            </td>
                            <td>
                                @if($hasStructure)
                                    <div style="display:flex;gap:.3rem;flex-wrap:wrap;">
                                        @if($structure->apply_paye)<span class="badge-fin badge-teal">PAYE</span>@endif
                                        @if($structure->apply_nssf)<span class="badge-fin badge-green">NSSF</span>@endif
                                    </div>
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                <button class="btn-fin btn-sm btn-primary-fin"
                                    onclick="openEditModal({{ $teacher->id }}, {{ json_encode($structure ? $structure->toArray() : null) }})">
                                    <i class="fas fa-{{ $hasStructure ? 'edit' : 'plus' }}"></i>
                                    {{ $hasStructure ? 'Edit' : 'Set' }}
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Salary Structure Modal --}}
    <div id="salaryModal" class="modal">
        <div class="modal-content" style="max-width: 550px; max-height: 85vh; display: flex; flex-direction: column;">
            <div class="modal-header">
                <h3 id="modalTitle"><i class="fas fa-money-bill-wave"></i> Set Salary Structure</h3>
                <span class="close-modal" onclick="closeModal()">&times;</span>
            </div>
            <form method="POST" action="{{ route('finance.salary-structures.store') }}" id="salaryForm"
                style="display: flex; flex-direction: column; flex: 1; overflow: hidden;">
                @csrf
                <input type="hidden" name="teacher_id" id="teacher_id">

                <div class="modal-body" style="flex: 1; overflow-y: auto; padding: 1.5rem;">
                    <div class="teacher-info-card" id="teacherInfo">
                        <i class="fas fa-chalkboard-user"></i>
                        <span>Loading teacher information...</span>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Basic Salary (UGX) <span style="color:var(--fin-red);">*</span></label>
                            <input type="text" name="basic_salary" id="basic_salary" required placeholder="e.g., 500,000">
                        </div>
                        <div class="form-group">
                            <label>Housing Allowance (UGX)</label>
                            <input type="text" name="housing_allowance" id="housing_allowance" placeholder="e.g., 200,000">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Transport Allowance (UGX)</label>
                            <input type="text" name="transport_allowance" id="transport_allowance"
                                placeholder="e.g., 100,000">
                        </div>
                        <div class="form-group">
                            <label>Other Allowances (UGX)</label>
                            <input type="text" name="other_allowances" id="other_allowances" placeholder="e.g., 50,000">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Notes (Optional)</label>
                        <textarea name="notes" id="notes" rows="2"
                            placeholder="Additional notes about this salary structure..."></textarea>
                    </div>

                    <div
                        style="background:rgba(47,44,203,.05);padding:.8rem;border-radius:12px;margin:1rem 0;border:1px solid rgba(47,44,203,.15);">
                        <div style="font-weight:600;margin-bottom:.5rem;color:#2f2ccb;">Tax & Deduction Settings</div>
                        <div class="checkbox-group">
                            <input type="checkbox" name="apply_paye" id="apply_paye" value="1" checked>
                            <label for="apply_paye">Apply PAYE (Pay As You Earn) Tax</label>
                        </div>
                        <div class="checkbox-group">
                            <input type="checkbox" name="apply_nssf" id="apply_nssf" value="1" checked>
                            <label for="apply_nssf">Apply NSSF (5% employee contribution)</label>
                        </div>
                        <div style="font-size:.7rem;color:var(--text-3);margin-top:.5rem;">
                            <i class="fas fa-info-circle"></i> PAYE is calculated based on Ugandan tax bands (0% up to
                            235,000/month, 10% up to 335,000, etc.)
                        </div>
                    </div>

                    <div class="gross-preview" id="grossPreview">
                        <span style="font-size:.7rem;color:var(--text-3);">Estimated Gross Monthly Pay</span>
                        <div style="font-size:1.2rem;font-weight:800;color:#2f2ccb;" id="grossPreviewAmount">UGX 0</div>
                    </div>
                </div>

                <div class="modal-actions" style="flex-shrink: 0;">
                    <button type="button" class="btn-fin btn-outline-fin" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="btn-fin btn-primary-fin">
                        <i class="fas fa-save"></i> Save Salary Structure
                    </button>
                </div>
            </form>
        </div>
    </div>
    </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const modal = document.getElementById('salaryModal');
        let currentTeacherId = null;

        // Format number with commas
        function formatNumberWithCommas(value) {
            // If value is null, undefined, or empty string
            if (!value && value !== 0) return '';
            // Convert to string and remove any existing commas
            let numbers = value.toString().replace(/,/g, '');
            // If not a valid number
            if (numbers === '' || isNaN(numbers)) return '';
            // If zero
            if (parseInt(numbers, 10) === 0) return '0';
            // Format with commas
            return parseInt(numbers, 10).toLocaleString('en-US');
        }

        function parseFormattedNumber(value) {
            return value.toString().replace(/,/g, '');
        }

        function openEditModal(teacherId, existingData) {
            currentTeacherId = teacherId;

            // Fetch teacher details
            fetch(`/finance/teacher/${teacherId}/details`)
                .then(response => response.json())
                .then(teacher => {
                    document.getElementById('teacherInfo').innerHTML = `
                    <i class="fas fa-chalkboard-user"></i>
                    <strong>${teacher.firstname} ${teacher.lastname}</strong>
                    ${teacher.employee_number ? `<span style="color:var(--text-3);"> (${teacher.employee_number})</span>` : ''}
                `;
                    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-money-bill-wave"></i> Salary Structure: ' + teacher.firstname;
                });

            document.getElementById('teacher_id').value = teacherId;

            // Format amount inputs
            const basicInput = document.getElementById('basic_salary');
            const housingInput = document.getElementById('housing_allowance');
            const transportInput = document.getElementById('transport_allowance');
            const otherInput = document.getElementById('other_allowances');

            if (existingData && existingData !== 'null') {
                // Edit mode - populate form
                // Directly use the numeric values from existingData without re-formatting already formatted numbers
                const basicSalary = existingData.basic_salary || 0;
                const housingAllowance = existingData.housing_allowance || 0;
                const transportAllowance = existingData.transport_allowance || 0;
                const otherAllowances = existingData.other_allowances || 0;

                // Store raw numeric values
                basicInput.setAttribute('data-raw', basicSalary);
                housingInput.setAttribute('data-raw', housingAllowance);
                transportInput.setAttribute('data-raw', transportAllowance);
                otherInput.setAttribute('data-raw', otherAllowances);

                // Display formatted values
                basicInput.value = formatNumberWithCommas(basicSalary);
                housingInput.value = formatNumberWithCommas(housingAllowance);
                transportInput.value = formatNumberWithCommas(transportAllowance);
                otherInput.value = formatNumberWithCommas(otherAllowances);

                document.getElementById('notes').value = existingData.notes || '';
                document.getElementById('apply_paye').checked = existingData.apply_paye == 1;
                document.getElementById('apply_nssf').checked = existingData.apply_nssf == 1;
            } else {
                // New mode - reset form
                basicInput.value = '';
                housingInput.value = '';
                transportInput.value = '';
                otherInput.value = '';
                basicInput.setAttribute('data-raw', '');
                housingInput.setAttribute('data-raw', '');
                transportInput.setAttribute('data-raw', '');
                otherInput.setAttribute('data-raw', '');
                document.getElementById('notes').value = '';
                document.getElementById('apply_paye').checked = true;
                document.getElementById('apply_nssf').checked = true;
            }

            updateGrossPreview();
            modal.style.display = 'flex';
        }

        function closeModal() {
            modal.style.display = 'none';
        }

        function updateGrossPreview() {
            const basic = parseFloat(parseFormattedNumber(document.getElementById('basic_salary').value)) || 0;
            const housing = parseFloat(parseFormattedNumber(document.getElementById('housing_allowance').value)) || 0;
            const transport = parseFloat(parseFormattedNumber(document.getElementById('transport_allowance').value)) || 0;
            const other = parseFloat(parseFormattedNumber(document.getElementById('other_allowances').value)) || 0;
            const gross = basic + housing + transport + other;
            document.getElementById('grossPreviewAmount').innerHTML = `UGX ${gross.toLocaleString()}`;
        }

        // Add formatting to all amount inputs
        const amountInputs = ['basic_salary', 'housing_allowance', 'transport_allowance', 'other_allowances'];
        amountInputs.forEach(id => {
            const input = document.getElementById(id);
            if (input) {
                input.addEventListener('input', function (e) {
                    const rawValue = this.value;
                    const numericValue = parseFormattedNumber(rawValue);
                    this.setAttribute('data-raw', numericValue);
                    if (numericValue !== '') {
                        this.value = formatNumberWithCommas(numericValue);
                    } else {
                        this.value = '';
                    }
                    updateGrossPreview();
                });
            }
        });

        // Filter table function
        function filterTable() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const rows = document.querySelectorAll('.teacher-row');

            rows.forEach(row => {
                const name = row.dataset.name || '';
                const number = row.dataset.number || '';
                if (name.includes(searchTerm) || number.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        function clearSearch() {
            document.getElementById('searchInput').value = '';
            filterTable();
        }

        // Close modal on outside click
        window.onclick = function (event) {
            if (event.target == modal) {
                closeModal();
            }
        }

        // Form submit with SweetAlert
        document.getElementById('salaryForm').addEventListener('submit', function (e) {
            e.preventDefault();

            // Convert formatted amounts back to raw numbers
            const basicInput = document.getElementById('basic_salary');
            const housingInput = document.getElementById('housing_allowance');
            const transportInput = document.getElementById('transport_allowance');
            const otherInput = document.getElementById('other_allowances');

            const basicSalary = parseFloat(parseFormattedNumber(basicInput.value)) || 0;

            if (basicSalary <= 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Amount',
                    text: 'Please enter a valid basic salary amount greater than 0.',
                    confirmButtonColor: '#2f2ccb'
                });
                return;
            }

            // Set raw values for submission
            basicInput.value = basicSalary;
            housingInput.value = parseFloat(parseFormattedNumber(housingInput.value)) || 0;
            transportInput.value = parseFloat(parseFormattedNumber(transportInput.value)) || 0;
            otherInput.value = parseFloat(parseFormattedNumber(otherInput.value)) || 0;

            Swal.fire({
                title: 'Save Salary Structure?',
                text: 'Are you sure you want to save this salary structure?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#2f2ccb',
                cancelButtonColor: '#94a3b8',
                confirmButtonText: 'Yes, save!',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Processing...',
                        text: 'Saving salary structure...',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                            document.getElementById('salaryForm').submit();
                        }
                    });
                } else {
                    // Restore formatted values
                    basicInput.value = formatNumberWithCommas(basicSalary);
                    housingInput.value = formatNumberWithCommas(housingInput.value);
                    transportInput.value = formatNumberWithCommas(transportInput.value);
                    otherInput.value = formatNumberWithCommas(otherInput.value);
                }
            });
        });

        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: '{{ session('success') }}',
                confirmButtonColor: '#2f2ccb',
                timer: 3000,
                timerProgressBar: true
            });
        @endif

        @if(session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: '{{ session('error') }}',
                confirmButtonColor: '#2f2ccb'
            });
        @endif
    </script>
@endsection