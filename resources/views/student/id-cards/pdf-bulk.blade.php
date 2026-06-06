{{-- resources/views/student/id-cards/pdf-bulk.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family: DejaVu Sans, sans-serif; background: #fff; }

        .page-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            padding: 20px;
        }

        /* ── card ── */
        .card {
            width: 242px;
            height: 153px;
            border-radius: 10px;
            overflow: hidden;
            position: relative;
            border: 1px solid #ccc;
            page-break-inside: avoid;
        }
        .strip-top    { height: 5px; background: #2f2ccb; }
        .strip-bottom { height: 5px; background: #2f2ccb; position: absolute; bottom:0; left:0; right:0; }

        .card-header {
            background: #1a1869;
            padding: 5px 8px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .logo-box {
            width: 26px; height: 26px;
            border-radius: 50%; overflow: hidden;
            border: 1.5px solid rgba(255,255,255,.4);
            background: rgba(255,255,255,.1);
            display: flex; align-items: center; justify-content: center;
        }
        .logo-box img { width: 100%; height: 100%; object-fit: cover; }
        .logo-placeholder { color: rgba(255,255,255,.7); font-size: 11px; font-weight: bold; }
        .school-name-txt { color: #fff; font-size: 6.5px; font-weight: bold; text-transform: uppercase; }
        .card-type-txt   { color: rgba(255,255,255,.75); font-size: 5.5px; }

        .card-body { display: flex; padding: 5px 7px; gap: 6px; background: #fff; }
        .photo-box {
            width: 50px; height: 56px;
            border-radius: 6px; overflow: hidden;
            border: 2px solid #2f2ccb;
            background: #e0e7ff;
            flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
        }
        .photo-box img { width:100%; height:100%; object-fit: cover; }
        .photo-initial { color: #2f2ccb; font-size: 16px; font-weight: bold; }

        .info-col { flex: 1; }
        .student-name { font-size: 7.5px; font-weight: bold; color: #0f172a; margin-bottom: 2px; }
        .info-row { display:flex; gap:3px; margin-bottom:1.5px; }
        .lbl { font-size:5.5px; color:#94a3b8; font-weight:bold; text-transform:uppercase; min-width:38px; }
        .val { font-size:6.5px; color:#334155; font-weight:bold; }

        .card-mid { display:flex; background:#f8fafc; border-top:1px solid #e2e8f0; padding:3px 7px; gap:7px; }
        .mid-col { flex:1; }
        .mid-lbl { font-size:5px; color:#94a3b8; font-weight:bold; text-transform:uppercase; }
        .mid-val { font-size:6.5px; color:#0f172a; font-weight:bold; }

        .card-foot { display:flex; align-items:center; padding:3px 7px; gap:5px; background:#fff; }
        .qr-wrap { width:44px; height:44px; }
        .qr-wrap svg { width:44px !important; height:44px !important; }
        .foot-right { flex:1; }
        .card-no-lbl { font-size:5px; color:#94a3b8; font-weight:bold; text-transform:uppercase; margin-bottom:2px; }
        .card-no-txt { font-size:5.5px; color:#2f2ccb; font-family:monospace; }
        .valid-lbl { font-size:5px; color:#94a3b8; font-weight:bold; text-transform:uppercase; margin-top:2px; }
        .valid-txt { font-size:6.5px; color:#059669; font-weight:bold; }
    </style>
</head>
<body>
<div class="page-grid">
    @foreach($cardsData as $item)
    @php
        $card       = $item['card'];
        $student    = $item['student'];
        $qrSvg      = $item['qrSvg'];
        $className  = $item['className'];
        $streamName = $item['streamName'];
        $photoUrl   = $item['photoUrl'];
    @endphp
    <div class="card">
        <div class="strip-top"></div>
        <div class="card-header">
            <div class="logo-box">
                @if($logoUrl)
                    <img src="{{ $logoUrl }}" alt="">
                @else
                    <span class="logo-placeholder">S</span>
                @endif
            </div>
            <div>
                <div class="school-name-txt">{{ $school->name ?? 'School Name' }}</div>
                <div class="card-type-txt">Student Identity Card</div>
            </div>
        </div>
       
        <div class="card-body">
            <div class="photo-box">
                @if($photoUrl)
                    <img src="{{ $photoUrl }}" alt="">
                @else
                    <span class="photo-initial">{{ strtoupper(substr($student->firstname,0,1).substr($student->lastname,0,1)) }}</span>
                @endif
            </div>
            <div class="info-col">
                <div class="student-name">{{ strtoupper($student->firstname . ' ' . $student->lastname) }}</div>
                <div class="info-row"><span class="lbl">Adm No.</span><span class="val">{{ $student->admission_number ?? $student->registration_number ?? '—' }}</span></div>
                <div class="info-row"><span class="lbl">Class</span><span class="val">{{ $className }}</span></div>
                <div class="info-row"><span class="lbl">Stream</span><span class="val">{{ $student->stream ?? '—' }}</span></div>
                <div class="info-row"><span class="lbl">Gender</span><span class="val">{{ $student->gender }}</span></div>
            </div>
        </div>
        <div class="card-mid">
            <div class="mid-col"><div class="mid-lbl">Year</div><div class="mid-val">{{ $card->academic_year }}</div></div>
            <div class="mid-col"><div class="mid-lbl">Issued</div><div class="mid-val">{{ $card->issue_date?->format('d/m/Y') }}</div></div>
            <div class="mid-col"><div class="mid-lbl">Expires</div><div class="mid-val" style="color:#dc2626;">{{ $card->expiry_date?->format('d/m/Y') }}</div></div>
        </div>
        <div class="card-foot">
            <div class="qr-wrap">{!! $qrSvg !!}</div>
            <div class="foot-right">
                <div class="card-no-lbl">Card Number</div>
                <div class="card-no-txt">{{ $card->card_number }}</div>
                <div class="valid-lbl">Status</div>
                <div class="valid-txt">{{ strtoupper($card->status) }}</div>
            </div>
        </div>
        <div class="strip-bottom"></div>
    </div>
    @endforeach
</div>
</body>
</html>
