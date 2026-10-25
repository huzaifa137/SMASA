{{-- $id, $terms, $metrics, $repOptions (for the picker), $fixedUser (optional) --}}
<dialog class="st-dlg" id="{{ $id }}">
    <form method="POST" action="{{ route('sales.targets.store') }}">@csrf
        <h5><i class="fas fa-bullseye"></i> Set a target</h5>
        @if(!empty($fixedUser))
            <input type="hidden" name="scope" value="selected"><input type="hidden" name="user_ids[]" value="{{ $fixedUser }}">
        @else
            <label>Who is it for?</label>
            <select name="scope" onchange="this.form.querySelector('.st-pick').style.display=this.value==='selected'?'':'none'">
                <option value="all">Every active representative</option><option value="selected">Selected representatives…</option>
            </select>
            <div class="st-pick" style="display:none"><label>Representatives (hold Ctrl/Cmd to pick several)</label>
                <select name="user_ids[]" multiple size="5">@foreach($repOptions as $uid => $label)<option value="{{ $uid }}">{{ $label }}</option>@endforeach</select></div>
        @endif
        <label>Target</label>
        <select name="metric" required>@foreach($metrics as $k => $m)<option value="{{ $k }}">{{ $m['label'] }}</option>@endforeach</select>
        <label>Value <span class="st-mute">(amount in currency, count, or 0–100 for rate)</span></label>
        <input name="target_value" type="number" step="0.01" min="0.01" required>
        <label>Period</label>
        <select name="period_type" onchange="var c=this.value==='custom';this.form.querySelector('.st-t').style.display=c?'none':'';this.form.querySelector('.st-c').style.display=c?'':'none'">
            <option value="term">A term</option><option value="custom">Custom dates</option></select>
        <div class="st-t"><label>Term</label><select name="academic_term_id">@foreach($terms as $t)<option value="{{ $t->id }}" @selected($t->is_active)>{{ $t->name }}{{ $t->is_active ? ' (current)' : '' }}</option>@endforeach</select></div>
        <div class="st-c" style="display:none"><label>Label</label><input name="period_label" maxlength="80" placeholder="e.g. Back-to-school drive">
            <div class="row2"><div><label>From</label><input type="date" name="period_start"></div><div><label>To</label><input type="date" name="period_end"></div></div></div>
        <label>Note (optional)</label><input name="notes" maxlength="500">
        <div class="foot"><button type="button" class="st-btn" onclick="stClose(this)">Cancel</button><button class="st-btn st-btn--primary">Save target</button></div>
    </form>
</dialog>
