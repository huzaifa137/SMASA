<?php
use App\Helpers\PermissionHelper;
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
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title text-primary">Teachers</h3>
                        <a href="{{ route('school.add-teachers') }}" class="btn btn-sm btn-primary"
                            style=";color:#FFF;">
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
                                        <th class="text-center">profile</th>
                                        <th>Surname</th>
                                        <th>Firstname</th>
                                        <th>Phone Number</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($teachers as $key => $teacher)
                                        <tr data-id="{{ $teacher->id }}">
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
                                            <td class="text-center">
                                                <button class="btn btn-sm btn-info btn-view-teacher" title="View"
                                                    data-id="{{ $teacher->id }}">
                                                    <i class="fas fa-eye"></i>
                                                </button>

                                                <button class="btn btn-sm btn-warning btn-edit-teacher" title="Edit"
                                                    data-id="{{ $teacher->id }}">
                                                    <i class="fas fa-edit"></i>
                                                </button>

                                                <button class="btn btn-sm btn-danger btn-delete-teacher" title="Delete"
                                                    data-id="{{ $teacher->id }}">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </td>
                                        </tr>
                                   @empty
    <tr>
        <td colspan="6" class="text-center py-5">
            <div class="text-center">
                <div class="mb-3">
                    <i class="fas fa-users-slash fa-4x text-muted"></i>
                </div>
                <h5 class="text-muted">No Teachers Found</h5>
                <p class="text-muted mb-3">Start by adding your first teacher to the school</p>
                <a href="{{ route('school.add-teachers') }}" class="btn btn-sm btn-primary rounded-pill">
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

                                        <!-- Modal Header -->
                                        <div class="modal-header bg-gradient-primary text-white border-0 rounded-top-4"
                                            style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                            <h5 class="modal-title fw-bold" id="teacherProfileModalLabel">
                                                <i class="fas fa-chalkboard-teacher mr-2"></i>
                                                Teacher Profile
                                            </h5>
                                            <!-- Bootstrap 4 close button -->
                                            <button type="button" class="close text-white" data-dismiss="modal"
                                                aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>

                                        <!-- Modal Body -->
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

                                        <!-- Modal Footer with Close Button -->
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

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>


    <script>
        $(document).on('click', '.btn-edit-teacher', function() {
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
                    window.location.href = `/update-teacher-profile/${teacherId}`
                }
            });
        });
    </script>

    <script>
        $(document).ready(function() {
            // View Teacher Profile - Opens modal
            $(document).on('click', '.btn-view-teacher', function() {
                const teacherId = $(this).data('id');

                // Show loading state in modal
                $('#modalContent').html(`
                        <div class="d-flex justify-content-center py-5">
                            <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                                
                            </div>
                        </div>
                    `);

                // Show modal
                $('#teacherProfileModal').modal('show');

                // Fetch teacher data via AJAX
                $.ajax({
                    url: '/teacher/profile/' + teacherId + '/data', // Create this route
                    type: 'GET',
                    success: function(response) {
                        displayTeacherProfile(response);
                    },
                    error: function(xhr) {
                        $('#modalContent').html(`
                    <div class="text-center py-5">
                        <i class="fas fa-exclamation-circle text-danger fa-3x mb-3"></i>
                        <p class="text-danger">Error loading teacher information. Please try again.</p>
                        <button class="btn btn-primary mt-3" onclick="location.reload()">Refresh</button>
                    </div>
                `);
                    }
                });
            });

            // Edit Teacher - Opens edit modal
            $(document).on('click', '.btn-edit-teacher', function() {
                const teacherId = $(this).data('id');

                // Show loading state in edit modal
                $('#editModalContent').html(`
            <div class="text-center py-5">
                <div class="spinner-border text-warning" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Loading edit form...</p>
            </div>
        `);

                // Show modal
                $('#editTeacherModal').modal('show');

                // Fetch teacher data for editing
                $.ajax({
                    url: '/teacher/edit/' + teacherId + '/data', // Create this route
                    type: 'GET',
                    success: function(response) {
                        displayEditForm(response);
                    },
                    error: function(xhr) {
                        $('#editModalContent').html(`
                    <div class="text-center py-5">
                        <i class="fas fa-exclamation-circle text-danger fa-3x mb-3"></i>
                        <p class="text-danger">Error loading edit form. Please try again.</p>
                        <button class="btn btn-primary mt-3" onclick="location.reload()">Refresh</button>
                    </div>
                `);
                    }
                });
            });

            // Delete Teacher with confirmation
            $(document).on('click', '.btn-delete-teacher', function() {
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
                            success: function(response) {
                                Swal.fire(
                                    'Deleted!',
                                    'Teacher has been deleted.',
                                    'success'
                                ).then(() => {
                                    row.remove();
                                });
                            },
                            // error: function(data) {
                            //     $('body').html(data.responseText);
                            // }
                            error: function(xhr) {
                                Swal.fire(
                                    'Error!',
                                    xhr.responseJSON?.message ||
                                    'Error deleting teacher.',
                                    'error'
                                );
                            }
                        });
                    }
                });
            });

            // Function to display teacher profile (read-only)
            function displayTeacherProfile(teacher) {
                const profileHtml = `
            <div class="container-fluid p-4">
                <div class="row">
                    <!-- Profile Image Card - Same as original -->
                    <div class="col-lg-4 mb-4">
                        <div class="card border-0 shadow-sm rounded-4 h-100">
                            <div class="card-body text-center p-4">
                                <div class="position-relative d-inline-block mb-3">
                                    <img src="${teacher.teacher_profile ? '/' + teacher.teacher_profile : '{{ asset('assets/images/brand/uplogolight.png') }}'}"
                                        class="rounded-circle shadow"
                                        style="width: 180px; height: 180px; object-fit: cover; border: 4px solid #fff; box-shadow: 0 0 0 3px #e9ecef;"
                                        alt="Teacher Profile">
                                </div>

                                <h4 class="fw-bold mb-1">${escapeHtml(teacher.firstname)} ${escapeHtml(teacher.surname)}</h4>
                                <p class="text-primary mb-3">
                                    <i class="fas fa-id-badge me-1"></i>
                                    ${escapeHtml(teacher.registration_number) || 'Registration Pending'}
                                </p>

                                <div class="row g-2 mt-3">
                                    <div class="col-6">
                                        <div class="p-2 bg-light rounded-3">
                                            <small class="text-muted d-block">Employee #</small>
                                            <strong>${escapeHtml(teacher.employee_number) || 'N/A'}</strong>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="p-2 bg-light rounded-3">
                                            <small class="text-muted d-block">Group/Title</small>
                                            <strong>${escapeHtml(teacher.group_teacher) || 'Teacher'}</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Personal Information Card - Same as original -->
                    <div class="col-lg-8 mb-4">
                        <div class="card border-0 shadow-sm rounded-4 h-100">
                            <div class="card-header bg-white border-0 pt-4 px-4">
                                <h5 class="fw-bold mb-0">
                                    <i class="fas fa-user-circle text-primary me-2"></i>
                                    Personal Information
                                </h5>
                            </div>
                            <div class="card-body p-4">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold text-muted">Surname</label>
                                        <p class="form-control-static bg-light p-2 rounded-3">${escapeHtml(teacher.surname)}</p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold text-muted">First Name</label>
                                        <p class="form-control-static bg-light p-2 rounded-3">${escapeHtml(teacher.firstname)}</p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold text-muted">Other Name</label>
                                        <p class="form-control-static bg-light p-2 rounded-3">${escapeHtml(teacher.othername) || 'N/A'}</p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold text-muted">Initials</label>
                                        <p class="form-control-static bg-light p-2 rounded-3">${escapeHtml(teacher.initials) || 'N/A'}</p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold text-muted">Gender</label>
                                        <p class="form-control-static bg-light p-2 rounded-3">${teacher.gender ? teacher.gender.charAt(0).toUpperCase() + teacher.gender.slice(1) : 'N/A'}</p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold text-muted">Phone Number</label>
                                        <p class="form-control-static bg-light p-2 rounded-3">${escapeHtml(teacher.phonenumber)}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Professional Information Card - Same as original -->
                    <div class="col-lg-12 mb-4">
                        <div class="card border-0 shadow-sm rounded-4">
                            <div class="card-header bg-white border-0 pt-4 px-4">
                                <h5 class="fw-bold mb-0">
                                    <i class="fas fa-briefcase text-primary me-2"></i>
                                    Professional & Contact Information
                                </h5>
                            </div>
                            <div class="card-body p-4">
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label fw-semibold text-muted">Registration Number</label>
                                        <p class="form-control-static bg-light p-2 rounded-3">${escapeHtml(teacher.registration_number) || 'N/A'}</p>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label fw-semibold text-muted">National ID</label>
                                        <p class="form-control-static bg-light p-2 rounded-3">${escapeHtml(teacher.national_id) || 'N/A'}</p>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label fw-semibold text-muted">Employee Number</label>
                                        <p class="form-control-static bg-light p-2 rounded-3">${escapeHtml(teacher.employee_number) || 'N/A'}</p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold text-muted">Postal Address</label>
                                        <p class="form-control-static bg-light p-2 rounded-3">${escapeHtml(teacher.address) || 'N/A'}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;

                $('#modalContent').html(profileHtml);
            }

            // Helper function to escape HTML to prevent XSS
            function escapeHtml(str) {
                if (!str) return '';
                return str
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#39;');
            }

            // Function to display edit form
            function displayEditForm(teacher) {
                const editHtml = `
            <form id="editTeacherForm" enctype="multipart/form-data">
                @csrf
                @method('POST')
                <input type="hidden" name="teacher_id" value="${teacher.id}">
                <input type="hidden" name="school_id" value="${teacher.school_id}">
                
                <div class="container-fluid p-4">
                    <div class="row">
                        <!-- Profile Image Card -->
                        <div class="col-lg-4 mb-4">
                            <div class="card border-0 shadow-sm rounded-4 h-100">
                                <div class="card-body text-center p-4">
                                    <div class="position-relative d-inline-block mb-3">
                                        <img id="editLogoPreview"
                                            src="${teacher.teacher_profile ? '/${teacher.teacher_profile}' : '{{ asset('assets/images/brand/uplogolight.png') }}'}"
                                            class="rounded-circle shadow"
                                            style="width: 180px; height: 180px; object-fit: cover; border: 4px solid #fff; box-shadow: 0 0 0 3px #e9ecef;"
                                            alt="Teacher Profile">
                                        <label for="edit_profile_image_upload"
                                            class="position-absolute bottom-0 end-0 mb-2 me-2">
                                            <div class="bg-primary rounded-circle p-2 shadow-sm"
                                                style="cursor: pointer; transition: all 0.2s;">
                                                <i class="fas fa-camera text-white fa-sm"></i>
                                            </div>
                                            <input type="file" name="teacher_profile" id="edit_profile_image_upload"
                                                class="d-none" accept="image/*">
                                        </label>
                                    </div>
                                    
                                    <h4 class="fw-bold mb-1">${teacher.firstname} ${teacher.surname}</h4>
                                    <p class="text-primary mb-3">
                                        <i class="fas fa-id-badge me-1"></i>
                                        ${teacher.registration_number || 'Registration Pending'}
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Personal Information Card -->
                        <div class="col-lg-8 mb-4">
                            <div class="card border-0 shadow-sm rounded-4 h-100">
                                <div class="card-header bg-white border-0 pt-4 px-4">
                                    <h5 class="fw-bold mb-0">
                                        <i class="fas fa-user-circle text-primary me-2"></i>
                                        Personal Information
                                    </h5>
                                </div>
                                <div class="card-body p-4">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-semibold text-muted">Surname *</label>
                                            <input type="text" name="surname" class="form-control border-0 bg-light rounded-3 py-2"
                                                value="${teacher.surname}">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-semibold text-muted">First Name *</label>
                                            <input type="text" name="firstname" class="form-control border-0 bg-light rounded-3 py-2"
                                                value="${teacher.firstname}">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-semibold text-muted">Other Name</label>
                                            <input type="text" name="othername" class="form-control border-0 bg-light rounded-3 py-2"
                                                value="${teacher.othername || ''}">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-semibold text-muted">Initials</label>
                                            <input type="text" name="initials" class="form-control border-0 bg-light rounded-3 py-2"
                                                value="${teacher.initials || ''}">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-semibold text-muted">Gender *</label>
                                            <select name="gender" class="form-select border-0 bg-light rounded-3 py-2">
                                                <option value="male" ${teacher.gender === 'male' ? 'selected' : ''}>Male</option>
                                                <option value="female" ${teacher.gender === 'female' ? 'selected' : ''}>Female</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-semibold text-muted">Phone Number *</label>
                                            <input type="text" name="phonenumber" class="form-control border-0 bg-light rounded-3 py-2"
                                                value="${teacher.phonenumber}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Professional Information Card -->
                        <div class="col-lg-12 mb-4">
                            <div class="card border-0 shadow-sm rounded-4">
                                <div class="card-header bg-white border-0 pt-4 px-4">
                                    <h5 class="fw-bold mb-0">
                                        <i class="fas fa-briefcase text-primary me-2"></i>
                                        Professional & Contact Information
                                    </h5>
                                </div>
                                <div class="card-body p-4">
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label fw-semibold text-muted">Registration Number</label>
                                            <input type="text" name="registration_number" class="form-control border-0 bg-light rounded-3 py-2"
                                                value="${teacher.registration_number || ''}">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label fw-semibold text-muted">National ID</label>
                                            <input type="text" name="national_id" class="form-control border-0 bg-light rounded-3 py-2"
                                                value="${teacher.national_id || ''}">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label fw-semibold text-muted">Employee Number</label>
                                            <input type="text" name="employee_number" class="form-control border-0 bg-light rounded-3 py-2"
                                                value="${teacher.employee_number || ''}">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-semibold text-muted">Postal Address</label>
                                            <input type="text" name="address" class="form-control border-0 bg-light rounded-3 py-2"
                                                value="${teacher.address || ''}">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-semibold text-muted">Group/Title</label>
                                            <input type="text" name="group_teacher" class="form-control border-0 bg-light rounded-3 py-2"
                                                value="${teacher.group_teacher || ''}">
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer bg-white border-0 pb-4 px-4">
                                    <div class="d-flex justify-content-end gap-2">
                                        <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">
                                            Cancel
                                        </button>
                                        <button type="submit" class="btn btn-primary rounded-pill px-5 py-2 shadow-sm">
                                            <i class="fas fa-save me-2"></i> Save Changes
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        `;
            }
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
