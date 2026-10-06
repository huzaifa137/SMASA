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
        'secondary_olevel' => 'O-LEVEL (Secondary)',
        'secondary_alevel' => 'A-LEVEL (Secondary)',
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

        // Only show the tab(s) for the category/categories this school has
        // actually opted into (School Products), instead of every class
        // type that exists in the system. A school with merged categories
        // sees a tab per merged category; everyone else sees just their one.
        $classTypes = self::classTypesForSchool($school);

        return view('Class.manage-custom-subjects', [
            'school'     => $school,
            'subjects'   => $subjects,
            'classTypes' => $classTypes,
        ]);
    }

    /**
     * This school's CLASS_TYPES entries, filtered down to only the
     * category/categories it currently belongs to (Helper::schoolClassTypes()
     * unions every merged School Product). Falls back to the full list only
     * if the school somehow has no resolvable product at all, so the page
     * never ends up completely empty.
     */
    public static function classTypesForSchool(School $school): array
    {
        $subjectTypeMap = config('constants.class_type_subject_types');
        $schoolClassTypes = Helper::schoolClassTypes($school->id);

        $allowedSubjectTypes = [];
        foreach ($schoolClassTypes as $classType) {
            if (isset($subjectTypeMap[$classType])) {
                $allowedSubjectTypes[] = $subjectTypeMap[$classType];
            }
        }

        $filtered = array_intersect_key(self::CLASS_TYPES, array_flip($allowedSubjectTypes));

        return $filtered ?: self::CLASS_TYPES;
    }

    public function store(Request $request)
    {
        $school = School::findOrFail(Helper::requireSchool());

        if (!$school->custom_subjects_enabled) {
            return response()->json(['success' => false, 'message' => 'Custom subjects are not enabled for your school.'], 403);
        }

        $allowedClassTypes = array_keys(self::classTypesForSchool($school));

        $request->validate([
            'class_type'   => 'required|in:' . implode(',', $allowedClassTypes),
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
                // A row that was switched back to the default list earlier
                // keeps its custom_subject_id (so exam marks and subject
                // settings stay linked). Re-use that link instead of
                // creating a new custom subject, otherwise the identity
                // used by existing marks would change.
                if ($row->custom_subject_id
                    && CustomSubject::where('id', $row->custom_subject_id)->where('school_id', $school->id)->exists()) {
                    $row->subject_source = 'custom';
                    $row->save();
                    continue;
                }

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

    /**
     * Master-list master_code option keys that hold the default subjects of
     * each subject_type. Used only to find the default-list equivalent of a
     * subject the school created itself while in custom mode.
     */
    private const MASTER_SUBJECT_OPTIONS = [
        'idaad' => ['IDAAD_ARABIC_LANGUAGE', 'IDAAD_FAITH_AND_CIVILIZATION', 'IDAAD_JURISPRUDENCE_AND_ITS_SOURCES', 'IDAAD_PROPHETIC_TRADITIONS', 'IDAAD_QURAN_ITS_SCIENCES'],
        'thanawi' => ['THANAWI_ARABIC_LANGUAGE', 'THANAWI_FAITH_AND_CIVILIZATION', 'THANAWI_JURISPRUDENCE_AND_ITS_SOURCES', 'THANAWI_PROPHETIC_TRADITIONS', 'THANAWI_QURAN_ITS_SCIENCES'],
        'primary_theology' => ['PRIMARY_THEOLOGY'],
        'primary_secular' => ['NURSERY_BABY_CLASS', 'NURSERY_MIDDLE_CLASS', 'NURSERY_TOP_CLASS', 'LOWER_PRIMARY_P1', 'LOWER_PRIMARY_P2', 'LOWER_PRIMARY_P3', 'UPPER_PRIMARY_P4_P7'],
        'secondary_olevel' => ['SECONDARY_OLEVEL_SUBJECTS'],
        'secondary_alevel' => ['SECONDARY_ALEVEL_SUBJECTS'],
    ];

    /**
     * Works out what switching this school back to the default subject list
     * would do, without changing anything.
     *
     *  - keep:      rows that still carry their original default subject_id
     *               (everything carried over by confirmSwitch). Only
     *               subject_source flips; ids stay, so exam marks, subject
     *               settings and teacher assignments are untouched.
     *  - matched:   rows for subjects the school created itself (subject_id
     *               null) whose name matches a default subject. Their marks
     *               and exam subject settings are re-pointed at that
     *               default subject.
     *  - unmatched: rows with no default equivalent. The switch is blocked
     *               until the admin removes them, or explicitly chooses to
     *               discard them.
     */
    private function buildRevertPlan(School $school): array
    {
        $rows = ClassSubject::where('school_id', $school->id)
            ->where('subject_source', 'custom')
            ->get();

        // Default subjects by type: [subject_type => [md_master_code_id => [lowercase name => md_id]]]
        $masterByType = [];
        foreach (self::MASTER_SUBJECT_OPTIONS as $type => $optionKeys) {
            $codes = array_map(fn($k) => config('constants.options.' . $k), $optionKeys);
            $records = DB::table('master_datas')->whereIn('md_master_code_id', $codes)->get();
            foreach ($records as $rec) {
                $masterByType[$type][$rec->md_master_code_id][mb_strtolower(trim($rec->md_name))] = $rec->md_id;
            }
        }

        // The default-list group a class already uses (taken from its rows
        // that still have a default subject), to disambiguate same-named
        // subjects that exist in several groups (e.g. English in P1 and P4).
        $classGroup = [];
        foreach ($rows as $row) {
            if ($row->subject_id) {
                $code = DB::table('master_datas')->where('md_id', $row->subject_id)->value('md_master_code_id');
                if ($code) {
                    $classGroup[$row->class_id . '|' . $row->stream_id] = $code;
                }
            }
        }

        $customNames = CustomSubject::where('school_id', $school->id)->pluck('subject_name', 'id');

        $plan = ['keep' => [], 'matched' => [], 'unmatched' => []];

        foreach ($rows as $row) {
            if ($row->subject_id) {
                $plan['keep'][] = $row;
                continue;
            }

            $name = mb_strtolower(trim((string) ($customNames[$row->custom_subject_id] ?? '')));
            $candidates = [];

            foreach ($masterByType[$row->subject_type] ?? [] as $code => $byName) {
                if ($name !== '' && isset($byName[$name])) {
                    $candidates[$code] = $byName[$name];
                }
            }

            $groupKey = $row->class_id . '|' . $row->stream_id;
            $mdId = null;

            if (count($candidates) === 1) {
                $mdId = reset($candidates);
            } elseif (count($candidates) > 1 && isset($classGroup[$groupKey], $candidates[$classGroup[$groupKey]])) {
                $mdId = $candidates[$classGroup[$groupKey]];
            }

            if ($mdId) {
                $plan['matched'][] = ['row' => $row, 'md_id' => $mdId];
            } else {
                $plan['unmatched'][] = $row;
            }
        }

        return $plan;
    }

    /**
     * School-facing confirmation screen for going back to the default list.
     */
    public function showRevertPrompt()
    {
        $school = School::findOrFail(Helper::requireSchool());

        if (!$school->custom_subjects_enabled) {
            abort(403, 'This option has not been enabled for your school.');
        }

        if (!$school->custom_subjects_active) {
            return redirect()->route('school.custom-subjects.switch')->with('error', 'Your school is already using the default subject list.');
        }

        $plan = $this->buildRevertPlan($school);

        $describe = fn($row) => [
            'class' => Helper::recordMdname($row->class_id) . ($row->stream_id && $row->stream_id !== 'NO_STREAM' ? ' - ' . $row->stream_id : ''),
            'custom_name' => Helper::classSubjectName($row),
            'default_name' => $row->subject_id ? Helper::recordMdname($row->subject_id) : null,
        ];

        $renamed = collect($plan['keep'])
            ->map($describe)
            ->filter(fn($r) => $r['default_name'] && mb_strtolower(trim($r['default_name'])) !== mb_strtolower(trim($r['custom_name'])))
            ->values();

        $matched = collect($plan['matched'])->map(function ($m) use ($describe) {
            $d = $describe($m['row']);
            $d['default_name'] = Helper::recordMdname($m['md_id']);
            return $d;
        })->values();

        $unmatched = collect($plan['unmatched'])->map($describe)->values();

        return view('Class.revert-to-default-subjects', [
            'school' => $school,
            'keepCount' => count($plan['keep']),
            'renamed' => $renamed,
            'matched' => $matched,
            'unmatched' => $unmatched,
        ]);
    }

    /**
     * Switches the school back to the shared default subject list.
     *
     * Nothing is deleted from custom_subjects, so the school can switch to
     * custom subjects again later and find its list exactly as it left it.
     * Unmatched school-created subjects only block the switch unless the
     * admin ticks "discard", in which case just their class assignments are
     * removed (their marks stay in the database but no longer appear).
     */
    public function confirmRevert(Request $request)
    {
        $school = School::findOrFail(Helper::requireSchool());

        if (!$school->custom_subjects_enabled) {
            abort(403, 'This option has not been enabled for your school.');
        }

        if (!$school->custom_subjects_active) {
            return redirect()->route('school.custom-subjects.switch')->with('error', 'Your school is already using the default subject list.');
        }

        $discard = $request->boolean('discard_unmatched');

        $blocked = DB::transaction(function () use ($school, $discard) {
            $plan = $this->buildRevertPlan($school);

            if (!empty($plan['unmatched']) && !$discard) {
                return true;
            }

            // 1. Subjects carried over from the default list: only the source flips.
            foreach ($plan['keep'] as $row) {
                $row->subject_source = 'master';
                $row->save();
            }

            // 2. School-created subjects with a default equivalent: re-point
            //    class row, marks and exam subject settings at the default subject.
            foreach ($plan['matched'] as $m) {
                $row = $m['row'];
                $mdId = $m['md_id'];
                $customId = $row->custom_subject_id;

                // Exam subject settings: simple re-point.
                DB::table('examination_subject_settings')
                    ->where('school_id', $school->id)
                    ->where('class_id', $row->class_id)
                    ->where('stream_id', $row->stream_id)
                    ->whereNull('subject_id')
                    ->where('custom_subject_id', $customId)
                    ->update(['subject_id' => $mdId]);

                // Marks: re-point, skipping any row that would collide with a
                // mark already stored under the default subject (MySQL does
                // not allow that check inside the UPDATE itself, so it is
                // resolved here first).
                $already = DB::table('examination_marks')
                    ->where('school_id', $school->id)
                    ->where('subject_id', $mdId)
                    ->where('custom_subject_id', $customId)
                    ->get(['examination_id', 'student_id'])
                    ->mapWithKeys(fn($r) => [$r->examination_id . '|' . $r->student_id => true]);

                $moveIds = DB::table('examination_marks')
                    ->where('school_id', $school->id)
                    ->where('class_id', $row->class_id)
                    ->where('stream_id', $row->stream_id)
                    ->whereNull('subject_id')
                    ->where('custom_subject_id', $customId)
                    ->get(['id', 'examination_id', 'student_id'])
                    ->reject(fn($r) => isset($already[$r->examination_id . '|' . $r->student_id]))
                    ->pluck('id');

                foreach ($moveIds->chunk(500) as $chunk) {
                    DB::table('examination_marks')->whereIn('id', $chunk->all())->update(['subject_id' => $mdId]);
                }

                $row->subject_id = $mdId;
                $row->subject_source = 'master';
                $row->save();
            }

            // 3. Explicitly discarded subjects (no default equivalent).
            foreach ($plan['unmatched'] as $row) {
                $row->delete();
            }

            $school->custom_subjects_active = false;
            $school->save();

            return false;
        });

        if ($blocked) {
            return redirect()->route('school.custom-subjects.revert')
                ->with('error', 'Some of your own subjects have no default equivalent. Remove them from their classes, or tick the option to discard them, then try again.');
        }

        return redirect()->route('school.custom-subjects.switch')
            ->with('success', 'Your school is back on the default subject list. Your own subjects are still saved, so you can switch to them again at any time.');
    }

    private function authorizeSchoolOwnership(CustomSubject $subject)
    {
        if ((int) $subject->school_id !== (int) Helper::requireSchool()) {
            abort(403);
        }
    }
}