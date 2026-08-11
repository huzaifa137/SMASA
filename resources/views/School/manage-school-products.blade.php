@extends('layouts-side-bar.master')

@php
    use App\Http\Controllers\Helper;
@endphp

@section('content')
    <div class="side-app">
        <style>
            .sp-card {
                background: white;
                border-radius: 10px;
                padding: 20px;
                margin-bottom: 20px;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            }

            .sp-section-title {
                font-size: 18px;
                font-weight: 600;
                margin-bottom: 20px;
                padding-bottom: 10px;
                border-bottom: 2px solid #e9ecef;
                color: #495057;
            }

            .sp-product-pill {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                background: #eef6ff;
                border: 1px solid #bfe0ff;
                color: #0b5ed7;
                border-radius: 20px;
                padding: 8px 16px;
                margin: 4px 8px 4px 0;
                font-size: 14px;
                font-weight: 500;
            }

            .sp-product-pill.is-primary {
                background: #eafbf1;
                border-color: #b7ecc9;
                color: #157347;
            }

            .sp-product-pill .badge {
                font-size: 10px;
            }

            .sp-empty-note {
                color: #6c757d;
                font-size: 14px;
            }

            .sp-warning-banner {
                background: #fff8e6;
                border: 1px solid #ffe08a;
                color: #7a5b00;
                padding: 14px 18px;
                border-radius: 8px;
                margin-bottom: 20px;
            }

            .sp-danger-banner {
                background: #fdecea;
                border: 1px solid #f5b5ac;
                color: #7a1f14;
                padding: 14px 18px;
                border-radius: 8px;
                margin-bottom: 20px;
            }

            .sp-impact-table td,
            .sp-impact-table th {
                padding: 8px 12px;
                font-size: 14px;
            }

            .sp-impact-table tr:not(:last-child) td,
            .sp-impact-table tr:not(:last-child) th {
                border-bottom: 1px solid #eee;
            }

            .sp-impact-count {
                font-weight: 700;
                color: #c0392b;
            }

            /* Table defaults to border-collapse: separate with a browser
                       border-spacing, which leaves a visible white seam between
                       adjacent <th> cells even when each has its own background
                       color. Collapsing removes that seam so the red header row
                       reads as one continuous bar edge-to-edge, matching the
                       "Data Deletion Impact" banner directly above it. */
            .sp-impact-table {
                width: 100%;
                border-collapse: collapse;
                border-spacing: 0;
            }
        </style>

        <div class="row">
            <div class="col-lg-12 col-xl-12 col-md-12 col-sm-12">
                <div class="card bg-primary">
                    @include('layouts.class-buttons')

                    <div class="card-body bg-light">

                        {{-- ═══════════════════════════════════════════════
                        CURRENT CATEGORIES
                        ════════════════════════════════════════════════ --}}
                        <div class="sp-card">
                            <h5 class="sp-section-title mb-0">
                                <i class="fas fa-layer-group me-2"></i> Current School Product Categories
                            </h5>

                            @if ($currentProducts->isEmpty())
                                <p class="sp-empty-note mb-0">This school does not have any product category
                                    attached yet.</p>
                            @else
                                <div>
                                    @foreach ($currentProducts as $product)
                                        <span class="sp-product-pill {{ $product->pivot->is_primary ? 'is-primary' : '' }}">
                                            <i
                                                class="fas {{ $product->pivot->is_primary ? 'fa-star' : 'fa-check-circle' }}"></i>
                                            {{ $product->md_name }}
                                            @if ($product->pivot->is_primary)
                                                <span class="badge bg-success text-white">Primary</span>
                                            @endif
                                        </span>
                                    @endforeach
                                </div>

                                @if ($school->hasMergedProducts())
                                    <p class="sp-empty-note mt-3 mb-0">
                                        <i class="fas fa-info-circle me-1"></i>
                                        This school has merged categories. Classes, subjects and students from
                                        every category above appear together throughout the system.
                                    </p>
                                @endif
                            @endif
                        </div>

                        {{-- ═══════════════════════════════════════════════
                        MERGE ANOTHER CATEGORY
                        ════════════════════════════════════════════════ --}}
                        <div class="sp-card">
                            <h5 class="sp-section-title mb-0">
                                <i class="fas fa-object-group me-2"></i> Merge Another Category In
                            </h5>

                            @if ($availableProducts->isEmpty())
                                <p class="sp-empty-note mb-0">
                                    This school already belongs to every available School Product category.
                                </p>
                            @else
                                <p class="sp-empty-note">
                                    Merging is additive and safe &mdash; nothing is deleted. Once merged, the new
                                    category's classes and subjects will start appearing on Create Class and
                                    Add Student right away.
                                </p>

                                <form id="mergeProductForm">
                                    @csrf
                                    <div class="row align-items-end">
                                        <div class="col-lg-6 col-md-12">
                                            <div class="form-group mb-0">
                                                <label class="form-label">Category To Merge In</label>
                                                <select class="form-control select2" id="merge_product_md_id"
                                                    name="product_md_id">
                                                    <option value="">-- Select --</option>
                                                    @foreach ($availableProducts as $product)
                                                        <option value="{{ $product->md_id }}">
                                                            {{ $product->md_name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-lg-3 col-md-12 mt-3 mt-lg-0">
                                            <button type="button" id="mergeProductBtn" class="btn btn-primary w-100">
                                                <i class="fas fa-object-group me-1"></i> Merge Category
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            @endif
                        </div>

                        {{-- ═══════════════════════════════════════════════
                        SPLIT CATEGORIES APART (admin only, destructive)
                        ════════════════════════════════════════════════ --}}
                        @if ($school->hasMergedProducts())
                            <div class="sp-card">
                                <h5 class="sp-section-title mb-0">
                                    <i class="fas fa-object-ungroup me-2"></i> Split Categories Apart
                                </h5>

                                @if (Helper::isTechSateAdminOrSchoolAdminsAlone())
                                    <div class="sp-danger-banner">
                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                        Splitting is permanent. Every class, stream, student, exam mark and fee
                                        record that belongs <strong>only</strong> to the category you drop will be
                                        deleted. The category you keep is left completely untouched.
                                    </div>

                                    <form id="splitPreviewForm">
                                        <div class="row align-items-end">
                                            <div class="col-lg-4 col-md-12">
                                                <div class="form-group mb-0">
                                                    <label class="form-label">Category To Remove</label>
                                                    <select class="form-control select2" id="remove_product_md_id">
                                                        <option value="">-- Select --</option>
                                                        @foreach ($currentProducts as $product)
                                                            <option value="{{ $product->md_id }}">
                                                                {{ $product->md_name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-lg-4 col-md-12 mt-3 mt-lg-0">
                                                <div class="form-group mb-0">
                                                    <label class="form-label">Category To Keep</label>
                                                    <select class="form-control select2" id="keep_product_md_id">
                                                        <option value="">-- Select --</option>
                                                        @foreach ($currentProducts as $product)
                                                            <option value="{{ $product->md_id }}">
                                                                {{ $product->md_name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-lg-4 col-md-12 mt-3 mt-lg-0">
                                                <button type="button" id="previewSplitBtn"
                                                    class="btn btn-outline-danger w-100">
                                                    <i class="fas fa-search me-1"></i> Preview What Will Be Deleted
                                                </button>
                                            </div>
                                        </div>
                                    </form>

                                    <div id="splitPreviewResult" class="mt-4" style="display: none;">
                                        <div class="fin-card-header"
                                            style="
                                        background: #dc2626;
                                        border-radius: 16px 16px 0 0;
                                        padding: 1.25rem 1.5rem;
                                        display: flex;
                                        align-items: center;
                                        justify-content: space-between;
                                        gap: 1.5rem;
                                        flex-wrap: wrap;
                                        min-height: 70px;
                                    ">
                                            <h3
                                                style="
                                            color: #fff; 
                                            margin: 0; 
                                            display: flex; 
                                            align-items: center; 
                                            gap: 0.75rem;
                                            flex-shrink: 0;
                                            font-size: clamp(1rem, 2.5vw, 1.25rem);
                                        ">
                                                <i class="fas fa-exclamation-triangle"></i>
                                                <span>Data Deletion Impact</span>
                                            </h3>

                                            <span class="badge-fin badge-red"
                                                style="
                                            background: rgba(255,255,255,0.2);
                                            color: #fff;
                                            border: 1px solid rgba(255,255,255,0.3);
                                            padding: 0.5rem 0.75rem;
                                            white-space: nowrap;
                                            flex-shrink: 0;
                                            font-size: clamp(0.75rem, 1.5vw, 0.875rem);
                                            display: inline-flex;
                                            align-items: center;
                                            gap: 0.5rem;
                                        ">
                                                <i class="fas fa-skull"></i> Permanent Action
                                            </span>
                                        </div>

                                        <table class="data-table sp-impact-table"
                                            style="min-width: auto; border-collapse: collapse; border-spacing: 0; width: 100%;">
                                            <thead>
                                                <tr style="background: #dc2626;">
                                                    <th
                                                        style="
                                                    background: #dc2626;
                                                    color: #fff;
                                                    border-radius: 0;
                                                    font-size: 0.8rem;
                                                    padding: 1rem 1.5rem;
                                                    text-align: left;
                                                    width: 60%;
                                                ">
                                                        <i class="fas fa-list-ul"></i> Data Category
                                                    </th>

                                                    <th
                                                        style="
                                                    background: #dc2626;
                                                    color: #fff;
                                                    border-radius: 0;
                                                    font-size: 0.8rem;
                                                    padding: 1rem 1.5rem;
                                                    text-align: center;
                                                    width: 40%;
                                                ">
                                                        <i class="fas fa-hashtag"></i> Count
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td
                                                        style="padding: 0.9rem 1.5rem; font-weight: 600; color: #1e293b; border-bottom: 1px solid #fef2f2;">
                                                        <i class="fas fa-chalkboard-teacher"
                                                            style="color: #dc2626; width: 1.5rem;"></i> Classes
                                                    </td>
                                                    <td
                                                        style="padding: 0.9rem 1.5rem; text-align: center; font-family: 'DM Mono', monospace; font-weight: 700; font-size: 1.1rem; color: #dc2626;">
                                                        <span class="sp-impact-count" id="impact_classes">0</span>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td
                                                        style="padding: 0.9rem 1.5rem; font-weight: 600; color: #1e293b; border-bottom: 1px solid #fef2f2;">
                                                        <i class="fas fa-code-branch"
                                                            style="color: #dc2626; width: 1.5rem;"></i>
                                                        Streams
                                                    </td>
                                                    <td
                                                        style="padding: 0.9rem 1.5rem; text-align: center; font-family: 'DM Mono', monospace; font-weight: 700; font-size: 1.1rem; color: #dc2626;">
                                                        <span class="sp-impact-count" id="impact_streams">0</span>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td
                                                        style="padding: 0.9rem 1.5rem; font-weight: 600; color: #1e293b; border-bottom: 1px solid #fef2f2;">
                                                        <i class="fas fa-book-open"
                                                            style="color: #dc2626; width: 1.5rem;"></i>
                                                        Class Subject Assignments
                                                    </td>
                                                    <td
                                                        style="padding: 0.9rem 1.5rem; text-align: center; font-family: 'DM Mono', monospace; font-weight: 700; font-size: 1.1rem; color: #dc2626;">
                                                        <span class="sp-impact-count" id="impact_class_subjects">0</span>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td
                                                        style="padding: 0.9rem 1.5rem; font-weight: 600; color: #1e293b; border-bottom: 1px solid #fef2f2;">
                                                        <i class="fas fa-user-graduate"
                                                            style="color: #dc2626; width: 1.5rem;"></i>
                                                        Students
                                                    </td>
                                                    <td
                                                        style="padding: 0.9rem 1.5rem; text-align: center; font-family: 'DM Mono', monospace; font-weight: 700; font-size: 1.1rem; color: #dc2626;">
                                                        <span class="sp-impact-count" id="impact_students">0</span>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td
                                                        style="padding: 0.9rem 1.5rem; font-weight: 600; color: #1e293b; border-bottom: 1px solid #fef2f2;">
                                                        <i class="fas fa-file-alt"
                                                            style="color: #dc2626; width: 1.5rem;"></i>
                                                        Examination Marks
                                                    </td>
                                                    <td
                                                        style="padding: 0.9rem 1.5rem; text-align: center; font-family: 'DM Mono', monospace; font-weight: 700; font-size: 1.1rem; color: #dc2626;">
                                                        <span class="sp-impact-count"
                                                            id="impact_examination_marks">0</span>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td
                                                        style="padding: 0.9rem 1.5rem; font-weight: 600; color: #1e293b; border-bottom: 1px solid #fef2f2;">
                                                        <i class="fas fa-chart-pie"
                                                            style="color: #dc2626; width: 1.5rem;"></i>
                                                        Exam Summaries
                                                    </td>
                                                    <td
                                                        style="padding: 0.9rem 1.5rem; text-align: center; font-family: 'DM Mono', monospace; font-weight: 700; font-size: 1.1rem; color: #dc2626;">
                                                        <span class="sp-impact-count"
                                                            id="impact_student_exam_summaries">0</span>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td
                                                        style="padding: 0.9rem 1.5rem; font-weight: 600; color: #1e293b; border-bottom: 1px solid #fef2f2;">
                                                        <i class="fas fa-calendar-check"
                                                            style="color: #dc2626; width: 1.5rem;"></i>
                                                        Attendance Records
                                                    </td>
                                                    <td
                                                        style="padding: 0.9rem 1.5rem; text-align: center; font-family: 'DM Mono', monospace; font-weight: 700; font-size: 1.1rem; color: #dc2626;">
                                                        <span class="sp-impact-count"
                                                            id="impact_attendance_records">0</span>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td
                                                        style="padding: 0.9rem 1.5rem; font-weight: 600; color: #1e293b; border-bottom: none;">
                                                        <i class="fas fa-coins"
                                                            style="color: #dc2626; width: 1.5rem;"></i> Fee
                                                        Records
                                                    </td>
                                                    <td
                                                        style="padding: 0.9rem 1.5rem; text-align: center; font-family: 'DM Mono', monospace; font-weight: 700; font-size: 1.1rem; color: #dc2626; border-bottom: none;">
                                                        <span class="sp-impact-count" id="impact_fee_records">0</span>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>

                                        <form id="splitConfirmForm" class="fin-card" style="margin-top: 1.5rem;">
                                            @csrf
                                            <div class="fin-card-header" style="background: #fafbff;">
                                                <h3><i class="fas fa-shield-alt"></i> Confirm Deletion</h3>
                                                <span class="badge-fin badge-red"><i
                                                        class="fas fa-exclamation-circle"></i> Requires
                                                    Verification</span>
                                            </div>
                                            <div style="padding: 1.5rem;">
                                                <div class="form-group" style="margin-bottom: 1.5rem;">
                                                    <label class="form-label"
                                                        style="display: block; font-size: 0.85rem; font-weight: 600; color: #1e293b; margin-bottom: 0.5rem;">
                                                        <i class="fas fa-key" style="color: #dc2626;"></i>
                                                        Type the school's name to confirm this permanent deletion
                                                    </label>
                                                    <div
                                                        style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 12px; padding: 0.75rem 1rem; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.75rem;">
                                                        <i class="fas fa-school"
                                                            style="color: #dc2626; font-size: 1.1rem;"></i>
                                                        <strong
                                                            style="color: #991b1b; font-size: 1rem;">{{ $school->name }}</strong>
                                                    </div>
                                                    <input type="text" class="form-control" id="confirm_school_name"
                                                        placeholder="Enter school name to confirm..."
                                                        style="width: 100%; padding: 0.75rem 1rem; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 0.95rem; transition: all 0.2s; outline: none; background: #fff;">
                                                    <style>
                                                        #confirm_school_name:focus {
                                                            border-color: #dc2626;
                                                            box-shadow: 0 0 0 4px rgba(220, 38, 38, 0.1);
                                                        }

                                                        #confirm_school_name.is-valid {
                                                            border-color: #059669;
                                                            box-shadow: 0 0 0 4px rgba(5, 150, 105, 0.1);
                                                            background: #f0fdf4;
                                                        }

                                                        #confirm_school_name.is-invalid {
                                                            border-color: #dc2626;
                                                            box-shadow: 0 0 0 4px rgba(220, 38, 38, 0.1);
                                                            background: #fef2f2;
                                                        }
                                                    </style>
                                                    <div id="schoolNameMatchIndicator"
                                                        style="margin-top: 0.5rem; font-size: 0.8rem; display: none;">
                                                        <i class="fas fa-check-circle" style="color: #059669;"></i>
                                                        <span style="color: #059669; font-weight: 600;">School name
                                                            matches</span>
                                                    </div>
                                                </div>
                                                <button type="button" id="confirmSplitBtn" class="btn-fin"
                                                    style="width: 100%; padding: 0.85rem 1.5rem; background: #dc2626; color: #fff; border: none; border-radius: 12px; font-size: 1rem; font-weight: 700; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; justify-content: center; gap: 0.75rem; opacity: 0.6; pointer-events: none;">
                                                    <i class="fas fa-trash-alt"></i>
                                                    <span>Permanently Split &amp; Delete School</span>
                                                </button>
                                                <p
                                                    style="margin-top: 0.75rem; font-size: 0.75rem; color: #94a3b8; text-align: center;">
                                                    <i class="fas fa-exclamation-circle" style="color: #dc2626;"></i>
                                                    This action is irreversible. All data listed above will be permanently
                                                    removed.
                                                </p>
                                            </div>
                                        </form>
                                    </div>

                                    <script>
                                        document.addEventListener('DOMContentLoaded', function() {
                                            const confirmInput = document.getElementById('confirm_school_name');
                                            const confirmBtn = document.getElementById('confirmSplitBtn');
                                            const schoolName = '{{ $school->name }}';
                                            const indicator = document.getElementById('schoolNameMatchIndicator');

                                            if (confirmInput && confirmBtn) {
                                                confirmInput.addEventListener('input', function() {
                                                    const entered = this.value.trim();
                                                    const matches = entered === schoolName;

                                                    // Update button state
                                                    if (matches) {
                                                        confirmBtn.style.opacity = '1';
                                                        confirmBtn.style.pointerEvents = 'auto';
                                                        confirmBtn.style.background = '#059669';
                                                        confirmBtn.style.boxShadow = '0 4px 16px rgba(5, 150, 105, 0.35)';
                                                        this.classList.remove('is-invalid');
                                                        this.classList.add('is-valid');
                                                        indicator.style.display = 'block';
                                                    } else {
                                                        confirmBtn.style.opacity = '0.6';
                                                        confirmBtn.style.pointerEvents = 'none';
                                                        confirmBtn.style.background = '#dc2626';
                                                        confirmBtn.style.boxShadow = 'none';
                                                        this.classList.remove('is-valid');
                                                        if (entered.length > 0) {
                                                            this.classList.add('is-invalid');
                                                        } else {
                                                            this.classList.remove('is-invalid');
                                                        }
                                                        indicator.style.display = 'none';
                                                    }
                                                });
                                            }

                                            // Update the impact counts if you have data
                                            function updateImpactCounts(data) {
                                                const mappings = {
                                                    'impact_classes': data.classes || 0,
                                                    'impact_streams': data.streams || 0,
                                                    'impact_class_subjects': data.class_subjects || 0,
                                                    'impact_students': data.students || 0,
                                                    'impact_examination_marks': data.examination_marks || 0,
                                                    'impact_student_exam_summaries': data.exam_summaries || 0,
                                                    'impact_attendance_records': data.attendance_records || 0,
                                                    'impact_fee_records': data.fee_records || 0
                                                };

                                                Object.keys(mappings).forEach(id => {
                                                    const el = document.getElementById(id);
                                                    if (el) {
                                                        el.textContent = mappings[id].toLocaleString();
                                                    }
                                                });
                                            }

                                            // Example: Update counts if you have data from backend
                                            // updateImpactCounts({ classes: 5, streams: 12, ... });
                                        });
                                    </script>
                                @else
                                    <p class="sp-warning-banner mb-0">
                                        <i class="fas fa-lock me-1"></i>
                                        Splitting merged categories apart is restricted to TechSate administrators
                                        because it permanently deletes student, marks and finance data. Contact
                                        TechSate support if you need a category removed.
                                    </p>
                                @endif
                            </div>
                        @endif

                    </div>
                </div>
            </div>
        </div>
         </div>
            </div>
        </div>


        <script>
            $(document).ready(function() {
                const mergeUrl = "{{ route('school.products.merge') }}";
                const previewUrl = "{{ route('school.products.split.preview') }}";
                const splitUrl = "{{ route('school.products.split') }}";
                const csrfToken = "{{ csrf_token() }}";
                let lastImpact = null;

                // ── Merge ────────────────────────────────────────────
$('#mergeProductBtn').on('click', function() {
    const productMdId = $('#merge_product_md_id').val();

    if (!productMdId) {
        Swal.fire({
            icon: 'warning',
            title: 'Pick A Category',
            text: 'Please select which category you want to merge in.'
        });
        return;
    }

    Swal.fire({
        title: 'Merge This Category?',
        text: 'Its classes and subjects will start appearing alongside your existing ones. This is safe and reversible.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, Merge It',
        confirmButtonColor: '#0d6efd',
        showLoaderOnConfirm: true,
        preConfirm: () => {
            return new Promise((resolve, reject) => {
                $.post(mergeUrl, {
                    _token: csrfToken,
                    product_md_id: productMdId
                }).done(function(response) {
                    resolve(response);
                }).fail(function(xhr) {
                    reject(xhr);
                });
            });
        },
        allowOutsideClick: () => !Swal.isLoading()
    }).then((result) => {
        if (result.isConfirmed) {
            // Show success toast
            Swal.fire({
                icon: 'success',
                title: 'Merged Successfully!',
                text: 'The category has been merged. The page will reload.',
                timer: 2000,
                timerProgressBar: true,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
            }).then(() => {
                window.location.reload();
            });
        }
    }).catch((error) => {
        Swal.fire({
            icon: 'error',
            title: 'Could Not Merge',
            text: (error.responseJSON && error.responseJSON.message) ||
                'Something went wrong. Please try again.'
        });
    });
});

                // ── Preview Split ────────────────────────────────────
                $('#previewSplitBtn').on('click', function() {
                    const removeId = $('#remove_product_md_id').val();
                    const keepId = $('#keep_product_md_id').val();

                    if (!removeId || !keepId) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Select Both Categories',
                            text: 'Please select which category to remove and which one to keep.'
                        });
                        return;
                    }

                    if (removeId === keepId) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Choose Different Categories',
                            text: 'The category to remove and the category to keep must be different.'
                        });
                        return;
                    }

                    $.post(previewUrl, {
                        _token: csrfToken,
                        remove_product_md_id: removeId,
                        keep_product_md_id: keepId
                    }).done(function(response) {
                        lastImpact = response.impact;

                        $('#impact_classes').text(response.impact.classes);
                        $('#impact_streams').text(response.impact.streams);
                        $('#impact_class_subjects').text(response.impact.class_subjects);
                        $('#impact_students').text(response.impact.students);
                        $('#impact_examination_marks').text(response.impact.examination_marks);
                        $('#impact_student_exam_summaries').text(response.impact
                        .student_exam_summaries);
                        $('#impact_attendance_records').text(response.impact.attendance_records);
                        $('#impact_fee_records').text(response.impact.fee_records);

                        $('#splitPreviewResult').slideDown();
                    }).fail(function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Could Not Preview Split',
                            text: (xhr.responseJSON && xhr.responseJSON.message) ||
                                'Something went wrong. Please try again.'
                        });
                    });
                });

