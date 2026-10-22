<?php use App\Http\Controllers\Helper; ?>
@extends('layouts-side-bar.master')

@section('css')
    @include('Examination.open-assessments._styles')
@endsection

@section('content')
    @php
        $canEnter = \App\Helpers\PermissionHelper::canFeature('enter_open_assessment_marks') && $students->count();
        $max = (float) $assessment->max_marks;
    @endphp

    <div class="oa-hero">
        <span class="hero-badge"><i class="fas fa-pen-to-square me-1"></i> Marks Entry</span>
        <div class="hero-title">{{ $assessment->title }}</div>
        <div class="hero-subtitle">
            {{ $subjectName }} — {{ $className }}@if($streamLabel) · {{ $streamLabel }}@endif · {{ \App\Support\Term::label($assessment->term) }} {{ $assessment->academic_year }}
            · marked out of <strong style="color:#fff;">{{ rtrim(rtrim(number_format($max, 2), '0'), '.') }}</strong>
        </div>
        <div class="hero-links mt-2">
            <a href="{{ route('open-assessments.hub', $classSubject->id) }}"><i class="fas fa-arrow-left me-1"></i> All assessments for this subject</a>
        </div>
    </div>

    <div class="oa-card">
        <div class="oa-card-head">
            <span><i class="fas fa-users me-2"></i> Students ({{ $students->count() }})</span>
            @if($siblings->count() > 1)
                <select id="switcher" class="oa-input" style="width:auto; min-width:220px;">
                    @foreach($siblings as $s)
                        <option value="{{ route('open-assessments.marks-entry', ['classSubjectId' => $classSubject->id, 'assessmentId' => $s->id]) }}" {{ $s->id === $assessment->id ? 'selected' : '' }}>
                            {{ $s->title }} · {{ \App\Support\Term::label($s->term) }} {{ $s->academic_year }} (out of {{ rtrim(rtrim(number_format($s->max_marks, 2), '0'), '.') }})
                        </option>
                    @endforeach
                </select>
            @endif
        </div>
        <div class="table-responsive">
            <table class="table oa-table">
                <thead>
                    <tr>
                        <th style="width:44px;">#</th>
                        <th>Student</th>
                        <th style="width:140px;">Marks / {{ rtrim(rtrim(number_format($max, 2), '0'), '.') }}</th>
                        <th style="width:100px;">Percent</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($students as $i => $student)
                        @php $m = $existingMarks[$student->id] ?? null; $v = $m?->marks_obtained; @endphp
                        <tr data-student-id="{{ $student->id }}">
                            <td class="oa-sub">{{ $i + 1 }}</td>
                            <td>
                                <span class="oa-avatar">{{ strtoupper(substr($student->lastname, 0, 1) . substr($student->firstname, 0, 1)) }}</span>
                                <span class="student-name">{{ $student->lastname }} {{ $student->firstname }}</span>
                            </td>
                            <td>
                                <input type="number" class="oa-marks" min="0" max="{{ $max }}" step="0.5"
                                    value="{{ $v !== null ? $v + 0 : '' }}" placeholder="—" {{ $canEnter ? '' : 'disabled' }}>
                            </td>
                            <td><span class="oa-pill grey pct">{{ ($v !== null && $max > 0) ? round(($v / $max) * 100) . '%' : '—' }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="4"><div class="oa-empty"><i class="fas fa-users"></i>{{ $subjectSelectionNote ?? 'No students found in this class-stream.' }}</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
     </div>
    </div>

    @if($canEnter)
        <button type="button" id="saveMarksBtn" class="oa-fab"><i class="fas fa-save me-2"></i> Save All Marks</button>
    @endif
@endsection

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        (function () {
            const CSRF = @json(csrf_token());
            const SAVE_URL = @json(route('open-assessments.marks.save', $assessment->id));
            const MAX = {{ $max }};

            document.getElementById('switcher')?.addEventListener('change', function () { window.location.href = this.value; });

            const isBad = v => v !== '' && (isNaN(parseFloat(v)) || parseFloat(v) < 0 || parseFloat(v) > MAX);

            document.querySelectorAll('.oa-marks').forEach(input => input.addEventListener('input', function () {
                const bad = isBad(this.value);
                this.classList.toggle('invalid', bad);
                this.title = bad ? 'Enter a mark between 0 and ' + MAX + '.' : '';
                this.closest('tr').querySelector('.pct').textContent =
                    (this.value === '' || bad) ? '—' : Math.round(parseFloat(this.value) / MAX * 100) + '%';
            }));

            document.getElementById('saveMarksBtn')?.addEventListener('click', function () {
                const rows = [...document.querySelectorAll('tr[data-student-id]')];
                const bad = rows.filter(r => isBad(r.querySelector('.oa-marks').value));

                if (bad.length) {
                    return Swal.fire({
                        icon: 'error', title: 'Marks out of range',
                        html: 'These marks must be between 0 and <strong>' + MAX + '</strong>:<ul style="text-align:left;font-size:.85rem;">'
                            + bad.map(r => '<li>' + r.querySelector('.student-name').textContent.trim() + '</li>').join('') + '</ul>',
                        confirmButtonColor: '#2C29CA',
                    });
                }

                const marks = rows.map(r => {
                    const v = r.querySelector('.oa-marks').value;
                    return { student_id: r.dataset.studentId, marks: v !== '' ? v : null };
                });
                const entered = marks.filter(m => m.marks !== null).length;

                Swal.fire({
                    title: 'Save marks?', icon: 'question', showCancelButton: true,
                    html: 'You are saving marks for <strong>' + entered + '</strong> of <strong>' + marks.length + '</strong> student(s).',
                    confirmButtonColor: '#2C29CA', confirmButtonText: 'Yes, save',
                }).then(async r => {
                    if (!r.isConfirmed) return;
                    const btn = this; btn.disabled = true;
                    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving...';
                    try {
                        const res = await fetch(SAVE_URL, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
                            body: JSON.stringify({ marks }),
                        });
                        const data = await res.json().catch(() => ({}));
                        if (!res.ok || !data.success) {
                            const first = data.errors ? Object.values(data.errors)[0][0] : null;
                            throw new Error(first || data.message || 'Failed to save marks.');
                        }
                        Swal.fire({ icon: 'success', title: 'Marks saved', text: data.message, confirmButtonColor: '#2C29CA' });
                    } catch (e) {
                        Swal.fire('Error', e.message, 'error');
                    } finally {
                        btn.disabled = false; btn.innerHTML = '<i class="fas fa-save me-2"></i> Save All Marks';
                    }
                });
            });
        })();
    </script>
@endsection
