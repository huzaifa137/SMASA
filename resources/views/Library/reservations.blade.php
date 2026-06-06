@extends('layouts-side-bar.master')

@section('css')
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        /* Your existing styles here (same as provided) */
        :root {
            --lib-blue: #2c29ca;
            --lib-blue-l: rgba(44, 41, 202, .12);
            --lib-blue-d: #2420a8;
            --lib-rose: #f43f5e;
            --lib-rose-l: rgba(244, 63, 94, .12);
            --lib-green: #10b981;
            --lib-green-l: rgba(16, 185, 129, .12);
            --lib-amber: #f59e0b;
            --lib-amber-l: rgba(245, 158, 11, .12);
            --lib-violet: #7c3aed;
            --lib-violet-l: rgba(124, 58, 237, .12);
            --surface: #fff;
            --bg: #f1f5f9;
            --border: #e2e8f0;
            --text-1: #0f172a;
            --text-2: #475569;
            --text-3: #94a3b8;
            --radius: 16px;
            --shadow: 0 1px 4px rgba(0, 0, 0, .06), 0 4px 20px rgba(0, 0, 0, .05);
        }

        * {
            box-sizing: border-box;
        }

        body {
            background: var(--bg);
        }

        .lib-hero {
            background: linear-gradient(135deg, #1a1869 0%, #2c29ca 60%, #0d0c5e 100%);
            border-radius: 24px;
            padding: 2rem 2.5rem;
            margin-bottom: 1.75rem;
            position: relative;
            overflow: hidden;
            color: #fff;
        }

        .lib-hero::before {
            content: '';
            position: absolute;
            top: -80px;
            right: -80px;
            width: 280px;
            height: 280px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(255, 255, 255, .08) 0%, transparent 70%);
        }

        .lib-hero::after {
            content: '';
            position: absolute;
            bottom: -50px;
            left: 30%;
            width: 250px;
            height: 250px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(255, 255, 255, .05) 0%, transparent 70%);
        }

        .lib-card {
            background: var(--surface);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
        }

        .lib-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--border);
        }

        .lib-card-header h3 {
            font-size: 1rem;
            font-weight: 700;
            color: var(--text-1);
            margin: 0;
            display: flex;
            align-items: center;
            gap: .5rem;
        }

        .lib-card-header h3 i {
            color: var(--lib-blue);
        }

        .lib-card-body {
            padding: 1.5rem;
        }

        .btn-lib {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            padding: .5rem 1rem;
            border-radius: 10px;
            font-size: .8rem;
            font-weight: 600;
            cursor: pointer;
            border: none;
            text-decoration: none;
            transition: all .2s;
        }

        .btn-primary-lib {
            background: var(--lib-blue);
            color: #fff;
        }

        .btn-primary-lib:hover {
            background: var(--lib-blue-d);
            color: #fff;
        }

        .btn-danger-lib {
            background: var(--lib-rose-l);
            color: var(--lib-rose);
        }

        .btn-danger-lib:hover {
            background: var(--lib-rose);
            color: #fff;
        }

        .btn-success-lib {
            background: var(--lib-green-l);
            color: var(--lib-green);
        }

        .btn-success-lib:hover {
            background: var(--lib-green);
            color: #fff;
        }

        .btn-outline-lib {
            background: transparent;
            color: var(--text-2);
            border: 1px solid var(--border);
        }

        .btn-outline-lib:hover {
            background: var(--bg);
            border-color: var(--lib-blue);
            color: var(--lib-blue);
        }

        .lib-table {
            width: 100%;
            border-collapse: collapse;
        }

        .lib-table th {
            padding: .75rem 1rem;
            text-align: left;
            font-size: .75rem;
            font-weight: 700;
            color: #fff;
            background: #2c29ca;
            text-transform: uppercase;
            letter-spacing: .05em;
            border-bottom: none;
        }

        .lib-table td {
            padding: .85rem 1rem;
            border-bottom: 1px solid var(--border);
            font-size: .875rem;
            color: var(--text-1);
            vertical-align: middle;
        }

        .lib-table tr:last-child td {
            border-bottom: none;
        }

        .lib-table tr:hover td {
            background: #f8fafc;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            padding: .25rem .65rem;
            border-radius: 999px;
            font-size: .7rem;
            font-weight: 700;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-label {
            display: block;
            font-size: .8rem;
            font-weight: 600;
            color: var(--text-2);
            margin-bottom: .4rem;
        }

        .form-control {
            width: 100%;
            padding: .6rem .85rem;
            border: 1.5px solid var(--border);
            border-radius: 10px;
            font-size: .875rem;
            font-family: inherit;
            transition: border-color .2s;
            outline: none;
        }

        .form-control:focus {
            border-color: var(--lib-blue);
        }

        .alert {
            padding: .85rem 1rem;
            border-radius: 10px;
            font-size: .85rem;
            font-weight: 500;
            margin-bottom: 1rem;
        }

        .alert-success {
            background: var(--lib-green-l);
            color: var(--lib-green);
            border-left: 4px solid var(--lib-green);
        }

        .alert-error {
            background: var(--lib-rose-l);
            color: var(--lib-rose);
            border-left: 4px solid var(--lib-rose);
        }

        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: var(--text-3);
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            display: block;
        }

        .lib-back-link {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            color: var(--text-2);
            text-decoration: none;
            font-size: .85rem;
            font-weight: 500;
            margin-bottom: 1.25rem;
        }

        .lib-back-link:hover {
            color: var(--lib-blue);
        }

        .filter-bar {
            display: flex;
            flex-wrap: wrap;
            gap: .75rem;
            align-items: center;
        }

        .filter-bar .form-control {
            width: auto;
            min-width: 140px;
        }
    </style>
