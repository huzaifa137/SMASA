<?php

namespace App\Exports\Concerns;

use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

/**
 * Shared "printed report" look for report Excel sheets: a bold
 * school-name row followed by one or more smaller context rows (report
 * title, exam/class/term info) sitting above the data — mirroring the
 * equivalent PDF's centered .header block, which none of these sheets
 * had before (they went straight into their column headers).
 *
 * Sheets using this build their heading block as ordinary rows inside
 * array()/collection() itself (see buildHeadingRows()) — the same
 * manual row-by-row layout ExamStatisticsExport already uses — rather
 * than via WithHeadings, so the block can sit above the column headers
 * without fighting Maatwebsite Excel's own single-header-row handling.
 */
trait FormatsReportSheet
{
    /**
     * One array row per heading line, single-celled — applyHeadingBlockStyles()
     * spans each across every column once the sheet's real width is known.
     */
    protected function buildHeadingRows(array $lines): array
    {
        return array_map(fn($line) => [$line], $lines);
    }

    /**
     * Bold/centred styling for the heading block: row 1 (school name,
     * larger/bold) through row $headingRowCount (smaller context lines).
     */
    protected function applyHeadingBlockStyles(Worksheet $sheet, int $headingRowCount): void
    {
        if ($headingRowCount < 1) {
            return;
        }

        $lastCol = $sheet->getHighestColumn();

        $sheet->mergeCells("A1:{$lastCol}1");
        $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
            'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => '2C29CA']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        for ($row = 2; $row <= $headingRowCount; $row++) {
            $sheet->mergeCells("A{$row}:{$lastCol}{$row}");
            $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
                'font' => ['size' => 10, 'color' => ['rgb' => '555555']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);
        }
    }

    /** Bold white-on-purple styling for a table's own column-header row. */
    protected function applyColumnHeaderStyle(Worksheet $sheet, int $row, ?string $lastCol = null): void
    {
        $lastCol = $lastCol ?? $sheet->getHighestColumn();

        $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '2C29CA']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
    }

    /** Freezes the pane just below $row and auto-sizes every column. */
    protected function freezeAndAutoSize(Worksheet $sheet, int $row): void
    {
        $lastCol = $sheet->getHighestColumn();

        $sheet->freezePane('B' . ($row + 1));

        foreach (range('A', $lastCol) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }

    /**
     * Convenience wrapper for the common single-table case: heading
     * block, one column-header row, freeze pane just below it, and
     * auto-sized columns. Call from styles() and return its result.
     */
    protected function applyReportHeaderStyles(Worksheet $sheet, int $headingRowCount, int $headerRow): array
    {
        $this->applyHeadingBlockStyles($sheet, $headingRowCount);
        $this->applyColumnHeaderStyle($sheet, $headerRow);
        $this->freezeAndAutoSize($sheet, $headerRow);

        return [];
    }
}