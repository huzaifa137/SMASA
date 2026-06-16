{{-- resources/views/user-rights/permissions/index.blade.php --}}
@extends('layouts-side-bar.master')

@section('css')
    <style>
        :root {
            --urp-primary: #4f46e5;
            --urp-dark: #1e1b4b;
            --urp-accent: #7c3aed;
            --radius: 14px;
        }

        .urp-hero {
            background: linear-gradient(135deg, var(--urp-dark) 0%, #312e81 60%, var(--urp-accent) 100%);
            border-radius: var(--radius);
            padding: 2rem 2rem 2.8rem;
            margin-bottom: -1.5rem;
            position: relative;
            overflow: hidden;
            color: #fff;
        }

        .urp-hero h2 {
            font-size: 1.7rem;
            font-weight: 700;
            margin-bottom: .25rem;
        }

        /* ROLE SELECTOR */
        .role-tab {
            cursor: pointer;
            border-radius: 10px;
            border: 2px solid #e5e7eb;
            padding: .55rem 1rem;
            font-size: .85rem;
            font-weight: 600;
            color: #374151;
            background: #fff;
            transition: all .2s;
            display: flex;
            align-items: center;
            gap: .45rem;
            white-space: nowrap;
        }

        .role-tab:hover {
            border-color: var(--urp-primary);
            color: var(--urp-primary);
        }

        .role-tab.active {
            background: linear-gradient(135deg, var(--urp-primary), var(--urp-accent));
            border-color: transparent;
            color: #fff;
            box-shadow: 0 4px 12px rgba(79, 70, 229, .35);
        }

        .role-tab .avatar {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: rgba(255, 255, 255, .25);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .8rem;
            font-weight: 700;
            flex-shrink: 0;
        }

        .role-tab:not(.active) .avatar {
            background: var(--urp-primary);
            color: #fff;
        }

        /* MODULE CARDS */
        .module-card {
            border-radius: 12px;
            border: 2px solid #e5e7eb;
            overflow: hidden;
            transition: border-color .2s, box-shadow .2s;
            background: #fff;
        }

        .module-card.module-enabled {
            border-color: #a5b4fc;
        }

        .module-card.module-disabled {
            /* opacity: .65; */
        }

        .module-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: .9rem 1.1rem;
            cursor: pointer;
            user-select: none;
            background: #f8f9fa;
        }

        .module-header:hover {
            background: #f0f0ff;
        }

        .module-icon-wrap {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.05rem;
            flex-shrink: 0;
        }

        .module-name {
            font-weight: 700;
            font-size: .92rem;
            color: #1e1b4b;
        }

        .module-features-wrap {
            padding: .75rem 1.1rem 1rem;
            border-top: 1px solid #f3f4f6;
        }

        .feature-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: .4rem 0;
            border-bottom: 1px solid #f9fafb;
        }

        .feature-row:last-child {
            border-bottom: none;
        }

        .feature-name {
            font-size: .82rem;
            color: #374151;
        }

        /* TOGGLE SWITCH */
        .tog-wrap {
            position: relative;
            display: inline-block;
            width: 42px;
            height: 22px;
        }

        .tog-wrap input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .tog-slider {
            position: absolute;
            cursor: pointer;
            inset: 0;
            border-radius: 22px;
            background: #d1d5db;
            transition: .25s;
        }

        .tog-slider::before {
            content: '';
            position: absolute;
            height: 16px;
            width: 16px;
            left: 3px;
            bottom: 3px;
            background: #fff;
            border-radius: 50%;
            transition: .25s;
        }

        input:checked+.tog-slider {
            background: var(--urp-primary);
        }

        input:checked+.tog-slider::before {
            transform: translateX(20px);
        }

        input:disabled+.tog-slider {
            opacity: .45;
            cursor: default;
        }

        /* STATUS INDICATOR */
        .perm-status {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            padding: .25rem .7rem;
            border-radius: 20px;
            font-size: .75rem;
            font-weight: 600;
        }

        .perm-status.on {
            background: #dcfce7;
            color: #15803d;
        }

        .perm-status.off {
            background: #fef2f2;
            color: #dc2626;
        }

        .saving-spinner {
            display: none;
        }

        .is-saving .saving-spinner {
            display: inline-block;
        }

        .is-saving .save-text {
            display: none;
        }

        .no-role-msg {
            text-align: center;
            padding: 4rem 2rem;
            color: #9ca3af;
        }

        .no-role-msg i {
            font-size: 4rem;
            display: block;
            margin-bottom: 1rem;
            opacity: .3;
        }
        #roleTabs .role-tab {
    margin-right: 0.6rem;
}

