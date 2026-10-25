<?php

use App\Http\Controllers\AcademicTermController;
use App\Http\Controllers\BillingAdminController;
use App\Http\Controllers\SchoolBillingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| SaaS Billing module
|--------------------------------------------------------------------------
| Loaded from the bottom of routes/web.php (inside the "web" group).
|
|   /admin/billing/*   platform administrator  (AdminAuth + admin.can:billing.*)
|   /school/billing/*  a school's own invoices (SchoolAuth, read-only)
*/

// ─── Platform administrator ─────────────────────────────────────────────────
Route::prefix('admin/billing')
    ->name('billing.')
    ->middleware(['AdminAuth'])
    ->controller(BillingAdminController::class)
    ->group(function () {

        Route::get('/', 'dashboard')->name('dashboard')->middleware('admin.can:billing.view');

        // Billing terms
        Route::get('/terms', 'terms')->name('terms')->middleware('admin.can:billing.view');
        Route::post('/terms', 'storeTerm')->name('terms.store')->middleware('admin.can:billing.manage_terms');
        Route::put('/terms/{term}', 'updateTerm')->name('terms.update')->middleware('admin.can:billing.manage_terms');
        Route::post('/terms/{term}/current', 'setCurrent')->name('terms.current')->middleware('admin.can:billing.manage_terms');
        Route::post('/terms/{term}/toggle', 'toggleTerm')->name('terms.toggle')->middleware('admin.can:billing.manage_terms');
        Route::delete('/terms/{term}', 'destroyTerm')->name('terms.destroy')->middleware('admin.can:billing.manage_terms');
        Route::get('/terms/{term}/preview', 'previewGenerate')->name('terms.preview')->middleware('admin.can:billing.generate_invoices');
        Route::post('/terms/{term}/generate', 'generate')->name('terms.generate')->middleware('admin.can:billing.generate_invoices');
        Route::post('/terms/{term}/issue-all', 'issueAll')->name('terms.issue-all')->middleware('admin.can:billing.generate_invoices');
        // Invoice ONE school for a term (button on School Pricing)
        Route::post('/terms/{term}/schools/{school}/invoice', 'generateSchoolInvoice')->name('terms.school-invoice')->middleware('admin.can:billing.generate_invoices');

        // Invoices
        Route::get('/invoices', 'invoices')->name('invoices')->middleware('admin.can:billing.view');
        Route::get('/invoices/{invoice}', 'showInvoice')->name('invoices.show')->middleware('admin.can:billing.view');
        Route::get('/invoices/{invoice}/pdf', 'invoicePdf')->name('invoices.pdf')->middleware('admin.can:billing.view');
        Route::put('/invoices/{invoice}', 'updateInvoice')->name('invoices.update')->middleware('admin.can:billing.edit_invoices');
        Route::post('/invoices/{invoice}/issue', 'issueInvoice')->name('invoices.issue')->middleware('admin.can:billing.generate_invoices');
        Route::post('/invoices/{invoice}/recount', 'recountInvoice')->name('invoices.recount')->middleware('admin.can:billing.edit_invoices');
        Route::post('/invoices/{invoice}/supplement', 'supplementInvoice')->name('invoices.supplement')->middleware('admin.can:billing.generate_invoices');
        Route::post('/invoices/{invoice}/cancel', 'cancelInvoice')->name('invoices.cancel')->middleware('admin.can:billing.edit_invoices');
        Route::post('/invoices/{invoice}/due-date', 'changeDueDate')->name('invoices.due-date')->middleware('admin.can:billing.edit_invoices');
        Route::post('/invoices/{invoice}/write-off', 'writeOffInvoice')->name('invoices.write-off')->middleware('admin.can:billing.edit_invoices');
        Route::post('/invoices/{invoice}/restore', 'restoreInvoice')->name('invoices.restore')->middleware('admin.can:billing.edit_invoices');
        Route::post('/invoices/{invoice}/adjustments', 'addAdjustment')->name('invoices.adjustments.add')->middleware('admin.can:billing.edit_invoices');
        Route::delete('/invoices/{invoice}/adjustments/{item}', 'removeAdjustment')->name('invoices.adjustments.remove')->middleware('admin.can:billing.edit_invoices');
        Route::post('/invoices/{invoice}/payments', 'recordPayment')->name('invoices.payments.store')->middleware('admin.can:billing.record_payments');

        // Payments
        Route::get('/payments', 'payments')->name('payments')->middleware('admin.can:billing.view');
        Route::post('/payments/{payment}/reverse', 'reversePayment')->name('payments.reverse')->middleware('admin.can:billing.record_payments');
        Route::get('/payments/{payment}/attachment', 'paymentAttachment')->name('payments.attachment')->middleware('admin.can:billing.view');
        Route::get('/payments/{payment}/receipt', 'receiptPdf')->name('payments.receipt')->middleware('admin.can:billing.view');

        // Per-school pricing (custom rate / discount / exemption)
        Route::get('/pricing', 'pricing')->name('pricing')->middleware('admin.can:billing.manage_pricing');
        Route::post('/pricing', 'savePricing')->name('pricing.save')->middleware('admin.can:billing.manage_pricing');
        Route::delete('/pricing/{rate}', 'deletePricing')->name('pricing.delete')->middleware('admin.can:billing.manage_pricing');

        // Settings, reports, CSV exports
        Route::get('/settings', 'settings')->name('settings')->middleware('admin.can:billing.manage_settings');
        Route::post('/settings', 'saveSettings')->name('settings.save')->middleware('admin.can:billing.manage_settings');
        Route::get('/reports', 'reports')->name('reports')->middleware('admin.can:billing.view_reports');
        Route::get('/export/{type}', 'export')->name('export')->middleware('admin.can:billing.view_reports');
    });

// ─── Platform terms (Term I/II/III + the active term), managed from /add-academic-year ──
Route::middleware(['AdminAuth', 'admin.can:academic_calendar.manage'])
    ->controller(AcademicTermController::class)
    ->group(function () {
        Route::post('/academic-terms', 'store')->name('academic-terms.store');
        Route::put('/academic-terms/{id}', 'update')->name('academic-terms.update');
        Route::patch('/academic-terms/{id}/activate', 'activate')->name('academic-terms.activate');
        Route::patch('/academic-terms/{id}/deactivate', 'deactivate')->name('academic-terms.deactivate');
        Route::delete('/academic-terms/{id}', 'destroy')->name('academic-terms.destroy');
    });

// ─── School portal (read-only) ──────────────────────────────────────────────
Route::prefix('school/billing')
    ->name('school.billing.')
    ->middleware(['SchoolAuth'])
    ->controller(SchoolBillingController::class)
    ->group(function () {
        Route::get('/locked', 'locked')->name('locked');
        Route::get('/', 'index')->name('index');
        Route::get('/invoices/{id}', 'show')->name('show');
        Route::get('/invoices/{id}/pdf', 'pdf')->name('pdf');
        Route::get('/receipts/{id}', 'receipt')->name('receipt');
        Route::get('/receipts/{id}/proof', 'proof')->name('proof');
    });
