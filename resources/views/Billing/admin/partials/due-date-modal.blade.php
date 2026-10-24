{{-- "Change due date" dialog, shared by the invoice list and the invoice page.
     Any button with class .due-btn opens it (see due-date-js.blade.php for the data-* it needs). --}}
<div class="modal fade bl" id="dueModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <form class="modal-content" id="dueForm" method="POST" action="" style="border-radius:16px;" data-today="{{ today()->format('Y-m-d') }}">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-calendar-plus mr-1"></i> Change due date</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="mb-3" style="padding:.7rem .9rem;border-radius:12px;background:var(--bl-bg);">
                    <div style="font-weight:700;" id="dueInv">—</div>
                    <div class="bl-muted" id="dueSchool"></div>
                    <div style="font-size:.84rem;margin-top:.35rem;">Current due date: <strong id="dueCurrent">not set</strong></div>
                </div>

                <div class="form-group">
                    <label for="dueDate">New due date</label>
                    <input type="date" name="due_date" id="dueDate" class="form-control w-100" required>
                    <div class="bl-hint" id="dueHelp"></div>
                </div>

                <div class="form-group">
                    <label>Quick extend <span class="bl-muted" style="font-weight:500;">(from today or the current due date, whichever is later)</span></label>
                    <div class="d-flex flex-wrap" style="gap:.4rem;">
                        <button type="button" class="bl-btn sm ghost due-chip" data-days="7">+ 7 days</button>
                        <button type="button" class="bl-btn sm ghost due-chip" data-days="14">+ 14 days</button>
                        <button type="button" class="bl-btn sm ghost due-chip" data-days="30">+ 30 days</button>
                        <button type="button" class="bl-btn sm ghost due-chip" data-days="60">+ 60 days</button>
                    </div>
                </div>

                <div class="form-group mb-0">
                    <label for="dueReason">Reason <span class="bl-muted" style="font-weight:500;">(optional · kept in the invoice history)</span></label>
                    <input type="text" name="reason" id="dueReason" class="form-control w-100" maxlength="255" autocomplete="off" placeholder="e.g. School asked for more time — promised to pay by month end">
                </div>
                <div class="bl-alert error mt-3 mb-0" id="dueError" style="display:none;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="bl-btn ghost" data-dismiss="modal">Cancel</button>
                <button type="submit" class="bl-btn primary" id="dueSave"><i class="fas fa-check"></i> Save due date</button>
            </div>
        </form>
    </div>
</div>
