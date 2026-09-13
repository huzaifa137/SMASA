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

            #secondary-alevel-combo-status {
    color: #fff !important;
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
                                            {{-- A school with merged School Product categories (see
                                                 "Manage School Products") has BOTH of these populated at
                                                 once, so both group of classes show together here instead
                                                 of only whichever one used to come first. --}}
                                            @if(isset($SecondaryClasses) && $SecondaryClasses->isNotEmpty())
                                                <optgroup label="Secondary">
                                                    @foreach ($SecondaryClasses as $class)
                                                        <option value="{{ $class->md_id }}"
                                                            data-type="{{ $classTypeMap[$class->md_id] ?? 'Unknown' }}">
                                                            {{ $class->md_name }}
                                                        </option>
                                                    @endforeach
                                                </optgroup>
                                            @endif
                                            @if(isset($PrimaryClasses) && $PrimaryClasses->isNotEmpty())
                                                <optgroup label="Primary">
                                                    @foreach ($PrimaryClasses as $class)
                                                        <option value="{{ $class->md_id }}"
                                                            data-type="{{ $classTypeMap[$class->md_id] ?? 'Unknown' }}">
                                                            {{ $class->md_name }}
                                                        </option>
                                                    @endforeach
                                                </optgroup>
                                            @endif
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-6 col-md-12">
                                    <div class="form-group">
                                        <label class="form-label">Stream</label>
                                        <input type="text" name="class_stream" id="class_stream" class="form-control"
                                            value="{{ old('class_stream') }}" placeholder="Enter Class Stream">
                                        <div class="form-check mt-2">
                                            <input class="form-check-input" type="checkbox" id="no_stream" name="no_stream" value="1">
                                            <label class="form-check-label" for="no_stream">
                                                This class has no streams
                                            </label>
                                        </div>
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

                            {{-- ═══════════════════════════════════════════════
                            SECONDARY O-LEVEL SUBJECTS
                            ════════════════════════════════════════════════ --}}
                            <div id="secondary-olevel-subjects" style="display: none;">
                                <div class="subject-section-card">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h5 class="section-title mb-0">O - LEVEL SUBJECTS (SECONDARY)</h5>
                                        <div class="subject-control-buttons">
                                            <button type="button" class="btn btn-sm btn-check-all"
                                                onclick="checkAllSecondaryOLevelSubjects()">
                                                <i class="fas fa-check-double"></i> Check All
                                            </button>
                                            <button type="button" class="btn btn-sm btn-uncheck-all"
                                                onclick="uncheckAllSecondaryOLevelSubjects()">
                                                <i class="fas fa-times-circle"></i> Uncheck All
                                            </button>
                                        </div>
                                    </div>
                                    <div class="row">
                                        @foreach ($SECONDARY_OLEVEL_SUBJECTS ?? [] as $subject)
                                            <div class="col-lg-4 col-md-12">
                                                <div class="form-check">
                                                    <input class="form-check-input secondary-olevel-subject" type="checkbox"
                                                        id="secondary-olevel-{{ $loop->index }}" value="{{ $subject->md_id }}"
                                                        data-group="secondary-olevel">
                                                    <label class="form-check-label"
                                                        for="secondary-olevel-{{ $loop->index }}">{{ $subject->md_name }}</label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            {{-- ═══════════════════════════════════════════════
                            SECONDARY A-LEVEL SUBJECTS — this is the POOL of
                            subjects offered to this class/stream, not one
                            student's combination. A given Senior 5/6 class
                            normally has students on several different
                            combinations (e.g. PCM and HEG in the same
                            stream), each picking their own 3 principals from
                            whatever the class offers — that per-student
                            pairing happens separately, wherever this school
                            builds student combinations. All this step needs
                            to guarantee is that General Paper (compulsory
                            for every A-Level student regardless of
                            combination) is part of the pool — so it's
                            auto-checked and locked below rather than being
                            just another optional checkbox.
                            ════════════════════════════════════════════════ --}}
                            <div id="secondary-alevel-subjects" style="display: none;">
                                <div class="subject-section-card">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h5 class="section-title mb-0">A - LEVEL SUBJECTS (SECONDARY)</h5>
                                        <div class="subject-control-buttons">
                                            <button type="button" class="btn btn-sm btn-check-all"
                                                onclick="checkAllSecondaryALevelSubjects()">
                                                <i class="fas fa-check-double"></i> Check All
                                            </button>
                                            <button type="button" class="btn btn-sm btn-uncheck-all"
                                                onclick="uncheckAllSecondaryALevelSubjects()">
                                                <i class="fas fa-times-circle"></i> Uncheck All
                                            </button>
                                        </div>
                                    </div>

                                    <div id="secondary-alevel-combo-status" class="alert alert-secondary py-2 px-3 mb-3" style="font-size: 0.9rem;">
                                        Select every subject this class/stream should offer. <strong>General Paper</strong> is compulsory and included automatically.
                                        Each student's own 3 principal subjects (plus, optionally, one subsidiary) are chosen separately when building that student's combination.
                                    </div>

                                    @foreach ([
                                        'General' => 'General Paper (compulsory)',
                                        'Principal - Arts' => 'Principal Subjects — Arts',
                                        'Principal - Sciences' => 'Principal Subjects — Sciences',
                                        'Subsidiary' => 'Subsidiary Subjects',
                                    ] as $groupKey => $groupLabel)
                                        @php $groupSubjects = ($SECONDARY_ALEVEL_SUBJECTS_GROUPED[$groupKey] ?? collect()); @endphp
                                        @if ($groupSubjects->isNotEmpty())
                                            <h6 class="text-muted mt-3 mb-2">{{ $groupLabel }}</h6>
                                            <div class="row">
                                                @foreach ($groupSubjects as $subject)
                                                    <div class="col-lg-4 col-md-12">
                                                        <div class="form-check">
                                                            <input class="form-check-input secondary-alevel-subject secondary-alevel-{{ str_replace(['Principal - ', ' '], ['', '-'], strtolower($groupKey)) }}"
                                                                type="checkbox"
                                                                id="secondary-alevel-{{ $subject->md_id }}" value="{{ $subject->md_id }}"
                                                                data-group="{{ $groupKey }}"
                                                                @if ($groupKey === 'General') checked disabled @endif>
                                                            <label class="form-check-label"
                                                                for="secondary-alevel-{{ $subject->md_id }}">{{ $subject->md_name }}</label>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    @endforeach
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

        function checkAllSecondaryOLevelSubjects() {
            $('.secondary-olevel-subject').prop('checked', true);
            showToast('All Secondary O-Level subjects have been selected', 'success');
        }
        function uncheckAllSecondaryOLevelSubjects() {
            $('.secondary-olevel-subject').prop('checked', false);
            showToast('All Secondary O-Level subjects have been deselected', 'info');
        }

        function checkAllSecondaryALevelSubjects() {
            $('.secondary-alevel-subject:not(:disabled)').prop('checked', true);
            updateSecondaryALevelComboStatus();
            showToast('All Secondary A-Level subjects have been selected', 'success');
        }
        function uncheckAllSecondaryALevelSubjects() {
            // General Paper is compulsory and locked — leave it checked even
            // on "Uncheck All", same as it can't be unchecked individually.
            $('.secondary-alevel-subject:not(:disabled)').prop('checked', false);
            updateSecondaryALevelComboStatus();
            showToast('All Secondary A-Level subjects have been deselected (General Paper stays, it\'s compulsory)', 'info');
        }

        // This picker builds the class/stream's subject POOL, not one
        // student's combination — a class normally offers several
        // combinations at once (e.g. PCM and HEG in the same Senior 5
        // stream), so there's no "exactly 3" count to enforce here. Each
        // student's own principal subjects (and optional subsidiary) are
        // chosen separately when building that student's combination. This
        // just reflects how many subjects are currently selected for the
        // pool, and confirms General Paper (always locked on) is included.
        function updateSecondaryALevelComboStatus() {
            const $status = $('#secondary-alevel-combo-status');
            if ($status.length === 0) return;

            const principalCount = $('.secondary-alevel-subject[data-group^="Principal"]:checked').length;
            const subsidiaryCount = $('.secondary-alevel-subject[data-group="Subsidiary"]:checked').length;

            $status.removeClass('alert-secondary alert-success alert-warning').addClass('alert-secondary').html(
                'General Paper is compulsory and included automatically. ' +
                principalCount + ' principal subject' + (principalCount === 1 ? '' : 's') + ' and ' +
                subsidiaryCount + ' subsidiary subject' + (subsidiaryCount === 1 ? '' : 's') +
                ' currently offered to this class — students pick their own 3 principals (plus, optionally, one subsidiary) when their individual combination is built.'
            );
        }

        $(document).on('change', '.secondary-alevel-subject', updateSecondaryALevelComboStatus);

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
            $('#secondary-olevel-subjects').hide();
            $('#secondary-alevel-subjects').hide();

            // Handle class selection change
            $('#class_id').on('change', function () {
                let selectedClassId = $(this).val();

                if (!selectedClassId) {
                    $('#idaad-subjects, #thanawi-subjects, #primary-theology-subjects, #primary-secular-subjects, #secondary-olevel-subjects, #secondary-alevel-subjects').hide();
                    return;
                }

                let classType = classTypes[selectedClassId];
                console.log('Selected class:', selectedClassId, 'Type:', classType);

                // Hide all first, then show the right one
                $('#idaad-subjects, #thanawi-subjects, #primary-theology-subjects, #primary-secular-subjects, #secondary-olevel-subjects, #secondary-alevel-subjects').hide();
                $('input[type="checkbox"]:not(#no_stream)').prop('checked', false);

                if (classType === 'O-Level') {
                    $('#idaad-subjects').show();
                } else if (classType === 'A-Level') {
                    $('#thanawi-subjects').show();
                } else if (classType === 'Primary Theology') {
                    $('#primary-theology-subjects').show();
                } else if (classType === 'Primary Secular') {
                    $('#primary-secular-subjects').show();
                } else if (classType === 'Secondary O-Level') {
                    $('#secondary-olevel-subjects').show();
                } else if (classType === 'Secondary A-Level') {
                    $('#secondary-alevel-subjects').show();
                    // The bulk uncheck above also unchecks disabled inputs —
                    // General Paper is compulsory, so lock it back on.
                    $('.secondary-alevel-subject[data-group="General"]').prop('checked', true);
                    updateSecondaryALevelComboStatus();
                }
            });

            // "This class has no streams" — disable/clear the stream input
            // while checked, same behavior create-class-custom.blade.php uses.
            $('#no_stream').on('change', function () {
                const checked = $(this).is(':checked');
                $('#class_stream').prop('disabled', checked);
                if (checked) {
                    $('#class_stream').removeClass('is-invalid').val('');
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

                // Validate stream (unless this class has been marked as
                // having no streams)
                const noStream = $('#no_stream').is(':checked');
                if (!noStream) {
                    let $streamGroup = $('#class_stream').closest('.form-group');
                    if (!$('#class_stream').val().trim()) {
                        $('#class_stream').addClass('is-invalid');
                        if ($streamGroup.find('.invalid-feedback').length === 0) {
                            $streamGroup.append('<div class="invalid-feedback d-block">Please enter class stream, or tick "This class has no streams".</div>');
                        }
                        isValid = false;
                    }
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
                } else if (classType === 'Secondary O-Level') {
                    $('.secondary-olevel-subject:checked').each(function () { selectedSubjects.push($(this).val()); });
                } else if (classType === 'Secondary A-Level') {
                    $('.secondary-alevel-subject:checked').each(function () { selectedSubjects.push($(this).val()); });
                }

                if (!isValid) {
                    Swal.fire({ icon: 'error', title: 'Incomplete Form', text: 'Please fill in all required fields before submitting.' });
                    return;
                }

                if (selectedSubjects.length === 0) {
                    Swal.fire({ icon: 'error', title: 'No Subjects Selected', text: 'Please select at least one subject before submitting.' });
                    return;
                }

                // Note: Secondary A-Level intentionally has no "must pick a
                // principal subject" requirement here. General Paper is the
                // only thing compulsory for the class itself (it's locked on
                // above); which principal subjects and subsidiary each
                // student takes is a per-student decision made later when
                // that student's own combination is built — it can't be
                // enforced on the whole class/stream at once.

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
                            class_stream: noStream ? '' : $('#class_stream').val(),
                            no_stream: noStream ? 1 : 0,
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
                                    $('#idaad-subjects, #thanawi-subjects, #primary-theology-subjects, #primary-secular-subjects, #secondary-olevel-subjects, #secondary-alevel-subjects').hide();
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