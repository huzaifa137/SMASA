{{-- resources/views/master-logic/partials/_category-sidebar.blade.php
     Expects: $selected (collection of master_codes), $mc_id (current selected mc_id|""), $code_totals (keyed by master_codes.id) --}}
<div class="mdx-sidebar">
    <div class="mdx-sidebar-head">
        <div class="mdx-panel-label">Categories</div>
        <div class="mdx-panel-title">{{ $selected->count() }} Master Codes</div>
    </div>
    <div class="mdx-sidebar-list">
        @forelse ($selected as $item)
            <a href="{{ url('master-data/master-code-list/' . $item->mc_id) }}"
                class="mdx-sidebar-item {{ $item->mc_id == $mc_id ? 'active' : '' }}">
                <span class="name"><i class="fa fa-circle"></i>{{ $item->mc_name }}</span>
                <span class="mdx-sidebar-badge">{{ @$code_totals[$item->id]->total ?? 0 }}</span>
            </a>
        @empty
            <div class="text-center py-5 px-3 text-muted" style="font-size:.8rem;">
                No master codes yet.
            </div>
        @endforelse
    </div>
</div>