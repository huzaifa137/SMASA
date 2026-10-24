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
                <form method="GET"><input type="hidden" name="q" value="{{ $search }}"><select name="term" class="bl-input"
                        onchange="this.form.submit()">@foreach ($terms as $t)
                            <option value="{{ $t->id }}" @selected($term && $t->id === $term->id)>{{ $t->name }}</option>
                        @endforeach
                    </select></form>
            </div>
        </div>
    </div>
@endsection
@section('content')
<style>
    .bl-table thead th {
    background-color: #2C29CA !important;
    color: #FFFFFF !important;
}
</style>
    <div class="bl">
        @include('Billing.partials.flash') @include('Billing.partials.admin-nav')
        @if (!$term)
        <div class="bl-alert warn">Create a billing term first.</div>@else
            <div class="bl-alert info"><i class="fas fa-circle-info mt-1"></i>
                <div>Showing the price each school would get for <strong>{{ $term->name }}</strong>. Overrides apply to invoices
                    generated from now on. A term-specific override beats an "all terms" one.</div>
            </div>
            <div class="bl-card">
                <div style="padding:.9rem 1rem;border-bottom:1px solid var(--bl-border);display:flex;gap:.6rem;align-items:center;flex-wrap:wrap;">
                    <div style="position:relative;flex:1;min-width:240px;max-width:460px;">
                        <i class="fas fa-magnifying-glass" style="position:absolute;left:.8rem;top:50%;transform:translateY(-50%);color:var(--bl-t3,#94a3b8);"></i>
                        <input type="search" id="pricingSearch" class="bl-input" style="width:100%;padding-left:2.2rem;"
                            placeholder="Search school name or code…" value="{{ $search }}" autocomplete="off">
                    </div>
                    <span id="searchSpin" class="bl-muted" style="display:none;"><i class="fas fa-spinner fa-spin"></i> Searching…</span>
                </div>
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
                                <th class="num" title="Unpaid invoices from any term">Unpaid</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="pricingRows">
                            @include('Billing.admin.partials.pricing-rows')
                        </tbody>
                    </table>
                </div>
                <div id="pricingPager">@include('Billing.admin.partials.pricing-pager')</div>
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

        // ── Live school search ────────────────────────────────────────────
        // Only #pricingRows / #pricingPager are replaced. Every button inside uses a handler bound to
        // `document` (above) or a normal <form>, so they keep working on whatever rows are showing.
        (function () {
            var url = @json(route('billing.pricing')), termId = @json($term ? $term->id : null);
            var timer = null, req = null, page = {{ $page->currentPage() }};

            function load() {
                if (req) req.abort();
                $('#searchSpin').show();
                req = $.ajax({
                    url: url, dataType: 'json',
                    data: { q: $('#pricingSearch').val(), term: termId, page: page }
                }).done(function (r) {
                    $('#pricingRows').html(r.rows); $('#pricingPager').html(r.pager);
                    var u = new URL(window.location.href);
                    $('#pricingSearch').val() ? u.searchParams.set('q', $('#pricingSearch').val()) : u.searchParams.delete('q');
                    page > 1 ? u.searchParams.set('page', page) : u.searchParams.delete('page');
                    history.replaceState(null, '', u);
                }).always(function () { $('#searchSpin').hide(); });
            }
            $('#pricingSearch').on('input', function () { clearTimeout(timer); page = 1; timer = setTimeout(load, 250); });
            $(document).on('click', '.pg-btn', function () { if (this.disabled) return; page = parseInt(this.dataset.page, 10); load(); });
        })();
    </script>
    @include('Billing.partials.reason-modal')
@endsection