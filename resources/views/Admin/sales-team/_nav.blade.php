@php $__A = \App\Support\AdminAccess::class; @endphp
<div class="st-tabs">
    @if($__A::can('sales_team.view_team'))
        <a href="{{ route('sales.index') }}" class="{{ ($tab ?? '') === 'leaders' ? 'on' : '' }}"><i class="fas fa-trophy"></i> Leaders</a>
        <a href="{{ route('sales.reps') }}" class="{{ ($tab ?? '') === 'reps' ? 'on' : '' }}"><i class="fas fa-users"></i> Representatives</a>
        <a href="{{ route('sales.targets') }}" class="{{ ($tab ?? '') === 'targets' ? 'on' : '' }}"><i class="fas fa-bullseye"></i> Targets</a>
        <a href="{{ route('sales.transfers') }}" class="{{ ($tab ?? '') === 'transfers' ? 'on' : '' }}"><i class="fas fa-right-left"></i> Transfers</a>
    @endif
    @if($__A::canAny(['sales_team.view_team', 'sales_team.assign_schools']))
        <a href="{{ route('sales.unassigned') }}" class="{{ ($tab ?? '') === 'unassigned' ? 'on' : '' }}"><i class="fas fa-inbox"></i> Unassigned schools</a>
    @endif
    @if($__A::can('sales_team.view_own') && !$__A::can('sales_team.view_team'))
        <a href="{{ route('sales.me') }}" class="on"><i class="fas fa-user-check"></i> My performance</a>
    @endif
</div>
