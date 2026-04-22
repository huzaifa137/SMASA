@extends('layouts-side-bar.master')
@section('css')
    <!---jvectormap css-->
    <link href="{{ URL::asset('assets/plugins/jvectormap/jqvmap.css') }}" rel="stylesheet" />
    <!-- Data table css -->
    <link href="{{ URL::asset('assets/plugins/datatable/dataTables.bootstrap4.min.css') }}" rel="stylesheet" />
    <!--Daterangepicker css-->
    <link href="{{ URL::asset('assets/plugins/bootstrap-daterangepicker/daterangepicker.css') }}" rel="stylesheet" />
@endsection

@section('content')
    <div class="side-app">
        <style>
            .form-check-input {
                transform: scale(1.5);
                margin-right: 10px;
            }
            .form-check-label {
                line-height: 1.5;
            }

            /* Button group styling */
            .subject-control-buttons {
                margin-bottom: 15px;
                padding: 10px;
                background: #f8f9fa;
                border-radius: 8px;
                display: inline-block;
            }

            .btn-check-all, .btn-uncheck-all {
                padding: 5px 15px;
                margin-right: 10px;
                border-radius: 5px;
                font-size: 13px;
                transition: all 0.3s ease;
            }

            .btn-check-all {
                background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
                border: none;
                color: white;
            }

            .btn-check-all:hover {
                background: linear-gradient(135deg, #218838 0%, #1ba87e 100%);
                transform: translateY(-1px);
                box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            }

            .btn-uncheck-all {
                background: linear-gradient(135deg, #dc3545 0%, #f86c6b 100%);
                border: none;
                color: white;
            }

            .btn-uncheck-all:hover {
                background: linear-gradient(135deg, #c82333 0%, #e05a59 100%);
                transform: translateY(-1px);
                box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            }

            .subject-section-card {
                background: white;
                border-radius: 10px;
                padding: 20px;
                margin-bottom: 20px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }

            .section-title {
                font-size: 18px;
                font-weight: 600;
                margin-bottom: 20px;
                padding-bottom: 10px;
                border-bottom: 2px solid #e9ecef;
                color: #495057;
            }
        </style>

        <div class="row">
            <div class="col-lg-12 col-xl-12 col-md-12 col-sm-12">
                <div class="card bg-primary">
                    @include('layouts.class-buttons')
                    <div class="card-body bg-light">
                        <form id="editSubjectAssignmentForm">
                            @csrf
                            @method('PUT')

                            <div class="row">
                                <div class="col-lg-6 col-md-12">
                                    <div class="form-group">
                                        <label class="form-label">Senior</label>
                                        <select class="form-control select2" id="class_id" name="class_id" disabled>
                                            <option value="">-- Select --</option>
                                            @if($classType === 'O-Level' || $classType === 'A-Level')
                                                @foreach ($SecondaryClasses as $class)
                                                    <option value="{{ $class->md_id }}" {{ $assignment->class_id == $class->md_id ? 'selected' : '' }}>
                                                        {{ $class->md_name }}
                                                    </option>
                                                @endforeach
                                            @elseif($classType === 'Primary Theology')
                                                @foreach ($PrimaryClasses as $class)
                                                    <option value="{{ $class->md_id }}" {{ $assignment->class_id == $class->md_id ? 'selected' : '' }}>
                                                        {{ $class->md_name }}
                                                    </option>
                                                @endforeach
                                            @elseif($classType === 'Primary Secular')
                                                @foreach ($PrimarySecularClasses as $class)
                                                    <option value="{{ $class->md_id }}" {{ $assignment->class_id == $class->md_id ? 'selected' : '' }}>
                                                        {{ $class->md_name }}
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                        <input type="hidden" name="class_id" value="{{ $assignment->class_id }}">
                                    </div>
                                </div>
                                <div class="col-lg-6 col-md-12">
                                    <div class="form-group">
                                        <label class="form-label">Stream</label>
                                        <input type="text" class="form-control" value="{{ $assignment->stream_id }}" disabled>
                                        <input type="hidden" name="stream_id" value="{{ $assignment->stream_id }}">
                                    </div>
                                </div>
                            </div>

                            <!-- IDAAD Subjects (O-Level) -->
                            @if($classType == 'O-Level')
                            <div id="idaad-subjects">
                                <div class="subject-section-card">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h5 class="section-title mb-0">O - LEVEL SUBJECTS (IDAAD)</h5>
                                        <div class="subject-control-buttons">
                                            <button type="button" class="btn btn-sm btn-check-all" onclick="checkAllIdaadSubjects()">
                                                <i class="fas fa-check-double"></i> Check All
                                            </button>
                                            <button type="button" class="btn btn-sm btn-uncheck-all" onclick="uncheckAllIdaadSubjects()">
                                                <i class="fas fa-times-circle"></i> Uncheck All
                                            </button>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-lg-4 col-md-12">
                                            <label class="form-label"><strong>Arabic Subjects</strong></label>
                                            @foreach ($IDAAD_ARABIC_LANGUAGE as $subject)
                                                <div class="form-check">
                                                    <input class="form-check-input idaad-subject" type="checkbox"
                                                        id="idaad-arabic-{{ $loop->index }}"
                                                        name="subjects[]"
                                                        value="{{ $subject->md_id }}"
                                                        {{ in_array($subject->md_id, $assignedSubjects) ? 'checked' : '' }}>
                                                    <label class="form-check-label"
                                                        for="idaad-arabic-{{ $loop->index }}">{{ $subject->md_name }}</label>
                                                </div>
                                            @endforeach
                                        </div>

                                        <div class="col-lg-4 col-md-12">
                                            <label class="form-label"><strong>Faith & Civilization</strong></label>
                                            @foreach ($IDAAD_FAITH_AND_CIVILIZATION as $subject)
                                                <div class="form-check">
                                                    <input class="form-check-input idaad-subject" type="checkbox"
                                                        id="idaad-faith-{{ $loop->index }}"
                                                        name="subjects[]"
                                                        value="{{ $subject->md_id }}"
                                                        {{ in_array($subject->md_id, $assignedSubjects) ? 'checked' : '' }}>
                                                    <label class="form-check-label"
                                                        for="idaad-faith-{{ $loop->index }}">{{ $subject->md_name }}</label>
                                                </div>
                                            @endforeach
                                        </div>

                                        <div class="col-lg-4 col-md-12">
                                            <label class="form-label"><strong>Jurisprudence & Its Sources</strong></label>
                                            @foreach ($IDAAD_JURISPRUDENCE_AND_ITS_SOURCES as $subject)
                                                <div class="form-check">
                                                    <input class="form-check-input idaad-subject" type="checkbox"
                                                        id="idaad-jurisprudence-{{ $loop->index }}"
                                                        name="subjects[]"
                                                        value="{{ $subject->md_id }}"
                                                        {{ in_array($subject->md_id, $assignedSubjects) ? 'checked' : '' }}>
                                                    <label class="form-check-label"
                                                        for="idaad-jurisprudence-{{ $loop->index }}">{{ $subject->md_name }}</label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                    <br>

                                    <div class="row">
                                        <div class="col-lg-4 col-md-12">
                                            <label class="form-label"><strong>Quran And Its Sciences</strong></label>
                                            @foreach ($IDAAD_QURAN_ITS_SCIENCES as $subject)
                                                <div class="form-check">
                                                    <input class="form-check-input idaad-subject" type="checkbox"
                                                        id="idaad-quran-{{ $loop->index }}"
                                                        name="subjects[]"
                                                        value="{{ $subject->md_id }}"
                                                        {{ in_array($subject->md_id, $assignedSubjects) ? 'checked' : '' }}>
                                                    <label class="form-check-label"
                                                        for="idaad-quran-{{ $loop->index }}">{{ $subject->md_name }}</label>
                                                </div>
                                            @endforeach
                                        </div>

                                        <div class="col-lg-4 col-md-12">
                                            <label class="form-label"><strong>Prophetic Traditions</strong></label>
                                            @foreach ($IDAAD_PROPHETIC_TRADITIONS as $subject)
                                                <div class="form-check">
                                                    <input class="form-check-input idaad-subject" type="checkbox"
                                                        id="idaad-prophetic-{{ $loop->index }}"
                                                        name="subjects[]"
                                                        value="{{ $subject->md_id }}"
                                                        {{ in_array($subject->md_id, $assignedSubjects) ? 'checked' : '' }}>
                                                    <label class="form-check-label"
                                                        for="idaad-prophetic-{{ $loop->index }}">{{ $subject->md_name }}</label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif

                            <!-- THANAWI Subjects (A-Level) -->
                            @if($classType == 'A-Level')
                            <div id="thanawi-subjects">
                                <div class="subject-section-card">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h5 class="section-title mb-0">A - LEVEL SUBJECTS (THANAWI)</h5>
                                        <div class="subject-control-buttons">
                                            <button type="button" class="btn btn-sm btn-check-all" onclick="checkAllThanawiSubjects()">
                                                <i class="fas fa-check-double"></i> Check All
                                            </button>
                                            <button type="button" class="btn btn-sm btn-uncheck-all" onclick="uncheckAllThanawiSubjects()">
                                                <i class="fas fa-times-circle"></i> Uncheck All
                                            </button>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-lg-4 col-md-12">
                                            <label class="form-label"><strong>Arabic Subjects</strong></label>
                                            @foreach ($THANAWI_ARABIC_LANGUAGE as $subject)
                                                <div class="form-check">
                                                    <input class="form-check-input thanawi-subject" type="checkbox"
                                                        id="thanawi-arabic-{{ $loop->index }}"
                                                        name="subjects[]"
                                                        value="{{ $subject->md_id }}"
                                                        {{ in_array($subject->md_id, $assignedSubjects) ? 'checked' : '' }}>
                                                    <label class="form-check-label"
                                                        for="thanawi-arabic-{{ $loop->index }}">{{ $subject->md_name }}</label>
                                                </div>
                                            @endforeach
                                        </div>

                                        <div class="col-lg-4 col-md-12">
                                            <label class="form-label"><strong>Faith & Civilization</strong></label>
                                            @foreach ($THANAWI_FAITH_AND_CIVILIZATION as $subject)
                                                <div class="form-check">
                                                    <input class="form-check-input thanawi-subject" type="checkbox"
                                                        id="thanawi-faith-{{ $loop->index }}"
                                                        name="subjects[]"
                                                        value="{{ $subject->md_id }}"
                                                        {{ in_array($subject->md_id, $assignedSubjects) ? 'checked' : '' }}>
                                                    <label class="form-check-label"
                                                        for="thanawi-faith-{{ $loop->index }}">{{ $subject->md_name }}</label>
                                                </div>
                                            @endforeach
                                        </div>

                                        <div class="col-lg-4 col-md-12">
                                            <label class="form-label"><strong>Jurisprudence & Its Sources</strong></label>
                                            @foreach ($THANAWI_JURISPRUDENCE_AND_ITS_SOURCES as $subject)
                                                <div class="form-check">
                                                    <input class="form-check-input thanawi-subject" type="checkbox"
                                                        id="thanawi-jurisprudence-{{ $loop->index }}"
                                                        name="subjects[]"
                                                        value="{{ $subject->md_id }}"
                                                        {{ in_array($subject->md_id, $assignedSubjects) ? 'checked' : '' }}>
                                                    <label class="form-check-label"
                                                        for="thanawi-jurisprudence-{{ $loop->index }}">{{ $subject->md_name }}</label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                    <br>

                                    <div class="row">
                                        <div class="col-lg-4 col-md-12">
                                            <label class="form-label"><strong>Quran And Its Sciences</strong></label>
                                            @foreach ($THANAWI_QURAN_ITS_SCIENCES as $subject)
                                                <div class="form-check">
                                                    <input class="form-check-input thanawi-subject" type="checkbox"
                                                        id="thanawi-quran-{{ $loop->index }}"
                                                        name="subjects[]"
                                                        value="{{ $subject->md_id }}"
                                                        {{ in_array($subject->md_id, $assignedSubjects) ? 'checked' : '' }}>
                                                    <label class="form-check-label"
                                                        for="thanawi-quran-{{ $loop->index }}">{{ $subject->md_name }}</label>
                                                </div>
                                            @endforeach
                                        </div>

                                        <div class="col-lg-4 col-md-12">
                                            <label class="form-label"><strong>Prophetic Traditions</strong></label>
                                            @foreach ($THANAWI_PROPHETIC_TRADITIONS as $subject)
                                                <div class="form-check">
                                                    <input class="form-check-input thanawi-subject" type="checkbox"
                                                        id="thanawi-prophetic-{{ $loop->index }}"
                                                        name="subjects[]"
                                                        value="{{ $subject->md_id }}"
                                                        {{ in_array($subject->md_id, $assignedSubjects) ? 'checked' : '' }}>
                                                    <label class="form-check-label"
                                                        for="thanawi-prophetic-{{ $loop->index }}">{{ $subject->md_name }}</label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif

                            <!-- PRIMARY THEOLOGY Subjects -->
                            @if($classType == 'Primary Theology')
                            <div id="primary-theology-subjects">
                                <div class="subject-section-card">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h5 class="section-title mb-0">PRIMARY THEOLOGY SUBJECTS</h5>
                                        <div class="subject-control-buttons">
                                            <button type="button" class="btn btn-sm btn-check-all" onclick="checkAllPrimaryTheologySubjects()">
                                                <i class="fas fa-check-double"></i> Check All
                                            </button>
                                            <button type="button" class="btn btn-sm btn-uncheck-all" onclick="uncheckAllPrimaryTheologySubjects()">
                                                <i class="fas fa-times-circle"></i> Uncheck All
                                            </button>
                                        </div>
                                    </div>
                                    <div class="row">
                                        @foreach ($primaryTheology ?? [] as $subject)
                                            <div class="col-lg-4 col-md-12">
                                                <div class="form-check">
                                                    <input class="form-check-input primary-theology-subject" type="checkbox"
                                                        id="primary-theology-{{ $loop->index }}"
                                                        name="subjects[]"
                                                        value="{{ $subject->md_id }}"
                                                        {{ in_array($subject->md_id, $assignedSubjects) ? 'checked' : '' }}>
                                                    <label class="form-check-label"
                                                        for="primary-theology-{{ $loop->index }}">{{ $subject->md_name }}</label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            @endif

                            <!-- PRIMARY SECULAR Subjects -->
                            @if($classType == 'Primary Secular')
                            <div id="primary-secular-subjects">
                                <div class="subject-section-card">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h5 class="section-title mb-0">PRIMARY SECULAR SUBJECTS</h5>
                                        <div class="subject-control-buttons">
                                            <button type="button" class="btn btn-sm btn-check-all" onclick="checkAllPrimarySecularSubjects()">
                                                <i class="fas fa-check-double"></i> Check All
                                            </button>
                                            <button type="button" class="btn btn-sm btn-uncheck-all" onclick="uncheckAllPrimarySecularSubjects()">
                                                <i class="fas fa-times-circle"></i> Uncheck All
                                            </button>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <!-- Nursery Baby Class -->
                                        <div class="col-lg-4 col-md-12">
                                            <label class="form-label"><strong>Nursery Baby Class</strong></label>
                                            @foreach ($primarySecularSubjects[config('constants.options.NURSERY_BABY_CLASS')] ?? [] as $subject)
                                                <div class="form-check">
                                                    <input class="form-check-input primary-secular-subject" type="checkbox"
                                                        id="primary-secular-baby-{{ $loop->index }}"
                                                        name="subjects[]"
                                                        value="{{ $subject->md_id }}"
                                                        {{ in_array($subject->md_id, $assignedSubjects) ? 'checked' : '' }}>
                                                    <label class="form-check-label"
                                                        for="primary-secular-baby-{{ $loop->index }}">{{ $subject->md_name }}</label>
                                                </div>
                                            @endforeach
                                        </div>
                                        <!-- Nursery Middle Class -->
                                        <div class="col-lg-4 col-md-12">
                                            <label class="form-label"><strong>Nursery Middle Class</strong></label>
                                            @foreach ($primarySecularSubjects[config('constants.options.NURSERY_MIDDLE_CLASS')] ?? [] as $subject)
                                                <div class="form-check">
                                                    <input class="form-check-input primary-secular-subject" type="checkbox"
                                                        id="primary-secular-middle-{{ $loop->index }}"
                                                        name="subjects[]"
                                                        value="{{ $subject->md_id }}"
                                                        {{ in_array($subject->md_id, $assignedSubjects) ? 'checked' : '' }}>
                                                    <label class="form-check-label"
                                                        for="primary-secular-middle-{{ $loop->index }}">{{ $subject->md_name }}</label>
                                                </div>
                                            @endforeach
                                        </div>
                                        <!-- Nursery Top Class -->
                                        <div class="col-lg-4 col-md-12">
                                            <label class="form-label"><strong>Nursery Top Class</strong></label>
                                            @foreach ($primarySecularSubjects[config('constants.options.NURSERY_TOP_CLASS')] ?? [] as $subject)
                                                <div class="form-check">
                                                    <input class="form-check-input primary-secular-subject" type="checkbox"
                                                        id="primary-secular-top-{{ $loop->index }}"
                                                        name="subjects[]"
                                                        value="{{ $subject->md_id }}"
                                                        {{ in_array($subject->md_id, $assignedSubjects) ? 'checked' : '' }}>
                                                    <label class="form-check-label"
                                                        for="primary-secular-top-{{ $loop->index }}">{{ $subject->md_name }}</label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    <br>
                                    <div class="row">
                                        <!-- Lower Primary P1 -->
                                        <div class="col-lg-4 col-md-12">
                                            <label class="form-label"><strong>Lower Primary P1</strong></label>
                                            @foreach ($primarySecularSubjects[config('constants.options.LOWER_PRIMARY_P1')] ?? [] as $subject)
                                                <div class="form-check">
                                                    <input class="form-check-input primary-secular-subject" type="checkbox"
                                                        id="primary-secular-p1-{{ $loop->index }}"
                                                        name="subjects[]"
                                                        value="{{ $subject->md_id }}"
                                                        {{ in_array($subject->md_id, $assignedSubjects) ? 'checked' : '' }}>
                                                    <label class="form-check-label"
                                                        for="primary-secular-p1-{{ $loop->index }}">{{ $subject->md_name }}</label>
                                                </div>
                                            @endforeach
                                        </div>
                                        <!-- Lower Primary P2 -->
                                        <div class="col-lg-4 col-md-12">
                                            <label class="form-label"><strong>Lower Primary P2</strong></label>
                                            @foreach ($primarySecularSubjects[config('constants.options.LOWER_PRIMARY_P2')] ?? [] as $subject)
                                                <div class="form-check">
                                                    <input class="form-check-input primary-secular-subject" type="checkbox"
                                                        id="primary-secular-p2-{{ $loop->index }}"
                                                        name="subjects[]"
                                                        value="{{ $subject->md_id }}"
                                                        {{ in_array($subject->md_id, $assignedSubjects) ? 'checked' : '' }}>
                                                    <label class="form-check-label"
                                                        for="primary-secular-p2-{{ $loop->index }}">{{ $subject->md_name }}</label>
                                                </div>
                                            @endforeach
                                        </div>
                                        <!-- Lower Primary P3 -->
                                        <div class="col-lg-4 col-md-12">
                                            <label class="form-label"><strong>Lower Primary P3</strong></label>
                                            @foreach ($primarySecularSubjects[config('constants.options.LOWER_PRIMARY_P3')] ?? [] as $subject)
                                                <div class="form-check">
                                                    <input class="form-check-input primary-secular-subject" type="checkbox"
                                                        id="primary-secular-p3-{{ $loop->index }}"
                                                        name="subjects[]"
                                                        value="{{ $subject->md_id }}"
                                                        {{ in_array($subject->md_id, $assignedSubjects) ? 'checked' : '' }}>
                                                    <label class="form-check-label"
                                                        for="primary-secular-p3-{{ $loop->index }}">{{ $subject->md_name }}</label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    <br>
                                    <div class="row">
                                        <!-- Upper Primary P4-P7 -->
                                        <div class="col-lg-12 col-md-12">
                                            <label class="form-label"><strong>Upper Primary P4-P7</strong></label>
                                            @foreach ($primarySecularSubjects[config('constants.options.UPPER_PRIMARY_P4_P7')] ?? [] as $subject)
                                                <div class="form-check">
                                                    <input class="form-check-input primary-secular-subject" type="checkbox"
                                                        id="primary-secular-p4p7-{{ $loop->index }}"
                                                        name="subjects[]"
                                                        value="{{ $subject->md_id }}"
                                                        {{ in_array($subject->md_id, $assignedSubjects) ? 'checked' : '' }}>
                                                    <label class="form-check-label"
                                                        for="primary-secular-p4p7-{{ $loop->index }}">{{ $subject->md_name }}</label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif

                            <div class="mt-4 text-left">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Update Subjects
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
                </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        // Functions for IDAAD subjects (O-Level)
        function checkAllIdaadSubjects() {
            $('.idaad-subject').prop('checked', true);
            showToast('All O-Level subjects have been selected', 'success');
        }
        function uncheckAllIdaadSubjects() {
            $('.idaad-subject').prop('checked', false);
            showToast('All O-Level subjects have been deselected', 'info');
        }

        // Functions for THANAWI subjects (A-Level)
        function checkAllThanawiSubjects() {
            $('.thanawi-subject').prop('checked', true);
            showToast('All A-Level subjects have been selected', 'success');
        }
        function uncheckAllThanawiSubjects() {
            $('.thanawi-subject').prop('checked', false);
            showToast('All A-Level subjects have been deselected', 'info');
        }

        // Functions for PRIMARY THEOLOGY subjects
        function checkAllPrimaryTheologySubjects() {
            $('.primary-theology-subject').prop('checked', true);
            showToast('All Primary Theology subjects have been selected', 'success');
        }
        function uncheckAllPrimaryTheologySubjects() {
            $('.primary-theology-subject').prop('checked', false);
            showToast('All Primary Theology subjects have been deselected', 'info');
        }

        // Functions for PRIMARY SECULAR subjects
        function checkAllPrimarySecularSubjects() {
            $('.primary-secular-subject').prop('checked', true);
            showToast('All Primary Secular subjects have been selected', 'success');
        }
        function uncheckAllPrimarySecularSubjects() {
            $('.primary-secular-subject').prop('checked', false);
            showToast('All Primary Secular subjects have been deselected', 'info');
        }

        // Toast notification function
        function showToast(message, type = 'success') {
            Swal.fire({
                icon: type,
                title: message,
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer);
                    toast.addEventListener('mouseleave', Swal.resumeTimer);
                }
            });
        }

        $(document).ready(function () {
            $('#editSubjectAssignmentForm').on('submit', function (e) {
                e.preventDefault();

                let $form = $(this);
                let $submitBtn = $form.find('button[type="submit"]');

                // Collect selected subjects
                let selectedSubjects = [];
                $('input[name="subjects[]"]:checked').each(function () {
                    selectedSubjects.push($(this).val());
                });

                // Validate subject selection
                if (selectedSubjects.length === 0) {
                    Swal.fire({
                        icon: 'error',
                        title: 'No Subjects Selected',
                        text: 'Please select at least one subject before updating.'
                    });
                    return;
                }

                // Confirm update
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You are about to update class subjects.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, update it!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Disable submit button
                        $submitBtn.prop('disabled', true);
                        const originalBtnHtml = $submitBtn.html();
                        $submitBtn.html('Updating... <i class="fas fa-spinner fa-spin"></i>');

                        // Prepare data
                        let dataToSend = {
                            class_id: $('input[name="class_id"]').val(),
                            stream_id: $('input[name="stream_id"]').val(),
                            subjects: selectedSubjects,
                            _token: '{{ csrf_token() }}',
                            _method: 'PUT'
                        };

                        // Send AJAX request
                        $.ajax({
                            url: '{{ route("assign.subjects.update", $assignment->id) }}',
                            method: 'POST',
                            data: JSON.stringify(dataToSend),
                            contentType: 'application/json',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            success: function (response) {
                                if (response.fail) {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: response.message
                                    });
                                } else {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Updated!',
                                        text: response.message || 'Subjects updated successfully.',
                                        timer: 2000,
                                        showConfirmButton: false
                                    }).then(() => {
                                        window.location.href = '{{ route("manage.class.streams", ["id" => $assignment->class_id]) }}';
                                    });
                                }
                            },
                            error: function (xhr) {
                                let errorMessage = 'Something went wrong.';
                                if (xhr.responseJSON && xhr.responseJSON.message) {
                                    errorMessage = xhr.responseJSON.message;
                                }
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Update Failed',
                                    text: errorMessage
                                });
                            },
                            complete: function () {
                                $submitBtn.prop('disabled', false).html(originalBtnHtml);
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection

@section('js')
    <!-- Existing scripts -->
@endsection