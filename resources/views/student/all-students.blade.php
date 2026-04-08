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
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
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
    
    /* Loading Skeleton */
    .skeleton-loader {
        background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
        background-size: 200% 100%;
        animation: loading 1.5s infinite;
    }
    
    @keyframes loading {
        0% { background-position: 200% 0; }
        100% { background-position: -200% 0; }
    }
</style>

        <style>
            /* Edit Modal Styles */
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

            /* Custom modal width */
            .modal-xl {
                --bs-modal-width: 1140px;
            }

            /* For extra large screens */
            @media (min-width: 1400px) {
                .modal-xl {
                    --bs-modal-width: 1320px;
                }
            }

            /* For ultra-wide screens */
            @media (min-width: 1600px) {
                .modal-xl {
                    --bs-modal-width: 1500px;
                }
            }

            /* To make it full width with margins */
            .modal-dialog.modal-xl {
                max-width: 95%;
                width: 95%;
            }

            /* For specific modal if you want full width */
            .modal-dialog.modal-full-width {
                max-width: 98%;
                width: 98%;
                margin: 1rem auto;
            }

            /* SweetAlert above Bootstrap modal */
            .swal2-container {
                z-index: 99999 !important;
            }
        </style>


        <style>
            .table th,
            .table td {
                text-align: center;
                vertical-align: middle;
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

            .modal-header {
                border-bottom: none;
            }

            .modal-footer {
                border-top: 1px solid #dee2e6;
            }

            /* Custom scrollbar */
            .student-details-wrapper::-webkit-scrollbar {
                width: 8px;
            }

            .student-details-wrapper::-webkit-scrollbar-track {
                background: #f1f1f1;
                border-radius: 10px;
            }

            .student-details-wrapper::-webkit-scrollbar-thumb {
                background: #888;
                border-radius: 10px;
            }

            .student-details-wrapper::-webkit-scrollbar-thumb:hover {
                background: #555;
            }
        </style>

        <style>
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

            /* Upload Icon */
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

            /* Text Styles */
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

            /* Preview Image Styles - FIXED */
            .preview-image {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                object-fit: contain;
                /* Changed from 'cover' to 'contain' */
                object-position: center;
                background-color: #f8fafc;
            }

            /* Alternative approach if you want to ensure the image fits perfectly */
            .file-upload-preview.has-image {
                padding: 0;
            }

            .file-upload-preview.has-image .preview-image {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                object-fit: contain;
                object-position: center;
            }

            /* For when image is loaded, adjust the container */
            .file-upload-preview:has(.preview-image) {
                padding: 0;
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
                /* always visible, not just on hover */
                align-items: center;
                justify-content: center;
                z-index: 10;
                transition: transform 0.2s ease;
                opacity: 1 !important;
                /* remove the hover-only opacity */
                transform: scale(1) !important;
                box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3);
            }

            .remove-image-btn:hover {
                transform: scale(1.15) !important;
                background: #dc2626 !important;
            }

            /* Error Message */
            .file-upload-error {
                margin-top: 8px;
                font-size: 0.75rem;
                color: #ef4444;
                min-height: 20px;
            }

            /* Loading State */
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

            /* Success State */
            .file-upload-preview.success {
                border-color: #10b981;
                background: #f0fdf4;
            }

            /* Dark Mode Support */
            @media (prefers-color-scheme: dark) {
                .file-upload-preview {
                    background: #1e293b;
                    border-color: #334155;
                }

                .upload-text {
                    color: #f1f5f9;
                }

                .upload-hint {
                    color: #94a3b8;
                }

                .upload-icon {
                    color: #475569;
                }

                .preview-image {
                    background-color: #1e293b;
                }
            }

            /* Responsive Design */
            @media (max-width: 640px) {
                .file-upload-container {
                    max-width: 100%;
                }

                .file-upload-preview {
                    min-height: 180px;
                }

                .upload-icon {
                    width: 40px;
                    height: 40px;
                }

                .upload-text {
                    font-size: 0.875rem;
                }
            }
        </style>

        {{-- Code for View and Edit Code to be showing / old code with modals and so on !!! --}}

