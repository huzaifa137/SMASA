@extends('layouts-side-bar.master')
@section('export_title', 'School Pricing')
@section('css') @include('Billing.partials.styles') @endsection
@section('page-header')
    <div class="bl">
        <div class="bl-hero">
            <div style="position:relative;z-index:1;"><span class="bl-badge"><i class="fas fa-tags"></i> Pricing</span>
                <h1>School Pricing &amp; Discounts</h1>
                <p>Default {{ $settings->currency }} {{ number_format($settings->default_rate_per_student, 0) }} per student
                    · set a custom rate, discount or exemption per school</p>
            </div>
            <div class="bl-hero-actions">
                <form method="GET"><select name="term" class="bl-input"
                        onchange="this.form.submit()">@foreach ($terms as $t)
                            <option value="{{ $t->id }}" @selected($term && $t->id === $term->id)>{{ $t->name }}</option>
                        @endforeach
                    </select></form>
            </div>
        </div>
    </div>
@endsection
@section('content')
    <div class="bl">
        @include('Billing.partials.flash') @include('Billing.partials.admin-nav')
        @if (!$term)
        <div class="bl-alert warn">Create a billing term first.</div>@else
            <div class="bl-alert info"><i class="fas fa-circle-info mt-1"></i>
                <div>Showing the price each school would get for <strong>{{ $term->name }}</strong>. Overrides apply to invoices
                    generated from now on. A term-specific override beats an "all terms" one.</div>
            </div>
            <div class="bl-card">
                <div class="bl-table-wrap">
                    <table class="bl-table">
                        <thead>
                            <tr>
                                <th>School</th>
                                <th class="num">Students</th>
                                <th class="num">Rate</th>
                                <th>Discount</th>
                                <th>Source</th>
                                <th class="num">Est. before tax</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($rows as $r)
                                @php $p = $r['pricing'];
                                    $disc = $p['discount_type'] === 'percent' ? rtrim(rtrim(number_format($p['discount_value'], 2), '0'), '.') . '%' : ($p['discount_type'] === 'fixed' ? 'UGX ' . number_format($p['discount_value'], 0) : '—');
                                    $gross = $r['students'] * $p['rate'];
                                $net = $p['discount_type'] === 'percent' ? $gross * (1 - $p['discount_value'] / 100) : ($p['discount_type'] === 'fixed' ? max($gross - $p['discount_value'], 0) : $gross); @endphp
                                <tr>
                                    <td><strong>{{ $r['school']->name }}</strong>
                                        <div class="bl-muted">{{ $r['school']->registration_code }}</div>
                                    </td>
                                    <td class="num">{{ number_format($r['students']) }}</td>
                                    <td class="num">{{ number_format($p['rate'], 0) }}</td>
                                    <td>{{ $disc }} @if ($p['reason'])
                                    <div class="bl-muted">{{ $p['reason'] }}</div>@endif
                                    </td>
                                    <td>@if ($p['exempt'])<span
                                    class="bl-pill waived">Exempt</span>@elseif ($p['source'] === 'term')<span
                                            class="bl-muted">Term rate</span>@else<span
                                            class="bl-pill partial">{{ $p['source'] === 'school' ? 'All terms' : 'This term' }}</span>@endif
                                    </td>
                                    <td class="num">{{ $p['exempt'] ? '—' : number_format($net, 0) }}</td>
                                    <td style="white-space:nowrap"><button class="bl-btn sm primary price-btn"
                                            data-school="{{ $r['school']->id }}" data-name="{{ $r['school']->name }}"
                                            data-term="{{ $term->id }}" data-termname="{{ $term->name }}"
                                            data-rate="{{ $p['source'] === 'term' ? '' : (float) $p['rate'] }}"
                                            data-dtype="{{ $p['discount_type'] }}" data-dval="{{ (float) $p['discount_value'] }}"
                                            data-exempt="{{ $p['exempt'] ? 1 : 0 }}" data-reason="{{ $p['reason'] }}"><i
                                                class="fas fa-pen"></i> Set</button>
                                        @foreach ($r['rates'] as $rate)
                                            <form method="POST" action="{{ route('billing.pricing.delete', $rate) }}" class="d-inline"
                                                data-confirm="Remove this override?">@csrf @method('DELETE')<button
                                                    class="bl-btn sm ghost-danger"
                                                    title="Remove {{ $rate->billing_term_id ? 'term' : 'all-terms' }} override"><i
                                        class="fas fa-xmark"></i></button></form>@endforeach
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="modal fade bl" id="priceModal" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <form class="modal-content" method="POST" action="{{ route('billing.pricing.save') }}"
                        style="border-radius:16px;">@csrf
                        <input type="hidden" name="school_id" id="pm_school">
                        <div class="modal-header">
                            <h5 class="modal-title" id="pm_title">Pricing</h5><button type="button" class="close"
                                data-dismiss="modal"><span>&times;</span></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="form-group col-12"><label>Applies to</label><select name="billing_term_id"
                                        id="pm_term" class="form-control w-100"></select></div>
                                <div class="form-group col-md-6"><label>Custom rate / student</label><input type="number"
                                        min="0" name="custom_rate" id="pm_rate" class="form-control w-100"
                                        placeholder="Term rate">
                                    <div class="bl-hint">Empty = use the term rate.</div>
                                </div>
                                <div class="form-group col-md-6"><label>Discount</label><select name="discount_type"
                                        id="pm_dtype" class="form-control w-100">
                                        <option value="none">None</option>
                                        <option value="percent">Percentage (%)</option>
                                        <option value="fixed">Fixed amount</option>
                                    </select></div>
                                <div class="form-group col-md-6"><label>Discount value</label><input type="number" min="0"
                                        step="0.01" name="discount_value" id="pm_dval" class="form-control w-100" value="0">
                                </div>
                                <div class="form-group col-md-6"><label>Reason</label><input name="reason" id="pm_reason"
                                        maxlength="255" class="form-control w-100"></div>
                                <div class="col-12">
                                    <div class="form-check"><input type="checkbox" class="form-check-input" name="is_exempt"
                                            id="pm_exempt" value="1"><label class="form-check-label" for="pm_exempt">Exempt — do
                                            not bill this school at all</label></div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer"><button type="button" class="bl-btn ghost"
                                data-dismiss="modal">Cancel</button><button class="bl-btn primary">Save pricing</button></div>
                    </form>
                </div>
            </div>
        @endif
    </div>
    </div>
            </div>
@endsection
@section('js')
    <script>
        $(document).on('click', '.price-btn', function () {
            var d = this.dataset;
            $('#pm_school').val(d.school); $('#pm_title').text('Pricing — ' + d.name);
            $('#pm_term').html('<option value="">All terms</option><option value="' + d.term + '" selected>Only ' + d.termname + '</option>');
            $('#pm_rate').val(d.rate); $('#pm_dtype').val(d.dtype); $('#pm_dval').val(d.dval); $('#pm_reason').val(d.reason);
            $('#pm_exempt').prop('checked', d.exempt === '1'); $('#priceModal').modal('show');
        });
    </script>
    @include('Billing.partials.reason-modal')
@endsection