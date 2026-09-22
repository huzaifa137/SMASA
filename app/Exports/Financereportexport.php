<?php

namespace App\Exports;

use App\Exports\Concerns\FormatsReportSheet;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Excel version of the Finance "Payments / Expenses / Payroll" report
 * exports — same filtered $rows CSV/PDF already build (reportsExportCsv()
 * / reportsExportPdf()), with the same school-name / report-title /
 * filters heading block the PDF prints above its table.
 */
class FinanceReportExport implements FromArray, WithTitle, WithStyles
{
    use FormatsReportSheet;

    protected string $type; // payments | expenses | payroll
    protected Collection $rows;
    protected $total;
    protected string $schoolName;
    protected string $contextLine;
    protected string $generatedAt;

    /** Row the column-header line actually lands on — computed from the
     *  array as it's built rather than hardcoded. Set by array(). */
    protected int $headerRow = 0;

    public function __construct(string $type, Collection $rows, $total, string $schoolName, string $contextLine, string $generatedAt)
    {
        $this->type = $type;
        $this->rows = $rows;
        $this->total = $total;
        $this->schoolName = $schoolName;
        $this->contextLine = $contextLine;
        $this->generatedAt = $generatedAt;
    }

    public function array(): array
    {
        $titles = [
            'payments' => 'Payments Report',
            'expenses' => 'Expenses Report',
            'payroll' => 'Payroll Report',
        ];

        $rows = $this->buildHeadingRows([
            $this->schoolName,
            $titles[$this->type] ?? 'Finance Report',
            $this->contextLine,
        ]);

        $rows[] = $this->blankRow();
        $rows[] = $this->headings();
        $this->headerRow = count($rows);

        foreach ($this->rows as $r) {
            $rows[] = $this->mapRow($r);
        }

        $rows[] = $this->blankRow();
        $rows[] = ['Total', '', '', $this->total];
        $rows[] = $this->blankRow();
        $rows[] = ['Generated on ' . $this->generatedAt . ' — SMASA'];

        return $rows;
    }

    private function headings(): array
    {
        return match ($this->type) {
            'expenses' => ['Expense #', 'Title', 'Category', 'Amount', 'Payee', 'Method', 'Date', 'Status'],
            'payroll' => ['Payslip #', 'Teacher', 'Period', 'Gross Pay', 'Deductions', 'Net Pay', 'Status', 'Paid Date'],
            default => ['Receipt #', 'Student', 'Adm #', 'Amount Paid', 'Method', 'Date', 'Term', 'Year', 'Status'],
        };
    }

    private function mapRow($r): array
    {
        return match ($this->type) {
            'expenses' => [
                $r->expense_number,
                $r->title,
                $r->category->name ?? '',
                $r->amount,
                $r->payee_name,
                ucfirst(str_replace('_', ' ', $r->payment_method ?? '')),
                optional($r->expense_date)->format('Y-m-d'),
                ucfirst($r->status),
            ],
            'payroll' => [
                $r->payslip_number,
                trim(($r->teacher->firstname ?? '') . ' ' . ($r->teacher->surname ?? '')),
                $r->period->period_name ?? '',
                $r->gross_pay,
                $r->total_deductions,
                $r->net_pay,
                ucfirst($r->status),
                optional($r->paid_date)->format('Y-m-d'),
            ],
            default => [
                $r->receipt_number,
                trim(($r->student->firstname ?? '') . ' ' . ($r->student->lastname ?? '')),
                $r->student->admission_number ?? '',
                $r->amount_paid,
                ucfirst(str_replace('_', ' ', $r->payment_method)),
                optional($r->payment_date)->format('Y-m-d'),
                $r->term,
                $r->academic_year,
                ucfirst($r->status),
            ],
        };
    }

    public function title(): string
    {
        return match ($this->type) {
            'expenses' => 'Expenses',
            'payroll' => 'Payroll',
            default => 'Payments',
        };
    }

    public function styles(Worksheet $sheet)
    {
        return $this->applyReportHeaderStyles($sheet, 3, $this->headerRow);
    }
}