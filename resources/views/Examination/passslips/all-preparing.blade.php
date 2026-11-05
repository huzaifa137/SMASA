<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Preparing report cards…</title>
    <style>
        body { font-family: system-ui, -apple-system, 'Segoe UI', sans-serif; background: #f1f5f9; margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .box { background: #fff; border-radius: 16px; padding: 2rem 2.4rem; max-width: 420px; text-align: center; box-shadow: 0 4px 24px rgba(0,0,0,.08); }
        .spin { width: 44px; height: 44px; border: 4px solid #e2e8f0; border-top-color: #2f2ccb; border-radius: 50%; margin: 0 auto 1rem; animation: s 1s linear infinite; }
        @keyframes s { to { transform: rotate(360deg); } }
        h2 { margin: 0 0 .4rem; font-size: 1.15rem; color: #0f172a; }
        p { margin: 0; color: #64748b; font-size: .9rem; }
        .err { color: #b91c1c; }
    </style>
</head>
<body>
<div class="box">
    <div class="spin" id="spin"></div>
    <h2 id="title">Preparing the report cards…</h2>
    <p id="msg">A whole school takes a little while. This page opens the report cards by itself when they are ready.</p>
</div>
<script>
    (function () {
        var statusUrl = @json(route('passslips.all.status', [$examId, $token]));
        var fileUrl = @json(route('passslips.all.file', [$examId, $token]));
        var tries = 0;

        function poll() {
            fetch(statusUrl, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
                .then(function (r) { return r.json(); })
                .then(function (d) {
                    if (d.status === 'done') { window.location.replace(fileUrl); return; }
                    if (d.status === 'failed') { fail(d.error); return; }
                    schedule();
                })
                .catch(schedule);
        }

        function schedule() {
            tries++;
            if (tries > 400) { fail('This is taking too long. Please try again.'); return; }
            setTimeout(poll, tries < 10 ? 2000 : 4000);
        }

        function fail(why) {
            document.getElementById('spin').style.display = 'none';
            document.getElementById('title').textContent = 'Could not prepare the report cards';
            var m = document.getElementById('msg'); m.className = 'err'; m.textContent = why || 'Please try again.';
        }

        poll();
    })();
</script>
</body>
</html>
