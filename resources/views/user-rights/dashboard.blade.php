{{-- resources/views/user-rights/dashboard.blade.php --}}
<?php use App\Helpers\PermissionHelper; ?>
@extends('layouts-side-bar.master')

@section('css')
    <style>
        :root {
            --urp-primary: #4f46e5;
            --urp-dark: #1e1b4b;
            --urp-light: #eef2ff;
            --urp-accent: #7c3aed;
            --urp-success: #059669;
            --urp-warn: #d97706;
            --urp-danger: #dc2626;
            --radius: 14px;
        }

        .urp-hero {
            background: linear-gradient(135deg, var(--urp-dark) 0%, #312e81 60%, var(--urp-accent) 100%);
            border-radius: var(--radius);
            padding: 2.2rem 2rem 3rem;
            margin-bottom: -1.8rem;
            position: relative;
            overflow: hidden;
            color: #fff;
        }

        .urp-hero::before {
            content: '';
            position: absolute;
            top: -60px;
            right: -60px;
            width: 260px;
            height: 260px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(255, 255, 255, .08) 0%, transparent 70%);
        }

        .urp-hero h2 {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: .3rem;
        }

        .urp-hero p {
            opacity: .8;
            margin: 0;
        }

        .stat-card {
            border-radius: var(--radius);
            border: none;
            padding: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1.1rem;
            box-shadow: 0 2px 12px rgba(0, 0, 0, .07);
            transition: transform .2s, box-shadow .2s;
            position: relative;
            overflow: hidden;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 24px rgba(0, 0, 0, .12);
        }

        .stat-icon {
            width: 56px;
            height: 56px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            flex-shrink: 0;
        }

        .stat-card .value {
            font-size: 2rem;
            font-weight: 700;
            line-height: 1;
        }

        .stat-card .label {
            font-size: .8rem;
            text-transform: uppercase;
            letter-spacing: .06em;
            opacity: .75;
            margin-top: .2rem;
        }

        .role-card {
            border-radius: var(--radius);
            border: 1px solid #e5e7eb;
            padding: 1.2rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #fff;
            transition: border-color .2s, box-shadow .2s;
        }

        .role-card:hover {
            border-color: var(--urp-primary);
            box-shadow: 0 4px 16px rgba(79, 70, 229, .1);
        }

        .role-badge {
            background: var(--urp-light);
            color: var(--urp-primary);
            border-radius: 20px;
            padding: .25rem .75rem;
            font-size: .78rem;
            font-weight: 600;
        }

        .module-pill {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            background: #f3f4f6;
            border-radius: 8px;
            padding: .4rem .85rem;
            font-size: .82rem;
            font-weight: 500;
            color: #374151;
            border: 1px solid #e5e7eb;
        }

        .unassigned-warn {
            background: #fff7ed;
            border: 1px solid #fed7aa;
            border-radius: var(--radius);
            padding: 1rem 1.4rem;
            display: flex;
            align-items: center;
            gap: .9rem;
            color: #92400e;
        }

        .section-title {
            font-size: .7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .1em;
            color: #9ca3af;
            margin-bottom: .8rem;
        }

        /* Add to your existing <style> section */

        .urp-hero {
            /* Keep your existing styles */
            position: relative;
            overflow: hidden;
        }

        /* Animated floating circles/balls */
        .urp-hero .ball {
            position: absolute;
            border-radius: 50%;
            opacity: 0.15;
            pointer-events: none;
            animation: floatBall 20s infinite ease-in-out;
        }

        .ball-1 {
            width: 180px;
            height: 180px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.8) 0%, rgba(255, 255, 255, 0) 70%);
            top: -60px;
            right: -40px;
            animation-delay: 0s;
        }

        .ball-2 {
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, rgba(79, 70, 229, 0.4), rgba(124, 58, 237, 0.2));
            bottom: -50px;
            left: 5%;
            animation-delay: 2s;
            animation-duration: 25s;
        }

        .ball-3 {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.1);
            top: 50%;
            right: 15%;
            animation-delay: 5s;
            animation-duration: 18s;
        }

        .ball-4 {
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(124, 58, 237, 0.15), transparent);
            bottom: -120px;
            right: -80px;
            animation-delay: 1s;
            animation-duration: 30s;
        }

        .ball-5 {
            width: 60px;
            height: 60px;
            background: rgba(79, 70, 229, 0.3);
            top: 20%;
            left: 20%;
            animation-delay: 8s;
            animation-duration: 15s;
        }

        .ball-6 {
            width: 140px;
            height: 140px;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.05), rgba(124, 58, 237, 0.08));
            bottom: 30%;
            right: 25%;
            animation-delay: 3s;
            animation-duration: 22s;
        }

        /* Floating animation */
        @keyframes floatBall {

            0%,
            100% {
                transform: translateY(0px) translateX(0px) scale(1);
            }

            25% {
                transform: translateY(-20px) translateX(10px) scale(1.05);
            }

            50% {
                transform: translateY(15px) translateX(-10px) scale(0.95);
            }

            75% {
                transform: translateY(-10px) translateX(-15px) scale(1.02);
            }
        }

        /* Glowing orb effect */
        .urp-hero::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -30%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(124, 58, 237, 0.08) 0%, transparent 70%);
            pointer-events: none;
            animation: rotateGlow 40s linear infinite;
        }

        @keyframes rotateGlow {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        /* Decorative dots pattern */
        .urp-hero .dot-pattern {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 60px;
            background-image: radial-gradient(rgba(255, 255, 255, 0.15) 1px, transparent 1px);
            background-size: 20px 20px;
            pointer-events: none;
        }

        /* Pulsing ring */
        .urp-hero .pulse-ring {
            position: absolute;
            top: 15%;
            right: 8%;
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: rgba(124, 58, 237, 0.2);
            animation: pulseRing 3s infinite;
            pointer-events: none;
        }

        @keyframes pulseRing {
            0% {
                transform: scale(0.8);
                opacity: 0.5;
            }

            100% {
                transform: scale(1.5);
                opacity: 0;
            }
        }

        /* Shine effect */
        .urp-hero .shine {
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg,
                    transparent 30%,
                    rgba(255, 255, 255, 0.05) 50%,
                    transparent 70%);
            transform: rotate(45deg);
            animation: shineMove 12s infinite;
            pointer-events: none;
        }

        @keyframes shineMove {
            0% {
                transform: translateX(-100%) translateY(-100%) rotate(45deg);
            }

            100% {
                transform: translateX(100%) translateY(100%) rotate(45deg);
            }
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid px-3 py-3">

        {{-- ── HERO ── --}}
        <div class="urp-hero mb-4">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                <div>
                    <h2><i class="fa fa-shield-alt mr-2"></i>User Rights &amp; Privileges</h2>
                    <p>Define roles for this school, control module access and assign staff.</p>
                </div>
                <div>@include('user-rights._nav')</div>
            </div>
            {{-- Add inside .urp-hero, before the closing
        </div> --}}
        <div class="ball ball-1"></div>
        <div class="ball ball-2"></div>
        <div class="ball ball-3"></div>
        <div class="ball ball-4"></div>
        <div class="ball ball-5"></div>
        <div class="ball ball-6"></div>
        <div class="dot-pattern"></div>
        <div class="pulse-ring"></div>
        <div class="shine"></div>
    </div>

    {{-- ── FLASH ── --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fa fa-check-circle mr-2"></i>{{ session('success') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fa fa-exclamation-circle mr-2"></i>{{ session('error') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    {{-- ── STATS ROW ── --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="stat-card" style="background:linear-gradient(135deg,#4f46e5,#7c3aed);color:#fff;">
                <div class="stat-icon" style="background:rgba(255,255,255,.18);">
                    <i class="fa fa-user-tag"></i>
                </div>
                <div>
                    <div class="value">{{ $totalRoles }}</div>
                    <div class="label">Roles Created</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card" style="background:linear-gradient(135deg,#059669,#10b981);color:#fff;">
                <div class="stat-icon" style="background:rgba(255,255,255,.18);">
                    <i class="fa fa-users"></i>
                </div>
                <div>
                    <div class="value">{{ $totalTeachers }}</div>
                    <div class="label">Total Staff</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card" style="background:linear-gradient(135deg,#0ea5e9,#38bdf8);color:#fff;">
                <div class="stat-icon" style="background:rgba(255,255,255,.18);">
                    <i class="fa fa-user-check"></i>
                </div>
                <div>
                    <div class="value">{{ $assignedCount }}</div>
                    <div class="label">Roles Assigned</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card" style="background:linear-gradient(135deg,#d97706,#f59e0b);color:#fff;">
                <div class="stat-icon" style="background:rgba(255,255,255,.18);">
                    <i class="fa fa-user-clock"></i>
                </div>
                <div>
                    <div class="value">{{ $unassigned }}</div>
                    <div class="label">No Role Yet</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── UNASSIGNED WARN ── --}}
    @if($unassigned > 0)
        <div class="unassigned-warn mb-4">
            <i class="fa fa-exclamation-triangle fa-lg"></i>
            <div>
                <strong>{{ $unassigned }} staff member{{ $unassigned > 1 ? 's have' : ' has' }} no role assigned.</strong>
                They currently <strong>cannot access any module</strong>.
                <a href="{{ route('urp.assign.index') }}" class="ml-2 text-warning font-weight-bold">
                    Assign Roles &rarr;
                </a>
            </div>
        </div>
    @endif

    <div class="row g-3">
        {{-- ── ROLES LIST ── --}}
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm" style="border-radius:var(--radius);">
                <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center"
                    style="border-radius:var(--radius) var(--radius) 0 0;">
                    <div>
                        <div class="section-title mb-0">School Roles</div>
                        <span class="text-muted" style="font-size:.82rem;">{{ $totalRoles }} roles defined</span>
                    </div>
                    <a href="{{ route('urp.roles.index') }}" class="btn btn-sm btn-outline-primary">
                        <i class="fa fa-plus mr-1"></i> Manage
                    </a>
                </div>
                <div class="card-body p-3">
                    @forelse($roles as $role)
                        <div class="role-card mb-2">
                            <div>
                                <div class="font-weight-600" style="color:#1e1b4b;">
                                    <i class="fa fa-crown mr-1 text-warning" style="font-size:.85rem;"></i>
                                    {{ $role->name }}
                                </div>
                                @if($role->description)
                                    <div class="text-muted" style="font-size:.78rem;">{{ $role->description }}</div>
                                @endif
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="role-badge">
                                    <i class="fa fa-users mr-1"></i>{{ $role->teachers_count }} staff
                                </span>
                                <a href="{{ route('urp.permissions.index') }}?role={{ $role->id }}"
                                    class="btn btn-xs btn-outline-primary" title="Set Permissions">
                                    <i class="fa fa-shield-alt"></i>
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4 text-muted">
                            <i class="fa fa-user-tag fa-2x mb-2 d-block" style="opacity:.3;"></i>
                            No roles created yet.
                            <br><a href="{{ route('urp.roles.index') }}" class="btn btn-sm btn-primary mt-2">Create First
                                Role</a>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- ── MODULES OVERVIEW ── --}}
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm" style="border-radius:var(--radius);">
                <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center"
                    style="border-radius:var(--radius) var(--radius) 0 0;">
                    <div>
                        <div class="section-title mb-0">System Modules</div>
                        <span class="text-muted" style="font-size:.82rem;">{{ $modules->count() }} modules
                            available</span>
                    </div>
                    <a href="{{ route('urp.permissions.index') }}" class="btn btn-sm btn-outline-primary">
                        <i class="fa fa-sliders-h mr-1"></i> Set Permissions
                    </a>
                </div>
                <div class="card-body p-3">
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($modules as $mod)
                            <div class="module-pill">
                                <i class="{{ $mod->icon }}" style="color:var(--urp-primary);"></i>
                                {{ $mod->name }}
                                <span class="badge badge-secondary ml-1"
                                    style="font-size:.68rem;">{{ $mod->features_count }}</span>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-3 p-3 rounded" style="background:#f8f9fa;border:1px dashed #d1d5db;">
                        <div class="d-flex align-items-start gap-2">
                            <i class="fa fa-info-circle text-primary mt-1"></i>
                            <div style="font-size:.83rem;color:#6b7280;">
                                Go to <strong>Module Permissions</strong> to configure which roles can access each
                                module and which specific features within that module they can use.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── QUICK ACTIONS ── --}}
    <div class="row g-3 mt-1">
        <div class="col-md-4">
            <a href="{{ route('urp.roles.index') }}" class="card border-0 shadow-sm text-decoration-none"
                style="border-radius:var(--radius);">
                <div class="card-body d-flex align-items-center gap-3 p-3">
                    <div class="stat-icon"
                        style="background:var(--urp-light);color:var(--urp-primary);width:48px;height:48px;font-size:1.2rem;">
                        <i class="fa fa-user-tag"></i>
                    </div>
                    <div>
                        <div class="font-weight-600" style="color:#111;">Create &amp; Manage Roles</div>
                        <div class="text-muted" style="font-size:.78rem;">Define custom roles like Bursar, Secretary
                        </div>
                    </div>
                    <i class="fa fa-chevron-right ml-auto text-muted"></i>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="{{ route('urp.permissions.index') }}" class="card border-0 shadow-sm text-decoration-none"
                style="border-radius:var(--radius);">
                <div class="card-body d-flex align-items-center gap-3 p-3">
                    <div class="stat-icon"
                        style="background:#f0fdf4;color:#059669;width:48px;height:48px;font-size:1.2rem;">
                        <i class="fa fa-sliders-h"></i>
                    </div>
                    <div>
                        <div class="font-weight-600" style="color:#111;">Configure Permissions</div>
                        <div class="text-muted" style="font-size:.78rem;">Toggle modules &amp; features per role</div>
                    </div>
                    <i class="fa fa-chevron-right ml-auto text-muted"></i>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="{{ route('urp.assign.index') }}" class="card border-0 shadow-sm text-decoration-none"
                style="border-radius:var(--radius);">
                <div class="card-body d-flex align-items-center gap-3 p-3">
                    <div class="stat-icon"
                        style="background:#fff7ed;color:#d97706;width:48px;height:48px;font-size:1.2rem;">
                        <i class="fa fa-users-cog"></i>
                    </div>
                    <div>
                        <div class="font-weight-600" style="color:#111;">Assign Roles to Staff</div>
                        <div class="text-muted" style="font-size:.78rem;">Link each teacher to a role</div>
                    </div>
                    <i class="fa fa-chevron-right ml-auto text-muted"></i>
                </div>
            </a>
        </div>
    </div>

    </div>
@endsection