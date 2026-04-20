<?php
use App\Http\Controllers\Helper;
?>
@extends('layouts-side-bar.master')
@section('content')
    <div class="side-app">

        <div class="row">
            <div class="col-lg-12 col-xl-12 col-md-12 col-sm-12">
                <div class="card bg-primary">
                    <div class="card-header">
                        @include('layouts.subjects-buttons')
                    </div>
                </div>
            </div>
        </div>

        <style>
            .file-upload-wrapper {
                position: relative;
                width: 100%;
            }

            .file-upload-preview {
                width: 100%;
                height: 150px;
                border: 2px dashed #dee2e6;
                border-radius: 8px;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                background-color: #f8f9fa;
                transition: all 0.3s ease;
                margin-bottom: 12px;
                overflow: hidden;
                position: relative;
            }

            .file-upload-preview i {
                font-size: 48px;
                color: #6c757d;
                margin-bottom: 8px;
            }

            .file-upload-preview span {
                color: #6c757d;
                font-size: 14px;
            }

            .file-upload-preview.has-image {
                border: 2px solid #28a745;
                background-color: #fff;
            }

            .file-upload-preview.has-image img {
                width: auto;
                height: auto;
                max-width: 100%;
                max-height: 100%;
                object-fit: contain;
            }

            .file-upload-preview.has-image i,
            .file-upload-preview.has-image span {
                display: none;
            }

            .file-upload-input {
                position: absolute;
                opacity: 0;
                width: 0;
                height: 0;
            }

            .file-upload-btn {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                background-color: #5351e4;
                color: white;
                padding: 8px 20px;
                border-radius: 6px;
                cursor: pointer;
                font-size: 14px;
                font-weight: 500;
                transition: all 0.3s ease;
                border: none;
                margin-top: 8px;
            }

            .file-upload-btn:hover {
                background-color: #3f3db8;
                transform: translateY(-1px);
            }

            .file-upload-btn i {
                font-size: 14px;
            }

            /* Remove button styling */
            .remove-image-btn {
                position: absolute;
                top: 10px;
                right: 10px;
                background-color: rgba(220, 53, 69, 0.9);
                color: white;
                border: none;
                border-radius: 50%;
                width: 30px;
                height: 30px;
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                transition: all 0.2s ease;
                z-index: 10;
            }

            .remove-image-btn:hover {
                background-color: #dc3545;
                transform: scale(1.05);
            }

            .student-form-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                gap: 10px 15px;
            }

            /* Make inputs take full width */
            .student-form-grid .form-control,
            .student-form-grid select,
            .student-form-grid textarea {
                width: 100%;
                box-sizing: border-box;
            }

            /* Force single column on very small screens */
            @media (max-width: 375px) {
                .student-form-grid {
                    grid-template-columns: 1fr !important;
                }
            }

            .select2-container {
                width: 100% !important;
            }

            .student-form-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
                gap: 10px 20px;
            }

            .student-form-grid .form-group {
                margin-bottom: 5px;
            }

            .student-form-grid label {
                margin-bottom: 3px;
            }

            .date-input-wrapper {
                position: relative;
                width: 100%;
            }

            .date-picker {
                padding-right: 40px;
                cursor: pointer;
                transition: all 0.3s ease;
                border: 1px solid #dee2e6;
                border-radius: 6px;
                padding: 8px 12px;
                font-size: 14px;
            }

            .date-picker:focus {
                border-color: #5351e4;
                box-shadow: 0 0 0 3px rgba(83, 81, 228, 0.1);
                outline: none;
            }

            .calendar-icon {
                position: absolute;
                right: 12px;
                top: 50%;
                transform: translateY(-50%);
                color: #6c757d;
                pointer-events: none;
                font-size: 16px;
                transition: color 0.3s ease;
            }

            .date-picker:focus+.calendar-icon {
                color: #5351e4;
            }

            .date-picker::-webkit-calendar-picker-indicator {
                opacity: 0;
                position: absolute;
                right: 0;
                width: 100%;
                height: 100%;
                cursor: pointer;
            }

            .date-picker::-webkit-datetime-edit {
                color: #495057;
            }

            .date-picker:invalid::-webkit-datetime-edit {
                color: #6c757d;
            }

            @media (max-width: 768px) {
                .date-picker {
                    font-size: 16px;
                }
            }
        </style>

        <div class="row">
            <div class="col-lg-12 col-xl-12 col-md-12 col-sm-12">
                <div class="card bg-primary">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0 text-white">{{ trans('common.add_student') }}</h4>
                        <a href="{{ url('students/all-students') }}" class="btn text-white"
                            style="background-color: #5351e4;">
                            <i class="fas fa-users text-white"></i>{{ trans('common.all_students') }}
                        </a>
                    </div>
                    <div class="card-body bg-light">
                        <form id="createStudentForm" method="POST" action="{{ route('students.store') }}">
                            @csrf

                            <div class="student-form-grid">

                                <input type="hidden" name="School" id="School" value="{{ Helper::requireSchool() }}">

                                <div class="form-group">
                                    <label>School <span class="text-danger">*</span></label>
                                    <input type="text" name="Student_ID" class="form-control"
                                        value="{{ Helper::schoolNameBySchoolID(Helper::requireSchool()) }}" readonly>
                                </div>

                                <div class="form-group">
                                    <label>Category <span class="text-danger">*</span></label>
                                    <select name="Category" class="form-control select2">
                                        <option value="">-- Select --</option>
                                        <option value="ID">Idaad - ID</option>
                                        <option value="TH">Thanawi - TH</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Admission Year <span class="text-danger">*</span></label>
                                    <input type="text" name="Admission_Year" class="form-control" id="year"
                                        value="{{Helper::schoolActiveYearName()}}" readonly>
                                    {{-- <select name="" id="" class="form-control select2">
                                        <option value="">All Years</option>
                                        @foreach ($years as $year)
                                        <option value="{{ $year }}" {{ request('year')==$year ? 'selected' : '' }}>
                                            {{ $year }}
                                        </option>
                                        @endforeach
                                    </select> --}}
                                </div>

                                <div class="form-group">
                                    <label>Student ID <span class="text-danger">*</span></label>
                                    <input type="text" name="Student_ID" class="form-control" id="Student_ID" readonly>
                                </div>

                                <!-- Migration-based fields -->
                                <div class="form-group">
                                    <label>First Name <span class="text-danger">*</span></label>
                                    <input type="text" name="firstname" class="form-control">
                                </div>

                                <div class="form-group">
                                    <label>Last Name <span class="text-danger">*</span></label>
                                    <input type="text" name="lastname" class="form-control">
                                </div>

                                <div class="form-group">
                                    <label>Class (Senior) <span class="text-danger">*</span></label>
                                    <select name="senior" id="senior" class="form-control select2" disabled>
                                        <option value="">-- Select Senior --</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Stream <span class="text-danger">*</span></label>
                                    <select name="stream" id="stream" class="form-control select2">
                                        <option value="">-- Select Stream --</option>
                                        <!-- Options will be loaded dynamically via AJAX -->
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Gender <span class="text-danger">*</span></label>
                                    <select name="gender" class="form-control select2">
                                        <option value="">-- Select --</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Registration Number</label>
                                    <input type="text" name="registration_number" class="form-control">
                                </div>

                                <div class="form-group">
                                    <label>Admission Number</label>
                                    <input type="text" name="admission_number" class="form-control">
                                </div>

                                <div class="form-group">
                                    <label>Primary Contact</label>
                                    <input type="text" name="primary_contact" class="form-control">
                                </div>

                                <div class="form-group">
                                    <label>Other Contact</label>
                                    <input type="text" name="other_contact" class="form-control">
                                </div>


                                <div class="form-group">
                                    <label>Date of Admission</label>
                                    <input type="date" name="date_of_admission" class="form-control">
                                </div>

                                <div class="form-group">
                                    <label>PLE Score</label>
                                    <input type="number" step="0.01" name="ple_score" class="form-control">
                                </div>

                                <div class="form-group">
                                    <label>UCE Score</label>
                                    <input type="number" step="0.01" name="uce_score" class="form-control">
                                </div>

                                <div class="form-group">
                                    <label>Previous School</label>
                                    <input type="text" name="previous_school" class="form-control">
                                </div>

                                <div class="form-group">
                                    <label>Primary School Name</label>
                                    <input type="text" name="primary_school_name" class="form-control">
                                </div>

                                <div class="form-group">
                                    <label>Guardian Names</label>
                                    <input type="text" name="guardian_names" class="form-control">
                                </div>

                                <div class="form-group">
                                    <label>Relation</label>
                                    <input type="text" name="relation" class="form-control">
                                </div>

                                <div class="form-group">
                                    <label>Guardian Phone</label>
                                    <input type="text" name="guardian_phone" class="form-control">
                                </div>

                                <div class="form-group">
                                    <label>Guardian Email</label>
                                    <input type="email" name="guardian_email" class="form-control">
                                </div>

                                <div class="form-group">
                                    <label>Home Address</label>
                                    <input type="text" name="home_address" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Date of Birth</label>
                                    <div class="date-input-wrapper">
                                        <input type="date" name="date_of_birth" class="form-control date-picker"
                                            id="dateOfBirth" max="<?php echo date('Y-m-d'); ?>">
                                        <i class="fas fa-calendar-alt calendar-icon"></i>
                                    </div>
                                    <small class="form-text text-muted">Select the student's date of birth</small>
                                </div>

                                <div class="form-group">
                                    <label>Birth Certificate Entry Number</label>
                                    <input type="text" name="birth_certificate_entry_number" class="form-control">
                                </div>

                                <div class="form-group">
                                    <label>Nationality</label>
                                    <input type="text" name="nationality" class="form-control">
                                </div>

                                <div class="form-group">
                                    <label>Medical History</label>
                                    <textarea name="medical_history" class="form-control"></textarea>
                                </div>

                                <div class="form-group">
                                    <label>Comments</label>
                                    <textarea name="comments" class="form-control"></textarea>
                                </div>

                            </div>

                            <div class="student-form-grid">
                                <div class="form-group">
                                    <label>Student Photo</label>
                                    <div class="file-upload-wrapper">
                                        <div class="file-upload-preview" id="photoPreview">
                                            <i class="fas fa-camera"></i>
                                            <span>No image selected</span>
                                        </div>
                                        <input type="file" name="student_photo" class="file-upload-input" id="studentPhoto"
                                            accept="image/*">
                                        <label for="studentPhoto" class="file-upload-btn">
                                            <i class="fas fa-upload"></i> Choose Photo
                                        </label>
                                        <small class="form-text text-muted">Supported formats: JPG, PNG, GIF. Max size:
                                            2MB</small>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-4">
                                <button type="submit" class="btn text-white" style="background-color:#5351e4;">
                                    <i class="fa-solid fa-paper-plane me-2"></i> Submit
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

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        const oLevel = @json($oLevel);
        const aLevel = @json($aLevel);

        function populateSeniorOptions(data) {
            const $senior = $('#senior');
            $senior.empty().append('<option value="">-- Select Senior --</option>');

            data.forEach(item => {
                $senior.append(
                    `<option value="${item.md_id}">${item.md_name}</option>`
                );
            });

            $senior.trigger('change'); // refresh select2
        }

        // Listen to Category change
        $(document).ready(function () {

            const oLevel = @json($oLevel);
            const aLevel = @json($aLevel);

            function populateSeniorOptions(data) {
                const $senior = $('#senior');

                $senior.empty().append('<option value="">-- Select Senior --</option>');

                console.log('DATA:', data); // 👈 debug

                data.forEach(item => {
                    $senior.append(
                        `<option value="${item.md_id}">${item.md_name}</option>`
                    );
                });

                // 🔥 IMPORTANT for Select2
                $senior.trigger('change.select2');
            }

            $(document).on('change', 'select[name="Category"]', function () {
                const val = $(this).val();
                const $senior = $('#senior');

                console.log('Category selected:', val); // 👈 debug

                if (val === 'ID') {
                    $senior.prop('disabled', false);
                    populateSeniorOptions(oLevel);

                } else if (val === 'TH') {
                    $senior.prop('disabled', false);
                    populateSeniorOptions(aLevel);

                } else {
                    $senior.prop('disabled', true);
                    $senior.empty().append('<option value="">-- Select Senior --</option>');
                }

                $senior.trigger('change.select2');
            });

        });

    </script>

    <script>
        $(document).ready(function () {

            function updateStudentID() {
                let schoolId = $('#School').val();
                let category = $('select[name="Category"]').val();
                let year = $('input[name="Admission_Year"]').val();

                if (schoolId && category && year) {
                    $.ajax({
                        url: '{{ route('students.generate-id') }}',
                        data: {
                            school_id: schoolId,
                            category: category,
                            year: year
                        },
                        success: function (res) {
                            $('#Student_ID').val(res.student_id);
                        }
                    });
                } else {
                    $('#Student_ID').val('');
                }
            }

            $('select[name="Category"], input[name="Admission_Year"]').on('change', updateStudentID);

            updateStudentID();
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const dateInput = document.getElementById('dateOfBirth');
            if (dateInput) {
                const today = new Date();
                const yyyy = today.getFullYear();
                const mm = String(today.getMonth() + 1).padStart(2, '0');
                const dd = String(today.getDate()).padStart(2, '0');
                const maxDate = `${yyyy}-${mm}-${dd}`;

                dateInput.setAttribute('max', maxDate);

                dateInput.addEventListener('change', function () {
                    if (this.value > maxDate) {
                        this.value = '';
                        alert('Date of birth cannot be in the future');
                    }
                });
            }
        });
    </script>

    <script>
        $(document).ready(function () {
            $('#createStudentForm').on('submit', function (e) {
                e.preventDefault();

                let $form = $(this);
                let $submitBtn = $form.find('button[type="submit"]');
                let isValid = true;

                // Clear previous validation
                $form.find('.is-invalid').removeClass('is-invalid');
                $form.find('.invalid-feedback').remove();

                // Required fields and their labels for error messages
                let requiredFields = {
                    'School': 'School',
                    'Category': 'Category',
                    'Admission_Year': 'Admission Year',
                    'Student_ID': 'Student ID',
                    'firstname': 'First Name',
                    'lastname': 'Last Name',
                    'senior': 'Senior',
                    'stream': 'Stream',
                    'gender': 'Gender'
                };

                // Validate required fields
                $.each(requiredFields, function (fieldName, label) {
                    let input = $form.find(`[name="${fieldName}"]`);
                    if (!input.val() || input.val().trim() === '') {
                        input.addClass('is-invalid');

                        // Place error inside the form-group container
                        input.closest('.form-group').append(
                            `<div class="invalid-feedback d-block">${label} is required.</div>`
                        );

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

                // Confirm before submission
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You are about to submit the student data.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, submit it!',
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: '#5351e4',
                    cancelButtonColor: '#6c757d'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Show custom loader with progress
                        Swal.fire({
                            title: 'Saving Student',
                            html: `
                                        <div class="custom-loader-container">
                                            <div class="loader-spinner"></div>
                                            <div class="loader-text">Processing student data...</div>
                                            <div class="loader-progress">
                                                <div class="progress-bar"></div>
                                            </div>
                                            <div class="loader-steps">
                                                <span class="step active">Validating</span>
                                                <span class="step">Saving</span>
                                                <span class="step">Complete</span>
                                            </div>
                                        </div>
                                    `,
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            showConfirmButton: false,
                            width: '400px',
                            didOpen: () => {
                                // Add custom styles
                                const style = document.createElement('style');
                                style.textContent = `
                                            .custom-loader-container {
                                                text-align: center;
                                                padding: 10px 0;
                                            }
                                            .loader-spinner {
                                                width: 50px;
                                                height: 50px;
                                                border: 4px solid #e9ecef;
                                                border-top-color: #5351e4;
                                                border-radius: 50%;
                                                animation: spin 0.8s linear infinite;
                                                margin: 0 auto 20px;
                                            }
                                            @keyframes spin {
                                                to { transform: rotate(360deg); }
                                            }
                                            .loader-text {
                                                color: #495057;
                                                font-size: 14px;
                                                margin-bottom: 15px;
                                            }
                                            .loader-progress {
                                                background: #e9ecef;
                                                border-radius: 10px;
                                                height: 6px;
                                                overflow: hidden;
                                                margin-bottom: 20px;
                                            }
                                            .progress-bar {
                                                width: 0%;
                                                height: 100%;
                                                background: #5351e4;
                                                border-radius: 10px;
                                                transition: width 0.3s ease;
                                            }
                                            .loader-steps {
                                                display: flex;
                                                justify-content: space-between;
                                                margin-top: 15px;
                                            }
                                            .loader-steps .step {
                                                font-size: 12px;
                                                color: #adb5bd;
                                                transition: color 0.3s ease;
                                            }
                                            .loader-steps .step.active {
                                                color: #5351e4;
                                                font-weight: 500;
                                            }
                                            .loader-steps .step.completed {
                                                color: #28a745;
                                            }
                                        `;
                                document.head.appendChild(style);

                                // Animate progress
                                let progress = 0;
                                const progressBar = Swal.getHtmlContainer()
                                    .querySelector('.progress-bar');
                                const steps = Swal.getHtmlContainer().querySelectorAll(
                                    '.step');

                                const updateProgress = (percent, stepIndex) => {
                                    progressBar.style.width = percent + '%';
                                    steps.forEach((step, idx) => {
                                        if (idx < stepIndex) {
                                            step.classList.add('completed');
                                            step.classList.remove('active');
                                        } else if (idx === stepIndex) {
                                            step.classList.add('active');
                                            step.classList.remove(
                                                'completed');
                                        }
                                    });
                                };

                                updateProgress(30, 0);
                            }
                        });

                        // Make the AJAX request
                        $.ajax({
                            url: $form.attr('action'),
                            type: 'POST',
                            data: new FormData($form[0]),
                            processData: false,
                            contentType: false,
                            headers: {
                                'X-CSRF-TOKEN': $('input[name="_token"]').val()
                            },
                            xhr: function () {
                                const xhr = new window.XMLHttpRequest();
                                xhr.upload.addEventListener('progress', function (e) {
                                    if (e.lengthComputable) {
                                        const percent = (e.loaded / e.total) *
                                            100;
                                        // Update progress for file upload
                                        const progressBar = Swal
                                            .getHtmlContainer().querySelector(
                                                '.progress-bar');
                                        const steps = Swal.getHtmlContainer()
                                            .querySelectorAll('.step');
                                        if (progressBar) {
                                            progressBar.style.width = Math.min(
                                                percent, 50) + '%';
                                        }
                                        if (percent > 30 && steps[1]) {
                                            steps[1].classList.add('active');
                                        }
                                    }
                                });
                                return xhr;
                            },
                            success: function (response) {
                                // Update to 100% and mark complete
                                const progressBar = Swal.getHtmlContainer()
                                    .querySelector('.progress-bar');
                                const steps = Swal.getHtmlContainer().querySelectorAll(
                                    '.step');
                                const loaderText = Swal.getHtmlContainer()
                                    .querySelector('.loader-text');

                                progressBar.style.width = '100%';
                                steps.forEach(step => {
                                    step.classList.remove('active');
                                    step.classList.add('completed');
                                });
                                loaderText.textContent = 'Student saved successfully!';

                                setTimeout(() => {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Success!',
                                        text: 'Student data has been submitted successfully.',
                                        confirmButtonColor: '#5351e4',
                                        confirmButtonText: 'OK',
                                        showConfirmButton: true
                                    }).then((result) => {
                                        if (result.isConfirmed) {
                                            if (response.redirect) {
                                                window.location.href =
                                                    response.redirect;
                                            } else {
                                                location
                                                    .reload(); // fallback refresh
                                            }
                                        }
                                    });
                                }, 500);
                            },
                            error: function (data) {
                                $('body').html(data.responseText);
                            }
                            // error: function(xhr, status, error) {
                            //     Swal.fire({
                            //         icon: 'error',
                            //         title: 'Submission Failed',
                            //         text: 'There was an error saving the student data. Please try again.',
                            //         confirmButtonColor: '#5351e4',
                            //         confirmButtonText: 'Try Again'
                            //     });
                            // }
                        });
                    }
                });
            });
        });
    </script>

    <script>
        document.getElementById('studentPhoto').addEventListener('change', function (e) {
            const preview = document.getElementById('photoPreview');
            const file = e.target.files[0];

            if (file) {
                // Check file size (2MB limit)
                if (file.size > 2 * 1024 * 1024) {
                    alert('File size must be less than 2MB');
                    this.value = '';
                    return;
                }

                // Check file type
                if (!file.type.match('image.*')) {
                    alert('Please select an image file');
                    this.value = '';
                    return;
                }

                const reader = new FileReader();

                reader.onload = function (e) {
                    // Remove existing image if any
                    const existingImg = preview.querySelector('img');
                    if (existingImg) {
                        existingImg.remove();
                    }

                    // Remove remove button if exists
                    const existingBtn = preview.querySelector('.remove-image-btn');
                    if (existingBtn) {
                        existingBtn.remove();
                    }

                    // Create image element
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.style.width = 'auto';
                    img.style.height = 'auto';
                    img.style.maxWidth = '100%';
                    img.style.maxHeight = '100%';
                    img.style.objectFit = 'contain';

                    // Create remove button
                    const removeBtn = document.createElement('button');
                    removeBtn.type = 'button';
                    removeBtn.className = 'remove-image-btn';
                    removeBtn.innerHTML = '<i class="fas fa-times"></i>';
                    removeBtn.onclick = function (e) {
                        e.stopPropagation();
                        document.getElementById('studentPhoto').value = '';
                        preview.classList.remove('has-image');
                        img.remove();
                        removeBtn.remove();

                        // Restore original content
                        const icon = document.createElement('i');
                        icon.className = 'fas fa-camera';
                        const text = document.createElement('span');
                        text.textContent = 'No image selected';
                        preview.appendChild(icon);
                        preview.appendChild(text);
                    };

                    preview.appendChild(img);
                    preview.appendChild(removeBtn);
                    preview.classList.add('has-image');

                    // Hide original content
                    const icon = preview.querySelector('i:not(.remove-image-btn i)');
                    const text = preview.querySelector('span');
                    if (icon) icon.style.display = 'none';
                    if (text) text.style.display = 'none';
                };

                reader.readAsDataURL(file);
            }
        });
    </script>

    <script>
        $(document).ready(function () {
            $('select[name="senior"]').on('change', function () {
                let seniorCode = $(this).val();
                let $streamSelect = $('#stream');

                $streamSelect.html('<option value="">Loading streams...</option>');

                if (seniorCode) {
                    $.ajax({
                        url: '/get-streams/' + seniorCode,
                        type: 'GET',
                        success: function (response) {
                            $streamSelect.empty();
                            $streamSelect.append('<option value="">-- Select Stream --</option>');
                            if (response.streams && response.streams.length > 0) {
                                response.streams.forEach(function (stream) {
                                    $streamSelect.append('<option value="' + stream.stream_id + '">' + stream.stream_id + '</option>');
                                });
                            } else {
                                $streamSelect.append('<option value="">No streams found</option>');
                            }
                        },
                        // error: function () {
                        //     $streamSelect.html('<option value="">Error loading streams</option>');
                        // }
                        error: function (data) {
                            $('body').html(data.responseText);
                        }
                    });
                } else {
                    $streamSelect.html('<option value="">-- Select Stream --</option>');
                }
            });
        });
    </script>

@endsection

@section('js')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.colVis.min.js"></script>

    <script></script>
@endsection