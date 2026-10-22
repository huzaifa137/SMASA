<?php use App\Http\Controllers\Helper; ?>
@extends('layouts-side-bar.master')

@section('css')
    @include('Examination.open-assessments._styles')
@endsection

@section('content')
    @php
        $canManage = \App\Helpers\PermissionHelper::canFeature('manage_open_assessments');
        $canDelete = \App\Helpers\PermissionHelper::canFeature('delete_open_assessments');
        $baseUrl = route('open-assessments.store', $classSubject->id);
    @endphp

    <div class="oa-hero">
        <span class="hero-badge"><i class="fas fa-list-check me-1"></i> Open Assessments</span>
        <div class="hero-title">{{ $subjectName }} — {{ $className }}@if($streamLabel) · {{ $streamLabel }}@endif</div>
        <div class="hero-subtitle">{{ $studentCount }} student(s) · create as many assessments as you like — no examination is needed.</div>
        <div class="hero-links mt-2">
            <a href="{{ route('open-assessments.index') }}"><i class="fas fa-arrow-left me-1"></i> My subjects</a>
        </div>
    </div>

    @if($canManage)
        <div class="oa-card">
            <div class="oa-card-head"><span id="formTitle"><i class="fas fa-plus-circle me-2"></i> New assessment</span></div>
            <div class="oa-card-body">
                <div class="oa-grid">
                    <div style="grid-column: span 2;">
                        <label class="oa-label">Title *</label>
                        <input type="text" id="fTitle" class="oa-input" maxlength="150" placeholder="e.g. Quiz 1, Mid-term test, Group project">
                    </div>
                    <div>
                        <label class="oa-label">Type</label>
                        <select id="fCategory" class="oa-input">
                            <option value="">— Optional —</option>
                            @foreach($categories as $c)<option value="{{ $c }}">{{ $c }}</option>@endforeach
                        </select>
                    </div>
                    <div>
                        <label class="oa-label">Marked out of *</label>
                        <input type="number" id="fMax" class="oa-input" min="0.01" max="1000" step="0.5" placeholder="e.g. 20">
                    </div>
                    <div>
                        <label class="oa-label">Term *</label>
                        <select id="fTerm" class="oa-input">
                            <option value="">— Choose —</option>
                            @foreach($terms as $t)<option value="{{ $t }}" {{ $t === $defaultTerm ? 'selected' : '' }}>{{ \App\Support\Term::label($t) }}</option>@endforeach
                        </select>
                    </div>
                    <div>
                        <label class="oa-label">Year *</label>
                        <select id="fYear" class="oa-input">
                            @foreach($years as $y)<option value="{{ $y }}" {{ (string) $y === (string) $defaultYear ? 'selected' : '' }}>{{ $y }}</option>@endforeach
                        </select>
                    </div>
                    <div>
                        <label class="oa-label">Date</label>
                        <input type="date" id="fDate" class="oa-input">
                    </div>
                </div>
                <div class="mt-3">
                    <label class="oa-label">Notes (optional)</label>
                    <input type="text" id="fDesc" class="oa-input" maxlength="1000" placeholder="What does this assessment cover?">
                </div>
                <div class="mt-3 d-flex gap-2">
                    <button type="button" class="oa-btn" id="saveBtn"><i class="fas fa-check"></i> <span id="saveLabel">Create assessment</span></button>
                    <button type="button" class="oa-btn light" id="cancelEditBtn" style="display:none;">Cancel edit</button>
                </div>
            </div>
        </div>
    @endif

    <div class="oa-card">
        <div class="oa-card-head">
            <span><i class="fas fa-list me-2"></i> Assessments ({{ $assessments->count() }})</span>
            <span class="oa-sub">The administrator decides later which of these count on the report card.</span>
        </div>
        <div class="table-responsive">
            <table class="table oa-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Type</th>
                        <th>Term</th>
                        <th>Date</th>
                        <th style="width:90px;">Out of</th>
                        <th style="width:170px;">Marks entered</th>
                        <th style="width:90px;">Class avg</th>
                        <th>Created by</th>
                        <th style="width:240px;"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assessments as $a)
                        @php $pct = $studentCount > 0 ? min(100, round(($a->entered_count / $studentCount) * 100)) : 0; @endphp
                        <tr data-id="{{ $a->id }}"
                            data-title="{{ $a->title }}" data-category="{{ $a->category }}" data-max="{{ $a->max_marks }}"
                            data-term="{{ $a->term }}" data-year="{{ $a->academic_year }}"
                            data-date="{{ optional($a->assessment_date)->format('Y-m-d') }}" data-desc="{{ $a->description }}">
                            <td class="fw-bold">{{ $a->title }}@if($a->description)<div class="oa-sub">{{ \Illuminate\Support\Str::limit($a->description, 70) }}</div>@endif</td>
                            <td>@if($a->category)<span class="oa-pill">{{ $a->category }}</span>@else<span class="oa-sub">—</span>@endif</td>
                            <td>{{ \App\Support\Term::label($a->term) }} {{ $a->academic_year }}</td>
                            <td>{{ optional($a->assessment_date)->format('d M Y') ?? '—' }}</td>
                            <td>{{ rtrim(rtrim(number_format($a->max_marks, 2), '0'), '.') }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="oa-bar flex-grow-1"><span style="width: {{ $pct }}%;"></span></div>
                                    <span class="oa-sub">{{ $a->entered_count }}/{{ $studentCount }}</span>
                                </div>
                            </td>
                            <td>{{ $a->average_percent !== null ? $a->average_percent . '%' : '—' }}</td>
                            <td class="oa-sub">{{ $a->creator_name }}
                                @if(!empty($a->card_names))<div><span class="oa-pill green" title="Used on: {{ implode(', ', $a->card_names) }}"><i class="fas fa-lock me-1"></i> On report card</span></div>@endif
                            </td>
                            <td class="text-end">
                                <a class="oa-btn sm" href="{{ route('open-assessments.marks-entry', ['classSubjectId' => $classSubject->id, 'assessmentId' => $a->id]) }}">
                                    <i class="fas fa-pen-to-square"></i> Marks
                                </a>
                                @if($canManage)<button type="button" class="oa-btn sm light edit-btn"><i class="fas fa-pen"></i></button>@endif
                                @if($canDelete && empty($a->card_names))<button type="button" class="oa-btn sm danger delete-btn" data-has-marks="{{ $a->entered_count }}"><i class="fas fa-trash"></i></button>@endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9"><div class="oa-empty"><i class="fas fa-clipboard"></i>No assessments yet — create your first one above, then enter marks for it.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    </div>
    </div>
    </div>
@endsection

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        (function () {
            const CSRF = @json(csrf_token());
            const BASE = @json($baseUrl);
            let editingId = null;

            const $ = id => document.getElementById(id);
            const fields = () => ({
                title: $('fTitle')?.value.trim(), category: $('fCategory')?.value || null,
                max_marks: $('fMax')?.value, assessment_date: $('fDate')?.value || null,
                term: $('fTerm')?.value, academic_year: $('fYear')?.value,
                description: $('fDesc')?.value.trim() || null,
            });

            function resetForm() {
                editingId = null;
                ['fTitle', 'fMax', 'fDate', 'fDesc'].forEach(i => { if ($(i)) $(i).value = ''; });
                if ($('fCategory')) $('fCategory').value = '';
                if ($('saveLabel')) $('saveLabel').textContent = 'Create assessment';
                if ($('formTitle')) $('formTitle').innerHTML = '<i class="fas fa-plus-circle me-2"></i> New assessment';
                if ($('cancelEditBtn')) $('cancelEditBtn').style.display = 'none';
            }

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

            $('saveBtn')?.addEventListener('click', async function () {
                const f = fields();
                if (!f.title) return Swal.fire('Title needed', 'Give the assessment a title.', 'warning');
                if (!f.term) return Swal.fire('Term needed', 'Choose the term this assessment belongs to.', 'warning');
                if (!f.max_marks || parseFloat(f.max_marks) <= 0) return Swal.fire('Marked out of', 'Enter the maximum marks (greater than 0).', 'warning');

                this.disabled = true;
                try {
                    await send(editingId ? BASE + '/' + editingId : BASE, editingId ? 'PUT' : 'POST', f);
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
                $('fTitle').value = d.title; $('fCategory').value = d.category || '';
                $('fTerm').value = d.term || ''; $('fYear').value = d.year || $('fYear').value;
                $('fMax').value = d.max; $('fDate').value = d.date || ''; $('fDesc').value = d.desc || '';
                $('saveLabel').textContent = 'Save changes';
                $('formTitle').innerHTML = '<i class="fas fa-pen me-2"></i> Edit assessment';
                $('cancelEditBtn').style.display = 'inline-flex';
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }));

            document.querySelectorAll('.delete-btn').forEach(btn => btn.addEventListener('click', function () {
                const row = this.closest('tr');
                const marks = parseInt(this.dataset.hasMarks || '0', 10);
                Swal.fire({
                    title: 'Delete “' + row.dataset.title + '”?',
                    text: marks ? 'The ' + marks + ' mark(s) already entered for it will be deleted too.' : 'This cannot be undone.',
                    icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc3545', confirmButtonText: 'Delete',
                }).then(async r => {
                    if (!r.isConfirmed) return;
                    try { await send(BASE + '/' + row.dataset.id, 'DELETE'); row.remove(); window.location.reload(); }
                    catch (e) { Swal.fire('Could not delete', e.message, 'error'); }
                });
            }));
        })();
    </script>
@endsection
