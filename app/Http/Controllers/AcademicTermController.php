<?php

namespace App\Http\Controllers;

use App\Models\AcademicTerm;
use App\Models\AcademicYear;
use App\Models\BillingTerm;
use App\Services\BillingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Platform-wide terms of an academic year (Term I / II / III) with their dates,
 * and the ONE term that is active for the whole platform. Managed by the
 * platform administrators from /add-academic-year; the billing module reads
 * its dates and its "current" term from here.
 *
 * Routes: routes/billing.php (admin.can:academic_calendar.manage).
 */
class AcademicTermController extends Controller
{
    public function __construct(private BillingService $billing)
    {
    }

    private function rules(): array
    {
        return [
            'academic_year_id' => 'required|integer|exists:academic_years,id',
            'term'             => 'required|integer|in:1,2,3',
            'start_date'       => 'required|date',
            'end_date'         => 'required|date|after_or_equal:start_date',
        ];
    }

    /** Dates must sit inside the year, must not overlap a sibling term and must keep Term I < II < III. */
    private function datesProblem(AcademicYear $year, int $termNo, string $start, string $end, ?int $ignoreId = null): ?string
    {
        $start = date('Y-m-d', strtotime($start));
        $end = date('Y-m-d', strtotime($end));

        if ($year->start_date && $start < $year->start_date->format('Y-m-d')) {
            return 'Term cannot start before the academic year starts (' . $year->start_date->format('j M Y') . ').';
        }
        if ($year->end_date && $end > $year->end_date->format('Y-m-d')) {
            return 'Term cannot end after the academic year ends (' . $year->end_date->format('j M Y') . ').';
        }

        $others = AcademicTerm::where('academic_year_id', $year->id)
            ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))->get();

        foreach ($others as $o) {
            $os = $o->start_date->format('Y-m-d');
            $oe = $o->end_date->format('Y-m-d');

            if ($start <= $oe && $end >= $os) {
                return 'These dates overlap ' . $o->term_label . ' (' . $o->start_date->format('j M') . ' – ' . $o->end_date->format('j M Y') . ').';
            }
            if (($o->term < $termNo && $os >= $start) || ($o->term > $termNo && $os <= $start)) {
                return 'Terms must follow each other in order: ' . $o->term_label . ' cannot come ' . ($o->term < $termNo ? 'after' : 'before') . ' ' . \App\Support\Term::label($termNo) . '.';
            }
        }

        return null;
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());
        $year = AcademicYear::findOrFail($data['academic_year_id']);

        if (AcademicTerm::where('academic_year_id', $year->id)->where('term', $data['term'])->exists()) {
            return response()->json(['message' => \App\Support\Term::label($data['term']) . ' already exists for ' . $year->name . '.'], 422);
        }
        if ($problem = $this->datesProblem($year, (int) $data['term'], $data['start_date'], $data['end_date'])) {
            return response()->json(['message' => $problem], 422);
        }

        $term = AcademicTerm::create($data + ['is_active' => false]);

        return response()->json(['message' => $term->load('year')->name . ' created.', 'data' => $term], 201);
    }

    public function update(Request $request, $id)
    {
        $term = AcademicTerm::with('year')->findOrFail($id);
        $data = $request->validate([
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
        ]);

        if ($problem = $this->datesProblem($term->year, (int) $term->term, $data['start_date'], $data['end_date'], $term->id)) {
            return response()->json(['message' => $problem], 422);
        }

        $term->update($data);

        // The billing term built from this calendar term follows its dates.
        BillingTerm::where('academic_year_id', $term->academic_year_id)->where('term', $term->term)
            ->update(['start_date' => $data['start_date'], 'end_date' => $data['end_date']]);

        return response()->json(['message' => $term->name . ' updated.']);
    }

    /**
     * Make this term THE active term of the platform. Its academic year becomes the
     * active year too (so year and term can never disagree), and the matching billing
     * term — if the administrators already created one — becomes the current billing term.
     */
    public function activate($id)
    {
        $term = AcademicTerm::with('year')->findOrFail($id);
        $billingNote = '';

        DB::transaction(function () use ($term, &$billingNote) {
            AcademicTerm::where('is_active', true)->update(['is_active' => false]);
            $term->update(['is_active' => true]);

            AcademicYear::where('is_active', 1)->where('id', '!=', $term->academic_year_id)->update(['is_active' => 0]);
            AcademicYear::where('id', $term->academic_year_id)->update(['is_active' => 1]);

            $billing = $term->billingTerm();
            if ($billing) {
                BillingTerm::where('is_current', true)->update(['is_current' => false]);
                $billing->update(['is_current' => true, 'status' => 'open']);
                $this->billing->log('term', "{$billing->name} is now the current billing term (active academic term changed).", ['term_id' => $billing->id]);
                $billingNote = ' It is now the current billing term as well.';
            } else {
                $billingNote = ' There is no billing term for it yet — create one under Billing → Terms.';
            }
        });

        \App\Support\ActiveYear::forget();

        return response()->json(['message' => $term->name . ' is now the active term.' . $billingNote]);
    }

    public function deactivate($id)
    {
        $term = AcademicTerm::findOrFail($id);
        $term->update(['is_active' => false]);

        return response()->json(['message' => 'Term deactivated. No term is active until you activate one.']);
    }

    public function destroy($id)
    {
        $term = AcademicTerm::with('year')->findOrFail($id);

        if ($term->is_active) {
            return response()->json(['message' => 'Cannot delete the active term. Activate another term first.'], 403);
        }

        $billing = $term->billingTerm();
        if ($billing && $billing->invoices()->exists()) {
            return response()->json(['message' => 'Schools have already been invoiced for this term, so it cannot be deleted.'], 403);
        }

        $term->delete();

        return response()->json(['message' => 'Term deleted.']);
    }
}
