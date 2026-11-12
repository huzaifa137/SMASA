@props(['icon' => 'fa-circle', 'title' => '', 'desc' => '', 'color' => '#818cf8'])
<div class="exh-feature">
    <div class="exh-feature-icon" style="background:{{ $color }}26;"><i class="fas {{ $icon }}" style="color:{{ $color }};"></i></div>
    <div>
        <div class="exh-feature-title">{{ $title }}</div>
        <div class="exh-feature-desc">{{ $desc }}</div>
    </div>
</div>