// ── Confirm & Execute Split ──────────────────────────
$('#confirmSplitBtn').on('click', function() {
    const removeId = $('#remove_product_md_id').val();
    const keepId = $('#keep_product_md_id').val();
    const confirmName = $('#confirm_school_name').val();

    if (!confirmName) {
        Swal.fire({
            icon: 'warning',
            title: 'Type The School Name',
            text: 'Please type the school\'s name exactly to confirm this permanent deletion.'
        });
        return;
    }

    Swal.fire({
        title: 'Are You Absolutely Sure?',
        html: 'This will <strong>permanently delete</strong> ' +
            (lastImpact ? lastImpact.classes : 0) + ' class(es) and ' +
            (lastImpact ? lastImpact.students : 0) +
            ' student(s), along with all their marks, attendance and fee records. This cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, Permanently Delete',
        confirmButtonColor: '#dc2626',
        cancelButtonText: 'Cancel',
        showLoaderOnConfirm: true,
        preConfirm: () => {
            return new Promise((resolve, reject) => {
                $.post(splitUrl, {
                    _token: csrfToken,
                    remove_product_md_id: removeId,
                    keep_product_md_id: keepId,
                    confirm_school_name: confirmName
                }).done(function(response) {
                    resolve(response);
                }).fail(function(xhr) {
                    reject(xhr);
                });
            });
        },
        allowOutsideClick: () => !Swal.isLoading()
    }).then((result) => {
        if (result.isConfirmed) {
            // Show success message
            Swal.fire({
                icon: 'success',
                title: 'Split Complete!',
                text: result.value?.message || 'Categories have been split & deleted successfully.',
                confirmButtonText: 'OK',
                confirmButtonColor: '#059669'
            }).then(() => {
                // Reload the page after user clicks OK
                window.location.href = "{{ route('school.products.manage') }}";
            });
        }
    }).catch((error) => {
        Swal.fire({
            icon: 'error',
            title: 'Could Not Split',
            text: (error.responseJSON && error.responseJSON.message) ||
                'Something went wrong. Please try again.'
        });
    });
});

                @if (session('success'))
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: @json(session('success')),
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#4f46e5'
                    });
                @endif

                @if (session('error'))
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: @json(session('error')),
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#dc2626'
                    });
                @endif
            });
        </script>
    </div>
@endsection
