{{-- Script for due-date-modal. Goes in @section('js') (needs jQuery).
     A .due-btn needs: data-url, data-number, data-school, data-due (Y-m-d or empty), data-issue (Y-m-d or empty).
     Add data-reload="1" to reload the page after saving; otherwise the table row is updated in place
     (cells .due-label, .late-note and .status-pill inside the same <tr>). --}}
<script>
    (function () {
        var $m = $('#dueModal'), $f = $('#dueForm'), cur = null;
        var today = $f.data('today');

        function fmt(d) { // local Y-m-d (never toISOString: that shifts the day in UTC+3)
            var m = d.getMonth() + 1, day = d.getDate();
            return d.getFullYear() + '-' + (m < 10 ? '0' : '') + m + '-' + (day < 10 ? '0' : '') + day;
        }
        function parse(s) { var p = s.split('-'); return new Date(+p[0], +p[1] - 1, +p[2]); }
        function nice(s) {
            return s ? parse(s).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' }) : 'not set';
        }
        function toast(msg, ok) {
            if (window.Swal) {
                Swal.fire({ toast: true, position: 'top-end', icon: ok ? 'success' : 'error', title: msg, showConfirmButton: false, timer: 3200, timerProgressBar: true });
            } else { alert(msg); }
        }

        $(document).on('click', '.due-btn', function () {
            cur = this;
            var d = this.dataset;
            $f.attr('action', d.url);
            $('#dueInv').text(d.number);
            $('#dueSchool').text(d.school || '');
            $('#dueCurrent').text(nice(d.due));
            $('#dueDate').val(d.due || today).attr('min', d.issue || '');
            $('#dueReason').val('');
            $('#dueError').hide().text('');
            $('#dueHelp').text(d.issue ? 'Cannot be earlier than the invoice date (' + nice(d.issue) + ').' : '');
            $m.modal('show');
        });

        $(document).on('click', '.due-chip', function () {
            var base = parse(today), due = cur && cur.dataset.due ? parse(cur.dataset.due) : null;
            if (due && due > base) base = due;
            base.setDate(base.getDate() + parseInt(this.dataset.days, 10));
            $('#dueDate').val(fmt(base));
        });

        $f.on('submit', function (e) {
            e.preventDefault();
            var $btn = $('#dueSave').prop('disabled', true);
            $('#dueError').hide();
            $.ajax({
                url: $f.attr('action'), method: 'POST', dataType: 'json', data: $f.serialize(),
                headers: { 'Accept': 'application/json' }
            }).done(function (r) {
                $m.modal('hide');
                if (cur && cur.dataset.reload) { window.location.reload(); return; }
                if (cur) {
                    cur.dataset.due = r.due_iso;
                    var $tr = $(cur).closest('tr');
                    $tr.find('.due-label').text(r.due_label);
                    $tr.find('.status-pill').attr('class', 'bl-pill status-pill ' + r.status).text(r.status_label);
                    $tr.find('.late-note').text(r.overdue ? r.days_late + 'd late' : '');
                    $tr.find('.due-cell').css('background', '#ecfdf5');
                    setTimeout(function () { $tr.find('.due-cell').css('background', ''); }, 1600);
                }
                toast(r.message, true);
            }).fail(function (x) {
                var msg = 'Could not save the due date.';
                if (x.responseJSON) {
                    msg = x.responseJSON.message || msg;
                    if (x.responseJSON.errors) { msg = Object.values(x.responseJSON.errors)[0][0]; }
                }
                $('#dueError').text(msg).show();
            }).always(function () { $btn.prop('disabled', false); });
        });
    })();
</script>
