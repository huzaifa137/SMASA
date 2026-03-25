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

        <!-- HTML -->
        <div class="row">
            <div class="col-lg-12 col-xl-12 col-md-12 col-sm-12">
                <div class="card bg-primary">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0 text-white">{{ trans('common.add_academic_year') }}</h4>
                    </div>

                    <div class="card-body bg-light">
                        <form method="POST" id="createAcademicYearForm" action="{{ route('academic-years.store') }}">

                            @csrf

                            <div class="row">
                                <div class="col-lg-6 col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">{{ trans('common.academic_year_name') }}</label>
                                        <input type="text" name="name" class="form-control" placeholder="e.g.2026"
                                            required>
                                        {{-- <input type="text" name="name" class="form-control"
                                            placeholder="e.g. 2024 - 2025" required pattern="\d{4} - \d{4}"
                                            title="Please enter the academic year in the format: 2024 - 2025"> --}}
                                    </div>
                                </div>

                                <div class="col-lg-6 col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">{{ trans('common.active') }}?</label>
                                        <select name="is_active" class="form-control">
                                            <option value="0">{{ trans('common.no') }}</option>
                                        </select>
                                    </div>
                                </div>

                                @php
                                    $currentYear = date('Y');
                                    $minDate = $currentYear . '-01-01';
                                    $maxDate = $currentYear . '-12-31';
                                @endphp

                                <div class="col-lg-6 col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">{{ trans('common.start_date') }}</label>
                                        <input type="date" name="start_date" id="start_date" class="form-control" required
                                            min="{{ $minDate }}" max="{{ $maxDate }}">
                                    </div>
                                </div>

                                <div class="col-lg-6 col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">{{ trans('common.end_date') }}</label>
                                        <input type="date" name="end_date" id="end_date" class="form-control" required
                                            min="{{ $minDate }}" max="{{ $maxDate }}">
                                    </div>
                                </div>

                            </div>

                            <div class="mt-4 text-left">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane"></i> {{ trans('common.save_academic_year') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-12 col-xl-12 col-md-12 col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">{{ trans('common.academic_year') }}</h3>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped card-table table-vcenter text-nowrap mb-0"
                                id="academicYearsTable">
                                <thead>
                                    <tr>
                                        <th>{{ trans('common.id') }}</th>
                                        <th>{{ trans('common.name') }}</th>
                                        <th>{{ trans('common.start_date') }}</th>
                                        <th>{{ trans('common.end_date') }}</th>
                                        <th>{{ trans('common.status') }}</th>
                                        <th>{{ trans('common.activation') }}</th>
                                        <th colspan="2" style="text-align: center">{{ trans('common.action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($academicYears as $key => $year)
                                        <tr data-id="{{ $year->id }}">
                                            <th scope="row" style="width: 1px;">{{ $key + 1 }}</th>
                                            <td>{{ $year->name }}</td>
                                            <td>{{ $year->start_date }}</td>
                                            <td>{{ $year->end_date }}</td>
                                            <td>
                                                <span
                                                    class="badge {{ $year->is_active ? 'badge-success' : 'badge-secondary' }}">
                                                    <i
                                                        class="fas {{ $year->is_active ? 'fa-check-circle text-white' : 'fa-times-circle text-white' }}"></i>
                                                    {{ $year->is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </td>
                                            <td>
                                                @if (!$year->is_active)
                                                    <button class="btn btn-sm btn-success activate-btn">
                                                        <i class="fas fa-play-circle text-white me-1"></i> {{ trans('common.activate') }}
                                                    </button>
                                                @else
                                                    <button class="btn btn-sm btn-warning deactivate-btn">
                                                        <i class="fas fa-pause-circle text-dark me-1"></i> {{ trans('common.deactivate') }}
                                                    </button>
                                                @endif
                                            </td>

                                            <td>
                                                <button class="btn btn-sm btn-outline-danger btn-delete-academic-year"
                                                    title="Delete" data-id="{{ $year->id }}">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-info btn-edit-academic-year"
                                                    data-id="{{ $year->id }}" data-name="{{ $year->name }}"
                                                    data-start_date="{{ $year->start_date }}"
                                                    data-end_date="{{ $year->end_date }}"
                                                    data-is_active="{{ $year->is_active }}">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center">{{ trans('common.no_academic_years_found') }}.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>

                            <!-- Edit Academic Year Modal -->
                            <div class="modal fade" id="editAcademicYearModal" tabindex="-1" role="dialog"
                                aria-labelledby="editAcademicYearModalLabel" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <form id="editAcademicYearForm">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">{{ trans('common.edit_academic_year') }}</h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>

                                            <div class="modal-body">

                                                <input type="hidden" id="edit_id">

                                                <div class="form-group">
                                                    <label for="edit_name">Year</label>
                                                    <input type="text" id="edit_name" name="name" class="form-control"
                                                        placeholder="e.g.2026" required>
                                                    {{-- <input type="text" id="edit_name" name="name" class="form-control"
                                                        placeholder="e.g. 2024 - 2025" required pattern="\d{4} - \d{4}"
                                                        title="Please enter the academic year in the format: 2024 - 2025"> --}}
                                                </div>

                                                <div class="form-group">
                                                    <label for="edit_is_active">{{ trans('common.is_active') }}?</label>
                                                    <select id="edit_is_active" name="is_active" class="form-control"
                                                        required>
                                                        <option value="0">{{ trans('common.no') }}</option>
                                                        <option value="1">{{ trans('common.yes') }}</option>
                                                    </select>
                                                </div>

                                            </div>

                                            <div class="modal-footer">
                                                <button type="submit" class="btn btn-primary"><i
                                                        class="fas fa-paper-plane"></i>
                                                    {{ trans('common.save_changes') }}</button>
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
    </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        $(document).ready(function () {
            $('#createAcademicYearForm').on('submit', function (e) {
                e.preventDefault();

                let isValid = true;
                let $form = $(this);
                let $submitBtn = $form.find('button[type="submit"]');

                $form.find('.form-control, select').removeClass('is-invalid');
                $form.find('.invalid-feedback').remove();

                $form.find('input, select').each(function () {
                    if (!$(this).val().trim()) {
                        $(this).addClass('is-invalid');

                        if ($(this).next('.invalid-feedback').length === 0) {
                            $(this).after(
                                '<div class="invalid-feedback">This field is required.</div>');
                        }

                        isValid = false;
                    }
                });

                if (!isValid) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Incomplete Form',
                        text: 'Please fill in all required fields before submitting.'
                    });
                    return;
                }

                Swal.fire({
                    title: 'Are you sure?',
                    text: "You are about to submit the academic year data.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, submit it!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {

                        $submitBtn.prop('disabled', true);
                        const originalBtnHtml = $submitBtn.html();
                        $submitBtn.html('Saving...<i class="fas fa-spinner fa-spin"></i>');

                        $.ajax({
                            url: '{{ route('academic-years.store') }}',
                            method: 'POST',
                            data: $form.serialize(),
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            success: function (response) {
                                Swal.fire(
                                    'Submitted!',
                                    response.message,
                                    'success'
                                ).then(() => {
                                    location
                                        .reload(); // Reloads the page after the user closes the alert
                                });
                                $form[0].reset();
                            },
                            error: function (xhr) {
                                if (xhr.status === 422) {
                                    let errors = xhr.responseJSON.errors;
                                    for (let field in errors) {
                                        let input = $form.find(`[name="${field}"]`);
                                        input.addClass('is-invalid');
                                        input.after(
                                            `<div class="invalid-feedback">${errors[field][0]}</div>`
                                        );
                                    }
                                } else {
                                    Swal.fire('Error', 'Something went wrong!',
                                        'error');
                                }
                            },
                            complete: function () {
                                $submitBtn.prop('disabled', false).html(
                                    originalBtnHtml);
                            }
                        });

                    }
                });
            });
        });
    </script>

    <script>
        // Delete academic year functionality
        $('tbody').on('click', '.btn-delete-academic-year', function () {
            var yearId = $(this).data('id');
            var row = $(this).closest('tr');

            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to recover this academic year!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/academic-years/' + yearId,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function (response) {
                            row.remove();
                            Swal.fire(
                                'Deleted!',
                                'Academic year has been deleted.',
                                'success'
                            );
                        },
                        error: function (xhr) {
                            Swal.fire(
                                'Error!',
                                'Something went wrong while deleting.',
                                'error'
                            );
                        }
                    });
                }
            });
        });
    </script>

    <script>
        $(document).on('click', '.btn-edit-academic-year', function () {
            const id = $(this).data('id');

            $('#edit_id').val(id);
            $('#edit_name').val($(this).data('name'));

            $('#edit_is_active').val($(this).data('is_active'));

            $('#editAcademicYearModal').modal('show');
        });

        $('#editAcademicYearForm').on('submit', function (e) {
            e.preventDefault();

            const id = $('#edit_id').val();

            $.ajax({
                url: '/academic-years/' + id,
                type: 'POST',
                data: $(this).serialize(),
                success: function (response) {
                    $('#editAcademicYearModal').modal('hide');

                    Swal.fire('Updated!', 'Academic Year updated successfully.', 'success')
                        .then(() => {
                            location.reload();
                        });
                },
                // error: function(xhr) {
                //     let errorText = 'Failed to update academic year.';
                //     if (xhr.responseJSON?.message) {
                //         errorText = xhr.responseJSON.message;
                //     }

                //     Swal.fire('Error!', errorText, 'error');
                // }
                error: function (data) {
                    $('body').html(data.responseText);
                }
            });
        });
    </script>

    <script>
        $(document).ready(function () {
            $('#academicYearsTable').on('click', '.activate-btn, .deactivate-btn', function () {
                const $btn = $(this);
                const $row = $btn.closest('tr');
                const id = $row.data('id');
                const action = $btn.hasClass('activate-btn') ? 'activate' : 'deactivate';
                const route = action === 'activate' ?
                    '{{ url('/academic-years') }}/' + id + '/activate' :
                    '{{ url('/academic-years') }}/' + id + '/deactivate';

                Swal.fire({
                    title: `Are you sure you want to ${action} this academic year?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes',
                    cancelButtonText: 'Cancel',
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: route,
                            type: 'PATCH',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            success: function (res) {
                                Swal.fire('Success', res.message, 'success').then(
                                    () => {
                                        location.reload();
                                    });
                            },
                            error: function () {
                                Swal.fire('Error', 'Something went wrong!', 'error');
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