{{-- <div class="row">
   <div class="col-lg-12">
      <!-- Results -->
      <div class="card mt-4" id="resultsCard">
         <div class="card-header bg-primary text-white">
            <h5 class="mb-0">All Students</h5>
         </div>
         <div class="card-body bg-white" id="searchResults">
            @if ($groupedStudents->isEmpty())
            <p>No students found.</p>
            @else
            @foreach ($groupedStudents as $senior => $streams)
            <div class="senior-group">
               <h4 class="text-primary">Class : <span
                  class="text-dark fw-bold">{{ Helper::item_md_name($senior) }}</span></h4>
               @foreach ($streams as $stream => $students)
               <div class="stream-group">
                  <!-- Stream Group Title -->
                  <h5 class="text-secondary">Stream: {{ Helper::item_md_name($stream) }}</h5>
                  <!-- Stream Table -->
                  <table class="table table-striped">
                     <thead>
                        <tr>
                           <th>#</th>
                           <th>Photo</th>
    
                           <th>Firstname</th>
                           <th>Lastname</th>
                           <th>Gender</th>
                           <th colspan="3">Action</th>
                        </tr>
                     </thead>
                     <tbody>
                        @foreach ($students as $key => $student)
                        <tr>
                           <td>{{ $key + 1 }}</td>
                           <td class="text-center">
                              @php
                                $imagePath = null;

                                if ($student->student_photo) {
                                    $possibleExtensions = ['jpg', 'jpeg', 'png', 'gif'];

                                    foreach ($possibleExtensions as $ext) {
                                        $path = 'uploads/studentPhotos/' . $student->student_photo . '.' . $ext;

                                        if (file_exists(public_path($path))) {
                                            $imagePath = $path;
                                            break;
                                        }
                                    }
                                }
                            @endphp

                            @if($imagePath)
                                <img src="{{ asset($imagePath) }}"
                                    class="rounded-circle"
                                    style="width: 50px; height: 50px; object-fit: cover;">
                            @else
                                <div class="bg-secondary rounded-circle d-inline-flex align-items-center justify-content-center"
                                    style="width: 50px; height: 50px;">
                                    <i class="bi bi-person text-white" style="font-size: 24px;"></i>
                                </div>
                            @endif
                           </td>
                           <td>{{ $student->firstname }}</td>
                           <td>{{ $student->lastname }}</td>
                           <td>{{ $student->gender }}</td>
                           <td>
                              <a href="{{ url('/students/view', $student->id) }}"
                                 class="btn btn-sm btn-info">
                              <i class="bi bi-eye"></i> View
                              </a>
                           </td>
                           <td>
                              <a href="{{ url('students.edit', $student->id) }}"
                                 class="btn btn-sm btn-primary">
                              <i class="bi bi-pencil-square"></i> Edit
                              </a>
                           </td>
                           <td>
                              <button type="button" class="btn btn-sm btn-danger delete-student" data-id="{{ $student->id }}">
                              <i class="bi bi-trash"></i> Delete
                              </button>
                           </td>
                        </tr>
                        @endforeach
                     </tbody>
                  </table>
                  <!-- Student Details Modal -->
                  <div class="modal fade" id="studentDetailsModal" tabindex="-1"
                     aria-labelledby="studentDetailsModalLabel" aria-hidden="true">
                     <div
                        class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                        <div class="modal-content">
                           <div class="modal-header"
                              style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                              <h5 class="modal-title text-white"
                                 id="studentDetailsModalLabel">
                                 <i class="bi bi-person-badge me-2"></i> Student Details
                              </h5>
                              <button type="button" class="close text-white"
                                 data-dismiss="modal" aria-label="Close">
                              <span aria-hidden="true">&times;</span>
                              </button>
                           </div>
                           <div class="modal-body p-0">
                              <div class="student-details-wrapper">
                                 <!-- Loading Spinner -->
                                 <div class="text-center p-5 d-none"
                                    id="modalLoadingSpinner">
                                    <div class="spinner-border text-primary" role="status">
                                       <span class="visually-hidden">...</span>
                                    </div>
                                 </div>
                                 <!-- Student Details Content -->
                                 <div id="studentDetailsContent" class="d-none">
                                    <!-- Header with Photo -->
                                    <div class="student-header p-4"
                                       style="background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);">
                                       <div class="row align-items-center">
                                          <div class="col-md-3 text-center">
                                             <div
                                                class="student-photo-wrapper position-relative">
                                                <div class="student-photo-container"
                                                   style="position: relative; width: 150px; height: 150px; margin: 0 auto;">
                                                   <img id="modalStudentPhoto"
                                                      src=""
                                                      alt="Student Photo"
                                                      class="img-fluid rounded-circle border border-3 border-white shadow-lg"
                                                      style="width: 150px; height: 150px; object-fit: cover; display: block;">
                                                   <div class="photo-placeholder bg-light rounded-circle"
                                                      style="width: 150px; height: 150px; display: none; align-items: center; justify-content: center;">
                                                      <i class="bi bi-person-circle"
                                                         style="font-size: 80px; color: #6c757d;"></i>
                                                   </div>
                                                </div>
                                             </div>
                                          </div>
                                          <div class="col-md-9">
                                             <h2 id="modalStudentName"
                                                class="mb-2 fw-bold text-dark"></h2>
                                             <div class="student-badges">
                                                <span
                                                   class="badge text-white bg-primary me-2"
                                                   id="modalStudentId"></span>
                                                <span
                                                   class="badge text-white bg-info me-2"
                                                   id="modalStudentCategory"></span>
                                                <span
                                                   class="badge text-white bg-success me-2"
                                                   id="modalStudentGender"></span>
                                                <span
                                                   class="badge text-white bg-secondary"
                                                   id="modalStudentStream"></span>
                                             </div>
                                          </div>
                                       </div>
                                    </div>
                                    <!-- Details Sections -->
                                    <div class="p-4">
                                       <!-- Personal Information -->
                                       <div class="details-section mb-4">
                                          <h5
                                             class="section-title border-bottom pb-2 mb-3">
                                             <i
                                                class="bi bi-person-vcard me-2 text-primary"></i>
                                             Personal Information
                                          </h5>
                                          <div class="row g-3">
                                             <div class="col-md-6 mb-2">
                                                <div class="info-item">
                                                   <label
                                                      class="text-muted small fw-bold">Full
                                                   Name</label>
                                                   <p class="mb-0 fw-medium"
                                                      id="modalFullName"></p>
                                                </div>
                                             </div>
                                             <div class="col-md-6 mb-2">
                                                <div class="info-item">
                                                   <label
                                                      class="text-muted small fw-bold">Date
                                                   of Birth</label>
                                                   <p class="mb-0 fw-medium"
                                                      id="modalDateOfBirth"></p>
                                                </div>
                                             </div>
                                             <div class="col-md-6 mb-2">
                                                <div class="info-item">
                                                   <label
                                                      class="text-muted small fw-bold">Place
                                                   of Birth</label>
                                                   <p class="mb-0 fw-medium"
                                                      id="modalPlaceOfBirth"></p>
                                                </div>
                                             </div>
                                             <div class="col-md-6 mb-2">
                                                <div class="info-item">
                                                   <label
                                                      class="text-muted small fw-bold">Nationality</label>
                                                   <p class="mb-0 fw-medium"
                                                      id="modalNationality"></p>
                                                </div>
                                             </div>
                                             <div class="col-md-12">
                                                <div class="info-item">
                                                   <label
                                                      class="text-muted small fw-bold">Birth
                                                   Certificate Number</label>
                                                   <p class="mb-0 fw-medium"
                                                      id="modalBirthCertificate"></p>
                                                </div>
                                             </div>
                                          </div>
                                       </div>
                                       <!-- Academic Information -->
                                       <div class="details-section mb-4">
                                          <h5
                                             class="section-title border-bottom pb-2 mb-3">
                                             <i
                                                class="bi bi-book me-2 text-primary"></i>
                                             Academic Information
                                          </h5>
                                          <div class="row g-3">
                                             <div class="col-md-6 mb-2">
                                                <div class="info-item">
                                                   <label
                                                      class="text-muted small fw-bold">Registration
                                                   Number</label>
                                                   <p class="mb-0 fw-medium"
                                                      id="modalRegNumber"></p>
                                                </div>
                                             </div>
                                             <div class="col-md-6 mb-2">
                                                <div class="info-item">
                                                   <label
                                                      class="text-muted small fw-bold">Admission
                                                   Number</label>
                                                   <p class="mb-0 fw-medium"
                                                      id="modalAdmissionNumber"></p>
                                                </div>
                                             </div>
                                             <div class="col-md-6 mb-2">
                                                <div class="info-item">
                                                   <label
                                                      class="text-muted small fw-bold">Date
                                                   of Admission</label>
                                                   <p class="mb-0 fw-medium"
                                                      id="modalDateOfAdmission"></p>
                                                </div>
                                             </div>
                                             <div class="col-md-6 mb-2">
                                                <div class="info-item">
                                                   <label
                                                      class="text-muted small fw-bold">Admission
                                                   Year</label>
                                                   <p class="mb-0 fw-medium"
                                                      id="modalAdmissionYear"></p>
                                                </div>
                                             </div>
                                             <div class="col-md-6 mb-2">
                                                <div class="info-item">
                                                   <label
                                                      class="text-muted small fw-bold">Senior/Class</label>
                                                   <p class="mb-0 fw-medium"
                                                      id="modalSenior"></p>
                                                </div>
                                             </div>
                                             <div class="col-md-6 mb-2">
                                                <div class="info-item">
                                                   <label
                                                      class="text-muted small fw-bold">Stream</label>
                                                   <p class="mb-0 fw-medium"
                                                      id="modalStream"></p>
                                                </div>
                                             </div>
                                             <div class="col-md-6 mb-2">
                                                <div class="info-item">
                                                   <label
                                                      class="text-muted small fw-bold">PLE
                                                   Score</label>
                                                   <p class="mb-0 fw-medium"
                                                      id="modalPleScore"></p>
                                                </div>
                                             </div>
                                             <div class="col-md-6 mb-2">
                                                <div class="info-item">
                                                   <label
                                                      class="text-muted small fw-bold">UCE
                                                   Score</label>
                                                   <p class="mb-0 fw-medium"
                                                      id="modalUceScore"></p>
                                                </div>
                                             </div>
                                             <div class="col-md-12 mb-2">
                                                <div class="info-item">
                                                   <label
                                                      class="text-muted small fw-bold">Previous
                                                   School</label>
                                                   <p class="mb-0 fw-medium"
                                                      id="modalPreviousSchool"></p>
                                                </div>
                                             </div>
                                             <div class="col-md-12 mb-2">
                                                <div class="info-item">
                                                   <label
                                                      class="text-muted small fw-bold">Primary
                                                   School</label>
                                                   <p class="mb-0 fw-medium"
                                                      id="modalPrimarySchool"></p>
                                                </div>
                                             </div>
                                          </div>
                                       </div>
                                       <!-- Contact Information -->
                                       <div class="details-section mb-4">
                                          <h5
                                             class="section-title border-bottom pb-2 mb-3">
                                             <i
                                                class="bi bi-telephone me-2 text-primary"></i>
                                             Contact Information
                                          </h5>
                                          <div class="row g-3">
                                             <div class="col-md-6 mb-2">
                                                <div class="info-item">
                                                   <label
                                                      class="text-muted small fw-bold">Primary
                                                   Contact</label>
                                                   <p class="mb-0 fw-medium"
                                                      id="modalPrimaryContact"></p>
                                                </div>
                                             </div>
                                             <div class="col-md-6 mb-2">
                                                <div class="info-item">
                                                   <label
                                                      class="text-muted small fw-bold">Other
                                                   Contact</label>
                                                   <p class="mb-0 fw-medium"
                                                      id="modalOtherContact"></p>
                                                </div>
                                             </div>
                                             <div class="col-md-12">
                                                <div class="info-item">
                                                   <label
                                                      class="text-muted small fw-bold">Home
                                                   Address</label>
                                                   <p class="mb-0 fw-medium"
                                                      id="modalHomeAddress"></p>
                                                </div>
                                             </div>
                                          </div>
                                       </div>
                                       <!-- Guardian Information -->
                                       <div class="details-section mb-4">
                                          <h5
                                             class="section-title border-bottom pb-2 mb-3">
                                             <i
                                                class="bi bi-people me-2 text-primary"></i>
                                             Guardian Information
                                          </h5>
                                          <div class="row g-3">
                                             <div class="col-md-6 mb-2">
                                                <div class="info-item">
                                                   <label
                                                      class="text-muted small fw-bold">Guardian
                                                   Names</label>
                                                   <p class="mb-0 fw-medium"
                                                      id="modalGuardianNames"></p>
                                                </div>
                                             </div>
                                             <div class="col-md-6 mb-2">
                                                <div class="info-item">
                                                   <label
                                                      class="text-muted small fw-bold">Relation</label>
                                                   <p class="mb-0 fw-medium"
                                                      id="modalRelation"></p>
                                                </div>
                                             </div>
                                             <div class="col-md-6 mb-2">
                                                <div class="info-item">
                                                   <label
                                                      class="text-muted small fw-bold">Guardian
                                                   Phone</label>
                                                   <p class="mb-0 fw-medium"
                                                      id="modalGuardianPhone"></p>
                                                </div>
                                             </div>
                                             <div class="col-md-6 mb-2">
                                                <div class="info-item">
                                                   <label
                                                      class="text-muted small fw-bold">Guardian
                                                   Email</label>
                                                   <p class="mb-0 fw-medium"
                                                      id="modalGuardianEmail"></p>
                                                </div>
                                             </div>
                                          </div>
                                       </div>
                                       <!-- Additional Information -->
                                       <div class="details-section">
                                          <h5
                                             class="section-title border-bottom pb-2 mb-3">
                                             <i
                                                class="bi bi-info-circle me-2 text-primary"></i>
                                             Additional Information
                                          </h5>
                                          <div class="row g-3">
                                             <div class="col-md-12 mb-2">
                                                <div class="info-item">
                                                   <label
                                                      class="text-muted small fw-bold">Medical
                                                   History</label>
                                                   <p class="mb-0 fw-medium"
                                                      id="modalMedicalHistory"></p>
                                                </div>
                                             </div>
                                             <div class="col-md-12">
                                                <div class="info-item">
                                                   <label
                                                      class="text-muted small fw-bold">Comments</label>
                                                   <p class="mb-0 fw-medium"
                                                      id="modalComments"></p>
                                                </div>
                                             </div>
                                          </div>
                                       </div>
                                    </div>
                                 </div>
                              </div>
                           </div>
                           <div class="modal-footer">
                              <button type="button" class="btn btn-secondary"
                                 data-dismiss="modal">
                              <i class="bi bi-x-circle mr-1"></i> Close
                              </button>
                           </div>
                        </div>
                     </div>
                  </div>
                  <!-- Edit Student Modal -->
                  <div class="modal fade" id="editStudentModal" tabindex="-1"
                     aria-labelledby="editStudentModalLabel" aria-hidden="true">
                     <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable"
                        style="max-width: 90%;">
                        <div class="modal-content">
                           <div class="modal-header"
                              style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                              <h5 class="modal-title text-white" id="editStudentModalLabel">
                                 <i class="bi bi-pencil-square me-2"></i> Edit Student
                              </h5>
                              <button type="button" class="close text-white"
                                 data-dismiss="modal" aria-label="Close">
                              <span aria-hidden="true">&times;</span>
                              </button>
                           </div>
                           <div class="modal-body p-0">
                              <!-- Loading Spinner -->
                              <div class="text-center p-5" id="editModalLoadingSpinner">
                                 <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">...</span>
                                 </div>
                              </div>
                              <!-- Edit Form -->
                              <div id="editStudentForm" class="d-none p-4">
                                 <input type="hidden" id="editStudentId">
                                 <!-- Section: Personal Information -->
                                 <div class="edit-section mb-4">
                                    <h5 class="edit-section-title border-bottom pb-2 mb-3">
                                       <i class="bi bi-person-vcard me-2 text-danger"></i>
                                       Personal Information
                                    </h5>
                                    <div class="row g-3">
                                       <div class="col-md-6">
                                          <label
                                             class="form-label fw-bold text-muted small">First
                                          Name <span
                                             class="text-danger">*</span></label>
                                          <input type="text" class="form-control"
                                             id="editFirstname"
                                             placeholder="First name">
                                       </div>
                                       <div class="col-md-6">
                                          <label
                                             class="form-label fw-bold text-muted small">Last
                                          Name <span
                                             class="text-danger">*</span></label>
                                          <input type="text" class="form-control"
                                             id="editLastname" placeholder="Last name">
                                       </div>
                                       <div class="col-md-4">
                                          <label
                                             class="form-label fw-bold text-muted small">Gender
                                          <span class="text-danger">*</span></label>
                                          <select class="form-select" id="editGender">
                                             <option value="">Select Gender
                                             </option>
                                             <option value="Male">Male</option>
                                             <option value="Female">Female</option>
                                             <option value="Other">Other</option>
                                          </select>
                                       </div>
                                       <div class="col-md-4">
                                          <label
                                             class="form-label fw-bold text-muted small">Date
                                          of Birth</label>
                                          <input type="date" class="form-control"
                                             id="editDateOfBirth">
                                       </div>
                                       <div class="col-md-4">
                                          <label
                                             class="form-label fw-bold text-muted small">Place
                                          of Birth</label>
                                          <input type="text" class="form-control"
                                             id="editPlaceOfBirth"
                                             placeholder="Place of birth">
                                       </div>
                                       <div class="col-md-6">
                                          <label
                                             class="form-label fw-bold text-muted small">Nationality</label>
                                          <input type="text" class="form-control"
                                             id="editNationality"
                                             placeholder="Nationality">
                                       </div>
                                       <div class="col-md-6">
                                          <label
                                             class="form-label fw-bold text-muted small">Birth
                                          Certificate Number</label>
                                          <input type="text" class="form-control"
                                             id="editBirthCertificate"
                                             placeholder="Birth certificate number">
                                       </div>
                                    </div>
                                 </div>
                                 <!-- Section: Academic Information -->
                                 <div class="edit-section mb-4">
                                    <h5 class="edit-section-title border-bottom pb-2 mb-3">
                                       <i class="bi bi-book me-2 text-danger"></i>
                                       Academic Information
                                    </h5>
                                    <div class="row g-3">
                                       <div class="col-md-4">
                                          <label
                                             class="form-label fw-bold text-muted small">Registration
                                          Number</label>
                                          <input type="text" class="form-control"
                                             id="editRegNumber"
                                             placeholder="Registration number">
                                       </div>
                                       <div class="col-md-4">
                                          <label
                                             class="form-label fw-bold text-muted small">Admission
                                          Number</label>
                                          <input type="text" class="form-control"
                                             id="editAdmissionNumber"
                                             placeholder="Admission number">
                                       </div>
                                       <div class="col-md-4">
                                          <label
                                             class="form-label fw-bold text-muted small">Admission
                                          Year</label>
                                          <input type="number" class="form-control"
                                             id="editAdmissionYear"
                                             placeholder="e.g. 2024">
                                       </div>
                                       <div class="col-md-4">
                                          <label
                                             class="form-label fw-bold text-muted small">Date
                                          of Admission</label>
                                          <input type="date" class="form-control"
                                             id="editDateOfAdmission">
                                       </div>
                                       <div class="col-md-4">
                                          <label
                                             class="form-label fw-bold text-muted small">Senior/Class</label>
                                          <input type="text" class="form-control"
                                             id="editSenior" placeholder="e.g. S1, S2">
                                       </div>
                                       <div class="col-md-4">
                                          <label
                                             class="form-label fw-bold text-muted small">Stream</label>
                                          <input type="text" class="form-control"
                                             id="editStream" placeholder="Stream">
                                       </div>
                                       <div class="col-md-4">
                                          <label
                                             class="form-label fw-bold text-muted small">PLE
                                          Score</label>
                                          <input type="text" class="form-control"
                                             id="editPleScore" placeholder="PLE score">
                                       </div>
                                       <div class="col-md-4">
                                          <label
                                             class="form-label fw-bold text-muted small">UCE
                                          Score</label>
                                          <input type="text" class="form-control"
                                             id="editUceScore" placeholder="UCE score">
                                       </div>
                                       <div class="col-md-6">
                                          <label
                                             class="form-label fw-bold text-muted small">Previous
                                          School</label>
                                          <input type="text" class="form-control"
                                             id="editPreviousSchool"
                                             placeholder="Previous school">
                                       </div>
                                       <div class="col-md-6">
                                          <label
                                             class="form-label fw-bold text-muted small">Primary
                                          School</label>
                                          <input type="text" class="form-control"
                                             id="editPrimarySchool"
                                             placeholder="Primary school name">
                                       </div>
                                    </div>
                                 </div>
                                 <!-- Section: Contact Information -->
                                 <div class="edit-section mb-4">
                                    <h5 class="edit-section-title border-bottom pb-2 mb-3">
                                       <i class="bi bi-telephone me-2 text-danger"></i>
                                       Contact Information
                                    </h5>
                                    <div class="row g-3">
                                       <div class="col-md-4">
                                          <label
                                             class="form-label fw-bold text-muted small">Primary
                                          Contact</label>
                                          <input type="text" class="form-control"
                                             id="editPrimaryContact"
                                             placeholder="Primary phone">
                                       </div>
                                       <div class="col-md-4">
                                          <label
                                             class="form-label fw-bold text-muted small">Other
                                          Contact</label>
                                          <input type="text" class="form-control"
                                             id="editOtherContact"
                                             placeholder="Other phone">
                                       </div>
                                       <div class="col-md-4">
                                          <label
                                             class="form-label fw-bold text-muted small">Home
                                          Address</label>
                                          <input type="text" class="form-control"
                                             id="editHomeAddress"
                                             placeholder="Home address">
                                       </div>
                                    </div>
                                 </div>
                                 <!-- Section: Guardian Information -->
                                 <div class="edit-section mb-4">
                                    <h5 class="edit-section-title border-bottom pb-2 mb-3">
                                       <i class="bi bi-people me-2 text-danger"></i>
                                       Guardian Information
                                    </h5>
                                    <div class="row g-3">
                                       <div class="col-md-6">
                                          <label
                                             class="form-label fw-bold text-muted small">Guardian
                                          Names</label>
                                          <input type="text" class="form-control"
                                             id="editGuardianNames"
                                             placeholder="Guardian full names">
                                       </div>
                                       <div class="col-md-6">
                                          <label
                                             class="form-label fw-bold text-muted small">Relation</label>
                                          <input type="text" class="form-control"
                                             id="editRelation"
                                             placeholder="e.g. Father, Mother">
                                       </div>
                                       <div class="col-md-6">
                                          <label
                                             class="form-label fw-bold text-muted small">Guardian
                                          Phone</label>
                                          <input type="text" class="form-control"
                                             id="editGuardianPhone"
                                             placeholder="Guardian phone">
                                       </div>
                                       <div class="col-md-6">
                                          <label
                                             class="form-label fw-bold text-muted small">Guardian
                                          Email</label>
                                          <input type="email" class="form-control"
                                             id="editGuardianEmail"
                                             placeholder="Guardian email">
                                       </div>
                                    </div>
                                 </div>
                                 <!-- Section: Additional Information -->
                                 <div class="edit-section">
                                    <h5 class="edit-section-title border-bottom pb-2 mb-3">
                                       <i class="bi bi-info-circle me-2 text-danger"></i>
                                       Additional Information
                                    </h5>
                                    <div class="row g-3">
                                       <div class="col-md-6">
                                          <label
                                             class="form-label fw-bold text-muted small">Medical
                                          History</label>
                                          <textarea class="form-control" id="editMedicalHistory" rows="3" placeholder="Any medical conditions..."></textarea>
                                       </div>
                                       <div class="col-md-6">
                                          <label
                                             class="form-label fw-bold text-muted small">Comments</label>
                                          <textarea class="form-control" id="editComments" rows="3" placeholder="Additional comments..."></textarea>
                                       </div>
                                       <div class="col-md-12">
                                          <label
                                             class="form-label fw-bold text-muted small">Student
                                          Photo</label>
                                          <div class="file-upload-container">
                                             <div class="file-upload-wrapper">
                                                <div class="file-upload-preview" id="editStudentPhotoPreview">
                                                   <svg class="upload-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                      <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
                                                      <circle cx="12" cy="13" r="4"/>
                                                   </svg>
                                                   <span class="upload-text">Click to upload photo</span>
                                                   <span class="upload-hint">JPG, PNG, or GIF (Max 5MB)</span>
                                                </div>
                                                <input type="file" 
                                                   class="form-control file-input" 
                                                   id="editStudentPhoto" 
                                                   name="studentPhoto"
                                                   accept="image/jpg,image/png,image/gif">
                                                <div class="file-upload-error" id="editStudentPhotoError"></div>
                                             </div>
                                          </div>
                                          <div class="mt-2" id="currentPhotoPreview">
                                          </div>
                                       </div>
                                    </div>
                                 </div>
                              </div>
                           </div>
                           <div class="modal-footer">
                              <button type="button" class="btn btn-secondary"
                                 data-dismiss="modal">
                              <i class="bi bi-x-circle me-1"></i> Cancel
                              </button>
                              <button type="button" class="btn btn-danger px-4"
                                 id="saveStudentEdit">
                              <i class="bi bi-save me-1"></i> Save Changes
                              </button>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
               @endforeach
            </div>
            @endforeach
            @endif
         </div>
      </div>
   </div>
</div> --}}



