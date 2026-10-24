<?php

namespace App\Http\Controllers;

use App\Models\AcademicTerm;
use App\Models\AcademicYear;
use App\Models\BillingInvoice;
use App\Models\BillingInvoiceItem;
use App\Models\BillingLog;
use App\Models\BillingPayment;
use App\Models\BillingSchoolRate;
use App\Models\BillingSetting;
use App\Models\BillingTerm;
use App\Models\School;
use App\Services\BillingService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Platform-administrator side of the SaaS billing module.
 * Every route here sits behind AdminAuth + BillingAdmin (see routes/billing.php).
 */
class BillingAdminController extends Controller
{
    public function __construct(private BillingService $billing)
    {
    }

    // ─── helpers ───────────────────────────────────────────────────────────

    /** The term the admin is looking at: ?term=ID, else the current term, else the newest. */
    private function selectedTerm(Request $request): ?BillingTerm
    {
        if ($request->filled('term')) {
            $t = BillingTerm::find($request->input('term'));
            if ($t) {
                return $t;
            }
        }

        return BillingTerm::current() ?? BillingTerm::orderByDesc('id')->first();
    }

    private function allTerms()
    {
        return BillingTerm::orderByDesc('year_label')->orderByDesc('term')->get();
    }

    private function fail(\Throwable $e)
    {
        return back()->with('error', $e instanceof InvalidArgumentException ? $e->getMessage() : 'Something went wrong: ' . $e->getMessage());
    }

    // ─── dashboard ─────────────────────────────────────────────────────────

    public function dashboard(Request $request)
    {
        $terms = $this->allTerms();
        $term = $this->selectedTerm($request);
        $settings = $this->billing->settings();

        $stats = $term ? $this->billing->termStats($term) : null;

        $topOutstanding = $term
            ? BillingInvoice::with('school:id,name')->where('billing_term_id', $term->id)
                ->whereIn('status', ['issued', 'partial'])->where('balance', '>', 0)
                ->orderByDesc('balance')->limit(8)->get()
            : collect();

        $recentPayments = BillingPayment::with(['school:id,name', 'invoice:id,invoice_number'])
            ->valid()->when($term, fn($q) => $q->where('billing_term_id', $term->id))
            ->orderByDesc('id')->limit(8)->get();

        return view('Billing.admin.dashboard', [
            'terms'          => $terms,
            'term'           => $term,
            'stats'          => $stats,
            'settings'       => $settings,
            'trend'          => $this->billing->trend(),
            'aging'          => $this->billing->aging($term?->id),
            'topOutstanding' => $topOutstanding,
            'recentPayments' => $recentPayments,
            'logs'           => BillingLog::with('school:id,name')->latest('id')->limit(8)->get(),
        ]);
    }

    // ─── billing terms ─────────────────────────────────────────────────────

    public function terms()
    {
        $terms = BillingTerm::withCount('invoices')->orderByDesc('year_label')->orderByDesc('term')->get();
        $sums = BillingInvoice::billable()->selectRaw('billing_term_id, SUM(total) as expected, SUM(amount_paid) as collected')
            ->groupBy('billing_term_id')->get()->keyBy('billing_term_id');

        return view('Billing.admin.terms', [
            'terms'    => $terms,
            'sums'     => $sums,
            'years'    => AcademicYear::orderByDesc('start_date')->get(),
            'settings' => $this->billing->settings(),
            // Terms + dates the administrators set on /add-academic-year
            'calendarTerms' => AcademicTerm::with('year')->orderByDesc('academic_year_id')->orderBy('term')->get(),
            'billedKeys'    => $terms->map(fn($t) => $t->academic_year_id . '-' . $t->term)->flip(),
        ]);
    }

    private function termRules(?int $ignoreId = null): array
    {
        return [
            'academic_year_id' => 'required|integer|exists:academic_years,id',
            'term'             => 'required|integer|in:1,2,3',
            'start_date'       => 'nullable|date',
            'end_date'         => 'nullable|date|after_or_equal:start_date',
            'due_date'         => 'nullable|date',
            'rate_per_student' => 'required|numeric|min:0|max:100000000',
            'notes'            => 'nullable|string|max:1000',
        ];
    }

