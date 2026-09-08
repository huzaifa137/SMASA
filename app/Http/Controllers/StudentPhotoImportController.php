<?php

namespace App\Http\Controllers;

use App\Helpers\PermissionHelper;
use App\Models\Classroom;
use App\Models\School;
use App\Services\StudentPhotoImportService;
use Illuminate\Http\Request;

/**
 * StudentPhotoImportController
 * ─────────────────────────────────────────────────────────────────────────
 * Bulk Photo Import: lets a school upload a batch of student photos for one
 * class + stream in a single pass, matching each file to a student by LIN
 * No., Registration Number, or full name (admin's choice). Every request
 * runs through StudentPhotoImportService, which always validates first —
 * this controller just wires the request through and enforces permissions.
 */
class StudentPhotoImportController extends Controller
{
    public function form()
    {
        PermissionHelper::denyUnlessFeature('import_students');

        $schoolId = Helper::requireSchool();
        $school = School::findOrFail($schoolId);
        $classrooms = Classroom::where('school_id', $schoolId)->get();

        return view('student.bulk-import-photos', compact('schoolId', 'school', 'classrooms'));
    }

    public function process(Request $request, StudentPhotoImportService $service)
    {
        if (!PermissionHelper::canFeature('import_students')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized. You do not have permission to import student photos.',
            ], 403);
        }

        $validated = $request->validate([
            'class_id' => 'required|string',
            'stream_id' => 'required|string',
            'match_by' => 'required|in:lin,reg,name',
            'photos' => 'required|array|min:1|max:300',
            'photos.*' => 'file|max:8192', // 8MB per photo; real image checks happen in the service
        ]);

        $schoolId = Helper::requireSchool();

        $report = $service->run(
            $schoolId,
            $validated['class_id'],
            $validated['stream_id'],
            $validated['match_by'],
            $request->file('photos'),
            $request->boolean('dry_run', true)
        );

        return response()->json($report);
    }
}
