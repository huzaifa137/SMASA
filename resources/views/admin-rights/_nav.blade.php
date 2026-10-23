@php
    use App\Support\AdminAccess;
    $arpActive = $arpActive ?? '';
    $arpTabs = [
        ['dashboard', 'Overview', route('arp.dashboard'), 'admin_rights.view_overview'],
        ['roles', 'Roles', route('arp.roles.index'), 'admin_rights.view_roles'],
        ['permissions', 'Permission Matrix', route('arp.permissions.index'), 'admin_rights.view_permissions'],
        ['users', 'Admin Users', route('arp.users.index'), 'admin_rights.view_users'],
        ['audit', 'Audit Log', route('arp.audit.index'), 'admin_rights.view_audit'],
    ];
@endphp
<div class="arp-hero mt-5">
    <h3><i class="fa fa-user-shield"></i> Admin Rights &amp; Privileges</h3>
    <p>Decide who can do what in the Admin Portal. Changes apply on the user's next request.</p>
    <span class="arp-me"><i class="fa fa-id-badge"></i> You are signed in as <b>{{ AdminAccess::roleName() }}</b></span>
</div>
<div class="arp-tabs">
    @foreach ($arpTabs as [$key, $label, $url, $perm])
        @if (AdminAccess::can($perm))
            <a href="{{ $url }}" class="{{ $arpActive === $key ? 'active' : '' }}">{{ $label }}</a>
        @endif
    @endforeach
</div>