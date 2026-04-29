<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class EmployeeWiseReportExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    protected $timesheetDetails;
    protected $employee;
    protected $reportData;
    protected $startDate;
    protected $endDate;

    public function __construct($timesheetDetails = null, $employee = null, $reportData = null, $startDate = null, $endDate = null)
    {
        $this->timesheetDetails = $timesheetDetails;
        $this->employee = $employee;
        $this->reportData = $reportData;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function array(): array
    {
        $data = [];
        
        // Add summary section
        $data[] = ['EMPLOYEE WISE REPORT', '', '', ''];
        $data[] = ['', '', '', ''];
        
        if ($this->employee) {
            $data[] = ['Employee Name:', $this->employee->name ?? '', '', ''];
            $data[] = ['Branch:', $this->employee->branch->name ?? '', '', ''];
            $data[] = ['Department:', $this->employee->department->name ?? '', '', ''];
            if ($this->startDate && $this->endDate) {
                $data[] = ['Start Date:', \Auth::user()->dateFormat($this->startDate), '', ''];
                $data[] = ['End Date:', \Auth::user()->dateFormat($this->endDate), '', ''];
            }
            $data[] = ['', '', '', ''];
        }
        
        // Add summary statistics
        $data[] = ['SUMMARY', '', '', ''];
        $data[] = ['Total Projects Worked On:', $this->reportData['total_projects'] ?? 0, '', ''];
        $data[] = ['Total Time Worked:', number_format($this->reportData['total_hours'] ?? 0, 2) . ' hrs', '', ''];
        $data[] = ['Hourly Rate:', number_format($this->employee->hourly_charged ?? 0, 2), '', ''];
        $data[] = ['Total Cost:', number_format($this->reportData['total_cost'] ?? 0, 2), '', ''];
        $data[] = ['', '', '', ''];
        $data[] = ['', '', '', ''];
        
        // Add table headers row
        $data[] = ['Date', 'Project', 'Time Spent', 'Description'];
        
        // Add timesheet details
        if ($this->timesheetDetails) {
            foreach ($this->timesheetDetails as $timesheet) {
                $data[] = [
                    \Auth::user()->dateFormat($timesheet->date),
                    $timesheet->project->project_name ?? '',
                    number_format($timesheet->total_time, 2) . ' hrs',
                    $timesheet->narration ?? '',
                ];
            }
        }
        
        return $data;
    }

    public function headings(): array
    {
        return [];
    }

    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();
        
        // Calculate row positions dynamically based on array structure
        // Row 1: Title, Row 2: Empty
        // If employee: Rows 3-6: Employee info (with dates: 3-8), Row 7 or 9: Empty
        // SUMMARY row: Row 7 (if employee without dates) or Row 9 (if employee with dates) or Row 3 (if no employee)
        // Summary data: Rows 8-11 (if employee without dates) or Rows 10-13 (if employee with dates) or Rows 4-7 (if no employee)
        // Empty rows: 2 rows after summary
        // Header row: After empty rows
        
        if ($this->employee && $this->startDate && $this->endDate) {
            $summaryRow = 9; // SUMMARY label row
            $summaryStartRow = 10; // First summary data row
            $summaryEndRow = 13; // Last summary data row
            $headerRow = 16; // Table header row (after 2 empty rows)
        } elseif ($this->employee) {
            $summaryRow = 7; // SUMMARY label row
            $summaryStartRow = 8; // First summary data row
            $summaryEndRow = 11; // Last summary data row
            $headerRow = 14; // Table header row (after 2 empty rows)
        } else {
            $summaryRow = 3; // SUMMARY label row
            $summaryStartRow = 4; // First summary data row
            $summaryEndRow = 7; // Last summary data row
            $headerRow = 10; // Table header row (after 2 empty rows)
        }
        
        // Style for title row (row 1)
        $sheet->getStyle('A1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 16,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
        $sheet->mergeCells('A1:D1');
        
        // Style for employee info section (if employee exists)
        if ($this->employee) {
            $infoStartRow = 3;
            $infoEndRow = $this->startDate && $this->endDate ? 8 : 6;
            $sheet->getStyle("A{$infoStartRow}:A{$infoEndRow}")->applyFromArray([
                'font' => ['bold' => true],
            ]);
        }
        
        // Style for SUMMARY label
        $sheet->getStyle("A{$summaryRow}")->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 12,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '70AD47'],
            ],
        ]);
        $sheet->mergeCells("A{$summaryRow}:D{$summaryRow}");
        
        // Style for summary data rows
        $sheet->getStyle("A{$summaryStartRow}:A{$summaryEndRow}")->applyFromArray([
            'font' => ['bold' => true],
        ]);
        $sheet->getStyle("B{$summaryStartRow}:B{$summaryEndRow}")->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
        ]);
        
        // Style for table header row
        $sheet->getStyle("A{$headerRow}:D{$headerRow}")->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ]);
        
        // Style for data rows
        if ($highestRow > $headerRow) {
            $sheet->getStyle("A" . ($headerRow + 1) . ":D{$highestRow}")->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'CCCCCC'],
                    ],
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_TOP,
                ],
            ]);
            
            // Alternate row colors for better readability
            for ($row = $headerRow + 1; $row <= $highestRow; $row++) {
                if ($row % 2 == 0) {
                    $sheet->getStyle("A{$row}:D{$row}")->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'F2F2F2'],
                        ],
                    ]);
                }
            }
        }
        
        // Set row heights
        $sheet->getRowDimension(1)->setRowHeight(30);
        $sheet->getRowDimension($summaryRow)->setRowHeight(25);
        $sheet->getRowDimension($headerRow)->setRowHeight(25);
        
        return $sheet;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 20,  // Date
            'B' => 30,  // Project
            'C' => 15,  // Time Spent
            'D' => 50,  // Description
        ];
    }

    public function title(): string
    {
        return 'Employee Wise Report';
    }
}

