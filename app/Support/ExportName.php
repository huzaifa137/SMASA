<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Single source of truth for NAMING anything that leaves the system:
 * PDF / Excel / CSV downloads and the browser "Save as PDF" name of printed
 * pages (report cards, reports...).
 *
 * Why this exists: exports used to be named with a fixed word plus a date or
 * year ("ID_Cards_Bulk_2026.pdf", "ReportCard.pdf"), so a bulk export and a
 * single one - or two different classes - produced the SAME name. The name
 * must now describe WHAT was exported (who / which class / which exam / which
 * term), so every caller passes the pieces that identify its content.
 *
 *   ExportName::file(['Report-Card', 'Amara Nakato', 'End of Term Exam', 'Term II 2026'], 'pdf')
 *     -> "Report-Card_Amara-Nakato_End-of-Term-Exam_Term-II-2026.pdf"
 *
 *   ExportName::title(['Report Card', 'Amara Nakato', 'End of Term Exam', 'Term II 2026'])
 *     -> "Report Card - Amara Nakato - End of Term Exam - Term II 2026"
 *
 * file()  = filesystem-safe (ASCII, no spaces) - for Content-Disposition names.
 * title() = human readable - for <title>, because browsers use the page title
 *           as the default file name when printing to PDF.
 */
class ExportName
{
    /** Longest base name (without extension) - keeps names OS/browser safe. */
    private const MAX_BASE = 120;

    /** Longest single segment, so one long exam name can't eat the whole name. */
    private const MAX_PART = 45;

    /**
     * One filesystem-safe segment: words joined with "-", ASCII only.
     * "End of Term (Exam) / 2" -> "End-of-Term-Exam-2"
     */
    public static function part($value): string
    {
        $text = trim((string) $value);
        if ($text === '') {
            return '';
        }

        $ascii = Str::ascii($text);
        $clean = trim(preg_replace('/[^A-Za-z0-9]+/', '-', $ascii), '-');

        if (strlen($clean) > self::MAX_PART) {
            $clean = rtrim(substr($clean, 0, self::MAX_PART), '-');
        }

        return $clean;
    }

    /**
     * Download file name: segments joined with "_", words inside a segment
     * joined with "-". Empty segments are skipped, so callers can pass
     * optional pieces (stream, term...) without checking them first.
     *
     * @param  array<int,mixed>  $parts
     */
    public static function file(array $parts, string $extension, string $fallback = 'Export'): string
    {
        $segments = [];
        foreach ($parts as $p) {
            $seg = self::part($p);
            if ($seg !== '') {
                $segments[] = $seg;
            }
        }

        $base = implode('_', $segments);
        if ($base === '') {
            $base = $fallback;
        }

        if (strlen($base) > self::MAX_BASE) {
            $base = rtrim(substr($base, 0, self::MAX_BASE), '-_');
        }

        return $base . '.' . ltrim($extension, '.');
    }

    /**
     * Human-readable title used as <title> (the default "Save as PDF" name).
     * Keeps spaces/Unicode (e.g. Arabic exam names) but removes characters
     * browsers or operating systems reject in file names.
     *
     * @param  array<int,mixed>  $parts
     */
    public static function title(array $parts, string $fallback = 'SMASA'): string
    {
        $segments = [];
        foreach ($parts as $p) {
            $seg = trim(preg_replace('/[\x00-\x1F\\\\\/:*?"<>|]+/u', ' ', (string) $p));
            $seg = trim(preg_replace('/\s+/u', ' ', $seg));
            if ($seg !== '') {
                $segments[] = $seg;
            }
        }

        $title = implode(' - ', $segments);

        return $title !== '' ? $title : $fallback;
    }

    /** "Term II 2026" - the term + year pair most exports need. */
    public static function termYear($term, $year): string
    {
        $label = ($term !== null && $term !== '') ? Term::label($term) : '';

        return trim($label . ' ' . ($year ?? ''));
    }

    /**
     * The "what exam" pieces shared by every exam-based export:
     * [exam name, "Term II 2026"].
     *
     * @param  object|null  $exam  Examination (exam_name, term, academic_year)
     */
    public static function examScope($exam): array
    {
        if (!$exam) {
            return [];
        }

        return [
            $exam->exam_name ?? '',
            self::termYear($exam->term ?? '', $exam->academic_year ?? ''),
        ];
    }

    /** "Amara Nakato" from any object/array carrying first + last name keys. */
    public static function personName($person, string $first = 'firstname', string $last = 'lastname'): string
    {
        $get = fn ($k) => is_array($person) ? ($person[$k] ?? '') : ($person->{$k} ?? '');

        return trim($get($first) . ' ' . $get($last));
    }

    /** "P5 Blue" / "P5" - class plus optional stream. */
    public static function classStream($className, $streamName = null): string
    {
        return trim(trim((string) $className) . ' ' . trim((string) $streamName));
    }
    /**
     * <title> for a printed report card page - browsers use it as the
     * default "Save as PDF" file name.
     *   single: "Report Card - Amara Nakato - P5 Blue - End of Term Exam - Term II 2026"
     *   class : "Report Cards - P5 Blue - End of Term Exam - Term II 2026 - 41 students"
     *   all   : "Report Cards - All Classes - End of Term Exam - Term II 2026"
     *
     * @param  object|null  $exam
     * @param  object|array|null  $student  the student (single mode)
     * @param  iterable  $slips  the slips (class / all mode), each carrying ['student' => ...]
     */
    public static function reportCardTitle($exam, string $mode = 'single', $student = null, $slips = []): string
    {
        $helper = \App\Http\Controllers\Helper::class;
        $classOf = function ($st) use ($helper) {
            if (!$st) {
                return '';
            }
            $senior = is_array($st) ? ($st['senior'] ?? null) : ($st->senior ?? null);
            $stream = is_array($st) ? ($st['stream'] ?? null) : ($st->stream ?? null);

            return self::classStream($senior ? $helper::recordMdname($senior) : '', $stream ? $helper::recordMdname($stream) : '');
        };

        if ($mode === 'single') {
            return self::reportCardTitleFrom($exam, 'single', self::personName($student ?? []), $classOf($student), 1);
        }

        $list = collect($slips);
        $first = $list->first();
        $firstStudent = is_array($first) ? ($first['student'] ?? null) : null;

        return self::reportCardTitleFrom(
            $exam,
            $mode,
            '',
            $mode === 'class' ? $classOf($firstStudent) : 'All Classes',
            $list->count()
        );
    }

    /** Same title from already-resolved names (used by the custom report card designs). */
    public static function reportCardTitleFrom($exam, string $mode, string $who, string $classStream, int $count = 1): string
    {
        if ($mode === 'single') {
            return self::title(array_merge(['Report Card', $who, $classStream], self::examScope($exam)), 'Report Card');
        }

        $parts = array_merge(['Report Cards', $classStream], self::examScope($exam));
        if ($mode === 'class' && $count > 0) {
            $parts[] = $count . ($count === 1 ? ' student' : ' students');
        }

        return self::title($parts, 'Report Cards');
    }
}
