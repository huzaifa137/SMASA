@extends('layouts-side-bar.master')
@section('export_title', $invoice->invoice_number)
@section('css') @include('Billing.partials.styles')<style>.inv-wrap{background:#fff;border:1px solid var(--bl-border);border-radius:16px;box-shadow:var(--bl-shadow);overflow:hidden;max-width:820px;margin:0 auto}</style>@endsection
@section('page-header')
    <div class="bl"><div class="bl-hero"><div style="position:relative;z-index:1;"><span class="bl-badge">{{ $invoice->term->name ?? '' }}</span><h1>{{ $invoice->invoice_number }}</h1><p>{{ $invoice->status_label }}</p></div>
        <div class="bl-hero-actions"><a class="bl-btn light" href="{{ route('school.billing.index') }}"><i class="fas fa-arrow-left"></i> All invoices</a>
            <a class="bl-btn light" target="_blank" href="{{ route('school.billing.pdf', $invoice->id) }}"><i class="fas fa-file-pdf"></i> Download PDF</a></div></div></div>
@endsection
@section('content')
<div class="bl">@include('Billing.partials.flash')<div class="inv-wrap">@include('Billing.partials.invoice-document')</div></div>
@endsection
