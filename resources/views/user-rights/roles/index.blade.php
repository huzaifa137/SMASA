{{-- resources/views/user-rights/roles/index.blade.php --}}
<?php use App\Helpers\PermissionHelper; ?>
@extends('layouts-side-bar.master')

@section('css')
<style>
:root { --urp-primary:#4f46e5; --urp-dark:#1e1b4b; --urp-accent:#7c3aed; --radius:14px; }
.urp-hero {
    background: linear-gradient(135deg, var(--urp-dark) 0%, #312e81 60%, var(--urp-accent) 100%);
    border-radius: var(--radius); padding: 2rem 2rem 2.8rem;
    margin-bottom: -1.5rem; position: relative; overflow: hidden; color: #fff;
}
.urp-hero h2 { font-size:1.7rem; font-weight:700; margin-bottom:.25rem; }

.role-row {
    background: #fff; border-radius: 12px; border: 1px solid #e5e7eb;
    padding: 1rem 1.3rem; display:flex; align-items:center; gap:1rem;
    transition: border-color .2s, box-shadow .2s;
    animation: fadeIn .3s ease backwards;
}
.role-row:hover { border-color: var(--urp-primary); box-shadow:0 4px 16px rgba(79,70,229,.1); }
.role-avatar {
    width:44px; height:44px; border-radius:50%;
    background: linear-gradient(135deg,var(--urp-primary),var(--urp-accent));
    display:flex; align-items:center; justify-content:center;
    color:#fff; font-size:1.1rem; font-weight:700; flex-shrink:0;
}
.role-name { font-size:1rem; font-weight:600; color:#1e1b4b; }
.role-desc { font-size:.78rem; color:#9ca3af; }
.staff-badge {
    background: #eef2ff; color: var(--urp-primary);
    border-radius:20px; padding:.2rem .65rem; font-size:.75rem; font-weight:600;
}
@keyframes fadeIn {
    from { opacity:0; transform:translateY(8px); }
    to   { opacity:1; transform:translateY(0); }
}
.empty-state { text-align:center; padding:3rem; color:#9ca3af; }
.empty-state i { font-size:3rem; margin-bottom:1rem; display:block; opacity:.35; }

.role-action-buttons > * {
    margin-right: 10px;
}

.role-action-buttons > *:last-child {
    margin-right: 0;
}

/* Manage Roles - Stack layout on mobile */

/* Hero section */
.urp-hero {
    padding: 2rem 2rem 2.8rem;
}

.urp-hero h2 {
    font-size: 1.7rem;
}

.urp-hero p {
    opacity: .8;
}

/* Role rows */
.role-row {
    padding: 1rem 1.3rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
}

.role-avatar {
    width: 44px;
    height: 44px;
    font-size: 1.1rem;
    flex-shrink: 0;
}

.role-name {
    font-size: 1rem;
}

.role-desc {
    font-size: .78rem;
}

.staff-badge {
    font-size: .75rem;
    padding: .2rem .65rem;
}

.role-action-buttons {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.role-action-buttons > * {
    margin-right: 0;
}

/* Tablet */
@media (max-width: 992px) {
    .urp-hero {
        padding: 1.5rem 1.5rem 2.5rem;
    }
    
    .urp-hero h2 {
        font-size: 1.4rem;
    }
    
    .urp-hero p {
        font-size: .9rem;
    }
    
    /* Stack the hero content */
    .urp-hero .d-flex {
        flex-direction: column;
        align-items: flex-start !important;
        gap: 0.75rem;
    }
    
    .urp-hero .d-flex > div:last-child {
        width: 100%;
    }
    
    /* Make form and list stack */
    .row.g-3 {
        flex-direction: column;
    }
    
    .col-lg-4,
    .col-lg-8 {
        flex: 0 0 100%;
        max-width: 100%;
    }
    
    /* Remove sticky on form */
    .sticky-top {
        position: relative !important;
        top: 0 !important;
    }
    
    .role-row {
        padding: 0.85rem 1rem;
        gap: 0.75rem;
    }
}

/* Mobile */
@media (max-width: 768px) {
    .urp-hero {
        padding: 1.25rem 1.25rem 2rem;
        border-radius: 12px;
    }
    
    .urp-hero h2 {
        font-size: 1.2rem;
    }
    
    .urp-hero p {
        font-size: .82rem;
        margin-top: 0.2rem !important;
    }
    
    .card-body.p-4 {
        padding: 1.25rem !important;
    }
    
    .role-row {
        flex-wrap: wrap;
        padding: 0.75rem 1rem;
        gap: 0.5rem;
        border-radius: 10px;
    }
    
    .role-avatar {
        width: 38px;
        height: 38px;
        font-size: 0.9rem;
    }
    
    .role-name {
        font-size: 0.9rem;
    }
    
    .role-desc {
        font-size: 0.72rem;
    }
    
    .staff-badge {
        font-size: 0.7rem;
        padding: 0.15rem 0.5rem;
    }
    
    .role-action-buttons {
        width: 100%;
        justify-content: flex-start;
        gap: 0.4rem;
        margin-top: 0.25rem;
    }
    
    .role-action-buttons .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.7rem;
    }
    
    .role-action-buttons .btn-sm i {
        font-size: 0.7rem;
    }
    
    /* Card header on mobile */
    .card-header {
        flex-direction: column;
        align-items: flex-start !important;
        gap: 0.5rem;
        padding: 0.75rem 1rem;
    }
    
    .card-header h6 {
        font-size: 0.9rem;
    }
    
    .card-header input {
        width: 100% !important;
        max-width: 100% !important;
    }
    
    .card-body.p-3 {
        padding: 0.75rem !important;
    }
    
    /* Form group labels */
    .form-label {
        font-size: 0.82rem;
    }
    
    .form-control {
        font-size: 14px;
        padding: 0.5rem 0.75rem;
    }
    
    /* Buttons */
    .btn {
        font-size: 0.82rem;
        padding: 0.4rem 0.8rem;
    }
    
    .btn-block {
        padding: 0.5rem;
    }
    
    /* Modal on mobile */
    .modal-dialog {
        margin: 0.5rem;
    }
    
    .modal-content {
        border-radius: 12px !important;
    }
    
    .modal-header {
        padding: 0.75rem 1rem;
        border-radius: 12px 12px 0 0 !important;
    }
    
    .modal-header h5 {
        font-size: 1rem;
    }
    
    .modal-body {
        padding: 1rem !important;
    }
    
    .modal-footer {
        padding: 0.75rem 1rem;
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    
    .modal-footer .btn {
        flex: 1;
        min-width: 80px;
        justify-content: center;
    }
}

/* Mobile small screens */
@media (max-width: 576px) {
    .urp-hero {
        padding: 1rem 1rem 1.75rem;
        border-radius: 10px;
    }
    
    .urp-hero h2 {
        font-size: 1rem;
    }
    
    .urp-hero p {
        font-size: 0.75rem;
    }
    
    .card-body.p-4 {
        padding: 1rem !important;
    }
    
    .role-row {
        padding: 0.6rem 0.75rem;
        gap: 0.4rem;
        border-radius: 8px;
    }
    
    .role-avatar {
        width: 32px;
        height: 32px;
        font-size: 0.75rem;
    }
    
    .role-name {
        font-size: 0.82rem;
    }
    
    .role-desc {
        font-size: 0.68rem;
    }
    
    .staff-badge {
        font-size: 0.65rem;
        padding: 0.1rem 0.4rem;
    }
    
    .role-action-buttons {
        gap: 0.3rem;
    }
    
    .role-action-buttons .btn-sm {
        padding: 0.2rem 0.4rem;
        font-size: 0.65rem;
        min-height: 28px;
        min-width: 28px;
    }
    
    .role-action-buttons .btn-sm i {
        font-size: 0.65rem;
    }
    
    .card-header {
        padding: 0.6rem 0.75rem;
    }
    
    .card-header h6 {
        font-size: 0.82rem;
    }
    
    .card-body.p-3 {
        padding: 0.5rem !important;
    }
    
    .form-label {
        font-size: 0.75rem;
    }
    
    .form-control {
        font-size: 13px;
        padding: 0.4rem 0.6rem;
        border-radius: 8px;
    }
    
    .btn {
        font-size: 0.75rem;
        padding: 0.35rem 0.6rem;
    }
    
    .btn-block {
        padding: 0.4rem;
    }
    
    .card-footer {
        padding: 0.6rem 0.75rem !important;
        font-size: 0.7rem !important;
    }
    
    .card-footer i {
        font-size: 0.7rem;
    }
    
    /* Modal on small screens */
    .modal-dialog {
        margin: 0.25rem;
    }
    
    .modal-box {
        padding: 0.75rem;
    }
    
    .modal-title {
        font-size: 0.9rem;
    }
    
    .modal-body .form-group {
        margin-bottom: 0.75rem;
    }
}

/* Very small screens */
@media (max-width: 400px) {
    .urp-hero {
        padding: 0.75rem 0.75rem 1.5rem;
    }
    
    .urp-hero h2 {
        font-size: 0.9rem;
    }
    
    .role-row {
        padding: 0.5rem 0.6rem;
    }
    
    .role-avatar {
        width: 28px;
        height: 28px;
        font-size: 0.65rem;
    }
    
    .role-name {
        font-size: 0.75rem;
    }
    
    .role-desc {
        font-size: 0.62rem;
    }
    
    .role-action-buttons .btn-sm {
        padding: 0.15rem 0.3rem;
        font-size: 0.6rem;
        min-height: 24px;
        min-width: 24px;
    }
    
    .role-action-buttons .btn-sm i {
        font-size: 0.6rem;
    }
    
    .card-header h6 {
        font-size: 0.75rem;
    }
    
    .card-header input {
        font-size: 12px;
        padding: 0.3rem 0.5rem;
    }
    
    .form-control {
        font-size: 12px;
        padding: 0.3rem 0.5rem;
    }
}

/* Empty state on mobile */
@media (max-width: 576px) {
    .empty-state {
        padding: 1.5rem 0.5rem;
    }
    
    .empty-state i {
        font-size: 2rem;
    }
    
    .empty-state h5 {
        font-size: 0.9rem;
    }
    
    .empty-state p {
        font-size: 0.75rem;
    }
}

/* Fix for nested flex on mobile */
@media (max-width: 768px) {
    .role-row .flex-grow-1 {
        flex: 1 1 100%;
        min-width: 0;
    }
}

/* Improve touch targets on mobile */
@media (max-width: 576px) {
    .btn,
    .role-row,
    .role-action-buttons .btn-sm,
    .card-header input,
    .form-control {
        cursor: pointer;
        -webkit-tap-highlight-color: transparent;
    }
    
    .btn:active,
    .role-row:active {
        transform: scale(0.98);
    }
    
    .role-action-buttons .btn-sm {
        min-height: 32px;
        min-width: 32px;
    }
}

/* Smooth transitions */
.role-row,
.btn,
.card,
.urp-hero {
    transition: all 0.2s ease;
}

/* Fix for role action buttons spacing */
.role-action-buttons {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.role-action-buttons > * {
    margin-right: 0;
}

@media (max-width: 768px) {
    .role-action-buttons {
        flex-wrap: wrap;
    }
}

/* Fix for navbar include on mobile */
@media (max-width: 768px) {
    .urp-hero .d-flex > div:last-child {
        width: 100%;
    }
    
    .urp-hero .d-flex > div:last-child .btn-group {
        width: 100%;
        display: flex;
        flex-wrap: wrap;
        gap: 0.3rem;
    }
    
    .urp-hero .d-flex > div:last-child .btn-group .btn {
        flex: 1;
        min-width: 60px;
        justify-content: center;
        font-size: 0.75rem;
        padding: 0.3rem 0.5rem;
    }
}

/* Fix for search input placeholder on mobile */
@media (max-width: 576px) {
    .card-header input::placeholder {
        font-size: 12px;
        opacity: 0.6;
    }
}
</style>
@endsection

@section('content')
<div class="container-fluid px-3 py-3">

    {{-- HERO --}}
    <div class="urp-hero mb-4">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
            <div>
                <h2><i class="fa fa-user-tag mr-2"></i>Manage Roles</h2>
                <p class="mt-2" style="opacity:.8;">Create custom roles for your school — Headteacher, Bursar, Secretary, etc.</p>
            </div>
            <div>@include('user-rights._nav')</div>
        </div>
    </div>

    <div class="row g-3">
        {{-- CREATE FORM --}}
        @if(PermissionHelper::canFeature('create_role'))
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm sticky-top" style="border-radius:var(--radius);top:80px;">
                <div class="card-header bg-white border-bottom" style="border-radius:var(--radius) var(--radius) 0 0;">
                    <h6 class="mb-0 font-weight-700" style="color:var(--urp-dark);">
                        <i class="fa fa-plus-circle mr-2 text-primary"></i>Add New Role
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="form-group">
                        <label class="form-label font-weight-600 text-sm">Role Name <span class="text-danger">*</span></label>
                        <input type="text" id="roleName" class="form-control" placeholder="e.g. Bursar, Secretary, Headteacher">
                    </div>
                    <div class="form-group">
                        <label class="form-label font-weight-600 text-sm">Description</label>
                        <input type="text" id="roleDesc" class="form-control" placeholder="Brief description (optional)">
                    </div>
                    <button id="btnSaveRole" class="btn btn-primary btn-block">
                        <i class="fa fa-save mr-2"></i>Save Role
                    </button>
                </div>

                {{-- TIPS --}}
                <div class="card-footer bg-light" style="border-radius:0 0 var(--radius) var(--radius);">
                    <div class="text-muted" style="font-size:.78rem;">
                        <i class="fa fa-lightbulb text-warning mr-1"></i>
                        <strong>Tip:</strong> After creating a role, go to <em>Module Permissions</em> to configure what each role can access.
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- ROLES LIST --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm" style="border-radius:var(--radius);">
                <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center" style="border-radius:var(--radius) var(--radius) 0 0;">
                    <h6 class="mb-0 font-weight-700" style="color:var(--urp-dark);">
                        All Roles <span class="badge badge-primary ml-2" id="roleCountBadge">{{ $roles->count() }}</span>
                    </h6>
                    <input type="text" id="searchRoles" class="form-control form-control-sm w-auto" placeholder="&#xf002; Search roles..." style="font-family:inherit;max-width:180px;">
                </div>
                <div class="card-body p-3" id="rolesContainer">
                    @forelse($roles as $i => $role)
                    <div class="role-row mb-2" data-id="{{ $role->id }}" style="animation-delay:{{ $i * 0.05 }}s;">
                        <div class="role-avatar">{{ strtoupper(substr($role->name,0,1)) }}</div>
                        <div class="flex-grow-1">
                            <div class="role-name">{{ $role->name }}</div>
                            <div class="role-desc">{{ $role->description ?: 'No description' }}</div>
                        </div>
                        <span class="staff-badge">
                            <i class="fa fa-users mr-1"></i>{{ $role->teachers_count }}
                        </span>
                       <div class="d-flex role-action-buttons">
    @if(PermissionHelper::canFeature('view_permissions'))
    <a href="{{ route('urp.permissions.index') }}?role={{ $role->id }}"
       class="btn btn-sm btn-outline-primary" title="Set permissions for this role">
        <i class="fa fa-shield-alt"></i>
    </a>
    @endif

    @if(PermissionHelper::canFeature('edit_role'))
    <button class="btn btn-sm btn-outline-secondary btn-edit-role"
            data-id="{{ $role->id }}"
            data-name="{{ $role->name }}"
            data-desc="{{ $role->description }}"
            title="Edit role">
        <i class="fa fa-edit"></i>
    </button>
    @endif

    @if(PermissionHelper::canFeature('delete_role'))
    <button class="btn btn-sm btn-outline-danger btn-delete-role"
            data-id="{{ $role->id }}"
            data-name="{{ $role->name }}"
            data-count="{{ $role->teachers_count }}"
            title="Delete role">
        <i class="fa fa-trash"></i>
    </button>
    @endif
</div>
                    </div>
                    @empty
                    <div class="empty-state">
                        <i class="fa fa-user-tag"></i>
                        <h5 style="color:#6b7280;">No roles created yet</h5>
                        <p style="font-size:.85rem;">Use the form on the left to create your first school role.</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

{{-- EDIT MODAL --}}
<div class="modal fade" id="editRoleModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:var(--radius);border:none;">
            <div class="modal-header" style="background:linear-gradient(135deg,var(--urp-dark),var(--urp-accent));border-radius:var(--radius) var(--radius) 0 0;">
                <h5 class="modal-title text-white"><i class="fa fa-edit mr-2"></i>Edit Role</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" id="editRoleId">
                <div class="form-group">
                    <label class="font-weight-600">Role Name <span class="text-danger">*</span></label>
                    <input type="text" id="editRoleName" class="form-control">
                </div>
                <div class="form-group">
                    <label class="font-weight-600">Description</label>
                    <input type="text" id="editRoleDesc" class="form-control">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" id="btnUpdateRole" class="btn btn-primary">
                    <i class="fa fa-save mr-1"></i>Update Role
                </button>
            </div>
        </div>
    </div>
</div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

// ── ROLE ROW TEMPLATE ──
function buildRoleRow(role, delay=0) {
    return `
    <div class="role-row mb-2" data-id="${role.id}" style="animation-delay:${delay}s;">
        <div class="role-avatar">${role.name.charAt(0).toUpperCase()}</div>
        <div class="flex-grow-1">
            <div class="role-name">${escHtml(role.name)}</div>
            <div class="role-desc">${escHtml(role.description || 'No description')}</div>
        </div>
        <span class="staff-badge"><i class="fa fa-users mr-1"></i>0</span>
        <div class="d-flex gap-1">
            <a href="{{ route('urp.permissions.index') }}?role=${role.id}" class="btn btn-sm btn-outline-primary" title="Permissions"><i class="fa fa-shield-alt"></i></a>
            <button class="btn btn-sm btn-outline-secondary btn-edit-role"
                data-id="${role.id}" data-name="${escHtml(role.name)}" data-desc="${escHtml(role.description||'')}">
                <i class="fa fa-edit"></i>
            </button>
            <button class="btn btn-sm btn-outline-danger btn-delete-role"
                data-id="${role.id}" data-name="${escHtml(role.name)}" data-count="0">
                <i class="fa fa-trash"></i>
            </button>
        </div>
    </div>`;
}
function escHtml(s){ const d=document.createElement('div');d.appendChild(document.createTextNode(s));return d.innerHTML; }

// ── SAVE ROLE ──
$('#btnSaveRole').on('click', function() {
    const name = $('#roleName').val().trim();
    const desc = $('#roleDesc').val().trim();
    if (!name) { Swal.fire('Required', 'Role name is required.', 'warning'); return; }

    $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i>Saving...');

    $.ajax({
        url: '{{ route("urp.roles.store") }}',
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
        contentType: 'application/json',
        data: JSON.stringify({ name, description: desc }),
        success(res) {
            const container = $('#rolesContainer');
            const empty = container.find('.empty-state');
            if (empty.length) empty.remove();
            container.prepend(buildRoleRow(res.role));
            updateCount(1);
            $('#roleName, #roleDesc').val('');
            Swal.fire({ icon:'success', title:'Done!', text: res.message, timer:2000, showConfirmButton:false });
        },
        error(xhr) {
            const msg = xhr.responseJSON?.message || 'Something went wrong.';
            Swal.fire('Error', msg, 'error');
        },
        complete() {
            $('#btnSaveRole').prop('disabled', false).html('<i class="fa fa-save mr-2"></i>Save Role');
        }
    });
});

// ── OPEN EDIT MODAL ──
$(document).on('click', '.btn-edit-role', function() {
    $('#editRoleId').val($(this).data('id'));
    $('#editRoleName').val($(this).data('name'));
    $('#editRoleDesc').val($(this).data('desc'));
    $('#editRoleModal').modal('show');
});

// ── UPDATE ROLE ──
$('#btnUpdateRole').on('click', function() {
    const id = $('#editRoleId').val();
    const name = $('#editRoleName').val().trim();
    const desc = $('#editRoleDesc').val().trim();
    if (!name) { Swal.fire('Required', 'Role name cannot be empty.', 'warning'); return; }

    $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i>Updating...');

    $.ajax({
        url: `/user-rights/roles/${id}`,
        method: 'PUT',
        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
        contentType: 'application/json',
        data: JSON.stringify({ name, description: desc }),
        success(res) {
            const row = $(`.role-row[data-id="${id}"]`);
            row.find('.role-name').text(res.role.name);
            row.find('.role-desc').text(res.role.description || 'No description');
            row.find('.role-avatar').text(res.role.name.charAt(0).toUpperCase());
            row.find('.btn-edit-role').data('name', res.role.name).data('desc', res.role.description);
            $('#editRoleModal').modal('hide');
            Swal.fire({ icon:'success', title:'Updated!', text: res.message, timer:2000, showConfirmButton:false });
        },
        error(xhr) { Swal.fire('Error', xhr.responseJSON?.message || 'Update failed.', 'error'); },
        complete() { $('#btnUpdateRole').prop('disabled', false).html('<i class="fa fa-save mr-1"></i>Update Role'); }
    });
});

// ── DELETE ROLE ──
$(document).on('click', '.btn-delete-role', function() {
    const id    = $(this).data('id');
    const name  = $(this).data('name');
    const count = $(this).data('count');

    if (count > 0) {
        Swal.fire('Cannot Delete', `"${name}" has ${count} staff assigned. Remove them first.`, 'warning');
        return;
    }

    Swal.fire({
        title: `Delete "${name}"?`,
        text: 'This will remove the role and all its permission settings.',
        icon: 'warning', showCancelButton: true,
        confirmButtonColor: '#dc2626', confirmButtonText: 'Yes, Delete',
    }).then(result => {
        if (!result.isConfirmed) return;
        $.ajax({
            url: `/user-rights/roles/${id}`,
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            success() {
                const row = $(`.role-row[data-id="${id}"]`);
                row.fadeOut(300, function() {
                    row.remove();
                    updateCount(-1);
                    if ($('.role-row').length === 0) {
                        $('#rolesContainer').html(`<div class="empty-state">
                            <i class="fa fa-user-tag"></i>
                            <h5 style="color:#6b7280;">No roles created yet</h5>
                        </div>`);
                    }
                });
                Swal.fire({ icon:'success', title:'Deleted!', timer:1800, showConfirmButton:false });
            },
            error(xhr) { Swal.fire('Error', xhr.responseJSON?.message || 'Delete failed.', 'error'); }
        });
    });
});

// ── SEARCH ──
$('#searchRoles').on('input', function() {
    const q = $(this).val().toLowerCase();
    $('.role-row').each(function() {
        const name = $(this).find('.role-name').text().toLowerCase();
        $(this).toggle(name.includes(q));
    });
});

function updateCount(delta) {
    const badge = $('#roleCountBadge');
    badge.text(parseInt(badge.text()) + delta);
}
</script>
@endsection