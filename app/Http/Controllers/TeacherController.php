<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Academic\ClassModel;
use App\Models\Academic\Lesson;
use App\Models\Academic\Exam;
use App\Models\Academic\ExamResult;
use App\Models\Academic\ClassEnrollment;
use App\Models\User;
use App\Models\Subject;
use App\Models\Academic\Topic;
use App\Models\Academic\Section;
use App\Models\Academic\StudentProgress;

class TeacherController extends Controller
{
    public function teacherDashboard()
    {
        $teacherId = session('LoggedTeacher');

        // Fetch teacher user record
        $teacher = User::find($teacherId);

        // Classes assigned to this teacher
        $assignedClasses = ClassModel::whereHas('subjects', function ($q) use ($teacherId) {
            $q->where('class_subjects.teacher_id', $teacherId);
        })->with(['level', 'subjects'])->get();

        $assignedClassesCount = $assignedClasses->count();
        $assignedClassIds = $assignedClasses->pluck('id')->toArray();

        // Total students enrolled in teacher's classes
        $totalStudents = ClassEnrollment::whereIn('class_id', $assignedClassIds)
            ->where('status', 'active')
            ->distinct('student_id')
            ->count('student_id');

        // Total lessons by this teacher
        $totalLessons = Lesson::where('teacher_id', $teacherId)->count();
        $publishedLessons = Lesson::where('teacher_id', $teacherId)->where('status', 'published')->count();
        $draftLessons = Lesson::where('teacher_id', $teacherId)->where('status', 'draft')->count();

        // Total quizzes
        $totalQuizzes = Exam::where('teacher_id', $teacherId)->where('exam_type', 'quiz')->count();

        // Students who have completed at least one lesson from this teacher
        $completedProgressCount = StudentProgress::whereHas('lesson', function ($q) use ($teacherId) {
            $q->where('teacher_id', $teacherId);
        })->where('status', 'completed')->count();

        // Pending assignments (quizzes not yet taken)
        $pendingAssignments = 0;

        // Recent lessons (last 5)
        $recentLessons = Lesson::where('teacher_id', $teacherId)
            ->with(['topic.subject', 'topic.class.level'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Recent enrollments in teacher's classes
        $recentEnrollments = ClassEnrollment::whereIn('class_id', $assignedClassIds)
            ->with(['student', 'class.level'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('Teacher.dashboard', compact(
            'teacher',
            'assignedClassesCount',
            'assignedClasses',
            'totalStudents',
            'pendingAssignments',
            'totalLessons',
            'publishedLessons',
            'draftLessons',
            'totalQuizzes',
            'completedProgressCount',
            'recentLessons',
            'recentEnrollments'
        ));
    }

    public function getDashboardData(Request $request)
    {
        $teacherId = Session('LoggedTeacher');

        $sections = Section::with([
            'levels' => function ($q) use ($teacherId) {
                $q->with([
                    'classes' => function ($q2) use ($teacherId) {
                        $q2->whereHas('subjects.teachers', function ($q3) use ($teacherId) {
                            $q3->where('teacher_id', $teacherId);
                        })->with([
                                    'subjects' => function ($q3) use ($teacherId) {
                                        $q3->whereHas('teachers', function ($q4) use ($teacherId) {
                                            $q4->where('teacher_id', $teacherId);
                                        })->with([
                                                    'topics' => function ($q4) {
                                                        $q4->with([
                                                            'lessons' => function ($q5) {
                                                                $q5->with('resources')->orderBy('lesson_order');
                                                            }
                                                        ])->orderBy('order_no');
                                                    }
                                                ]);
                                    }
                                ]);
                    }
                ]);
            }
        ])->get();

        $stats = [
            'total_classes' => ClassModel::whereHas('subjects.teachers', function ($q) use ($teacherId) {
                $q->where('teacher_id', $teacherId);
            })->count(),
            'total_subjects' => Subject::whereHas('teachers', function ($q) use ($teacherId) {
                $q->where('teacher_id', $teacherId);
            })->count(),
            'total_topics' => Topic::whereHas('class.subjects.teachers', function ($q) use ($teacherId) {
                $q->where('teacher_id', $teacherId);
            })->count(),
            'total_lessons' => Lesson::where('teacher_id', $teacherId)->count(),
        ];

        return response()->json(['sections' => $sections, 'stats' => $stats]);
    }
}
