<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\ClassStreamAssignment;
use App\Models\ClassSubject;
use App\Models\Student;
use App\Models\Stream;
use App\Models\Classes;
use App\Models\Teacher;
use DB;
use Illuminate\Http\Request;

class ClassandSubjectController extends Controller
{
    public function createClass()
    {
        $SecondaryClasses = Helper::MasterRecordMerge(
            config('constants.options.O_LEVEL'),
            config('constants.options.A_LEVEL')
        );

        // Create a mapping of class ID to type
        $classTypeMap = [];

        // Get O-Level class IDs
        $oLevelClasses = Helper::MasterRecords(config('constants.options.O_LEVEL'));
        $oLevelIds = $oLevelClasses->pluck('md_id')->toArray();

        // Get A-Level class IDs  
        $aLevelClasses = Helper::MasterRecords(config('constants.options.A_LEVEL'));
        $aLevelIds = $aLevelClasses->pluck('md_id')->toArray();

        // Map each class to its type
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
            'classTypeMap', // Pass the mapping to view
            'IDAAD_ARABIC_LANGUAGE',
            'IDAAD_FAITH_AND_CIVILIZATION',
            'IDAAD_JURISPRUDENCE_AND_ITS_SOURCES',
            'IDAAD_PROPHETIC_TRADITIONS',
            'IDAAD_QURAN_ITS_SCIENCES',
            'THANAWI_ARABIC_LANGUAGE',
            'THANAWI_FAITH_AND_CIVILIZATION',
            'THANAWI_JURISPRUDENCE_AND_ITS_SOURCES',
            'THANAWI_PROPHETIC_TRADITIONS',
            'THANAWI_QURAN_ITS_SCIENCES'
        ));
    }

    // public function storeClass(Request $request)
    // {

    //     $request->validate([
    //         'class_id' => 'required',
    //         'class_stream' => 'required',
    //     ]);

    //     $classRecord = Classroom::where('class_name', $request->class_id)->where('school_id', Session('LoggedSchool'))->first();
    //     $StreamRecord = Stream::where('class_id', $request->class_id)->where('stream_id', $request->class_stream)->where('school_id', Session('LoggedSchool'))->first();

    //     if ($classRecord === null) {

    //         $class = new Classroom;

    //         $class->school_id = Session('LoggedSchool');
    //         $class->class_name = $request->class_id;
    //         $class->added_by = Session('LoggedAdmin');
    //         $class->date_added = now();
    //         $class->save();
    //     }

    //     if ($StreamRecord === null) {

    //         $stream = new Stream;

    //         $stream->school_id = Session('LoggedSchool');
    //         $stream->class_id = $request->class_id;
    //         $stream->stream_id = $request->class_stream;
    //         $stream->added_by = Session('LoggedAdmin');
    //         $stream->date_added = now();
    //         $stream->save();

    //         $classStreamAssignment = ClassStreamAssignment::create([
    //             'class_id' => $request->input('class_id'),
    //             'stream_id' => $request->input('class_stream'),
    //             'school_id' => Session('LoggedSchool'),
    //             'added_by' => Session('LoggedAdmin'),
    //             'date_added' => now(),
    //         ]);

    //         $assignmentId = $request->class_stream;

    //         $subjectCategories = [
    //             'technical_subjects' => 'technical',
    //             'optionals' => 'optional',
    //             'vocationals' => 'vocational',
    //             'mathematics' => 'mathematics',
    //             'languages' => 'language',
    //             'sciences' => 'science',
    //             'humanities' => 'humanities',
    //         ];

    //         foreach ($subjectCategories as $requestKey => $subjectType) {
    //             if ($request->has($requestKey) && is_array($request->input($requestKey))) {
    //                 foreach ($request->input($requestKey) as $subjectId) {
    //                     ClassSubject::create([
    //                         'class_id' => $request->input('class_id'),
    //                         'stream_id' => $request->input('class_stream'),
    //                         'subject_id' => $subjectId,
    //                         'subject_type' => $subjectType,
    //                         'school_id' => session('LoggedSchool'),
    //                     ]);
    //                 }
    //             }
    //         }
    //     } else {
    //         return response()->json(['fail' => true, 'message' => 'Stream was already created.']);
    //     }

    //     return response()->json(['success' => true, 'message' => 'Class created successfully.']);
    // }


    public function storeClass(Request $request)
    {

        $request->validate([
            'class_id' => 'required',
            'class_stream' => 'required',
            'subjects' => 'required|array|min:1',
            'subjects.*' => 'required'
        ]);

        $classRecord = Classroom::where('class_name', $request->class_id)
            ->where('school_id', Session('LoggedSchool'))
            ->first();

        $StreamRecord = Stream::where('class_id', $request->class_id)
            ->where('stream_id', $request->class_stream)
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
            $stream->stream_id = $request->class_stream;
            $stream->added_by = Session('LoggedAdmin');
            $stream->date_added = now();
            $stream->save();

            $classStreamAssignment = ClassStreamAssignment::create([
                'class_id' => $request->class_id,
                'stream_id' => $request->class_stream,
                'school_id' => Session('LoggedSchool'),
                'added_by' => Session('LoggedAdmin'),
                'date_added' => now(),
            ]);

            // Save the selected subjects
            // You can store them all as a specific type or categorize them
            foreach ($request->subjects as $subjectId) {
                ClassSubject::create([
                    'class_id' => $request->class_id,
                    'stream_id' => $request->class_stream,
                    'subject_id' => $subjectId,
                    'subject_type' => $request->class_type == 'O-Level' ? 'idaad' : 'thanawi', // or whatever type you want
                    'school_id' => session('LoggedSchool'),
                ]);
            }

            return response()->json(['success' => true, 'message' => 'Class created successfully.']);

        } else {
            return response()->json(['fail' => true, 'message' => 'Stream already exists for this class.']);
        }
    }
   public function updateClassSubjects(Request $request, $id)
{
    $assignment = ClassStreamAssignment::findOrFail($id);
    
    // Validate request
    $request->validate([
        'subjects' => 'required|array|min:1',
        'subjects.*' => 'required'
    ]);
    
    // Delete old subjects
    ClassSubject::where('class_id', $assignment->class_id)
        ->where('stream_id', $assignment->stream_id)
        ->where('school_id', session('LoggedSchool'))
        ->delete();
    
    // Determine subject type based on class level
    $oLevelIds = Helper::MasterRecords(config('constants.options.O_LEVEL'))->pluck('md_id')->toArray();
    $classType = in_array($assignment->class_id, $oLevelIds) ? 'idaad' : 'thanawi';
    
    // Insert new subjects
    foreach ($request->subjects as $subjectId) {
        ClassSubject::create([
            'class_id' => $assignment->class_id,
            'stream_id' => $assignment->stream_id,
            'subject_id' => $subjectId,
            'subject_type' => $classType,
            'school_id' => session('LoggedSchool'),
        ]);
    }
    
    return response()->json([
        'success' => true,
        'message' => 'Subjects updated successfully.'
    ]);
}

    public function manageClasses()
    {
        Helper::requireSchool();
        $classRecord = Classroom::where('school_id', Helper::requireSchool())->orderBy('class_name', 'Asc')->get();

        $Teachers = Teacher::with('school')
            ->where('school_id', Helper::requireSchool())
            ->get();

        return view('Class.manage-classes', compact('classRecord', 'Teachers'));
    }

    public function destroyClass($id)
    {
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

        $class_id = $stream->class_id;
        $stream_id = $stream->stream_id;

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

        $Streams = DB::table('streams')->where('class_id', $class_id)->where('school_id', Helper::requireSchool())->orderBy('stream_id', 'Asc')->get();

        $Teachers = Teacher::with('school')
            ->where('school_id', Session('LoggedSchool'))
            ->get();

        return view('Class.class-streams', compact(['Streams', 'Teachers', 'class_id']));
    }

    public function assignClassTeacher(Request $request)
    {
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

        return view('Class.attached-stream-subjects', compact('assignment', 'classSubjects', 'groupedSubjects', 'Teachers', 'classId', 'streamId'));
    }

    public function editClassSubjects($classId, $streamId)
    {
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

        // Get class type (O-Level or A-Level)
        $oLevelIds = Helper::MasterRecords(config('constants.options.O_LEVEL'))->pluck('md_id')->toArray();
        $classType = in_array($classId, $oLevelIds) ? 'O-Level' : 'A-Level';

        $SecondaryClasses = Helper::MasterRecordMerge(
            config('constants.options.O_LEVEL'),
            config('constants.options.A_LEVEL')
        );

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

        return view('Class.edit-class', compact(
            'assignment',
            'assignedSubjects',
            'SecondaryClasses',
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
            'THANAWI_QURAN_ITS_SCIENCES'
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
        $classRecord = Classroom::where('school_id', Helper::requireSchool())->where('class_supervisor', Session('LoggedTeacher'))->orderBy('class_name', 'Asc')->get();

        $Streams = DB::table('streams')->where('school_id', Helper::requireSchool())->where('class_teacher', Session('LoggedTeacher'))->orderBy('stream_id', 'Asc')->get();

        $Teachers = Teacher::with('school')
            ->where('school_id', Helper::requireSchool())
            ->get();

        $classSubjects = ClassSubject::where('subject_teacher_1', Session('LoggedTeacher'))
            ->orwhere('subject_teacher_2', Session('LoggedTeacher'))
            ->get();

        $Teachers = Teacher::with('school')
            ->where('school_id', Session('LoggedSchool'))
            ->get();

        return view('Class.my-classes', compact('classRecord', 'Teachers', 'Streams', 'classSubjects'));
    }
}
