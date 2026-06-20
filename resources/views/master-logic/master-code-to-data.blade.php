{{-- resources/views/master-logic/master-code-to-data.blade.php --}}
@extends('layouts-side-bar.master')

@section('css')
    @include('master-logic.partials._styles')
@endsection

@section('content')
    <?php
    use App\Http\Controllers\Helper;
    use App\Helpers\PermissionHelper;

    $totalCategories = $all_data->count();
    $totals = [];
    $totalRecords = 0;
    $emptyCategories = 0;

    foreach ($all_data as $item) {
        $count = Helper::totalRows('master_datas', 'md_master_code_id', $item->id);
        $totals[$item->id] = $count;
        $totalRecords += $count;
        if ($count == 0) {
            $emptyCategories++;
        }
    }

    $avgPerCategory = $totalCategories > 0 ? round($totalRecords / $totalCategories, 1) : 0;
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

    <div class="mdx-page">
        <div class="mdx-topbar"></div>

        {{-- ── Header ── --}}
        <div class="mdx-header">
            <div class="mdx-header-left">
                <div class="mdx-header-icon"><i class="fa fa-cubes"></i></div>
                <div>
                    <h4 class="mdx-header-title">Master Codes</h4>
                    <div class="mdx-header-sub">
                        {{ $totalCategories }} categor{{ $totalCategories == 1 ? 'y' : 'ies' }} &middot;
                        {{ $totalRecords }} total record{{ $totalRecords == 1 ? '' : 's' }}
                    </div>
                </div>
            </div>
            <div class="mdx-header-actions">
                @if(PermissionHelper::canFeature('create_master_codes'))
                    <button type="button" id="addCodeBtn" class="mdx-btn mdx-btn-primary">
                        <i class="fa fa-plus"></i> Add Record
                    </button>
                @endif
            </div>
        </div>

        {{-- ── Stat strip ── --}}
        <div class="mdx-stat-strip">
            <div class="mdx-stat m-indigo">
                <div class="mdx-stat-top">
                    <div class="mdx-stat-icon"><i class="fa fa-folder"></i></div>
                </div>
                <div class="mdx-stat-value">{{ $totalCategories }}</div>
                <div class="mdx-stat-label">Master Codes</div>
            </div>
            <div class="mdx-stat m-teal">
                <div class="mdx-stat-top">
                    <div class="mdx-stat-icon"><i class="fa fa-database"></i></div>
                </div>
                <div class="mdx-stat-value">{{ $totalRecords }}</div>
                <div class="mdx-stat-label">Total Records</div>
            </div>
            <div class="mdx-stat m-sky">
                <div class="mdx-stat-top">
                    <div class="mdx-stat-icon"><i class="fa fa-chart-line"></i></div>
                </div>
                <div class="mdx-stat-value">{{ $avgPerCategory }}</div>
                <div class="mdx-stat-label">Avg / Category</div>
            </div>
            <div class="mdx-stat m-amber">
                <div class="mdx-stat-top">
                    <div class="mdx-stat-icon"><i class="fa fa-inbox"></i></div>
                </div>
                <div class="mdx-stat-value">{{ $emptyCategories }}</div>
                <div class="mdx-stat-label">Empty Categories</div>
            </div>
        </div>

        {{-- ── Add record (collapsible) ── --}}
        @if(PermissionHelper::canFeature('create_master_codes'))
            <div class="mdx-panel mdx-collapse" id="addCodeForm">
                <div class="mdx-panel-head">
                    <div>
                        <div class="mdx-panel-label">New Entry</div>
                        <div class="mdx-panel-title">Add a Master Code</div>
                    </div>
                </div>
                <div class="mdx-panel-body">
                    <form id="myForm" action="{{ route('send-master-code') }}" method="POST">
                        @csrf
                        <div class="mdx-form-grid">
                            <div class="mdx-field mdx-col-3">
                                <label for="mc_code">Master Code</label>
                                <input class="form-control mdx-input" type="text" name="mc_code" id="mc_code" required>
                            </div>
                            <div class="mdx-field mdx-col-9">
                                <label for="mc_name">Master Code Name</label>
                                <input class="form-control mdx-input" type="text" name="mc_name" id="mc_name">
                            </div>
                            <div class="mdx-field mdx-col-12">
                                <label for="mc_description">Master Description</label>
                                <textarea class="form-control mdx-input" name="mc_description" id="mc_description"
                                    required></textarea>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button class="mdx-btn mdx-btn-primary" type="submit">
                                <i class="fa fa-fw fa-save"></i> Save
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        {{-- ── Categories ── --}}
        <div class="mdx-panel">
            <div class="mdx-panel-head">
                <div>
                    <div class="mdx-panel-label">Browse</div>
                    <div class="mdx-panel-title">All Master Codes</div>
                </div>
            </div>
            <div class="mdx-panel-body">

                @if ($all_data->count())
                    <div class="mdx-toolbar">
                        <div class="mdx-search-wrap">
                            <i class="fa fa-search"></i>
                            <input type="text" id="codeSearch" placeholder="Search by code or name...">
                        </div>
                    </div>

                    <div class="mdx-card-grid" id="codeGrid">
                        @foreach ($all_data as $item)
                            <?php        $count = $totals[$item->id] ?? 0; ?>
                            <div class="mdx-card" data-search="{{ strtolower($item->mc_code . ' ' . $item->mc_name) }}">
                                <div class="mdx-card-top">
                                    <div class="mdx-card-icon">{{ strtoupper(substr($item->mc_code, 0, 2)) }}</div>
                                    <div class="mdx-card-count">{{ $count }} record{{ $count == 1 ? '' : 's' }}</div>
                                </div>
                                <div>
                                    <div class="mdx-card-code">{{ $item->mc_code }}</div>
                                    <h5 class="mdx-card-title">{{ $item->mc_name }}</h5>
                                </div>
                                <div class="mdx-card-desc">{{ $item->mc_description }}</div>
                                <div class="mdx-card-footer">
                                    <a href="{{ url('master-data/master-code-list/' . $item->mc_id) }}"
                                        class="mdx-btn mdx-btn-outline mdx-btn-sm">
                                        <i class="fa fa-list"></i> View Records
                                    </a>
                                    <div class="mdx-card-actions">
                                        @if(PermissionHelper::canFeature('edit_master_codes'))
                                            <a href="{{ url('master-data/edit-code/' . $item->id) }}"
                                                class="mdx-icon-btn i-edit edit-record-btn"
                                                data-url="{{ url('master-data/edit-code/' . $item->id) }}" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endif
                                       @if(PermissionHelper::canFeature('delete_master_codes'))
    <a href="{{ url('delete-code/' . $item->mc_id) }}"
        class="mdx-icon-btn i-delete delete-record-btn"
        data-url="{{ url('delete-code/' . $item->mc_id) }}"
        title="Delete">
        <i class="fas fa-trash-alt"></i>
    </a>
@endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mdx-empty" id="noResults" style="display:none;">
                        <i class="fa fa-search"></i>
                        <h5>No matches found</h5>
                        <p>Try a different search term.</p>
                    </div>
                @else
                    <div class="mdx-empty">
                        <i class="fa fa-cubes"></i>
                        <h5>No master codes yet</h5>
                        <p>Get started by adding your first master code above.</p>
                    </div>
                @endif

            </div>
        </div>
    </div>
    </div>
    </div>
    </div>
@endsection

@section('js')
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

            /* ── Live card search ── */
            $('#codeSearch').on('keyup', function () {
                var term = $(this).val().trim().toLowerCase();
                var visible = 0;

                $('#codeGrid .mdx-card').each(function () {
                    var match = $(this).attr('data-search').indexOf(term) > -1;
                    $(this).toggle(match);
                    if (match) visible++;
                });

                $('#noResults').toggle(visible === 0 && term.length > 0);
            });
        });

        /* ── SweetAlert Delete Confirmation ── */
$(document).on('click', '.delete-record-btn', function(e) {
    e.preventDefault();

    const deleteUrl = $(this).data('url') || $(this).attr('href');

    Swal.fire({
        title: 'Delete Master Code?',
        html: `
            <div style="font-size:14px;">
                This will permanently delete the master code
                <strong>and all associated records</strong>.
                <br><br>
                This action cannot be undone.
            </div>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="fas fa-trash-alt"></i> Yes, Delete',
        cancelButtonText: 'Cancel',
        reverseButtons: true,
        focusCancel: true
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Deleting...',
                text: 'Please wait.',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            window.location.href = deleteUrl;
        }
    });
});
    </script>
@endsection