/* ── Action buttons group ── */
#btnEnableAll,
#btnDisableAll,
#btnSaveAll {
    border-radius: 8px;
    font-size: .8rem;
    font-weight: 600;
    padding: .5rem 1.1rem;
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    transition: all .2s;
    border-width: 1.5px;
    letter-spacing: .01em;
}

/* Enable All */
#btnEnableAll {
    background: #f0fdf4;
    color: #15803d;
    border-color: #86efac;
}

#btnEnableAll:hover {
    background: #059669;
    color: #fff;
    border-color: #059669;
    box-shadow: 0 3px 10px rgba(5, 150, 105, .25);
    transform: translateY(-1px);
}

/* Disable All */
#btnDisableAll {
    background: #fef2f2;
    color: #dc2626;
    border-color: #fca5a5;
}

#btnDisableAll:hover {
    background: #dc2626;
    color: #fff;
    border-color: #dc2626;
    box-shadow: 0 3px 10px rgba(220, 38, 38, .22);
    transform: translateY(-1px);
}

/* Save Permissions */
#btnSaveAll {
    background: linear-gradient(135deg, #4f46e5, #7c3aed);
    color: #fff;
    border: none;
    box-shadow: 0 3px 12px rgba(79, 70, 229, .35);
    padding-left: 1.3rem;
    padding-right: 1.3rem;
}

#btnSaveAll:hover:not(:disabled) {
    background: linear-gradient(135deg, #4338ca, #6d28d9);
    box-shadow: 0 5px 18px rgba(79, 70, 229, .45);
    transform: translateY(-1px);
}

#btnSaveAll:disabled {
    opacity: .75;
    cursor: not-allowed;
    transform: none;
}

/* Divider between enable/disable and save */
#btnSaveAll {
    margin-left: .35rem;
    border-left: none;
    position: relative;
}

#btnSaveAll::before {
    content: '';
    position: absolute;
    left: -.6rem;
    top: 15%;
    height: 70%;
    width: 1px;
    background: #e2e8f0;
}
    </style>
@endsection

