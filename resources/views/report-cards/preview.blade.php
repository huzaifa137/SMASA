<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $template->name }} — Preview</title>
    <style>
        * { box-sizing: border-box; }
        html, body {
            margin: 0; padding: 0;
            background: #eef0f4;
            display: flex; justify-content: center;
            font-family: 'Helvetica Neue', Arial, sans-serif;
        }
        .preview-wrap {
            padding: 24px 0;
            transform-origin: top center;
        }
        .preview-sheet {
            background: #fff;
            box-shadow: 0 4px 24px rgba(0,0,0,.12);
        }
    </style>
</head>
<body>
    <div class="preview-wrap">
        <div class="preview-sheet">
            {!! $html !!}
        </div>
    </div>
    <script>
        // Auto-scale the sheet to fit whatever iframe/window it's shown in
        // (gallery thumbnails are tiny; a direct "open in new tab" is full size).
        (function () {
            var sheet = document.querySelector('.preview-sheet');
            function fit() {
                var pad = 16;
                var scale = Math.min(
                    (window.innerWidth - pad) / sheet.offsetWidth,
                    (window.innerHeight - pad) / sheet.offsetHeight
                );
                if (scale < 1) {
                    sheet.style.transform = 'scale(' + scale + ')';
                    sheet.style.transformOrigin = 'top center';
                }
            }
            window.addEventListener('resize', fit);
            fit();
        })();
    </script>
</body>
</html>
