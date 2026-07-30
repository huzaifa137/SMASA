@extends('layouts-side-bar.master')

@section('content')
    <div class="side-app">
        <style>
            .preview-card {
                background: white;
                border-radius: 10px;
                padding: 20px;
                margin-bottom: 20px;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            }

            .preview-pill {
                display: inline-block;
                background: #f1f3f5;
                border-radius: 20px;
                padding: 5px 14px;
                margin: 4px;
                font-size: 14px;
            }

            /* Custom spinner animation */
            .custom-spinner {
                display: inline-block;
                width: 50px;
                height: 50px;
                border: 4px solid rgba(79, 70, 229, 0.1);
                border-radius: 50%;
                border-top-color: #4f46e5;
                animation: spin 1s ease-in-out infinite;
            }

            @keyframes spin {
                to { transform: rotate(360deg); }
            }

            .progress-container {
                margin: 15px 0;
                width: 100%;
            }

            .progress-steps {
                display: flex;
                justify-content: space-between;
                margin-bottom: 10px;
            }

            .step-item {
                display: flex;
                flex-direction: column;
                align-items: center;
                flex: 1;
                position: relative;
            }

            .step-item .step-circle {
                width: 30px;
                height: 30px;
                border-radius: 50%;
                background: #e5e7eb;
                display: flex;
                align-items: center;
                justify-content: center;
                font-weight: bold;
                font-size: 12px;
                color: #6b7280;
                transition: all 0.3s ease;
                position: relative;
                z-index: 2;
            }

            .step-item.active .step-circle {
                background: #4f46e5;
                color: white;
                transform: scale(1.1);
            }

            .step-item.completed .step-circle {
                background: #10b981;
                color: white;
            }

            .step-item .step-line {
                position: absolute;
                top: 15px;
                left: -50%;
                width: 100%;
                height: 2px;
                background: #e5e7eb;
                z-index: 1;
            }

            .step-item:first-child .step-line {
                display: none;
            }

            .step-item .step-label {
                font-size: 11px;
                color: #6b7280;
                margin-top: 5px;
                text-align: center;
            }

            .step-item.active .step-label {
                color: #4f46e5;
                font-weight: 600;
            }

            .step-item.completed .step-label {
                color: #10b981;
            }

            .step-item .step-line.completed {
                background: #10b981;
            }
        </style>

        <div class="row">
            <div class="col-12">
                <div class="card bg-primary">
                    @include('layouts.class-buttons')
                    <div class="card-body bg-light">

                        <h4 class="mb-3">Switch to Your Own Subject List</h4>

                        <p>
                            Right now your classes use a shared, system-wide subject list. Switching lets you
                            rename, add, or remove subjects freely, without affecting any other school.
                        </p>

                        <p>
                            <strong>Nothing you already have will be lost.</strong> Below is a preview of the
                            subjects currently attached to your classes &mdash; these will be copied into your own
                            list automatically, exactly as named today. You can rename or remove any of them
                            afterwards.
                        </p>

                        @if ($preview->isEmpty())
                            <div class="preview-card">
                                <p class="text-muted mb-0">
                                    You don't have any classes with subjects attached yet, so there's nothing to
                                    copy over. You can switch now and start adding your own subjects straight away.
                                </p>
                            </div>
                        @else
                            @foreach ($preview as $subjectType => $rows)
                                <div class="preview-card">
                                    <h6 class="mb-2 text-uppercase text-muted">{{ str_replace('_', ' ', $subjectType) }}</h6>
                                    @foreach ($rows as $row)
                                        <span class="preview-pill">{{ $row['name'] }}</span>
                                    @endforeach
                                </div>
                            @endforeach
                        @endif

                        <form id="confirmSwitchForm" action="{{ route('school.custom-subjects.confirm') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="fas fa-check"></i> Confirm &amp; Switch Now
                            </button>
                            <a href="{{ route('school.create-class') }}" class="btn btn-outline-secondary">Cancel</a>
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
            $('#confirmSwitchForm').on('submit', function (e) {
                e.preventDefault();
                const $form = $(this);
                const $submitBtn = $('#submitBtn');

                // Show confirmation dialog
                Swal.fire({
                    title: 'Switch to your own subjects?',
                    html: `
                        <p style="color: #6b7280; font-size: 14px;">
                            This will carry over your current subject names and cannot be undone from here.
                        </p>
                        <div style="background: #fef3c7; border-radius: 8px; padding: 10px; margin-top: 10px; border-left: 4px solid #d97706;">
                            <small style="color: #92400e;">
                                <i class="fas fa-info-circle"></i>
                                You can rename or remove any subject after switching.
                            </small>
                        </div>
                    `,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#4f46e5',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Yes, switch now',
                    cancelButtonText: 'Cancel',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Show progress dialog with custom spinner
                        Swal.fire({
                            title: 'Switching to Custom Subjects...',
                            html: `
                                <div style="margin: 20px 0;">
                                    <div class="custom-spinner"></div>
                                </div>
                                <div class="progress-container">
                                    <div class="progress-steps">
                                        <div class="step-item active" id="step1">
                                            <div class="step-circle">1</div>
                                            <div class="step-label">Copying Subjects</div>
                                        </div>
                                        <div class="step-item" id="step2">
                                            <div class="step-line"></div>
                                            <div class="step-circle">2</div>
                                            <div class="step-label">Enabling Custom List</div>
                                        </div>
                                        <div class="step-item" id="step3">
                                            <div class="step-line"></div>
                                            <div class="step-circle">3</div>
                                            <div class="step-label">Finalizing</div>
                                        </div>
                                    </div>
                                    <div class="progress" style="height: 6px; background: #e5e7eb; border-radius: 3px; overflow: hidden;">
                                        <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                             role="progressbar" 
                                             style="width: 0%; background: linear-gradient(90deg, #4f46e5, #7c3aed);"
                                             aria-valuenow="0" 
                                             aria-valuemin="0" 
                                             aria-valuemax="100">
                                        </div>
                                    </div>
                                    <p style="color: #6b7280; font-size: 13px; margin-top: 10px;" id="progressStatus">
                                        <i class="fas fa-spinner fa-spin"></i> Copying subjects...
                                    </p>
                                </div>
                            `,
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            showConfirmButton: false,
                            didOpen: () => {
                                // Animate progress
                                let progress = 0;
                                const progressBar = document.querySelector('.progress-bar');
                                const statusText = document.getElementById('progressStatus');

                                // Step 1: Copying subjects (0-40%)
                                setTimeout(() => {
                                    progress = 20;
                                    progressBar.style.width = '20%';
                                    progressBar.setAttribute('aria-valuenow', 20);
                                }, 500);

                                setTimeout(() => {
                                    progress = 40;
                                    progressBar.style.width = '40%';
                                    progressBar.setAttribute('aria-valuenow', 40);
                                    document.getElementById('step1').classList.remove('active');
                                    document.getElementById('step1').classList.add('completed');
                                    document.getElementById('step1').querySelector('.step-circle').innerHTML = '<i class="fas fa-check"></i>';
                                    document.getElementById('step2').classList.add('active');
                                    statusText.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enabling custom subject list...';
                                }, 1500);

                                // Step 2: Enabling custom list (40-70%)
                                setTimeout(() => {
                                    progress = 60;
                                    progressBar.style.width = '60%';
                                    progressBar.setAttribute('aria-valuenow', 60);
                                }, 2500);

                                setTimeout(() => {
                                    progress = 70;
                                    progressBar.style.width = '70%';
                                    progressBar.setAttribute('aria-valuenow', 70);
                                    document.getElementById('step2').classList.remove('active');
                                    document.getElementById('step2').classList.add('completed');
                                    document.getElementById('step2').querySelector('.step-circle').innerHTML = '<i class="fas fa-check"></i>';
                                    document.getElementById('step3').classList.add('active');
                                    statusText.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Finalizing...';
                                }, 3500);

                                // Step 3: Finalizing (70-100%)
                                setTimeout(() => {
                                    progress = 85;
                                    progressBar.style.width = '85%';
                                    progressBar.setAttribute('aria-valuenow', 85);
                                }, 4500);

                                setTimeout(() => {
                                    progress = 100;
                                    progressBar.style.width = '100%';
                                    progressBar.setAttribute('aria-valuenow', 100);
                                    document.getElementById('step3').classList.remove('active');
                                    document.getElementById('step3').classList.add('completed');
                                    document.getElementById('step3').querySelector('.step-circle').innerHTML = '<i class="fas fa-check"></i>';
                                    statusText.innerHTML = '<i class="fas fa-check-circle" style="color: #10b981;"></i> Complete!';
                                }, 5500);
                            }
                        });

                        // Disable the submit button
                        $submitBtn.prop('disabled', true);
                        $submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Processing...');

                        // Submit the form after a short delay to show the progress
                        setTimeout(() => {
                            // Submit the form programmatically
                            $form.off('submit').submit();
                        }, 1000);
                    }
                });
            });

            // Handle form submission success (if you want to show a success message after redirect)
            @if(session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: '{{ session('success') }}',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#4f46e5',
                    allowOutsideClick: false,
                    allowEscapeKey: false
                }).then(() => {
                    // Optional: reload or redirect after OK
                });
            @endif

            @if(session('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: '{{ session('error') }}',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#dc2626'
                });
            @endif
        });
    </script>
@endsection