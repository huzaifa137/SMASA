@php $crcRoute = request()->route()->getName(); @endphp
<style>
    .crc-tabs{display:flex;gap:6px;flex-wrap:wrap;margin:0 0 18px;border-bottom:2px solid #e5e7eb}
    .crc-tabs a{padding:10px 18px;font-weight:600;color:#475569;border-radius:8px 8px 0 0;text-decoration:none}
    .crc-tabs a.active{color:#fff;background:#2C29CA}
    .crc-card{background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:20px;margin-bottom:18px}
    .crc-badge{display:inline-block;padding:2px 10px;border-radius:999px;font-size:12px;font-weight:700}
    .crc-b-nursery{background:#fef3c7;color:#92400e}.crc-b-primary{background:#dbeafe;color:#1e40af}.crc-b-secondary{background:#ede9fe;color:#5b21b6}
    .crc-b-on{background:#dcfce7;color:#166534}.crc-b-off{background:#fee2e2;color:#991b1b}.crc-b-lock{background:#e0e7ff;color:#3730a3}
</style>
<div class="crc-tabs">
    <a href="{{ route('admin.custom-report-cards.index') }}" class="{{ $crcRoute === 'admin.custom-report-cards.index' ? 'active' : '' }}">Designs</a>
    <a href="{{ route('admin.custom-report-cards.assignments') }}" class="{{ $crcRoute === 'admin.custom-report-cards.assignments' ? 'active' : '' }}">School assignments</a>
    <a href="{{ route('admin.custom-report-cards.studio') }}" class="{{ $crcRoute === 'admin.custom-report-cards.studio' ? 'active' : '' }}">Preview studio</a>
</div>

@if(!$ready)
    <div class="alert alert-warning"><strong>Run the migration first:</strong> <code>php artisan migrate</code> (creates the custom report card tables).</div>
@endif
