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
                                        <th>#</th>
                                        <th>Senior</th>
                                        <th>Boys</th>
                                        <th>Girls</th>
                                        <th>Total Students</th>
                                        <th>Class Supervisor</th>
                                        <th colspan="2" style="text-align: center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($classRecord as $key => $class)
                                        <?php
                                        $maleClassStudents = Helper::maleClassStudents($class->class_name);
                                        $femaleClassStudents = Helper::femaleClassStudents($class->class_name);                                    
                                        $totalClassStudent = Helper::totalClassStudent($class->class_name);
                                        
                                        ?>
                                        <tr data-id="{{ $class->id }}">
                                            <td style="width:1px;">{{ $key + 1 }}</td>
                                            <td>{{ Helper::recordMdname($class->class_name) }}</td>
                                            <td style="text-align: center;">{{ $maleClassStudents }}</td>
                                            <td style="text-align: center;">{{ $femaleClassStudents }}</td>
                                            <td style="text-align: center;">{{ $totalClassStudent }}</td>
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
                                                                {{ $teacher->surname }} {{ $teacher->firstname }}
                                                            </option>
                                                        @endforeach
                                                    </select>

                                                    @if ($class->class_supervisor)
                                                        &nbsp;
                                                        <button class="btn btn-md btn-danger btn-remove-supervisor"
                                                            data-class-id="{{ $class->id }}" title="Remove Supervisor">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                            <td style="text-align: center;">
                                                <a href="{{ route('manage.class.streams', ['id' => $class->class_name]) }}"
                                                    class="btn btn-sm btn-info">
                                                    <i class="fas fa-link me-2"></i> Manage Streams
                                                </a>

                                                <button class="btn btn-sm btn-danger btn-delete-class"
                                                    data-class-id="{{ $class->id }}"
                                                    data-class-name="{{ Helper::recordMdname($class->class_name) }}">
                                                    <i class="fas fa-trash-alt me-2"></i> Delete
                                                </button>

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
        $(document).ready(function() {

            $('.assign-supervisor').on('change', function() {
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
                        success: function(response) {
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
                        error: function() {
                            Swal.fire('Oops', 'Something went wrong. Try again.', 'error');
                        }
                    });
                }
            });

            // Remove Supervisor
            $('.btn-remove-supervisor').on('click', function() {
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
                            success: function(response) {
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
                            error: function() {
                                Swal.fire('Oops', 'Something went wrong.', 'error');
                            }
                        });
                    }
                });
            });
        });
    </script>

    <script>
        $(document).ready(function() {

            // Delete class
            $('.btn-delete-class').on('click', function() {
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
                            success: function(response) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Deleted!',
                                    text: response.message ||
                                        'Class and related data deleted successfully.',
                                    timer: 2000,
                                    showConfirmButton: false
                                }).then(() => {
                                    location.reload(); // reload page
                                });
                            },
                            // error: function (xhr) {
                            //     Swal.fire({
                            //         icon: 'error',
                            //         title: 'Delete Failed',
                            //         text: xhr.responseJSON?.message || 'Something went wrong!'
                            //     });
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
