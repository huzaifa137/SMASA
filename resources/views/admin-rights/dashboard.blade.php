@extends('layouts-side-bar.master')

@section('css')
    @include('admin-rights._styles')
@endsection

@section('content')
    @include('admin-rights._nav', ['arpActive' => 'dashboard'])

    <div class="arp-stats">
        <div class="arp-stat"><b>{{ $stats['super_admins'] }}</b><span>Super Administrators</span></div>
        <div class="arp-stat"><b>{{ $stats['admins'] }}</b><span>Admin users</span></div>
        <div class="arp-stat"><b>{{ $stats['roles'] }}</b><span>Admin roles</span></div>
        <div class="arp-stat"><b>{{ $stats['permissions'] }}</b><span>Permissions in catalogue</span></div>
        <div class="arp-stat {{ $stats['no_role'] ? 'warn' : '' }}"><b>{{ $stats['no_role'] }}</b><span>Admins with no role (dashboard only)</span></div>
        <div class="arp-stat {{ $stats['inactive'] ? 'warn' : '' }}"><b>{{ $stats['inactive'] }}</b><span>Deactivated admins</span></div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="arp-card">
                <h5>Roles in use</h5>
                @forelse ($roles as $role)
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <div>
                            <strong>{{ $role->name }}</strong>
                            @unless ($role->is_active) <span class="arp-badge off">Off</span> @endunless
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
            <div class="arp-card">
                <h5>Recent activity</h5>
                @forelse ($recent as $log)
                    <div class="py-2 border-bottom">
                        <div style="font-size:14px">{{ $log->description }}</div>
                        <div class="arp-muted">{{ optional($log->actor)->username ?? 'system / console' }} · {{ $log->created_at?->diffForHumans() }}</div>
                    </div>
                @empty
                    <p class="arp-muted mb-0">Nothing recorded yet.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="arp-card">
        <h5>What can be controlled</h5>
        <div class="arp-mod-grid">
            @foreach ($catalogue as $key => $module)
                <div class="arp-mod">
                    <header><strong><i class="{{ $module['icon'] }}"></i> {{ $module['name'] }}</strong>
                        <span class="arp-badge role">{{ count($module['permissions']) }}</span></header>
                    <div class="desc">{{ $module['description'] }}</div>
                </div>
            @endforeach
        </div>
    </div>
    </div>
        </div>
    </div>
@endsection
