@extends('layouts-side-bar.master')
@section('export_title', 'Billing & Invoices')
@section('css') @include('Billing.partials.styles') @endsection
@section('page-header')
    <div class="bl">
        <div class="bl-hero">
            <div style="position:relative;z-index:1;"><span class="bl-badge"><i class="fas fa-file-invoice-dollar"></i>
                    Billing</span>
                <h1>Billing &amp; Invoices</h1>
                <p>Your SMASA subscription invoices and payments</p>
            </div>
        </div>
    </div>
@endsection
@section('content')
    @php $f = fn($v) => number_format($v, 0);
    $cur = $settings->currency; @endphp
    <div class="bl">
        @include('Billing.partials.flash')
        @if ($currentInvoice && $currentInvoice->isCollectable())
            <div class="bl-alert {{ $currentInvoice->isOverdue() ? 'error' : 'warn' }}"><i
                    class="fas fa-file-invoice-dollar mt-1"></i>
                <div><strong>{{ $current->name }}: {{ $cur }} {{ $f($currentInvoice->balance) }}
                        {{ $currentInvoice->isOverdue() ? 'is overdue' : 'is due' }}</strong>
                    @if ($currentInvoice->due_date) — {{ $currentInvoice->isOverdue() ? 'was due' : 'due' }}
                    {{ $currentInvoice->due_date->format('j M Y') }}@endif.
                    <a href="{{ route('school.billing.show', $currentInvoice->id) }}">View invoice</a>
                </div>
            </div>
        @elseif ($currentInvoice && $currentInvoice->status === 'paid')
            <div class="bl-alert success"><i class="fas fa-circle-check mt-1"></i>
                <div>{{ $current->name }} is fully paid. Thank you!</div>
            </div>
        @endif
        <div class="bl-kpis">
            <div class="bl-kpi blue"><span class="ico"><i class="fas fa-file-invoice"></i></span>
                <div class="lbl">Total billed</div>
                <div class="val">{{ $cur }} {{ $f($position['billed']) }}</div>
                <div class="sub">{{ $position['invoices'] }} invoice(s)</div>
            </div>
            <div class="bl-kpi green"><span class="ico"><i class="fas fa-circle-check"></i></span>
                <div class="lbl">Total paid</div>
                <div class="val">{{ $cur }} {{ $f($position['paid']) }}</div>
            </div>
            <div class="bl-kpi amber"><span class="ico"><i class="fas fa-hourglass-half"></i></span>
                <div class="lbl">Outstanding</div>
                <div class="val">{{ $cur }} {{ $f($position['outstanding']) }}</div>
            </div>
            <div class="bl-kpi red"><span class="ico"><i class="fas fa-triangle-exclamation"></i></span>
                <div class="lbl">Overdue</div>
                <div class="val">{{ $cur }} {{ $f($position['overdue']) }}</div>
            </div>
        </div>
        <div class="bl-card">
            <div class="bl-card-h">
                <h3>Invoices</h3>
            </div>
            @if ($invoices->isEmpty())
                <div class="bl-empty"><i class="fas fa-file-circle-question"></i>No invoices have been issued to your school
                    yet.</div>
            @else<div class="bl-table-wrap">
                <table class="bl-table">
                    <thead>
                        <tr>
                            <th>Invoice</th>
                            <th>Term</th>
                            <th class="num">Students</th>
                            <th class="num">Total</th>
                            <th class="num">Paid</th>
                            <th class="num">Balance</th>
                            <th>Due</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($invoices as $i)
                            <tr>
                                <td><a
                                        href="{{ route('school.billing.show', $i->id) }}"><strong>{{ $i->invoice_number }}</strong></a>
                                </td>
                                <td>{{ $i->term->name ?? '' }}</td>
                                <td class="num">{{ number_format($i->student_count) }}</td>
                                <td class="num"><strong>{{ $f($i->total) }}</strong></td>
                                <td class="num" style="color:var(--bl-green)">{{ $f($i->amount_paid) }}</td>
                                <td class="num">
                                    <strong>{{ in_array($i->status, ['issued', 'partial']) ? $f($i->balance) : '—' }}</strong>
                                </td>
                                <td>{{ optional($i->due_date)->format('j M Y') ?? '—' }}</td>
                                <td><span class="bl-pill {{ $i->display_status }}">{{ $i->status_label }}</span></td>
                                <td style="white-space:nowrap"><a class="bl-btn sm primary"
                                        href="{{ route('school.billing.show', $i->id) }}">View</a> <a class="bl-btn sm ghost"
                                        target="_blank" href="{{ route('school.billing.pdf', $i->id) }}"><i
                                            class="fas fa-file-pdf"></i></a></td>
                        </tr>@endforeach
                    </tbody>
                </table>
            </div>@endif
        </div>
        @if ($payments->count())
            <div class="bl-card">
                <div class="bl-card-h">
                    <h3>Payment history</h3>
                </div>
                <div class="bl-table-wrap">
                    <table class="bl-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Receipt</th>
                                <th>Invoice</th>
                                <th>Method</th>
                                <th class="num">Amount</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($payments as $p)
                                <tr>
                                    <td>{{ $p->paid_on->format('j M Y') }}</td>
                                    <td>{{ $p->receipt_number }}</td>
                                    <td>{{ $p->invoice->invoice_number ?? '' }}</td>
                                    <td>{{ $p->method_label }}</td>
                                    <td class="num"><strong>{{ $f($p->amount) }}</strong></td>
                                    <td><a class="bl-btn sm ghost" target="_blank"
                                            href="{{ route('school.billing.receipt', $p->id) }}"><i class="fas fa-receipt"></i>
                                            Receipt</a></td>
                            </tr>@endforeach
                        </tbody>
                    </table>
                </div>
        </div>@endif
    </div></div></div></div>
@endsection