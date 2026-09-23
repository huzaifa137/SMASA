<?php

namespace App\Exports;

use App\Exports\Concerns\FormatsReportSheet;
use App\Http\Controllers\Helper;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Excel version of the Outstanding Fees report — same filtered
 * $allocations collection outstandingFeesPdf() already builds (same
 * outstandingFeesQuery()/outstandingFeesFilters() helpers), laid out
 * with the same school-name / report-title / filters heading block the
 * other report Excel exports use (ClassSummaryReportExport,
 * FinanceReportExport, etc.).
 */
class OutstandingFeesExport implements FromArray, WithTitle, WithStyles
{
    use FormatsReportSheet;

    protected Collection $allocations;
    protected $totalOutstanding;
    protected array $filters;
    protected string $schoolName;
    protected string $generatedAt;

    /** Row the column-header line actually lands on — computed from the
     *  array as it's built rather than hardcoded. Set by array(). */
    protected int $headerRow = 0;

    public function __construct(Collection $allocations, $totalOutstanding, array $filters, string $schoolName, string $generatedAt)
    {
        $this->allocations = $allocations;
        $this->totalOutstanding = $totalOutstanding;
        $this->filters = $filters;
        $this->schoolName = $schoolName;
        $this->generatedAt = $generatedAt;
    }

    public function array(): array
    {
        $totalPaid = $this->allocations->sum(fn($a) => $a->allocated_amount - $a->discount_amount - $a->balance);

        $rows = $this->buildHeadingRows([
            $this->schoolName,
            'Outstanding Fees Report',
            $this->contextLine(),
            'Students Matching: ' . $this->allocations->count()
                . '  |  Total Outstanding: UGX ' . number_format($this->totalOutstanding, 0)
                . '  |  Total Paid: UGX ' . number_format($totalPaid, 0),
        ]);

        $rows[] = $this->blankRow();
        $rows[] = ['#', 'Student', 'Adm #', 'Class', 'Fee Structure', 'Term', 'Year', 'Billed', 'Paid', 'Balance', 'Status'];
        $this->headerRow = count($rows);

        foreach ($this->allocations as $i => $alloc) {
            $net = $alloc->allocated_amount - $alloc->discount_amount;
            $paid = $net - $alloc->balance;

            $rows[] = [
                $i + 1,
                trim(($alloc->student->firstname ?? 'N/A') . ' ' . ($alloc->student->lastname ?? '')),
                $alloc->student->admission_number ?? '—',
                Helper::recordMdname($alloc->student->senior ?? null) ?? '—',
                $alloc->feeStructure->name ?? '—',
                'T' . $alloc->term,
                $alloc->academic_year,
                $net,
                $paid,
                $alloc->balance,
                ucfirst($alloc->payment_status),
            ];
        }

        $rows[] = $this->blankRow();
        $rows[] = ['', '', '', '', '', '', '', 'Total', $totalPaid, $this->totalOutstanding, ''];
        $rows[] = $this->blankRow();
        $rows[] = ['Generated on ' . $this->generatedAt . ' — SMASA'];

        return $rows;
    }

    /** Plain-text filters line — mirrors the PDF's .filters-bar. */
    private function contextLine(): string
    {
        $bits = [
            'Year: ' . $this->filters['year'],
            'Term: ' . ($this->filters['term'] ?: 'All'),
            'Status: ' . ($this->filters['status'] ? ucfirst($this->filters['status']) : 'Unpaid + Partial (defaulters)'),
        ];

        if ($this->filters['class_id']) {
            $bits[] = 'Class: ' . Helper::recordMdname($this->filters['class_id']);
        }
        if ($this->filters['stream_id']) {
            $bits[] = 'Stream: ' . Helper::recordMdname($this->filters['stream_id']);
        }
        if ($this->filters['gender']) {
            $bits[] = 'Gender: ' . $this->filters['gender'];
        }
        if ($this->filters['fee_structure_id']) {
            $bits[] = 'Fee Structure: ' . (optional(\App\Models\FeeStructure::find($this->filters['fee_structure_id']))->name ?? '');
        }
        if ($this->filters['min_balance'] !== '') {
            $bits[] = 'Min Balance: ' . number_format((float) str_replace(',', '', $this->filters['min_balance']));
        }
        if ($this->filters['max_balance'] !== '') {
            $bits[] = 'Max Balance: ' . number_format((float) str_replace(',', '', $this->filters['max_balance']));
        }
        if ($this->filters['min_paid'] !== '') {
            $bits[] = 'Min Paid: ' . number_format((float) str_replace(',', '', $this->filters['min_paid']));
        }
        if ($this->filters['max_paid'] !== '') {
            $bits[] = 'Max Paid: ' . number_format((float) str_replace(',', '', $this->filters['max_paid']));
        }
        if ($this->filters['search']) {
            $bits[] = 'Search: "' . $this->filters['search'] . '"';
        }

        return implode('  |  ', $bits);
    }

    public function title(): string
    {
        return 'Outstanding Fees';
    }

    public function styles(Worksheet $sheet)
    {
        return $this->applyReportHeaderStyles($sheet, 4, $this->headerRow);
    }
}
