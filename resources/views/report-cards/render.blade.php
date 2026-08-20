{{--
    THE RENDERER — SECTION / GRID EDITION.
    Elements are no longer free x/y objects on a canvas — each element is a
    "section" that belongs to a `row` (an integer group) and has a `width`
    (1–12, Bootstrap-style grid units). Sections in the same row are floated
    side by side; rows stack top to bottom in array order. This is what
    lets a school just pick a section and make it col-md-6 or col-md-12
    instead of hand-positioning pixels.

    Deliberately uses an old-school FLOAT grid (not flexbox/CSS grid)
    because dompdf — which renders the real PDF — has solid float support
    but very unreliable flexbox support. Keeping one CSS technique here
    guarantees the on-screen preview and the printed PDF match.

    Used for: the gallery thumbnail iframe, "Preview with sample data",
    the live builder canvas, and actual PDF generation — ONE renderer,
    so design-time and print-time can never drift apart.
--}}
@php
    $gridUnit = ($template->canvas_width - 80) / 12; // 80 = 40px page margin each side
    $rows = collect($elements)
        ->filter(fn ($el) => ($el['type'] ?? null) !== 'watermark')
        ->groupBy(fn ($el) => $el['row'] ?? 0)
        ->sortKeys();
    $watermark = collect($elements)->first(fn ($el) => ($el['type'] ?? null) === 'watermark');
@endphp
<div class="report-canvas" style="
    position: relative;
    width: {{ $template->canvas_width }}px;
    min-height: {{ $template->canvas_height }}px;
    box-sizing: border-box;
    padding: 40px;
    background: {{ $template->background['color'] ?? '#ffffff' }};
    font-family: 'Helvetica Neue', Arial, sans-serif;
    color: #1a1a1a;
