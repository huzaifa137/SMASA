{{-- resources/views/user-rights/dashboard.blade.php --}}
{{-- LAYOUT 2: EXECUTIVE GRID — Blue-tinted, polished, spaced --}}
<?php use App\Helpers\PermissionHelper; ?>
@extends('layouts-side-bar.master')

@section('css')
    <style>
        :root {
            --navy: #0f172a;
            --indigo: #2c29ca;
            --indigo2: #4338ca;
            --indigo3: #6366f1;
            --teal: #0d9488;
            --amber: #d97706;
            --sky: #0284c7;
            --slate: #64748b;
            --border: #dde3f7;
            --bg: #eef1fb;
            --bg2: #f4f6fd;
            --white: #ffffff;
            --r: 12px;
        }

        /* ── Page background ── */
        .urp-page {
            background: linear-gradient(160deg, #e8ecf8 0%, #eef1fb 40%, #f0f4ff 100%);
            min-height: 100vh;
            padding: 1.5rem;
            border-radius: var(--r);
        }

        /* ── Top accent bar ── */
        .urp-topbar-accent {
            height: 4px;
            background: linear-gradient(90deg, var(--indigo) 0%, #5b5fef 50%, var(--teal) 100%);
            border-radius: var(--r) var(--r) 0 0;
        }

        /* ── Header card ── */
        .urp-header {
            background: var(--white);
            border: 1px solid var(--border);
            border-top: none;
            border-radius: 0 0 var(--r) var(--r);
            padding: 1.5rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 1.75rem;
            box-shadow: 0 2px 12px rgba(44, 41, 202, .06);
        }

        .urp-header-title {
            font-size: 1.3rem;
            font-weight: 800;
            color: var(--navy);
            letter-spacing: -.025em;
            margin: 0;
        }

        .urp-header-sub {
            font-size: .8rem;
            color: var(--slate);
            margin-top: .2rem;
        }

        /* ── Metric strip ── */
        .urp-metric-strip {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 1.75rem;
        }

        .urp-metric {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: var(--r);
            padding: 1.4rem 1.5rem 1.2rem;
            position: relative;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(44, 41, 202, .06);
            transition: transform .2s, box-shadow .2s;
        }

        .urp-metric:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(44, 41, 202, .12);
        }

        /* colored left stripe */
        .urp-metric::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            border-radius: var(--r) 0 0 var(--r);
        }

        /* subtle background tint blob */
        .urp-metric::after {
            content: '';
            position: absolute;
            top: -20px;
            right: -20px;
            width: 80px;
            height: 80px;
            border-radius: 50%;
            opacity: .07;
        }

        .m-indigo::before {
            background: var(--indigo);
        }

        .m-teal::before {
            background: var(--teal);
        }

        .m-sky::before {
            background: var(--sky);
        }

        .m-amber::before {
            background: var(--amber);
        }

        .m-indigo::after {
            background: var(--indigo);
        }

        .m-teal::after {
            background: var(--teal);
        }

        .m-sky::after {
            background: var(--sky);
        }

        .m-amber::after {
            background: var(--amber);
        }

        .metric-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            margin-bottom: 1rem;
        }

        .metric-icon {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            flex-shrink: 0;
        }

        .m-indigo .metric-icon {
            background: #eef2ff;
            color: var(--indigo);
        }

        .m-teal .metric-icon {
            background: #f0fdfa;
            color: var(--teal);
        }

        .m-sky .metric-icon {
            background: #e0f2fe;
            color: var(--sky);
        }

        .m-amber .metric-icon {
            background: #fff7ed;
            color: var(--amber);
        }

        .metric-value {
            font-size: 2.4rem;
            font-weight: 800;
            color: var(--navy);
            line-height: 1;
            letter-spacing: -.04em;
        }

        .metric-label {
            font-size: .72rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: var(--slate);
            margin-top: .3rem;
        }

        /* ── Content grid ── */
        .urp-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            grid-template-rows: auto auto;
            gap: 1.25rem;
        }

        .g-roles {
            grid-column: 1 / 2;
            grid-row: 1 / 3;
        }

        .g-modules {
            grid-column: 2 / 4;
            grid-row: 1 / 2;
        }

        .g-actions {
            grid-column: 2 / 4;
            grid-row: 2 / 3;
        }

        /* ── Generic panel ── */
        .urp-panel {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: var(--r);
            overflow: hidden;
            height: 100%;
            display: flex;
            flex-direction: column;
            box-shadow: 0 2px 10px rgba(44, 41, 202, .05);
        }

        .urp-panel-head {
            padding: 1rem 1.4rem;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: linear-gradient(90deg, #f4f6fd, #f8faff);
        }

        .panel-label {
            font-size: .66rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .12em;
            color: #a5b4fc;
        }

        .panel-title {
            font-size: .92rem;
            font-weight: 700;
            color: var(--navy);
            margin-top: .1rem;
        }

        .urp-panel-body {
            padding: 1.1rem 1.4rem;
            flex: 1;
        }

        /* ── Role list ── */
        .role-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: .8rem 0;
            border-bottom: 1px solid #eef1f8;
        }

        .role-item:last-child {
            border-bottom: none;
        }

        .role-item:hover .role-avatar {
            background: var(--indigo);
            color: #fff;
        }

        .role-avatar {
            width: 36px;
            height: 36px;
            border-radius: 9px;
            background: #eef2ff;
            color: var(--indigo);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .82rem;
            font-weight: 700;
            flex-shrink: 0;
            margin-right: .85rem;
            transition: background .15s, color .15s;
        }

        .ri-name {
            font-weight: 600;
            font-size: .87rem;
            color: var(--navy);
        }

        .ri-desc {
            font-size: .72rem;
            color: #94a3b8;
            margin-top: .08rem;
        }

        .ri-chip {
            font-size: .7rem;
            font-weight: 600;
            background: #eef2ff;
            color: var(--indigo2);
            border-radius: 20px;
            padding: .22rem .7rem;
            white-space: nowrap;
            border: 1px solid #c7d2fe;
        }

        /* ── Modules grid ── */
        .mod-wrap {
            display: flex;
            flex-wrap: wrap;
            gap: .55rem;
        }

        .mod-tag {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            padding: .45rem .9rem;
            border-radius: 8px;
            background: var(--bg2);
            border: 1px solid var(--border);
            font-size: .79rem;
            color: #334155;
            font-weight: 500;
            transition: all .15s;
            cursor: default;
        }

        .mod-tag:hover {
            border-color: var(--indigo);
            color: var(--indigo);
            background: #eef2ff;
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(44, 41, 202, .1);
        }

        .mod-tag i {
            color: var(--indigo);
            font-size: .8rem;
        }

        .mod-count {
            background: #dde3f7;
            border-radius: 10px;
            padding: .05rem .42rem;
            font-size: .62rem;
            color: var(--indigo2);
            font-weight: 600;
        }

        /* ── Action cards ── */
        .action-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
        }

        .action-card {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            padding: 1.2rem 1.1rem;
            border-radius: var(--r);
            border: 1px solid var(--border);
            background: var(--bg2);
            text-decoration: none;
            transition: all .18s;
            position: relative;
            overflow: hidden;
        }

        .action-card::before {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 3px;
            opacity: 0;
            transition: opacity .18s;
        }

        .action-card.ac-blue::before {
            background: var(--indigo);
        }

        .action-card.ac-teal::before {
            background: var(--teal);
        }

        .action-card.ac-orange::before {
            background: var(--amber);
        }

        .action-card:hover {
            border-color: transparent;
            box-shadow: 0 4px 20px rgba(44, 41, 202, .14);
            background: var(--white);
            text-decoration: none;
            transform: translateY(-2px);
        }

        .action-card:hover::before {
            opacity: 1;
        }

        .ac-icon {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            margin-bottom: .85rem;
        }

        .ac-title {
            font-size: .86rem;
            font-weight: 700;
            color: var(--navy);
        }

        .ac-sub {
            font-size: .73rem;
            color: var(--slate);
            margin-top: .25rem;
            line-height: 1.45;
        }

        /* ── Warn strip ── */
        .urp-warn {
            display: flex;
            align-items: center;
            gap: .9rem;
            padding: .9rem 1.3rem;
            background: #fffbeb;
            border: 1px solid #fde68a;
            border-left: 4px solid var(--amber);
            border-radius: var(--r);
            font-size: .82rem;
            color: #78350f;
            margin-bottom: 1.75rem;
            box-shadow: 0 2px 8px rgba(217, 119, 6, .08);
        }

        /* ── Info tip ── */
        .urp-tip {
            margin-top: 1rem;
            padding: .85rem 1rem;
            background: #eef2ff;
            border: 1px solid #c7d2fe;
            border-radius: 9px;
            font-size: .77rem;
            color: var(--indigo2);
            display: flex;
            align-items: flex-start;
            gap: .6rem;
            line-height: 1.5;
        }

        .urp-tip i {
            margin-top: .1rem;
            flex-shrink: 0;
            color: var(--indigo3);
        }

        @media (max-width: 900px) {
            .urp-metric-strip {
                grid-template-columns: repeat(2, 1fr);
            }

            .urp-grid {
                grid-template-columns: 1fr;
            }

            .g-roles,
            .g-modules,
            .g-actions {
                grid-column: auto;
                grid-row: auto;
            }

            .action-row {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 560px) {
            .urp-metric-strip {
                grid-template-columns: 1fr 1fr;
                gap: .75rem;
            }

            .urp-page {
                padding: 1rem;
            }
        }
    </style>
@endsection

@section('content')
    <div class="urp-page">

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-3">
                <i class="fa fa-check-circle mr-2"></i>{{ session('success') }}
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show mb-3">
                <i class="fa fa-exclamation-circle mr-2"></i>{{ session('error') }}
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
        @endif

        {{-- ── Header ── --}}
        <div class="urp-topbar-accent"></div>
        <div class="urp-header">
            <div>
                <h4 class="urp-header-title">
                    <i class="fa fa-shield-alt mr-2" style="color:var(--indigo);"></i>
                    User Rights &amp; Privileges
                </h4>
                <div class="urp-header-sub">Access control for your school — define roles, set permissions, assign staff.
                </div>
            </div>
            <div>@include('user-rights._nav')</div>
        </div>

        {{-- ── Metrics ── --}}
        <div class="urp-metric-strip">
            <div class="urp-metric m-indigo">
                <div class="metric-top">
                    <div class="metric-icon"><i class="fa fa-user-tag"></i></div>
                </div>
                <div class="metric-value">{{ $totalRoles }}</div>
                <div class="metric-label">Roles Created</div>
            </div>
            <div class="urp-metric m-teal">
                <div class="metric-top">
                    <div class="metric-icon"><i class="fa fa-users"></i></div>
                </div>
                <div class="metric-value">{{ $totalTeachers }}</div>
                <div class="metric-label">Total Staff</div>
            </div>
            <div class="urp-metric m-sky">
                <div class="metric-top">
                    <div class="metric-icon"><i class="fa fa-user-check"></i></div>
                </div>
                <div class="metric-value">{{ $assignedCount }}</div>
                <div class="metric-label">Roles Assigned</div>
            </div>
            <div class="urp-metric m-amber">
                <div class="metric-top">
                    <div class="metric-icon"><i class="fa fa-user-clock"></i></div>
                </div>
                <div class="metric-value">{{ $unassigned }}</div>
                <div class="metric-label">No Role Yet</div>
            </div>
        </div>

        {{-- ── Warning ── --}}
        @if($unassigned > 0)
            <div class="urp-warn">
                <i class="fa fa-exclamation-triangle fa-lg"></i>
                <div>
                    <strong>{{ $unassigned }} staff {{ $unassigned > 1 ? 'members have' : 'member has' }} no role
                        assigned</strong>
                    and cannot access any module.
                    @if(PermissionHelper::canFeature('view_roles'))
                    <a href="{{ route('urp.assign.index') }}" class="font-weight-bold ml-2"
                        style="color:#92400e;text-decoration:underline;">
                        Assign now &rarr;
                    </a>
                    @endif
                </div>
            </div>
        @endif

        {{-- ── Content Grid ── --}}
        <div class="urp-grid">

            {{-- ROLES --}}
            <div class="g-roles">
                <div class="urp-panel">
                    <div class="urp-panel-head">
                        <div>
                            <div class="panel-label">Access Roles</div>
                            <div class="panel-title">{{ $totalRoles }} Defined Roles</div>
                        </div>
                        @if(PermissionHelper::canFeature('view_roles'))
                        <a href="{{ route('urp.roles.index') }}" class="btn btn-sm btn-outline-primary"
                            style="font-size:.73rem;border-color:#c7d2fe;color:var(--indigo);">
                            <i class="fa fa-plus mr-1"></i>Manage
                        </a>
                        @endif
                    </div>
                    <div class="urp-panel-body">
                        @forelse($roles as $role)
                            <div class="role-item">
                                <div class="d-flex align-items-center">
                                    <div class="role-avatar">{{ strtoupper(substr($role->name, 0, 1)) }}</div>
                                    <div>
                                        <div class="ri-name">{{ $role->name }}</div>
                                        @if($role->description)
                                            <div class="ri-desc">{{ Str::limit($role->description, 30) }}</div>
                                        @endif
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="ri-chip">{{ $role->teachers_count }} staff</span>
                                    @if(PermissionHelper::canFeature('view_permissions'))
                                    <a href="{{ route('urp.permissions.index') }}?role={{ $role->id }}"
                                        class="btn btn-xs btn-outline-secondary" title="Set Permissions"
                                        style="border-color:#dde3f7;">
                                        <i class="fa fa-shield-alt" style="font-size:.65rem;color:var(--indigo);"></i>
                                    </a>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-5 text-muted">
                                <i class="fa fa-user-tag fa-2x d-block mb-2" style="opacity:.2;color:var(--indigo);"></i>
                                No roles yet.
                                @if(PermissionHelper::canFeature('create_role'))
                                <br><a href="{{ route('urp.roles.index') }}" class="btn btn-sm btn-primary mt-2">Create First
                                    Role</a>
                                @endif
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- MODULES --}}
            <div class="g-modules">
                <div class="urp-panel">
                    <div class="urp-panel-head">
                        <div>
                            <div class="panel-label">System Modules</div>
                            <div class="panel-title">{{ $modules->count() }} modules available</div>
                        </div>
                        @if(PermissionHelper::canFeature('view_permissions'))
                        <a href="{{ route('urp.permissions.index') }}" class="btn btn-sm btn-outline-primary"
                            style="font-size:.73rem;border-color:#c7d2fe;color:var(--indigo);">
                            <i class="fa fa-sliders-h mr-1"></i>Permissions
                        </a>
                        @endif
                    </div>
                    <div class="urp-panel-body">
                        <div class="mod-wrap">
                            @foreach($modules as $mod)
                                <span class="mod-tag">
                                    <i class="{{ $mod->icon }}"></i>
                                    {{ $mod->name }}
                                    <span class="mod-count">{{ $mod->features_count }}</span>
                                </span>
                            @endforeach
                        </div>
                        <div class="urp-tip">
                            <i class="fa fa-info-circle"></i>
                            <span>Go to <strong>Module Permissions</strong> to configure which roles can access each module
                                and which specific features within it they can use.</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ACTIONS --}}
            <div class="g-actions">
                <div class="urp-panel">
                    <div class="urp-panel-head">
                        <div>
                            <div class="panel-label">Quick Actions</div>
                            <div class="panel-title">Common Tasks</div>
                        </div>
                    </div>
                    <div class="urp-panel-body">
                        <div class="action-row">
                            @if(PermissionHelper::canFeature('view_roles'))
                            <a href="{{ route('urp.roles.index') }}" class="action-card ac-blue">
                                <div class="ac-icon" style="background:#eef2ff;color:var(--indigo);">
                                    <i class="fa fa-user-tag"></i>
                                </div>
                                <div class="ac-title">Manage Roles</div>
                                <div class="ac-sub">Create and edit custom roles like Bursar or Secretary</div>
                            </a>
                            @endif
                            @if(PermissionHelper::canFeature('view_permissions'))
                            <a href="{{ route('urp.permissions.index') }}" class="action-card ac-teal">
                                <div class="ac-icon" style="background:#f0fdf4;color:var(--teal);">
                                    <i class="fa fa-sliders-h"></i>
                                </div>
                                <div class="ac-title">Permissions</div>
                                <div class="ac-sub">Toggle module &amp; feature access per role</div>
                            </a>
                            @endif
                            @if(PermissionHelper::canFeature('view_roles'))
                            <a href="{{ route('urp.assign.index') }}" class="action-card ac-orange">
                                <div class="ac-icon" style="background:#fff7ed;color:var(--amber);">
                                    <i class="fa fa-users-cog"></i>
                                </div>
                                <div class="ac-title">Assign Roles</div>
                                <div class="ac-sub">Link each staff member to their role</div>
                            </a>
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
@endsection