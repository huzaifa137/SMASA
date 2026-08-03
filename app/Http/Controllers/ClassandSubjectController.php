<?php

namespace App\Http\Controllers;

use App\Helpers\PermissionHelper;
use App\Models\Classroom;
use App\Models\ClassStreamAssignment;
use App\Models\ClassSubject;
use App\Models\CustomSubject;
use App\Models\School;
use App\Models\Student;
use App\Models\Stream;
use App\Models\Classes;
use App\Models\Teacher;
use App\Http\Controllers\Helper;
use DB;
use Illuminate\Http\Request;

class ClassandSubjectController extends Controller
{
    /**
     * Sentinel stream_id used for classes created with "no streams". Using a
     * real, consistent string (instead of NULL) means every existing query
     * in the app that does ->where('stream_id', $x) across Attendance,
     * Exams, Timetable, Finance, etc. keeps working unmodified for
     * streamless classes — a Stream row simply exists with this id.
     */
    public const NO_STREAM_SENTINEL = 'NO_STREAM';

    public function createClass()
    {
        PermissionHelper::denyUnlessFeature('add_class');

        $school = School::find(Helper::requireSchool());

        // Schools that have switched to their own subject list get a
        // simpler, dynamic form instead of the fixed master-data one below.
        // Everything else in this method (and every other school) is
        // completely unaffected.
        if ($school && $school->usesCustomSubjects()) {
            return $this->createClassCustom($school);
        }

        $schoolProduct = Helper::recordMdname(Helper::schoolProducts());
        $SecondaryClasses = collect();
        $PrimaryClasses = collect();
        $classTypeMap = [];

        // Fetch Primary Theology Subjects
        $primaryTheology = Helper::MasterRecords(config('constants.options.PRIMARY_THEOLOGY'));

        // Fetch Primary Secular Subjects
        $primarySecularSubjects = [
            config('constants.options.NURSERY_BABY_CLASS') => Helper::MasterRecords(config('constants.options.NURSERY_BABY_CLASS')),
            config('constants.options.NURSERY_MIDDLE_CLASS') => Helper::MasterRecords(config('constants.options.NURSERY_MIDDLE_CLASS')),
            config('constants.options.NURSERY_TOP_CLASS') => Helper::MasterRecords(config('constants.options.NURSERY_TOP_CLASS')),
            config('constants.options.LOWER_PRIMARY_P1') => Helper::MasterRecords(config('constants.options.LOWER_PRIMARY_P1')),
            config('constants.options.LOWER_PRIMARY_P2') => Helper::MasterRecords(config('constants.options.LOWER_PRIMARY_P2')),
            config('constants.options.LOWER_PRIMARY_P3') => Helper::MasterRecords(config('constants.options.LOWER_PRIMARY_P3')),
            config('constants.options.UPPER_PRIMARY_P4_P7') => Helper::MasterRecords(config('constants.options.UPPER_PRIMARY_P4_P7')),
        ];

        if ($schoolProduct === 'Idaad And Thanawi') {
            $SecondaryClasses = Helper::MasterRecordMerge(
                config('constants.options.O_LEVEL'),
                config('constants.options.A_LEVEL')
            );

            $oLevelClasses = Helper::MasterRecords(config('constants.options.O_LEVEL'));
            $oLevelIds = $oLevelClasses->pluck('md_id')->toArray();
            $aLevelClasses = Helper::MasterRecords(config('constants.options.A_LEVEL'));
            $aLevelIds = $aLevelClasses->pluck('md_id')->toArray();

            foreach ($SecondaryClasses as $class) {
                if (in_array($class->md_id, $oLevelIds)) {
                    $classTypeMap[$class->md_id] = 'O-Level';
                } elseif (in_array($class->md_id, $aLevelIds)) {
                    $classTypeMap[$class->md_id] = 'A-Level';
                } else {
                    $classTypeMap[$class->md_id] = 'Unknown';
                }
            }

            $IDAAD_ARABIC_LANGUAGE = Helper::MasterRecords(config('constants.options.IDAAD_ARABIC_LANGUAGE'));
            $IDAAD_FAITH_AND_CIVILIZATION = Helper::MasterRecords(config('constants.options.IDAAD_FAITH_AND_CIVILIZATION'));
            $IDAAD_JURISPRUDENCE_AND_ITS_SOURCES = Helper::MasterRecords(config('constants.options.IDAAD_JURISPRUDENCE_AND_ITS_SOURCES'));
            $IDAAD_PROPHETIC_TRADITIONS = Helper::MasterRecords(config('constants.options.IDAAD_PROPHETIC_TRADITIONS'));
            $IDAAD_QURAN_ITS_SCIENCES = Helper::MasterRecords(config('constants.options.IDAAD_QURAN_ITS_SCIENCES'));

            $THANAWI_ARABIC_LANGUAGE = Helper::MasterRecords(config('constants.options.THANAWI_ARABIC_LANGUAGE'));
            $THANAWI_FAITH_AND_CIVILIZATION = Helper::MasterRecords(config('constants.options.THANAWI_FAITH_AND_CIVILIZATION'));
            $THANAWI_JURISPRUDENCE_AND_ITS_SOURCES = Helper::MasterRecords(config('constants.options.THANAWI_JURISPRUDENCE_AND_ITS_SOURCES'));
            $THANAWI_PROPHETIC_TRADITIONS = Helper::MasterRecords(config('constants.options.THANAWI_PROPHETIC_TRADITIONS'));
            $THANAWI_QURAN_ITS_SCIENCES = Helper::MasterRecords(config('constants.options.THANAWI_QURAN_ITS_SCIENCES'));

            return view('Class.create-class', compact(
                'SecondaryClasses',
                'classTypeMap',
                'IDAAD_ARABIC_LANGUAGE',
                'IDAAD_FAITH_AND_CIVILIZATION',
                'IDAAD_JURISPRUDENCE_AND_ITS_SOURCES',
                'IDAAD_PROPHETIC_TRADITIONS',
                'IDAAD_QURAN_ITS_SCIENCES',
                'THANAWI_ARABIC_LANGUAGE',
                'THANAWI_FAITH_AND_CIVILIZATION',
                'THANAWI_JURISPRUDENCE_AND_ITS_SOURCES',
                'THANAWI_PROPHETIC_TRADITIONS',
                'THANAWI_QURAN_ITS_SCIENCES',
            ));
        } elseif ($schoolProduct === 'Primary Theology') {
            $PrimaryClasses = Helper::MasterRecords(config('constants.options.PRIMARY_THEOLOGY_CLASSES'));

            foreach ($PrimaryClasses as $class) {
                $classTypeMap[$class->md_id] = 'Primary Theology';
            }

            return view('Class.create-class', compact(
                'PrimaryClasses',
                'classTypeMap',
                'primaryTheology'
            ));
        } elseif ($schoolProduct === 'Primary Secular') {
            $PrimaryClasses = Helper::MasterRecords(config('constants.options.PRIMARY_SECULAR_CLASSES'));

            foreach ($PrimaryClasses as $class) {
                $classTypeMap[$class->md_id] = 'Primary Secular';
            }

            return view('Class.create-class', compact(
                'PrimaryClasses',
                'classTypeMap',
                'primarySecularSubjects'
            ));
        } elseif ($schoolProduct === 'Both Primary Theology and Secular') {
            $PrimaryTheologyClasses = Helper::MasterRecords(config('constants.options.PRIMARY_THEOLOGY_CLASSES'));
            $PrimarySecularClasses = Helper::MasterRecords(config('constants.options.PRIMARY_SECULAR_CLASSES'));

            // Merge Primary Theology and Primary Secular Classes
            $PrimaryClasses = $PrimaryTheologyClasses->merge($PrimarySecularClasses);

            foreach ($PrimaryClasses as $class) {
                $theologyIds = Helper::MasterRecords(config('constants.options.PRIMARY_THEOLOGY_CLASSES'))->pluck('md_id')->toArray();
                $secularIds = Helper::MasterRecords(config('constants.options.PRIMARY_SECULAR_CLASSES'))->pluck('md_id')->toArray();

                if (in_array($class->md_id, $theologyIds)) {
                    $classTypeMap[$class->md_id] = 'Primary Theology';
                } elseif (in_array($class->md_id, $secularIds)) {
                    $classTypeMap[$class->md_id] = 'Primary Secular';
                }
            }

            return view('Class.create-class', compact(
                'PrimaryClasses',
                'classTypeMap',
                'primaryTheology',
                'primarySecularSubjects'
            ));
        }
    }
    /**
     * Create-class screen for schools that define their own subjects.
     * Classes still come from the same shared class taxonomy (Secondary /
     * Primary Theology / Primary Secular class names don't change — only
     * the subjects attached to them do), but subjects are pulled from this
     * school's own custom_subjects instead of the shared master list.
     */
    private function createClassCustom(School $school)
    {
        $schoolProduct = Helper::recordMdname(Helper::schoolProducts());
        $classTypeMap = [];
        $SecondaryClasses = collect();
        $PrimaryClasses = collect();

        if ($schoolProduct === 'Idaad And Thanawi') {
            $SecondaryClasses = Helper::MasterRecordMerge(
                config('constants.options.O_LEVEL'),
                config('constants.options.A_LEVEL')
            );

            $oLevelIds = Helper::MasterRecords(config('constants.options.O_LEVEL'))->pluck('md_id')->toArray();
            $aLevelIds = Helper::MasterRecords(config('constants.options.A_LEVEL'))->pluck('md_id')->toArray();

            foreach ($SecondaryClasses as $class) {
                $classTypeMap[$class->md_id] = in_array($class->md_id, $oLevelIds) ? 'O-Level' : (in_array($class->md_id, $aLevelIds) ? 'A-Level' : 'Unknown');
            }
        } elseif ($schoolProduct === 'Primary Theology') {
            $PrimaryClasses = Helper::MasterRecords(config('constants.options.PRIMARY_THEOLOGY_CLASSES'));
            foreach ($PrimaryClasses as $class) {
                $classTypeMap[$class->md_id] = 'Primary Theology';
            }
        } elseif ($schoolProduct === 'Primary Secular') {
            $PrimaryClasses = Helper::MasterRecords(config('constants.options.PRIMARY_SECULAR_CLASSES'));
            foreach ($PrimaryClasses as $class) {
                $classTypeMap[$class->md_id] = 'Primary Secular';
            }
        } elseif ($schoolProduct === 'Both Primary Theology and Secular') {
            $theology = Helper::MasterRecords(config('constants.options.PRIMARY_THEOLOGY_CLASSES'));
            $secular = Helper::MasterRecords(config('constants.options.PRIMARY_SECULAR_CLASSES'));
            $PrimaryClasses = $theology->merge($secular);

            $theologyIds = $theology->pluck('md_id')->toArray();
            $secularIds = $secular->pluck('md_id')->toArray();

            foreach ($PrimaryClasses as $class) {
                $classTypeMap[$class->md_id] = in_array($class->md_id, $theologyIds) ? 'Primary Theology' : 'Primary Secular';
            }
        }

        $customSubjectsByType = CustomSubject::forSchool($school->id)
            ->active()
            ->orderBy('subject_name')
            ->get()
            ->groupBy('class_type');

        return view('Class.create-class-custom', compact(
            'SecondaryClasses',
            'PrimaryClasses',
            'classTypeMap',
            'customSubjectsByType'
        ));
    }

