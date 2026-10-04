{{-- resources/views/student/id-cards/partials/pdf-card-back.blade.php
    Back face of the printed ID card. Expects: $card, $student, $school. --}}
@php
    $stripImgB = \App\Helpers\IdCardArt::strip();
    $schoolNmB = $school->name ?? 'the school';
    $statusB   = $card->status ?? 'active';
@endphp
<div class="card">
    <div class="b-head"></div>
    <div class="b-head-txt">{{ \Illuminate\Support\Str::limit($schoolNmB, 34, '...') }} &middot; Student ID</div>
    <div class="b-accent"></div>

    <div class="b-grid">
        <table>
            <tr>
                <td>
                    <div class="b-lbl">Full Name</div>
                    <div class="b-val">{{ \Illuminate\Support\Str::limit(trim($student->firstname . ' ' . $student->lastname), 27, '...') }}</div>
                </td>
                <td>
                    <div class="b-lbl">Nationality</div>
                    <div class="b-val">{{ $student->nationality ?: '—' }}</div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="b-lbl">Date of Birth</div>
                    <div class="b-val">{{ $student->date_of_birth ? \Carbon\Carbon::parse($student->date_of_birth)->format('d M Y') : '—' }}</div>
                </td>
                <td>
                    <div class="b-lbl">Place of Birth</div>
                    <div class="b-val">{{ \Illuminate\Support\Str::limit($student->place_of_birth ?: '—', 24, '...') }}</div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="b-lbl">Guardian</div>
                    <div class="b-val">{{ \Illuminate\Support\Str::limit($student->guardian_names ?: '—', 27, '...') }}</div>
                </td>
                <td>
                    <div class="b-lbl">Contact</div>
                    <div class="b-val">{{ $student->primary_contact ?: '—' }}</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="b-divider"></div>

    <div class="b-lost">
        If found, please return to<br>
        <b>{{ \Illuminate\Support\Str::limit($schoolNmB, 46, '...') }}</b>@if(!empty($school->phone))<br>Tel: {{ $school->phone }}@endif
    </div>
    @if($statusB === 'active')
        <div class="b-chip chip-active">VALID</div>
    @else
        <div class="b-chip chip-inactive">{{ strtoupper($statusB) === 'REVOKED' ? 'INVALID' : strtoupper($statusB) }}</div>
    @endif

    <img class="f-strip" src="{{ $stripImgB }}" alt="">
</div>
