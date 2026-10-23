@extends('layouts-side-bar.master')
@php use App\Support\AdminAccess; @endphp

@section('css')
    @include('admin-rights._styles')
@endsection
<style>
    /* =================================================================
   ADMIN ROLES PAGE — palette: #5D42E8 / #5351E4 / #2C29CA
   ================================================================= */

/* ---------- Toolbar ---------- */
.arp-toolbar {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 16px;
    flex-wrap: wrap;
    padding-bottom: 16px;
    margin-bottom: 4px;
    border-bottom: 1px solid #f0eefb;
}

.arp-toolbar-head h5 {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: #2C29CA;
    font-weight: 600;
    margin-bottom: 4px;
}
.arp-toolbar-head h5 i { font-size: 13px; color: #5D42E8; }
.arp-toolbar-head .arp-muted {
    display: block;
    max-width: 640px;
    font-size: 12.5px;
    line-height: 1.5;
    color: #6b6b85;
}

.arp-toolbar-actions {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}


/* ---------- Table ---------- */
.arp-table-modern {
    border-collapse: separate;
    border-spacing: 0 6px;
    width: 100%;
}
.arp-table-modern thead th {
    font-size: 11.5px;
    text-transform: uppercase;
    letter-spacing: .06em;
    color: #8a86a8;
    font-weight: 600;
    padding: 8px 14px;
    border-bottom: 1px solid #f0eefb;
    background: transparent;
}

.arp-table-modern tbody tr {
    background: #ffffff;
    box-shadow: 0 1px 2px rgba(93, 66, 232, 0.05);
    transition: box-shadow .25s ease, transform .25s ease, background .25s ease;
}
.arp-table-modern tbody tr td {
    padding: 14px;
    border-top: 1px solid #f2f0fc;
    border-bottom: 1px solid #f2f0fc;
    vertical-align: middle;
    background: #ffffff;
    transition: background .22s ease;
}
.arp-table-modern tbody tr td:first-child {
    border-left: 1px solid #f2f0fc;
    border-radius: 10px 0 0 10px;
}
.arp-table-modern tbody tr td:last-child {
    border-right: 1px solid #f2f0fc;
    border-radius: 0 10px 10px 0;
}

.arp-table-modern tbody tr:hover td {
    background: #faf9ff;
    border-color: #e6e2fb;
}
.arp-table-modern tbody tr:hover {
    box-shadow: 0 6px 18px rgba(93, 66, 232, 0.10),
                0 2px 4px rgba(44, 41, 202, 0.05);
}


/* ---------- Role title cell ---------- */
.arp-cell-title {
    display: inline-flex;
    align-items: center;
    gap: 9px;
}
.arp-cell-title strong {
    color: #2b2740;
    font-weight: 600;
    font-size: 14px;
}
.arp-cell-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: linear-gradient(135deg, #5D42E8, #2C29CA);
    box-shadow: 0 0 0 3px rgba(93, 66, 232, 0.14);
}


/* ---------- Permission meter ---------- */
.arp-meter {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    min-width: 140px;
}
.arp-meter-bar {
    flex: 1;
    height: 6px;
    border-radius: 999px;
    background: #eeecfd;
    overflow: hidden;
    position: relative;
}
.arp-meter-bar span {
    display: block;
    height: 100%;
    border-radius: 999px;
    background: linear-gradient(90deg, #5D42E8, #2C29CA);
    box-shadow: 0 0 8px rgba(93, 66, 232, 0.35);
    transition: width .6s cubic-bezier(.2,.8,.2,1);
    animation: arpBarGrow .8s cubic-bezier(.2,.8,.2,1) both;
}
@keyframes arpBarGrow {
    from { transform: scaleX(0); transform-origin: left; }
    to   { transform: scaleX(1); transform-origin: left; }
}
.arp-meter-label {
    font-size: 12px;
    font-weight: 600;
    color: #5D42E8;
    white-space: nowrap;
}


/* ---------- Neutral badge ---------- */
.arp-badge.neutral {
    background: #f4f2fd;
    color: #4a4570;
    border: 1px solid #e6e2fb;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-weight: 600;
    padding: 3px 9px;
    border-radius: 999px;
    font-size: 11.5px;
}
.arp-badge.neutral i { font-size: 10px; opacity: .8; }


/* ---------- Status badges (modernized) ---------- */
.arp-badge.on {
    background: linear-gradient(135deg, #5D42E8, #5351E4);
    color: #ffffff;
    border: 1px solid #5351E4;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-weight: 600;
    padding: 3px 10px;
    border-radius: 999px;
    font-size: 11.5px;
    box-shadow: 0 4px 10px rgba(93, 66, 232, 0.22);
}
.arp-badge.off {
    background: #f4f2fd;
    color: #6b6b85;
    border: 1px solid #e2ddfa;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-weight: 600;
    padding: 3px 10px;
    border-radius: 999px;
    font-size: 11.5px;
}


/* ---------- Action buttons ---------- */
.arp-actions {
    display: inline-flex;
    gap: 6px;
    justify-content: flex-end;
}
.arp-btn.icon-btn {
    width: 34px;
    height: 34px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 9px;
    border: 1px solid #e6e2fb;
    background: #ffffff;
    color: #5D42E8;
    font-size: 13px;
    transition: all .22s cubic-bezier(.2,.8,.2,1);
    position: relative;
}
.arp-btn.icon-btn:hover {
    background: linear-gradient(135deg, #5D42E8, #5351E4);
    color: #ffffff;
    border-color: #5351E4;
    transform: translateY(-2px);
    box-shadow: 0 6px 14px rgba(93, 66, 232, 0.30);
}
.arp-btn.icon-btn.danger {
    color: #d32f5b;
    border-color: #f6dbe4;
}
.arp-btn.icon-btn.danger:hover {
    background: linear-gradient(135deg, #ef476f, #d32f5b);
    color: #ffffff;
    border-color: #d32f5b;
    box-shadow: 0 6px 14px rgba(211, 47, 91, 0.30);
}


/* ---------- Primary button (upgraded) ---------- */
.arp-btn.primary {
    background: linear-gradient(135deg, #5D42E8, #5351E4);
    border: 1px solid #5351E4;
    color: #ffffff;
    font-weight: 600;
    padding: 8px 16px;
    border-radius: 9px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    box-shadow: 0 4px 12px rgba(93, 66, 232, 0.25);
    transition: all .25s cubic-bezier(.2,.8,.2,1);
}
.arp-btn.primary:hover {
    background: linear-gradient(135deg, #5351E4, #2C29CA);
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(93, 66, 232, 0.35);
}
.arp-btn.primary:active { transform: translateY(0); }


/* ---------- Card chip (reused) ---------- */
.arp-card-chip {
    font-size: 11.5px;
    font-weight: 600;
    padding: 4px 11px;
    border-radius: 999px;
    color: #5D42E8;
    background: #eeecfd;
    border: 1px solid #d7d2fb;
}


/* ---------- Empty state ---------- */
.arp-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 12px;
    padding: 40px 20px;
    text-align: center;
    color: #6b6b85;
}
.arp-empty i {
    font-size: 32px;
    color: #c7c2f9;
    background: #f4f2fd;
    width: 64px;
    height: 64px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}


/* ---------- Staggered row entrance ---------- */
@keyframes arpRowIn {
    from { opacity: 0; transform: translateY(8px); }
    to   { opacity: 1; transform: translateY(0); }
}
.arp-table-modern tbody tr {
    animation: arpRowIn .45s cubic-bezier(.2,.8,.2,1) both;
}
.arp-table-modern tbody tr:nth-child(1)  { animation-delay: .02s; }
.arp-table-modern tbody tr:nth-child(2)  { animation-delay: .06s; }
.arp-table-modern tbody tr:nth-child(3)  { animation-delay: .10s; }
.arp-table-modern tbody tr:nth-child(4)  { animation-delay: .14s; }
.arp-table-modern tbody tr:nth-child(5)  { animation-delay: .18s; }
.arp-table-modern tbody tr:nth-child(6)  { animation-delay: .22s; }
.arp-table-modern tbody tr:nth-child(7)  { animation-delay: .26s; }
.arp-table-modern tbody tr:nth-child(8)  { animation-delay: .30s; }


/* ---------- Accessibility ---------- */
@media (prefers-reduced-motion: reduce) {
    .arp-table-modern tbody tr,
    .arp-meter-bar span,
    .arp-btn.icon-btn,
    .arp-btn.primary {
        animation: none !important;
        transition: none !important;
    }
    .arp-btn.icon-btn:hover,
    .arp-btn.primary:hover { transform: none; }
}
</style>
@section('content')
    @include('admin-rights._nav', ['arpActive' => 'roles'])

    <div class="arp-card arp-card-accent">
        <div class="arp-toolbar">
            <div class="arp-toolbar-head">
                <h5 class="mb-0"><i class="fa fa-layer-group"></i> Admin roles</h5>
                <span class="arp-muted">
                    A role is a named bundle of permissions. Super Administrators are not a role — they hold every right.
                </span>
            </div>

            <div class="arp-toolbar-actions">
                <span class="arp-card-chip">{{ $roles->count() }} role(s)</span>
                @if (AdminAccess::can('admin_rights.create_role'))
                    <button class="arp-btn primary" id="btnNewRole">
                        <i class="fa fa-plus"></i> New role
                    </button>
                @endif
            </div>
        </div>

        <div class="arp-wrap">
            <table class="arp-table arp-table-modern">
                <thead>
                    <tr>
                        <th>Role</th>
                        <th>Description</th>
                        <th>Permissions</th>
                        <th>Users</th>
                        <th>Status</th>
                        <th style="text-align:right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($roles as $role)
                    <tr data-id="{{ $role->id }}"
                        data-name="{{ $role->name }}"
                        data-description="{{ $role->description }}"
                        data-active="{{ $role->is_active ? 1 : 0 }}">

                        <td>
                            <div class="arp-cell-title">
                                <span class="arp-cell-dot"></span>
                                <strong>{{ $role->name }}</strong>
                            </div>
                        </td>

                        <td class="arp-muted">
                            {{ $role->description ?: '—' }}
                        </td>

                        <td>
                            <div class="arp-meter" title="{{ $role->permission_count }} of {{ $totalPermissions }} permissions">
                                <div class="arp-meter-bar">
                                    <span style="width: {{ $totalPermissions > 0 ? round(($role->permission_count / $totalPermissions) * 100) : 0 }}%"></span>
                                </div>
                                <span class="arp-meter-label">
                                    {{ $role->permission_count }} / {{ $totalPermissions }}
                                </span>
                            </div>
                        </td>

                        <td>
                            <span class="arp-badge neutral">
                                <i class="fa fa-user"></i> {{ $role->users_count }}
                            </span>
                        </td>

                        <td>
                            <span class="arp-badge {{ $role->is_active ? 'on' : 'off' }}">
                                <i class="fa {{ $role->is_active ? 'fa-check-circle' : 'fa-pause-circle' }}"></i>
                                {{ $role->is_active ? 'Active' : 'Off' }}
                            </span>
                        </td>

                        <td style="text-align:right;white-space:nowrap">
                            <div class="arp-actions">
                                @if (AdminAccess::can('admin_rights.view_permissions'))
                                    <a class="arp-btn icon-btn" href="{{ route('arp.permissions.index', ['role' => $role->id]) }}" title="Permissions">
                                        <i class="fa fa-key"></i>
                                    </a>
                                @endif
                                @if (AdminAccess::can('admin_rights.edit_role'))
                                    <button class="arp-btn icon-btn btn-edit" title="Edit">
                                        <i class="fa fa-pen"></i>
                                    </button>
                                @endif
                                @if (AdminAccess::can('admin_rights.create_role'))
                                    <button class="arp-btn icon-btn btn-copy" title="Duplicate">
                                        <i class="fa fa-copy"></i>
                                    </button>
                                @endif
                                @if (AdminAccess::can('admin_rights.delete_role'))
                                    <button class="arp-btn icon-btn danger btn-del" title="Delete">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <div class="arp-empty">
                                <i class="fa fa-folder-open"></i>
                                <div>No roles yet.</div>
                                @if (AdminAccess::can('admin_rights.create_role'))
                                    <button class="arp-btn primary" onclick="document.getElementById('btnNewRole')?.click()">
                                        <i class="fa fa-plus"></i> Create the first role
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
     </div>
    </div>
@endsection

@section('js')
    @include('admin-rights._scripts')
    <script>
    const ROLE_URL = @json(url('/admin-rights/roles'));

    function roleForm(r) {
        return `<div class="arp-field"><label>Role name</label><input id="rf-name" maxlength="100" value="${arpEsc(r.name || '')}"></div>
                <div class="arp-field"><label>Description</label><input id="rf-desc" maxlength="255" value="${arpEsc(r.description || '')}"></div>
                ${r.id ? `<div class="arp-field"><label><input type="checkbox" id="rf-active" ${r.active ? 'checked' : ''} style="width:auto"> Role is active (an inactive role grants nothing)</label></div>` : ''}`;
    }

    document.getElementById('btnNewRole')?.addEventListener('click', async () => {
        const f = await Swal.fire({ title: 'New role', html: roleForm({}), showCancelButton: true, confirmButtonText: 'Create',
            preConfirm: () => ({ name: document.getElementById('rf-name').value, description: document.getElementById('rf-desc').value }) });
        if (!f.isConfirmed) return;
        const r = await arpAct(ROLE_URL, 'POST', f.value);
        if (r.ok) window.location = @json(url('/admin-rights/permissions')) + '?role=' + r.data.id;
    });

    document.querySelectorAll('tr[data-id]').forEach(tr => {
        const id = tr.dataset.id;
        tr.querySelector('.btn-edit')?.addEventListener('click', async () => {
            const f = await Swal.fire({ title: 'Edit role', html: roleForm({ id, name: tr.dataset.name, description: tr.dataset.description, active: tr.dataset.active === '1' }),
                showCancelButton: true, confirmButtonText: 'Save',
                preConfirm: () => ({ name: document.getElementById('rf-name').value, description: document.getElementById('rf-desc').value, is_active: document.getElementById('rf-active').checked }) });
            if (!f.isConfirmed) return;
            const r = await arpAct(ROLE_URL + '/' + id, 'PUT', f.value);
            if (r.ok) location.reload();
        });
        tr.querySelector('.btn-copy')?.addEventListener('click', async () => {
            const f = await Swal.fire({ title: 'Duplicate "' + tr.dataset.name + '"', html: roleForm({ name: tr.dataset.name + ' (copy)' }),
                showCancelButton: true, confirmButtonText: 'Duplicate',
                preConfirm: () => ({ name: document.getElementById('rf-name').value, description: document.getElementById('rf-desc').value, copy_from: id }) });
            if (!f.isConfirmed) return;
            const r = await arpAct(ROLE_URL, 'POST', f.value);
            if (r.ok) location.reload();
        });
        tr.querySelector('.btn-del')?.addEventListener('click', async () => {
            const c = await Swal.fire({ icon: 'warning', title: 'Delete "' + tr.dataset.name + '"?', text: 'This cannot be undone.', showCancelButton: true, confirmButtonColor: '#dc2626', confirmButtonText: 'Delete' });
            if (!c.isConfirmed) return;
            const r = await arpAct(ROLE_URL + '/' + id, 'DELETE');
            if (r.ok) location.reload();
        });
    });
    </script>
@endsection