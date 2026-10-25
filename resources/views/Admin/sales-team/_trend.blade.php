{{-- expects $trend (monthlyTrend) and $cur --}}
@php
    $max = max(1, collect($trend)->max('cash'));
    $w = 560; $h = 190; $pad = 28; $n = max(1, count($trend)); $slot = ($w - $pad * 2) / $n; $bw = min(46, $slot * .55);
@endphp
<div class="st-scroll"><svg class="st-chart" viewBox="0 0 {{ $w }} {{ $h }}" width="100%" style="min-width:420px;max-width:760px" role="img" aria-label="Cash collected and schools enrolled by month">
    <line x1="{{ $pad }}" y1="{{ $h - 30 }}" x2="{{ $w - $pad }}" y2="{{ $h - 30 }}" stroke="#e6e8f5"/>
    @foreach($trend as $i => $m)
        @php $bh = ($h - 70) * ($m['cash'] / $max); $x = $pad + $i * $slot + ($slot - $bw) / 2; @endphp
        <rect x="{{ $x }}" y="{{ $h - 30 - $bh }}" width="{{ $bw }}" height="{{ max(1, $bh) }}" rx="5" fill="#2C29CA" opacity="{{ $m['cash'] > 0 ? .9 : .15 }}"/>
        <text x="{{ $x + $bw / 2 }}" y="{{ $h - 34 - $bh }}" text-anchor="middle">{{ $m['cash'] > 0 ? \App\Support\SalesFormat::short($m['cash']) : '' }}</text>
        <text x="{{ $x + $bw / 2 }}" y="{{ $h - 14 }}" text-anchor="middle">{{ $m['label'] }}</text>
        <text x="{{ $x + $bw / 2 }}" y="{{ $h - 1 }}" text-anchor="middle" style="fill:#1fa463;font-weight:700">+{{ $m['enrolled'] }}</text>
    @endforeach
</svg></div>
<div class="st-mute" style="font-size:.78rem"><span style="color:#2C29CA">■</span> {{ $cur }} collected &nbsp; <span style="color:#1fa463">+n</span> schools enrolled</div>
