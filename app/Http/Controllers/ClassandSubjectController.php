<?php

namespace App\Http\Controllers;


use DB;
use App\Models\Stream;
use App\Models\Teacher;
use Illuminate\Http\Request;
use App\Models\Classroom;
use App\Models\ClassSubject;
use App\Models\MasterData;
use App\Models\ClassStreamAssignment;
use App\Http\Controllers\Helper;
use Session;

class ClassandSubjectController extends Controller
{

    public function createClass()
    {
        $SecondaryClasses = Helper::MasterRecordMerge(
            config('constants.options.O_LEVEL'),
            config('constants.options.A_LEVEL')
        );

        $TECHNICAL_SUBJECTS = Helper::MasterRecords(config('constants.options.TECHNICAL_SUBJECTS'));
        $OPTIONALS = Helper::MasterRecords(config('constants.options.OPTIONALS'));
        $VOCATIONALS = Helper::MasterRecords(config('constants.options.VOCATIONALS'));
        $MATHEMATICS = Helper::MasterRecords(config('constants.options.MATHEMATICS'));
        $LANGUAGES = Helper::MasterRecords(config('constants.options.LANGUAGES'));
        $SCIENCES = Helper::MasterRecords(config('constants.options.SCIENCES'));
        $HUMANITIES = Helper::MasterRecords(config('constants.options.HUMANITIES'));
        $CLASS_STREAMS = Helper::MasterRecords(config('constants.options.CLASS_STREAMS'));

        return view('Class.create-class', compact(
            'SecondaryClasses',
            'TECHNICAL_SUBJECTS',
            'OPTIONALS',
            'VOCATIONALS',
            'MATHEMATICS',
            'LANGUAGES',
            'SCIENCES',
            'HUMANITIES',
            'CLASS_STREAMS'
        ));
    }

