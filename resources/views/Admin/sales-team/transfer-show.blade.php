@extends('layouts-side-bar.master')

@section('content')
    @include('Admin.sales-team._styles')
    @php $F = \App\Support\SalesFormat::class; @endphp
    @include('Admin.sales-team._hero', ['title' => $batch->reference, 'subtitle' => $batch->created_at->format('j M Y H:i') . ' · by ' . ($names[$batch->created_by] ?? 'system'), 'icon' => 'fa-right-left',
        'actions' => '<a class="st-btn" href="' . route('sales.transfers') . '"><i class="fas fa-arrow-left"></i> All transfers</a>'])
    @include('Admin.sales-team._nav', ['tab' => 'transfers'])
    <div class="st-kpis">
        <div class="st-kpi"><small>From</small><b style="font-size:1.1rem">{{ $names[$batch->from_user_id] ?? '—' }}</b></div>
        <div class="st-kpi"><small>To</small><b style="font-size:1.1rem">{{ $batch->to_user_id ? ($names[$batch->to_user_id] ?? '—') : 'Unassigned pool' }}</b></div>
        <div class="st-kpi"><small>Schools · students</small><b>{{ $batch->school_count }} · {{ number_format($batch->student_count) }}</b></div>
        <div class="st-kpi {{ $batch->overdue_amount > 0 ? 'st-kpi--warn' : '' }}"><small>Debt handed over</small><b>{{ $F::short($batch->outstanding_amount) }}</b><span>{{ $F::money($batch->overdue_amount, $cur) }} overdue</span></div>
    </div>
    <div class="st-card"><h5>Reason</h5><b>{{ $batch->reason_label }}</b>@if($batch->notes)<p style="margin:.5rem 0 0">{{ $batch->notes }}</p>@endif</div>
    <div class="st-card"><h5>Schools in this transfer</h5>
        @forelse($schools as $s)<div class="st-tg"><div><b>{{ $s->name }}</b> <span class="st-mute">{{ $s->registration_code }}</span></div></div>
        @empty<div class="st-empty">School list unavailable.</div>@endforelse
    </div>
@endsection
