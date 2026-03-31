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
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-12">
                {{-- Header Section --}}
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2 class="fw-bold mb-1">
                            <i class="fas fa-user-graduate text-primary me-2"></i>
                            Teacher Profile
                        </h2>
                        <p class="text-muted">View and edit teacher information</p>
                    </div>
                    <a href="{{ route('school.teachers') }}" class="btn btn-outline-primary rounded-pill px-4">
                        <i class="fas fa-arrow-left me-2"></i> Back to All Teachers
                    </a>
                </div>

                <form id="createSchoolTeacher">
                    @csrf
                    @method('POST')
                    <input type="hidden" name="school_id" value="{{ $teacher->school_id }}">
                    <input type="hidden" name="teacher_id" value="{{ $teacher->id }}">

                    <div class="row">
                        {{-- Profile Image Card --}}
                        <div class="col-lg-4 mb-4">
                            <div class="card border-0 shadow-sm rounded-4 h-100">
                                <div class="card-body text-center p-4">
                                    <div class="position-relative d-inline-block mb-3">
                                        <img id="logoPreview"
                                            src="{{ asset($teacher->teacher_profile ?? 'assets/images/brand/uplogolight.png') }}"
                                            class="rounded-circle shadow"
                                            style="width: 180px; height: 180px; object-fit: cover; border: 4px solid #fff; box-shadow: 0 0 0 3px #e9ecef;"
                                            alt="Teacher Profile">
                                        <label for="profile_image_upload"
                                            class="position-absolute bottom-0 end-0 mb-2 me-2">
                                            <div class="bg-primary rounded-circle p-2 shadow-sm"
                                                style="cursor: pointer; transition: all 0.2s;">
                                                <i class="fas fa-camera text-white fa-sm"></i>
                                            </div>
                                            <input type="file" name="teacher_profile" id="profile_image_upload"
                                                class="d-none" accept="image/*">
                                        </label>
                                    </div>

                                    <h4 class="fw-bold mb-1">{{ $teacher->firstname }} {{ $teacher->surname }}</h4>
                                    <p class="text-primary mb-3">
                                        <i class="fas fa-id-badge me-1"></i>
                                        {{ $teacher->registration_number ?? 'Registration Pending' }}
                                    </p>

                                    <div class="row g-2 mt-3">
                                        <div class="col-6">
                                            <div class="p-2 bg-light rounded-3">
                                                <small class="text-muted d-block">Employee #</small>
                                                <strong>{{ $teacher->employee_number ?? 'N/A' }}</strong>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="p-2 bg-light rounded-3">
                                                <small class="text-muted d-block">Group/Title</small>
                                                <strong>{{ $teacher->group_teacher ?? 'Teacher' }}</strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Personal Information Card --}}
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
                                            <input type="text" name="surname"
                                                class="form-control border-0 bg-light rounded-3 py-2"
                                                value="{{ $teacher->surname }}">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-semibold text-muted">First Name</label>
                                            <input type="text" name="firstname"
                                                class="form-control border-0 bg-light rounded-3 py-2"
                                                value="{{ $teacher->firstname }}">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-semibold text-muted">Other Name</label>
                                            <input type="text" name="othername"
                                                class="form-control border-0 bg-light rounded-3 py-2"
                                                value="{{ $teacher->othername }}">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-semibold text-muted">Initials</label>
                                            <input type="text" name="initials"
                                                class="form-control border-0 bg-light rounded-3 py-2"
                                                value="{{ $teacher->initials }}">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-semibold text-muted">Gender</label>
                                            <select name="gender" class="form-control border-0 bg-light rounded-3 py-2">
                                                <option value="{{ $teacher->gender }}">{{ ucfirst($teacher->gender) }}
                                                </option>
                                                <option value="male">Male</option>
                                                <option value="female">Female</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-semibold text-muted">Phone Number</label>
                                            <input type="text" name="phonenumber"
                                                class="form-control border-0 bg-light rounded-3 py-2"
                                                value="{{ $teacher->phonenumber }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Professional Information Card --}}
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
                                            <input type="text" name="registration_number"
                                                class="form-control border-0 bg-light rounded-3 py-2"
                                                value="{{ $teacher->registration_number }}">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label fw-semibold text-muted">National ID</label>
                                            <input type="text" name="national_id"
                                                class="form-control border-0 bg-light rounded-3 py-2"
                                                value="{{ $teacher->national_id }}">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label fw-semibold text-muted">Employee Number</label>
                                            <input type="text" name="employee_number"
                                                class="form-control border-0 bg-light rounded-3 py-2"
                                                value="{{ $teacher->employee_number }}">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-semibold text-muted">Postal Address</label>
                                            <input type="text" name="address"
                                                class="form-control border-0 bg-light rounded-3 py-2"
                                                value="{{ $teacher->address }}">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-semibold text-muted">Group/Title</label>
                                            <input type="text" name="group_teacher"
                                                class="form-control border-0 bg-light rounded-3 py-2"
                                                value="{{ $teacher->group_teacher ?? 'Teacher'}}" disabled>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer bg-white border-0 pb-4 px-4">
                                    <div class="d-flex justify-content-end">
                                        <button type="submit" form="createSchoolTeacher"
                                            class="btn btn-primary rounded-pill px-5 py-2 shadow-sm">
                                            <i class="fas fa-save me-2"></i> Save All Changes
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        .form-control:focus,
        .form-select:focus {
            box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.1);
            border-color: #0d6efd;
            background-color: #fff !important;
        }

        .card {
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.08) !important;
        }

        .btn-outline-primary:hover {
            background-color: #0d6efd;
            color: white;
        }
    </style>
    </div>
    </div>
    </div>



    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
$(document).ready(function() {
    $('#createSchoolTeacher').on('submit', function(e) {
        e.preventDefault();

        let isValid = true;
        let $form = $(this);
        let $submitBtn = $form.find('button[type="submit"]');

        // Remove previous validation styles
        $form.find('.form-control, select').removeClass('is-invalid');
        $form.find('.invalid-feedback').remove();

        // Only these fields are required
        const requiredFields = [
            'surname',
            'firstname',
            'gender',
            'phonenumber'
        ];

        // Validate required fields
        requiredFields.forEach(function(field) {
            let input = $form.find(`[name="${field}"]`);
            if (!input.val() || !input.val().trim()) {
                input.addClass('is-invalid');
                if (input.next('.invalid-feedback').length === 0) {
                    input.after(
                        '<div class="invalid-feedback">This field is required.</div>'
                    );
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

        // Confirmation dialog
        Swal.fire({
            title: 'Are you sure?',
            text: "You are about to update the teacher data.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, submit it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $submitBtn.prop('disabled', true);
                const originalBtnHtml = $submitBtn.html();
                $submitBtn.html('Saving... <i class="fas fa-spinner fa-spin"></i>');

                // Use FormData to handle file uploads
                let formData = new FormData($form[0]);

                $.ajax({
                    url: '{{ route('users.update.information') }}',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        Swal.fire(
                            'Submitted!',
                            response.message,
                            'success'
                        ).then(() => {
                            location.reload(); // Reload page after success
                        });
                    },
                    error: function(data) {
                        $('body').html(data.responseText);
                    },
                    complete: function() {
                        $submitBtn.prop('disabled', false).html(originalBtnHtml);
                    }
                });
            }
        });
    });
});
</script>

<script>
$(document).ready(function() {
    $('#profile_image_upload').on('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#logoPreview').attr('src', e.target.result);
            };
            reader.readAsDataURL(file);
        }
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
