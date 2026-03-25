<?php
use App\Models\Classroom;
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

        <!-- HTML -->
        <div class="row">
            <div class="col-lg-12 col-xl-12 col-md-12 col-sm-12">
                <div class="card bg-primary">
                    <div class="card-header">
                        <div class="row w-100 g-2">
                            <div class="col-12 col-sm-4 mb-2 mb-sm-0">
                                <a href="{{ route('school.allSchools') }}" class="btn btn-info w-100">
                                    <i class="fas fa-chalkboard-teacher me-2"></i> My Classes
                                </a>
                            </div>
                            <div class="col-12 col-sm-4 mb-2 mb-sm-0">
                                <a href="{{ route('manage.classes') }}" class="btn btn-info w-100">
                                    <i class="fas fa-sliders-h me-2"></i> Manage Classes
                                </a>
                            </div>
                            <div class="col-12 col-sm-4 mb-2 mb-sm-0">
                                <a href="{{ route('school.create-class') }}" class="btn btn-info w-100">
                                    <i class="fas fa-plus-circle me-2"></i> Add New Class
                                </a>
                            </div>
                        </div>
                    </div>
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
                                        <th>Subject Teacher (1)</th>
                                        <th>Subject Teacher (2)</th>
                                        <th colspan="2" style="text-align: center">Action</th>
                                    </tr>
                                </thead>
                                <tbody> @forelse ($classSubjects as $key => $class)
                                    <?php
                                        $classInfo = DB::table('class_stream_assignments')->where('id',$class->class_stream_assignment_id)->first();
                                   ?>

                                    <tr data-id="{{ $class->id }}">
                                        <td style="width:1px;">{{ $key + 1 }}</td>
                                        <td>{{ Helper::recordMdname($classInfo->class_id) }}</td>
                                        <td>{{ Helper::recordMdname($classInfo->stream_id) }}</td>
                                        <td>{{ Helper::recordMdname($class->subject_id) }}</td>
                                        <td>0</td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
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
                                            </div>
                                        </td>
                                         <td>
                                            <div class="d-flex align-items-center gap-2">
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
                                            </div>
                                        </td>
                                        <td style="text-align: center;">
                                            <a href="#" class="btn btn-sm btn-info">
                                                <i class="fas fa-users me-2"></i> Students Per Subject
                                            </a>
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
                    // error: function () {
                    //     Swal.fire('Oops', 'Something went wrong. Try again.', 'error');
                    // }
                    error: function(data) {
                    $('body').html(data.responseText);
                    }
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
                    // error: function () {
                    //     Swal.fire('Oops', 'Something went wrong. Try again.', 'error');
                    // }
                    error: function(data) {
                    $('body').html(data.responseText);
                    }
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