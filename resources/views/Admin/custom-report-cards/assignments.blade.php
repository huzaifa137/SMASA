@extends('layouts-side-bar.master')

@section('content')
<div class="container-fluid pt-4">
    <h3 class="mb-1">Custom Report Cards — School assignments</h3>
    <p class="text-muted mb-3">A school with a custom design sees it as the default in its Pass Slips designer, on every print route and in the parent portal.</p>

    @include('Admin.custom-report-cards._nav')

    @if($ready)
    <div class="crc-card">
        <input type="search" id="schoolFilter" class="form-control mb-3" placeholder="Filter schools…">
        <div class="table-responsive">
        <table class="table table-bordered" id="schoolTable">
            <thead><tr><th>School</th>@foreach($levels as $lk => $ll)<th>{{ $ll }}</th>@endforeach</tr></thead>
            <tbody>
            @foreach($schools as $school)
                <tr>
                    <td class="font-weight-bold">{{ $school->name }}</td>
                    @foreach($levels as $lk => $ll)
                        @php $a = $assignments->get($school->id . '|' . $lk); @endphp
                        <td>
                            @if($a)
                                <div class="font-weight-bold">{{ $a->template->name ?? '—' }}</div>
                                <span class="crc-badge {{ $a->is_active ? 'crc-b-on' : 'crc-b-off' }}">{{ $a->is_active ? 'Active' : 'Paused' }}</span>
                                @if($a->lock_to_custom)<span class="crc-badge crc-b-lock">Locked</span>@endif
                                <form method="POST" action="{{ route('admin.custom-report-cards.assignments.update', $a->id) }}" class="mt-2">
                                    @csrf @method('PUT')
                                    <label class="small mb-0 mr-2"><input type="checkbox" name="is_active" value="1" {{ $a->is_active ? 'checked' : '' }}> Active</label>
                                    <label class="small mb-0 mr-2"><input type="checkbox" name="lock_to_custom" value="1" {{ $a->lock_to_custom ? 'checked' : '' }}> Locked</label>
                                    <button class="btn btn-sm btn-outline-success">Save</button>
                                    <button type="submit" form="un-{{ $a->id }}" class="btn btn-sm btn-outline-danger" onclick="return confirm('Remove this assignment?')">Remove</button>
                                </form>
                                <form id="un-{{ $a->id }}" method="POST" action="{{ route('admin.custom-report-cards.assignments.delete', $a->id) }}">@csrf @method('DELETE')</form>
                            @else
                                @php $opts = $templates->where('level', $lk); @endphp
                                @if($opts->isEmpty())
                                    <span class="text-muted small">Standard design (no {{ strtolower($ll) }} custom design registered)</span>
                                @else
                                <form method="POST" action="{{ route('admin.custom-report-cards.assign') }}">
                                    @csrf
                                    <input type="hidden" name="school_id" value="{{ $school->id }}">
                                    <input type="hidden" name="level" value="{{ $lk }}">
                                    <select name="template_id" class="form-control form-control-sm mb-1" required>
                                        <option value="">Standard design</option>
                                        @foreach($opts as $o)<option value="{{ $o->id }}">{{ $o->name }}</option>@endforeach
                                    </select>
                                    <label class="small mb-1"><input type="checkbox" name="lock_to_custom" value="1" checked> Lock to custom</label>
                                    <button class="btn btn-sm btn-primary btn-block">Assign</button>
                                </form>
                                @endif
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endforeach
            </tbody>
        </table>
        </div>
    </div>
    @endif
</div>
</div>
</div> 
@endsection

@section('js')
<script>
document.getElementById('schoolFilter')?.addEventListener('input', function () {
    const q = this.value.toLowerCase();
    document.querySelectorAll('#schoolTable tbody tr').forEach(tr => {
        tr.style.display = tr.cells[0].textContent.toLowerCase().includes(q) ? '' : 'none';
    });
});
</script>
@endsection
