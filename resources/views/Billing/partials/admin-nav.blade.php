@php $r = request()->route()?->getName(); $A = \App\Support\AdminAccess::class; @endphp
<div class="bl-tabs">
    @if($A::can('billing.view'))<a href="{{ route('billing.dashboard') }}" class="{{ $r === 'billing.dashboard' ? 'active' : '' }}"><i class="fas fa-chart-pie"></i>Dashboard</a>@endif
    @if($A::can('billing.view'))<a href="{{ route('billing.terms') }}" class="{{ str_starts_with($r ?? '', 'billing.terms') ? 'active' : '' }}"><i class="fas fa-calendar-days"></i>Billing Terms</a>@endif
    @if($A::can('billing.view'))<a href="{{ route('billing.invoices') }}" class="{{ str_starts_with($r ?? '', 'billing.invoices') ? 'active' : '' }}"><i class="fas fa-file-invoice-dollar"></i>Invoices</a>@endif
    @if($A::can('billing.view'))<a href="{{ route('billing.payments') }}" class="{{ str_starts_with($r ?? '', 'billing.payments') ? 'active' : '' }}"><i class="fas fa-hand-holding-dollar"></i>Payments</a>@endif
    @if($A::can('billing.manage_pricing'))<a href="{{ route('billing.pricing') }}" class="{{ str_starts_with($r ?? '', 'billing.pricing') ? 'active' : '' }}"><i class="fas fa-tags"></i>School Pricing</a>@endif
    @if($A::can('billing.view_reports'))<a href="{{ route('billing.reports') }}" class="{{ $r === 'billing.reports' ? 'active' : '' }}"><i class="fas fa-chart-column"></i>Reports</a>@endif
    @if($A::can('billing.manage_settings'))<a href="{{ route('billing.settings') }}" class="{{ str_starts_with($r ?? '', 'billing.settings') ? 'active' : '' }}"><i class="fas fa-gear"></i>Settings</a>@endif
</div>
