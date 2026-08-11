<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Services\SchoolProductMergeService;
use Illuminate\Http\Request;
use RuntimeException;

class SchoolProductController extends Controller
{
    public function __construct(private SchoolProductMergeService $service)
    {
    }

    /**
     * Management screen: shows the categories the school currently
     * belongs to, a picker for merging another one in, and (once more
     * than one is attached) a split panel.
     */
    public function manage()
    {
        $school = School::with('products')->findOrFail(Helper::requireSchool());

        return view('School.manage-school-products', [
            'school' => $school,
            'currentProducts' => $school->products,
            'availableProducts' => $this->service->availableProducts($school),
            'classTypes' => Helper::schoolClassTypes($school->id),
        ]);
    }

    /**
     * Merge another School Product category into this school. Purely
     * additive - safe, reversible via split(), no confirmation typing
     * required.
     */
    public function merge(Request $request)
    {
        $this->authorizeManage();

        $request->validate([
            'product_md_id' => 'required|integer',
        ]);

        $school = School::findOrFail(Helper::requireSchool());

        try {
            $this->service->merge($school, (int) $request->product_md_id, $this->currentUserId());
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Category merged in. Its classes and subjects now appear alongside your existing ones.');
    }

    /**
     * AJAX: counts of what a proposed split would permanently delete, so
     * the confirmation dialog can show real numbers before anything
     * destructive happens.
     */
    public function previewSplit(Request $request)
    {
        $this->authorizeManage();

        $request->validate([
            'remove_product_md_id' => 'required|integer',
            'keep_product_md_id' => 'required|integer|different:remove_product_md_id',
        ]);

        $school = School::findOrFail(Helper::requireSchool());

        try {
            $impact = $this->service->previewSplit(
                $school,
                (int) $request->remove_product_md_id,
                (int) $request->keep_product_md_id
            );
        } catch (RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json(['success' => true, 'impact' => $impact]);
    }

    /**
     * Performs the split. Destructive and irreversible - gated behind
     * admin-only access and a typed confirmation of the school's name on
     * top of the preview the person already saw.
     */
    public function split(Request $request)
    {
        // Splitting deletes real student/marks/finance data permanently,
        // so it's restricted to TechSate admins rather than every school
        // admin who can merge. Adjust this to match your own role model
        // if school-level admins should also be allowed to do this.
        if (!Helper::isAdminAllowed()) {
            abort(403, 'Only a TechSate administrator can split merged categories apart.');
        }

        $school = School::findOrFail(Helper::requireSchool());

        $request->validate([
            'remove_product_md_id' => 'required|integer',
            'keep_product_md_id' => 'required|integer|different:remove_product_md_id',
            'confirm_school_name' => 'required|string',
        ]);

        if (trim(strtolower($request->confirm_school_name)) !== trim(strtolower($school->name))) {
            return back()->with('error', 'The school name you typed does not match. Nothing was deleted.');
        }

        try {
            $impact = $this->service->split(
                $school,
                (int) $request->remove_product_md_id,
                (int) $request->keep_product_md_id,
                $this->currentUserId()
            );
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('school.products.manage')
            ->with('success', sprintf(
                'Split complete. Removed %d class(es), %d stream(s) and %d student(s) that belonged only to the dropped category.',
                $impact['classes'],
                $impact['streams'],
                $impact['students']
            ));
    }

    private function authorizeManage(): void
    {
        if (!Helper::isTechSateAdminOrSchoolAdminsOrTechSateSalesRepresentatives()) {
            abort(403, 'You do not have permission to manage School Products.');
        }
    }

    private function currentUserId(): ?int
    {
        return session('LoggedAdmin') ?? session('LoggedTeacher') ?? null;
    }
}