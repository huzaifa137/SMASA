@extends('layouts-side-bar.master')
@section('export_title', 'Billing Terms')
@section('css')
    @include('Billing.partials.styles')
@endsection

@section('page-header')
<style>
    .bl-table thead th {
    background-color: #2C29CA !important;
    color: #FFFFFF !important;
}
</style>
    <div class="bl">
        <div class="bl-hero">
            <div style="position:relative;z-index:1;">
                <span class="bl-badge"><i class="fas fa-calendar-days"></i> Billing terms</span>
                <h1>Billing Terms</h1>
                <p>Choose the term schools are billed for and the price per student. One term is "current" at a time — setting the active term under Academic years makes its billing term current.</p>
            </div>
            <div class="bl-hero-actions">
                @if (\App\Support\AdminAccess::can('billing.manage_terms'))<button class="bl-btn light" data-toggle="modal" data-target="#termModal"><i class="fas fa-plus"></i> New billing term</button>@endif
            </div>
        </div>
    </div>
@endsection

@section('content')
<div class="bl">
    @include('Billing.partials.flash')
    @include('Billing.partials.admin-nav')

    {{-- Terms + dates the administrators set on /add-academic-year --}}
    @php $activeCal = $calendarTerms->firstWhere('is_active', true); @endphp
    <div class="bl-card">
        <div class="bl-card-h" style="display:flex;justify-content:space-between;align-items:center;gap:.6rem;flex-wrap:wrap;">
            <h3 style="margin:0;">Term calendar <span class="bl-muted" style="font-weight:400;font-size:.8rem;">· set by administrators</span></h3>
            @if (Route::has('add-academic-year'))<a class="bl-btn sm ghost" href="{{ route('add-academic-year') }}"><i class="fas fa-calendar-plus"></i> Manage years &amp; terms</a>@endif
        </div>
        @if ($calendarTerms->isEmpty())
            <div class="bl-empty" style="padding:1.2rem;"><i class="fas fa-calendar-days"></i>
                No terms have been set yet. Add Term I / II / III with their dates under <a href="{{ route('add-academic-year') }}">Academic years</a>.</div>
        @else
            <div class="bl-table-wrap"><table class="bl-table">
                <thead><tr><th>Term</th><th>Starts</th><th>Ends</th><th>Platform</th><th>Billing term</th><th style="width:1%;"></th></tr></thead>
                <tbody>
                @foreach ($calendarTerms as $ct)
                    @php $hasBilling = $billedKeys->has($ct->academic_year_id . '-' . $ct->term); @endphp
                    <tr>
                        <td><strong>{{ $ct->term_label }}</strong> <span class="bl-muted">· {{ $ct->year->name ?? '' }}</span></td>
                        <td>{{ $ct->start_date->format('j M Y') }}</td>
                        <td>{{ $ct->end_date->format('j M Y') }}</td>
                        <td>@if ($ct->is_active)<span class="bl-pill current">ACTIVE TERM</span>@else<span class="bl-muted">—</span>@endif</td>
                        <td>@if ($hasBilling)<span class="bl-pill paid">Created</span>@else<span class="bl-muted">Not created</span>@endif</td>
                        <td style="white-space:nowrap;">
                            @if (!$hasBilling && \App\Support\AdminAccess::can('billing.manage_terms'))
                                <button class="bl-btn sm primary from-calendar" data-year="{{ $ct->academic_year_id }}" data-term="{{ $ct->term }}"><i class="fas fa-plus"></i> Create billing term</button>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table></div>
        @endif
    </div>

    <div class="bl-card">
        @if ($terms->isEmpty())
            <div class="bl-empty"><i class="fas fa-calendar-plus"></i><strong>No billing terms yet</strong>
                <p class="mb-3">Create one to start invoicing schools.</p>
                <button class="bl-btn primary" data-toggle="modal" data-target="#termModal">Create billing term</button></div>
        @else
            <div class="bl-table-wrap"><table class="bl-table">
                <thead><tr><th>Term</th><th>Term dates</th><th>Invoice due</th><th class="num">Rate / student</th>
                    <th class="num">Invoices</th><th class="num">Expected</th><th class="num">Collected</th><th>Status</th><th style="width:1%;"></th></tr></thead>
                <tbody>
                @foreach ($terms as $t)
                    @php $s = $sums->get($t->id); @endphp
                    <tr>
                        <td><strong>{{ $t->term_label }}</strong> <span class="bl-muted">· {{ $t->year_label }}</span>
                            @if ($t->is_current) <span class="bl-pill current ml-1">CURRENT</span>@endif</td>
                        <td>@if ($t->start_date && $t->end_date){{ $t->start_date->format('j M') }} – {{ $t->end_date->format('j M Y') }}@else<span class="bl-muted">—</span>@endif</td>
                        <td>{{ $t->due_date ? $t->due_date->format('j M Y') : '—' }}</td>
                        <td class="num">{{ $settings->currency }} {{ number_format($t->rate_per_student, 0) }}</td>
                        <td class="num">@if ($t->invoices_count)<a href="{{ route('billing.invoices', ['term' => $t->id]) }}">{{ $t->invoices_count }}</a>@else 0 @endif</td>
                        <td class="num">{{ number_format($s->expected ?? 0, 0) }}</td>
                        <td class="num" style="color:var(--bl-green);">{{ number_format($s->collected ?? 0, 0) }}</td>
                        <td><span class="bl-pill {{ $t->status }}">{{ ucfirst($t->status) }}</span></td>
                        <td style="white-space:nowrap;">
                            @php $mt = \App\Support\AdminAccess::can('billing.manage_terms'); @endphp
                            <a class="bl-btn sm ghost" href="{{ route('billing.dashboard', ['term' => $t->id]) }}" title="Dashboard"><i class="fas fa-chart-pie"></i></a>
                            @if ($mt)<button class="bl-btn sm ghost edit-term" title="Edit"
                                data-action="{{ route('billing.terms.update', $t) }}" data-name="{{ $t->name }}"
                                data-start="{{ optional($t->start_date)->format('Y-m-d') }}" data-end="{{ optional($t->end_date)->format('Y-m-d') }}"
                                data-due="{{ optional($t->due_date)->format('Y-m-d') }}" data-rate="{{ (float) $t->rate_per_student }}"
                                data-notes="{{ $t->notes }}" data-invoices="{{ $t->invoices_count }}"><i class="fas fa-pen"></i></button>@endif
                            @if ($mt && !$t->is_current)
                                <form method="POST" action="{{ route('billing.terms.current', $t) }}" class="d-inline" data-confirm="Make {{ $t->name }} the current billing term?">@csrf
                                    <button class="bl-btn sm primary" title="Set as current">Set current</button></form>
                            @endif
                            @if ($mt)<form method="POST" action="{{ route('billing.terms.toggle', $t) }}" class="d-inline" data-confirm="{{ $t->isOpen() ? 'Close' : 'Reopen' }} {{ $t->name }}?" data-confirm-text="{{ $t->isOpen() ? 'No new invoices can be generated for a closed term. Payments can still be recorded.' : '' }}">@csrf
                                <button class="bl-btn sm ghost" title="{{ $t->isOpen() ? 'Close' : 'Reopen' }}"><i class="fas fa-{{ $t->isOpen() ? 'lock' : 'lock-open' }}"></i></button></form>@endif
                            @if ($mt && !$t->invoices_count)
                                <form method="POST" action="{{ route('billing.terms.destroy', $t) }}" class="d-inline" data-confirm="Delete {{ $t->name }}?">@csrf @method('DELETE')
                                    <button class="bl-btn sm ghost-danger" title="Delete"><i class="fas fa-trash"></i></button></form>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table></div>
        @endif
    </div>

    <div class="bl-alert info"><i class="fas fa-circle-info mt-1"></i><div>
        <strong>How billing works:</strong> schools are charged <em>students × rate per student</em> (unique students — a child enrolled in two programmes counts once).
        Change the term rate here, give a school its own rate or discount under <a href="{{ route('billing.pricing') }}">School Pricing</a>, or edit a single invoice.
        Existing invoices keep the figures they were generated with.</div></div>

    {{-- Create --}}
    <div class="modal fade bl" id="termModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <form class="modal-content" method="POST" action="{{ route('billing.terms.store') }}" style="border-radius:16px;">
                @csrf
                <div class="modal-header"><h5 class="modal-title">New billing term</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>
                <div class="modal-body">
                    @if ($years->isEmpty())
                        <div class="bl-alert warn">No academic years exist yet. @if (Route::has('add-academic-year'))<a href="{{ route('add-academic-year') }}">Add an academic year</a> first.@endif</div>
                    @endif
                    <div class="row">
                        <div class="form-group col-md-7"><label>Academic year</label>
                            <select name="academic_year_id" id="nt_year" class="form-control w-100" required>
                                <option value="">— Select —</option>
                                @foreach ($years as $y)<option value="{{ $y->id }}" @selected((string) old('academic_year_id', \App\Support\ActiveYear::id()) === (string) $y->id)>{{ $y->name }}{{ \App\Support\ActiveYear::matches($y) ? ' (active)' : '' }}</option>@endforeach
                            </select></div>
                        <div class="form-group col-md-5"><label>Term</label>
                            <select name="term" id="nt_term" class="form-control w-100" required>
                                @foreach (\App\Support\Term::options() as $n => $label)<option value="{{ $n }}" @selected(old('term') == $n)>{{ $label }}</option>@endforeach
                            </select></div>
                        <div class="form-group col-md-6"><label>Term starts</label><input type="date" name="start_date" id="nt_start" class="form-control w-100" value="{{ old('start_date') }}"></div>
                        <div class="form-group col-md-6"><label>Term ends</label><input type="date" name="end_date" id="nt_end" class="form-control w-100" value="{{ old('end_date') }}"></div>
                        <div class="form-group col-md-6"><label>Invoices due on</label><input type="date" name="due_date" class="form-control w-100" value="{{ old('due_date') }}">
                            <div class="bl-hint">Leave empty to use {{ $settings->due_days }} days after issue.</div></div>
                        <div class="form-group col-md-6"><label>Rate per student ({{ $settings->currency }})</label>
                            <input type="number" step="1" min="0" name="rate_per_student" class="form-control w-100" required value="{{ old('rate_per_student', (int) $settings->default_rate_per_student) }}"></div>
                        <div class="form-group col-12 mb-0"><label>Notes (internal)</label><input type="text" name="notes" class="form-control w-100" maxlength="1000" value="{{ old('notes') }}"></div>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="bl-btn ghost" data-dismiss="modal">Cancel</button>
                    <button class="bl-btn primary" type="submit">Create term</button></div>
            </form>
        </div>
    </div>

    {{-- Edit --}}
    <div class="modal fade bl" id="editTermModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <form class="modal-content" method="POST" id="editTermForm" style="border-radius:16px;">
                @csrf @method('PUT')
                <div class="modal-header"><h5 class="modal-title" id="editTermTitle">Edit term</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>
                <div class="modal-body">
                    <div class="bl-alert warn" id="editTermWarn" style="display:none;"><i class="fas fa-triangle-exclamation mt-1"></i><div>This term already has invoices. A new rate only affects invoices generated later — edit an invoice to change its own rate.</div></div>
                    <div class="row">
                        <div class="form-group col-md-6"><label>Term starts</label><input type="date" name="start_date" id="et_start" class="form-control w-100"></div>
                        <div class="form-group col-md-6"><label>Term ends</label><input type="date" name="end_date" id="et_end" class="form-control w-100"></div>
                        <div class="form-group col-md-6"><label>Invoices due on</label><input type="date" name="due_date" id="et_due" class="form-control w-100"></div>
                        <div class="form-group col-md-6"><label>Rate per student ({{ $settings->currency }})</label><input type="number" min="0" step="1" name="rate_per_student" id="et_rate" class="form-control w-100" required></div>
                        <div class="form-group col-12 mb-0"><label>Notes (internal)</label><input type="text" name="notes" id="et_notes" class="form-control w-100" maxlength="1000"></div>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="bl-btn ghost" data-dismiss="modal">Cancel</button>
                    <button class="bl-btn primary" type="submit">Save</button></div>
            </form>
        </div>
    </div>
