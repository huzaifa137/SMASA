{{-- resources/views/student/id-cards/index.blade.php --}}
<?php
use App\Http\Controllers\Helper;
use App\Helpers\PermissionHelper;
?>
@extends('layouts-side-bar.master')

@section('css')
    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        :root {
            --b: #2f2ccb;
            --bl: rgba(47, 44, 203, .10);
            --b2: #2420a8;
            --r: #dc2626;
            --rl: rgba(220, 38, 38, .10);
            --g: #059669;
            --gl: rgba(5, 150, 105, .10);
            --a: #d97706;
            --al: rgba(217, 119, 6, .10);
            --surf: #fff;
            --bg: #f0f4f8;
            --brd: #e2e8f0;
            --t1: #0f172a;
            --t2: #475569;
            --t3: #94a3b8;
            --rad: 16px;
            --rads: 10px;
            --sh: 0 1px 3px rgba(0, 0, 0, .06), 0 4px 16px rgba(0, 0, 0, .05);
            --sh-lg: 0 10px 40px rgba(0, 0, 0, .12);
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }

        *:not(i):not([class*="fa"]):not([class*="bi"]) {
            font-family: 'DM Sans', sans-serif;
        }

        body {
            background: var(--bg);
        }

        .fin-hero {
            background: linear-gradient(135deg, #1a1869 0%, #2f2ccb 60%, #0d0c5e 100%);
            border-radius: 24px;
            padding: 2rem 2.5rem;
            margin-bottom: 1.75rem;
            position: relative;
            overflow: hidden;
        }

        .fin-hero::before {
            content: '';
            position: absolute;
            top: -60px;
            right: -60px;
            width: 220px;
            height: 220px;
            background: rgba(255, 255, 255, .06);
            border-radius: 50%;
        }

        .fin-hero::after {
            content: '';
            position: absolute;
            bottom: -40px;
            right: 120px;
            width: 140px;
            height: 140px;
            background: rgba(255, 255, 255, .04);
            border-radius: 50%;
        }

        .hero-title {
            color: #fff;
            font-size: 1.6rem;
            font-weight: 700;
            margin: 0 0 .3rem;
        }

        .hero-sub {
            color: rgba(255, 255, 255, .7);
            font-size: .93rem;
            margin: 0;
        }

        .hero-actions {
            display: flex;
            gap: .75rem;
            margin-top: 1.25rem;
            flex-wrap: wrap;
        }

        /* Stat cards */
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 1rem;
            margin-bottom: 1.75rem;
        }

        .stat-card {
            background: var(--surf);
            border-radius: var(--rad);
            padding: 1.2rem 1.4rem;
            box-shadow: var(--sh);
            border-left: 4px solid transparent;
        }

        .stat-card.blue {
            border-color: var(--b);
        }

        .stat-card.green {
            border-color: var(--g);
        }

        .stat-card.red {
            border-color: var(--r);
        }

        .stat-card.amber {
            border-color: var(--a);
        }

        .stat-label {
            font-size: .73rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: var(--t3);
            margin-bottom: .3rem;
        }

        .stat-val {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--t1);
            line-height: 1;
        }

        .stat-icon {
            float: right;
            font-size: 1.4rem;
            /* opacity: .35; */
            margin-top: -2px;
        }

        /* Filter bar */
        .filter-bar {
            background: var(--surf);
            border-radius: var(--rad);
            padding: 1.1rem 1.4rem;
            box-shadow: var(--sh);
            margin-bottom: 1.5rem;
            display: flex;
            gap: .75rem;
            flex-wrap: wrap;
            align-items: flex-end;
        }

        .filter-bar label {
            font-size: .8rem;
            font-weight: 600;
            color: var(--t2);
            margin-bottom: .3rem;
            display: block;
        }

        .filter-bar select,
        .filter-bar input {
            padding: .45rem .75rem;
            border: 1.5px solid var(--brd);
            border-radius: 8px;
            font-size: .88rem;
            background: var(--bg);
            color: var(--t1);
            min-width: 150px;
        }

        .filter-bar select:focus,
        .filter-bar input:focus {
            outline: none;
            border-color: var(--b);
        }

        /* Table */
        .tbl-wrap {
            background: var(--surf);
            border-radius: var(--rad);
            box-shadow: var(--sh);
            overflow: hidden;
        }

        .tbl-head {
            padding: 1rem 1.4rem;
            border-bottom: 1.5px solid var(--brd);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .5rem;
            flex-wrap: wrap;
        }

        .tbl-head h6 {
            margin: 0;
            font-size: .95rem;
            font-weight: 700;
            color: var(--t1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

th {
    background: #2c29ca;
    color: #ffffff;
    font-size: .73rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .05em;
    padding: .7rem 1rem;
    text-align: left;
}

        td {
            padding: .75rem 1rem;
            border-bottom: 1px solid var(--brd);
            font-size: .87rem;
            color: var(--t2);
            vertical-align: middle;
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:hover td {
            background: #f8fafc;
        }

        /* Badges */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: .25rem;
            padding: .2rem .55rem;
            border-radius: 999px;
            font-size: .72rem;
            font-weight: 600;
        }

        .badge-active {
            background: var(--gl);
            color: var(--g);
        }

        .badge-revoked {
            background: var(--rl);
            color: var(--r);
        }

        .badge-expired {
            background: var(--al);
            color: var(--a);
        }

        .badge-blue {
            background: var(--bl);
            color: var(--b);
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            padding: .48rem 1rem;
            border-radius: 8px;
            font-size: .85rem;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: .15s;
            text-decoration: none;
        }

        .btn-primary {
            background: var(--b);
            color: #fff;
        }

        .btn-primary:hover {
            background: var(--b2);
            color: #fff;
        }

        .btn-outline {
            background: transparent;
            color: var(--b);
            border: 1.5px solid var(--b);
        }

        .btn-outline:hover {
            background: var(--bl);
        }

        .btn-success {
            background: var(--g);
            color: #fff;
        }

        .btn-success:hover {
            background: #047857;
            color: #fff;
        }

        .btn-danger {
            background: var(--r);
            color: #fff;
        }

        .btn-danger:hover {
            background: #b91c1c;
            color: #fff;
        }

        .btn-sm {
            padding: .3rem .7rem;
            font-size: .78rem;
        }

        .btn-icon {
            width: 32px;
            height: 32px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            transition: .15s;
        }

        .btn-view {
            background: var(--bl);
            color: var(--b);
        }

        .btn-view:hover {
            background: var(--b);
            color: #fff;
        }

        .btn-print {
            background: var(--gl);
            color: var(--g);
        }

        .btn-print:hover {
            background: var(--g);
            color: #fff;
        }

        .btn-revoke {
            background: var(--rl);
            color: var(--r);
        }

        .btn-revoke:hover {
            background: var(--r);
            color: #fff;
        }

        /* Modal */
        .modal-backdrop {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, .45);
            z-index: 1050;
            align-items: center;
            justify-content: center;
        }

        .modal-backdrop.open {
            display: flex;
        }

        .modal-box {
            background: #fff;
            border-radius: 20px;
            padding: 2rem;
            max-width: 520px;
            width: 95%;
            box-shadow: var(--sh-lg);
        }

        .modal-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--t1);
            margin: 0 0 1rem;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            font-size: .82rem;
            font-weight: 600;
            color: var(--t2);
            display: block;
            margin-bottom: .35rem;
        }

        .form-group select,
        .form-group input {
            width: 100%;
            padding: .55rem .8rem;
            border: 1.5px solid var(--brd);
            border-radius: 8px;
            font-size: .9rem;
            background: var(--bg);
            color: var(--t1);
        }

        .form-group select:focus,
        .form-group input:focus {
            outline: none;
            border-color: var(--b);
        }

        /* Student avatar */
        .stu-avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            object-fit: cover;
            background: var(--bl);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: .8rem;
            color: var(--b);
            border: 2px solid var(--bl);
        }

        .empty-state {
            padding: 3rem 1rem;
            text-align: center;
            color: var(--t3);
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: .75rem;
            display: block;
        }

        @media(max-width:640px) {
            .fin-hero {
                padding: 1.5rem;
            }

            .hero-title {
                font-size: 1.25rem;
            }

            .stat-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

          /* ── Pagination ── */
        .pagination {
            display: flex;
            gap: .25rem;
            align-items: center;
            flex-wrap: wrap;
        }

        .pagination .page-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 32px;
            height: 32px;
            padding: 0 .5rem;
            border-radius: 8px;
            font-size: .8rem;
            font-weight: 600;
            color: var(--t2);
            background: var(--surf);
            border: 1px solid var(--brd);
            text-decoration: none;
            transition: all .15s;
        }

        .pagination .page-link:hover {
            background: var(--bl);
            color: var(--b);
            border-color: rgba(47, 44, 203, .3);
        }

        .pagination .page-item.active .page-link {
            background: var(--b);
            color: #fff;
            border-color: var(--b);
        }

        .pagination .page-item.disabled .page-link {
            opacity: .4;
            pointer-events: none;
        }

    </style>
