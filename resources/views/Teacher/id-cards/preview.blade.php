{{-- resources/views/teacher/id-cards/preview.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher ID – {{ $teacher->firstname }} {{ $teacher->surname }}</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700;800&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'DM Sans', sans-serif;
            background: #ecfdf5;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
            gap: 1.5rem;
        }

        .card-container { perspective: 1000px; }

        /* ── FRONT ── */
        .id-card {
            width: 340px;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,.22), 0 4px 16px rgba(0,0,0,.12);
            position: relative;
        }

        .card-strip-top {
            height: 7px;
            background: linear-gradient(90deg, #0f766e, #14b8a6, #f59e0b, #0f766e);
        }

        .card-header {
            background: linear-gradient(135deg, #064e3b 0%, #0f766e 55%, #065f46 100%);
            padding: 1rem 1.2rem .9rem;
            position: relative;
            overflow: hidden;
        }
        .card-header::before {
            content: '';
            position: absolute; top: -30px; right: -30px;
            width: 110px; height: 110px;
            background: rgba(255,255,255,.08); border-radius: 50%;
        }
        .card-header::after {
            content: '';
            position: absolute; bottom: -50px; left: 30%;
            width: 140px; height: 140px;
            background: rgba(255,255,255,.05); border-radius: 50%;
        }

        /* Gold diamond accent */
        .diamond-accent {
            position: absolute;
            top: 8px; right: 10px;
            display: flex; gap: 4px; z-index: 2;
        }
        .diamond {
            width: 8px; height: 8px;
            background: #f59e0b;
            transform: rotate(45deg);
            border-radius: 2px;
        }
        .diamond:nth-child(2) { background: rgba(245,158,11,.5); }
        .diamond:nth-child(3) { background: rgba(245,158,11,.25); }

        .school-row {
            display: flex; align-items: center; gap: .65rem;
            position: relative; z-index: 2;
        }
        .school-logo {
            width: 46px; height: 46px;
            border-radius: 50%;
            border: 2px solid rgba(245,158,11,.6);
            object-fit: cover;
            background: rgba(255,255,255,.12);
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0; overflow: hidden;
        }
        .school-logo img { width: 100%; height: 100%; object-fit: cover; }
        .school-logo i { color: rgba(255,255,255,.7); font-size: 1.3rem; }
        .school-name { color: #fff; font-size: .82rem; font-weight: 700; line-height: 1.2; text-transform: uppercase; letter-spacing: .04em; }
        .card-type-tag {
            display: inline-block;
            background: linear-gradient(90deg, rgba(245,158,11,.8), rgba(245,158,11,.4));
            color: #fff;
            font-size: .6rem; font-weight: 700; text-transform: uppercase;
            letter-spacing: .1em; padding: .2rem .6rem;
            border-radius: 999px; margin-top: .3rem;
        }

        .card-body {
            background: #fff; padding: 1rem 1.2rem .85rem;
            display: flex; gap: 1rem; align-items: flex-start;
        }
        .photo-wrap { flex-shrink: 0; }
        .photo-circle {
            width: 76px; height: 76px;
            border-radius: 14px; overflow: hidden;
            border: 3px solid #0f766e;
            background: #ccfbf1;
            display: flex; align-items: center; justify-content: center;
        }
        .photo-circle img { width: 100%; height: 100%; object-fit: cover; }
        .photo-initials { font-size: 1.6rem; font-weight: 800; color: #0f766e; }

        /* Gold trim under photo */
        .photo-gold-bar {
            height: 3px;
            background: linear-gradient(90deg, #f59e0b, #fbbf24);
            border-radius: 0 0 6px 6px;
            margin-top: -3px;
        }

        .info-block { flex: 1; min-width: 0; }
        .teacher-name {
            font-size: 1rem; font-weight: 800; color: #0f172a;
            line-height: 1.2; margin-bottom: .35rem;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .info-row { display: flex; align-items: center; gap: .4rem; margin-bottom: .22rem; }
        .info-label { font-size: .65rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: #94a3b8; min-width: 52px; }
        .info-val { font-size: .78rem; font-weight: 600; color: #334155; }

        .gender-badge { display: inline-flex; align-items: center; gap: .2rem; padding: .15rem .5rem; border-radius: 999px; font-size: .65rem; font-weight: 700; }
        .badge-male { background: #dbeafe; color: #1d4ed8; }
        .badge-female { background: #fce7f3; color: #be185d; }

        /* Role badge */
        .role-chip {
            display: inline-flex; align-items: center; gap: .25rem;
            padding: .2rem .65rem; border-radius: 999px;
            font-size: .65rem; font-weight: 700;
            background: linear-gradient(90deg, #0f766e, #14b8a6);
            color: #fff; margin-top: .2rem;
        }

        .card-mid {
            background: #f0fdf4;
            padding: .6rem 1.2rem;
            border-top: 1px solid #d1fae5;
            border-bottom: 1px solid #d1fae5;
            display: flex; gap: 1rem;
        }
        .mid-item { flex: 1; }
        .mid-label { font-size: .6rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: #94a3b8; }
        .mid-val { font-size: .82rem; font-weight: 700; color: #0f172a; }

        .card-footer {
            background: #fff; padding: .8rem 1.2rem;
            display: flex; align-items: center; justify-content: space-between; gap: .75rem;
        }
        .qr-block {
            background: #fff; border: 1.5px solid #d1fae5;
            border-radius: 10px; padding: .35rem;
            display: flex; align-items: center; justify-content: center;
        }
        .qr-block svg { width: 70px !important; height: 70px !important; }
        .card-no-block { flex: 1; }
        .card-no-label { font-size: .6rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: #94a3b8; margin-bottom: .2rem; }
        .card-no-val { font-family: 'DM Mono', monospace; font-size: .65rem; color: #0f766e; font-weight: 600; word-break: break-all; }
        .validity-block { text-align: right; }
        .validity-label { font-size: .6rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: #94a3b8; margin-bottom: .2rem; }
        .validity-val { font-size: .78rem; font-weight: 700; color: #059669; }

        .card-strip-bottom {
            height: 7px;
            background: linear-gradient(90deg, #0f766e, #14b8a6, #f59e0b, #0f766e);
        }

        /* ── BACK ── */
        .id-card-back {
            width: 340px;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,.22), 0 4px 16px rgba(0,0,0,.12);
        }
        .back-stripe { background: linear-gradient(90deg, #064e3b, #0f766e); height: 44px; }
        .back-body { background: #fff; padding: .9rem 1.2rem; }
        .back-info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: .5rem .8rem; margin-bottom: .8rem; }
        .back-field-label { font-size: .62rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: #94a3b8; }
        .back-field-val { font-size: .8rem; font-weight: 600; color: #0f172a; margin-top: .1rem; }
        .back-divider { border: none; border-top: 1px solid #d1fae5; margin: .6rem 0; }
        .back-footer { display: flex; align-items: center; justify-content: space-between; }
        .lost-msg { font-size: .65rem; color: #94a3b8; max-width: 180px; line-height: 1.4; }
        .status-chip { display: inline-flex; align-items: center; gap: .3rem; padding: .3rem .75rem; border-radius: 999px; font-size: .72rem; font-weight: 700; }
        .chip-active { background: #dcfce7; color: #166534; }
        .chip-revoked { background: #fee2e2; color: #991b1b; }
        .back-gold-strip { height: 4px; background: linear-gradient(90deg, #f59e0b, #fbbf24, #f59e0b); }

        /* Buttons */
        .action-row { display: flex; gap: .75rem; justify-content: center; flex-wrap: wrap; }
        .btn { display: inline-flex; align-items: center; gap: .4rem; padding: .55rem 1.2rem; border-radius: 10px; font-size: .88rem; font-weight: 600; cursor: pointer; border: none; text-decoration: none; transition: .15s; }
        .btn-primary { background: #0f766e; color: #fff; }
        .btn-primary:hover { background: #0d6460; }
        .btn-outline { background: transparent; color: #0f766e; border: 1.5px solid #0f766e; }
        .btn-outline:hover { background: rgba(15,118,110,.08); }
        .btn-danger { background: #dc2626; color: #fff; }
        .btn-danger:hover { background: #b91c1c; }
    </style>
</head>
<body>

    {{-- ── FRONT ────────────────────────────────────── --}}
    <div class="card-container">
        <div class="id-card">
            <div class="card-strip-top"></div>

            <div class="card-header">
                <div class="diamond-accent">
                    <div class="diamond"></div>
                    <div class="diamond"></div>
                    <div class="diamond"></div>
                </div>
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
                        <span class="card-type-tag"><i class="fas fa-chalkboard-teacher" style="font-size:.55rem;"></i> &nbsp;Staff Identity Card</span>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <div class="photo-wrap">
                    <div class="photo-circle">
                        @if($photoUrl)
                            <img src="{{ $photoUrl }}" alt="">
                        @else
                            <span class="photo-initials">{{ strtoupper(substr($teacher->firstname,0,1).substr($teacher->surname,0,1)) }}</span>
                        @endif
                    </div>
                    <div class="photo-gold-bar"></div>
                </div>

                <div class="info-block">
                    <div class="teacher-name">{{ $teacher->firstname }} {{ $teacher->surname }}</div>
                    @if($teacher->othername)
                    <div style="font-size:.72rem;color:#94a3b8;margin-bottom:.3rem;">{{ $teacher->othername }}</div>
                    @endif

                    <div class="info-row">
                        <span class="info-label">Emp No.</span>
                        <span class="info-val">{{ $teacher->employee_number ?? '—' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Phone</span>
                        <span class="info-val">{{ $teacher->phonenumber ?? '—' }}</span>
                    </div>
                    <div class="info-row" style="margin-top:.3rem;">
                        @if($teacher->gender === 'Male')
                            <span class="gender-badge badge-male"><i class="fas fa-mars" style="font-size:.65rem;"></i> Male</span>
                        @elseif($teacher->gender === 'Female')
                            <span class="gender-badge badge-female"><i class="fas fa-venus" style="font-size:.65rem;"></i> Female</span>
                        @else
                            <span class="gender-badge" style="background:#f1f5f9;color:#64748b;">{{ $teacher->gender ?? '—' }}</span>
                        @endif
                        &nbsp;
                        <span class="role-chip"><i class="fas fa-chalkboard-teacher" style="font-size:.55rem;"></i> Teacher</span>
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

            <div class="card-strip-bottom"></div>
        </div>
    </div>

    {{-- ── BACK ─────────────────────────────────────── --}}
    <div class="card-container">
        <div class="id-card-back">
            <div class="back-gold-strip"></div>
            <div class="back-stripe"></div>
            <div class="back-body">
                <div class="back-info-grid">
                    <div>
                        <div class="back-field-label">Full Name</div>
                        <div class="back-field-val">{{ $teacher->firstname }} {{ $teacher->surname }}</div>
                    </div>
                    <div>
                        <div class="back-field-label">Employee No.</div>
                        <div class="back-field-val">{{ $teacher->employee_number ?? '—' }}</div>
                    </div>
                    <div>
                        <div class="back-field-label">Phone Number</div>
                        <div class="back-field-val">{{ $teacher->phonenumber ?? '—' }}</div>
                    </div>
                    <div>
                        <div class="back-field-label">National ID</div>
                        <div class="back-field-val">{{ $teacher->national_id ?? '—' }}</div>
                    </div>
                    <div>
                        <div class="back-field-label">Email</div>
                        <div class="back-field-val" style="font-size:.72rem;">{{ $teacher->email ?? '—' }}</div>
                    </div>
                    <div>
                        <div class="back-field-label">Address</div>
                        <div class="back-field-val" style="font-size:.72rem;">{{ $teacher->address ?? '—' }}</div>
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
            <div class="back-gold-strip"></div>
        </div>
    </div>

    {{-- Action Buttons --}}
    <div class="action-row">
        <a href="{{ route('teacher-id-cards.print', $card->id) }}" class="btn btn-primary" target="_blank">
            <i class="fas fa-print"></i> Print / Download PDF
        </a>
        @if($card->status === 'active')
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
        } else {
            window.history.back();
        }
    }

    function revokeCard(cardId) {
        Swal.fire({
            title: 'Revoke this card?',
            text: 'This action will invalidate the teacher\'s ID card.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, revoke it!'
        }).then(result => {
            if (result.isConfirmed) {
                fetch(`/teacher-id-cards/revoke/${cardId}`, {
                    method: 'PATCH',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' }
                })
                .then(r => r.json())
                .then(data => {
                    Swal.fire('Revoked!', data.message, 'success').then(() => location.reload());
                });
            }
        });
    }
    </script>
</body>
</html>
