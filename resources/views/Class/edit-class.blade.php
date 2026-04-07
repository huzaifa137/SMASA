<?php
use App\Http\Controllers\Helper;
use App\Http\Controllers\Controller;
$controller = new Controller();
?>
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
    <!-- Student Dashboard -->
    <div class="side-app">

        <style>
            .form-check-input {
                transform: scale(1.5);
                margin-right: 10px;
            }

            .form-check-label {
                line-height: 1.5;
            }
        </style>
        <!-- HTML -->
        <div class="row">
            <div class="col-lg-12 col-xl-12 col-md-12 col-sm-12">
                <div class="card bg-primary">
                    @include('layouts.class-buttons')
                    <div class="card-body bg-light">
                        {{-- Form action will point to the update route, using the assignment's ID --}}
                        <form id="editSubjectAssignmentForm" action="{{ url('assign.subjects.update', $assignment->id) }}" method="POST">
                            @csrf
                            @method('PUT') {{-- Required for PUT/PATCH requests in Laravel forms --}}

                            <div class="row">
                                <div class="col-lg-6 col-md-12">
                                    <div class="form-group">
                                        <label class="form-label">Senior</label>
                                        <select class="form-control select2" id="class_id" name="class_id" disabled>
                                            <option value="">-- Select --</option>
                                            @foreach ($SecondaryClasses as $class)
                                                <option value="{{ $class->md_id }}" {{ $assignment->class_id == $class->md_id ? 'selected' : '' }}>
                                                    {{ $class->md_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        {{-- Hidden field to still send the value if the select is disabled --}}
                                        <input type="hidden" name="class_id" value="{{ $assignment->class_id }}">
                                    </div>
                                </div>
                                <div class="col-lg-6 col-md-12">
                                    <div class="form-group">
                                        <label class="form-label">Stream</label>
                                        <input type="text" class="form-control" value="{{ $assignment->stream_id }}" disabled>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-3 col-md-12">
                                    <label class="form-label">Technical Subjects</label>
                                    @foreach ($TECHNICAL_SUBJECTS as $subject)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox"
                                                id="technical-subject-{{ $subject->md_id }}"
                                                name="technical_subjects[]" {{-- IMPORTANT: Use array syntax for name --}}
                                                value="{{ $subject->md_id }}"
                                                {{ in_array($subject->md_id, $assignedSubjects['technical'] ?? []) ? 'checked' : '' }}>
                                            <label class="form-check-label"
                                                for="technical-subject-{{ $subject->md_id }}">{{ $subject->md_name }}</label>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="col-lg-3 col-md-12">
                                    <label class="form-label">Optionals</label>
                                    @foreach ($OPTIONALS as $subject)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox"
                                                id="optional-subject-{{ $subject->md_id }}"
                                                name="optionals[]" {{-- IMPORTANT: Use array syntax for name --}}
                                                value="{{ $subject->md_id }}"
                                                {{ in_array($subject->md_id, $assignedSubjects['optional'] ?? []) ? 'checked' : '' }}>
                                            <label class="form-check-label"
                                                for="optional-subject-{{ $subject->md_id }}">{{ $subject->md_name }}</label>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="col-lg-3 col-md-12">
                                    <label class="form-label">Vocationals</label>
                                    @foreach ($VOCATIONALS as $subject)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox"
                                                id="vocational-subject-{{ $subject->md_id }}"
                                                name="vocationals[]" {{-- IMPORTANT: Use array syntax for name --}}
                                                value="{{ $subject->md_id }}"
                                                {{ in_array($subject->md_id, $assignedSubjects['vocational'] ?? []) ? 'checked' : '' }}>
                                            <label class="form-check-label"
                                                for="vocational-subject-{{ $subject->md_id }}">{{ $subject->md_name }}</label>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="col-lg-3 col-md-12">
                                    <label class="form-label">Mathematics</label>
                                    @foreach ($MATHEMATICS as $subject)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox"
                                                id="math-subject-{{ $subject->md_id }}"
                                                name="mathematics[]" {{-- IMPORTANT: Use array syntax for name --}}
                                                value="{{ $subject->md_id }}"
                                                {{ in_array($subject->md_id, $assignedSubjects['mathematics'] ?? []) ? 'checked' : '' }}>
                                            <label class="form-check-label"
                                                for="math-subject-{{ $subject->md_id }}">{{ $subject->md_name }}</label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <br>

                            <div class="row">
                                <div class="col-lg-3 col-md-12">
                                    <label class="form-label">Languages</label>
                                    @foreach ($LANGUAGES as $subject)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox"
                                                id="language-subject-{{ $subject->md_id }}"
                                                name="languages[]" {{-- IMPORTANT: Use array syntax for name --}}
                                                value="{{ $subject->md_id }}"
                                                {{ in_array($subject->md_id, $assignedSubjects['language'] ?? []) ? 'checked' : '' }}>
                                            <label class="form-check-label"
                                                for="language-subject-{{ $subject->md_id }}">{{ $subject->md_name }}</label>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="col-lg-3 col-md-12">
                                    <label class="form-label">Sciences</label>
                                    @foreach ($SCIENCES as $subject)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox"
                                                id="science-subject-{{ $subject->md_id }}"
                                                name="sciences[]" {{-- IMPORTANT: Use array syntax for name --}}
                                                value="{{ $subject->md_id }}"
                                                {{ in_array($subject->md_id, $assignedSubjects['science'] ?? []) ? 'checked' : '' }}>
                                            <label class="form-check-label"
                                                for="science-subject-{{ $subject->md_id }}">{{ $subject->md_name }}</label>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="col-lg-3 col-md-12">
                                    <label class="form-label">Humanities</label>
                                    @foreach ($HUMANITIES as $subject)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox"
                                                id="humanity-subject-{{ $subject->md_id }}"
                                                name="humanities[]" {{-- IMPORTANT: Use array syntax for name --}}
                                                value="{{ $subject->md_id }}"
                                                {{ in_array($subject->md_id, $assignedSubjects['humanities'] ?? []) ? 'checked' : '' }}>
                                            <label class="form-check-label"
                                                for="humanity-subject-{{ $subject->md_id }}">{{ $subject->md_name }}</label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

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

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        $(document).ready(function () {
            $('#editSubjectAssignmentForm').on('submit', function (e) {
                e.preventDefault();

                let isValid = true;
                let $form = $(this);
                let $submitBtn = $form.find('button[type="submit"]');

                $form.find('.form-control, select').removeClass('is-invalid');
                $form.find('.invalid-feedback').remove();

                $form.find('input:not([type="checkbox"]):not([type="hidden"]), select').each(function () {
                    if (!$(this).val().trim()) {
                        $(this).addClass('is-invalid');

                        if ($(this).next('.invalid-feedback').length === 0) {
                            $(this).after(
                                '<div class="invalid-feedback">This field is required.</div>');
                        }
                        isValid = false;
                    }
                });

                const technicalSubjects = [];
                $('input[id^="technical-subject-"]:checked').each(function () {
                    technicalSubjects.push($(this).val());
                });

                const optionals = [];
                $('input[id^="optional-subject-"]:checked').each(function () {
                    optionals.push($(this).val());
                });

                const vocationals = [];
                $('input[id^="vocational-subject-"]:checked').each(function () {
                    vocationals.push($(this).val());
                });

                const mathematics = [];

                $('input[id^="math-subject-"]:checked, input[id^="math-subject-alt-"]:checked').each(function () {
                    mathematics.push($(this).val());
                });

                const languages = [];
                $('input[id^="language-subject-"]:checked').each(function () {
                    languages.push($(this).val());
                });

                const sciences = [];
                $('input[id^="science-subject-"]:checked').each(function () {
                    sciences.push($(this).val());
                });

                const humanities = [];
                $('input[id^="humanity-subject-"]:checked').each(function () {
                    humanities.push($(this).val());
                });

                if (!isValid) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Incomplete Form',
                        text: 'Please fill in all required fields before submitting.'
                    });
                    return;
                }

                // Merge all selected subjects
                const allSelectedSubjects = [
                    ...technicalSubjects,
                    ...optionals,
                    ...vocationals,
                    ...mathematics,
                    ...languages,
                    ...sciences,
                    ...humanities
                ];

                // 🚫 Prevent submitting if none selected
                if (allSelectedSubjects.length === 0) {
                    Swal.fire({
                        icon: 'error',
                        title: 'No Subjects Selected',
                        text: 'Please select at least one subject before updating.'
                    });
                    return;
                }

                Swal.fire({
                    title: 'Are you sure?',
                    text: "You are about to update class subjects.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, submit it!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {

                        $submitBtn.prop('disabled', true);
                        const originalBtnHtml = $submitBtn.html();
                        $submitBtn.html('Saving...<i class="fas fa-spinner fa-spin"></i>');

                        let dataToSend = {};
                        $.each($form.serializeArray(), function () {
                            if (dataToSend[this.name]) {

                                if (!Array.isArray(dataToSend[this.name])) {
                                    dataToSend[this.name] = [dataToSend[this.name]];
                                }
                                dataToSend[this.name].push(this.value);
                            } else {
                                dataToSend[this.name] = this.value;
                            }
                        });

                        dataToSend.technical_subjects = technicalSubjects;
                        dataToSend.optionals = optionals;
                        dataToSend.vocationals = vocationals;
                        dataToSend.mathematics = mathematics;
                        dataToSend.languages = languages;
                        dataToSend.sciences = sciences;
                        dataToSend.humanities = humanities;

                        $.ajax({
                            url: "{{ route('assign.subjects.update', $assignment->id) }}",
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
                                    return;
                                }

                                Swal.fire({
                                    icon: 'success',
                                    title: 'Updated!',
                                    text: response.message || 'Subjects updated successfully.',
                                    timer: 2000,
                                    showConfirmButton: false
                                }).then(() => {
                                    location.reload(); // reload after OK
                                });
                            },
                            error: function (xhr) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Update Failed',
                                    text: 'Something went wrong.'
                                });
                            },
                        //     error: function(data) {
                        // $('body').html(data.responseText);
                        // },
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
    <!-- c3.js Charts js-->
    <script src="{{ URL::asset('assets/plugins/charts-c3/d3.v5.min.js') }}"></script>
    <script src="{{ URL::asset('assets/plugins/charts-c3/c3-chart.js') }}"></script>
    <script src="{{ URL::asset('assets/js/charts.js') }}"></script>

    <!-- ECharts js -->
    <script src="{{ URL::asset('assets/plugins/echarts/echarts.js') }}"></script>
    <!-- Peitychart js-->
    <script src="{{ URL::asset('assets/plugins/peitychart/jquery.peity.min.js') }}"></script>
    <script src="{{ URL::asset('assets/plugins/peitychart/peitychart.init.js') }}"></script>
    <!-- Apexchart js-->
    <script src="{{ URL::asset('assets/js/apexcharts.js') }}"></script>
    <!--Moment js-->
    <script src="{{ URL::asset('assets/plugins/moment/moment.js') }}"></script>
    <!-- Daterangepicker js-->
    <script src="{{ URL::asset('assets/plugins/bootstrap-daterangepicker/daterangepicker.js') }}"></script>
    <script src="{{ URL::asset('assets/js/daterange.js') }}"></script>
    <!---jvectormap js-->
    <script src="{{ URL::asset('assets/plugins/jvectormap/jquery.vmap.js') }}"></script>
    <script src="{{ URL::asset('assets/plugins/jvectormap/jquery.vmap.world.js') }}"></script>
    <script src="{{ URL::asset('assets/plugins/jvectormap/jquery.vmap.sampledata.js') }}"></script>
    <!-- Index js-->
    <script src="{{ URL::asset('assets/js/index1.js') }}"></script>
    <!-- Data tables js-->
    <script src="{{ URL::asset('assets/plugins/datatable/js/jquery.dataTables.js') }}"></script>
    <script src="{{ URL::asset('assets/plugins/datatable/js/dataTables.bootstrap4.js') }}"></script>
    <script src="{{ URL::asset('assets/plugins/datatable/js/dataTables.buttons.min.js') }}"></script>
    <script src="{{ URL::asset('assets/plugins/datatable/js/buttons.bootstrap4.min.js') }}"></script>
    <script src="{{ URL::asset('assets/plugins/datatable/js/jszip.min.js') }}"></script>
    <script src="{{ URL::asset('assets/plugins/datatable/js/pdfmake.min.js') }}"></script>
    <script src="{{ URL::asset('assets/plugins/datatable/js/vfs_fonts.js') }}"></script>
    <script src="{{ URL::asset('assets/plugins/datatable/js/buttons.html5.min.js') }}"></script>
    <script src="{{ URL::asset('assets/plugins/datatable/js/buttons.print.min.js') }}"></script>
    <script src="{{ URL::asset('assets/plugins/datatable/js/buttons.colVis.min.js') }}"></script>
    <script src="{{ URL::asset('assets/plugins/datatable/dataTables.responsive.min.js') }}"></script>
    <script src="{{ URL::asset('assets/plugins/datatable/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ URL::asset('assets/js/datatables.js') }}"></script>
    <!--Counters -->
    <script src="{{ URL::asset('assets/plugins/counters/counterup.min.js') }}"></script>
    <script src="{{ URL::asset('assets/plugins/counters/waypoints.min.js') }}"></script>
    <!--Chart js -->
    <script src="{{ URL::asset('assets/plugins/chart/chart.bundle.js') }}"></script>
    <script src="{{ URL::asset('assets/plugins/chart/utils.js') }}"></script>
@endsection