{{-- resources/views/student/id-cards/pdf-bulk.blade.php
    Batch ID card print sheet — A4 portrait. One student per row: FRONT | BACK side by side,
    each in a dashed cut-guide, so a sheet can be printed and trimmed straight away.

    Same artwork as the single-card PDF (partials/pdf-card-*.blade.php).
    Layout is a plain HTML table (flex/grid are unreliable in DomPDF); <thead> repeats on
    every page and `page-break-inside: avoid` keeps a student's two faces together.
--}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 30pt 28pt 34pt 28pt; }
        * { margin: 0; padding: 0; }
        body { font-family: Helvetica, Arial, sans-serif; background: #ffffff; color: #0f172a; }

        /* running sheet header (repeats on each page via <thead>) */
        .sheet-head-cell { padding: 0 0 9pt 0; border-bottom: 1.5pt solid #2f2ccb; }
        .sheet-title { font-size: 12pt; font-weight: bold; color: #1a1869; }
        .sheet-sub   { font-size: 7.2pt; color: #64748b; padding-top: 2pt; }
        .sheet-meta  { text-align: right; font-size: 7.2pt; color: #64748b; line-height: 10pt; }
        .sheet-meta b { color: #0f172a; }

        .grid-table { width: 100%; border-collapse: collapse; }
        .grid-table td.cell { padding: 11pt 0 0 0; vertical-align: top; page-break-inside: avoid; }
        .pair { border-collapse: collapse; margin: 0 auto; }
        .pair td { padding: 0 5pt; vertical-align: top; }
        .side-lbl { font-size: 5.6pt; line-height: 8pt; font-weight: bold; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5pt; padding-bottom: 2pt; text-align: center; }
        .cut {
            width: 241pt; height: 152pt;
            padding: 0;
            border: 0.6pt dashed #b6bfcc;
            border-radius: 8pt; overflow: hidden;
        }
        .cut .card { border-radius: 8pt; }

        @include('student.id-cards.partials.pdf-card-styles')
    </style>
</head>
<body>

<table class="grid-table">
    <thead>
        <tr>
            <td class="sheet-head-cell">
                <table style="width:100%; border-collapse:collapse;">
                    <tr>
                        <td>
                            <div class="sheet-title">{{ $school->name ?? 'School' }}</div>
                            <div class="sheet-sub">Student ID Cards &middot; Batch Print &middot; Academic Year {{ $activeYear ?? '' }}</div>
                        </td>
                        <td class="sheet-meta">
                            Generated {{ \Carbon\Carbon::now()->format('d M Y, H:i') }}<br>
                            <b>{{ $cardCount }}</b> card(s)
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </thead>
    <tbody>
    @foreach($cardItems as $item)
        @php
            $card       = $item['card'];
            $student    = $item['student'];
            $qrImg      = $item['qrImg'];
            $className  = $item['className'];
            $streamName = $item['streamName'];
            $photoUrl   = $item['photoUrl'];
        @endphp
        <tr>
            <td class="cell">
                <table class="pair">
                    <tr>
                        <td>
                            <div class="side-lbl">Front</div>
                            <div class="cut">@include('student.id-cards.partials.pdf-card-front')</div>
                        </td>
                        <td>
                            <div class="side-lbl">Back</div>
                            <div class="cut">@include('student.id-cards.partials.pdf-card-back')</div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>

</body>
</html>