    public function storeClass(Request $request)
    {

        $request->validate([
            'class_id' => 'required',
            'class_stream' => 'required',
        ]);

        $classRecord = Classroom::where('class_name', $request->class_id)->where('school_id', Session('LoggedSchool'))->first();
        $StreamRecord = Stream::where('class_id', $request->class_id)->where('stream_id', $request->class_stream)->where('school_id', Session('LoggedSchool'))->first();

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
                'class_id' => $request->input('class_id'),
                'stream_id' => $request->input('class_stream'),
                'school_id' => Session('LoggedSchool'),
                'added_by' => Session('LoggedAdmin'),
                'date_added' => now(),
            ]);

            $assignmentId = $request->class_stream;

            $subjectCategories = [
                'technical_subjects' => 'technical',
                'optionals' => 'optional',
                'vocationals' => 'vocational',
                'mathematics' => 'mathematics',
                'languages' => 'language',
                'sciences' => 'science',
                'humanities' => 'humanities',
            ];

            foreach ($subjectCategories as $requestKey => $subjectType) {
                if ($request->has($requestKey) && is_array($request->input($requestKey))) {
                    foreach ($request->input($requestKey) as $subjectId) {
                        ClassSubject::create([
                            'class_id' => $request->input('class_id'),
                            'stream_id' => $request->input('class_stream'),
                            'subject_id' => $subjectId,
                            'subject_type' => $subjectType,
                            'school_id' => session('LoggedSchool'),
                        ]);
                    }
                }
            }
        } else {
            return response()->json(['fail' => true, 'message' => 'Stream was already created.']);
        }
        return response()->json(['success' => true, 'message' => 'Class created successfully.']);
    }


    public function manageClasses()
    {
        $classRecord = Classroom::where('school_id', Session('LoggedSchool'))->orderBy('class_name', 'Asc')->get();

        $Teachers = Teacher::with('school')
            ->where('school_id', Session('LoggedSchool'))
            ->get();

        return view('Class.manage-classes', compact('classRecord', 'Teachers'));
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
        $Streams = DB::table('streams')->where('class_id', $class_id)->orderBy('stream_id', 'Asc')->get();

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

    public function deleteStream(Stream $stream)
    {
        try {
            $stream->delete();
            return response()->json(['status' => 'success', 'message' => 'Stream deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Failed to delete stream: ' . $e->getMessage()], 500);
        }
    }

    public function attachedStreamSubjects($classId, $streamId)
    {

        $assignment = ClassStreamAssignment::where('class_id', $classId)
            ->where('stream_id', $streamId)
            ->where('school_id', Session('LoggedSchool'))
            ->first();

        if ($assignment) {

            $classSubjects = $assignment->classSubjects()->get();
            $groupedSubjects = $classSubjects->groupBy('subject_type');
        }

        $Teachers = Teacher::with('school')
            ->where('school_id', Session('LoggedSchool'))
            ->get();

        return view('Class.attached-stream-subjects', compact(['assignment', 'classSubjects', 'Teachers']));
    }

    public function edit($assignmentId)
    {
        // 1. Fetch the specific ClassStreamAssignment along with its assigned subjects
        $assignment = ClassStreamAssignment::with('classSubjects')->find($assignmentId);

        if (!$assignment) {
            return redirect()->back()->with('error', 'Class-Stream Assignment not found.');
        }

        // 2. Prepare the list of currently assigned subject IDs, grouped by their type
        // This makes it easy to check if a subject should be pre-checked in the view.
        $assignedSubjects = [];
        foreach ($assignment->classSubjects as $classSubject) {
            // 'subject_type' should match the categories you're using (e.g., 'technical', 'optional', 'mathematics')
            $assignedSubjects[$classSubject->subject_type][] = $classSubject->subject_id;
        }

        // 3. Fetch all available master data for dropdowns and checkboxes (same as your create page)
        $SecondaryClasses = MasterData::where('md_type', config('constants.options.SECONDARY_CLASSES_TYPE'))->get();

        // Assuming your constants for subject types are defined like this:
        // $TECHNICAL_SUBJECTS = MasterData::where('md_type', config('constants.options.TECHNICAL_SUBJECTS_TYPE'))->get();
        // $OPTIONALS = MasterData::where('md_type', config('constants.options.OPTIONALS_TYPE'))->get();
        // $VOCATIONALS = MasterData::where('md_type', config('constants.options.VOCATIONALS_TYPE'))->get();
        // $MATHEMATICS = MasterData::where('md_type', config('constants.options.MATHEMATICS_TYPE'))->get();
        // $LANGUAGES = MasterData::where('md_type', config('constants.options.LANGUAGES_TYPE'))->get();
        // $SCIENCES = MasterData::where('md_type', config('constants.options.SCIENCES_TYPE'))->get();
        // $HUMANITIES = MasterData::where('md_type', config('constants.options.HUMANITIES_TYPE'))->get();

        $TECHNICAL_SUBJECTS = Helper::MasterRecords(config('constants.options.TECHNICAL_SUBJECTS'));
        $OPTIONALS = Helper::MasterRecords(config('constants.options.OPTIONALS'));
        $VOCATIONALS = Helper::MasterRecords(config('constants.options.VOCATIONALS'));
        $MATHEMATICS = Helper::MasterRecords(config('constants.options.MATHEMATICS'));
        $LANGUAGES = Helper::MasterRecords(config('constants.options.LANGUAGES'));
        $SCIENCES = Helper::MasterRecords(config('constants.options.SCIENCES'));
        $HUMANITIES = Helper::MasterRecords(config('constants.options.HUMANITIES'));
        $CLASS_STREAMS = Helper::MasterRecords(config('constants.options.CLASS_STREAMS'));

        return view('class_subjects.edit', compact(
            'assignment',
            'assignedSubjects',
            'SecondaryClasses',
            'TECHNICAL_SUBJECTS',
            'OPTIONALS',
            'VOCATIONALS',
            'MATHEMATICS',
            'LANGUAGES',
            'SCIENCES',
            'HUMANITIES'
        ));
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

}

