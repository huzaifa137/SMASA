@extends('layouts-side-bar.master')

@section('content')
    @include('Admin.sales-team._styles')
    @php $F = \App\Support\SalesFormat::class; @endphp

    @include('Admin.sales-team._hero', ['title' => 'Transfer schools', 'subtitle' => 'Hand schools — and everything owed on them — to another representative', 'icon' => 'fa-right-left'])
    @include('Admin.sales-team._nav', ['tab' => 'transfers'])

    <div class="st-card">
        <h5>1 · Who is handing over?</h5>
        <form method="GET" class="st-bar" style="margin:0">
            <select name="from" onchange="this.form.submit()" style="min-width:280px">
                <option value="">Choose a representative…</option>
                @foreach($fromOptions as $uid => $label)<option value="{{ $uid }}" @selected($from === $uid)>{{ $label }}</option>@endforeach
            </select>
        </form>
    </div>

    @if($from)
    <form method="POST" action="{{ route('sales.transfers.store') }}" id="st-transfer" onsubmit="return confirm('Transfer the selected schools now? This is recorded in the audit trail.')">@csrf
        <input type="hidden" name="from_user_id" value="{{ $from }}">
        <div class="st-card">
            <h5>2 · Which schools? <small>{{ $schools->count() }} owned</small></h5>
            @if($schools->isEmpty())
                <div class="st-empty"><i class="fas fa-school"></i>This representative does not own any schools.</div>
            @else
                <div class="st-bar">
                    <label style="margin:0"><input type="radio" name="mode" value="all" checked onchange="stSum()"> All {{ $schools->count() }} schools</label>
                    <label style="margin:0"><input type="radio" name="mode" value="selected" onchange="stSum()"> Only the ones I tick</label>
                </div>
                <div class="st-scroll" style="max-height:340px;overflow:auto"><table class="st-table">
                    <thead><tr><th><input type="checkbox" id="st-all" onclick="document.querySelectorAll('.st-sc').forEach(c=>c.checked=this.checked);document.querySelector('[name=mode][value=selected]').checked=true;stSum()"></th><th>School</th><th>Status</th><th class="num">Students</th><th class="num">Outstanding</th><th class="num">Overdue</th></tr></thead>
                    <tbody>@foreach($schools as $s)
                        <tr><td><input type="checkbox" class="st-sc" name="school_ids[]" value="{{ $s['id'] }}" data-st="{{ $s['students'] }}" data-out="{{ $s['outstanding'] }}" data-od="{{ $s['overdue'] }}" @checked(in_array($s['id'], $preselect)) onchange="document.querySelector('[name=mode][value=selected]').checked=true;stSum()"></td>
                            <td><b>{{ $s['name'] }}</b></td><td><span class="st-pill {{ $s['status_tone'] }}">{{ $s['status_label'] }}</span></td>
                            <td class="num">{{ $s['students'] }}</td><td class="num">{{ $F::money($s['outstanding'], '') }}</td><td class="num">{{ $F::money($s['overdue'], '') }}</td></tr>
                    @endforeach</tbody>
                </table></div>
                <div class="st-note" style="margin-top:.8rem" id="st-summary"></div>
            @endif
        </div>

        @if($schools->isNotEmpty())
        <div class="st-card">
            <h5>3 · Who takes over?</h5>
            <div class="st-bar"><select name="to" required style="min-width:280px">
                <option value="">Choose…</option>
                @foreach($toOptions as $uid => $label)@if($uid !== $from)<option value="{{ $uid }}">{{ $label }}</option>@endif @endforeach
                <option value="pool">Nobody — release to the Unassigned pool</option></select></div>
            <div class="row2" style="display:grid;grid-template-columns:1fr 2fr;gap:.8rem;margin-top:.5rem">
                <select name="reason" class="st-in" required><option value="">Reason…</option>@foreach($reasons as $k => $l)<option value="{{ $k }}" @selected(old('reason') === $k)>{{ $l }}</option>@endforeach</select>
                <input name="notes" class="st-in" maxlength="2000" placeholder="Note (required when the reason is “Other”)" value="{{ old('notes') }}">
            </div>
            <div class="st-note" style="margin-top:1rem">The new owner becomes responsible for these schools from now: students, invoices, outstanding and overdue balances and follow-up.
                Cash the previous owner already collected, and their enrolment credit, stay with them.</div>
            <div style="margin-top:1rem;display:flex;gap:.6rem"><button class="st-btn st-btn--primary"><i class="fas fa-right-left"></i> Transfer schools</button><a class="st-btn" href="{{ route('sales.transfers') }}">Cancel</a></div>
        </div>
        @endif
    </form>
    <script>
        var CUR = @js($cur);
        function stSum(){
            var all=document.querySelector('[name=mode][value=all]');if(!all)return;
            var boxes=[].slice.call(document.querySelectorAll('.st-sc')), use=all.checked?boxes:boxes.filter(function(b){return b.checked});
            var s=0,o=0,d=0;use.forEach(function(b){s+=+b.dataset.st;o+=+b.dataset.out;d+=+b.dataset.od});
            document.getElementById('st-summary').innerHTML='<b>'+use.length+'</b> school(s) · <b>'+s.toLocaleString()+'</b> students · <b>'+CUR+' '+o.toLocaleString()+'</b> outstanding of which <b>'+CUR+' '+d.toLocaleString()+'</b> overdue will be handed over.';
        }
        stSum();
    </script>
    @endif
@endsection
