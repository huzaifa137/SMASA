{{--
    TEXT SIZES — shared partial (replaces the old "Text & layout scale")
    Included by every slip view inside <head>. Renders NOTHING unless the
    customise panel set a Label size and/or Value size, so untouched
    classes print exactly as before. Each template reads the variables
    with its own built-in size as the fallback, e.g.
        font-size: var(--lbl-size, 1.10rem);
--}}
@php $__ts = \App\Http\Controllers\Helper::passslipTextSizes(); @endphp
@if($__ts['lbl'] !== null || $__ts['val'] !== null)
    <style>
        :root {
            @if($__ts['lbl'] !== null) --lbl-size: {{ $__ts['lbl'] }}rem; @endif
            @if($__ts['val'] !== null) --val-size: {{ $__ts['val'] }}rem; @endif
        }
    </style>
@endif
