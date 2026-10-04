{{-- resources/views/student/id-cards/partials/pdf-card-styles.blade.php

    Shared CSS for the printed student ID card (front + back).
    Used by pdf-card.blade.php (single) and pdf-bulk.blade.php (batch) so both
    always look identical — and match the on-screen preview.

    DomPDF-SAFE: no flex / grid / CSS variables / object-fit. Every element is
    positioned in pt inside a fixed CR80 canvas (242pt x 153pt = 85.6 x 54 mm),
    with explicit line-heights so text can never push other blocks around.
    Fonts: Helvetica (built-in, clean) + DejaVu Sans Mono for the card number.
--}}
.card {
    position: relative;
    width: 241pt; height: 152pt;
    overflow: hidden;
    background: #ffffff;
    font-family: Helvetica, Arial, sans-serif;
    color: #0f172a;
}
.card .abs { position: absolute; }
.card img { display: block; }

/* ─────────────── FRONT ─────────────── */
.f-head-bg   { position: absolute; top: 0; left: 0; width: 241pt; height: 33pt; }
.f-logo {
    position: absolute; top: 5.5pt; left: 8pt; width: 22pt; height: 22pt;
    border-radius: 11pt; overflow: hidden;
    border: 1pt solid #8f8de6;
    background: #3d3ad6;
    text-align: center;
}
.f-logo img  { width: 22pt; height: 22pt; }
.f-logo .ph  { font-size: 10.5pt; font-weight: bold; color: #ffffff; line-height: 22pt; }

.f-school {
    position: absolute; top: 7pt; left: 37pt; width: 198pt; height: 9pt;
    font-size: 7.2pt; line-height: 9pt; font-weight: bold; color: #ffffff;
    text-transform: uppercase; letter-spacing: 0.25pt;
    white-space: nowrap; overflow: hidden;
}
.f-tag {
    position: absolute; top: 18.5pt; left: 37pt;
    font-size: 4.8pt; line-height: 6pt; font-weight: bold; color: #e4e4ff;
    text-transform: uppercase; letter-spacing: 0.5pt;
    background: #4c49d8;
    padding: 1.4pt 5.5pt; border-radius: 6pt;
}

.f-photo {
    position: absolute; top: 39pt; left: 8pt; width: 52pt; height: 64pt;
    border: 1.5pt solid #2f2ccb; border-radius: 6pt; overflow: hidden;
    background-color: #e0e7ff;
    background-repeat: no-repeat; background-position: center top; background-size: cover;
    text-align: center;
}
.f-photo .init { display: block; font-size: 17pt; font-weight: bold; color: #2f2ccb; line-height: 61pt; }

.f-name {
    position: absolute; top: 38pt; left: 68pt; width: 120pt; height: 12pt;
    font-size: 9pt; line-height: 12pt; font-weight: bold; color: #0f172a;
    white-space: nowrap; overflow: hidden;
}
.f-rows { position: absolute; top: 53pt; left: 68pt; width: 120pt; }
.f-rows table { width: 120pt; border-collapse: collapse; }
.f-rows td { height: 10.5pt; font-size: 6.6pt; line-height: 10.5pt; vertical-align: middle; padding: 0; white-space: nowrap; overflow: hidden; }
.f-rows .lbl { width: 34pt; font-size: 5.2pt; font-weight: bold; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.3pt; }
.f-rows .val { font-weight: bold; color: #334155; }

.f-pill {
    position: absolute; top: 88pt; left: 68pt;
    font-size: 5.6pt; line-height: 7pt; font-weight: bold;
    padding: 1.6pt 6pt; border-radius: 7pt;
}
.pill-male   { background: #dbeafe; color: #1d4ed8; }
.pill-female { background: #fce7f3; color: #be185d; }
.pill-other  { background: #f1f5f9; color: #64748b; }

.f-qr {
    position: absolute; top: 40pt; left: 194pt; width: 39pt; height: 39pt;
    border: 0.75pt solid #e2e8f0; border-radius: 6pt; background: #ffffff;
}
.f-qr img    { position: absolute; top: 2pt; left: 2pt; width: 35pt; height: 35pt; }
.f-qr-cap {
    position: absolute; top: 82pt; left: 188pt; width: 51pt;
    font-size: 4pt; line-height: 5pt; font-weight: bold; color: #94a3b8;
    text-align: center; text-transform: uppercase; letter-spacing: 0.4pt;
}

.f-mid {
    position: absolute; top: 106pt; left: 0; width: 241pt; height: 21pt;
    background: #f8fafc;
    border-top: 0.75pt solid #e2e8f0; border-bottom: 0.75pt solid #e2e8f0;
}
.f-mid table { width: 241pt; border-collapse: collapse; }
.f-mid td { width: 33.33%; padding: 3.4pt 0 0 8pt; vertical-align: top; }
.m-lbl { font-size: 4.6pt; line-height: 6pt; font-weight: bold; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.4pt; }
.m-val { font-size: 7pt; line-height: 9pt; font-weight: bold; color: #0f172a; }
.m-exp { color: #dc2626; }

.f-cardno-lbl {
    position: absolute; top: 130pt; left: 8pt;
    font-size: 4.6pt; line-height: 6pt; font-weight: bold; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.4pt;
}
.f-cardno-val {
    position: absolute; top: 136.5pt; left: 8pt; width: 150pt;
    font-family: "DejaVu Sans Mono", monospace; font-size: 6.4pt; line-height: 8pt; font-weight: bold; color: #2f2ccb;
    white-space: nowrap; overflow: hidden;
}
.f-status-lbl {
    position: absolute; top: 130pt; right: 8pt; width: 60pt; text-align: right;
    font-size: 4.6pt; line-height: 6pt; font-weight: bold; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.4pt;
}
.f-status-val {
    position: absolute; top: 136pt; right: 8pt; width: 60pt; text-align: right;
    font-size: 7.4pt; line-height: 9pt; font-weight: bold;
}
.st-active  { color: #059669; }
.st-revoked { color: #dc2626; }
.st-expired { color: #d97706; }

.f-strip { position: absolute; left: 0; bottom: 0; width: 241pt; height: 3.5pt; }

/* ─────────────── BACK ─────────────── */
.b-head { position: absolute; top: 0; left: 0; width: 241pt; height: 20pt; background: #1a1869; }
.b-head-txt {
    position: absolute; top: 0; left: 10pt; width: 221pt; height: 20pt;
    font-size: 6.2pt; line-height: 20pt; font-weight: bold; color: #ffffff;
    text-transform: uppercase; letter-spacing: 0.4pt; white-space: nowrap; overflow: hidden;
}
.b-accent { position: absolute; top: 20pt; left: 0; width: 241pt; height: 1.5pt; background: #2f2ccb; }

.b-grid { position: absolute; top: 28pt; left: 10pt; width: 221pt; }
.b-grid table { width: 221pt; border-collapse: collapse; }
.b-grid td { width: 110pt; height: 23pt; padding: 0 6pt 0 0; vertical-align: top; }
.b-lbl { font-size: 4.9pt; line-height: 6pt; font-weight: bold; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.4pt; }
.b-val { font-size: 7.2pt; line-height: 10pt; font-weight: bold; color: #0f172a; white-space: nowrap; overflow: hidden; }

.b-divider { position: absolute; top: 101pt; left: 10pt; width: 221pt; height: 0; border-top: 0.75pt solid #e2e8f0; }
.b-lost {
    position: absolute; top: 107pt; left: 10pt; width: 150pt;
    font-size: 5.4pt; line-height: 7.6pt; color: #64748b;
}
.b-lost b { color: #0f172a; }
.b-chip {
    position: absolute; top: 108pt; right: 10pt;
    font-size: 6.4pt; line-height: 8pt; font-weight: bold; padding: 2.6pt 9pt; border-radius: 9pt;
}
.chip-active   { background: #dcfce7; color: #166534; }
.chip-inactive { background: #fee2e2; color: #991b1b; }
