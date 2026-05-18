<?php use App\Http\Controllers\Helper; ?>
@extends('layouts-side-bar.master')

@section('css')
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --brand: #5351e4;
            --brand-light: #2C29CA;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --info: #3b82f6;
            --purple: #8b5cf6;
            --text-primary: #1e293b;
            --text-secondary: #475569;
            --text-muted: #94a3b8;
            --border: #e2e8f0;
            --surface: #ffffff;
        }

        .glass-header {
            background: linear-gradient(135deg, #5351e4 0%, #2C29CA 100%);
            border-radius: 28px;
            padding: 2rem 2.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 20px 40px -12px rgba(83, 81, 228, 0.35);
            position: relative;
            overflow: hidden;
        }

        .glass-header::before {
            content: '';
            position: absolute;
            top: -40%;
            right: -10%;
            width: 280px;
            height: 280px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            border-radius: 50%;
        }

        .btn-glass {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(4px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
            border-radius: 12px;
            padding: 0.5rem 1.2rem;
            font-weight: 600;
            font-size: 0.85rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            transition: all 0.2s;
        }

        .btn-glass:hover {
            background: rgba(255, 255, 255, 0.25);
            color: white;
        }

        .main-card {
            background: white;
            border-radius: 24px;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.07);
            border: 1px solid rgba(83, 81, 228, 0.08);
            overflow: hidden;
        }

        .card-toolbar {
            padding: 1.5rem 2rem;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .btn-primary-sm {
            background: var(--brand);
            color: white;
            border: none;
            border-radius: 12px;
            padding: 0.6rem 1.4rem;
            font-weight: 700;
            font-size: 0.875rem;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            transition: all 0.2s;
        }

        .btn-primary-sm:hover {
            background: var(--brand-light);
            transform: translateY(-1px);
        }

        /* Period row table */
        .period-table {
            width: 100%;
            border-collapse: collapse;
        }

        .period-table th {
            padding: 0.9rem 1.5rem;
            text-align: left;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--text-muted);
            background: #f8fafc;
            border-bottom: 1px solid var(--border);
        }

        .period-table td {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.875rem;
            color: var(--text-primary);
            vertical-align: middle;
        }

        .period-table tr:last-child td {
            border-bottom: none;
        }

        .period-table tr:hover td {
            background: #fafbff;
        }

        .type-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.25rem 0.75rem;
            border-radius: 99px;
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .type-lesson {
            background: rgba(83, 81, 228, 0.1);
            color: #5351e4;
        }

        .type-break {
            background: rgba(16, 185, 129, 0.1);
            color: #059669;
        }

        .type-lunch {
            background: rgba(245, 158, 11, 0.1);
            color: #d97706;
        }

        .type-assembly {
            background: rgba(59, 130, 246, 0.1);
            color: #2563eb;
        }

        .type-other {
            background: rgba(100, 116, 139, 0.1);
            color: #64748b;
        }

        .time-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            background: #f1f5f9;
            border-radius: 8px;
            padding: 0.3rem 0.7rem;
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--text-secondary);
        }

        .duration-pill {
            font-size: 0.75rem;
            color: var(--text-muted);
            background: #f8fafc;
            border-radius: 6px;
            padding: 0.2rem 0.5rem;
        }

        .action-btns {
            display: flex;
            gap: 0.5rem;
        }

        .btn-icon {
            width: 34px;
            height: 34px;
            border-radius: 10px;
            border: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 0.8rem;
            transition: all 0.2s;
        }

        .btn-edit-icon {
            background: rgba(83, 81, 228, 0.1);
            color: #5351e4;
        }

        .btn-edit-icon:hover {
            background: #5351e4;
            color: white;
        }

        .btn-del-icon {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
        }

        .btn-del-icon:hover {
            background: #ef4444;
            color: white;
        }

        .sort-handle {
            cursor: grab;
            color: var(--text-muted);
        }

        .toggle-active {
            cursor: pointer;
        }

        .badge-active {
            background: rgba(16, 185, 129, 0.1);
            color: #059669;
            font-weight: 700;
            font-size: 0.72rem;
            padding: 0.2rem 0.6rem;
            border-radius: 99px;
        }

        .badge-inactive {
            background: rgba(100, 116, 139, 0.1);
            color: #64748b;
            font-weight: 700;
            font-size: 0.72rem;
            padding: 0.2rem 0.6rem;
            border-radius: 99px;
        }

        /* Modal */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 1000;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
            align-items: center;
            justify-content: center;
        }

        .modal-overlay.open {
            display: flex;
        }

        .modal-box {
            background: white;
            border-radius: 24px;
            width: min(520px, 95vw);
            padding: 2rem;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.2);
            animation: slideUp 0.3s ease;
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

        .modal-title {
            font-size: 1.15rem;
            font-weight: 800;
            color: var(--text-primary);
            margin-bottom: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.2rem;
        }

        .form-label {
            font-size: 0.8rem;
            font-weight: 700;
            color: var(--text-secondary);
            display: block;
            margin-bottom: 0.4rem;
        }

        .form-control-modern {
            width: 100%;
            padding: 0.65rem 1rem;
            border: 1.5px solid var(--border);
            border-radius: 12px;
            font-size: 0.875rem;
            color: var(--text-primary);
            background: #f8fafc;
            transition: all 0.2s;
            box-sizing: border-box;
        }

        .form-control-modern:focus {
            outline: none;
            border-color: var(--brand);
            background: white;
            box-shadow: 0 0 0 3px rgba(83, 81, 228, 0.12);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .modal-footer {
            display: flex;
            gap: 0.75rem;
            justify-content: flex-end;
            margin-top: 1.5rem;
        }

        .btn-cancel {
            background: #f1f5f9;
            color: var(--text-secondary);
            border: none;
            border-radius: 12px;
            padding: 0.65rem 1.4rem;
            font-weight: 700;
            cursor: pointer;
        }

        .btn-save {
            background: var(--brand);
            color: white;
            border: none;
            border-radius: 12px;
            padding: 0.65rem 1.6rem;
            font-weight: 700;
            cursor: pointer;
        }

        .btn-save:hover {
            background: var(--brand-light);
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: var(--text-muted);
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        .empty-state h5 {
            font-weight: 700;
            color: var(--text-secondary);
        }

        .toast-notif {
            position: fixed;
            bottom: 1.5rem;
            right: 1.5rem;
            z-index: 9999;
            background: #1e293b;
            color: white;
            padding: 0.85rem 1.5rem;
            border-radius: 14px;
            font-size: 0.875rem;
            font-weight: 600;
            transform: translateY(80px);
            opacity: 0;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
        }

        .toast-notif.show {
            transform: translateY(0);
            opacity: 1;
        }

        .toast-notif.success {
            background: #059669;
        }

        .toast-notif.error {
            background: #dc2626;
        }
    </style>
@endsection

@section('page-header')
<div class="glass-header mt-5">
    <div class="row align-items-center" style="position:relative;z-index:1;">
        <div class="col-lg-8">
            <h1 style="font-size: 2rem; font-weight: 800; color: white; margin-bottom: 0.5rem;">
                <i class="fas fa-clock me-3"></i> Period Management
            </h1>
            <p style="font-size: 0.95rem; color: rgba(255,255,255,0.85); margin-bottom: 0;">
                Define your school's daily periods, breaks, and assembly slots
            </p>
        </div>
<div class="col-lg-4 text-lg-end mt-3 mt-lg-0">

    <div style="display: flex; justify-content: flex-end; align-items: center; gap: 0.75rem; flex-wrap: wrap;">

        <button class="btn-glass" onclick="openModal()"
                style="
                    background: rgba(255,255,255,0.2);
                    border: 1px solid rgba(255,255,255,0.3);
                    color: white;
                    border-radius: 8px;
                    padding: 0.6rem 1.5rem;
                    font-size: 1rem;
                    font-weight: 600;
                    cursor: pointer;
                    display: inline-flex;
                    align-items: center;
                    gap: 0.5rem;
                    transition: all 0.3s ease;
                    white-space: nowrap;
                ">
            <i class="fas fa-plus"></i>
            Add Period
        </button>

        <a href="{{ route('timetable.dashboard') }}" class="btn"
           style="
                background: rgba(255,255,255,0.2);
                border: 1px solid rgba(255,255,255,0.3);
                color: white;
                border-radius: 8px;
                padding: 0.6rem 1.5rem;
                font-size: 1rem;
                font-weight: 600;
                text-decoration: none;
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                transition: all 0.3s ease;
                white-space: nowrap;
           ">
            <i class="fas fa-arrow-left"></i>
            Back to Timetable
        </a>

    </div>

</div>
    </div>
</div>
@endsection

@section('content')
    <div class="container-fluid px-0">
        <div class="main-card">
            <div class="card-toolbar">
                <div>
                    <h6 style="margin:0;font-weight:700;color:var(--text-primary);">
                        All Periods <span
                            style="color:var(--text-muted);font-weight:500;font-size:0.85rem;">({{ $periods->count() }}
                            total)</span>
                    </h6>
                    <p style="margin:0;font-size:0.8rem;color:var(--text-muted);">Drag rows to reorder. Changes are saved
                        per action.</p>
                </div>
                <button class="btn-primary-sm" onclick="openModal()">
                    <i class="fas fa-plus"></i> Add New Period
                </button>
            </div>

            @if($periods->isEmpty())
                <div class="empty-state">
                    <i class="fas fa-clock"></i>
                    <h5>No Periods Yet</h5>
                    <p>Add your school's daily periods to start building timetables.</p>
                    <button class="btn-primary-sm" onclick="openModal()"><i class="fas fa-plus"></i> Add First Period</button>
                </div>
            @else
                <div style="overflow-x:auto;">
                    <table class="period-table" id="periodTable">
                        <thead>
                            <tr>
                                <th style="width:40px;"></th>
                                <th>Period Name</th>
                                <th>Type</th>
                                <th>Start Time</th>
                                <th>End Time</th>
                                <th>Duration</th>
                                <th>Order</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="periodBody">
                            @foreach($periods as $period)
                                <tr data-id="{{ $period->id }}" data-sort="{{ $period->sort_order }}">
                                    <td><i class="fas fa-grip-vertical sort-handle text-muted"></i></td>
                                    <td>
                                        <strong>{{ $period->name }}</strong>
                                    </td>
                                    <td>
                                        @php
                                            $typeMap = ['lesson' => 'type-lesson', 'break' => 'type-break', 'lunch' => 'type-lunch', 'assembly' => 'type-assembly', 'other' => 'type-other'];
                                            $typeIcon = ['lesson' => 'fa-book', 'break' => 'fa-coffee', 'lunch' => 'fa-utensils', 'assembly' => 'fa-users', 'other' => 'fa-circle'];
                                        @endphp
                                        <span class="type-badge {{ $typeMap[$period->type] ?? 'type-other' }}">
                                            <i class="fas {{ $typeIcon[$period->type] ?? 'fa-circle' }}"></i>
                                            {{ ucfirst($period->type) }}
                                        </span>
                                    </td>
                                    <td><span class="time-pill"><i class="far fa-clock"></i>
                                            {{ \Carbon\Carbon::parse($period->start_time)->format('H:i') }}</span></td>
                                    <td><span class="time-pill"><i class="far fa-clock"></i>
                                            {{ \Carbon\Carbon::parse($period->end_time)->format('H:i') }}</span></td>
                                    <td>
                                        @php
                                            $dur = \Carbon\Carbon::parse($period->start_time)->diffInMinutes(\Carbon\Carbon::parse($period->end_time));
                                        @endphp
                                        <span class="duration-pill">{{ $dur }} min</span>
                                    </td>
                                    <td><span style="font-weight:700;color:var(--brand);">{{ $period->sort_order }}</span></td>
                                    <td>
                                        @if($period->is_active)
                                            <span class="badge-active"><i class="fas fa-circle" style="font-size:0.5rem;"></i>
                                                Active</span>
                                        @else
                                            <span class="badge-inactive"><i class="fas fa-circle" style="font-size:0.5rem;"></i>
                                                Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="action-btns">
                                            <button class="btn-icon btn-edit-icon"
                                                onclick="editPeriod({{ $period->id }}, '{{ addslashes($period->name) }}', '{{ $period->type }}', '{{ $period->start_time }}', '{{ $period->end_time }}', {{ $period->sort_order }}, {{ $period->is_active ? 1 : 0 }})">
                                                <i class="fas fa-pen"></i>
                                            </button>
                                            <button class="btn-icon btn-del-icon"
                                                onclick="deletePeriod({{ $period->id }}, '{{ addslashes($period->name) }}')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <!-- Legend -->
        <div style="margin-top:1.5rem;display:flex;gap:0.75rem;flex-wrap:wrap;">
            <span class="type-badge type-lesson"><i class="fas fa-book"></i> Lesson</span>
            <span class="type-badge type-break"><i class="fas fa-coffee"></i> Break</span>
            <span class="type-badge type-lunch"><i class="fas fa-utensils"></i> Lunch</span>
            <span class="type-badge type-assembly"><i class="fas fa-users"></i> Assembly</span>
            <span class="type-badge type-other"><i class="fas fa-circle"></i> Other</span>
        </div>
    </div>

    <!-- Add/Edit Modal -->
    <div class="modal-overlay" id="periodModal">
        <div class="modal-box">
            <h5 class="modal-title" id="modalTitle"><i class="fas fa-clock me-2" style="color:var(--brand);"></i> Add Period
            </h5>
            <input type="hidden" id="editingId" value="">

            <div class="form-group">
                <label class="form-label">Period Name *</label>
                <input type="text" id="fName" class="form-control-modern"
                    placeholder="e.g. Period 1, Morning Break, Lunch Break">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Type *</label>
                    <select id="fType" class="form-control-modern">
                        <option value="lesson">Lesson</option>
                        <option value="break">Break</option>
                        <option value="lunch">Lunch</option>
                        <option value="assembly">Assembly</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Sort Order *</label>
                    <input type="number" id="fSort" class="form-control-modern" min="0" value="{{ $periods->count() + 1 }}">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Start Time *</label>
                    <input type="time" id="fStart" class="form-control-modern">
                </div>
                <div class="form-group">
                    <label class="form-label">End Time *</label>
                    <input type="time" id="fEnd" class="form-control-modern">
                </div>
            </div>
            <div class="form-group" id="activeGroup" style="display:none;">
                <label class="form-label">Status</label>
                <select id="fActive" class="form-control-modern">
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
            </div>

            <div class="modal-footer">
                <button class="btn-cancel" onclick="closeModal()">Cancel</button>
                <button class="btn-save" id="saveBtn" onclick="savePeriod()">
                    <i class="fas fa-save"></i> Save Period
                </button>
            </div>
        </div>
    </div>
    </div>
    </div>
    </div>

    <div class="toast-notif" id="toast"></div>

@endsection

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        const csrfToken = '{{ csrf_token() }}';
        const storeUrl = '{{ route('timetable.periods.store') }}';
        const updateUrl = (id) => `/timetable/periods/${id}`;
        const deleteUrl = (id) => `/timetable/periods/${id}`;

        function openModal() {
            document.getElementById('editingId').value = '';
            document.getElementById('modalTitle').innerHTML = '<i class="fas fa-clock me-2" style="color:var(--brand);"></i> Add New Period';
            document.getElementById('fName').value = '';
            document.getElementById('fType').value = 'lesson';
            document.getElementById('fStart').value = '';
            document.getElementById('fEnd').value = '';
            document.getElementById('fSort').value = '{{ $periods->count() + 1 }}';
            document.getElementById('activeGroup').style.display = 'none';
            document.getElementById('periodModal').classList.add('open');
        }

        function editPeriod(id, name, type, start, end, sort, isActive) {
            document.getElementById('editingId').value = id;
            document.getElementById('modalTitle').innerHTML = '<i class="fas fa-pen me-2" style="color:var(--brand);"></i> Edit Period';
            document.getElementById('fName').value = name;
            document.getElementById('fType').value = type;
            document.getElementById('fStart').value = start.substring(0, 5);
            document.getElementById('fEnd').value = end.substring(0, 5);
            document.getElementById('fSort').value = sort;
            document.getElementById('fActive').value = isActive;
            document.getElementById('activeGroup').style.display = 'block';
            document.getElementById('periodModal').classList.add('open');
        }

        function closeModal() {
            document.getElementById('periodModal').classList.remove('open');
        }

        async function savePeriod() {
            const id = document.getElementById('editingId').value;
            const name = document.getElementById('fName').value.trim();
            const type = document.getElementById('fType').value;
            const start = document.getElementById('fStart').value;
            const end = document.getElementById('fEnd').value;
            const sort = document.getElementById('fSort').value;
            const isActive = document.getElementById('fActive').value;

            if (!name || !start || !end) { showToast('Please fill all required fields.', 'error'); return; }
            if (start >= end) { showToast('End time must be after start time.', 'error'); return; }

            const btn = document.getElementById('saveBtn');
            btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';

            const isEdit = !!id;
            const url = isEdit ? updateUrl(id) : storeUrl;
            const method = isEdit ? 'PUT' : 'POST';

            try {
                const res = await fetch(url, {
                    method, headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({ name, type, start_time: start, end_time: end, sort_order: parseInt(sort), is_active: isEdit ? parseInt(isActive) : 1 })
                });
                const data = await res.json();
                if (data.success) {
                    showToast(data.message, 'success');
                    closeModal();
                    setTimeout(() => location.reload(), 700);
                } else {
                    showToast(data.message || 'Save failed.', 'error');
                }
            } catch (e) { showToast('Connection error.', 'error'); }
            btn.disabled = false; btn.innerHTML = '<i class="fas fa-save"></i> Save Period';
        }

async function deletePeriod(id, name) {

    const result = await Swal.fire({
        title: 'Delete Period?',
        text: `Delete "${name}"?\nThis cannot be undone.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280'
    });

    if (!result.isConfirmed) return;

    try {
        const res = await fetch(deleteUrl(id), {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json'
            }
        });

        const data = await res.json();

        if (data.success) {
            await Swal.fire({
                icon: 'success',
                title: 'Deleted!',
                text: 'Period deleted successfully.',
                timer: 1200,
                showConfirmButton: false
            });

            setTimeout(() => location.reload(), 600);
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Failed',
                text: data.message || 'Cannot delete.'
            });
        }

    } catch (e) {
        Swal.fire({
            icon: 'error',
            title: 'Connection Error',
            text: 'Please try again.'
        });
    }
}

        function showToast(msg, type = 'success') {
            const t = document.getElementById('toast');
            t.textContent = msg; t.className = `toast-notif show ${type}`;
            clearTimeout(window._toastT);
            window._toastT = setTimeout(() => t.classList.remove('show'), 3000);
        }

        // Close modal on overlay click
        document.getElementById('periodModal').addEventListener('click', function (e) {
            if (e.target === this) closeModal();
        });

        // ESC to close
        document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });
    </script>
