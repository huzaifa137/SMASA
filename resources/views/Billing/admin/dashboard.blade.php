@extends('layouts-side-bar.master')
@section('export_title', 'SaaS Billing')
@section('css')
    @include('Billing.partials.styles')
@endsection

@section('page-header')
    <div class="bl">
        <div class="bl-hero">
            <div style="position:relative;z-index:1;">
                <span class="bl-badge"><i class="fas fa-file-invoice-dollar"></i> Platform billing</span>
                <h1>Billing Dashboard</h1>
                <p>
                    @if ($term)
                        {{ $term->name }} · {{ \App\Services\BillingService::fmt($term->rate_per_student, $settings->currency) }} per student
                        @if ($term->is_current) · <strong>Current term</strong>@endif
                    @else
                        Set up your first billing term to start invoicing schools.
                    @endif
                </p>
            </div>
            <div class="bl-hero-actions">
                @if ($terms->count())
                    <form method="GET" action="{{ route('billing.dashboard') }}">
                        <select name="term" class="bl-input" onchange="this.form.submit()" style="min-width:210px;">
                            @foreach ($terms as $t)
                                <option value="{{ $t->id }}" @selected($term && $t->id === $term->id)>{{ $t->name }}{{ $t->is_current ? ' (current)' : '' }}</option>
                            @endforeach
                        </select>
                    </form>
                @endif
                @if ($term && $term->isOpen() && \App\Support\AdminAccess::can('billing.generate_invoices'))
                    <button type="button" class="bl-btn light" id="openGenerate"><i class="fas fa-bolt"></i> Generate invoices</button>
                @endif
                @if (!$term && \App\Support\AdminAccess::can('billing.manage_terms'))
                    <a href="{{ route('billing.terms') }}" class="bl-btn light"><i class="fas fa-plus"></i> Create billing term</a>
                @endif
            </div>
        </div>
    </div>
@endsection

