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
            /* Enhanced Table Styling */
            .table {
                border-radius: 12px;
                overflow: hidden;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            }

            .table thead th {
                background: linear-gradient(135deg, #2C29CA 0%, #2C29CA 100%);
                color: white;
                font-weight: 600;
                border: none;
                padding: 12px;
            }

            .table-hover tbody tr:hover {
                background-color: rgba(102, 126, 234, 0.05);
                transition: all 0.3s ease;
            }

            /* Pagination Styling */
            .pagination .page-item.active .page-link {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                border-color: #667eea;
                color: white;
            }

            .pagination .page-link {
                color: #667eea;
                border-radius: 8px;
                margin: 0 3px;
                transition: all 0.3s ease;
            }

            .pagination .page-link:hover {
                background-color: #2C29CA;
                border-color: #2C29CA;
                color: white;
                transform: translateY(-2px);
            }

            /* Stream Group Animation */
            .stream-group {
                animation: fadeInUp 0.5s ease;
                margin-bottom: 2rem;
            }

            /* Badge Styling */
            .badge {
                padding: 6px 12px;
                font-weight: 500;
                border-radius: 20px;
            }

            /* Button Group Styling */
            .btn-group-sm .btn {
                padding: 5px 12px;
                border-radius: 6px;
                margin: 0 2px;
            }

            /* Modal Custom Styles */
            .modal-dialog.modal-lg {
                max-width: 800px;
            }

            .student-details-wrapper {
                max-height: 70vh;
                overflow-y: auto;
            }

            .details-section {
                animation: fadeInUp 0.5s ease;
            }

            @keyframes fadeInUp {
                from {
                    opacity: 0;
                    transform: translateY(20px);
                }

                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .info-item {
                background: #f8f9fa;
                padding: 10px 15px;
                border-radius: 8px;
                transition: all 0.3s ease;
            }

            .info-item:hover {
                background: #e9ecef;
                transform: translateX(5px);
            }

            .info-item label {
                display: block;
                margin-bottom: 5px;
                font-size: 0.75rem;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }

            .info-item p {
                font-size: 0.95rem;
                margin-bottom: 0;
                color: #2c3e50;
            }

            .section-title {
                font-size: 1.1rem;
                font-weight: 600;
                color: #2c3e50;
                position: relative;
            }

            .section-title i {
                font-size: 1.2rem;
            }

            .student-badges .badge {
                font-size: 0.85rem;
                padding: 6px 12px;
            }

            /* File Upload Container */
            .file-upload-container {
                width: 100%;
                max-width: 400px;
                margin: 0 auto;
            }

            .file-upload-wrapper {
                position: relative;
                width: 100%;
            }

            .file-input {
                position: absolute;
                opacity: 0;
                width: 100%;
                height: 100%;
                top: 0;
                left: 0;
                cursor: pointer;
                z-index: 2;
            }

            .file-upload-preview {
                position: relative;
                width: 100%;
                min-height: 200px;
                border: 2px dashed #e2e8f0;
                border-radius: 12px;
                background: #f8fafc;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                gap: 12px;
                transition: all 0.3s ease;
                cursor: pointer;
                overflow: hidden;
                padding: 20px;
            }

            .file-upload-preview:hover {
                border-color: #3b82f6;
                background: #f0f9ff;
                transform: translateY(-2px);
                box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
            }

            .upload-icon {
                width: 48px;
                height: 48px;
                color: #94a3b8;
                transition: all 0.3s ease;
            }

            .file-upload-preview:hover .upload-icon {
                color: #3b82f6;
                transform: scale(1.1);
            }

            .upload-text {
                font-size: 1rem;
                font-weight: 500;
                color: #1e293b;
                margin: 0;
            }

            .upload-hint {
                font-size: 0.75rem;
                color: #64748b;
                margin: 0;
            }

            .preview-image {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                object-fit: contain;
                object-position: center;
                background-color: #f8fafc;
            }

            .remove-image-btn {
                position: absolute;
                top: 12px;
                right: 12px;
                width: 32px;
                height: 32px;
                border-radius: 50%;
                background: #ef4444 !important;
                border: 2px solid white !important;
                color: white !important;
                font-size: 18px;
                font-weight: bold;
                line-height: 1;
                cursor: pointer;
                display: flex !important;
                align-items: center;
                justify-content: center;
                z-index: 10;
                transition: transform 0.2s ease;
                opacity: 1 !important;
                transform: scale(1) !important;
                box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3);
            }

            .remove-image-btn:hover {
                transform: scale(1.15) !important;
                background: #dc2626 !important;
            }

            .file-upload-error {
                margin-top: 8px;
                font-size: 0.75rem;
                color: #ef4444;
                min-height: 20px;
            }

            .file-upload-preview.loading {
                position: relative;
            }

            .file-upload-preview.loading::after {
                content: '';
                position: absolute;
                top: 50%;
                left: 50%;
                width: 30px;
                height: 30px;
                border: 3px solid #e2e8f0;
                border-top-color: #3b82f6;
                border-radius: 50%;
                animation: spin 0.6s linear infinite;
                transform: translate(-50%, -50%);
            }

            @keyframes spin {
                to {
                    transform: translate(-50%, -50%) rotate(360deg);
                }
            }

            .file-upload-preview.success {
                border-color: #10b981;
                background: #f0fdf4;
            }

            .edit-section-title {
                font-size: 1rem;
                font-weight: 600;
                color: #2c3e50;
            }

            #editStudentForm .form-control,
            #editStudentForm .form-select {
                border-radius: 8px;
                border: 1px solid #dee2e6;
                transition: border-color 0.3s ease, box-shadow 0.3s ease;
                font-size: 0.9rem;
            }

            #editStudentForm .form-control:focus,
            #editStudentForm .form-select:focus {
                border-color: #2C29CA;
                box-shadow: 0 0 0 0.2rem rgba(245, 87, 108, 0.15);
            }

            #editStudentForm label {
                font-size: 0.75rem;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }

            .edit-section {
                background: #fff;
                border-radius: 10px;
                padding: 20px;
                border: 1px solid #f0f0f0;
                animation: fadeInUp 0.4s ease;
            }

            #saveStudentEdit {
                background: linear-gradient(135deg, #2C29CA 0%, #2C29CA 100%);
                border: none;
                transition: opacity 0.3s;
            }

            #saveStudentEdit:hover {
                opacity: 0.9;
            }

            .swal2-container {
                z-index: 99999 !important;
            }
        </style>

        <div class="row">
            <div class="col-lg-12">
                <div class="card mt-4" id="resultsCard">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">All Students</h5>
                    </div>

                    <div class="card-body bg-white" id="searchResults">

                        @if (empty($groupedStudents))
                            <p>No students found.</p>
                        @else
                            @foreach ($groupedStudents as $senior => $streams)
                                <div class="senior-group mb-4">
                                    <h4 class="text-info">
                                        Class :
                                        <span class="text-primary fw-bold">
                                            {{ Helper::item_md_name($senior) }}
                                        </span>
                                    </h4>

                                    @foreach ($streams as $stream => $students)
                                        <div class="stream-group mb-5">

                                            <!-- Header -->
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <h5 class="text-info mb-0">
                                                    Stream:  <span class="text-primary fw-bold">{{ $stream }}</span>
                                                </h5>
                                            </div>

                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                               <h5 class="text-secondary mb-0">
                                                    Students :
                                                    <span class="badge bg-primary text-white ms-2">
                                                        {{ $students->total() }} students
                                                    </span>
                                                </h5>
                                            </div>

                                            <!-- Table -->
                                            <div class="table-responsive">
                                                <table class="table table-hover table-striped">
                                                    <thead class="bg-light">
                                                        <tr>
                                                            <th width="5%" class="text-center">#</th>
                                                            <th width="10%" class="text-center">Photo</th>
                                                            <th width="15%" class="text-center">Firstname</th>
                                                            <th width="15%" class="text-center">Lastname</th>
                                                            <th width="10%" class="text-center">Gender</th>
                                                            <th width="30%" class="text-center">Action</th>
                                                        </tr>
                                                    </thead>

                                                    <tbody>
                                                        @foreach ($students as $key => $student)
                                                            <tr id="student-row-{{ $student->id }}">
                                                                <td class="text-center">{{ $students->firstItem() + $key }}
                                                                </td>

                                                                <!-- Photo -->
                                                                <td class="text-center">
                                                                    @php
                                                                        $imagePath = null;
                                                                        if ($student->student_photo) {
                                                                            foreach (
                                                                                ['jpg', 'jpeg', 'png', 'gif']
                                                                                as $ext
                                                                            ) {
                                                                                $path =
                                                                                    'uploads/studentPhotos/' .
                                                                                    $student->student_photo .
                                                                                    '.' .
                                                                                    $ext;
                                                                                if (file_exists(public_path($path))) {
                                                                                    $imagePath = $path;
                                                                                    break;
                                                                                }
                                                                            }
                                                                        }
                                                                    @endphp

                                                                    @if ($imagePath)
                                                                        <img src="{{ asset($imagePath) }}"
                                                                            class="rounded-circle border"
                                                                            style="width:45px;height:45px;object-fit:cover;">
                                                                    @else
                                                                        <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center"
                                                                            style="width:45px;height:45px;">
                                                                            <i class="bi bi-person text-white"></i>
                                                                        </div>
                                                                    @endif
                                                                </td>

                                                                <td class="text-center fw-medium">{{ $student->firstname }}
                                                                </td>
                                                                <td class="text-center">{{ $student->lastname }}</td>

                                                                <!-- Gender -->
                                                                <td class="text-center">
                                                                    <span
                                                                        class="badge {{ $student->gender == 'Male' ? 'bg-info' : ($student->gender == 'Female' ? 'bg-success' : 'bg-secondary') }}">
                                                                        {{ $student->gender }}
                                                                    </span>
                                                                </td>

                                                                <!-- Actions -->
                                                                <td class="text-center">
                                                                    <div class="btn-group btn-group-sm">
                                                                        <button type="button"
                                                                            class="btn btn-info view-student-btn"
                                                                            data-id="{{ $student->id }}">
                                                                            <i class="bi bi-eye"></i>
                                                                        </button>

                                                                        <button type="button"
                                                                            class="btn btn-primary edit-student-btn"
                                                                            data-id="{{ $student->id }}">
                                                                            <i class="bi bi-pencil-square"></i>
                                                                        </button>

                                                                        <button type="button"
                                                                            class="btn btn-danger delete-student-btn"
                                                                            data-id="{{ $student->id }}">
                                                                            <i class="bi bi-trash"></i>
                                                                        </button>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>

                                            <!-- Pagination -->
                                            @if ($students->total() > 10)
                                                <div class="d-flex justify-content-between align-items-center mt-3">
                                                    <div class="text-muted small">
                                                        Showing {{ $students->firstItem() }}
                                                        to {{ $students->lastItem() }}
                                                        of {{ $students->total() }} students
                                                    </div>

                                                    {{ $students->onEachSide(1)->links('pagination::bootstrap-5') }}
                                                </div>
                                            @endif

                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        @endif

                    </div>
                </div>
            </div>
        </div>

        <!-- Student Details Modal -->
        <div class="modal fade" id="studentDetailsModal" tabindex="-1" aria-labelledby="studentDetailsModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <h5 class="modal-title text-white" id="studentDetailsModalLabel">
                            <i class="bi bi-person-badge me-2"></i> Student Details
                        </h5>
                        <button type="button" class="btn p-0 border-0 bg-transparent" data-bs-dismiss="modal"
                            aria-label="Close">
                            <i class="fas fa-times" style="font-size: 1.2rem; color: #fff;"></i>
                        </button>
                    </div>
                    <div class="modal-body p-0">
                        <div class="student-details-wrapper">
                            <div class="text-center p-5" id="modalLoadingSpinner">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                            <div id="studentDetailsContent" class="d-none">
                                <div class="student-header p-4"
                                    style="background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);">
                                    <div class="row align-items-center">
                                        <div class="col-md-3 text-center">
                                            <div class="student-photo-container"
                                                style="position: relative; width: 150px; height: 150px; margin: 0 auto;">
                                                <img id="modalStudentPhoto" src="" alt="Student Photo"
                                                    class="img-fluid rounded-circle border border-3 border-white shadow-lg"
                                                    style="width: 150px; height: 150px; object-fit: cover; display: block;">
                                                <div class="photo-placeholder bg-light rounded-circle d-none align-items-center justify-content-center"
                                                    style="width: 150px; height: 150px;">
                                                    <i class="bi bi-person-circle"
                                                        style="font-size: 80px; color: #6c757d;"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-9">
                                            <h2 id="modalStudentName" class="mb-2 fw-bold text-dark"></h2>
                                            <div class="student-badges">
                                                <span class="badge text-white bg-primary me-2" id="modalStudentId"></span>
                                                <span class="badge text-white bg-info me-2"
                                                    id="modalStudentCategory"></span>
                                                <span class="badge text-white bg-success me-2"
                                                    id="modalStudentGender"></span>
                                                <span class="badge text-white bg-secondary"
                                                    id="modalStudentStream"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="p-4">
                                    <div class="details-section mb-4">
                                        <h5 class="section-title border-bottom pb-2 mb-3">
                                            <i class="bi bi-person-vcard me-2 text-primary"></i> Personal Information
                                        </h5>
                                        <div class="row g-3">
                                            <div class="col-md-6 mb-2">
                                                <div class="info-item"><label class="text-muted small fw-bold">Full
                                                        Name</label>
                                                    <p class="mb-0 fw-medium" id="modalFullName"></p>
                                                </div>
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <div class="info-item"><label class="text-muted small fw-bold">Date of
                                                        Birth</label>
                                                    <p class="mb-0 fw-medium" id="modalDateOfBirth"></p>
                                                </div>
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <div class="info-item"><label class="text-muted small fw-bold">Place of
                                                        Birth</label>
                                                    <p class="mb-0 fw-medium" id="modalPlaceOfBirth"></p>
                                                </div>
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <div class="info-item"><label
                                                        class="text-muted small fw-bold">Nationality</label>
                                                    <p class="mb-0 fw-medium" id="modalNationality"></p>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="info-item"><label class="text-muted small fw-bold">Birth
                                                        Certificate Number</label>
                                                    <p class="mb-0 fw-medium" id="modalBirthCertificate"></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="details-section mb-4">
                                        <h5 class="section-title border-bottom pb-2 mb-3">
                                            <i class="bi bi-book me-2 text-primary"></i> Academic Information
                                        </h5>
                                        <div class="row g-3">
                                            <div class="col-md-6 mb-2">
                                                <div class="info-item"><label
                                                        class="text-muted small fw-bold">Registration Number</label>
                                                    <p class="mb-0 fw-medium" id="modalRegNumber"></p>
                                                </div>
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <div class="info-item"><label class="text-muted small fw-bold">Admission
                                                        Number</label>
                                                    <p class="mb-0 fw-medium" id="modalAdmissionNumber"></p>
                                                </div>
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <div class="info-item"><label class="text-muted small fw-bold">Date of
                                                        Admission</label>
                                                    <p class="mb-0 fw-medium" id="modalDateOfAdmission"></p>
                                                </div>
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <div class="info-item"><label class="text-muted small fw-bold">Admission
                                                        Year</label>
                                                    <p class="mb-0 fw-medium" id="modalAdmissionYear"></p>
                                                </div>
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <div class="info-item"><label
                                                        class="text-muted small fw-bold">Senior/Class</label>
                                                    <p class="mb-0 fw-medium" id="modalSenior"></p>
                                                </div>
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <div class="info-item"><label
                                                        class="text-muted small fw-bold">Stream</label>
                                                    <p class="mb-0 fw-medium" id="modalStream"></p>
                                                </div>
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <div class="info-item"><label class="text-muted small fw-bold">PLE
                                                        Score</label>
                                                    <p class="mb-0 fw-medium" id="modalPleScore"></p>
                                                </div>
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <div class="info-item"><label class="text-muted small fw-bold">UCE
                                                        Score</label>
                                                    <p class="mb-0 fw-medium" id="modalUceScore"></p>
                                                </div>
                                            </div>
                                            <div class="col-md-12 mb-2">
                                                <div class="info-item"><label class="text-muted small fw-bold">Previous
                                                        School</label>
                                                    <p class="mb-0 fw-medium" id="modalPreviousSchool"></p>
                                                </div>
                                            </div>
                                            <div class="col-md-12 mb-2">
                                                <div class="info-item"><label class="text-muted small fw-bold">Primary
                                                        School</label>
                                                    <p class="mb-0 fw-medium" id="modalPrimarySchool"></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="details-section mb-4">
                                        <h5 class="section-title border-bottom pb-2 mb-3">
                                            <i class="bi bi-telephone me-2 text-primary"></i> Contact Information
                                        </h5>
                                        <div class="row g-3">
                                            <div class="col-md-6 mb-2">
                                                <div class="info-item"><label class="text-muted small fw-bold">Primary
                                                        Contact</label>
                                                    <p class="mb-0 fw-medium" id="modalPrimaryContact"></p>
                                                </div>
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <div class="info-item"><label class="text-muted small fw-bold">Other
                                                        Contact</label>
                                                    <p class="mb-0 fw-medium" id="modalOtherContact"></p>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="info-item"><label class="text-muted small fw-bold">Home
                                                        Address</label>
                                                    <p class="mb-0 fw-medium" id="modalHomeAddress"></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="details-section mb-4">
                                        <h5 class="section-title border-bottom pb-2 mb-3">
                                            <i class="bi bi-people me-2 text-primary"></i> Guardian Information
                                        </h5>
                                        <div class="row g-3">
                                            <div class="col-md-6 mb-2">
                                                <div class="info-item"><label class="text-muted small fw-bold">Guardian
                                                        Names</label>
                                                    <p class="mb-0 fw-medium" id="modalGuardianNames"></p>
                                                </div>
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <div class="info-item"><label
                                                        class="text-muted small fw-bold">Relation</label>
                                                    <p class="mb-0 fw-medium" id="modalRelation"></p>
                                                </div>
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <div class="info-item"><label class="text-muted small fw-bold">Guardian
                                                        Phone</label>
                                                    <p class="mb-0 fw-medium" id="modalGuardianPhone"></p>
                                                </div>
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <div class="info-item"><label class="text-muted small fw-bold">Guardian
                                                        Email</label>
                                                    <p class="mb-0 fw-medium" id="modalGuardianEmail"></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="details-section">
                                        <h5 class="section-title border-bottom pb-2 mb-3">
                                            <i class="bi bi-info-circle me-2 text-primary"></i> Additional Information
                                        </h5>
                                        <div class="row g-3">
                                            <div class="col-md-12 mb-2">
                                                <div class="info-item"><label class="text-muted small fw-bold">Medical
                                                        History</label>
                                                    <p class="mb-0 fw-medium" id="modalMedicalHistory"></p>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="info-item"><label
                                                        class="text-muted small fw-bold">Comments</label>
                                                    <p class="mb-0 fw-medium" id="modalComments"></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle me-1"></i> Close
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Student Modal -->
        <div class="modal fade" id="editStudentModal" tabindex="-1" aria-labelledby="editStudentModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" style="max-width: 90%;">
                <div class="modal-content">
                    <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <h5 class="modal-title text-white" id="editStudentModalLabel">
                            <i class="bi bi-pencil-square me-2"></i> Edit Student
                        </h5>
                        <button type="button" class="btn p-0 border-0 bg-transparent" data-bs-dismiss="modal"
                            aria-label="Close">
                            <i class="fas fa-times" style="font-size: 1.2rem; color: #fff;"></i>
                        </button>
                    </div>
                    <div class="modal-body p-0">
                        <div class="text-center p-5" id="editModalLoadingSpinner">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                        <div id="editStudentForm" class="d-none p-4">
                            <input type="hidden" id="editStudentId">
                            <div class="edit-section mb-4">
                                <h5 class="edit-section-title border-bottom pb-2 mb-3">
                                    <i class="bi bi-person-vcard me-2 text-danger"></i> Personal Information
                                </h5>
                                <div class="row g-3">
                                    <div class="col-md-6"><label class="form-label fw-bold text-muted small">First Name
                                            <span class="text-danger">*</span></label><input type="text"
                                            class="form-control" id="editFirstname"></div>
                                    <div class="col-md-6"><label class="form-label fw-bold text-muted small">Last Name
                                            <span class="text-danger">*</span></label><input type="text"
                                            class="form-control" id="editLastname"></div>
                                    <div class="col-md-4"><label class="form-label fw-bold text-muted small">Gender <span
                                                class="text-danger">*</span></label><select class="form-control"
                                            id="editGender">
                                            <option value="">Select Gender</option>
                                            <option value="Male">Male</option>
                                            <option value="Female">Female</option>
                                            <option value="Other">Other</option>
                                        </select></div>
                                    <div class="col-md-4"><label class="form-label fw-bold text-muted small">Date of
                                            Birth</label><input type="date" class="form-control" id="editDateOfBirth">
                                    </div>
                                    <div class="col-md-4"><label class="form-label fw-bold text-muted small">Place of
                                            Birth</label><input type="text" class="form-control"
                                            id="editPlaceOfBirth"></div>
                                    <div class="col-md-6"><label
                                            class="form-label fw-bold text-muted small">Nationality</label><input
                                            type="text" class="form-control" id="editNationality"></div>
                                    <div class="col-md-6"><label class="form-label fw-bold text-muted small">Birth
                                            Certificate Number</label><input type="text" class="form-control"
                                            id="editBirthCertificate"></div>
                                </div>
                            </div>
                            <div class="edit-section mb-4">
                                <h5 class="edit-section-title border-bottom pb-2 mb-3"><i
                                        class="bi bi-book me-2 text-danger"></i> Academic Information</h5>
                                <div class="row g-3">
                                    <div class="col-md-4"><label class="form-label fw-bold text-muted small">Registration
                                            Number</label><input type="text" class="form-control" id="editRegNumber">
                                    </div>
                                    <div class="col-md-4"><label class="form-label fw-bold text-muted small">Admission
                                            Number</label><input type="text" class="form-control"
                                            id="editAdmissionNumber"></div>
                                    <div class="col-md-4"><label class="form-label fw-bold text-muted small">Admission
                                            Year</label><input type="number" class="form-control"
                                            id="editAdmissionYear"></div>
                                    <div class="col-md-4"><label class="form-label fw-bold text-muted small">Date of
                                            Admission</label><input type="date" class="form-control"
                                            id="editDateOfAdmission"></div>
                                    <div class="col-md-4"><label
                                            class="form-label fw-bold text-muted small">Senior/Class</label><input
                                            type="text" class="form-control" id="editSenior" readonly></div>
                                    <div class="col-md-4"><label
                                            class="form-label fw-bold text-muted small">Stream</label><input
                                            type="text" class="form-control" id="editStream" readonly></div>
                                    <div class="col-md-4"><label class="form-label fw-bold text-muted small">PLE
                                            Score</label><input type="text" class="form-control" id="editPleScore">
                                    </div>
                                    <div class="col-md-4"><label class="form-label fw-bold text-muted small">UCE
                                            Score</label><input type="text" class="form-control" id="editUceScore">
                                    </div>
                                    <div class="col-md-6"><label class="form-label fw-bold text-muted small">Previous
                                            School</label><input type="text" class="form-control"
                                            id="editPreviousSchool"></div>
                                    <div class="col-md-6"><label class="form-label fw-bold text-muted small">Primary
                                            School</label><input type="text" class="form-control"
                                            id="editPrimarySchool"></div>
                                </div>
                            </div>
                            <div class="edit-section mb-4">
                                <h5 class="edit-section-title border-bottom pb-2 mb-3"><i
                                        class="bi bi-telephone me-2 text-danger"></i> Contact Information</h5>
                                <div class="row g-3">
                                    <div class="col-md-4"><label class="form-label fw-bold text-muted small">Primary
                                            Contact</label><input type="text" class="form-control"
                                            id="editPrimaryContact"></div>
                                    <div class="col-md-4"><label class="form-label fw-bold text-muted small">Other
                                            Contact</label><input type="text" class="form-control"
                                            id="editOtherContact"></div>
                                    <div class="col-md-4"><label class="form-label fw-bold text-muted small">Home
                                            Address</label><input type="text" class="form-control"
                                            id="editHomeAddress"></div>
                                </div>
                            </div>
                            <div class="edit-section mb-4">
                                <h5 class="edit-section-title border-bottom pb-2 mb-3"><i
                                        class="bi bi-people me-2 text-danger"></i> Guardian Information</h5>
                                <div class="row g-3">
                                    <div class="col-md-6"><label class="form-label fw-bold text-muted small">Guardian
                                            Names</label><input type="text" class="form-control"
                                            id="editGuardianNames"></div>
                                    <div class="col-md-6"><label
                                            class="form-label fw-bold text-muted small">Relation</label><input
                                            type="text" class="form-control" id="editRelation"></div>
                                    <div class="col-md-6"><label class="form-label fw-bold text-muted small">Guardian
                                            Phone</label><input type="text" class="form-control"
                                            id="editGuardianPhone"></div>
                                    <div class="col-md-6"><label class="form-label fw-bold text-muted small">Guardian
                                            Email</label><input type="email" class="form-control"
                                            id="editGuardianEmail"></div>
                                </div>
                            </div>
                            <div class="edit-section">
                                <h5 class="edit-section-title border-bottom pb-2 mb-3"><i
                                        class="bi bi-info-circle me-2 text-danger"></i> Additional Information</h5>
                                <div class="row g-3">
                                    <div class="col-md-6"><label class="form-label fw-bold text-muted small">Medical
                                            History</label>
                                        <textarea class="form-control" id="editMedicalHistory" rows="3"></textarea>
                                    </div>
                                    <div class="col-md-6"><label
                                            class="form-label fw-bold text-muted small">Comments</label>
                                        <textarea class="form-control" id="editComments" rows="3"></textarea>
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label fw-bold text-muted small">Student Photo</label>
                                        <div class="file-upload-container">
                                            <div class="file-upload-wrapper">
                                                <div class="file-upload-preview" id="editStudentPhotoPreview">
                                                    <svg class="upload-icon" viewBox="0 0 24 24" fill="none"
                                                        stroke="currentColor" stroke-width="2">
                                                        <path
                                                            d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z" />
                                                        <circle cx="12" cy="13" r="4" />
                                                    </svg>
                                                    <span class="upload-text">Click to upload photo</span>
                                                    <span class="upload-hint">JPG, PNG, or GIF (Max 5MB)</span>
                                                </div>
                                                <input type="file" class="form-control file-input"
                                                    id="editStudentPhoto" name="studentPhoto"
                                                    accept="image/jpg,image/png,image/gif">
                                                <div class="file-upload-error" id="editStudentPhotoError"></div>
                                            </div>
                                        </div>
                                        <div class="mt-2" id="currentPhotoPreview"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i
                                class="bi bi-x-circle me-1"></i> Cancel</button>
                        <button type="button" class="btn btn-danger px-4" id="saveStudentEdit"><i
                                class="bi bi-save me-1"></i> Save Changes</button>
                    </div>
                </div>
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <script>
        $(document).ready(function() {
            // ==================== VIEW STUDENT ====================
            $(document).on('click', '.view-student-btn', function() {
                const studentId = $(this).data('id');

                $('#studentDetailsModal').modal('show');
                $('#modalLoadingSpinner').removeClass('d-none');
                $('#studentDetailsContent').addClass('d-none');

                $.ajax({
                    url: `{{ url('/students/view') }}/${studentId}`,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        const student = response.student || response;
                        updateStudentDetailsModal(student);
                        $('#modalLoadingSpinner').addClass('d-none');
                        $('#studentDetailsContent').removeClass('d-none');
                    },
                    error: function(xhr) {
                        $('#modalLoadingSpinner').addClass('d-none');
                        let errorMsg = 'Failed to load student data';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: errorMsg
                        });
                    }
                });
            });

            // ==================== EDIT STUDENT ====================
            $(document).on('click', '.edit-student-btn', function() {
                const studentId = $(this).data('id');

                $('#editStudentModal').modal('show');
                $('#editModalLoadingSpinner').removeClass('d-none');
                $('#editStudentForm').addClass('d-none');

                $.ajax({
                    url: `{{ url('/students/Information/') }}/${studentId}`,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        const student = response.student || response;
                        populateEditForm(student);
                        $('#editModalLoadingSpinner').addClass('d-none');
                        $('#editStudentForm').removeClass('d-none');
                    },
                    error: function(xhr) {
                        $('#editModalLoadingSpinner').addClass('d-none');
                        let errorMsg = 'Could not load student data';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: errorMsg
                        });
                        $('#editStudentModal').modal('hide');
                    }
                });
            });

            // ==================== DELETE STUDENT ====================
            $(document).on('click', '.delete-student-btn', function() {
                const studentId = $(this).data('id');
                const row = $(this).closest('tr');

                Swal.fire({
                    title: 'Are you sure?',
                    text: "This will permanently delete the student!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#2C29CA',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `{{ url('/students/delete') }}/${studentId}`,
                            type: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire('Deleted!', response.message, 'success')
                                        .then(() => {
                                            location.reload();
                                        });
                                } else {
                                    Swal.fire('Error!', response.message, 'error');
                                }
                            },
                            error: function() {
                                Swal.fire('Error!', 'Something went wrong', 'error');
                            }
                        });
                    }
                });
            });

            // ==================== SAVE EDIT ====================
            $('#saveStudentEdit').on('click', function() {
                const firstname = $('#editFirstname').val().trim();
                const lastname = $('#editLastname').val().trim();
                const gender = $('#editGender').val();

                if (!firstname || !lastname || !gender) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Missing Fields',
                        text: 'First name, last name and gender are required.',
                        confirmButtonColor: '#2C29CA'
                    });
                    return;
                }

                Swal.fire({
                    title: 'Save Changes?',
                    html: `Update record for <strong>${firstname} ${lastname}</strong>?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#2C29CA',
                    cancelButtonColor: '#2C29CA',
                    confirmButtonText: '<i class="bi bi-save"></i> Yes, Save',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const studentId = $('#editStudentId').val();
                        const formData = new FormData();
                        formData.append('_method', 'POST');
                        formData.append('firstname', firstname);
                        formData.append('student_', firstname);
                        formData.append('lastname', lastname);
                        formData.append('gender', gender);
                        formData.append('student_id', studentId); // 👈 add this
                        formData.append('date_of_birth', $('#editDateOfBirth').val());
                        formData.append('place_of_birth', $('#editPlaceOfBirth').val());
                        formData.append('nationality', $('#editNationality').val());
                        formData.append('birth_certificate_entry_number', $('#editBirthCertificate')
                            .val());
                        formData.append('registration_number', $('#editRegNumber').val());
                        formData.append('admission_number', $('#editAdmissionNumber').val());
                        formData.append('admission_year', $('#editAdmissionYear').val());
                        formData.append('date_of_admission', $('#editDateOfAdmission').val());
                        formData.append('senior', $('#editSenior').val());
                        formData.append('stream', $('#editStream').val());
                        formData.append('ple_score', $('#editPleScore').val());
                        formData.append('uce_score', $('#editUceScore').val());
                        formData.append('previous_school', $('#editPreviousSchool').val());
                        formData.append('primary_school_name', $('#editPrimarySchool').val());
                        formData.append('primary_contact', $('#editPrimaryContact').val());
                        formData.append('other_contact', $('#editOtherContact').val());
                        formData.append('home_address', $('#editHomeAddress').val());
                        formData.append('guardian_names', $('#editGuardianNames').val());
                        formData.append('relation', $('#editRelation').val());
                        formData.append('guardian_phone', $('#editGuardianPhone').val());
                        formData.append('guardian_email', $('#editGuardianEmail').val());
                        formData.append('medical_history', $('#editMedicalHistory').val());
                        formData.append('comments', $('#editComments').val());

                        const photoFile = $('#editStudentPhoto')[0].files[0];
                        if (photoFile) {
                            formData.append('student_photo', photoFile);
                        }

                        $('#saveStudentEdit').html(
                            '<span class="spinner-border spinner-border-sm me-1"></span> Saving...'
                        ).prop('disabled', true);

                        $.ajax({
                            url: `{{ url('/students/update') }}/${studentId}`,
                            type: 'POST',
                            data: formData,
                            processData: false,
                            contentType: false,
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                $('#saveStudentEdit').html(
                                        '<i class="bi bi-save me-1"></i> Save Changes')
                                    .prop('disabled', false);
                                $('#editStudentModal').modal('hide');
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Updated!',
                                    text: response.message ||
                                        'Student updated successfully.',
                                    confirmButtonColor: '#2C29CA',
                                    timer: 2500,
                                    timerProgressBar: true
                                }).then(() => {
                                    location.reload();
                                });
                            },
                            error: function(xhr) {
                                $('#saveStudentEdit').html(
                                        '<i class="bi bi-save me-1"></i> Save Changes')
                                    .prop('disabled', false);
                                let errorMsg = 'Failed to update student';
                                if (xhr.responseJSON && xhr.responseJSON.message) {
                                    errorMsg = xhr.responseJSON.message;
                                }
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: errorMsg
                                });
                            }
                            //                             error: function(data) {
                            // $('body').html(data.responseText);
                            // }
                        });
                    }
                });
            });
        });

        // ==================== HELPER FUNCTIONS ====================
        function updateStudentDetailsModal(student) {
            // Photo
            if (student.photo_url) {
                $('#modalStudentPhoto').attr('src', student.photo_url).show();
                $('.photo-placeholder').addClass('d-none');
            } else {
                $('#modalStudentPhoto').hide();
                $('.photo-placeholder').removeClass('d-none');
            }

            // Basic Info
            $('#modalStudentName').text(`${student.firstname || ''} ${student.lastname || ''}`);
            $('#modalFullName').text(`${student.firstname || ''} ${student.lastname || ''}`);
            $('#modalStudentId').text(`ID: ${student.registration_number || 'N/A'}`);
            $('#modalStudentCategory').text(`Category: ${student.senior || 'N/A'}`);
            $('#modalStudentGender').text(`Gender: ${student.gender || 'N/A'}`);
            $('#modalStudentStream').text(`Stream: ${student.stream || 'N/A'}`);

            // Personal
            $('#modalRegNumber').text(student.registration_number || 'N/A');
            $('#modalAdmissionNumber').text(student.admission_number || 'N/A');
            $('#modalDateOfBirth').text(formatDate(student.date_of_birth) || 'N/A');
            $('#modalPlaceOfBirth').text(student.place_of_birth || 'N/A');
            $('#modalNationality').text(student.nationality || 'N/A');
            $('#modalBirthCertificate').text(student.birth_certificate_entry_number || 'N/A');

            // Academic
            $('#modalDateOfAdmission').text(formatDate(student.date_of_admission) || 'N/A');
            $('#modalAdmissionYear').text(student.admission_year || 'N/A');
            $('#modalSenior').text(student.senior || 'N/A');
            $('#modalStream').text(student.stream || 'N/A');
            $('#modalPleScore').text(student.ple_score || 'N/A');
            $('#modalUceScore').text(student.uce_score || 'N/A');
            $('#modalPreviousSchool').text(student.previous_school || 'N/A');
            $('#modalPrimarySchool').text(student.primary_school_name || 'N/A');

            // Contact
            $('#modalPrimaryContact').text(student.primary_contact || 'N/A');
            $('#modalOtherContact').text(student.other_contact || 'N/A');
            $('#modalHomeAddress').text(student.home_address || 'N/A');

            // Guardian
            $('#modalGuardianNames').text(student.guardian_names || 'N/A');
            $('#modalRelation').text(student.relation || 'N/A');
            $('#modalGuardianPhone').text(student.guardian_phone || 'N/A');
            $('#modalGuardianEmail').text(student.guardian_email || 'N/A');

            // Additional
            $('#modalMedicalHistory').text(student.medical_history || 'N/A');
            $('#modalComments').text(student.comments || 'N/A');
        }

        function populateEditForm(student) {
            $('#editStudentId').val(student.id);
            $('#editFirstname').val(student.firstname || '');
            $('#editLastname').val(student.lastname || '');
            $('#editGender').val(student.gender || '');
            $('#editDateOfBirth').val(student.date_of_birth ? student.date_of_birth.split('T')[0] : '');
            $('#editPlaceOfBirth').val(student.place_of_birth || '');
            $('#editNationality').val(student.nationality || '');
            $('#editBirthCertificate').val(student.birth_certificate_entry_number || '');
            $('#editRegNumber').val(student.registration_number || '');
            $('#editAdmissionNumber').val(student.admission_number || '');
            $('#editAdmissionYear').val(student.admission_year || '');
            $('#editDateOfAdmission').val(student.date_of_admission ? student.date_of_admission.split('T')[0] : '');
            $('#editSenior').val(student.senior || '');
            $('#editStream').val(student.stream || '');
            $('#editPleScore').val(student.ple_score || '');
            $('#editUceScore').val(student.uce_score || '');
            $('#editPreviousSchool').val(student.previous_school || '');
            $('#editPrimarySchool').val(student.primary_school_name || '');
            $('#editPrimaryContact').val(student.primary_contact || '');
            $('#editOtherContact').val(student.other_contact || '');
            $('#editHomeAddress').val(student.home_address || '');
            $('#editGuardianNames').val(student.guardian_names || '');
            $('#editRelation').val(student.relation || '');
            $('#editGuardianPhone').val(student.guardian_phone || '');
            $('#editGuardianEmail').val(student.guardian_email || '');
            $('#editMedicalHistory').val(student.medical_history || '');
            $('#editComments').val(student.comments || '');

            if (student.photo_url) {
                $('#currentPhotoPreview').html(`
                    <p class="text-muted small mb-1">Current photo:</p>
                    <img src="${student.photo_url}" class="rounded-circle border" style="width:60px;height:60px;object-fit:cover;">
                    <p class="text-muted small mt-1">Upload a new file to replace it.</p>
                `);
            } else {
                $('#currentPhotoPreview').html('<p class="text-muted small">No photo uploaded yet.</p>');
            }

            // Reset file input
            $('#editStudentPhoto').val('');
            const preview = document.getElementById('editStudentPhotoPreview');
            if (preview) {
                preview.innerHTML = `
                    <svg class="upload-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
                        <circle cx="12" cy="13" r="4"/>
                    </svg>
                    <span class="upload-text">Click to upload photo</span>
                    <span class="upload-hint">JPG, PNG, or GIF (Max 5MB)</span>
                `;
                preview.classList.remove('success');
            }
        }

        function formatDate(dateString) {
            if (!dateString) return null;
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
        }

        // ==================== FILE UPLOAD HANDLER ====================
        class FileUploadHandler {
            constructor(inputId, previewId, errorId, options = {}) {
                this.input = document.getElementById(inputId);
                this.preview = document.getElementById(previewId);
                this.errorEl = document.getElementById(errorId);
                this.maxSize = options.maxSize || 5 * 1024 * 1024;
                this.acceptedTypes = options.acceptedTypes || ['image/jpg', 'image/jpeg', 'image/png', 'image/gif'];
                this.init();
            }

            init() {
                if (!this.input) return;
                this.input.addEventListener('change', (e) => this.handleFileSelect(e));
                if (this.preview) {
                    this.preview.addEventListener('dragover', (e) => e.preventDefault());
                    this.preview.addEventListener('drop', (e) => {
                        e.preventDefault();
                        const file = e.dataTransfer.files[0];
                        if (file) this.processFile(file);
                    });
                    this.preview.addEventListener('click', (e) => {
                        if (e.target.classList.contains('remove-image-btn') || e.target.closest(
                                '.remove-image-btn')) return;
                        this.input.click();
                    });
                }
            }

            handleFileSelect(e) {
                const file = e.target.files[0];
                if (file) this.processFile(file);
            }

            processFile(file) {
                if (!this.acceptedTypes.includes(file.type)) {
                    this.showError(`Please upload a valid image file (${this.acceptedTypes.join(', ')})`);
                    return;
                }
                if (file.size > this.maxSize) {
                    this.showError(`File size must be less than ${this.formatBytes(this.maxSize)}`);
                    return;
                }
                const reader = new FileReader();
                reader.onload = (e) => this.createPreview(e.target.result, file);
                reader.onerror = () => this.showError('Failed to load image');
                reader.readAsDataURL(file);
            }

            createPreview(imageUrl, file) {
                if (!this.preview) return;
                this.preview.innerHTML = '';
                const img = document.createElement('img');
                img.src = imageUrl;
                img.className = 'preview-image';
                const removeBtn = document.createElement('button');
                removeBtn.innerHTML = '&times;';
                removeBtn.className = 'remove-image-btn';
                removeBtn.type = 'button';
                removeBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    this.removePreview();
                });
                this.preview.appendChild(img);
                this.preview.appendChild(removeBtn);
                this.preview.classList.add('success');
            }

            removePreview() {
                if (!this.preview) return;
                this.preview.innerHTML = `
                    <svg class="upload-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
                        <circle cx="12" cy="13" r="4"/>
                    </svg>
                    <span class="upload-text">Click to upload photo</span>
                    <span class="upload-hint">JPG, PNG, or GIF (Max 5MB)</span>
                `;
                this.input.value = '';
                this.preview.classList.remove('success');
            }

            showError(message) {
                if (this.errorEl) {
                    this.errorEl.textContent = message;
                    setTimeout(() => {
                        this.errorEl.textContent = '';
                    }, 3000);
                }
            }

            formatBytes(bytes) {
                if (bytes === 0) return '0 Bytes';
                const k = 1024;
                const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
            }
        }

        // Initialize file upload handler
        document.addEventListener('DOMContentLoaded', function() {
            new FileUploadHandler('editStudentPhoto', 'editStudentPhotoPreview', 'editStudentPhotoError', {
                maxSize: 5 * 1024 * 1024,
                acceptedTypes: ['image/jpg', 'image/jpeg', 'image/png', 'image/gif']
            });
        });
    </script>
@endsection
