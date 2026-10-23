@extends('layouts-side-bar.master')
@php use App\Support\AdminAccess; @endphp

@section('css')
    @include('admin-rights._styles')
@endsection

@section('content')
    @include('admin-rights._nav', ['arpActive' => 'users'])

    <style>
/* ============================================================
   USERS PAGE ENHANCEMENTS
   ============================================================ */

.arp-users-card {
    overflow: hidden;
    position: relative;
}

/* ---------- Toolbar / Search ---------- */
.arp-users-toolbar {
    gap: 12px;
    flex-wrap: wrap;
    padding-bottom: 8px;
}

.arp-search-form {
    flex: 1 1 320px;
    max-width: 480px;
}

.arp-input-wrap {
    position: relative;
    flex: 1;
}

.arp-input-icon {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: #9aa3b2;
    font-size: 14px;
    pointer-events: none;
    transition: color .2s;
}

.arp-input-search {
    padding-left: 38px !important;
    padding-right: 38px !important;
    transition: box-shadow .25s, border-color .25s, background .25s;
}

.arp-input-search:focus {
    border-color: #2C29CA;
    box-shadow: 0 0 0 4px rgba(44, 41, 202, .12);
    background: #fff;
}

.arp-input-wrap:focus-within .arp-input-icon {
    color: #2C29CA;
}

.arp-input-clear {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    width: 22px;
    height: 22px;
    display: grid;
    place-items: center;
    border-radius: 50%;
    color: #6b7280;
    font-size: 11px;
    background: #eef1f6;
    transition: background .2s, color .2s, transform .2s;
}

.arp-input-clear:hover {
    background: #dc2626;
    color: #fff;
    transform: translateY(-50%) rotate(90deg);
}

/* ---------- Buttons ---------- */
.arp-btn {
    position: relative;
    overflow: hidden;
    transition: transform .15s, box-shadow .2s, background .2s, color .2s;
}

.arp-btn:active { transform: scale(.97); }

.arp-btn-search {
    background: #2C29CA;
    color: #fff;
    border-color: transparent;
}
.arp-btn-search:hover {
    background: #2320a8;
    box-shadow: 0 4px 14px rgba(44, 41, 202, .35);
}

.arp-btn-ghost {
    background: transparent;
    color: #6b7280;
    border: 1px dashed #cfd6e4;
}
.arp-btn-ghost:hover {
    border-color: #2C29CA;
    color: #2C29CA;
    background: rgba(44, 41, 202, .06);
}

.arp-btn-new {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 9px 18px;
    font-weight: 600;
    background: linear-gradient(135deg, #2C29CA, #5b4bff);
    color: #fff;
    border: none;
    box-shadow: 0 4px 14px rgba(44, 41, 202, .25);
}
.arp-btn-new:hover {
    box-shadow: 0 6px 22px rgba(44, 41, 202, .45);
    transform: translateY(-1px);
}
.arp-btn-new i { transition: transform .25s; }
.arp-btn-new:hover i { transform: rotate(15deg) scale(1.1); }

/* Shine sweep */
.arp-btn-shine {
    position: absolute;
    top: 0; left: -100%;
    width: 60%; height: 100%;
    background: linear-gradient(100deg, transparent, rgba(255,255,255,.45), transparent);
    transform: skewX(-20deg);
    transition: left .55s;
    pointer-events: none;
}
.arp-btn-new:hover .arp-btn-shine { left: 130%; }

/* ---------- Stats strip ---------- */
.arp-stats-strip {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    margin: 14px 0 6px;
}

.arp-stat {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 16px;
    border-radius: 12px;
    background: #f6f8fc;
    border: 1px solid #e6ebf4;
    flex: 1 1 140px;
    transition: transform .2s, box-shadow .2s, border-color .2s;
}
.arp-stat:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(28, 43, 82, .08);
    border-color: #cfd9ec;
}

