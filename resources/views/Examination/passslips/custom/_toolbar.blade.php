{{-- Screen-only toolbar (hidden when printing and inside the preview iframe). --}}
<div class="crc-toolbar">
    @php $back = $backUrl ?? route('examination.passslips.index', $exam->id); @endphp
    <a href="{{ $back }}">← Back</a>
    <button type="button" class="crc-print" onclick="window.print()">Print</button>
    <span class="crc-sub">{{ $exam->exam_name }} · {{ $toolbarSubtitle }} · {{ count($reports) }} report{{ count($reports) === 1 ? '' : 's' }}</span>
</div>
