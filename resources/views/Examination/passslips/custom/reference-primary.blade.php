{{--
  @report-card
  name: Reference Executive (Primary)
  level: primary
  description: Reference design - letterhead, learner panel, marks table, summary, remarks, signatures. Copy this file as the starting point for a school's own design.
  accent: #1e3a8a
  toggles: show_logo, show_photo, show_qr, show_remarks, show_signatures
--}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $exam->exam_name }} — Report Cards</title>
    @include('Examination.passslips.custom._base-css')
    <style>
        .rc{padding:12mm 12mm 10mm;font:12px/1.4 "Segoe UI",Arial,sans-serif;color:#0f172a}
        .rc-head{display:flex;align-items:center;gap:14px;border-bottom:3px solid var(--ac);padding-bottom:10px}
        .rc-head img.logo{width:74px;height:74px;object-fit:contain}
        .rc-head .t{flex:1;text-align:center}
        .rc-head h1{margin:0;font-size:22px;letter-spacing:.5px;color:var(--ac);text-transform:uppercase}
        .rc-head .sub{font-size:11px;color:#475569}
        .rc-title{margin:10px 0;text-align:center;font-weight:700;letter-spacing:1px;background:var(--ac);color:#fff;padding:6px;border-radius:4px}
        .rc-learner{display:grid;grid-template-columns:1fr 1fr auto;gap:6px 18px;border:1px solid #cbd5e1;border-radius:6px;padding:10px}
        .rc-learner .f span{display:block;font-size:10px;text-transform:uppercase;color:#64748b}
        .rc-learner .f b{font-size:13px}
        .rc-photo{grid-row:1 / span 3;grid-column:3;width:84px;height:100px;object-fit:cover;border:1px solid #cbd5e1;border-radius:4px}
        table.m{width:100%;border-collapse:collapse;margin-top:12px}
        table.m th{background:var(--ac);color:#fff;font-size:11px;text-transform:uppercase;padding:6px;text-align:center}
        table.m td{border:1px solid #cbd5e1;padding:5px 7px;text-align:center}
        table.m td.l,table.m th.l{text-align:left}
        table.m tr:nth-child(even) td{background:#f8fafc}
        .rc-sum{display:grid;grid-template-columns:repeat(4,1fr);gap:8px;margin-top:12px}
        .rc-sum div{border:1px solid #cbd5e1;border-radius:6px;padding:8px;text-align:center}
        .rc-sum span{display:block;font-size:10px;text-transform:uppercase;color:#64748b}
        .rc-sum b{font-size:17px;color:var(--ac)}
        .rc-rem{margin-top:12px;border:1px solid #cbd5e1;border-radius:6px;padding:8px 10px;min-height:42px}
        .rc-rem span{font-size:10px;text-transform:uppercase;color:#64748b;display:block}
        .rc-sign{display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-top:14px}
        .rc-sign .s{border-top:1px solid #0f172a;padding-top:4px;text-align:center;font-size:11px;min-height:54px}
        .rc-sign img{max-height:36px;display:block;margin:0 auto 2px}
        .rc-foot{display:flex;justify-content:space-between;align-items:flex-end;margin-top:12px;font-size:10px;color:#64748b}
        .scale{font-size:10px;margin-top:10px;color:#475569}
    </style>
</head>
<body>
@unless($embed) @include('Examination.passslips.custom._toolbar') @endunless

@foreach($reports as $r)
<div class="crc-sheet">
 <div class="rc" style="--ac: {{ $r->accent }}">
    <div class="rc-head">
        @if($r->on('show_logo') && $r->school->logo_url)<img class="logo" src="{{ $r->school->logo_url }}" alt="">@endif
        <div class="t">
            <h1>{{ $r->school->name }}</h1>
            <div class="sub">
                {{ collect([$r->school->location, $r->school->phone, $r->school->email, $r->school->website])->filter()->implode('  •  ') }}
            </div>
            @if($r->school->motto)<div class="sub"><em>“{{ $r->school->motto }}”</em></div>@endif
        </div>
    </div>

    <div class="rc-title">{{ $r->exam->title }} — LEARNER'S REPORT</div>

    <div class="rc-learner">
        <div class="f"><span>Name</span><b>{{ $r->student->full_name }}</b></div>
        <div class="f"><span>Admission No.</span><b>{{ $r->student->admission_no }}</b></div>
        @if($r->on('show_photo'))
            @if($r->student->photo_url)<img class="rc-photo" src="{{ $r->student->photo_url }}" alt="">@else<div class="rc-photo"></div>@endif
        @endif
        <div class="f"><span>Class</span><b>{{ $r->student->class_name }} {{ $r->student->stream }}</b></div>
        <div class="f"><span>Gender</span><b>{{ $r->student->gender }}</b></div>
        <div class="f"><span>Term / Year</span><b>{{ $r->exam->term_label }} {{ $r->exam->academic_year }}</b></div>
        <div class="f"><span>Position</span><b>{{ $r->summary->rank_label }}</b></div>
    </div>

    <table class="m">
        <thead><tr>
            <th>#</th><th class="l">Subject</th>
            @if($r->is_comment_scale)<th>Rating</th>@else<th>Marks</th><th>Out of</th><th>%</th><th>Grade</th>@endif
            <th class="l">Remark</th><th>Teacher</th>
        </tr></thead>
        <tbody>
        @foreach($r->subjects as $s)
            <tr>
                <td>{{ $s->no }}</td><td class="l">{{ $s->name }}</td>
                @if($r->is_comment_scale)
                    <td>{{ $s->grade }}</td>
                @else
                    <td>{{ $s->marks_display }}</td><td>{{ $s->total }}</td><td>{{ $s->pct_display }}</td><td><b>{{ $s->grade }}</b></td>
                @endif
                <td class="l">{{ $s->remark }}</td><td>{{ $s->initials ?? $s->teacher }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <div class="rc-sum">
        <div><span>Total</span><b>{{ $r->summary->total_obtained_display }} / {{ $r->summary->total_max_display }}</b></div>
        <div><span>Average</span><b>{{ $r->summary->percentage_display }}</b></div>
        <div><span>Overall grade</span><b>{{ $r->summary->grade }}</b></div>
        <div><span>Result</span><b>{{ $r->summary->result }}</b></div>
    </div>

    @if(!$r->is_comment_scale && count($r->grade_scale))
        <div class="scale"><b>Grading:</b>
            {{ collect($r->grade_scale)->map(fn($b) => $b->grade . ' (' . $b->min . '–' . $b->max . ')')->implode('  ·  ') }}
        </div>
    @endif

    @if($r->on('show_remarks'))
        <div class="rc-rem"><span>Class teacher's remark</span>{{ $r->remarks->class_teacher->remark ?: '—' }}</div>
        <div class="rc-rem"><span>Head teacher's remark</span>{{ $r->remarks->head_teacher->remark ?: '—' }}</div>
    @endif

    @if($r->on('show_signatures'))
        <div class="rc-sign">
            <div class="s">@if($r->remarks->class_teacher->signature_url)<img src="{{ $r->remarks->class_teacher->signature_url }}" alt="">@endif Class Teacher {{ $r->remarks->class_teacher->name ? '— ' . $r->remarks->class_teacher->name : '' }}</div>
            <div class="s">@if($r->remarks->head_teacher->signature_url)<img src="{{ $r->remarks->head_teacher->signature_url }}" alt="">@endif Head Teacher {{ $r->remarks->head_teacher->name ? '— ' . $r->remarks->head_teacher->name : '' }}</div>
        </div>
    @endif

    <div class="rc-foot">
        <div>
            @if($r->term_dates->ends_on)Term ends: <b>{{ $r->term_dates->ends_on }}</b>@endif
            @if($r->term_dates->next_starts_on) &nbsp;•&nbsp; Next term begins: <b>{{ $r->term_dates->next_starts_on }}</b>@endif
            <br>Generated {{ $r->generated_at }}
        </div>
        @if($r->on('show_qr'))<canvas data-qr="{{ $r->qr_text }}" data-size="72"></canvas>@endif
    </div>
 </div>
</div>
@endforeach

@include('Examination.passslips.custom._runtime')
</body>
</html>
