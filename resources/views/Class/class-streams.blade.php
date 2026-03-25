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
                                        <th colspan="8" class="bg-primary text-white">Streams - {{ Helper::recordMdname($class_id) }}</th>
                                    </tr>
                                    <tr>
                                        <th>#</th>
                                        <th>Class</th>
                                        <th>Stream</th>
                                        <th>Boys</th>
                                        <th>Girls</th>
                                        <th>Total Students</th>
                                        <th>Class Teacher</th>
                                        <th colspan="2" style="text-align: center">Action</th>
                                    </tr>
                                </thead>
                               <tbody>
    @forelse ($Streams as $key => $stream)
        <tr data-id="{{ $stream->id }}">
            <td style="width:1px;">{{ $key + 1 }}</td>
            <td>{{ Helper::recordMdname($stream->class_id) }}</td>
            <td>{{ Helper::recordMdname($stream->stream_id) }}</td>
            <td>0</td>
            <td>0</td>
            <td>0</td>
            <td>
                <div class="d-flex align-items-center gap-2">
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

                    @if ($stream->class_teacher)
                    &nbsp;
                        <button class="btn btn-md btn-danger btn-remove-supervisor"
                            data-class-id="{{ $stream->id }}" title="Remove Supervisor">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    @endif
                </div>
            </td>
            <td style="text-align: center;">
                <a href="{{ route('class.stream.subjects', ['classId' => $stream->class_id, 'streamId' => $stream->stream_id]) }}" class="btn btn-sm btn-dark">
                    <i class="fas fa-graduation-cap me-2"></i> Manage Subjects
                </a>

                <a href="#" class="btn btn-sm btn-info btn-delete-stream" data-stream-id="{{ $stream->id }}">
                    <i class="fas fa-plus-circle me-2"></i> Add More Subjects
                </a>

                <a href="#" class="btn btn-sm btn-danger btn-delete-stream" data-stream-id="{{ $stream->id }}">
                    <i class="fas fa-trash-alt me-2"></i> Delete Stream
                </a>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="8" class="text-center">No streams found.</td>
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
        
        $('.assign-class-teacher').on('change', function () {
            let classId = $(this).data('class-id');
            let teacherId = $(this).val();
            let selectElement = $(this);

            let current = selectElement.data('current-supervisor');
            if (teacherId == current) {
                return; 
            }

            if (teacherId !== '') {
                $.ajax({
                    url: "{{ route('class.assignClassTeacher') }}",
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
                                text: 'Class Teacher assigned successfully.',
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
                    // error: function(data) {
                    // $('body').html(data.responseText);
                    // }
                });
            }
        });

        // Remove Supervisor
        $('.btn-remove-supervisor').on('click', function () {
            let classId = $(this).data('class-id');
            Swal.fire({
                title: 'Are you sure?',
                text: "Remove the assigned Class Teacher?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, remove it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('class.removeClassTeacher') }}",
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
                                    text: 'ClassTeacher removed successfully.',
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

<script>
    // Ensure jQuery is loaded before this script
    $(document).ready(function() {
        // ... (your existing .btn-remove-supervisor script) ...

        $('.btn-delete-stream').on('click', function (e) {
            e.preventDefault(); // Prevent the default link behavior
            let streamId = $(this).data('stream-id');

            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this! This will delete the stream and all associated data.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('streams.delete', ':streamId') }}".replace(':streamId', streamId),
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            _method: "DELETE" 
                        },
                        success: function (response) {
                            if (response.status === 'success') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Deleted!',
                                    text: 'Stream deleted successfully.',
                                    timer: 1500,
                                    showConfirmButton: false
                                });
                      
                                $('tr[data-id="' + streamId + '"]').remove();
                                setTimeout(() => location.reload(), 1600); 
                            } else {
                                Swal.fire('Error', response.message || 'Failed to delete stream.', 'error');
                            }
                        },
                        error: function (xhr) {
                            let errorMessage = 'Something went wrong. Please try again.';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            }
                            Swal.fire('Oops', errorMessage, 'error');
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