.arp-stat-icon {
    width: 36px; height: 36px;
    display: grid; place-items: center;
    border-radius: 10px;
    background: #e6e5fb;
    color: #2C29CA;
    font-size: 15px;
    flex-shrink: 0;
}
.arp-stat-icon.super { background: #fff4d6; color: #d99a00; }
.arp-stat-icon.on    { background: #e3f7ea; color: #1a9d54; }

.arp-stat-value { font-size: 18px; font-weight: 700; color: #1c2b52; line-height: 1.1; }
.arp-stat-label { font-size: 11px; text-transform: uppercase; letter-spacing: .04em; color: #8a94a6; }

/* ---------- Table header ---------- */
.arp-table-users thead th {
    background: #2C29CA !important;
    color: #ffffff !important;
    font-weight: 600;
    letter-spacing: .02em;
}
.arp-table-users thead th:first-child { border-top-left-radius: 10px; }
.arp-table-users thead th:last-child  { border-top-right-radius: 10px; }

/* ---------- Table rows ---------- */
.arp-table-users tbody tr.arp-row {
    transition: background .2s, box-shadow .2s, transform .2s;
    position: relative;
}
.arp-table-users tbody tr.arp-row:hover {
    background: #f8faff;
    box-shadow: inset 3px 0 0 #2C29CA;
}
.arp-table-users tbody tr.is-inactive { opacity: .82; }
.arp-table-users tbody tr.is-inactive:hover { opacity: 1; }
.arp-table-users tbody tr.is-me { background: rgba(44, 41, 202, .04); }

/* Fade-in stagger */
.arp-table-users tbody tr.arp-row {
    animation: arpRowIn .35s ease both;
}
.arp-table-users tbody tr.arp-row:nth-child(1) { animation-delay: .02s; }
.arp-table-users tbody tr.arp-row:nth-child(2) { animation-delay: .06s; }
.arp-table-users tbody tr.arp-row:nth-child(3) { animation-delay: .10s; }
.arp-table-users tbody tr.arp-row:nth-child(4) { animation-delay: .14s; }
.arp-table-users tbody tr.arp-row:nth-child(5) { animation-delay: .18s; }
.arp-table-users tbody tr.arp-row:nth-child(6) { animation-delay: .22s; }
.arp-table-users tbody tr.arp-row:nth-child(7) { animation-delay: .26s; }
.arp-table-users tbody tr.arp-row:nth-child(8) { animation-delay: .30s; }
@keyframes arpRowIn {
    from { opacity: 0; transform: translateY(8px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* ---------- User cell / avatar ---------- */
.arp-user-cell { display: flex; align-items: center; gap: 12px; }

.arp-avatar {
    width: 38px; height: 38px;
    border-radius: 50%;
    display: grid; place-items: center;
    font-weight: 700;
    font-size: 13px;
    color: #fff;
    background: linear-gradient(135deg, #6f6dff, #2C29CA);
    box-shadow: 0 2px 8px rgba(44, 41, 202, .3);
    flex-shrink: 0;
    transition: transform .2s, box-shadow .2s;
}
.arp-row:hover .arp-avatar {
    transform: scale(1.08);
    box-shadow: 0 4px 14px rgba(44, 41, 202, .45);
}
.arp-avatar.super {
    background: linear-gradient(135deg, #ffcf4d, #f0a500);
    box-shadow: 0 2px 8px rgba(240, 165, 0, .35);
}

.arp-user-meta { min-width: 0; }
.arp-user-name { font-weight: 600; color: #1c2b52; display: flex; align-items: center; gap: 6px; }
.arp-user-username { font-size: 12px; }
.arp-badge-you { font-size: 9px; padding: 1px 6px; }

/* ---------- Email link ---------- */
.arp-email-link {
    color: #4a5568;
    text-decoration: none;
    border-bottom: 1px dashed transparent;
    transition: color .2s, border-color .2s;
}
.arp-email-link:hover {
    color: #2C29CA;
    border-color: #2C29CA;
}

/* ---------- Status pill ---------- */
.arp-status-pill {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 4px 12px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 600;
}
.arp-status-pill.on  { background: #e3f7ea; color: #148a49; }
.arp-status-pill.off { background: #fdecec; color: #c53030; }

.arp-status-dot {
    width: 7px; height: 7px;
    border-radius: 50%;
    background: currentColor;
    position: relative;
}
.arp-status-pill.on .arp-status-dot::after {
    content: '';
    position: absolute;
    inset: -3px;
    border-radius: 50%;
    border: 1.5px solid currentColor;
    opacity: 0;
    animation: arpPulse 2s infinite;
}
@keyframes arpPulse {
    0%   { opacity: .6; transform: scale(.8); }
    70%  { opacity: 0;  transform: scale(1.8); }
    100% { opacity: 0;  transform: scale(1.8); }
}

/* ---------- Action buttons (distinct colors) ---------- */
.arp-action-group {
    display: inline-flex;
    gap: 6px;
    flex-wrap: wrap;
    justify-content: flex-end;
    opacity: .9;
    transition: opacity .2s;
}
.arp-row:hover .arp-action-group { opacity: 1; }

.arp-action-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 11px;
    border-radius: 8px;
    border: 1px solid transparent;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    line-height: 1;
    white-space: nowrap;
    transition: transform .15s, box-shadow .2s, filter .2s;
}
.arp-action-btn i { font-size: 11px; }

.arp-action-btn:hover {
    transform: translateY(-2px);
    filter: brightness(.94);
}
.arp-action-btn:active { transform: translateY(0) scale(.96); }

/* EDIT — blue */
.arp-action-btn.a-edit {
    background: #e8f0fe;
    color: #1a56c4;
    border-color: #cdddfb;
}
.arp-action-btn.a-edit:hover { box-shadow: 0 4px 12px rgba(26, 86, 196, .28); }

/* ROLE — purple */
.arp-action-btn.a-role {
    background: #f1e9fe;
    color: #7a2fd1;
    border-color: #e2d1fb;
}
.arp-action-btn.a-role:hover { box-shadow: 0 4px 12px rgba(122, 47, 209, .28); }

/* RESET PASSWORD — amber */
.arp-action-btn.a-pass {
    background: #fff3d6;
    color: #b17400;
    border-color: #ffe4a3;
}
.arp-action-btn.a-pass:hover { box-shadow: 0 4px 12px rgba(177, 116, 0, .28); }

/* STATUS (Deactivate/Activate) — teal/green */
.arp-action-btn.a-status {
    background: #e0f5f0;
    color: #0e7c66;
    border-color: #bce9de;
}
.arp-action-btn.a-status:hover { box-shadow: 0 4px 12px rgba(14, 124, 102, .28); }

/* SUPER ADMIN — gold */
.arp-action-btn.a-super {
    background: #fdf0d1;
    color: #9a6700;
    border-color: #f6dfa5;
}
.arp-action-btn.a-super:hover { box-shadow: 0 4px 12px rgba(154, 103, 0, .3); }

/* DELETE — red */
.arp-action-btn.danger {
    background: #fdeaea;
    color: #b91c1c;
    border-color: #f9c9c9;
}
.arp-action-btn.danger:hover { box-shadow: 0 4px 12px rgba(185, 28, 28, .28); }

.arp-locked {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    padding: 5px 10px;
    border-radius: 8px;
    background: #f6f8fc;
}

/* ---------- Empty state ---------- */
.arp-empty { padding: 40px 16px !important; }
.arp-empty-state {
    display: flex; flex-direction: column; align-items: center;
    gap: 10px; color: #8a94a6;
}
.arp-empty-state i { font-size: 34px; opacity: .5; }
.arp-empty-state p { margin: 0; }

/* ---------- Footnote ---------- */
.arp-footnote { font-size: 12px; display: flex; align-items: center; gap: 6px; }

/* ---------- SweetAlert inputs polish ---------- */
.swal2-popup .arp-input {
    transition: border-color .2s, box-shadow .2s;
}
.swal2-popup .arp-input:focus {
    border-color: #2C29CA;
    box-shadow: 0 0 0 4px rgba(44, 41, 202, .12);
    outline: none;
}
.swal2-popup .arp-field { margin-bottom: 12px; }
.swal2-popup .arp-field label { font-size: 12px; font-weight: 600; color: #4a5568; display: block; margin-bottom: 4px; }
.swal2-popup .arp-check-field label { display: flex; align-items: center; gap: 8px; font-weight: 500; }
.swal2-popup .arp-check-field input { width: auto; }

/* ---------- Responsive ---------- */
@media (max-width: 720px) {
    .arp-users-toolbar { flex-direction: column; align-items: stretch; }
    .arp-search-form { max-width: none; }
    .arp-stats-strip { flex-direction: column; }
    .arp-action-group { opacity: 1; }
    .arp-stat { flex: 1 1 100%; }
}
    </style>

    <div class="arp-card arp-users-card">
        {{-- Toolbar --}}
        <div class="arp-toolbar arp-users-toolbar">
            <form method="GET" class="arp-search-form d-flex gap-2" id="arpSearchForm">
                <div class="arp-input-wrap">
                    <i class="fa fa-search arp-input-icon"></i>
                    <input name="q" value="{{ $search }}" class="arp-input arp-input-search"
                           placeholder="Search name, username, e-mail…" autocomplete="off">
                    @if ($search)
                        <a class="arp-input-clear" href="{{ route('arp.users.index') }}" title="Clear search">
                            <i class="fa fa-times"></i>
                        </a>
                    @endif
                </div>
                <button class="arp-btn arp-btn-search">Search</button>
                @if ($search)
                    <a class="arp-btn arp-btn-ghost" href="{{ route('arp.users.index') }}">Clear</a>
                @endif
            </form>

            @if (AdminAccess::can('admin_rights.create_user'))
                <button class="arp-btn primary arp-btn-new" id="btnNewUser">
                    <i class="fa fa-user-plus"></i>
                    <span>New admin user</span>
                    <span class="arp-btn-shine"></span>
                </button>
            @endif
        </div>

        {{-- Stats strip --}}
        <div class="arp-stats-strip mb-3">
            <div class="arp-stat">
                <span class="arp-stat-icon"><i class="fa fa-users"></i></span>
                <div>
                    <div class="arp-stat-value">{{ $users->count() }}</div>
                    <div class="arp-stat-label">Total users</div>
                </div>
            </div>
            <div class="arp-stat">
                <span class="arp-stat-icon super"><i class="fa fa-crown"></i></span>
                <div>
                    <div class="arp-stat-value">{{ $activeSupers }}</div>
                    <div class="arp-stat-label">Super admins</div>
                </div>
            </div>
            <div class="arp-stat">
                <span class="arp-stat-icon on"><i class="fa fa-check-circle"></i></span>
                <div>
                    <div class="arp-stat-value">{{ $users->where('is_active', 1)->count() }}</div>
                    <div class="arp-stat-label">Active</div>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="arp-wrap arp-table-wrap">
            <table class="arp-table arp-table-users">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>E-mail</th>
                        <th>Access</th>
                        <th>Status</th>
                        <th style="text-align:right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($users as $u)
                    @php
                        $isMe = $u->id === $meId;
                        $locked = $u->is_super_admin && !$isSuper;
                        $displayName = $u->name ?? trim(($u->firstname ?? '') . ' ' . ($u->lastname ?? ''));
                        $initials = strtoupper(mb_substr($displayName ?: $u->username, 0, 1) . mb_substr(explode(' ', $displayName ?: $u->username)[1] ?? '', 0, 1));
                    @endphp
                    <tr data-id="{{ $u->id }}" data-name="{{ $displayName }}" data-username="{{ $u->username }}" data-email="{{ $u->email }}"
                        data-role="{{ $u->admin_role_id }}" data-super="{{ $u->is_super_admin ? 1 : 0 }}" data-active="{{ (int) $u->is_active !== 0 ? 1 : 0 }}"
                        class="arp-row {{ $isMe ? 'is-me' : '' }} {{ (int) $u->is_active !== 0 ? '' : 'is-inactive' }}">
                        <td>
                            <div class="arp-user-cell">
                                <div class="arp-avatar {{ $u->is_super_admin ? 'super' : '' }}">
                                    {{ $initials ?: '?' }}
                                </div>
                                <div class="arp-user-meta">
                                    <div class="arp-user-name">
                                        {{ $displayName ?: $u->username }}
                                        @if ($isMe)<span class="arp-badge none arp-badge-you">you</span>@endif
                                    </div>
                                    <div class="arp-muted arp-user-username">@ {{ $u->username }}</div>
                                </div>
                            </div>
                        </td>
                        <td><a href="mailto:{{ $u->email }}" class="arp-email-link">{{ $u->email }}</a></td>
                        <td>
                            @if ($u->is_super_admin)
                                <span class="arp-badge super"><i class="fa fa-crown"></i> Super Administrator</span>
                            @elseif ($u->adminRole)
                                <span class="arp-badge role">{{ $u->adminRole->name }}</span>
                                @unless ($u->adminRole->is_active)
                                    <span class="arp-badge off">role off</span>
                                @endunless
                            @else
                                <span class="arp-badge none">No role</span>
                            @endif
                        </td>
                        <td>
                            <span class="arp-status-pill {{ (int) $u->is_active !== 0 ? 'on' : 'off' }}">
                                <span class="arp-status-dot"></span>
                                {{ (int) $u->is_active !== 0 ? 'Active' : 'Deactivated' }}
                            </span>
                        </td>
                        <td style="text-align:right;white-space:nowrap">
                            @if ($locked)
                                <span class="arp-muted arp-locked"><i class="fa fa-lock"></i> Managed by Super Admins</span>
                            @else
                                <div class="arp-action-group">
                                    @if (AdminAccess::can('admin_rights.edit_user'))
                                        <button class="arp-action-btn a-edit"><i class="fa fa-pen"></i> Edit</button>
                                    @endif
                                    @if (AdminAccess::can('admin_rights.assign_role') && !$u->is_super_admin && !($isMe && !$isSuper))
                                        <button class="arp-action-btn a-role"><i class="fa fa-user-tag"></i> Role</button>
                                    @endif
                                    @if (AdminAccess::can('admin_rights.reset_password') && !$isMe)
                                        <button class="arp-action-btn a-pass"><i class="fa fa-key"></i> Reset Password</button>
                                    @endif
                                    @if (AdminAccess::can('admin_rights.toggle_status') && !$isMe)
                                        <button class="arp-action-btn a-status">
                                            <i class="fa {{ (int) $u->is_active !== 0 ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i>
                                            {{ (int) $u->is_active !== 0 ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    @endif
                                    @if ($isSuper)
                                        <button class="arp-action-btn a-super">
                                            <i class="fa fa-crown"></i>
                                            {{ $u->is_super_admin ? 'Remove Superadmin' : 'Make Superadmin' }}
                                        </button>
                                    @endif
                                    @if (AdminAccess::can('admin_rights.delete_user') && !$isMe)
                                        <button class="arp-action-btn danger a-del"><i class="fa fa-trash"></i> Delete</button>
                                    @endif
                                </div>
                            @endif
                        </td>
                    </tr>
                @endforeach
                @if ($users->isEmpty())
                    <tr>
                        <td colspan="5" class="arp-empty">
                            <div class="arp-empty-state">
                                <i class="fa fa-users-slash"></i>
                                <p>No admin users found{{ $search ? ' for "' . $search . '"' : '' }}.</p>
                                @if ($search) <a href="{{ route('arp.users.index') }}" class="arp-btn">Clear search</a> @endif
                            </div>
                        </td>
                    </tr>
                @endif
                </tbody>
            </table>
        </div>

        <p class="arp-muted mt-3 mb-0 arp-footnote">
            <i class="fa fa-shield-alt"></i>
            {{ $activeSupers }} active Super Administrator(s). The last one can never be demoted, deactivated or deleted.
        </p>
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
        <div class="arp-field"><label>Full name</label><input id="uf-name" class="arp-input" value="${arpEsc(u.name || '')}"></div>
        <div class="arp-field"><label>Username</label><input id="uf-username" class="arp-input" value="${arpEsc(u.username || '')}"></div>
        <div class="arp-field"><label>E-mail</label><input id="uf-email" class="arp-input" type="email" value="${arpEsc(u.email || '')}"></div>
        ${creating ? `<div class="arp-field"><label>Password <span class="arp-muted">(leave empty to generate one)</span></label><input id="uf-pass" class="arp-input" type="text" autocomplete="off"></div>
        <div class="arp-field"><label>Role</label><select id="uf-role" class="arp-input">${roleOptions('')}</select></div>
        ${IS_SUPER ? '<div class="arp-field arp-check-field"><label><input type="checkbox" id="uf-super"> <span>Super Administrator (every right, no role)</span></label></div>' : ''}` : ''}`;

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
            const f = await Swal.fire({ title: 'Role for ' + name, html: `<div class="arp-field"><select id="uf-role" class="arp-input">${roleOptions(tr.dataset.role)}</select></div>
                <p class="arp-muted">You can only assign roles within your own rights.</p>`, showCancelButton: true, confirmButtonText: 'Save',
                preConfirm: () => ({ admin_role_id: document.getElementById('uf-role').value || null }) });
            if (!f.isConfirmed) return;
            if ((await arpAct(U + '/' + id + '/role', 'POST', f.value)).ok) location.reload();
        });
        tr.querySelector('.a-pass')?.addEventListener('click', async () => {
            const f = await Swal.fire({ title: 'Reset password — ' + name, html: '<div class="arp-field"><label>New password <span class="arp-muted">(empty = generate)</span></label><input id="uf-pass" class="arp-input" autocomplete="off"></div>',
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