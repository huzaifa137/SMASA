<?php

namespace App\Http\Controllers;

use App\Helpers\PermissionHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Examinations -> Subject Icons.
 * Lets a school choose, for each nursery subject, one of the system's icons or an image of its own. The report
 * card (all three nursery designs) then shows that icon instead of the automatic by-name match.
 */
class SubjectIconController extends Controller
{
    private const ALLOWED_EXT = ['png', 'jpg', 'jpeg', 'webp'];     // no SVG: uploaded SVG can carry scripts

    public function index()
    {
        PermissionHelper::denyUnlessFeature('manage_report_card_settings');
        $schoolId = Session('LoggedSchool');

        $ready = Schema::hasColumn('class_subjects', 'icon');

        $classIds = Helper::nurseryClassIds();
        $rows = $ready && $classIds
            ? DB::table('class_subjects')->where('school_id', $schoolId)->whereIn('class_id', $classIds)->orderBy('class_id')->orderBy('id')->get()
            : collect();

        $groups = $rows->groupBy('class_id')->map(function ($subjects, $classId) {
            return [
                'class' => Helper::recordMdname($classId),
                'subjects' => $subjects->map(fn($cs) => [
                    'id' => $cs->id,
                    'name' => Helper::classSubjectName($cs),
                    'chosen' => $cs->icon ? Helper::subjectIconPublicUrl($cs->icon) : null,
                    'auto' => Helper::nurserySubjectIconUrl(Helper::classSubjectName($cs)),
                ])->values(),
            ];
        })->values();

        return view('Examination.subject-icons', ['groups' => $groups, 'library' => $this->library(), 'ready' => $ready]);
    }

    public function update(Request $request, $id)
    {
        PermissionHelper::denyUnlessFeature('manage_report_card_settings');
        $schoolId = Session('LoggedSchool');

        if (!Schema::hasColumn('class_subjects', 'icon')) {
            return back()->with('error', 'Please run the database update (php artisan migrate) first.');
        }

        $request->validate([
            'icon' => 'nullable|string|max:150',
            'file' => 'nullable|file|mimes:png,jpg,jpeg,webp|max:512',
            'reset' => 'nullable|boolean',
        ]);

        $row = DB::table('class_subjects')->where('id', $id)->where('school_id', $schoolId)->first();
        abort_unless($row, 404);

        $path = null;
        if ($request->boolean('reset')) {
            $path = null;
        } elseif ($request->hasFile('file')) {
            $ext = strtolower($request->file('file')->getClientOriginalExtension());
            abort_unless(in_array($ext, self::ALLOWED_EXT, true), 422);
            $name = Str::random(24) . '.' . $ext;
            $request->file('file')->storeAs("subject-icons/{$schoolId}", $name, 'public');
            $path = "storage/subject-icons/{$schoolId}/{$name}";
        } elseif ($request->filled('icon')) {
            $file = basename($request->input('icon'));                // library icons only - never a free path
            if (!in_array($file, array_column($this->library(), 'file'), true)) {
                return back()->with('error', 'That icon is not in the library.');
            }
            $path = 'images/subject-icons/' . $file;
        } else {
            return back()->with('error', 'Choose an icon or upload an image first.');
        }

        $query = DB::table('class_subjects')->where('school_id', $schoolId);
        if ($request->boolean('apply_all')) {
            // the same subject in every nursery class of this school
            $query->whereIn('class_id', Helper::nurseryClassIds())
                ->where('custom_subject_id', $row->custom_subject_id)
                ->where('subject_id', $row->subject_id);
        } else {
            $query->where('id', $row->id);
        }
        $query->update(['icon' => $path, 'updated_at' => now()]);

        return back()->with('success', 'Icon saved for ' . Helper::classSubjectName($row) . '.');
    }

    /** The system icon library: every image in public/images/subject-icons. */
    private function library(): array
    {
        $dir = public_path('images/subject-icons');
        if (!is_dir($dir)) {
            return [];
        }

        return collect(File::files($dir))
            ->filter(fn($f) => in_array(strtolower($f->getExtension()), self::ALLOWED_EXT, true))
            ->map(fn($f) => [
                'file' => $f->getFilename(),
                'label' => str_replace(['Wtritting', '_'], ['Writing', ' '], pathinfo($f->getFilename(), PATHINFO_FILENAME)),
                'url' => asset('images/subject-icons/' . rawurlencode($f->getFilename())),
            ])
            ->sortBy('label', SORT_NATURAL | SORT_FLAG_CASE)
            ->values()
            ->all();
    }
}
