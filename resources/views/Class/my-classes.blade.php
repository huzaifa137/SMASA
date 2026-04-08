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
                    @include('layouts.class-buttons')
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped card-table table-vcenter text-nowrap mb-0"
                                id="termDatesTable">
                                <thead>
                                    <tr>
                                        <th colspan="8" class="bg-white text-primary text-center">
                                            Class Supervisor
                                        </th>
                                    </tr>
                                    <tr>
                                        <th>#</th>
                                        <th>Senior</th>
                                        <th>Boys</th>
                                        <th>Girls</th>
                                        <th>Total Students</th>
                                        <th>Class Supervisor</th>
                                    </tr>
                                </thead>
                                <tbody> @forelse ($classRecord as $key => $class)

                                        <tr data-id="{{ $class->id }}">
                                            <td style="width:1px;">{{ $key + 1 }}</td>
                                            <td>{{ Helper::recordMdname($class->class_name) }}</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <select name="teacher_id"
                                                        class="form-select form-select-sm assign-supervisor form-control"
                                                        data-class-id="{{ $class->id }}"
                                                        data-current-supervisor="{{ $class->class_supervisor }}"
                                                        {{ $class->class_supervisor ? 'disabled' : '' }}>
                                                        <option value="">Select Supervisor</option>
                                                        @foreach ($Teachers as $teacher)
                                                            <option value="{{ $teacher->id }}"
                                                                {{ $class->class_supervisor == $teacher->id ? 'selected' : '' }}>
                                                                {{ $teacher->surname }} {{ $teacher->firstname }} (You)
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center">No class supervisor role is assigned to you at the moment.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped card-table table-vcenter text-nowrap mb-0"
                                id="termDatesTable">
                                <thead>
                                    <tr>
                                        <th colspan="8" class="bg-primary text-white text-center">
                                            Stream - Class Teacher
                                        </th>
                                    </tr>
                                    <tr>
                                        <th class="text-center">#</th>
                                        <th class="text-center">Class</th>
                                        <th class="text-center">Stream</th>
                                        <th class="text-center">Boys</th>
                                        <th class="text-center">Girls</th>
                                        <th class="text-center">Total Students</th>
                                        <th class="text-center">Class Teacher</th>
                                        <th colspan="2" class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($Streams as $key => $stream)
                                        <tr data-id="{{ $stream->id }}">
                                            <td class="text-center" style="width:1px;">{{ $key + 1 }}</td>
                                            <td class="text-center">{{ Helper::recordMdname($stream->class_id) }}</td>
                                            <td class="text-center">{{ $stream->stream_id }}</td>
                                            <td class="text-center">0</td>
                                            <td class="text-center">0</td>
                                            <td class="text-center">0</td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2 justify-content-center">
                                                    <select name="teacher_id"
                                                        class="form-select form-select-sm assign-class-teacher form-control"
                                                        data-class-id="{{ $stream->id }}"
                                                        data-current-supervisor="{{ $stream->class_teacher }}"
                                                        {{ $stream->class_teacher ? 'disabled' : '' }}>
                                                        <option value="">Select Class Teacher</option>
                                                        @foreach ($Teachers as $teacher)
                                                            <option value="{{ $teacher->id }}"
                                                                {{ $stream->class_teacher == $teacher->id ? 'selected' : '' }}>
                                                                {{ $teacher->surname }} {{ $teacher->firstname }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <a href="{{ route('class.stream.subjects', ['classId' => $stream->class_id, 'streamId' => $stream->stream_id]) }}"
                                                    class="btn btn-sm btn-dark mb-1">
                                                    <i class="fas fa-graduation-cap me-2"></i> Manage Subjects
                                                </a>

                                                <a href="{{ route('school.edit-class-subject', ['classId' => $stream->class_id, 'streamId' => $stream->stream_id]) }}" class="btn btn-sm btn-info btn-edit-stream mb-1"
                                                    data-stream-id="{{ $stream->id }}">
                                                    <i class="fas fa-pen-to-square me-2"></i> Edit Subjects
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center">No class streams are assigned to you at the moment.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped card-table table-vcenter text-nowrap mb-0"
                                id="termDatesTable">
                                <thead>
                                    <th colspan="8" class="bg-primary text-white text-center">
                                            Subject Teachers
                                        </th>
                                    <tr>
                                        <th>#</th>
                                        <th>Class</th>
                                        <th>Stream</th>
                                        <th>Subject</th>
                                        <th>Students</th>
                                        <th>Subject Teacher (1)</th>
                                        <th>Subject Teacher (2)</th>
                                        <!-- <th colspan="2" style="text-align: center">Action</th> -->
                                    </tr>
                                </thead>
                                <tbody> @forelse ($classSubjects as $key => $class)
    <?php
        $classInfo = DB::table('class_stream_assignments')->where('school_id',Helper::requireSchool())->where('class_id',$class->class_id)->where('stream_id',$class->stream_id)->first();
   ?>

    <tr data-id="{{ $class->id }}">
        <td style="width:1px;">{{ $key + 1 }}</td>
        <td>{{ Helper::recordMdname($classInfo->class_id) }}</td>
        <td>{{ $classInfo->stream_id }}</td>
        <td>{{ Helper::recordMdname($class->subject_id) }}</td>
        <td>0</td>
        <td>
            <div class="d-flex align-items-center gap-2">
                @if($class->subject_teacher_1)
                    <select name="teacher_id"
                        class="form-select form-select-sm assign-subject-teacher-1 form-control"
                        data-class-id="{{ $class->id }}"
                        data-current-supervisor="{{ $class->subject_teacher_1 }}"
                        disabled>
                        <option value="">Assign Teacher</option>
                        @foreach ($Teachers as $teacher)
                            <option value="{{ $teacher->id }}"
                                {{ $class->subject_teacher_1 == $teacher->id ? 'selected' : '' }}>
                                {{ $teacher->surname }} {{ $teacher->firstname }}
                            </option>
                        @endforeach
                    </select>

                @else
                    <span class="text-muted">No Teacher Assigned</span>
                @endif
            </div>
        </td>
        <td>
            <div class="d-flex align-items-center gap-2">
                @if($class->subject_teacher_2)
                    <select name="teacher_id"
                        class="form-select form-select-sm assign-subject-teacher-2 form-control"
                        data-class-id="{{ $class->id }}"
                        data-current-supervisor="{{ $class->subject_teacher_2 }}"
                        disabled>
                        <option value="">Assign Teacher</option>
                        @foreach ($Teachers as $teacher)
                            <option value="{{ $teacher->id }}"
                                {{ $class->subject_teacher_2 == $teacher->id ? 'selected' : '' }}>
                                {{ $teacher->surname }} {{ $teacher->firstname }}
                            </option>
                        @endforeach
                    </select>

                @else
                    <span class="text-muted">No Teacher Assigned</span>
                @endif
            </div>
        </td>
        <!-- <td style="text-align: center;">
            <a href="#" class="btn btn-sm btn-info">
                <i class="fas fa-users me-2"></i> Students Per Optional Subject
            </a>
        </td> -->
    </tr>
@empty
    <tr>
        <td colspan="8" class="text-center">You don’t have any subject assignments at the moment</td>
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
            
            $('.assign-supervisor').on('change', function () {
                let classId = $(this).data('class-id');
                let teacherId = $(this).val();
                let selectElement = $(this);

                let current = selectElement.data('current-supervisor');
                if (teacherId == current) {
                    return; 
                }

                if (teacherId !== '') {
                    $.ajax({
                        url: "{{ route('class.assignSupervisor') }}",
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            class_id: classId,
                            teacher_id: teacherId
                        },
                        success: function (response) {
                            if (response.status === 'success') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Assigned!',
                                    text: 'Class supervisor assigned successfully.',
                                    timer: 1500,
                                    showConfirmButton: false
                                });
                                selectElement.prop('disabled', true);
                                // Optionally reload the page or row to show the delete icon
                                setTimeout(() => location.reload(), 1600);
                            } else {
                                Swal.fire('Error', response.message, 'error');
                            }
                        },
                        error: function () {
                            Swal.fire('Oops', 'Something went wrong. Try again.', 'error');
                        }
                    });
                }
            });

            // Remove Supervisor
            $('.btn-remove-supervisor').on('click', function () {
                let classId = $(this).data('class-id');
                Swal.fire({
                    title: 'Are you sure?',
                    text: "Remove the assigned supervisor?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, remove it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route('class.removeSupervisor') }}",
                            type: "POST",
                            data: {
                                _token: "{{ csrf_token() }}",
                                class_id: classId
                            },
                            success: function (response) {
                                if (response.status === 'success') {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Removed!',
                                        text: 'Supervisor removed successfully.',
                                        timer: 1500,
                                        showConfirmButton: false
                                    });
                                    setTimeout(() => location.reload(), 1600);
                                } else {
                                    Swal.fire('Error', response.message, 'error');
                                }
                            },
                            error: function () {
                                Swal.fire('Oops', 'Something went wrong.', 'error');
                            }
                        });
                    }
                });
            });
        });
    </script>

    <script>
        $(document).ready(function () {

            // Delete class
            $('.btn-delete-class').on('click', function () {
                let classId = $(this).data('class-id');
                let className = $(this).data('class-name');

                Swal.fire({
                    title: `Delete ${className}?`,
                    text: "All streams, subjects, and students in this class will be deleted permanently!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {

                        $.ajax({
                            url: `/classes/${classId}`,
                            type: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            success: function (response) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Deleted!',
                                    text: response.message || 'Class and related data deleted successfully.',
                                    timer: 2000,
                                    showConfirmButton: false
                                }).then(() => {
                                    location.reload(); // reload page
                                });
                            },
                            error: function (xhr) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Delete Failed',
                                    text: xhr.responseJSON?.message || 'Something went wrong!'
                                });
                            }
        //                     error: function(data) {
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