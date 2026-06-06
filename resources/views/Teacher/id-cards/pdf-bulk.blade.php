{{-- resources/views/teacher/id-cards/pdf-bulk.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family: DejaVu Sans, sans-serif; background: #fff; }

        .page-header {
            text-align: center;
            padding: 10px 20px 8px;
            border-bottom: 2px solid #0f766e;
            margin-bottom: 12px;
        }
        .page-header-title { font-size: 13px; font-weight: bold; color: #064e3b; text-transform: uppercase; letter-spacing: .05em; }
        .page-header-sub { font-size: 9px; color: #64748b; margin-top: 2px; }

        .page-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            padding: 0 20px 20px;
        }

        .card {
            width: 242px; height: 153px;
            border-radius: 10px; overflow: hidden;
            position: relative; border: 1px solid #ccc;
            page-break-inside: avoid;
        }

        .strip-rainbow {
            height: 4px;
            background: linear-gradient(90deg, #0f766e 0%, #14b8a6 40%, #f59e0b 70%, #0f766e 100%);
        }

        .card-header {
            background: #064e3b; padding: 4px 7px;
            display: flex; align-items: center; gap: 5px;
        }
        .logo-box {
            width: 26px; height: 26px; border-radius: 50%;
            overflow: hidden; border: 1.5px solid rgba(245,158,11,.6);
            background: rgba(255,255,255,.1);
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
        }
        .logo-box img { width: 100%; height: 100%; object-fit: cover; }
        .logo-placeholder { color: rgba(255,255,255,.7); font-size: 11px; font-weight: bold; }
        .school-name-txt { color: #fff; font-size: 6.5px; font-weight: bold; text-transform: uppercase; }
        .card-type-txt   { color: #fbbf24; font-size: 5.5px; font-weight: bold; }

        .card-body { display:flex; padding: 5px 7px; gap: 6px; background: #fff; }
        .photo-box {
            width: 48px; height: 56px; border-radius: 6px; overflow: hidden;
            border: 2px solid #0f766e; background: #ccfbf1; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
        }
        .photo-box img { width:100%; height:100%; object-fit: cover; }
        .photo-initial { color: #0f766e; font-size: 15px; font-weight: bold; }
        .photo-gold { height: 2px; background: linear-gradient(90deg, #f59e0b, #fbbf24); margin-top:-2px; }

        .info-col { flex: 1; }
        .teacher-name { font-size: 7px; font-weight: bold; color: #0f172a; margin-bottom: 2px; }
        .info-row { display: flex; gap: 3px; margin-bottom: 1.5px; }
        .lbl { font-size: 5px; color: #94a3b8; font-weight: bold; text-transform: uppercase; min-width: 35px; }
        .val { font-size: 6px; color: #334155; font-weight: bold; }
        .role-tag { display:inline-block; background:#0f766e; color:#fff; font-size:5px; font-weight:bold; padding:1px 4px; border-radius:999px; margin-top:2px; text-transform:uppercase; }

        .card-mid { display:flex; background:#f0fdf4; border-top:1px solid #d1fae5; padding: 2.5px 7px; gap: 6px; }
        .mid-col { flex:1; }
        .mid-lbl { font-size:4.5px; color:#94a3b8; font-weight:bold; text-transform:uppercase; }
        .mid-val { font-size:6px; color:#0f172a; font-weight:bold; }

        .card-foot { display:flex; align-items:center; padding: 3px 7px; gap: 5px; background:#fff; }
        .qr-svg { width:42px; height:42px; }
        .qr-svg svg { width:42px!important; height:42px!important; }
        .foot-right { flex:1; }
        .card-no-lbl { font-size:4.5px; color:#94a3b8; font-weight:bold; text-transform:uppercase; margin-bottom:1px; }
        .card-no-txt { font-size:5px; color:#0f766e; font-family:monospace; word-break:break-all; }
        .valid-lbl { font-size:4.5px; color:#94a3b8; font-weight:bold; text-transform:uppercase; margin-top:2px; }
        .valid-txt { font-size:6px; color:#059669; font-weight:bold; }
    </style>
</head>
<body>

    <div class="page-header">
        <div class="page-header-title">{{ $school->name ?? 'School' }} — Teacher ID Cards</div>
        <div class="page-header-sub">Generated: {{ now()->format('d M Y') }} &bull; Academic Year: {{ \App\Http\Controllers\Helper::active_year() }}</div>
    </div>

    <div class="page-grid">
        @foreach($cardsData as $item)
        @php
            $card    = $item['card'];
            $teacher = $item['teacher'];
            $qrSvg   = $item['qrSvg'];
            $photoUrl= $item['photoUrl'];
        @endphp
        <div class="card">
            <div class="strip-rainbow"></div>

            <div class="card-header">
                <div class="logo-box">
                    @if(isset($logoUrl) && $logoUrl)
                        <img src="{{ $logoUrl }}" alt="">
                    @else
                        <span class="logo-placeholder">S</span>
                    @endif
                </div>
                <div>
                    <div class="school-name-txt">{{ $school->name ?? 'School' }}</div>
                    <div class="card-type-txt">&#9670; STAFF IDENTITY CARD</div>
                </div>
            </div>

            <div class="card-body">
                <div>
                    <div class="photo-box">
                        @if($photoUrl)
                            <img src="{{ $photoUrl }}" alt="">
                        @else
                            <span class="photo-initial">{{ strtoupper(substr($teacher->firstname,0,1).substr($teacher->surname,0,1)) }}</span>
                        @endif
                    </div>
                    <div class="photo-gold"></div>
                </div>
                <div class="info-col">
                    <div class="teacher-name">{{ $teacher->firstname }} {{ $teacher->surname }}</div>
                    <div class="info-row">
                        <span class="lbl">Emp No.</span>
                        <span class="val">{{ $teacher->employee_number ?? '—' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="lbl">Phone</span>
                        <span class="val">{{ $teacher->phonenumber ?? '—' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="lbl">Gender</span>
                        <span class="val">{{ $teacher->gender ?? '—' }}</span>
                    </div>
                    <span class="role-tag">Teacher</span>
                </div>
            </div>

            <div class="card-mid">
                <div class="mid-col">
                    <div class="mid-lbl">Year</div>
                    <div class="mid-val">{{ $card->academic_year }}</div>
                </div>
                <div class="mid-col">
                    <div class="mid-lbl">Issued</div>
                    <div class="mid-val">{{ $card->issue_date?->format('d M Y') }}</div>
                </div>
                <div class="mid-col">
                    <div class="mid-lbl">Expires</div>
                    <div class="mid-val" style="color:#dc2626;">{{ $card->expiry_date?->format('d M Y') }}</div>
                </div>
            </div>

            <div class="card-foot">
                <div class="qr-svg">{!! $qrSvg !!}</div>
                <div class="foot-right">
                    <div class="card-no-lbl">Card Number</div>
                    <div class="card-no-txt">{{ $card->card_number }}</div>
                    <div class="valid-lbl">Status</div>
                    <div class="valid-txt">{{ strtoupper($card->status) }}</div>
                </div>
            </div>

            <div class="strip-rainbow"></div>
        </div>
        @endforeach
    </div>

</body>
</html>
