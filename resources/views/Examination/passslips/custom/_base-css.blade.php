{{-- Shared reset + print rules for every custom design. Expects $page (pageW/pageH). --}}
<style>
    *{box-sizing:border-box}
    html,body{margin:0;padding:0}
    body{background:#e5e7eb;-webkit-print-color-adjust:exact;print-color-adjust:exact}
    .crc-toolbar{position:sticky;top:0;z-index:50;display:flex;gap:10px;align-items:center;padding:10px 16px;background:#0f172a;color:#fff;font:600 14px/1.2 system-ui,sans-serif}
    .crc-toolbar a,.crc-toolbar button{border:0;border-radius:6px;padding:8px 14px;font:600 13px system-ui,sans-serif;cursor:pointer;text-decoration:none;color:#fff;background:#334155}
    .crc-toolbar .crc-print{background:#2563eb}
    .crc-toolbar .crc-sub{opacity:.75;font-weight:500;margin-left:auto}
    .crc-sheet{width:{{ $page->pageW }};min-height:{{ $page->pageH }};margin:16px auto;background:#fff;box-shadow:0 4px 18px rgba(0,0,0,.18);position:relative;page-break-after:always;break-after:page}
    .crc-sheet:last-child{page-break-after:auto;break-after:auto}
    @page{size:{{ $page->pageW }} {{ $page->pageH }};margin:0}
    @media print{
        body{background:#fff}
        .crc-toolbar{display:none!important}
        .crc-sheet{margin:0;box-shadow:none}
    }
    @if($embed ?? false)
        .crc-toolbar{display:none!important}
        .crc-sheet{margin:8px auto}
    @endif
</style>