@section('content')
<div class="bl">
    @include('Billing.partials.flash')
    @include('Billing.partials.admin-nav')

    @if (!$term)
        <div class="bl-card"><div class="bl-empty">
            <i class="fas fa-calendar-plus"></i>
            <strong>No billing term yet</strong>
            <p class="mb-3">A billing term decides which term schools are invoiced for and how much each student costs.</p>
            <a href="{{ route('billing.terms') }}" class="bl-btn primary">Create the first billing term</a>
        </div></div>
    @else
        @php $cur = $settings->currency; $f = fn($v) => number_format($v, 0); @endphp

        @if (!$term->isOpen())
            <div class="bl-alert warn"><i class="fas fa-lock mt-1"></i><div><strong>{{ $term->name }} is closed.</strong> You can still record payments, but new invoices can't be generated until it is reopened.</div></div>
        @endif
        @if ($stats['count_draft'] > 0 && \App\Support\AdminAccess::can('billing.generate_invoices'))
            <div class="bl-alert info"><i class="fas fa-pen-ruler mt-1"></i>
                <div><strong>{{ $stats['count_draft'] }} draft invoice(s)</strong> are not visible to schools yet.
                    <form method="POST" action="{{ route('billing.terms.issue-all', $term) }}" class="d-inline" data-confirm="Issue all {{ $stats['count_draft'] }} draft invoices?" data-confirm-text="Schools will be able to see and pay them.">@csrf
                        <button class="bl-btn sm primary ml-2" type="submit">Issue all drafts</button></form></div></div>
        @endif
        @if ($term->isOpen() && $stats['not_invoiced']->count() > 0 && $term->invoices_generated_at && \App\Support\AdminAccess::can('billing.generate_invoices'))
            <div class="bl-alert warn"><i class="fas fa-school mt-1"></i>
                <div><strong>{{ $stats['not_invoiced']->count() }} active school(s) have no invoice for this term</strong>
                    (new schools, or schools that had no students or are exempt when you generated).
                    <a href="#" id="openGenerate2">Generate missing invoices</a></div></div>
        @endif

        {{-- KPIs --}}
        <div class="bl-kpis">
            <div class="bl-kpi blue"><span class="ico"><i class="fas fa-sack-dollar"></i></span>
                <div class="lbl">Expected this term</div><div class="val">{{ $cur }} {{ $f($stats['expected']) }}</div>
                <div class="sub">{{ $stats['schools_billed'] }} school(s) · {{ number_format($stats['students']) }} students</div></div>
            <div class="bl-kpi green"><span class="ico"><i class="fas fa-circle-check"></i></span>
                <div class="lbl">Collected</div><div class="val">{{ $cur }} {{ $f($stats['collected']) }}</div>
                <div class="sub">{{ $stats['rate_pct'] }}% of expected</div></div>
            <div class="bl-kpi amber"><span class="ico"><i class="fas fa-hourglass-half"></i></span>
                <div class="lbl">Outstanding</div><div class="val">{{ $cur }} {{ $f($stats['outstanding']) }}</div>
                <div class="sub">{{ $stats['count_unpaid'] + $stats['count_partial'] + $stats['count_overdue'] }} school(s) still owe</div></div>
            <div class="bl-kpi red"><span class="ico"><i class="fas fa-triangle-exclamation"></i></span>
                <div class="lbl">Overdue</div><div class="val">{{ $cur }} {{ $f($stats['overdue_amount']) }}</div>
                <div class="sub">{{ $stats['count_overdue'] }} school(s) past the due date</div></div>
            <div class="bl-kpi purple"><span class="ico"><i class="fas fa-percent"></i></span>
                <div class="lbl">Discounts given</div><div class="val">{{ $cur }} {{ $f($stats['discounts']) }}</div>
                <div class="sub">Before tax, on {{ $cur }} {{ $f($stats['subtotal']) }} gross</div></div>
            <div class="bl-kpi"><span class="ico" style="background:var(--bl-gray-l);color:var(--bl-gray);"><i class="fas fa-receipt"></i></span>
                <div class="lbl">{{ $settings->tax_name }} included</div><div class="val">{{ $cur }} {{ $f($stats['tax']) }}</div>
                <div class="sub">In the expected total</div></div>
        </div>

        <div class="bl-grid3">
            <div class="bl-card">
                <div class="bl-card-h"><h3>Collection progress</h3><small>{{ $term->name }}</small></div>
                <div class="bl-card-b">
                    <div class="d-flex justify-content-between mb-2"><strong>{{ $stats['rate_pct'] }}% collected</strong>
                        <span class="bl-muted">{{ $cur }} {{ $f($stats['collected']) }} of {{ $f($stats['expected']) }}</span></div>
                    <div class="bl-progress mb-4"><span style="width: {{ min($stats['rate_pct'], 100) }}%"></span></div>

                    <div class="bl-kpis" style="margin:0;grid-template-columns:repeat(auto-fit,minmax(130px,1fr));">
                        @foreach ([
                            ['paid', 'Paid in full', $stats['count_paid'], 'var(--bl-green)'],
                            ['partial', 'Partially paid', $stats['count_partial'], 'var(--bl-blue)'],
                            ['unpaid', 'Not paid yet', $stats['count_unpaid'], 'var(--bl-amber)'],
                            ['overdue', 'Overdue', $stats['count_overdue'], 'var(--bl-red)'],
                            ['draft', 'Draft', $stats['count_draft'], 'var(--bl-gray)'],
                            ['waived', 'Written off', $stats['count_writeoff'], 'var(--bl-purple)'],
                        ] as [$key, $label, $n, $color])
                            <a href="{{ route('billing.invoices', ['term' => $term->id, 'status' => $key]) }}" class="bl-kpi" style="padding:.8rem 1rem;text-decoration:none;color:inherit;">
                                <div class="lbl">{{ $label }}</div>
                                <div class="val" style="color:{{ $color }};font-size:1.4rem;">{{ $n }}</div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="bl-card">
                <div class="bl-card-h"><h3>Overdue ageing</h3><small>{{ $term->name }}</small></div>
                <div class="bl-card-b">
                    @php $totalAged = collect($aging['buckets'])->sum('amount'); @endphp
                    @if ($totalAged <= 0)
                        <div class="bl-empty" style="padding:1.4rem 0;"><i class="fas fa-face-smile"></i>Nothing is overdue.</div>
                    @else
                        <ul class="bl-list">
                            @foreach ($aging['buckets'] as $label => $b)
                                <li><span>{{ $label }} <span class="bl-muted">· {{ $b['count'] }} school(s)</span></span><strong>{{ $cur }} {{ $f($b['amount']) }}</strong></li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>

        <div class="bl-card">
            <div class="bl-card-h"><h3>Expected vs collected by term</h3><small>Last {{ $trend->count() }} terms</small></div>
            <div class="bl-card-b"><div style="position:relative;height:280px;"><canvas id="trendChart"></canvas></div></div>
        </div>

        <div class="bl-grid2">
            <div class="bl-card">
                <div class="bl-card-h"><h3>Biggest balances</h3><a href="{{ route('billing.invoices', ['term' => $term->id, 'status' => 'all']) }}" class="bl-btn sm ghost">All invoices</a></div>
                @if ($topOutstanding->isEmpty())
                    <div class="bl-empty"><i class="fas fa-circle-check"></i>No outstanding balances for this term.</div>
                @else
                    <div class="bl-table-wrap"><table class="bl-table">
                        <thead><tr><th>School</th><th class="num">Balance</th><th>Due</th><th></th></tr></thead>
                        <tbody>
                        @foreach ($topOutstanding as $inv)
                            <tr>
                                <td><a href="{{ route('billing.invoices.show', $inv) }}"><strong>{{ $inv->school->name ?? '—' }}</strong></a><div class="bl-muted">{{ $inv->invoice_number }}</div></td>
                                <td class="num"><strong>{{ $f($inv->balance) }}</strong></td>
                                <td><span class="bl-pill {{ $inv->display_status }}">{{ $inv->status_label }}</span>
                                    <div class="bl-muted">{{ optional($inv->due_date)->format('j M Y') }}</div></td>
                                <td class="num"><a class="bl-btn sm primary" href="{{ route('billing.invoices.show', $inv) }}">Open</a></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table></div>
                @endif
            </div>

            <div class="bl-card">
                <div class="bl-card-h"><h3>Recent payments</h3><a href="{{ route('billing.payments', ['term' => $term->id]) }}" class="bl-btn sm ghost">All payments</a></div>
                @if ($recentPayments->isEmpty())
                    <div class="bl-empty"><i class="fas fa-inbox"></i>No payments recorded yet.</div>
                @else
                    <div class="bl-table-wrap"><table class="bl-table">
                        <thead><tr><th>School</th><th>Date</th><th class="num">Amount</th></tr></thead>
                        <tbody>
                        @foreach ($recentPayments as $p)
                            <tr>
                                <td><strong>{{ $p->school->name ?? '—' }}</strong><div class="bl-muted">{{ $p->receipt_number }} · {{ $p->method_label }}</div></td>
                                <td>{{ $p->paid_on->format('j M Y') }}</td>
                                <td class="num"><strong style="color:var(--bl-green)">{{ $f($p->amount) }}</strong></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table></div>
                @endif
            </div>
        </div>

        <div class="bl-card">
            <div class="bl-card-h"><h3>Recent activity</h3></div>
            @if ($logs->isEmpty())
                <div class="bl-empty">Nothing has happened yet.</div>
            @else
                <ul class="bl-list" style="padding:0 1.3rem;">
                    @foreach ($logs as $log)
                        <li><span>{{ $log->description }}</span><span class="bl-muted" style="white-space:nowrap;">{{ $log->created_at->diffForHumans() }}</span></li>
                    @endforeach
                </ul>
            @endif
        </div>

        {{-- Generate modal --}}
        @if ($term->isOpen() && \App\Support\AdminAccess::can('billing.generate_invoices'))
        <div class="modal fade bl" id="generateModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <form class="modal-content" method="POST" action="{{ route('billing.terms.generate', $term) }}" style="border-radius:16px;">
                    @csrf
                    <div class="modal-header"><h5 class="modal-title">Generate invoices — {{ $term->name }}</h5>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>
                    <div class="modal-body">
                        <div id="genPreview" class="bl-muted">Counting students…</div>
                        <div class="form-check mt-3">
                            <input type="checkbox" class="form-check-input" id="issueNow" name="issue_now" value="1" checked>
                            <label class="form-check-label" for="issueNow" style="font-size:.86rem;">Issue immediately (schools can see them). Untick to create drafts you can review first.</label>
                        </div>
                        <p class="bl-hint mt-2">Safe to run again: schools that already have an invoice are skipped. Only <strong>active</strong> schools are billed, and a student enrolled in two programmes counts once.</p>
                    </div>
                    <div class="modal-footer"><button type="button" class="bl-btn ghost" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="bl-btn primary" id="genBtn" disabled>Generate</button></div>
                </form>
            </div>
        </div>
        @endif
    @endif
