{{-- resources/views/student/id-cards/pdf-card.blade.php
    Single ID card PDF — CR80 card size (242pt x 153pt), 2 pages: FRONT + BACK.

    NOTE ON DOMPDF LAYOUT STRATEGY:
    DomPDF's support for `display:flex` + `gap` and `display:grid` is
    inconsistent across versions and prints unpredictably (squashed /
    overlapping elements). To get a pixel-accurate physical card we use
    `position:absolute` blocks anchored to a fixed-size `.card` canvas,
    plus plain HTML tables for label/value rows. Both are very reliably
    supported by DomPDF.
--}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 0; padding: 0; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body { width: 242pt; height: 153pt; }
        body { font-family: DejaVu Sans, sans-serif; background: #fff; -webkit-print-color-adjust: exact; }

        .page { width: 242pt; height: 153pt; position: relative; page-break-after: always; }
        .page:last-child { page-break-after: avoid; }

        .card {
            width: 242pt; height: 153pt;
            position: relative;
            border: 0.75pt solid #d7dce5;
            border-radius: 11pt;
            overflow: hidden;
            background: #fff;
        }

        /* ── shared bits ── */
        .strip-bottom {
            position: absolute; left: 0; right: 0; bottom: 0; height: 4pt;
            background: #2f2ccb;
        }

        /* ═══════════════════ FRONT ═══════════════════ */
        .front-header {
            position: absolute; top: 0; left: 0; right: 0; height: 34pt;
            background: #1a1869;
            background-image: linear-gradient(120deg, #1a1869 0%, #2f2ccb 60%, #100d70 100%);
        }
        .front-logo {
            position: absolute; top: 5pt; left: 8pt; width: 24pt; height: 24pt;
            border-radius: 12pt; overflow: hidden;
            border: 1pt solid rgba(255,255,255,.55);
            background: rgba(255,255,255,.15);
            text-align: center;
        }
        .front-logo img { width: 24pt; height: 24pt; }
        .front-logo .ph { color: rgba(255,255,255,.85); font-size: 11pt; font-weight: bold; line-height: 24pt; }

        .front-school-name {
            position: absolute; top: 6pt; left: 38pt; width: 196pt; height: 11pt;
            color: #fff; font-size: 7.5pt; font-weight: bold; text-transform: uppercase;
            overflow: hidden;
        }
        .front-school-tag {
            position: absolute; top: 18pt; left: 38pt;
            color: rgba(255,255,255,.85); font-size: 5.5pt; font-weight: bold;
            text-transform: uppercase; letter-spacing: 0.4pt;
            background: rgba(255,255,255,.18);
            padding: 1.5pt 6pt; border-radius: 6pt;
        }

        .front-photo {
            position: absolute; top: 40pt; left: 8pt; width: 50pt; height: 58pt;
            border-radius: 6pt; overflow: hidden;
            border: 1.5pt solid #2f2ccb;
            background: #e0e7ff;
            text-align: center;
        }
        .front-photo img { width: 50pt; height: 58pt; }
        .front-photo .init { display: block; line-height: 58pt; color: #2f2ccb; font-size: 16pt; font-weight: bold; }

        .front-name {
            position: absolute; top: 40pt; left: 64pt; width: 170pt; height: 10pt;
            font-size: 8.5pt; font-weight: bold; color: #0f172a; overflow: hidden;
        }

        .front-rows { position: absolute; top: 51pt; left: 64pt; width: 170pt; }
        .front-rows table { width: 100%; border-collapse: collapse; }
        .front-rows td { padding: 0.8pt 0; font-size: 6.6pt; vertical-align: middle; }
        .front-rows .lbl { width: 40pt; color: #94a3b8; font-weight: bold; text-transform: uppercase; }
        .front-rows .val { color: #334155; font-weight: bold; }

        .gender-pill {
            position: absolute; top: 82pt; left: 64pt;
            font-size: 6pt; font-weight: bold; padding: 1.5pt 6pt; border-radius: 6pt;
        }
        .pill-male { background: #dbeafe; color: #1d4ed8; }
        .pill-female { background: #fce7f3; color: #be185d; }
        .pill-other { background: #f1f5f9; color: #64748b; }

        .front-mid {
            position: absolute; top: 98pt; left: 0; right: 0; height: 20pt;
            background: #f8fafc;
            border-top: 0.75pt solid #e2e8f0;
            border-bottom: 0.75pt solid #e2e8f0;
        }
        .front-mid table { width: 100%; height: 100%; border-collapse: collapse; }
        .front-mid td { width: 33.33%; text-align: left; padding: 3pt 0 0 8pt; vertical-align: top; }
        .mid-lbl { font-size: 5pt; color: #94a3b8; font-weight: bold; text-transform: uppercase; }
        .mid-val { font-size: 6.6pt; color: #0f172a; font-weight: bold; margin-top: 1pt; }

        .front-footer { position: absolute; top: 122pt; left: 0; right: 0; height: 27pt; }
        .qr-box {
            position: absolute; top: 0; left: 8pt; width: 30pt; height: 30pt;
            border: 0.75pt solid #e2e8f0; border-radius: 5pt;
            text-align: center;
        }
        .qr-box svg { width: 26pt !important; height: 26pt !important; margin-top: 1.5pt; }
        .card-no-block { position: absolute; top: 1pt; left: 44pt; width: 190pt; }
        .card-no-lbl { font-size: 5pt; color: #94a3b8; font-weight: bold; text-transform: uppercase; }
        .card-no-val { font-size: 6.3pt; color: #2f2ccb; font-family: DejaVu Sans Mono, monospace; margin-top: 1pt; }
        .status-lbl { font-size: 5pt; color: #94a3b8; font-weight: bold; text-transform: uppercase; margin-top: 4pt; }
        .status-val { font-size: 7pt; font-weight: bold; margin-top: 1pt; }
        .status-active { color: #059669; }
        .status-revoked { color: #dc2626; }
        .status-expired { color: #d97706; }

        /* ═══════════════════ BACK ═══════════════════ */
        .back-header {
            position: absolute; top: 0; left: 0; right: 0; height: 22pt;
            background: #1a1869;
        }
        .back-header-txt {
            position: absolute; top: 6pt; left: 10pt;
            color: #fff; font-size: 6.5pt; font-weight: bold; text-transform: uppercase; letter-spacing: 0.4pt;
        }

        .back-grid { position: absolute; top: 30pt; left: 10pt; width: 222pt; }
        .back-grid table { width: 100%; border-collapse: collapse; }
        .back-grid td { width: 50%; padding: 3.5pt 6pt 3.5pt 0; vertical-align: top; }
        .back-lbl { font-size: 5.5pt; color: #94a3b8; font-weight: bold; text-transform: uppercase; }
        .back-val { font-size: 7pt; color: #0f172a; font-weight: bold; margin-top: 1pt; }

        .back-divider {
            position: absolute; top: 100pt; left: 10pt; right: 10pt; height: 0;
            border-top: 0.75pt solid #e2e8f0;
        }

        .back-footer { position: absolute; top: 108pt; left: 10pt; right: 10pt; height: 38pt; }
        .lost-msg { position: absolute; top: 0; left: 0; width: 150pt; font-size: 5.8pt; color: #94a3b8; line-height: 1.5; }
        .status-chip {
            position: absolute; top: 0; right: 0;
            font-size: 6.6pt; font-weight: bold; padding: 3pt 8pt; border-radius: 8pt;
        }
        .chip-active { background: #dcfce7; color: #166534; }
        .chip-inactive { background: #fee2e2; color: #991b1b; }

        .back-strip-top { position: absolute; top: 22pt; left: 0; right: 0; height: 2pt; background: #2f2ccb; }
    </style>
</head>
<body>

{{-- ═══════════════════ FRONT ═══════════════════ --}}
<div class="page">
    <div class="card">
        <div class="front-header">
            <div class="front-logo">
                @if($logoUrl)
                    <img src="{{ $logoUrl }}" alt="">
                @else
                    <span class="ph">{{ strtoupper(substr($school->name ?? 'S', 0, 1)) }}</span>
                @endif
            </div>
            <div class="front-school-name">{{ Str::limit($school->name ?? 'School Name', 42, '') }}</div>
            <div class="front-school-tag">Student Identity Card</div>
        </div>

        <div class="front-photo">
            @if($photoUrl)
                <img src="{{ $photoUrl }}" alt="">
            @else
                <span class="init">{{ strtoupper(substr($student->firstname,0,1).substr($student->lastname,0,1)) }}</span>
            @endif
        </div>

        <div class="front-name">{{ Str::limit(strtoupper($student->firstname . ' ' . $student->lastname), 26, '') }}</div>

        <div class="front-rows">
            <table>
                <tr><td class="lbl">Adm No.</td><td class="val">{{ $student->admission_number ?? $student->registration_number ?? '—' }}</td></tr>
                <tr><td class="lbl">Class</td><td class="val">{{ $className }}</td></tr>
                <tr><td class="lbl">Stream</td><td class="val">{{ $student->stream ?? '—' }}</td></tr>
            </table>
        </div>

        @if($student->gender === 'Male')
            <div class="gender-pill pill-male">Male</div>
        @elseif($student->gender === 'Female')
            <div class="gender-pill pill-female">Female</div>
        @else
            <div class="gender-pill pill-other">{{ $student->gender ?? '—' }}</div>
        @endif

        <div class="front-mid">
            <table>
                <tr>
                    <td>
                        <div class="mid-lbl">Year</div>
                        <div class="mid-val">{{ $card->academic_year }}</div>
                    </td>
                    <td>
                        <div class="mid-lbl">Issued</div>
                        <div class="mid-val">{{ $card->issue_date?->format('d/m/Y') }}</div>
                    </td>
                    <td>
                        <div class="mid-lbl">Expires</div>
                        <div class="mid-val" style="color:#dc2626;">{{ $card->expiry_date?->format('d/m/Y') }}</div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="front-footer">
            <div class="qr-box">{!! $qrSvg !!}</div>
            <div class="card-no-block">
                <div class="card-no-lbl">Card Number</div>
                <div class="card-no-val">{{ $card->card_number }}</div>
                <div class="status-lbl">Status</div>
                <div class="status-val status-{{ $card->status }}">{{ strtoupper($card->status) }}</div>
            </div>
        </div>

        <div class="strip-bottom"></div>
    </div>
</div>

{{-- ═══════════════════ BACK ═══════════════════ --}}
<div class="page">
    <div class="card">
        <div class="back-header">
            <div class="back-header-txt">{{ Str::limit($school->name ?? 'School Name', 40, '') }} &middot; Student ID</div>
        </div>
        <div class="back-strip-top"></div>

        <div class="back-grid">
            <table>
                <tr>
                    <td>
                        <div class="back-lbl">Full Name</div>
                        <div class="back-val">{{ Str::limit($student->firstname . ' ' . $student->lastname, 24, '') }}</div>
                    </td>
                    <td>
                        <div class="back-lbl">Nationality</div>
                        <div class="back-val">{{ $student->nationality ?? '—' }}</div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="back-lbl">Date of Birth</div>
                        <div class="back-val">{{ $student->date_of_birth ? \Carbon\Carbon::parse($student->date_of_birth)->format('d M Y') : '—' }}</div>
                    </td>
                    <td>
                        <div class="back-lbl">Place of Birth</div>
                        <div class="back-val">{{ Str::limit($student->place_of_birth ?? '—', 20, '') }}</div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="back-lbl">Guardian</div>
                        <div class="back-val">{{ Str::limit($student->guardian_names ?? '—', 22, '') }}</div>
                    </td>
                    <td>
                        <div class="back-lbl">Contact</div>
                        <div class="back-val">{{ $student->primary_contact ?? '—' }}</div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="back-divider"></div>

        <div class="back-footer">
            <div class="lost-msg">
                If found, please return to<br>
                <strong>{{ $school->name ?? 'the school' }}</strong>{{ ($school->phone ?? null) ? ' — ' . $school->phone : '' }}
            </div>
            @if($card->status === 'active')
                <div class="status-chip chip-active">VALID</div>
            @else
                <div class="status-chip chip-inactive">{{ strtoupper($card->status) }}</div>
            @endif
        </div>

        <div class="strip-bottom"></div>
    </div>
</div>

</body>
</html>
