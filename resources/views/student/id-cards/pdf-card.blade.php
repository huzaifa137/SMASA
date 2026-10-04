{{-- resources/views/student/id-cards/pdf-card.blade.php
    Single student ID card PDF — CR80 (85.6 x 54 mm, landscape), 2 pages: FRONT then BACK.
    Print-ready: full-bleed artwork, no outer border (the printer / cutter trims the card).

    The artwork lives in partials/pdf-card-{styles,front,back}.blade.php and is shared
    with the batch sheet (pdf-bulk.blade.php), so both always look the same and follow
    the on-screen preview. DomPDF-safe layout (absolute pt-positioned blocks + tables).
--}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 0; }
        * { margin: 0; padding: 0; }
        body { background: #ffffff; }
        .page { width: 241pt; height: 152pt; page-break-after: always; overflow: hidden; }
        .page.last { page-break-after: auto; }
        @include('student.id-cards.partials.pdf-card-styles')
    </style>
</head>
<body>
    <div class="page">
        @include('student.id-cards.partials.pdf-card-front')
    </div>
    <div class="page last">
        @include('student.id-cards.partials.pdf-card-back')
    </div>
</body>
</html>
