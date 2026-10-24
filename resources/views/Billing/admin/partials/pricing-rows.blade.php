{{-- Rows of School Pricing. Re-rendered by the live search, so every button inside is wired with a
     delegated handler on the page (never bind to these elements directly). --}}
@forelse ($rows as $r)
    @php
        $p = $r['pricing'];
        $disc = $p['discount_type'] === 'percent' ? rtrim(rtrim(number_format($p['discount_value'], 2), '0'), '.') . '%' : ($p['discount_type'] === 'fixed' ? 'UGX ' . number_format($p['discount_value'], 0) : '—');
        $gross = $r['students'] * $p['rate'];
        $net = $p['discount_type'] === 'percent' ? $gross * (1 - $p['discount_value'] / 100) : ($p['discount_type'] === 'fixed' ? max($gross - $p['discount_value'], 0) : $gross);
    @endphp
    <tr>
        <td><strong>{{ $r['school']->name }}</strong>
            <div class="bl-muted">{{ $r['school']->registration_code }}</div>
        </td>
        <td class="num">{{ number_format($r['students']) }}</td>
        <td class="num">{{ number_format($p['rate'], 0) }}</td>
        <td>{{ $disc }} @if ($p['reason'])
                <div class="bl-muted">{{ $p['reason'] }}</div>
            @endif
        </td>
        <td>@if ($p['exempt'])<span class="bl-pill waived">Exempt</span>@elseif ($p['source'] === 'term')<span class="bl-muted">Term rate</span>@else<span class="bl-pill partial">{{ $p['source'] === 'school' ? 'All terms' : 'This term' }}</span>@endif
        </td>
        <td class="num">{{ $p['exempt'] ? '—' : number_format($net, 0) }}</td>
        <td class="num">
            @if ($r['arrears'])
                <span class="bl-pill overdue" title="{{ $r['arrears']['count'] }} unpaid invoice(s)">{{ number_format($r['arrears']['amount'], 0) }}</span>
            @else
                <span class="bl-muted">—</span>
            @endif
        </td>
        <td style="white-space:nowrap">
            <button class="bl-btn sm primary price-btn" data-school="{{ $r['school']->id }}"
                data-name="{{ $r['school']->name }}" data-term="{{ $term->id }}" data-termname="{{ $term->name }}"
                data-rate="{{ $p['source'] === 'term' ? '' : (float) $p['rate'] }}" data-dtype="{{ $p['discount_type'] }}"
                data-dval="{{ (float) $p['discount_value'] }}" data-exempt="{{ $p['exempt'] ? 1 : 0 }}"
                data-reason="{{ $p['reason'] }}"><i class="fas fa-pen"></i> Set</button>

            @if ($r['invoice'])
                <a class="bl-btn sm ghost" href="{{ route('billing.invoices.show', $r['invoice']) }}" title="Open this school's invoice for {{ $term->name }}"><i class="fas fa-file-invoice"></i> Invoice</a>
            @elseif ($term->isOpen() && !$p['exempt'] && $r['students'] > 0 && \App\Support\AdminAccess::can('billing.generate_invoices'))
                <form method="POST" action="{{ route('billing.terms.school-invoice', [$term, $r['school']->id]) }}" class="d-inline"
                    data-confirm="Invoice {{ $r['school']->name }} for {{ $term->name }}?"
                    data-confirm-text="{{ $r['students'] }} student(s) × {{ number_format($p['rate'], 0) }} — the school will see the invoice straight away.">
                    @csrf
                    <button class="bl-btn sm ghost" title="Create and issue this school's invoice for {{ $term->name }}"><i class="fas fa-file-circle-plus"></i> Invoice</button>
                </form>
            @endif

            @foreach ($r['rates'] as $rate)
                <form method="POST" action="{{ route('billing.pricing.delete', $rate) }}" class="d-inline" data-confirm="Remove this override?">
                    @csrf @method('DELETE')
                    <button class="bl-btn sm ghost-danger" title="Remove {{ $rate->billing_term_id ? 'term' : 'all-terms' }} override"><i class="fas fa-xmark"></i></button>
                </form>
            @endforeach
        </td>
    </tr>
@empty
    <tr>
        <td colspan="8">
            <div class="bl-empty" style="padding:1.5rem;"><i class="fas fa-magnifying-glass"></i>
                @if ($search !== '') No school matches “{{ $search }}”. @else No active schools yet. @endif
            </div>
        </td>
    </tr>
@endforelse
