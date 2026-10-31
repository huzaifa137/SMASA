<?php use App\Http\Controllers\Helper; ?>
@extends('layouts-side-bar.master')

@section('css')
    @include('Examination.open-assessments._styles')
@endsection

@section('content')
    <div class="oa-hero">
        <span class="hero-badge"><i class="fas fa-sliders me-1"></i> Administrator</span>
        <div class="hero-title">Set Assessment Targets</div>
        <div class="hero-subtitle">
            Decide how many assessments teachers must submit each term — in total, or per kind (Activities of Integration, Projects, Quizzes ...),
            for every class, one class, or one subject. Teachers see their target on their own page and you track progress on the progress page.
            Where two targets overlap, the more specific one wins (subject, then class, then whole school).
        </div>
        <div class="hero-links mt-2">
    <a href="{{ route('open-assessments.compliance', ['academic_year' => $year, 'term' => $term]) }}"
       class="btn btn-primary">
        <i class="fas fa-bullseye me-1"></i>
        Teacher Progress
    </a>

    <a href="{{ route('open-assessments.overview') }}"
       class="btn btn-primary">
        <i class="fas fa-user-check me-1"></i>
        Assessments by Teacher
    </a>
</div>
    </div>

    <div class="oa-card">
        <div class="oa-card-body">
            <form method="GET" action="{{ route('open-assessments.expectations') }}" class="oa-filters">
                <div>
                    <label class="oa-label">Term</label>
                    <select name="term" class="oa-input" onchange="this.form.submit()">
                        @foreach($terms as $t)<option value="{{ $t }}" {{ $term === $t ? 'selected' : '' }}>{{ \App\Support\Term::label($t) }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <label class="oa-label">Year</label>
                    <select name="academic_year" class="oa-input" onchange="this.form.submit()">
                        @foreach($years as $y)<option value="{{ $y }}" {{ (string) $year === (string) $y ? 'selected' : '' }}>{{ $y }}</option>@endforeach
                    </select>
                </div>
            </form>
        </div>
    </div>

    @if($canManage)
        <div class="oa-card">
            <div class="oa-card-head"><span id="formTitle"><i class="fas fa-plus-circle me-2"></i> New target for {{ \App\Support\Term::label($term) }} {{ $year }}</span></div>
            <div class="oa-card-body">
                <div class="oa-grid">
                    <div>
                        <label class="oa-label">Class</label>
                        <select id="fClass" class="oa-input">
                            <option value="">All classes</option>
                            @foreach($classNames as $id => $name)<option value="{{ $id }}">{{ $name }}</option>@endforeach
                        </select>
                    </div>
                    <div>
                        <label class="oa-label">Subject</label>
                        <select id="fSubject" class="oa-input">
                            <option value="">All subjects</option>
                            @foreach($subjectOptions as $value => $name)<option value="{{ $value }}">{{ $name }}</option>@endforeach
                        </select>
                    </div>
                    <div>
                        <label class="oa-label">Assessment type *</label>
                        <select id="fTypeKey" class="oa-input">
                            @foreach($typeOptions as $key => $label)<option value="{{ $key }}">{{ $label }}</option>@endforeach
                        </select>
                    </div>
                    <div>
                        <label class="oa-label">How many are expected? *</label>
                        <input type="number" id="fCount" class="oa-input" min="1" max="50" step="1" placeholder="e.g. 2">
                    </div>
                    <div>
                        <label class="oa-label">Due by <span class="oa-sub">(optional)</span></label>
                        <input type="date" id="fDue" class="oa-input">
                    </div>
                </div>
                <div class="mt-3">
                    <label class="oa-label">Note for teachers (optional)</label>
                    <input type="text" id="fNotes" class="oa-input" maxlength="255" placeholder="e.g. Projects must be marked out of 20">
                </div>
                <div class="mt-3 d-flex gap-2">
                    <button type="button" class="oa-btn" id="saveBtn"><i class="fas fa-check"></i> <span id="saveLabel">Save target</span></button>
                    <button type="button" class="oa-btn light" id="cancelEditBtn" style="display:none;">Cancel edit</button>
                </div>
            </div>
        </div>
    @endif

    <div class="oa-card">
        <div class="oa-card-head">
            <span><i class="fas fa-list me-2"></i> Targets for {{ \App\Support\Term::label($term) }} {{ $year }} ({{ $rows->count() }})</span>
            <span class="oa-sub">“Applies to” counts the Senior 1-4 class-subjects each target covers.</span>
        </div>
        <div class="table-responsive">
            <table class="table oa-table">
                <thead>
                    <tr><th>Class</th><th>Subject</th><th>Assessment type</th><th style="width:100px;">Expected</th><th>Due</th><th>Applies to</th><th>Note</th>@if($canManage)<th style="width:110px;"></th>@endif</tr>
                </thead>
                <tbody>
                    @forelse($rows as $r)
                        <tr data-id="{{ $r->id }}" data-class="{{ $r->class_id }}" data-subject="{{ $r->subject_value }}" data-type="{{ $r->type_key }}"
                            data-count="{{ $r->required }}" data-due="{{ $r->due }}" data-notes="{{ $r->notes }}">
                            <td>{{ $r->class_name }}</td>
                            <td class="fw-bold">{{ $r->subject_name }}</td>
                            <td><span class="oa-pill">{{ $r->type_label }}</span></td>
                            <td class="fw-bold">{{ $r->required }}</td>
                            <td>{{ $r->due_text ?? '—' }}</td>
                            <td><span class="oa-pill {{ $r->coverage ? 'grey' : 'amber' }}">{{ $r->coverage }} subject(s)</span></td>
                            <td class="oa-sub">{{ $r->notes }}</td>
                            @if($canManage)
                                <td class="text-end">
                                    <button type="button" class="oa-btn sm light edit-btn"><i class="fas fa-pen"></i></button>
                                    <button type="button" class="oa-btn sm danger delete-btn"><i class="fas fa-trash"></i></button>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr><td colspan="8"><div class="oa-empty"><i class="fas fa-bullseye"></i>No targets for this term yet.@if($canManage) Add one above, or copy another term's targets below.@endif</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($canManage && $otherPeriods->isNotEmpty())
        <div class="oa-card">
            <div class="oa-card-head"><span><i class="fas fa-copy me-2"></i> Copy targets from another term</span></div>
            <div class="oa-card-body">
                <div class="oa-filters">
                    <div>
                        <label class="oa-label">Copy from</label>
                        <select id="copyFrom" class="oa-input">
                            @foreach($otherPeriods as $p)<option value="{{ $p->academic_year }}|{{ $p->term }}">{{ \App\Support\Term::label($p->term) }} {{ $p->academic_year }} ({{ $p->n }} target(s))</option>@endforeach
                        </select>
                    </div>
                    <div style="min-width:auto;"><button type="button" class="oa-btn" id="copyBtn"><i class="fas fa-copy"></i> Copy into {{ \App\Support\Term::label($term) }} {{ $year }}</button></div>
                </div>
                <div class="oa-sub mt-2">Existing targets are kept and due dates are not copied.</div>
            </div>
        </div>
    @endif
    </div></div></div></div>
@endsection

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        (function () {
            const CSRF = @json(csrf_token());
            const URLS = {
                store: @json(route('open-assessments.expectations.store')),
                copy: @json(route('open-assessments.expectations.copy')),
                base: @json(url('examinations/open-assessments/targets')),
            };
            const YEAR = @json((string) $year), TERM = @json($term);
            const $ = id => document.getElementById(id);
            let editingId = null;

            async function send(url, method, body) {
                const res = await fetch(url, {
                    method,
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
                    body: body ? JSON.stringify(body) : undefined,
                });
                const data = await res.json().catch(() => ({}));
                if (!res.ok || !data.success) {
                    const first = data.errors ? Object.values(data.errors)[0][0] : null;
                    throw new Error(first || data.message || 'Something went wrong.');
                }
                return data;
            }

            function resetForm() {
                editingId = null;
                $('fClass').value = ''; $('fSubject').value = ''; $('fTypeKey').selectedIndex = 0;
                $('fCount').value = ''; $('fDue').value = ''; $('fNotes').value = '';
                $('saveLabel').textContent = 'Save target';
                $('formTitle').innerHTML = '<i class="fas fa-plus-circle me-2"></i> New target';
                $('cancelEditBtn').style.display = 'none';
            }

            $('saveBtn')?.addEventListener('click', async function () {
                if (!$('fCount').value || parseInt($('fCount').value, 10) < 1) {
                    return Swal.fire('How many?', 'Enter how many assessments are expected (at least 1).', 'warning');
                }
                const body = {
                    academic_year: YEAR, term: TERM,
                    class_id: $('fClass').value || null, subject: $('fSubject').value || null,
                    type_key: $('fTypeKey').value, required_count: $('fCount').value,
                    due_date: $('fDue').value || null, notes: $('fNotes').value.trim() || null,
                };
                this.disabled = true;
                try {
                    await send(editingId ? URLS.base + '/' + editingId : URLS.store, editingId ? 'PUT' : 'POST', body);
                    window.location.reload();
                } catch (e) {
                    Swal.fire('Could not save', e.message, 'error');
                    this.disabled = false;
                }
            });

            $('cancelEditBtn')?.addEventListener('click', resetForm);

            document.querySelectorAll('.edit-btn').forEach(btn => btn.addEventListener('click', function () {
                const d = this.closest('tr').dataset;
                editingId = d.id;
                $('fClass').value = d.class || ''; $('fSubject').value = d.subject || '';
                $('fTypeKey').value = d.type; $('fCount').value = d.count;
                $('fDue').value = d.due || ''; $('fNotes').value = d.notes || '';
                $('saveLabel').textContent = 'Save changes';
                $('formTitle').innerHTML = '<i class="fas fa-pen me-2"></i> Edit target';
                $('cancelEditBtn').style.display = 'inline-flex';
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }));

            document.querySelectorAll('.delete-btn').forEach(btn => btn.addEventListener('click', function () {
                const id = this.closest('tr').dataset.id;
                Swal.fire({ title: 'Remove this target?', text: 'Teachers will no longer be measured against it. Their assessments are not affected.', icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc3545', confirmButtonText: 'Remove' })
                    .then(async r => {
                        if (!r.isConfirmed) return;
                        try { await send(URLS.base + '/' + id, 'DELETE'); window.location.reload(); }
                        catch (e) { Swal.fire('Could not remove', e.message, 'error'); }
                    });
            }));

            $('copyBtn')?.addEventListener('click', async function () {
                const [fromYear, fromTerm] = $('copyFrom').value.split('|');
                this.disabled = true;
                try {
                    const data = await send(URLS.copy, 'POST', { from_year: fromYear, from_term: fromTerm, to_year: YEAR, to_term: TERM });
                    await Swal.fire('Done', data.message, 'success');
                    window.location.reload();
                } catch (e) {
                    Swal.fire('Could not copy', e.message, 'error');
                    this.disabled = false;
                }
            });
        })();
    </script>
@endsection
