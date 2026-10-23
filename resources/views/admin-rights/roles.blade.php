@extends('layouts-side-bar.master')
@php use App\Support\AdminAccess; @endphp

@section('css')
    @include('admin-rights._styles')
@endsection

@section('content')
    @include('admin-rights._nav', ['arpActive' => 'roles'])

    <div class="arp-card">
        <div class="arp-toolbar">
            <div>
                <h5 class="mb-0">Admin roles</h5>
                <span class="arp-muted">A role is a named bundle of permissions. Super Administrators are not a role — they hold every right.</span>
            </div>
            @if (AdminAccess::can('admin_rights.create_role'))
                <button class="arp-btn primary" id="btnNewRole"><i class="fa fa-plus"></i> New role</button>
            @endif
        </div>

        <div class="arp-wrap">
            <table class="arp-table">
                <thead><tr><th>Role</th><th>Description</th><th>Permissions</th><th>Users</th><th>Status</th><th style="text-align:right">Actions</th></tr></thead>
                <tbody>
                @forelse ($roles as $role)
                    <tr data-id="{{ $role->id }}" data-name="{{ $role->name }}" data-description="{{ $role->description }}" data-active="{{ $role->is_active ? 1 : 0 }}">
                        <td><strong>{{ $role->name }}</strong></td>
                        <td class="arp-muted">{{ $role->description ?: '—' }}</td>
                        <td>{{ $role->permission_count }} / {{ $totalPermissions }}</td>
                        <td>{{ $role->users_count }}</td>
                        <td><span class="arp-badge {{ $role->is_active ? 'on' : 'off' }}">{{ $role->is_active ? 'Active' : 'Off' }}</span></td>
                        <td style="text-align:right;white-space:nowrap">
                            @if (AdminAccess::can('admin_rights.view_permissions'))
                                <a class="arp-btn" href="{{ route('arp.permissions.index', ['role' => $role->id]) }}"><i class="fa fa-key"></i> Permissions</a>
                            @endif
                            @if (AdminAccess::can('admin_rights.edit_role'))
                                <button class="arp-btn btn-edit"><i class="fa fa-pen"></i> Edit</button>
                            @endif
                            @if (AdminAccess::can('admin_rights.create_role'))
                                <button class="arp-btn btn-copy"><i class="fa fa-copy"></i> Duplicate</button>
                            @endif
                            @if (AdminAccess::can('admin_rights.delete_role'))
                                <button class="arp-btn danger btn-del"><i class="fa fa-trash"></i></button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="arp-muted">No roles yet.</td></tr>
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
