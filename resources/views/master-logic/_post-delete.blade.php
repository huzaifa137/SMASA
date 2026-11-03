{{--
    Master-data deletes are DELETE requests, never GET links, so a button has to
    submit a real form (with the CSRF token) instead of navigating to a URL.

    Include on any page whose delete button needs it:
        @include('master-logic._post-delete')                        helper + refusal message
        @include('master-logic._post-delete', ['flashError' => false])  helper only (page already shows session('error'))
--}}
<script>
    function smasaPostDelete(url) {
        var form = document.createElement('form');
        form.method = 'POST';
        form.action = url;
        form.style.display = 'none';

        [['_token', @json(csrf_token())], ['_method', 'DELETE']].forEach(function (pair) {
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = pair[0];
            input.value = pair[1];
            form.appendChild(input);
        });

        document.body.appendChild(form);
        form.submit();
    }
</script>

@if (($flashError ?? true) && session('error'))
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            Swal.fire({
                icon: 'error',
                title: 'Cannot delete',
                text: @json(session('error')),
                confirmButtonColor: '#2c29ca'
            });
        });
    </script>
@endif
