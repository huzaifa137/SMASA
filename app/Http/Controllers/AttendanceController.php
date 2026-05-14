<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\ClassSubject;
use App\Models\Stream;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\Teacher;
use App\Models\TeacherAttendance;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    // ══════════════════════════════════════════════════════════════
    //  ATTENDANCE DASHBOARD
    // ══════════════════════════════════════════════════════════════

    public function dashboard()
    {
        $schoolId  = session('LoggedSchool');
        $teacherId = session('LoggedTeacher');
        $today     = Carbon::today()->toDateString();

        // ── Student Attendance Today ──────────────────────────────
        $studentStats = StudentAttendance::where('school_id', $schoolId)
            ->where('attendance_date', $today)
            ->selectRaw("
                COUNT(*) as total,
                SUM(status='present') as present,
                SUM(status='absent') as absent,
                SUM(status='late') as late,
                SUM(status='excused') as excused
            ")->first();

        // Total enrolled students
        $totalEnrolled = Student::where('school_id', $schoolId)->count();

        // ── Teacher Attendance Today ──────────────────────────────
        $teacherStats = TeacherAttendance::where('school_id', $schoolId)
            ->where('attendance_date', $today)
            ->selectRaw("
                COUNT(*) as total,
                SUM(status='present') as present,
                SUM(status='absent') as absent,
                SUM(status='late') as late,
                SUM(status='on_leave') as on_leave
            ")->first();

        $totalTeachers = Teacher::where('school_id', $schoolId)->count();

        // ── Weekly Trend (last 7 days) ────────────────────────────
        $weeklyStudentTrend = StudentAttendance::where('school_id', $schoolId)
            ->where('attendance_date', '>=', Carbon::today()->subDays(6)->toDateString())
            ->selectRaw("attendance_date, SUM(status='present') as present, SUM(status='absent') as absent, COUNT(*) as total")
            ->groupBy('attendance_date')
            ->orderBy('attendance_date')
            ->get();

        $weeklyTeacherTrend = TeacherAttendance::where('school_id', $schoolId)
            ->where('attendance_date', '>=', Carbon::today()->subDays(6)->toDateString())
            ->selectRaw("attendance_date, SUM(status='present') as present, COUNT(*) as total")
            ->groupBy('attendance_date')
            ->orderBy('attendance_date')
            ->get();

        // ── Classes with attendance taken today ───────────────────
        $classesToday = StudentAttendance::where('school_id', $schoolId)
            ->where('attendance_date', $today)
            ->selectRaw("class_id, stream_id, COUNT(*) as total, SUM(status='present') as present, SUM(status='absent') as absent")
            ->groupBy('class_id', 'stream_id')
            ->get()
            ->map(function ($row) {
                $row->class_name  = Helper::recordMdname($row->class_id);
                $row->stream_name = Helper::recordMdname($row->stream_id);
                $row->rate        = $row->total > 0 ? round(($row->present / $row->total) * 100) : 0;
                return $row;
            });

        // ── Monthly attendance rate ───────────────────────────────
        $monthStart = Carbon::today()->startOfMonth()->toDateString();
        $monthlyRate = StudentAttendance::where('school_id', $schoolId)
            ->where('attendance_date', '>=', $monthStart)
            ->selectRaw("ROUND(SUM(status='present')/COUNT(*)*100,1) as rate")
            ->value('rate');

        // ── Teacher: classes they supervise (for quick action) ────
        $myClasses = [];
        if ($teacherId) {
            $myClasses = ClassSubject::where('school_id', $schoolId)
                ->where(function ($q) use ($teacherId) {
                    $q->where('subject_teacher_1', $teacherId)
                      ->orWhere('subject_teacher_2', $teacherId);
                })
                ->get()
                ->map(function ($cs) {
                    $cs->class_name  = Helper::recordMdname($cs->class_id);
                    $cs->stream_name = Helper::recordMdname($cs->stream_id);
                    $cs->subject_name = Helper::recordMdname($cs->subject_id);
                    return $cs;
                });
        }

        // ── Recent teacher attendance ─────────────────────────────
        $recentTeacherAttendance = TeacherAttendance::where('school_id', $schoolId)
            ->where('attendance_date', $today)
            ->with('teacher')
            ->orderBy('arrival_time')
            ->take(10)
            ->get();

        return view('Attendance.dashboard', compact(
            'studentStats',
            'teacherStats',
            'totalEnrolled',
            'totalTeachers',
            'weeklyStudentTrend',
            'weeklyTeacherTrend',
            'classesToday',
            'monthlyRate',
            'myClasses',
            'recentTeacherAttendance',
            'today'
        ));
    }

    // ══════════════════════════════════════════════════════════════
    //  STUDENT ATTENDANCE – Take Attendance (portal selection)
    // ══════════════════════════════════════════════════════════════

    public function studentAttendancePortal()
    {
        $schoolId  = session('LoggedSchool');
        $teacherId = session('LoggedTeacher');
        $today     = Carbon::today()->toDateString();

        $isAdmin = Helper::isTechSateAdminOrSchoolAdminsAlone();

        if ($isAdmin) {
            // Admin sees all classes in school
            $classrooms = Classroom::where('school_id', $schoolId)
                ->orderBy('class_name')
                ->get();
        } else {
            // Teacher sees only their assigned classes/subjects
            $assignedClassIds = ClassSubject::where('school_id', $schoolId)
                ->where(function ($q) use ($teacherId) {
                    $q->where('subject_teacher_1', $teacherId)
                      ->orWhere('subject_teacher_2', $teacherId);
                })
                ->pluck('class_id')
                ->unique()
                ->toArray();

            $supervisedClassIds = Classroom::where('school_id', $schoolId)
                ->where('class_supervisor', $teacherId)
                ->pluck('class_name')
                ->toArray();

            $allClassIds = array_unique(array_merge($assignedClassIds, $supervisedClassIds));

            $classrooms = Classroom::where('school_id', $schoolId)
                ->whereIn('class_name', $allClassIds)
                ->orderBy('class_name')
                ->get();
        }

        // Attach streams and today's attendance summary to each class
        $classrooms = $classrooms->map(function ($classroom) use ($schoolId, $today, $teacherId, $isAdmin) {
            $streams = Stream::where('school_id', $schoolId)
                ->where('class_id', $classroom->class_name)
                ->get();

            $classroom->streams = $streams->map(function ($stream) use ($schoolId, $today, $classroom, $teacherId, $isAdmin) {
                $totalStudents = Student::where('school_id', $schoolId)
                    ->where('senior', $classroom->class_name)
                    ->where('stream', $stream->stream_id)
                    ->count();

                $attendanceTaken = StudentAttendance::where('school_id', $schoolId)
                    ->where('class_id', $classroom->class_name)
                    ->where('stream_id', $stream->stream_id)
                    ->where('attendance_date', $today)
                    ->exists();

                $presentCount = StudentAttendance::where('school_id', $schoolId)
                    ->where('class_id', $classroom->class_name)
                    ->where('stream_id', $stream->stream_id)
                    ->where('attendance_date', $today)
                    ->where('status', 'present')
                    ->count();

                $stream->class_name    = Helper::recordMdname($classroom->class_name);
                $stream->stream_name   = Helper::recordMdname($stream->stream_id);
                $stream->total         = $totalStudents;
                $stream->taken         = $attendanceTaken;
                $stream->present_count = $presentCount;

                // Check if teacher is class teacher for this stream
                $stream->is_class_teacher = $stream->class_teacher == $teacherId;

                // Get subjects for this stream taught by this teacher
                $stream->subjects = $isAdmin ? collect() : ClassSubject::where('school_id', $schoolId)
                    ->where('class_id', $classroom->class_name)
                    ->where('stream_id', $stream->stream_id)
                    ->where(function ($q) use ($teacherId) {
                        $q->where('subject_teacher_1', $teacherId)
                          ->orWhere('subject_teacher_2', $teacherId);
                    })
                    ->get()
                    ->map(function ($cs) {
                        $cs->subject_name = Helper::recordMdname($cs->subject_id);
                        return $cs;
                    });

                return $stream;
            });

            $classroom->class_display = Helper::recordMdname($classroom->class_name);
            return $classroom;
        });

        return view('Attendance.student-portal', compact('classrooms', 'today', 'isAdmin'));
    }

    // ══════════════════════════════════════════════════════════════
    //  STUDENT ATTENDANCE – Take Attendance for a Class-Stream
    // ══════════════════════════════════════════════════════════════

    public function takeStudentAttendance(Request $request, $classId, $streamId)
    {
        $schoolId  = session('LoggedSchool');
        $teacherId = session('LoggedTeacher');
        $date      = $request->get('date', Carbon::today()->toDateString());
        $subjectId = $request->get('class_subject_id'); // optional: for subject-specific

        // Authorise
        $isAdmin = Helper::isTechSateAdminOrSchoolAdminsAlone();
        if (!$isAdmin) {
            $hasAccess = ClassSubject::where('school_id', $schoolId)
                ->where('class_id', $classId)
                ->where('stream_id', $streamId)
                ->where(function ($q) use ($teacherId) {
                    $q->where('subject_teacher_1', $teacherId)
                      ->orWhere('subject_teacher_2', $teacherId);
                })->exists();

            $isSupervisor = Classroom::where('school_id', $schoolId)
                ->where('class_name', $classId)
                ->where('class_supervisor', $teacherId)
                ->exists();

            $isClassTeacher = Stream::where('school_id', $schoolId)
                ->where('class_id', $classId)
                ->where('stream_id', $streamId)
                ->where('class_teacher', $teacherId)
                ->exists();

            if (!$hasAccess && !$isSupervisor && !$isClassTeacher) {
                abort(403, 'You are not assigned to this class.');
            }
        }

        // Load students
        $students = Student::where('school_id', $schoolId)
            ->where('senior', $classId)
            ->where('stream', $streamId)
            ->orderBy('lastname')
            ->get();

        // Load existing attendance for that date
        $existing = StudentAttendance::where('school_id', $schoolId)
            ->where('class_id', $classId)
            ->where('stream_id', $streamId)
            ->where('attendance_date', $date)
            ->when($subjectId, fn($q) => $q->where('class_subject_id', $subjectId))
            ->get()
            ->keyBy('student_id');

        // Load subjects for this class-stream (teacher's)
        $subjects = $isAdmin
            ? ClassSubject::where('school_id', $schoolId)
                ->where('class_id', $classId)
                ->where('stream_id', $streamId)
                ->get()
                ->map(fn($cs) => $cs->subject_name = Helper::recordMdname($cs->subject_id) ? $cs : $cs)
            : ClassSubject::where('school_id', $schoolId)
                ->where('class_id', $classId)
                ->where('stream_id', $streamId)
                ->where(function ($q) use ($teacherId) {
                    $q->where('subject_teacher_1', $teacherId)->orWhere('subject_teacher_2', $teacherId);
                })->get();

        $subjects = $subjects->map(function ($cs) {
            $cs->subject_name = Helper::recordMdname($cs->subject_id);
            return $cs;
        });

        // Attendance summary for date range (last 10 days) per student
        $history = StudentAttendance::where('school_id', $schoolId)
            ->where('class_id', $classId)
            ->where('stream_id', $streamId)
            ->where('attendance_date', '>=', Carbon::parse($date)->subDays(9)->toDateString())
            ->where('attendance_date', '<=', $date)
            ->get()
            ->groupBy('student_id');

        $className  = Helper::recordMdname($classId);
        $streamName = Helper::recordMdname($streamId);

        return view('Attendance.take-student-attendance', compact(
            'students', 'existing', 'subjects', 'history',
            'classId', 'streamId', 'className', 'streamName',
            'date', 'subjectId', 'teacherId'
        ));
    }

    // ══════════════════════════════════════════════════════════════
    //  STUDENT ATTENDANCE – Save
    // ══════════════════════════════════════════════════════════════

    public function saveStudentAttendance(Request $request)
    {
        $request->validate([
            'class_id'        => 'required|string',
            'stream_id'       => 'required|string',
            'attendance_date' => 'required|date',
            'attendance'      => 'required|array',
            'attendance.*.student_id' => 'required|integer',
            'attendance.*.status'     => 'required|in:present,absent,late,excused',
        ]);

        $schoolId  = session('LoggedSchool');
        $teacherId = session('LoggedTeacher');

        DB::beginTransaction();
        try {
            foreach ($request->attendance as $row) {
                StudentAttendance::updateOrCreate(
                    [
                        'school_id'        => $schoolId,
                        'student_id'       => $row['student_id'],
                        'class_id'         => $request->class_id,
                        'stream_id'        => $request->stream_id,
                        'attendance_date'  => $request->attendance_date,
                        'class_subject_id' => $request->class_subject_id ?: null,
                        'session'          => $request->session ?? 'morning',
                    ],
                    [
                        'status'       => $row['status'],
                        'arrival_time' => $row['arrival_time'] ?? null,
                        'remarks'      => $row['remarks'] ?? null,
                        'taken_by'     => $teacherId,
                        'period_label' => $request->period_label ?? null,
                    ]
                );
            }
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Attendance saved successfully for ' . count($request->attendance) . ' students.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ══════════════════════════════════════════════════════════════
    //  STUDENT ATTENDANCE – Report
    // ══════════════════════════════════════════════════════════════

    public function studentAttendanceReport(Request $request)
    {
        $schoolId = session('LoggedSchool');
        $classId  = $request->class_id;
        $streamId = $request->stream_id;
        $from     = $request->from ?? Carbon::today()->startOfMonth()->toDateString();
        $to       = $request->to   ?? Carbon::today()->toDateString();
        $status   = $request->status;

        // All classes and streams for filter dropdowns
        $classrooms = Classroom::where('school_id', $schoolId)->orderBy('class_name')->get();
        $streams    = $classId
            ? Stream::where('school_id', $schoolId)->where('class_id', $classId)->get()
            : collect();

        $records = collect();
        $studentSummary = collect();

        if ($classId && $streamId) {
            $query = StudentAttendance::where('school_id', $schoolId)
                ->where('class_id', $classId)
                ->where('stream_id', $streamId)
                ->whereBetween('attendance_date', [$from, $to]);

            if ($status) $query->where('status', $status);

            $records = $query->orderBy('attendance_date', 'desc')->get();

            // Per-student summary
            $studentSummary = StudentAttendance::where('school_id', $schoolId)
                ->where('class_id', $classId)
                ->where('stream_id', $streamId)
                ->whereBetween('attendance_date', [$from, $to])
                ->selectRaw("
                    student_id,
                    COUNT(*) as total_days,
                    SUM(status='present') as present,
                    SUM(status='absent') as absent,
                    SUM(status='late') as late,
                    SUM(status='excused') as excused,
                    ROUND(SUM(status='present')/COUNT(*)*100,1) as attendance_rate
                ")
                ->groupBy('student_id')
                ->get()
                ->map(function ($row) {
                    $student = Student::find($row->student_id);
                    $row->full_name = $student ? $student->firstname . ' ' . $student->lastname : 'N/A';
                    $row->admission = $student->admission_number ?? 'N/A';
                    return $row;
                })
                ->sortByDesc('attendance_rate');
        }

        $className  = $classId  ? Helper::recordMdname($classId)  : null;
        $streamName = $streamId ? Helper::recordMdname($streamId) : null;

        return view('Attendance.student-report', compact(
            'classrooms', 'streams', 'records', 'studentSummary',
            'classId', 'streamId', 'from', 'to', 'status',
            'className', 'streamName'
        ));
    }

    // ══════════════════════════════════════════════════════════════
    //  TEACHER ATTENDANCE – Daily Entry
    // ══════════════════════════════════════════════════════════════

    public function teacherAttendancePage(Request $request)
    {
        $schoolId = session('LoggedSchool');
        $date     = $request->get('date', Carbon::today()->toDateString());

        $teachers = Teacher::where('school_id', $schoolId)
            ->orderBy('surname')
            ->get();

        $existing = TeacherAttendance::where('school_id', $schoolId)
            ->where('attendance_date', $date)
            ->get()
            ->keyBy('teacher_id');

        // Stats for today
        $stats = [
            'total'    => $teachers->count(),
            'present'  => $existing->where('status', 'present')->count(),
            'absent'   => $existing->where('status', 'absent')->count(),
            'late'     => $existing->where('status', 'late')->count(),
            'on_leave' => $existing->where('status', 'on_leave')->count(),
            'not_marked' => $teachers->count() - $existing->count(),
        ];

        return view('Attendance.teacher-attendance', compact(
            'teachers', 'existing', 'date', 'stats'
        ));
    }

    // ══════════════════════════════════════════════════════════════
    //  TEACHER ATTENDANCE – Save single record (AJAX)
    // ══════════════════════════════════════════════════════════════

    public function saveTeacherAttendance(Request $request)
    {
        $request->validate([
            'teacher_id'      => 'required|exists:teachers,id',
            'attendance_date' => 'required|date',
            'status'          => 'required|in:present,absent,late,on_leave,half_day,excused',
        ]);

        $schoolId = session('LoggedSchool');

        $record = TeacherAttendance::updateOrCreate(
            [
                'school_id'       => $schoolId,
                'teacher_id'      => $request->teacher_id,
                'attendance_date' => $request->attendance_date,
            ],
            [
                'arrival_time'   => $request->arrival_time   ?: null,
                'departure_time' => $request->departure_time ?: null,
                'status'         => $request->status,
                'leave_type'     => $request->leave_type     ?: null,
                'remarks'        => $request->remarks        ?: null,
                'recorded_by'    => session('LoggedTeacher') ?? session('LoggedAdmin'),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Teacher attendance recorded.',
            'data'    => $record,
        ]);
    }

    // ══════════════════════════════════════════════════════════════
    //  TEACHER ATTENDANCE – Bulk Save (full page submit)
    // ══════════════════════════════════════════════════════════════

    public function saveTeacherAttendanceBulk(Request $request)
    {
        $request->validate([
            'attendance_date' => 'required|date',
            'attendance'      => 'required|array',
        ]);

        $schoolId = session('LoggedSchool');

        DB::beginTransaction();
        try {
            foreach ($request->attendance as $teacherId => $data) {
                if (empty($data['status'])) continue;

                TeacherAttendance::updateOrCreate(
                    [
                        'school_id'       => $schoolId,
                        'teacher_id'      => $teacherId,
                        'attendance_date' => $request->attendance_date,
                    ],
                    [
                        'arrival_time'   => $data['arrival_time']   ?? null,
                        'departure_time' => $data['departure_time'] ?? null,
                        'status'         => $data['status'],
                        'leave_type'     => $data['leave_type']     ?? null,
                        'remarks'        => $data['remarks']        ?? null,
                        'recorded_by'    => session('LoggedTeacher') ?? session('LoggedAdmin'),
                    ]
                );
            }
            DB::commit();

            return redirect()->back()->with('success', 'Teacher attendance saved successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    // ══════════════════════════════════════════════════════════════
    //  TEACHER ATTENDANCE – Report
    // ══════════════════════════════════════════════════════════════

    public function teacherAttendanceReport(Request $request)
    {
        $schoolId  = session('LoggedSchool');
        $teacherId = $request->teacher_id;
        $from      = $request->from ?? Carbon::today()->startOfMonth()->toDateString();
        $to        = $request->to   ?? Carbon::today()->toDateString();
        $status    = $request->status;

        $teachers = Teacher::where('school_id', $schoolId)->orderBy('surname')->get();

        $query = TeacherAttendance::where('school_id', $schoolId)
            ->whereBetween('attendance_date', [$from, $to]);

        if ($teacherId) $query->where('teacher_id', $teacherId);
        if ($status)    $query->where('status', $status);

        $records = $query->with('teacher')->orderBy('attendance_date', 'desc')->get();

        // Per-teacher summary
        $teacherSummary = TeacherAttendance::where('school_id', $schoolId)
            ->whereBetween('attendance_date', [$from, $to])
            ->when($teacherId, fn($q) => $q->where('teacher_id', $teacherId))
            ->selectRaw("
                teacher_id,
                COUNT(*) as total_days,
                SUM(status='present') as present,
                SUM(status='absent') as absent,
                SUM(status='late') as late,
                SUM(status='on_leave') as on_leave,
                SUM(status='half_day') as half_day,
                ROUND(SUM(status='present')/COUNT(*)*100,1) as attendance_rate
            ")
            ->groupBy('teacher_id')
            ->get()
            ->map(function ($row) {
                $teacher = Teacher::find($row->teacher_id);
                $row->full_name = $teacher ? $teacher->firstname . ' ' . $teacher->surname : 'N/A';
                $row->phone     = $teacher->phonenumber ?? '';
                return $row;
            })
            ->sortByDesc('attendance_rate');

        return view('Attendance.teacher-report', compact(
            'teachers', 'records', 'teacherSummary',
            'teacherId', 'from', 'to', 'status'
        ));
    }

    // ══════════════════════════════════════════════════════════════
    //  AJAX: Get streams by class (for filters)
    // ══════════════════════════════════════════════════════════════

    public function getStreamsByClass($classId)
    {
        $schoolId = session('LoggedSchool');
        $streams  = Stream::where('school_id', $schoolId)
            ->where('class_id', $classId)
            ->get()
            ->map(function ($s) {
                $s->stream_name = Helper::recordMdname($s->stream_id);
                return $s;
            });

        return response()->json($streams);
    }

    // ══════════════════════════════════════════════════════════════
    //  AJAX: Quick stats for a specific class-date
    // ══════════════════════════════════════════════════════════════

    public function classAttendanceSummary(Request $request)
    {
        $schoolId = session('LoggedSchool');
        $stats = StudentAttendance::where('school_id', $schoolId)
            ->where('class_id', $request->class_id)
            ->where('stream_id', $request->stream_id)
            ->where('attendance_date', $request->date)
            ->selectRaw("
                COUNT(*) as total,
                SUM(status='present') as present,
                SUM(status='absent') as absent,
                SUM(status='late') as late,
                SUM(status='excused') as excused
            ")->first();

        return response()->json($stats);
    }
}