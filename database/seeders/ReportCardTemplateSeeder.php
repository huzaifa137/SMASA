<?php

namespace Database\Seeders;

use App\Models\ReportCardTemplate;
use Illuminate\Database\Seeder;

class ReportCardTemplateSeeder extends Seeder
{
    /**
     * The 3 designs shown by default under /report-templates — deliberately
     * built to look like the ACTUAL pass-slip output (see
     * resources/views/Examination/passslips/slip.blade.php +
     * partials/template-themes.blade.php), not a generic placeholder, so a
     * school opens the builder and sees something already close to what
     * they're used to printing. Still 100% editable afterwards.
     *
     * One of each style is seeded per school-level category (nursery,
     * primary, secondary) so the gallery + the "which template prints for
     * this class" resolution both keep working unchanged. "Classic" is
     * marked as the out-of-the-box default for each category.
     *
     * Known gap: the pass-slip's two performance charts ("Subject
     * Performance — Student vs Class" and "Performance Over Time") have no
     * equivalent element type in the drag-and-drop builder yet — it only
     * lays out text/table/image "sections" in rows, there's no chart
     * renderer. Everything else (header, title band, student info, subject
     * table, totals bar, remarks, signatures, footer) is rebuilt here to
     * match.
     */
    private const STYLES = ['classic', 'modern', 'minimal'];

    public function run(): void
    {
        foreach (['nursery', 'primary', 'secondary'] as $category) {
            foreach (self::STYLES as $style) {
                ReportCardTemplate::create([
                    'school_id'   => null,
                    'is_default'  => $style === 'classic',
                    'is_active'   => true,
                    'name'        => ucfirst($style) . ' — ' . ucfirst($category),
                    'category'    => $category,
                    'description' => $this->description($style),
                    'canvas_width'  => 794,
                    'canvas_height' => 1123,
                    'background'  => ['color' => '#FFFFFF'],
                    'elements'    => $this->elements($style, $category),
                ])->publish();
            }
        }
    }

    private function description(string $style): string
    {
        return match ($style) {
            'classic' => 'Light header framed by two seals, dark title band, ornate touches — the traditional report-card look.',
            'modern'  => 'Solid dark banner header running straight into the title band — a bold, contemporary layout.',
            default   => 'Quiet, editorial layout — a single hairline rule, generous whitespace, left-aligned header.',
        };
    }

