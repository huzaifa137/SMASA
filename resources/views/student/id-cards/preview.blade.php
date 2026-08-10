{{-- resources/views/student/id-cards/preview.blade.php --}}
<?php use App\Helpers\PermissionHelper; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ID Card – {{ $student->firstname }} {{ $student->lastname }}</title>
    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700;800&family=DM+Mono:wght@400;500&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background: #f0f4f8;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
            gap: 1.5rem;
        }

        .card-container {
            perspective: 1000px;
        }

        /* ── FRONT FACE ── */
        .id-card {
            width: 340px;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, .22), 0 4px 16px rgba(0, 0, 0, .12);
            position: relative;
            font-family: 'DM Sans', sans-serif;
        }

        .card-header {
            background: linear-gradient(135deg, #1a1869 0%, #2f2ccb 55%, #0d0b5e 100%);
            padding: 1rem 1.2rem .8rem;
            position: relative;
            overflow: hidden;
        }

        .card-header::before {
            content: '';
            position: absolute;
            top: -30px;
            right: -30px;
            width: 110px;
            height: 110px;
            background: rgba(255, 255, 255, .08);
            border-radius: 50%;
        }

        .card-header::after {
            content: '';
            position: absolute;
            bottom: -50px;
            left: 30%;
            width: 140px;
            height: 140px;
            background: rgba(255, 255, 255, .05);
            border-radius: 50%;
        }

        .school-row {
            display: flex;
            align-items: center;
            gap: .65rem;
            position: relative;
            z-index: 2;
        }

        .school-logo {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            border: 2px solid rgba(255, 255, 255, .4);
            object-fit: cover;
            background: rgba(255, 255, 255, .15);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            overflow: hidden;
        }

        .school-logo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .school-logo i {
            color: rgba(255, 255, 255, .7);
            font-size: 1.3rem;
        }

        .school-name {
            color: #fff;
            font-size: .82rem;
            font-weight: 700;
            line-height: 1.2;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .card-type-tag {
            display: inline-block;
            background: rgba(255, 255, 255, .18);
            color: rgba(255, 255, 255, .9);
            font-size: .6rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .08em;
            padding: .15rem .5rem;
            border-radius: 999px;
            margin-top: .25rem;
        }

        .card-body {
            background: #fff;
            padding: 1rem 1.2rem .9rem;
            display: flex;
            gap: 1rem;
            align-items: flex-start;
        }

        .photo-wrap {
            flex-shrink: 0;
        }

        .photo-circle {
            width: 72px;
            height: 72px;
            border-radius: 14px;
            overflow: hidden;
            border: 3px solid #2f2ccb;
            background: #e0e7ff;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .photo-circle img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .photo-initials {
            font-size: 1.5rem;
            font-weight: 800;
            color: #2f2ccb;
        }

        .info-block {
            flex: 1;
            min-width: 0;
        }

        .student-name {
            font-size: 1rem;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.2;
            margin-bottom: .35rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .info-row {
            display: flex;
            align-items: center;
            gap: .4rem;
            margin-bottom: .25rem;
        }

        .info-label {
            font-size: .65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: #94a3b8;
            min-width: 52px;
        }

        .info-val {
            font-size: .78rem;
            font-weight: 600;
            color: #334155;
        }

        .gender-badge {
            display: inline-flex;
            align-items: center;
            gap: .2rem;
            padding: .15rem .5rem;
            border-radius: 999px;
            font-size: .65rem;
            font-weight: 700;
        }

        .badge-male {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .badge-female {
            background: #fce7f3;
            color: #be185d;
        }

        .card-mid {
            background: #f8fafc;
            padding: .6rem 1.2rem;
            border-top: 1px solid #e2e8f0;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            gap: 1rem;
        }

        .mid-item {
            flex: 1;
        }

        .mid-label {
            font-size: .6rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: #94a3b8;
        }

        .mid-val {
            font-size: .82rem;
            font-weight: 700;
            color: #0f172a;
        }

        .card-footer {
            background: #fff;
            padding: .8rem 1.2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .75rem;
        }

        .qr-block {
            background: #fff;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            padding: .35rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .qr-block svg {
            width: 70px !important;
            height: 70px !important;
        }

        .card-no-block {
            flex: 1;
        }

        .card-no-label {
            font-size: .6rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: #94a3b8;
            margin-bottom: .2rem;
        }

        .card-no-val {
            font-family: 'DM Mono', monospace;
            font-size: .68rem;
            color: #2f2ccb;
            font-weight: 600;
            word-break: break-all;
        }

        .validity-block {
            text-align: right;
        }

        .validity-label {
            font-size: .6rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: #94a3b8;
            margin-bottom: .2rem;
        }

        .validity-val {
            font-size: .78rem;
            font-weight: 700;
            color: #059669;
        }

        .card-strip {
            background: linear-gradient(90deg, #1a1869, #2f2ccb, #1a1869);
            height: 6px;
        }

        /* ── BACK FACE ── */
        .id-card-back {
            width: 340px;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, .22), 0 4px 16px rgba(0, 0, 0, .12);
        }

        .back-stripe {
            background: #1a1869;
            height: 44px;
        }

        .back-body {
            background: #fff;
            padding: .9rem 1.2rem;
        }

        .back-info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: .5rem .8rem;
            margin-bottom: .8rem;
        }

        .back-field-label {
            font-size: .62rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: #94a3b8;
        }

        .back-field-val {
            font-size: .8rem;
            font-weight: 600;
            color: #0f172a;
            margin-top: .1rem;
        }

        .back-divider {
            border: none;
            border-top: 1px solid #e2e8f0;
            margin: .6rem 0;
        }

        .back-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .lost-msg {
            font-size: .65rem;
            color: #94a3b8;
            max-width: 180px;
            line-height: 1.4;
        }

        .status-chip {
            display: inline-flex;
            align-items: center;
            gap: .3rem;
            padding: .3rem .75rem;
            border-radius: 999px;
            font-size: .72rem;
            font-weight: 700;
        }

        .chip-active {
            background: #dcfce7;
            color: #166534;
        }

        .chip-revoked {
            background: #fee2e2;
            color: #991b1b;
        }

        /* Buttons */
        .action-row {
            display: flex;
            gap: .75rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            padding: .55rem 1.2rem;
            border-radius: 10px;
            font-size: .88rem;
            font-weight: 600;
            cursor: pointer;
            border: none;
            text-decoration: none;
            transition: .15s;
        }

        .btn-primary {
            background: #2f2ccb;
            color: #fff;
        }

        .btn-primary:hover {
            background: #2420a8;
        }

        .btn-outline {
            background: transparent;
            color: #2f2ccb;
            border: 1.5px solid #2f2ccb;
        }

        .btn-outline:hover {
            background: rgba(47, 44, 203, .08);
        }

        .btn-danger {
            background: #dc2626;
            color: #fff;
        }

        .btn-danger:hover {
            background: #b91c1c;
        }
    </style>
</head>

<body>

    {{-- ── FRONT ──────────────────────────────────── --}}
    <div class="card-container">
        <div class="id-card">
            <div class="card-strip"></div>
            <div class="card-header">
                <div class="school-row">
                    <div class="school-logo">
                        @if(isset($logoUrl) && $logoUrl)
                            <img src="{{ $logoUrl }}" alt="Logo">
                        @else
                            <i class="fas fa-school"></i>
                        @endif
                    </div>
                    <div>
                        <div class="school-name">{{ $school->name ?? 'School Name' }}</div>
                        <span class="card-type-tag">Student Identity Card</span>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <div class="photo-wrap">
                    <div class="photo-circle">
                        @if($photoUrl)
                            <img src="{{ $photoUrl }}" alt="">
                        @else
                            <span
                                class="photo-initials">{{ strtoupper(substr($student->firstname, 0, 1) . substr($student->lastname, 0, 1)) }}</span>
                        @endif
                    </div>
                </div>
  
                <div class="info-block">
                    <div class="student-name">{{ $student->firstname }} {{ $student->lastname }}</div>
                    <div class="info-row">
                        <span class="info-label">Adm No.</span>
                        <span
                            class="info-val">{{ $student->admission_number ?? $student->registration_number ?? '—' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Class</span>
                        <span class="info-val">{{ $className }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Stream</span>
                        <span class="info-val">{{ $student->stream ?? '—' }}</span>
                    </div>
                    <div class="info-row" style="margin-top:.3rem;">
                        @if($student->gender === 'Male')
                            <span class="gender-badge badge-male"><i class="fas fa-mars" style="font-size:.65rem;"></i>
                                Male</span>
                        @elseif($student->gender === 'Female')
                            <span class="gender-badge badge-female"><i class="fas fa-venus" style="font-size:.65rem;"></i>
                                Female</span>
                        @else
                            <span class="gender-badge"
                                style="background:#f1f5f9;color:#64748b;">{{ $student->gender }}</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card-mid">
                <div class="mid-item">
                    <div class="mid-label">Year</div>
                    <div class="mid-val">{{ $card->academic_year }}</div>
                </div>
                <div class="mid-item">
                    <div class="mid-label">Issued</div>
                    <div class="mid-val">{{ $card->issue_date?->format('d M Y') }}</div>
                </div>
                <div class="mid-item">
                    <div class="mid-label">Expires</div>
                    <div class="mid-val" style="color:#dc2626;">{{ $card->expiry_date?->format('d M Y') }}</div>
                </div>
            </div>

            <div class="card-footer">
                <div class="qr-block">{!! $qrSvg !!}</div>
                <div class="card-no-block">
                    <div class="card-no-label">Card Number</div>
                    <div class="card-no-val">{{ $card->card_number }}</div>
                </div>
            </div>
            <div class="card-strip"></div>
        </div>
    </div>

    {{-- ── BACK ──────────────────────────────────── --}}
    <div class="card-container">
        <div class="id-card-back">
            <div class="back-stripe"></div>
            <div class="back-body">
                <div class="back-info-grid">
                    <div>
                        <div class="back-field-label">Full Name</div>
                        <div class="back-field-val">{{ $student->firstname }} {{ $student->lastname }}</div>
                    </div>
                    <div>
                        <div class="back-field-label">Nationality</div>
                        <div class="back-field-val">{{ $student->nationality ?? '—' }}</div>
                    </div>
                    <div>
                        <div class="back-field-label">Date of Birth</div>
                        <div class="back-field-val">
                            {{ $student->date_of_birth ? \Carbon\Carbon::parse($student->date_of_birth)->format('d M Y') : '—' }}
                        </div>
                    </div>
                    <div>
                        <div class="back-field-label">Place of Birth</div>
                        <div class="back-field-val">{{ $student->place_of_birth ?? '—' }}</div>
                    </div>
                    <div>
                        <div class="back-field-label">Guardian</div>
                        <div class="back-field-val">{{ $student->guardian_names ?? '—' }}</div>
                    </div>
                    <div>
                        <div class="back-field-label">Contact</div>
                        <div class="back-field-val">{{ $student->primary_contact ?? '—' }}</div>
                    </div>
                </div>
                <hr class="back-divider">
                <div class="back-footer">
                    <div class="lost-msg">
                        If found, please return to: <strong>{{ $school->name ?? 'the school' }}</strong>.<br>
                        {{ $school->phone ?? '' }}
                    </div>
                    <div>
                        @if($card->status === 'active')
                            <span class="status-chip chip-active"><i class="fas fa-check-circle"></i> VALID</span>
                        @else
                            <span class="status-chip chip-revoked"><i class="fas fa-ban"></i> INVALID</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="back-stripe"></div>
        </div>
    </div>

    {{-- Action Buttons --}}
    <div class="action-row">
        <a href="{{ route('id-cards.print', $card->id) }}" class="btn btn-primary" target="_blank">
            <i class="fas fa-print"></i> Print / Download PDF
        </a>
        @if($card->status === 'active' && PermissionHelper::canFeature('revoke_cards'))
            <button class="btn btn-danger" onclick="revokeCard({{ $card->id }})">
                <i class="fas fa-ban"></i> Revoke Card
            </button>
        @endif
        <button type="button" class="btn btn-outline" onclick="closeParentModal()">
            <i class="fas fa-times"></i> Close
        </button>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>

        function closeParentModal() {
            if (window.parent && typeof window.parent.closePreviewModal === 'function') {
                window.parent.closePreviewModal();
            }
        }

        function revokeCard(cardId) {
            Swal.fire({
                title: 'Are you sure?',
                text: 'This action will permanently revoke the ID card and cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, revoke it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {

                    fetch(`/student-id-cards/revoke/${cardId}`, {
                        method: 'PATCH',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json'
                        }
                    })
                        .then(response => response.json())
                        .then(data => {
                            Swal.fire({
                                title: 'Revoked!',
                                text: data.message,
                                icon: 'success'
                            }).then(() => {
                                location.reload();
                            });
                        })
                        .catch(error => {
                            Swal.fire({
                                title: 'Error!',
                                text: 'Something went wrong while revoking the card.',
                                icon: 'error'
                            });
                        });

                }
            });
        }
    </script>
</body>

</html>