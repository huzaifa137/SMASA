{{-- One shared "give a reason" dialog for cancel / write-off / reverse. Buttons use
     data-reason-url, data-reason-title, data-reason-btn and optional data-reason-text. --}}
<div class="modal fade bl" id="reasonModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <form class="modal-content" method="POST" id="reasonForm" style="border-radius:16px;">
            @csrf
            <div class="modal-header"><h5 class="modal-title" id="reasonTitle">Reason</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span>&times;</span></button></div>
            <div class="modal-body">
                <p class="bl-hint" id="reasonText" style="font-size:.86rem;margin-bottom:.8rem;"></p>
                <div class="form-group mb-0"><label>Reason (recorded in the audit log)</label>
                    <input type="text" name="reason" class="form-control w-100" maxlength="255" required autocomplete="off"></div>
            </div>
            <div class="modal-footer"><button type="button" class="bl-btn ghost" data-dismiss="modal">Back</button>
                <button type="submit" class="bl-btn danger" id="reasonBtn">Confirm</button></div>
        </form>
    </div>
</div>
<script>
    document.addEventListener('click', function (e) {
        var b = e.target.closest('[data-reason-url]');
        if (!b) return;
        e.preventDefault();
        document.getElementById('reasonForm').action = b.dataset.reasonUrl;
        document.getElementById('reasonTitle').textContent = b.dataset.reasonTitle || 'Reason';
        document.getElementById('reasonText').textContent = b.dataset.reasonText || '';
        document.getElementById('reasonBtn').textContent = b.dataset.reasonBtn || 'Confirm';
        document.querySelector('#reasonForm [name=reason]').value = '';
        $('#reasonModal').modal('show');
    });
    // <form data-confirm="Are you sure?"> asks first
    document.addEventListener('submit', function (e) {
        var f = e.target; if (!f.dataset || !f.dataset.confirm || f.dataset.ok) return;
        e.preventDefault();
        var go = function () { f.dataset.ok = 1; f.submit(); };
        if (window.Swal) {
            Swal.fire({ title: f.dataset.confirm, text: f.dataset.confirmText || '', icon: 'question', showCancelButton: true, confirmButtonText: f.dataset.confirmBtn || 'Yes, continue', confirmButtonColor: '#2f2ccb' })
                .then(function (r) { if (r.isConfirmed) go(); });
        } else if (confirm(f.dataset.confirm)) go();
    });
</script>
