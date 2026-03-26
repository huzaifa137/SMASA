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
<link href="{{ URL::asset('assets/plugins/datatable/dataTables.bootstrap4.min.css') }}"
    rel="stylesheet" />
<!--Daterangepicker css-->
<link
    href="{{ URL::asset('assets/plugins/bootstrap-daterangepicker/daterangepicker.css') }}"
    rel="stylesheet" />
@endsection

@section('content')
<!-- Student Dashboard -->
<div class="side-app">

    <!-- HTML -->
    <div class="row">
        <div class="col-lg-12 col-xl-12 col-md-12 col-sm-12">
            <div class="card bg-primary">
                <div class="card-header d-flex justify-content-between align-items-center"
                    style="background-color: #253F2D;">
                    <h4 class="card-title mb-0 text-white">Create New School</h4>
                    <a href="{{ route('school.allSchools') }}" class="btn text-white"
                        style="background-color: #287C44;">
                        <i class="fas fa-school me-2"></i> All Schools
                    </a>
                </div>
                <div class="card-body bg-light">
                    <form id="createSchoolForm">
                        <div class="row">
                            <div class="col-lg-6 col-md-12">
                                <div class="form-group">
                                    <label class="form-label">School Type</label>
                                    <?php
                echo Helper::DropMasterData(config('constants.options.SCHOOL_TYPE'), '', 'school_type');
                ?>
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="example-email">Email</label>
                                    <input type="email" id="example-email" name="email" class="form-control"
                                        placeholder="Email">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Gender</label>
                                    <?php
                echo Helper::DropMasterData(config('constants.options.SCHOOL_GENDER'), '', 'gender');
                ?>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Regional Level</label>
                                    <?php
                echo Helper::DropMasterData(config('constants.options.REGIONAL_LEVEL'), '', 'regional_level');
                ?>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">School Ownership</label>
                                    <?php
                echo Helper::DropMasterData(config('constants.options.SCHOOL_OWNERSHIP'), '', 'school_ownership', 1);
                ?>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Boarding Status</label>
                                    <?php
                echo Helper::DropMasterData(config('constants.options.SCHOOL_GENDER'), '', 'boarding_status', 1);
                ?>
                                </div>
                            </div>
                            <div class="col-lg-6 col-md-12">
                                <div class="form-group">
                                    <label class="form-label">School Name</label>
                                    <input class="form-control" type="text" name="name">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">School Products</label>
                                    <?php
                                     echo Helper::DropMasterData(config('constants.options.SCHOOL_PRODUCTS'), '', 'school_product', 1);
                                    ?>
                                </div>
<div class="form-group">

    <div class="d-flex justify-content-between align-items-center">
        <label class="form-label mb-0">Registration Code</label>

        <button type="button" id="toggleCustomCode"
            class="btn btn-sm btn-outline-primary">
            <i class="fas fa-edit me-1"></i> Use Custom
        </button>
    </div>

    <input class="form-control mt-2" type="text" name="registration_code"
        id="registration_code" value="{{ $registrationCode }}" readonly>

    <small class="text-muted" id="codeHint">
        Auto-generated code
    </small>
