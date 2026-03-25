{{-- resources/views/itemGrading/grading-results.blade.php --}}
@extends('layouts-side-bar.master')

{{-- Move all CSS to the top --}}
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">
<!-- Font Awesome 6 Free (CSS) -->
<!-- Font Awesome 5 CDN -->
<!-- Font Awesome 6 CDN -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

@php
    use App\Models\StudentBasic;
    use App\Http\Controllers\Helper;
@endphp

@section('content')
    <div class="side-app">
        <div class="container mt-4">

            {{-- Header Card --}}
            <div class="card shadow-lg border-0 mb-4">
                <div class="card-header text-white d-flex justify-content-between align-items-center"
                    style="background-color: #28a745;">
                    <h4 class="mb-0">
                        <i class="fas fa-star me-2"></i> Grading Results :
                        {{ $schoolName }} - {{ $category }} - {{ $year }}
                    </h4>

                    @if ($level)
                        <span class="badge badge-light text-dark">
                            Level {{ $level }}
                        </span>
                    @endif
                </div>


                <div class="card-body">
                    {{-- Statistics Cards --}}
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h6>Total Students</h6>
                                    <h3>{{ $statistics['count'] }}</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h6>Average Percentage</h6>
                                    <h3>{{ $statistics['average'] }}%</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <h6>Highest Score</h6>
                                    <h3>{{ $statistics['highest'] }}%</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-danger text-white">
                                <div class="card-body">
                                    <h6>Lowest Score</h6>
                                    <h3>{{ $statistics['lowest'] }}%</h3>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Distribution Charts Row --}}
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-info text-white">
                                    <h6 class="mb-0">Grade Distribution (Marks)</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Grade</th>
                                                    <th>Count</th>
                                                    <th>Percentage</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($statistics['grade_distribution'] as $grade => $count)
                                                    <tr>
                                                        <td><strong>{{ $grade }}</strong></td>
                                                        <td>{{ $count }}</td>
                                                        <td>
                                                            @if ($statistics['count'] > 0)
                                                                {{ round(($count / $statistics['count']) * 100, 1) }}%
                                                            @else
                                                                0%
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-success text-white">
                                    <h6 class="mb-0">Classification Distribution (Points)</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Classification</th>
                                                    <th>Count</th>
                                                    <th>Percentage</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach (array_reverse($statistics['class_distribution'], true) as $class => $count)
                                                    <tr>
                                                        <td><strong>{{ $class }}</strong></td>
                                                        <td>{{ $count }}</td>
                                                        <td>
                                                            @if ($statistics['count'] > 0)
                                                                {{ round(($count / $statistics['count']) * 100, 1) }}%
                                                            @else
                                                                0%
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Results Table --}}
                    <div class="card">
                        <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Student Results</h5>
                            <div>
                                <button class="btn btn-sm btn-light" onclick="exportToExcel()">
                                    <i class="fas fa-file-excel"></i> Export
                                </button>
                                <button class="btn btn-sm btn-light" onclick="printResults()">
                                    <i class="fas fa-print"></i> Print
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <form id="saveResultsForm" method="POST" action="{{ route('iteb.save.grading') }}">
                                @csrf
                                <input type="hidden" name="year" value="{{ $year }}">
                                <input type="hidden" name="category" value="{{ $category }}">
                                <input type="hidden" name="school_number" value="{{ $schoolNumber }}">
                                <input type="hidden" name="level" value="{{ $level }}">

                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered" id="resultsTable">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Index Number</th>
                                                <th>Student Name</th>
                                                <th>School</th>
                                                <td>Gender</th>
                                                <th>Grade</th>
                                                <th>Percentage</th>
                                                <th>Action</th>
                                            </tr>
                                        <!-- </thead> -->
                                        <tbody>
                                            @foreach ($results as $studentId => $result)

                                                            <?php 
                                                            $StudentSex = StudentBasic::where('Student_ID', $studentId)->value('StudentSex');
                                                            ?>
                                                            <tr>
                                                                <td>{{ $loop->iteration }}</td>
                                                                <td>
                                                                    {{ $studentId }}
                                                                    <input type="hidden" name="results[{{ $studentId }}][total_marks]"
                                                                        value="{{ $result['total_marks'] }}">
                                                                    <input type="hidden" name="results[{{ $studentId }}][percentage]"
                                                                        value="{{ $result['percentage'] }}">
                                                                    <input type="hidden" name="results[{{ $studentId }}][grade]"
                                                                        value="{{ $result['grade'] }}">
                                                                    <input type="hidden" name="results[{{ $studentId }}][classification]"
                                                                        value="{{ $result['classification'] }}">
                                                                </td>
                                                                <td>{{ Helper::parseStudentId($studentId, 'student') }}</td>
                                                                <td>{{ Helper::parseStudentId($studentId, 'school') }}</td>
                                                                <td class="text-center">
                                                                    @if(strtolower($StudentSex) == 'male')
                                                                        <span class="badge bg-info text-white">♂ Male</span>
                                                                    @else
                                                                        <span class="badge bg-danger text-white">♀ Female</span>
                                                                    @endif
                                                                </td>
                                                                <td>
                                                                    <span
                                                                        class="badge 
                                                                            @if ($result['percentage'] >= 80) bg-success
                                                                            @elseif($result['percentage'] >= 70) bg-primary text-white
                                                                            @elseif($result['percentage'] >= 60) bg-info
                                                                            @elseif($result['percentage'] >= 50) bg-warning
                                                                            @else bg-danger @endif
                                                                         ">
                                                                        {{ $result['percentage'] }}%
                                                                    </span>
                                                                </td>
                                                                <td>
                                                                    <strong>{{ $result['grade'] }}</strong>
                                                                    <small class="d-block text-muted">{{ $result['grade_comment'] }}</small>
                                                                </td>

                                                                {{-- Update your button in the table --}}
                                                                <td>
                                                                    <button type="button" class="btn btn-sm btn-info view-details"
                                                                        data-bs-toggle="modal" data-bs-target="#studentDetailsModal"
                                                                        data-student-id="{{ $studentId }}"
                                                                        data-marks-details='{{ json_encode($result['marks_details']) }}'
                                                                        data-total-marks="{{ $result['total_marks'] }}"
                                                                        data-total-possible="{{ $result['total_possible'] }}"
                                                                        data-percentage="{{ $result['percentage'] }}"
                                                                        data-grade="{{ $result['grade'] }}"
                                                                        data-grade-comment="{{ $result['grade_comment'] }}"
                                                                        data-classification="{{ $result['classification'] }}"
                                                                        data-classification-comment="{{ $result['classification_comment'] }}">
                                                                        <i class="fas fa-eye"></i>
                                                                    </button>
                                                                </td>
                                                            </tr>
                                                </div>
                                            @endforeach
                                </tbody>
                                </table>
                                @foreach ($results as $studentId => $result)
                                    <div class="modal fade" id="studentDetailsModal" tabindex="-1"
                                        aria-labelledby="studentDetailsModalLabel" aria-hidden="true">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header bg-info text-white">
                                                    <h5 class="modal-title" id="studentDetailsModalLabel">Student Details
                                                    </h5>
                                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </div>
                                                <div class="modal-body" id="modalContent">
                                                    Loading...
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                        <i class="fas fa-times me-1"></i> Close
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                        </div>
                        </form>

                        <div class="row mt-3">
                            <div class="col-12 text-center">
                                <button class="btn btn-success btn-lg" onclick="saveResults()">
                                    <i class="fas fa-save me-2"></i> Save Grading Results
                                </button>
                                <a href="{{ route('iteb.grading.summary') }}" class="btn btn-secondary btn-lg">
                                    <i class="fas fa-arrow-left me-2"></i> Back to Filters
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>

    {{-- Student Details Modal --}}
    <div class="modal fade" id="studentDetailsModal" tabindex="-1" role="dialog" aria-labelledby="studentDetailsModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="studentDetailsModalLabel">Student Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="modalContent">
                    Loading...
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fa-solid fa-xmark me-1"></i> Close
                    </button>
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
        $(document).ready(function () {
            console.log('Document ready');

            // -------------------------
            // Initialize DataTable
            // -------------------------
            if (typeof $.fn.DataTable !== 'undefined' && !$.fn.DataTable.isDataTable('#resultsTable')) {
                $('#resultsTable').DataTable({
                    pageLength: 25,
                    order: [
                        [4, 'desc']
                    ],
                    dom: 'Bfrtip',
                    buttons: [{
                        extend: 'excelHtml5',
                        text: '<i class="fas fa-file-excel"></i> Excel',
                        className: 'btn btn-success btn-sm',
                        title: 'Grading_Results_{{ $schoolName }}_{{ $category }}_{{ $year }}',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6]
                        }
                    },
                    {
                        extend: 'print',
                        text: '<i class="fas fa-print"></i> Print',
                        className: 'btn btn-info btn-sm',
                        title: 'Grading Results - {{ $schoolName }} - {{ $category }} - {{ $year }}'
                    }
                    ]
                });
            }

            // -------------------------
            // View Details Modal
            // -------------------------
            $('.view-details').on('click', function () {
                const studentId = $(this).data('student-id');
                const marksDetails = $(this).data('marks-details');
                const totalMarks = $(this).data('total-marks');
                const totalPossible = $(this).data('total-possible');
                const percentage = $(this).data('percentage');
                const grade = $(this).data('grade');
                const gradeComment = $(this).data('grade-comment');
                const classification = $(this).data('classification');
                const classificationComment = $(this).data('classification-comment');

                console.log('Student ID:', studentId);
                console.log('Marks Details:', marksDetails); // Debug: see marks structure

                // Build modal content
                let modalContent = `
                                                    <h6 class="mb-3">Student Index Number : <strong>${studentId}</strong></h6>
                                                    <div class="table-responsive">
                                                        <table class="table table-bordered">
                                                            <tr>
                                                                <th style="width: 30%" class="text-dark">Total Marks</th>
                                                                <td>${totalMarks} / ${totalPossible}</td>
                                                            </tr>
                                                            <tr>
                                                                <th class="text-dark">Percentage</th>
                                                                <td>${percentage}%</td>
                                                            </tr>
                                                            <tr>
                                                                <th class="text-dark">Grade</th>
                                                                <td>${grade}</td>
                                                            </tr>
                                                            <tr>
                                                                <th class="text-dark">Grade Comment</th>
                                                                <td>${gradeComment || 'N/A'}</td>
                                                            </tr>
                                                            <tr>
                                                                <th class="text-dark">Classification</th>
                                                                <td>${classification}</td>
                                                            </tr>
                                                            <tr>
                                                                <th class="text-dark">Classification Comment</th>
                                                                <td>${classificationComment || 'N/A'}</td>
                                                            </tr>
                                                        </table>
                                                    </div>
                                                    <h6 class="mt-3">Subject Marks</h6>
                                                    <div class="table-responsive">
                                                        <table class="table table-sm table-bordered">
                                                            <thead class="table-light">
                                                                <tr>
                                                                    <th>Subject</th>
                                                                    <th>Mark</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                `;

                // Add subject marks - now using subject_name directly from the data
                if (marksDetails && marksDetails.length > 0) {
                    marksDetails.forEach(mark => {
                        // subject_name is now included directly in the mark object from the controller
                        const subjectName = mark.subject_name || 'Unknown Subject';
                        const markValue = mark.mark || 'N/A';

                        modalContent += `<tr><td>${subjectName}</td><td>${markValue}</td></tr>`;
                    });
                } else {
                    modalContent +=
                        `<tr><td colspan="2" class="text-center text-muted">No subject marks available</td></tr>`;
                }

                modalContent += `
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                `;

                // Update modal content
                $('#modalContent').html(modalContent);

                // Show modal via Bootstrap 5
                const modalEl = document.getElementById('studentDetailsModal');
                const modal = new bootstrap.Modal(modalEl);
                modal.show();

                // Clean up backdrops when modal is hidden
                modalEl.addEventListener('hidden.bs.modal', function () {
                    document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
                    document.body.classList.remove('modal-open');
                }, {
                    once: true
                });
            });

            // -------------------------
            // Save Results
            // -------------------------
            window.saveResults = function () {
                if (!confirm('Are you sure you want to save these grading results?')) return;

                const form = document.getElementById('saveResultsForm');
                const formData = new FormData(form);

                fetch('{{ route('iteb.save.grading') }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Saved!',
                                text: data.message,
                                confirmButtonText: 'OK'
                            }).then(() => location.reload());
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: data.message
                            });
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'An error occurred while saving results.'
                        });
                    });
            }

            // -------------------------
            // Export / Print
            // -------------------------
            window.exportToExcel = function () {
                if ($.fn.DataTable && $.fn.DataTable.isDataTable('#resultsTable')) {
                    $('#resultsTable').DataTable().button('.buttons-excel').trigger();
                }
            }

            window.printResults = function () {
                if ($.fn.DataTable && $.fn.DataTable.isDataTable('#resultsTable')) {
                    $('#resultsTable').DataTable().button('.buttons-print').trigger();
                }
            }
        });
    </script>
@endsection

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>