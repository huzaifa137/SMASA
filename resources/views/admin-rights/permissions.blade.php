@extends('layouts-side-bar.master')
@php use App\Support\AdminAccess; @endphp

@section('css')
    @include('admin-rights._styles')
@endsection

@section('content')
    @include('admin-rights._nav', ['arpActive' => 'permissions'])

    @if ($roles->isEmpty())
        <div class="arp-card"><p class="arp-muted mb-0">Create a role first on the <a href="{{ route('arp.roles.index') }}">Roles</a> page.</p></div>
    @else
        @php $canEdit = AdminAccess::can('admin_rights.assign_permissions'); @endphp

        <div class="arp-card">
            <div class="arp-toolbar">
                <div>
                    <label class="arp-muted mb-1 d-block">Role</label>
                    <select id="roleSelect" class="arp-input">
                        @foreach ($roles as $role)
                            <option value="{{ $role->id }}" @selected($role->id == $selectedRoleId)>{{ $role->name }}{{ $role->is_active ? '' : ' (off)' }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <input id="permSearch" class="arp-input" placeholder="Filter permissions…" style="min-width:200px">
                    @if ($canEdit)
                        <button class="arp-btn" id="btnAll">Select all I can grant</button>
                        <button class="arp-btn" id="btnNone">Clear</button>
                    @endif
                </div>
            </div>
            @unless ($isSuper)
                <p class="arp-muted mb-0"><i class="fa fa-info-circle"></i> Greyed-out permissions are above your own rights: you can neither add nor remove them.</p>
            @endunless
        </div>

        <div class="arp-mod-grid" id="matrix">
            @foreach ($catalogue as $moduleKey => $module)
                <div class="arp-mod" data-module="{{ $moduleKey }}">
                    <header>
                        <strong><i class="{{ $module['icon'] }}"></i> {{ $module['name'] }}</strong>
                        @if ($canEdit)<label class="arp-muted mb-0" style="cursor:pointer"><input type="checkbox" class="mod-all"> all</label>@endif
                    </header>
                    <div class="desc">{{ $module['description'] }}</div>
                    @foreach ($module['permissions'] as $action => $label)
                        @php $key = $moduleKey . '.' . $action; $held = $isSuper || in_array($key, $mine, true); @endphp
                        <label class="arp-perm {{ $held ? '' : 'locked' }}" data-label="{{ strtolower($label) }}">
                            <input type="checkbox" class="perm" value="{{ $key }}" data-held="{{ $held ? 1 : 0 }}" {{ ($canEdit && $held) ? '' : 'disabled' }}>
                            <span>{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            @endforeach
        </div>

        @if ($canEdit)
            <div class="arp-savebar">
                <span class="arp-muted"><b id="selCount">0</b> permission(s) selected <span id="dirty" class="arp-dirty" style="display:none">· unsaved changes</span></span>
                <button class="arp-btn primary" id="btnSave"><i class="fa fa-save"></i> Save permissions</button>
            </div>
        @endif
    @endif

    </div></div>
@endsection

@section('js')
    @include('admin-rights._scripts')
    <script>
    const BASE = @json(url('/admin-rights/permissions'));
    const sel = document.getElementById('roleSelect');
    const boxes = () => [...document.querySelectorAll('.perm')];
    let saved = '';
    const state = () => boxes().filter(b => b.checked).map(b => b.value).sort().join('|');

    function refresh() {
        document.getElementById('selCount') && (document.getElementById('selCount').textContent = boxes().filter(b => b.checked).length);
        const d = document.getElementById('dirty'); if (d) d.style.display = state() !== saved ? 'inline' : 'none';
        document.querySelectorAll('.arp-mod').forEach(m => {
            const own = [...m.querySelectorAll('.perm:not(:disabled)')];
            const all = m.querySelector('.mod-all');
            if (all) all.checked = own.length > 0 && own.every(b => b.checked);
        });
    }

    async function loadRole() {
        const r = await arpRequest(BASE + '/' + sel.value);
        if (!r.ok) { Swal.fire({ icon: 'error', title: 'Could not load', text: arpErrorText(r) }); return; }
        const on = new Set(r.data.permissions);
        boxes().forEach(b => b.checked = on.has(b.value));
        saved = state(); refresh();
    }

    if (sel) {
        sel.addEventListener('change', async () => {
            if (state() !== saved) {
                const c = await Swal.fire({ icon: 'question', title: 'Discard unsaved changes?', showCancelButton: true, confirmButtonText: 'Discard' });
                if (!c.isConfirmed) { sel.value = sel.dataset.cur; return; }
            }
            sel.dataset.cur = sel.value; history.replaceState(null, '', '?role=' + sel.value); loadRole();
        });
        sel.dataset.cur = sel.value;
        loadRole();
    }

    document.addEventListener('change', e => {
        if (e.target.classList.contains('mod-all')) {
            e.target.closest('.arp-mod').querySelectorAll('.perm:not(:disabled)').forEach(b => b.checked = e.target.checked);
        }
        refresh();
    });
    document.getElementById('btnAll')?.addEventListener('click', () => { boxes().filter(b => !b.disabled).forEach(b => b.checked = true); refresh(); });
    document.getElementById('btnNone')?.addEventListener('click', () => { boxes().filter(b => !b.disabled).forEach(b => b.checked = false); refresh(); });

    document.getElementById('permSearch')?.addEventListener('input', e => {
        const q = e.target.value.toLowerCase().trim();
        document.querySelectorAll('.arp-perm').forEach(p => p.style.display = !q || p.dataset.label.includes(q) ? '' : 'none');
    });

    document.getElementById('btnSave')?.addEventListener('click', async () => {
        const r = await arpAct(BASE + '/' + sel.value, 'POST', { permissions: boxes().filter(b => b.checked).map(b => b.value) });
        if (!r.ok) return;
        saved = state(); refresh();
        Swal.fire({ icon: 'success', title: 'Saved', text: r.data.added + ' added, ' + r.data.removed + ' removed.', timer: 1800, showConfirmButton: false });
    });

    window.addEventListener('beforeunload', e => { if (document.getElementById('btnSave') && state() !== saved) { e.preventDefault(); e.returnValue = ''; } });
    </script>
@endsection