@section('content')
    <div class="container-fluid px-3 py-3">

        {{-- HERO --}}
        <div class="urp-hero mb-4">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                <div>
                    <h2><i class="fa fa-shield-alt mr-2"></i>Module Permissions</h2>
                    <p class="mt-2" style="opacity:.8;">Select a role then toggle which modules and features it can access.
                    </p>
                </div>
                <div>@include('user-rights._nav')</div>
            </div>
        </div>

        @if($roles->isEmpty())
            <div class="card border-0 shadow-sm p-5 text-center" style="border-radius:var(--radius);">
                <i class="fa fa-user-tag fa-3x text-muted mb-3"></i>
                <h5>No roles found for this school.</h5>
                <p class="text-muted">Create roles first before configuring permissions.</p>
                <a href="{{ route('urp.roles.index') }}" class="btn btn-primary">
                    <i class="fa fa-plus mr-1"></i> Create Roles
                </a>
            </div>
        @else

            {{-- ROLE TABS --}}
            <div class="card border-0 shadow-sm mb-3 p-3" style="border-radius:var(--radius);">
                <div class="d-flex align-items-center gap-2 flex-wrap" id="roleTabs">
                    <span class="text-muted mr-2" style="font-size:.8rem;font-weight:600;white-space:nowrap;">SELECT
                        ROLE:</span>
                    @foreach($roles as $role)
                        <div class="role-tab {{ ($loop->first || request('role') == $role->id) && ($loop->first ? !request('role') : true) ? 'active' : '' }}"
                            data-role-id="{{ $role->id }}" data-role-name="{{ $role->name }}">
                            <div class="avatar">{{ strtoupper(substr($role->name, 0, 1)) }}</div>
                            {{ $role->name }}
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- ACTIVE ROLE HEADER --}}
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <div>
                    <span id="activeRoleName" class="font-weight-700" style="font-size:1.1rem;color:var(--urp-dark);">
                        {{ $roles->first()->name }}
                    </span>
                    <span class="text-muted ml-2" style="font-size:.85rem;">— configure module access below</span>
                </div>
                <div class="d-flex gap-2 align-items-center">
                    <button id="btnEnableAll" class="btn btn-sm btn-outline-success">
                        <i class="fa fa-check-double mr-1"></i>Enable All Modules
                    </button> &nbsp;&nbsp;
                    <button id="btnDisableAll" class="btn btn-sm btn-outline-danger">
                        <i class="fa fa-times mr-1"></i>Disable All
                    </button>
                    <button id="btnSaveAll" class="btn btn-sm btn-primary is-saving-btn">
                        <span class="save-text"><i class="fa fa-save mr-1"></i>Save Permissions</span>
                        <span class="saving-spinner"><i class="fa fa-spinner fa-spin mr-1"></i>Saving...</span>
                    </button>
                </div>
            </div>

            {{-- PERMISSIONS MATRIX --}}
            <div id="permissionsMatrix">
                {{-- Rendered by JS after role selection --}}
                <div class="row g-3" id="moduleGrid">
                    @foreach($modules as $module)
                        <div class="col-md-6 col-lg-4 mb-1">
                            <div class="module-card" data-module-id="{{ $module->id }}" data-module-key="{{ $module->key }}">
                                <div class="module-header" onclick="toggleModuleExpand(this)">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="module-icon-wrap" style="background:#eef2ff;color:var(--urp-primary);">
                                            <i class="{{ $module->icon }}"></i>
                                        </div> &nbsp;
                                        <div>
                                            <div class="module-name">{{ $module->name }}</div>
                                            <div style="font-size:.72rem;color:#9ca3af;">&nbsp;{{ $module->features->count() }}
                                                feature{{ $module->features->count() != 1 ? 's' : '' }}</div>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center gap-2" onclick="event.stopPropagation();">
                                        <label class="tog-wrap mt-2" title="Toggle module access">
                                            <input type="checkbox" class="module-toggle" data-module-id="{{ $module->id }}"
                                                value="{{ $module->id }}">
                                            <span class="tog-slider"></span>
                                        </label> &nbsp;&nbsp;&nbsp;
                                        <i class="fa fa-chevron-down text-muted"
                                            style="font-size:.75rem;transition:transform .2s;"></i>
                                    </div>
                                </div>
                                @if($module->features->count() > 0)
                                    <div class="module-features-wrap" style="display:none;">
                                        @foreach($module->features as $feat)
                                            <div class="feature-row">
                                                <span class="feature-name">
                                                    <i class="fa fa-circle"
                                                        style="font-size:.4rem;color:#d1d5db;margin-right:.4rem;vertical-align:middle;"></i>
                                                    {{ $feat->name }}
                                                </span>
                                                <label class="tog-wrap" title="{{ $feat->name }}">
                                                    <input type="checkbox" class="feature-toggle" data-feature-id="{{ $feat->id }}"
                                                        data-module-id="{{ $module->id }}" value="{{ $feat->id }}">
                                                    <span class="tog-slider"></span>
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="px-3 py-2 text-muted" style="font-size:.78rem;display:none;">No granular features for
                                        this module.</div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

        @endif
    </div>
     </div>
            </div>
