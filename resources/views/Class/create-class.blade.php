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

            .subject-control-buttons {
                margin-bottom: 15px;
                padding: 10px;
                background: #f8f9fa;
                border-radius: 8px;
                display: inline-block;
            }

            .btn-check-all,
            .btn-uncheck-all {
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
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            }

            .btn-uncheck-all {
                background: linear-gradient(135deg, #dc3545 0%, #f86c6b 100%);
                border: none;
                color: white;
            }

            .btn-uncheck-all:hover {
                background: linear-gradient(135deg, #c82333 0%, #e05a59 100%);
                transform: translateY(-1px);
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            }

            .subject-section-card {
                background: white;
                border-radius: 10px;
                padding: 20px;
                margin-bottom: 20px;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
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
                        <form id="createClassForm">
                            <div class="row">
                                <div class="col-lg-6 col-md-12">
                                    <div class="form-group">
                                        <label class="form-label">Class</label>
                                        <select class="form-control select2" id="class_id" name="class_id">
                                            <option value="">-- Select --</option>
                                            @if(isset($SecondaryClasses) && $SecondaryClasses->isNotEmpty())
                                                @foreach ($SecondaryClasses as $class)
                                                    <option value="{{ $class->md_id }}"
                                                        data-type="{{ $classTypeMap[$class->md_id] ?? 'Unknown' }}">
                                                        {{ $class->md_name }}
                                                    </option>
                                                @endforeach
                                            @elseif(isset($PrimaryClasses) && $PrimaryClasses->isNotEmpty())
                                                @foreach ($PrimaryClasses as $class)
                                                    <option value="{{ $class->md_id }}"
                                                        data-type="{{ $classTypeMap[$class->md_id] }}">
                                                        {{ $class->md_name }}
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-6 col-md-12">
                                    <div class="form-group">
                                        <label class="form-label">Stream</label>
                                        <input type="text" name="class_stream" id="class_stream" class="form-control"
                                            value="{{ old('class_stream') }}" placeholder="Enter Class Stream">
                                    </div>
                                </div>
                            </div>

                            {{-- ═══════════════════════════════════════════════
                            IDAAD SUBJECTS (O-Level)
                            — Single card, all 5 categories in columns,
                            matching the edit-class layout exactly
                            ════════════════════════════════════════════════ --}}
                            <div id="idaad-subjects" style="display: none;">
                                <div class="subject-section-card">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h5 class="section-title mb-0">O - LEVEL SUBJECTS (IDAAD)</h5>
                                        <div class="subject-control-buttons">
                                            <button type="button" class="btn btn-sm btn-check-all"
                                                onclick="checkAllIdaadSubjects()">
                                                <i class="fas fa-check-double"></i> Check All
                                            </button>
                                            <button type="button" class="btn btn-sm btn-uncheck-all"
                                                onclick="uncheckAllIdaadSubjects()">
                                                <i class="fas fa-times-circle"></i> Uncheck All
                                            </button>
                                        </div>
                                    </div>

                                    {{-- Row 1: Arabic | Faith & Civilization | Jurisprudence --}}
                                    <div class="row">
                                        <div class="col-lg-4 col-md-12">
                                            <label class="form-label"><strong>Arabic Subjects</strong></label>
                                            @foreach ($IDAAD_ARABIC_LANGUAGE ?? [] as $subject)
                                                <div class="form-check">
                                                    <input class="form-check-input idaad-subject" type="checkbox"
                                                        id="idaad-arabic-{{ $loop->index }}" value="{{ $subject->md_id }}"
                                                        data-group="idaad">
                                                    <label class="form-check-label"
                                                        for="idaad-arabic-{{ $loop->index }}">{{ $subject->md_name }}</label>
                                                </div>
                                            @endforeach
                                        </div>

                                        <div class="col-lg-4 col-md-12">
                                            <label class="form-label"><strong>Faith &amp; Civilization</strong></label>
                                            @foreach ($IDAAD_FAITH_AND_CIVILIZATION ?? [] as $subject)
                                                <div class="form-check">
                                                    <input class="form-check-input idaad-subject" type="checkbox"
                                                        id="idaad-faith-{{ $loop->index }}" value="{{ $subject->md_id }}"
                                                        data-group="idaad">
                                                    <label class="form-check-label"
                                                        for="idaad-faith-{{ $loop->index }}">{{ $subject->md_name }}</label>
                                                </div>
                                            @endforeach
                                        </div>

                                        <div class="col-lg-4 col-md-12">
                                            <label class="form-label"><strong>Jurisprudence &amp; Its
                                                    Sources</strong></label>
                                            @foreach ($IDAAD_JURISPRUDENCE_AND_ITS_SOURCES ?? [] as $subject)
                                                <div class="form-check">
                                                    <input class="form-check-input idaad-subject" type="checkbox"
                                                        id="idaad-jurisprudence-{{ $loop->index }}"
                                                        value="{{ $subject->md_id }}" data-group="idaad">
                                                    <label class="form-check-label"
                                                        for="idaad-jurisprudence-{{ $loop->index }}">{{ $subject->md_name }}</label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                    <br>

                                    {{-- Row 2: Quran & Its Sciences | Prophetic Traditions --}}
                                    <div class="row">
                                        <div class="col-lg-4 col-md-12">
                                            <label class="form-label"><strong>Quran And Its Sciences</strong></label>
                                            @foreach ($IDAAD_QURAN_ITS_SCIENCES ?? [] as $subject)
                                                <div class="form-check">
                                                    <input class="form-check-input idaad-subject" type="checkbox"
                                                        id="idaad-quran-{{ $loop->index }}" value="{{ $subject->md_id }}"
                                                        data-group="idaad">
                                                    <label class="form-check-label"
                                                        for="idaad-quran-{{ $loop->index }}">{{ $subject->md_name }}</label>
                                                </div>
                                            @endforeach
                                        </div>

                                        <div class="col-lg-4 col-md-12">
                                            <label class="form-label"><strong>Prophetic Traditions</strong></label>
                                            @foreach ($IDAAD_PROPHETIC_TRADITIONS ?? [] as $subject)
                                                <div class="form-check">
                                                    <input class="form-check-input idaad-subject" type="checkbox"
                                                        id="idaad-prophetic-{{ $loop->index }}" value="{{ $subject->md_id }}"
                                                        data-group="idaad">
                                                    <label class="form-check-label"
                                                        for="idaad-prophetic-{{ $loop->index }}">{{ $subject->md_name }}</label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- ═══════════════════════════════════════════════
                            THANAWI SUBJECTS (A-Level)
                            — Single card, all 5 categories in columns,
                            matching the edit-class layout exactly
                            ════════════════════════════════════════════════ --}}
                            <div id="thanawi-subjects" style="display: none;">
                                <div class="subject-section-card">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h5 class="section-title mb-0">A - LEVEL SUBJECTS (THANAWI)</h5>
                                        <div class="subject-control-buttons">
                                            <button type="button" class="btn btn-sm btn-check-all"
                                                onclick="checkAllThanawiSubjects()">
                                                <i class="fas fa-check-double"></i> Check All
                                            </button>
                                            <button type="button" class="btn btn-sm btn-uncheck-all"
                                                onclick="uncheckAllThanawiSubjects()">
                                                <i class="fas fa-times-circle"></i> Uncheck All
                                            </button>
                                        </div>
                                    </div>

                                    {{-- Row 1: Arabic | Faith & Civilization | Jurisprudence --}}
                                    <div class="row">
                                        <div class="col-lg-4 col-md-12">
                                            <label class="form-label"><strong>Arabic Subjects</strong></label>
                                            @foreach ($THANAWI_ARABIC_LANGUAGE ?? [] as $subject)
                                                <div class="form-check">
                                                    <input class="form-check-input thanawi-subject" type="checkbox"
                                                        id="thanawi-arabic-{{ $loop->index }}" value="{{ $subject->md_id }}"
                                                        data-group="thanawi">
                                                    <label class="form-check-label"
                                                        for="thanawi-arabic-{{ $loop->index }}">{{ $subject->md_name }}</label>
                                                </div>
                                            @endforeach
                                        </div>

                                        <div class="col-lg-4 col-md-12">
                                            <label class="form-label"><strong>Faith &amp; Civilization</strong></label>
                                            @foreach ($THANAWI_FAITH_AND_CIVILIZATION ?? [] as $subject)
                                                <div class="form-check">
                                                    <input class="form-check-input thanawi-subject" type="checkbox"
                                                        id="thanawi-faith-{{ $loop->index }}" value="{{ $subject->md_id }}"
                                                        data-group="thanawi">
                                                    <label class="form-check-label"
                                                        for="thanawi-faith-{{ $loop->index }}">{{ $subject->md_name }}</label>
                                                </div>
                                            @endforeach
                                        </div>

                                        <div class="col-lg-4 col-md-12">
                                            <label class="form-label"><strong>Jurisprudence &amp; Its
                                                    Sources</strong></label>
                                            @foreach ($THANAWI_JURISPRUDENCE_AND_ITS_SOURCES ?? [] as $subject)
                                                <div class="form-check">
                                                    <input class="form-check-input thanawi-subject" type="checkbox"
                                                        id="thanawi-jurisprudence-{{ $loop->index }}"
                                                        value="{{ $subject->md_id }}" data-group="thanawi">
                                                    <label class="form-check-label"
                                                        for="thanawi-jurisprudence-{{ $loop->index }}">{{ $subject->md_name }}</label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                    <br>

                                    {{-- Row 2: Quran & Its Sciences | Prophetic Traditions --}}
                                    <div class="row">
                                        <div class="col-lg-4 col-md-12">
                                            <label class="form-label"><strong>Quran And Its Sciences</strong></label>
                                            @foreach ($THANAWI_QURAN_ITS_SCIENCES ?? [] as $subject)
                                                <div class="form-check">
                                                    <input class="form-check-input thanawi-subject" type="checkbox"
                                                        id="thanawi-quran-{{ $loop->index }}" value="{{ $subject->md_id }}"
                                                        data-group="thanawi">
                                                    <label class="form-check-label"
                                                        for="thanawi-quran-{{ $loop->index }}">{{ $subject->md_name }}</label>
                                                </div>
                                            @endforeach
                                        </div>

                                        <div class="col-lg-4 col-md-12">
                                            <label class="form-label"><strong>Prophetic Traditions</strong></label>
                                            @foreach ($THANAWI_PROPHETIC_TRADITIONS ?? [] as $subject)
                                                <div class="form-check">
                                                    <input class="form-check-input thanawi-subject" type="checkbox"
                                                        id="thanawi-prophetic-{{ $loop->index }}" value="{{ $subject->md_id }}"
                                                        data-group="thanawi">
                                                    <label class="form-check-label"
                                                        for="thanawi-prophetic-{{ $loop->index }}">{{ $subject->md_name }}</label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- ═══════════════════════════════════════════════
                            PRIMARY THEOLOGY SUBJECTS
                            ════════════════════════════════════════════════ --}}
                            <div id="primary-theology-subjects" style="display: none;">
                                <div class="subject-section-card">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h5 class="section-title mb-0">PRIMARY THEOLOGY SUBJECTS</h5>
                                        <div class="subject-control-buttons">
                                            <button type="button" class="btn btn-sm btn-check-all"
                                                onclick="checkAllPrimaryTheologySubjects()">
                                                <i class="fas fa-check-double"></i> Check All
                                            </button>
                                            <button type="button" class="btn btn-sm btn-uncheck-all"
                                                onclick="uncheckAllPrimaryTheologySubjects()">
                                                <i class="fas fa-times-circle"></i> Uncheck All
                                            </button>
                                        </div>
                                    </div>
                                    <div class="row">
                                        @foreach ($primaryTheology ?? [] as $subject)
                                            <div class="col-lg-4 col-md-12">
                                                <div class="form-check">
                                                    <input class="form-check-input primary-theology-subject" type="checkbox"
                                                        id="primary-theology-{{ $loop->index }}" value="{{ $subject->md_id }}"
                                                        data-group="primary-theology">
                                                    <label class="form-check-label"
                                                        for="primary-theology-{{ $loop->index }}">{{ $subject->md_name }}</label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            {{-- ═══════════════════════════════════════════════
                            PRIMARY SECULAR SUBJECTS
                            ════════════════════════════════════════════════ --}}
                            <div id="primary-secular-subjects" style="display: none;">
                                <div class="subject-section-card">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h5 class="section-title mb-0">PRIMARY SECULAR SUBJECTS</h5>
                                        <div class="subject-control-buttons">
                                            <button type="button" class="btn btn-sm btn-check-all"
                                                onclick="checkAllPrimarySecularSubjects()">
                                                <i class="fas fa-check-double"></i> Check All
                                            </button>
                                            <button type="button" class="btn btn-sm btn-uncheck-all"
                                                onclick="uncheckAllPrimarySecularSubjects()">
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
                                                        value="{{ $subject->md_id }}" data-group="primary-secular">
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
                                                        value="{{ $subject->md_id }}" data-group="primary-secular">
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
                                                        value="{{ $subject->md_id }}" data-group="primary-secular">
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
                                                        id="primary-secular-p1-{{ $loop->index }}" value="{{ $subject->md_id }}"
                                                        data-group="primary-secular">
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
                                                        id="primary-secular-p2-{{ $loop->index }}" value="{{ $subject->md_id }}"
                                                        data-group="primary-secular">
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
                                                        id="primary-secular-p3-{{ $loop->index }}" value="{{ $subject->md_id }}"
                                                        data-group="primary-secular">
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
                                                        value="{{ $subject->md_id }}" data-group="primary-secular">
                                                    <label class="form-check-label"
                                                        for="primary-secular-p4p7-{{ $loop->index }}">{{ $subject->md_name }}</label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="mt-4 text-left">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-paper-plane"></i> Submit
                                    </button>
                                </div>
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
        function checkAllIdaadSubjects() {
            $('.idaad-subject').prop('checked', true);
            showToast('All O-Level subjects have been selected', 'success');
        }
        function uncheckAllIdaadSubjects() {
            $('.idaad-subject').prop('checked', false);
            showToast('All O-Level subjects have been deselected', 'info');
        }

        function checkAllThanawiSubjects() {
            $('.thanawi-subject').prop('checked', true);
            showToast('All A-Level subjects have been selected', 'success');
        }
        function uncheckAllThanawiSubjects() {
            $('.thanawi-subject').prop('checked', false);
            showToast('All A-Level subjects have been deselected', 'info');
        }

        function checkAllPrimaryTheologySubjects() {
            $('.primary-theology-subject').prop('checked', true);
            showToast('All Primary Theology subjects have been selected', 'success');
        }
        function uncheckAllPrimaryTheologySubjects() {
            $('.primary-theology-subject').prop('checked', false);
            showToast('All Primary Theology subjects have been deselected', 'info');
        }

        function checkAllPrimarySecularSubjects() {
            $('.primary-secular-subject').prop('checked', true);
            showToast('All Primary Secular subjects have been selected', 'success');
        }
        function uncheckAllPrimarySecularSubjects() {
            $('.primary-secular-subject').prop('checked', false);
            showToast('All Primary Secular subjects have been deselected', 'info');
        }

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
            let classTypes = @json($classTypeMap ?? []);

            // Initially hide all subject sections
            $('#idaad-subjects').hide();
            $('#thanawi-subjects').hide();
            $('#primary-theology-subjects').hide();
            $('#primary-secular-subjects').hide();

            // Handle class selection change
            $('#class_id').on('change', function () {
                let selectedClassId = $(this).val();

                if (!selectedClassId) {
                    $('#idaad-subjects, #thanawi-subjects, #primary-theology-subjects, #primary-secular-subjects').hide();
                    return;
                }

                let classType = classTypes[selectedClassId];
                console.log('Selected class:', selectedClassId, 'Type:', classType);

                // Hide all first, then show the right one
                $('#idaad-subjects, #thanawi-subjects, #primary-theology-subjects, #primary-secular-subjects').hide();
                $('input[type="checkbox"]').prop('checked', false);

                if (classType === 'O-Level') {
                    $('#idaad-subjects').show();
                } else if (classType === 'A-Level') {
                    $('#thanawi-subjects').show();
                } else if (classType === 'Primary Theology') {
                    $('#primary-theology-subjects').show();
                } else if (classType === 'Primary Secular') {
                    $('#primary-secular-subjects').show();
                }
            });

            // Form submission handler
            $('#createClassForm').on('submit', function (e) {
                e.preventDefault();

                let isValid = true;
                let $form = $(this);
                let $submitBtn = $form.find('button[type="submit"]');

                // Clear previous validation
                $form.find('.form-control, select').removeClass('is-invalid');
                $form.find('.invalid-feedback').remove();

                // Validate class
                let $classGroup = $('#class_id').closest('.form-group');
                if (!$('#class_id').val()) {
                    $('#class_id').addClass('is-invalid');
                    if ($classGroup.find('.invalid-feedback').length === 0) {
                        $classGroup.append('<div class="invalid-feedback d-block">Please select a class.</div>');
                    }
                    isValid = false;
                }

                // Validate stream
                let $streamGroup = $('#class_stream').closest('.form-group');
                if (!$('#class_stream').val().trim()) {
                    $('#class_stream').addClass('is-invalid');
                    if ($streamGroup.find('.invalid-feedback').length === 0) {
                        $streamGroup.append('<div class="invalid-feedback d-block">Please enter class stream.</div>');
                    }
                    isValid = false;
                }

                // Collect subjects based on selected class type
                let selectedClassId = $('#class_id').val();
                let classType = classTypes[selectedClassId];
                let selectedSubjects = [];

                if (classType === 'O-Level') {
                    $('.idaad-subject:checked').each(function () { selectedSubjects.push($(this).val()); });
                } else if (classType === 'A-Level') {
                    $('.thanawi-subject:checked').each(function () { selectedSubjects.push($(this).val()); });
                } else if (classType === 'Primary Theology') {
                    $('.primary-theology-subject:checked').each(function () { selectedSubjects.push($(this).val()); });
                } else if (classType === 'Primary Secular') {
                    $('.primary-secular-subject:checked').each(function () { selectedSubjects.push($(this).val()); });
                }

                if (!isValid) {
                    Swal.fire({ icon: 'error', title: 'Incomplete Form', text: 'Please fill in all required fields before submitting.' });
                    return;
                }

                if (selectedSubjects.length === 0) {
                    Swal.fire({ icon: 'error', title: 'No Subjects Selected', text: 'Please select at least one subject before submitting.' });
                    return;
                }

                Swal.fire({
                    title: 'Are you sure?',
                    text: "You are about to create this class.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, submit it!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $submitBtn.prop('disabled', true);
                        const originalBtnHtml = $submitBtn.html();
                        $submitBtn.html('Saving... <i class="fas fa-spinner fa-spin"></i>');

                        let dataToSend = {
                            class_id: $('#class_id').val(),
                            class_stream: $('#class_stream').val(),
                            subjects: selectedSubjects,
                            class_type: classType,
                            _token: '{{ csrf_token() }}'
                        };

                        $.ajax({
                            url: '{{ route("schools.class-store") }}',
                            method: 'POST',
                            data: JSON.stringify(dataToSend),
                            contentType: 'application/json',
                            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            success: function (response) {
                                if (response.fail) {
                                    Swal.fire({ icon: 'error', title: 'Error', text: response.message || 'Something went wrong.' });
                                } else {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Success!',
                                        text: response.message || 'Class has been created successfully.',
                                        confirmButtonText: 'OK'
                                    }).then((result) => {
                                        if (result.isConfirmed) { location.reload(); }
                                    });

                                    $form[0].reset();
                                    $('input[type="checkbox"]').prop('checked', false);
                                    $('#idaad-subjects, #thanawi-subjects, #primary-theology-subjects, #primary-secular-subjects').hide();
                                    $('.is-invalid').removeClass('is-invalid');
                                    $('.invalid-feedback').remove();
                                }
                            },
                            error: function (xhr) {
                                let errorMessage = 'An unexpected error occurred.';
                                if (xhr.responseJSON) {
                                    if (xhr.responseJSON.message) { errorMessage = xhr.responseJSON.message; }
                                    if (xhr.responseJSON.errors) {
                                        let validationErrors = '';
                                        for (const field in xhr.responseJSON.errors) {
                                            validationErrors += xhr.responseJSON.errors[field][0] + '\n';
                                        }
                                        errorMessage = validationErrors;
                                    }
                                }
                                Swal.fire({ icon: 'error', title: 'Submission Error', text: errorMessage });
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