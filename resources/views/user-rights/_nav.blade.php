{{-- resources/views/user-rights/_nav.blade.php --}}
<div class="urp-nav d-flex flex-wrap gap-2 mb-0">
    <a href="{{ route('urp.dashboard') }}"
        class="btn btn-md px-5 py-2 rounded-pill {{ request()->routeIs('urp.dashboard') ? 'btn-gradient text-white' : 'btn-light border' }}">
        <i class="fa fa-tachometer-alt mr-2"></i> Overview
    </a>
    <a href="{{ route('urp.roles.index') }}"
        class="btn btn-md px-5 py-2 rounded-pill {{ request()->routeIs('urp.roles.*') ? 'btn-gradient text-white' : 'btn-light border' }}">
        <i class="fa fa-user-tag mr-2"></i> Manage Roles
    </a>
    <a href="{{ route('urp.permissions.index') }}"
        class="btn btn-md px-5 py-2 rounded-pill {{ request()->routeIs('urp.permissions.*') ? 'btn-gradient text-white' : 'btn-light border' }}">
        <i class="fa fa-shield-alt mr-2"></i> Module Permissions
    </a>
    <a href="{{ route('urp.assign.index') }}"
        class="btn btn-md px-5 py-2 rounded-pill {{ request()->routeIs('urp.assign.*') ? 'btn-gradient text-white' : 'btn-light border' }}">
        <i class="fa fa-users-cog mr-2"></i> Assign to Staff
    </a>
</div>

<style>
    .btn-gradient {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
    }

    .btn-gradient:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    }

    .btn-light.border {
        background: white;
        border: 1px solid #e0e0e0 !important;
        transition: all 0.3s ease;
    }

    .btn-light.border:hover {
        background: #f8f9fa;
        border-color: #667eea !important;
        transform: translateY(-1px);
    }
</style>