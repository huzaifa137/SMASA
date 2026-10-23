@extends('layouts-side-bar.master')
@section('export_title', 'Billing Settings')
@section('css') @include('Billing.partials.styles') @endsection
@section('page-header')
    <div class="bl"><div class="bl-hero"><div style="position:relative;z-index:1;"><span class="bl-badge"><i class="fas fa-gear"></i> Settings</span>
        <h1>Billing Settings</h1><p>Default price, tax, due days and what appears on every invoice.</p></div></div></div>
@endsection
@section('content')
<div class="bl">
    @include('Billing.partials.flash') @include('Billing.partials.admin-nav')
    <form method="POST" action="{{ route('billing.settings.save') }}">@csrf
    <div class="bl-grid2">
        <div class="bl-card"><div class="bl-card-h"><h3>Pricing &amp; tax</h3></div><div class="bl-card-b"><div class="row">
            <div class="form-group col-md-6"><label>Default rate per student ({{ $settings->currency }})</label><input type="number" min="0" name="default_rate_per_student" value="{{ old('default_rate_per_student', (float) $settings->default_rate_per_student) }}" class="form-control w-100" required><div class="bl-hint">Used as the starting rate for new terms.</div></div>
            <div class="form-group col-md-6"><label>Invoice due after (days)</label><input type="number" min="0" max="365" name="due_days" value="{{ old('due_days', $settings->due_days) }}" class="form-control w-100" required></div>
            <div class="col-12 mb-2"><div class="form-check"><input type="checkbox" class="form-check-input" id="te" name="tax_enabled" value="1" @checked(old('tax_enabled', $settings->tax_enabled))><label for="te" class="form-check-label">Charge tax on invoices</label></div></div>
            <div class="form-group col-md-6"><label>Tax name</label><input name="tax_name" value="{{ old('tax_name', $settings->tax_name) }}" class="form-control w-100" required></div>
            <div class="form-group col-md-6"><label>Tax rate (%)</label><input type="number" step="0.01" min="0" max="100" name="tax_rate" value="{{ old('tax_rate', (float) $settings->tax_rate) }}" class="form-control w-100" required></div>
            <div class="form-group col-md-6 mb-0"><label>Invoice number prefix</label><input name="invoice_prefix" value="{{ old('invoice_prefix', $settings->invoice_prefix) }}" class="form-control w-100" required><div class="bl-hint">e.g. INV-2026-T1-0001</div></div></div></div></div>
        <div class="bl-card"><div class="bl-card-h"><h3>Company on the invoice</h3></div><div class="bl-card-b"><div class="row">
            <div class="form-group col-md-6"><label>Company name</label><input name="company_name" value="{{ old('company_name', $settings->company_name) }}" class="form-control w-100" required></div>
            <div class="form-group col-md-6"><label>Tagline</label><input name="company_tagline" value="{{ old('company_tagline', $settings->company_tagline) }}" class="form-control w-100"></div>
            <div class="form-group col-12"><label>Address</label><input name="company_address" value="{{ old('company_address', $settings->company_address) }}" class="form-control w-100"></div>
            <div class="form-group col-md-6"><label>Phone</label><input name="company_phone" value="{{ old('company_phone', $settings->company_phone) }}" class="form-control w-100"></div>
            <div class="form-group col-md-6"><label>Email</label><input type="email" name="company_email" value="{{ old('company_email', $settings->company_email) }}" class="form-control w-100"></div>
            <div class="form-group col-md-6 mb-0"><label>Website</label><input name="company_website" value="{{ old('company_website', $settings->company_website) }}" class="form-control w-100"></div>
            <div class="form-group col-md-6 mb-0"><label>TIN</label><input name="company_tin" value="{{ old('company_tin', $settings->company_tin) }}" class="form-control w-100"></div></div></div></div>
    </div>
    <div class="bl-card"><div class="bl-card-h"><h3>Payment instructions &amp; terms</h3></div><div class="bl-card-b"><div class="row">
        <div class="form-group col-md-6"><label>How to pay (bank, Mobile Money…)</label><textarea name="payment_instructions" rows="6" class="form-control w-100">{{ old('payment_instructions', $settings->payment_instructions) }}</textarea></div>
        <div class="form-group col-md-6"><label>Terms &amp; notes on every invoice</label><textarea name="invoice_notes" rows="6" class="form-control w-100">{{ old('invoice_notes', $settings->invoice_notes) }}</textarea></div></div>
        <div class="bl-alert warn mt-2 mb-0"><i class="fas fa-triangle-exclamation mt-1"></i><div>Tax and company details are copied onto an invoice when it is generated. Changing them does not alter existing invoices.</div></div></div></div>
    <button class="bl-btn primary"><i class="fas fa-floppy-disk"></i> Save settings</button>
    </form>
</div>
@endsection
@section('js') @include('Billing.partials.reason-modal') @endsection