    public function storeTerm(Request $request)
    {
        $data = $request->validate($this->termRules());
        $year = AcademicYear::findOrFail($data['academic_year_id']);

        if (BillingTerm::where('year_label', $year->name)->where('term', $data['term'])->exists()) {
            return back()->withInput()->with('error', 'That term already exists for ' . $year->name . '.');
        }

        // Dates left empty are taken from the term the administrators set on /add-academic-year.
        $calendar = AcademicTerm::where('academic_year_id', $year->id)->where('term', $data['term'])->first();

        // The first billing term becomes current; so does the one matching the platform's active term.
        $makeCurrent = !BillingTerm::where('is_current', true)->exists() || ($calendar && $calendar->is_active);
        if ($makeCurrent) {
            BillingTerm::where('is_current', true)->update(['is_current' => false]);
        }

        $term = BillingTerm::create([
            'academic_year_id' => $year->id,
            'year_label'       => $year->name,
            'term'             => $data['term'],
            'start_date'       => $data['start_date'] ?? optional($calendar)->start_date,
            'end_date'         => $data['end_date'] ?? optional($calendar)->end_date,
            'due_date'         => $data['due_date'] ?? null,
            'rate_per_student' => $data['rate_per_student'],
            'notes'            => $data['notes'] ?? null,
            'status'           => 'open',
            'is_current'       => $makeCurrent,
            'created_by'       => session('LoggedAdmin'),
        ]);

        $this->billing->log('term', "Created billing term {$term->name} at " . BillingService::fmt($term->rate_per_student) . ' per student.', ['term_id' => $term->id]);

        return redirect()->route('billing.terms')->with('success', "Billing term {$term->name} created.");
    }

    public function updateTerm(Request $request, BillingTerm $term)
    {
        $data = $request->validate([
            'start_date'       => 'nullable|date',
            'end_date'         => 'nullable|date|after_or_equal:start_date',
            'due_date'         => 'nullable|date',
            'rate_per_student' => 'required|numeric|min:0|max:100000000',
            'notes'            => 'nullable|string|max:1000',
        ]);

        $rateChanged = (float) $data['rate_per_student'] !== (float) $term->rate_per_student;
        $term->update($data);

        $this->billing->log('term', "Updated billing term {$term->name}" . ($rateChanged ? ' (rate now ' . BillingService::fmt($term->rate_per_student) . ')' : '') . '.', ['term_id' => $term->id]);

        $msg = 'Term updated.';
        if ($rateChanged && $term->invoices()->exists()) {
            $msg .= ' Existing invoices keep the rate they were generated with — edit an invoice to change its rate.';
        }

        return back()->with('success', $msg);
    }

    public function setCurrent(BillingTerm $term)
    {
        BillingTerm::where('is_current', true)->update(['is_current' => false]);
        $term->update(['is_current' => true, 'status' => 'open']);

        $this->billing->log('term', "{$term->name} is now the current billing term.", ['term_id' => $term->id]);

        return back()->with('success', "{$term->name} is now the current billing term.");
    }

    public function toggleTerm(BillingTerm $term)
    {
        $term->status = $term->isOpen() ? 'closed' : 'open';
        if ($term->status === 'closed') {
            $term->is_current = false;
        }
        $term->save();

        $this->billing->log('term', ($term->isOpen() ? 'Reopened ' : 'Closed ') . $term->name . '.', ['term_id' => $term->id]);

        return back()->with('success', $term->name . ($term->isOpen() ? ' reopened.' : ' closed.'));
    }

    public function destroyTerm(BillingTerm $term)
    {
        if ($term->invoices()->exists()) {
            return back()->with('error', 'This term already has invoices and cannot be deleted. Close it instead.');
        }

        $name = $term->name;
        BillingSchoolRate::where('billing_term_id', $term->id)->delete();
        $term->delete();
        $this->billing->log('term', "Deleted billing term {$name}.");

        return back()->with('success', "{$name} deleted.");
    }

