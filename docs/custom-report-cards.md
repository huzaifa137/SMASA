# Custom (per-school) report cards

For schools that insist on their own report-card layout.

## Setup
1. `php artisan migrate`
2. Admin → **Custom Report Cards** → *Scan & register new designs*
3. **School assignments** → pick the design per school and level (Nursery / Primary / Secondary).
   *Locked* = school only sees its own design. Unlocked = default, but the school may switch to the standard ones.
4. **Preview studio** → preview any design with a school's real exams/students.

## Adding a school's design
Copy `resources/views/Examination/passslips/custom/reference-primary.blade.php` to `<slug>.blade.php`
(slug: lowercase letters/digits/hyphens, max 30 chars), edit the header and paste the school's HTML/CSS:

```blade
{{--
  @report-card
  name: St. Example Primary
  level: primary            (nursery | primary | secondary)
  description: Navy letterhead
  accent: #1e3a8a           (optional - enables the colour picker)
  toggles: show_logo, show_photo, show_qr, show_remarks, show_signatures
  off_by_default: show_photo   (optional, must also be in toggles)
--}}
```
Keep `@include('...custom._base-css')`, the toolbar and `_runtime` includes, wrap each learner in `<div class="crc-sheet">`
and loop `@foreach($reports as $r)`. Bind only to `$r` (see `app/Support/CustomReport.php` for every field);
use `$r->on('show_qr')` for switches and `<canvas data-qr="{{ $r->qr_text }}">` for the QR code.

## Notes
- Bulk "print all" across mixed levels keeps the old behaviour; the custom design applies when all classes are one level (print per class otherwise).
- Files starting with `_` are partials and never listed as designs.