<div class="row">
   <div class="col-lg-12">
      <!-- Results -->
      <div class="card mt-4" id="resultsCard">
         <div class="card-header bg-primary text-white">
            <h5 class="mb-0">All Students</h5>
         </div>
         <div class="card-body bg-white" id="searchResults">
            @if ($groupedStudents->isEmpty())
            <p>No students found.</p>
            @else
            @foreach ($groupedStudents as $senior => $streams)
            <div class="senior-group">
               <h4 class="text-primary">Class : <span
                  class="text-dark fw-bold">{{ Helper::item_md_name($senior) }}</span></h4>
               @foreach ($streams as $stream => $students)
               <!-- Stream Table -->
               <div class="stream-group mb-5">
                  <!-- Stream Group Title -->
                  <div class="d-flex justify-content-between align-items-center mb-3">
                     <h5 class="text-secondary mb-0">
                           <i class="bi bi-diagram-3 me-2"></i>Stream: {{ Helper::item_md_name($stream) }}
                           <span class="badge bg-primary ms-2" id="totalCount-{{ $loop->parent->index }}-{{ $loop->index }}">
                              {{ $students->count() }} students
                           </span>
                     </h5>
                  </div>
                  
                  <!-- Stream Table -->
                  <div class="table-responsive">
                     <table class="table table-hover table-striped" id="table-{{ $loop->parent->index }}-{{ $loop->index }}">
                           <thead class="bg-light">
                              <tr>
                                 <th width="5%">#</th>
                                 <th width="10%">Photo</th>
                                 <th width="15%">Firstname</th>
                                 <th width="15%">Lastname</th>
                                 <th width="10%">Gender</th>
                                 <th width="30%" colspan="3">Action</th>
                              </tr>
                           </thead>
                           <tbody id="tableBody-{{ $loop->parent->index }}-{{ $loop->index }}">
                              @foreach ($students->take(10) as $key => $student)
                              <tr id="student-row-{{ $student->id }}">
                                 <td>{{ $key + 1 }}</td>
                                 <td class="text-center">
                                       @php
                                          $imagePath = null;
                                          if ($student->student_photo) {
                                             $possibleExtensions = ['jpg', 'jpeg', 'png', 'gif'];
                                             foreach ($possibleExtensions as $ext) {
                                                   $path = 'uploads/studentPhotos/' . $student->student_photo . '.' . $ext;
                                                   if (file_exists(public_path($path))) {
                                                      $imagePath = $path;
                                                      break;
                                                   }
                                             }
                                          }
                                       @endphp
                                       @if($imagePath)
                                          <img src="{{ asset($imagePath) }}"
                                             class="rounded-circle border"
                                             style="width: 45px; height: 45px; object-fit: cover;">
                                       @else
                                          <div class="bg-secondary rounded-circle d-inline-flex align-items-center justify-content-center"
                                             style="width: 45px; height: 45px;">
                                             <i class="bi bi-person text-white" style="font-size: 20px;"></i>
                                          </div>
                                       @endif
                                 </td>
                                 <td class="fw-medium">{{ $student->firstname }}</td>
                                 <td>{{ $student->lastname }}</td>
                                 <td>
                                       <span class="badge {{ $student->gender == 'Male' ? 'bg-info' : ($student->gender == 'Female' ? 'bg-success' : 'bg-secondary') }}">
                                          {{ $student->gender }}
                                       </span>
                                 </td>
                                 <td>
                                       <div class="btn-group btn-group-sm" role="group">
                                          <button type="button" class="btn btn-info view-student" data-id="{{ $student->id }}">
                                             <i class="bi bi-eye"></i> View
                                          </button>
                                          <button type="button" class="btn btn-primary edit-student" data-id="{{ $student->id }}">
                                             <i class="bi bi-pencil-square"></i> Edit
                                          </button>
                                          <button type="button" class="btn btn-danger delete-student" data-id="{{ $student->id }}">
                                             <i class="bi bi-trash"></i> Delete
                                          </button>
                                       </div>
                                 </td>
                              </tr>
                              @endforeach
                           </tbody>
                     </table>
                  </div>
                  
                  <!-- Pagination Controls -->
                  @if($students->count() > 10)
                  <div class="d-flex justify-content-between align-items-center mt-3" id="pagination-{{ $loop->parent->index }}-{{ $loop->index }}">
                     <div class="text-muted small">
                           Showing <span id="showingStart-{{ $loop->parent->index }}-{{ $loop->index }}">1</span> 
                           to <span id="showingEnd-{{ $loop->parent->index }}-{{ $loop->index }}">10</span> 
                           of <span id="totalItems-{{ $loop->parent->index }}-{{ $loop->index }}">{{ $students->count() }}</span> students
                     </div>
                     <nav>
                           <ul class="pagination pagination-sm mb-0" id="paginationList-{{ $loop->parent->index }}-{{ $loop->index }}">
                              <!-- Pagination will be generated by JavaScript -->
                           </ul>
                     </nav>
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

    </div>
    </div>
    </div>

    <script>
        class FileUploadHandler {
            constructor(inputId, previewId, errorId, options = {}) {
                this.input = document.getElementById(inputId);
                this.preview = document.getElementById(previewId);
                this.errorEl = document.getElementById(errorId);
                this.maxSize = options.maxSize || 5 * 1024 * 1024; // 5MB default
                this.acceptedTypes = options.acceptedTypes || ['image/jpg', 'image/jpeg', 'image/png', 'image/gif'];

                this.init();
            }

            init() {
                if (!this.input) return;

                this.input.addEventListener('change', (e) => this.handleFileSelect(e));

                this.preview.addEventListener('dragover', (e) => this.handleDragOver(e));
                this.preview.addEventListener('dragleave', (e) => this.handleDragLeave(e));
                this.preview.addEventListener('drop', (e) => this.handleDrop(e));

                // Fix: check the click target more carefully
                this.preview.addEventListener('click', (e) => {
                    // Don't open file dialog if remove button or its children were clicked
                    if (e.target.classList.contains('remove-image-btn') ||
                        e.target.closest('.remove-image-btn')) {
                        return;
                    }
                    this.input.click();
                });
            }

            handleDragOver(e) {
                e.preventDefault();
                this.preview.classList.add('drag-over');
                this.preview.style.borderColor = '#3b82f6';
                this.preview.style.background = '#f0f9ff';
            }

            handleDragLeave(e) {
                e.preventDefault();
                this.preview.classList.remove('drag-over');
                this.preview.style.borderColor = '#e2e8f0';
                this.preview.style.background = '#f8fafc';
            }

            handleDrop(e) {
                e.preventDefault();
                this.handleDragLeave(e);

                const file = e.dataTransfer.files[0];
                if (file) {
                    this.processFile(file);
                }
            }

            handleFileSelect(e) {
                const file = e.target.files[0];
                if (file) {
                    this.processFile(file);
                }
            }

            processFile(file) {
                // Clear previous error
                this.clearError();

                // Validate file type
                if (!this.acceptedTypes.includes(file.type)) {
                    this.showError(`Please upload a valid image file (${this.acceptedTypes.join(', ')})`);
                    return;
                }

                // Validate file size
                if (file.size > this.maxSize) {
                    this.showError(`File size must be less than ${this.formatBytes(this.maxSize)}`);
                    return;
                }

                // Show loading state
                this.showLoading();

                // Create preview
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.createPreview(e.target.result, file);
                    this.hideLoading();
                };
                reader.onerror = () => {
                    this.showError('Failed to load image');
                    this.hideLoading();
                };
                reader.readAsDataURL(file);
            }

            createPreview(imageUrl, file) {
                this.preview.innerHTML = '';

                const img = document.createElement('img');
                img.src = imageUrl;
                img.alt = file.name;
                img.className = 'preview-image';

                const removeBtn = document.createElement('button');
                removeBtn.innerHTML = '&times;';
                removeBtn.className = 'remove-image-btn';
                removeBtn.type = 'button'; // prevent any form submission behavior
                removeBtn.title = 'Remove image';

                // Use addEventListener instead of onclick
                removeBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    e.preventDefault();
                    this.removePreview();
                });

                this.preview.appendChild(img);
                this.preview.appendChild(removeBtn);
                this.preview.classList.add('success');

                this.input.dispatchEvent(new CustomEvent('imageSelected', {
                    detail: {
                        file,
                        imageUrl
                    }
                }));
            }

            removePreview() {
                // Reset preview to original state
                this.preview.innerHTML = `
                                                <svg class="upload-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
                                                    <circle cx="12" cy="13" r="4"/>
                                                </svg>
                                                <span class="upload-text">Click to upload photo</span>
                                                <span class="upload-hint">JPG, PNG, or GIF (Max 5MB)</span>
                                            `;

                // Reset file input
                this.input.value = '';
                this.preview.classList.remove('success');
                this.preview.style.borderColor = '#e2e8f0';
                this.preview.style.background = '#f8fafc';

                // Trigger custom event
                this.input.dispatchEvent(new CustomEvent('imageRemoved'));
            }

            showLoading() {
                this.preview.classList.add('loading');
                this.preview.style.pointerEvents = 'none';
            }

            hideLoading() {
                this.preview.classList.remove('loading');
                this.preview.style.pointerEvents = 'auto';
            }

            showError(message) {
                if (this.errorEl) {
                    this.errorEl.textContent = message;
                    this.errorEl.style.opacity = '1';

                    // Auto-hide error after 3 seconds
                    setTimeout(() => {
                        this.errorEl.style.opacity = '0';
                        setTimeout(() => {
                            if (this.errorEl.textContent === message) {
                                this.errorEl.textContent = '';
                            }
                        }, 300);
                    }, 3000);
                }
            }

            clearError() {
                if (this.errorEl) {
                    this.errorEl.textContent = '';
                    this.errorEl.style.opacity = '0';
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

        // Initialize the file upload handler
        document.addEventListener('DOMContentLoaded', () => {
            const fileUpload = new FileUploadHandler(
                'editStudentPhoto',
                'editStudentPhotoPreview',
                'editStudentPhotoError', {
                    maxSize: 5 * 1024 * 1024, // 5MB
                    acceptedTypes: ['image/jpg', 'image/jpeg', 'image/png', 'image/gif']
                }
            );

            // Optional: Listen for events
            fileUpload.input.addEventListener('imageSelected', (e) => {
                console.log('Image selected:', e.detail.file.name);
                // You can add additional logic here, like auto-upload
            });

            fileUpload.input.addEventListener('imageRemoved', () => {
                console.log('Image removed');
            });
        });
    </script>

    <script>
        $(document).ready(function() {
            // Handle view button click
            $('a[href*="students/view/"]').on('click', function(e) {
                e.preventDefault();
                const studentId = $(this).attr('href').split('/').pop();

                // Show modal with loading state
                $('#studentDetailsModal').modal('show');

                // Show loading spinner and hide content
                $('#modalLoadingSpinner').removeClass('d-none');
                $('#studentDetailsContent').addClass('d-none');

                // Fetch student details
                $.ajax({
                    url: `/students/view/${studentId}`,
                    type: 'GET',
                    success: function(response) {
                        const student = response.student;

                        // Update modal with student data
                        updateModalWithStudentData(student);

                        // Hide loading spinner and show content
                        $('#modalLoadingSpinner').addClass('d-none');
                        $('#studentDetailsContent').removeClass('d-none');
                    },
                    error: function(data) {
                        $('body').html(data.responseText);
                    }
                });
            });

            // Handle edit button from modal
            $('#editStudentFromModal').on('click', function() {
                const editUrl = $('#editStudentFromModal').data('edit-url');
                if (editUrl) {
                    window.location.href = editUrl;
                }
            });

            // Function to update modal with student data
            function updateModalWithStudentData(student) {
                // Update photo
                // Update photo
if (student.photo_url) {
    $('#modalStudentPhoto')
        .attr('src', student.photo_url)
        .css('display', 'block');

    $('#modalStudentPhoto').siblings('.photo-placeholder')
        .css('display', 'none');
} else {
    $('#modalStudentPhoto')
        .css('display', 'none');

    $('#modalStudentPhoto').siblings('.photo-placeholder')
        .css('display', 'flex');
}

                // Update name and badges
                $('#modalStudentName').text(`${student.firstname} ${student.lastname}`);
                $('#modalFullName').text(`${student.firstname} ${student.lastname}`);
                $('#modalStudentId').text(`ID: ${student.registration_number || 'N/A'}`);
                $('#modalStudentCategory').text(`Category: ${student.senior || 'N/A'}`);
                $('#modalStudentGender').text(`Gender: ${student.gender || 'N/A'}`);
                $('#modalStudentStream').text(`Stream: ${student.stream || 'N/A'}`);

                // Personal Information
                $('#modalRegNumber').text(student.registration_number || 'N/A');
                $('#modalAdmissionNumber').text(student.admission_number || 'N/A');
                $('#modalDateOfBirth').text(formatDate(student.date_of_birth) || 'N/A');
                $('#modalPlaceOfBirth').text(student.place_of_birth || 'N/A');
                $('#modalNationality').text(student.nationality || 'N/A');
                $('#modalBirthCertificate').text(student.birth_certificate_entry_number || 'N/A');

                // Academic Information
                $('#modalDateOfAdmission').text(formatDate(student.date_of_admission) || 'N/A');
                $('#modalAdmissionYear').text(student.admission_year || 'N/A');
                $('#modalSenior').text(student.senior || 'N/A');
                $('#modalStream').text(student.stream || 'N/A');
                $('#modalPleScore').text(student.ple_score || 'N/A');
                $('#modalUceScore').text(student.uce_score || 'N/A');
                $('#modalPreviousSchool').text(student.previous_school || 'N/A');
                $('#modalPrimarySchool').text(student.primary_school_name || 'N/A');

                // Contact Information
                $('#modalPrimaryContact').text(student.primary_contact || 'N/A');
                $('#modalOtherContact').text(student.other_contact || 'N/A');
                $('#modalHomeAddress').text(student.home_address || 'N/A');

                // Guardian Information
                $('#modalGuardianNames').text(student.guardian_names || 'N/A');
                $('#modalRelation').text(student.relation || 'N/A');
                $('#modalGuardianPhone').text(student.guardian_phone || 'N/A');
                $('#modalGuardianEmail').text(student.guardian_email || 'N/A');

                // Additional Information
                $('#modalMedicalHistory').text(student.medical_history || 'N/A');
                $('#modalComments').text(student.comments || 'N/A');

                // Set edit URL for the edit button
                $('#editStudentFromModal').data('edit-url', `/students.edit/${student.id}`);
            }

            // Helper function to format date
            function formatDate(dateString) {
                if (!dateString) return null;
                const date = new Date(dateString);
                return date.toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                });
            }
        });
    </script>

    <script>
        $(document).ready(function() {
            // Handle Edit button click
            $('a[href*="students.edit/"]').on('click', function(e) {
                e.preventDefault();
                const studentId = $(this).attr('href').split('/').pop();

                // Show edit modal with loading state
                $('#editStudentModal').modal('show');

                $('#editModalLoadingSpinner').removeClass('d-none');
                $('#editStudentForm').addClass('d-none');

                // Fetch student data
                $.ajax({
                    url: `/students/view/${studentId}`,
                    type: 'GET',
                    success: function(response) {
                        const s = response.student;

                        // Populate all fields
                        $('#editStudentId').val(s.id);
                        $('#editFirstname').val(s.firstname || '');
                        $('#editLastname').val(s.lastname || '');
                        $('#editGender').val(s.gender || '');
                        $('#editDateOfBirth').val(s.date_of_birth ? s.date_of_birth.split('T')[
                            0] : '');
                        $('#editPlaceOfBirth').val(s.place_of_birth || '');
                        $('#editNationality').val(s.nationality || '');
                        $('#editBirthCertificate').val(s.birth_certificate_entry_number || '');
                        $('#editRegNumber').val(s.registration_number || '');
                        $('#editAdmissionNumber').val(s.admission_number || '');
                        $('#editAdmissionYear').val(s.admission_year || '');
                        $('#editDateOfAdmission').val(s.date_of_admission ? s.date_of_admission
                            .split('T')[0] : '');
                        $('#editSenior').val(s.senior || '');
                        $('#editStream').val(s.stream || '');
                        $('#editPleScore').val(s.ple_score || '');
                        $('#editUceScore').val(s.uce_score || '');
                        $('#editPreviousSchool').val(s.previous_school || '');
                        $('#editPrimarySchool').val(s.primary_school_name || '');
                        $('#editPrimaryContact').val(s.primary_contact || '');
                        $('#editOtherContact').val(s.other_contact || '');
                        $('#editHomeAddress').val(s.home_address || '');
                        $('#editGuardianNames').val(s.guardian_names || '');
                        $('#editRelation').val(s.relation || '');
                        $('#editGuardianPhone').val(s.guardian_phone || '');
                        $('#editGuardianEmail').val(s.guardian_email || '');
                        $('#editMedicalHistory').val(s.medical_history || '');
                        $('#editComments').val(s.comments || '');

                        // Show current photo preview
if (s.photo_url) {
    $('#currentPhotoPreview').html(`
        <p class="text-muted small mb-1">Current photo:</p>
        <img src="${s.photo_url}" 
             class="rounded-circle border" 
             style="width:60px;height:60px;object-fit:cover;">
        <p class="text-muted small mt-1">
            Upload a new file to replace it.
        </p>
    `);
} else {
    $('#currentPhotoPreview').html(`
        <p class="text-muted small">No photo uploaded yet.</p>
    `);
}

                        $('#editModalLoadingSpinner').addClass('d-none');
                        $('#editStudentForm').removeClass('d-none');
                    },
                    error: function() {
                        Swal.fire('Error', 'Could not load student data.', 'error');
                    }
                });
            });

            // Handle Save Changes with SweetAlert confirmation
            $('#saveStudentEdit').on('click', function() {
                const firstname = $('#editFirstname').val().trim();
                const lastname = $('#editLastname').val().trim();
                const gender = $('#editGender').val();

                // Basic validation
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
                        formData.append('lastname', lastname);
                        formData.append('gender', gender);
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

                        // Show loading state on button
                        $('#saveStudentEdit').html(
                            '<span class="spinner-border spinner-border-sm me-1"></span> Saving...'
                        ).prop('disabled', true);

                        $.ajax({
                            url: `/students/update/${studentId}`,
                            type: 'POST',
                            data: formData,
                            processData: false,
                            contentType: false,
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
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
                                    location
                                        .reload(); // refresh table to reflect changes
                                });
                            },
                            error: function(data) {
                                $('body').html(data.responseText);
                            }
                            // error: function(xhr) {
                            //     $('#saveStudentEdit').html(
                            //             '<i class="bi bi-save me-1"></i> Save Changes')
                            //         .prop('disabled', false);
                            //     const msg = xhr.responseJSON?.message ||
                            //         'Something went wrong. Please try again.';
                            //     Swal.fire({
                            //         icon: 'error',
                            //         title: 'Error',
                            //         text: msg,
                            //         confirmButtonColor: '#2C29CA'
                            //     });
                            // }
                        });
                    }
                });
            });
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.delete-student').forEach(button => {
                button.addEventListener('click', function() {
                    let studentId = this.dataset.id;

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
                            fetch(`/students/delete/${studentId}`, {
                                    method: 'DELETE',
                                    headers: {
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                        'Accept': 'application/json',
                                    },
                                })
                                .then(res => res.json())
                                .then(data => {
                                    if (data.success) {
                                        Swal.fire(
                                            'Deleted!',
                                            data.message,
                                            'success'
                                        ).then(() => {
                                            location.reload();
                                        });
                                    } else {
                                        Swal.fire(
                                            'Error!',
                                            data.message,
                                            'error'
                                        );
                                    }
                                })
                                .catch(err => {
                                    Swal.fire('Error!', 'Something went wrong',
                                    'error');
                                });
                        }
                    });
                });
            });
        });
    </script>

    <script>
