@if($students->total() > 10)
    <div style="display:flex;align-items:center;justify-content:space-between;margin-top:.85rem;flex-wrap:wrap;gap:.5rem;">
        <span style="font-size:.78rem;color:var(--t3);">
            Showing {{ $students->firstItem() }}–{{ $students->lastItem() }} of {{ $students->total() }}
        </span>
        {{ $students->onEachSide(1)->links('pagination::bootstrap-5') }}
    </div>
@endif