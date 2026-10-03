<?php

namespace App\Http\Controllers;

use App\Helpers\PermissionHelper;
use App\Models\Examination;
use App\Models\ExaminationClass;
use App\Models\ExaminationMark;
use App\Models\ExaminationSubjectSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Examinations → Exam Subjects.
 *
 * Lets an examination choose, per class-stream and per subject:
 *   - "Sat in this exam"  → marks entry is open for the subject
 *   - "Show on report"    → the subject appears on pass slips / report cards
 *
 * Only exceptions are stored (see the migration); a subject left ticked on
 * both keeps the historic behaviour.
 */
class ExaminationSubjectController extends Controller
{
    public function edit($examId)
    {
        PermissionHelper::denyUnlessFeature('edit_exam');

        $schoolId = Session('LoggedSchool');

        $exam = Examination::where('id', $examId)
            ->where('school_id', $schoolId)
            ->firstOrFail();

        $examClasses = ExaminationClass::where('examination_id', $exam->id)
            ->where('school_id', $schoolId)
            ->get();

        $settings = ExaminationSubjectSetting::forExam($exam->id);

        // How many marks are already saved per subject, so the screen can
        // warn before something with data in it is switched off / hidden.
        $markCounts = ExaminationMark::where('examination_id', $exam->id)
            ->where('school_id', $schoolId)
            ->whereNotNull('marks_obtained')
            ->selectRaw('class_id, stream_id, subject_id, custom_subject_id, COUNT(*) as c')
            ->groupBy('class_id', 'stream_id', 'subject_id', 'custom_subject_id')
            ->get()
            ->mapWithKeys(fn($r) => [ExaminationSubjectSetting::keyFor($r) => (int) $r->c]);

        $classes = [];
        foreach ($examClasses as $ec) {
            $subjects = DB::table('class_subjects')
                ->where('school_id', $schoolId)
                ->where('class_id', $ec->class_id)
                ->where('stream_id', (string) $ec->stream_id)
                ->get()
                // A choice subject no student currently takes has nothing to sit.
                ->filter(fn($cs) => !Helper::choiceSubjectHasNoStudents($schoolId, $cs))
                ->values();

            if ($subjects->isEmpty()) {
                continue;
            }

            $rows = $subjects->map(function ($cs) use ($settings, $markCounts) {
                $key = ExaminationSubjectSetting::keyFor($cs);
                $setting = $settings[$key] ?? null;

                return (object) [
                    'key' => $key,
                    'subject_id' => $cs->subject_id,
                    'custom_subject_id' => $cs->custom_subject_id ?? null,
                    'name' => Helper::classSubjectName($cs),
                    'teacher' => Helper::teacherFullName($cs->subject_teacher_1 ?? null),
                    'sat' => $setting ? (bool) $setting->marks_entry_enabled : true,
                    'show' => $setting ? (bool) $setting->show_on_report : true,
                    'marks' => $markCounts[$key] ?? 0,
                ];
            })->sortBy('name')->values();

            $classes[] = (object) [
                'class_id' => $ec->class_id,
                'stream_id' => $ec->stream_id,
                'stream_key' => (string) $ec->stream_id,
                'label' => (Helper::recordMdname($ec->class_id) ?? $ec->class_id)
                    . (($ec->stream_id && $ec->stream_id !== 'NO_STREAM') ? ' — ' . $ec->stream_id : ''),
                'subjects' => $rows,
            ];
        }

        return view('Examination.subjects.edit', compact('exam', 'classes'));
    }

    public function save(Request $request, $examId)
    {
        if (!PermissionHelper::canFeature('edit_exam')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. You do not have permission to change exam subjects.'], 403);
        }

        $request->validate([
            'rows' => 'required|array',
            'rows.*.class_id' => 'required|integer',
            'rows.*.stream_id' => 'nullable|string|max:191',
            'rows.*.subject_id' => 'nullable|integer',
            'rows.*.custom_subject_id' => 'nullable|integer',
            'rows.*.sat' => 'required|boolean',
            'rows.*.show' => 'required|boolean',
        ]);

        $schoolId = Session('LoggedSchool');

        $exam = Examination::where('id', $examId)
            ->where('school_id', $schoolId)
            ->firstOrFail();

        // Only accept rows that really belong to a class-stream in this
        // exam AND a subject that class-stream really has — never trust
        // the posted ids blindly.
        $examClasses = ExaminationClass::where('examination_id', $exam->id)
            ->where('school_id', $schoolId)
            ->get();

        $validKeys = [];
        foreach ($examClasses as $ec) {
            DB::table('class_subjects')
                ->where('school_id', $schoolId)
                ->where('class_id', $ec->class_id)
                ->where('stream_id', (string) $ec->stream_id)
                ->get()
                ->each(function ($cs) use (&$validKeys) {
                    $validKeys[ExaminationSubjectSetting::keyFor($cs)] = $cs;
                });
        }

        $toStore = [];
        foreach ($request->rows as $r) {
            $probe = (object) [
                'class_id' => $r['class_id'],
                'stream_id' => $r['stream_id'] ?? '',
                'subject_id' => $r['subject_id'] ?? null,
                'custom_subject_id' => $r['custom_subject_id'] ?? null,
            ];
            $key = ExaminationSubjectSetting::keyFor($probe);

            if (!isset($validKeys[$key])) {
                continue;
            }

            $cs = $validKeys[$key];
            $sat = (bool) $r['sat'];
            // A subject that is not sat cannot appear on the report.
            $show = $sat ? (bool) $r['show'] : false;

            if ($sat && $show) {
                continue; // default state — no row needed
            }

            $toStore[$key] = [
                'examination_id' => $exam->id,
                'school_id' => $schoolId,
                'class_id' => $cs->class_id,
                'stream_id' => $cs->stream_id,
                'subject_id' => $cs->subject_id ?: null,
                'custom_subject_id' => $cs->custom_subject_id ?? null,
                'marks_entry_enabled' => $sat,
                'show_on_report' => $show,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::beginTransaction();
        try {
            // Replace the exam's exceptions for the class-streams that were
            // submitted (so class-streams not on the page are untouched).
            $submittedClasses = collect($request->rows)
                ->map(fn($r) => (int) $r['class_id'] . '|' . ($r['stream_id'] ?? ''))
                ->unique();

            ExaminationSubjectSetting::where('examination_id', $exam->id)
                ->get()
                ->filter(fn($s) => $submittedClasses->contains((int) $s->class_id . '|' . ($s->stream_id ?? '')))
                ->each->delete();

            if (!empty($toStore)) {
                ExaminationSubjectSetting::insert(array_values($toStore));
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }

        ExaminationSubjectSetting::forgetCache($exam->id);

        return response()->json([
            'success' => true,
            'message' => 'Exam subjects saved.',
            'exceptions' => count($toStore),
        ]);
    }
}