    public function previewGenerate(BillingTerm $term)
    {
        return response()->json($this->billing->preview($term) + ['term' => $term->name]);
    }

    public function generate(Request $request, BillingTerm $term)
    {
        if (!$term->isOpen()) {
            return back()->with('error', 'This term is closed. Reopen it to generate invoices.');
        }

        try {
            $r = $this->billing->generateForTerm($term, $request->boolean('issue_now', true));
        } catch (\Throwable $e) {
            return $this->fail($e);
        }

        $msg = "{$r['created']} invoice(s) created for {$term->name}";
        if ($r['skipped_existing']) $msg .= ", {$r['skipped_existing']} already existed";
        if ($r['exempt']) $msg .= ", {$r['exempt']} exempt";
        if ($r['no_students']) $msg .= ", {$r['no_students']} skipped (no students)";

        return redirect()->route('billing.dashboard', ['term' => $term->id])->with('success', $msg . '.');
    }

    public function generateSchoolInvoice(Request $request, BillingTerm $term, int $school)
    {
        try {
            $invoice = $this->billing->generateForSchool($term, $school, $request->boolean('issue_now', true));
        } catch (\Throwable $e) {
            return $this->fail($e);
        }

        return back()->with('success', "Invoice {$invoice->invoice_number} created for {$term->name} (" . BillingService::fmt($invoice->total) . ').');
    }

    public function issueAll(BillingTerm $term)
    {
        $n = $this->billing->issueAllDrafts($term);

        return back()->with($n ? 'success' : 'error', $n ? "{$n} draft invoice(s) issued." : 'There are no draft invoices to issue.');
    }

    // ─── invoices ──────────────────────────────────────────────────────────

