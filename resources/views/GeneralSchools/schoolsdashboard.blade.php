{{-- resources/views/GeneralSchools/schoolsdashboard.blade.php --}}
@extends('layouts-side-bar.master')

@section('content')

    @php
        use App\Models\Student;
        use App\Models\Teacher;
        use App\Models\Classroom;
        use App\Models\Examination;
        use App\Models\ExaminationMark;
        use App\Models\StudentAttendance;
        use App\Models\TeacherAttendance;
        use App\Models\StudentExamSummary;
        use App\Models\School;
        use App\Models\SchoolProfile;
        use App\Http\Controllers\Helper;
        use Illuminate\Support\Facades\DB;

        $schoolId = Session('LoggedSchool');
        $school = School::find($schoolId);
        $profile = SchoolProfile::where('school_id', $schoolId)->first();

        // ── School identity ──────────────────────────────────────────────────────
        $schoolName = Helper::schoolNameBySchoolID($schoolId) ?? ($school->name ?? 'School Dashboard');
        $schoolCode = $school->registration_code ?? '—';
        $schoolCategory = Helper::recordMdname($school->school_type ?? '') ?: '—';
        $academicYear = Helper::schoolActiveYearName() ?: Helper::active_year() ?: date('Y');
        $currentTerm = Helper::schoolActiveTermName() ?: '—';

        // ── Core counts ──────────────────────────────────────────────────────────
        $totalStudents = Student::where('school_id', $schoolId)->count();
        $totalTeachers = Teacher::where('school_id', $schoolId)->count();
        $totalClasses = Classroom::where('school_id', $schoolId)->count();

        $maleStudents = Student::where('school_id', $schoolId)->where('gender', 'Male')->count();
        $femaleStudents = Student::where('school_id', $schoolId)->where('gender', 'Female')->count();

        // ── Attendance Rate (student) – last 30 days ─────────────────────────────
        $last30 = now()->subDays(30)->toDateString();

        $totalAttendanceRecords = StudentAttendance::where('school_id', $schoolId)
            ->where('attendance_date', '>=', $last30)
            ->count();

        $presentRecords = StudentAttendance::where('school_id', $schoolId)
            ->where('attendance_date', '>=', $last30)
            ->whereIn('status', ['present', 'late'])
            ->count();

        $avgAttendance = $totalAttendanceRecords > 0
            ? round(($presentRecords / $totalAttendanceRecords) * 100, 1)
            : 0;

        // ── Teacher Attendance Rate – this month ─────────────────────────────────
        $thisMonth = now()->startOfMonth()->toDateString();

        $teacherTotalRecords = TeacherAttendance::where('school_id', $schoolId)
            ->where('attendance_date', '>=', $thisMonth)->count();
        $teacherPresentRecords = TeacherAttendance::where('school_id', $schoolId)
            ->where('attendance_date', '>=', $thisMonth)
            ->whereIn('status', ['present', 'late'])->count();

        $teacherAttendanceRate = $teacherTotalRecords > 0
            ? round(($teacherPresentRecords / $teacherTotalRecords) * 100, 1)
            : 0;

        // ── Examinations ─────────────────────────────────────────────────────────
        $recentExaminations = Examination::where('school_id', $schoolId)
            ->orderBy('created_at', 'desc')
            ->limit(6)
            ->get()
            ->map(function ($exam) use ($schoolId) {
                $marks = ExaminationMark::where('examination_id', $exam->id)
                    ->where('school_id', $schoolId)
                    ->whereNotNull('marks_obtained')
                    ->get();

                $totalStudentsInExam = $marks->pluck('student_id')->unique()->count();
                $avgScore = $marks->count() > 0 ? round($marks->avg('marks_obtained'), 1) : 0;
                $passCount = $marks->where('marks_obtained', '>=', $exam->pass_mark ?? 50)
                    ->pluck('student_id')->unique()->count();

                return [
                    'name' => $exam->exam_name,
                    'date' => \Carbon\Carbon::parse($exam->start_date)->format('M d, Y'),
                    'avg_score' => $avgScore,
                    'passed' => $passCount,
                    'total' => $totalStudentsInExam,
                    'status' => $exam->status,
                ];
            });

        // ── Overall Pass Rate (across all exams for this school) ─────────────────
        $allMarks = ExaminationMark::where('school_id', $schoolId)
            ->whereNotNull('marks_obtained')
            ->get();

        $overallPassRate = 0;
        $averageScore = 0;
        if ($allMarks->count() > 0) {
            $passThreshold = 50;
            $passCount = $allMarks->where('marks_obtained', '>=', $passThreshold)->count();
            $overallPassRate = round(($passCount / $allMarks->count()) * 100, 1);
            $averageScore = round($allMarks->avg('marks_obtained'), 1);
        }

        // Map average score to a grade letter
        $averageGrade = '—';
        if ($averageScore >= 80)
            $averageGrade = 'A';
        elseif ($averageScore >= 70)
            $averageGrade = 'B+';
        elseif ($averageScore >= 60)
            $averageGrade = 'B';
        elseif ($averageScore >= 50)
            $averageGrade = 'C';
        elseif ($averageScore > 0)
            $averageGrade = 'D';

        // ── Top Performer (student with highest average exam mark) ───────────────
        $topPerformerData = ExaminationMark::where('school_id', $schoolId)
            ->whereNotNull('marks_obtained')
            ->selectRaw('student_id, AVG(marks_obtained) as avg_score')
            ->groupBy('student_id')
            ->orderByDesc('avg_score')
            ->first();

        $topPerformerName = '—';
        $topPerformerScore = 0;
        $topPerformerClass = '—';

        if ($topPerformerData) {
            $topStudent = Student::find($topPerformerData->student_id);
            if ($topStudent) {
                $topPerformerName = trim(($topStudent->firstname ?? '') . ' ' . ($topStudent->lastname ?? ''));
                $topPerformerScore = round($topPerformerData->avg_score, 1);
                $topPerformerClass = Helper::recordMdname($topStudent->senior ?? '') ?: '—';
            }
        }

        // ── Top 5 Students leaderboard ───────────────────────────────────────────
        $topStudentsRaw = ExaminationMark::where('school_id', $schoolId)
            ->whereNotNull('marks_obtained')
            ->selectRaw('student_id, AVG(marks_obtained) as avg_score, COUNT(DISTINCT examination_id) as exams_count')
            ->groupBy('student_id')
            ->orderByDesc('avg_score')
            ->limit(5)
            ->get();

        $studentLeaderboard = $topStudentsRaw->map(function ($row, $index) {
            $student = Student::find($row->student_id);
            return [
                'rank' => $index + 1,
                'name' => $student ? trim(($student->firstname ?? '') . ' ' . ($student->lastname ?? '')) : 'Unknown',
                'class' => $student ? (Helper::recordMdname($student->senior ?? '') ?: '—') : '—',
                'avg_score' => round($row->avg_score, 1),
                'exams' => $row->exams_count,
            ];
        });

        // ── Class Performance ────────────────────────────────────────────────────
        $classPerformance = Classroom::where('school_id', $schoolId)
            ->limit(8)
            ->get()
            ->map(function ($classroom) use ($schoolId) {
                $studentIds = Student::where('school_id', $schoolId)
                    ->where('senior', $classroom->id)
                    ->pluck('id');

                $marks = ExaminationMark::where('school_id', $schoolId)
                    ->whereIn('student_id', $studentIds)
                    ->whereNotNull('marks_obtained')
                    ->get();

                $avgScore = $marks->count() > 0 ? round($marks->avg('marks_obtained'), 1) : 0;
                $count = $studentIds->count();

                return [
                    'class' => $classroom->class_name,
                    'students' => $count,
                    'avg_score' => $avgScore,
                ];
            })
            ->filter(fn($c) => $c['avg_score'] > 0 || $c['students'] > 0)
            ->sortByDesc('avg_score')
            ->values()
            ->map(function ($item, $index) {
                $item['rank'] = $index + 1;
                return $item;
            });

        // ── Subject Performance ──────────────────────────────────────────────────
        $subjectPerformance = DB::table('examination_marks')
            ->join('subjects', 'examination_marks.subject_id', '=', 'subjects.ID')
            ->where('examination_marks.school_id', $schoolId)
            ->whereNotNull('marks_obtained')
            ->selectRaw('
                                    subjects.subject as subject_name,
                                    AVG(marks_obtained) as avg_score,
                                    COUNT(*) as total_marks,
                                    SUM(CASE WHEN marks_obtained >= 50 THEN 1 ELSE 0 END) as passed_count
                                ')
            ->groupBy('subjects.ID', 'subjects.subject')
            ->orderByDesc('avg_score')
            ->limit(6)
            ->get()
            ->map(function ($row) {
                $passRate = $row->total_marks > 0
                    ? round(($row->passed_count / $row->total_marks) * 100, 1)
                    : 0;
                return [
                    'subject' => $row->subject_name ?? 'Unknown',
                    'avg_score' => round($row->avg_score, 1),
                    'pass_rate' => $passRate,
                    'students' => $row->total_marks,
                ];
            });

        // ── Recent Exams for table (formatted) ───────────────────────────────────
        $recentExams = $recentExaminations;

        // ── Recent Enrollments (activity feed) ───────────────────────────────────
        $recentStudents = Student::where('school_id', $schoolId)
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();

        $recentTeachers = Teacher::where('school_id', $schoolId)
            ->orderBy('created_at', 'desc')
            ->limit(2)
            ->get();

        $schoolActivities = collect();

        foreach ($recentStudents as $s) {
            $name = trim(($s->firstname ?? '') . ' ' . ($s->lastname ?? ''));
            $className = Helper::recordMdname($s->senior ?? '') ?: '—';
            $schoolActivities->push([
                'icon' => 'fa-user-graduate',
                'color' => '#5351e4',
                'text' => "New student enrolled: {$name}" . ($className !== '—' ? " ({$className})" : ''),
                'time' => $s->created_at ? \Carbon\Carbon::parse($s->created_at)->diffForHumans() : 'Recently',
            ]);
        }

        foreach ($recentTeachers as $t) {
            $name = trim(($t->firstname ?? '') . ' ' . ($t->surname ?? ''));
            $schoolActivities->push([
                'icon' => 'fa-chalkboard-user',
                'color' => '#3b82f6',
                'text' => "New teacher added: {$name}",
                'time' => $t->created_at ? \Carbon\Carbon::parse($t->created_at)->diffForHumans() : 'Recently',
            ]);
        }

        // Latest exam result published
        $latestPublished = Examination::where('school_id', $schoolId)
            ->where('status', 'results_released')
            ->orderBy('published_at', 'desc')
            ->first();

        if ($latestPublished) {
            $schoolActivities->push([
                'icon' => 'fa-file-alt',
                'color' => '#6c3fc5',
                'text' => "Results published: {$latestPublished->exam_name}",
                'time' => \Carbon\Carbon::parse($latestPublished->published_at)->diffForHumans(),
            ]);
        }

        // ── Teacher of the Month (highest attendance rate this month) ─────────────
        $teacherOfMonth = null;
        $bestTeacher = TeacherAttendance::where('school_id', $schoolId)
            ->where('attendance_date', '>=', $thisMonth)
            ->selectRaw('teacher_id, COUNT(*) as total_days, SUM(CASE WHEN status IN ("present","late") THEN 1 ELSE 0 END) as present_days')
            ->groupBy('teacher_id')
            ->orderByRaw('SUM(CASE WHEN status IN ("present","late") THEN 1 ELSE 0 END) / COUNT(*) DESC')
            ->first();

        if ($bestTeacher) {
            $t = Teacher::find($bestTeacher->teacher_id);
            if ($t) {
                $rate = $bestTeacher->total_days > 0
                    ? round(($bestTeacher->present_days / $bestTeacher->total_days) * 100, 1)
                    : 100;
                $teacherOfMonth = [
                    'name' => trim(($t->firstname ?? '') . ' ' . ($t->surname ?? '')),
                    'subject' => '—',
                    'rating' => min(5, round($rate / 20, 1)),
                    'achievement' => "{$rate}% attendance this month",
                ];
            }
        }

        // ── Upcoming exams (active or marks_entry status) ─────────────────────────
        $upcomingExams = Examination::where('school_id', $schoolId)
            ->whereIn('status', ['draft', 'active', 'marks_entry'])
            ->orderBy('start_date')
            ->limit(5)
            ->get();

        // ── Current date info ─────────────────────────────────────────────────────
        $currentDate = now();

        // Days until next upcoming exam
        $nextExam = Examination::where('school_id', $schoolId)
            ->where('start_date', '>', now()->toDateString())
            ->orderBy('start_date')
            ->first();
        $daysUntilExams = $nextExam
            ? (int) now()->diffInDays(\Carbon\Carbon::parse($nextExam->start_date))
            : null;

        // Students added this month
        $newStudentsThisMonth = Student::where('school_id', $schoolId)
            ->where('created_at', '>=', $thisMonth)
            ->count();

    @endphp

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800&display=swap');

        :root {
            --brand: #5351e4;
            --brand-light: #2C29CA;
            --brand-dark: #2C29CA;
            --brand-muted: rgba(83, 81, 228, 0.10);
            --accent: #e0a020;
            --accent-muted: rgba(224, 160, 32, 0.12);
            --purple: #6c3fc5;
            --purple-muted: rgba(108, 63, 197, 0.12);
            --danger: #ef4444;
            --danger-muted: rgba(239, 68, 68, 0.12);
            --info: #3b82f6;
            --info-muted: rgba(59, 130, 246, 0.12);
            --success: #16a34a;
            --surface: #ffffff;
            --surface-2: #f7f9f7;
            --surface-3: #eef3ef;
            --border: rgba(83, 81, 228, 0.12);
            --text-primary: #0f1f14;
            --text-secondary: #4b6356;
            --text-muted: #8ca898;
            --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.06);
            --shadow-md: 0 4px 20px rgba(0, 0, 0, 0.09);
            --shadow-lg: 0 8px 40px rgba(0, 0, 0, 0.12);
            --radius-sm: 10px;
            --radius-md: 16px;
            --radius-lg: 24px;
            --font: 'Sora', sans-serif;
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }

        .school-dashboard {
            font-family: var(--font);
            color: var(--text-primary);
            background: var(--surface-2);
            min-height: 100vh;
            padding: 0 0 40px;
        }

        /* ── Header ── */
        .school-header {
            background: linear-gradient(135deg, var(--brand-dark) 0%, var(--brand) 55%, var(--brand-light) 100%);
            padding: 28px 32px 70px;
            position: relative;
            overflow: hidden;
            margin-top: 1.5em;
        }

        .school-header::before {
            content: '';
            position: absolute;
            inset: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }

        .school-header::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            right: 0;
            height: 60px;
            background: var(--surface-2);
            border-radius: 40px 40px 0 0;
        }

        .school-info {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 20px;
        }

        .school-title h1 {
            font-size: 1.8rem;
            font-weight: 800;
            color: #fff;
            margin: 0 0 8px;
            letter-spacing: -0.5px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .school-badge {
            background: rgba(255, 255, 255, 0.20);
            backdrop-filter: blur(8px);
            padding: 6px 14px;
            border-radius: 100px;
            font-size: 0.75rem;
            font-weight: 600;
            color: #fff;
        }

        .school-meta {
            color: rgba(255, 255, 255, 0.85);
            font-size: 0.85rem;
            display: flex;
            gap: 16px;
            margin-top: 8px;
            flex-wrap: wrap;
        }

        .school-meta i {
            margin-right: 6px;
        }

        .school-actions {
            display: flex;
            gap: 12px;
        }

        .school-btn {
            padding: 10px 20px;
            border-radius: 100px;
            font-size: 0.8rem;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .school-btn-primary {
            background: #fff;
            color: var(--brand-dark);
        }

        .school-btn-outline {
            background: rgba(255, 255, 255, 0.15);
            color: #fff;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .school-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.15);
        }

        /* ── Body ── */
        .school-body {
            padding: 0 24px;
            margin-top: -46px;
            position: relative;
            z-index: 2;
        }

        /* ── Welcome Card ── */
        .welcome-card {
            background: linear-gradient(135deg, #fff 0%, #fef9e8 100%);
            border-radius: var(--radius-lg);
            padding: 24px 28px;
            margin-bottom: 24px;
            border: 1px solid var(--border);
            box-shadow: var(--shadow-sm);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
        }

        .welcome-text h3 {
            font-size: 1.4rem;
            font-weight: 700;
            margin: 0 0 6px;
            color: var(--text-primary);
        }

        .welcome-text p {
            color: var(--text-secondary);
            font-size: 0.85rem;
            margin: 0;
        }

        .countdown-badge {
            background: var(--brand-muted);
            padding: 12px 20px;
            border-radius: var(--radius-md);
            text-align: center;
        }

        .countdown-number {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--brand);
            line-height: 1;
        }

        .countdown-label {
            font-size: 0.7rem;
            color: var(--text-muted);
            text-transform: uppercase;
            font-weight: 600;
        }

        /* ── KPI Grid ── */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 24px;
        }

        @media (max-width: 1100px) {
            .kpi-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 600px) {
            .kpi-grid {
                grid-template-columns: 1fr;
            }
        }

        .kpi-card {
            background: var(--surface);
            border-radius: var(--radius-md);
            padding: 20px;
            border: 1px solid var(--border);
            box-shadow: var(--shadow-sm);
            position: relative;
            overflow: hidden;
            transition: all 0.25s;
            animation: fadeUp 0.4s ease both;
        }

        .kpi-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-md);
        }

        .kpi-card:nth-child(1) {
            animation-delay: 0.05s;
        }

        .kpi-card:nth-child(2) {
            animation-delay: 0.10s;
        }

        .kpi-card:nth-child(3) {
            animation-delay: 0.15s;
        }

        .kpi-card:nth-child(4) {
            animation-delay: 0.20s;
        }

        .kpi-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            margin-bottom: 14px;
        }

        .kpi-value {
            font-size: 1.8rem;
            font-weight: 800;
            line-height: 1;
            margin-bottom: 4px;
            color: var(--text-primary);
        }

        .kpi-label {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .kpi-sub {
            font-size: 0.72rem;
            color: var(--text-muted);
            margin-top: 6px;
        }

        .kpi-trend {
            font-size: 0.7rem;
            margin-top: 8px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 2px 8px;
            border-radius: 100px;
        }

        .trend-up {
            background: rgba(22, 163, 74, 0.1);
            color: var(--success);
        }

        .trend-down {
            background: var(--danger-muted);
            color: var(--danger);
        }

        .trend-neutral {
            background: var(--brand-muted);
            color: var(--brand);
        }

        /* ── Section Header ── */
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }

        .section-title {
            font-size: 1rem;
            font-weight: 700;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .section-title i {
            color: var(--brand);
            font-size: 1rem;
        }

        .section-link {
            font-size: 0.75rem;
            color: var(--brand);
            text-decoration: none;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .section-link:hover {
            color: var(--brand-dark);
        }

        /* ── Cards ── */
        .card-school {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            margin-bottom: 20px;
        }

        .card-header-custom {
            padding: 16px 20px;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-body-custom {
            padding: 20px;
        }

        /* ── Grids ── */
        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 24px;
        }

        .grid-3 {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 24px;
        }

        @media (max-width: 1000px) {

            .grid-2,
            .grid-3 {
                grid-template-columns: 1fr;
            }
        }

        /* ── Tables ── */
        .school-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.8rem;
        }

        .school-table th {
            text-align: left;
            padding: 10px 12px;
            font-weight: 600;
            color: var(--text-muted);
            border-bottom: 1px solid var(--border);
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .school-table td {
            padding: 12px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.04);
        }

        .school-table tr:hover td {
            background: var(--surface-2);
        }

        /* ── Progress ── */
        .progress-track {
            height: 6px;
            background: var(--surface-3);
            border-radius: 3px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            border-radius: 3px;
            transition: width 0.6s ease;
        }

        /* ── Rank Badges ── */
        .rank-badge-sm {
            width: 26px;
            height: 26px;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            font-weight: 800;
        }

        .rank-1 {
            background: linear-gradient(135deg, #ffd700, #f5a623);
            color: #5a3a00;
        }

        .rank-2 {
            background: linear-gradient(135deg, #e8e8e8, #b0b0b0);
            color: #333;
        }

        .rank-3 {
            background: linear-gradient(135deg, #e8ab6e, #c57b3e);
            color: #fff;
        }

        .rank-other {
            background: var(--surface-3);
            color: var(--text-muted);
        }

        /* ── Activity Feed ── */
        .activity-feed {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .activity-item {
            display: flex;
            gap: 12px;
            padding: 12px 0;
            border-bottom: 1px solid var(--border);
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .activity-content {
            flex: 1;
        }

        .activity-text {
            font-size: 0.8rem;
            font-weight: 500;
            color: var(--text-primary);
            margin-bottom: 4px;
        }

        .activity-time {
            font-size: 0.7rem;
            color: var(--text-muted);
        }

        /* ── Upcoming Exams ── */
        .event-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .event-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            background: var(--surface-2);
            border-radius: var(--radius-sm);
            transition: all 0.2s;
        }

        .event-date {
            width: 50px;
            text-align: center;
            flex-shrink: 0;
        }

        .event-day {
            font-size: 1.2rem;
            font-weight: 800;
            color: var(--brand);
            line-height: 1;
        }

        .event-month {
            font-size: 0.7rem;
            color: var(--text-muted);
            text-transform: uppercase;
        }

        .event-info {
            flex: 1;
        }

        .event-title {
            font-weight: 600;
            font-size: 0.85rem;
            margin-bottom: 2px;
        }

        .event-time {
            font-size: 0.7rem;
            color: var(--text-muted);
        }

        .event-badge {
            padding: 4px 8px;
            border-radius: 100px;
            font-size: 0.65rem;
            font-weight: 600;
        }

        .event-badge.active {
            background: var(--brand-muted);
            color: var(--brand);
        }

        .event-badge.draft {
            background: var(--accent-muted);
            color: #b07d00;
        }

        .event-badge.marks_entry {
            background: var(--info-muted);
            color: var(--info);
        }

        .event-badge.closed {
            background: var(--danger-muted);
            color: var(--danger);
        }

        .event-badge.results_released {
            background: rgba(22, 163, 74, 0.1);
            color: var(--success);
        }

        /* ── Teacher Card ── */
        .teacher-card {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 16px;
            background: linear-gradient(135deg, var(--surface) 0%, var(--surface-2) 100%);
            border-radius: var(--radius-md);
            border: 1px solid var(--border);
        }

        .teacher-avatar {
            width: 56px;
            height: 56px;
            border-radius: 16px;
            background: linear-gradient(135deg, var(--brand) 0%, var(--brand-light) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 1.2rem;
            font-weight: 700;
        }

        .teacher-info h4 {
            font-size: 1rem;
            font-weight: 700;
            margin: 0 0 4px;
        }

        .teacher-rating {
            color: #f59e0b;
            font-size: 0.75rem;
        }

        /* ── Leaderboard ── */
        .leaderboard-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 0;
            border-bottom: 1px solid var(--border);
        }

        /* ── Empty State ── */
        .empty-state {
            text-align: center;
            padding: 32px 20px;
            color: var(--text-muted);
            font-size: 0.85rem;
        }

        .empty-state i {
            font-size: 2rem;
            margin-bottom: 10px;
            display: block;
            opacity: 0.4;
        }

        /* ── Footer ── */
        .school-footer {
            margin-top: 24px;
            padding: 20px 24px;
            border-top: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            font-size: 0.7rem;
            color: var(--text-muted);
        }

        /* ── Exam status badge ── */
        .exam-status {
            padding: 4px 8px;
            border-radius: 100px;
            font-size: 0.7rem;
            font-weight: 600;
        }

        /* ── Gender split bar ── */
        .gender-bar {
            display: flex;
            height: 8px;
            border-radius: 8px;
            overflow: hidden;
            margin-top: 8px;
        }

        .gender-male {
            background: var(--brand);
        }

        .gender-female {
            background: #ec4899;
        }

        /* ── Animations ── */
        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* ── Responsive School Header ── */
        @media (max-width: 768px) {
            .school-info {
                flex-direction: column !important;
                align-items: center !important;
                text-align: center !important;
            }

            .school-title h1 {
                justify-content: center !important;
                flex-wrap: wrap !important;
            }

            .school-meta {
                justify-content: center !important;
                flex-wrap: wrap !important;
            }

            .school-actions {
                justify-content: center !important;
                width: 100% !important;
                margin-top: 10px !important;
            }

            /* Move icon to top center */
            .school-title h1 i.fa-school {
                display: block !important;
                width: 100% !important;
                text-align: center !important;
                font-size: 2.5rem !important;
                margin-bottom: 8px !important;
            }

            /* Date on separate line */
            .school-meta span:last-child {
                display: block !important;
                width: 100% !important;
                text-align: center !important;
                margin-top: 4px !important;
            }

            /* The three meta items stay in a row */
            .school-meta span:not(:last-child) {
                display: inline-flex !important;
            }
        }
    </style>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <div class="school-dashboard">

        {{-- ── School Header ── --}}
        <div class="school-header">
            <div class="school-info">
                <div>
                    <div class="school-title">
                        <h1>
                            <i class="fas fa-school text-white"></i>
                            <span class="text-white">{{ $schoolName }}</span>
                            <span class="school-badge">{{ $schoolCode }}</span>
                        </h1>
                    </div>
                    <div class="school-meta">
                        <div style="display:flex;flex-wrap:nowrap;gap:12px;justify-content:flex-start;overflow-x:auto;margin-top:1em;">
                            @if($schoolCategory !== '—')
                                <span style="white-space:nowrap;"><i class="fas fa-layer-group"></i>
                                    {{ $schoolCategory }}</span>
                            @endif
                            <span style="white-space:nowrap;"><i class="fas fa-calendar-alt"></i> {{ $academicYear }}</span>
                            @if($currentTerm !== '—')
                                <span style="white-space:nowrap;"><i class="fas fa-book-open"></i> {{ $currentTerm }}</span>
                            @endif
                        </div>
                        <div>
                            <span><i class="fas fa-clock"></i> {{ $currentDate->format('l, d M Y') }}</span>
                        </div>
                    </div>
                </div>
                <div class="school-actions">
                    <a href="{{ url('/school-individual-profile/' . Session('LoggedSchool')) ?? '#' }}"
                        class="school-btn school-btn-outline">
                        <i class="fas fa-cog"></i> Settings
                    </a>
                </div>
            </div>
        </div>

        <div class="school-body">

            {{-- ── Welcome + countdown ── --}}
            <div class="welcome-card">
                <div class="welcome-text">
                    <h3>Welcome, {{ $schoolName }}!</h3>
                    <p class="mt-3">
                        <i class="fas fa-users me-1"></i> {{ number_format($totalStudents) }} students
                        &nbsp;·&nbsp;
                        <i class="fas fa-chalkboard-user me-1"></i> {{ number_format($totalTeachers) }} teachers
                        @if($overallPassRate > 0)
                            &nbsp;·&nbsp;
                            <i class="fas fa-chart-line me-1"></i> {{ $overallPassRate }}% overall pass rate
                        @endif
                    </p>
                </div>
                @if($daysUntilExams !== null)
                    <div class="countdown-badge">
                        <div class="countdown-number">{{ $daysUntilExams }}</div>
                        <div class="countdown-label">Days until<br>next exam</div>
                    </div>
                @else
                    <div class="countdown-badge">
                        <div class="countdown-number">{{ $currentDate->format('d') }}</div>
                        <div class="countdown-label">{{ $currentDate->format('M Y') }}</div>
                    </div>
                @endif
            </div>

            {{-- ── KPI Cards ── --}}
            <div class="kpi-grid">

                {{-- Total Students --}}
                <div class="kpi-card">
                    <div class="kpi-icon" style="background: var(--brand-muted); color: var(--brand);">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <div class="kpi-value">{{ number_format($totalStudents) }}</div>
                    <div class="kpi-label">Total Students</div>
                    @if($totalStudents > 0)
                        <div class="gender-bar" style="margin-top:10px;">
                            @php
                                $malePct = $totalStudents > 0 ? round(($maleStudents / $totalStudents) * 100) : 50;
                                $femalePct = 100 - $malePct;
                            @endphp
                            <div class="gender-male" style="width:{{ $malePct }}%;"></div>
                            <div class="gender-female" style="width:{{ $femalePct }}%;"></div>
                        </div>
                        <div class="kpi-sub">
                            <span style="color:var(--brand);">♂ {{ $maleStudents }}</span>
                            &nbsp;
                            <span style="color:#ec4899;">♀ {{ $femaleStudents }}</span>
                        </div>
                    @else
                        <span class="kpi-trend trend-neutral"><i class="fas fa-info-circle"></i> No students yet</span>
                    @endif
                </div>

                {{-- Total Teachers --}}
                <div class="kpi-card">
                    <div class="kpi-icon" style="background: var(--info-muted); color: var(--info);">
                        <i class="fas fa-chalkboard-user"></i>
                    </div>
                    <div class="kpi-value">{{ number_format($totalTeachers) }}</div>
                    <div class="kpi-label">Total Teachers</div>
                    @if($newStudentsThisMonth > 0)
                        <span class="kpi-trend trend-up">
                            <i class="fas fa-arrow-up"></i> +{{ $newStudentsThisMonth }} students this month
                        </span>
                    @else
                        <span class="kpi-trend trend-neutral">
                            <i class="fas fa-building"></i> {{ $totalClasses }} class{{ $totalClasses !== 1 ? 'es' : '' }}
                        </span>
                    @endif
                </div>

                {{-- Pass Rate --}}
                <div class="kpi-card">
                    <div class="kpi-icon" style="background: var(--accent-muted); color: #b07d00;">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    @if($overallPassRate > 0)
                        <div class="kpi-value">{{ $overallPassRate }}%</div>
                        <div class="kpi-label">Overall Pass Rate</div>
                        <div class="progress-track" style="margin-top:10px;">
                            <div class="progress-fill" style="width:{{ $overallPassRate }}%; background: #e0a020;"></div>
                        </div>
                    @else
                        <div class="kpi-value">—</div>
                        <div class="kpi-label">Pass Rate</div>
                        <span class="kpi-trend trend-neutral"><i class="fas fa-info-circle"></i> No exam data yet</span>
                    @endif
                </div>

                {{-- Attendance Rate --}}
                <div class="kpi-card">
                    <div class="kpi-icon" style="background: var(--purple-muted); color: var(--purple);">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    @if($avgAttendance > 0)
                        <div class="kpi-value">{{ $avgAttendance }}%</div>
                        <div class="kpi-label">Student Attendance</div>
                        <div class="progress-track" style="margin-top:10px;">
                            <div class="progress-fill" style="width:{{ $avgAttendance }}%; background: var(--purple);"></div>
                        </div>
                        <div class="kpi-sub">Last 30 days</div>
                    @else
                        <div class="kpi-value">—</div>
                        <div class="kpi-label">Attendance Rate</div>
                        <span class="kpi-trend trend-neutral"><i class="fas fa-info-circle"></i> No records yet</span>
                    @endif
                </div>

            </div>

            {{-- ── Secondary Row: Top Performer, Average Grade, Teacher Attendance ── --}}
            <div class="grid-3">

                {{-- Top Performer --}}
                <div class="card-school">
                    <div class="card-header-custom">
                        <div class="section-title"><i class="fas fa-trophy"></i> Top Performer</div>
                        <i class="fas fa-medal" style="color:#f59e0b;"></i>
                    </div>
                    <div class="card-body-custom">
                        @if($topPerformerName !== '—')
                            <div style="display:flex; align-items:center; gap:16px;">
                                <div
                                    style="width:60px;height:60px;background:linear-gradient(135deg,#f59e0b,#fbbf24);border-radius:20px;display:flex;align-items:center;justify-content:center;font-size:1.5rem;font-weight:800;color:#fff;">
                                    ★
                                </div>
                                <div>
                                    <div style="font-size:1rem;font-weight:700;">{{ $topPerformerName }}</div>
                                    <div style="font-size:0.75rem;color:var(--text-muted);">
                                        Class: {{ $topPerformerClass }}
                                    </div>
                                    <div style="font-size:0.75rem;color:var(--text-muted);">
                                        Avg Score: <strong style="color:var(--brand);">{{ $topPerformerScore }}%</strong>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="empty-state">
                                <i class="fas fa-trophy"></i>
                                No exam results yet
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Average Grade --}}
                <div class="card-school">
                    <div class="card-header-custom">
                        <div class="section-title"><i class="fas fa-chart-simple"></i> Average Grade</div>
                    </div>
                    <div class="card-body-custom" style="text-align:center;">
                        @if($averageGrade !== '—')
                            <div style="font-size:3rem;font-weight:800;color:var(--brand);">{{ $averageGrade }}</div>
                            <div style="font-size:0.75rem;color:var(--text-muted);">School-wide average ({{ $averageScore }}%)
                            </div>
                            <div class="progress-track" style="margin-top:12px;">
                                <div class="progress-fill" style="width:{{ $averageScore }}%;background:var(--brand);"></div>
                            </div>
                        @else
                            <div style="font-size:3rem;font-weight:800;color:var(--text-muted);">—</div>
                            <div style="font-size:0.75rem;color:var(--text-muted);">No exam data available</div>
                        @endif
                    </div>
                </div>

                {{-- Teacher Attendance --}}
                <div class="card-school">
                    <div class="card-header-custom">
                        <div class="section-title"><i class="fas fa-user-check"></i> Teacher Attendance</div>
                        <span style="font-size:0.7rem;color:var(--text-muted);">This month</span>
                    </div>
                    <div class="card-body-custom">
                        @if($teacherAttendanceRate > 0)
                            <div style="display:flex;justify-content:space-between;margin-bottom:8px;">
                                <span style="font-size:0.7rem;color:var(--text-muted);">Attendance Rate</span>
                                <span style="font-weight:700;">{{ $teacherAttendanceRate }}%</span>
                            </div>
                            <div class="progress-track" style="margin-bottom:16px;">
                                <div class="progress-fill"
                                    style="width:{{ $teacherAttendanceRate }}%;background:var(--success);"></div>
                            </div>
                            <div style="display:flex;justify-content:space-between;">
                                <div>
                                    <div style="font-size:1rem;font-weight:700;">{{ $teacherPresentRecords }}</div>
                                    <div style="font-size:0.7rem;color:var(--text-muted);">Present Records</div>
                                </div>
                                <div style="text-align:right;">
                                    <div style="font-size:1rem;font-weight:700;color:var(--danger);">
                                        {{ $teacherTotalRecords - $teacherPresentRecords }}
                                    </div>
                                    <div style="font-size:0.7rem;color:var(--text-muted);">Absent Records</div>
                                </div>
                            </div>
                        @else
                            <div class="empty-state">
                                <i class="fas fa-calendar-times"></i>
                                No attendance records this month
                            </div>
                        @endif
                    </div>
                </div>

            </div>

            {{-- ── Class Performance + Student Leaderboard ── --}}
            <div class="grid-2">

                {{-- Class Performance --}}
                <div class="card-school">
                    <div class="card-header-custom">
                        <div class="section-title"><i class="fas fa-chart-bar"></i> Class Performance</div>
                        <a href="{{ route('school.teachers') ?? '#' }}" class="section-link">
                            View All <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                    @if($classPerformance->count() > 0)
                        <div style="overflow-x:auto;">
                            <table class="school-table">
                                <thead>
                                    <tr>
                                        <th>Class</th>
                                        <th>Students</th>
                                        <th>Avg Score</th>
                                        <th>Rank</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($classPerformance as $class)
                                        <tr>
                                            <td style="font-weight:600;">{{ $class['class'] }}</td>
                                            <td>{{ $class['students'] }}</td>
                                            <td>
                                                @if($class['avg_score'] > 0)
                                                    <strong
                                                        style="color:{{ $class['avg_score'] >= 50 ? 'var(--brand)' : 'var(--danger)' }};">
                                                        {{ $class['avg_score'] }}%
                                                    </strong>
                                                @else
                                                    <span style="color:var(--text-muted);">—</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span
                                                    class="rank-badge-sm rank-{{ $class['rank'] <= 3 ? $class['rank'] : 'other' }}">
                                                    {{ $class['rank'] }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="empty-state">
                            <i class="fas fa-school"></i>
                            No classes found. Add classes to see performance data.
                        </div>
                    @endif
                </div>

                {{-- Student Leaderboard --}}
                <div class="card-school">
                    <div class="card-header-custom">
                        <div class="section-title"><i class="fas fa-crown"></i> Top Students</div>
                    </div>
                    <div class="card-body-custom">
                        @if($studentLeaderboard->count() > 0)
                            @foreach($studentLeaderboard as $student)
                                <div class="leaderboard-item">
                                    <span class="rank-badge-sm rank-{{ $student['rank'] <= 3 ? $student['rank'] : 'other' }}">
                                        {{ $student['rank'] }}
                                    </span>
                                    <div style="flex:1;">
                                        <div style="font-weight:600;font-size:0.85rem;">{{ $student['name'] }}</div>
                                        <div style="font-size:0.7rem;color:var(--text-muted);">
                                            {{ $student['class'] }}
                                            &nbsp;·&nbsp; {{ $student['exams'] }} exam{{ $student['exams'] !== 1 ? 's' : '' }}
                                        </div>
                                    </div>
                                    <div style="text-align:right;">
                                        <div style="font-weight:700;color:var(--brand);">{{ $student['avg_score'] }}%</div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="empty-state">
                                <i class="fas fa-star"></i>
                                No exam results yet to rank students.
                            </div>
                        @endif
                    </div>
                </div>

            </div>

            {{-- ── Subject Performance ── --}}
            @if($subjectPerformance->count() > 0)
                <div class="card-school" style="margin-bottom:24px;">
                    <div class="card-header-custom">
                        <div class="section-title"><i class="fas fa-book"></i> Subject Performance</div>
                    </div>
                    <div class="card-body-custom">
                        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:16px;">
                            @foreach($subjectPerformance as $subject)
                                <div>
                                    <div style="display:flex;justify-content:space-between;margin-bottom:4px;">
                                        <span style="font-weight:600;font-size:0.85rem;">{{ $subject['subject'] }}</span>
                                        <span style="font-weight:700;color:var(--brand);">{{ $subject['avg_score'] }}%</span>
                                    </div>
                                    <div
                                        style="display:flex;justify-content:space-between;font-size:0.7rem;color:var(--text-muted);margin-bottom:4px;">
                                        <span>Pass Rate: {{ $subject['pass_rate'] }}%</span>
                                        <span>{{ $subject['students'] }} records</span>
                                    </div>
                                    <div class="progress-track">
                                        <div class="progress-fill"
                                            style="width:{{ $subject['avg_score'] }}%;
                                                                           background:{{ $subject['avg_score'] >= 70 ? 'var(--brand)' : ($subject['avg_score'] >= 50 ? 'var(--accent)' : 'var(--danger)') }};">
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            {{-- ── Recent Examinations + Upcoming Exams ── --}}
            <div class="grid-2">

                {{-- Recent Examinations --}}
                <div class="card-school">
                    <div class="card-header-custom">
                        <div class="section-title"><i class="fas fa-file-alt"></i> Recent Examinations</div>
                    </div>
                    @if($recentExams->count() > 0)
                        <div style="overflow-x:auto;">
                            <table class="school-table">
                                <thead>
                                    <tr>
                                        <th>Exam</th>
                                        <th>Date</th>
                                        <th>Avg</th>
                                        <th>Pass/Total</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentExams as $exam)
                                        <tr>
                                            <td style="font-weight:500;">{{ $exam['name'] }}</td>
                                            <td style="font-size:0.75rem;">{{ $exam['date'] }}</td>
                                            <td>
                                                @if($exam['avg_score'] > 0)
                                                    <strong
                                                        style="color:{{ $exam['avg_score'] >= 50 ? 'var(--brand)' : 'var(--danger)' }};">
                                                        {{ $exam['avg_score'] }}%
                                                    </strong>
                                                @else
                                                    <span style="color:var(--text-muted);">—</span>
                                                @endif
                                            </td>
                                            <td>{{ $exam['passed'] }}/{{ $exam['total'] }}</td>
                                            <td>
                                                @php
                                                    $statusColors = [
                                                        'draft' => ['bg' => 'rgba(224,160,32,0.1)', 'color' => '#b07d00'],
                                                        'active' => ['bg' => 'var(--brand-muted)', 'color' => 'var(--brand)'],
                                                        'marks_entry' => ['bg' => 'var(--info-muted)', 'color' => 'var(--info)'],
                                                        'closed' => ['bg' => 'var(--danger-muted)', 'color' => 'var(--danger)'],
                                                        'results_released' => ['bg' => 'rgba(22,163,74,0.1)', 'color' => 'var(--success)'],
                                                    ];
                                                    $sc = $statusColors[$exam['status']] ?? ['bg' => 'var(--surface-3)', 'color' => 'var(--text-muted)'];
                                                @endphp
                                                <span class="exam-status"
                                                    style="background:{{ $sc['bg'] }};color:{{ $sc['color'] }};">
                                                    {{ ucwords(str_replace('_', ' ', $exam['status'])) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="empty-state">
                            <i class="fas fa-file-alt"></i>
                            No examinations created yet.
                        </div>
                    @endif
                </div>

                {{-- Upcoming Exams --}}
                <div class="card-school">
                    <div class="card-header-custom">
                        <div class="section-title"><i class="fas fa-calendar-alt"></i> Upcoming Examinations</div>
                    </div>
                    <div class="card-body-custom">
                        @if($upcomingExams->count() > 0)
                            <div class="event-list">
                                @foreach($upcomingExams as $exam)
                                    <div class="event-item">
                                        <div class="event-date">
                                            <div class="event-day">{{ \Carbon\Carbon::parse($exam->start_date)->format('d') }}</div>
                                            <div class="event-month">{{ \Carbon\Carbon::parse($exam->start_date)->format('M') }}
                                            </div>
                                        </div>
                                        <div class="event-info">
                                            <div class="event-title">{{ $exam->exam_name }}</div>
                                            <div class="event-time">
                                                <i class="fas fa-tag"></i> {{ $exam->exam_type }}
                                                &nbsp;·&nbsp; {{ $exam->term }}
                                            </div>
                                        </div>
                                        <span class="event-badge {{ $exam->status }}">
                                            {{ ucwords(str_replace('_', ' ', $exam->status)) }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="empty-state">
                                <i class="fas fa-calendar-times"></i>
                                No upcoming examinations scheduled.
                            </div>
                        @endif
                    </div>
                </div>

            </div>

            {{-- ── Teacher Spotlight + Recent Activity ── --}}
            <div class="grid-2">

                {{-- Teacher of the Month --}}
                <div class="card-school">
                    <div class="card-header-custom">
                        <div class="section-title"><i class="fas fa-chalkboard-user"></i> Best Attending Teacher</div>
                        <i class="fas fa-award" style="color:#f59e0b;"></i>
                    </div>
                    <div class="card-body-custom">
                        @if($teacherOfMonth)
                            <div class="teacher-card">
                                <div class="teacher-avatar">
                                    @php
                                        $parts = explode(' ', $teacherOfMonth['name']);
                                        echo strtoupper(substr($parts[0] ?? 'T', 0, 1) . substr($parts[1] ?? '', 0, 1));
                                    @endphp
                                </div>
                                <div class="teacher-info">
                                    <h4>{{ $teacherOfMonth['name'] }}</h4>
                                    <div class="teacher-rating">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="fas fa-star"
                                                style="color:{{ $i <= floor($teacherOfMonth['rating']) ? '#f59e0b' : '#e5e7eb' }};font-size:0.7rem;"></i>
                                        @endfor
                                        <span style="font-size:0.7rem;margin-left:4px;">{{ $teacherOfMonth['rating'] }}</span>
                                    </div>
                                    <div style="font-size:0.75rem;color:var(--text-secondary);margin-top:6px;">
                                        <i class="fas fa-chart-line"></i> {{ $teacherOfMonth['achievement'] }}
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="empty-state">
                                <i class="fas fa-chalkboard-user"></i>
                                No teacher attendance records this month yet.
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Recent Activity --}}
                <div class="card-school">
                    <div class="card-header-custom">
                        <div class="section-title"><i class="fas fa-bell"></i> Recent Activity</div>
                        <span
                            style="width:8px;height:8px;background:#22c55e;border-radius:50%;box-shadow:0 0 0 3px rgba(34,197,94,0.2);display:inline-block;"></span>
                    </div>
                    <div class="card-body-custom">
                        @if($schoolActivities->count() > 0)
                            <ul class="activity-feed">
                                @foreach($schoolActivities as $activity)
                                    <li class="activity-item">
                                        <div class="activity-icon"
                                            style="background:{{ $activity['color'] }}1a; color:{{ $activity['color'] }};">
                                            <i class="fas {{ $activity['icon'] }}"></i>
                                        </div>
                                        <div class="activity-content">
                                            <div class="activity-text">{{ $activity['text'] }}</div>
                                            <div class="activity-time">
                                                <i class="far fa-clock"></i> {{ $activity['time'] }}
                                            </div>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <div class="empty-state">
                                <i class="fas fa-bell-slash"></i>
                                No recent activity to show.
                            </div>
                        @endif
                    </div>
                </div>

            </div>

            {{-- ── Quick Stats Row ── --}}
            <div class="grid-3">

                {{-- Classes Summary --}}
                <div class="card-school">
                    <div class="card-header-custom">
                        <div class="section-title"><i class="fas fa-door-open"></i> Classes</div>
                    </div>
                    <div class="card-body-custom" style="text-align:center;">
                        <div style="font-size:2.5rem;font-weight:800;color:var(--brand);">{{ $totalClasses }}</div>
                        <div style="font-size:0.8rem;color:var(--text-muted);margin-top:4px;">
                            Active Classrooms
                        </div>
                        @if($totalStudents > 0 && $totalClasses > 0)
                            <div style="margin-top:12px;font-size:0.8rem;color:var(--text-secondary);">
                                ~{{ round($totalStudents / $totalClasses) }} students/class
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Gender Split --}}
                <div class="card-school">
                    <div class="card-header-custom">
                        <div class="section-title"><i class="fas fa-venus-mars"></i> Gender Split</div>
                    </div>
                    <div class="card-body-custom">
                        @if($totalStudents > 0)
                            <div style="display:flex;justify-content:space-between;margin-bottom:8px;">
                                <span style="font-size:0.8rem;color:var(--brand);font-weight:600;">
                                    ♂ Male: {{ $maleStudents }}
                                    ({{ round(($maleStudents / $totalStudents) * 100) }}%)
                                </span>
                                <span style="font-size:0.8rem;color:#ec4899;font-weight:600;">
                                    ♀ Female: {{ $femaleStudents }}
                                    ({{ round(($femaleStudents / $totalStudents) * 100) }}%)
                                </span>
                            </div>
                            <div class="gender-bar" style="height:12px;">
                                <div class="gender-male" style="width:{{ round(($maleStudents / $totalStudents) * 100) }}%;">
                                </div>
                                <div class="gender-female"
                                    style="width:{{ round(($femaleStudents / $totalStudents) * 100) }}%;">
                                </div>
                            </div>
                        @else
                            <div class="empty-state">
                                <i class="fas fa-users"></i>
                                No students enrolled yet.
                            </div>
                        @endif
                    </div>
                </div>

                {{-- School Info Summary --}}
                <div class="card-school">
                    <div class="card-header-custom">
                        <div class="section-title"><i class="fas fa-info-circle"></i> School Info</div>
                    </div>
                    <div class="card-body-custom">
                        <div style="font-size:0.8rem;display:flex;flex-direction:column;gap:8px;">
                            <div style="display:flex;justify-content:space-between;">
                                <span style="color:var(--text-muted);">Code</span>
                                <span style="font-weight:600;font-family:monospace;">{{ $schoolCode }}</span>
                            </div>
                            @if($schoolCategory !== '—')
                                <div style="display:flex;justify-content:space-between;">
                                    <span style="color:var(--text-muted);">Type</span>
                                    <span style="font-weight:600;">{{ $schoolCategory }}</span>
                                </div>
                            @endif
                            <div style="display:flex;justify-content:space-between;">
                                <span style="color:var(--text-muted);">Academic Year</span>
                                <span style="font-weight:600;">{{ $academicYear }}</span>
                            </div>
                            @if($currentTerm !== '—')
                                <div style="display:flex;justify-content:space-between;">
                                    <span style="color:var(--text-muted);">Current Term</span>
                                    <span style="font-weight:600;">{{ $currentTerm }}</span>
                                </div>
                            @endif
                            @if($school && $school->gender)
                                <div style="display:flex;justify-content:space-between;">
                                    <span style="color:var(--text-muted);">Gender</span>
                                    <span style="font-weight:600;">{{ Helper::recordMdname($school->gender) }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

            </div>

        </div>{{-- /school-body --}}

        {{-- ── Footer ── --}}
        <div class="school-footer">
            <span><i class="fas fa-shield-alt"></i> {{ $schoolName }} — School Management Portal</span>
            <span><i class="fas fa-clock"></i> Last loaded: {{ now()->format('H:i:s') }}</span>
            <span><i class="fas fa-calendar-alt"></i> {{ $academicYear }}@if($currentTerm !== '—') &nbsp;|&nbsp;
            {{ $currentTerm }}@endif</span>
        </div>

    </div>{{-- /school-dashboard --}}
    </div>
    </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Animate progress bars on load
            document.querySelectorAll('.progress-fill').forEach(function (el) {
                const targetWidth = el.style.width;
                el.style.width = '0';
                setTimeout(function () {
                    el.style.width = targetWidth;
                }, 300);
            });

            // Animate gender bar
            document.querySelectorAll('.gender-male, .gender-female').forEach(function (el) {
                const targetWidth = el.style.width;
                el.style.width = '0';
                setTimeout(function () {
                    el.style.transition = 'width 0.8s ease';
                    el.style.width = targetWidth;
                }, 400);
            });
        });
    </script>

@endsection