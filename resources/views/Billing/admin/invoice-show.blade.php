@extends('layouts-side-bar.master')
@section('export_title', $invoice->invoice_number)
@section('css')
    @include('Billing.partials.styles')
    <style>.inv-wrap{background:#fff;border:1px solid var(--bl-border);border-radius:16px;box-shadow:var(--bl-shadow);overflow:hidden;max-width:820px}</style>
@endsection
@section('page-header')
    <div class="bl"><div class="bl-hero">
        <div style="position:relative;z-index:1;"><span class="bl-badge">{{ $invoice->term->name ?? '' }}</span>
            <h1>{{ $invoice->invoice_number }}</h1><p>{{ $school->name }} · <span style="font-weight:700">{{ $invoice->status_label }}</span></p></div>
        <div class="bl-hero-actions">
            <a class="bl-btn light" href="{{ route('billing.invoices', ['term' => $invoice->billing_term_id]) }}"><i class="fas fa-arrow-left"></i> Invoices</a>
            <a class="bl-btn light" target="_blank" href="{{ route('billing.invoices.pdf', $invoice) }}"><i class="fas fa-file-pdf"></i> PDF</a>
        </div>
    </div></div>
@endsection
@section('content')
@php $f = fn($v) => number_format($v, 0); $live = in_array($invoice->status, ['draft', 'issued', 'partial']);
    $can = fn($k) => \App\Support\AdminAccess::can('billing.' . $k); @endphp
<div class="bl">
    @include('Billing.partials.flash')
    @include('Billing.partials.admin-nav')
    <div class="bl-grid3" style="grid-template-columns: minmax(0,1.6fr) minmax(0,1fr);">
        <div>
            <div class="inv-wrap">@include('Billing.partials.invoice-document')</div>
        </div>
        <div>
            <div class="bl-card"><div class="bl-card-h"><h3>Actions</h3></div><div class="bl-card-b d-flex flex-wrap" style="gap:.5rem;">
                @if ($invoice->status === 'draft' && $can('generate_invoices'))
                    <form method="POST" action="{{ route('billing.invoices.issue', $invoice) }}">@csrf<button class="bl-btn success"><i class="fas fa-paper-plane"></i> Issue to school</button></form>
                @endif
                @if ($invoice->isCollectable() && $can('record_payments'))
                    <button class="bl-btn primary" data-toggle="modal" data-target="#payModal"><i class="fas fa-hand-holding-dollar"></i> Record payment</button>
                @endif
                @if ($live && $can('edit_invoices'))
                    <form method="POST" action="{{ route('billing.invoices.recount', $invoice) }}" data-confirm="Re-count students?" data-confirm-text="Currently {{ $invoice->student_count }}. The invoice will use today's number of enrolled students.">@csrf<button class="bl-btn ghost"><i class="fas fa-rotate"></i> Recount students</button></form>
                    <button class="bl-btn ghost" data-toggle="modal" data-target="#editModal"><i class="fas fa-pen"></i> Edit price / discount</button>
                    <button class="bl-btn ghost" data-toggle="modal" data-target="#adjModal"><i class="fas fa-plus-minus"></i> Add charge / credit</button>
                @endif
                @if ($invoice->isCollectable() && $can('edit_invoices'))
                    <button class="bl-btn ghost-danger" data-reason-url="{{ route('billing.invoices.write-off', $invoice) }}" data-reason-title="Write off balance" data-reason-text="The unpaid {{ $f($invoice->balance) }} will no longer count as outstanding." data-reason-btn="Write off"><i class="fas fa-eraser"></i> Write off</button>
                @endif
                @if ($live && $can('edit_invoices') && $invoice->payments->where('status', 'valid')->isEmpty())
                    <button class="bl-btn ghost-danger" data-reason-url="{{ route('billing.invoices.cancel', $invoice) }}" data-reason-title="Cancel invoice" data-reason-text="A cancelled invoice is removed from all totals." data-reason-btn="Cancel invoice"><i class="fas fa-ban"></i> Cancel</button>
                @endif
                @if (in_array($invoice->status, ['cancelled', 'waived']) && $can('edit_invoices'))
                    <form method="POST" action="{{ route('billing.invoices.restore', $invoice) }}" data-confirm="Restore as draft?">@csrf<button class="bl-btn ghost"><i class="fas fa-rotate-left"></i> Restore as draft</button></form>
                    <div class="bl-muted w-100">Reason: {{ $invoice->cancel_reason }}</div>
                @endif
            </div></div>

            @if ($invoice->items->where('type', '!=', 'students')->count() && $live)
            <div class="bl-card"><div class="bl-card-h"><h3>Adjustments</h3></div><div class="bl-card-b">
                <ul class="bl-list">@foreach ($invoice->items->where('type', '!=', 'students') as $it)
                    <li><span>{{ $it->description }} <span class="bl-pill {{ $it->type === 'credit' ? 'paid' : 'issued' }}">{{ $it->type }}</span></span>
                        <span>{{ $f($it->amount) }}
                        <form method="POST" action="{{ route('billing.invoices.adjustments.remove', [$invoice, $it]) }}" class="d-inline" data-confirm="Remove this line?">@csrf @method('DELETE')<button class="bl-btn sm ghost-danger"><i class="fas fa-xmark"></i></button></form></span></li>
                @endforeach</ul></div></div>
            @endif

            <div class="bl-card"><div class="bl-card-h"><h3>Payments</h3><small>{{ $f($invoice->amount_paid) }} of {{ $f($invoice->total) }}</small></div>
                @if ($invoice->payments->isEmpty())<div class="bl-empty" style="padding:1.2rem;">No payments yet.</div>
                @else<div class="bl-table-wrap"><table class="bl-table"><tbody>
                    @foreach ($invoice->payments as $p)
                        <tr style="{{ $p->status === 'reversed' ? 'opacity:.55' : '' }}">
                            <td><strong>{{ $f($p->amount) }}</strong> <span class="bl-pill {{ $p->status }}">{{ $p->status }}</span>
                                <div class="bl-muted">{{ $p->paid_on->format('j M Y') }} · {{ $p->method_label }}{{ $p->reference ? ' · ' . $p->reference : '' }}<br>{{ $p->receipt_number }}</div></td>
                            <td class="num">
                                <a class="bl-btn sm ghost" target="_blank" href="{{ route('billing.payments.receipt', $p) }}"><i class="fas fa-receipt"></i></a>
                                @if ($p->status === 'valid' && $can('record_payments'))<button class="bl-btn sm ghost-danger" data-reason-url="{{ route('billing.payments.reverse', $p) }}" data-reason-title="Reverse {{ $p->receipt_number }}" data-reason-text="Use this for bounced or wrongly recorded payments." data-reason-btn="Reverse payment"><i class="fas fa-rotate-left"></i></button>@endif
                            </td></tr>
                    @endforeach</tbody></table></div>@endif
            </div>

            <div class="bl-card"><div class="bl-card-h"><h3>History</h3></div><div class="bl-card-b">
                <ul class="bl-list">@forelse ($invoice->logs->take(10) as $l)<li><span>{{ $l->description }}</span><span class="bl-muted">{{ $l->created_at->format('j M, H:i') }}</span></li>@empty<li>No activity.</li>@endforelse</ul>
                <div class="bl-muted mt-2">Students counted {{ optional($invoice->counted_at)->diffForHumans() }}.</div></div></div>
        </div>
    </div>

    {{-- Record payment --}}
    <div class="modal fade bl" id="payModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><form class="modal-content" method="POST" action="{{ route('billing.invoices.payments.store', $invoice) }}" style="border-radius:16px;">@csrf
        <div class="modal-header"><h5 class="modal-title">Record payment</h5><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>
        <div class="modal-body"><div class="row">
            <div class="form-group col-md-6"><label>Amount ({{ $settings->currency }})</label><input type="number" name="amount" min="1" max="{{ (int) $invoice->balance }}" value="{{ (int) $invoice->balance }}" class="form-control w-100" required><div class="bl-hint">Balance: {{ $f($invoice->balance) }}</div></div>
            <div class="form-group col-md-6"><label>Date received</label><input type="date" name="paid_on" value="{{ today()->format('Y-m-d') }}" max="{{ today()->format('Y-m-d') }}" class="form-control w-100" required></div>
            <div class="form-group col-md-6"><label>Method</label><select name="method" class="form-control w-100">@foreach ($methods as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach</select></div>
            <div class="form-group col-md-6"><label>Reference / txn ID</label><input name="reference" maxlength="120" class="form-control w-100"></div>
            <div class="form-group col-12 mb-0"><label>Notes</label><input name="notes" maxlength="1000" class="form-control w-100"></div></div></div>
        <div class="modal-footer"><button type="button" class="bl-btn ghost" data-dismiss="modal">Cancel</button><button class="bl-btn success">Save payment</button></div></form></div></div>

    {{-- Edit --}}
    <div class="modal fade bl" id="editModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><form class="modal-content" method="POST" action="{{ route('billing.invoices.update', $invoice) }}" style="border-radius:16px;">@csrf @method('PUT')
        <div class="modal-header"><h5 class="modal-title">Edit price &amp; discount</h5><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>
        <div class="modal-body"><div class="row">
            <div class="form-group col-md-6"><label>Rate per student</label><input type="number" min="0" step="1" name="rate_per_student" value="{{ (float) $invoice->rate_per_student }}" class="form-control w-100" required></div>
            <div class="form-group col-md-6"><label>{{ $settings->tax_name }} rate (%)</label><input type="number" min="0" max="100" step="0.01" name="tax_rate" value="{{ (float) $invoice->tax_rate }}" class="form-control w-100" required></div>
            <div class="form-group col-md-6"><label>Discount type</label><select name="discount_type" class="form-control w-100">@foreach (['none' => 'No discount', 'percent' => 'Percentage (%)', 'fixed' => 'Fixed amount'] as $k => $v)<option value="{{ $k }}" @selected($invoice->discount_type === $k)>{{ $v }}</option>@endforeach</select></div>
            <div class="form-group col-md-6"><label>Discount value</label><input type="number" min="0" step="0.01" name="discount_value" value="{{ (float) $invoice->discount_value }}" class="form-control w-100"></div>
            <div class="form-group col-12"><label>Discount reason (shown on invoice)</label><input name="discount_reason" maxlength="255" value="{{ $invoice->discount_reason }}" class="form-control w-100"></div>
            <div class="form-group col-md-6"><label>Due date</label><input type="date" name="due_date" value="{{ optional($invoice->due_date)->format('Y-m-d') }}" class="form-control w-100"></div>
            <div class="form-group col-md-6"><label>Note on invoice</label><input name="notes" maxlength="2000" value="{{ $invoice->notes }}" class="form-control w-100"></div></div></div>
        <div class="modal-footer"><button type="button" class="bl-btn ghost" data-dismiss="modal">Cancel</button><button class="bl-btn primary">Save changes</button></div></form></div></div>

    {{-- Adjustment --}}
    <div class="modal fade bl" id="adjModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><form class="modal-content" method="POST" action="{{ route('billing.invoices.adjustments.add', $invoice) }}" style="border-radius:16px;">@csrf
        <div class="modal-header"><h5 class="modal-title">Add charge or credit</h5><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>
        <div class="modal-body"><div class="row">
            <div class="form-group col-md-5"><label>Type</label><select name="type" class="form-control w-100"><option value="charge">Extra charge (+)</option><option value="credit">Credit (–)</option></select></div>
            <div class="form-group col-md-7"><label>Amount ({{ $settings->currency }})</label><input type="number" name="amount" min="1" class="form-control w-100" required></div>
            <div class="form-group col-12 mb-0"><label>Description</label><input name="description" maxlength="255" class="form-control w-100" required placeholder="e.g. Onboarding & training fee"></div></div></div>
        <div class="modal-footer"><button type="button" class="bl-btn ghost" data-dismiss="modal">Cancel</button><button class="bl-btn primary">Add line</button></div></form></div></div>
</div></div></div></div>
@endsection
@section('js') @include('Billing.partials.reason-modal') @endsection
