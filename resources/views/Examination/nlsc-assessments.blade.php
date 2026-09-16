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
        .nt-required { color: #dc3545; }

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

        .nt-check { display: flex; align-items: center; gap: .6rem; font-size: .87rem; color: #1e1b4b; }
        .nt-check input { width: 18px; height: 18px; accent-color: #16a34a; }

        .btn-nt-primary {
            background: #2C29CA; color: #fff; border: none; border-radius: .65rem;
            padding: .65rem 1.4rem; font-weight: 700; font-size: .87rem;
        }
        .btn-nt-primary:hover { background: #211ea3; color: #fff; }
        .btn-nt-primary:disabled { background: #b9b6f0; cursor: not-allowed; }

        .btn-nt-secondary {
            background: #f1f0f6; color: #1e1b4b; border: none; border-radius: .65rem;
            padding: .65rem 1.4rem; font-weight: 700; font-size: .87rem;
        }
        .btn-nt-secondary:hover { background: #e4e2ff; color: #1e1b4b; }

        .nt-table { margin-bottom: 0; font-size: .85rem; }
        .nt-table thead th {
            background: #2C29CA; color: #fff; font-size: .68rem; text-transform: uppercase;
            letter-spacing: .06em; font-weight: 700; padding: .8rem .9rem; border: none; white-space: nowrap;
        }
        .nt-table tbody td { vertical-align: middle; padding: .8rem .9rem; border-bottom: 1px solid #f0eeff; }

        .nt-type-badge {
            display: inline-block; padding: .3rem .7rem; border-radius: .5rem;
            font-size: .72rem; font-weight: 700; background: #eef0ff; color: #3a37b8;
        }

        .empty-state { text-align: center; padding: 2rem 1rem; color: #a3a0c9; }
    </style>
@endsection

@section('content')
    <div class="side-app">
        <div class="nt-hero">
            <span class="hero-badge"><i class="fas fa-clipboard-list me-1"></i> NLSC Assessment</span>
            <div class="hero-title">Create Assessment — ({{ $className }} {{ $classSubject->stream_id }} - {{ Helper::recordMdname($classSubject->subject_id) }})</div>
            <div class="hero-subtitle">
                {{ $exam->exam_name }} — choose what this exam's marks entry for this class-subject
                is being assessed against before scoring students.
            </div>
        </div>

        @if($assessments->count())
            <div class="nt-card">
                <div class="card-header-custom"><i class="fas fa-list me-2"></i> Existing Assessments</div>
                <div class="table-responsive">
                    <table class="table nt-table">
                        <thead>
                            <tr>
                                <th>Assessment Type</th>
                                <th>Topic / Project</th>
                                <th>Term</th>
                                <th>Year</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($assessments as $a)
                                <tr>
                                    <td><span class="nt-type-badge">
                                        @if($a->assessment_type === 'activities_of_integration') Activities of Integration
                                        @elseif($a->assessment_type === 'projects') Projects
                                        @else Subject Achievement
                                        @endif
                                    </span></td>
                                    <td>{{ $a->subject_matter_name ?? '—' }}</td>
                                    <td>{{ $a->term }}</td>
                                    <td>{{ $a->academic_year }}</td>
                                    <td>
                                        <a href="{{ route('examination.marks.subject', ['examId' => $exam->id, 'classSubjectId' => $classSubject->id]) }}" class="btn-nt-primary" style="text-decoration:none; display:inline-block;">
                                            <i class="fas fa-pen me-1"></i> Go to Marks Entry
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <div class="nt-card">
            <div class="card-header-custom"><i class="fas fa-plus-circle me-2"></i> Create Assessment</div>
            <div class="card-body-custom">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="nt-form-label">Assessment type <span class="nt-required">*</span></label>
                        <select id="assessmentType" class="nt-form-control">
                            <option value="">Select...</option>
                            <option value="activities_of_integration">Activities of Integration</option>
                            <option value="projects">Projects</option>
                            <option value="subject_achievement">Subject Achievement</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="nt-form-label" id="subjectMatterLabel">Topics <span class="nt-required">*</span></label>
                        <select id="subjectMatter" class="nt-form-control" disabled>
                            <option value="">Select assessment type first...</option>
                        </select>
                    </div>

                    <div class="col-md-6" id="competencyAreaWrap">
                        <label class="nt-form-label">Competency Areas <span class="nt-required">*</span></label>
                        <select id="competencyArea" class="nt-form-control" disabled>
                            <option value="">Select a topic/project first...</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="nt-form-label">Year <span class="nt-required">*</span></label>
                        <select id="academicYear" class="nt-form-control">
                            @php $currentYear = (int) date('Y'); @endphp
                            @for ($y = $currentYear - 1; $y <= $currentYear + 1; $y++)
                                <option value="{{ $y }}" @if((string) $y === (string) $exam->academic_year) selected @endif>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="nt-form-label">Term <span class="nt-required">*</span></label>
                        <select id="term" class="nt-form-control">
                            <option value="Term 1" @if($exam->term === 'Term 1') selected @endif>Term 1</option>
                            <option value="Term 2" @if($exam->term === 'Term 2') selected @endif>Term 2</option>
                            <option value="Term 3" @if($exam->term === 'Term 3') selected @endif>Term 3</option>
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="nt-check">
                            <input type="checkbox" id="includeInReport" checked>
                            Include in this term's report form
                        </label>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('examination.marks.entry', $exam->id) }}" class="btn-nt-secondary" style="text-decoration:none;">
                        <i class="fas fa-arrow-left me-1"></i> Back
                    </a>
                    <button type="button" id="createAssessmentBtn" class="btn-nt-primary" disabled>
                        <i class="fas fa-plus me-1"></i> Create
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        const CSRF = '{{ csrf_token() }}';
        const CLASS_SUBJECT_ID = {{ $classSubject->id }};
        const SUBJECT_MATTER_OPTIONS_URL = "{{ route('nlsc-assessments.subject-matter-options', $classSubject->id) }}";
        const COMPETENCY_AREA_OPTIONS_URL = "{{ route('nlsc-assessments.competency-area-options') }}";
        const STORE_URL = "{{ route('nlsc-assessments.store', ['examId' => $exam->id, 'classSubjectId' => $classSubject->id]) }}";

        const $assessmentType = document.getElementById('assessmentType');
        const $subjectMatter = document.getElementById('subjectMatter');
        const $subjectMatterLabel = document.getElementById('subjectMatterLabel');
        const $competencyAreaWrap = document.getElementById('competencyAreaWrap');
        const $competencyArea = document.getElementById('competencyArea');
        const $createBtn = document.getElementById('createAssessmentBtn');

        function resetSelect($el, placeholder) {
            $el.innerHTML = `<option value="">${placeholder}</option>`;
            $el.disabled = true;
        }

        function refreshCreateButtonState() {
            const typeOk = !!$assessmentType.value;
            const subjectMatterOk = !!$subjectMatter.value;
            const needsCompetency = $assessmentType.value === 'activities_of_integration' || $assessmentType.value === 'projects';
            const competencyOk = !needsCompetency || !!$competencyArea.value;
            $createBtn.disabled = !(typeOk && subjectMatterOk && competencyOk);
        }

        $assessmentType.addEventListener('change', function () {
            resetSelect($subjectMatter, 'Loading...');
            resetSelect($competencyArea, 'Select a topic/project first...');
            refreshCreateButtonState();

            if (!this.value) {
                resetSelect($subjectMatter, 'Select assessment type first...');
                return;
            }

            $subjectMatterLabel.innerHTML = (this.value === 'projects' ? 'Project' : 'Topics') + ' <span class="nt-required">*</span>';
            $competencyAreaWrap.style.display = (this.value === 'subject_achievement') ? 'none' : '';

            fetch(`${SUBJECT_MATTER_OPTIONS_URL}?assessment_type=${this.value}`, {
                headers: { 'Accept': 'application/json' },
            })
                .then(r => r.json())
                .then(res => {
                    if (!res.success || !res.options.length) {
                        resetSelect($subjectMatter, 'None set up yet — add one from the catalogue first');
                        return;
                    }
                    $subjectMatter.innerHTML = '<option value="">Select...</option>'
                        + res.options.map(o => `<option value="${o.id}">${o.label}</option>`).join('');
                    $subjectMatter.disabled = false;
                })
                .catch(() => resetSelect($subjectMatter, 'Failed to load — try again'));
        });

        $subjectMatter.addEventListener('change', function () {
            refreshCreateButtonState();

            const type = $assessmentType.value;
            if (type === 'subject_achievement' || !this.value) {
                resetSelect($competencyArea, 'Select a topic/project first...');
                refreshCreateButtonState();
                return;
            }

            resetSelect($competencyArea, 'Loading...');

            fetch(`${COMPETENCY_AREA_OPTIONS_URL}?assessment_type=${type}&subject_matter_id=${this.value}`, {
                headers: { 'Accept': 'application/json' },
            })
                .then(r => r.json())
                .then(res => {
                    if (!res.success || !res.options.length) {
                        resetSelect($competencyArea, 'None set up yet — add one from the catalogue first');
                        refreshCreateButtonState();
                        return;
                    }
                    $competencyArea.innerHTML = '<option value="">Select...</option>'
                        + res.options.map(o => `<option value="${o.id}">${o.label}</option>`).join('');
                    $competencyArea.disabled = false;
                    refreshCreateButtonState();
                })
                .catch(() => { resetSelect($competencyArea, 'Failed to load — try again'); refreshCreateButtonState(); });
        });

        $competencyArea.addEventListener('change', refreshCreateButtonState);

        $createBtn.addEventListener('click', function () {
            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Creating...';

            fetch(STORE_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                body: JSON.stringify({
                    assessment_type: $assessmentType.value,
                    subject_matter_id: $subjectMatter.value,
                    nlsc_competency_area_id: $competencyArea.value || null,
                    academic_year: document.getElementById('academicYear').value,
                    term: document.getElementById('term').value,
                    include_in_report: document.getElementById('includeInReport').checked ? 1 : 0,
                }),
            })
                .then(r => r.json())
                .then(res => {
                    if (!res.success) {
                        Swal.fire('Error', res.message || 'Failed to create assessment.', 'error');
                        this.disabled = false;
                        this.innerHTML = '<i class="fas fa-plus me-1"></i> Create';
                        return;
                    }
                    window.location.href = res.redirect;
                })
                .catch(() => {
                    Swal.fire('Error', 'Failed to create — check your connection.', 'error');
                    this.disabled = false;
                    this.innerHTML = '<i class="fas fa-plus me-1"></i> Create';
                });
        });
    </script>
@endsection