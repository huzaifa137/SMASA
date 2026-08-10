{{-- resources/views/student/id-cards/pdf-bulk.blade.php
    Batch ID card print sheet — A4, 2 cards per row.

    NOTE ON LAYOUT: CSS grid/flex + gap are unreliable in DomPDF, so the
    sheet is built as a plain HTML table (2 columns). Table headers
    (`<thead>`) repeat automatically on every page, and `page-break-inside:
    avoid` on each row keeps a card from being split across a page break.
    Each card reuses the same design language as the single-card PDF
    (pdf-card.blade.php) so single prints and batch prints look identical.
--}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 26pt 24pt; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; background: #fff; -webkit-print-color-adjust: exact; }

        /* ── running sheet header (repeats each page via <thead>) ── */
        .sheet-head-cell { padding: 0 0 12pt 0; border-bottom: 0.75pt solid #e2e8f0; }
        .sheet-head { width: 100%; }
        .sheet-title { font-size: 11pt; font-weight: bold; color: #0f172a; }
        .sheet-sub { font-size: 7.5pt; color: #64748b; margin-top: 2pt; }
        .sheet-meta { text-align: right; font-size: 7.5pt; color: #64748b; }

        /* ── grid table ── */
        .grid-table { width: 100%; border-collapse: collapse; }
        .grid-table td.cell {
            width: 50%;
            padding: 12pt 8pt;
            text-align: center;
            vertical-align: top;
            page-break-inside: avoid;
        }

        /* ── card (mirrors pdf-card.blade.php) ── */
        .card-wrap { display: inline-block; padding: 6pt; border: 0.5pt dashed #cbd5e1; border-radius: 13pt; }
        .card {
            width: 242pt; height: 153pt;
            position: relative;
            border: 0.75pt solid #d7dce5;
            border-radius: 11pt;
            overflow: hidden;
            background: #fff;
        }
        .strip-bottom { position: absolute; left: 0; right: 0; bottom: 0; height: 4pt; background: #2f2ccb; }

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
    </style>
</head>
<body>

<table class="grid-table">
    <thead>
        <tr>
            <td colspan="2" class="sheet-head-cell">
                <table class="sheet-head">
                    <tr>
                        <td>
                            <div class="sheet-title">{{ $school->name ?? 'School' }}</div>
                            <div class="sheet-sub">Student ID Cards &middot; Batch Print &middot; {{ $activeYear ?? '' }}</div>
                        </td>
                        <td class="sheet-meta">
                            Generated {{ \Carbon\Carbon::now()->format('d M Y, H:i') }}<br>
                            {{ $cardRows->sum(fn($row) => $row->count()) }} card(s)
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </thead>
    <tbody>
    @foreach($cardRows as $row)
        <tr>
        @foreach($row as $item)
            @php
                $card       = $item['card'];
                $student    = $item['student'];
                $qrSvg      = $item['qrSvg'];
                $className  = $item['className'];
                $photoUrl   = $item['photoUrl'];
            @endphp
            <td class="cell">
                <div class="card-wrap">
                    <div class="card">
                        <div class="front-header">
                            <div class="front-logo">
                                @if($logoUrl)
                                    <img src="{{ $logoUrl }}" alt="">
                                @else
                                    <span class="ph">{{ strtoupper(substr($school->name ?? 'S', 0, 1)) }}</span>
                                @endif
                            </div>
                            <div class="front-school-name">{{ \Illuminate\Support\Str::limit($school->name ?? 'School Name', 42, '') }}</div>
                            <div class="front-school-tag">Student Identity Card</div>
                        </div>

                        <div class="front-photo">
                            @if($photoUrl)
                                <img src="{{ $photoUrl }}" alt="">
                            @else
                                <span class="init">{{ strtoupper(substr($student->firstname,0,1).substr($student->lastname,0,1)) }}</span>
                            @endif
                        </div>

                        <div class="front-name">{{ \Illuminate\Support\Str::limit(strtoupper($student->firstname . ' ' . $student->lastname), 26, '') }}</div>

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
            </td>
        @endforeach
        @if($row->count() < 2)
            <td class="cell"></td>
        @endif
        </tr>
    @endforeach
    </tbody>
</table>

</body>
</html>
