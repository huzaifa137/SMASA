{{-- resources/views/student/id-cards/partials/pdf-card-front.blade.php
    Front face of the printed ID card. Expects: $card, $student, $school,
    $className, $streamName, $photoUrl, $logoUrl, $qrImg (svg data-URI). --}}
@php
    // Gradient artwork is pre-built (DomPDF can't paint CSS/SVG gradients) — see App\Helpers\IdCardArt.
    $headImg  = \App\Helpers\IdCardArt::head();
    $stripImg = \App\Helpers\IdCardArt::strip();
    $fullName = trim($student->firstname . ' ' . $student->lastname);
    $schoolNm = $school->name ?? 'School Name';
    // Long school names step the font down so they stay on one line inside the header.
    $schoolLen  = mb_strlen($schoolNm);
    $schoolSize = $schoolLen > 40 ? '5.2pt' : ($schoolLen > 32 ? '5.9pt' : ($schoolLen > 26 ? '6.6pt' : '7.2pt'));
    $gender   = $student->gender ?? null;
    $status   = $card->status ?? 'active';
@endphp
<div class="card">
    <img class="f-head-bg" src="{{ $headImg }}" alt="">

    <div class="f-logo">
        @if(!empty($logoUrl))
            <img src="{{ $logoUrl }}" alt="">
        @else
            <div class="ph">{{ strtoupper(substr($schoolNm, 0, 1)) }}</div>
        @endif
    </div>
    <div class="f-school" style="font-size: {{ $schoolSize }};">{{ \Illuminate\Support\Str::limit($schoolNm, 56, '...') }}</div>
    <div class="f-tag">Student Identity Card</div>

    @if(!empty($photoUrl))
        <div class="f-photo" style="background-image: url('{{ $photoUrl }}');"></div>
    @else
        <div class="f-photo"><span class="init">{{ strtoupper(substr($student->firstname, 0, 1) . substr($student->lastname, 0, 1)) }}</span></div>
    @endif

    <div class="f-name">{{ \Illuminate\Support\Str::limit($fullName, 24, '...') }}</div>

    <div class="f-rows">
        <table>
            <tr><td class="lbl">LIN No.</td><td class="val">{{ $student->admission_number ?? $student->registration_number ?? '—' }}</td></tr>
            <tr><td class="lbl">Class</td><td class="val">{{ $className ?: '—' }}</td></tr>
            <tr><td class="lbl">Stream</td><td class="val">{{ $streamName ?: '—' }}</td></tr>
        </table>
    </div>

    @if($gender === 'Male')
        <div class="f-pill pill-male">Male</div>
    @elseif($gender === 'Female')
        <div class="f-pill pill-female">Female</div>
    @elseif($gender)
        <div class="f-pill pill-other">{{ $gender }}</div>
    @endif

    <div class="f-qr"><img src="{{ $qrImg }}" alt=""></div>
    <div class="f-qr-cap">Scan to verify</div>

    <div class="f-mid">
        <table>
            <tr>
                <td><div class="m-lbl">Year</div><div class="m-val">{{ $card->academic_year }}</div></td>
                <td><div class="m-lbl">Issued</div><div class="m-val">{{ $card->issue_date?->format('d M Y') }}</div></td>
                <td><div class="m-lbl">Expires</div><div class="m-val m-exp">{{ $card->expiry_date?->format('d M Y') }}</div></td>
            </tr>
        </table>
    </div>

    <div class="f-cardno-lbl">Card Number</div>
    <div class="f-cardno-val">{{ $card->card_number }}</div>
    <div class="f-status-lbl">Status</div>
    <div class="f-status-val st-{{ $status }}">{{ strtoupper($status) }}</div>

    <img class="f-strip" src="{{ $stripImg }}" alt="">
</div>
