@extends('layouts-side-bar.master')
@php use App\Support\AdminAccess; @endphp

@section('css')
    @include('admin-rights._styles')
@endsection

@section('content')

<style>
    /* =================================================================
   PERMISSIONS PAGE — palette: #5D42E8 / #5351E4 / #2C29CA
   ================================================================= */

/* ---------- Control row ---------- */
.arp-control-row {
    display: flex;
    flex-wrap: wrap;
    gap: 16px;
    align-items: flex-end;
    padding-top: 8px;
}
.arp-control-group { display: flex; flex-direction: column; }
.arp-control-group.grow { flex: 1 1 240px; }

/* ---------- Search input ---------- */
.arp-search {
    position: relative;
    display: flex;
    align-items: center;
}
.arp-search i {
    position: absolute;
    left: 12px;
    font-size: 13px;
    color: #8a86a8;
    pointer-events: none;
}
.arp-search .arp-input {
    padding-left: 34px;
    width: 100%;
    min-width: 200px;
}

/* ---------- Info strip ---------- */
.arp-info-strip {
    margin-top: 16px;
    padding: 10px 14px;
    background: #f8f7ff;
    border: 1px solid #e6e2fb;
    border-left: 3px solid #5D42E8;
    border-radius: 8px;
    font-size: 12.5px;
    color: #4a4570;
    display: flex;
    align-items: center;
    gap: 8px;
}
.arp-info-strip i { color: #5D42E8; }


/* ---------- Permission list (inside .arp-mod) ---------- */
.arp-perm-list {
    display: flex;
    flex-direction: column;
    gap: 2px;
    margin-top: 10px;
    position: relative;
    z-index: 1;
}

.arp-perm {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 8px 10px;
    border-radius: 8px;
    cursor: pointer;
    transition: background .2s ease;
    user-select: none;
}
.arp-perm:hover {
    background: #f8f7ff;
}
.arp-perm-name {
    font-size: 13px;
    color: #2b2740;
    line-height: 1.4;
}
.arp-perm.locked {
    opacity: .55;
    cursor: not-allowed;
}
.arp-perm.locked:hover { background: transparent; }


/* =================================================================
   TOGGLE SWITCH
   ================================================================= */
.arp-toggle {
    position: relative;
    flex: 0 0 auto;
    display: inline-block;
    width: 38px;
    height: 22px;
}
.arp-toggle input {
    position: absolute;
    opacity: 0;
    width: 0;
    height: 0;
    pointer-events: none;
}
.arp-toggle-track {
    position: absolute;
    inset: 0;
    background: #e2ddfa;
    border-radius: 999px;
    transition: background .28s cubic-bezier(.2,.8,.2,1);
    box-shadow: inset 0 1px 2px rgba(93, 66, 232, 0.08);
}
.arp-toggle-thumb {
    position: absolute;
    top: 3px;
    left: 3px;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    background: #ffffff;
    box-shadow: 0 1px 3px rgba(44, 41, 202, 0.20);
    transition: transform .28s cubic-bezier(.34,1.56,.64,1),
                box-shadow .28s ease;
}

/* Checked state */
.arp-toggle input:checked + .arp-toggle-track {
    background: linear-gradient(135deg, #5D42E8, #2C29CA);
    box-shadow: 0 0 10px rgba(93, 66, 232, 0.35);
}
.arp-toggle input:checked + .arp-toggle-track .arp-toggle-thumb {
    transform: translateX(16px);
    box-shadow: 0 1px 4px rgba(44, 41, 202, 0.35);
}

/* Focus ring for keyboard users */
.arp-toggle input:focus-visible + .arp-toggle-track {
    outline: 2px solid #5D42E8;
    outline-offset: 2px;
}

/* Disabled / locked */
.arp-toggle input:disabled + .arp-toggle-track {
    background: #f0eefb;
    box-shadow: none;
}
.arp-toggle input:disabled + .arp-toggle-track .arp-toggle-thumb {
    background: #dcd6fb;
    box-shadow: none;
}

/* "all" module toggle (smaller) */
.arp-mod-toggle {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    cursor: pointer;
    font-size: 11.5px;
    color: #6b6b85;
    font-weight: 600;
    padding: 4px 8px;
    border-radius: 999px;
    background: #f8f7ff;
    border: 1px solid #e6e2fb;
    transition: all .22s ease;
}
.arp-mod-toggle:hover {
    background: #eeecfd;
    border-color: #d7d2fb;
    color: #5D42E8;
}
.arp-mod-toggle input { display: none; }
.arp-mod-toggle-track {
    position: relative;
    width: 28px;
    height: 16px;
    border-radius: 999px;
    background: #e2ddfa;
    transition: background .28s ease;
    flex: 0 0 auto;
}
.arp-mod-toggle-thumb {
    position: absolute;
    top: 2px;
    left: 2px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: #ffffff;
    box-shadow: 0 1px 2px rgba(44, 41, 202, 0.20);
    transition: transform .28s cubic-bezier(.34,1.56,.64,1);
}
.arp-mod-toggle input:checked + .arp-mod-toggle-track {
    background: linear-gradient(135deg, #5D42E8, #2C29CA);
}
.arp-mod-toggle input:checked + .arp-mod-toggle-track .arp-mod-toggle-thumb {
    transform: translateX(12px);
}
.arp-mod-toggle input:indeterminate + .arp-mod-toggle-track {
    background: linear-gradient(135deg, #8f7df0, #5D42E8);
}
.arp-mod-toggle input:indeterminate + .arp-mod-toggle-track .arp-mod-toggle-thumb {
    transform: translateX(6px);
}
.arp-mod-toggle-label {
    text-transform: uppercase;
    letter-spacing: .05em;
    font-size: 10.5px;
}


/* =================================================================
   SAVE BAR
   ================================================================= */
.arp-savebar {
    position: sticky;
    bottom: 16px;
    margin-top: 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding: 14px 20px;
    background: rgba(255, 255, 255, 0.92);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border: 1px solid #e6e2fb;
    border-radius: 14px;
    box-shadow: 0 10px 30px rgba(93, 66, 232, 0.14),
                0 2px 6px rgba(44, 41, 202, 0.06);
    z-index: 20;
}
.arp-savebar-info {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 13px;
    color: #4a4570;
    flex-wrap: wrap;
}
.arp-savebar-info i {
    color: #5D42E8;
    font-size: 12px;
}
.arp-savebar-info b { color: #2C29CA; }

.arp-dirty {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 3px 10px;
    border-radius: 999px;
    background: #fdf7ec;
    border: 1px solid #f3e3c2;
    color: #b45309;
    font-size: 11.5px;
    font-weight: 600;
    animation: arpDirtyPulse 1.8s ease-in-out infinite;
}
.arp-dirty i { color: #d97706; font-size: 8px; }

@keyframes arpDirtyPulse {
    0%, 100% { box-shadow: 0 0 0 0 rgba(217, 119, 6, 0); }
    50%      { box-shadow: 0 0 0 4px rgba(217, 119, 6, 0.10); }
}


/* =================================================================
   MODULE CARD REFINEMENTS (for this page)
   ================================================================= */
/* Add a nice scroll container feel with subtle spacing */
#matrix .arp-mod header { gap: 10px; }
#matrix .arp-mod .desc { margin-bottom: 4px; }


/* =================================================================
   ACCESSIBILITY
   ================================================================= */
@media (prefers-reduced-motion: reduce) {
    .arp-toggle-track,
    .arp-toggle-thumb,
    .arp-mod-toggle-track,
    .arp-mod-toggle-thumb,
    .arp-dirty {
        animation: none !important;
        transition: none !important;
    }
}
</style>
    @include('admin-rights._nav', ['arpActive' => 'permissions'])

    @if ($roles->isEmpty())
        <div class="arp-card">
            <div class="arp-empty">
                <i class="fa fa-layer-group"></i>
                <div>Create a role first on the <a href="{{ route('arp.roles.index') }}">Roles</a> page.</div>
                <a href="{{ route('arp.roles.index') }}" class="arp-btn primary">
                    <i class="fa fa-plus"></i> Go to Roles
                </a>
            </div>
        </div>
    @else
        @php $canEdit = AdminAccess::can('admin_rights.assign_permissions'); @endphp

        {{-- ==================== CONTROL BAR ==================== --}}
        <div class="arp-card arp-card-accent">
            <div class="arp-toolbar">
                <div class="arp-toolbar-head">
                    <h5 class="mb-0"><i class="fa fa-sliders-h"></i> Permission matrix</h5>
                    <span class="arp-muted">Toggle what this role can access. Changes are saved per role.</span>
                </div>

                <div class="arp-toolbar-actions">
                    <span class="arp-card-chip" id="roleChip">—</span>
                </div>
            </div>

            <div class="arp-control-row">
                <div class="arp-control-group">
                    <label class="arp-muted mb-1 d-block">Role</label>
                    <select id="roleSelect" class="arp-input">
                        @foreach ($roles as $role)
                            <option value="{{ $role->id }}" @selected($role->id == $selectedRoleId)>
                                {{ $role->name }}{{ $role->is_active ? '' : ' (off)' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="arp-control-group grow">
                    <label class="arp-muted mb-1 d-block">Filter</label>
                    <div class="arp-search">
                        <i class="fa fa-search"></i>
                        <input id="permSearch" class="arp-input" placeholder="Search permissions…">
                    </div>
                </div>

                @if ($canEdit)
                    <div class="arp-control-group">
                        <label class="arp-muted mb-1 d-block">&nbsp;</label>
                        <div class="d-flex gap-2 flex-wrap">
                            <button class="arp-btn" id="btnAll">
                                <i class="fa fa-check-double"></i> Select all
                            </button>
                            <button class="arp-btn" id="btnNone">
                                <i class="fa fa-eraser"></i> Clear
                            </button>
                        </div>
                    </div>
                @endif
            </div>

            @unless ($isSuper)
                <div class="arp-info-strip">
                    <i class="fa fa-info-circle"></i>
                    Greyed-out permissions are above your own rights: you can neither add nor remove them.
                </div>
            @endunless
        </div>

        {{-- ==================== MATRIX ==================== --}}
        <div class="arp-mod-grid" id="matrix">
            @foreach ($catalogue as $moduleKey => $module)
                <div class="arp-mod" data-module="{{ $moduleKey }}">
                    <header>
                        <strong>
                            <i class="{{ $module['icon'] }}"></i>
                            {{ $module['name'] }}
                        </strong>

                        @if ($canEdit)
                            <label class="arp-mod-toggle" title="Toggle all in this module">
                                <input type="checkbox" class="mod-all">
                                <span class="arp-mod-toggle-track"><span class="arp-mod-toggle-thumb"></span></span>
                                <span class="arp-mod-toggle-label">all</span>
                            </label>
                        @endif
                    </header>

                    <div class="desc">{{ $module['description'] }}</div>

                    <div class="arp-perm-list">
                        @foreach ($module['permissions'] as $action => $label)
                            @php
                                $key = $moduleKey . '.' . $action;
                                $held = $isSuper || in_array($key, $mine, true);
                            @endphp

                            <label class="arp-perm {{ $held ? '' : 'locked' }}"
                                   data-label="{{ strtolower($label) }}">
                                <span class="arp-perm-name">{{ $label }}</span>
                                <span class="arp-toggle">
                                    <input type="checkbox"
                                           class="perm"
                                           value="{{ $key }}"
                                           data-held="{{ $held ? 1 : 0 }}"
                                           {{ ($canEdit && $held) ? '' : 'disabled' }}>
                                    <span class="arp-toggle-track">
                                        <span class="arp-toggle-thumb"></span>
                                    </span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        {{-- ==================== SAVE BAR ==================== --}}
        @if ($canEdit)
            <div class="arp-savebar" id="saveBar">
                <div class="arp-savebar-info">
                    <i class="fa fa-circle-check"></i>
                    <span><b id="selCount">0</b> permission(s) selected</span>
                    <span id="dirty" class="arp-dirty" style="display:none">
                        <i class="fa fa-circle-dot"></i> unsaved changes
                    </span>
                </div>
                <button class="arp-btn primary" id="btnSave">
                    <i class="fa fa-save"></i> Save permissions
                </button>
            </div>
        @endif
    @endif
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
        const count = boxes().filter(b => b.checked).length;
        document.getElementById('selCount') && (document.getElementById('selCount').textContent = count);

        const d = document.getElementById('dirty');
        if (d) d.style.display = state() !== saved ? 'inline-flex' : 'none';

        // Sync "all" toggle per module
        document.querySelectorAll('.arp-mod').forEach(m => {
            const own = [...m.querySelectorAll('.perm:not(:disabled)')];
            const all = m.querySelector('.mod-all');
            if (all) {
                const on = own.length > 0 && own.every(b => b.checked);
                const some = own.some(b => b.checked);
                all.checked = on;
                all.indeterminate = !on && some;
            }
        });

        // Update role chip
        const chip = document.getElementById('roleChip');
        if (chip && sel) {
            const opt = sel.options[sel.selectedIndex];
            chip.textContent = opt ? opt.textContent.trim() : '—';
        }
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
                const c = await Swal.fire({
                    icon: 'question',
                    title: 'Discard unsaved changes?',
                    showCancelButton: true,
                    confirmButtonText: 'Discard',
                    confirmButtonColor: '#5D42E8',
                });
                if (!c.isConfirmed) { sel.value = sel.dataset.cur; return; }
            }
            sel.dataset.cur = sel.value;
            history.replaceState(null, '', '?role=' + sel.value);
            loadRole();
        });
        sel.dataset.cur = sel.value;
        loadRole();
    }

    document.addEventListener('change', e => {
        if (e.target.classList.contains('mod-all')) {
            e.target.closest('.arp-mod')
                .querySelectorAll('.perm:not(:disabled)')
                .forEach(b => b.checked = e.target.checked);
        }
        refresh();
    });

    document.getElementById('btnAll')?.addEventListener('click', () => {
        boxes().filter(b => !b.disabled).forEach(b => b.checked = true);
        refresh();
    });

    document.getElementById('btnNone')?.addEventListener('click', () => {
        boxes().filter(b => !b.disabled).forEach(b => b.checked = false);
        refresh();
    });

    document.getElementById('permSearch')?.addEventListener('input', e => {
        const q = e.target.value.toLowerCase().trim();
        document.querySelectorAll('.arp-mod').forEach(mod => {
            let visibleCount = 0;
            mod.querySelectorAll('.arp-perm').forEach(p => {
                const match = !q || p.dataset.label.includes(q);
                p.style.display = match ? '' : 'none';
                if (match) visibleCount++;
            });
            mod.style.display = visibleCount > 0 ? '' : 'none';
        });
    });

    document.getElementById('btnSave')?.addEventListener('click', async () => {
        const r = await arpAct(BASE + '/' + sel.value, 'POST', {
            permissions: boxes().filter(b => b.checked).map(b => b.value)
        });
        if (!r.ok) return;
        saved = state(); refresh();
        Swal.fire({
            icon: 'success',
            title: 'Saved',
            text: r.data.added + ' added, ' + r.data.removed + ' removed.',
            timer: 1800,
            showConfirmButton: false,
        });
    });

    window.addEventListener('beforeunload', e => {
        if (document.getElementById('btnSave') && state() !== saved) {
            e.preventDefault();
            e.returnValue = '';
        }
    });
    </script>
@endsection