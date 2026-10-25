{{-- Hero banner shared by the School Activity pages (same look as Audit Trail).
     Vars: $title, $subtitle, $icon (fa class), optional $chip, optional $chipClass; slot "actions" for buttons. --}}
@php $cfgH = config('school_activity'); @endphp
<div class="sa-hero mt-5">
    <div class="sa-hero-main">
        <div class="sa-hero-left">
            <div class="sa-hero-icon"><i class="fas {{ $icon ?? 'fa-heart-pulse' }}"></i></div>
            <div class="sa-hero-info">
                <h4>{{ $title }} @if(!empty($chip))<span class="sa-hero-chip {{ $chipClass ?? '' }}">{{ $chip }}</span>@endif</h4>
                <p>{{ $subtitle }}</p>
            </div>
        </div>
        @isset($actions)<div class="sa-hero-actions">{{ $actions }}</div>@endisset
    </div>
    <div class="sa-hero-features">
        <div class="sa-hero-feature">
            <div class="sa-hero-feature-icon" style="background:rgba(129,140,248,.15);"><i class="fas fa-fingerprint" style="color:#818cf8;"></i></div>
            <div><div class="sa-hero-feature-title">Real usage</div><div class="sa-hero-feature-desc">Built from the signed audit trail</div></div>
        </div>
        <div class="sa-hero-feature">
            <div class="sa-hero-feature-icon" style="background:rgba(52,211,153,.15);"><i class="fas fa-user-check" style="color:#34d399;"></i></div>
            <div><div class="sa-hero-feature-title">School users only</div><div class="sa-hero-feature-desc">Admin actions never count as activity</div></div>
        </div>
        <div class="sa-hero-feature">
            <div class="sa-hero-feature-icon" style="background:rgba(251,191,36,.15);"><i class="fas fa-calendar-days" style="color:#fbbf24;"></i></div>
            <div><div class="sa-hero-feature-title">Window</div><div class="sa-hero-feature-desc">Last {{ $cfgH['window_days'] }} days · Active ≤ {{ $cfgH['active_days'] }}d · Idle ≤ {{ $cfgH['idle_days'] }}d</div></div>
        </div>
        <div class="sa-hero-feature">
            <div class="sa-hero-feature-icon" style="background:rgba(244,114,182,.15);"><i class="fas fa-school" style="color:#f472b6;"></i></div>
            <div><div class="sa-hero-feature-title">Scope</div><div class="sa-hero-feature-desc">All schools · refreshed every {{ max(1, intdiv($cfgH['cache_seconds'], 60)) }} min</div></div>
        </div>
    </div>
</div>
