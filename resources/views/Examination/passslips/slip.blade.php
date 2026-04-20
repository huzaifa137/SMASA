{{-- resources/views/Examination/passslips/slip.blade.php --}}
{{-- Handles: mode = 'single' | 'class' | 'all' --}}
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pass Slip — {{ $exam->exam_name }}</title>
<link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700;900&family=Merriweather:wght@400;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
:root {
    --brand:      #2C29CA;
    --brand-mid:  #5351e4;
    --pass-green: #1a7a4a;
    --fail-red:   #c0392b;
    --warn:       #856404;
    --thead:      #1e1b4b;
    --alt:        #f7f8ff;
    --grp:        #eceaff;
    --border:     #c8c6e8;
    --text:       #1a1a2e;
    --muted:      #555;
}
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:'Lato',sans-serif; background:#e0dff5; color:var(--text); font-size:13px; }

/* ─ Screen toolbar ─ */
.toolbar {
    background: linear-gradient(135deg,#2C29CA,#5351e4);
    padding:.8rem 2rem; display:flex; align-items:center;
    justify-content:space-between; flex-wrap:wrap; gap:.5rem;
    position:sticky; top:0; z-index:100;
    box-shadow:0 4px 18px rgba(44,41,202,.35);
}
.toolbar-info strong { color:#fff; font-size:.9rem; }
.toolbar-info small  { display:block; color:rgba(255,255,255,.65); font-size:.7rem; margin-top:1px; }
.tbtn {
    padding:.45rem 1rem; border-radius:.5rem; border:none; cursor:pointer;
    font-family:inherit; font-weight:700; font-size:.78rem;
    display:inline-flex; align-items:center; gap:.35rem;
    transition:all .15s; text-decoration:none;
}
.tbtn-print { background:#fff; color:var(--brand); }
.tbtn-print:hover { background:#ede9ff; transform:translateY(-1px); }
.tbtn-back  { background:rgba(255,255,255,.15); color:#fff; border:1px solid rgba(255,255,255,.3); }
.tbtn-back:hover { background:rgba(255,255,255,.25); }

/* ─ Page wrapper ─ */
.page-wrap { max-width:820px; margin:1.5rem auto; }

/* ─ Single slip ─ */
.slip {
    background:#fff; border:1px solid #bbb;
    margin-bottom:2rem; page-break-after:always;
}
.slip:last-child { page-break-after:avoid; margin-bottom:0; }

/* ─ School header ─ */
.sch-head {
    text-align:center; padding:.8rem 1.4rem .55rem;
    border-bottom:2.5px solid var(--thead);
}
.sch-logo-wrap {
    width:62px; height:62px; border-radius:50%;
    background:linear-gradient(135deg,var(--brand),var(--brand-mid));
    display:flex; align-items:center; justify-content:center;
    margin:.2rem auto .35rem; border:2px solid var(--border);
}
.sch-logo-wrap i { color:#fff; font-size:1.5rem; }
.sch-logo-wrap img { width:100%; height:100%; border-radius:50%; object-fit:cover; }
.sch-name { font-size:.95rem; font-weight:900; letter-spacing:.07em; color:var(--thead); text-transform:uppercase; }
.sch-sub  { font-size:.72rem; color:#666; margin-top:.1rem; }

/* ─ Title band ─ */
.title-band {
    display:flex; align-items:center; justify-content:space-between;
    padding:.42rem 1.2rem; background:var(--thead); color:#fff;
}
.title-band .en { font-size:1.2rem; font-weight:900; letter-spacing:.08em; font-family:'Merriweather',serif; }
.title-band .ar { font-size:.95rem; font-weight:700; letter-spacing:.04em; font-family:'Merriweather',serif; direction:rtl; }

/* ─ Student strip ─ */
.stu-strip {
    display:flex; align-items:flex-start;
    padding:.6rem 1.2rem; gap:.9rem;
    border-bottom:1.5px solid var(--border);
}

/* Left info grid */
.stu-info {
    flex:1; display:grid;
    grid-template-columns:1fr 1fr;
    gap:.15rem .5rem;
    align-content:start;
}
.irow { display:flex; align-items:baseline; gap:.3rem; font-size:.78rem; }
.ilbl {
    font-weight:700; min-width:68px; flex-shrink:0;
    font-size:.72rem; text-transform:uppercase; letter-spacing:.03em; color:var(--text);
}
.ilbl::after { content:':'; }
.ival { font-size:.8rem; color:#333; }

/* Right Arabic bilingual */
.stu-ar {
    flex-shrink:0; min-width:150px; direction:rtl; text-align:right;
    font-size:.76rem; display:flex; flex-direction:column; gap:.15rem; align-self:center;
}
.stu-ar span { color:#444; }
.stu-ar strong { color:var(--text); }

/* Rank badge */
.rank-badge {
    display:inline-block;
    background:linear-gradient(135deg,#f59e0b,#d97706);
    color:#fff; border-radius:4px;
    padding:.28rem .65rem; font-weight:900; font-size:.9rem; line-height:1.1;
    box-shadow:0 3px 10px rgba(245,158,11,.3);
}
.rank-badge small { display:block; font-size:.56rem; font-weight:600; opacity:.88; margin-top:.1rem; }

/* Photo box */
.stu-photo {
    flex-shrink:0; width:82px; height:97px;
    border:1.5px solid var(--border); border-radius:3px;
    overflow:hidden; background:#f0efff;
    display:flex; align-items:center; justify-content:center;
}
.stu-photo img { width:100%; height:100%; object-fit:cover; }
.stu-photo .nophoto {
    display:flex; flex-direction:column; align-items:center; justify-content:center;
    height:100%; width:100%; gap:.15rem;
}
.stu-photo .nophoto i { font-size:2.1rem; color:#c5c3e8; }
.stu-photo .nophoto span { font-size:.55rem; color:#bbb; }

/* ─ Marks table ─ */
.marks-wrap { padding:.45rem 1.2rem; }

.marks-tbl {
    width:100%; border-collapse:collapse;
    font-size:.76rem; border:1.5px solid var(--border);
}
.marks-tbl th {
    background:var(--thead); color:#fff;
    padding:.35rem .55rem; text-align:center;
    font-size:.69rem; text-transform:uppercase; letter-spacing:.05em;
    border:1px solid rgba(255,255,255,.18);
}
.marks-tbl th.tl { text-align:left; }
.marks-tbl td {
    padding:.3rem .55rem;
    border:1px solid var(--border);
    vertical-align:middle;
}
.marks-tbl tbody tr:nth-child(even) { background:var(--alt); }

.grp-row td {
    background:var(--grp); font-weight:700; font-size:.7rem;
    color:var(--brand); text-transform:uppercase; letter-spacing:.06em;
    padding:.25rem .55rem; border-top:1.5px solid var(--border);
}
.code-td { text-align:center; color:#999; font-size:.67rem; }
.num-td  { text-align:center; }
.score-td{ text-align:center; font-weight:700; }
.pct-td  { text-align:center; font-size:.7rem; color:#555; }
.ar-td   { direction:rtl; color:#555; }
.ar-code { direction:rtl; color:#aaa; font-size:.68rem; }

.g-pill {
    display:inline-block; min-width:26px; text-align:center;
    padding:.1rem .38rem; border-radius:3px; font-weight:800; font-size:.7rem;
}
.g-D { background:#d4f5e2; color:#1a7a4a; }
.g-C { background:#cfe2ff; color:#0a4191; }
.g-P { background:#fff3cd; color:#856404; }
.g-F { background:#fde8e8; color:#c0392b; }
.g-x { background:#eceaff; color:var(--brand); }

.delta-up   { color:var(--pass-green); font-weight:700; font-size:.68rem; }
.delta-down { color:var(--fail-red);   font-weight:700; font-size:.68rem; }
.delta-eq   { color:#bbb; font-size:.68rem; }

.totals-row td {
    background:#eceaff; font-weight:700; font-size:.78rem;
    border-top:2px solid var(--border);
}

/* ─ Summary band ─ */
.sum-band {
    display:flex; border-top:2px solid var(--border); border-bottom:2px solid var(--border);
    margin:.45rem 1.2rem;
}
.sum-cell {
    flex:1; text-align:center; padding:.5rem .3rem;
    border-right:1px solid var(--border);
}
.sum-cell:last-child { border-right:none; }
.sum-lbl { font-size:.59rem; text-transform:uppercase; letter-spacing:.07em; color:#888; font-weight:700; margin-bottom:.18rem; }
.sum-val { font-size:1.1rem; font-weight:900; color:var(--brand); line-height:1; }
.sum-val.pass { color:var(--pass-green); }
.sum-val.fail { color:var(--fail-red); }
.sum-val.warn { color:var(--warn); }
.sum-ar  { font-size:.58rem; color:#aaa; margin-top:.1rem; }

/* ─ Growth section ─ */
.growth { padding:.35rem 1.2rem .5rem; }
.growth-ttl {
    font-size:.64rem; text-transform:uppercase; letter-spacing:.08em;
    color:#888; font-weight:700; margin-bottom:.35rem;
    display:flex; align-items:center; gap:.3rem;
}
.growth-chart {
    display:flex; align-items:flex-end; gap:.4rem;
    height:55px; border-bottom:1.5px solid var(--border); padding-bottom:2px;
}
.g-col { display:flex; flex-direction:column; align-items:center; flex:1; }
.g-bar {
    border-radius:3px 3px 0 0; width:65%; min-height:4px;
    background:linear-gradient(180deg,var(--brand-mid),var(--brand));
    position:relative;
}
.g-bar.cur { background:linear-gradient(180deg,#f59e0b,#d97706); }
.g-pct-lbl {
    font-size:.56rem; font-weight:700; color:var(--brand);
    position:absolute; top:-13px; left:50%; transform:translateX(-50%);
    white-space:nowrap;
}
.g-pct-lbl.cur { color:#d97706; }
.g-lbl { font-size:.55rem; color:#999; text-align:center; margin-top:.2rem; line-height:1.2; }
.g-arr { font-size:.72rem; margin-bottom:16px; flex-shrink:0; }
.arr-up   { color:var(--pass-green); font-weight:900; }
.arr-down { color:var(--fail-red); font-weight:900; }
.arr-eq   { color:#ccc; }

/* ─ Grade legend ─ */
.legend {
    display:flex; flex-wrap:wrap; gap:.25rem .75rem;
    padding:.35rem 1.2rem; border-top:1px dashed var(--border);
    font-size:.63rem; color:#666;
}
.leg-item { display:flex; align-items:center; gap:.28rem; }

/* ─ Sig strip ─ */
.sig-strip {
    display:flex; border-top:1.5px solid var(--border);
    padding:.65rem 1.2rem .9rem;
}
.sig-col { flex:1; text-align:center; }
.sig-line {
    border-top:1px solid #999;
    margin:1.6rem .4rem .2rem;
    padding-top:.22rem;
    font-size:.6rem; color:#666;
    text-transform:uppercase; letter-spacing:.05em;
}

/* ─ Footer ─ */
.slip-footer {
    background:#f7f8ff; padding:.32rem 1.2rem;
    display:flex; align-items:center; justify-content:space-between;
    border-top:1px solid var(--border);
}
.slip-footer small { font-size:.58rem; color:#bbb; }
.slip-footer .conf { font-size:.58rem; font-weight:700; color:var(--brand); }

/* ═══ PRINT ═════════════════════════════════════════════════════ */
@media print {
    @page { margin:.55cm .75cm; size:A4; }
    body { background:#fff; font-size:11px; }
    .toolbar { display:none !important; }
    .page-wrap { max-width:100%; margin:0; }
    .slip { margin:0; box-shadow:none; border:none; page-break-after:always; }
    .slip:last-child { page-break-after:avoid; }
    .title-band, .sch-logo-wrap, .grp-row td, .totals-row td,
    .g-D,.g-C,.g-P,.g-F,.g-x, .g-pill, .rank-badge, .g-bar,
    .marks-tbl thead, .sum-cell, .sum-band {
        -webkit-print-color-adjust:exact;
        print-color-adjust:exact;
        color-adjust:exact;
    }
}
</style>
</head>
<body>

{{-- ── Toolbar ─────────────────────────────────────────────── --}}
<div class="toolbar">
    <div class="toolbar-info">
        <strong>
            <i class="fas fa-id-card" style="margin-right:.35rem;"></i>
            @if($mode==='single') Pass Slip
            @elseif($mode==='class') Class Pass Slips
            @else All Pass Slips @endif
             — {{ $exam->exam_name }}
        </strong>
        <small>
            @if($mode==='single')
                {{ $student->lastname }} {{ $student->firstname }}
            @elseif($mode==='class')
                {{ \App\Http\Controllers\Helper::recordMdname($classId) }}
                {{ isset($streamId)&&$streamId ? '– '.$streamId : '' }}
            @else All Classes @endif
            &bull; {{ $exam->term }} {{ $exam->academic_year }}
        </small>
    </div>
    <div style="display:flex;gap:.45rem;flex-wrap:wrap;">
        <button class="tbtn tbtn-print" onclick="window.print()">
            <i class="fas fa-print"></i> Print / Save PDF
        </button>
        <a href="{{ route('examination.passslips.index', $exam->id) }}" class="tbtn tbtn-back">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>
</div>

@php
    /* ── Normalise to single render array ── */
    if ($mode === 'single') {
        $renderSlips = [[
            'student'              => $student,
            'subjectMarks'         => $subjectMarks,
            'totalObtained'        => $totalObtained,
            'totalMax'             => $totalMax,
            'percentage'           => $percentage,
            'overallGrade'         => $overallGrade,
            'overallRemark'        => $overallRemark,
            'classRank'            => $classRank,
            'classTotal'           => $classTotal,
            'growthData'           => $growthData,
            'previousSubjectMarks' => $previousSubjectMarks,
        ]];
    } else {
$renderSlips = $slips;
    }

    // Ordinal suffix
    $ord = function($n) {
        if (!is_numeric($n)) return $n;
        $s = ['th','st','nd','rd'];
        $v = $n % 100;
        return $n.($s[($v-20)%10] ?? $s[$v] ?? $s[0]);
    };

    // Grade CSS class
    $gc = function($grade) {
        if (!$grade || $grade==='—') return 'g-x';
        return match(strtoupper(substr($grade,0,1))) {
            'D'=>'g-D','C'=>'g-C','P'=>'g-P','F'=>'g-F',default=>'g-x'
        };
    };

    $schoolName = Session('LoggedSchoolName') ?? config('app.name','School');
@endphp

<div class="page-wrap">
@foreach($renderSlips as $slip)
@php
    $s            = (object)$slip['student'];
    $subjMarks    = collect($slip['subjectMarks']);
    $totObt       = $slip['totalObtained'];
    $totMax       = $slip['totalMax'];
    $pct          = $slip['percentage'];
    $oGrade       = $slip['overallGrade'];
    $oRemark      = $slip['overallRemark'];
    $rank         = $slip['classRank'];
    $classTotal   = $slip['classTotal'];
    $growth       = $slip['growthData'];
    $prevSubj     = collect($slip['previousSubjectMarks']);

    $passed   = $pct >= $exam->pass_mark;
    $initials = strtoupper(substr($s->lastname,0,1).substr($s->firstname,0,1));

    // Resolve student photo
    $photo = null;
    foreach (['photo','profile_photo','image','avatar'] as $field) {
        if (!empty($s->$field)) {
            foreach (['uploads/students/','storage/students/','images/students/'] as $dir) {
                if (file_exists(public_path($dir.$s->$field))) {
                    $photo = asset($dir.$s->$field); break 2;
                }
            }
        }
    }

    // Group by subject_type if the field exists & has variety
    $grouped = $subjMarks->groupBy(fn($sm) => $sm->subject_type ?? '');
    $useGroups = $grouped->count() > 1 || ($grouped->count()===1 && !$grouped->has(''));
@endphp

<div class="slip">

    {{-- SCHOOL HEADER --}}
    <div class="sch-head">
        <div class="sch-logo-wrap">
            @if(file_exists(public_path('images/school_logo.png')))
                <img src="{{ asset('images/school_logo.png') }}" alt="logo">
            @else
                <i class="fas fa-school"></i>
            @endif
        </div>
        <div class="sch-name">{{ $schoolName }}</div>
        <div class="sch-sub">{{ $exam->exam_name }} — Official Examination Result Slip</div>
    </div>

    {{-- TITLE BAND --}}
    <div class="title-band">
        <span class="en">PASS SLIP</span>
        <span class="ar">كشف الدرجات</span>
    </div>

    {{-- STUDENT INFO --}}
    <div class="stu-strip">

        {{-- Info grid (left) --}}
        <div class="stu-info">
            <div class="irow" style="grid-column:1/-1;">
                <span class="ilbl">Name</span>
                <span class="ival" style="font-weight:800;font-size:.88rem;">
                    {{ $s->lastname }} {{ $s->firstname }} {{ $s->other_names ?? '' }}
                </span>
            </div>
            <div class="irow">
                <span class="ilbl">Level</span>
                <span class="ival">{{ \App\Http\Controllers\Helper::recordMdname($s->senior) }}</span>
            </div>
            <div class="irow">
                <span class="ilbl">Year</span>
                <span class="ival">{{ $exam->academic_year }}</span>
            </div>
            <div class="irow">
                <span class="ilbl">Index No</span>
                <span class="ival">{{ $s->adm_no ?? ($s->index_no ?? '—') }}</span>
            </div>
            <div class="irow">
                <span class="ilbl">Term</span>
                <span class="ival">{{ $exam->term }}</span>
            </div>
            <div class="irow">
                <span class="ilbl">Gender</span>
                <span class="ival">{{ ucfirst($s->gender ?? '—') }}</span>
            </div>
            @if($s->stream ?? false)
            <div class="irow">
                <span class="ilbl">Stream</span>
                <span class="ival">{{ $s->stream }}</span>
            </div>
            @endif
            <div class="irow" style="grid-column:1/-1;">
                <span class="ilbl">School</span>
                <span class="ival">{{ $schoolName }}</span>
            </div>
            <div class="irow" style="grid-column:1/-1; margin-top:.25rem;">
                <span class="ilbl">Position</span>
                <span class="ival">
                    @if(is_numeric($rank))
                        <span class="rank-badge">
                            {{ $ord($rank) }}
                            <small>of {{ $classTotal }} students</small>
                        </span>
                    @else —
                    @endif
                </span>
            </div>
        </div>

        {{-- Arabic mirror (right) --}}
        <div class="stu-ar">
            <span><strong>اسم الطالب:</strong> {{ $s->lastname }} {{ $s->firstname }}</span>
            <span><strong>المرحلة:</strong> {{ \App\Http\Controllers\Helper::recordMdname($s->senior) }}</span>
            <span><strong>العام:</strong> {{ $exam->academic_year }}</span>
            <span><strong>الفصل:</strong> {{ $exam->term }}</span>
            <span><strong>اسم المدرسة:</strong> {{ $schoolName }}</span>
        </div>

        {{-- Student photo --}}
        <div class="stu-photo">
            @if($photo)
                <img src="{{ $photo }}" alt="Photo">
            @else
                <div class="nophoto">
                    <i class="fas fa-user-circle"></i>
                    <span>Photo</span>
                </div>
            @endif
        </div>

    </div>{{-- /stu-strip --}}

    {{-- MARKS TABLE --}}
    <div class="marks-wrap">
        <table class="marks-tbl">
            <thead>
                <tr>
                    <th style="width:28px;">#</th>
                    <th class="tl" style="width:52px;">Code</th>
                    <th class="tl">Paper / Subject</th>
                    <th style="width:48px;">Score</th>
                    <th style="width:36px;">Max</th>
                    <th style="width:36px;">%</th>
                    <th style="width:38px;">Grade</th>
                    <th class="tl" style="min-width:65px;">Remark</th>
                    @if($prevSubj->count())
                        <th style="width:36px;">Prev</th>
                        <th style="width:28px;">Δ</th>
                    @endif
                    <th style="direction:rtl;width:90px;">اسم المعرفة</th>
                    <th style="direction:rtl;width:48px;">رمز</th>
                </tr>
            </thead>
            <tbody>
                @php $rn = 0; @endphp

                @if($useGroups)
                    @foreach($grouped as $grpName => $grpSubjs)
                        @if($grpName)
                        <tr class="grp-row">
                            <td colspan="{{ $prevSubj->count() ? 12 : 10 }}">{{ strtoupper($grpName) }}</td>
                        </tr>
                        @endif
                        @foreach($grpSubjs as $sm)
                            @php
                                $rn++;
                                $prevM = $prevSubj[$sm->subject_id] ?? null;
                                $delta = null;
                                if ($prevM && ($prevM->total_marks ?? 0) > 0) {
                                    $pPct  = round(($prevM->marks_obtained / $prevM->total_marks) * 100, 1);
                                    $delta = round($sm->percentage - $pPct, 1);
                                }
                                $subCode = $sm->subject_code ?? ('S-'.str_pad($sm->subject_id,3,'0',STR_PAD_LEFT));
                            @endphp
                            <tr>
                                <td class="num-td" style="color:#bbb;font-size:.68rem;">{{ $rn }}</td>
                                <td class="code-td">{{ $subCode }}</td>
                                <td>{{ $sm->subject_name }}</td>
                                <td class="score-td">{{ $sm->marks_obtained ?? '—' }}</td>
                                <td class="num-td" style="color:#888;">{{ $sm->total_marks }}</td>
                                <td class="pct-td">{{ $sm->percentage }}%</td>
                                <td class="num-td">
                                    <span class="g-pill {{ $gc($sm->grade) }}">{{ $sm->grade ?? '—' }}</span>
                                </td>
                                <td>{{ $sm->grade_remark ?? '—' }}</td>
                                @if($prevSubj->count())
                                    <td class="num-td" style="color:#999;">{{ $prevM?->marks_obtained ?? '—' }}</td>
                                    <td class="num-td">
                                        @if($delta !== null)
                                            @if($delta > 0)      <span class="delta-up">▲+{{ $delta }}</span>
                                            @elseif($delta < 0)  <span class="delta-down">▼{{ $delta }}</span>
                                            @else                <span class="delta-eq">—</span>
                                            @endif
                                        @else <span class="delta-eq">—</span> @endif
                                    </td>
                                @endif
                                <td class="ar-td">{{ $sm->subject_name_ar ?? $sm->subject_name }}</td>
                                <td class="ar-code">{{ $subCode }}</td>
                            </tr>
                        @endforeach
                    @endforeach
                @else
                    @foreach($subjMarks as $sm)
                        @php
                            $rn++;
                            $prevM = $prevSubj[$sm->subject_id] ?? null;
                            $delta = null;
                            if ($prevM && ($prevM->total_marks ?? 0) > 0) {
                                $pPct  = round(($prevM->marks_obtained / $prevM->total_marks) * 100, 1);
                                $delta = round($sm->percentage - $pPct, 1);
                            }
                            $subCode = $sm->subject_code ?? ('S-'.str_pad($sm->subject_id,3,'0',STR_PAD_LEFT));
                        @endphp
                        <tr>
                            <td class="num-td" style="color:#bbb;font-size:.68rem;">{{ $rn }}</td>
                            <td class="code-td">{{ $subCode }}</td>
                            <td>{{ $sm->subject_name }}</td>
                            <td class="score-td">{{ $sm->marks_obtained ?? '—' }}</td>
                            <td class="num-td" style="color:#888;">{{ $sm->total_marks }}</td>
                            <td class="pct-td">{{ $sm->percentage }}%</td>
                            <td class="num-td">
                                <span class="g-pill {{ $gc($sm->grade) }}">{{ $sm->grade ?? '—' }}</span>
                            </td>
                            <td>{{ $sm->grade_remark ?? '—' }}</td>
                            @if($prevSubj->count())
                                <td class="num-td" style="color:#999;">{{ $prevM?->marks_obtained ?? '—' }}</td>
                                <td class="num-td">
                                    @if($delta !== null)
                                        @if($delta > 0)      <span class="delta-up">▲+{{ $delta }}</span>
                                        @elseif($delta < 0)  <span class="delta-down">▼{{ $delta }}</span>
                                        @else                <span class="delta-eq">—</span>
                                        @endif
                                    @else <span class="delta-eq">—</span> @endif
                                </td>
                            @endif
                            <td class="ar-td">{{ $sm->subject_name_ar ?? $sm->subject_name }}</td>
                            <td class="ar-code">{{ $subCode }}</td>
                        </tr>
                    @endforeach
                @endif

                {{-- TOTALS ROW --}}
                <tr class="totals-row">
                    <td colspan="3" style="text-align:right; color:#666; font-size:.73rem; padding-right:.8rem;">
                        TOTAL MARK: {{ $totMax }}
                    </td>
                    <td class="score-td">{{ number_format($totObt,1) }}</td>
                    <td class="num-td">{{ $totMax }}</td>
                    <td class="pct-td" style="font-weight:800;">{{ $pct }}%</td>
                    <td class="num-td">
                        <span class="g-pill {{ $gc($oGrade) }}">{{ $oGrade }}</span>
                    </td>
                    <td>
                        <strong style="color:{{ $passed ? 'var(--pass-green)' : 'var(--fail-red)' }}">
                            {{ strtoupper($oRemark) }}
                        </strong>
                    </td>
                    @if($prevSubj->count())
                        <td colspan="2"></td>
                    @endif
                    <td class="ar-td" style="font-size:.7rem; color:#777; direction:rtl;">
                        المجموع: {{ number_format($totObt,1) }}
                    </td>
                    <td></td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- SUMMARY BAND --}}
    <div class="sum-band">
        <div class="sum-cell">
            <div class="sum-lbl">Total Score</div>
            <div class="sum-val">{{ number_format($totObt,1) }}<span style="font-size:.6rem;color:#ccc;">/{{ $totMax }}</span></div>
            <div class="sum-ar">المجموع الكلي</div>
        </div>
        <div class="sum-cell">
            <div class="sum-lbl">Average Score</div>
            <div class="sum-val {{ $pct>=80 ? 'pass' : ($pct>=$exam->pass_mark ? 'warn' : 'fail') }}">{{ $pct }}%</div>
            <div class="sum-ar">النسبة المئوية: {{ $pct }}</div>
        </div>
        <div class="sum-cell">
            <div class="sum-lbl">Grade</div>
            <div class="sum-val" style="font-size:1.35rem;">{{ $oGrade }}</div>
            <div class="sum-ar">التقدير</div>
        </div>
        <div class="sum-cell">
            <div class="sum-lbl">Result</div>
            <div class="sum-val {{ $passed ? 'pass' : 'fail' }}" style="font-size:.9rem;margin-top:.1rem;">
                {{ $passed ? 'PASS' : 'FAIL' }}
            </div>
            <div class="sum-ar">{{ $passed ? 'ناجح' : 'راسب' }}</div>
        </div>
        <div class="sum-cell">
            <div class="sum-lbl">Position</div>
            @if(is_numeric($rank))
                <div style="margin-top:.12rem;">
                    <span class="rank-badge">
                        {{ $ord($rank) }}
                        <small>of {{ $classTotal }}</small>
                    </span>
                </div>
            @else
                <div class="sum-val">—</div>
            @endif
            <div class="sum-ar">الترتيب</div>
        </div>
    </div>

    {{-- GROWTH CHART --}}
    @if(count($growth) > 1)
    <div class="growth">
        @php
            $gFirst = $growth[0]['percentage'];
            $gLast  = $growth[count($growth)-1]['percentage'];
            $gDiff  = round($gLast - $gFirst, 1);
        @endphp
        <div class="growth-ttl">
            <i class="fas fa-chart-line" style="color:var(--brand);"></i>
            Performance Trend
            @if($gDiff > 0)   &nbsp;<span class="delta-up">▲ +{{ $gDiff }}% since {{ $growth[0]['label'] }}</span>
            @elseif($gDiff<0) &nbsp;<span class="delta-down">▼ {{ $gDiff }}% since {{ $growth[0]['label'] }}</span>
            @else             &nbsp;<span class="delta-eq">No change</span>
            @endif
        </div>
        <div class="growth-chart">
            @foreach($growth as $gi => $gd)
                @php $isLast = $gi === array_key_last($growth); @endphp
                @if($gi > 0)
                    @php
                        $prev = $growth[$gi-1]['percentage'];
                        $dir  = $gd['percentage'] > $prev ? 'up' : ($gd['percentage'] < $prev ? 'down' : 'eq');
                    @endphp
                    <div class="g-arr">
                        <span class="arr-{{ $dir }}">{{ $dir==='up'?'▲':($dir==='down'?'▼':'—') }}</span>
                    </div>
                @endif
                <div class="g-col">
                    <div style="position:relative;width:100%;flex:1;display:flex;align-items:flex-end;height:100%;">
                        <div class="g-bar {{ $isLast?'cur':'' }}"
                             style="height:{{ max(4,$gd['percentage']) }}%; width:65%; margin:0 auto;">
                            <span class="g-pct-lbl {{ $isLast?'cur':'' }}">{{ $gd['percentage'] }}%</span>
                        </div>
                    </div>
                    <div class="g-lbl">{{ $gd['label'] }}</div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- GRADE LEGEND --}}
    <div class="legend">
        @foreach(\Illuminate\Support\Facades\DB::table('grading_scales')
            ->where(function($q){ $q->where('school_id',Session('LoggedSchool'))->orWhereNull('school_id'); })
            ->orderByDesc('school_id')->orderByDesc('min_mark')->get() as $gs)
            <span class="leg-item">
                <span class="g-pill {{ $gc($gs->grade) }}">{{ $gs->grade }}</span>
                {{ $gs->min_mark }}–{{ $gs->max_mark }}: <strong>{{ $gs->remark }}</strong>
            </span>
        @endforeach
    </div>

    {{-- SIGNATURE STRIP --}}
    <div class="sig-strip">
        <div class="sig-col"><div class="sig-line">Class Teacher</div></div>
        <div class="sig-col"><div class="sig-line">Deputy Head Teacher</div></div>
        <div class="sig-col"><div class="sig-line">Head Teacher / Principal</div></div>
        <div class="sig-col"><div class="sig-line">Parent / Guardian</div></div>
    </div>

    {{-- FOOTER --}}
    <div class="slip-footer">
        <small>Generated: {{ now()->format('d M Y, H:i') }}</small>
        <small>{{ $exam->exam_code }} &bull; {{ $exam->term }} {{ $exam->academic_year }}</small>
        <small class="conf">CONFIDENTIAL</small>
    </div>

</div>{{-- /.slip --}}
@endforeach
</div>{{-- /.page-wrap --}}

<script>
@if($mode === 'class' || $mode === 'all')
window.addEventListener('load', () => { setTimeout(() => window.print(), 700); });
@endif
</script>
</body>
</html>