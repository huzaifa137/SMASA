{{-- resources/views/user-rights/assign-roles/index.blade.php --}}
@extends('layouts-side-bar.master')

@section('css')
<style>
:root { --urp-primary:#4f46e5; --urp-dark:#1e1b4b; --urp-accent:#7c3aed; --radius:14px; }
.urp-hero {
    background:linear-gradient(135deg,var(--urp-dark) 0%,#312e81 60%,var(--urp-accent) 100%);
    border-radius:var(--radius); padding:2rem 2rem 2.8rem;
    margin-bottom:-1.5rem; position:relative; overflow:hidden; color:#fff;
}
.urp-hero h2 { font-size:1.7rem; font-weight:700; margin-bottom:.25rem; }

/* TEACHER TABLE */
.teacher-card {
    background:#fff; border-radius:12px; border:1px solid #e5e7eb;
    padding:.9rem 1.2rem; display:flex; align-items:center; gap:1rem;
    transition:border-color .2s, box-shadow .2s;
    animation:fadeIn .3s ease backwards;
}
.teacher-card:hover { border-color:#a5b4fc; box-shadow:0 4px 14px rgba(79,70,229,.1); }
.teacher-avatar {
    width:44px; height:44px; border-radius:50%; flex-shrink:0;
    display:flex; align-items:center; justify-content:center;
    font-size:1rem; font-weight:700; color:#fff;
}
.teacher-name { font-weight:600; font-size:.92rem; color:#1e1b4b; }
.teacher-sub  { font-size:.76rem; color:#9ca3af; }
.role-select  { border-radius:8px; border:1.5px solid #e5e7eb; font-size:.82rem; padding:.35rem .65rem; min-width:160px; cursor:pointer; }
.role-select:focus { border-color:var(--urp-primary); outline:none; box-shadow:0 0 0 3px rgba(79,70,229,.15); }
.assigned-badge  { background:#dcfce7; color:#15803d; border-radius:20px; padding:.2rem .65rem; font-size:.72rem; font-weight:600; display:inline-flex; align-items:center; gap:.3rem; }
.no-role-badge   { background:#fef3c7; color:#92400e; border-radius:20px; padding:.2rem .65rem; font-size:.72rem; font-weight:600; }
.save-btn {
    border:none; border-radius:8px; padding:.32rem .75rem; font-size:.8rem;
    font-weight:600; cursor:pointer; transition:all .2s; display:inline-flex; align-items:center; gap:.35rem;
}
.save-btn.pending  { background:#f0f0ff; color:var(--urp-primary); }
.save-btn.saving   { background:#e5e7eb; color:#6b7280; cursor:default; pointer-events:none; }
.save-btn.saved    { background:#dcfce7; color:#15803d; }
.save-btn.error    { background:#fee2e2; color:#dc2626; }
@keyframes fadeIn { from{opacity:0;transform:translateY(8px);}to{opacity:1;transform:translateY(0);} }
.filter-bar { background:#fff; border-radius:12px; border:1px solid #e5e7eb; padding:.9rem 1.2rem; margin-bottom:1rem; }
.stat-pill { display:inline-flex; align-items:center; gap:.4rem; background:#f3f4f6; border-radius:8px; padding:.35rem .85rem; font-size:.8rem; font-weight:600; color:#374151; }
.stat-pill.green  { background:#dcfce7; color:#15803d; }
.stat-pill.yellow { background:#fef3c7; color:#92400e; }
</style>
@endsection

@section('content')
<div class="container-fluid px-3 py-3">

    {{-- HERO --}}
    <div class="urp-hero mb-4">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
            <div>
                <h2><i class="fa fa-users-cog mr-2"></i>Assign Roles to Staff</h2>
                <p class="mt-1" style="opacity:.8;">Link each staff member to a role. Their access is controlled by that role's permissions.</p>
            </div>
            <div>@include('user-rights._nav')</div>
        </div>
    </div>

    {{-- STATS + FILTER BAR --}}
    <div class="filter-bar d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <div class="d-flex gap-2 flex-wrap">
            <div class="stat-pill green">
                <i class="fa fa-user-check"></i>
                <span id="countAssigned">{{ $teachers->filter(fn($t) => $t->schoolRoleAssignment)->count() }}</span> Assigned
            </div>
            <div class="stat-pill yellow">
                <i class="fa fa-user-clock"></i>
                <span id="countUnassigned">{{ $teachers->filter(fn($t) => !$t->schoolRoleAssignment)->count() }}</span> No Role
            </div>
            <div class="stat-pill">
                <i class="fa fa-users"></i>
                {{ $teachers->count() }} Total Staff
            </div>
        </div>
        <div class="d-flex gap-2 flex-wrap align-items-center">
            <select id="filterRole" class="form-control form-control-sm" style="min-width:160px;border-radius:8px;">
                <option value="">All Roles</option>
                <option value="__none__">No Role Assigned</option>
                @foreach($roles as $role)
                <option value="{{ $role->id }}">{{ $role->name }}</option>
                @endforeach
            </select>
            <input type="text" id="searchTeacher" class="form-control form-control-sm" placeholder="&#xf002;  Search staff..." style="min-width:180px;border-radius:8px;font-family:inherit;">
        </div>
    </div>

    @if($teachers->isEmpty())
    <div class="text-center py-5 text-muted">
        <i class="fa fa-users fa-3x mb-3 d-block" style="opacity:.3;"></i>
        <h5>No staff found for this school.</h5>
    </div>
    @else
    <div id="teacherList">
        @foreach($teachers as $i => $teacher)
        @php
            $assignment = $teacher->schoolRoleAssignment;
            $currentRoleId = $assignment?->school_role_id;
            $colors = ['#4f46e5','#7c3aed','#059669','#d97706','#dc2626','#0ea5e9','#0891b2'];
            $color  = $colors[$i % count($colors)];
        @endphp
        <div class="teacher-card mb-2"
             data-teacher-id="{{ $teacher->id }}"
             data-role-id="{{ $currentRoleId ?? '' }}"
             data-name="{{ strtolower($teacher->firstname.' '.$teacher->surname) }}"
             style="animation-delay:{{ $i * 0.03 }}s;">

            {{-- Avatar --}}
            <div class="teacher-avatar" style="background:{{ $color }};">
                {{ strtoupper(substr($teacher->firstname,0,1).substr($teacher->surname,0,1)) }}
            </div>

            {{-- Info --}}
            <div class="flex-grow-1">
                <div class="teacher-name">{{ $teacher->firstname }} {{ $teacher->surname }}</div>
                <div class="teacher-sub">
                    @if($teacher->email) {{ $teacher->email }} &bull; @endif
                    {{ $teacher->phonenumber ?: 'No phone' }}
                </div>
            </div>

            {{-- Current role badge --}}
            <div class="d-none d-sm-block">
                @if($assignment?->schoolRole)
                    <span class="assigned-badge role-badge-display">
                        <i class="fa fa-crown"></i>{{ $assignment->schoolRole->name }}
                    </span>
                @else
                    <span class="no-role-badge role-badge-display">No Role</span>
                @endif
            </div>

            {{-- Role selector --}}
            <select class="role-select teacher-role-select" data-teacher-id="{{ $teacher->id }}">
                <option value="">— Remove Role —</option>
                @foreach($roles as $role)
                <option value="{{ $role->id }}" {{ $currentRoleId == $role->id ? 'selected' : '' }}>
                    {{ $role->name }}
                </option>
                @endforeach
            </select>

            {{-- Save button --}}
            <button class="save-btn pending btn-save-role" data-teacher-id="{{ $teacher->id }}" style="display:none;">
                <i class="fa fa-check"></i> Apply
            </button>
        </div>
        @endforeach
    </div>
    @endif
</div>
            </div>
</div>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

// ── SHOW SAVE BUTTON WHEN SELECTION CHANGES ──
document.querySelectorAll('.teacher-role-select').forEach(sel => {
    const teacherId = sel.dataset.teacherId;
    const saveBtn   = document.querySelector(`.btn-save-role[data-teacher-id="${teacherId}"]`);
    const original  = sel.value;

    sel.addEventListener('change', function() {
        if (this.value !== original) {
            saveBtn.style.display = 'inline-flex';
            saveBtn.className = 'save-btn pending btn-save-role';
            saveBtn.innerHTML = '<i class="fa fa-check"></i> Apply';
        } else {
            saveBtn.style.display = 'none';
        }
    });
});

// ── APPLY / SAVE ROLE ──
document.querySelectorAll('.btn-save-role').forEach(btn => {
    btn.addEventListener('click', function() {
        const teacherId = this.dataset.teacherId;
        const sel = document.querySelector(`.teacher-role-select[data-teacher-id="${teacherId}"]`);
        const roleId = sel.value;
        const card   = document.querySelector(`.teacher-card[data-teacher-id="${teacherId}"]`);
        const badge  = card.querySelector('.role-badge-display');

        this.className = 'save-btn saving btn-save-role';
        this.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Saving...';

        fetch('{{ route("urp.assign.store") }}', {
            method: 'POST',
            headers: { 'Content-Type':'application/json', 'Accept':'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify({ teacher_id: teacherId, school_role_id: roleId || null })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                this.className = 'save-btn saved btn-save-role';
                this.innerHTML = '<i class="fa fa-check-circle"></i> Saved';

                // Update badge
                const roleName = sel.options[sel.selectedIndex].text;
                if (roleId) {
                    badge.className = 'assigned-badge role-badge-display';
                    badge.innerHTML = `<i class="fa fa-crown"></i>${roleName}`;
                } else {
                    badge.className = 'no-role-badge role-badge-display';
                    badge.innerHTML = 'No Role';
                }

                // Update card data attr
                card.dataset.roleId = roleId;
                updateStats();

                setTimeout(() => {
                    this.style.display = 'none';
                    this.className = 'save-btn pending btn-save-role';
                    this.innerHTML = '<i class="fa fa-check"></i> Apply';
                }, 2000);

                // Subtle toast
                Swal.fire({ icon:'success', text: data.message, timer:1800, showConfirmButton:false, toast:true, position:'top-end' });
            } else {
                this.className = 'save-btn error btn-save-role';
                this.innerHTML = '<i class="fa fa-times"></i> Error';
                Swal.fire('Error', data.message || 'Something went wrong.', 'error');
            }
        })
        .catch(() => {
            this.className = 'save-btn error btn-save-role';
            this.innerHTML = '<i class="fa fa-times"></i> Error';
        });
    });
});

// ── SEARCH ──
document.getElementById('searchTeacher').addEventListener('input', function() {
    const q = this.value.toLowerCase();
    applyFilters();
});

// ── FILTER BY ROLE ──
document.getElementById('filterRole').addEventListener('change', applyFilters);

function applyFilters() {
    const q      = document.getElementById('searchTeacher').value.toLowerCase();
    const roleF  = document.getElementById('filterRole').value;

    document.querySelectorAll('.teacher-card').forEach(card => {
        const name   = card.dataset.name || '';
        const roleId = card.dataset.roleId || '';

        const matchName = !q || name.includes(q);
        let   matchRole = true;
        if (roleF === '__none__') matchRole = !roleId;
        else if (roleF) matchRole = roleId === roleF;

        card.style.display = (matchName && matchRole) ? 'flex' : 'none';
    });
}

function updateStats() {
    const cards   = document.querySelectorAll('.teacher-card');
    let assigned  = 0;
    let noRole    = 0;
    cards.forEach(c => {
        if (c.dataset.roleId) assigned++; else noRole++;
    });
    document.getElementById('countAssigned').textContent   = assigned;
    document.getElementById('countUnassigned').textContent = noRole;
}
</script>
@endsection