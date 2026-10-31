{{-- Teacher's view of the school's targets for one class-subject and term. Needs: $targetLines, $targetTally, $targetYear, $targetTerm, $terms, $years, $classSubject, $canManage --}}
@php
    $pillFor = ['complete' => 'green', 'marks_pending' => 'amber', 'in_progress' => 'amber', 'not_started' => 'grey', 'overdue' => 'amber'];
    $nlscKeys = array_keys($nlscTypes);
@endphp
<div class="oa-card">
    <div class="oa-card-head">
        <span><i class="fas fa-bullseye me-2"></i> What is expected this term</span>
        <form method="GET" action="{{ route('open-assessments.hub', $classSubject->id) }}" class="d-flex gap-2">
            <select name="term" class="oa-input" style="width:auto;" onchange="this.form.submit()">
                @foreach($terms as $t)<option value="{{ $t }}" {{ $t === $targetTerm ? 'selected' : '' }}>{{ \App\Support\Term::label($t) }}</option>@endforeach
            </select>
            <select name="academic_year" class="oa-input" style="width:auto;" onchange="this.form.submit()">
                @foreach($years as $y)<option value="{{ $y }}" {{ (string) $y === (string) $targetYear ? 'selected' : '' }}>{{ $y }}</option>@endforeach
            </select>
        </form>
    </div>
    @if($targetLines->isEmpty())
        <div class="oa-card-body"><span class="oa-sub">The school has not set a target for this subject in {{ \App\Support\Term::label($targetTerm) }} {{ $targetYear }}. You can still create as many assessments as you like.</span></div>
    @else
        <div class="oa-card-body" style="padding-bottom:.25rem;">
            <span class="oa-pill {{ $targetTally['remaining'] > 0 ? 'amber' : 'green' }}">
                {{ $targetTally['remaining'] > 0 ? $targetTally['remaining'] . ' assessment(s) still to create' : 'Everything required has been created' }}
            </span>
            <span class="oa-sub ms-2">{{ $targetTally['met'] }} of {{ $targetTally['targets'] }} target(s) fully met</span>
        </div>
        <div class="table-responsive">
            <table class="table oa-table">
                <thead><tr><th>Assessment type</th><th style="width:200px;">Created</th><th>Fully marked</th><th>Still to create</th><th>Due</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    @foreach($targetLines as $l)
                        @php $formValue = in_array($l->type_key, $nlscKeys, true) ? $l->type_key : (str_starts_with($l->type_key, 'cat:') ? substr($l->type_key, 4) : ''); @endphp
                        <tr>
                            <td class="fw-bold">{{ $l->label }}@if($l->notes)<div class="oa-sub">{{ $l->notes }}</div>@endif</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="oa-bar flex-grow-1"><span style="width: {{ $l->percent }}%;"></span></div>
                                    <span class="oa-sub">{{ $l->created }}/{{ $l->required }}</span>
                                </div>
                            </td>
                            <td>{{ $l->marked }}/{{ $l->created }}</td>
                            <td>{{ $l->remaining }}</td>
                            <td>{{ $l->due_text ?? '—' }}</td>
                            <td><span class="oa-pill {{ $pillFor[$l->status] ?? 'grey' }}">{{ $l->status_label }}</span></td>
                            <td class="text-end">
                                @if($canManage && $l->remaining > 0)
                                    <button type="button" class="oa-btn sm light target-create" data-type="{{ $formValue }}" data-term="{{ $l->term }}" data-year="{{ $l->year }}"><i class="fas fa-plus"></i> Create</button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.target-create').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var type = document.getElementById('fType');
                if (!type) return;
                type.value = this.dataset.type || '';
                type.dispatchEvent(new Event('change'));
                if (document.getElementById('fTerm')) document.getElementById('fTerm').value = this.dataset.term;
                if (document.getElementById('fYear')) document.getElementById('fYear').value = this.dataset.year;
                type.scrollIntoView({ behavior: 'smooth', block: 'center' });
            });
        });
    });
</script>
