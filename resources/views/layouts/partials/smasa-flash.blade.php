{{-- Bridges Laravel flash messages to SMASA toasts (no alert banners, no extra clicks). --}}
@php
    $smasaFlash = [];
    foreach (['success' => 'success', 'error' => 'error', 'warning' => 'warning', 'info' => 'info', 'message' => 'info', 'status' => 'success'] as $key => $type) {
        if (session()->has($key) && is_string(session($key))) {
            $smasaFlash[] = ['type' => $type, 'message' => session($key)];
        }
    }
@endphp
@if(count($smasaFlash))
<script type="application/json" id="smasa-flash">{!! json_encode($smasaFlash, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
@endif
