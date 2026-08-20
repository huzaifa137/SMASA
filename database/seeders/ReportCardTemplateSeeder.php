<?php

namespace Database\Seeders;

use App\Models\ReportCardTemplate;
use Illuminate\Database\Seeder;

class ReportCardTemplateSeeder extends Seeder
{
    /**
     * The 3 designs shown by default under /report-templates — the SAME
     * three looks already offered on the pass-slip screen (Classic /
     * Modern / Minimal), just rebuilt as section/grid templates so a
     * school customizes by resizing (col-md-3 -> col-md-6 -> col-md-12)
     * and re-ordering sections instead of building a layout from scratch.
     *
     * One of each style is seeded per school-level category (nursery,
     * primary, secondary) so the gallery + the "which template prints for
     * this class" resolution both keep working unchanged. "Classic" is
     * marked as the out-of-the-box default for each category.
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
            'classic' => 'Centered header, gold accent band and ornate touches — the traditional report-card look.',
            'modern'  => 'Bold colour-blocked banner header with a confident, contemporary layout.',
            default   => 'Quiet, editorial layout — a single hairline rule, generous whitespace, left-aligned header.',
        };
    }

    /**
     * Every element below carries `row` (which horizontal band it sits in)
     * and `width` (grid units out of 12) instead of x/y/w/h — that's the
     * whole schema change that lets the builder resize by column instead
     * of dragging pixels.
     */
    private function elements(string $style, string $category): array
    {
        $palette = $this->palette($style);
        $isEarlyYears = $category === 'nursery';
        $columns = $isEarlyYears ? ['name', 'grade', 'remark'] : ['name', 'score', 'grade', 'remark'];
        $row = 0;
        $els = [];

        // -- Header row: the one section that differs most per style --
        if ($style === 'classic') {
            $els[] = $this->el('logo-l', 'logo', $row, 3, ['slot' => 'logo_primary', 'height' => 76, 'align' => 'left']);
            $els[] = $this->el('title-1', 'text', $row, 6, [
                'content' => '{{school_name}}', 'fontSize' => 24, 'fontWeight' => 700, 'color' => $palette['accent_dark'], 'align' => 'center',
            ]);
            $els[] = $this->el('logo-r', 'logo', $row, 3, ['slot' => 'logo_secondary', 'height' => 76, 'align' => 'right']);
            $row++;
            $els[] = $this->el('subtitle-1', 'text', $row, 12, [
                'content' => 'Report Card — {{term}} {{year}}', 'fontSize' => 13, 'color' => '#555', 'align' => 'center',
            ]);
            $row++;
            $els[] = $this->el('band-1', 'shape', $row, 12, ['fill' => $palette['accent'], 'height' => 6, 'borderRadius' => 2]);
        } elseif ($style === 'modern') {
            $els[] = $this->el('logo-1', 'logo', $row, 2, ['slot' => 'logo_primary', 'height' => 60]);
            $els[] = $this->el('title-1', 'text', $row, 10, [
                'content' => '{{school_name}}<br><span style="font-size:12px;font-weight:400;">Report — {{term}} {{year}}</span>',
                'fontSize' => 22, 'fontWeight' => 700, 'color' => $palette['accent_dark'],
            ]);
            $row++;
            $els[] = $this->el('band-1', 'shape', $row, 12, ['fill' => '#1a1a1a', 'height' => 6]);
        } else { // minimal
            $els[] = $this->el('logo-1', 'logo', $row, 2, ['slot' => 'logo_primary', 'height' => 54]);
            $els[] = $this->el('title-1', 'text', $row, 10, [
                'content' => '{{school_name}}', 'fontSize' => 22, 'fontWeight' => 700, 'color' => '#111',
            ]);
            $row++;
            $els[] = $this->el('subtitle-1', 'text', $row, 10, [
                'content' => 'Report — {{term}} {{year}}', 'fontSize' => 11, 'color' => '#888', 'align' => 'left',
            ]);
            $els[] = $this->el('rule-1', 'divider', $row, 12, ['color' => '#e2e2e2', 'thickness' => 1]);
        }
        $row++;

        // -- Student info row --
        if ($isEarlyYears) {
            $els[] = $this->el('photo-1', 'student_photo', $row, 3, ['height' => 130, 'borderRadius' => 12, 'border' => '3px solid ' . $palette['accent']]);
            $els[] = $this->el('name-1', 'student_field', $row, 5, ['field' => 'name', 'label' => 'Name', 'fontSize' => 15]);
            $els[] = $this->el('class-1', 'student_field', $row, 4, ['field' => 'class', 'label' => 'Class', 'fontSize' => 13]);
        } else {
            $els[] = $this->el('name-1', 'student_field', $row, 4, ['field' => 'name', 'label' => 'Name', 'fontSize' => 13]);
            $els[] = $this->el('adm-1', 'student_field', $row, 4, ['field' => 'admission_no', 'label' => 'Adm. No', 'fontSize' => 13]);
            $els[] = $this->el('class-1', 'student_field', $row, 4, ['field' => 'class', 'label' => 'Class', 'fontSize' => 13]);
        }
        $row++;

        // -- Subjects table --
        $els[] = $this->el('table-1', 'subjects_table', $row, 12, [
            'columns' => $columns,
            'zebra' => $style !== 'minimal',
            'headerColor' => $style === 'modern' ? $palette['accent_dark'] : ($style === 'minimal' ? '#ffffff' : $palette['accent_a08']),
            'fontSize' => 12,
        ]);
        $row++;

        // -- Grading key + attendance --
        $els[] = $this->el('key-1', 'grading_key', $row, 8, ['fontSize' => 11]);
        $els[] = $this->el('att-1', 'attendance', $row, 4, ['fontSize' => 11]);
        $row++;

        // -- Remarks --
        $els[] = $this->el('remarks-1', 'remarks', $row, 12, ['role' => 'class_teacher', 'fontSize' => 12]);
        $row++;
        if (!$isEarlyYears) {
            $els[] = $this->el('remarks-2', 'remarks', $row, 12, ['role' => 'head_teacher', 'fontSize' => 12]);
            $row++;
        }

        // -- Signatures --
        if ($isEarlyYears) {
            $els[] = $this->el('sig-1', 'signature', $row, 6, ['label' => 'Class Teacher']);
            $els[] = $this->el('sig-2', 'signature', $row, 6, ['label' => 'Head of Nursery']);
        } else {
            $els[] = $this->el('sig-1', 'signature', $row, 4, ['label' => 'Class Teacher']);
            $els[] = $this->el('sig-2', 'signature', $row, 4, ['label' => $category === 'secondary' ? 'Principal' : 'Head Teacher']);
            $els[] = $this->el('sig-3', 'signature', $row, 4, ['label' => 'Date']);
        }

        return $els;
    }

    private function el(string $id, string $type, int $row, int $width, array $props): array
    {
        return ['id' => $id, 'type' => $type, 'row' => $row, 'width' => $width, 'props' => $props];
    }

    /** Accent colours reused from the pass-slip theme CSS so the two pickers feel like the same product. */
    private function palette(string $style): array
    {
        return match ($style) {
            'classic' => ['accent' => '#d9a441', 'accent_dark' => '#8a611c', 'accent_a08' => '#faf1de'],
            'modern'  => ['accent' => '#2f2ccb', 'accent_dark' => '#1e1c99', 'accent_a08' => '#eceeff'],
            default   => ['accent' => '#999999', 'accent_dark' => '#111111', 'accent_a08' => '#f5f5f5'],
        };
    }
}