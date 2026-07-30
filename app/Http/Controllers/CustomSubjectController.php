<?php

namespace App\Http\Controllers;

use App\Helpers\PermissionHelper;
use App\Models\ClassSubject;
use App\Models\CustomSubject;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomSubjectController extends Controller
{
    /**
     * The four buckets subjects are grouped into, mirroring the
     * subject_type values already used across the system.
     */
    public const CLASS_TYPES = [
        'idaad'            => 'O-LEVEL (Idaad)',
        'thanawi'          => 'A-LEVEL (Thanawi)',
        'primary_theology' => 'Primary Theology',
        'primary_secular'  => 'Primary Secular',
    ];

    /**
     * School-facing: list/manage this school's own subjects.
     * Only reachable once the super admin has unlocked the option
     * (custom_subjects_enabled) for this school.
     */
    public function manage()
    {
        $school = School::findOrFail(Helper::requireSchool());

        if (!$school->custom_subjects_enabled) {
            return redirect()->back()->with('error', 'Custom subjects have not been enabled for your school yet. Please contact support.');
        }

        $subjects = CustomSubject::forSchool($school->id)
            ->orderBy('class_type')
            ->orderBy('subject_name')
            ->get()
            ->groupBy('class_type');

        return view('Class.manage-custom-subjects', [
            'school'     => $school,
            'subjects'   => $subjects,
            'classTypes' => self::CLASS_TYPES,
        ]);
    }

    public function store(Request $request)
    {
        $school = School::findOrFail(Helper::requireSchool());

        if (!$school->custom_subjects_enabled) {
            return response()->json(['success' => false, 'message' => 'Custom subjects are not enabled for your school.'], 403);
        }

        $request->validate([
            'class_type'   => 'required|in:' . implode(',', array_keys(self::CLASS_TYPES)),
            'subject_name' => 'required|string|max:255',
            'subject_code' => 'nullable|string|max:50',
        ]);

        $exists = CustomSubject::forSchool($school->id)
            ->ofType($request->class_type)
            ->where('subject_name', $request->subject_name)
            ->exists();

        if ($exists) {
            return response()->json(['success' => false, 'message' => 'You already have a subject with that name in this category.'], 422);
        }

        $subject = CustomSubject::create([
            'school_id'    => $school->id,
            'class_type'   => $request->class_type,
            'subject_name' => $request->subject_name,
            'subject_code' => $request->subject_code,
            'is_active'    => true,
        ]);

        return response()->json(['success' => true, 'subject' => $subject]);
    }

    public function update(Request $request, CustomSubject $subject)
    {
        $this->authorizeSchoolOwnership($subject);

        $request->validate([
            'subject_name' => 'required|string|max:255',
            'subject_code' => 'nullable|string|max:50',
            'is_active'    => 'nullable|boolean',
        ]);

        $subject->subject_name = $request->subject_name;
        $subject->subject_code = $request->subject_code;
        if ($request->has('is_active')) {
            $subject->is_active = $request->boolean('is_active');
        }
        $subject->save();

        return response()->json(['success' => true, 'subject' => $subject]);
    }

    public function destroy(CustomSubject $subject)
    {
        $this->authorizeSchoolOwnership($subject);

        $inUse = ClassSubject::where('custom_subject_id', $subject->id)->exists();

        if ($inUse) {
            // Don't hard-delete a subject that's already attached to classes;
            // deactivate instead so existing class-subject records keep working.
            $subject->is_active = false;
            $subject->save();

            return response()->json([
                'success' => true,
                'message' => 'This subject is already attached to one or more classes, so it has been deactivated instead of deleted. Remove it from those classes first if you want to delete it permanently.',
            ]);
        }

        $subject->delete();

        return response()->json(['success' => true, 'message' => 'Subject deleted.']);
    }

    /**
     * School-facing confirmation screen, only shown once the super admin
     * has unlocked the option for this school.
     */
    public function showSwitchPrompt()
    {
        $school = School::findOrFail(Helper::requireSchool());

        if (!$school->custom_subjects_enabled) {
            abort(403, 'This option has not been enabled for your school.');
        }

        if ($school->custom_subjects_active) {
            return redirect()->route('school.custom-subjects.manage');
        }

        // Preview of what will be copied over, purely for the confirmation screen.
        $preview = ClassSubject::where('school_id', $school->id)
            ->where('subject_source', 'master')
            ->get()
            ->map(function ($row) {
                return [
                    'subject_type' => $row->subject_type,
                    'name'         => Helper::recordMdname($row->subject_id),
                ];
            })
            ->unique(function ($row) {
                return $row['subject_type'] . '|' . $row['name'];
            })
            ->groupBy('subject_type');

        return view('Class.switch-to-custom-subjects', compact('school', 'preview'));
    }

    /**
     * The school admin's confirmation. Copies every subject name the school
     * currently has attached (per subject_type/class_type) into their own
     * custom_subjects list, points existing class_subjects rows at those new
     * custom rows, then flips the school into custom mode.
     *
     * Nothing is deleted — the original subject_id values stay on the rows
     * for audit purposes, only subject_source and custom_subject_id change.
     */
    public function confirmSwitch(Request $request)
    {
        $school = School::findOrFail(Helper::requireSchool());

        if (!$school->custom_subjects_enabled) {
            abort(403, 'This option has not been enabled for your school.');
        }

        if ($school->custom_subjects_active) {
            return redirect()->route('school.custom-subjects.manage')->with('success', 'Already switched over.');
        }

        DB::transaction(function () use ($school) {
            $masterRows = ClassSubject::where('school_id', $school->id)
                ->where('subject_source', 'master')
                ->get();

            // name -> CustomSubject cache so we don't create duplicates
            $created = [];

            foreach ($masterRows as $row) {
                $name = Helper::recordMdname($row->subject_id);

                if (!$name) {
                    continue; // nothing sensible to copy, leave this row untouched
                }

                $cacheKey = $row->subject_type . '|' . $name;

                if (!isset($created[$cacheKey])) {
                    $customSubject = CustomSubject::firstOrCreate(
                        [
                            'school_id'  => $school->id,
                            'class_type' => $row->subject_type,
                            'subject_name' => $name,
                        ],
                        ['is_active' => true]
                    );
                    $created[$cacheKey] = $customSubject;
                }

                $row->custom_subject_id = $created[$cacheKey]->id;
                $row->subject_source = 'custom';
                $row->save();
            }

            $school->custom_subjects_active = true;
            $school->save();
        });

        return redirect()->route('school.custom-subjects.manage')
            ->with('success', 'Your school has switched to its own subject list. Your previous subjects were carried over — you can now rename, add, or remove them freely.');
    }

    private function authorizeSchoolOwnership(CustomSubject $subject)
    {
        if ((int) $subject->school_id !== (int) Helper::requireSchool()) {
            abort(403);
        }
    }
}