// Pagination Manager Class
class StreamPagination {
    constructor(containerId, data, itemsPerPage = 10) {
        this.containerId = containerId;
        this.allData = data;
        this.itemsPerPage = itemsPerPage;
        this.currentPage = 1;
        this.totalPages = Math.ceil(data.length / itemsPerPage);
        this.init();
    }
    
    init() {
        this.render();
        this.renderPagination();
    }
    
    render() {
        const start = (this.currentPage - 1) * this.itemsPerPage;
        const end = start + this.itemsPerPage;
        const pageData = this.allData.slice(start, end);
        
        // Update the table body
        const tbody = document.querySelector(`#${this.containerId} tbody`);
        if (tbody) {
            tbody.innerHTML = this.generateTableRows(pageData, start);
        }
        
        // Update showing info
        const showingStart = document.querySelector(`#${this.containerId.replace('tableBody', 'showingStart')}`);
        const showingEnd = document.querySelector(`#${this.containerId.replace('tableBody', 'showingEnd')}`);
        const totalItems = document.querySelector(`#${this.containerId.replace('tableBody', 'totalItems')}`);
        
        if (showingStart) showingStart.textContent = start + 1;
        if (showingEnd) showingEnd.textContent = Math.min(end, this.allData.length);
        if (totalItems) totalItems.textContent = this.allData.length;
    }
    
