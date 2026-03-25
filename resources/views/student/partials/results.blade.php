<?php
use App\Http\Controllers\Helper;
?>

@if ($students->isEmpty())
    <p class="text-danger">{{ trans('common.No_students_found') }}.</p>
@else
    <table class="table table-bordered">
        <thead>
            <tr>
                <th style="width:1px;">No.</th>
                <th>{{ trans('common.Admission_Number') }}</th>
                <th>{{ trans('common.Name') }}</th>
                <th>{{ trans('common.Name') }} (AR)</th>
                <th>{{ trans('common.School') }}</th>
                {{-- <th style="text-align: center;">Action</th> --}}
            </tr>
        </thead>
        <tbody>
            @foreach ($students as $count => $student)
                <tr>
                    <td>{{ $count + 1 }}</td>
                    <td>{{ $student->Student_ID }}</td>
                    <td>{{ $student->Student_Name }}</td>
                    <td>{{ $student->Student_Name_AR }}</td>
                    <td>{{ $student->House }}</td>
                    {{-- <td style="text-align: center;">
                        <button class="btn btn-outline-primary btn-sm view-bio-btn" data-toggle="modal"
                            data-target="#viewStudentModal" data-id="{{ $student->id }}"
                            data-firstname="{{ $student->firstname }}" data-lastname="{{ $student->lastname }}"
                            data-gender="{{ $student->gender }}"
                            data-admission_number="{{ $student->admission_number }}"
                            data-senior="{{ Helper::recordMdname($student->senior) }}"
                            data-stream="{{ Helper::recordMdname($student->stream) }}"
                            data-primary_contact="{{ $student->primary_contact }}"
                            data-other_contact="{{ $student->other_contact }}"
                            data-date_of_birth="{{ $student->date_of_birth }}"
                            data-nationality="{{ $student->nationality }}"
                            data-guardian_names="{{ $student->guardian_names }}"
                            data-guardian_phone="{{ $student->guardian_phone }}">
                            <i class="fa fa-id-card mr-1"></i> View Bio
                        </button>
                        <a href="javascript:void(0)" data-id="{{ $student->id }}"
                            class="btn btn-outline-primary btn-sm btn-edit-student">
                            <i class="fa fa-edit mr-1"></i> Edit
                        </a>
                    </td> --}}
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Edit Student Modal -->
    <div class="modal fade" id="editStudentModal" tabindex="-1" role="dialog" aria-labelledby="editStudentModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <form id="updateStudentForm">
                @csrf
                @method('PUT')
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="editStudentModalLabel">{{ trans('common.edit_student') }}</h5>
                        <button type="button" class="close text-white" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <input type="hidden" name="student_id" id="edit_student_id">

                        <div class="row">
                            <div class="col-md-6">
                                <!-- Basic Info -->
                                <div class="form-group">
                                    <label>{{ trans('common.first_name') }}</label>
                                    <input type="text" name="firstname" id="edit_firstname" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>{{ trans('common.last_name') }}</label>
                                    <input type="text" name="lastname" id="edit_lastname" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>{{ trans('common.gender') }}</label>
                                    <select name="gender" id="edit_gender" class="form-control">
                                        <option value="Male">{{ trans('common.male') }}</option>
                                        <option value="Female">{{ trans('common.female') }}</option>
                                        <option value="Other">{{ trans('common.other') }}</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>{{ trans('common.admission_number') }}</label>
                                    <input type="text" name="admission_number" id="edit_admission_number"
                                        class="form-control">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <!-- Guardian / Contact -->
                                <div class="form-group">
                                    <label>{{ trans('common.primary_contact') }}</label>
                                    <input type="text" name="primary_contact" id="edit_primary_contact"
                                        class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>{{ trans('common.other_contact') }}</label>
                                    <input type="text" name="other_contact" id="edit_other_contact"
                                        class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>{{ trans('common.date_of_birth') }}</label>
                                    <input type="date" name="date_of_birth" id="edit_date_of_birth"
                                        class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>{{ trans('common.nationality') }}</label>
                                    <input type="text" name="nationality" id="edit_nationality" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>{{ trans('common.guardian_names') }}</label>
                                    <input type="text" name="guardian_names" id="edit_guardian_names"
                                        class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>{{ trans('common.guardian_phone') }}</label>
                                    <input type="text" name="guardian_phone" id="edit_guardian_phone"
                                        class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">
                            <i class="fa fa-save me-1"></i> {{ trans('common.update_student') }}
                        </button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times"></i>{{ trans('common.cancel') }} 
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="viewStudentModal" tabindex="-1" role="dialog"
        aria-labelledby="viewStudentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="viewStudentModalLabel">{{ trans('common.student_information') }}</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <dl class="row">
                        <dt class="col-sm-4">ID</dt>
                        <dd class="col-sm-8" id="view_id"></dd>

                        <dt class="col-sm-4">{{ trans('common.first_name') }}</dt>
                        <dd class="col-sm-8" id="view_firstname"></dd>

                        <dt class="col-sm-4">{{ trans('common.last_name') }}</dt>
                        <dd class="col-sm-8" id="view_lastname"></dd>

                        <dt class="col-sm-4">{{ trans('common.gender') }}</dt>
                        <dd class="col-sm-8" id="view_gender"></dd>

                        <dt class="col-sm-4">{{ trans('common.admission_number') }}</dt>
                        <dd class="col-sm-8" id="view_admission_number"></dd>

                        <dt class="col-sm-4">{{ trans('common.Class') }}</dt>
                        <dd class="col-sm-8" id="view_senior"></dd>

                        <dt class="col-sm-4">{{ trans('common.stream') }}</dt>
                        <dd class="col-sm-8" id="view_stream"></dd>

                        <dt class="col-sm-4">{{ trans('common.primary_contact') }}</dt>
                        <dd class="col-sm-8" id="view_primary_contact"></dd>

                        <dt class="col-sm-4">{{ trans('common.other_contact') }}</dt>
                        <dd class="col-sm-8" id="view_other_contact"></dd>

                        <dt class="col-sm-4">{{ trans('common.date_of_birth') }}</dt>
                        <dd class="col-sm-8" id="view_date_of_birth"></dd>

                        <dt class="col-sm-4">{{ trans('common.nationality') }}</dt>
                        <dd class="col-sm-8" id="view_nationality"></dd>

                        <dt class="col-sm-4">{{ trans('common.guardian_names') }}</dt>
                        <dd class="col-sm-8" id="view_guardian_names"></dd>

                        <dt class="col-sm-4">{{ trans('common.guardian_phone') }}</dt>
                        <dd class="col-sm-8" id="view_guardian_phone"></dd>
                    </dl>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i>{{ trans('common.close') }} 
                    </button>
                </div>

            </div>
        </div>
    </div>

    <style>
        .swal2-container {
            z-index: 99999 !important;
        }
    </style>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#createStudentForm').on('submit', function(e) {
                e.preventDefault();

                let $form = $(this);
                let $submitBtn = $form.find('button[type="submit"]');
                let isValid = true;

                $form.find('.form-control, select').removeClass('is-invalid');
                $form.find('.invalid-feedback').remove();

                // Basic required fields
                let requiredFields = ['firstname', 'lastname', 'senior', 'stream', 'gender'];

                requiredFields.forEach(field => {
                    let input = $form.find(`[name="${field}"]`);
                    if (!input.val().trim()) {
                        input.addClass('is-invalid');
                        input.after('<div class="invalid-feedback">This field is required.</div>');
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
                    text: "You are about to submit the student data.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, submit it!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        submitStudentForm($form, $submitBtn);
                    }
                });
            });

            function submitStudentForm($form, $submitBtn) {
                let formData = $form.serialize();
                let originalHtml = $submitBtn.html();

                $submitBtn.prop('disabled', true).html('Saving... <i class="fas fa-spinner fa-spin"></i>');

                $.ajax({
                    url: '{{ route('students.store') }}',
                    method: 'POST',
                    data: formData,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        Swal.fire('Success!', response.message, 'success');
                        $form[0].reset();
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            let errors = xhr.responseJSON.errors;
                            for (let field in errors) {
                                let input = $form.find(`[name="${field}"]`);
                                input.addClass('is-invalid');
                                input.after(`<div class="invalid-feedback">${errors[field][0]}</div>`);
                            }

                            Swal.fire({
                                icon: 'error',
                                title: 'Validation Error',
                                text: 'Please fix the highlighted errors.'
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Server Error',
                                text: xhr.responseJSON?.message ||
                                    'An unexpected error occurred.'
                            });
                        }
                    },
                    // error: function (data) {
                    //     $('body').html(data.responseText);
                    // },
                    complete: function() {
                        $submitBtn.prop('disabled', false).html(originalHtml);
                    }
                });
            }
        });


        $(document).ready(function() {

            $('.btn-edit-student').on('click', function() {
                const studentId = $(this).data('id');

                $.ajax({
                    url: "{{ url('/students/Information') }}/" + studentId,
                    method: 'GET',
                    success: function(student) {
                        $('#edit_student_id').val(student.id);
                        $('#edit_firstname').val(student.firstname);
                        $('#edit_lastname').val(student.lastname);
                        $('#edit_gender').val(student.gender);
                        $('#edit_admission_number').val(student.admission_number);
                        $('#edit_senior').val(student.senior_id);
                        $('#edit_stream').val(student.stream_id);
                        $('#edit_primary_contact').val(student.primary_contact);
                        $('#edit_other_contact').val(student.other_contact);
                        $('#edit_date_of_birth').val(student.date_of_birth);
                        $('#edit_nationality').val(student.nationality);
                        $('#edit_guardian_names').val(student.guardian_names);
                        $('#edit_guardian_phone').val(student.guardian_phone);

                        $('#editStudentModal').modal('show');
                    },
                    error: function(data) {
                        $('body').html(data.responseText);
                    }
                });
            });


            // Submit update
            $('#updateStudentForm').on('submit', function(e) {
                e.preventDefault();

                const studentId = $('#edit_student_id').val();
                const formData = $(this).serialize();

                Swal.fire({
                    title: 'Confirm Update',
                    text: 'Are you sure you want to update this student?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, update',
                    cancelButtonText: 'Cancel',
                    reverseButtons: true
                }).then((result) => {

                    if (!result.isConfirmed) return;

                    $.ajax({
                        url: "{{ url('/students/update') }}/" + studentId,
                        type: 'POST',
                        data: formData,
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'X-HTTP-Method-Override': 'PUT'
                        },
                        success: function(response) {

                            Swal.fire({
                                title: 'Success',
                                text: response.message ||
                                    'Student updated successfully',
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                $('#editStudentModal').modal('hide');
                                location.reload();
                            });

                        },
                        error: function(xhr) {
                            let errorText = 'Something went wrong';

                            if (xhr.status === 422 && xhr.responseJSON.errors) {
                                errorText = Object.values(xhr.responseJSON.errors).join(
                                    '<br>');
                            }

                            Swal.fire({
                                title: 'Error',
                                html: errorText,
                                icon: 'error',
                                didOpen: () => {
                                    const swalEl = Swal.getPopup();
                                    swalEl.style.zIndex =
                                        parseInt($('.modal').css(
                                            'z-index')) + 10;
                                }
                            });
                        }
                    });

                });
            });
        });
    </script>

    <script>
        $(document).ready(function() {
            $('#viewStudentModal').on('show.bs.modal', function(event) {
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