    public function invoices(Request $request)
    {
        $term = $this->selectedTerm($request);
        $status = $request->input('status', 'all');
        $search = trim((string) $request->input('q', ''));

        $q = BillingInvoice::with('school:id,name,registration_code')
            ->when($request->input('term') !== 'all' && $term, fn($q) => $q->where('billing_term_id', $term->id))
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where('invoice_number', 'like', "%{$search}%")
                        ->orWhereHas('school', fn($s) => $s->where('name', 'like', "%{$search}%")->orWhere('registration_code', 'like', "%{$search}%"));
                });
            });

        switch ($status) {
            case 'overdue':
                $q->overdue();
                break;
            case 'unpaid':
                $q->where('status', 'issued')->where(fn($q) => $q->whereNull('due_date')->orWhereDate('due_date', '>=', today()));
                break;
            case 'partial':
                $q->where('status', 'partial')->where(fn($q) => $q->whereNull('due_date')->orWhereDate('due_date', '>=', today()));
                break;
            case 'paid':
            case 'draft':
            case 'cancelled':
            case 'waived':
                $q->where('status', $status);
                break;
        }

        $totals = (clone $q)->selectRaw("COUNT(*) c, COALESCE(SUM(total),0) t, COALESCE(SUM(amount_paid),0) p, COALESCE(SUM(CASE WHEN status IN ('issued','partial') THEN balance ELSE 0 END),0) b")->first();

        $invoices = $q->orderByDesc('id')->paginate(25)->withQueryString();

        return view('Billing.admin.invoices', [
            'terms'    => $this->allTerms(),
            'term'     => $request->input('term') === 'all' ? null : $term,
            'allTerms' => $request->input('term') === 'all',
            'status'   => $status,
            'search'   => $search,
            'invoices' => $invoices,
            'totals'   => $totals,
        ]);
    }

    public function showInvoice(BillingInvoice $invoice)
    {
        $invoice->load(['school', 'term', 'items', 'payments.invoice', 'logs']);

        return view('Billing.admin.invoice-show', $this->invoiceViewData($invoice));
    }

    private function invoiceViewData(BillingInvoice $invoice): array
    {
        $profile = \App\Models\SchoolProfile::where('school_id', $invoice->school_id)->first();

        return [
            'invoice'  => $invoice,
            'school'   => $invoice->school,
            'profile'  => $profile,
            'settings' => $this->billing->settings(),
            'methods'  => BillingPayment::METHODS,
        ];
    }

    public function invoicePdf(BillingInvoice $invoice)
    {
        $invoice->load(['school', 'term', 'items', 'payments']);

        return Pdf::loadView('Billing.pdf.invoice', $this->invoiceViewData($invoice) + ['forPdf' => true])
            ->setPaper('a4', 'portrait')
            ->stream($invoice->invoice_number . '.pdf');
    }

    public function updateInvoice(Request $request, BillingInvoice $invoice)
    {
        $data = $request->validate([
            'rate_per_student' => 'required|numeric|min:0|max:100000000',
            'discount_type'    => ['required', Rule::in(['none', 'percent', 'fixed'])],
            'discount_value'   => 'nullable|numeric|min:0|max:1000000000',
            'discount_reason'  => 'nullable|string|max:255',
            'tax_rate'         => 'required|numeric|min:0|max:100',
            'due_date'         => 'nullable|date',
            'notes'            => 'nullable|string|max:2000',
        ]);

        if ($data['discount_type'] === 'percent' && (float) ($data['discount_value'] ?? 0) > 100) {
            return back()->withInput()->with('error', 'A percentage discount cannot exceed 100%.');
        }
        $data['discount_value'] = $data['discount_value'] ?? 0;

        try {
            $this->billing->updateInvoice($invoice, $data);
        } catch (\Throwable $e) {
            return $this->fail($e);
        }

        return back()->with('success', 'Invoice updated.');
    }

    public function issueInvoice(BillingInvoice $invoice)
    {
        try {
            $this->billing->issue($invoice);
        } catch (\Throwable $e) {
            return $this->fail($e);
        }

        return back()->with('success', "{$invoice->invoice_number} issued — the school can now see it.");
    }

    public function recountInvoice(BillingInvoice $invoice)
    {
        try {
            $inv = $this->billing->recount($invoice);
        } catch (\Throwable $e) {
            return $this->fail($e);
        }

        return back()->with('success', "Student count refreshed: {$inv->student_count} students.");
    }

    public function cancelInvoice(Request $request, BillingInvoice $invoice)
    {
        $data = $request->validate(['reason' => 'required|string|max:255']);

        try {
            $this->billing->cancel($invoice, $data['reason']);
        } catch (\Throwable $e) {
            return $this->fail($e);
        }

        return back()->with('success', "{$invoice->invoice_number} cancelled.");
    }

    public function writeOffInvoice(Request $request, BillingInvoice $invoice)
    {
        $data = $request->validate(['reason' => 'required|string|max:255']);

        try {
            $this->billing->writeOff($invoice, $data['reason']);
        } catch (\Throwable $e) {
            return $this->fail($e);
        }

        return back()->with('success', "{$invoice->invoice_number} written off.");
    }

    public function restoreInvoice(BillingInvoice $invoice)
    {
        try {
            $this->billing->restore($invoice);
        } catch (\Throwable $e) {
            return $this->fail($e);
        }

        return back()->with('success', "{$invoice->invoice_number} restored as a draft.");
    }

    public function addAdjustment(Request $request, BillingInvoice $invoice)
    {
        $data = $request->validate([
            'type'        => ['required', Rule::in(['charge', 'credit'])],
            'description' => 'required|string|max:255',
            'amount'      => 'required|numeric|min:1|max:1000000000',
        ]);

        try {
            $this->billing->addAdjustment($invoice, $data['type'], $data['description'], (float) $data['amount']);
        } catch (\Throwable $e) {
            return $this->fail($e);
        }

        return back()->with('success', ucfirst($data['type']) . ' added to the invoice.');
    }

    public function removeAdjustment(BillingInvoice $invoice, BillingInvoiceItem $item)
    {
        try {
            $this->billing->removeAdjustment($invoice, $item);
        } catch (\Throwable $e) {
            return $this->fail($e);
        }

        return back()->with('success', 'Line removed.');
    }

    // ─── payments ──────────────────────────────────────────────────────────

    public function recordPayment(Request $request, BillingInvoice $invoice)
    {
        $data = $request->validate([
            'amount'    => 'required|numeric|min:1|max:1000000000',
            'method'    => ['required', Rule::in(array_keys(BillingPayment::METHODS))],
            'reference' => 'nullable|string|max:120',
            'paid_on'   => 'required|date|before_or_equal:today',
            'notes'     => 'nullable|string|max:1000',
        ]);

        try {
            $payment = $this->billing->recordPayment($invoice, $data);
        } catch (\Throwable $e) {
            return $this->fail($e);
        }

        return back()->with('success', 'Payment of ' . BillingService::fmt($payment->amount) . " recorded ({$payment->receipt_number}).");
    }

    public function payments(Request $request)
    {
        $term = $request->input('term') === 'all' ? null : $this->selectedTerm($request);
        $search = trim((string) $request->input('q', ''));

        $q = BillingPayment::with(['school:id,name', 'invoice:id,invoice_number', 'term'])
            ->when($term, fn($q) => $q->where('billing_term_id', $term->id))
            ->when($request->filled('method'), fn($q) => $q->where('method', $request->input('method')))
            ->when($request->filled('from'), fn($q) => $q->whereDate('paid_on', '>=', $request->input('from')))
            ->when($request->filled('to'), fn($q) => $q->whereDate('paid_on', '<=', $request->input('to')))
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where('receipt_number', 'like', "%{$search}%")->orWhere('reference', 'like', "%{$search}%")
                        ->orWhereHas('school', fn($s) => $s->where('name', 'like', "%{$search}%"));
                });
            });

        $total = (clone $q)->where('status', 'valid')->sum('amount');
        $byMethod = (clone $q)->where('status', 'valid')->selectRaw('method, SUM(amount) as total, COUNT(*) as n')->groupBy('method')->get();

        return view('Billing.admin.payments', [
            'terms'    => $this->allTerms(),
            'term'     => $term,
            'allTerms' => $request->input('term') === 'all',
            'payments' => $q->orderByDesc('paid_on')->orderByDesc('id')->paginate(25)->withQueryString(),
            'total'    => $total,
            'byMethod' => $byMethod,
            'search'   => $search,
            'methods'  => BillingPayment::METHODS,
        ]);
    }

    public function reversePayment(Request $request, BillingPayment $payment)
    {
        $data = $request->validate(['reason' => 'required|string|max:255']);

        try {
            $this->billing->reversePayment($payment, $data['reason']);
        } catch (\Throwable $e) {
            return $this->fail($e);
        }

        return back()->with('success', "{$payment->receipt_number} reversed.");
    }

    public function receiptPdf(BillingPayment $payment)
    {
        $payment->load(['invoice.term', 'school']);
        $profile = \App\Models\SchoolProfile::where('school_id', $payment->school_id)->first();

        return Pdf::loadView('Billing.pdf.receipt', [
            'payment' => $payment, 'invoice' => $payment->invoice, 'school' => $payment->school,
            'profile' => $profile, 'settings' => $this->billing->settings(),
        ])->setPaper('a5', 'portrait')->stream($payment->receipt_number . '.pdf');
    }

    // ─── per-school pricing ────────────────────────────────────────────────

    public function pricing(Request $request)
    {
        $term = $this->selectedTerm($request);
        $search = trim((string) $request->input('q', ''));

        $q = School::where('school_status', BillingService::ACTIVE_SCHOOL_STATUS)->orderBy('name');
        if ($search !== '') {
            $like = '%' . addcslashes($search, '\\%_') . '%';
            $q->where(function ($w) use ($like) {
                $w->where('name', 'like', $like)->orWhere('registration_code', 'like', $like);
            });
        }

        $total = School::where('school_status', BillingService::ACTIVE_SCHOOL_STATUS)->count();
        $page = $q->paginate(25)->withQueryString();
        $ids = $page->pluck('id')->all();

        $students = $this->billing->studentCounts($ids);
        $arrears = $this->billing->arrearsMap($ids);
        $rates = BillingSchoolRate::whereIn('school_id', $ids)->get()->groupBy('school_id');
        $invoiced = $term
            ? BillingInvoice::where('billing_term_id', $term->id)->whereIn('school_id', $ids)->pluck('id', 'school_id')
            : collect();

        // Without a billing term there is nothing to price (the page shows "create a term first").
        $rows = (!$term ? collect() : $page->getCollection())->map(function ($s) use ($term, $rates, $students, $arrears, $invoiced) {
            return [
                'school'   => $s,
                'students' => $students[$s->id] ?? 0,
                'pricing'  => $term ? $this->billing->resolvePricing($s->id, $term) : null,
                'rates'    => $rates->get($s->id, collect()),
                'arrears'  => $arrears[$s->id] ?? null,
                'invoice'  => $invoiced[$s->id] ?? null,
            ];
        });

        $data = ['term' => $term, 'rows' => $rows, 'page' => $page, 'search' => $search, 'schoolsTotal' => $total];

        // Live search / paging: only the table body and the pager are re-drawn.
        if ($request->ajax()) {
            return response()->json([
                'rows'  => view('Billing.admin.partials.pricing-rows', $data)->render(),
                'pager' => view('Billing.admin.partials.pricing-pager', $data)->render(),
            ]);
        }

        return view('Billing.admin.pricing', $data + [
            'terms' => $this->allTerms(), 'settings' => $this->billing->settings(),
        ]);
    }

    public function savePricing(Request $request)
    {
        $data = $request->validate([
            'school_id'       => 'required|integer|exists:schools,id',
            'billing_term_id' => 'nullable|integer|exists:billing_terms,id',
            'custom_rate'     => 'nullable|numeric|min:0|max:100000000',
            'discount_type'   => ['required', Rule::in(['none', 'percent', 'fixed'])],
            'discount_value'  => 'nullable|numeric|min:0|max:1000000000',
            'is_exempt'       => 'nullable|boolean',
            'reason'          => 'nullable|string|max:255',
        ]);

        if ($data['discount_type'] === 'percent' && (float) ($data['discount_value'] ?? 0) > 100) {
            return back()->withInput()->with('error', 'A percentage discount cannot exceed 100%.');
        }

        $rate = BillingSchoolRate::updateOrCreate(
            ['school_id' => $data['school_id'], 'billing_term_id' => $data['billing_term_id'] ?? null],
            [
                'custom_rate'    => $data['custom_rate'] ?? null,
                'discount_type'  => $data['discount_type'],
                'discount_value' => $data['discount_type'] === 'none' ? 0 : ($data['discount_value'] ?? 0),
                'is_exempt'      => $request->boolean('is_exempt'),
                'reason'         => $data['reason'] ?? null,
                'created_by'     => session('LoggedAdmin'),
            ]
        );

        $school = School::find($rate->school_id);
        $this->billing->log('pricing', "Pricing for {$school->name} saved" . ($rate->billing_term_id ? ' (single term)' : ' (all terms)') . '.', [
            'school_id' => $school->id, 'term_id' => $rate->billing_term_id,
        ]);

        return back()->with('success', "Pricing saved for {$school->name}. It applies to invoices generated from now on; edit an existing invoice to change it.");
    }

    public function deletePricing(BillingSchoolRate $rate)
    {
        $school = School::find($rate->school_id);
        $rate->delete();
        $this->billing->log('pricing', 'Removed a pricing override for ' . ($school->name ?? 'a school') . '.', ['school_id' => $rate->school_id]);

        return back()->with('success', 'Pricing override removed.');
    }

    // ─── settings ──────────────────────────────────────────────────────────

    public function settings()
    {
        return view('Billing.admin.settings', ['settings' => $this->billing->settings()]);
    }

    public function saveSettings(Request $request)
    {
        $data = $request->validate([
            'default_rate_per_student' => 'required|numeric|min:0|max:100000000',
            'tax_enabled'              => 'nullable|boolean',
            'tax_name'                 => 'required|string|max:30',
            'tax_rate'                 => 'required|numeric|min:0|max:100',
            'due_days'                 => 'required|integer|min:0|max:365',
            'invoice_prefix'           => 'required|string|max:12|alpha_num',
            'company_name'             => 'required|string|max:150',
            'company_tagline'          => 'nullable|string|max:190',
            'company_address'          => 'nullable|string|max:255',
            'company_phone'            => 'nullable|string|max:60',
            'company_email'            => 'nullable|email|max:120',
            'company_website'          => 'nullable|string|max:120',
            'company_tin'              => 'nullable|string|max:60',
            'payment_instructions'     => 'nullable|string|max:3000',
            'invoice_notes'            => 'nullable|string|max:3000',
        ]);
        $data['tax_enabled'] = $request->boolean('tax_enabled');

        $this->billing->settings()->update($data);
        $this->billing->log('settings', 'Billing settings updated.');

        return back()->with('success', 'Billing settings saved. Tax and invoice details apply to invoices generated from now on.');
    }

    // ─── reports ───────────────────────────────────────────────────────────

    public function reports(Request $request)
    {
        $term = $this->selectedTerm($request);
        $terms = $this->allTerms();

        $schools = collect();
        if ($term) {
            $schools = BillingInvoice::with('school:id,name,registration_code')
                ->where('billing_term_id', $term->id)->billable()->get()
                ->map(fn($i) => [
                    'invoice' => $i,
                    'name'    => $i->school->name ?? '—',
                ])->sortByDesc(fn($r) => $r['invoice']->balance)->values();
        }

        $termRows = $terms->map(function ($t) {
            $s = $this->billing->termStats($t);

            return ['term' => $t, 'stats' => $s];
        });

        return view('Billing.admin.reports', [
            'terms'    => $terms,
            'term'     => $term,
            'schools'  => $schools,
            'termRows' => $termRows,
            'aging'    => $this->billing->aging($term?->id),
            'agingAll' => $this->billing->aging(),
        ]);
    }

    public function export(Request $request, string $type): StreamedResponse
    {
        abort_unless(in_array($type, ['invoices', 'payments', 'outstanding'], true), 404);
        $term = $request->input('term') === 'all' ? null : $this->selectedTerm($request);

        $name = "billing-{$type}-" . ($term ? str_replace([' ', '·', '–'], '-', $term->name) : 'all-terms') . '-' . now()->format('Ymd') . '.csv';

        return response()->streamDownload(function () use ($type, $term) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM so Excel opens it correctly

            if ($type === 'payments') {
                fputcsv($out, ['Receipt', 'Date', 'School', 'Invoice', 'Term', 'Method', 'Reference', 'Amount', 'Status']);
                BillingPayment::with(['school:id,name', 'invoice:id,invoice_number', 'term'])
                    ->when($term, fn($q) => $q->where('billing_term_id', $term->id))
                    ->orderBy('paid_on')->chunk(500, function ($rows) use ($out) {
                        foreach ($rows as $p) {
                            fputcsv($out, [$p->receipt_number, $p->paid_on->format('Y-m-d'), $p->school->name ?? '', $p->invoice->invoice_number ?? '', $p->term->name ?? '', $p->method_label, $p->reference, $p->amount, $p->status]);
                        }
                    });
            } else {
                fputcsv($out, ['Invoice', 'School', 'Term', 'Students', 'Rate', 'Subtotal', 'Discount', 'Tax', 'Total', 'Paid', 'Balance', 'Status', 'Issued', 'Due', 'Days overdue']);
                BillingInvoice::with(['school:id,name', 'term'])
                    ->when($term, fn($q) => $q->where('billing_term_id', $term->id))
                    ->when($type === 'outstanding', fn($q) => $q->whereIn('status', ['issued', 'partial'])->where('balance', '>', 0))
                    ->orderBy('id')->chunk(500, function ($rows) use ($out) {
                        foreach ($rows as $i) {
                            fputcsv($out, [
                                $i->invoice_number, $i->school->name ?? '', $i->term->name ?? '', $i->student_count, $i->rate_per_student,
                                $i->subtotal, $i->discount_amount, $i->tax_amount, $i->total, $i->amount_paid, $i->balance,
                                $i->status_label, optional($i->issue_date)->format('Y-m-d'), optional($i->due_date)->format('Y-m-d'), $i->daysOverdue(),
                            ]);
                        }
                    });
            }
            fclose($out);
        }, $name, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
