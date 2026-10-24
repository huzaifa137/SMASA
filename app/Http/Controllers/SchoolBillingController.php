<?php

namespace App\Http\Controllers;

use App\Helpers\PermissionHelper;
use App\Models\BillingInvoice;
use App\Models\BillingPayment;
use App\Models\BillingTerm;
use App\Models\SchoolProfile;
use App\Services\BillingService;
use Barryvdh\DomPDF\Facade\Pdf;

/**
 * School-facing billing: a school sees ONLY its own, issued invoices and the
 * payments recorded against them. Read-only — payments are recorded by the
 * platform administrator once the money is received.
 *
 * Who in a school may open it: roles holding the "Billing & Invoices" module
 * features view_billing / download_invoice_pdf / download_receipt_pdf — see canView().
 */
class SchoolBillingController extends Controller
{
    public function __construct(private BillingService $billing)
    {
    }

    private function canView(string $feature = 'view_billing'): bool
    {
        // Platform admins with the Billing right, or a school role holding the
        // Billing & Invoices feature (seeded by SystemModulesSeeder).
        return \App\Support\AdminAccess::can('billing.view') || PermissionHelper::canFeature($feature);
    }

    private function guard(string $feature = 'view_billing')
    {
        if (!$this->canView($feature)) {
            return redirect()->route('school.dashboard')
                ->with('error', 'Access denied: you do not have permission to view billing.');
        }

        return null;
    }

    public function index()
    {
        if ($r = $this->guard()) {
            return $r;
        }

        $schoolId = (int) session('LoggedSchool');

        $invoices = BillingInvoice::with('term')
            ->where('school_id', $schoolId)->visibleToSchool()
            ->orderByDesc('id')->get();

        $current = BillingTerm::current();
        $currentInvoice = $current ? $invoices->firstWhere('billing_term_id', $current->id) : null;

        $payments = BillingPayment::with(['invoice:id,invoice_number', 'term'])
            ->where('school_id', $schoolId)->valid()
            ->orderByDesc('paid_on')->orderByDesc('id')->limit(15)->get();

        return view('Billing.school.index', [
            'invoices'       => $invoices,
            'current'        => $current,
            'currentInvoice' => $currentInvoice,
            'payments'       => $payments,
            'position'       => $this->billing->schoolPosition($schoolId),
            'settings'       => $this->billing->settings(),
        ]);
    }

    private function ownInvoice(int $id): BillingInvoice
    {
        return BillingInvoice::with(['school', 'term', 'items', 'payments'])
            ->where('school_id', (int) session('LoggedSchool'))
            ->visibleToSchool()
            ->findOrFail($id);
    }

    private function data(BillingInvoice $invoice): array
    {
        return [
            'invoice'  => $invoice,
            'school'   => $invoice->school,
            'profile'  => SchoolProfile::where('school_id', $invoice->school_id)->first(),
            'settings' => $this->billing->settings(),
        ];
    }

    public function show(int $id)
    {
        if ($r = $this->guard()) {
            return $r;
        }

        return view('Billing.school.show', $this->data($this->ownInvoice($id)));
    }

    public function pdf(int $id)
    {
        if ($r = $this->guard('download_invoice_pdf')) {
            return $r;
        }

        $invoice = $this->ownInvoice($id);

        return Pdf::loadView('Billing.pdf.invoice', $this->data($invoice) + ['forPdf' => true])
            ->setPaper('a4', 'portrait')
            ->stream($invoice->invoice_number . '.pdf');
    }

    public function receipt(int $id)
    {
        if ($r = $this->guard('download_receipt_pdf')) {
            return $r;
        }

        $payment = BillingPayment::with(['invoice.term', 'school'])
            ->where('school_id', (int) session('LoggedSchool'))->valid()->findOrFail($id);

        return Pdf::loadView('Billing.pdf.receipt', [
            'payment'  => $payment,
            'invoice'  => $payment->invoice,
            'school'   => $payment->school,
            'profile'  => SchoolProfile::where('school_id', $payment->school_id)->first(),
            'settings' => $this->billing->settings(),
        ])->setPaper('a5', 'portrait')->stream($payment->receipt_number . '.pdf');
    }
}
