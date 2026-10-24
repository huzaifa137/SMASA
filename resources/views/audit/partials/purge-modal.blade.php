{{-- Audit purge: alerts + modal + script. Self-contained: needs no variables from the parent view. --}}
@php
    $auMin = max(1, (int) config('audit.purge_min_days', 90));
    $auBag = $errors->getBag('auditPurge');
    $auResult = session('audit_purge_result');
@endphp

@if($auResult)
    <div class="alert {{ $auResult['error'] ? 'alert-danger' : 'alert-success' }}">
        <strong>Purge:</strong> {{ number_format($auResult['deleted']) }} entries deleted.
        @if($auResult['remaining'])
            {{ number_format($auResult['remaining']) }} are still pending (time limit reached) - open "Purge old entries" and run it again.
        @endif
        @if($auResult['error'])
            Stopped early: {{ $auResult['error'] }}
        @endif
        This purge has been recorded in the trail.
    </div>
@endif

<div class="modal fade" id="auPurgeModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form method="POST" action="{{ route('audit.purge') }}" class="modal-content" id="auPurgeForm" autocomplete="off">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title text-white"><i class="fas fa-exclamation-triangle mr-1"></i>Purge old audit entries</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>

            <div class="modal-body">
                <p class="small text-muted mb-3">
                    Individual entries can never be deleted. This permanently removes <strong>all entries older than the
                    chosen age</strong> (the last {{ $auMin }} days are always kept). It cannot be undone, and the purge
                    itself is recorded in the trail.
                </p>

                @if($auBag->any())
                    <div class="alert alert-danger py-2 small">
                        @foreach($auBag->all() as $m) <div>{{ $m }}</div> @endforeach
                    </div>
                @endif

                <div class="form-group">
                    <label for="auPurgeDays">Delete entries older than (days)</label>
                    <input type="number" class="form-control" id="auPurgeDays" name="days"
                           min="{{ $auMin }}" max="3650" value="{{ old('days', max($auMin, 365)) }}" required>
                </div>

                <div id="auPurgePreview" class="border rounded p-2 small mb-2 bg-light">Loading preview...</div>

                <a id="auPurgeExport" href="#" class="btn btn-sm btn-outline-primary mb-3">
                    <i class="fas fa-file-csv mr-1"></i>Step 1: Download CSV archive (required)
                </a>

                <div class="form-group">
                    <label for="auPurgeReason">Reason</label>
                    <textarea class="form-control" id="auPurgeReason" name="reason" rows="2" maxlength="255" required>{{ old('reason') }}</textarea>
                </div>

                <div class="form-group">
                    <label for="auPurgeConfirm">Type <strong>PURGE</strong> to confirm</label>
                    <input type="text" class="form-control" id="auPurgeConfirm" name="confirm" required>
                </div>

                <div class="form-group mb-0">
                    <label for="auPurgePassword">Your password</label>
                    <input type="password" class="form-control" id="auPurgePassword" name="password" autocomplete="current-password" required>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-danger" id="auPurgeSubmit" disabled>
                    <i class="fas fa-trash-alt mr-1"></i>Purge permanently
                </button>
            </div>
        </form>
    </div>
</div>

<script>
(function () {
    'use strict';

    var modal = document.getElementById('auPurgeModal');
    var days = document.getElementById('auPurgeDays');
    var box = document.getElementById('auPurgePreview');
    var exp = document.getElementById('auPurgeExport');
    var reason = document.getElementById('auPurgeReason');
    var confirmEl = document.getElementById('auPurgeConfirm');
    var pass = document.getElementById('auPurgePassword');
    var submit = document.getElementById('auPurgeSubmit');
    var openBtn = document.getElementById('auPurgeOpen');
    var previewUrl = @json(route('audit.purge.preview'));
    var exportUrl = @json(route('audit.purge.export'));
    var min = {{ $auMin }};
    var count = 0, exported = false, timer = null;

    // Bootstrap modals misbehave inside transformed containers: move to <body>.
    document.body.appendChild(modal);

    function esc(s) {
        return String(s).replace(/[&<>"']/g, function (c) {
            return {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'}[c];
        });
    }

    function validDays() {
        var v = parseInt(days.value, 10);
        return !isNaN(v) && v >= min && v <= 3650 ? v : null;
    }

    function gate() {
        submit.disabled = !(count > 0 && exported && validDays() !== null
            && reason.value.trim().length >= 10 && confirmEl.value === 'PURGE' && pass.value.length > 0);
    }

    function refresh() {
        exported = false;
        count = 0;
        gate();

        var v = validDays();
        if (v === null) {
            box.textContent = 'Enter between ' + min + ' and 3650 days.';
            return;
        }

        exp.href = exportUrl + '?days=' + v;
        box.textContent = 'Loading preview...';

        fetch(previewUrl + '?days=' + v, {
            headers: {'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest'},
            credentials: 'same-origin'
        })
            .then(function (r) { if (!r.ok) { throw new Error(); } return r.json(); })
            .then(function (d) {
                count = d.count;
                box.innerHTML = count
                    ? '<strong>' + esc(d.count.toLocaleString()) + '</strong> entries before ' + esc(d.cutoff)
                        + '<br>Oldest: ' + esc(d.oldest) + ' &middot; Newest: ' + esc(d.newest)
                    : 'Nothing to purge before ' + esc(d.cutoff) + '.';
                gate();
            })
            .catch(function () { box.textContent = 'Preview failed. Reload the page and try again.'; });
    }

    days.addEventListener('input', function () { clearTimeout(timer); timer = setTimeout(refresh, 400); });
    exp.addEventListener('click', function () { if (count > 0) { exported = true; gate(); } });
    [reason, confirmEl, pass].forEach(function (el) { el.addEventListener('input', gate); });
    if (openBtn) { openBtn.addEventListener('click', refresh); }

    @if($auBag->any())
        window.addEventListener('load', function () {
            if (window.jQuery) { window.jQuery(modal).modal('show'); refresh(); }
        });
    @endif
})();
</script>
