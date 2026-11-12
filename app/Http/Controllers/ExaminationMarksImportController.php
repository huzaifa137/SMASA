<?php

namespace App\Http\Controllers;

use App\Exports\MarksImportTemplateExport;
use App\Helpers\PermissionHelper;
use App\Models\Examination;
use App\Models\ExaminationClass;
use App\Models\ExaminationMark;
use App\Services\ExaminationMarksImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Examinations -> Marks Entry -> "Import marks from Excel".
 *
 * Step 1  pick a class-stream, download the template (or use your own marklist), upload it.
 * Step 2  preview: columns matched to subjects, pupils matched, problems, what will change. Nothing is saved yet.
 * Step 3  confirm: the marks are saved with the same grading as the Marks Entry screen.
 */
class ExaminationMarksImportController extends Controller
{
    public function __construct(private ExaminationMarksImportService $service)
    {
    }

    public function index($examId)
    {
        $exam = $this->exam($examId);

        return view('Examination.marks-import', [
            'exam' => $exam,
            'classOptions' => $this->classOptions($exam),
            'selected' => null,
            'plan' => null,
        ]);
    }

    public function template(Request $request, $examId)
    {
        $exam = $this->exam($examId);
        [$classId, $streamId] = $this->classStream($request, $exam);

        $ctx = $this->service->importableSubjects($exam, $classId, $streamId, Session('LoggedTeacher'));
        $subjects = $ctx['subjects'];

        $marks = ExaminationMark::where('examination_id', $exam->id)
            ->where('school_id', $exam->school_id)
            ->where('class_id', $classId)
            ->where('stream_id', $streamId)
            ->get()
            ->keyBy(fn($m) => $m->student_id . '|' . ($m->custom_subject_id ?? 0) . '|' . ($m->subject_id ?? 0));

        $rows = [];
        foreach ($this->service->students($exam, $classId, $streamId) as $s) {
            $line = [$s['id'], $s['firstname'], $s['lastname']];
            foreach ($subjects as $sub) {
                $m = $marks->get($s['id'] . '|' . ($sub['row']->custom_subject_id ?? 0) . '|' . ($sub['row']->subject_id ?? 0));
                $line[] = $m && $m->marks_obtained !== null ? (float) $m->marks_obtained : null;
            }
            $rows[] = $line;
        }

        $file = 'marks-' . Str::slug(Helper::recordMdname($classId) . '-' . $streamId . '-' . $exam->exam_name) . '.xlsx';

        return Excel::download(new MarksImportTemplateExport(array_column($subjects, 'name'), $rows, Helper::recordMdname($classId) . ' ' . $streamId), $file);
    }

    /** Upload (new file) or re-check (same file, other sheet / other column choices). */
    public function preview(Request $request, $examId)
    {
        $exam = $this->exam($examId);
        [$classId, $streamId] = $this->classStream($request, $exam);
        $this->requireOpen($exam);

        $request->validate([
            'file' => 'nullable|file|mimes:xlsx,xls,csv,txt|max:8192',
            'token' => ['nullable', 'regex:/^[A-Za-z0-9]{40}\.(xlsx|xls|csv|txt)$/'],
        ]);

        $schoolId = $exam->school_id;
        $token = $request->input('token');

        if ($request->hasFile('file')) {
            $ext = strtolower($request->file('file')->getClientOriginalExtension() ?: 'xlsx');
            $token = Str::random(40) . '.' . $ext;
            $request->file('file')->storeAs("marks-imports/{$schoolId}", $token, 'local');
        }
        if (!$token || !Storage::disk('local')->exists("marks-imports/{$schoolId}/{$token}")) {
            return back()->with('error', 'Please choose the file to import.')->withInput();
        }

        try {
            [$sheetNames, $rows, $sheetIndex] = $this->readSheet($schoolId, $token, $request->input('sheet'), $classId, $streamId);
        } catch (\Throwable $e) {
            report($e);
            return back()->with('error', 'That file could not be read. Please upload an .xlsx or .csv file.')->withInput();
        }

        $mapping = $request->has('map') ? $this->cleanMapping($request->input('map')) : null;
        $plan = $this->service->buildPlan($exam, $classId, $streamId, $rows, $mapping, $request->boolean('overwrite'), Session('LoggedTeacher'));

        return view('Examination.marks-import', [
            'exam' => $exam,
            'classOptions' => $this->classOptions($exam),
            'selected' => ['class_id' => $classId, 'stream_id' => $streamId, 'token' => $token, 'sheets' => $sheetNames, 'sheet' => $sheetIndex, 'overwrite' => $request->boolean('overwrite')],
            'plan' => $plan,
        ]);
    }

