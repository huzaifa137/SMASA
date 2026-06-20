{{-- resources/views/master-logic/master-code-list.blade.php --}}
@extends('layouts-side-bar.master')

@section('css')
    @include('master-logic.partials._styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
@endsection

@section('content')
    <?php
    use App\Helpers\PermissionHelper;
    $currentCode = collect($selected)->firstWhere('mc_id', $mc_id);
    $currentCount = $code_totals[$currentCode?->id]->total ?? 0;
        ?>

    <script>
        @if (session('success'))
            Swal.fire({
                title: 'Success!',
                text: @json(session('success')),
                icon: 'success',
                confirmButtonText: 'OK'
            });
        @endif
    </script>

    <style>
        .mdx-table-wrap {
    margin-top: 20px;
}
    </style>

    <div class="mdx-page">
        <div class="mdx-topbar"></div>

        {{-- ── Header ── --}}
        <div class="mdx-header">
            <div class="mdx-header-left">
                <div class="mdx-header-icon"><i class="fa fa-cubes"></i></div>
                <div>
                    <h4 class="mdx-header-title">{{ $mc_name ?: 'Master Data' }}</h4>
                    <div class="mdx-header-sub">{{ $currentCount }} record{{ $currentCount == 1 ? '' : 's' }} in this
                        category.</div>
                </div>
            </div>
            <div class="mdx-header-actions">
                <a href="{{ route('master-code-to-data') }}" class="mdx-btn mdx-btn-outline">
                    <i class="fa fa-arrow-left"></i> All Categories
                </a>
                @if(PermissionHelper::canFeature('create_master_data'))
                    <button type="button" id="addCodeBtn" class="mdx-btn mdx-btn-primary">
                        <i class="fa fa-plus"></i> Add Record
                    </button>
                @endif
            </div>
        </div>

        <div class="row mb-0">
            {{-- ── Category sidebar ── --}}
            <div class="col-lg-3 mb-4">
                @include('master-logic.partials._category-sidebar')
            </div>

            {{-- ── Main column ── --}}
            <div class="col-lg-9">

                {{-- Add record (collapsible) --}}
                @if(PermissionHelper::canFeature('create_master_data'))
                    <div class="mdx-panel mdx-collapse" id="addCodeForm">
                        <div class="mdx-panel-head">
                            <div>
                                <div class="mdx-panel-label">New Entry</div>
                                <div class="mdx-panel-title">Add a Record to {{ $mc_name }}</div>
                            </div>
                        </div>
                        <div class="mdx-panel-body">
                            <form id="myForm" action="{{ route('add-new-record') }}" method="POST">
                                @csrf
                                <div class="mdx-form-grid">
                                    <div class="mdx-field mdx-col-4">
                                        <label for="master_code_id">Master Code</label>
                                        <select name="master_code_id" id="master_code_id" class="form-control mdx-input">
                                            @foreach ($selected as $item)
                                                @if ($item->mc_id == $mc_id)
                                                    <option selected value="{{ $item->id }}">{{ $item->mc_name }}</option>
                                                @else
                                                    <option value="{{ $item->id }}">{{ $item->mc_name }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mdx-field mdx-col-4">
                                        <label for="md_code">Master Data Code</label>
                                        <input class="form-control mdx-input" type="text" name="md_code" id="md_code" required>
                                    </div>
                                    <div class="mdx-field mdx-col-4">
                                        <label for="md_name">Master Data Name</label>
                                        <input class="form-control mdx-input" type="text" name="md_name" id="md_name" required>
                                    </div>
                                    <div class="mdx-field mdx-col-12">
                                        <label for="md_description">Master Data Description</label>
                                        <textarea class="form-control mdx-input" name="md_description"
                                            id="md_description"></textarea>
                                    </div>
                                </div>

                                {{-- ── Dynamic field builder ── --}}
                                <div class="mdx-builder-wrap">
                                    <div class="mdx-builder-label"><i class="fa fa-puzzle-piece"></i> Optional Custom
                                        Fields</div>
                                    <div class="mdx-form-grid">
                                        <div class="mdx-field mdx-col-4">
                                            <select id="elementType" class="form-control mdx-input">
                                                <option value="">-- Choose Element --</option>
                                                <option value="input">Input</option>
                                                <option value="textarea">Textarea</option>
                                                <option value="select">Dropdown</option>
                                            </select>
                                        </div>
                                        <div class="mdx-col-8 d-flex align-items-end">
                                            <button type="button" id="addElement" class="mdx-btn mdx-btn-outline mdx-btn-sm">
                                                <i class="bi bi-plus-circle"></i> Add Element
                                            </button>
                                        </div>
                                    </div>

                                    <div id="elementOptions" class="mt-3" style="display:none;"></div>
                                    <div id="formElements" class="mt-3"></div>
                                </div>

                                <div class="mt-4">
                                    <button class="mdx-btn mdx-btn-primary" type="submit" id="add_new_data">
                                        <i class="fa fa-fw fa-save"></i> Save Record
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                @endif

                {{-- Records table --}}
                <div class="mdx-panel">
                    <div class="mdx-panel-head">
                        <div>
                            <div class="mdx-panel-label">Records</div>
                            <div class="mdx-panel-title">{{ $mc_name }} Data List</div>
                        </div>
                    </div>
                    <div class="mdx-table-wrap" style="border:none;border-radius:0;">
                        <table class="mdx-table" id="table" style="width:100%;">
                            <thead>
                                <tr>
                                    <th>Data Code</th>
                                    <th>Data Name</th>
                                    <th>Data Description</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                        </table>
                        <input type="hidden" value="{{ $mc_id }}" id="mc_id" />
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
    </div>
    </div>
@endsection

@section('js')
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>

    <script>
        $(function () {

            /* ── Toggle the "Add Record" panel ── */
            var $form = $('#addCodeForm');
            $('#addCodeBtn').on('click', function () {
                $form.toggleClass('open');
                $(this).html($form.hasClass('open')
                    ? '<i class="fa fa-minus"></i> Hide Form'
                    : '<i class="fa fa-plus"></i> Add Record');
            });

            /* ── Records DataTable (server-side) ── */
            var mcId = $('#mc_id').val();
            if (mcId) {
                $('#table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: '{{ route('master-code-list', ['id' => '__mc_id__']) }}'.replace('__mc_id__', mcId),
                        error: function (data) {
                            $('body').html(data.responseText);
                        }
                    },
                    columns: [
                        { data: 'md_code', name: 'md_code' },
                        { data: 'md_name', name: 'md_name' },
                        { data: 'md_description', name: 'md_description' },
                        { data: 'action', name: 'action', orderable: false, searchable: false }
                    ],
                    pageLength: 20,
                    lengthMenu: [10, 25, 50, 100],
                    order: [
                        [0, 'asc']
                    ],
                    searching: true,
                    ordering: true,
                    dom: 'Bfrtip',
                    buttons: ['copy', 'csv', 'excel', 'pdf', 'print']
                });
            }

            /* ── Dynamic field builder ── */
            $('#elementType').on('change', function () {
                var selected = $(this).val();
                var $optionsDiv = $('#elementOptions').empty().hide();

                if (selected === 'select') {
                    $optionsDiv.show().append(
                        '<label><strong>Add Dropdown Options</strong></label>' +
                        '<div id="dropdownOptions">' +
                        '<div class="option-wrapper"><input type="text" class="form-control mdx-input option-input" placeholder="Option 1"><span class="delete-option-btn bi bi-x-circle-fill"></span></div>' +
                        '<div class="option-wrapper"><input type="text" class="form-control mdx-input option-input" placeholder="Option 2"><span class="delete-option-btn bi bi-x-circle-fill"></span></div>' +
                        '</div>' +
                        '<button type="button" class="mdx-btn mdx-btn-outline mdx-btn-sm mt-2 mb-2" id="addDropdownOption"><i class="bi bi-plus"></i> Add More Option</button>'
                    );
                }
            });

            $('#elementOptions').on('click', '#addDropdownOption', function () {
                $('#dropdownOptions').append(
                    '<div class="option-wrapper"><input type="text" class="form-control mdx-input option-input" placeholder="New Option"><span class="delete-option-btn bi bi-x-circle-fill"></span></div>'
                );
            });

            $('#elementOptions').on('click', '.delete-option-btn', function () {
                $(this).closest('.option-wrapper').remove();
            });

            $('#addElement').on('click', function () {
                var selected = $('#elementType').val();
                if (!selected) {
                    Swal.fire('Warning', 'Please select a form element type.', 'warning');
                    return;
                }

                var index = $('#formElements .form-builder-section').length + 1;
                var deleteButton = '<button type="button" class="delete-btn" title="Delete Section"><i class="bi bi-trash3-fill"></i></button>';
                var elementHtml = '';

                if (selected === 'input') {
                    elementHtml =
                        '<div class="form-builder-section">' + deleteButton +
                        '<label>Input Field ' + index + '</label>' +
                        '<input type="text" name="dynamic_input_' + index + '" class="form-control mdx-input">' +
                        '</div>';
                }

                if (selected === 'textarea') {
                    elementHtml =
                        '<div class="form-builder-section">' + deleteButton +
                        '<label>Textarea ' + index + '</label>' +
                        '<textarea name="dynamic_textarea_' + index + '" class="form-control mdx-input" rows="3"></textarea>' +
                        '</div>';
                }

                if (selected === 'select') {
                    var options = [];
                    $('#dropdownOptions .option-input').each(function () {
                        var val = $(this).val().trim();
                        if (val) options.push(val);
                    });

                    if (options.length === 0) {
                        Swal.fire('Error', 'You must add at least one non-empty option for the dropdown.', 'error');
                        return;
                    }

                    var selectOptionsHtml = options.map(function (opt) {
                        return '<option value="' + opt + '">' + opt + '</option>';
                    }).join('');

                    var optionListHtml = options.map(function (opt) {
                        return '<li class="list-group-item d-flex justify-content-between align-items-center">' + opt +
                            '<button type="button" class="btn btn-sm btn-danger btn-delete-option"><i class="bi bi-x-circle-fill"></i></button></li>';
                    }).join('');

                    elementHtml =
                        '<div class="form-builder-section">' + deleteButton +
                        '<label>Dropdown ' + index + '</label>' +
                        '<div class="dropdown-section" data-field-name="dynamic_select_' + index + '">' +
                        '<select name="dynamic_select_' + index + '" class="form-control mdx-input mb-2">' + selectOptionsHtml + '</select>' +
                        '<ul class="list-group dropdown-options-list">' + optionListHtml + '</ul>' +
                        '<div class="input-group mb-2">' +
                        '<input type="text" class="form-control mdx-input new-dropdown-option" placeholder="Add new option">' +
                        '<button type="button" class="btn btn-sm btn-outline-secondary add-new-option"><i class="bi bi-plus-circle"></i></button>' +
                        '</div></div></div>';
                }

                $('#formElements').append(elementHtml);
                $('#elementType').val('');
                $('#elementOptions').hide().empty();
            });

            $('#formElements').on('click', '.delete-btn', function () {
                $(this).closest('.form-builder-section').remove();
                renumberElements();
            });

            function renumberElements() {
                $('#formElements .form-builder-section').each(function (index) {
                    var number = index + 1;
                    var label = $(this).find('label').first();
                    var inputField = $(this).find('input[type="text"], textarea, select');

                    var currentName = inputField.attr('name');
                    var newName = '';

                    if (currentName.includes('dynamic_input_')) {
                        newName = 'dynamic_input_' + number;
                        label.text('Input Field ' + number);
                    } else if (currentName.includes('dynamic_textarea_')) {
                        newName = 'dynamic_textarea_' + number;
                        label.text('Textarea ' + number);
                    } else if (currentName.includes('dynamic_select_')) {
                        newName = 'dynamic_select_' + number;
                        label.text('Dropdown ' + number);
                        $(this).find('.dropdown-section').attr('data-field-name', newName);
                    }
                    inputField.attr('name', newName);
                });
            }

            $('#formElements').on('click', '.add-new-option', function () {
                var container = $(this).closest('.dropdown-section');
                var input = container.find('.new-dropdown-option');
                var value = input.val().trim();
                var select = container.find('select');
                var optionList = container.find('.dropdown-options-list');

                if (!value) {
                    Swal.fire('Error', 'Option cannot be empty.', 'error');
                    return;
                }

                select.append('<option value="' + value + '">' + value + '</option>');
                optionList.append(
                    '<li class="list-group-item d-flex justify-content-between align-items-center">' + value +
                    '<button type="button" class="btn btn-sm btn-danger btn-delete-option"><i class="bi bi-x-circle-fill"></i></button></li>'
                );

                input.val('');
            });

            $('#formElements').on('click', '.btn-delete-option', function () {
                var listItem = $(this).closest('li');
                var optionText = listItem.contents().get(0).nodeValue.trim();
                var select = $(this).closest('.dropdown-section').find('select');

                select.find('option').filter(function () {
                    return $(this).text() === optionText;
                }).remove();

                listItem.remove();
            });

            $('#myForm').on('submit', function (e) {
                e.preventDefault();

                var $submitForm = $(this);
                var url = $submitForm.attr('action');
                var method = $submitForm.attr('method');
                var formData = new FormData($submitForm[0]);
                var dynamicElementsData = {};

                $('#formElements .form-builder-section').each(function () {
                    var $this = $(this);
                    var inputField = $this.find('input[type="text"], textarea, select');

                    if (inputField.length > 0) {
                        var name = inputField.attr('name');
                        var value = inputField.val();

                        if (inputField.is('select')) {
                            var options = [];
                            inputField.find('option').each(function () {
                                options.push({ value: $(this).val(), text: $(this).text() });
                            });
                            dynamicElementsData[name] = { value: value, options: options };
                        } else {
                            dynamicElementsData[name] = value;
                        }
                    }
                });

                formData.append('dynamic_form_elements', JSON.stringify(dynamicElementsData));

                $.ajax({
                    url: url,
                    method: method,
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        if (response.success) {
                            Swal.fire('Success!', response.message, 'success').then(function () {
                                window.location.href = response.redirect_url;
                            });
                        } else {
                            Swal.fire('Error!', response.message, 'error');
                        }
                    },
                    error: function (data) {
                        $('body').html(data.responseText);
                    }
                });
            });
        });
    </script>
@endsection