@extends('layouts-side-bar.master')
@section('css')
    <link href="{{ URL::asset('assets/plugins/jvectormap/jqvmap.css') }}" rel="stylesheet" />
    <link href="{{ URL::asset('assets/plugins/datatable/dataTables.bootstrap4.min.css') }}" rel="stylesheet" />
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
            }

            .btn-check-all {
                background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
                border: none;
                color: white;
            }

            .btn-uncheck-all {
                background: linear-gradient(135deg, #dc3545 0%, #f86c6b 100%);
                border: none;
                color: white;
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

            .custom-mode-banner {
                background: #eef7ff;
                border: 1px solid #b6def7;
                color: #0c5987;
                padding: 12px 18px;
                border-radius: 8px;
                margin-bottom: 20px;
            }
        </style>

        <div class="row">
            <div class="col-lg-12 col-xl-12 col-md-12 col-sm-12">
                <div class="card bg-primary">
                    @include('layouts.class-buttons')
                    <div class="card-body bg-light">

                        <div class="custom-mode-banner">
                            <i class="fas fa-info-circle"></i>
                            Your school is using its own subject list. Manage subject names in
                            <a href="{{ route('school.custom-subjects.manage') }}"><strong>Manage My Subjects</strong></a>
                            before attaching them to a class here.
                        </div>

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

                            {{-- One flat, school-owned subject list per class type --}}
                            @foreach ([
                                'idaad' => ['O-Level', 'O-LEVEL SUBJECTS (IDAAD)'],
                                'thanawi' => ['A-Level', 'A-LEVEL SUBJECTS (THANAWI)'],
                                'primary_theology' => ['Primary Theology', 'PRIMARY THEOLOGY SUBJECTS'],
                                'primary_secular' => ['Primary Secular', 'PRIMARY SECULAR SUBJECTS'],
                            ] as $bucketKey => [$label, $title])
                                <div id="{{ str_replace('_', '-', $bucketKey) }}-subjects" style="display: none;">
                                    <div class="subject-section-card">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h5 class="section-title mb-0">{{ $title }}</h5>
                                            <div class="subject-control-buttons">
                                                <button type="button" class="btn btn-sm btn-check-all"
                                                    onclick="toggleBucket('{{ $bucketKey }}', true)">
                                                    <i class="fas fa-check-double"></i> Check All
                                                </button>
                                                <button type="button" class="btn btn-sm btn-uncheck-all"
                                                    onclick="toggleBucket('{{ $bucketKey }}', false)">
                                                    <i class="fas fa-times-circle"></i> Uncheck All
                                                </button>
                                            </div>
                                        </div>

                                        @php $bucketSubjects = ($customSubjectsByType[$bucketKey] ?? collect()); @endphp

                                        @if ($bucketSubjects->isEmpty())
                                            <p class="text-muted mb-0">
                                                You haven't added any {{ $label }} subjects yet.
                                                <a href="{{ route('school.custom-subjects.manage') }}">Add some here</a>.
                                            </p>
                                        @else
                                            <div class="row">
                                                @foreach ($bucketSubjects as $subject)
                                                    <div class="col-lg-4 col-md-6 col-12">
                                                        <div class="form-check">
                                                            <input class="form-check-input custom-subject-{{ $bucketKey }}"
                                                                type="checkbox" id="custom-subject-{{ $subject->id }}"
                                                                value="{{ $subject->id }}">
                                                            <label class="form-check-label" for="custom-subject-{{ $subject->id }}">
                                                                {{ $subject->subject_name }}
                                                            </label>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach

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
        const classTypeToBucket = {
            'O-Level': 'idaad',
            'A-Level': 'thanawi',
            'Primary Theology': 'primary_theology',
            'Primary Secular': 'primary_secular'
        };

        function toggleBucket(bucketKey, state) {
            $('.custom-subject-' + bucketKey).prop('checked', state);
            showToast(state ? 'All subjects selected' : 'All subjects deselected', state ? 'success' : 'info');
        }

        function showToast(message, type = 'success') {
            Swal.fire({
                icon: type,
                title: message,
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true
            });
        }

        $(document).ready(function () {
            let classTypes = @json($classTypeMap ?? []);
            const allBuckets = ['idaad', 'thanawi', 'primary_theology', 'primary_secular'];

            function hideAllBuckets() {
                allBuckets.forEach(b => $('#' + b.replace('_', '-') + '-subjects').hide());
            }

            hideAllBuckets();

            $('#class_id').on('change', function () {
                let selectedClassId = $(this).val();
                hideAllBuckets();
                $('input[type="checkbox"]:not(#no_stream)').prop('checked', false);

                if (!selectedClassId) return;

                let classType = classTypes[selectedClassId];
                let bucketKey = classTypeToBucket[classType];
                if (bucketKey) {
                    $('#' + bucketKey.replace('_', '-') + '-subjects').show();
                }
            });

            $('#no_stream').on('change', function () {
                const checked = $(this).is(':checked');
                $('#class_stream').prop('disabled', checked);
                if (checked) {
                    $('#class_stream').removeClass('is-invalid').val('');
                }
            });

            $('#createClassForm').on('submit', function (e) {
                e.preventDefault();

                let isValid = true;
                let $form = $(this);
                let $submitBtn = $form.find('button[type="submit"]');

                $form.find('.form-control, select').removeClass('is-invalid');
                $form.find('.invalid-feedback').remove();

                let $classGroup = $('#class_id').closest('.form-group');
                if (!$('#class_id').val()) {
                    $('#class_id').addClass('is-invalid');
                    $classGroup.append('<div class="invalid-feedback d-block">Please select a class.</div>');
                    isValid = false;
                }

                const noStream = $('#no_stream').is(':checked');
                if (!noStream) {
                    let $streamGroup = $('#class_stream').closest('.form-group');
                    if (!$('#class_stream').val().trim()) {
                        $('#class_stream').addClass('is-invalid');
                        $streamGroup.append('<div class="invalid-feedback d-block">Please enter a class stream, or tick "This class has no streams".</div>');
                        isValid = false;
                    }
                }

                let selectedClassId = $('#class_id').val();
                let classType = classTypes[selectedClassId];
                let bucketKey = classTypeToBucket[classType];
                let selectedSubjects = [];

                if (bucketKey) {
                    $('.custom-subject-' + bucketKey + ':checked').each(function () {
                        selectedSubjects.push($(this).val());
                    });
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