</div>

                                <div class="form-group">
                                    <label class="form-label">Contact Phone Number</label>
                                    <input class="form-control" type="tel" name="phone">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Population</label>
                                    <?php
                                        echo Helper::DropMasterData(config('constants.options.SCHOOL_POPULATION'), '', 'population', 1);
                                    ?>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">School Name Arabic</label>
                                    <input type="text" name="school_name_arabic" class="form-control"
                                        placeholder="School Name Arabic" dir="rtl" lang="ar">
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 text-left">
                            <button type="submit" class="btn text-white" style="background-color: #287C44;">
                                <i class="fas fa-paper-plane"></i> Submit
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

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    $(document).ready(function () {

        let isCustom = false;
        let defaultCode = $('#registration_code').val();

$('#toggleCustomCode').on('click', function () {

    let $input = $('#registration_code');
    let $btn = $(this);
    let $hint = $('#codeHint');

    if (!isCustom) {
        $input.val('').prop('readonly', false).focus();

        $btn.html('<i class="fas fa-sync-alt me-1"></i> Use Auto');
        $hint.text('Enter custom school registration code');

        isCustom = true;
    } else {
        $input.val(defaultCode).prop('readonly', true);

        $btn.html('<i class="fas fa-edit me-1"></i> Use Custom');
        $hint.text('Auto-generated code');

        isCustom = false;
    }
});

        $('#createSchoolForm').on('submit', function (e) {
            e.preventDefault();

            let isValid = true;
            let $form = $(this);
            let $submitBtn = $form.find('button[type="submit"]');

            $form.find('.form-control, select').removeClass('is-invalid');
            $form.find('.invalid-feedback').remove();

            $form.find('input, select').each(function () {

                let fieldName = $(this).attr('name');

                // if (fieldName === 'school_name_arabic') {
                //     return true;
                // }

                if (!$(this).val().trim()) {
                    $(this).addClass('is-invalid');

                    $(this).closest('.form-group').append(
                        '<div class="invalid-feedback d-block">This field is required.</div>'
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

            Swal.fire({
                title: 'Are you sure?',
                text: "You are about to submit the school data.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, submit it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {

                    $submitBtn.prop('disabled', true);
                    const originalBtnHtml = $submitBtn.html();
                    $submitBtn.html('Saving...<i class="fas fa-spinner fa-spin"></i>');

                    $.ajax({
                        url: '{{ route('create.new-school') }}',
                        method: 'POST',
                        data: $form.serialize(),
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        success: function (response) {
                            Swal.fire(
                                'Submitted!',
                                'School has been created successfully.',
                                'success'
                            );
                            $form[0].reset();
                        },
                        error: function (data) {
                            $('body').html(data.responseText);
                        },
                        complete: function () {
                            $submitBtn.prop('disabled', false).html(
                                originalBtnHtml);
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
<script
    src="{{ URL::asset('assets/plugins/bootstrap-daterangepicker/daterangepicker.js') }}">
</script>
<script src="{{ URL::asset('assets/js/daterange.js') }}"></script>
<!---jvectormap js-->
<script src="{{ URL::asset('assets/plugins/jvectormap/jquery.vmap.js') }}"></script>
<script src="{{ URL::asset('assets/plugins/jvectormap/jquery.vmap.world.js') }}"></script>
<script src="{{ URL::asset('assets/plugins/jvectormap/jquery.vmap.sampledata.js') }}">
</script>
<!-- Index js-->
<script src="{{ URL::asset('assets/js/index1.js') }}"></script>
<!-- Data tables js-->
<script src="{{ URL::asset('assets/plugins/datatable/js/jquery.dataTables.js') }}">
</script>
<script src="{{ URL::asset('assets/plugins/datatable/js/dataTables.bootstrap4.js') }}">
</script>
<script src="{{ URL::asset('assets/plugins/datatable/js/dataTables.buttons.min.js') }}">
</script>
<script src="{{ URL::asset('assets/plugins/datatable/js/buttons.bootstrap4.min.js') }}">
</script>
<script src="{{ URL::asset('assets/plugins/datatable/js/jszip.min.js') }}"></script>
<script src="{{ URL::asset('assets/plugins/datatable/js/pdfmake.min.js') }}"></script>
<script src="{{ URL::asset('assets/plugins/datatable/js/vfs_fonts.js') }}"></script>
<script src="{{ URL::asset('assets/plugins/datatable/js/buttons.html5.min.js') }}">
</script>
<script src="{{ URL::asset('assets/plugins/datatable/js/buttons.print.min.js') }}">
</script>
<script src="{{ URL::asset('assets/plugins/datatable/js/buttons.colVis.min.js') }}">
</script>
<script src="{{ URL::asset('assets/plugins/datatable/dataTables.responsive.min.js') }}">
</script>
<script src="{{ URL::asset('assets/plugins/datatable/responsive.bootstrap4.min.js') }}">
</script>
<script src="{{ URL::asset('assets/js/datatables.js') }}"></script>
<!--Counters -->
<script src="{{ URL::asset('assets/plugins/counters/counterup.min.js') }}"></script>
<script src="{{ URL::asset('assets/plugins/counters/waypoints.min.js') }}"></script>
<!--Chart js -->
<script src="{{ URL::asset('assets/plugins/chart/chart.bundle.js') }}"></script>
<script src="{{ URL::asset('assets/plugins/chart/utils.js') }}"></script>
@endsection
