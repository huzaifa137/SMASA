{{-- resources/views/master-logic/master-code.blade.php --}}
@extends('layouts-side-bar.master')

@section('css')
    @include('master-logic.partials._styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        /* ════════════════════════════════════════════════════════════
                   MASTER CODE — Advanced Layout Enhancements
                   ════════════════════════════════════════════════════════════ */

        /* ── Quick Action Bar ── */
        .mdx-quick-actions {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
            margin-bottom: 1.5rem;
        }

        .mdx-quick-action-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1.2rem;
            border-radius: 10px;
            font-size: 0.78rem;
            font-weight: 600;
            border: 1px solid #2c29ca;
            background: var(--mdx-white);
            color: var(--mdx-navy);
            transition: all 0.2s;
            text-decoration: none;
        }

        .mdx-quick-action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(44, 41, 202, 0.12);
            text-decoration: none;
            border-color: var(--mdx-indigo3);
        }

        .mdx-quick-action-btn i {
            font-size: 0.9rem;
        }

        .mdx-quick-action-btn.primary {
            background: linear-gradient(135deg, var(--mdx-indigo), var(--mdx-indigo3));
            color: #fff;
            border-color: var(--mdx-indigo);
        }

        .mdx-quick-action-btn.primary:hover {
            background: linear-gradient(135deg, var(--mdx-indigo2), var(--mdx-indigo));
            color: #fff;
        }

        .mdx-quick-action-btn.success {
            background: linear-gradient(135deg, #059669, #10b981);
            color: #fff;
            border-color: #059669;
        }

        .mdx-quick-action-btn.success:hover {
            background: linear-gradient(135deg, #047857, #059669);
            color: #fff;
        }

        .mdx-quick-action-btn.warning {
            background: linear-gradient(135deg, #2c29ca, #2c29ca);
            color: #fff;
            border-color: #2c29ca;
        }

        .mdx-quick-action-btn.warning:hover {
            background: linear-gradient(135deg, #b45309, #2c29ca);
            color: #fff;
        }

        /* ── View Toggle ── */
        .mdx-view-toggle {
            display: flex;
            gap: 0.3rem;
            background: var(--mdx-bg2);
            padding: 0.25rem;
            border-radius: 10px;
            border: 1px solid #2c29ca;
        }

        .mdx-view-toggle button {
            padding: 0.45rem 0.9rem;
            border: none;
            background: transparent;
            border-radius: 8px;
            font-size: 0.72rem;
            font-weight: 600;
            color: var(--mdx-slate);
            transition: all 0.2s;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        .mdx-view-toggle button:hover {
            color: var(--mdx-indigo);
            background: rgba(44, 41, 202, 0.05);
        }

        .mdx-view-toggle button.active {
            background: var(--mdx-white);
            color: var(--mdx-indigo);
            box-shadow: 0 2px 8px rgba(44, 41, 202, 0.12);
        }

        /* ── Advanced Card Grid ── */
        .mdx-card-grid-advanced {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.25rem;
        }

        .mdx-card-advanced {
            background: var(--mdx-white);
            border: 1.5px solid #2c29ca;
            border-radius: var(--mdx-r);
            overflow: hidden;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
        }

        .mdx-card-advanced:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 32px rgba(44, 41, 202, 0.12);
            border-color: #60a5fa;
        }

        .mdx-card-advanced .card-header {
            padding: 1rem 1.25rem;
            background: linear-gradient(135deg, #f8faff, #f4f6fd);
            border-bottom: 1px solid #2c29ca;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .mdx-card-advanced .card-header .badge-status {
            padding: 0.2rem 0.6rem;
            border-radius: 20px;
            font-size: 0.6rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .badge-status.active {
            background: #d1fae5;
            color: #065f46;
        }

        .badge-status.inactive {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-status.empty {
            background: #fee2e2;
            color: #991b1b;
        }

        .mdx-card-advanced .card-body {
            padding: 1.25rem;
        }

        .mdx-card-advanced .card-code {
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #2c29ca !important;
            margin-bottom: 0.3rem;
        }

        .mdx-card-advanced .card-title {
            font-size: 1rem;
            font-weight: 700;
            color: var(--mdx-navy);
            margin: 0 0 0.4rem 0;
            line-height: 1.3;
        }

        .mdx-card-advanced .card-desc {
            font-size: 0.8rem;
            color: var(--mdx-slate);
            line-height: 1.6;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            min-height: 2.6rem;
        }

        .mdx-card-advanced .card-stats {
            display: flex;
            gap: 1.5rem;
            padding: 0.75rem 1.25rem;
            background: var(--mdx-bg2);
            border-top: 1px solid #2c29ca;
        }

        .mdx-card-advanced .card-stats .stat-item {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.75rem;
            color: var(--mdx-slate);
        }

        .mdx-card-advanced .card-stats .stat-item strong {
            color: var(--mdx-navy);
            font-size: 0.9rem;
        }

        .mdx-card-advanced .card-actions {
            padding: 0.75rem 1.25rem;
            display: flex;
            gap: 0.4rem;
            border-top: 1px solid #2c29ca;
            flex-wrap: wrap;
        }

        .mdx-card-advanced .card-actions .mdx-btn {
            flex: 1;
            min-width: 80px;
            justify-content: center;
        }

        /* ── Progress Bar ── */
        .mdx-progress-bar {
            width: 100%;
            height: 4px;
            background: #e5e7eb;
            border-radius: 2px;
            overflow: hidden;
            margin-top: 0.5rem;
        }

        .mdx-progress-bar .progress-fill {
            height: 100%;
            border-radius: 2px;
            transition: width 0.6s ease;
            background: linear-gradient(90deg, var(--mdx-indigo), var(--mdx-indigo3));
        }

        .mdx-progress-bar .progress-fill.high {
            background: linear-gradient(90deg, #059669, #10b981);
        }

        .mdx-progress-bar .progress-fill.medium {
            background: linear-gradient(90deg, #2c29ca, #2c29ca);
        }

        .mdx-progress-bar .progress-fill.low {
            background: linear-gradient(90deg, #dc2626, #ef4444);
        }

        /* ── Table View ── */
        .mdx-table-view {
            display: none;
        }

        .mdx-table-view.visible {
            display: block;
        }

        .mdx-grid-view.hidden {
            display: none;
        }

        /* ── Quick Stats Bar ── */
        .mdx-quick-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 0.75rem;
            margin-bottom: 1.5rem;
        }

        .mdx-quick-stat {
            background: var(--mdx-white);
            border: 1px solid #2c29ca;
            border-radius: var(--mdx-r);
            padding: 0.75rem 1rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            transition: all 0.2s;
        }

        .mdx-quick-stat:hover {
            border-color: #60a5fa;
            box-shadow: 0 2px 8px rgba(44, 41, 202, 0.06);
        }

        .mdx-quick-stat .stat-icon {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
            flex-shrink: 0;
        }

        .mdx-quick-stat .stat-icon.purple {
            background: #eef2ff;
            color: var(--mdx-indigo);
        }

        .mdx-quick-stat .stat-icon.green {
            background: #d1fae5;
            color: #059669;
        }

        .mdx-quick-stat .stat-icon.blue {
            background: #dbeafe;
            color: #2563eb;
        }

        .mdx-quick-stat .stat-icon.orange {
            background: #fef3c7;
            color: #2c29ca;
        }

        .mdx-quick-stat .stat-info .stat-value {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--mdx-navy);
            line-height: 1.2;
        }

        .mdx-quick-stat .stat-info .stat-label {
            font-size: 0.65rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--mdx-slate);
        }

        /* ── Responsive ── */
        @media (max-width: 768px) {
            .mdx-card-grid-advanced {
                grid-template-columns: 1fr;
            }

            .mdx-quick-stats {
                grid-template-columns: 1fr 1fr;
            }

            .mdx-quick-actions {
                flex-direction: column;
            }

            .mdx-quick-action-btn {
                width: 100%;
                justify-content: center;
            }

            .mdx-view-toggle {
                width: 100%;
            }

            .mdx-view-toggle button {
                flex: 1;
                justify-content: center;
            }
        }

        @media (max-width: 480px) {
            .mdx-quick-stats {
                grid-template-columns: 1fr;
            }
        }

        /* ── DataTable Overrides for Advanced View ── */
        .mdx-table-view .dataTables_wrapper {
            padding: 0 1rem 1rem;
        }

        .mdx-table-view .mdx-table thead th {
            background: #f8faff !important;
            color: var(--mdx-navy) !important;
            font-weight: 700;
            border-bottom: 2px solid #2c29ca;
        }

        .mdx-table-view .mdx-table tbody tr {
            transition: background 0.15s;
        }

        .mdx-table-view .mdx-table tbody tr:hover {
            background: #f8faff;
        }

        /* ── Animations ── */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .mdx-card-advanced {
            animation: fadeInUp 0.4s ease forwards;
        }

        .mdx-card-advanced:nth-child(2) {
            animation-delay: 0.05s;
        }

        .mdx-card-advanced:nth-child(3) {
            animation-delay: 0.1s;
        }

        .mdx-card-advanced:nth-child(4) {
            animation-delay: 0.15s;
        }

        .mdx-card-advanced:nth-child(5) {
            animation-delay: 0.2s;
        }

        .mdx-card-advanced:nth-child(6) {
            animation-delay: 0.25s;
        }

        .mdx-card-advanced:nth-child(7) {
            animation-delay: 0.3s;
        }

        .mdx-card-advanced:nth-child(8) {
            animation-delay: 0.35s;
        }
    </style>
@endsection

@section('content')
    <?php
    use App\Http\Controllers\Helper;
    use App\Helpers\PermissionHelper;

    $totalCategories = $all_data->count();
    $totals = [];
    $totalRecords = 0;
    $emptyCategories = 0;
    $maxRecords = 0;

    foreach ($all_data as $item) {
        $count = Helper::totalRows('master_datas', 'md_master_code_id', $item->id);
        $totals[$item->id] = $count;
        $totalRecords += $count;
        if ($count == 0) {
            $emptyCategories++;
        }
        if ($count > $maxRecords) {
            $maxRecords = $count;
        }
    }

    $avgPerCategory = $totalCategories > 0 ? round($totalRecords / $totalCategories, 1) : 0;
    $hasData = $totalCategories > 0;
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
                    <h4 class="mdx-header-title">Master Code Management</h4>
                    <div class="mdx-header-sub">
                        <span>{{ $totalCategories }} categor{{ $totalCategories == 1 ? 'y' : 'ies' }}</span>
                        <span class="mx-1">·</span>
                        <span>{{ $totalRecords }} total record{{ $totalRecords == 1 ? '' : 's' }}</span>
                        @if($hasData)
                            <span class="mx-1">·</span>
                            <span>{{ $avgPerCategory }} avg per category</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="mdx-header-actions">
                @if(PermissionHelper::canFeature('create_master_codes'))
                    <button type="button" id="headerAddCodeBtn" class="mdx-btn mdx-btn-primary">
                        <i class="fa fa-plus"></i> New Category
                    </button>
                @endif
            </div>
        </div>

        {{-- ── Quick Stats ── --}}
        <div class="mdx-quick-stats">
            <div class="mdx-quick-stat">
                <div class="stat-icon purple"><i class="fa fa-folder"></i></div>
                <div class="stat-info">
                    <div class="stat-value">{{ $totalCategories }}</div>
                    <div class="stat-label">Total Categories</div>
                </div>
            </div>
            <div class="mdx-quick-stat">
                <div class="stat-icon green"><i class="fa fa-database"></i></div>
                <div class="stat-info">
                    <div class="stat-value">{{ $totalRecords }}</div>
                    <div class="stat-label">Total Records</div>
                </div>
            </div>
            <div class="mdx-quick-stat">
                <div class="stat-icon blue"><i class="fa fa-chart-bar"></i></div>
                <div class="stat-info">
                    <div class="stat-value">{{ $avgPerCategory }}</div>
                    <div class="stat-label">Average per Category</div>
                </div>
            </div>
            <div class="mdx-quick-stat">
                <div class="stat-icon orange"><i class="fa fa-inbox"></i></div>
                <div class="stat-info">
                    <div class="stat-value">{{ $emptyCategories }}</div>
                    <div class="stat-label">Empty Categories</div>
                </div>
            </div>
        </div>

        {{-- ── Quick Actions ── --}}
        <div class="mdx-quick-actions">
            @if(PermissionHelper::canFeature('create_master_codes'))
                <button type="button" id="quickAddCodeBtn" class="mdx-quick-action-btn primary">
                    <i class="fa fa-plus-circle"></i> Add New Category
                </button>
            @endif
            @if($hasData)
                <button type="button" id="exportBtn" class="mdx-quick-action-btn success">
                    <i class="fa fa-file-export"></i> Export Data
                </button>
            @endif
            <button type="button" id="refreshBtn" class="mdx-quick-action-btn warning">
                <i class="fa fa-sync"></i> Refresh
            </button>
        </div>

        {{-- ── Add Record (Collapsible) ── --}}
        @if(PermissionHelper::canFeature('create_master_codes'))
            <div class="mdx-panel mdx-collapse" id="addCodeForm">
                <div class="mdx-panel-head">
                    <div>
                        <div class="mdx-panel-label">New Entry</div>
                        <div class="mdx-panel-title">Create a New Master Code Category</div>
                    </div>
                </div>
                <div class="mdx-panel-body">
                    <form id="myForm" action="{{ route('send-master-code') }}" method="POST">
                        @csrf
                        <div class="mdx-form-grid">
                            <div class="mdx-field mdx-col-4">
                                <label for="mc_code">Master Code <span class="text-danger">*</span></label>
                                <input class="form-control mdx-input" type="text" name="mc_code" id="mc_code" required
                                    placeholder="e.g. PRD001">
                            </div>
                            <div class="mdx-field mdx-col-8">
                                <label for="mc_name">Category Name <span class="text-danger">*</span></label>
                                <input class="form-control mdx-input" type="text" name="mc_name" id="mc_name" required
                                    placeholder="e.g. Product Categories">
                            </div>
                            <div class="mdx-field mdx-col-12">
                                <label for="mc_description">Description</label>
                                <textarea class="form-control mdx-input" name="mc_description" id="mc_description" rows="3"
                                    placeholder="Describe the purpose of this master code category..."></textarea>
                            </div>
                        </div>
                        <div class="mt-4 d-flex gap-2">
                            <button class="mdx-btn mdx-btn-primary" type="submit">
                                <i class="fa fa-fw fa-save"></i> Save Category
                            </button> &nbsp;
                            <button type="button" id="cancelFormBtn" class="mdx-btn mdx-btn-outline">
                                <i class="fa fa-times"></i> Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        {{-- ── Main Panel ── --}}
        <div class="mdx-panel">
            <div class="mdx-panel-head">
                <div>
                    <div class="mdx-panel-label">Browse</div>
                    <div class="mdx-panel-title">All Master Code Categories</div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <div class="mdx-view-toggle">
                        <button type="button" id="gridViewBtn" class="active" title="Grid View">
                            <i class="fa fa-th"></i> Grid
                        </button>
                        <button type="button" id="tableViewBtn" title="Table View">
                            <i class="fa fa-table"></i> Table
                        </button>
                    </div>
                </div>
            </div>
            <div class="mdx-panel-body">
                {{-- ── Search ── --}}
                <div class="mdx-toolbar">
                    <div class="mdx-search-wrap" style="flex:1; max-width:400px;">
                        <i class="fa fa-search"></i>
                        <input type="text" id="codeSearch" placeholder="Search by code, name, or description...">
                    </div>
                    <div>
                        <span class="text-muted" style="font-size:0.75rem;" id="resultCount">
                            Showing {{ $totalCategories }} categor{{ $totalCategories == 1 ? 'y' : 'ies' }}
                        </span>
                    </div>
                </div>

                {{-- ── Grid View ── --}}
                <div class="mdx-grid-view" id="gridView">
                    @if ($hasData)
                        <div class="mdx-card-grid-advanced" id="codeGrid">
                            @foreach ($all_data as $item)
                                            <?php 
                                                                                    $count = $totals[$item->id] ?? 0;
                                $percentage = $maxRecords > 100 ? round(($count / $maxRecords) * 100) : 100;
                                $statusClass = $count > 10 ? 'high' : ($count > 0 ? 'medium' : 'low');
                                $badgeClass = $count > 0 ? 'active' : 'empty';
                                $badgeText = $count > 0 ? 'Active' : 'Empty';
                                                                                ?>
                                            <div class="mdx-card-advanced"
                                                data-search="{{ strtolower($item->mc_code . ' ' . $item->mc_name . ' ' . $item->mc_description) }}">
                                                <div class="card-header">
                                                    <span class="mdx-card-code">{{ $item->mc_code }}</span>
                                                    <span class="badge-status {{ $badgeClass }}">{{ $badgeText }}</span>
                                                </div>
                                                <div class="card-body">
                                                    <h5 class="card-title">{{ $item->mc_name }}</h5>
                                                    <div class="card-desc">{{ $item->mc_description ?: 'No description provided.' }}</div>
                                                    <div class="mdx-progress-bar">
                                                        <div class="progress-fill {{ $statusClass }}" style="width: {{ $percentage }}%;"></div>
                                                    </div>
                                                </div>
                                                <div class="card-stats">
                                                    <div class="stat-item">
                                                        <i class="fa fa-list-ul"></i>
                                                        <strong>{{ $count }}</strong> record{{ $count == 1 ? '' : 's' }}
                                                    </div>
                                                    <div class="stat-item">
                                                        <i class="fa fa-percent"></i>
                                                        {{ $percentage }}% of max
                                                    </div>
                                                </div>
                                                <div class="card-actions">
                                                    <a href="{{ url('master-data/master-code-list/' . $item->mc_id) }}"
                                                        class="mdx-btn mdx-btn-outline mdx-btn-sm">
                                                        <i class="fa fa-list"></i> View
                                                    </a>
                                                    @if(PermissionHelper::canFeature('edit_master_codes'))
                                                        <a href="{{ url('master-data/edit-code/' . $item->id) }}"
                                                            class="mdx-btn mdx-btn-outline mdx-btn-sm edit-record-btn"
                                                            style="border-color:#2c29ca; color:#2c29ca;">
                                                            <i class="fas fa-edit"></i> Edit
                                                        </a>
                                                    @endif
                                                    @if(PermissionHelper::canFeature('delete_master_codes'))
                                                       <a href="{{ url('delete-code/' . $item->mc_id) }}"
    class="mdx-btn mdx-btn-danger-outline mdx-btn-sm delete-record-btn">
    <i class="fas fa-trash-alt"></i>
</a>
                                                    @endif
                                                </div>
                                            </div>
                            @endforeach
                        </div>
                        <div class="mdx-empty" id="noResults" style="display:none;">
                            <i class="fa fa-search"></i>
                            <h5>No matches found</h5>
                            <p>Try adjusting your search term.</p>
                        </div>
                    @else
                        <div class="mdx-empty">
                            <i class="fa fa-cubes"></i>
                            <h5>No master codes yet</h5>
                            <p>Get started by clicking the <strong>"Add New Category"</strong> button above.</p>
                        </div>
                    @endif
                </div>

                {{-- ── Table View ── --}}
                <div class="mdx-table-view" id="tableView">
                    <div class="mdx-table-wrap" style="border:none;border-radius:0;padding:0;">
                        <table class="mdx-table" id="table" style="width:100%;">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Category Name</th>
                                    <th>Description</th>
                                    <th class="text-center">Records</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($all_data as $item)
                                    <?php    $count = $totals[$item->id] ?? 0; ?>
                                    <tr>
                                        <td><span class="mdx-code-pill">{{ $item->mc_code }}</span></td>
                                        <td><strong>{{ $item->mc_name }}</strong></td>
                                        <td style="max-width:200px; white-space:normal; word-wrap:break-word;">
                                            {{ $item->mc_description ?: '—' }}
                                        </td>
                                        <td class="text-center">
                                            <span class="mdx-sidebar-badge" style="font-size:0.85rem; padding:0.2rem 0.7rem;">
                                                {{ $count }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge-status {{ $count > 0 ? 'active' : 'empty' }}"
                                                style="font-size:0.65rem; padding:0.15rem 0.6rem;">
                                                {{ $count > 0 ? 'Active' : 'Empty' }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center gap-1" style="gap:0.3rem;">
                                                <a href="{{ url('master-data/master-code-list/' . $item->mc_id) }}"
                                                    class="mdx-btn mdx-btn-sm mdx-btn-outline" title="View Records">
                                                    <i class="fa fa-list"></i>
                                                </a>
                                                @if(PermissionHelper::canFeature('edit_master_codes'))
                                                    <a href="{{ url('master-data/edit-code/' . $item->id) }}"
                                                        class="mdx-btn mdx-btn-sm mdx-btn-outline edit-record-btn"
                                                        style="border-color:#2c29ca; color:#2c29ca;" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                @endif
                                               @if(PermissionHelper::canFeature('delete_master_codes'))
                                                    <a href="{{ url('delete-code/' . $item->mc_id) }}"
                                                    class="mdx-btn mdx-btn-sm mdx-btn-danger-outline delete-record-btn"
                                                    data-url="{{ url('delete-code/' . $item->mc_id) }}"
                                                    title="Delete">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </a>
                                                @endif
                                            </div>
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
            let dataTable = null;

            /* ── View Toggle ── */
            $('#gridViewBtn').on('click', function () {
                $(this).addClass('active');
                $('#tableViewBtn').removeClass('active');
                $('#gridView').show();
                $('#tableView').removeClass('visible').hide();
                if (dataTable) {
                    dataTable.destroy();
                    dataTable = null;
                }
            });

            $('#tableViewBtn').on('click', function () {
                $(this).addClass('active');
                $('#gridViewBtn').removeClass('active');
                $('#gridView').hide();
                $('#tableView').addClass('visible').show();

                if (!dataTable) {
                    dataTable = $('#table').DataTable({
                        pageLength: 20,
                        lengthMenu: [10, 25, 50, 100],
                        order: [[0, 'asc']],
                        searching: true,
                        ordering: true,
                        dom: 'Bfrtip',
                        buttons: [
                            { extend: 'copy', className: 'mdx-btn mdx-btn-sm mdx-btn-outline' },
                            { extend: 'csv', className: 'mdx-btn mdx-btn-sm mdx-btn-outline' },
                            { extend: 'excel', className: 'mdx-btn mdx-btn-sm mdx-btn-outline' },
                            { extend: 'pdf', className: 'mdx-btn mdx-btn-sm mdx-btn-outline' },
                            { extend: 'print', className: 'mdx-btn mdx-btn-sm mdx-btn-outline' }
                        ],
                        columnDefs: [
                            { targets: [3, 4, 5], className: 'text-center' },
                            { targets: 5, orderable: false, searchable: false }
                        ],
                        language: {
                            emptyTable: "No master code categories found.",
                            zeroRecords: "No matching categories found.",
                            info: "Showing _START_ to _END_ of _TOTAL_ categories",
                            infoEmpty: "Showing 0 to 0 of 0 categories",
                            infoFiltered: "(filtered from _MAX_ total categories)",
                        }
                    });
                }
            });

            /* ── Toggle Form ── */
            var $form = $('#addCodeForm');
            $('#headerAddCodeBtn, #quickAddCodeBtn').on('click', function () {
                $form.toggleClass('open');

                $('#headerAddCodeBtn').html(
                    $form.hasClass('open')
                        ? '<i class="fa fa-minus"></i> Hide Form'
                        : '<i class="fa fa-plus"></i> New Category'
                );

                $('#quickAddCodeBtn').html(
                    $form.hasClass('open')
                        ? '<i class="fa fa-minus-circle"></i> Hide Form'
                        : '<i class="fa fa-plus-circle"></i> Add New Category'
                );
            });

            $('#cancelFormBtn').on('click', function () {
                $form.removeClass('open');
                $('#addCodeBtn').html('<i class="fa fa-plus"></i> New Category');
            });

            /* ── Live Card Search ── */
            $('#codeSearch').on('keyup', function () {
                var term = $(this).val().trim().toLowerCase();
                var visible = 0;

                $('#codeGrid .mdx-card-advanced').each(function () {
                    var match = $(this).attr('data-search').indexOf(term) > -1;
                    $(this).toggle(match);
                    if (match) visible++;
                });

                $('#noResults').toggle(visible === 0 && term.length > 0);
                $('#resultCount').text('Showing ' + visible + ' categor' + (visible == 1 ? 'y' : 'ies'));
            });

            /* ── Refresh Button ── */
            $('#refreshBtn').on('click', function () {
                $(this).html('<i class="fa fa-spinner fa-spin"></i> Refreshing...');
                setTimeout(function () {
                    location.reload();
                }, 500);
            });

            /* ── Export Button ── */
            $('#exportBtn').on('click', function () {
                Swal.fire({
                    title: 'Export Options',
                    text: 'Choose your export format',
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonText: 'CSV',
                    cancelButtonText: 'Excel',
                    showDenyButton: true,
                    denyButtonText: 'PDF',
                }).then((result) => {
                    if (result.isConfirmed) {
                        $('#table').DataTable().button('.buttons-csv').trigger();
                    } else if (result.isDenied) {
                        $('#table').DataTable().button('.buttons-pdf').trigger();
                    } else if (result.dismiss === Swal.DismissReason.cancel) {
                        $('#table').DataTable().button('.buttons-excel').trigger();
                    }
                });
            });

            /* ── Default view: Grid ── */
            $('#gridView').show();
            $('#tableView').hide();

            /* ── Responsive: if screen is small, default to grid ── */
            if ($(window).width() < 768) {
                $('#gridViewBtn').click();
            }
        });


        /* ── Delete Confirmation ── */
$(document).on('click', '.delete-record-btn', function (e) {
    e.preventDefault();

    let deleteUrl = $(this).attr('href');

Swal.fire({
    title: 'Are you sure?',
    html: `
        <div style="text-align:left">
            <p><strong>This action cannot be undone.</strong></p>
            <p>The selected master code and all associated records will be permanently deleted.</p>
        </div>
    `,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#dc3545',
    cancelButtonColor: '#2c29ca',
    confirmButtonText: '<i class="fas fa-trash-alt"></i> Delete',
    cancelButtonText: 'Keep It'
}).then((result) => {
    if (result.isConfirmed) {
        window.location.href = deleteUrl;
    }
});
});
    </script>
@endsection