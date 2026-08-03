<?php
use App\Models\Classroom;
use App\Http\Controllers\Helper;
use App\Http\Controllers\Controller;
use App\Helpers\PermissionHelper;
$controller = new Controller();
?>
@extends('layouts-side-bar.master')
@section('css')
    <style>
        @keyframes highlightFlash {
            0%, 100% { background-color: #fff3cd; }
            50% { background-color: #ffe9a8; }
        }
        tr.highlighted-row {
            animation: highlightFlash 1.1s ease-in-out 2;
            outline: 2px solid #f0ad4e;
        }
    </style>
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

        <!-- HTML -->
        <div class="row">
            <div class="col-lg-12 col-xl-12 col-md-12 col-sm-12">
                <div class="card bg-primary">
                    @include('layouts.class-buttons')
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped card-table table-vcenter text-nowrap mb-0"
                                id="termDatesTable">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Class</th>
                                        <th>Stream</th>
                                        <th>Subject</th>
                                        <th>Students</th>
                                        <th>Assessment Type</th>
                                        <th>Subject Teacher (1)</th>
                                        <th>Subject Teacher (2)</th>
                                        <!-- <th colspan="2" style="text-align: center">Action</th> -->
                                    </tr>
                                </thead>
                                <tbody> @forelse ($classSubjects as $key => $class)
                                    <?php
                                        $classInfo = DB::table('class_stream_assignments')->where('school_id',Helper::requireSchool())->where('class_id',$class->class_id)->where('stream_id',$class->stream_id)->first();
                                   ?>

                                    <tr data-id="{{ $class->id }}" id="subject-row-{{ $class->id }}">
                                        <td style="width:1px;">{{ $key + 1 }}</td>
                                        <td>{{ Helper::recordMdname($classInfo->class_id) }}</td>
                                        <td>{{ $classInfo->stream_id === \App\Http\Controllers\ClassandSubjectController::NO_STREAM_SENTINEL ? 'No Stream' : $classInfo->stream_id }}</td>
                                        <td>{{ $class->display_name }}</td>
                                        <td>0</td>
                                        <td>
                                            @if(PermissionHelper::canFeature('edit_class'))
                                                <select name="assessment_scale_id"
                                                    class="form-select form-select-sm assign-assessment-scale form-control"
                                                    data-class-subject-id="{{ $class->id }}"
                                                    data-original-scale-id="{{ $class->assessment_scale_id ?? '' }}"
                                                    style="min-width:170px;">
                                                    <option value="">Numeric marks (default)</option>
                                                    @foreach ($assessmentScales as $scale)
                                                        <option value="{{ $scale->id }}"
                                                            {{ $class->assessment_scale_id == $scale->id ? 'selected' : '' }}>
                                                            {{ $scale->name }} ({{ rtrim(rtrim(number_format($scale->min_score, 2), '0'), '.') }}–{{ rtrim(rtrim(number_format($scale->max_score, 2), '0'), '.') }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @if ($assessmentScales->isEmpty())
                                                    <div class="text-muted mt-1" style="font-size:.72rem;">
                                                        <a href="{{ route('examination.assessment-scales.index') }}" target="_blank">Create a scale</a> to enable comment-based grading.
                                                    </div>
                                                @endif
                                            @else
                                                <span>
                                                    {{ $class->assessment_scale_id
                                                        ? ($assessmentScales->firstWhere('id', $class->assessment_scale_id)->name ?? 'Custom scale')
                                                        : 'Numeric marks' }}
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                @if(PermissionHelper::canFeature('assign_subject_teachers'))
                                                    <select name="teacher_id"
                                                        class="form-select form-select-sm assign-subject-teacher-1 form-control"
                                                        data-class-id="{{ $class->id }}"
                                                        data-current-supervisor="{{ $class->subject_teacher_1 }}"
                                                        {{ $class->subject_teacher_1 ? 'disabled' : '' }}>
                                                        <option value="">Assign Teacher</option>
                                                        @foreach ($Teachers as $teacher)
                                                            <option value="{{ $teacher->id }}"
                                                                {{ $class->subject_teacher_1 == $teacher->id ? 'selected' : '' }}>
                                                                {{ $teacher->surname }} {{ $teacher->firstname }}
                                                            </option>
                                                        @endforeach
                                                    </select>

                                                    @if ($class->subject_teacher_1)
                                                    &nbsp;
                                                        <button class="btn btn-md btn-danger btn-remove-subject-teacher-1"
                                                            data-class-id="{{ $class->id }}" title="Remove Subject Teacher 1">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </button>
                                                    @endif
                                                @else
                                                    <span>{{ $class->subject_teacher_1 ? Helper::recordMdname($class->subject_teacher_1) : 'Not Assigned' }}</span>
                                                @endif
                                            </div>
                                        </td>
                                         <td>
                                            <div class="d-flex align-items-center gap-2">
                                                @if(PermissionHelper::canFeature('assign_subject_teachers'))
                                                    <select name="teacher_id"
                                                        class="form-select form-select-sm assign-subject-teacher-2 form-control"
                                                        data-class-id="{{ $class->id }}"
                                                        data-current-supervisor="{{ $class->subject_teacher_2 }}"
                                                        {{ $class->subject_teacher_2 ? 'disabled' : '' }}>
                                                        <option value="">Assign Teacher</option>
                                                        @foreach ($Teachers as $teacher)
                                                            <option value="{{ $teacher->id }}"
                                                                {{ $class->subject_teacher_2 == $teacher->id ? 'selected' : '' }}>
                                                                {{ $teacher->surname }} {{ $teacher->firstname }}
                                                            </option>
                                                        @endforeach
                                                    </select>

                                                    @if ($class->subject_teacher_2)
                                                    &nbsp;
                                                        <button class="btn btn-md btn-danger btn-remove-subject-teacher-2"
                                                            data-class-id="{{ $class->id }}" title="Remove Subject Teacher 1">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </button>
                                                    @endif
                                                @else
                                                    <span>{{ $class->subject_teacher_2 ? Helper::recordMdname($class->subject_teacher_2) : 'Not Assigned' }}</span>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                        <tr>
                                            <td colspan="8" class="text-center">No classes found.</td>
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
    </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    $(document).ready(function () {

        // Arriving from a "Set assessment scale for this subject" link on
        // the marks-entry page (?highlight=<class_subject_id>): scroll to
        // that row and flash it so it's easy to find among the full list.
        (function () {
            const params = new URLSearchParams(window.location.search);
            const highlightId = params.get('highlight');
            if (!highlightId) return;

            const $row = $(`#subject-row-${highlightId}`);
            if (!$row.length) return;

            setTimeout(() => {
                $row[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
                $row.addClass('highlighted-row');
                $row.find('.assign-assessment-scale').first().focus();
            }, 200);
        })();

        // Assessment Type (numeric marks vs. a comment/mark scale) per subject
        $('.assign-assessment-scale').on('change', function () {
            let $select = $(this);
            let classSubjectId = $select.data('class-subject-id');
            let scaleId = $select.val();
            let originalScaleId = String($select.data('original-scale-id') ?? '');

            // Some page scripts / browser autofill can fire a 'change' event
            // on selects that the user never actually touched. Comparing
            // against the value the row was rendered with — instead of
            // reacting to every 'change' event — is what stops the
            // "Subject switched back to numeric marks" toast from firing
            // on every row on page load.
            if (String(scaleId) === originalScaleId) {
                return;
            }

            $select.data('original-scale-id', scaleId);

            $.ajax({
                url: "{{ route('examination.assessment-scales.assign') }}",
                type: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    class_subject_id: classSubjectId,
                    assessment_scale_id: scaleId
                },
                success: function (response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Updated!',
                            text: response.message,
                            timer: 1500,
                            showConfirmButton: false
                        });
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function (xhr) {
                    const message = xhr.responseJSON?.message
                        || (xhr.responseJSON?.errors ? Object.values(xhr.responseJSON.errors).flat().join('\n') : 'Something went wrong.');
                    Swal.fire('Error', message, 'error');
                }
            });
        });

        $('.assign-subject-teacher-1').on('change', function () {
            let classId = $(this).data('class-id');
            let teacherId = $(this).val();
            let selectElement = $(this);

            let current = selectElement.data('current-supervisor');
            if (teacherId == current) {
                return; 
            }

            if (teacherId !== '') {
                $.ajax({
                    url: "{{ route('class.assignSubjectTeacher1') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        subject_id: classId,
                        teacher_id: teacherId
                    },
                    success: function (response) {
                        if (response.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Assigned!',
                                text: 'Subject Teacher 1 assigned successfully.',
                                timer: 1500,
                                showConfirmButton: false
                            });
                            selectElement.prop('disabled', true);
                            setTimeout(() => location.reload(), 1600);
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    },
                   error: function(xhr) {
    if (xhr.status === 422) {
        let errors = xhr.responseJSON.errors;
        let message = Object.values(errors).flat().join("\n");

        Swal.fire('Validation Error', message, 'error');
    } 
    else if (xhr.status === 403) {
        Swal.fire('Unauthorized', xhr.responseJSON.message, 'error');
    } 
    else {
        Swal.fire('Oops', 'Something went wrong. Try again.', 'error');
    }
}
                    // error: function(data) {
                    // $('body').html(data.responseText);
                    // }
                });
            }
        });

        // Remove Supervisor
        $('.btn-remove-subject-teacher-1').on('click', function () {
            let classId = $(this).data('class-id');
            Swal.fire({
                title: 'Are you sure?',
                text: "Remove assigned Subject Teacher 1 ?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, remove it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('class.removeSubjectTeacher1') }}",
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            subject_id: classId
                        },
                        success: function (response) {
                            if (response.status === 'success') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Removed!',
                                    text: 'Subject Teacher 1 removed successfully.',
                                    timer: 1500,
                                    showConfirmButton: false
                                });
                                setTimeout(() => location.reload(), 1600);
                            } else {
                                Swal.fire('Error', response.message, 'error');
                            }
                        },
                        // error: function () {
                        //     Swal.fire('Oops', 'Something went wrong.', 'error');
                        // }
                        error: function(data) {
                    $('body').html(data.responseText);
                    }
                    });
                }
            });
        });
    });

      $(document).ready(function () {
        
        $('.assign-subject-teacher-2').on('change', function () {
            let classId = $(this).data('class-id');
            let teacherId = $(this).val();
            let selectElement = $(this);

            let current = selectElement.data('current-supervisor');
            if (teacherId == current) {
                return; 
            }

            if (teacherId !== '') {
                $.ajax({
                    url: "{{ route('class.assignSubjectTeacher2') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        subject_id: classId,
                        teacher_id: teacherId
                    },
                    success: function (response) {
                        if (response.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Assigned!',
                                text: 'Subject Teacher 2 assigned successfully.',
                                timer: 1500,
                                showConfirmButton: false
                            });
                            selectElement.prop('disabled', true);
                            setTimeout(() => location.reload(), 1600);
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    },
                   error: function(xhr) {
    if (xhr.status === 422) {
        let errors = xhr.responseJSON.errors;
        let message = Object.values(errors).flat().join("\n");

        Swal.fire('Validation Error', message, 'error');
    } 
    else if (xhr.status === 403) {
        Swal.fire('Unauthorized', xhr.responseJSON.message, 'error');
    } 
    else {
        Swal.fire('Oops', 'Something went wrong. Try again.', 'error');
    }
}
                    // error: function(data) {
                    // $('body').html(data.responseText);
                    // }
                });
            }
        });

        // Remove Supervisor
        $('.btn-remove-subject-teacher-2').on('click', function () {
            let classId = $(this).data('class-id');
            Swal.fire({
                title: 'Are you sure?',
                text: "Remove assigned Subject Teacher 2 ?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, remove it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('class.removeSubjectTeacher2') }}",
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            subject_id: classId
                        },
                        success: function (response) {
                            if (response.status === 'success') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Removed!',
                                    text: 'Subject Teacher 2 removed successfully.',
                                    timer: 1500,
                                    showConfirmButton: false
                                });
                                setTimeout(() => location.reload(), 1600);
                            } else {
                                Swal.fire('Error', response.message, 'error');
                            }
                        },
                         error: function(xhr) {
    if (xhr.status === 422) {
        let errors = xhr.responseJSON.errors;
        let message = Object.values(errors).flat().join("\n");

        Swal.fire('Validation Error', message, 'error');
    } 
    else if (xhr.status === 403) {
        Swal.fire('Unauthorized', xhr.responseJSON.message, 'error');
    } 
    else {
        Swal.fire('Oops', 'Something went wrong. Try again.', 'error');
    }
}
                        // error: function () {
                        //     Swal.fire('Oops', 'Something went wrong.', 'error');
                        // }
                    //     error: function(data) {
                    // $('body').html(data.responseText);
                    // }
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