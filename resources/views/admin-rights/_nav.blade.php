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

<style>
    /* ============================ TABS / NAV ============================ */
.arp-tabs {
    display: grid;
    grid-template-columns: repeat(5, minmax(0, 1fr)); /* 5 across on desktop */
    gap: 10px;
    margin-bottom: 22px;
    padding: 10px;
    background: linear-gradient(145deg, #ffffff 0%, #f7f6ff 100%);
    border: 1px solid #e5e3fb;
    border-radius: 14px;
    box-shadow: 0 1px 2px rgba(93, 66, 232, 0.05);
}

.arp-tabs a {
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: 10px 14px;
    border-radius: 10px;
    font-size: 13.5px;
    font-weight: 600;
    color: #5D42E8;
    background: #eeecfd;
    border: 1px solid #d7d2fb;
    text-decoration: none;
    transition: background .22s ease, color .22s ease, transform .22s ease, box-shadow .22s ease;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.arp-tabs a:hover {
    background: #dcd6fb;
    transform: translateY(-1px);
    box-shadow: 0 4px 10px rgba(93, 66, 232, 0.15);
}

.arp-tabs a.active {
    background: linear-gradient(135deg, #5D42E8, #5351E4);
    color: #fff;
    border-color: #5D42E8;
    box-shadow: 0 6px 14px rgba(93, 66, 232, 0.28);
}

/* Tablet: 3 per row */
@media (max-width: 992px) {
    .arp-tabs {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
}

/* Phone: still 3 per row (as requested), each spanning its share */
@media (max-width: 576px) {
    .arp-tabs {
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 8px;
        padding: 8px;
    }

    .arp-tabs a {
        font-size: 12px;
        padding: 9px 6px;
        white-space: normal;   /* allow wrapping if label is long */
        line-height: 1.2;
    }
}

/* Very small phones: 2 per row so labels don't get crushed */
@media (max-width: 380px) {
    .arp-tabs {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}
</style>