    public function storeClass(Request $request)
    {
        PermissionHelper::denyUnlessFeature('add_class');

        $request->validate([
            'class_id' => 'required',
            'class_stream' => 'required_unless:no_stream,1',
            'no_stream' => 'nullable|boolean',
            'subjects' => 'required|array|min:1',
            'subjects.*' => 'required',
            'class_type' => 'required|in:O-Level,A-Level,Primary Theology,Primary Secular'
        ]);

        // A class can be created without any streams. We still store a real
        // Stream/ClassStreamAssignment row using a fixed sentinel id so that
        // every other part of the system (attendance, exams, timetable,
        // finance, etc.) that expects a stream_id keeps working unchanged.
        $streamId = $request->boolean('no_stream') ? self::NO_STREAM_SENTINEL : $request->class_stream;

        $school = School::find(Session('LoggedSchool'));
        $usesCustomSubjects = $school && $school->usesCustomSubjects();

        $classRecord = Classroom::where('class_name', $request->class_id)
            ->where('school_id', Session('LoggedSchool'))
            ->first();

        $StreamRecord = Stream::where('class_id', $request->class_id)
            ->where('stream_id', $streamId)
            ->where('school_id', Session('LoggedSchool'))
            ->first();

        if ($classRecord === null) {
            $class = new Classroom;
            $class->school_id = Session('LoggedSchool');
            $class->class_name = $request->class_id;
            $class->added_by = Session('LoggedAdmin');
            $class->date_added = now();
            $class->save();
        }

        if ($StreamRecord === null) {
            $stream = new Stream;
            $stream->school_id = Session('LoggedSchool');
            $stream->class_id = $request->class_id;
            $stream->stream_id = $streamId;
            $stream->added_by = Session('LoggedAdmin');
            $stream->date_added = now();
            $stream->save();

            $classStreamAssignment = ClassStreamAssignment::create([
                'class_id' => $request->class_id,
                'stream_id' => $streamId,
                'school_id' => Session('LoggedSchool'),
                'added_by' => Session('LoggedAdmin'),
                'date_added' => now(),
            ]);

            // Save the selected subjects with the correct subject_type
            foreach ($request->subjects as $subjectId) {
                $subjectType = '';
                if ($request->class_type === 'O-Level') {
                    $subjectType = 'idaad';
                } elseif ($request->class_type === 'A-Level') {
                    $subjectType = 'thanawi';
                } elseif ($request->class_type === 'Primary Theology') {
                    $subjectType = 'primary_theology';
                } elseif ($request->class_type === 'Primary Secular') {
                    $subjectType = 'primary_secular';
                }

                if ($usesCustomSubjects) {
                    ClassSubject::create([
                        'class_id' => $request->class_id,
                        'stream_id' => $streamId,
                        'custom_subject_id' => $subjectId,
                        'subject_source' => 'custom',
                        'subject_type' => $subjectType,
                        'school_id' => Session('LoggedSchool'),
                    ]);
                } else {
                    ClassSubject::create([
                        'class_id' => $request->class_id,
                        'stream_id' => $streamId,
                        'subject_id' => $subjectId,
                        'subject_source' => 'master',
                        'subject_type' => $subjectType,
                        'school_id' => Session('LoggedSchool'),
                    ]);
                }
            }

            return response()->json(['success' => true, 'message' => 'Class created successfully.']);

        } else {
            return response()->json(['fail' => true, 'message' => 'Stream already exists for this class.']);
        }
    }
    public function updateClassSubjects(Request $request, $id)
    {
        PermissionHelper::denyUnlessFeature('edit_class');

        $assignment = ClassStreamAssignment::findOrFail($id);

        // Validate request
        $request->validate([
            'subjects' => 'required|array|min:1',
            'subjects.*' => 'required'
        ]);

        $school = School::find(session('LoggedSchool'));
        $usesCustomSubjects = $school && $school->usesCustomSubjects();

        // Delete old subjects
        ClassSubject::where('class_id', $assignment->class_id)
            ->where('stream_id', $assignment->stream_id)
            ->where('school_id', session('LoggedSchool'))
            ->delete();

        // Determine subject type based on class level
        $oLevelIds = Helper::MasterRecords(config('constants.options.O_LEVEL'))->pluck('md_id')->toArray();
        $primaryTheologyIds = Helper::MasterRecords(config('constants.options.PRIMARY_THEOLOGY_CLASSES'))->pluck('md_id')->toArray();
        $primarySecularIds = Helper::MasterRecords(config('constants.options.PRIMARY_SECULAR_CLASSES'))->pluck('md_id')->toArray();

        if (in_array($assignment->class_id, $oLevelIds)) {
            $subjectType = 'idaad';
        } elseif (in_array($assignment->class_id, $primaryTheologyIds)) {
            $subjectType = 'primary_theology';
        } elseif (in_array($assignment->class_id, $primarySecularIds)) {
            $subjectType = 'primary_secular';
        } else {
            $subjectType = 'thanawi';
        }

        // Insert new subjects
        foreach ($request->subjects as $subjectId) {
            if ($usesCustomSubjects) {
                ClassSubject::create([
                    'class_id' => $assignment->class_id,
                    'stream_id' => $assignment->stream_id,
                    'custom_subject_id' => $subjectId,
                    'subject_source' => 'custom',
                    'subject_type' => $subjectType,
                    'school_id' => session('LoggedSchool'),
                ]);
            } else {
                ClassSubject::create([
                    'class_id' => $assignment->class_id,
                    'stream_id' => $assignment->stream_id,
                    'subject_id' => $subjectId,
                    'subject_source' => 'master',
                    'subject_type' => $subjectType,
                    'school_id' => session('LoggedSchool'),
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Subjects updated successfully.'
        ]);
    }

    public function manageClasses()
    {
        PermissionHelper::denyUnlessFeature('edit_class');

        Helper::requireSchool();
        $classRecord = Classroom::where('school_id', Helper::requireSchool())->orderBy('class_name', 'Asc')->get();

        $Teachers = Teacher::with('school')
            ->where('school_id', Helper::requireSchool())
            ->get();

        return view('Class.manage-classes', compact('classRecord', 'Teachers'));
    }

    /**
     * One consolidated screen for assigning Class Supervisors, Class
     * Teachers, and Subject Teachers, so a school admin can make several
     * assignment changes and save them all in one action instead of one
     * dropdown change (and one page reload) at a time.
     *
     * This intentionally reuses the exact same assign/remove endpoints and
     * business rules already in place (assignSupervisor, assignClassTeacher,
     * assignSubjectTeacher1/2 and their remove counterparts) — nothing about
     * those routes or their validation changes. This screen just lets the
     * browser fire the right sequence of calls to those existing endpoints
     * on a single "Save" click.
     */
    public function teacherAssignments()
    {
        if (!PermissionHelper::canFeature('assign_class_teacher') && !PermissionHelper::canFeature('assign_subject_teachers')) {
            abort(403, 'Unauthorized Access. Contact School Admin to Assign Teachers.');
        }

        $schoolId = Helper::requireSchool();

        $classRecord = Classroom::where('school_id', $schoolId)->orderBy('class_name', 'Asc')->get();
        $Teachers = Teacher::where('school_id', $schoolId)->orderBy('surname', 'Asc')->get();

        $classesData = $classRecord->map(function ($classroom) use ($schoolId) {
            $streams = Stream::where('class_id', $classroom->class_name)
                ->where('school_id', $schoolId)
                ->orderBy('stream_id', 'Asc')
                ->get()
                ->map(function ($stream) use ($schoolId) {
                    $stream->subjects = ClassSubject::where('class_id', $stream->class_id)
                        ->where('stream_id', $stream->stream_id)
                        ->where('school_id', $schoolId)
                        ->get();

                    return $stream;
                });

            return [
                'classroom' => $classroom,
                'streams' => $streams,
            ];
        });

        return view('Class.assign-teachers', compact('classesData', 'Teachers'));
    }

    public function destroyClass($id)
    {
        PermissionHelper::denyUnlessFeature('delete_class');

        $class = Classroom::findOrFail($id);
        $class_id = $class->class_name;

        $streams = Stream::where('class_id', $class_id)->where('school_id', Helper::requireSchool())->get();

        foreach ($streams as $stream) {

            ClassSubject::where('class_id', $class_id)->where('stream_id', $stream->stream_id)->where('school_id', Helper::requireSchool())->delete();

            Student::where('senior', $class->class_name)
                ->where('stream', $stream->stream_name)
                ->where('school_id', Helper::requireSchool())
                ->delete();
        }

        Stream::where('class_id', $class_id)->where('school_id', Helper::requireSchool())->delete();
        ClassStreamAssignment::where('school_id', Helper::requireSchool())->where('class_id', $class_id)->delete();
        Classes::where('school_id', Helper::requireSchool())->where('class_id', $class_id)->delete();
        $class->delete();

        return response()->json(['message' => 'Class, its streams, students, and subjects have been deleted successfully.']);
    }

    public function deleteStream(Stream $stream)
    {
        PermissionHelper::denyUnlessFeature('manage_streams');

        $class_id = $stream->class_id;
        $stream_id = $stream->stream_id;

        Stream::where('class_id', $class_id)->where('stream_id', $stream_id)->where('school_id', Helper::requireSchool())->delete();
        ClassSubject::where('class_id', $class_id)->where('stream_id', $stream_id)->where('school_id', Helper::requireSchool())->delete();
        ClassStreamAssignment::where('school_id', Helper::requireSchool())->where('class_id', $class_id)->where('stream_id', $stream_id)->delete();

        try {
            $stream->delete();

            return response()->json(['status' => 'success', 'message' => 'Stream deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Failed to delete stream: ' . $e->getMessage()], 500);
        }
    }

    public function assignSupervisor(Request $request)
    {
        if (!PermissionHelper::canFeature('assign_class_teacher')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized Access. Contact School Admin to Assign Class Supervisor'
            ], 403);
        }

        $request->validate([
            'class_id' => 'required|exists:classrooms,id',
            'teacher_id' => 'required|exists:teachers,id',
        ]);

        $classroom = Classroom::find($request->class_id);

        if ($classroom->class_supervisor !== null && $classroom->class_supervisor != $request->teacher_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Supervisor already assigned to another teacher.',
            ]);
        }

        $classroom->class_supervisor = $request->teacher_id;
        $classroom->save();

        return response()->json(['status' => 'success']);
    }

    public function removeSupervisor(Request $request)
    {
        if (!PermissionHelper::canFeature('assign_class_teacher')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized Access. Contact School Admin to Remove Class Supervisor'
            ], 403);
        }

        $request->validate([
            'class_id' => 'required|exists:classrooms,id',
        ]);

        $classroom = Classroom::find($request->class_id);

        if (!$classroom->class_supervisor) {
            return response()->json(['status' => 'error', 'message' => 'No supervisor to remove.']);
        }

        $classroom->class_supervisor = null;
        $classroom->save();

        return response()->json(['status' => 'success']);
    }

    public function assignSubjectTeacher1(Request $request)
    {
        if (!PermissionHelper::canFeature('assign_subject_teachers')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized Access. Contact School Admin to Assign Subject Supervisor'
            ], 403);
        }

        $request->validate([
            'subject_id' => 'required|exists:class_subjects,id',
            'teacher_id' => 'required|exists:teachers,id',
        ]);

        $subject = ClassSubject::find($request->subject_id);

        if ($subject->subject_teacher_1 !== null && $subject->subject_teacher_1 != $request->teacher_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Subject Teacher already assigned to another teacher.',
            ]);
        }

        $subject->subject_teacher_1 = $request->teacher_id;
        $subject->save();

        return response()->json(['status' => 'success']);
    }

    public function removeSubjectTeacher1(Request $request)
    {
        if (!PermissionHelper::canFeature('assign_subject_teachers')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized Access. Contact School Admin to Assign Subject Supervisor'
            ], 403);
        }

        $request->validate([
            'subject_id' => 'required|exists:class_subjects,id',
        ]);

        $subject = ClassSubject::find($request->subject_id);

        if (!$subject->subject_teacher_1) {
            return response()->json(['status' => 'error', 'message' => 'No Subject Teacher to remove.']);
        }

        $subject->subject_teacher_1 = null;
        $subject->save();

        return response()->json(['status' => 'success']);
    }

    public function assignSubjectTeacher2(Request $request)
    {
        if (!PermissionHelper::canFeature('assign_subject_teachers')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized Access. Contact School Admin to Assign Subject Supervisor'
            ], 403);
        }

        $request->validate([
            'subject_id' => 'required|exists:class_subjects,id',
            'teacher_id' => 'required|exists:teachers,id',
        ]);

        $subject = ClassSubject::find($request->subject_id);

        if ($subject->subject_teacher_2 !== null && $subject->subject_teacher_2 != $request->teacher_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Subject Teacher already assigned to another teacher.',
            ]);
        }

        $subject->subject_teacher_2 = $request->teacher_id;
        $subject->save();

        return response()->json(['status' => 'success']);
    }

    public function removeSubjectTeacher2(Request $request)
    {
        if (!PermissionHelper::canFeature('assign_subject_teachers')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized Access. Contact School Admin to Assign Subject Supervisor'
            ], 403);
        }

        $request->validate([
            'subject_id' => 'required|exists:class_subjects,id',
        ]);

        $subject = ClassSubject::find($request->subject_id);

        if (!$subject->subject_teacher_2) {
            return response()->json(['status' => 'error', 'message' => 'No Subject Teacher to remove.']);
        }

        $subject->subject_teacher_2 = null;
        $subject->save();

        return response()->json(['status' => 'success']);
    }

    public function manageClassStreams($class_id)
    {
        PermissionHelper::denyUnlessFeature('manage_streams');

        $Streams = DB::table('streams')->where('class_id', $class_id)->where('school_id', Helper::requireSchool())->orderBy('stream_id', 'Asc')->get();

        $Teachers = Teacher::with('school')
            ->where('school_id', Session('LoggedSchool'))
            ->get();

        return view('Class.class-streams', compact(['Streams', 'Teachers', 'class_id']));
    }

    public function assignClassTeacher(Request $request)
    {
        if (!PermissionHelper::canFeature('assign_class_teacher')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized Access. Contact School Admin to Assign Subject Supervisor'
            ], 403);
        }

        $request->validate([
            'class_id' => 'required|exists:streams,id',
            'teacher_id' => 'required|exists:teachers,id',
        ]);

        $stream = Stream::find($request->class_id);

        if ($stream->class_teacher !== null && $stream->class_teacher != $request->teacher_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Class Teacher already assigned to another teacher.',
            ]);
        }

        $stream->class_teacher = $request->teacher_id;
        $stream->save();

        return response()->json(['status' => 'success']);
    }

    public function removeClassTeacher(Request $request)
    {
        if (!PermissionHelper::canFeature('assign_class_teacher')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized Access. Contact School Admin to Remove Class Teacher'
            ], 403);
        }

        $request->validate([
            'class_id' => 'required|exists:streams,id',
        ]);

        $stream = Stream::find($request->class_id);

        if (!$stream->class_teacher) {
            return response()->json(['status' => 'error', 'message' => 'No Class Teacher to remove.']);
        }

        $stream->class_teacher = null;
        $stream->save();

        return response()->json(['status' => 'success']);
    }

    public function attachedStreamSubjects($classId, $streamId)
    {
        PermissionHelper::denyUnlessFeature('view_classes');

        $assignment = ClassStreamAssignment::where('class_id', $classId)
            ->where('stream_id', $streamId)
            ->where('school_id', Session('LoggedSchool'))
            ->first();

        $classSubjects = collect(); // empty collection by default
        $groupedSubjects = collect();

        if ($assignment) {
            // Fetch subjects directly using class_id + stream_id
            $classSubjects = ClassSubject::where('class_id', $classId)
                ->where('stream_id', $streamId)
                ->where('school_id', Session('LoggedSchool'))
                ->get();

            $groupedSubjects = $classSubjects->groupBy('subject_type');
        }

        $Teachers = Teacher::with('school')
            ->where('school_id', Session('LoggedSchool'))
            ->get();

        // Active assessment scales this school has defined (e.g. Early
        // Years 1-3), offered as an "Assessment Type" option per subject.
        $assessmentScales = \App\Models\AssessmentScale::availableTo(Session('LoggedSchool'))
            ->orderBy('name')
            ->get(['id', 'name', 'min_score', 'max_score']);

        return view('Class.attached-stream-subjects', compact('assignment', 'classSubjects', 'groupedSubjects', 'Teachers', 'classId', 'streamId', 'assessmentScales'));
    }

    public function editClassSubjects($classId, $streamId)
    {
        PermissionHelper::denyUnlessFeature('edit_class');

        $school = School::find(Helper::requireSchool());

        if ($school && $school->usesCustomSubjects()) {
            return $this->editClassSubjectsCustom($school, $classId, $streamId);
        }

        $assignment = ClassStreamAssignment::with([
            'classSubjects' => function ($query) use ($classId, $streamId) {
                $query->where('stream_id', $streamId)
                    ->where('class_id', $classId)
                    ->where('school_id', session('LoggedSchool'));
            }
        ])
            ->where('class_id', $classId)
            ->where('stream_id', $streamId)
            ->first();

        if (!$assignment) {
            return redirect()->back()->with('error', 'Class-Stream Assignment not found.');
        }

        // Get assigned subjects
        $assignedSubjects = [];
        foreach ($assignment->classSubjects as $classSubject) {
            $assignedSubjects[] = $classSubject->subject_id;
        }

        // Get class type (O-Level, A-Level, Primary Theology, or Primary Secular)
        $oLevelIds = Helper::MasterRecords(config('constants.options.O_LEVEL'))->pluck('md_id')->toArray();
        $primaryTheologyIds = Helper::MasterRecords(config('constants.options.PRIMARY_THEOLOGY_CLASSES'))->pluck('md_id')->toArray();
        $primarySecularIds = Helper::MasterRecords(config('constants.options.PRIMARY_SECULAR_CLASSES'))->pluck('md_id')->toArray();

        if (in_array($classId, $oLevelIds)) {
            $classType = 'O-Level';
        } elseif (in_array($classId, $primaryTheologyIds)) {
            $classType = 'Primary Theology';
        } elseif (in_array($classId, $primarySecularIds)) {
            $classType = 'Primary Secular';
        } else {
            $classType = 'A-Level';
        }

        $SecondaryClasses = Helper::MasterRecordMerge(
            config('constants.options.O_LEVEL'),
            config('constants.options.A_LEVEL')
        );

        $PrimaryClasses = Helper::MasterRecords(config('constants.options.PRIMARY_THEOLOGY_CLASSES'));
        $PrimarySecularClasses = Helper::MasterRecords(config('constants.options.PRIMARY_SECULAR_CLASSES'));

        // IDAAD Subjects (O-Level)
        $IDAAD_ARABIC_LANGUAGE = Helper::MasterRecords(config('constants.options.IDAAD_ARABIC_LANGUAGE'));
        $IDAAD_FAITH_AND_CIVILIZATION = Helper::MasterRecords(config('constants.options.IDAAD_FAITH_AND_CIVILIZATION'));
        $IDAAD_JURISPRUDENCE_AND_ITS_SOURCES = Helper::MasterRecords(config('constants.options.IDAAD_JURISPRUDENCE_AND_ITS_SOURCES'));
        $IDAAD_PROPHETIC_TRADITIONS = Helper::MasterRecords(config('constants.options.IDAAD_PROPHETIC_TRADITIONS'));
        $IDAAD_QURAN_ITS_SCIENCES = Helper::MasterRecords(config('constants.options.IDAAD_QURAN_ITS_SCIENCES'));

        // THANAWI Subjects (A-Level)
        $THANAWI_ARABIC_LANGUAGE = Helper::MasterRecords(config('constants.options.THANAWI_ARABIC_LANGUAGE'));
        $THANAWI_FAITH_AND_CIVILIZATION = Helper::MasterRecords(config('constants.options.THANAWI_FAITH_AND_CIVILIZATION'));
        $THANAWI_JURISPRUDENCE_AND_ITS_SOURCES = Helper::MasterRecords(config('constants.options.THANAWI_JURISPRUDENCE_AND_ITS_SOURCES'));
        $THANAWI_PROPHETIC_TRADITIONS = Helper::MasterRecords(config('constants.options.THANAWI_PROPHETIC_TRADITIONS'));
        $THANAWI_QURAN_ITS_SCIENCES = Helper::MasterRecords(config('constants.options.THANAWI_QURAN_ITS_SCIENCES'));

        // PRIMARY THEOLOGY Subjects
        $primaryTheology = Helper::MasterRecords(config('constants.options.PRIMARY_THEOLOGY'));

        // PRIMARY SECULAR Subjects
        $primarySecularSubjects = [
            config('constants.options.NURSERY_BABY_CLASS') => Helper::MasterRecords(config('constants.options.NURSERY_BABY_CLASS')),
            config('constants.options.NURSERY_MIDDLE_CLASS') => Helper::MasterRecords(config('constants.options.NURSERY_MIDDLE_CLASS')),
            config('constants.options.NURSERY_TOP_CLASS') => Helper::MasterRecords(config('constants.options.NURSERY_TOP_CLASS')),
            config('constants.options.LOWER_PRIMARY_P1') => Helper::MasterRecords(config('constants.options.LOWER_PRIMARY_P1')),
            config('constants.options.LOWER_PRIMARY_P2') => Helper::MasterRecords(config('constants.options.LOWER_PRIMARY_P2')),
            config('constants.options.LOWER_PRIMARY_P3') => Helper::MasterRecords(config('constants.options.LOWER_PRIMARY_P3')),
            config('constants.options.UPPER_PRIMARY_P4_P7') => Helper::MasterRecords(config('constants.options.UPPER_PRIMARY_P4_P7')),
        ];

        return view('Class.edit-class', compact(
            'assignment',
            'assignedSubjects',
            'SecondaryClasses',
            'PrimaryClasses',
            'PrimarySecularClasses',
            'classType',
            'IDAAD_ARABIC_LANGUAGE',
            'IDAAD_FAITH_AND_CIVILIZATION',
            'IDAAD_JURISPRUDENCE_AND_ITS_SOURCES',
            'IDAAD_PROPHETIC_TRADITIONS',
            'IDAAD_QURAN_ITS_SCIENCES',
            'THANAWI_ARABIC_LANGUAGE',
            'THANAWI_FAITH_AND_CIVILIZATION',
            'THANAWI_JURISPRUDENCE_AND_ITS_SOURCES',
            'THANAWI_PROPHETIC_TRADITIONS',
            'THANAWI_QURAN_ITS_SCIENCES',
            'primaryTheology',
            'primarySecularSubjects'
        ));
    }

    /**
     * Edit-subjects screen for schools running in custom-subjects mode.
     */
    private function editClassSubjectsCustom(School $school, $classId, $streamId)
    {
       
        $assignment = ClassStreamAssignment::with([
            'classSubjects' => function ($query) use ($classId, $streamId,$school) {
                $query->where('stream_id', $streamId)
                    ->where('class_id', $classId)
                    ->where('school_id', $school->id);
            }
        ])
            ->where('class_id', $classId)
            ->where('stream_id', $streamId)
            ->first();

        if (!$assignment) {
            return redirect()->back()->with('error', 'Class-Stream Assignment not found.');
        }

        $assignedSubjectIds = $assignment->classSubjects
            ->where('subject_source', 'custom')
            ->pluck('custom_subject_id')
            ->toArray();

        $oLevelIds = Helper::MasterRecords(config('constants.options.O_LEVEL'))->pluck('md_id')->toArray();
        $primaryTheologyIds = Helper::MasterRecords(config('constants.options.PRIMARY_THEOLOGY_CLASSES'))->pluck('md_id')->toArray();
        $primarySecularIds = Helper::MasterRecords(config('constants.options.PRIMARY_SECULAR_CLASSES'))->pluck('md_id')->toArray();

        if (in_array($classId, $oLevelIds)) {
            $classType = 'idaad';
        } elseif (in_array($classId, $primaryTheologyIds)) {
            $classType = 'primary_theology';
        } elseif (in_array($classId, $primarySecularIds)) {
            $classType = 'primary_secular';
        } else {
            $classType = 'thanawi';
        }

        $customSubjects = CustomSubject::forSchool($school->id)
            ->ofType($classType)
            ->active()
            ->orderBy('subject_name')
            ->get();

        return view('Class.edit-class-custom', compact(
            'assignment',
            'assignedSubjectIds',
            'customSubjects',
            'classType',
            'classId',
            'streamId'
        ));
    }

    public function getStreams($senior)
    {
        Helper::requireSchool();

        $streams = Stream::where('class_id', $senior)
            ->where('school_id', Helper::requireSchool())
            ->get(['stream_id', 'stream_id']);

        return response()->json(['streams' => $streams]);
    }
    public function update(Request $request, $assignmentId)
    {
        // Find the existing assignment
        $assignment = ClassStreamAssignment::find($assignmentId);

        if (!$assignment) {
            return redirect()->back()->with('error', 'Class-Stream Assignment not found.');
        }

        // Validate the incoming request data
        $request->validate([
            'class_id' => 'required|exists:master_data,md_id', // Adjust table/column if different
            'class_stream' => 'required|exists:master_data,md_id', // Adjust table/column if different
            'technical_subjects' => 'array',
            'technical_subjects.*' => 'exists:master_data,md_id', // Adjust table/column if different
            'optionals' => 'array',
            'optionals.*' => 'exists:master_data,md_id',
            'vocationals' => 'array',
            'vocationals.*' => 'exists:master_data,md_id',
            'mathematics' => 'array',
            'mathematics.*' => 'exists:master_data,md_id',
            'languages' => 'array',
            'languages.*' => 'exists:master_data,md_id',
            'sciences' => 'array',
            'sciences.*' => 'exists:master_data,md_id',
            'humanities' => 'array',
            'humanities.*' => 'exists:master_data,md_id',
        ]);

        // Use a database transaction for atomicity
        DB::beginTransaction();

        try {
            // If you chose to allow class_id/stream_id to be editable, you'd update them here:
            // $assignment->class_id = $request->input('class_id');
            // $assignment->stream_id = $request->input('class_stream');
            // $assignment->save(); // Save the main assignment changes if any

            // Delete all existing subjects for this assignment
            // This is a common approach for many-to-many relationships when the entire set changes.
            $assignment->classSubjects()->delete();

            // Define the subject categories and their corresponding database 'subject_type' values
            $subjectCategories = [
                'technical_subjects' => 'technical',
                'optionals' => 'optional',
                'vocationals' => 'vocational',
                'mathematics' => 'mathematics',
                'languages' => 'language',
                'sciences' => 'science',
                'humanities' => 'humanities',
            ];

            // Loop through each subject category and re-insert the newly selected subjects
            foreach ($subjectCategories as $requestKey => $subjectType) {
                // Check if the array of subjects for this category exists in the request
                if ($request->has($requestKey) && is_array($request->input($requestKey))) {
                    foreach ($request->input($requestKey) as $subjectId) {
                        ClassSubject::create([
                            'class_stream_assignment_id' => $assignment->id, // Link to the current assignment
                            'subject_id' => $subjectId,
                            'subject_type' => $subjectType, // Store the category type
                        ]);
                    }
                }
            }

            DB::commit(); // Commit the transaction

            return redirect()->route('your.assignments.index')->with('success', 'Subjects assigned updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack(); // Rollback on error
            // Log the error for debugging
            \Log::error('Error updating subjects for assignment ' . $assignmentId . ': ' . $e->getMessage(), ['request' => $request->all()]);

            return redirect()->back()->with('error', 'Failed to update subjects. Please try again or contact support.');
        }
    }

    public function allMyClasses()
    {
        Helper::requireSchool();

        $teacherId = session('LoggedTeacher');

        // Default empty collections
        $classRecord = collect();
        $Streams = collect();
        $classSubjects = collect();

        // Only query if teacher session exists
        if ($teacherId) {

            $classRecord = Classroom::where('school_id', Helper::requireSchool())
                ->where('class_supervisor', $teacherId)
                ->orderBy('class_name', 'Asc')
                ->get();

            $Streams = DB::table('streams')
                ->where('school_id', Helper::requireSchool())
                ->where('class_teacher', $teacherId)
                ->orderBy('stream_id', 'Asc')
                ->get();

            $classSubjects = ClassSubject::where('subject_teacher_1', $teacherId)
                ->orWhere('subject_teacher_2', $teacherId)
                ->get();
        }

        $Teachers = Teacher::with('school')
            ->where('school_id', Session('LoggedSchool'))
            ->get();

        return view(
            'Class.my-classes',
            compact(
                'classRecord',
                'Teachers',
                'Streams',
                'classSubjects'
            )
        );
    }
}