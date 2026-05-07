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
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .role-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            display: inline-block;
        }

        .role-badge-admin {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .role-badge-teacher {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
        }

        .role-select {
            min-width: 150px;
            font-size: 13px;
        }

        .update-role-btn {
            padding: 4px 8px;
            font-size: 11px;
            margin-left: 8px;
        }

        .role-update-spinner {
            display: none;
            margin-left: 10px;
        }

        .role-select-admin+.select2-container .select2-selection {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
            color: #fff !important;
            border: none !important;
        }

        .role-select-admin+.select2-container .select2-selection__rendered {
            color: #fff !important;
            font-weight: 600;
        }

        .role-select-teacher+.select2-container .select2-selection {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%) !important;
            color: #fff !important;
            border: none !important;
        }

        .role-select-teacher+.select2-container .select2-selection__rendered {
            color: #fff !important;
            font-weight: 600;
        }

        .role-select-default+.select2-container .select2-selection {
            background: #f8f9fa !important;
        }

        .select2-container.role-select-admin .select2-selection--single {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
            border: none !important;
            color: #FFF !important;
        }

        .select2-container.role-select-teacher .select2-selection--single {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%) !important;
            border: none !important;
            color: #FFF !important;
        }

        .role-select+.select2-container .select2-selection {
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .role-select-admin+.select2-container .select2-selection {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
            border: none !important;
            box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
        }

        .role-select-admin+.select2-container .select2-selection__rendered {
            color: #fff !important;
            font-weight: 600;
        }

        .role-select-admin+.select2-container .select2-selection__arrow b {
            border-top-color: #fff !important;
        }

        .role-select-teacher+.select2-container .select2-selection {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%) !important;
            border: none !important;
            box-shadow: 0 2px 8px rgba(240, 147, 251, 0.3);
        }

        .role-select-teacher+.select2-container .select2-selection__rendered {
            color: #fff !important;
            font-weight: 600;
        }

        .role-select-teacher+.select2-container .select2-selection__arrow b {
            border-top-color: #fff !important;
        }

        .role-select-default+.select2-container .select2-selection {
            background: #f8f9fa !important;
            border: 1px solid #dee2e6 !important;
        }

        .role-select-default+.select2-container .select2-selection__rendered {
            color: #495057 !important;
        }

        .role-select-admin+.select2-container .select2-selection:hover,
        .role-select-teacher+.select2-container .select2-selection:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .role-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            display: inline-block;
            transition: all 0.3s ease;
        }

        .role-badge-admin {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white !important;
            box-shadow: 0 2px 4px rgba(102, 126, 234, 0.3);
        }

        .role-badge-teacher {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white !important;
            box-shadow: 0 2px 4px rgba(240, 147, 251, 0.3);
        }

        .role-badge-parent {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white !important;
        }

        .role-badge-student {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
            color: white !important;
        }

        .update-role-btn {
            padding: 4px 12px;
            font-size: 12px;
            border-radius: 6px;
            transition: all 0.3s ease;
        }

        .update-role-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .role-select+.select2-container {
                min-width: 120px;
            }

            .role-badge {
                font-size: 10px;
                padding: 4px 8px;
            }
        }

        /* FORCE WHITE TEXT ON SELECT2 */
        .select2-container--default .select2-selection--single {
            background: #5351e4 !important;
            border: none !important;
            height: 38px !important;
            display: flex !important;
            align-items: center !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #FFF !important;
            line-height: 38px !important;
            font-weight: 600;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow b {
            border-top-color: #FFF !important;
        }
    </style>
@endsection

@section('content')
    <!-- Student Dashboard -->
    <div class="side-app">
        <div class="row">
            <div class="col-lg-12 col-xl-12 col-md-12 col-sm-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title text-primary">Teachers</h3>
                        <a href="{{ route('school.add-teachers') }}" class="btn btn-sm btn-primary" style=";color:#FFF;">
                            <span
                                class="rounded-circle bg-white d-inline-flex align-items-center justify-content-center me-1"
                                style="width: 20px; height: 20px; color:#5351e4;">
                                <i class="fas fa-plus" style="font-size: 12px;"></i>
                            </span>
                            Add Teacher
                        </a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped card-table table-vcenter text-nowrap mb-0" id="teachersTable">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th class="text-center">Profile</th>
                                        <th>Surname</th>
                                        <th>Firstname</th>
                                        <th>Phone Number</th>
                                        <th>Role</th>
                                        @if (Helper::isTechSateAdminOrSchoolAdminsAlone())
                                        <th class="text-center">Action</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($teachers as $key => $teacher)
                                        <tr data-id="{{ $teacher->id }}" data-role="{{ $teacher->teacher_role }}">
                                            <td style="width:1px;">{{ $key + 1 }}</td>
                                            <td class="text-center">
                                                <img src="{{ asset($teacher->teacher_profile ?? 'assets/images/brand/uplogolight.png') }}"
                                                    class="img-fluid rounded-circle border p-2"
                                                    style="width: 100px; height: 100px; object-fit: cover;"
                                                    alt="Teacher Profile">
                                            </td>
                                            <td>{{ $teacher->surname }}</td>
                                            <td>{{ $teacher->firstname }}</td>
                                            <td>{{ $teacher->phonenumber }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <select class="form-select form-control role-select text-white"
                                                        data-teacher-id="{{ $teacher->id }}"
                                                        style="width:220px; background:#5351e4; color:#FFF;">

                                                        <option value="" style="color:#000;">Select Role</option>

                                                        @foreach($schoolRoles as $role)
                                                            <option value="{{ $role->id }}" {{ $teacher->teacher_role == $role->id ? 'selected' : '' }}>
                                                                {{ $role->name }}
                                                            </option>
                                                        @endforeach

                                                    </select>

                                                    <button class="btn btn-sm btn-primary update-role-btn ms-2"
                                                        data-teacher-id="{{ $teacher->id }}">
                                                        <i class="fas fa-save"></i>
                                                    </button>

                                                    <div class="role-update-spinner" id="spinner-{{ $teacher->id }}"
                                                        style="display:none;">
                                                        <div class="spinner-border spinner-border-sm text-primary"
                                                            role="status">
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <!-- <button class="btn btn-sm btn-info btn-view-teacher" title="View"
                                                    data-id="{{ $teacher->id }}">
                                                    <i class="fas fa-eye"></i>
                                                </button> -->

                                                @if (Helper::isTechSateAdminOrSchoolAdminsAlone())
                                                    <button class="btn btn-sm btn-warning btn-edit-teacher" title="Edit"
                                                        data-id="{{ $teacher->id }}">
                                                        <i class="fas fa-edit"></i>
                                                    </button>

                                                    <button class="btn btn-sm btn-danger btn-delete-teacher" title="Delete"
                                                        data-id="{{ $teacher->id }}">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-5">
                                                <div class="text-center">
                                                    <div class="mb-3">
                                                        <i class="fas fa-users-slash fa-4x text-muted"></i>
                                                    </div>
                                                    <h5 class="text-muted">No Teachers Found</h5>
                                                    <p class="text-muted mb-3">Start by adding your first teacher to the school
                                                    </p>
                                                    <a href="{{ route('school.add-teachers') }}"
                                                        class="btn btn-sm btn-primary rounded-pill">
                                                        <i class="fas fa-plus me-1"></i> Add Teacher
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>

                            <!-- Teacher Profile Modal -->
                            <div class="modal fade" id="teacherProfileModal" tabindex="-1" role="dialog"
                                aria-labelledby="teacherProfileModalLabel" aria-hidden="true">
                                <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable"
                                    style="max-width: 95%; width: 1400px;" role="document">
                                    <div class="modal-content border-0 rounded-4">
                                        <div class="modal-header bg-gradient-primary text-white border-0 rounded-top-4"
                                            style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                            <h5 class="modal-title fw-bold" id="teacherProfileModalLabel">
                                                <i class="fas fa-chalkboard-teacher mr-2"></i>
                                                Teacher Profile
                                            </h5>
                                            <button type="button" class="close text-white" data-dismiss="modal"
                                                aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body p-0">
                                            <div id="modalContent">
                                                <div class="text-center py-5">
                                                    <div class="spinner-border text-primary" role="status">
                                                        <span class="sr-only">Loading...</span>
                                                    </div>
                                                    <p class="mt-2">Loading teacher information...</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer border-0">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                                <i class="fa fa-times me-2"></i> Close
                                            </button>
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
    </div>
    </div>
    </div>


    <script>

        // APPLY ROLE COLORS
        // APPLY ROLE COLORS
        function applyRoleColor(select) {
            const value = select.val();
            const container = select.next('.select2-container');

            // Remove existing classes
            container.removeClass('role-select-admin role-select-teacher role-select-default');

            // School Administrators - Role ID 3
            if (value == '3') {
                container.addClass('role-select-admin');
            }
            // School Teachers - Role ID 4
            else if (value == '4') {
                container.addClass('role-select-teacher');
            }
            // Default for other roles
            else {
                container.addClass('role-select-default');
            }
        }


        // SORT TABLE
        function sortTeachersTable() {

            const tbody = $('#teachersTable tbody');

            const rows = tbody.find('tr').get();

            rows.sort(function (a, b) {

                const roleA = $(a).find('.role-select').val();

                const roleB = $(b).find('.role-select').val();

                // Push School Teachers (role 4) to bottom
                if (roleA == '4' && roleB != '4') {

                    return 1;

                }

                if (roleA != '4' && roleB == '4') {

                    return -1;

                }

                return 0;

            });

            $.each(rows, function (index, row) {

                tbody.append(row);

            });

        }


        $(document).ready(function () {

            setTimeout(function () {

                $('.role-select').each(function () {

                    const select = $(this);

                    // Destroy duplicate select2
                    if (select.hasClass('select2-hidden-accessible')) {

                        select.select2('destroy');

                    }

                    // Initialize select2
                    select.select2({

                        width: '100%',

                        dropdownAutoWidth: true,

                        placeholder: 'Select Role',

                        allowClear: false,

                        minimumResultsForSearch: Infinity

                    });

                    // Apply role color
                    applyRoleColor(select);

                });

                // SORT TABLE AFTER LOADING
                sortTeachersTable();

            }, 500);

        });


        // CHANGE ROLE COLOR LIVE
        $(document).on('change', '.role-select', function () {

            applyRoleColor($(this));

        });


        // UPDATE ROLE
        $(document).on('click', '.update-role-btn', function () {

            const teacherId = $(this).data('teacher-id');

            const selectEl = $(`.role-select[data-teacher-id="${teacherId}"]`);

            const roleId = selectEl.val();

            const spinner = $(`#spinner-${teacherId}`);

            const btn = $(this);

            if (!roleId) {

                Swal.fire({

                    title: 'Error!',

                    text: 'Please select a role first.',

                    icon: 'error',

                    timer: 2000,

                    showConfirmButton: false

                });

                return;

            }

            btn.prop('disabled', true);

            spinner.show();

            $.ajax({

                url: '/teacher/update-role/' + teacherId,

                type: 'POST',

                data: {

                    role_id: roleId,

                    _token: '{{ csrf_token() }}'

                },

                success: function (response) {

                    Swal.fire({

                        title: 'Success!',

                        text: response.message || 'Teacher role updated successfully!',

                        icon: 'success',

                        timer: 1500,

                        showConfirmButton: false

                    });

                    // Re-sort table
                    sortTeachersTable();

                    // Refresh page
                    setTimeout(function () {

                        location.reload();

                    }, 1500);

                },

                error: function (xhr) {

                    Swal.fire({

                        title: 'Error!',

                        text: xhr.responseJSON?.message || 'Error updating teacher role.',

                        icon: 'error'

                    });

                },

                complete: function () {

                    btn.prop('disabled', false);

                    spinner.hide();

                }

            });

        });


        // VIEW TEACHER PROFILE
        $(document).on('click', '.btn-view-teacher', function () {

            const teacherId = $(this).data('id');

            $('#modalContent').html(`
                                        <div class="d-flex justify-content-center py-5">
                                            <div class="spinner-border text-primary"
                                                role="status"
                                                style="width: 3rem; height: 3rem;">
                                            </div>
                                        </div>
                                    `);

            $('#teacherProfileModal').modal('show');

            $.ajax({

                url: '/teacher/profile/' + teacherId + '/data',

                type: 'GET',

                success: function (response) {

                    displayTeacherProfile(response);

                },

                error: function () {

                    $('#modalContent').html(`
                                                <div class="text-center py-5">
                                                    <i class="fas fa-exclamation-circle text-danger fa-3x mb-3"></i>

                                                    <p class="text-danger">
                                                        Error loading teacher information.
                                                    </p>
                                                </div>
                                            `);

                }

            });

        });


        // EDIT TEACHER
        $(document).on('click', '.btn-edit-teacher', function () {

            const teacherId = $(this).data('id');

            Swal.fire({

                title: 'Edit Teacher Profile?',

                text: "Are you sure you want to edit this teacher's profile?",

                icon: 'warning',

                showCancelButton: true,

                confirmButtonColor: '#3085d6',

                cancelButtonColor: '#d33',

                confirmButtonText: 'Yes, proceed'

            }).then((result) => {

                if (result.isConfirmed) {

                    window.location.href = `/update-teacher-profile/${teacherId}`;

                }

            });

        });


        // DELETE TEACHER
        $(document).on('click', '.btn-delete-teacher', function () {

            const teacherId = $(this).data('id');

            const row = $(this).closest('tr');

            Swal.fire({

                title: 'Are you sure?',

                text: "You won't be able to revert this!",

                icon: 'warning',

                showCancelButton: true,

                confirmButtonColor: '#d33',

                cancelButtonColor: '#3085d6',

                confirmButtonText: 'Yes, delete it!'

            }).then((result) => {

                if (result.isConfirmed) {

                    $.ajax({

                        url: '/teachers/' + teacherId,

                        type: 'DELETE',

                        headers: {

                            'X-CSRF-TOKEN': '{{ csrf_token() }}'

                        },

                        success: function () {

                            Swal.fire(
                                'Deleted!',
                                'Teacher has been deleted.',
                                'success'
                            ).then(() => {

                                row.remove();

                            });

                        },

                        error: function (xhr) {

                            Swal.fire(
                                'Error!',
                                xhr.responseJSON?.message || 'Error deleting teacher.',
                                'error'
                            );

                        }

                    });

                }

            });

        });


        // DISPLAY PROFILE
        function displayTeacherProfile(teacher) {

            const profileHtml = `
                                        <div class="container-fluid p-4">

                                            <div class="row">

                                                <div class="col-lg-4 mb-4">

                                                    <div class="card border-0 shadow-sm rounded-4 h-100">

                                                        <div class="card-body text-center p-4">

                                                            <div class="position-relative d-inline-block mb-3">

                                                                <img src="${teacher.teacher_profile ? '/' + teacher.teacher_profile : '{{ asset('assets/images/brand/uplogolight.png') }}'}"
                                                                    class="rounded-circle shadow"
                                                                    style="width: 180px; height: 180px; object-fit: cover;"
                                                                    alt="Teacher Profile">

                                                            </div>

                                                            <h4 class="fw-bold mb-1">

                                                                ${escapeHtml(teacher.firstname)}
                                                                ${escapeHtml(teacher.surname)}

                                                            </h4>

                                                        </div>

                                                    </div>

                                                </div>

                                            </div>

                                        </div>
                                    `;

            $('#modalContent').html(profileHtml);

        }


        // ESCAPE HTML
        function escapeHtml(str) {

            if (!str) return '';

            return str
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');

        }

    </script>
@endsection

@section('js')
    <!-- Your existing JS includes -->
    <script src="{{ URL::asset('assets/plugins/charts-c3/d3.v5.min.js') }}"></script>
    <script src="{{ URL::asset('assets/plugins/charts-c3/c3-chart.js') }}"></script>
    <script src="{{ URL::asset('assets/js/charts.js') }}"></script>
    <script src="{{ URL::asset('assets/plugins/echarts/echarts.js') }}"></script>
    <script src="{{ URL::asset('assets/plugins/peitychart/jquery.peity.min.js') }}"></script>
    <script src="{{ URL::asset('assets/plugins/peitychart/peitychart.init.js') }}"></script>
    <script src="{{ URL::asset('assets/js/apexcharts.js') }}"></script>
    <script src="{{ URL::asset('assets/plugins/moment/moment.js') }}"></script>
    <script src="{{ URL::asset('assets/plugins/bootstrap-daterangepicker/daterangepicker.js') }}"></script>
    <script src="{{ URL::asset('assets/js/daterange.js') }}"></script>
    <script src="{{ URL::asset('assets/plugins/jvectormap/jquery.vmap.js') }}"></script>
    <script src="{{ URL::asset('assets/plugins/jvectormap/jquery.vmap.world.js') }}"></script>
    <script src="{{ URL::asset('assets/plugins/jvectormap/jquery.vmap.sampledata.js') }}"></script>
    <script src="{{ URL::asset('assets/js/index1.js') }}"></script>
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
    <script src="{{ URL::asset('assets/plugins/counters/counterup.min.js') }}"></script>
    <script src="{{ URL::asset('assets/plugins/counters/waypoints.min.js') }}"></script>
    <script src="{{ URL::asset('assets/plugins/chart/chart.bundle.js') }}"></script>
    <script src="{{ URL::asset('assets/plugins/chart/utils.js') }}"></script>
@endsection