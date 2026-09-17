<?php use App\Http\Controllers\Helper; use App\Helpers\PermissionHelper; ?>
@extends('layouts-side-bar.master')

@section('css')
    <style>
        .nt-hero {
            background: linear-gradient(135deg, #0a0a0f 0%, #14143a 40%, #1e1b8a 75%, #2C29CA 100%);
            border-radius: 1.75rem;
            padding: 1.5rem 2rem 2rem;
            margin-bottom: 1.5rem;
        }

        .nt-hero .hero-badge {
            background: rgba(44, 41, 202, 0.25);
            border: 1px solid rgba(107, 105, 232, 0.5);
            color: #c7c5ff;
            padding: .3rem .9rem;
            border-radius: 999px;
            font-size: .65rem;
            font-weight: 700;
            letter-spacing: .06em;
            text-transform: uppercase;
        }

        .nt-hero .hero-title { font-size: 1.4rem; font-weight: 800; color: #fff; margin: .25rem 0; }
        .nt-hero .hero-subtitle { color: rgba(255, 255, 255, .68); font-size: .85rem; max-width: 760px; }

        .nt-card {
            background: #fff;
            border-radius: 1.25rem;
            box-shadow: 0 4px 28px rgba(44, 41, 202, .08);
            overflow: hidden;
            margin-bottom: 1.5rem;
        }

        .nt-card .card-header-custom {
            padding: 1.1rem 1.6rem;
            border-bottom: 2px solid #f0eeff;
            font-weight: 700;
            font-size: .95rem;
            color: #1e1b4b;
        }

        .nt-card .card-body-custom { padding: 1.4rem 1.6rem; }

        .nt-form-label { font-weight: 600; font-size: .85rem; color: #1e1b4b; margin-bottom: .35rem; display: block; }

        .nt-form-control {
            width: 100%;
            border: 1.5px solid #e4e2ff;
            background: #f6f5ff;
            border-radius: .6rem;
            padding: .6rem .85rem;
            font-size: .875rem;
            color: #1e1b4b;
        }

        .nt-form-control:focus { outline: none; border-color: #2C29CA; box-shadow: 0 0 0 3.5px rgba(44, 41, 202, .14); }
        .nt-form-control:disabled { background: #f1f0f6; color: #a3a0c9; cursor: not-allowed; }

        .btn-nt-primary {
            background: #2C29CA; color: #fff; border: none; border-radius: .65rem;
            padding: .55rem 1.2rem; font-weight: 700; font-size: .82rem;
        }
        .btn-nt-primary:hover { background: #211ea3; color: #fff; }
        .btn-nt-primary:disabled { background: #b9b6f0; cursor: not-allowed; }

        .btn-nt-secondary {
            background: #f1f0f6; color: #1e1b4b; border: none; border-radius: .65rem;
            padding: .55rem 1.2rem; font-weight: 700; font-size: .82rem;
        }
        .btn-nt-secondary:hover { background: #e4e2ff; color: #1e1b4b; }

        .nt-table { margin-bottom: 0; font-size: .85rem; }
        .nt-table thead th {
            background: #2C29CA; color: #fff; font-size: .68rem; text-transform: uppercase;
            letter-spacing: .06em; font-weight: 700; padding: .8rem .9rem; border: none; white-space: nowrap;
        }
        .nt-table tbody td { vertical-align: middle; padding: .7rem .9rem; border-bottom: 1px solid #f0eeff; }

        .nt-info-row { margin-bottom: .55rem; font-size: .88rem; color: #1e1b4b; }
        .nt-info-row .nt-info-label { font-weight: 700; color: #3a37b8; }
        .nt-info-row ul { margin: .3rem 0 0 1.1rem; padding: 0; }
        .nt-info-row ul li { margin-bottom: .2rem; }

        .nt-meta-pill {
            display: inline-flex; align-items: center; gap: .3rem;
            background: #eef0ff; color: #3a37b8; font-weight: 700; font-size: .78rem;
            padding: .3rem .8rem; border-radius: .6rem; margin-right: .6rem;
        }

        .student-avatar {
            width: 30px; height: 30px; border-radius: 50%;
            background: linear-gradient(135deg, #2C29CA, #7c7aec); color: #fff;
            display: inline-flex; align-items: center; justify-content: center;
            font-size: .68rem; font-weight: 700; flex-shrink: 0;
        }

        .marks-input {
            width: 90px; border: 1.5px solid #e4e2ff; background: #f6f5ff;
            border-radius: .5rem; padding: .45rem .6rem; font-size: .85rem; color: #1e1b4b;
        }
        .marks-input:focus { outline: none; border-color: #2C29CA; box-shadow: 0 0 0 3px rgba(44, 41, 202, .14); }
.marks-input.invalid { border-color: #dc3545; background: #fff5f5; }
.marks-input.invalid:focus { border-color: #dc3545; box-shadow: 0 0 0 3px rgba(220, 53, 69, .15); }
        .marks-input:disabled { background: #f1f0f6; color: #a3a0c9; cursor: not-allowed; }

        .score-badge {
            display: inline-block; min-width: 56px; text-align: center;
            background: #eef0ff; color: #2C29CA; font-weight: 700; font-size: .85rem;
            padding: .4rem .6rem; border-radius: .5rem;
        }

        .max-marks-display { display: flex; align-items: center; gap: .6rem; }
        .max-marks-edit { display: none; align-items: center; gap: .5rem; }
        .max-marks-edit input { width: 110px; }

        .no-max-banner {
            background: #fff7e6; border: 1.5px solid #ffe1a8; color: #9a6b00;
            border-radius: .8rem; padding: .8rem 1.1rem; font-size: .85rem; font-weight: 600;
            margin-bottom: 1.25rem; display: flex; align-items: center; gap: .6rem;
        }

        .save-fab {
            position: fixed; bottom: 2rem; right: 2rem;
            background: linear-gradient(135deg, #2C29CA, #5351e4); color: #fff;
            border: none; border-radius: 2rem; padding: .85rem 2rem;
            font-weight: 700; font-size: .9rem; box-shadow: 0 6px 24px rgba(44, 41, 202, .35);
            z-index: 999; transition: transform .15s, box-shadow .15s;
        }
        .save-fab:hover { transform: translateY(-2px); box-shadow: 0 10px 28px rgba(44, 41, 202, .45); }
        .save-fab:active { transform: translateY(0); }
        .save-fab:disabled { background: #b9b6f0; box-shadow: none; cursor: not-allowed; }

        .empty-state { text-align: center; padding: 2rem 1rem; color: #a3a0c9; }
    </style>
@endsection

@section('content')
    <div class="side-app">
        <div class="nt-hero">
            <span class="hero-badge"><i class="fas fa-clipboard-list me-1"></i> NLSC Marks Entry</span>
            <div class="hero-title">{{ $className }} {{ $classSubject->stream_id }} - {{ $subjectName }}</div>
            <div class="hero-subtitle">{{ $exam->exam_name }}</div>
        </div>

        <div class="nt-card">
            <div class="card-header-custom"><i class="fas fa-info-circle me-2"></i> Assessment Details</div>
            <div class="card-body-custom">
                <div class="nt-info-row"><span class="nt-info-label">{{ $display['topic_label'] }}:</span> {{ $display['topic_name'] }}</div>

                @if($display['project_description'])
                    <div class="nt-info-row">{{ $display['project_description'] }}</div>
                @endif

                @if($display['competency_text'])
                    <div class="nt-info-row"><span class="nt-info-label">{{ $display['competency_label'] }}:</span> {{ $display['competency_text'] }}</div>
                @elseif(count($display['achievements']))
                    <div class="nt-info-row">
                        <span class="nt-info-label">{{ $display['competency_label'] }}:</span>
                        <ul>
                            @foreach($display['achievements'] as $achievement)
                                <li>{{ $achievement }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="mt-2">
                    <span class="nt-meta-pill"><i class="fas fa-calendar"></i> Year: {{ $assessment->academic_year }}</span>
                    <span class="nt-meta-pill"><i class="fas fa-hourglass-half"></i> Term: {{ $assessment->term }}</span>
                </div>
            </div>
        </div>

        <div class="nt-card">
            <div class="card-body-custom">
                <div class="row g-3">
                    <div class="col-md-7">
                        <label class="nt-form-label">Assessment</label>
                        <select id="assessmentSwitcher" class="nt-form-control">
                            @foreach($siblingAssessments as $sibling)
                                <option value="{{ route('nlsc-assessments.marks-entry', ['examId' => $exam->id, 'classSubjectId' => $classSubject->id, 'assessmentId' => $sibling->id]) }}"
                                    @selected($sibling->id === $assessment->id)>
                                    {{ $sibling->display_label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label class="nt-form-label">Maximum Marks</label>
                        @if(PermissionHelper::canFeature('edit_exam') && in_array($exam->status, ['active', 'marks_entry']))
                            <div class="max-marks-display" id="maxMarksDisplay">
                                <input type="text" class="nt-form-control" value="{{ $assessment->max_marks ?? '—' }}" disabled style="max-width:140px;">
                                <button type="button" class="btn-nt-secondary" id="editMaxMarksBtn">
                                    <i class="fas fa-pen me-1"></i> {{ $assessment->max_marks ? 'Edit' : 'Set' }}
                                </button>
                            </div>
                            <div class="max-marks-edit" id="maxMarksEdit">
                                <input type="number" min="0.01" step="0.01" class="nt-form-control" id="maxMarksInput" value="{{ $assessment->max_marks }}" style="max-width:140px;">
                                <button type="button" class="btn-nt-primary" id="saveMaxMarksBtn">Save <i class="fas fa-check-double"></i></button>
                                <button type="button" class="btn-nt-secondary" id="cancelMaxMarksBtn"><i class="fas fa-times"></i></button>
                            </div>
                        @else
                            <input type="text" class="nt-form-control" value="{{ $assessment->max_marks ?? '—' }}" disabled>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="nt-card">
            <div class="card-header-custom"><i class="fas fa-users me-2"></i> Student Results</div>
            <div class="card-body-custom" style="padding-bottom:0.5rem;">
                @if(!$assessment->max_marks)
                    <div class="no-max-banner">
                        <i class="fas fa-triangle-exclamation"></i>
                        Set Maximum Marks above before entering Raw Marks — the Calculated Score can't be worked out without it.
                    </div>
                @endif
            </div>
            <div class="table-responsive">
                <table class="table nt-table" id="marksTable">
                    <thead>
                        <tr>
                            <th style="width:40px;">#</th>
                            <th>Name</th>
                            <th style="width:120px;">Raw Mark</th>
                            <th style="width:130px;">Calculated Score</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($students as $key => $student)
                        
                            @php $mark = $existingMarks[$student->id] ?? null; @endphp
                            <tr data-student-id="{{ $student->id }}">
                                <td class="text-muted" style="font-size:.8rem;">{{ $key + 1 }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="student-avatar">{{ strtoupper(substr($student->lastname, 0, 1) . substr($student->firstname, 0, 1)) }}</div> &nbsp;
                                        <span style="font-size:.86rem;">{{ $student->lastname }} {{ $student->firstname }}</span>
                                    </div>
                                </td>
                                <td>
                                    <input type="number" class="marks-input" data-student="{{ $student->id }}"
                                        min="0" max="{{ $assessment->max_marks ?? '' }}" step="0.5"
                                        value="{{ $mark?->marks_obtained ?? '' }}" placeholder="—"
                                        {{ $assessment->max_marks ? '' : 'disabled' }}>
                                </td>
                                <td><span class="score-badge" id="score_{{ $student->id }}">{{ $mark?->calculated_score ?? '—' }}</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
                                    <div class="empty-state">
                                        <i class="fas fa-users d-block mb-2" style="font-size:1.8rem;"></i>
                                        No students found in this class-stream.
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
 </div>
        </div>
    </div>

    @if(PermissionHelper::canFeature('edit_exam') && in_array($exam->status, ['active', 'marks_entry']) && $students->count())
        <button type="button" id="saveMarksBtn" class="save-fab" {{ $assessment->max_marks ? '' : 'disabled' }}>
            <i class="fas fa-save me-2"></i> Save All Marks
        </button>
    @endif

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const CSRF = '{{ csrf_token() }}';
        const MAX_MARKS_URL = "{{ route('nlsc-assessments.max-marks', $assessment->id) }}";
        const SAVE_MARKS_URL = "{{ route('nlsc-assessments.marks.save', $assessment->id) }}";
        const CLASS_SUBJECT_ID = {{ $classSubject->id }};
        const NLSC_SCALE = 3;

        document.getElementById('assessmentSwitcher')?.addEventListener('change', function () {
            window.location.href = this.value;
        });

        function calculatedScore(raw, max) {
            if (raw === '' || raw === null || isNaN(parseFloat(raw)) || !max) return '—';
            return (parseFloat(raw) / parseFloat(max) * NLSC_SCALE).toFixed(1);
        }

        // ----- Maximum Marks inline edit -----
        const displayBox = document.getElementById('maxMarksDisplay');
        const editBox = document.getElementById('maxMarksEdit');

        document.getElementById('editMaxMarksBtn')?.addEventListener('click', function () {
            displayBox.style.display = 'none';
            editBox.style.display = 'flex';
        });

        document.getElementById('cancelMaxMarksBtn')?.addEventListener('click', function () {
            editBox.style.display = 'none';
            displayBox.style.display = 'flex';
        });

document.getElementById('saveMaxMarksBtn')?.addEventListener('click', function () {
    const val = document.getElementById('maxMarksInput').value;
    const btn = this;

    if (val === '' || parseFloat(val) <= 0) {
        Swal.fire('Invalid value', 'Enter a Maximum Marks value greater than 0.', 'error');
        return;
    }

    // Show spinner while saving
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

    fetch(MAX_MARKS_URL, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        body: JSON.stringify({ max_marks: val, class_subject_id: CLASS_SUBJECT_ID }),
    })
        .then(r => r.json())
        .then(res => {
            if (!res.success) {
                Swal.fire('Error', res.message || 'Failed to update Maximum Marks.', 'error');
                // Restore check icon on failure
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-check"></i>';
                return;
            }
            // Keep spinner visible until the reload happens
            location.reload();
        })
        .catch(() => {
            Swal.fire('Error', 'Failed to update — check your connection.', 'error');
            // Restore check icon on error
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check"></i>';
        });
});

// ----- Live Calculated Score preview + range check as Raw Mark is typed -----
document.querySelectorAll('.marks-input').forEach(input => {
    input.addEventListener('input', function () {
        const sid = this.dataset.student;
        const max = parseFloat(this.getAttribute('max'));
        const raw = this.value;

        let invalid = false;
        if (raw !== '') {
            const num = parseFloat(raw);
            invalid = isNaN(num) || num < 0 || (max && num > max);
        }

        // Flag the input red when out of range
        this.classList.toggle('invalid', invalid);

        // Show a tooltip-style title so hovering explains why
        if (invalid && max && parseFloat(raw) > max) {
            this.title = `Raw Mark cannot exceed the Maximum Marks (${max}).`;
        } else if (invalid && parseFloat(raw) < 0) {
            this.title = 'Raw Mark cannot be negative.';
        } else {
            this.title = '';
        }

        // Still show a score preview, but only if valid
        document.getElementById(`score_${sid}`).textContent =
            invalid ? '—' : calculatedScore(raw, max);
    });
});

        // ----- Save All Marks -----
        document.getElementById('saveMarksBtn')?.addEventListener('click', function () {
let hasError = false;
const offending = [];
const max = parseFloat(document.querySelector('.marks-input')?.getAttribute('max'));

document.querySelectorAll('tr[data-student-id]').forEach(row => {
    const input = row.querySelector('.marks-input');
    if (!input) return;

    const val = input.value;
    const rowMax = parseFloat(input.getAttribute('max'));
    const num = parseFloat(val);

    const invalid = val !== '' && (
        isNaN(num) || num < 0 || (rowMax && num > rowMax)
    );

    input.classList.toggle('invalid', invalid);
    if (invalid) {
        hasError = true;
        const name = row.querySelector('td:nth-child(2) span')?.textContent?.trim() || 'Unknown';
        if (!isNaN(num) && rowMax && num > rowMax) {
            offending.push(`<strong>${name}</strong>: ${num} exceeds the Maximum Marks of ${rowMax}`);
        } else if (!isNaN(num) && num < 0) {
            offending.push(`<strong>${name}</strong>: ${num} is negative`);
        } else {
            offending.push(`<strong>${name}</strong>: invalid value`);
        }
    }
});

if (hasError) {
    Swal.fire({
        icon: 'error',
        title: 'Invalid Raw Marks',
        html: `
            <p style="margin-bottom:.6rem;">
                The following Raw Mark${offending.length > 1 ? 's are' : ' is'} out of range.
                ${max ? `The Maximum Marks is <strong>${max}</strong>.` : ''}
            </p>
            <ul style="text-align:left;font-size:.85rem;line-height:1.5;margin:0;padding-left:1.1rem;">
                ${offending.map(o => `<li>${o}</li>`).join('')}
            </ul>
        `,
        confirmButtonColor: '#2C29CA',
    });
    return;
}

            const marksData = [];
            document.querySelectorAll('tr[data-student-id]').forEach(row => {
                const sid = row.dataset.studentId;
                const val = row.querySelector('.marks-input')?.value ?? '';
                marksData.push({ student_id: sid, marks: val !== '' ? val : null });
            });

            const enteredCount = marksData.filter(m => m.marks !== null).length;

            Swal.fire({
                title: 'Save Marks?',
                html: `You are saving marks for <strong>${enteredCount}</strong> of <strong>${marksData.length}</strong> student(s).`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#2C29CA',
                confirmButtonText: 'Yes, save!',
            }).then(result => {
                if (!result.isConfirmed) return;

                const btn = document.getElementById('saveMarksBtn');
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving...';

                fetch(SAVE_MARKS_URL, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                    body: JSON.stringify({ marks: marksData }),
                })
                    .then(r => r.json())
                    .then(res => {
                        if (!res.success) {
                            Swal.fire('Error', res.message || 'Failed to save marks.', 'error');
                            return;
                        }
                        Swal.fire({
                            icon: 'success',
                            title: 'Marks Saved!',
                            text: res.message,
                            confirmButtonColor: '#2C29CA',
                        });
                    })
                    .catch(() => Swal.fire('Error', 'Failed to save — check your connection.', 'error'))
                    .finally(() => {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fas fa-save me-2"></i> Save All Marks';
                    });
            });
        });
    </script>
@endsection