</div>
 </div>
    </div>
</div>
@endsection

@section('js')
    <script>
        $(document).on('click', '.edit-term', function () {
            var d = this.dataset;
            $('#editTermForm').attr('action', d.action);
            $('#editTermTitle').text('Edit ' + d.name);
            $('#et_start').val(d.start); $('#et_end').val(d.end); $('#et_due').val(d.due);
            $('#et_rate').val(d.rate); $('#et_notes').val(d.notes);
            $('#editTermWarn').toggle(parseInt(d.invoices, 10) > 0);
            $('#editTermModal').modal('show');
        });
        // Dates come from the term calendar the administrators set (year + term -> dates).
        var calendar = @json($calendarTerms->mapWithKeys(fn($c) => [$c->academic_year_id . '-' . $c->term => ['start' => $c->start_date->format('Y-m-d'), 'end' => $c->end_date->format('Y-m-d')]]));
        function fillFromCalendar() {
            var c = calendar[$('#nt_year').val() + '-' + $('#nt_term').val()];
            if (c) { $('#nt_start').val(c.start); $('#nt_end').val(c.end); }
        }
        $('#nt_year, #nt_term').on('change', fillFromCalendar);
        $(document).on('click', '.from-calendar', function () {
            $('#nt_year').val(this.dataset.year); $('#nt_term').val(this.dataset.term); fillFromCalendar();
            $('#termModal').modal('show');
        });
        @if ($errors->any() && old('academic_year_id') !== null) $('#termModal').modal('show'); @endif
    </script>
    @include('Billing.partials.reason-modal')
@endsection
