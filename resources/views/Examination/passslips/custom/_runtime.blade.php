{{-- QR codes: <canvas data-qr="TEXT" data-size="84"></canvas>. Class / all-class runs auto-open the print dialog. --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcode/1.5.1/qrcode.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('canvas[data-qr]').forEach(function (c) {
            var size = parseInt(c.dataset.size || '84', 10);
            try { QRCode.toCanvas(c, c.dataset.qr, { width: size, margin: 0 }); } catch (e) {}
        });
        @if(in_array($mode, ['class', 'all'], true) && !($embed ?? false))
            setTimeout(function () { window.focus(); window.print(); }, 900);
        @endif
    });
</script>
