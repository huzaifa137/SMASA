<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\ClassSubject;
use App\Models\Stream;
use App\Models\Teacher;
use App\Models\Timetable;
use App\Models\TimetablePeriod;
use App\Models\TimetableSlot;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TimetableController extends Controller
{
    // ══════════════════════════════════════════════════════════════
    //  TIMETABLE DASHBOARD
    // ══════════════════════════════════════════════════════════════

    public function dashboard()
    {
        $schoolId  = session('LoggedSchool');
        $teacherId = session('LoggedTeacher');
        $today     = Carbon::today()->dayOfWeekIso; // 1=Mon…7=Sun

        $allTimetables = Timetable::where('school_id', $schoolId)
            ->orderBy('status')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($tt) {
                $tt->class_name  = Helper::recordMdname($tt->class_id);
                $tt->stream_name = Helper::recordMdname($tt->stream_id);
                $tt->slot_count  = TimetableSlot::where('timetable_id', $tt->id)->count();
                return $tt;
            });

        $periods = TimetablePeriod::where('school_id', $schoolId)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $activeTimetables = $allTimetables->where('status', 'active');
        $draftTimetables  = $allTimetables->where('status', 'draft');

        // Today's schedule for logged-in teacher
        $todaySchedule = collect();
        if ($teacherId) {
            $todaySchedule = TimetableSlot::where('teacher_id', $teacherId)
                ->where('day_of_week', $today)
                ->whereHas('timetable', fn($q) => $q->where('school_id', $schoolId)->where('status', 'active'))
                ->with(['timetable', 'period'])
                ->get()
                ->sortBy('period.sort_order')
                ->map(function ($slot) {
                    $slot->class_name   = Helper::recordMdname($slot->timetable->class_id);
                    $slot->stream_name  = Helper::recordMdname($slot->timetable->stream_id);
                    $slot->subject_name = Helper::recordMdname($slot->subject_id);
                    return $slot;
                });
        }

        return view('Timetable.dashboard', compact(
            'allTimetables', 'activeTimetables', 'draftTimetables',
            'periods', 'todaySchedule', 'today'
        ));
    }

    // ══════════════════════════════════════════════════════════════
    //  PERIODS Management
    // ══════════════════════════════════════════════════════════════

    public function periods()
    {
        Helper::authorizeTechSateAdminOrSchoolAdmins();

        $schoolId = session('LoggedSchool');
        $periods  = TimetablePeriod::where('school_id', $schoolId)
            ->orderBy('sort_order')
            ->get();

        return view('Timetable.periods', compact('periods'));
    }

    public function storePeriod(Request $request)
    {
        Helper::authorizeTechSateAdminOrSchoolAdmins();

        $request->validate([
            'name'       => 'required|string|max:100',
            'start_time' => 'required',
            'end_time'   => 'required|after:start_time',
            'type'       => 'required|in:lesson,break,lunch,assembly,other',
            'sort_order' => 'required|integer|min:0',
        ]);

        $schoolId = session('LoggedSchool');

        TimetablePeriod::create([
            'school_id'  => $schoolId,
            'name'       => $request->name,
            'start_time' => $request->start_time,
            'end_time'   => $request->end_time,
            'type'       => $request->type,
            'sort_order' => $request->sort_order,
            'is_active'  => true,
        ]);

        return response()->json(['success' => true, 'message' => 'Period added successfully.']);
    }

    public function updatePeriod(Request $request, $id)
    {
        Helper::authorizeTechSateAdminOrSchoolAdmins();

        $request->validate([
            'name'       => 'required|string|max:100',
            'start_time' => 'required',
            'end_time'   => 'required',
            'type'       => 'required|in:lesson,break,lunch,assembly,other',
            'sort_order' => 'required|integer|min:0',
        ]);

        $schoolId = session('LoggedSchool');
        $period   = TimetablePeriod::where('school_id', $schoolId)->findOrFail($id);
        $period->update($request->only(['name', 'start_time', 'end_time', 'type', 'sort_order', 'is_active']));

        return response()->json(['success' => true, 'message' => 'Period updated.']);
    }

    public function destroyPeriod($id)
    {
        Helper::authorizeTechSateAdminOrSchoolAdmins();

        $schoolId = session('LoggedSchool');
        $period   = TimetablePeriod::where('school_id', $schoolId)->findOrFail($id);

        // Check if used
        if (TimetableSlot::where('period_id', $id)->exists()) {
            return response()->json(['success' => false, 'message' => 'Period is used in a timetable and cannot be deleted.'], 409);
        }

        $period->delete();
        return response()->json(['success' => true, 'message' => 'Period deleted.']);
    }

    // ══════════════════════════════════════════════════════════════
    //  TIMETABLE CRUD
    // ══════════════════════════════════════════════════════════════

    public function create()
    {
        Helper::authorizeTechSateAdminOrSchoolAdmins();

        $schoolId     = session('LoggedSchool');
        $classrooms   = Classroom::where('school_id', $schoolId)->orderBy('class_name')->get();
        $academicYears = AcademicYear::orderBy('id', 'desc')->get();

        return view('Timetable.create', compact('classrooms', 'academicYears'));
    }

    public function store(Request $request)
    {
        Helper::authorizeTechSateAdminOrSchoolAdmins();

        $request->validate([
            'class_id'        => 'required|string',
            'stream_id'       => 'required|string',
            'academic_year_id'=> 'nullable|exists:academic_years,id',
            'term'            => 'nullable|string',
            'name'            => 'nullable|string|max:255',
        ]);

        $schoolId = session('LoggedSchool');

        $timetable = Timetable::create([
            'school_id'        => $schoolId,
            'class_id'         => $request->class_id,
            'stream_id'        => $request->stream_id,
            'academic_year_id' => $request->academic_year_id,
            'term'             => $request->term,
            'name'             => $request->name ?? (Helper::recordMdname($request->class_id) . ' – ' . Helper::recordMdname($request->stream_id)),
            'status'           => 'draft',
            'created_by'       => session('LoggedTeacher') ?? session('LoggedAdmin'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Timetable created.',
            'redirect'=> route('timetable.edit', $timetable->id),
        ]);
    }

    // ══════════════════════════════════════════════════════════════
    //  TIMETABLE EDITOR (drag-and-drop grid)
    // ══════════════════════════════════════════════════════════════

    public function edit($id)
    {
        Helper::authorizeTechSateAdminOrSchoolAdmins();

        $schoolId  = session('LoggedSchool');
        $timetable = Timetable::where('school_id', $schoolId)->findOrFail($id);

        $periods  = TimetablePeriod::where('school_id', $schoolId)
            ->orderBy('sort_order')
            ->get();

        $days = [1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday'];

        // All subjects for this class-stream
        $classSubjects = ClassSubject::where('school_id', $schoolId)
            ->where('class_id', $timetable->class_id)
            ->where('stream_id', $timetable->stream_id)
            ->get()
            ->map(function ($cs) {
                $cs->subject_name = Helper::recordMdname($cs->subject_id);
                $cs->teacher1_name = Helper::get_teacher_name($cs->subject_teacher_1 ?? 0);
                return $cs;
            });

        // All teachers in school
        $teachers = Teacher::where('school_id', $schoolId)->orderBy('surname')->get();

        // Existing slots — keyed as day_period
        $slots = TimetableSlot::where('timetable_id', $id)
            ->with('period')
            ->get()
            ->keyBy(fn($s) => $s->day_of_week . '_' . $s->period_id);

        // Color palette per subject
        $palette = ['#5351e4','#10b981','#f59e0b','#ef4444','#3b82f6','#8b5cf6','#ec4899','#06b6d4','#84cc16','#f97316'];

        // Map subject → color
        $subjectColors = [];
        $idx = 0;
        foreach ($classSubjects as $cs) {
            $subjectColors[$cs->subject_id] = $palette[$idx % count($palette)];
            $idx++;
        }

        // Check teacher conflicts across ALL active timetables for this school
        $teacherSlotMap = TimetableSlot::whereHas('timetable', fn($q) => $q->where('school_id', $schoolId)->where('status', 'active')->where('id', '!=', $id))
            ->whereNotNull('teacher_id')
            ->get()
            ->groupBy(fn($s) => $s->teacher_id . '_' . $s->day_of_week . '_' . $s->period_id);

        $className  = Helper::recordMdname($timetable->class_id);
        $streamName = Helper::recordMdname($timetable->stream_id);

        return view('Timetable.edit', compact(
            'timetable', 'periods', 'days', 'classSubjects',
            'teachers', 'slots', 'subjectColors', 'palette',
            'teacherSlotMap', 'className', 'streamName'
        ));
    }

    // ══════════════════════════════════════════════════════════════
    //  SLOT – Save / Update (AJAX)
    // ══════════════════════════════════════════════════════════════

    public function saveSlot(Request $request)
    {
        $request->validate([
            'timetable_id' => 'required|exists:timetables,id',
            'day_of_week'  => 'required|integer|between:1,7',
            'period_id'    => 'required|exists:timetable_periods,id',
        ]);

        $schoolId = session('LoggedSchool');

        // Teacher conflict check
        if ($request->teacher_id) {
            $conflict = TimetableSlot::where('day_of_week', $request->day_of_week)
                ->where('period_id', $request->period_id)
                ->where('teacher_id', $request->teacher_id)
                ->whereHas('timetable', fn($q) => $q->where('school_id', $schoolId)->where('status', 'active'))
                ->where('timetable_id', '!=', $request->timetable_id)
                ->first();

            if ($conflict) {
                $teacherName = Teacher::find($request->teacher_id);
                return response()->json([
                    'success' => false,
                    'message' => "⚠️ Teacher conflict: {$teacherName->firstname} {$teacherName->surname} is already assigned to another class at this time.",
                ], 409);
            }
        }

        $slot = TimetableSlot::updateOrCreate(
            [
                'timetable_id' => $request->timetable_id,
                'day_of_week'  => $request->day_of_week,
                'period_id'    => $request->period_id,
            ],
            [
                'class_subject_id' => $request->class_subject_id ?: null,
                'subject_id'       => $request->subject_id       ?: null,
                'teacher_id'       => $request->teacher_id       ?: null,
                'room'             => $request->room             ?: null,
                'color'            => $request->color            ?? '#5351e4',
                'notes'            => $request->notes            ?: null,
            ]
        );

        return response()->json([
            'success'     => true,
            'message'     => 'Slot saved.',
            'subject_name'=> Helper::recordMdname($request->subject_id),
            'teacher_name'=> $request->teacher_id ? Helper::get_teacher_name($request->teacher_id) : null,
            'slot'        => $slot,
        ]);
    }

    // ══════════════════════════════════════════════════════════════
    //  SLOT – Clear (AJAX)
    // ══════════════════════════════════════════════════════════════

    public function clearSlot(Request $request)
    {
        TimetableSlot::where('timetable_id', $request->timetable_id)
            ->where('day_of_week', $request->day_of_week)
            ->where('period_id', $request->period_id)
            ->delete();

        return response()->json(['success' => true, 'message' => 'Slot cleared.']);
    }

    // ══════════════════════════════════════════════════════════════
    //  TIMETABLE – Update status
    // ══════════════════════════════════════════════════════════════

    public function updateStatus(Request $request, $id)
    {
        Helper::authorizeTechSateAdminOrSchoolAdmins();

        $request->validate(['status' => 'required|in:draft,active,archived']);

        $schoolId  = session('LoggedSchool');
        $timetable = Timetable::where('school_id', $schoolId)->findOrFail($id);

        // If activating, check for teacher conflicts within the school
        if ($request->status === 'active') {
            $conflicts = $this->checkConflicts($timetable);
            if (!empty($conflicts)) {
                return response()->json([
                    'success'   => false,
                    'message'   => 'Cannot activate: teacher conflicts detected.',
                    'conflicts' => $conflicts,
                ], 409);
            }
        }

        $timetable->update(['status' => $request->status]);

        return response()->json(['success' => true, 'message' => 'Status updated to ' . $request->status . '.']);
    }

    // ══════════════════════════════════════════════════════════════
    //  TIMETABLE – Delete
    // ══════════════════════════════════════════════════════════════

    public function destroy($id)
    {
        Helper::authorizeTechSateAdminOrSchoolAdmins();

        $schoolId  = session('LoggedSchool');
        $timetable = Timetable::where('school_id', $schoolId)->findOrFail($id);

        DB::beginTransaction();
        TimetableSlot::where('timetable_id', $id)->delete();
        $timetable->delete();
        DB::commit();

        return response()->json(['success' => true, 'message' => 'Timetable deleted.']);
    }

    // ══════════════════════════════════════════════════════════════
    //  TIMETABLE – View (read-only, shareable)
    // ══════════════════════════════════════════════════════════════

    public function view($id)
    {
        $schoolId  = session('LoggedSchool');
        $timetable = Timetable::where('school_id', $schoolId)->findOrFail($id);

        $periods = TimetablePeriod::where('school_id', $schoolId)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $days = [1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday'];

        $slots = TimetableSlot::where('timetable_id', $id)
            ->with(['period', 'teacher'])
            ->get()
            ->keyBy(fn($s) => $s->day_of_week . '_' . $s->period_id);

        $className  = Helper::recordMdname($timetable->class_id);
        $streamName = Helper::recordMdname($timetable->stream_id);

        return view('Timetable.view', compact(
            'timetable', 'periods', 'days', 'slots', 'className', 'streamName'
        ));
    }

    // ══════════════════════════════════════════════════════════════
    //  TEACHER TIMETABLE VIEW
    // ══════════════════════════════════════════════════════════════

    public function teacherTimetable()
    {
        $schoolId  = session('LoggedSchool');
        $teacherId = session('LoggedTeacher');

        $periods = TimetablePeriod::where('school_id', $schoolId)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $days = [1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday'];

        // Get all active timetable slots for this teacher
        $slots = TimetableSlot::where('teacher_id', $teacherId)
            ->whereHas('timetable', fn($q) => $q->where('school_id', $schoolId)->where('status', 'active'))
            ->with(['timetable', 'period'])
            ->get()
            ->map(function ($slot) {
                $slot->class_name   = Helper::recordMdname($slot->timetable->class_id);
                $slot->stream_name  = Helper::recordMdname($slot->timetable->stream_id);
                $slot->subject_name = Helper::recordMdname($slot->subject_id);
                return $slot;
            })
            ->keyBy(fn($s) => $s->day_of_week . '_' . $s->period_id);

        // Weekly hours count
        $weeklyHours = TimetableSlot::where('teacher_id', $teacherId)
            ->whereHas('timetable', fn($q) => $q->where('school_id', $schoolId)->where('status', 'active'))
            ->whereHas('period', fn($q) => $q->where('type', 'lesson'))
            ->count();

        return view('Timetable.teacher-view', compact(
            'periods', 'days', 'slots', 'weeklyHours'
        ));
    }

    // ══════════════════════════════════════════════════════════════
    //  DUPLICATE TIMETABLE
    // ══════════════════════════════════════════════════════════════

    public function duplicate($id)
    {
        Helper::authorizeTechSateAdminOrSchoolAdmins();

        $schoolId  = session('LoggedSchool');
        $original  = Timetable::where('school_id', $schoolId)->findOrFail($id);

        DB::beginTransaction();
        try {
            $copy = $original->replicate();
            $copy->name   = $original->name . ' (Copy)';
            $copy->status = 'draft';
            $copy->save();

            foreach ($original->slots as $slot) {
                $newSlot = $slot->replicate();
                $newSlot->timetable_id = $copy->id;
                $newSlot->save();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Timetable duplicated.',
                'redirect'=> route('timetable.edit', $copy->id),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ══════════════════════════════════════════════════════════════
    //  CONFLICT CHECKER
    // ══════════════════════════════════════════════════════════════

    private function checkConflicts(Timetable $timetable): array
    {
        $conflicts = [];
        $slots = TimetableSlot::where('timetable_id', $timetable->id)->whereNotNull('teacher_id')->get();

        foreach ($slots as $slot) {
            $conflict = TimetableSlot::where('day_of_week', $slot->day_of_week)
                ->where('period_id', $slot->period_id)
                ->where('teacher_id', $slot->teacher_id)
                ->whereHas('timetable', fn($q) => $q->where('school_id', $timetable->school_id)->where('status', 'active')->where('id', '!=', $timetable->id))
                ->first();

            if ($conflict) {
                $teacher = Teacher::find($slot->teacher_id);
                $conflicts[] = [
                    'teacher' => $teacher ? $teacher->firstname . ' ' . $teacher->surname : 'Unknown',
                    'day'     => Timetable::dayName($slot->day_of_week),
                    'period'  => TimetablePeriod::find($slot->period_id)?->name,
                ];
            }
        }

        return $conflicts;
    }

    // ══════════════════════════════════════════════════════════════
    //  AJAX: Get slot details
    // ══════════════════════════════════════════════════════════════

    public function getSlot(Request $request)
    {
        $slot = TimetableSlot::where('timetable_id', $request->timetable_id)
            ->where('day_of_week', $request->day)
            ->where('period_id', $request->period)
            ->with(['teacher', 'period'])
            ->first();

        if (!$slot) return response()->json(['exists' => false]);

        return response()->json([
            'exists'        => true,
            'subject_id'    => $slot->subject_id,
            'subject_name'  => Helper::recordMdname($slot->subject_id),
            'teacher_id'    => $slot->teacher_id,
            'teacher_name'  => $slot->teacher ? $slot->teacher->firstname . ' ' . $slot->teacher->surname : null,
            'room'          => $slot->room,
            'color'         => $slot->color,
            'notes'         => $slot->notes,
            'class_subject_id' => $slot->class_subject_id,
        ]);
    }
}