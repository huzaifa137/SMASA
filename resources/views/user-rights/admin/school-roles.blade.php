{{-- resources/views/user-rights/admin/school-roles.blade.php --}}
@extends('layouts-side-bar.master')

@section('css')
    <style>
        :root {
            --urp-primary: #4f46e5;
            --urp-dark: #1e1b4b;
            --urp-accent: #7c3aed;
            --teal: #0d9488;
            --amber: #d97706;
            --red: #dc2626;
            --radius: 14px;
        }

        .urp-hero {
            background: linear-gradient(135deg, var(--urp-dark) 0%, #312e81 60%, var(--urp-accent) 100%);
            border-radius: var(--radius);
            padding: 1.8rem 2rem 2.6rem;
            margin-bottom: -1.5rem;
            position: relative;
            overflow: hidden;
            color: #fff;
        }

        .urp-hero h2 { font-size: 1.5rem; font-weight: 700; margin-bottom: .2rem; }

        .urp-breadcrumb {
            font-size: .78rem;
            opacity: .85;
            margin-bottom: .6rem;
        }

        .urp-breadcrumb a { color: #c7d2fe; text-decoration: none; }
        .urp-breadcrumb a:hover { text-decoration: underline; }

        .hero-code-chip {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            background: rgba(255, 255, 255, .15);
            border: 1px solid rgba(255, 255, 255, .25);
            border-radius: 20px;
            padding: .25rem .8rem;
            font-size: .75rem;
            font-weight: 600;
        }

        /* ── stat pills ── */
        .filter-bar {
            background: #fff;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            padding: .9rem 1.2rem;
            margin-bottom: 1rem;
        }

        .stat-pill {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            background: #f3f4f6;
            border-radius: 8px;
            padding: .35rem .85rem;
            font-size: .8rem;
            font-weight: 600;
            color: #374151;
        }

        .stat-pill.green { background: #dcfce7; color: #15803d; }
        .stat-pill.yellow { background: #fef3c7; color: #92400e; }
        .stat-pill.red { background: #fee2e2; color: #b91c1c; }

        /* ── risk banner ── */
        .risk-banner {
            display: flex;
            align-items: center;
            gap: .9rem;
            padding: .9rem 1.3rem;
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-left: 4px solid var(--red);
            border-radius: var(--radius);
            font-size: .82rem;
            color: #7f1d1d;
            margin-bottom: 1.25rem;
        }

        /* ── role rows ── */
        .role-row {
            background: #fff;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            padding: 1rem 1.3rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: border-color .2s, box-shadow .2s;
            animation: fadeIn .3s ease backwards;
            flex-wrap: wrap;
        }

        .role-row:hover { border-color: var(--urp-primary); box-shadow: 0 4px 16px rgba(79, 70, 229, .1); }

        .role-avatar {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--urp-primary), var(--urp-accent));
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 1.1rem;
            font-weight: 700;
            flex-shrink: 0;
        }

        .role-name { font-size: 1rem; font-weight: 600; color: var(--urp-dark); }
        .role-desc { font-size: .78rem; color: #9ca3af; }

        .staff-badge {
            background: #eef2ff;
            color: var(--urp-primary);
            border-radius: 20px;
            padding: .2rem .65rem;
            font-size: .75rem;
            font-weight: 600;
            white-space: nowrap;
        }

        .urp-badge {
            border-radius: 20px;
            padding: .2rem .65rem;
            font-size: .7rem;
            font-weight: 700;
            white-space: nowrap;
            display: inline-flex;
            align-items: center;
            gap: .3rem;
        }

        .urp-badge.on { background: #dcfce7; color: #15803d; }
        .urp-badge.off { background: #fef2f2; color: #b91c1c; }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(8px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .empty-state { text-align: center; padding: 3rem; color: #9ca3af; }
        .empty-state i { font-size: 3rem; margin-bottom: 1rem; display: block; opacity: .35; }

        /* ── staff cards (reused pattern from assign-roles) ── */
        .teacher-card {
            background: #fff;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            padding: .9rem 1.2rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: border-color .2s, box-shadow .2s;
            animation: fadeIn .3s ease backwards;
            flex-wrap: wrap;
        }

        .teacher-card:hover { border-color: #a5b4fc; box-shadow: 0 4px 14px rgba(79, 70, 229, .1); }

        .teacher-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .9rem;
            font-weight: 700;
            color: #fff;
        }

        .teacher-name { font-weight: 600; font-size: .88rem; color: var(--urp-dark); }
        .teacher-sub { font-size: .73rem; color: #9ca3af; }

        .role-select {
            border-radius: 8px;
            border: 1.5px solid #e5e7eb;
            font-size: .8rem;
            padding: .35rem .6rem;
            min-width: 150px;
            cursor: pointer;
        }

        .role-select:focus { border-color: var(--urp-primary); outline: none; box-shadow: 0 0 0 3px rgba(79, 70, 229, .15); }

        .assigned-badge { background: #dcfce7; color: #15803d; border-radius: 20px; padding: .2rem .6rem; font-size: .7rem; font-weight: 600; display: inline-flex; align-items: center; gap: .3rem; }
        .no-role-badge { background: #fef3c7; color: #92400e; border-radius: 20px; padding: .2rem .6rem; font-size: .7rem; font-weight: 600; }

        .save-btn {
            border: none;
            border-radius: 8px;
            padding: .32rem .75rem;
            font-size: .78rem;
            font-weight: 600;
            cursor: pointer;
            transition: all .2s;
            display: inline-flex;
            align-items: center;
            gap: .35rem;
        }

        .save-btn.pending { background: #f0f0ff; color: var(--urp-primary); }
        .save-btn.saving { background: #e5e7eb; color: #6b7280; cursor: default; pointer-events: none; }
        .save-btn.saved { background: #dcfce7; color: #15803d; }
        .save-btn.error { background: #fee2e2; color: #dc2626; }

        /* ── permissions modal matrix (reused from permissions/index) ── */
        .module-card {
            border-radius: 12px;
            border: 2px solid #e5e7eb;
            overflow: hidden;
            transition: border-color .2s;
            background: #fff;
        }

        .module-card.module-enabled { border-color: #a5b4fc; }

        .module-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: .8rem 1rem;
            cursor: pointer;
            user-select: none;
            background: #f8f9fa;
        }

        .module-header:hover { background: #f0f0ff; }

        .module-icon-wrap {
            width: 36px;
            height: 36px;
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .95rem;
            flex-shrink: 0;
        }

        .module-name { font-weight: 700; font-size: .87rem; color: #1e1b4b; }

        .module-features-wrap {
            padding: .7rem 1rem .9rem;
            border-top: 1px solid #f3f4f6;
        }

        .feature-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: .35rem 0;
            border-bottom: 1px solid #f9fafb;
        }

        .feature-row:last-child { border-bottom: none; }
        .feature-name { font-size: .8rem; color: #374151; }

        .tog-wrap { position: relative; display: inline-block; width: 40px; height: 21px; flex-shrink: 0; }
        .tog-wrap input { opacity: 0; width: 0; height: 0; }
        .tog-slider { position: absolute; cursor: pointer; inset: 0; border-radius: 22px; background: #d1d5db; transition: .25s; }
        .tog-slider::before { content: ''; position: absolute; height: 15px; width: 15px; left: 3px; bottom: 3px; background: #fff; border-radius: 50%; transition: .25s; }
        input:checked + .tog-slider { background: var(--urp-primary); }
        input:checked + .tog-slider::before { transform: translateX(19px); }
        input:disabled + .tog-slider { opacity: .45; cursor: default; }

        .saving-spinner { display: none; }
        .is-saving .saving-spinner { display: inline-block; }
        .is-saving .save-text { display: none; }

        #permModal .modal-dialog { max-width: 920px; }
        #permModuleGrid { max-height: 55vh; overflow-y: auto; padding-right: .3rem; }

        .filter-bar {
    gap: 12px; /* adjust as needed */
}

.action-buttons {
    gap: 8px;
}

.action-toolbar {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

/* Password toggle button styles */
.input-group .toggle-password-visibility {
    border-left: none;
    background-color: #f8f9fa;
    color: #6c757d;
    transition: all 0.2s ease;
}

.input-group .toggle-password-visibility:hover {
    background-color: #e9ecef;
    color: #495057;
}

.input-group .toggle-password-visibility:focus {
    box-shadow: none;
    outline: none;
}

.input-group .toggle-password-visibility .fa-eye-slash {
    display: none;
}

.input-group .toggle-password-visibility.showing .fa-eye {
    display: none;
}

.input-group .toggle-password-visibility.showing .fa-eye-slash {
    display: inline-block;
}

    </style>
@endsection

@section('content')
<div class="container-fluid px-3 py-3">

    {{-- HERO --}}
<div class="urp-hero mb-4">
    <div class="d-flex align-items-start justify-content-between flex-wrap gap-2">

        <div>
            <h2><i class="fa fa-user-shield mr-2"></i>{{ $school->name }}</h2>

            <div class="mt-2 d-flex align-items-center gap-2 flex-wrap" style="opacity:.9;">
                <span class="hero-code-chip">
                    <i class="fa fa-hashtag"></i>{{ $school->registration_code }}
                </span> &nbsp;
                <span style="font-size:.8rem;">
                    Manage this school's roles, permissions and staff assignments.
                </span>
            </div>
        </div>

        <a href="{{ route('urp.admin.index') }}"
           class="hero-code-chip text-decoration-none d-inline-flex align-items-center text-white"
           style="cursor:pointer;">
            <i class="fa fa-arrow-left mr-1 text-white"></i>
            All Schools 
        </a>

    </div>
</div>

    {{-- STATS BAR --}}
    @php
        $assignedCount   = $teachers->filter(fn($t) => $t->schoolRoleAssignment)->count();
        $unassignedCount = $teachers->count() - $assignedCount;
    @endphp
    <div class="filter-bar d-flex flex-wrap align-items-center gap-2 mb-3">
        <div class="stat-pill"><i class="fa fa-user-tag"></i>{{ $roles->count() }} Roles</div>
        <div class="stat-pill green"><i class="fa fa-user-check"></i>{{ $assignedCount }} Assigned</div>
        <div class="stat-pill {{ $unassignedCount > 0 ? 'yellow' : '' }}"><i class="fa fa-user-clock"></i>{{ $unassignedCount }} No Role</div>
        <div class="stat-pill"><i class="fa fa-users"></i>{{ $teachers->count() }} Total Staff</div>
    </div>

    {{-- RISK BANNER --}}
    @if($roles->count() > 0 && count($roleIdsWithUrp) === 0)
        <div class="risk-banner">
            <i class="fa fa-triangle-exclamation fa-lg"></i>
            <div>
                <strong>None of this school's roles grant User Rights &amp; Permissions access.</strong>
                If a teacher loses or never gets one of these roles, they will only ever see the bootstrap
                "User Rights" screen — nothing else — until a role here is given that access.
                Use <i class="fa fa-shield-alt"></i> <strong>Permissions</strong> on a role below to fix this.
            </div>
        </div>
    @endif

    @if($teachers->isEmpty())
        <div class="risk-banner" id="zeroStaffBanner" style="background:#eff6ff;border-color:#bfdbfe;border-left-color:var(--urp-primary);color:#1e3a8a;">
            <i class="fa fa-user-slash fa-lg"></i>
            <div>
                <strong>This school has no teacher accounts yet — nobody can log in.</strong>
                School login requires an existing teacher record, so this school is completely locked out
                until a first account is created. Click <strong>Add Staff</strong> in Staff Assignments below
                to create one (you can assign a role to them in the same step).
            </div>
        </div>
    @endif

    <div class="row g-3">
        {{-- CREATE FORM --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm sticky-top" style="border-radius:var(--radius);top:80px;">
                <div class="card-header bg-white border-bottom" style="border-radius:var(--radius) var(--radius) 0 0;">
                    <h6 class="mb-0 font-weight-700" style="color:var(--urp-dark);">
                        <i class="fa fa-plus-circle mr-2 text-primary"></i>Add New Role
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="form-group">
                        <label class="font-weight-600 text-sm">Role Name <span class="text-danger">*</span></label>
                        <input type="text" id="roleName" class="form-control" placeholder="e.g. Bursar, Secretary, Headteacher">
                    </div>
                    <div class="form-group">
                        <label class="font-weight-600 text-sm">Description</label>
                        <input type="text" id="roleDesc" class="form-control" placeholder="Brief description (optional)">
                    </div>
                    <button id="btnSaveRole" class="btn btn-primary btn-block">
                        <i class="fa fa-save mr-2"></i>Save Role
                    </button>
                </div>
                <div class="card-footer bg-light" style="border-radius:0 0 var(--radius) var(--radius);">
                    <div class="text-muted" style="font-size:.78rem;">
                        <i class="fa fa-lightbulb text-warning mr-1"></i>
                        <strong>Tip:</strong> after creating a role, click its <i class="fa fa-shield-alt"></i> icon
                        to tick which modules &amp; features it can access — including
                        <strong>User Rights &amp; Permissions</strong>, if this school needs a recovery role.
                    </div>
                </div>
            </div>
        </div>

        {{-- ROLES LIST --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-3" style="border-radius:var(--radius);">
                <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center" style="border-radius:var(--radius) var(--radius) 0 0;">
                    <h6 class="mb-0 font-weight-700" style="color:var(--urp-dark);">
                        Roles <span class="badge badge-primary ml-2" id="roleCountBadge">{{ $roles->count() }}</span>
                    </h6>
                    <input type="text" id="searchRoles" class="form-control form-control-sm w-auto" placeholder="Search roles..." style="font-family:inherit;max-width:180px;">
                </div>
                <div class="card-body p-3" id="rolesContainer">
                    @forelse($roles as $i => $role)
                    <div class="role-row mb-2" data-id="{{ $role->id }}" style="animation-delay:{{ $i * 0.05 }}s;">
                        <div class="role-avatar">{{ strtoupper(substr($role->name, 0, 1)) }}</div>
                        <div class="flex-grow-1">
                            <div class="role-name">{{ $role->name }}</div>
                            <div class="role-desc">{{ $role->description ?: 'No description' }}</div>
                        </div>
                        <span class="urp-badge {{ in_array($role->id, $roleIdsWithUrp) ? 'on' : 'off' }} role-urp-badge">
                            <i class="fa {{ in_array($role->id, $roleIdsWithUrp) ? 'fa-shield-halved' : 'fa-shield' }}"></i>
                            {{ in_array($role->id, $roleIdsWithUrp) ? 'Has User Rights' : 'No User Rights' }}
                        </span>
                        <span class="staff-badge"><i class="fa fa-users mr-1"></i>{{ $role->teachers_count }} staff</span>
                        <div class="d-flex action-buttons">
                            <button class="btn btn-sm btn-outline-primary btn-permissions"
                                    data-id="{{ $role->id }}" data-name="{{ $role->name }}"
                                    title="Set permissions for this role">
                                <i class="fa fa-shield-alt"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-secondary btn-edit-role"
                                    data-id="{{ $role->id }}" data-name="{{ $role->name }}" data-desc="{{ $role->description }}"
                                    title="Edit role">
                                <i class="fa fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger btn-delete-role"
                                    data-id="{{ $role->id }}" data-name="{{ $role->name }}"
                                    data-count="{{ $role->teachers_count }}"
                                    title="Delete role">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    @empty
                    <div class="empty-state">
                        <i class="fa fa-user-tag"></i>
                        No roles created for this school yet.
                        <br><span style="font-size:.85rem;">Use the form on the left to create the first one.</span>
                    </div>
                    @endforelse
                </div>
            </div>

            {{-- STAFF ASSIGNMENTS --}}
            <div class="card border-0 shadow-sm" style="border-radius:var(--radius);">
                <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2" style="border-radius:var(--radius) var(--radius) 0 0;">
                    <h6 class="mb-0 font-weight-700" style="color:var(--urp-dark);">
                        <i class="fa fa-users-cog mr-2"></i>Staff Assignments
                    </h6>
                    <div class="d-flex align-items-center">
    <input type="text" id="searchTeacher"
        class="form-control form-control-sm w-auto mr-3"
        placeholder="Search staff..."
        style="font-family:inherit;max-width:180px;">

    <button type="button" id="btnOpenAddTeacher" class="btn btn-sm btn-primary">
        <i class="fa fa-user-plus mr-1"></i>Add Staff
    </button>
</div>
                </div>
                <div class="card-body p-3" id="teacherList">
                    @forelse($teachers as $i => $teacher)
                    @php
                        $assignment    = $teacher->schoolRoleAssignment;
                        $currentRoleId = $assignment?->school_role_id;
                        $colors = ['#4f46e5','#7c3aed','#059669','#d97706','#dc2626','#0ea5e9','#0891b2'];
                        $color  = $colors[$i % count($colors)];
                    @endphp
                    <div class="teacher-card mb-2"
                         data-teacher-id="{{ $teacher->id }}"
                         data-role-id="{{ $currentRoleId ?? '' }}"
                         data-name="{{ strtolower($teacher->firstname.' '.$teacher->surname) }}"
                         style="animation-delay:{{ $i * 0.03 }}s;">

                        <div class="teacher-avatar" style="background:{{ $color }};">
                            {{ strtoupper(substr($teacher->firstname,0,1).substr($teacher->surname,0,1)) }}
                        </div>

                        <div class="flex-grow-1">
                            <div class="teacher-name">{{ $teacher->firstname }} {{ $teacher->surname }}</div>
                            <div class="teacher-sub">
                                @if($teacher->email) {{ $teacher->email }} &bull; @endif
                                {{ $teacher->phonenumber ?: 'No phone' }}
                            </div>
                        </div>

                        <div class="d-none d-sm-block">
                            @if($assignment?->schoolRole)
                                <span class="assigned-badge role-badge-display">
                                    <i class="fa fa-crown"></i>{{ $assignment->schoolRole->name }}
                                </span>
                            @else
                                <span class="no-role-badge role-badge-display">No Role</span>
                            @endif
                        </div>

                        <select class="role-select teacher-role-select" data-teacher-id="{{ $teacher->id }}">
                            <option value="">— Remove Role —</option>
                            @foreach($roles as $role)
                            <option value="{{ $role->id }}" {{ $currentRoleId == $role->id ? 'selected' : '' }}>
                                {{ $role->name }}
                            </option>
                            @endforeach
                        </select>

                        <button class="save-btn pending btn-save-role" data-teacher-id="{{ $teacher->id }}" style="display:none;">
                            <i class="fa fa-check"></i> Apply
                        </button>
                    </div>
                    @empty
                    <div class="empty-state">
                        <i class="fa fa-users"></i>
                        No staff records for this school yet.
                        <br><span style="font-size:.85rem;">Click <strong>Add Staff</strong> above to create the first teacher account — they can then be assigned a role below.</span>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ADD STAFF MODAL --}}
<div class="modal fade" id="addTeacherModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border-radius:var(--radius);border:none;">
            <div class="modal-header" style="background:linear-gradient(135deg,var(--urp-dark),var(--urp-accent));border-radius:var(--radius) var(--radius) 0 0;">
                <h5 class="modal-title text-white"><i class="fa fa-user-plus mr-2"></i>Add Staff to {{ $school->name }}</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body p-4">
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label class="font-weight-600 text-sm">Surname <span class="text-danger">*</span></label>
                        <input type="text" id="atSurname" class="form-control" placeholder="Surname">
                    </div>
                    <div class="col-md-6 form-group">
                        <label class="font-weight-600 text-sm">Firstname <span class="text-danger">*</span></label>
                        <input type="text" id="atFirstname" class="form-control" placeholder="Firstname">
                    </div>
                    <div class="col-md-6 form-group">
                        <label class="font-weight-600 text-sm">Othername</label>
                        <input type="text" id="atOthername" class="form-control" placeholder="Other name (optional)">
                    </div>
                    <div class="col-md-6 form-group">
                        <label class="font-weight-600 text-sm">Gender</label>
                        <select id="atGender" class="form-control">
                            <option value="">— Select —</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                        </select>
                    </div>
                    <div class="col-md-6 form-group">
                        <label class="font-weight-600 text-sm">Email <span class="text-danger">*</span></label>
                        <input type="email" id="atEmail" class="form-control" placeholder="name@example.com">
                    </div>
                    <div class="col-md-6 form-group">
                        <label class="font-weight-600 text-sm">Phone Number <span class="text-danger">*</span></label>
                        <input type="tel" id="atPhone" class="form-control" placeholder="Used to log in">
                    </div>
                    
                    {{-- Password Field with Eye Icon --}}
                    <div class="col-md-6 form-group">
                        <label class="font-weight-600 text-sm">Password <span class="text-muted">(optional — leave blank to auto-generate)</span></label>
                        <div class="input-group">
                            <input type="password" id="atPassword" class="form-control" placeholder="Leave blank to auto-generate" autocomplete="new-password">
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary toggle-password-visibility" type="button" data-target="atPassword" style="border-radius:0 8px 8px 0;">
                                    <i class="fa fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Confirm Password Field with Eye Icon --}}
                    <div class="col-md-6 form-group">
                        <label class="font-weight-600 text-sm">Confirm Password</label>
                        <div class="input-group">
                            <input type="password" id="atPasswordConfirm" class="form-control" placeholder="Re-enter password" autocomplete="new-password">
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary toggle-password-visibility" type="button" data-target="atPasswordConfirm" style="border-radius:0 8px 8px 0;">
                                    <i class="fa fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-12 form-group mb-0">
                        <label class="font-weight-600 text-sm">Assign Role <span class="text-muted">(optional — can be done later)</span></label>
                        <select id="atRole" class="form-control">
                            <option value="">— No role yet —</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button id="btnSaveTeacher" class="btn btn-primary"><i class="fa fa-save mr-1"></i>Create Teacher</button>
            </div>
        </div>
    </div>
</div>

{{-- EDIT ROLE MODAL --}}
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

{{-- PERMISSIONS MATRIX MODAL --}}
<div class="modal fade" id="permModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:var(--radius);border:none;">
            <div class="modal-header" style="background:linear-gradient(135deg,var(--urp-dark),var(--urp-accent));border-radius:var(--radius) var(--radius) 0 0;">
                <h5 class="modal-title text-white">
                    <i class="fa fa-shield-alt mr-2"></i>Permissions — <span id="permRoleName"></span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" id="permRoleId">
               <div class="d-flex justify-content-end action-toolbar mb-3">
    <button id="btnEnableAll" class="btn btn-sm btn-outline-success">
        <i class="fa fa-check-double mr-1"></i>Enable All
    </button>
    <button id="btnDisableAll" class="btn btn-sm btn-outline-danger">
        <i class="fa fa-times mr-1"></i>Disable All
    </button>
</div>
                <div class="row g-2" id="permModuleGrid">
                    @foreach($modules as $module)
                        <div class="col-md-6 mb-1">
                            <div class="module-card" data-module-id="{{ $module->id }}" data-module-key="{{ $module->key }}">
                                <div class="module-header" onclick="toggleModuleExpand(this)">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="module-icon-wrap" style="background:#eef2ff;color:var(--urp-primary);">
                                            <i class="{{ $module->icon }}"></i>
                                        </div>
                                        <div>
                                            <div class="module-name">{{ $module->name }}</div>
                                            <div style="font-size:.68rem;color:#9ca3af;">{{ $module->features->count() }} feature{{ $module->features->count() != 1 ? 's' : '' }}</div>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center gap-2" onclick="event.stopPropagation();">
                                        <label class="tog-wrap" title="Toggle module access">
                                            <input type="checkbox" class="module-toggle" data-module-id="{{ $module->id }}" value="{{ $module->id }}">
                                            <span class="tog-slider"></span>
                                        </label>
                                        <i class="fa fa-chevron-down text-muted" style="font-size:.7rem;transition:transform .2s;"></i>
                                    </div>
                                </div>
                                @if($module->features->count() > 0)
                                    <div class="module-features-wrap" style="display:none;">
                                        @foreach($module->features as $feat)
                                            <div class="feature-row">
                                                <span class="feature-name">
                                                    <i class="fa fa-circle" style="font-size:.35rem;color:#d1d5db;margin-right:.4rem;vertical-align:middle;"></i>
                                                    {{ $feat->name }}
                                                </span>
                                                <label class="tog-wrap" title="{{ $feat->name }}">
                                                    <input type="checkbox" class="feature-toggle" data-feature-id="{{ $feat->id }}" data-module-id="{{ $module->id }}" value="{{ $feat->id }}">
                                                    <span class="tog-slider"></span>
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="px-3 py-2 text-muted" style="font-size:.74rem;display:none;">No granular features for this module.</div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button id="btnSavePerm" class="btn btn-primary is-saving-btn">
                    <span class="save-text"><i class="fa fa-save mr-1"></i>Save Permissions</span>
                    <span class="saving-spinner"><i class="fa fa-spinner fa-spin mr-1"></i>Saving...</span>
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
const schoolId = {{ $school->id }};

function escHtml(s) { const d = document.createElement('div'); d.appendChild(document.createTextNode(s)); return d.innerHTML; }

// ════════════════════════════════════════════
// ROLE CRUD
// ════════════════════════════════════════════

function buildRoleRow(role, delay = 0) {
    return `
    <div class="role-row mb-2" data-id="${role.id}" style="animation-delay:${delay}s;">
        <div class="role-avatar">${role.name.charAt(0).toUpperCase()}</div>
        <div class="flex-grow-1">
            <div class="role-name">${escHtml(role.name)}</div>
            <div class="role-desc">${escHtml(role.description || 'No description')}</div>
        </div>
        <span class="urp-badge off role-urp-badge"><i class="fa fa-shield"></i> No User Rights</span>
        <span class="staff-badge"><i class="fa fa-users mr-1"></i>0 staff</span>
        <div class="d-flex gap-1">
            <button class="btn btn-sm btn-outline-primary btn-permissions" data-id="${role.id}" data-name="${escHtml(role.name)}"><i class="fa fa-shield-alt"></i></button>
            <button class="btn btn-sm btn-outline-secondary btn-edit-role" data-id="${role.id}" data-name="${escHtml(role.name)}" data-desc="${escHtml(role.description||'')}"><i class="fa fa-edit"></i></button>
            <button class="btn btn-sm btn-outline-danger btn-delete-role" data-id="${role.id}" data-name="${escHtml(role.name)}" data-count="0"><i class="fa fa-trash"></i></button>
        </div>
    </div>`;
}

document.getElementById('btnSaveRole').addEventListener('click', function () {
    const name = document.getElementById('roleName').value.trim();
    const desc = document.getElementById('roleDesc').value.trim();
    if (!name) { Swal.fire('Required', 'Role name is required.', 'warning'); return; }

    this.disabled = true;
    this.innerHTML = '<i class="fa fa-spinner fa-spin mr-1"></i>Saving...';

    fetch(`/user-rights/admin-schools/${schoolId}/roles`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
        body: JSON.stringify({ name, description: desc })
    })
    .then(r => r.json().then(data => ({ ok: r.ok, data })))
    .then(({ ok, data }) => {
        if (!ok || !data.success) { Swal.fire('Error', data.message || 'Something went wrong.', 'error'); return; }
        const container = document.getElementById('rolesContainer');
        const empty = container.querySelector('.empty-state');
        if (empty) empty.remove();
        container.insertAdjacentHTML('afterbegin', buildRoleRow(data.role));
        updateRoleCount(1);
        document.getElementById('roleName').value = '';
        document.getElementById('roleDesc').value = '';
        addRoleOptionToAllSelects(data.role);
        Swal.fire({ icon: 'success', title: 'Done!', text: data.message, timer: 2000, showConfirmButton: false });
    })
    .catch(() => Swal.fire('Error', 'Something went wrong.', 'error'))
    .finally(() => {
        this.disabled = false;
        this.innerHTML = '<i class="fa fa-save mr-2"></i>Save Role';
    });
});

document.addEventListener('click', function (e) {
    const editBtn = e.target.closest('.btn-edit-role');
    if (editBtn) {
        document.getElementById('editRoleId').value = editBtn.dataset.id;
        document.getElementById('editRoleName').value = editBtn.dataset.name;
        document.getElementById('editRoleDesc').value = editBtn.dataset.desc || '';
        $('#editRoleModal').modal('show');
    }
});

document.getElementById('btnUpdateRole').addEventListener('click', function () {
    const id = document.getElementById('editRoleId').value;
    const name = document.getElementById('editRoleName').value.trim();
    const desc = document.getElementById('editRoleDesc').value.trim();
    if (!name) { Swal.fire('Required', 'Role name cannot be empty.', 'warning'); return; }

    this.disabled = true;
    this.innerHTML = '<i class="fa fa-spinner fa-spin mr-1"></i>Updating...';

    fetch(`/user-rights/admin-schools/roles/${id}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
        body: JSON.stringify({ name, description: desc })
    })
    .then(r => r.json().then(data => ({ ok: r.ok, data })))
    .then(({ ok, data }) => {
        if (!ok || !data.success) { Swal.fire('Error', data.message || 'Update failed.', 'error'); return; }
        const row = document.querySelector(`.role-row[data-id="${id}"]`);
        row.querySelector('.role-name').textContent = data.role.name;
        row.querySelector('.role-desc').textContent = data.role.description || 'No description';
        row.querySelector('.role-avatar').textContent = data.role.name.charAt(0).toUpperCase();
        const editBtn = row.querySelector('.btn-edit-role');
        editBtn.dataset.name = data.role.name;
        editBtn.dataset.desc = data.role.description || '';
        document.querySelectorAll(`.teacher-role-select option[value="${id}"]`).forEach(opt => opt.textContent = data.role.name);
        $('#editRoleModal').modal('hide');
        Swal.fire({ icon: 'success', title: 'Updated!', text: data.message, timer: 2000, showConfirmButton: false });
    })
    .catch(() => Swal.fire('Error', 'Update failed.', 'error'))
    .finally(() => {
        this.disabled = false;
        this.innerHTML = '<i class="fa fa-save mr-1"></i>Update Role';
    });
});

document.addEventListener('click', function (e) {
    const delBtn = e.target.closest('.btn-delete-role');
    if (!delBtn) return;

    const id = delBtn.dataset.id;
    const name = delBtn.dataset.name;
    const count = parseInt(delBtn.dataset.count || '0');

    if (count > 0) {
        Swal.fire('Cannot Delete', `"${name}" has ${count} staff assigned. Remove them first in Staff Assignments below.`, 'warning');
        return;
    }

    Swal.fire({
        title: `Delete "${name}"?`,
        text: 'This will remove the role and all its permission settings.',
        icon: 'warning', showCancelButton: true,
        confirmButtonColor: '#dc2626', confirmButtonText: 'Yes, Delete',
    }).then(result => {
        if (!result.isConfirmed) return;
        fetch(`/user-rights/admin-schools/roles/${id}`, {
            method: 'DELETE',
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
        })
        .then(r => r.json().then(data => ({ ok: r.ok, data })))
        .then(({ ok, data }) => {
            if (!ok || (data && data.success === false)) { Swal.fire('Error', (data && data.message) || 'Delete failed.', 'error'); return; }
            const row = document.querySelector(`.role-row[data-id="${id}"]`);
            row.style.transition = 'opacity .3s';
            row.style.opacity = 0;
            setTimeout(() => {
                row.remove();
                updateRoleCount(-1);
                if (!document.querySelector('.role-row')) {
                    document.getElementById('rolesContainer').innerHTML = `
                        <div class="empty-state"><i class="fa fa-user-tag"></i>No roles created for this school yet.</div>`;
                }
            }, 300);
            removeRoleOptionFromAllSelects(id);
            Swal.fire({ icon: 'success', title: 'Deleted!', timer: 1800, showConfirmButton: false });
        })
        .catch(() => Swal.fire('Error', 'Delete failed.', 'error'));
    });
});

document.getElementById('searchRoles').addEventListener('input', function () {
    const q = this.value.toLowerCase();
    document.querySelectorAll('.role-row').forEach(row => {
        const name = row.querySelector('.role-name').textContent.toLowerCase();
        row.style.display = name.includes(q) ? '' : 'none';
    });
});

function updateRoleCount(delta) {
    const badge = document.getElementById('roleCountBadge');
    badge.textContent = parseInt(badge.textContent) + delta;
}

function addRoleOptionToAllSelects(role) {
    document.querySelectorAll('.teacher-role-select').forEach(sel => {
        const opt = document.createElement('option');
        opt.value = role.id;
        opt.textContent = role.name;
        sel.appendChild(opt);
    });
}

function removeRoleOptionFromAllSelects(id) {
    document.querySelectorAll(`.teacher-role-select option[value="${id}"]`).forEach(opt => opt.remove());
}

// ════════════════════════════════════════════
// PERMISSIONS MATRIX MODAL
// ════════════════════════════════════════════

document.addEventListener('click', function (e) {
    const btn = e.target.closest('.btn-permissions');
    if (!btn) return;

    const roleId = btn.dataset.id;
    const roleName = btn.dataset.name;

    document.getElementById('permRoleId').value = roleId;
    document.getElementById('permRoleName').textContent = roleName;

    // reset
    document.querySelectorAll('#permModuleGrid .module-toggle, #permModuleGrid .feature-toggle').forEach(cb => {
        cb.checked = false; cb.disabled = false;
    });
    updatePermModuleCardStates();

    fetch(`/user-rights/admin-schools/roles/${roleId}/permissions`, {
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf }
    })
    .then(r => r.json())
    .then(data => {
        (data.module_ids || []).forEach(id => {
            const cb = document.querySelector(`#permModuleGrid .module-toggle[data-module-id="${id}"]`);
            if (cb) cb.checked = true;
        });
        (data.feature_ids || []).forEach(id => {
            const cb = document.querySelector(`#permModuleGrid .feature-toggle[data-feature-id="${id}"]`);
            if (cb) cb.checked = true;
        });
        updatePermModuleCardStates();
    })
    .catch(() => {/* first time, no permissions saved yet */});

    $('#permModal').modal('show');
});

document.querySelectorAll('#permModuleGrid .module-toggle').forEach(cb => {
    cb.addEventListener('change', function () {
        const moduleId = this.dataset.moduleId;
        const enabled = this.checked;
        document.querySelectorAll(`#permModuleGrid .feature-toggle[data-module-id="${moduleId}"]`).forEach(fcb => {
            if (!enabled) { fcb.checked = false; fcb.disabled = true; } else { fcb.disabled = false; }
        });
        updatePermModuleCardStates();
    });
});

function updatePermModuleCardStates() {
    document.querySelectorAll('#permModuleGrid .module-card').forEach(card => {
        const toggle = card.querySelector('.module-toggle');
        if (!toggle) return;
        const on = toggle.checked;
        card.classList.toggle('module-enabled', on);
        card.querySelectorAll('.feature-toggle').forEach(fcb => { fcb.disabled = !on; });
    });
}

function toggleModuleExpand(header) {
    const wrap = header.nextElementSibling;
    const arrow = header.querySelector('.fa-chevron-down');
    const isOpen = wrap && wrap.style.display !== 'none';
    if (wrap) wrap.style.display = isOpen ? 'none' : 'block';
    if (arrow) arrow.style.transform = isOpen ? '' : 'rotate(180deg)';
}

document.getElementById('btnEnableAll').addEventListener('click', () => {
    document.querySelectorAll('#permModuleGrid .module-toggle').forEach(cb => cb.checked = true);
    document.querySelectorAll('#permModuleGrid .feature-toggle').forEach(cb => { cb.checked = true; cb.disabled = false; });
    updatePermModuleCardStates();
});

document.getElementById('btnDisableAll').addEventListener('click', () => {
    Swal.fire({
        title: 'Disable all modules?',
        text: 'This will remove all module & feature access for this role.',
        icon: 'warning', showCancelButton: true,
        confirmButtonColor: '#dc2626', confirmButtonText: 'Yes, disable all',
    }).then(r => {
        if (!r.isConfirmed) return;
        document.querySelectorAll('#permModuleGrid .module-toggle, #permModuleGrid .feature-toggle').forEach(cb => {
            cb.checked = false; cb.disabled = false;
        });
        updatePermModuleCardStates();
    });
});

document.getElementById('btnSavePerm').addEventListener('click', function () {
    const roleId = document.getElementById('permRoleId').value;
    if (!roleId) return;

    const btn = this;
    btn.classList.add('is-saving');
    btn.disabled = true;

    const moduleIds = [...document.querySelectorAll('#permModuleGrid .module-toggle:checked')].map(cb => parseInt(cb.value));
    const featureIds = [...document.querySelectorAll('#permModuleGrid .feature-toggle:checked')].map(cb => parseInt(cb.value));

    // does this save grant user_rights module?
    const userRightsModuleCard = [...document.querySelectorAll('#permModuleGrid .module-card')].find(c => c.dataset.moduleKey === 'user_rights');
    const grantsUserRights = userRightsModuleCard ? userRightsModuleCard.querySelector('.module-toggle').checked : false;

    fetch(`/user-rights/admin-schools/roles/${roleId}/permissions`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
        body: JSON.stringify({ module_ids: moduleIds, feature_ids: featureIds })
    })
    .then(r => r.json().then(data => ({ ok: r.ok, data })))
    .then(({ ok, data }) => {
        if (!ok || !data.success) { Swal.fire('Error', (data && data.message) || 'Failed to save.', 'error'); return; }

        // update the badge + risk banner live, no full reload needed
        const row = document.querySelector(`.role-row[data-id="${roleId}"]`);
        if (row) {
            const badge = row.querySelector('.role-urp-badge');
            badge.className = `urp-badge ${grantsUserRights ? 'on' : 'off'} role-urp-badge`;
            badge.innerHTML = `<i class="fa ${grantsUserRights ? 'fa-shield-halved' : 'fa-shield'}"></i> ${grantsUserRights ? 'Has User Rights' : 'No User Rights'}`;
        }

        Swal.fire({ icon: 'success', title: 'Permissions Saved!', text: 'Access settings for this role have been updated.', timer: 2200, showConfirmButton: false });
        $('#permModal').modal('hide');

        if (grantsUserRights) {
            document.querySelector('.risk-banner')?.remove();
        }
    })
    .catch(() => Swal.fire('Error', 'Failed to save. Please try again.', 'error'))
    .finally(() => {
        btn.classList.remove('is-saving');
        btn.disabled = false;
    });
});

// ════════════════════════════════════════════
// STAFF ASSIGNMENTS
// ════════════════════════════════════════════

function handleSaveRoleClick() {
    const teacherId = this.dataset.teacherId;
    const sel = document.querySelector(`.teacher-role-select[data-teacher-id="${teacherId}"]`);
    const roleId = sel.value;
    const card = document.querySelector(`.teacher-card[data-teacher-id="${teacherId}"]`);
    const badge = card.querySelector('.role-badge-display');

    this.className = 'save-btn saving btn-save-role';
    this.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Saving...';

    fetch('{{ route("urp.admin.teachers.assign-role") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
        body: JSON.stringify({ teacher_id: teacherId, school_role_id: roleId || null })
    })
    .then(r => r.json().then(data => ({ ok: r.ok, data })))
    .then(({ ok, data }) => {
        if (!ok || !data.success) {
            this.className = 'save-btn error btn-save-role';
            this.innerHTML = '<i class="fa fa-times"></i> Error';
            Swal.fire('Error', (data && data.message) || 'Something went wrong.', 'error');
            return;
        }

        this.className = 'save-btn saved btn-save-role';
        this.innerHTML = '<i class="fa fa-check-circle"></i> Saved';

        const roleName = roleId ? sel.options[sel.selectedIndex].text : '';
        if (roleId) {
            badge.className = 'assigned-badge role-badge-display';
            badge.innerHTML = `<i class="fa fa-crown"></i>${roleName}`;
        } else {
            badge.className = 'no-role-badge role-badge-display';
            badge.innerHTML = 'No Role';
        }

        card.dataset.roleId = roleId;

        setTimeout(() => {
            this.style.display = 'none';
            this.className = 'save-btn pending btn-save-role';
            this.innerHTML = '<i class="fa fa-check"></i> Apply';
        }, 2000);

        Swal.fire({ icon: 'success', text: data.message, timer: 2200, showConfirmButton: false, toast: true, position: 'top-end' });
    })
    .catch(() => {
        this.className = 'save-btn error btn-save-role';
        this.innerHTML = '<i class="fa fa-times"></i> Error';
    });
}

function wireTeacherRoleSelect(teacherId) {
    const sel = document.querySelector(`.teacher-role-select[data-teacher-id="${teacherId}"]`);
    const saveBtn = document.querySelector(`.btn-save-role[data-teacher-id="${teacherId}"]`);
    const original = sel.value;

    sel.addEventListener('change', function () {
        if (this.value !== original) {
            saveBtn.style.display = 'inline-flex';
            saveBtn.className = 'save-btn pending btn-save-role';
            saveBtn.innerHTML = '<i class="fa fa-check"></i> Apply';
        } else {
            saveBtn.style.display = 'none';
        }
    });

    saveBtn.addEventListener('click', handleSaveRoleClick);
}

document.querySelectorAll('.teacher-role-select').forEach(sel => wireTeacherRoleSelect(sel.dataset.teacherId));

// ════════════════════════════════════════════
// ADD STAFF (CREATE TEACHER)
// ════════════════════════════════════════════

const allRoles = @json($roles->map(fn($r) => ['id' => $r->id, 'name' => $r->name])->values());
const teacherCardColors = ['#4f46e5','#7c3aed','#059669','#d97706','#dc2626','#0ea5e9','#0891b2'];
let teacherCardCount = {{ $teachers->count() }};

document.getElementById('btnOpenAddTeacher').addEventListener('click', function () {
    ['atSurname','atFirstname','atOthername','atEmail','atPhone','atPassword','atPasswordConfirm'].forEach(id => {
        const el = document.getElementById(id);
        el.value = '';
        el.classList.remove('is-invalid');
    });
    document.getElementById('atGender').value = '';
    document.getElementById('atRole').value = '';
    $('#addTeacherModal').modal('show');
});

function roleOptionsHtml(selectedId) {
    return allRoles.map(r => `<option value="${r.id}" ${selectedId == r.id ? 'selected' : ''}>${escHtml(r.name)}</option>`).join('');
}

function buildTeacherCard(teacher, delay = 0) {
    const color = teacherCardColors[teacherCardCount % teacherCardColors.length];
    teacherCardCount++;
    const initials = (teacher.firstname.charAt(0) + teacher.surname.charAt(0)).toUpperCase();
    const roleBadge = teacher.role_name
        ? `<span class="assigned-badge role-badge-display"><i class="fa fa-crown"></i>${escHtml(teacher.role_name)}</span>`
        : `<span class="no-role-badge role-badge-display">No Role</span>`;

    return `
    <div class="teacher-card mb-2" data-teacher-id="${teacher.id}" data-role-id="${teacher.school_role_id || ''}"
         data-name="${escHtml((teacher.firstname + ' ' + teacher.surname).toLowerCase())}" style="animation-delay:${delay}s;">
        <div class="teacher-avatar" style="background:${color};">${initials}</div>
        <div class="flex-grow-1">
            <div class="teacher-name">${escHtml(teacher.firstname)} ${escHtml(teacher.surname)}</div>
            <div class="teacher-sub">${teacher.email ? escHtml(teacher.email) + ' &bull; ' : ''}${escHtml(teacher.phonenumber || 'No phone')}</div>
        </div>
        <div class="d-none d-sm-block">${roleBadge}</div>
        <select class="role-select teacher-role-select" data-teacher-id="${teacher.id}">
            <option value="">— Remove Role —</option>
            ${roleOptionsHtml(teacher.school_role_id)}
        </select>
        <button class="save-btn pending btn-save-role" data-teacher-id="${teacher.id}" style="display:none;"><i class="fa fa-check"></i> Apply</button>
    </div>`;
}

document.getElementById('btnSaveTeacher').addEventListener('click', function () {
    const payload = {
        surname: document.getElementById('atSurname').value.trim(),
        firstname: document.getElementById('atFirstname').value.trim(),
        othername: document.getElementById('atOthername').value.trim(),
        gender: document.getElementById('atGender').value,
        email: document.getElementById('atEmail').value.trim(),
        phonenumber: document.getElementById('atPhone').value.trim(),
        password: document.getElementById('atPassword').value,
        password_confirmation: document.getElementById('atPasswordConfirm').value,
        school_role_id: document.getElementById('atRole').value || null,
    };

    ['atSurname','atFirstname','atEmail','atPhone'].forEach(id => document.getElementById(id).classList.remove('is-invalid'));

    let missing = [];
    if (!payload.surname) missing.push('atSurname');
    if (!payload.firstname) missing.push('atFirstname');
    if (!payload.email) missing.push('atEmail');
    if (!payload.phonenumber) missing.push('atPhone');

    if (missing.length) {
        missing.forEach(id => document.getElementById(id).classList.add('is-invalid'));
        Swal.fire('Required', 'Surname, firstname, email and phone number are required.', 'warning');
        return;
    }

    if (payload.password && payload.password !== payload.password_confirmation) {
        document.getElementById('atPasswordConfirm').classList.add('is-invalid');
        Swal.fire('Password Mismatch', 'Password and confirmation do not match.', 'warning');
        return;
    }

    this.disabled = true;
    this.innerHTML = '<i class="fa fa-spinner fa-spin mr-1"></i>Creating...';

    fetch(`/user-rights/admin-schools/${schoolId}/teachers`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
        body: JSON.stringify(payload)
    })
    .then(r => r.json().then(data => ({ ok: r.ok, data })))
    .then(({ ok, data }) => {
        if (!ok || !data.success) {
            Swal.fire('Error', (data && data.message) || 'Could not create teacher.', 'error');
            return;
        }

        const list = document.getElementById('teacherList');
        const empty = list.querySelector('.empty-state');
        if (empty) empty.remove();
        list.insertAdjacentHTML('beforeend', buildTeacherCard(data.teacher));
        wireTeacherRoleSelect(data.teacher.id);

        const zeroBanner = document.getElementById('zeroStaffBanner');
        if (zeroBanner) zeroBanner.remove();

        // Update stat pills
        const pills = document.querySelectorAll('.filter-bar .stat-pill');
        if (pills[3]) pills[3].innerHTML = `<i class="fa fa-users"></i>${teacherCardCount} Total Staff`;
        if (data.teacher.role_name) {
            if (pills[1]) {
                const n = (parseInt(pills[1].textContent) || 0) + 1;
                pills[1].className = 'stat-pill green';
                pills[1].innerHTML = `<i class="fa fa-user-check"></i>${n} Assigned`;
            }
        } else {
            if (pills[2]) {
                const n = (parseInt(pills[2].textContent) || 0) + 1;
                pills[2].className = 'stat-pill yellow';
                pills[2].innerHTML = `<i class="fa fa-user-clock"></i>${n} No Role`;
            }
        }

        $('#addTeacherModal').modal('hide');

        if (data.temporary_password) {
            Swal.fire({
                icon: 'success',
                title: 'Teacher Created!',
                html: `${data.message}<br><br>
                    <strong>Temporary password:</strong>
                    <code style="font-size:1.1rem;">${data.temporary_password}</code><br>
                    <small class="text-muted">Share this with them directly — they'll be asked to set a new password on first login.</small>`,
                confirmButtonText: 'Got it'
            });
        } else {
            Swal.fire({ icon: 'success', title: 'Done!', text: data.message, timer: 2500, showConfirmButton: false });
        }
    })
    .catch(() => Swal.fire('Error', 'Could not create teacher.', 'error'))
    .finally(() => {
        this.disabled = false;
        this.innerHTML = '<i class="fa fa-save mr-1"></i>Create Teacher';
    });
});

document.getElementById('searchTeacher').addEventListener('input', function () {
    const q = this.value.toLowerCase();
    document.querySelectorAll('.teacher-card').forEach(card => {
        const name = card.dataset.name || '';
        card.style.display = name.includes(q) ? 'flex' : 'none';
    });
});

// ════════════════════════════════════════════
// PASSWORD VISIBILITY TOGGLE
// ════════════════════════════════════════════

document.addEventListener('click', function(e) {
    const toggleBtn = e.target.closest('.toggle-password-visibility');
    if (!toggleBtn) return;
    
    const targetId = toggleBtn.dataset.target;
    const input = document.getElementById(targetId);
    if (!input) return;
    
    // Toggle password visibility
    if (input.type === 'password') {
        input.type = 'text';
        toggleBtn.classList.add('showing');
        toggleBtn.querySelector('i').className = 'fa fa-eye-slash';
    } else {
        input.type = 'password';
        toggleBtn.classList.remove('showing');
        toggleBtn.querySelector('i').className = 'fa fa-eye';
    }
});

// Also handle Enter key in password fields to toggle
document.addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
        const target = e.target;
        if (target.id === 'atPassword' || target.id === 'atPasswordConfirm') {
            e.preventDefault();
            const toggleBtn = document.querySelector(`.toggle-password-visibility[data-target="${target.id}"]`);
            if (toggleBtn) {
                toggleBtn.click();
            }
        }
    }
});
</script>
@endsection