<?php
use App\Http\Controllers\Helper;
?>
@extends('layouts-side-bar.master')
@section('css')
    <link href="{{ URL::asset('assets/plugins/jvectormap/jqvmap.css') }}" rel="stylesheet" />
    <link href="{{ URL::asset('assets/plugins/datatable/dataTables.bootstrap4.min.css') }}" rel="stylesheet" />
    <link href="{{ URL::asset('assets/plugins/bootstrap-daterangepicker/daterangepicker.css') }}" rel="stylesheet" />
@endsection

@section('content')
    <div class="side-app">
        <style>
            .form-check-input {
                transform: scale(1.5);
                margin-right: 10px;
            }

            .form-check-label {
                line-height: 1.5;
            }

            .subject-control-buttons {
                margin-bottom: 15px;
                padding: 10px;
                background: #f8f9fa;
                border-radius: 8px;
                display: inline-block;
            }

            .btn-check-all,
            .btn-uncheck-all {
                padding: 5px 15px;
                margin-right: 10px;
                border-radius: 5px;
                font-size: 13px;
            }

            .btn-check-all {
                background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
                border: none;
                color: white;
            }

            .btn-uncheck-all {
                background: linear-gradient(135deg, #dc3545 0%, #f86c6b 100%);
                border: none;
                color: white;
            }

            .subject-section-card {
                background: white;
                border-radius: 10px;
                padding: 20px;
                margin-bottom: 20px;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            }

            .section-title {
                font-size: 18px;
                font-weight: 600;
                margin-bottom: 20px;
                padding-bottom: 10px;
                border-bottom: 2px solid #e9ecef;
                color: #495057;
            }

            .custom-mode-banner {
                background: #eef7ff;
                border: 1px solid #b6def7;
                color: #0c5987;
                padding: 12px 18px;
                border-radius: 8px;
                margin-bottom: 20px;
            }
        </style>

        <div class="row">
            <div class="col-lg-12 col-xl-12 col-md-12 col-sm-12">
                <div class="card bg-primary">
                    @include('layouts.class-buttons')
                    <div class="card-body bg-light">

                        <div class="custom-mode-banner">
                            <i class="fas fa-info-circle"></i>
                            Editing subjects for <strong>{{ Helper::recordMdname($classId) }}</strong>
                            &mdash; {{ $streamId === \App\Http\Controllers\ClassandSubjectController::NO_STREAM_SENTINEL ? 'No Stream' : $streamId }}.
                            Need a new subject that isn't listed below? Add it in
                            <a href="{{ route('school.custom-subjects.manage') }}"><strong>Manage My Subjects</strong></a> first.
                        </div>

                        <form id="editSubjectAssignmentForm">
                            @csrf
                            @method('PUT')

                            <div class="subject-section-card">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="section-title mb-0">Subjects for this class</h5>
                                    <div class="subject-control-buttons">
                                        <button type="button" class="btn btn-sm btn-check-all" onclick="$('.custom-subject-checkbox').prop('checked', true);">
                                            <i class="fas fa-check-double"></i> Check All
                                        </button>
                                        <button type="button" class="btn btn-sm btn-uncheck-all" onclick="$('.custom-subject-checkbox').prop('checked', false);">
                                            <i class="fas fa-times-circle"></i> Uncheck All
                                        </button>
                                    </div>
                                </div>

                                @if ($customSubjects->isEmpty())
                                    <p class="text-muted mb-0">
                                        You don't have any subjects in this category yet.
                                        <a href="{{ route('school.custom-subjects.manage') }}">Add some here</a>.
                                    </p>
                                @else
                                    <div class="row">
                                        @foreach ($customSubjects as $subject)
                                            <div class="col-lg-4 col-md-6 col-12">
                                                <div class="form-check">
                                                    <input class="form-check-input custom-subject-checkbox" type="checkbox"
                                                        id="edit-custom-subject-{{ $subject->id }}" value="{{ $subject->id }}"
                                                        {{ in_array($subject->id, $assignedSubjectIds) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="edit-custom-subject-{{ $subject->id }}">
                                                        {{ $subject->subject_name }}
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            <div class="row">
                                <div class="mt-4 text-left">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Save Changes
                                    </button>
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

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    $(document).ready(function () {
        $('#editSubjectAssignmentForm').on('submit', function (e) {
            e.preventDefault();

            let selectedSubjects = [];
            $('.custom-subject-checkbox:checked').each(function () {
                selectedSubjects.push($(this).val());
            });

            if (selectedSubjects.length === 0) {
                Swal.fire({ 
                    icon: 'error', 
                    title: 'No Subjects Selected', 
                    text: 'Please select at least one subject before saving.' 
                });
                return;
            }

            let $submitBtn = $(this).find('button[type="submit"]');

            Swal.fire({
                title: 'Save changes?',
                text: "This will replace the subjects currently attached to this class-stream.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, save it!',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show custom loading spinner with progress information
                    Swal.fire({
                        title: 'Saving Changes...',
                        html: `
                            <div style="margin: 20px 0;">
                                <div class="spinner-border text-primary" role="status" style="width: 4rem; height: 4rem;">
                                    <span class="sr-only">Loading...</span>
                                </div>
                            </div>
                            <p style="color: #6c757d; font-size: 14px;">Updating subjects for this class...</p>
                            <div class="progress" style="height: 5px; margin-top: 15px;">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                     role="progressbar" 
                                     style="width: 100%; background-color: #3085d6;" 
                                     aria-valuenow="100" 
                                     aria-valuemin="0" 
                                     aria-valuemax="100">
                                </div>
                            </div>
                        `,
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    $submitBtn.prop('disabled', true);

                    $.ajax({
                        url: '{{ route("assign.subjects.update", $assignment->id) }}',
                        method: 'PUT',
                        data: {
                            subjects: selectedSubjects,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function (response) {
                            // Close the loading spinner
                            Swal.close();
                            
                            // Show success message with auto-close option
                            Swal.fire({
                                icon: 'success',
                                title: 'Saved!',
                                text: response.message || 'Subjects updated successfully.',
                                confirmButtonText: 'OK',
                                confirmButtonColor: '#3085d6',
                                allowOutsideClick: false,
                                allowEscapeKey: false,
                                timer: 3000,
                                timerProgressBar: true
                            }).then(() => {
                                window.location.href = '{{ route("manage.class.streams", ["id" => $assignment->class_id]) }}';
                            });
                        },
                        error: function (xhr) {
                            // Close the loading spinner
                            Swal.close();
                            
                            let errorMessage = xhr.responseJSON?.message || 'An unexpected error occurred.';
                            let errorDetails = '';
                            if (xhr.responseJSON?.errors) {
                                errorDetails = '<br><br><small style="color: #dc3545;">' + 
                                    Object.values(xhr.responseJSON.errors).flat().join('<br>') + 
                                    '</small>';
                            }
                            
                            Swal.fire({ 
                                icon: 'error', 
                                title: 'Error', 
                                html: errorMessage + errorDetails,
                                confirmButtonColor: '#d33'
                            });
                        },
                        complete: function () {
                            $submitBtn.prop('disabled', false);
                        }
                    });
                }
            });
        });
    });
</script>
@endsection
