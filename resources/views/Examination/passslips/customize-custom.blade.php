@extends('layouts-side-bar.master')

@php
    use App\Http\Controllers\Helper;
    $meta = $custom['meta'];
    $hasAccent = !empty($meta['accent']);
    $offByDefault = $meta['off_by_default'] ?? [];
@endphp

@section('css')
<style>
    .cc-wrap{display:grid;grid-template-columns:340px 1fr;gap:18px;align-items:start}
    @media(max-width:991px){.cc-wrap{grid-template-columns:1fr}}
    .cc-panel{background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:18px}
    .cc-panel h6{font-weight:700;margin:14px 0 8px;text-transform:uppercase;font-size:11px;color:#64748b;letter-spacing:.6px}
    .cc-tog{display:flex;justify-content:space-between;align-items:center;padding:6px 0;border-bottom:1px solid #f1f5f9}
    .cc-badge{display:inline-block;background:#e0e7ff;color:#3730a3;border-radius:999px;padding:2px 10px;font-size:12px;font-weight:700}
    #ccFrame{width:100%;height:1250px;border:1px solid #e5e7eb;border-radius:12px;background:#f1f5f9}
</style>
@endsection

@section('content')
<div class="container-fluid pt-4">
    <a href="{{ route('examination.passslips.index', $exam->id) }}" class="btn btn-sm btn-outline-secondary mb-3">← Back to Pass Slips</a>
    <h3 class="mb-1">Your school's report card design</h3>
    <p class="text-muted">
        <span class="cc-badge">{{ $custom['template']->name }}</span>
        &nbsp;{{ $custom['template']->description }}
        @if($custom['locked'])<br><small>This design was set up specifically for your school.</small>@endif
    </p>

    @if($examClasses->isEmpty())
        <div class="alert alert-warning">This examination has no {{ strtolower(\App\Support\CustomReportCards::LEVELS[$custom['level']]) }} classes, so there is nothing to preview.</div>
    @else
    <div class="cc-wrap">
        <div class="cc-panel">
            <label class="font-weight-bold small">Class</label>
            <select id="ccClass" class="form-control mb-2">
                @foreach($examClasses as $ec)
                    <option value="{{ $ec->class_id }}">{{ Helper::recordMdname($ec->class_id) }}</option>
                @endforeach
            </select>

            <div class="d-flex gap-2 mb-2">
                <a id="ccPrintClass" class="btn btn-primary btn-sm mr-2" target="_blank" href="#">Print this class</a>
                <a class="btn btn-outline-primary btn-sm" target="_blank" href="{{ route('examination.passslips.all', $exam->id) }}?template={{ $template }}">Print all</a>
            </div>

            @if($hasAccent)
                <h6>Accent colour</h6>
                <input type="color" id="ccAccent" value="{{ $meta['accent'] }}" style="width:100%;height:38px;border:1px solid #e5e7eb;border-radius:8px">
            @endif

            @foreach($toggleGroups as $group => $items)
                <h6>{{ $group }}</h6>
                @foreach($items as $key => $t)
                    <label class="cc-tog mb-0">
                        <span>{{ $t['label'] }}</span>
                        <input type="checkbox" class="cc-toggle" data-key="{{ $key }}" {{ in_array($key, $offByDefault, true) ? '' : 'checked' }}>
                    </label>
                @endforeach
            @endforeach

            @if(count($toggleGroups) || $hasAccent)
                <hr>
                <button id="ccSave" class="btn btn-success btn-block">Save for this class</button>
                <button id="ccSaveAll" class="btn btn-outline-success btn-block">Save for all {{ strtolower(\App\Support\CustomReportCards::LEVELS[$custom['level']]) }} classes</button>
                <div id="ccMsg" class="small mt-2"></div>
            @else
                <p class="text-muted small mt-3">This design is fully fixed — nothing to customise.</p>
            @endif
        </div>

        <iframe id="ccFrame" title="Preview"></iframe>
    </div>
    @endif
</div>
</div>
</div>
@endsection

@section('js')
<script>
(function () {
    const $ = s => document.querySelector(s);
    if (!$('#ccClass')) return;
    const cfg = {
        template: @json($template),
        preview: @json(route('examination.passslips.preview', $exam->id)),
        printClass: @json(route('examination.passslips.class', $exam->id)),
        get: @json(route('examination.passslips.settings.get', $exam->id)),
        save: @json(route('examination.passslips.settings.save', $exam->id)),
        csrf: @json(csrf_token()),
        classIds: @json($examClasses->pluck('class_id')->values()),
    };

    const collect = () => {
        const s = { template: cfg.template };
        document.querySelectorAll('.cc-toggle').forEach(c => s[c.dataset.key] = c.checked ? 1 : 0);
        if ($('#ccAccent')) s.accent = $('#ccAccent').value;
        return s;
    };
    const refresh = () => {
        const q = new URLSearchParams(Object.assign({ embed: 1, class_id: $('#ccClass').value }, collect()));
        $('#ccFrame').src = cfg.preview + '?' + q;
        const p = new URLSearchParams(Object.assign({ class_id: $('#ccClass').value }, collect()));
        $('#ccPrintClass').href = cfg.printClass + '?' + p;
    };
    const msg = (t, ok) => { $('#ccMsg').textContent = t; $('#ccMsg').style.color = ok ? '#15803d' : '#b91c1c'; };

    async function loadSaved() {
        const r = await fetch(cfg.get + '?' + new URLSearchParams({ class_id: $('#ccClass').value, template: cfg.template }), { headers: { Accept: 'application/json' } });
        const j = await r.json();
        const s = j.settings || {};
        document.querySelectorAll('.cc-toggle').forEach(c => { if (c.dataset.key in s) c.checked = !!Number(s[c.dataset.key]) && s[c.dataset.key] !== 'false'; });
        if ($('#ccAccent') && s.accent) $('#ccAccent').value = s.accent;
        refresh();
    }
    async function save(ids) {
        const r = await fetch(cfg.save, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': cfg.csrf },
            body: JSON.stringify({ class_ids: ids, settings: collect() }),
        });
        const j = await r.json().catch(() => ({}));
        msg(r.ok ? (j.message || 'Saved.') : 'Could not save.', r.ok);
    }

    $('#ccClass').addEventListener('change', loadSaved);
    document.querySelectorAll('.cc-toggle').forEach(c => c.addEventListener('change', refresh));
    $('#ccAccent')?.addEventListener('input', refresh);
    $('#ccSave')?.addEventListener('click', () => save([Number($('#ccClass').value)]));
    $('#ccSaveAll')?.addEventListener('click', () => save(cfg.classIds.map(Number)));
    loadSaved();
})();
</script>
@endsection
