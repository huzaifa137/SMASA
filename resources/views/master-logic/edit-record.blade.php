{{-- resources/views/master-logic/edit-master-record.blade.php --}}
@extends('layouts-side-bar.master')

@section('css')
    @include('master-logic.partials._styles')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        /* ════════════════════════════════════════════════════════════
           EDIT MASTER RECORD — Advanced Styling
           ════════════════════════════════════════════════════════════ */

        /* ── Edit Form Container ── */
        .mdx-edit-wrapper {
            max-width: 1000px;
            margin: 0 auto;
        }

        .mdx-edit-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--mdx-border);
            flex-wrap: wrap;
        }

        .mdx-edit-header .back-link {
            margin-left: auto;
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            padding: 0.75rem 1.25rem;
            font-size: 0.9rem;
            font-weight: 700;
            color: var(--mdx-indigo);
            text-decoration: none;
            background: #ffffff;
            border: 1px solid #c7d2fe;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(44, 41, 202, 0.08);
            transition: all 0.25s ease;
        }

        .mdx-edit-header .back-link:hover {
            color: #fff;
            background: linear-gradient(135deg, var(--mdx-indigo), var(--mdx-indigo3));
            border-color: var(--mdx-indigo);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(44, 41, 202, 0.2);
            text-decoration: none;
        }

        .mdx-edit-header .edit-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--mdx-indigo), var(--mdx-indigo3));
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            flex-shrink: 0;
            box-shadow: 0 4px 14px rgba(44, 41, 202, 0.25);
        }

        .mdx-edit-header .edit-title {
            font-size: 1.2rem;
            font-weight: 800;
            color: var(--mdx-navy);
            margin: 0;
            letter-spacing: -0.025em;
        }

        .mdx-edit-header .edit-subtitle {
            font-size: 0.78rem;
            color: var(--mdx-slate);
            margin: 0.1rem 0 0 0;
        }

        .mdx-edit-header .code-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: #eef2ff;
            border: 1px solid #c7d2fe;
            border-radius: 8px;
            padding: 0.3rem 0.8rem;
            font-size: 0.78rem;
            color: var(--mdx-indigo2);
            font-weight: 600;
        }

        .mdx-edit-header .code-badge i {
            font-size: 0.5rem;
            color: #22c55e;
        }

        /* ── Form Fields ── */
        .mdx-edit-form .form-group {
            margin-bottom: 1.25rem;
        }

        .mdx-edit-form label {
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--mdx-slate);
            display: block;
            margin-bottom: 0.35rem;
        }

        .mdx-edit-form label .required {
            color: #dc2626;
            margin-left: 2px;
        }

        .mdx-edit-form .form-control {
            border: 1px solid var(--mdx-border);
            border-radius: 10px;
            padding: 0.7rem 1rem;
            font-size: 0.88rem;
            color: var(--mdx-navy);
            background: var(--mdx-white);
            transition: all 0.2s;
            width: 100%;
            height: auto;
        }

        .mdx-edit-form .form-control:focus {
            outline: none;
            border-color: var(--mdx-indigo3);
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.12);
            background: var(--mdx-white);
        }

        .mdx-edit-form .form-control:disabled,
        .mdx-edit-form .form-control[readonly] {
            background: var(--mdx-bg2);
            cursor: not-allowed;
        }

        .mdx-edit-form .form-control.textarea {
            resize: vertical;
            min-height: 80px;
        }

        .mdx-edit-form .form-control.select-disabled {
            background: var(--mdx-bg2);
            cursor: not-allowed;
            opacity: 0.8;
        }

        .mdx-edit-form .form-hint {
            font-size: 0.7rem;
            color: var(--mdx-slate);
            margin-top: 0.3rem;
        }

        /* ── Read-only select styling ── */
        .mdx-edit-form .select-wrapper {
            position: relative;
        }

        .mdx-edit-form .select-wrapper .form-control {
            padding-right: 2.5rem;
        }

        .mdx-edit-form .select-wrapper .select-icon {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--mdx-slate);
            font-size: 0.8rem;
            pointer-events: none;
        }

        /* ── Form Actions ── */
        .mdx-form-actions {
            display: flex;
            gap: 0.75rem;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 2px solid var(--mdx-border);
            flex-wrap: wrap;
        }

        .mdx-form-actions .mdx-btn {
            min-width: 120px;
            justify-content: center;
            padding: 0.65rem 1.5rem;
        }

        .mdx-form-actions .mdx-btn-danger-outline {
            background: var(--mdx-white);
            border-color: #fecdd3;
            color: var(--mdx-rose);
        }

        .mdx-form-actions .mdx-btn-danger-outline:hover {
            background: #fff1f2;
            border-color: var(--mdx-rose);
            color: var(--mdx-rose);
        }

        /* ── Sidebar Info ── */
        .mdx-sidebar-info {
            background: var(--mdx-white);
            border: 1px solid var(--mdx-border);
            border-radius: var(--mdx-r);
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(44, 41, 202, 0.05);
            margin-top: 1.5rem;
        }

        .mdx-sidebar-info .info-item {
            padding: 0.8rem 1.2rem;
            border-bottom: 1px solid #f1f3fb;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.82rem;
        }

        .mdx-sidebar-info .info-item:last-child {
            border-bottom: none;
        }

        .mdx-sidebar-info .info-label {
            color: var(--mdx-slate);
            font-weight: 500;
        }

        .mdx-sidebar-info .info-value {
            color: var(--mdx-navy);
            font-weight: 600;
        }

        /* ── Responsive ── */
        @media (max-width: 768px) {
            .mdx-edit-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .mdx-edit-header .back-link {
                margin-left: 0;
                width: 100%;
                justify-content: center;
            }

            .mdx-form-actions {
                flex-direction: column;
            }

            .mdx-form-actions .mdx-btn {
                width: 100%;
            }

            .mdx-edit-header .code-badge {
                align-self: flex-start;
            }
        }

        /* ── Animation ── */
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .mdx-edit-wrapper {
            animation: slideIn 0.4s ease forwards;
        }

        /* ── Toast Customization ── */
        .mdx-toast-success {
            background: #d1fae5 !important;
            color: #065f46 !important;
            border: 1px solid #a7f3d0 !important;
        }

        /* ── Field Status Indicators ── */
        .mdx-field-status {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            font-size: 0.65rem;
            font-weight: 600;
            padding: 0.2rem 0.6rem;
            border-radius: 20px;
            margin-top: 0.3rem;
        }

        .mdx-field-status.required {
            color: #dc2626;
            background: #fee2e2;
        }

        .mdx-field-status.optional {
            color: var(--mdx-slate);
            background: #f1f3f4;
        }
    </style>
