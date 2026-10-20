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

        // Every class type across every School Product this school
        // currently belongs to (normally one, more than one for a school
        // whose categories have been merged - see SchoolProductMergeService).
        // $SecondaryClasses/$PrimaryClasses/$classTypeMap below are the
        // UNION of whichever of the four class types apply, so the class
        // dropdown always reflects everything the school is enrolled in.
        $classTypes = Helper::schoolClassTypes($school->id);

        [$SecondaryClasses, $PrimaryClasses, $classTypeMap] = $this->buildMergedClassLists($classTypes);

        $viewData = [
            'SecondaryClasses' => $SecondaryClasses,
            'PrimaryClasses' => $PrimaryClasses,
            'classTypeMap' => $classTypeMap,
        ];

        if (in_array('Primary Theology', $classTypes, true)) {
            $viewData['primaryTheology'] = Helper::MasterRecords(config('constants.options.PRIMARY_THEOLOGY'));
        }

        if (in_array('Primary Secular', $classTypes, true)) {
            $viewData['primarySecularSubjects'] = [
                config('constants.options.NURSERY_BABY_CLASS') => Helper::MasterRecords(config('constants.options.NURSERY_BABY_CLASS')),
                config('constants.options.NURSERY_MIDDLE_CLASS') => Helper::MasterRecords(config('constants.options.NURSERY_MIDDLE_CLASS')),
                config('constants.options.NURSERY_TOP_CLASS') => Helper::MasterRecords(config('constants.options.NURSERY_TOP_CLASS')),
                config('constants.options.LOWER_PRIMARY_P1') => Helper::MasterRecords(config('constants.options.LOWER_PRIMARY_P1')),
                config('constants.options.LOWER_PRIMARY_P2') => Helper::MasterRecords(config('constants.options.LOWER_PRIMARY_P2')),
                config('constants.options.LOWER_PRIMARY_P3') => Helper::MasterRecords(config('constants.options.LOWER_PRIMARY_P3')),
                config('constants.options.UPPER_PRIMARY_P4_P7') => Helper::MasterRecords(config('constants.options.UPPER_PRIMARY_P4_P7')),
            ];
        }

        if (in_array('O-Level', $classTypes, true) || in_array('A-Level', $classTypes, true)) {
            $viewData += [
                'IDAAD_ARABIC_LANGUAGE' => Helper::MasterRecords(config('constants.options.IDAAD_ARABIC_LANGUAGE')),
                'IDAAD_FAITH_AND_CIVILIZATION' => Helper::MasterRecords(config('constants.options.IDAAD_FAITH_AND_CIVILIZATION')),
                'IDAAD_JURISPRUDENCE_AND_ITS_SOURCES' => Helper::MasterRecords(config('constants.options.IDAAD_JURISPRUDENCE_AND_ITS_SOURCES')),
                'IDAAD_PROPHETIC_TRADITIONS' => Helper::MasterRecords(config('constants.options.IDAAD_PROPHETIC_TRADITIONS')),
                'IDAAD_QURAN_ITS_SCIENCES' => Helper::MasterRecords(config('constants.options.IDAAD_QURAN_ITS_SCIENCES')),
                'THANAWI_ARABIC_LANGUAGE' => Helper::MasterRecords(config('constants.options.THANAWI_ARABIC_LANGUAGE')),
                'THANAWI_FAITH_AND_CIVILIZATION' => Helper::MasterRecords(config('constants.options.THANAWI_FAITH_AND_CIVILIZATION')),
                'THANAWI_JURISPRUDENCE_AND_ITS_SOURCES' => Helper::MasterRecords(config('constants.options.THANAWI_JURISPRUDENCE_AND_ITS_SOURCES')),
                'THANAWI_PROPHETIC_TRADITIONS' => Helper::MasterRecords(config('constants.options.THANAWI_PROPHETIC_TRADITIONS')),
                'THANAWI_QURAN_ITS_SCIENCES' => Helper::MasterRecords(config('constants.options.THANAWI_QURAN_ITS_SCIENCES')),
            ];
        }

        // Secondary (secular O-Level/A-Level) — separate from the Idaad/
        // Thanawi Islamic curriculum above. A-Level subjects are grouped by
        // md_misc1 (General / Principal - Arts / Principal - Sciences /
        // Subsidiary — see 2026_09_13_120000_add_secondary_alevel_principal_subjects.php)
        // so the view can show Arts/Sciences as their own sections instead
        // of one flat list, and so storeClass() can enforce the standard
        // "exactly 3 principals, at most 1 subsidiary" combination rule.
        if (in_array('Secondary O-Level', $classTypes, true) || in_array('Secondary A-Level', $classTypes, true)) {
            $viewData += [
                'SECONDARY_OLEVEL_SUBJECTS' => Helper::MasterRecords(config('constants.options.SECONDARY_OLEVEL_SUBJECTS')),
                'SECONDARY_ALEVEL_SUBJECTS' => Helper::MasterRecords(config('constants.options.SECONDARY_ALEVEL_SUBJECTS')),
                'SECONDARY_ALEVEL_SUBJECTS_GROUPED' => Helper::MasterRecords(config('constants.options.SECONDARY_ALEVEL_SUBJECTS'))->groupBy(function ($subject) {
                    return $subject->md_misc1 ?: 'Principal - Arts';
                }),
            ];
        }

        return view('Class.create-class', $viewData);
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
        $classTypes = Helper::schoolClassTypes($school->id);

        [$SecondaryClasses, $PrimaryClasses, $classTypeMap] = $this->buildMergedClassLists($classTypes);

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

    /**
     * Builds the Secondary (O-Level/A-Level) and Primary (Theology/Secular)
     * class collections - and the md_id => class-type map the create-class
     * views key their subject panels off of - for whichever of the four
     * class types are passed in. Shared by both the master-subject and
     * custom-subject create-class screens, and is what makes a school with
     * merged School Product categories show every category's classes in
     * one dropdown instead of picking just one.
     *
     * @return array{0: \Illuminate\Support\Collection, 1: \Illuminate\Support\Collection, 2: array}
     */
    private function buildMergedClassLists(array $classTypes): array
    {
        $SecondaryClasses = collect();
        $PrimaryClasses = collect();
        $classTypeMap = [];

        $wantsOLevel = in_array('O-Level', $classTypes, true);
        $wantsALevel = in_array('A-Level', $classTypes, true);
        $wantsSecondaryOLevel = in_array('Secondary O-Level', $classTypes, true);
        $wantsSecondaryALevel = in_array('Secondary A-Level', $classTypes, true);

        if ($wantsOLevel || $wantsALevel || $wantsSecondaryOLevel || $wantsSecondaryALevel) {
            $oLevelClasses = $wantsOLevel ? Helper::MasterRecords(config('constants.options.O_LEVEL')) : collect();
            $aLevelClasses = $wantsALevel ? Helper::MasterRecords(config('constants.options.A_LEVEL')) : collect();
            $secondaryOLevelClasses = $wantsSecondaryOLevel ? Helper::MasterRecords(config('constants.options.SECONDARY_OLEVEL_CLASSES')) : collect();
            $secondaryALevelClasses = $wantsSecondaryALevel ? Helper::MasterRecords(config('constants.options.SECONDARY_ALEVEL_CLASSES')) : collect();

            $SecondaryClasses = $oLevelClasses->merge($aLevelClasses)
                ->merge($secondaryOLevelClasses)
                ->merge($secondaryALevelClasses);

            $oLevelIds = $oLevelClasses->pluck('md_id')->toArray();
            $aLevelIds = $aLevelClasses->pluck('md_id')->toArray();
            $secondaryOLevelIds = $secondaryOLevelClasses->pluck('md_id')->toArray();
            $secondaryALevelIds = $secondaryALevelClasses->pluck('md_id')->toArray();

            foreach ($SecondaryClasses as $class) {
                if (in_array($class->md_id, $oLevelIds, true)) {
                    $classTypeMap[$class->md_id] = 'O-Level';
                } elseif (in_array($class->md_id, $aLevelIds, true)) {
                    $classTypeMap[$class->md_id] = 'A-Level';
                } elseif (in_array($class->md_id, $secondaryOLevelIds, true)) {
                    $classTypeMap[$class->md_id] = 'Secondary O-Level';
                } elseif (in_array($class->md_id, $secondaryALevelIds, true)) {
                    $classTypeMap[$class->md_id] = 'Secondary A-Level';
                } else {
                    $classTypeMap[$class->md_id] = 'Unknown';
                }
            }
        }

        $wantsTheology = in_array('Primary Theology', $classTypes, true);
        $wantsSecular = in_array('Primary Secular', $classTypes, true);

        if ($wantsTheology || $wantsSecular) {
            $theologyClasses = $wantsTheology ? Helper::MasterRecords(config('constants.options.PRIMARY_THEOLOGY_CLASSES')) : collect();
            $secularClasses = $wantsSecular ? Helper::MasterRecords(config('constants.options.PRIMARY_SECULAR_CLASSES')) : collect();

            $PrimaryClasses = $theologyClasses->merge($secularClasses);

            $theologyIds = $theologyClasses->pluck('md_id')->toArray();
            $secularIds = $secularClasses->pluck('md_id')->toArray();

            foreach ($PrimaryClasses as $class) {
                if (in_array($class->md_id, $theologyIds, true)) {
                    $classTypeMap[$class->md_id] = 'Primary Theology';
                } elseif (in_array($class->md_id, $secularIds, true)) {
                    $classTypeMap[$class->md_id] = 'Primary Secular';
                }
            }
        }

        return [$SecondaryClasses, $PrimaryClasses, $classTypeMap];
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
            'class_type' => 'required|in:O-Level,A-Level,Primary Theology,Primary Secular,Secondary O-Level,Secondary A-Level'
        ]);

        // A class can be created without any streams. We still store a real
        // Stream/ClassStreamAssignment row using a fixed sentinel id so that
        // every other part of the system (attendance, exams, timetable,
        // finance, etc.) that expects a stream_id keeps working unchanged.
        $streamId = $request->boolean('no_stream') ? self::NO_STREAM_SENTINEL : $request->class_stream;

        $school = School::find(Session('LoggedSchool'));
        $usesCustomSubjects = $school && $school->usesCustomSubjects();

        // This picker builds the class/stream's SUBJECT POOL, not one
        // student's combination — a class normally offers several
        // combinations at once (e.g. PCM and HEG in the same Senior 5
        // stream), so there's nothing to require here beyond General Paper
        // itself. Each student's own principal subjects and optional
        // subsidiary are chosen separately later, when that student's own
        // combination is built — it can't be enforced on the whole
        // class/stream at once. The only thing worth guaranteeing here:
        // General Paper — compulsory for every A-Level student regardless
        // of combination — is part of the pool (the UI locks its checkbox
        // on, but this re-adds it server-side too as a safety net rather
        // than trusting the client).
        if ($request->class_type === 'Secondary A-Level' && !$usesCustomSubjects) {
            $subjectGroups = DB::table('master_datas')
                ->where('md_master_code_id', config('constants.options.SECONDARY_ALEVEL_SUBJECTS'))
                ->pluck('md_misc1', 'md_id');

            $submittedSubjects = collect($request->subjects);

            $generalPaperId = $subjectGroups->search('General');
            if ($generalPaperId !== false && !$submittedSubjects->contains((string) $generalPaperId) && !$submittedSubjects->contains($generalPaperId)) {
                $request->merge(['subjects' => [...$request->subjects, $generalPaperId]]);
            }
        }

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

        // A class is either stream-less or streamed, never both: students of a
        // "No Stream" class would otherwise be split from a named stream's
        // roster with no way to tell which they belong to. The way from one
        // to the other is Manage Streams -> Rename Stream (it moves students,
        // marks, attendance, timetables and teachers along with it).
        if ($StreamRecord === null) {
            $otherStreams = Stream::where('class_id', $request->class_id)
                ->where('school_id', Session('LoggedSchool'))
                ->pluck('stream_id');

            if ($streamId === self::NO_STREAM_SENTINEL && $otherStreams->isNotEmpty()) {
                return response()->json(['fail' => true, 'message' => 'This class already has named streams, so it cannot also be created with no stream.']);
            }

            if ($streamId !== self::NO_STREAM_SENTINEL && $otherStreams->contains(self::NO_STREAM_SENTINEL)) {
                return response()->json(['fail' => true, 'message' => 'This class currently has no streams. Open Manage Streams for this class and use "Rename Stream" to give it its first stream name, then add further streams.']);
            }
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
                } elseif ($request->class_type === 'Secondary O-Level') {
                    $subjectType = 'secondary_olevel';
                } elseif ($request->class_type === 'Secondary A-Level') {
                    $subjectType = 'secondary_alevel';
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
    /**
     * Single source of truth for "which subject_type does this class use?".
     *
     * storeClass() writes all SIX types (idaad, thanawi, primary_theology,
     * primary_secular, secondary_olevel, secondary_alevel) but the edit and
     * update paths below used to know only four and silently treated every
     * Secondary class as 'thanawi'. For a custom-subjects school that meant
     * the edit page listed the wrong category (Thanawi subjects instead of
     * the Secondary ones) so nothing was ticked, and saving rewrote the
     * class's subject_type to 'thanawi'.
     */
    private function resolveSubjectType($classId, $schoolId = null, $streamId = null): string
    {
        $map = [
            'O_LEVEL'                  => 'idaad',
            'A_LEVEL'                  => 'thanawi',
            'PRIMARY_THEOLOGY_CLASSES' => 'primary_theology',
            'PRIMARY_SECULAR_CLASSES'  => 'primary_secular',
            'SECONDARY_OLEVEL_CLASSES' => 'secondary_olevel',
            'SECONDARY_ALEVEL_CLASSES' => 'secondary_alevel',
        ];

        foreach ($map as $option => $type) {
            $ids = Helper::MasterRecords(config('constants.options.' . $option))->pluck('md_id')->toArray();
            if (in_array($classId, $ids)) {
                return $type;
            }
        }

        // Class isn't in any known list: trust what its rows already say.
        if ($schoolId) {
            $existing = ClassSubject::where('school_id', $schoolId)
                ->where('class_id', $classId)
                ->when($streamId !== null, fn($q) => $q->where('stream_id', $streamId))
                ->whereNotNull('subject_type')
                ->where('subject_type', '!=', '')
                ->value('subject_type');

            if ($existing) {
                return $existing;
            }
        }

        return 'thanawi';
    }

    /**
     * A class_subjects row the edit-subjects screen must never delete,
     * because it cannot be shown as a tickable item (so the admin could
     * never have "deselected" it on purpose):
     *   - rows auto-created from students' A-Level combinations
     *     (is_auto_synced / subject_source = school_alevel) — they carry
     *     teacher assignments and marks and are owned by the combinations;
     *   - a legacy default-list row with no custom_subject_id whose name
     *     matches none of the school's custom subjects.
     *
     * @param array $customNamesLower  [lowercased custom subject name => true]
     */
    private function isLockedClassSubjectRow(ClassSubject $row, array $customNamesLower, bool $customMode): bool
    {
        if ($row->is_auto_synced || $row->subject_source === 'school_alevel') {
            return true;
        }

        if (!$customMode) {
            return false;
        }

        if ($row->custom_subject_id) {
            return false;
        }

        $name = mb_strtolower(trim((string) Helper::recordMdname($row->subject_id)));

        return $name === '' || !isset($customNamesLower[$name]);
    }

    public function updateClassSubjects(Request $request, $id)
    {
        PermissionHelper::denyUnlessFeature('edit_class');

        $schoolId = Helper::requireSchool();

        // Scoped to the logged-in school: class/stream ids are shared master
        // data, so an unscoped lookup could touch another school's row.
        $assignment = ClassStreamAssignment::where('school_id', $schoolId)->findOrFail($id);

        $request->validate([
            'subjects' => 'required|array|min:1',
            'subjects.*' => 'required'
        ]);

        $school = School::find($schoolId);
        $usesCustomSubjects = $school && $school->usesCustomSubjects();

        $submitted = collect($request->subjects)->map(fn($v) => (string) $v)->unique()->values();

        // Secondary A-Level (default list): General Paper is compulsory for every
        // student, so it can never be removed from the pool (same rule storeClass() applies).
        if (!$usesCustomSubjects
            && $this->resolveSubjectType($assignment->class_id, $schoolId, $assignment->stream_id) === 'secondary_alevel') {
            $generalPaperId = DB::table('master_datas')
                ->where('md_master_code_id', config('constants.options.SECONDARY_ALEVEL_SUBJECTS'))
                ->where('md_misc1', 'General')
                ->value('md_id');

            if ($generalPaperId && !$submitted->contains((string) $generalPaperId)) {
                $submitted->push((string) $generalPaperId);
            }
        }

        // The page may have been opened before the school switched between
        // the default and custom lists (or in another tab). Make sure every
        // submitted id belongs to the list that is active NOW, otherwise we
        // would attach another school's subject or a default-list id to a
        // custom-mode class (and the reverse).
        if ($usesCustomSubjects) {
            $validCount = CustomSubject::where('school_id', $schoolId)->whereIn('id', $submitted)->count();
        } else {
            $validCount = DB::table('master_datas')->whereIn('md_id', $submitted)->count();
        }

        if ($validCount !== $submitted->count()) {
            return response()->json([
                'success' => false,
                'message' => 'Your subject list has changed since this page was opened (the school may have switched between default and custom subjects). Please reload the page and try again.',
            ], 422);
        }

        $subjectType = $this->resolveSubjectType($assignment->class_id, $schoolId, $assignment->stream_id);

        // This SYNCS instead of delete-and-recreate so subject_teacher_1/2,
        // assessment_scale_id, counts_towards_aggregate and — crucially — the
        // original subject_id that exam marks are linked to survive an edit
        // (see CustomSubjectController::confirmSwitch / confirmRevert).
        DB::transaction(function () use ($submitted, $assignment, $schoolId, $usesCustomSubjects, $subjectType) {
            $existingRows = ClassSubject::where('class_id', $assignment->class_id)
                ->where('stream_id', $assignment->stream_id)
                ->where('school_id', $schoolId)
                ->get();

            $keepIds = [];

            // Names of every custom subject this school owns (lower-cased).
            $customNamesLower = $usesCustomSubjects
                ? CustomSubject::forSchool($schoolId)->pluck('subject_name')
                    ->mapWithKeys(fn($n) => [mb_strtolower(trim($n)) => true])->all()
                : [];

            if ($usesCustomSubjects) {
                $existingByCustomId = $existingRows->whereNotNull('custom_subject_id')->keyBy('custom_subject_id');

                // Not-yet-converted default-list rows by resolved name, so
                // re-selecting a custom subject with the same name upgrades
                // the row in place (keeps subject_id => marks stay linked).
                $existingMasterByName = $existingRows
                    ->whereNull('custom_subject_id')
                    ->whereNotNull('subject_id')
                    ->reject(fn($row) => $row->is_auto_synced || $row->subject_source === 'school_alevel')
                    ->keyBy(fn($row) => mb_strtolower(trim((string) Helper::recordMdname($row->subject_id))));

                foreach ($submitted as $customSubjectId) {
                    $existing = $existingByCustomId->get($customSubjectId);

                    if (!$existing) {
                        $customName = CustomSubject::where('id', $customSubjectId)->value('subject_name');
                        $existing = $customName ? $existingMasterByName->get(mb_strtolower(trim($customName))) : null;
                    }

                    if ($existing) {
                        $existing->custom_subject_id = $customSubjectId;
                        $existing->subject_source = 'custom';
                        $existing->subject_type = $subjectType;
                        $existing->save();
                        $keepIds[] = $existing->id;
                        continue;
                    }

                    $created = ClassSubject::create([
                        'class_id' => $assignment->class_id,
                        'stream_id' => $assignment->stream_id,
                        'custom_subject_id' => $customSubjectId,
                        'subject_source' => 'custom',
                        'subject_type' => $subjectType,
                        'school_id' => $schoolId,
                    ]);
                    $keepIds[] = $created->id;
                }
            } else {
                $existingBySubjectId = $existingRows->whereNotNull('subject_id')->keyBy('subject_id');

                foreach ($submitted as $subjectId) {
                    $existing = $existingBySubjectId->get($subjectId);

                    if ($existing) {
                        $existing->subject_type = $subjectType;
                        $existing->save();
                        $keepIds[] = $existing->id;
                        continue;
                    }

                    $created = ClassSubject::create([
                        'class_id' => $assignment->class_id,
                        'stream_id' => $assignment->stream_id,
                        'subject_id' => $subjectId,
                        'subject_source' => 'master',
                        'subject_type' => $subjectType,
                        'school_id' => $schoolId,
                    ]);
                    $keepIds[] = $created->id;
                }
            }

            // Rows the screen can't represent are never removed by an edit.
            $protectedIds = $existingRows
                ->filter(fn($row) => $this->isLockedClassSubjectRow($row, $customNamesLower, $usesCustomSubjects))
                ->pluck('id')
                ->all();

            ClassSubject::where('class_id', $assignment->class_id)
                ->where('stream_id', $assignment->stream_id)
                ->where('school_id', $schoolId)
                ->whereNotIn('id', array_merge($keepIds, $protectedIds))
                ->delete();
        });

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
        if (!PermissionHelper::canFeature('view_teacher_assignments')) {
            abort(403, 'Unauthorized Access. Contact School Admin to Assign Teachers.');
        }

        $schoolId = Helper::requireSchool();

        // Self-heal: electives (O-Level) and combinations (A-Level) are saved
        // per student, so make sure every subject a student actually takes
        // has a class_subjects row here — otherwise it can never be given a
        // teacher. Add-only and idempotent; a failure must not block the page.
        try {
            app(\App\Services\StudentSubjectChoiceSyncService::class)->syncSchool($schoolId);
        } catch (\Throwable $e) {
            report($e);
        }

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

        $schoolId = Helper::requireSchool();
        $cleanup = app(\App\Services\OrphanCleanupService::class);

        $streams = Stream::where('class_id', $class_id)->where('school_id', $schoolId)->get();

        // Students first, through the deletion service: a bare ->delete() here
        // used to leave every one of their marks, attendance rows, fees, ID
        // cards, assessment marks and remarks behind.
        $studentIds = Student::where('senior', $class->class_name)
            ->where('school_id', $schoolId)
            ->whereIn('stream', $streams->pluck('stream_name')->all())
            ->pluck('id')
            ->all();

        if (!empty($studentIds)) {
            $result = app(\App\Services\StudentDeletionService::class)->deleteStudents($studentIds);

            if ($result['failed'] > 0) {
                return response()->json([
                    'message' => 'Could not delete ' . $result['failed'] . ' student(s) of this class, so nothing else was removed: ' . implode('; ', array_slice($result['errors'], 0, 2)),
                ], 500);
            }
        }

        // Exam enrolment, marks, assessments, attendance, timetables, pass-slip
        // settings of this class — before its class_subjects rows disappear.
        $cleanup->purgeClassStream($schoolId, $class_id, null);

        foreach ($streams as $stream) {
            ClassSubject::where('class_id', $class_id)->where('stream_id', $stream->stream_id)->where('school_id', $schoolId)->delete();
        }
        ClassSubject::where('class_id', $class_id)->where('school_id', $schoolId)->delete();

        Stream::where('class_id', $class_id)->where('school_id', Helper::requireSchool())->delete();
        ClassStreamAssignment::where('school_id', Helper::requireSchool())->where('class_id', $class_id)->delete();
        Classes::where('school_id', Helper::requireSchool())->where('class_id', $class_id)->delete();
        $class->delete();

        return response()->json(['message' => 'Class, its streams, students, and subjects have been deleted successfully.']);
    }

    public function deleteStream(Stream $stream)
    {
        PermissionHelper::denyUnlessFeature('delete_stream');

        $class_id = $stream->class_id;
        $stream_id = $stream->stream_id;

        // Students still sitting in this stream would be left pointing at a
        // stream that no longer exists (invisible everywhere, yet still counted
        // in marks / fees). Make the user move or remove them deliberately.
        $studentsInStream = Student::where('school_id', Helper::requireSchool())
            ->where('senior', $class_id)
            ->where(function ($q) use ($stream) {
                $q->where('stream', $stream->stream_id);

                if ($stream->stream_id === self::NO_STREAM_SENTINEL) {
                    $q->orWhereNull('stream')->orWhere('stream', '');
                }
            })
            ->count();

        if ($studentsInStream > 0) {
            return response()->json([
                'status' => 'error',
                'message' => "This stream still has {$studentsInStream} student(s). Move or delete them first, then delete the stream.",
            ], 422);
        }

        app(\App\Services\OrphanCleanupService::class)->purgeClassStream(Helper::requireSchool(), $class_id, (string) $stream_id);

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

    /**
     * What renaming this stream would touch (shown in the confirm dialog).
     */
    public function previewStreamRename(Stream $stream)
    {
        PermissionHelper::denyUnlessFeature('edit_class');

        abort_unless((int) $stream->school_id === (int) Helper::requireSchool(), 403);

        return response()->json([
            'status' => 'success',
            'current' => $stream->stream_id,
            'is_no_stream' => $stream->stream_id === self::NO_STREAM_SENTINEL,
            'affected' => app(\App\Services\StreamRenameService::class)->preview($stream),
        ]);
    }

    /**
     * Rename a stream (A -> B, "No Stream" -> A, or the only stream -> "No Stream")
     * and carry everything attached to it along.
     */
    public function renameStream(Request $request, Stream $stream)
    {
        PermissionHelper::denyUnlessFeature('edit_class');

        abort_unless((int) $stream->school_id === (int) Helper::requireSchool(), 403);

        $request->validate([
            'stream_name' => 'nullable|string|max:45',
            'no_stream' => 'nullable|boolean',
        ]);

        $service = app(\App\Services\StreamRenameService::class);
        $makeNoStream = $request->boolean('no_stream');

        $error = $service->validate($stream, $request->stream_name, $makeNoStream);

        if ($error) {
            return response()->json(['status' => 'error', 'message' => $error], 422);
        }

        $newName = $makeNoStream ? self::NO_STREAM_SENTINEL : trim($request->stream_name);

        try {
            $updated = $service->rename($stream, $newName);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'status' => 'error',
                'message' => 'Could not rename the stream, nothing was changed: ' . $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Stream renamed.',
            'stream_name' => $newName,
            'updated' => $updated,
        ]);
    }

    public function assignSupervisor(Request $request)
    {
        if (!PermissionHelper::canFeature('assign_class_supervisor')) {
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
        if (!PermissionHelper::canFeature('assign_class_supervisor')) {
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
            ->where('school_id', session('LoggedSchool'))
            ->where('class_id', $classId)
            ->where('stream_id', $streamId)
            ->first();

        if (!$assignment) {
            return redirect()->back()->with('error', 'Class-Stream Assignment not found.');
        }

        // Get assigned subjects (default list: matched on subject_id)
        $assignedSubjects = $assignment->classSubjects
            ->whereNotNull('subject_id')
            ->pluck('subject_id')
            ->all();

        // Get class type (O-Level, A-Level, Primary Theology, Primary Secular,
        // Secondary O-Level, or Secondary A-Level)
        $oLevelIds = Helper::MasterRecords(config('constants.options.O_LEVEL'))->pluck('md_id')->toArray();
        $aLevelIds = Helper::MasterRecords(config('constants.options.A_LEVEL'))->pluck('md_id')->toArray();
        $primaryTheologyIds = Helper::MasterRecords(config('constants.options.PRIMARY_THEOLOGY_CLASSES'))->pluck('md_id')->toArray();
        $primarySecularIds = Helper::MasterRecords(config('constants.options.PRIMARY_SECULAR_CLASSES'))->pluck('md_id')->toArray();
        $secondaryOLevelIds = Helper::MasterRecords(config('constants.options.SECONDARY_OLEVEL_CLASSES'))->pluck('md_id')->toArray();
        $secondaryALevelIds = Helper::MasterRecords(config('constants.options.SECONDARY_ALEVEL_CLASSES'))->pluck('md_id')->toArray();

        if (in_array($classId, $oLevelIds)) {
            $classType = 'O-Level';
        } elseif (in_array($classId, $aLevelIds)) {
            $classType = 'A-Level';
        } elseif (in_array($classId, $primaryTheologyIds)) {
            $classType = 'Primary Theology';
        } elseif (in_array($classId, $primarySecularIds)) {
            $classType = 'Primary Secular';
        } elseif (in_array($classId, $secondaryOLevelIds)) {
            $classType = 'Secondary O-Level';
        } elseif (in_array($classId, $secondaryALevelIds)) {
            $classType = 'Secondary A-Level';
        } else {
            $classType = 'A-Level';
        }

        $SecondaryClasses = Helper::MasterRecordMerge(
            config('constants.options.O_LEVEL'),
            config('constants.options.A_LEVEL')
        )->merge(Helper::MasterRecords(config('constants.options.SECONDARY_OLEVEL_CLASSES')))
            ->merge(Helper::MasterRecords(config('constants.options.SECONDARY_ALEVEL_CLASSES')));

        $PrimaryClasses = Helper::MasterRecords(config('constants.options.PRIMARY_THEOLOGY_CLASSES'));
        $PrimarySecularClasses = Helper::MasterRecords(config('constants.options.PRIMARY_SECULAR_CLASSES'));

        // SECONDARY Subjects (secular O-Level / A-Level — defaults only for
        // A-Level: General Paper + the two subsidiaries)
        $SECONDARY_OLEVEL_SUBJECTS = Helper::MasterRecords(config('constants.options.SECONDARY_OLEVEL_SUBJECTS'));
        $SECONDARY_ALEVEL_SUBJECTS = Helper::MasterRecords(config('constants.options.SECONDARY_ALEVEL_SUBJECTS'));

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
            'primarySecularSubjects',
            'SECONDARY_OLEVEL_SUBJECTS',
            'SECONDARY_ALEVEL_SUBJECTS'
        ));
    }

    /**
     * Edit-subjects screen for schools running in custom-subjects mode.
     */
    private function editClassSubjectsCustom(School $school, $classId, $streamId)
    {
        $assignment = ClassStreamAssignment::where('school_id', $school->id)
            ->where('class_id', $classId)
            ->where('stream_id', $streamId)
            ->first();

        if (!$assignment) {
            return redirect()->back()->with('error', 'Class-Stream Assignment not found.');
        }

        $classType = $this->resolveSubjectType($classId, $school->id, $streamId);

        $rows = ClassSubject::where('school_id', $school->id)
            ->where('class_id', $classId)
            ->where('stream_id', $streamId)
            ->get();

        $allCustom = CustomSubject::forSchool($school->id)->get();

        // 1) Rows that already point at a custom subject — whatever their
        //    subject_source says (a row switched back to the default list and
        //    then to custom again keeps its custom_subject_id).
        $assignedSubjectIds = $rows->whereNotNull('custom_subject_id')
            ->pluck('custom_subject_id')
            ->map(fn($v) => (int) $v)
            ->all();

        // 2) Legacy default-list rows with no custom link yet: tick the
        //    same-named custom subject of this category (updateClassSubjects
        //    upgrades them in place on save using the same rule).
        $byName = $allCustom->where('class_type', $classType)
            ->keyBy(fn($c) => mb_strtolower(trim($c->subject_name)));

        foreach ($rows->whereNull('custom_subject_id')->whereNotNull('subject_id') as $row) {
            if ($row->is_auto_synced || $row->subject_source === 'school_alevel') {
                continue;
            }
            $name = mb_strtolower(trim((string) Helper::recordMdname($row->subject_id)));
            if ($name !== '' && $byName->has($name)) {
                $assignedSubjectIds[] = (int) $byName[$name]->id;
            }
        }

        $assignedSubjectIds = array_values(array_unique($assignedSubjectIds));

        // Active subjects of this category, PLUS anything already attached to
        // the class even if it has since been deactivated (the delete button
        // on Manage Subjects deactivates in-use subjects). Without this the
        // attached-but-inactive subject was invisible here and the next save
        // silently removed it from the class.
        $customSubjects = CustomSubject::forSchool($school->id)
            ->where(function ($q) use ($classType, $assignedSubjectIds) {
                $q->where(function ($q2) use ($classType) {
                    $q2->where('class_type', $classType)->where('is_active', true);
                })->orWhereIn('id', $assignedSubjectIds);
            })
            ->orderBy('subject_name')
            ->get();

        // Rows this screen can't edit (A-Level combination subjects etc.),
        // shown read-only so the admin knows they exist and are kept.
        $customNamesLower = $allCustom->mapWithKeys(fn($c) => [mb_strtolower(trim($c->subject_name)) => true])->all();
        $lockedSubjectNames = $rows
            ->filter(fn($row) => $this->isLockedClassSubjectRow($row, $customNamesLower, true))
            ->map(fn($row) => $row->display_name)
            ->filter()
            ->unique()
            ->values()
            ->all();

        return view('Class.edit-class-custom', compact(
            'assignment',
            'assignedSubjectIds',
            'customSubjects',
            'lockedSubjectNames',
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
        PermissionHelper::denyUnlessFeature('view_classes');

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