</div>
@endsection

@section('js')
    @if ($term)
    <script src="{{ URL::asset('assets/plugins/chartjs/chart.bundle.min.js') }}"></script>
    <script>
        (function () {
            var fmt = function (n) { return Math.round(n).toLocaleString('en-US'); };
            var data = @json($trend);
            var el = document.getElementById('trendChart');
            if (el && window.Chart) {
                new Chart(el.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: data.map(function (d) { return d.label; }),
                        datasets: [
                            { label: 'Expected', backgroundColor: 'rgba(47,44,203,.80)', data: data.map(function (d) { return d.expected; }) },
                            { label: 'Collected', backgroundColor: 'rgba(5,150,105,.85)', data: data.map(function (d) { return d.collected; }) }
                        ]
                    },
                    options: {
                        maintainAspectRatio: false,
                        legend: { position: 'bottom' },
                        scales: { yAxes: [{ ticks: { beginAtZero: true, callback: function (v) { return fmt(v); } } }] },
                        tooltips: { callbacks: { label: function (t, d) { return d.datasets[t.datasetIndex].label + ': {{ $settings->currency }} ' + fmt(t.yLabel); } } }
                    }
                });
            }

            @if ($term->isOpen() && \App\Support\AdminAccess::can('billing.generate_invoices'))
            var open = function (e) {
                if (e) e.preventDefault();
                $('#generateModal').modal('show');
                $('#genBtn').prop('disabled', true);
                $('#genPreview').text('Counting students…');
                $.getJSON(@json(route('billing.terms.preview', $term)), function (p) {
                    $('#genPreview').html(
                        '<ul class="bl-list">' +
                        '<li><span>Active schools</span><strong>' + p.schools_total + '</strong></li>' +
                        '<li><span>Already invoiced</span><strong>' + p.already_invoiced + '</strong></li>' +
                        '<li><span>Invoices to create</span><strong>' + p.to_create + '</strong></li>' +
                        '<li><span>Students to bill</span><strong>' + fmt(p.students) + '</strong></li>' +
                        '<li><span>Estimated amount (before tax &amp; discounts)</span><strong>{{ $settings->currency }} ' + fmt(p.estimate) + '</strong></li>' +
                        (p.exempt ? '<li><span>Exempt schools (skipped)</span><strong>' + p.exempt + '</strong></li>' : '') +
                        (p.no_students ? '<li><span>Schools with no students (skipped)</span><strong>' + p.no_students + '</strong></li>' : '') +
                        '</ul>');
                    $('#genBtn').prop('disabled', p.to_create === 0).text(p.to_create === 0 ? 'Nothing to generate' : 'Generate ' + p.to_create + ' invoice(s)');
                }).fail(function () { $('#genPreview').text('Could not load the preview. You can still generate.'); $('#genBtn').prop('disabled', false); });
            };
            $('#openGenerate, #openGenerate2').on('click', open);
            @endif
        })();
    </script>
    @endif
    @include('Billing.partials.reason-modal')
@endsection
