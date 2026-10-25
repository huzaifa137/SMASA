@extends('layouts-side-bar.master')

@section('content')
    @include('Admin.sales-team._styles')
    @php $F = \App\Support\SalesFormat::class; @endphp

    @include('Admin.sales-team._hero', [
        'title' => 'School Transfers',
        'subtitle' => 'Every hand-over of schools between representatives, with the debt handed over',
        'icon' => 'fa-right-left',
        'actions' => ($canTransfer ? '<a class="st-btn st-btn--primary" href="' . route('sales.transfers.create') . '"><i class="fas fa-right-left"></i> New transfer</a>' : '')
            . ($canExport ? '<a class="st-btn" href="' . route('sales.export', ['type' => 'transfers']) . '"><i class="fas fa-file-csv"></i> Export</a>' : ''),
    ])
    @include('Admin.sales-team._nav', ['tab' => 'transfers'])

    <div class="st-bar"><form method="GET"><select name="rep" onchange="this.form.submit()"><option value="">All representatives</option>
        @foreach($repOptions as $uid => $label)<option value="{{ $uid }}" @selected((string) $rep === (string) $uid)>{{ $label }}</option>@endforeach</select></form></div>

    <div class="st-card">
        @if($batches->isEmpty())
            <div class="st-empty"><i class="fas fa-right-left"></i>No transfers yet.</div>
        @else
            <div class="st-scroll"><table class="st-table">
                <thead><tr><th>Reference</th><th>Date</th><th>From</th><th>To</th><th class="num">Schools</th><th class="num">Students</th><th class="num">Outstanding handed over</th><th>Reason</th><th>By</th></tr></thead>
                <tbody>
                @foreach($batches as $b)
                    <tr>
                        <td><a href="{{ route('sales.transfers.show', $b->id) }}"><b>{{ $b->reference }}</b></a></td>
                        <td>{{ $b->created_at->format('j M Y H:i') }}</td>
                        <td>{{ $names[$b->from_user_id] ?? '—' }}</td>
                        <td>{{ $b->to_user_id ? ($names[$b->to_user_id] ?? '—') : 'Unassigned pool' }}</td>
                        <td class="num">{{ $b->school_count }}</td><td class="num">{{ number_format($b->student_count) }}</td>
                        <td class="num">{{ $F::money($b->outstanding_amount, '') }}</td>
                        <td>{{ $b->reason_label }}</td><td class="st-mute">{{ $names[$b->created_by] ?? '—' }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table></div>
            <div style="margin-top:1rem">{{ $batches->links() }}</div>
        @endif
    </div>
@endsection
