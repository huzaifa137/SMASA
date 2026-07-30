@extends('layouts-side-bar.master')
@section('css')
    <link href="{{ URL::asset('assets/plugins/datatable/dataTables.bootstrap4.min.css') }}" rel="stylesheet" />
@endsection

@section('content')
    <div class="side-app">
        <style>
            .subject-tab-card {
                background: white;
                border-radius: 10px;
                padding: 20px;
                margin-bottom: 20px;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            }

            .subject-row {
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 10px 14px;
                border: 1px solid #e9ecef;
                border-radius: 8px;
                margin-bottom: 8px;
                transition: all 0.3s ease;
            }

            .subject-row:hover {
                background-color: #007bff;
                border-color: #dee2e6;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
                transform: translateX(4px);
            }

            .subject-row:hover,
            .subject-row:hover strong,
            .subject-row:hover span,
            .subject-row:hover div,
            .subject-row:hover i {
                color: #fff !important;
            }


            .subject-row.inactive {
                opacity: 0.55;
            }

            .subject-row.inactive:hover {
                opacity: 0.7;
            }

            .switch-banner {
                background: #fff8e6;
                border: 1px solid #ffe08a;
                color: #7a5b00;
                padding: 14px 18px;
                border-radius: 8px;
                margin-bottom: 20px;
            }

            .tab-pane {
                display: none;
            }

            .tab-pane.active {
                display: block;
            }

            /* Improved Tab Styling */
            .nav-tabs {
                border-bottom: 2px solid #e9ecef;
                padding-bottom: 0;
                gap: 8px;
                /* Space between tabs */
                ;
            }

            .nav-tabs .nav-item {
                margin-bottom: 0;
            }

            .nav-tabs .nav-link {
                border: none;
                border-radius: 8px 8px 0 0;
                padding: 12px 24px;
                color: #6c757d;
                font-weight: 500;
                background: transparent;
                transition: all 0.3s ease;
                position: relative;
                margin-right: 4px;
            }

            .nav-tabs .nav-link:hover {
                background: #d6ecff !important;
                color: #004085 !important;
            }

            .nav-tabs .nav-link:hover i,
            .nav-tabs .nav-link:hover .badge {
                color: #004085 !important;
            }

            .nav-tabs .nav-link:focus {
                outline: none;
            }

            .nav-tabs .nav-link.active {
                color: #007bff;
                background: white;
                border: none;
                border-radius: 8px 8px 0 0;
                box-shadow: 0 -2px 8px rgba(0, 0, 0, 0.05);
                font-weight: 600;
            }

            .nav-tabs .nav-link.active::after {
                content: '';
                position: absolute;
                bottom: -2px;
                left: 0;
                right: 0;
                height: 2px;
                background: #007bff;
                border-radius: 2px;
            }

            /* Add subtle animation for tab content */
            .tab-pane.fade {
                transition: opacity 0.3s ease;
            }

            .tab-pane.fade:not(.show) {
                opacity: 0;
            }

            .tab-pane.fade.show {
                opacity: 1;
            }

            /* Button hover effects */
            .btn-add-subject:hover {
                transform: translateY(-1px);
                box-shadow: 0 4px 8px rgba(0, 123, 255, 0.2);
            }

            .btn-edit-subject:hover {
                background: #007bff;
                color: white;
                transform: scale(1.05);
            }

            .btn-delete-subject:hover {
                background: #dc3545;
                color: white;
                transform: scale(1.05);
            }

            /* Action buttons container */
            .subject-row .btn {
                transition: all 0.25s ease;
                margin-left: 4px;
            }

            .subject-row .btn:first-child {
                margin-left: 0;
            }

            /* Keep active tab text visible when hovered */
            .nav-tabs .nav-link.active:hover,
            .nav-tabs .nav-link.active:focus {
                color: #007bff !important;
                background: #fff !important;
            }

            /* Keep the badge visible too */
            .nav-tabs .nav-link.active:hover .badge,
            .nav-tabs .nav-link.active:focus .badge {
                color: #212529;
                background-color: #f8f9fa;
            }

            #subjectModal .modal-header .modal-title,
            #subjectModal .modal-header .modal-title i {
                color: #fff !important;
            }
        </style>

        <div class="row">
            <div class="col-lg-12">
                <div class="card bg-primary">
                    @include('layouts.class-buttons')
                    <div class="card-body bg-light">

                        <h4 class="mb-3">Manage My Subjects</h4>

                        @if (!$school->custom_subjects_active)
                            <div class="switch-banner">
                                <i class="fas fa-exclamation-triangle"></i>
                                You haven't switched to your own subject list yet. You can build it below first,
                                then head to the
                                <a href="{{ route('school.custom-subjects.switch') }}" style="margin-top:3rem;"><strong>Switch
                                        to Custom Subjects</strong></a>
                                page to confirm &mdash; your current subjects will be carried over automatically.
                            </div>
                        @endif

                        <!-- Improved Bootstrap 4 Tabs -->
                        <ul class="nav nav-tabs mb-4" id="subjectTypeTabs" role="tablist">
                            @foreach ($classTypes as $key => $label)
                                <li class="nav-item">
                                    <a class="nav-link {{ $loop->first ? 'active' : '' }}" id="tab-{{ $key }}" data-toggle="tab"
                                        href="#pane-{{ $key }}" role="tab" aria-controls="pane-{{ $key }}"
                                        aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                                        <i
                                            class="fas fa-{{ $key === 'primary' ? 'book' : ($key === 'secondary' ? 'graduation-cap' : 'university') }} mr-2"></i>
                                        {{ $label }}
                                        <span
                                            class="badge badge-light ml-2">{{ ($subjects[$key] ?? collect())->count() }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>

                        <div class="tab-content" id="subjectTabContent">
                            @foreach ($classTypes as $key => $label)
                                <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="pane-{{ $key }}"
                                    role="tabpanel" aria-labelledby="tab-{{ $key }}">
                                    <div class="subject-tab-card">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h5 class="mb-0">
                                                <i
                                                    class="fas fa-{{ $key === 'primary' ? 'book' : ($key === 'secondary' ? 'graduation-cap' : 'university') }} mr-2"></i>
                                                {{ $label }} Subjects
                                            </h5>
                                            <button type="button" class="btn btn-sm btn-primary btn-add-subject"
                                                data-class-type="{{ $key }}" data-class-label="{{ $label }}">
                                                <i class="fas fa-plus"></i> Add Subject
                                            </button>
                                        </div>

                                        <div id="subject-list-{{ $key }}">
                                            @forelse (($subjects[$key] ?? collect()) as $subject)
                                                <div class="subject-row {{ $subject->is_active ? '' : 'inactive' }}"
                                                    data-id="{{ $subject->id }}">
                                                    <div>
                                                        <strong>{{ $subject->subject_name }}</strong>
                                                        @if ($subject->subject_code)
                                                            <span class="text-muted">({{ $subject->subject_code }})</span>
                                                        @endif
                                                        @unless ($subject->is_active)
                                                            <span class="badge bg-secondary">inactive</span>
                                                        @endunless
                                                    </div>
                                                    <div>
                                                        <button type="button" class="btn btn-sm btn-primary btn-edit-subject"
                                                            data-id="{{ $subject->id }}" data-name="{{ $subject->subject_name }}"
                                                            data-code="{{ $subject->subject_code }}"
                                                            data-active="{{ $subject->is_active ? 1 : 0 }}">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-danger btn-delete-subject"
                                                            data-id="{{ $subject->id }}">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </button>

                                                    </div>
                                                </div>
                                            @empty
                                                <p class="text-muted mb-0">No {{ $label }} subjects yet.</p>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add / Edit Subject Modal -->
<!-- Add / Edit Subject Modal -->
<div class="modal fade" id="subjectModal" tabindex="-1" role="dialog" aria-labelledby="subjectModalTitle"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">

            <form id="subjectForm">

                <div class="modal-header">
                    <h5 class="modal-title" id="subjectModalTitle">Add Subject</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">

                    <input type="hidden" id="subject_id" value="">
                    <input type="hidden" id="subject_class_type" value="">

                    <div class="form-group">
                        <label>Category</label>
                        <input type="text" id="subject_class_label" class="form-control" disabled>
                    </div>

                    <div class="form-group">
                        <label>Subject Name</label>
                        <input type="text" id="subject_name" class="form-control" placeholder="e.g. General Science"
                            required>
                    </div>

                    <div class="form-group">
                        <label>Subject Code (optional)</label>
                        <input type="text" id="subject_code" class="form-control" placeholder="e.g. SCI">
                    </div>

                    <div class="form-check" id="active-toggle-wrapper" style="display:none;">
                        <input class="form-check-input" type="checkbox" id="subject_is_active" checked>
                        <label class="form-check-label" for="subject_is_active">
                            Active
                        </label>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>

            </form>

        </div>
    </div>
</div>
    </div>
    </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js"></script>

    <script>
    $(document).ready(function () {
        // Initialize Bootstrap 4 modal
        const modal = new bootstrap.Modal(document.getElementById('subjectModal'));

        // ===== TAB PERSISTENCE =====
        // Function to save active tab
        function saveActiveTab(tabId) {
            if (tabId) {
                localStorage.setItem('activeSubjectTab', tabId);
            }
        }

        // Function to get saved active tab
        function getSavedActiveTab() {
            return localStorage.getItem('activeSubjectTab');
        }

        // Function to activate a specific tab - more robust version
        function activateTab(tabId) {
            if (!tabId) return false;
            
            // Find the tab link with this ID
            const tabLink = $(`#${tabId}`);
            if (tabLink.length && tabLink.hasClass('nav-link')) {
                // Get the target pane ID from href
                const targetPane = tabLink.attr('href');
                
                // Remove active class from all tabs and panes
                $('.nav-tabs .nav-link').removeClass('active');
                $('.tab-pane').removeClass('show active');
                
                // Add active class to this tab
                tabLink.addClass('active');
                tabLink.attr('aria-selected', 'true');
                
                // Show the target pane
                if (targetPane) {
                    $(targetPane).addClass('show active');
                }
                
                // Update the tab's aria-selected attributes
                $('.nav-tabs .nav-link').not(tabLink).attr('aria-selected', 'false');
                
                return true;
            }
            return false;
        }

        // Handle tab switching with persistence
        $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            const targetId = $(e.target).attr('id');
            // Save the active tab ID
            saveActiveTab(targetId);
        });

        // Also save when tab is clicked (before shown event)
        $('a[data-toggle="tab"]').on('click', function (e) {
            const targetId = $(this).attr('id');
            saveActiveTab(targetId);
        });

        // Restore active tab on page load - with a slight delay to ensure DOM is ready
        setTimeout(function() {
            const savedTabId = getSavedActiveTab();
            
            if (savedTabId) {
                // Try to activate the saved tab
                const tabActivated = activateTab(savedTabId);
                
                // If the saved tab doesn't exist, fall back to first tab
                if (!tabActivated) {
                    const firstTab = $('.nav-tabs .nav-link:first');
                    if (firstTab.length) {
                        activateTab(firstTab.attr('id'));
                        saveActiveTab(firstTab.attr('id'));
                    }
                }
            } else {
                // No saved tab, activate the first tab
                const firstTab = $('.nav-tabs .nav-link:first');
                if (firstTab.length) {
                    activateTab(firstTab.attr('id'));
                    saveActiveTab(firstTab.attr('id'));
                }
            }
        }, 100);

        // Add Subject button click handler
        $('.btn-add-subject').on('click', function () {
            const classType = $(this).data('class-type');
            const classLabel = $(this).data('class-label');
            
            console.log('Add Subject - Class Type:', classType, 'Class Label:', classLabel); // Debug log
            
            $('#subjectModalTitle').text('Add Subject');
            $('#subject_id').val('');
            $('#subject_class_type').val(classType);
            $('#subject_class_label').val(classLabel); // Set the category label
            $('#subject_name').val('');
            $('#subject_code').val('');
            $('#active-toggle-wrapper').hide();
            $('#subjectForm')[0].reset();
            
            // Make sure the category field is properly set after reset
            $('#subject_class_label').val(classLabel);
            
            modal.show();
        });

        // Edit Subject button click handler
        $(document).on('click', '.btn-edit-subject', function () {
            const subjectId = $(this).data('id');
            const subjectName = $(this).data('name');
            const subjectCode = $(this).data('code');
            const isActive = $(this).data('active');

            // Get the class type and label from the parent card
            const parentCard = $(this).closest('.subject-tab-card');
            const classType = parentCard.find('.btn-add-subject').data('class-type');
            const classLabel = parentCard.find('.btn-add-subject').data('class-label');
            
            console.log('Edit Subject - Class Type:', classType, 'Class Label:', classLabel); // Debug log

            $('#subjectModalTitle').text('Edit Subject');
            $('#subject_id').val(subjectId);
            $('#subject_name').val(subjectName);
            $('#subject_code').val(subjectCode || '');
            $('#subject_is_active').prop('checked', isActive == 1);
            $('#active-toggle-wrapper').show();
            $('#subject_class_type').val(classType);
            $('#subject_class_label').val(classLabel); // Set the category label

            modal.show();
        });

        // Form submission handler with SweetAlert confirmation
        $('#subjectForm').on('submit', function (e) {
            e.preventDefault();

            const id = $('#subject_id').val();
            const isEdit = !!id;
            const subjectName = $('#subject_name').val();
            
            // Show confirmation dialog before submission
            const actionText = isEdit ? 'update' : 'add';
            const confirmTitle = isEdit ? 'Update Subject?' : 'Add New Subject?';
            const confirmText = isEdit ? 
                `Are you sure you want to update "<strong>${subjectName}</strong>"?` : 
                `Are you sure you want to add "<strong>${subjectName}</strong>"?`;

            Swal.fire({
                title: confirmTitle,
                html: confirmText,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: `Yes, ${actionText} it`,
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Proceed with the form submission
                    const payload = {
                        subject_name: $('#subject_name').val(),
                        subject_code: $('#subject_code').val(),
                        _token: '{{ csrf_token() }}'
                    };

                    if (!isEdit) {
                        payload.class_type = $('#subject_class_type').val();
                    } else {
                        payload.is_active = $('#subject_is_active').is(':checked') ? 1 : 0;
                        payload._method = 'PUT';
                    }

                    Swal.fire({
                        title: 'Saving...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    $.ajax({
                        url: isEdit ? `/custom-subjects/${id}` : '/custom-subjects',
                        method: 'POST',
                        data: payload,
                        dataType: 'json',
                        success: function (response) {
                            modal.hide();
                            // Show success alert and keep it until user clicks OK
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: response.message || 'Subject saved successfully!',
                                showConfirmButton: true,
                                confirmButtonText: 'OK',
                                allowOutsideClick: false,
                                allowEscapeKey: false
                            }).then((result) => {
                                // Only reload after user clicks OK
                                if (result.isConfirmed) {
                                    location.reload();
                                }
                            });
                        },
                        error: function (xhr) {
                            Swal.close();
                            let errorMessage = 'Something went wrong.';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                                const errors = Object.values(xhr.responseJSON.errors).flat();
                                errorMessage = errors.join('\n');
                            }
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: errorMessage,
                                confirmButtonColor: '#d33',
                                confirmButtonText: 'OK'
                            });
                        }
                    });
                }
            });
        });

        // Delete Subject handler with SweetAlert confirmation
        $(document).on('click', '.btn-delete-subject', function () {
            const id = $(this).data('id');
            const subjectRow = $(this).closest('.subject-row');
            const subjectName = subjectRow.find('strong').text().trim();

            // Enhanced confirmation for delete with more details
            Swal.fire({
                title: 'Delete Subject?',
                html: `Are you sure you want to delete <strong>"${subjectName}"</strong>?<br><br>
                          <small class="text-muted">If it's attached to a class, it will be deactivated instead.</small>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it',
                cancelButtonText: 'Cancel',
                allowOutsideClick: false,
                allowEscapeKey: false
            }).then((result) => {
                if (result.isConfirmed) {
                    // Second confirmation for extra safety
                    Swal.fire({
                        title: 'Are you absolutely sure?',
                        text: "This action cannot be undone!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Yes, permanently delete!',
                        cancelButtonText: 'Cancel'
                    }).then((secondResult) => {
                        if (secondResult.isConfirmed) {
                            // Show loading state
                            Swal.fire({
                                title: 'Deleting...',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });

                            $.ajax({
                                url: `/custom-subjects/${id}`,
                                method: 'POST',
                                data: {
                                    _method: 'DELETE',
                                    _token: '{{ csrf_token() }}'
                                },
                                dataType: 'json',
                                success: function (response) {
                                    // Show success alert and keep it until user clicks OK
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Deleted!',
                                        text: response.message || 'Subject deleted successfully.',
                                        showConfirmButton: true,
                                        confirmButtonText: 'OK',
                                        allowOutsideClick: false,
                                        allowEscapeKey: false
                                    }).then((result) => {
                                        // Only reload after user clicks OK
                                        if (result.isConfirmed) {
                                            location.reload();
                                        }
                                    });
                                },
                                error: function (xhr) {
                                    Swal.close();
                                    const errorMsg = xhr.responseJSON?.message || 'Failed to delete this subject.';
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: errorMsg,
                                        confirmButtonColor: '#d33',
                                        confirmButtonText: 'OK'
                                    });
                                }
                            });
                        }
                    });
                }
            });
        });

        // Handle modal hidden event to reset form
        $('#subjectModal').on('hidden.bs.modal', function () {
            $('#subjectForm')[0].reset();
            $('#subject_id').val('');
            $('#subject_class_label').val(''); // Clear the category label
            $('#active-toggle-wrapper').hide();
        });

        // Handle modal show event to set focus
        $('#subjectModal').on('shown.bs.modal', function () {
            $('#subject_name').focus();
        });

        // Check if Bootstrap is properly loaded
        if (typeof bootstrap === 'undefined') {
            console.warn('Bootstrap is not loaded properly. Please check your script includes.');
        } else {
            console.log('Bootstrap loaded successfully.');
        }
    });
</script>
@endsection