    /**
     * Every element carries `row` (which horizontal band it sits in) and
     * `width` (grid units out of 12) instead of x/y/w/h — a school resizes
     * by column and reorders by row instead of dragging pixels.
     */
    private function elements(string $style, string $category): array
    {
        $ink = $this->ink($style);
        $isEarlyYears = $category === 'nursery';
        $row = 0;
        $els = [];

        // ── Header ──────────────────────────────────────────────────
        if ($style === 'minimal') {
            $els[] = $this->el('logo-1', 'logo', $row, 2, ['slot' => 'logo_primary', 'height' => 54]);
            $els[] = $this->el('title-1', 'text', $row, 10, [
                'content' => '<span style="border-bottom:2px solid ' . $ink . ';padding-bottom:4px;">{{school_name}}</span>',
                'fontSize' => 24, 'fontWeight' => 700, 'color' => '#111', 'align' => 'left',
            ]);
            $row++;
            $els[] = $this->el('subtitle-1', 'text', $row, 12, [
                'content' => '{{exam_name}} — {{term}} {{year}}', 'fontSize' => 11, 'color' => '#888', 'align' => 'left',
            ]);
            $row++;
            $els[] = $this->el('rule-1', 'divider', $row, 12, ['color' => '#e2e2e2', 'thickness' => 1]);
        } elseif ($style === 'modern') {
            // Whole header block shares one dark background across all
            // three sections so it reads as a single continuous banner.
            $els[] = $this->el('logo-l', 'logo', $row, 2, ['slot' => 'logo_primary', 'height' => 58, 'background' => $ink, 'padding' => '10px']);
            $els[] = $this->el('title-1', 'text', $row, 8, [
                'content' => '{{school_name}}<br><span style="font-size:12px;font-weight:400;opacity:.85;">Academic Report — {{term}} {{year}}</span>',
                'fontSize' => 24, 'fontWeight' => 700, 'color' => '#ffffff', 'align' => 'center',
                'background' => $ink, 'padding' => '18px 10px',
            ]);
            $els[] = $this->el('logo-r', 'logo', $row, 2, ['slot' => 'logo_secondary', 'height' => 58, 'background' => $ink, 'padding' => '10px']);
            $row++;
            $els[] = $this->el('band-1', 'text', $row, 12, [
                'content' => 'ACADEMIC REPORT FORM — {{student.class}} — {{term}} ({{year}})',
                'fontSize' => 12, 'fontWeight' => 700, 'color' => '#ffffff', 'align' => 'center',
                'background' => '#000000', 'padding' => '9px 14px', 'uppercase' => true,
            ]);
        } else { // classic
            $els[] = $this->el('logo-l', 'logo', $row, 3, ['slot' => 'logo_primary', 'height' => 76]);
            $els[] = $this->el('title-1', 'text', $row, 6, [
                'content' => '{{school_name}}', 'fontSize' => 26, 'fontWeight' => 700, 'color' => $ink, 'align' => 'center',
            ]);
            $els[] = $this->el('logo-r', 'logo', $row, 3, ['slot' => 'logo_secondary', 'height' => 76]);
            $row++;
            $els[] = $this->el('subtitle-1', 'text', $row, 12, [
                'content' => '{{exam_name}} — {{term}} {{year}}', 'fontSize' => 12, 'color' => '#555', 'align' => 'center',
            ]);
            $row++;
            $els[] = $this->el('band-1', 'text', $row, 12, [
                'content' => 'ACADEMIC REPORT FORM — {{student.class}} — {{term}} ({{year}})',
                'fontSize' => 12, 'fontWeight' => 700, 'color' => '#ffffff', 'align' => 'center',
                'background' => $ink, 'padding' => '9px 14px', 'uppercase' => true,
            ]);
        }
        $row++;

        // ── Student info + verification QR ─────────────────────────
        $els[] = $this->el('photo-1', 'student_photo', $row, 3, [
            'height' => $isEarlyYears ? 130 : 110,
            'borderRadius' => $style === 'minimal' ? 6 : 12,
            'border' => $style === 'minimal' ? '1px solid #e2e2e2' : '3px solid ' . $ink,
        ]);
        $infoLines = [
            '<strong>NAME:</strong> {{student.name}}',
            '<strong>CLASS:</strong> {{student.class}}',
            '<strong>ADM NO:</strong> {{student.admission_no}}',
            '<strong>POSITION:</strong> {{class_rank}} of {{class_total}}',
        ];
        $els[] = $this->el('info-1', 'text', $row, 5, [
            'content' => implode('<br>', $infoLines), 'fontSize' => 13, 'color' => '#222', 'align' => 'left',
        ]);
        $els[] = $this->el('qr-1', 'qr_code', $row, 4, [
            'size' => 90,
            'topLabel' => 'Scan to verify',
            'labelColor' => $style === 'minimal' ? '#888' : '#333',
            'border' => $style === 'minimal' ? null : '2px solid ' . $ink,
        ]);
        $row++;

        // ── Subjects table ──────────────────────────────────────────
        $columns = $isEarlyYears ? ['name', 'grade', 'remark', 'teacher'] : ['name', 'score', 'grade', 'remark', 'teacher'];
        $labels = ['name' => 'Subjects', 'score' => 'Marks', 'grade' => 'Grade', 'remark' => 'Comment', 'teacher' => 'Teacher'];
        $els[] = $this->el('table-1', 'subjects_table', $row, 12, [
            'columns' => $columns,
            'columnLabels' => array_intersect_key($labels, array_flip($columns)),
            'zebra' => $style !== 'minimal',
            'headerColor' => $style === 'minimal' ? '#ffffff' : $ink,
            'headerTextColor' => $style === 'minimal' ? '#111111' : '#ffffff',
            'fontSize' => 12,
        ]);
        $row++;

        // ── Totals / average bar ────────────────────────────────────
        $els[] = $this->el('summary-1', 'text', $row, 12, [
            'content' => '<table style="width:100%;font-size:13px;"><tr>'
                . '<td style="text-align:left;color:#666;text-transform:uppercase;font-size:11px;letter-spacing:.05em;">Total / Average</td>'
                . '<td style="text-align:center;font-weight:700;">{{percentage}}%</td>'
                . '<td style="text-align:center;font-weight:700;color:' . $ink . ';">{{overall_grade}}</td>'
                . '<td style="text-align:right;font-weight:700;color:#17a06d;text-transform:uppercase;">{{overall_remark}}</td>'
                . '</tr></table>',
            'background' => $style === 'minimal' ? 'transparent' : '#f4f5f8',
            'padding' => '8px 12px',
            'borderRadius' => 6,
        ]);
        $row++;

        // ── Grading key + attendance ─────────────────────────────────
        $els[] = $this->el('key-1', 'grading_key', $row, 8, ['fontSize' => 11]);
        $els[] = $this->el('att-1', 'attendance', $row, 4, ['fontSize' => 11]);
        $row++;

        // ── Remarks ──────────────────────────────────────────────────
        $els[] = $this->el('remarks-1', 'remarks', $row, 12, ['role' => 'class_teacher', 'fontSize' => 12]);
        $row++;
        if (!$isEarlyYears) {
            $els[] = $this->el('remarks-2', 'remarks', $row, 12, ['role' => 'head_teacher', 'fontSize' => 12]);
            $row++;
        }

        // ── Signatures ───────────────────────────────────────────────
        if ($isEarlyYears) {
            $els[] = $this->el('sig-1', 'signature', $row, 6, ['label' => 'Class Teacher']);
            $els[] = $this->el('sig-2', 'signature', $row, 6, ['label' => 'Head of Nursery']);
        } else {
            $els[] = $this->el('sig-1', 'signature', $row, 3, ['label' => 'Class Teacher']);
            $els[] = $this->el('sig-2', 'signature', $row, 3, ['label' => 'House Teacher']);
            $els[] = $this->el('sig-3', 'signature', $row, 3, ['label' => $category === 'secondary' ? 'Principal' : 'Head Teacher']);
            $els[] = $this->el('sig-4', 'signature', $row, 3, ['label' => 'Parent / Guardian']);
        }
        $row++;

        // ── Footer ───────────────────────────────────────────────────
        $els[] = $this->el('footer-1', 'text', $row, 12, [
            'content' => '<table style="width:100%;font-size:10px;"><tr>'
                . '<td style="text-align:left;color:#999;">{{exam_name}} • {{term}} {{year}}</td>'
                . '<td style="text-align:right;color:#c0392b;font-weight:700;">CONFIDENTIAL</td>'
                . '</tr></table>',
            'padding' => '8px 0 0',
        ]);

        // Faint background watermark — matches the pass-slip look on
        // Classic/Modern; Minimal deliberately stays watermark-free.
        if ($style !== 'minimal') {
            $els[] = $this->el('watermark-1', 'watermark', $row, 12, [
                'content' => strtoupper($category), 'opacity' => 0.045,
            ]);
        }

        return $els;
    }

    private function el(string $id, string $type, int $row, int $width, array $props): array
    {
        return ['id' => $id, 'type' => $type, 'row' => $row, 'width' => $width, 'props' => array_filter($props, fn ($v) => $v !== null)];
    }

    /** One dark "ink" colour per style, matching the near-black/navy the pass-slip themes already use. */
    private function ink(string $style): string
    {
        return match ($style) {
            'classic' => '#15213b',
            'modern'  => '#111111',
            default   => '#111111',
        };
    }
}