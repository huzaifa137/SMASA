@extends('layouts-side-bar.master')
@php use App\Support\AdminAccess; @endphp

@section('css')
    @include('admin-rights._styles')
@endsection

@section('content')
    @include('admin-rights._nav', ['arpActive' => 'users'])

    <div class="arp-card">
        <div class="arp-toolbar">
            <form method="GET" class="d-flex gap-2">
                <input name="q" value="{{ $search }}" class="arp-input" placeholder="Search name, username, e-mail…">
                <button class="arp-btn">Search</button>
                @if ($search) <a class="arp-btn" href="{{ route('arp.users.index') }}">Clear</a> @endif
            </form>
            @if (AdminAccess::can('admin_rights.create_user'))
                <button class="arp-btn primary" id="btnNewUser"><i class="fa fa-user-plus"></i> New admin user</button>
            @endif
        </div>

        <div class="arp-wrap">
            <table class="arp-table">
                <thead><tr><th>User</th><th>E-mail</th><th>Access</th><th>Status</th><th style="text-align:right">Actions</th></tr></thead>
                <tbody>
                @foreach ($users as $u)
                    @php
                        $isMe = $u->id === $meId;
                        $locked = $u->is_super_admin && !$isSuper;
                        $displayName = $u->name ?? trim(($u->firstname ?? '') . ' ' . ($u->lastname ?? ''));
                    @endphp
                    <tr data-id="{{ $u->id }}" data-name="{{ $displayName }}" data-username="{{ $u->username }}" data-email="{{ $u->email }}"
                        data-role="{{ $u->admin_role_id }}" data-super="{{ $u->is_super_admin ? 1 : 0 }}" data-active="{{ (int) $u->is_active !== 0 ? 1 : 0 }}">
                        <td><strong>{{ $displayName ?: $u->username }}</strong> @if ($isMe)<span class="arp-badge none">you</span>@endif
                            <div class="arp-muted">{{ $u->username }}</div></td>
                        <td>{{ $u->email }}</td>
                        <td>
                            @if ($u->is_super_admin) <span class="arp-badge super"><i class="fa fa-crown"></i> Super Administrator</span>
                            @elseif ($u->adminRole) <span class="arp-badge role">{{ $u->adminRole->name }}</span>@unless ($u->adminRole->is_active) <span class="arp-badge off">role off</span>@endunless
                            @else <span class="arp-badge none">No role</span> @endif
                        </td>
                        <td><span class="arp-badge {{ (int) $u->is_active !== 0 ? 'on' : 'off' }}">{{ (int) $u->is_active !== 0 ? 'Active' : 'Deactivated' }}</span></td>
                        <td style="text-align:right;white-space:nowrap">
                            @if ($locked)
                                <span class="arp-muted">Managed by Super Administrators</span>
                            @else
                                @if (AdminAccess::can('admin_rights.edit_user')) <button class="arp-btn a-edit">Edit</button> @endif
                                @if (AdminAccess::can('admin_rights.assign_role') && !$u->is_super_admin && !($isMe && !$isSuper)) <button class="arp-btn a-role">Role</button> @endif
                                @if (AdminAccess::can('admin_rights.reset_password') && !$isMe) <button class="arp-btn a-pass">Reset password</button> @endif
                                @if (AdminAccess::can('admin_rights.toggle_status') && !$isMe) <button class="arp-btn a-status">{{ (int) $u->is_active !== 0 ? 'Deactivate' : 'Activate' }}</button> @endif
                                @if ($isSuper) <button class="arp-btn a-super">{{ $u->is_super_admin ? 'Remove super admin' : 'Make super admin' }}</button> @endif
                                @if (AdminAccess::can('admin_rights.delete_user') && !$isMe) <button class="arp-btn danger a-del"><i class="fa fa-trash"></i></button> @endif
                            @endif
                        </td>
                    </tr>
                @endforeach
                @if ($users->isEmpty()) <tr><td colspan="5" class="arp-muted">No admin users found.</td></tr> @endif
                </tbody>
            </table>
        </div>
        <p class="arp-muted mt-3 mb-0"><i class="fa fa-shield-alt"></i> {{ $activeSupers }} active Super Administrator(s). The last one can never be demoted, deactivated or deleted.</p>
    </div></div></div></div>
@endsection

