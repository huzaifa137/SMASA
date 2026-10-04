# No-reload UX (SMASA.*)

Loaded globally from `public/assets/js/smasa-ux.js` (+ `smasa-ux.css`).

| Call | Use when |
|---|---|
| `SMASA.done(title, text)` | A save/edit/delete succeeded. Toast + in-place refresh of the table/card. |
| `SMASA.refresh()` | Refresh in place, no toast. |
| `SMASA.toast('error', title, text)` | Message only. |
| `SMASA.reload(title, text)` | The whole page really must reload (year/term/school switch). The toast is shown after the reload. |

In-place refresh swaps `<table>` cards (or `[data-smasa-zone]`), refreshes `.stat-chip` / `[data-smasa-live]`
counters, highlights new/changed rows, and falls back to a normal reload if anything looks unsafe.

Rules for pages that use `SMASA.done`/`SMASA.refresh`: bind row-button handlers with delegation
(`$(document).on('click', '.btn', fn)` or inline `onclick`), never directly on elements inside a table.

Plain forms: add `data-smasa-ajax` (and `data-smasa-reset`) to a `<form>` to submit via fetch, show the
Laravel flash message as a toast, and refresh zones. Laravel `session('success'|'error'|...)` flashes
become toasts automatically on normal page loads.

## Page mode (whole-page, in place)

`SMASA.donePage(title, text)` / `SMASA.refreshPage()` re-fetch the page and patch the body in place
using the vendored `morphdom` (public/assets/js/vendors/morphdom-umd.min.js, MIT, lazy-loaded).

- Updates stat cards, badges, steppers, tables and DataTables (DataTables keep page/search/sort).
- Keeps open modals, active tabs/collapses, select2, scroll position and anything the user is typing outside tables.
- Header, sidebar, charts, `[data-smasa-ignore]` are never touched. Scripts are never re-run.
- New rows glow (`.row-flash`).
- Row buttons bound with `addEventListener` loops are wrapped in `SMASA.bind('key', fn)`, which re-runs
  after each refresh (each loop guards with `el.__smb` so nothing binds twice).

Pages that stay on a real reload on purpose (header/session data or JS-computed pages): academic year,
term dates, school profile/switch, marks-entry/grading result pages, imports, timetable editor,
card-scan attendance, outstanding-fees recalculation, id-card previews, all-schools, master-code.
They still get the toast after the reload (no OK click).
