{{--
    THE RENDERER.
    Loops the same `elements` array the builder edits and draws each one
    at its stored x/y/w/h. This partial is used for:
      - the gallery thumbnail iframe + "Preview with sample data"
      - actual PDF generation (via dompdf, same HTML/CSS)
    Keeping ONE renderer guarantees WYSIWYG between design and print.
--}}
<div class="report-canvas" style="
    position: relative;
    width: {{ $template->canvas_width }}px;
    height: {{ $template->canvas_height }}px;
    background: {{ $template->background['color'] ?? '#ffffff' }};
    font-family: 'Helvetica Neue', Arial, sans-serif;
    overflow: hidden;
">
    @if(!empty($template->background['image']))
        <img src="{{ $template->background['image'] }}" style="
            position:absolute; inset:0; width:100%; height:100%; object-fit:cover;
            opacity: {{ $template->background['opacity'] ?? 1 }};
        ">
    @endif

    @foreach($elements as $el)
        @php
            $props = $el['props'] ?? [];
            $style = sprintf(
                'position:absolute; left:%dpx; top:%dpx; width:%dpx; height:%dpx; transform: rotate(%ddeg); z-index:%d; text-align:%s;',
                $el['x'] ?? 0, $el['y'] ?? 0, $el['w'] ?? 100, $el['h'] ?? 30, $el['rotation'] ?? 0, $el['zIndex'] ?? 1, $el['align'] ?? 'left'
            );
        @endphp

        <div class="rc-el rc-el--{{ $el['type'] }}" style="{{ $style }}" data-el-id="{{ $el['id'] ?? '' }}">
            @switch($el['type'])

                @case('logo')
                    @php $src = $data[$props['slot'] ?? 'logo_primary'] ?? null; @endphp
                    @if($src)
                        <img src="{{ $src }}" style="
                            width:100%; height:100%; object-fit:contain;
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
                        font-family:{{ $props['fontFamily'] ?? 'inherit' }};
                        line-height:1.35;
                    ">{!! \App\Support\MergeTags::resolve($props['content'] ?? '', $data) !!}</div>
                    @break

                @case('student_field')
                    @php
                        $val = data_get($data, 'student.' . ($props['field'] ?? ''), '');
                        $label = $props['label'] ?? null;
                    @endphp
                    <div style="font-size:{{ $props['fontSize'] ?? 13 }}px;">
                        @if($label)<strong>{{ $label }}:</strong>@endif {{ $val }}
                    </div>
                    @break

                @case('student_photo')
                    <img src="{{ data_get($data, 'student.photo_url') }}" style="
                        width:100%; height:100%; object-fit:cover;
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
                                    <th style="padding:4px 8px; border:1px solid #ccc; text-align:left; text-transform:capitalize;">{{ str_replace('_',' ',$col) }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($data['subjects'] ?? [] as $i => $subject)
                                <tr style="{{ !empty($props['zebra']) && $i % 2 ? 'background:#fafafa;' : '' }}">
                                    @foreach($props['columns'] ?? ['name','score','grade','remark'] as $col)
                                        <td style="padding:4px 8px; border:1px solid #ccc;">{{ $subject[$col] ?? '' }}</td>
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
                    <div style="height:100%; display:flex; flex-direction:column; justify-content:flex-end;">
                        <div style="border-top:1px solid #333; margin-bottom:2px;"></div>
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
                    <div style="width:100%; height:100%; background:{{ $props['color'] ?? '#ccc' }};"></div>
                    @break

                @case('shape')
                    <div style="
                        width:100%; height:100%;
                        background:{{ $props['fill'] ?? 'transparent' }};
                        border:{{ isset($props['borderColor']) ? '1px solid '.$props['borderColor'] : 'none' }};
                        border-radius:{{ $props['borderRadius'] ?? 0 }}px;
                    "></div>
                    @break

                @case('watermark')
                    <div style="opacity:{{ $props['opacity'] ?? 0.08 }}; font-size:64px; text-align:center;">
                        {{ $props['content'] ?? '' }}
                    </div>
                    @break

                @case('qr_code')
                    @php
                        $qrValue = !empty($props['dataField'])
                            ? \App\Support\MergeTags::resolve($props['dataField'], $data)
                            : ($data['qr_text'] ?? \App\Support\MergeTags::resolve('@{{student.admission_no}}', $data));
                    @endphp
                    @if($qrValue !== '')
                        <img src="data:image/svg+xml;base64,{{ base64_encode(\SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')->size(200)->generate($qrValue)) }}" style="width:100%; height:100%;">
                    @endif
                    @break

                @case('custom_html')
                    {!! $props['html'] ?? '' !!}
                    @break

            @endswitch
        </div>
    @endforeach
</div>