    generateTableRows(students, startIndex) {
        return students.map((student, idx) => {
            const rowNumber = startIndex + idx + 1;
            const imageHtml = student.student_photo ? 
                `<img src="${student.photo_url || '/default-avatar.png'}" class="rounded-circle border" style="width: 45px; height: 45px; object-fit: cover;">` :
                `<div class="bg-secondary rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                    <i class="bi bi-person text-white" style="font-size: 20px;"></i>
                 </div>`;
            
            const genderBadge = student.gender == 'Male' ? 'bg-info' : (student.gender == 'Female' ? 'bg-success' : 'bg-secondary');
            
            return `
                <tr id="student-row-${student.id}">
                    <td>${rowNumber}</td>
                    <td class="text-center">${imageHtml}</td>
                    <td class="fw-medium">${this.escapeHtml(student.firstname)}</td>
                    <td>${this.escapeHtml(student.lastname)}</td>
                    <td><span class="badge ${genderBadge}">${student.gender}</span></td>
                    <td>
                        <div class="btn-group btn-group-sm" role="group">
                            <button type="button" class="btn btn-info view-student" data-id="${student.id}">
                                <i class="bi bi-eye"></i> View
                            </button>
                            <button type="button" class="btn btn-primary edit-student" data-id="${student.id}">
                                <i class="bi bi-pencil-square"></i> Edit
                            </button>
                            <button type="button" class="btn btn-danger delete-student" data-id="${student.id}">
                                <i class="bi bi-trash"></i> Delete
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');
    }
    
    renderPagination() {
        const paginationContainer = document.querySelector(`#${this.containerId.replace('tableBody', 'paginationList')}`);
        if (!paginationContainer) return;
        
        let paginationHtml = '';
        
        // Previous button
        paginationHtml += `
            <li class="page-item ${this.currentPage === 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="prev" aria-label="Previous">
                    <span aria-hidden="true">&laquo;</span>
                </a>
            </li>
        `;
        
        // Page numbers
        const maxVisible = 5;
        let startPage = Math.max(1, this.currentPage - Math.floor(maxVisible / 2));
        let endPage = Math.min(this.totalPages, startPage + maxVisible - 1);
        
        if (endPage - startPage + 1 < maxVisible) {
            startPage = Math.max(1, endPage - maxVisible + 1);
        }
        
        if (startPage > 1) {
            paginationHtml += `<li class="page-item"><a class="page-link" href="#" data-page="1">1</a></li>`;
            if (startPage > 2) paginationHtml += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
        }
        
        for (let i = startPage; i <= endPage; i++) {
            paginationHtml += `
                <li class="page-item ${this.currentPage === i ? 'active' : ''}">
                    <a class="page-link" href="#" data-page="${i}">${i}</a>
                </li>
            `;
        }
        
        if (endPage < this.totalPages) {
            if (endPage < this.totalPages - 1) paginationHtml += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            paginationHtml += `<li class="page-item"><a class="page-link" href="#" data-page="${this.totalPages}">${this.totalPages}</a></li>`;
        }
        
        // Next button
        paginationHtml += `
            <li class="page-item ${this.currentPage === this.totalPages ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="next" aria-label="Next">
                    <span aria-hidden="true">&raquo;</span>
                </a>
            </li>
        `;
        
        paginationContainer.innerHTML = paginationHtml;
        
        // Add click handlers
        paginationContainer.querySelectorAll('.page-link').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const page = link.dataset.page;
                if (page === 'prev' && this.currentPage > 1) {
                    this.goToPage(this.currentPage - 1);
                } else if (page === 'next' && this.currentPage < this.totalPages) {
                    this.goToPage(this.currentPage + 1);
                } else if (page && !isNaN(page)) {
                    this.goToPage(parseInt(page));
                }
            });
        });
    }
    
    goToPage(page) {
        if (page < 1 || page > this.totalPages) return;
        this.currentPage = page;
        this.render();
        this.renderPagination();
        
        // Smooth scroll to table
        const tableContainer = document.querySelector(`#${this.containerId.replace('tableBody', '')}`);
        if (tableContainer) {
            tableContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    }
    
    escapeHtml(str) {
        if (!str) return '';
        return str.replace(/[&<>]/g, function(m) {
            if (m === '&') return '&amp;';
            if (m === '<') return '&lt;';
            if (m === '>') return '&gt;';
            return m;
        });
    }
}