@endsection

@section('content')
    <?php
    use App\Helpers\PermissionHelper;
    use App\Http\Controllers\Helper;

    $record = $tb_record->first();
    $totalRecords = Helper::totalRows('master_datas', 'md_master_code_id', $master_code_id ?? 0);
    ?>

    <script>
        @if (session('success'))
            Swal.fire({
                title: 'Success!',
                text: @json(session('success')),
                icon: 'success',
                confirmButtonText: 'OK',
                timer: 3000,
                timerProgressBar: true
            });
        @endif

        @if (session('error'))
            Swal.fire({
                title: 'Error!',
                text: @json(session('error')),
                icon: 'error',
                confirmButtonText: 'OK'
            });
        @endif
    </script>

    <div class="mdx-page">

        <div class="row">
            {{-- ── Main Content ── --}}
            <div class="col-lg-12">
                <div class="mdx-edit-wrapper">
                    {{-- ── Header ── --}}
                    <div class="mdx-edit-header">
                        <div class="edit-icon">
                            <i class="fa fa-pen"></i>
                        </div>
                        <div>
                            <h4 class="edit-title">Edit Master Record</h4>
                            <p class="edit-subtitle">Update the record details below</p>
                        </div>
                        @if($record)
                            <span class="code-badge">
                                <i class="fa fa-circle"></i>
                                {{ $record->md_code }}
                            </span>
                        @endif
                        <a href="{{ isset($master_code_id) ? url('master-data/master-code-list/' . $master_code_id) : route('master-code-to-data') }}" 
                           class="back-link">
                            <i class="fa fa-arrow-left"></i> Back
                        </a>
                    </div>

                    {{-- ── Form ── --}}
                    <div class="mdx-panel">
                        <div class="mdx-panel-head">
                            <div>
                                <div class="mdx-panel-label">Edit Form</div>
                                <div class="mdx-panel-title">Record Details</div>
                            </div>
                        </div>
                        <div class="mdx-panel-body">
                            @foreach ($tb_record as $item)
                                <form id="myForm" action="{{ route('update-master-record') }}" method="POST" class="mdx-edit-form">
                                    @csrf
                                    <input type="hidden" name="md_id" value="{{ $item->md_id }}">
                                    <input type="hidden" name="record_id" value="{{ $item->md_id }}">

                                    <div class="row">
                                        {{-- Master Code Category (Read-only) --}}
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="md_master_code_id">
                                                    Category <span class="required">*</span>
                                                </label>
                                                <div class="select-wrapper">
                                                    <select name="md_master_code_id" id="md_master_code_id" 
                                                            class="form-control select-disabled" disabled>
                                                        <option value="{{ $master_code_id ?? '' }}">
                                                            {{ $master_code_name ?? 'N/A' }}
                                                        </option>
                                                    </select>
                                                    <span class="select-icon"><i class="fa fa-lock"></i></span>
                                                </div>
                                                <input type="hidden" name="md_master_code_id" 
                                                       value="{{ $master_code_id ?? '' }}">
                                                <div class="form-hint">
                                                    <i class="fa fa-info-circle"></i> 
                                                    Category is read-only
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Master Data Code --}}
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="md_code">
                                                    Data Code <span class="required">*</span>
                                                </label>
                                                <input class="form-control" type="text" 
                                                       name="md_code" id="md_code"
                                                       value="{{ $item->md_code }}"
                                                       placeholder="e.g. PRD001-01"
                                                       required>
                                                <div class="form-hint">Unique code for this record</div>
                                            </div>
                                        </div>

                                        {{-- Master Data Name --}}
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="md_name">
                                                    Record Name <span class="required">*</span>
                                                </label>
                                                <input class="form-control" type="text" 
                                                       name="md_name" id="md_name"
                                                       value="{{ $item->md_name }}"
                                                       placeholder="e.g. Product A"
                                                       required>
                                                <div class="form-hint">Descriptive name for this record</div>
                                            </div>
                                        </div>

                                        {{-- Master Data Description --}}
                                        <div class="col-12">
                                            <div class="form-group">
                                                <label for="md_description">Description</label>
                                                <textarea class="form-control textarea" 
                                                          name="md_description" 
                                                          id="md_description"
                                                          placeholder="Describe this record in detail...">{{ $item->md_description }}</textarea>
                                                <div class="form-hint">
                                                    <i class="fa fa-pencil"></i> 
                                                    Optional description to provide additional context
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- ── Dynamic Fields (if any) ── --}}
                                    @if(isset($dynamicFields) && count($dynamicFields) > 0)
                                        <div class="mt-4 pt-4 border-top">
                                            <div class="mb-3">
                                                <h6 class="text-primary" style="font-weight:700; font-size:0.85rem;">
                                                    <i class="fa fa-puzzle-piece"></i> Custom Fields
                                                </h6>
                                                <p class="text-muted" style="font-size:0.75rem;">
                                                    Additional fields for this record
                                                </p>
                                            </div>
                                            <div class="row">
                                                @foreach($dynamicFields as $field)
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="{{ $field['name'] }}">
                                                                {{ $field['label'] }}
                                                                @if($field['required'] ?? false)
                                                                    <span class="required">*</span>
                                                                @endif
                                                            </label>
                                                            @if($field['type'] === 'textarea')
                                                                <textarea class="form-control textarea" 
                                                                          name="{{ $field['name'] }}" 
                                                                          id="{{ $field['name'] }}"
                                                                          placeholder="{{ $field['placeholder'] ?? '' }}"
                                                                          {{ ($field['required'] ?? false) ? 'required' : '' }}>{{ $field['value'] ?? '' }}</textarea>
                                                            @elseif($field['type'] === 'select')
                                                                <select class="form-control" 
                                                                        name="{{ $field['name'] }}" 
                                                                        id="{{ $field['name'] }}"
                                                                        {{ ($field['required'] ?? false) ? 'required' : '' }}>
                                                                    <option value="">-- Select --</option>
                                                                    @foreach($field['options'] ?? [] as $option)
                                                                        <option value="{{ $option['value'] }}" 
                                                                                {{ ($field['value'] ?? '') == $option['value'] ? 'selected' : '' }}>
                                                                            {{ $option['text'] }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            @else
                                                                <input class="form-control" type="text" 
                                                                       name="{{ $field['name'] }}" 
                                                                       id="{{ $field['name'] }}"
                                                                       value="{{ $field['value'] ?? '' }}"
                                                                       placeholder="{{ $field['placeholder'] ?? '' }}"
                                                                       {{ ($field['required'] ?? false) ? 'required' : '' }}>
                                                            @endif
                                                            @if(!empty($field['hint']))
                                                                <div class="form-hint">{{ $field['hint'] }}</div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    {{-- ── Form Actions ── --}}
                                    <div class="mdx-form-actions">
                                        <button class="mdx-btn mdx-btn-primary" type="submit" id="submitBtn">
                                            <i class="fa fa-fw fa-save"></i> Update Record
                                        </button>
                                        <a href="{{ isset($master_code_id) ? url('master-data/master-code-list/' . $master_code_id) : route('master-code-to-data') }}" 
                                           class="mdx-btn mdx-btn-outline">
                                            <i class="fa fa-times"></i> Cancel
                                        </a>
                                        @if(PermissionHelper::canFeature('delete_master_data') && $record)
                                            <a href="{{ url('delete-record/' . $record->md_id) }}" 
                                               class="mdx-btn mdx-btn-danger-outline ml-auto delete-record-btn"
                                               onclick="return confirmDelete()">
                                                <i class="fas fa-trash-alt"></i> Delete Record
                                            </a>
                                        @endif
                                    </div>
                                </form>
                            @endforeach
                        </div>
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('myForm');

            // ── Form Submit with Confirmation ──
            form.addEventListener('submit', function(event) {
                event.preventDefault();

                Swal.fire({
                    title: 'Confirm Update',
                    text: 'Please confirm you want to update this master record.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#4338ca',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Yes, update it!',
                    cancelButtonText: 'Cancel',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Show loading state
                        const submitBtn = document.getElementById('submitBtn');
                        const originalHtml = submitBtn.innerHTML;
                        submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Updating...';
                        submitBtn.disabled = true;

                        // Submit the form
                        form.submit();
                    }
                });
            });

            // ── Delete Confirmation ──
            window.confirmDelete = function() {
                Swal.fire({
                    title: '⚠️ Delete Record?',
                    text: 'This will permanently delete this record. This action cannot be undone!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'Cancel',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        const recordId = '{{ $record->md_id ?? 0 }}';
                        window.location.href = '{{ url("delete-record/") }}/' + recordId;
                    }
                    return false;
                });
                return false;
            };

            // ── Auto-save indicator ──
            const formInputs = form.querySelectorAll('input:not([type="hidden"]), textarea, select');
            let hasChanges = false;

            formInputs.forEach(input => {
                const originalValue = input.value;
                input.addEventListener('change', function() {
                    if (this.value !== originalValue) {
                        hasChanges = true;
                    }
                });
                input.addEventListener('input', function() {
                    if (this.value !== originalValue) {
                        hasChanges = true;
                    }
                });
            });

            // ── Warn before leaving with unsaved changes ──
            window.addEventListener('beforeunload', function(e) {
                if (hasChanges) {
                    e.preventDefault();
                    e.returnValue = 'You have unsaved changes. Are you sure you want to leave?';
                    return e.returnValue;
                }
            });

            // ── Reset unsaved changes flag on submit ──
            form.addEventListener('submit', function() {
                hasChanges = false;
            });

            // ── Live character counter for description ──
            const descTextarea = document.getElementById('md_description');
            if (descTextarea) {
                const charCount = document.createElement('div');
                charCount.className = 'form-hint text-right';
                charCount.style.marginTop = '0.2rem';
                charCount.style.fontSize = '0.65rem';
                charCount.style.color = 'var(--mdx-slate)';
                descTextarea.parentNode.appendChild(charCount);

                function updateCharCount() {
                    const length = descTextarea.value.length;
                    charCount.textContent = length + ' characters';
                    if (length > 500) {
                        charCount.style.color = '#dc2626';
                    } else {
                        charCount.style.color = 'var(--mdx-slate)';
                    }
                }

                descTextarea.addEventListener('input', updateCharCount);
                updateCharCount();
            }

            // ── Keyboard shortcuts ──
            document.addEventListener('keydown', function(e) {
                // Ctrl+Enter to submit
                if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
                    e.preventDefault();
                    form.dispatchEvent(new Event('submit'));
                }
            });

            // ── Enhanced Select (Read-only styling) ──
            const readonlySelect = document.querySelector('.select-disabled');
            if (readonlySelect) {
                readonlySelect.style.color = 'var(--mdx-navy)';
                readonlySelect.style.fontWeight = '600';
            }

            // ── Tooltip for readonly fields ──
            const readonlyFields = document.querySelectorAll('.form-control[readonly], .select-disabled');
            readonlyFields.forEach(field => {
                field.title = 'This field is read-only';
            });
        });
    </script>
@endsection