    public function commit(Request $request, $examId)
    {
        $exam = $this->exam($examId);
        [$classId, $streamId] = $this->classStream($request, $exam);
        $this->requireOpen($exam);

        $request->validate(['token' => ['required', 'regex:/^[A-Za-z0-9]{40}\.(xlsx|xls|csv|txt)$/']]);

        $schoolId = $exam->school_id;
        $token = $request->input('token');
        if (!Storage::disk('local')->exists("marks-imports/{$schoolId}/{$token}")) {
            return redirect()->route('examination.marks.import', $exam->id)->with('error', 'The uploaded file has expired. Please upload it again.');
        }

        try {
            [, $rows] = $this->readSheet($schoolId, $token, $request->input('sheet'), $classId, $streamId);
            $teacherId = Session('LoggedTeacher');
            $plan = $this->service->buildPlan($exam, $classId, $streamId, $rows, $this->cleanMapping($request->input('map', [])), $request->boolean('overwrite'), $teacherId);

            if (!$plan['ok'] || !$plan['changes']) {
                return redirect()->route('examination.marks.import', $exam->id)->with('error', 'There is nothing to import.');
            }

            $saved = $this->service->commit($exam, $classId, $streamId, $plan, $teacherId);
        } catch (\Throwable $e) {
            report($e);
            return redirect()->route('examination.marks.import', $exam->id)->with('error', 'The import failed and nothing was saved: ' . $e->getMessage());
        }

        try {
            \App\Audit\Audit::log('marks_imported', "Imported {$saved} mark(s) from Excel into examination #{$exam->id}", [
                'metadata' => ['examination_id' => $exam->id, 'class_id' => $classId, 'stream_id' => $streamId, 'marks' => $saved],
            ]);
        } catch (\Throwable $e) {
            report($e);
        }

        Storage::disk('local')->delete("marks-imports/{$schoolId}/{$token}");

        return redirect()->route('examination.marks.import', $exam->id)
            ->with('success', "{$saved} mark(s) imported for " . Helper::recordMdname($classId) . ($streamId && $streamId !== '-' && $streamId !== 'NO_STREAM' ? " {$streamId}" : '') . '.');
    }

    // ── helpers ──────────────────────────────────────────────────────────────────

    private function exam($examId): Examination
    {
        PermissionHelper::denyUnlessFeature('enter_marks');

        $exam = Examination::where('id', $examId)->where('school_id', Session('LoggedSchool'))->firstOrFail();
        $exam->syncStatus();

        return $exam;
    }

    private function requireOpen(Examination $exam): void
    {
        if (!in_array($exam->status, ExaminationMarksImportService::OPEN_STATUSES, true)) {
            abort(403, 'Marks entry is closed for this examination, so marks cannot be imported.');
        }
    }

    /** The class-stream must be one of this examination's own. */
    private function classStream(Request $request, Examination $exam): array
    {
        if ($request->filled('cs')) {                       // the class dropdown sends "classId|stream"
            [$c, $st] = array_pad(explode('|', (string) $request->input('cs'), 2), 2, null);
            $request->merge(['class_id' => $c, 'stream_id' => $st]);
        }

        $data = $request->validate(['class_id' => 'required|integer', 'stream_id' => 'required|string|max:50']);

        ExaminationClass::where('examination_id', $exam->id)
            ->where('school_id', $exam->school_id)
            ->where('class_id', $data['class_id'])
            ->where('stream_id', $data['stream_id'])
            ->firstOrFail();

        return [(int) $data['class_id'], (string) $data['stream_id']];
    }

    private function classOptions(Examination $exam)
    {
        return ExaminationClass::where('examination_id', $exam->id)
            ->where('school_id', $exam->school_id)
            ->get()
            ->map(function ($ec) {
                $stream = ($ec->stream_id && !in_array($ec->stream_id, ['-', 'NO_STREAM'], true)) ? ' ' . $ec->stream_id : '';

                return ['class_id' => $ec->class_id, 'stream_id' => $ec->stream_id, 'label' => Helper::recordMdname($ec->class_id) . $stream];
            })
            ->sortBy('label', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();
    }

    /** @return array [sheetNames, rows, chosenIndex] */
    private function readSheet($schoolId, string $token, $sheetParam, int $classId, string $streamId): array
    {
        $path = Storage::disk('local')->path("marks-imports/{$schoolId}/{$token}");
        $reader = IOFactory::createReaderForFile($path);
        $reader->setReadDataOnly(true);
        $book = $reader->load($path);
        $names = $book->getSheetNames();

        $index = is_numeric($sheetParam) && isset($names[(int) $sheetParam]) ? (int) $sheetParam : $this->guessSheet($names, $classId, $streamId);
        $rows = $book->getSheet($index)->toArray(null, true, false, false);
        $book->disconnectWorksheets();

        return [$names, $rows, $index];
    }

    /** Several-sheet marklist: start on the sheet whose name matches the chosen class (e.g. "PRI FOUR-EAST"). */
    private function guessSheet(array $names, int $classId, string $streamId): int
    {
        $norm = fn($s) => preg_replace('/[^A-Z0-9]/', '', strtoupper($s));
        $class = $norm(Helper::recordMdname($classId));
        $stream = in_array($streamId, ['-', 'NO_STREAM'], true) ? '' : $norm($streamId);

        foreach ($names as $i => $n) {
            $x = $norm($n);
            if ($x === $class . $stream || $x === $norm($class . $stream)) {
                return $i;
            }
        }
        foreach ($names as $i => $n) {
            $x = $norm($n);
            if ($stream !== '' && str_contains($x, $stream) && str_contains($x, $class)) {
                return $i;
            }
        }

        return 0;
    }

    private function cleanMapping($map): array
    {
        $out = [];
        foreach ((array) $map as $col => $v) {
            if (is_numeric($col)) {
                $out[(int) $col] = ($v === '' || $v === null) ? '' : (int) $v;
            }
        }

        return $out;
    }
}
