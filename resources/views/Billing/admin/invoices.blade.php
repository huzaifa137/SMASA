@extends('layouts-side-bar.master')
@section('export_title', 'Billing Invoices')
@section('css')
    @include('Billing.partials.styles')
@endsection
@section('page-header')
    <div class="bl"><div class="bl-hero">
        <div style="position:relative;z-index:1;"><span class="bl-badge"><i class="fas fa-file-invoice-dollar"></i> Invoices</span>
            <h1>School Invoices</h1><p>{{ $allTerms ? 'All terms' : ($term->name ?? 'No term') }}</p></div>
        <div class="bl-hero-actions">
            <a class="bl-btn light" href="{{ route('billing.export', ['type' => 'invoices', 'term' => $allTerms ? 'all' : ($term->id ?? null)]) }}"><i class="fas fa-file-csv"></i> Export CSV</a>
            @if ($term && !$allTerms && \App\Support\AdminAccess::can('billing.generate_invoices'))
                <form method="POST" action="{{ route('billing.terms.issue-all', $term) }}" data-confirm="Issue all draft invoices for {{ $term->name }}?">@csrf
                    <button class="bl-btn light"><i class="fas fa-paper-plane"></i> Issue drafts</button></form>
            @endif
        </div>
    </div></div>
@endsection
@section('content')

<style>
    .bl-table thead th {
    background-color: #2C29CA !important;
    color: #FFFFFF !important;
}
</style>
@php $canEditDue = \App\Support\AdminAccess::can('billing.edit_invoices'); @endphp
<div class="bl">
    @include('Billing.partials.flash')
    @include('Billing.partials.admin-nav')
    <div class="bl-card">
        <form class="bl-filters" method="GET">
            <div><label>Term</label><select name="term" class="bl-input">
                <option value="all" @selected($allTerms)>All terms</option>
                @foreach ($terms as $t)<option value="{{ $t->id }}" @selected(!$allTerms && $term && $t->id === $term->id)>{{ $t->name }}</option>@endforeach</select></div>
            <div><label>Status</label><select name="status" class="bl-input">
                @foreach (['all' => 'All', 'draft' => 'Draft', 'unpaid' => 'Unpaid', 'partial' => 'Partially paid', 'overdue' => 'Overdue', 'paid' => 'Paid', 'waived' => 'Written off', 'cancelled' => 'Cancelled'] as $k => $v)
                    <option value="{{ $k }}" @selected($status === $k)>{{ $v }}</option>@endforeach</select></div>
            <div><label>Search</label><input class="bl-input" name="q" value="{{ $search }}" placeholder="School or invoice no."></div>
            <button class="bl-btn primary"><i class="fas fa-filter"></i> Filter</button>
            <a class="bl-btn ghost" href="{{ route('billing.invoices') }}">Reset</a>
        </form>
        @if ($invoices->isEmpty())
            <div class="bl-empty"><i class="fas fa-file-circle-question"></i>No invoices match these filters.</div>
        @else
        <div class="bl-table-wrap"><table class="bl-table">
            <thead><tr><th>Invoice</th><th>School</th><th class="num">Students</th><th class="num">Rate</th><th class="num">Discount</th><th class="num">Tax</th><th class="num">Total</th><th class="num">Paid</th><th class="num">Balance</th><th>Due</th><th>Status</th><th></th></tr></thead>
            <tbody>
            @foreach ($invoices as $i)
                <tr>
                    <td><a href="{{ route('billing.invoices.show', $i) }}"><strong>{{ $i->invoice_number }}</strong></a></td>
                    <td>{{ $i->school->name ?? '—' }}<div class="bl-muted">{{ $i->school->registration_code ?? '' }}</div></td>
                    <td class="num">{{ number_format($i->student_count) }}</td>
                    <td class="num">{{ number_format($i->rate_per_student, 0) }}</td>
                    <td class="num">{{ $i->discount_amount > 0 ? number_format($i->discount_amount, 0) : '—' }}</td>
                    <td class="num">{{ number_format($i->tax_amount, 0) }}</td>
                    <td class="num"><strong>{{ number_format($i->total, 0) }}</strong></td>
                    <td class="num" style="color:var(--bl-green)">{{ number_format($i->amount_paid, 0) }}</td>
                    <td class="num"><strong>{{ in_array($i->status, ['issued', 'partial']) ? number_format($i->balance, 0) : '—' }}</strong></td>
                    <td class="due-cell" style="white-space:nowrap;transition:background .6s;">
                        <span class="due-label">{{ optional($i->due_date)->format('j M Y') ?? '—' }}</span>
                        @if ($canEditDue && in_array($i->status, ['draft', 'issued', 'partial'], true))
                            <button type="button" class="bl-btn sm ghost due-btn" title="Extend or change the due date"
                                data-url="{{ route('billing.invoices.due-date', $i) }}" data-number="{{ $i->invoice_number }}"
                                data-school="{{ $i->school->name ?? '' }}" data-due="{{ optional($i->due_date)->format('Y-m-d') }}"
                                data-issue="{{ optional($i->issue_date)->format('Y-m-d') }}" style="padding:.2rem .45rem;margin-left:.25rem;"><i class="fas fa-calendar-plus"></i></button>
                        @endif
                    </td>
                    <td><span class="bl-pill status-pill {{ $i->display_status }}">{{ $i->status_label }}</span><div class="bl-muted late-note">@if ($i->isOverdue()){{ $i->daysOverdue() }}d late @endif</div></td>
                    <td style="white-space:nowrap;"><a class="bl-btn sm primary" href="{{ route('billing.invoices.show', $i) }}">Open</a>
                        <a class="bl-btn sm ghost" target="_blank" href="{{ route('billing.invoices.pdf', $i) }}"><i class="fas fa-file-pdf"></i></a></td>
                </tr>
            @endforeach
            </tbody>
            <tfoot><tr><td colspan="6">{{ $totals->c }} invoice(s) matching</td><td class="num">{{ number_format($totals->t, 0) }}</td><td class="num">{{ number_format($totals->p, 0) }}</td><td class="num">{{ number_format($totals->b, 0) }}</td><td colspan="3"></td></tr></tfoot>
        </table></div>
        <div class="p-3">{{ $invoices->links() }}</div>
        @endif
    </div>
    @if ($canEditDue) @include('Billing.admin.partials.due-date-modal') @endif
</div>
 </div>
</div>
@endsection
@section('js') @include('Billing.partials.reason-modal') @if ($canEditDue) @include('Billing.admin.partials.due-date-js') @endif @endsection
