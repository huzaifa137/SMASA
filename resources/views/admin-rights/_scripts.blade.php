<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const ARP_CSRF = document.querySelector('meta[name="csrf-token"]').content;

/** JSON request helper. Resolves {ok, status, data}. */
async function arpRequest(url, method = 'GET', body = null) {
    const res = await fetch(url, {
        method,
        credentials: 'same-origin',
        headers: {
            'X-CSRF-TOKEN': ARP_CSRF,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            ...(body ? { 'Content-Type': 'application/json' } : {}),
        },
        body: body ? JSON.stringify(body) : null,
    });
    let data = {};
    try { data = await res.json(); } catch (e) {}
    return { ok: res.ok && data.success !== false, status: res.status, data };
}

function arpErrorText(r) {
    if (r.data && r.data.errors) return Object.values(r.data.errors).flat().join('\n');
    return (r.data && r.data.message) || 'Something went wrong (HTTP ' + r.status + ').';
}

async function arpAct(url, method, body, okTitle) {
    const r = await arpRequest(url, method, body);
    if (!r.ok) { await Swal.fire({ icon: 'error', title: 'Not done', text: arpErrorText(r) }); return r; }
    return r;
}

function arpEsc(v) { const d = document.createElement('div'); d.textContent = v ?? ''; return d.innerHTML; }

/** Shows a one-time temporary password. */
function arpShowPassword(pw, title) {
    return Swal.fire({
        icon: 'success', title: title || 'Done',
        html: '<p class="arp-muted">Give this temporary password to the user. It is shown only once.</p>' +
              '<code style="font-size:18px;user-select:all">' + arpEsc(pw) + '</code>',
    });
}
</script>
