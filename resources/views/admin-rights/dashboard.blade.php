@extends('layouts-side-bar.master')

@section('css')
    @include('admin-rights._styles')
@endsection

<style>
    /* =================================================================
   PALETTE
   primary : #5D42E8
   accent  : #5351E4
   deep    : #2C29CA
   ================================================================= */

    /* ============================ STATS ============================ */
    .arp-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
        gap: 14px;
        margin-bottom: 22px;
    }

    .arp-stat {
        position: relative;
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 16px 18px;
        background: linear-gradient(145deg, #ffffff 0%, #f7f6ff 100%);
        border: 1px solid #e5e3fb;
        border-radius: 14px;
        overflow: hidden;
        transition: transform .28s cubic-bezier(.2, .8, .2, 1),
            box-shadow .28s ease,
            border-color .28s ease;
        box-shadow: 0 1px 2px rgba(93, 66, 232, 0.05);
    }

    /* Left accent bar */
    .arp-stat::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        bottom: 0;
        width: 4px;
        background: linear-gradient(180deg, #5D42E8, #2C29CA);
        opacity: .85;
    }

    .arp-stat:hover {
        transform: translateY(-3px);
        border-color: #c7c2f9;
        box-shadow: 0 10px 22px rgba(93, 66, 232, 0.14),
            0 2px 6px rgba(44, 41, 202, 0.08);
    }

    /* Icon chip */
    .arp-stat-icon {
        flex: 0 0 auto;
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 17px;
        color: #fff;
        background: linear-gradient(135deg, #5D42E8, #5351E4);
        box-shadow: 0 6px 14px rgba(93, 66, 232, 0.28);
        transition: transform .35s cubic-bezier(.34, 1.56, .64, 1);
    }

    .arp-stat:hover .arp-stat-icon {
        transform: rotate(-8deg) scale(1.08);
    }

    .arp-stat-body {
        display: flex;
        flex-direction: column;
        line-height: 1.2;
    }

    .arp-stat-body b {
        font-size: 22px;
        font-weight: 700;
        color: #2C29CA;
        letter-spacing: -.3px;
    }

    .arp-stat-body span {
        font-size: 12.5px;
        color: #6b6b85;
        margin-top: 2px;
    }

    /* Tone variations per stat */
    .arp-stat[data-tone="purple"] .arp-stat-icon {
        background: linear-gradient(135deg, #5D42E8, #5351E4);
    }

    .arp-stat[data-tone="indigo"] .arp-stat-icon {
        background: linear-gradient(135deg, #5351E4, #2C29CA);
    }

    .arp-stat[data-tone="violet"] .arp-stat-icon {
        background: linear-gradient(135deg, #6d5cf0, #5D42E8);
    }

    .arp-stat[data-tone="blue"] .arp-stat-icon {
        background: linear-gradient(135deg, #4a63f0, #2C29CA);
    }

    .arp-stat[data-tone="amber"] .arp-stat-icon {
        background: linear-gradient(135deg, #f59e0b, #d97706);
        box-shadow: 0 6px 14px rgba(217, 119, 6, 0.28);
    }

    .arp-stat[data-tone="rose"] .arp-stat-icon {
        background: linear-gradient(135deg, #ef476f, #d32f5b);
        box-shadow: 0 6px 14px rgba(211, 47, 91, 0.28);
    }

    /* Warn tint (kept from before) */
    .arp-stat.warn {
        background: linear-gradient(145deg, #fffdf6 0%, #fdf7ec 100%);
        border-color: #f3e3c2;
    }

    .arp-stat.warn::before {
        background: linear-gradient(180deg, #f59e0b, #d97706);
    }

    .arp-stat.warn .arp-stat-body b {
        color: #b45309;
    }


    /* ============================ CARDS ============================ */
    .arp-card-accent {
        position: relative;
        padding-top: 4px;
    }

    .arp-card-accent::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, #5D42E8, #5351E4, #2C29CA);
        border-radius: 3px 3px 0 0;
        opacity: .9;
    }

    .arp-card-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 12px;
    }

    .arp-card-head h5 {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin: 0;
        color: #2C29CA;
        font-weight: 600;
    }

    .arp-card-head h5 i {
        font-size: 13px;
        color: #5D42E8;
    }

    .arp-card-chip {
        font-size: 11.5px;
        font-weight: 600;
        padding: 3px 10px;
        border-radius: 999px;
        color: #5D42E8;
        background: #eeecfd;
        border: 1px solid #d7d2fb;
    }


    /* ============================ ROLE LIST ============================ */
    .arp-list-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        padding: 12px 4px;
        border-bottom: 1px solid #f0eefb;
        transition: background .22s ease, padding-left .22s ease;
    }

    .arp-list-row:last-child {
        border-bottom: 0;
    }

    .arp-list-row:hover {
        background: #f8f7ff;
        padding-left: 10px;
        border-radius: 8px;
    }

    .arp-list-main strong {
        color: #2b2740;
        font-weight: 600;
    }

    .arp-list-main .arp-muted {
        font-size: 12.5px;
    }


    /* ============================ ACTIVITY ============================ */
    .arp-activity-row {
        display: flex;
        gap: 12px;
        padding: 10px 4px;
        border-bottom: 1px solid #f0eefb;
        transition: background .22s ease;
    }

    .arp-activity-row:last-child {
        border-bottom: 0;
    }

    .arp-activity-row:hover {
        background: #f8f7ff;
        border-radius: 8px;
    }

    .arp-activity-dot {
        flex: 0 0 auto;
        width: 10px;
        height: 10px;
        margin-top: 6px;
        border-radius: 50%;
        background: linear-gradient(135deg, #5D42E8, #2C29CA);
        box-shadow: 0 0 0 3px rgba(93, 66, 232, 0.15);
    }

    .arp-activity-body {
        min-width: 0;
    }

    .arp-activity-desc {
        font-size: 14px;
        color: #2b2740;
        line-height: 1.45;
    }

    .arp-activity-body .arp-muted {
        font-size: 12px;
        margin-top: 2px;
    }


    /* ============================ MODULE GRID ============================ */
    .arp-mod-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
        gap: 16px;
        margin-top: 16px;
    }

    .arp-mod {
        position: relative;
        background: linear-gradient(145deg, #ffffff 0%, #f7f6ff 100%);
        border: 1px solid #e5e3fb;
        border-radius: 12px;
        padding: 18px 18px 16px;
        overflow: hidden;
        cursor: default;
        transition: transform .28s cubic-bezier(.2, .8, .2, 1),
            box-shadow .28s ease,
            border-color .28s ease;
        box-shadow: 0 1px 2px rgba(93, 66, 232, 0.04);
    }

    /* Blue accent bar that grows on hover */
    .arp-mod::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        height: 3px;
        width: 40px;
        background: linear-gradient(90deg, #5D42E8, #2C29CA);
        border-radius: 0 0 3px 0;
        transition: width .35s cubic-bezier(.2, .8, .2, 1);
    }

    /* Soft radial glow that fades in on hover */
    .arp-mod::after {
        content: "";
        position: absolute;
        top: -60px;
        right: -60px;
        width: 160px;
        height: 160px;
        background: radial-gradient(circle, rgba(93, 66, 232, 0.16) 0%, rgba(93, 66, 232, 0) 70%);
        opacity: 0;
        transition: opacity .35s ease;
        pointer-events: none;
    }

    .arp-mod:hover {
        transform: translateY(-4px);
        border-color: #b8affb;
        box-shadow: 0 10px 24px rgba(93, 66, 232, 0.16),
            0 2px 6px rgba(44, 41, 202, 0.08);
    }

    .arp-mod:hover::before {
        width: 100%;
    }

    .arp-mod:hover::after {
        opacity: 1;
    }

    /* Header */
    .arp-mod header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 10px;
        position: relative;
        z-index: 1;
    }

    .arp-mod header strong {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 14.5px;
        font-weight: 600;
        color: #2C29CA;
        letter-spacing: .1px;
    }

    .arp-mod header i {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 30px;
        height: 30px;
        border-radius: 8px;
        background: linear-gradient(135deg, #eeecfd, #dcd6fb);
        color: #5D42E8;
        font-size: 14px;
        transition: transform .35s cubic-bezier(.34, 1.56, .64, 1),
            background .35s ease,
            color .35s ease;
    }

    .arp-mod:hover header i {
        transform: rotate(-8deg) scale(1.12);
        background: linear-gradient(135deg, #5D42E8, #5351E4);
        color: #ffffff;
    }

    /* Badge */
    .arp-mod .arp-badge.role {
        background: #eeecfd;
        color: #5D42E8;
        border: 1px solid #d7d2fb;
        font-size: 11.5px;
        padding: 3px 9px;
        border-radius: 999px;
        font-weight: 600;
        transition: all .28s ease;
        white-space: nowrap;
    }

    .arp-mod:hover .arp-badge.role {
        background: #5D42E8;
        color: #ffffff;
        border-color: #5D42E8;
    }

    /* Description */
    .arp-mod .desc {
        font-size: 13px;
        line-height: 1.5;
        color: #5b5b6f;
        position: relative;
        z-index: 1;
    }

    /* Staggered entrance */
    @keyframes arpModIn {
        from {
            opacity: 0;
            transform: translateY(14px) scale(.97);
        }

        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    .arp-mod {
        animation: arpModIn .5s cubic-bezier(.2, .8, .2, 1) both;
    }

    .arp-mod:nth-child(1) {
        animation-delay: .02s;
    }

    .arp-mod:nth-child(2) {
        animation-delay: .06s;
    }

    .arp-mod:nth-child(3) {
        animation-delay: .10s;
    }

    .arp-mod:nth-child(4) {
        animation-delay: .14s;
    }

    .arp-mod:nth-child(5) {
        animation-delay: .18s;
    }

    .arp-mod:nth-child(6) {
        animation-delay: .22s;
    }

    .arp-mod:nth-child(7) {
        animation-delay: .26s;
    }

    .arp-mod:nth-child(8) {
        animation-delay: .30s;
    }

    .arp-mod:nth-child(9) {
        animation-delay: .34s;
    }

    .arp-mod:nth-child(10) {
        animation-delay: .38s;
    }

    .arp-mod:nth-child(11) {
        animation-delay: .42s;
    }

    .arp-mod:nth-child(12) {
        animation-delay: .46s;
    }

    /* ============================ A11Y ============================ */
    @media (prefers-reduced-motion: reduce) {

        .arp-stat,
        .arp-stat-icon,
        .arp-mod,
        .arp-mod::before,
        .arp-mod::after,
        .arp-mod header i,
        .arp-mod .arp-badge.role,
        .arp-list-row,
        .arp-activity-row {
            animation: none !important;
            transition: none !important;
        }
    }
</style>

@section('content')
    @include('admin-rights._nav', ['arpActive' => 'dashboard'])

    {{-- ============================ STATS ============================ --}}
    <div class="arp-stats">
        <div class="arp-stat" data-tone="purple">
            <div class="arp-stat-icon"><i class="fa fa-crown"></i></div>
            <div class="arp-stat-body">
                <b>{{ $stats['super_admins'] }}</b>
                <span>Super Administrators</span>
            </div>
        </div>

        <div class="arp-stat" data-tone="indigo">
            <div class="arp-stat-icon"><i class="fa fa-user-shield"></i></div>
            <div class="arp-stat-body">
                <b>{{ $stats['admins'] }}</b>
                <span>Admin users</span>
            </div>
        </div>

        <div class="arp-stat" data-tone="violet">
            <div class="arp-stat-icon"><i class="fa fa-users-cog"></i></div>
            <div class="arp-stat-body">
                <b>{{ $stats['roles'] }}</b>
                <span>Admin roles</span>
            </div>
        </div>

        <div class="arp-stat" data-tone="blue">
            <div class="arp-stat-icon"><i class="fa fa-key"></i></div>
            <div class="arp-stat-body">
                <b>{{ $stats['permissions'] }}</b>
                <span>Permissions in catalogue</span>
            </div>
        </div>

        <div class="arp-stat {{ $stats['no_role'] ? 'warn' : '' }}" data-tone="amber">
            <div class="arp-stat-icon"><i class="fa fa-exclamation-triangle"></i></div>
            <div class="arp-stat-body">
                <b>{{ $stats['no_role'] }}</b>
                <span>Admins with no role (dashboard only)</span>
            </div>
        </div>

        <div class="arp-stat {{ $stats['inactive'] ? 'warn' : '' }}" data-tone="rose">
            <div class="arp-stat-icon"><i class="fa fa-user-slash"></i></div>
            <div class="arp-stat-body">
                <b>{{ $stats['inactive'] }}</b>
                <span>Deactivated admins</span>
            </div>
        </div>
    </div>

    {{-- ============================ ROLES + ACTIVITY ============================ --}}
    <div class="row">
        <div class="col-lg-6">
            <div class="arp-card arp-card-accent">
                <div class="arp-card-head">
                    <h5><i class="fa fa-layer-group"></i> Roles in use</h5>
                    <span class="arp-card-chip">{{ $roles->count() }} total</span>
                </div>

                @forelse ($roles as $role)
                    <div class="arp-list-row">
                        <div class="arp-list-main">
                            <strong>{{ $role->name }}</strong>
                            @unless ($role->is_active)
                                <span class="arp-badge off">Off</span>
                            @endunless
                            <div class="arp-muted">{{ $role->permission_count }} permission(s)</div>
                        </div>
                        <span class="arp-badge role">{{ $role->users_count }} user(s)</span>
                    </div>
                @empty
                    <p class="arp-muted mb-0">No roles yet. Create one under <b>Roles</b>.</p>
                @endforelse
            </div>
        </div>

        <div class="col-lg-6">
            <div class="arp-card arp-card-accent">
                <div class="arp-card-head">
                    <h5><i class="fa fa-stream"></i> Recent activity</h5>
                    <span class="arp-card-chip">{{ $recent->count() }} events</span>
                </div>

                @forelse ($recent as $log)
                    <div class="arp-activity-row">
                        <div class="arp-activity-dot"></div>
                        <div class="arp-activity-body">
                            <div class="arp-activity-desc">{{ $log->description }}</div>
                            <div class="arp-muted">
                                {{ optional($log->actor)->username ?? 'system / console' }}
                                · {{ $log->created_at?->diffForHumans() }}
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="arp-muted mb-0">Nothing recorded yet.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- ============================ MODULES ============================ --}}
    <div class="arp-card">
        <h5>What can be controlled</h5>
        <div class="arp-mod-grid">
            @foreach ($catalogue as $key => $module)
                <div class="arp-mod">
                    <header>
                        <strong>
                            <i class="{{ $module['icon'] }}"></i>
                            {{ $module['name'] }}
                        </strong>
                        <span class="arp-badge role">{{ count($module['permissions']) }}</span>
                    </header>
                    <div class="desc">{{ $module['description'] }}</div>
                </div>
            @endforeach
        </div>
    </div>
     </div>
    </div>
@endsection