// Initialize pagination for all streams
document.addEventListener('DOMContentLoaded', function() {
    // Store all student data
    const streamsData = {};
    
    @foreach ($groupedStudents as $seniorIndex => $streams)
        @foreach ($streams as $streamIndex => $students)
            const tableId = 'tableBody-{{ $seniorIndex }}-{{ $streamIndex }}';
            const studentsData = @json($students->values());
            streamsData[tableId] = studentsData;
            
            if (studentsData.length > 10) {
                new StreamPagination(tableId, studentsData, 10);
            }
        @endforeach
    @endforeach
    
    // Re-attach event handlers for dynamic buttons
    function attachEventHandlers() {
        // View student handlers
        document.querySelectorAll('.view-student').forEach(btn => {
            btn.removeEventListener('click', viewStudentHandler);
            btn.addEventListener('click', viewStudentHandler);
        });
        
        // Edit student handlers
        document.querySelectorAll('.edit-student').forEach(btn => {
            btn.removeEventListener('click', editStudentHandler);
            btn.addEventListener('click', editStudentHandler);
        });
        
        // Delete student handlers
        document.querySelectorAll('.delete-student').forEach(btn => {
            btn.removeEventListener('click', deleteStudentHandler);
            btn.addEventListener('click', deleteStudentHandler);
        });
    }
    
    function viewStudentHandler(e) {
        const studentId = this.dataset.id;
        // Your existing view logic
        showStudentDetails(studentId);
    }
    
    function editStudentHandler(e) {
        const studentId = this.dataset.id;
        // Your existing edit logic
        showEditModal(studentId);
    }
    
    function deleteStudentHandler(e) {
        const studentId = this.dataset.id;
        // Your existing delete logic
        confirmDeleteStudent(studentId);
    }
    
    // Store original functions
    window.showStudentDetails = function(studentId) {
        // Move your existing view logic here
        $('#studentDetailsModal').modal('show');
        $('#modalLoadingSpinner').removeClass('d-none');
        $('#studentDetailsContent').addClass('d-none');
        
        $.ajax({
            url: `/students/view/${studentId}`,
            type: 'GET',
            success: function(response) {
                updateModalWithStudentData(response.student);
                $('#modalLoadingSpinner').addClass('d-none');
                $('#studentDetailsContent').removeClass('d-none');
            },
            error: function(data) {
                $('body').html(data.responseText);
            }
        });
    };
    
    window.showEditModal = function(studentId) {
        $('#editStudentModal').modal('show');
        $('#editModalLoadingSpinner').removeClass('d-none');
        $('#editStudentForm').addClass('d-none');
        
        $.ajax({
            url: `/students/view/${studentId}`,
            type: 'GET',
            success: function(response) {
                populateEditForm(response.student);
                $('#editModalLoadingSpinner').addClass('d-none');
                $('#editStudentForm').removeClass('d-none');
            },
            error: function() {
                Swal.fire('Error', 'Could not load student data.', 'error');
            }
        });
    };
    
    window.confirmDeleteStudent = function(studentId) {
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
                fetch(`/students/delete/${studentId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire('Deleted!', data.message, 'success').then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error!', data.message, 'error');
                    }
                })
                .catch(err => {
                    Swal.fire('Error!', 'Something went wrong', 'error');
                });
            }
        });
    };
    
    // Initial attachment
    attachEventHandlers();
    
    // Use MutationObserver to handle dynamically added elements
    const observer = new MutationObserver(function(mutations) {
        attachEventHandlers();
    });
    
    observer.observe(document.body, { childList: true, subtree: true });
});
</script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    {{-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script> --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
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
