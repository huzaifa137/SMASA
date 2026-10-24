@extends('layouts-side-bar.master')
@section('export_title', 'Billing Reports')
@section('css') @include('Billing.partials.styles') @endsection
@section('page-header')
    <div class="bl">
        <div class="bl-hero">
            <div style="position:relative;z-index:1;"><span class="bl-badge"><i class="fas fa-chart-column"></i>
                    Reports</span>
                <h1>Billing Reports</h1>
                <p>{{ $term->name ?? 'No term' }}</p>
            </div>
            <div class="bl-hero-actions">
                <form method="GET"><select name="term" class="bl-input"
                        onchange="this.form.submit()">@foreach ($terms as $t)
                            <option value="{{ $t->id }}" @selected($term && $t->id === $term->id)>{{ $t->name }}</option>
                        @endforeach
                    </select></form>
                @if ($term)<a class="bl-btn light"
                    href="{{ route('billing.export', ['type' => 'outstanding', 'term' => $term->id]) }}"><i
                class="fas fa-file-csv"></i> Defaulters CSV</a>@endif
            </div>
        </div>
    </div>
@endsection
@section('content')
    @php $f = fn($v) => number_format($v, 0); @endphp
    <div class="bl">
        @include('Billing.partials.flash') @include('Billing.partials.admin-nav')

        <div class="bl-card">
            <div class="bl-card-h">
                <h3>Term-by-term summary</h3><a class="bl-btn sm ghost"
                    href="{{ route('billing.export', ['type' => 'invoices', 'term' => 'all']) }}"><i
                        class="fas fa-file-csv"></i> All invoices CSV</a>
            </div>
            <div class="bl-table-wrap">
                <table class="bl-table">
                    <thead>
                        <tr>
                            <th>Term</th>
                            <th class="num">Schools</th>
                            <th class="num">Students</th>
                            <th class="num">Discounts</th>
                            <th class="num">Tax</th>
                            <th class="num">Expected</th>
                            <th class="num">Collected</th>
                            <th class="num">Outstanding</th>
                            <th class="num">Written off</th>
                            <th style="width:140px">Collected %</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($termRows as $r) @php $s = $r['stats']; @endphp
                            <tr>
                                <td><a
                                        href="{{ route('billing.dashboard', ['term' => $r['term']->id]) }}"><strong>{{ $r['term']->name }}</strong></a>
                                </td>
                                <td class="num">{{ $s['schools_billed'] }}</td>
                                <td class="num">{{ number_format($s['students']) }}</td>
                                <td class="num">{{ $f($s['discounts']) }}</td>
                                <td class="num">{{ $f($s['tax']) }}</td>
                                <td class="num"><strong>{{ $f($s['expected']) }}</strong></td>
                                <td class="num" style="color:var(--bl-green)">{{ $f($s['collected']) }}</td>
                                <td class="num">{{ $f($s['outstanding']) }}</td>
                                <td class="num">{{ $f($s['written_off']) }}</td>
                                <td>
                                    <div class="bl-progress"><span style="width:{{ min($s['rate_pct'], 100) }}%"></span></div>
                                    <div class="bl-muted">{{ $s['rate_pct'] }}%</div>
                                </td>
                            </tr>
                        @empty<tr>
                            <td colspan="10" class="bl-empty">No terms yet.</td>
                        </tr>@endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bl-grid2">
            <div class="bl-card">
                <div class="bl-card-h">
                    <h3>Ageing — {{ $term->name ?? '' }}</h3>
                </div>
                <div class="bl-card-b">
                    <ul class="bl-list">
                        @foreach ($aging['buckets'] as $label => $b)
                            <li><span>{{ $label }} <span class="bl-muted">· {{ $b['count'] }}</span></span><strong>UGX
                        {{ $f($b['amount']) }}</strong></li>@endforeach
                    </ul>
                </div>
            </div>
            <div class="bl-card">
                <div class="bl-card-h">
                    <h3>Ageing — all terms</h3>
                </div>
                <div class="bl-card-b">
                    <ul class="bl-list">
                        @foreach ($agingAll['buckets'] as $label => $b)
                            <li><span>{{ $label }} <span class="bl-muted">· {{ $b['count'] }}</span></span><strong>UGX
                        {{ $f($b['amount']) }}</strong></li>@endforeach
                    </ul>
                </div>
            </div>
        </div>

        <div class="bl-card">
            <div class="bl-card-h">
                <h3>School-by-school — {{ $term->name ?? '' }}</h3><small>Largest balance first</small>
            </div>
            @if ($schools->isEmpty())
            <div class="bl-empty">No invoices for this term.</div>@else
                <div class="bl-table-wrap">
                    <table class="bl-table">
                        <thead>
                            <tr>
                                <th>School</th>
                                <th class="num">Students</th>
                                <th class="num">Total</th>
                                <th class="num">Paid</th>
                                <th class="num">Balance</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($schools as $r) @php $i = $r['invoice']; @endphp
                                <tr>
                                    <td><a href="{{ route('billing.invoices.show', $i) }}"><strong>{{ $r['name'] }}</strong></a>
                                    </td>
                                    <td class="num">{{ number_format($i->student_count) }}</td>
                                    <td class="num">{{ $f($i->total) }}</td>
                                    <td class="num" style="color:var(--bl-green)">{{ $f($i->amount_paid) }}</td>
                                    <td class="num">
                                        <strong>{{ in_array($i->status, ['issued', 'partial']) ? $f($i->balance) : '—' }}</strong>
                                    </td>
                                    <td><span class="bl-pill {{ $i->display_status }}">{{ $i->status_label }}</span></td>
                            </tr>@endforeach
                        </tbody>
                    </table>
            </div>@endif
        </div>
    </div>
    </div>
    </div>
@endsection
@section('js') @include('Billing.partials.reason-modal') @endsection