<?php
use App\Http\Controllers\Helper; 
?>

@if($students->isEmpty())
    <p class="text-danger">No students found.</p>
@else

<style>
    /* Make table container responsive with horizontal scroll */
.table-responsive-wrapper {
    width: 100%;
    overflow-x: auto;
    overflow-y: visible;
    -webkit-overflow-scrolling: touch;
    margin-bottom: 1rem;
    border-radius: 8px;
}

/* Style the table to prevent wrapping and ensure scroll works */
.table-bordered {
    min-width: 800px;
    width: 100%;
    white-space: nowrap;
}

/* Allow some cells to wrap on very small screens if needed */
@media (max-width: 576px) {
    .table-bordered td,
    .table-bordered th {
        white-space: normal;
        word-break: break-word;
        min-width: 120px;
    }
    
    .table-bordered td:first-child,
    .table-bordered th:first-child {
        min-width: 60px;
    }
    
    .table-bordered td:last-child,
    .table-bordered th:last-child {
        min-width: 100px;
    }
}

/* Style scrollbar for better UX */
.table-responsive-wrapper::-webkit-scrollbar {
    height: 8px;
    -webkit-appearance: none;
}

.table-responsive-wrapper::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.table-responsive-wrapper::-webkit-scrollbar-thumb {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 10px;
}

.table-responsive-wrapper::-webkit-scrollbar-thumb:hover {
    background: #5351e4;
}

