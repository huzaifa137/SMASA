{{-- expects $period, $periodOptions; $extra = hidden query keys to keep --}}
<form method="GET" class="st-period" onchange="if(event.target.name==='period'){stCustomToggle(this);if(event.target.value!=='custom')this.submit();}">
    @foreach(($extra ?? []) as $k => $v)<input type="hidden" name="{{ $k }}" value="{{ $v }}">@endforeach
    <i class="fas fa-calendar-days st-mute"></i>
    <select name="period">
        @foreach($periodOptions as $k => $label)
            <option value="{{ $k }}" @selected($period->key === $k || ($period->key === 'custom' && $k === 'custom'))>{{ $label }}</option>
        @endforeach
    </select>
    <span class="st-custom" style="{{ $period->key === 'custom' ? '' : 'display:none' }}">
        <input type="date" name="from" value="{{ $period->startDate() }}"> <span>to</span> <input type="date" name="to" value="{{ $period->endDate() }}">
        <button class="st-btn st-btn--sm st-btn--primary" type="submit">Apply</button>
    </span>
    <span class="st-mute" style="font-size:.8rem">{{ $period->start->format('j M Y') }} – {{ $period->end->format('j M Y') }}</span>
</form>
<script>function stCustomToggle(f){var c=f.querySelector('.st-custom');if(c)c.style.display=f.period.value==='custom'?'':'none';}</script>