@endsection

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        let activeRoleId = null;

        // ── INIT: pick first role (or from URL param) ──
        document.addEventListener('DOMContentLoaded', () => {
            const urlRole = new URLSearchParams(window.location.search).get('role');
            const firstTab = urlRole
                ? document.querySelector(`.role-tab[data-role-id="${urlRole}"]`) || document.querySelector('.role-tab')
                : document.querySelector('.role-tab');
            if (firstTab) selectRole(firstTab);
        });

        // ── SELECT ROLE TAB ──
        document.querySelectorAll('.role-tab').forEach(tab => {
            tab.addEventListener('click', () => selectRole(tab));
        });

        function selectRole(tab) {
            document.querySelectorAll('.role-tab').forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            activeRoleId = tab.dataset.roleId;
            document.getElementById('activeRoleName').textContent = tab.dataset.roleName;

            // Fetch current permissions for this role
            loadRolePermissions(activeRoleId);
        }

        function loadRolePermissions(roleId) {
            // Uncheck everything first
            document.querySelectorAll('.module-toggle, .feature-toggle').forEach(cb => {
                cb.checked = false;
                cb.disabled = false;
            });
            updateModuleCardStates();

            fetch(`/user-rights/permissions/${roleId}/get`, {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf }
            })
                .then(r => r.json())
                .then(data => {
                    // Apply module access
                    (data.module_ids || []).forEach(id => {
                        const cb = document.querySelector(`.module-toggle[data-module-id="${id}"]`);
                        if (cb) cb.checked = true;
                    });
                    // Apply feature access
                    (data.feature_ids || []).forEach(id => {
                        const cb = document.querySelector(`.feature-toggle[data-feature-id="${id}"]`);
                        if (cb) cb.checked = true;
                    });
                    updateModuleCardStates();
                })
                .catch(() => {/* first time - no permissions yet */ });
        }

        // ── MODULE TOGGLE: when module turned OFF, disable its features ──
        document.querySelectorAll('.module-toggle').forEach(cb => {
            cb.addEventListener('change', function () {
                const moduleId = this.dataset.moduleId;
                const enabled = this.checked;
                // If disabling the module, uncheck + disable all its features
                document.querySelectorAll(`.feature-toggle[data-module-id="${moduleId}"]`).forEach(fcb => {
                    if (!enabled) {
                        fcb.checked = false;
                        fcb.disabled = true;
                    } else {
                        fcb.disabled = false;
                    }
                });
                updateModuleCardStates();
            });
        });

        function updateModuleCardStates() {
            document.querySelectorAll('.module-card').forEach(card => {
                const modId = card.dataset.moduleId;
                const toggle = card.querySelector('.module-toggle');
                if (!toggle) return;
                const on = toggle.checked;
                card.classList.toggle('module-enabled', on);
                card.classList.toggle('module-disabled', !on);
                // Disable feature toggles if module is off
                card.querySelectorAll('.feature-toggle').forEach(fcb => {
                    fcb.disabled = !on;
                });
            });
        }

        // ── EXPAND / COLLAPSE FEATURES ──
        function toggleModuleExpand(header) {
            const wrap = header.nextElementSibling;
            const arrow = header.querySelector('.fa-chevron-down');
            const isOpen = wrap && wrap.style.display !== 'none';
            if (wrap) wrap.style.display = isOpen ? 'none' : 'block';
            if (arrow) arrow.style.transform = isOpen ? '' : 'rotate(180deg)';
        }

        // ── ENABLE ALL ──
        document.getElementById('btnEnableAll').addEventListener('click', () => {
            document.querySelectorAll('.module-toggle').forEach(cb => { cb.checked = true; });
            document.querySelectorAll('.feature-toggle').forEach(cb => { cb.checked = true; cb.disabled = false; });
            updateModuleCardStates();
        });

        // ── DISABLE ALL ──
        document.getElementById('btnDisableAll').addEventListener('click', () => {
            Swal.fire({
                title: 'Disable all modules?',
                text: 'This will remove all module & feature access for this role.',
                icon: 'warning', showCancelButton: true,
                confirmButtonColor: '#dc2626', confirmButtonText: 'Yes, disable all',
            }).then(r => {
                if (!r.isConfirmed) return;
                document.querySelectorAll('.module-toggle, .feature-toggle').forEach(cb => {
                    cb.checked = false; cb.disabled = false;
                });
                updateModuleCardStates();
            });
        });

        // ── SAVE ALL PERMISSIONS ──
        document.getElementById('btnSaveAll').addEventListener('click', function () {
            if (!activeRoleId) { Swal.fire('No role selected', '', 'warning'); return; }
            const btn = this;
            btn.classList.add('is-saving');
            btn.disabled = true;

            const moduleIds = [...document.querySelectorAll('.module-toggle:checked')].map(cb => parseInt(cb.value));
            const featureIds = [...document.querySelectorAll('.feature-toggle:checked')].map(cb => parseInt(cb.value));

            fetch(`/user-rights/permissions/${activeRoleId}/save`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify({ module_ids: moduleIds, feature_ids: featureIds })
            })
                .then(r => r.json())
                .then(data => {
                    Swal.fire({
                        icon: 'success', title: 'Permissions Saved!',
                        text: `Access settings for this role have been updated.`,
                        timer: 2200, showConfirmButton: false,
                    });
                })
                .catch(() => Swal.fire('Error', 'Failed to save. Please try again.', 'error'))
                .finally(() => {
                    btn.classList.remove('is-saving');
                    btn.disabled = false;
                });
        });
    </script>
@endsection