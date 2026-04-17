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
    <!-- Student Dashboard -->
    <div class="side-app">

        <style>
            .form-check-input {
                transform: scale(1.5);
                margin-right: 10px;
            }

            .form-check-label {
                line-height: 1.5;
            }
        </style>

        <style>
    .form-check-input {
        transform: scale(1.5);
        margin-right: 10px;
    }

    .form-check-label {
        line-height: 1.5;
    }
    
    /* Button group styling */
    .subject-control-buttons {
        margin-bottom: 15px;
        padding: 10px;
        background: #f8f9fa;
        border-radius: 8px;
        display: inline-block;
    }
    
    .btn-check-all, .btn-uncheck-all {
        padding: 5px 15px;
        margin-right: 10px;
        border-radius: 5px;
        font-size: 13px;
        transition: all 0.3s ease;
    }
    
    .btn-check-all {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        border: none;
        color: white;
    }
    
    .btn-check-all:hover {
        background: linear-gradient(135deg, #218838 0%, #1ba87e 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    
    .btn-uncheck-all {
        background: linear-gradient(135deg, #dc3545 0%, #f86c6b 100%);
        border: none;
        color: white;
    }
    
    .btn-uncheck-all:hover {
        background: linear-gradient(135deg, #c82333 0%, #e05a59 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    
    .subject-section-card {
        background: white;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .section-title {
        font-size: 18px;
        font-weight: 600;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #e9ecef;
        color: #495057;
    }
</style>

        <!-- HTML -->
        <div class="row">
            <div class="col-lg-12 col-xl-12 col-md-12 col-sm-12">
                <div class="card bg-primary">
                     @include('layouts.class-buttons')
                    <div class="card-body bg-light">
                        <form id="createClassForm">
                            <div class="row">
                                <div class="col-lg-6 col-md-12">
                                    <div class="form-group">
                                        <label class="form-label">Senior</label>
                                        <select class="form-control select2" id="class_id" name="class_id">
                                            <option value="">-- Select --</option>
                                            @foreach ($SecondaryClasses as $class)
                                                @php
                                                    // Determine if this is O-Level or A-Level
                                                    // You'll need to adjust this logic based on your data
                                                    $classType = str_contains($class->md_name, 'A-Level') ? 'A-Level' : 'O-Level';
                                                @endphp
                                                <option value="{{ $class->md_id }}" data-type="{{ $classType }}">
                                                    {{ $class->md_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-6 col-md-12">
                                    <div class="form-group">
                                        <label class="form-label">Stream</label>
                                        <?php
    // echo Helper::DropMasterData(config('constants.options.CLASS_STREAMS'), '', 'class_stream', 1);
    //                                   ?>

                                      <input type="text" name="class_stream" id="class_stream" class="form-control" value="{{ old('class_stream') }}" 
                                                placeholder="Enter Class Stream">
    
                                    </div>
                                </div>
                            </div>

<!-- IDAAD Subjects (O-Level) - Hidden by default -->
<div id="idaad-subjects" style="display: none;">
    <div class="subject-section-card">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="section-title mb-0">O - LEVEL SUBJECTS (IDAAD)</h5>
            <div class="subject-control-buttons">
                <button type="button" class="btn btn-sm btn-check-all" onclick="checkAllIdaadSubjects()">
                    <i class="fas fa-check-double"></i> Check All
                </button>
                <button type="button" class="btn btn-sm btn-uncheck-all" onclick="uncheckAllIdaadSubjects()">
                    <i class="fas fa-times-circle"></i> Uncheck All
                </button>
            </div>
        </div>
        
        <div class="row">
            <div class="col-lg-4 col-md-12">
                <label class="form-label"><strong>Arabic Subjects</strong></label>
                @foreach ($IDAAD_ARABIC_LANGUAGE as $subject)
                    <div class="form-check">
                        <input class="form-check-input idaad-subject" type="checkbox"
                            id="idaad-arabic-{{ $loop->index }}" value="{{$subject->md_id}}" data-group="idaad">
                        <label class="form-check-label"
                            for="idaad-arabic-{{ $loop->index }}">{{$subject->md_name}}</label>
                    </div>
                @endforeach
            </div>

            <div class="col-lg-4 col-md-12">
                <label class="form-label"><strong>Faith & Civilization</strong></label>
                @foreach ($IDAAD_FAITH_AND_CIVILIZATION as $subject)
                    <div class="form-check">
                        <input class="form-check-input idaad-subject" type="checkbox"
                            id="idaad-faith-{{ $loop->index }}" value="{{$subject->md_id}}" data-group="idaad">
                        <label class="form-check-label"
                            for="idaad-faith-{{ $loop->index }}">{{$subject->md_name}}</label>
                    </div>
                @endforeach
            </div>

            <div class="col-lg-4 col-md-12">
                <label class="form-label"><strong>Jurisprudence & Its Sources</strong></label>
                @foreach ($IDAAD_JURISPRUDENCE_AND_ITS_SOURCES as $subject)
                    <div class="form-check">
                        <input class="form-check-input idaad-subject" type="checkbox"
                            id="idaad-jurisprudence-{{ $loop->index }}" value="{{$subject->md_id}}" data-group="idaad">
                        <label class="form-check-label"
                            for="idaad-jurisprudence-{{ $loop->index }}">{{$subject->md_name}}</label>
                    </div>
                @endforeach
            </div>
        </div>

        <br>

        <div class="row">
            <div class="col-lg-4 col-md-12">
                <label class="form-label"><strong>Quran And Its Sciences</strong></label>
                @foreach ($IDAAD_QURAN_ITS_SCIENCES as $subject)
                    <div class="form-check">
                        <input class="form-check-input idaad-subject" type="checkbox"
                            id="idaad-quran-{{ $loop->index }}" value="{{$subject->md_id}}" data-group="idaad">
                        <label class="form-check-label"
                            for="idaad-quran-{{ $loop->index }}">{{$subject->md_name}}</label>
                    </div>
                @endforeach
            </div>

            <div class="col-lg-4 col-md-12">
                <label class="form-label"><strong>Prophetic Traditions</strong></label>
                @foreach ($IDAAD_PROPHETIC_TRADITIONS as $subject)
                    <div class="form-check">
                        <input class="form-check-input idaad-subject" type="checkbox"
                            id="idaad-prophetic-{{ $loop->index }}" value="{{$subject->md_id}}" data-group="idaad">
                        <label class="form-check-label"
                            for="idaad-prophetic-{{ $loop->index }}">{{$subject->md_name}}</label>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<!-- THANAWI Subjects (A-Level) - Hidden by default -->
<!-- THANAWI Subjects (A-Level) - Hidden by default -->
<div id="thanawi-subjects" style="display: none;">
    <div class="subject-section-card">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="section-title mb-0">A - LEVEL SUBJECTS (THANAWI)</h5>
            <div class="subject-control-buttons">
                <button type="button" class="btn btn-sm btn-check-all" onclick="checkAllThanawiSubjects()">
                    <i class="fas fa-check-double"></i> Check All
                </button>
                <button type="button" class="btn btn-sm btn-uncheck-all" onclick="uncheckAllThanawiSubjects()">
                    <i class="fas fa-times-circle"></i> Uncheck All
                </button>
            </div>
        </div>
        
        <div class="row">
            <div class="col-lg-4 col-md-12">
                <label class="form-label"><strong>Arabic Subjects</strong></label>
                @foreach ($THANAWI_ARABIC_LANGUAGE as $subject)
                    <div class="form-check">
                        <input class="form-check-input thanawi-subject" type="checkbox"
                            id="thanawi-arabic-{{ $loop->index }}" value="{{$subject->md_id}}" data-group="thanawi">
                        <label class="form-check-label"
                            for="thanawi-arabic-{{ $loop->index }}">{{$subject->md_name}}</label>
                    </div>
                @endforeach
            </div>

            <div class="col-lg-4 col-md-12">
                <label class="form-label"><strong>Faith & Civilization</strong></label>
                @foreach ($THANAWI_FAITH_AND_CIVILIZATION as $subject)
                    <div class="form-check">
                        <input class="form-check-input thanawi-subject" type="checkbox"
                            id="thanawi-faith-{{ $loop->index }}" value="{{$subject->md_id}}" data-group="thanawi">
                        <label class="form-check-label"
                            for="thanawi-faith-{{ $loop->index }}">{{$subject->md_name}}</label>
                    </div>
                @endforeach
            </div>

            <div class="col-lg-4 col-md-12">
                <label class="form-label"><strong>Jurisprudence & Its Sources</strong></label>
                @foreach ($THANAWI_JURISPRUDENCE_AND_ITS_SOURCES as $subject)
                    <div class="form-check">
                        <input class="form-check-input thanawi-subject" type="checkbox"
                            id="thanawi-jurisprudence-{{ $loop->index }}" value="{{$subject->md_id}}" data-group="thanawi">
                        <label class="form-check-label"
                            for="thanawi-jurisprudence-{{ $loop->index }}">{{$subject->md_name}}</label>
                    </div>
                @endforeach
            </div>
        </div>

        <br>

        <div class="row">
            <div class="col-lg-4 col-md-12">
                <label class="form-label"><strong>Quran And Its Sciences</strong></label>
                @foreach ($THANAWI_QURAN_ITS_SCIENCES as $subject)
                    <div class="form-check">
                        <input class="form-check-input thanawi-subject" type="checkbox"
                            id="thanawi-quran-{{ $loop->index }}" value="{{$subject->md_id}}" data-group="thanawi">
                        <label class="form-check-label"
                            for="thanawi-quran-{{ $loop->index }}">{{$subject->md_name}}</label>
                    </div>
                @endforeach
            </div>

            <div class="col-lg-4 col-md-12">
                <label class="form-label"><strong>Prophetic Traditions</strong></label>
                @foreach ($THANAWI_PROPHETIC_TRADITIONS as $subject)
                    <div class="form-check">
                        <input class="form-check-input thanawi-subject" type="checkbox"
                            id="thanawi-prophetic-{{ $loop->index }}" value="{{$subject->md_id}}" data-group="thanawi">
                        <label class="form-check-label"
                            for="thanawi-prophetic-{{ $loop->index }}">{{$subject->md_name}}</label>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

    <br>

 <div class="row">
                            <div class="mt-4 text-left">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane"></i> Submit
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

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>

    // Functions for IDAAD subjects (O-Level)
function checkAllIdaadSubjects() {
    $('.idaad-subject').prop('checked', true);
    showToast('All O-Level subjects have been selected', 'success');
}

function uncheckAllIdaadSubjects() {
    $('.idaad-subject').prop('checked', false);
    showToast('All O-Level subjects have been deselected', 'info');
}

// Functions for THANAWI subjects (A-Level)
function checkAllThanawiSubjects() {
    $('.thanawi-subject').prop('checked', true);
    showToast('All A-Level subjects have been selected', 'success');
}

function uncheckAllThanawiSubjects() {
    $('.thanawi-subject').prop('checked', false);
    showToast('All A-Level subjects have been deselected', 'info');
}

// Toast notification function
function showToast(message, type = 'success') {
    Swal.fire({
        icon: type,
        title: message,
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 2000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer);
            toast.addEventListener('mouseleave', Swal.resumeTimer);
        }
    });
}

$(document).ready(function () {
    // Use the class type map passed from controller
    let classTypes = @json($classTypeMap);
    
    // Initially hide both subject sections
    $('#idaad-subjects').hide();
    $('#thanawi-subjects').hide();
    
    // Handle class selection change
    $('#class_id').on('change', function() {
        let selectedClassId = $(this).val();
        
        if (!selectedClassId) {
            $('#idaad-subjects').hide();
            $('#thanawi-subjects').hide();
            return;
        }
        
        let classType = classTypes[selectedClassId];
        console.log('Selected class:', selectedClassId, 'Type:', classType);
        
        if (classType === 'O-Level') {
            $('#idaad-subjects').show();
            $('#thanawi-subjects').hide();
            // Uncheck all thanawi subjects
            $('.thanawi-subject').prop('checked', false);
        } else if (classType === 'A-Level') {
            $('#idaad-subjects').hide();
            $('#thanawi-subjects').show();
            // Uncheck all idaad subjects
            $('.idaad-subject').prop('checked', false);
        }
    });
    
    // Form submission handler
    $('#createClassForm').on('submit', function(e) {
        e.preventDefault();

        let isValid = true;
        let $form = $(this);
        let $submitBtn = $form.find('button[type="submit"]');

        // Remove existing validation styles
        $form.find('.form-control, select').removeClass('is-invalid');
        $form.find('.invalid-feedback').remove();

        // Validate class selection
        if (!$('#class_id').val()) {
            $('#class_id').addClass('is-invalid');
            if ($('#class_id').next('.invalid-feedback').length === 0) {
                $('#class_id').after('<div class="invalid-feedback d-block">Please select a class.</div>');
            }
            isValid = false;
        }

        // Validate stream input
        if (!$('#class_stream').val().trim()) {
            $('#class_stream').addClass('is-invalid');
            if ($('#class_stream').next('.invalid-feedback').length === 0) {
                $('#class_stream').after('<div class="invalid-feedback d-block">Please enter class stream.</div>');
            }
            isValid = false;
        }

        // Collect subjects based on selected class type
        let selectedClassId = $('#class_id').val();
        let classType = classTypes[selectedClassId];
        
        let selectedSubjects = [];
        
        if (classType === 'O-Level') {
            $('.idaad-subject:checked').each(function() {
                selectedSubjects.push($(this).val());
            });
        } else if (classType === 'A-Level') {
            $('.thanawi-subject:checked').each(function() {
                selectedSubjects.push($(this).val());
            });
        }

        // Check if form is valid
        if (!isValid) {
            Swal.fire({
                icon: 'error',
                title: 'Incomplete Form',
                text: 'Please fill in all required fields before submitting.'
            });
            return;
        }

        // Check if subjects are selected
        if (selectedSubjects.length === 0) {
            Swal.fire({
                icon: 'error',
                title: 'No Subjects Selected',
                text: 'Please select at least one subject before submitting.'
            });
            return;
        }

        // Confirm submission
        Swal.fire({
            title: 'Are you sure?',
            text: "You are about to create this class.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, submit it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // Disable submit button and show loading state
                $submitBtn.prop('disabled', true);
                const originalBtnHtml = $submitBtn.html();
                $submitBtn.html('Saving... <i class="fas fa-spinner fa-spin"></i>');

                // Prepare data to send
                let dataToSend = {
                    class_id: $('#class_id').val(),
                    class_stream: $('#class_stream').val(),
                    subjects: selectedSubjects,
                    class_type: classType,
                    _token: '{{ csrf_token() }}'
                };

                // Send AJAX request
                $.ajax({
                    url: '{{ route('schools.class-store') }}',
                    method: 'POST',
                    data: JSON.stringify(dataToSend),
                    contentType: 'application/json',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.fail) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message || 'Something went wrong.'
                            });
                        } else {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: response.message || 'Class has been created successfully.',
                                timer: 2000,
                                showConfirmButton: false
                            });
                            
                            // Reset the form
                            $form[0].reset();
                            $('input[type="checkbox"]').prop('checked', false);
                            $('#idaad-subjects').hide();
                            $('#thanawi-subjects').hide();
                            $('.is-invalid').removeClass('is-invalid');
                            $('.invalid-feedback').remove();
                        }
                    },
                    error: function(xhr) {
                        let errorMessage = 'An unexpected error occurred.';
                        
                        if (xhr.responseJSON) {
                            if (xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            }
                            
                            // Handle validation errors
                            if (xhr.responseJSON.errors) {
                                let validationErrors = '';
                                for (const field in xhr.responseJSON.errors) {
                                    validationErrors += xhr.responseJSON.errors[field][0] + '\n';
                                }
                                errorMessage = validationErrors;
                            }
                        }
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Submission Error',
                            text: errorMessage
                        });
                    },
                    complete: function() {
                        // Re-enable submit button
                        $submitBtn.prop('disabled', false).html(originalBtnHtml);
                    }
                });
            }
        });
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