@endsection

@section('content')
    <div style="padding:1.5rem;">

        <div class="lib-hero mb-4">
            <div style="font-size:1.6rem;font-weight:800;margin:0 0 .25rem;">
                <i class="fas fa-calendar-check" style="color:#a5b4fc;margin-right:.5rem;"></i>Reservations
            </div>
            <div style="font-size:.875rem;opacity:.7;">Manage book reservations and holds</div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 320px;gap:1.5rem;align-items:start;">

            <div>
                {{-- Filters --}}
                <div class="lib-card" style="margin-bottom:1.25rem;">
                    <div class="lib-card-body" style="padding:1rem 1.5rem;">
                        <form method="GET" class="filter-bar" id="filterForm">
                            <select name="status" class="form-control" id="statusFilter">
                                <option value="">All Statuses</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="ready" {{ request('status') == 'ready' ? 'selected' : '' }}>Ready</option>
                                <option value="fulfilled" {{ request('status') == 'fulfilled' ? 'selected' : '' }}>Fulfilled</option>
                                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expired</option>
                            </select>
                            <button type="submit" class="btn-lib btn-primary-lib"><i class="fas fa-filter"></i> Filter</button>
                            <a href="{{ route('library.reservations') }}" class="btn-lib btn-outline-lib">Clear</a>
                        </form>
                    </div>
                </div>

                <div class="lib-card">
                    <div class="lib-card-header">
                        <h3><i class="fas fa-list" style="color:var(--lib-blue);"></i> All Reservations</h3>
                        <span style="font-size:.8rem;color:var(--text-3);">{{ $reservations->total() }} total</span>
                    </div>
                    <div style="overflow-x:auto;">
                        @if($reservations->count())
                            <table class="lib-table">
                                <thead>
                                    <tr>
                                        <th>Ref</th>
                                        <th>Member</th>
                                        <th>Book</th>
                                        <th>Reserved</th>
                                        <th>Expires</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($reservations as $r)
                                        <tr>
                                            <td><code
                                                    style="font-size:.75rem;background:var(--bg);padding:.2rem .4rem;border-radius:6px;">{{ $r->reservation_number }}</code>
                                            </td>
                                            <td>
                                                <div style="font-weight:600;">{{ $r->member->name ?? '—' }}</div>
                                                <div style="font-size:.75rem;color:var(--text-3);">
                                                    {{ ucfirst($r->member->member_type ?? '') }}</div>
                                            </td>
                                            <td
                                                style="max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-weight:500;">
                                                {{ $r->book->title ?? '—' }}</td>
                                            <td style="color:var(--text-2);font-size:.8rem;">
                                                {{ \Carbon\Carbon::parse($r->reservation_date)->format('d M Y') }}</td>
                                            <td style="color:var(--text-2);font-size:.8rem;">
                                                {{ \Carbon\Carbon::parse($r->expiry_date)->format('d M Y') }}</td>
                                            <td>
                                                @php
                                                    $statusMap = [
                                                        'pending' => ['bg' => 'var(--lib-amber-l)', 'c' => 'var(--lib-amber)'],
                                                        'ready' => ['bg' => 'var(--lib-blue-l)', 'c' => 'var(--lib-blue)'],
                                                        'fulfilled' => ['bg' => 'var(--lib-green-l)', 'c' => 'var(--lib-green)'],
                                                        'cancelled' => ['bg' => '#f1f5f9', 'c' => 'var(--text-3)'],
                                                        'expired' => ['bg' => 'var(--lib-rose-l)', 'c' => 'var(--lib-rose)'],
                                                    ];
                                                    $s = $statusMap[$r->status] ?? $statusMap['cancelled'];
                                                @endphp
                                                <span class="badge"
                                                    style="background:{{ $s['bg'] }};color:{{ $s['c'] }};">{{ ucfirst($r->status) }}</span>
                                            </td>
                                            <td>
                                                @if(in_array($r->status, ['pending', 'ready']))
                                                    <div style="display:flex;gap:.4rem;">
                                                        @if($r->status === 'pending')
                                                            <button onclick="updateStatus({{ $r->id }}, 'ready')" 
                                                                    class="btn-lib btn-success-lib"
                                                                    style="padding:.3rem .65rem;" title="Mark Ready">
                                                                <i class="fas fa-check"></i>
                                                            </button>
                                                        @endif
                                                        <button onclick="updateStatus({{ $r->id }}, 'fulfilled')" 
                                                                class="btn-lib btn-primary-lib"
                                                                style="padding:.3rem .65rem;" title="Fulfil">
                                                            <i class="fas fa-book"></i>
                                                        </button>
                                                        <button onclick="updateStatus({{ $r->id }}, 'cancelled')" 
                                                                class="btn-lib btn-danger-lib"
                                                                style="padding:.3rem .65rem;" title="Cancel">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </div>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <div style="padding:1rem 1.5rem;">{{ $reservations->appends(request()->all())->links() }}</div>
                        @else
                            <div class="empty-state"><i class="fas fa-calendar-check"></i>No reservations found.</div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- New Reservation --}}
            <div class="lib-card" style="position:sticky;top:1.5rem;">
                <div class="lib-card-header">
                    <h3><i class="fas fa-plus-circle" style="color:var(--lib-blue);"></i> New Reservation</h3>
                </div>
                <div class="lib-card-body">
                    <form id="reservationForm">
                        @csrf
                        <div class="form-group">
                            <label class="form-label">Member *</label>
                            <select name="member_id" class="form-control" id="member_id" required>
                                <option value="">— Select Member —</option>
                                @foreach($members as $m)
                                    <option value="{{ $m->id }}">{{ $m->name }} ({{ ucfirst($m->member_type) }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Book *</label>
                            <select name="book_id" class="form-control" id="book_id" required>
                                <option value="">— Select Book —</option>
                                @foreach($books as $book)
                                    <option value="{{ $book->id }}">{{ $book->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Optional notes…" id="notes"></textarea>
                        </div>
                        <button type="submit" class="btn-lib btn-primary-lib" style="width:100%;justify-content:center;" id="submitBtn">
                            <i class="fas fa-calendar-plus"></i> Create Reservation
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    </div>
    </div>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // SweetAlert Toast configuration
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
    });

    // Create Reservation
    document.getElementById('reservationForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const memberId = document.getElementById('member_id').value;
        const bookId = document.getElementById('book_id').value;
        
        if (!memberId || !bookId) {
            Toast.fire({
                icon: 'error',
                title: 'Please select both member and book'
            });
            return;
        }
        
        const memberName = document.getElementById('member_id').options[document.getElementById('member_id').selectedIndex]?.text;
        const bookTitle = document.getElementById('book_id').options[document.getElementById('book_id').selectedIndex]?.text;
        
        Swal.fire({
            title: 'Create Reservation?',
            html: `Are you sure you want to reserve <strong>"${bookTitle}"</strong> for <strong>${memberName}</strong>?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#2c29ca',
            cancelButtonColor: '#f43f5e',
            confirmButtonText: 'Yes, create it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Creating Reservation...',
                    text: 'Please wait while we create the reservation',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    allowEnterKey: false,
                    showConfirmButton: false,
                    didOpen: () => { Swal.showLoading(); }
                });
                
                const submitBtn = document.getElementById('submitBtn');
                const originalHtml = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating...';
                
                fetch('{{ route("library.reservations.store") }}', {
                    method: 'POST',
                    body: new FormData(this),
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Created!',
                            text: data.message,
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => { location.reload(); });
                    } else {
                        throw new Error(data.message || 'Failed to create reservation');
                    }
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: error.message
                    });
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalHtml;
                });
            }
        });
    });

    // Update Reservation Status
    function updateStatus(reservationId, newStatus) {
        let title = '';
        let text = '';
        let confirmText = '';
        let confirmColor = '';
        
        switch(newStatus) {
            case 'ready':
                title = 'Mark as Ready?';
                text = 'This reservation will be marked as ready for pickup.';
                confirmText = 'Yes, mark as ready!';
                confirmColor = '#10b981';
                break;
            case 'fulfilled':
                title = 'Mark as Fulfilled?';
                text = 'This will mark the reservation as fulfilled (book borrowed).';
                confirmText = 'Yes, mark as fulfilled!';
                confirmColor = '#2c29ca';
                break;
            case 'cancelled':
                title = 'Cancel Reservation?';
                text = 'Are you sure you want to cancel this reservation? This action cannot be undone.';
                confirmText = 'Yes, cancel it!';
                confirmColor = '#f43f5e';
                break;
        }
        
        Swal.fire({
            title: title,
            text: text,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: confirmColor,
            cancelButtonColor: '#94a3b8',
            confirmButtonText: confirmText,
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Processing...',
                    text: 'Please wait while we update the status',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    allowEnterKey: false,
                    showConfirmButton: false,
                    didOpen: () => { Swal.showLoading(); }
                });
                
                fetch(`/library/reservations/${reservationId}/status`, {
                    method: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ status: newStatus })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Updated!',
                            text: data.message,
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => { location.reload(); });
                    } else {
                        throw new Error(data.message || 'Failed to update status');
                    }
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: error.message
                    });
                });
            }
        });
    }
</script>
@endsection