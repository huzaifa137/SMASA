@extends('layouts-side-bar.master')

@section('content')
    @include('Admin.sales-team._styles')
    @php $F = \App\Support\SalesFormat::class; @endphp

    @include('Admin.sales-team._hero', [
        'title' => 'Targets',
        'subtitle' => 'What each representative is expected to achieve — and how they are pacing',
        'icon' => 'fa-bullseye',
        'actions' => ($canSet ? '<button type="button" class="st-btn st-btn--primary" onclick="stOpen(\'dlg-target\')"><i class="fas fa-plus"></i> Set target</button>' : '')
            . ($canExport ? '<a class="st-btn" href="' . route('sales.export', ['type' => 'targets'] + $period->query()) . '"><i class="fas fa-file-csv"></i> Export</a>' : ''),
    ])
    @include('Admin.sales-team._nav', ['tab' => 'targets'])
    <div class="st-bar">@include('Admin.sales-team._period', ['extra' => []])</div>
    <div class="st-note" style="margin-bottom:1rem">Showing targets whose dates overlap {{ $period->label }}. Each target is measured over its own dates.</div>

    @forelse($evaluated as $uid => $items)
        <div class="st-card">
            <h5><a href="{{ route('sales.show', $uid) }}">{{ \App\Models\SalesRepProfile::displayName($names->get($uid)) }}</a></h5>
            <div class="st-scroll"><table class="st-table">
                <thead><tr><th>Target</th><th>Period</th><th class="num">Goal</th><th class="num">Actual</th><th style="min-width:160px">Progress</th><th>Status</th><th></th></tr></thead>
                <tbody>
                @foreach($items as $i)
                    @php $t = $i['target']; $e = $i['eval']; @endphp
                    <tr>
                        <td><b>{{ $metrics[$t->metric]['label'] ?? $t->metric }}</b></td>
                        <td>{{ $t->period_label }}<br><small class="st-mute">{{ $t->period_start->format('j M') }} – {{ $t->period_end->format('j M Y') }}</small></td>
                        <td class="num">{{ $F::value($t->metric, $t->target_value, $cur) }}</td>
                        <td class="num">{{ $F::value($t->metric, $i['actual'], $cur) }}</td>
                        <td><div style="display:flex;gap:.5rem;align-items:center"><div class="st-prog {{ $e['status'] }}" style="flex:1"><i style="width:{{ min(100, $e['pct']) }}%"></i></div><b>{{ $e['pct'] }}%</b></div></td>
                        <td><span class="st-pill {{ $e['status'] }}">{{ $e['label'] }}</span></td>
                        <td class="num">
                            @if($canSet)
                                <button type="button" class="st-btn st-btn--sm" onclick="stEdit({{ $t->id }}, {{ $t->target_value }}, @js($t->notes ?? ''))">Edit</button>
                                <form method="POST" action="{{ route('sales.targets.destroy', $t->id) }}" style="display:inline" onsubmit="return confirm('Delete this target?')">@csrf @method('DELETE')<button class="st-btn st-btn--sm st-btn--danger">Delete</button></form>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table></div>
        </div>
    @empty
        <div class="st-card"><div class="st-empty"><i class="fas fa-bullseye"></i>No targets for this period yet.@if($canSet)<br><a href="#" onclick="stOpen('dlg-target');return false">Set targets for the team</a>@endif</div></div>
    @endforelse

    @if($canSet)
        @include('Admin.sales-team._target-dialog', ['id' => 'dlg-target', 'terms' => $terms, 'metrics' => $metrics, 'repOptions' => $repOptions])
        <dialog class="st-dlg" id="dlg-edit-target">
            <form method="POST" id="st-edit-form">@csrf @method('PUT')
                <h5>Edit target</h5>
                <label>Value</label><input name="target_value" id="st-edit-value" type="number" step="0.01" min="0.01" required>
                <label>Note</label><input name="notes" id="st-edit-notes" maxlength="500">
                <div class="foot"><button type="button" class="st-btn" onclick="stClose(this)">Cancel</button><button class="st-btn st-btn--primary">Save</button></div>
            </form>
        </dialog>
        <script>
            function stEdit(id, v, n){var f=document.getElementById('st-edit-form');f.action='{{ url('/admin/sales-team/targets') }}/'+id;document.getElementById('st-edit-value').value=v;document.getElementById('st-edit-notes').value=n;stOpen('dlg-edit-target');}
        </script>
    @endif
@endsection
