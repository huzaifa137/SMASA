# Student Promotion

Open it from **Students → Promote Students** (`/students/promotion`). Four tabs: **Promote**, **Setup**, **History**, **Alumni**.

## Setup (once per school)
1. **Mode** – *Manual* (you run it) or *Automatic* (runs on a schedule: when the school's active academic year changes, or on a fixed date; it either **prepares a proposal for approval** or **applies immediately**).
2. **Rules** – outstanding fees (ignore / warn / **block**), allowed balance, optional minimum year average, what to do with students who have no results, and what happens in a final class (graduate / stay).
3. **Class ladder** – per class: the class it promotes into, or **Final class** (no class after it, students graduate). Stream placement: same stream name (with fallback), one fixed stream, or choose each time. A per-class *Automatic* switch keeps e.g. Senior 4 → Senior 5 out of auto runs. "Fill from suggestion" proposes the ladder from the master class sequence; nothing is saved until you press Save.

## Promoting
Pick the academic year being closed, a class (and optionally a stream) → *Load students*. Each student gets a suggested result (Promote / Repeat / Graduate / Leave out) and destination, with flags for fees, average, no results, unresolved stream. Change anything per student or in bulk, then *Review & promote*.

Guarantees (enforced on the server): one promotion per student per academic year; students of a final class can only graduate; fee **block** cannot be overridden; a student moved elsewhere since the list was loaded is skipped and reported; everything runs in one transaction.

## After promotion
* Every run is a batch (`PRM-2026-0001`) with a per-student ledger: History → Open, export **CSV**, **Revert** the whole run or one student (students moved again since are left alone and reported).
* Graduates move to the reserved class marker `ALUMNI` (like `NO_STREAM`): they drop out of rosters, attendance, marks entry and the **billing student count**; the Alumni tab lists them and can restore one.
* O-Level electives / A-Level combinations are re-synced for the new class-stream. Students entering A-Level still need a combination assigned.
* Marks, fees and attendance keep their own class snapshots/history.

## Automatic mode
`php artisan promotion:run-auto [--school=ID] [--dry-run]` — scheduled daily 03:00 (needs the `schedule:run` cron). Students who cannot be placed automatically (stream unresolved) or fall in non-automatic classes are left for the manual tab. Idempotent: already-promoted students are never moved twice for the same year.

## Permissions (Students module)
`view_student_promotion`, `promote_students`, `manage_promotion_settings`, `revert_student_promotion`, `export_student_promotion`.

## Install
```bash
git apply student-promotion.patch
php artisan migrate
php artisan db:seed --class=SystemModulesSeeder   # registers the new permissions (safe to re-run)
php artisan config:clear && php artisan route:clear && php artisan view:clear
```
