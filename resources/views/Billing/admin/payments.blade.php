@extends('layouts-side-bar.master')
@section('export_title', 'Billing Payments')
@section('css') @include('Billing.partials.styles') @endsection
@section('page-header')
    <div class="bl">
        <div class="bl-hero">
            <div style="position:relative;z-index:1;"><span class="bl-badge"><i class="fas fa-hand-holding-dollar"></i>
                    Payments</span>
                <h1>Payments Received</h1>
                <p>{{ $allTerms ? 'All terms' : ($term->name ?? '') }} · UGX {{ number_format($total, 0) }} collected</p>
            </div>
            <div class="bl-hero-actions"><a class="bl-btn light"
                    href="{{ route('billing.export', ['type' => 'payments', 'term' => $allTerms ? 'all' : ($term->id ?? null)]) }}"><i
                        class="fas fa-file-csv"></i> Export CSV</a></div>
        </div>
    </div>
@endsection
@section('content')
    <div class="bl">
        @include('Billing.partials.flash') @include('Billing.partials.admin-nav')
        @if ($byMethod->count())
            <div class="bl-kpis">@foreach ($byMethod as $m)
                <div class="bl-kpi green">
                    <div class="lbl">{{ $methods[$m->method] ?? $m->method }}</div>
                    <div class="val" style="font-size:1.25rem">UGX {{ number_format($m->total, 0) }}</div>
                    <div class="sub">{{ $m->n }} payment(s)</div>
            </div>@endforeach
        </div>@endif
        <div class="bl-card">
            <form class="bl-filters" method="GET">
                <div><label>Term</label><select name="term" class="bl-input">
                        <option value="all" @selected($allTerms)>All terms</option>
                        @foreach ($terms as $t)
                            <option value="{{ $t->id }}" @selected(!$allTerms && $term && $t->id === $term->id)>{{ $t->name }}
                        </option>@endforeach
                    </select></div>
                <div><label>Method</label><select name="method" class="bl-input">
                        <option value="">All</option>@foreach ($methods as $k => $v)
                        <option value="{{ $k }}" @selected(request('method') === $k)>{{ $v }}</option>@endforeach
                    </select></div>
                <div><label>From</label><input type="date" name="from" class="bl-input" value="{{ request('from') }}"></div>
                <div><label>To</label><input type="date" name="to" class="bl-input" value="{{ request('to') }}"></div>
                <div><label>Search</label><input name="q" class="bl-input" value="{{ $search }}"
                        placeholder="School, receipt, ref"></div>
                <button class="bl-btn primary"><i class="fas fa-filter"></i> Filter</button>
            </form>
            @if ($payments->isEmpty())
                <div class="bl-empty"><i class="fas fa-inbox"></i>No payments found.</div>
            @else<div class="bl-table-wrap">
                    <table class="bl-table">
                        <thead>
                            <tr>
                                <th>Receipt</th>
                                <th>Date</th>
                                <th>School</th>
                                <th>Invoice</th>
                                <th>Method</th>
                                <th>Reference</th>
                                <th class="num">Amount</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($payments as $p)
                                <tr style="{{ $p->status === 'reversed' ? 'opacity:.55' : '' }}">
                                    <td><strong>{{ $p->receipt_number }}</strong></td>
                                    <td>{{ $p->paid_on->format('j M Y') }}</td>
                                    <td>{{ $p->school->name ?? '—' }}</td>
                                    <td><a
                                            href="{{ route('billing.invoices.show', $p->invoice_id) }}">{{ $p->invoice->invoice_number ?? '' }}</a>
                                    </td>
                                    <td>{{ $p->method_label }}</td>
                                    <td>{{ $p->reference ?: '—' }}</td>
                                    <td class="num"><strong>{{ number_format($p->amount, 0) }}</strong></td>
                                    <td><span class="bl-pill {{ $p->status }}">{{ $p->status }}</span></td>
                                    <td style="white-space:nowrap"><a class="bl-btn sm ghost" target="_blank"
                                            href="{{ route('billing.payments.receipt', $p) }}"><i class="fas fa-receipt"></i></a>
                                        @if ($p->status === 'valid' && \App\Support\AdminAccess::can('billing.record_payments'))<button
                                            class="bl-btn sm ghost-danger"
                                            data-reason-url="{{ route('billing.payments.reverse', $p) }}"
                                            data-reason-title="Reverse {{ $p->receipt_number }}"
                                        data-reason-btn="Reverse payment"><i class="fas fa-rotate-left"></i></button>@endif
                                    </td>
                            </tr>@endforeach
                        </tbody>
                    </table>
                </div>
            <div class="p-3">{{ $payments->links() }}</div>@endif
        </div>
    </div></div>
    </div>
@endsection
@section('js') @include('Billing.partials.reason-modal') @endsection