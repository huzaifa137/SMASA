{{-- resources/views/user-rights/admin/schools-index.blade.php --}}
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
            --red: #dc2626;
            --sky: #0284c7;
            --slate: #64748b;
            --border: #dde3f7;
            --bg: #eef1fb;
            --bg2: #f4f6fd;
            --white: #ffffff;
            --r: 12px;
        }

        .urp-page {
            background: linear-gradient(160deg, #e8ecf8 0%, #eef1fb 40%, #f0f4ff 100%);
            min-height: 100vh;
            padding: 1.5rem;
            border-radius: var(--r);
        }

        .urp-topbar-accent {
            height: 4px;
            background: linear-gradient(90deg, var(--indigo) 0%, #5b5fef 50%, var(--teal) 100%);
            border-radius: var(--r) var(--r) 0 0;
        }

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
            max-width: 560px;
        }

        .urp-search {
            display: flex;
            align-items: center;
            gap: .5rem;
        }

        .urp-search input {
            border: 1.5px solid var(--border);
            border-radius: 9px;
            padding: .55rem .9rem;
            font-size: .85rem;
            min-width: 230px;
        }

        .urp-search input:focus {
            outline: none;
            border-color: var(--indigo3);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, .15);
        }

        .urp-search button {
            border: none;
            background: linear-gradient(135deg, var(--indigo), var(--indigo3));
            color: #fff;
            border-radius: 9px;
            padding: .55rem 1.1rem;
            font-size: .85rem;
            font-weight: 600;
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

        .urp-metric::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            border-radius: var(--r) 0 0 var(--r);
        }

        .m-indigo::before { background: var(--indigo); }
        .m-teal::before { background: var(--teal); }
        .m-amber::before { background: var(--amber); }
        .m-red::before { background: var(--red); }

        .metric-icon {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            margin-bottom: 1rem;
        }

        .m-indigo .metric-icon { background: #eef2ff; color: var(--indigo); }
        .m-teal .metric-icon { background: #f0fdfa; color: var(--teal); }
        .m-amber .metric-icon { background: #fff7ed; color: var(--amber); }
        .m-red .metric-icon { background: #fef2f2; color: var(--red); }

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

        /* ── School cards grid ── */
        .school-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.1rem;
        }

        .school-card {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: var(--r);
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(44, 41, 202, .05);
            transition: transform .18s, box-shadow .18s, border-color .18s;
            display: flex;
            flex-direction: column;
            animation: fadeIn .3s ease backwards;
        }

        .school-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 24px rgba(44, 41, 202, .14);
            border-color: #c7d2fe;
        }

        .school-card.at-risk {
            border-color: #fecaca;
        }

        .school-card.no-staff {
            border-color: #bfdbfe;
        }

        .school-card-risk-ribbon {
            background: linear-gradient(90deg, #fef2f2, #fff7ed);
            color: #b91c1c;
            font-size: .7rem;
            font-weight: 700;
            padding: .4rem 1.1rem;
            border-bottom: 1px solid #fecaca;
            display: flex;
            align-items: center;
            gap: .4rem;
        }

        .school-card-risk-ribbon.no-staff-ribbon {
            background: linear-gradient(90deg, #eff6ff, #f5f3ff);
            color: #1e3a8a;
            border-bottom: 1px solid #bfdbfe;
        }

        .school-card-body {
            padding: 1.2rem 1.3rem;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .school-card-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: .6rem;
        }

        .school-avatar {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--indigo), var(--indigo3));
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 1rem;
            flex-shrink: 0;
        }

        .school-name {
            font-size: .95rem;
            font-weight: 700;
            color: var(--navy);
            line-height: 1.3;
        }

        .school-code {
            font-size: .72rem;
            color: var(--slate);
            margin-top: .1rem;
        }

        .status-pill {
            font-size: .65rem;
            font-weight: 700;
            border-radius: 20px;
            padding: .2rem .6rem;
            white-space: nowrap;
            text-transform: uppercase;
            letter-spacing: .03em;
        }

        .status-active { background: #dcfce7; color: #15803d; }
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-locked { background: #fef2f2; color: #b91c1c; }
        .status-other { background: #f1f5f9; color: #475569; }

        .school-stats {
            display: flex;
            gap: .7rem;
            margin-top: 1rem;
            flex-wrap: wrap;
        }

        .school-stat {
            flex: 1;
            min-width: 70px;
            background: var(--bg2);
            border: 1px solid var(--border);
            border-radius: 9px;
            padding: .55rem .5rem;
            text-align: center;
        }

        .school-stat .ss-val {
            font-size: 1.1rem;
            font-weight: 800;
            color: var(--navy);
        }

        .school-stat .ss-label {
            font-size: .62rem;
            color: var(--slate);
            text-transform: uppercase;
            letter-spacing: .05em;
            margin-top: .1rem;
        }

        .school-stat.warn .ss-val { color: var(--amber); }
        .school-stat.danger .ss-val { color: var(--red); }

        .school-card-footer {
            margin-top: 1.1rem;
        }

        .btn-manage {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .5rem;
            background: linear-gradient(135deg, var(--indigo), var(--indigo3));
            color: #fff;
            border-radius: 9px;
            padding: .6rem 1rem;
            font-size: .82rem;
            font-weight: 600;
            text-decoration: none;
            transition: all .18s;
            width: 100%;
        }

        .btn-manage:hover {
            color: #fff;
            text-decoration: none;
            box-shadow: 0 4px 14px rgba(44, 41, 202, .3);
            transform: translateY(-1px);
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(8px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: var(--slate);
            background: var(--white);
            border-radius: var(--r);
            border: 1px solid var(--border);
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            display: block;
            opacity: .25;
            color: var(--indigo);
        }

        @media (max-width: 1100px) {
            .school-grid { grid-template-columns: repeat(2, 1fr); }
        }

        @media (max-width: 900px) {
            .urp-metric-strip { grid-template-columns: repeat(2, 1fr); }
        }

        @media (max-width: 640px) {
            .school-grid { grid-template-columns: 1fr; }
            .urp-search input { min-width: 0; flex: 1; }
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

        {{-- ── Header ── --}}
        <div class="urp-topbar-accent"></div>
        <div class="urp-header">
            <div>
                <h4 class="urp-header-title">
                    <i class="fas fa-sitemap mr-2" style="color:var(--indigo);"></i>
                    Schools &amp; Roles
                </h4>
                <div class="urp-header-sub">
                    Every school's roles and permission status in one place. Edit, delete, or re-grant access
                    without touching the database — the supported fix if a teacher (or you) gets locked out.
                </div>
            </div>
            <form method="GET" action="{{ route('urp.admin.index') }}" class="urp-search">
                <input type="text" name="q" value="{{ $search }}" placeholder="Search school name or code...">
                <button type="submit"><i class="fa fa-search mr-1"></i>Search</button>
            </form>
        </div>

        {{-- ── Metrics ── --}}
        <div class="urp-metric-strip">
            <div class="urp-metric m-indigo">
                <div class="metric-icon"><i class="fa fa-school"></i></div>
                <div class="metric-value">{{ $totalSchools }}</div>
                <div class="metric-label">Schools</div>
            </div>
            <div class="urp-metric m-teal">
                <div class="metric-icon"><i class="fa fa-user-tag"></i></div>
                <div class="metric-value">{{ $totalRoles }}</div>
                <div class="metric-label">Roles Defined</div>
            </div>
            <div class="urp-metric m-amber">
                <div class="metric-icon"><i class="fa fa-user-clock"></i></div>
                <div class="metric-value">{{ $unassignedTotal }}</div>
                <div class="metric-label">Staff Without a Role</div>
            </div>
            <div class="urp-metric m-red">
                <div class="metric-icon"><i class="fa fa-triangle-exclamation"></i></div>
                <div class="metric-value">{{ $atRiskSchools }}</div>
                <div class="metric-label">Schools At Risk</div>
            </div>
        </div>

        {{-- ── School cards ── --}}
        @forelse($schools as $i => $school)
        @php
            $statusMap = [
                10 => ['label' => 'Active', 'class' => 'status-active'],
                1  => ['label' => 'Pending', 'class' => 'status-pending'],
                8  => ['label' => 'Locked', 'class' => 'status-locked'],
                9  => ['label' => 'Suspended', 'class' => 'status-other'],
                0  => ['label' => 'Banned', 'class' => 'status-locked'],
            ];
            $statusInfo = $statusMap[$school->school_status] ?? ['label' => 'Unknown', 'class' => 'status-other'];
        @endphp
        @if($loop->first)
        <div class="school-grid">
        @endif
            <div class="school-card {{ $school->teachers_count == 0 ? 'no-staff' : ($school->at_risk ? 'at-risk' : '') }}" style="animation-delay:{{ $i * 0.04 }}s;">
                @if($school->teachers_count == 0)
                    <div class="school-card-risk-ribbon no-staff-ribbon">
                        <i class="fa fa-user-slash"></i>
                        No staff — nobody can log in
                    </div>
                @elseif($school->at_risk)
                    <div class="school-card-risk-ribbon">
                        <i class="fa fa-triangle-exclamation"></i>
                        No role grants User Rights access
                    </div>
                @endif
                <div class="school-card-body">
                    <div class="school-card-top">
                        <div class="d-flex align-items-start" style="gap:.75rem;">
                            <div class="school-avatar">{{ strtoupper(substr($school->name, 0, 2)) }}</div>
                            <div>
                                <div class="school-name">{{ $school->name }}</div>
                                <div class="school-code">
                                    <i class="fa fa-hashtag" style="font-size:.6rem;"></i> {{ $school->registration_code }}
                                </div>
                            </div>
                        </div>
                        <span class="status-pill {{ $statusInfo['class'] }}">{{ $statusInfo['label'] }}</span>
                    </div>

                    <div class="school-stats">
                        <div class="school-stat">
                            <div class="ss-val">{{ $school->school_roles_count }}</div>
                            <div class="ss-label">Roles</div>
                        </div>
                        <div class="school-stat">
                            <div class="ss-val">{{ $school->teachers_count }}</div>
                            <div class="ss-label">Staff</div>
                        </div>
                        <div class="school-stat {{ $school->unassigned_teachers_count > 0 ? 'warn' : '' }}">
                            <div class="ss-val">{{ $school->unassigned_teachers_count }}</div>
                            <div class="ss-label">No Role</div>
                        </div>
                    </div>

                    <div class="school-card-footer">
                        <a href="{{ route('urp.admin.school.roles', $school->id) }}" class="btn-manage">
                            <i class="fa fa-user-shield"></i> Manage Roles
                        </a>
                    </div>
                </div>
            </div>
        @if($loop->last)
        </div>
        @endif
        @empty
            <div class="empty-state">
                <i class="fa fa-school"></i>
                <h5>No schools found</h5>
                <p style="font-size:.85rem;">Try a different search term.</p>
            </div>
        @endforelse
    </div>
    </div>
                </div>
            </div>
@endsection