@endsection

@section('content')
    <div class="container-fluid mt-5" style="max-width:1200px;">

        {{-- Hero --}}
        <div class="fin-hero">
            <p class="hero-sub mb-1">Academic Year: <strong style="color:#fff;">{{ Helper::active_year() }}</strong></p>
            <h2 class="hero-title"><i class="fas fa-id-card me-2"></i> Student ID Card Management</h2>
            <p class="hero-sub">Generate, print and manage professional student identity cards with QR verification.</p>
            <div class="hero-actions">
                @if(PermissionHelper::canFeature('generate_cards'))
                    <button class="btn btn-outline" style="color:#fff;border-color:rgba(255,255,255,.5);"
                        onclick="openGenerateModal()">
                        <i class="fas fa-magic"></i> Generate Class Cards
                    </button>
                @endif
                @if(PermissionHelper::canFeature('verify_cards'))
                    <a href="{{ route('id-cards.scanner') }}" class="btn btn-outline"
                        style="color:#fff;border-color:rgba(255,255,255,.5);">
                        <i class="fas fa-qrcode"></i> QR Scanner
                    </a>
                @endif
                @if(PermissionHelper::canFeature('print_cards'))
                    <button class="btn btn-outline" style="color:#fff;border-color:rgba(255,255,255,.5);"
                        onclick="openBulkPrintModal()">
                        <i class="fas fa-print"></i> Bulk Print
                    </button>
                @endif
            </div>
        </div>

        {{-- Stats --}}
        <div class="stat-grid" id="statGrid">
            <div class="stat-card blue">
                <span class="stat-icon text-primary"><i class="fas fa-id-badge"></i></span>
                <div class="stat-label">Total Cards</div>
                <div class="stat-val" id="statTotal">—</div>
            </div>
            <div class="stat-card green">
                <span class="stat-icon text-success"><i class="fas fa-check-circle"></i></span>
                <div class="stat-label">Active</div>
                <div class="stat-val" id="statActive">—</div>
            </div>
            <div class="stat-card red">
                <span class="stat-icon text-danger"><i class="fas fa-ban"></i></span>
                <div class="stat-label">Revoked</div>
                <div class="stat-val" id="statRevoked">—</div>
            </div>
            <div class="stat-card amber">
                <span class="stat-icon"><i class="fas fa-users"></i></span>
                <div class="stat-label">Total Students</div>
                <div class="stat-val" id="statStudents">—</div>
            </div>
        </div>

        {{-- Filter Bar --}}
        <div class="filter-bar">
            <div>
                <label>Filter by Status</label>
                <select id="filterStatus" onchange="applyFilter()">
                    <option value="">All Statuses</option>
                    <option value="active">Active</option>
                    <option value="revoked">Revoked</option>
                    <option value="expired">Expired</option>
                </select>
            </div>
            <div>
                <label>Search Student</label>
                <input type="text" id="filterSearch" placeholder="Name or card no." oninput="applyFilter()">
            </div>
            <button class="btn btn-outline btn-sm" onclick="resetFilter()"><i class="fas fa-redo"></i> Reset</button>
        </div>

        {{-- Cards Table --}}
        <div class="tbl-wrap mb-3">
            <div class="tbl-head">
                <h6><i class="fas fa-id-card me-2" style="color:var(--b);"></i> ID Cards</h6>
                <span style="font-size:.8rem;color:#000;">{{ $cards->total() }} record(s)</span>
            </div>

            @if($cards->isEmpty())
                <div class="empty-state">
                    <i class="fas fa-id-card"></i>
                    <p>No ID cards generated yet. Click <strong>Generate Cards</strong> to get started.</p>
                </div>
            @else
                <div style="overflow-x:auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Student</th>
                                <th>Class</th>
                                <th>Stream</th>
                                <th>Card No.</th>
                                <th>Issue Date</th>
                                <th>Expiry</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="cardsTableBody">
                            @foreach($cards as $i => $card)
                            
                                @php
                                    $student = $card->student;
                                    $initials = strtoupper(substr($student->firstname ?? 'S', 0, 1) . substr($student->lastname ?? 'T', 0, 1));
                                    $className = Helper::recordMdname($student->senior);
                                    $streamName = $student->stream;
                                @endphp
                
                                <tr data-status="{{ $card->status }}"
                                    data-name="{{ strtolower($student->firstname . ' ' . $student->lastname) }}"
                                    data-card="{{ strtolower($card->card_number) }}">
                                    <td style="color:var(--t3);font-size:.78rem;">{{ $cards->firstItem() + $i }}</td>
                                    <td>
                                        <div style="display:flex;align-items:center;gap:.6rem;">
                                           @php
                                                $photoUrl = Helper::getStudentPhotoUrl($student->id);
                                                $initials = Helper::getStudentInitials($student->id);
                                            @endphp

                                            @if($photoUrl)
                                                <img src="{{ $photoUrl }}" class="stu-avatar" alt="">
                                            @else
                                                <div class="stu-avatar">{{ $initials }}</div>
                                            @endif
                                            <div>
                                                <div style="font-weight:600;color:var(--t1);">{{ $student->firstname }}
                                                    {{ $student->lastname }}</div>
                                                <div style="font-size:.75rem;color:var(--t3);">Adm:
                                                    {{ $student->admission_number ?? '—' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="badge badge-blue">{{ $className }}</span></td>
                                    <td>{{ $streamName ?? '—' }}</td>
                                    <td style="font-family:'DM Mono',monospace;font-size:.78rem;">{{ $card->card_number }}</td>
                                    <td>{{ $card->issue_date?->format('d M Y') ?? '—' }}</td>
                                    <td>{{ $card->expiry_date?->format('d M Y') ?? '—' }}</td>
                                    <td>
                                        @if($card->status === 'active')
                                            <span class="badge badge-active"><i class="fas fa-circle" style="font-size:.5rem;"></i>
                                                Active</span>
                                        @elseif($card->status === 'revoked')
                                            <span class="badge badge-revoked"><i class="fas fa-ban" style="font-size:.6rem;"></i>
                                                Revoked</span>
                                        @else
                                            <span class="badge badge-expired"><i class="fas fa-clock" style="font-size:.6rem;"></i>
                                                Expired</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div style="display:flex;gap:.35rem;">
                                            @if(PermissionHelper::canFeature('print_cards'))
                                                <button class="btn-icon btn-view" onclick="previewCard({{ $card->id }})"
                                                    title="Preview Card">
                                                    <i class="fas fa-eye"></i>
                                                </button>

                                                <a href="javascript:void();" class="btn-icon btn-print"
                                                    title="Download PDF" target="_blank">
                                                    <i class="fas fa-print"></i>
                                                </a>
                                            @endif

                                            @if($card->status === 'active' && PermissionHelper::canFeature('revoke_cards'))
                                                <button class="btn-icon btn-revoke"
                                                    onclick="revokeCard({{ $card->id }}, '{{ addslashes($student->firstname) }} {{ addslashes($student->lastname) }}')"
                                                    title="Revoke Card">
                                                    <i class="fas fa-ban"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                 @if($cards->total() > 30)
                                <div
                                    style="display:flex;align-items:center;justify-content:space-between;margin-top:.85rem;flex-wrap:wrap;gap:.5rem;">
                                    <span style="font-size:.78rem;color:var(--t3);">
                                        Showing {{ $cards->firstItem() }}–{{ $cards->lastItem() }} of {{ $cards->total() }}
                                    </span>
                                    {{ $cards->onEachSide(1)->links('pagination::bootstrap-5') }}
                                </div>
                            @endif

                {{-- Pagination --}}
                @if($cards->total() > 20)
                    <div
                        style="padding:.85rem 1.4rem;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.5rem;border-top:1px solid var(--brd);">
                        <span style="font-size:.78rem;color:var(--t3);">Showing {{ $cards->firstItem() }}–{{ $cards->lastItem() }}
                            of {{ $cards->total() }}</span>
                        {{ $cards->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>

    {{-- Generate Cards Modal --}}
    <div class="modal-backdrop" id="generateModal">
        <div class="modal-box">
            <h5 class="modal-title"><i class="fas fa-magic me-2" style="color:var(--b);"></i>Generate ID Cards</h5>
            <div class="form-group">
                <label>Class</label>
                <select id="genSenior" onchange="loadStreamsForGen()">
                    <option value="">— Select Class —</option>
                    @foreach($classrooms as $cls)
                        <option value="{{ $cls->class_name }}">{{ Helper::recordMdname($cls->class_name) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Stream <small style="color:var(--t3);">(leave blank for all streams)</small></label>
                <select id="genStream">
                    <option value="">— All Streams —</option>
                </select>
            </div>
            <div
                style="background:var(--bl);border-radius:8px;padding:.75rem 1rem;font-size:.83rem;color:var(--b);margin-bottom:1rem;">
                <i class="fas fa-info-circle me-1"></i> Students who already have an active card for
                <strong>{{ Helper::active_year() }}</strong> will be skipped.
            </div>
            <div style="display:flex;gap:.75rem;justify-content:flex-end;">
                <button class="btn btn-outline btn-sm" onclick="closeGenerateModal()">Cancel</button>
                <button class="btn btn-primary btn-sm" onclick="submitGenerate()" id="btnGenSubmit">
                    <i class="fas fa-magic"></i> Generate
                </button>
            </div>
        </div>
    </div>

    {{-- Bulk Print Modal --}}
    <div class="modal-backdrop" id="bulkPrintModal">
        <div class="modal-box">
            <h5 class="modal-title"><i class="fas fa-print me-2" style="color:var(--b);"></i> Bulk Print Cards</h5>
            <div class="form-group">
                <label>Class</label>
                <select id="printSenior" onchange="loadStreamsForPrint()">
                    <option value="">— Select Class —</option>
                    @foreach($classrooms as $cls)
                        <option value="{{ $cls->class_name }}">{{ Helper::recordMdname($cls->class_name) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Stream</label>
                <select id="printStream">
                    <option value="">— All Streams —</option>
                </select>
            </div>
            <div style="display:flex;gap:.75rem;justify-content:flex-end;">
                <button class="btn btn-outline btn-sm" onclick="closeBulkPrintModal()">Cancel</button>
                <button class="btn btn-success btn-sm" onclick="submitBulkPrint()">
                    <i class="fas fa-download"></i> Download PDF
                </button>
            </div>
        </div>
    </div>

    {{-- Preview Modal (iframe) --}}
    <div class="modal-backdrop" id="previewModal">
        <div class="modal-box" style="max-width:700px;padding:0;overflow:hidden;">
            <div
                style="padding:1rem 1.4rem;border-bottom:1px solid var(--brd);display:flex;align-items:center;justify-content:space-between;">
                <strong style="color:var(--t1);">ID Card Preview</strong>
                <button onclick="closePreviewModal()"
                    style="background:none;border:none;font-size:1.2rem;cursor:pointer;color:var(--t2);"><i
                        class="fas fa-times"></i></button>
            </div>
            <iframe id="previewFrame" src="" style="width:100%;height:500px;border:none;"></iframe>
        </div>
    </div>

    {{-- Toast --}}
    <div id="toast"
        style="display:none;position:fixed;bottom:1.5rem;right:1.5rem;z-index:9999;background:#1e293b;color:#fff;padding:.75rem 1.25rem;border-radius:12px;font-size:.88rem;box-shadow:var(--sh-lg);min-width:260px;">
    </div>

     </div>
        </div>
    </div>
@endsection

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const CSRF = document.querySelector('meta[name="csrf-token"]').content;

        // ── Load stats on page load ──────────────────────────
        fetch('{{ route("id-cards.stats") }}')
            .then(r => r.json())
            .then(d => {
                document.getElementById('statTotal').textContent = d.total ?? 0;
                document.getElementById('statActive').textContent = d.active ?? 0;
                document.getElementById('statRevoked').textContent = d.revoked ?? 0;
                document.getElementById('statStudents').textContent = d.totalStudents ?? 0;
            });

        // ── Toast ──────────────────────────────────────────────
        function showToast(msg, type = 'info') {
            const el = document.getElementById('toast');
            el.textContent = msg;
            el.style.background = type === 'success' ? '#059669' : type === 'error' ? '#dc2626' : '#1e293b';
            el.style.display = 'block';
            setTimeout(() => el.style.display = 'none', 3500);
        }

        // ── Filter ─────────────────────────────────────────────
        function applyFilter() {
            const status = document.getElementById('filterStatus').value.toLowerCase();
            const search = document.getElementById('filterSearch').value.toLowerCase();
            document.querySelectorAll('#cardsTableBody tr').forEach(row => {
                const rowStatus = row.dataset.status ?? '';
                const rowName = row.dataset.name ?? '';
                const rowCard = row.dataset.card ?? '';
                const matchStatus = !status || rowStatus === status;
                const matchSearch = !search || rowName.includes(search) || rowCard.includes(search);
                row.style.display = matchStatus && matchSearch ? '' : 'none';
            });
        }
        function resetFilter() {
            document.getElementById('filterStatus').value = '';
            document.getElementById('filterSearch').value = '';
            applyFilter();
        }

        // ── Generate Modal ─────────────────────────────────────
        function openGenerateModal() { document.getElementById('generateModal').classList.add('open'); }
        function closeGenerateModal() { document.getElementById('generateModal').classList.remove('open'); }

        function loadStreamsForGen() {
            const classId = document.getElementById('genSenior').value;
            loadStreams(classId, 'genStream');
        }
        function loadStreamsForPrint() {
            const classId = document.getElementById('printSenior').value;
            loadStreams(classId, 'printStream');
        }
        function loadStreams(classId, targetId) {
            const sel = document.getElementById(targetId);
            sel.innerHTML = '<option value="">— All Streams —</option>';
            if (!classId) return;
            fetch(`{{ route('id-cards.streams.by.senior') }}?class_id=${classId}`)
                .then(r => r.json())
                .then(streams => {
                    streams.forEach(s => {
                        const opt = document.createElement('option');
                        opt.value = s.stream_id ?? s.id;
                        opt.textContent = s.display_name ?? s.stream_id;
                        sel.appendChild(opt);
                    });
                });
        }

        function submitGenerate() {
            const senior = document.getElementById('genSenior').value;
            const stream = document.getElementById('genStream').value;
            if (!senior) { showToast('Please select a class.', 'error'); return; }
            const btn = document.getElementById('btnGenSubmit');
            btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating…';
            fetch('{{ route("id-cards.generate") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: JSON.stringify({ senior, stream })
            })
                .then(r => r.json())
                .then(d => {
                    closeGenerateModal();
                    showToast(d.message, d.status === 'success' ? 'success' : 'error');
                    if (d.status === 'success') setTimeout(() => location.reload(), 1500);
                })
                .catch(() => showToast('Server error. Try again.', 'error'))
                .finally(() => { btn.disabled = false; btn.innerHTML = '<i class="fas fa-magic"></i> Generate'; });
        }

        // ── Bulk Print ─────────────────────────────────────────
        function openBulkPrintModal() { document.getElementById('bulkPrintModal').classList.add('open'); }
        function closeBulkPrintModal() { document.getElementById('bulkPrintModal').classList.remove('open'); }

        function submitBulkPrint() {
            const senior = document.getElementById('printSenior').value;
            const stream = document.getElementById('printStream').value;
            if (!senior) { showToast('Please select a class.', 'error'); return; }
            let url = `{{ route('id-cards.print.bulk') }}?senior=${encodeURIComponent(senior)}`;
            if (stream) url += `&stream=${encodeURIComponent(stream)}`;
            window.open(url, '_blank');
            closeBulkPrintModal();
        }

        // ── Preview ───────────────────────────────────────────
        function previewCard(cardId) {
            document.getElementById('previewFrame').src = `/student-id-cards/preview/${cardId}`;
            document.getElementById('previewModal').classList.add('open');
        }
        function closePreviewModal() {
            document.getElementById('previewModal').classList.remove('open');
            document.getElementById('previewFrame').src = '';
        }

        // ── Revoke ────────────────────────────────────────────
function revokeCard(cardId, name) {
    Swal.fire({
        title: 'Are you sure?',
        text: `Revoke ID card for ${name}? This action cannot be undone.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, revoke it',
        cancelButtonText: 'Cancel'
    }).then((result) => {

        if (!result.isConfirmed) return;

        fetch(`/student-id-cards/revoke/${cardId}`, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': CSRF,
                'Content-Type': 'application/json'
            }
        })
        .then(r => r.json())
        .then(d => {
            Swal.fire({
                title: 'Revoked!',
                text: d.message,
                icon: 'success',
                timer: 2000,
                showConfirmButton: false
            });

            setTimeout(() => location.reload(), 2000);
        })
        .catch(() => {
            Swal.fire({
                title: 'Error!',
                text: 'Failed to revoke the ID card.',
                icon: 'error'
            });
        });

    });
}

        // Close modals on backdrop click
        document.querySelectorAll('.modal-backdrop').forEach(el => {
            el.addEventListener('click', function (e) {
                if (e.target === this) {
                    this.classList.remove('open');
                    const frame = document.getElementById('previewFrame');
                    if (frame) frame.src = '';
                }
            });
        });
    </script>
@endsection