/* Scroll indicator (optional) */
.scroll-indicator {
    text-align: center;
    padding: 8px 0;
    font-size: 0.7rem;
    color: #667eea;
    background: linear-gradient(90deg, transparent, #f0f0ff, transparent);
    margin-top: 8px;
    border-radius: 20px;
    display: none;
    animation: fadeInOut 2s ease-in-out infinite;
}

@keyframes fadeInOut {
    0%, 100% { opacity: 0.5; }
    50% { opacity: 1; }
}

/* Show indicator only on devices that need scrolling */
@media (max-width: 992px) {
    .scroll-indicator {
        display: block;
    }
}

/* Improve table readability */
.table-bordered {
    font-size: 0.85rem;
}

.table-bordered thead th {
    background: linear-gradient(135deg, #3d4bb7 0%, #3d4bb7 100%);
    color: white;
    font-weight: 600;
    padding: 12px 8px;
    white-space: nowrap;
}

.table-bordered tbody td {
    padding: 10px 8px;
    vertical-align: middle;
}

/* Make action buttons more touch-friendly on mobile */
@media (max-width: 768px) {
    .btn-sm {
        padding: 6px 12px;
        font-size: 0.75rem;
    }
    
    .table-bordered thead th,
    .table-bordered tbody td {
        padding: 8px 6px;
    }
}
</style>
<div class="table-responsive-wrapper">

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>No</th>
                <!-- <th>Admission No</th> -->
                <th>Name</th>
                <th>Class</th>
                <th>Stream</th>
                <th>Gender</th>
                <th style="text-align: center;">Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($students as $count => $student)
                <tr>
                    <td>{{ $count + 1}}</td>
                    <!-- <td>{{ $student->admission_number }}</td> -->
                    <td>{{ $student->firstname }} {{ $student->lastname }}</td>
                    <td>{{ Helper::recordMdname($student->senior) }}</td>
                    <td>{{ $student->stream }}</td>
                    <td>{{ $student->gender }}</td>
                    <td style="text-align: center;">
                        <button class="btn btn-outline-primary btn-sm view-bio-btn" data-toggle="modal"
                            data-target="#viewStudentModal" data-id="{{ $student->id }}"
                            data-firstname="{{ $student->firstname }}" data-lastname="{{ $student->lastname }}"
                            data-gender="{{ $student->gender }}" data-admission_number="{{ $student->admission_number }}"
                            data-senior="{{ Helper::recordMdname($student->senior) }}" data-stream="{{ $student->stream }}"
                            data-primary_contact="{{ $student->primary_contact }}"
                            data-other_contact="{{ $student->other_contact }}"
                            data-date_of_birth="{{ $student->date_of_birth }}" data-nationality="{{ $student->nationality }}"
                            data-guardian_names="{{ $student->guardian_names }}"
                            data-guardian_phone="{{ $student->guardian_phone }}">
                            <i class="fa fa-id-card mr-1"></i> View Bio
                        </button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="scroll-indicator">
    <i class="fas fa-chevron-left"></i> Swipe to see more columns <i class="fas fa-chevron-right"></i>
</div>

    <div class="modal fade" id="viewStudentModal" tabindex="-1" role="dialog" aria-labelledby="viewStudentModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header text-white" style="background:#5351e4;">
                    <h5 class="modal-title" id="viewStudentModalLabel">Student Information</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <dl class="row">

                        <dt class="col-sm-4">First Name</dt>
                        <dd class="col-sm-8" id="view_firstname"></dd>

                        <dt class="col-sm-4">Last Name</dt>
                        <dd class="col-sm-8" id="view_lastname"></dd>

                        <dt class="col-sm-4">Gender</dt>
                        <dd class="col-sm-8" id="view_gender"></dd>

                        <dt class="col-sm-4">Admission Number</dt>
                        <dd class="col-sm-8" id="view_admission_number"></dd>

                        <dt class="col-sm-4">Class</dt>
                        <dd class="col-sm-8" id="view_senior"></dd>

                        <dt class="col-sm-4">Stream</dt>
                        <dd class="col-sm-8" id="view_stream"></dd>

                        <dt class="col-sm-4">Primary Contact</dt>
                        <dd class="col-sm-8" id="view_primary_contact"></dd>

                        <dt class="col-sm-4">Other Contact</dt>
                        <dd class="col-sm-8" id="view_other_contact"></dd>

                        <dt class="col-sm-4">Date of Birth</dt>
                        <dd class="col-sm-8" id="view_date_of_birth"></dd>

                        <dt class="col-sm-4">Nationality</dt>
                        <dd class="col-sm-8" id="view_nationality"></dd>

                        <dt class="col-sm-4">Guardian Names</dt>
                        <dd class="col-sm-8" id="view_guardian_names"></dd>

                        <dt class="col-sm-4">Guardian Phone</dt>
                        <dd class="col-sm-8" id="view_guardian_phone"></dd>
                    </dl>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Close
                    </button>
                </div>

            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        $(document).ready(function () {
            $('#viewStudentModal').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);

                var data = {
                    id: button.data('id'),
                    firstname: button.data('firstname'),
                    lastname: button.data('lastname'),
                    gender: button.data('gender'),
                    admission_number: button.data('admission_number'),
                    senior: button.data('senior'),
                    stream: button.data('stream'),
                    primary_contact: button.data('primary_contact'),
                    other_contact: button.data('other_contact'),
                    date_of_birth: button.data('date_of_birth'),
                    nationality: button.data('nationality'),
                    guardian_names: button.data('guardian_names'),
                    guardian_phone: button.data('guardian_phone')
                };

                // Populate fields
                $('#view_id').text(data.id || '-');
                $('#view_firstname').text(data.firstname || '-');
                $('#view_lastname').text(data.lastname || '-');
                $('#view_gender').text(data.gender || '-');
                $('#view_admission_number').text(data.admission_number || '-');
                $('#view_senior').text(data.senior || '-');
                $('#view_stream').text(data.stream || '-');
                $('#view_primary_contact').text(data.primary_contact || '-');
                $('#view_other_contact').text(data.other_contact || '-');
                $('#view_date_of_birth').text(data.date_of_birth || '-');
                $('#view_nationality').text(data.nationality || '-');
                $('#view_guardian_names').text(data.guardian_names || '-');
                $('#view_guardian_phone').text(data.guardian_phone || '-');
            });
        });
    </script>
@endif