@section('js')
    @include('admin-rights._scripts')
    <script>
    const U = @json(url('/admin-rights/users'));
    const ROLES = @json($roles->map(fn ($r) => ['id' => $r->id, 'name' => $r->name])->values());
    const IS_SUPER = @json($isSuper);

    const roleOptions = (cur) => '<option value="">— No role (dashboard only) —</option>' +
        ROLES.map(r => `<option value="${r.id}" ${String(cur) === String(r.id) ? 'selected' : ''}>${arpEsc(r.name)}</option>`).join('');

    const userForm = (u, creating) => `
        <div class="arp-field"><label>Full name</label><input id="uf-name" value="${arpEsc(u.name || '')}"></div>
        <div class="arp-field"><label>Username</label><input id="uf-username" value="${arpEsc(u.username || '')}"></div>
        <div class="arp-field"><label>E-mail</label><input id="uf-email" type="email" value="${arpEsc(u.email || '')}"></div>
        ${creating ? `<div class="arp-field"><label>Password <span class="arp-muted">(leave empty to generate one)</span></label><input id="uf-pass" type="text" autocomplete="off"></div>
        <div class="arp-field"><label>Role</label><select id="uf-role">${roleOptions('')}</select></div>
        ${IS_SUPER ? '<div class="arp-field"><label><input type="checkbox" id="uf-super" style="width:auto"> Super Administrator (every right, no role)</label></div>' : ''}` : ''}`;

    const readUser = (creating) => ({
        name: document.getElementById('uf-name').value, username: document.getElementById('uf-username').value,
        email: document.getElementById('uf-email').value,
        ...(creating ? { password: document.getElementById('uf-pass').value || null, admin_role_id: document.getElementById('uf-role').value || null,
                         is_super_admin: !!document.getElementById('uf-super')?.checked } : {}),
    });

    document.getElementById('btnNewUser')?.addEventListener('click', async () => {
        const f = await Swal.fire({ title: 'New admin user', html: userForm({}, true), showCancelButton: true, confirmButtonText: 'Create', preConfirm: () => readUser(true) });
        if (!f.isConfirmed) return;
        const r = await arpAct(U, 'POST', f.value);
        if (!r.ok) return;
        if (r.data.temporary_password) await arpShowPassword(r.data.temporary_password, 'Admin user created');
        location.reload();
    });

    document.querySelectorAll('tr[data-id]').forEach(tr => {
        const id = tr.dataset.id, name = tr.dataset.username;
        tr.querySelector('.a-edit')?.addEventListener('click', async () => {
            const f = await Swal.fire({ title: 'Edit ' + name, html: userForm({ name: tr.dataset.name, username: tr.dataset.username, email: tr.dataset.email }, false),
                showCancelButton: true, confirmButtonText: 'Save', preConfirm: () => readUser(false) });
            if (!f.isConfirmed) return;
            if ((await arpAct(U + '/' + id, 'PUT', f.value)).ok) location.reload();
        });
        tr.querySelector('.a-role')?.addEventListener('click', async () => {
            const f = await Swal.fire({ title: 'Role for ' + name, html: `<div class="arp-field"><select id="uf-role">${roleOptions(tr.dataset.role)}</select></div>
                <p class="arp-muted">You can only assign roles within your own rights.</p>`, showCancelButton: true, confirmButtonText: 'Save',
                preConfirm: () => ({ admin_role_id: document.getElementById('uf-role').value || null }) });
            if (!f.isConfirmed) return;
            if ((await arpAct(U + '/' + id + '/role', 'POST', f.value)).ok) location.reload();
        });
        tr.querySelector('.a-pass')?.addEventListener('click', async () => {
            const f = await Swal.fire({ title: 'Reset password — ' + name, html: '<div class="arp-field"><label>New password <span class="arp-muted">(empty = generate)</span></label><input id="uf-pass" autocomplete="off"></div>',
                showCancelButton: true, confirmButtonText: 'Reset', preConfirm: () => ({ password: document.getElementById('uf-pass').value || null }) });
            if (!f.isConfirmed) return;
            const r = await arpAct(U + '/' + id + '/password', 'POST', f.value);
            if (!r.ok) return;
            if (r.data.temporary_password) await arpShowPassword(r.data.temporary_password, 'Password reset');
            else Swal.fire({ icon: 'success', title: 'Password updated', timer: 1500, showConfirmButton: false });
        });
        tr.querySelector('.a-status')?.addEventListener('click', async () => {
            const on = tr.dataset.active === '1';
            const c = await Swal.fire({ icon: 'question', title: (on ? 'Deactivate ' : 'Activate ') + name + '?', text: on ? 'They are signed out on their next request.' : '', showCancelButton: true });
            if (c.isConfirmed && (await arpAct(U + '/' + id + '/status', 'POST')).ok) location.reload();
        });
        tr.querySelector('.a-super')?.addEventListener('click', async () => {
            const is = tr.dataset.super === '1';
            const c = await Swal.fire({ icon: 'warning', title: (is ? 'Remove Super Administrator from ' : 'Make Super Administrator: ') + name + '?',
                text: is ? 'They keep their account but lose unrestricted access until a role is assigned.' : 'Super Administrators hold every right, including future ones.', showCancelButton: true });
            if (c.isConfirmed && (await arpAct(U + '/' + id + '/super-admin', 'POST')).ok) location.reload();
        });
        tr.querySelector('.a-del')?.addEventListener('click', async () => {
            const c = await Swal.fire({ icon: 'warning', title: 'Delete ' + name + '?', text: 'This cannot be undone. Deactivating is usually safer.', showCancelButton: true, confirmButtonColor: '#dc2626', confirmButtonText: 'Delete' });
            if (c.isConfirmed && (await arpAct(U + '/' + id, 'DELETE')).ok) location.reload();
        });
    });
    </script>
@endsection