">
    @if(!empty($template->background['image']))
        <img src="{{ $template->background['image'] }}" style="
            position:absolute; inset:0; width:100%; height:100%; object-fit:cover;
            opacity: {{ $template->background['opacity'] ?? 1 }};
        ">
    @endif

    @if($watermark)
        @php $wp = $watermark['props'] ?? []; @endphp
        <div style="
            position:absolute; top:45%; left:0; width:100%; text-align:center;
            transform: translateY(-50%); z-index:0; pointer-events:none;
            opacity:{{ $wp['opacity'] ?? 0.06 }}; font-size:{{ $wp['fontSize'] ?? 90 }}px;
            font-weight:800; letter-spacing:.05em; color:#000;
        ">{{ $wp['content'] ?? '' }}</div>
    @endif

    <div style="position:relative; z-index:1;">
        @foreach($rows as $rowElements)
            <div class="rc-row" style="width:100%; overflow:hidden; margin-bottom:{{ $rowElements->first()['props']['rowSpacing'] ?? 12 }}px;">
                @foreach($rowElements as $el)
                    @php
                        $width = max(1, min(12, (int) ($el['width'] ?? 12)));
                        $props = $el['props'] ?? [];
                        $align = $el['align'] ?? ($props['align'] ?? 'left');
                    @endphp
                    <div class="rc-col rc-col--{{ $el['type'] }}" data-el-id="{{ $el['id'] ?? '' }}" style="
                        float:left; width:{{ round($width / 12 * 100, 4) }}%; box-sizing:border-box;
                        padding: 0 8px; text-align:{{ $align }};
                    ">
                        @switch($el['type'])

                            @case('logo')
                                @php $src = $data[$props['slot'] ?? 'logo_primary'] ?? null; $h = $props['height'] ?? 72; @endphp
                                @if($src)
                                    <img src="{{ $src }}" style="
                                        height:{{ $h }}px; max-width:100%; object-fit:contain;
                                        border-radius:{{ $props['borderRadius'] ?? 0 }}px;
                                        {{ !empty($props['shadow']) ? 'box-shadow:0 2px 6px rgba(0,0,0,.15);' : '' }}
                                    ">
                                @endif
                                @break

                            @case('text')
                                <div style="
                                    font-size:{{ $props['fontSize'] ?? 14 }}px;
                                    font-weight:{{ $props['fontWeight'] ?? 400 }};
                                    color:{{ $props['color'] ?? '#111' }};
                                    line-height:1.35;
                                ">{!! \App\Support\MergeTags::resolve($props['content'] ?? '', $data) !!}</div>
                                @break

                            @case('student_field')
                                @php
                                    $val = data_get($data, 'student.' . ($props['field'] ?? ''), '');
                                    $label = $props['label'] ?? null;
                                @endphp
                                <div style="font-size:{{ $props['fontSize'] ?? 13 }}px; padding:3px 0;">
                                    @if($label)<strong>{{ $label }}:</strong>@endif {{ $val }}
                                </div>
                                @break

                            @case('student_photo')
                                @php $h = $props['height'] ?? 130; @endphp
                                <img src="{{ data_get($data, 'student.photo_url') }}" style="
                                    height:{{ $h }}px; max-width:100%; object-fit:cover;
                                    border-radius:{{ $props['borderRadius'] ?? 4 }}px;
                                    border: {{ $props['border'] ?? '1px solid #ddd' }};
                                    {{ !empty($props['grayscale']) ? 'filter:grayscale(1);' : '' }}
                                ">
                                @break

                            @case('subjects_table')
                                <table style="width:100%; border-collapse:collapse; font-size:{{ $props['fontSize'] ?? 12 }}px;">
                                    <thead>
                                        <tr style="background:{{ $props['headerColor'] ?? '#f2f2f2' }};">
                                            @foreach($props['columns'] ?? ['name','score','grade','remark'] as $col)
                                                <th style="padding:5px 8px; border:1px solid #ccc; text-align:left; text-transform:capitalize;">{{ str_replace('_',' ',$col) }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($data['subjects'] ?? [] as $i => $subject)
                                            <tr style="{{ !empty($props['zebra']) && $i % 2 ? 'background:#fafafa;' : '' }}">
                                                @foreach($props['columns'] ?? ['name','score','grade','remark'] as $col)
                                                    <td style="padding:5px 8px; border:1px solid #ccc;">{{ $subject[$col] ?? '' }}</td>
                                                @endforeach
                                            </tr>
                                        @empty
                                            <tr><td style="padding:6px 8px; color:#888;" colspan="{{ count($props['columns'] ?? ['name','score','grade','remark']) }}">No subjects recorded.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                                @break

                            @case('grading_key')
                                <div style="font-size:{{ $props['fontSize'] ?? 11 }}px; display:flex; gap:10px; flex-wrap:wrap;">
                                    @foreach($data['grading_key'] ?? [] as $band)
                                        <span><strong>{{ $band['grade'] }}</strong>: {{ $band['min'] }}–{{ $band['max'] }}</span>
                                    @endforeach
                                </div>
                                @break

                            @case('remarks')
                                <div style="font-size:{{ $props['fontSize'] ?? 12 }}px;">
                                    <strong style="text-transform:capitalize;">{{ str_replace('_',' ',$props['role'] ?? '') }} remarks:</strong>
                                    <p style="margin:4px 0 0;">{{ data_get($data, 'remarks.' . ($props['role'] ?? ''), '') }}</p>
                                </div>
                                @break

                            @case('signature')
                                <div style="padding-top:{{ $props['spacing'] ?? 40 }}px;">
                                    <div style="border-top:1px solid #333; margin-bottom:3px;"></div>
                                    <span style="font-size:11px; color:#555;">{{ $props['label'] ?? 'Signature' }}</span>
                                </div>
                                @break

                            @case('attendance')
                                <div style="font-size:{{ $props['fontSize'] ?? 12 }}px;">
                                    Present: {{ $data['attendance']['present'] ?? '-' }} &nbsp;
                                    Absent: {{ $data['attendance']['absent'] ?? '-' }}
                                </div>
                                @break

                            @case('divider')
                                <div style="width:100%; height:{{ $props['thickness'] ?? 2 }}px; background:{{ $props['color'] ?? '#ccc' }};"></div>
                                @break

                            @case('shape')
                                <div style="
                                    width:100%; height:{{ $props['height'] ?? 20 }}px;
                                    background:{{ $props['fill'] ?? 'transparent' }};
                                    border:{{ isset($props['borderColor']) ? '1px solid '.$props['borderColor'] : 'none' }};
                                    border-radius:{{ $props['borderRadius'] ?? 0 }}px;
                                "></div>
                                @break

                            @case('qr_code')
                                @php
                                    $qrValue = !empty($props['dataField'])
                                        ? \App\Support\MergeTags::resolve($props['dataField'], $data)
                                        : ($data['qr_text'] ?? \App\Support\MergeTags::resolve('@{{student.admission_no}}', $data));
                                    $size = $props['size'] ?? 90;
                                @endphp
                                @if($qrValue !== '')
                                    <img src="data:image/svg+xml;base64,{{ base64_encode(\SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')->size($size)->generate($qrValue)) }}" style="width:{{ $size }}px; height:{{ $size }}px;">
                                @endif
                                @break

                            @case('custom_html')
                                {!! $props['html'] ?? '' !!}
                                @break

                        @endswitch
                    </div>
                @endforeach
            </div>
        @endforeach
    </div>
</div>