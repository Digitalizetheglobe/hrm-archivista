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

class BranchWiseReportExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    protected $timesheetDetails;
    protected $branch;
    protected $branchData;
    protected $startDate;
    protected $endDate;

    public function __construct($timesheetDetails = null, $branch = null, $branchData = null, $startDate = null, $endDate = null)
    {
        $this->timesheetDetails = $timesheetDetails;
        $this->branch = $branch;
        $this->branchData = $branchData;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function array(): array
    {
        $data = [];
        
        // Add title section
        $data[] = ['BRANCH WISE REPORT', '', '', '', '', '', '', ''];
        $data[] = ['', '', '', '', '', '', '', ''];
        
        if ($this->branch) {
            $data[] = ['Branch Name:', $this->branch->name ?? '', '', '', '', '', '', ''];
            if ($this->startDate && $this->endDate) {
                $data[] = ['Start Date:', \Auth::user()->dateFormat($this->startDate), '', '', '', '', '', ''];
                $data[] = ['End Date:', \Auth::user()->dateFormat($this->endDate), '', '', '', '', '', ''];
            }
            $data[] = ['', '', '', '', '', '', '', ''];
        }
        
        // Add summary statistics
        $data[] = ['SUMMARY', '', '', '', '', '', '', ''];
        $data[] = ['Total Employees:', $this->branchData['total_employees'] ?? 0, '', '', '', '', '', ''];
        $data[] = ['Total Time Spent:', number_format($this->branchData['total_hours'] ?? 0, 2) . ' hrs', '', '', '', '', '', ''];
        $data[] = ['Total Expense:', number_format($this->branchData['total_expense'] ?? 0, 2), '', '', '', '', '', ''];
        $data[] = ['Total Cost:', number_format($this->branchData['total_cost'] ?? 0, 2), '', '', '', '', '', ''];
        $data[] = ['', '', '', '', '', '', '', ''];
        $data[] = ['', '', '', '', '', '', '', ''];
        
        // Add table headers row
        $data[] = ['Date', 'Employee', 'Project', 'Time Spent', 'Hourly Rate', 'Cost', 'Expense', 'Description'];
        
        // Add timesheet details
        if ($this->timesheetDetails) {
            foreach ($this->timesheetDetails as $timesheet) {
                $data[] = [
                    \Auth::user()->dateFormat($timesheet->date),
                    $timesheet->employee->name ?? '',
                    $timesheet->project->project_name ?? '',
                    number_format($timesheet->total_time, 2) . ' hrs',
                    $timesheet->employee->hourly_charged ?? 0,
                    number_format($timesheet->total_time * ($timesheet->employee->hourly_charged ?? 0), 2),
                    number_format($timesheet->expense, 2),
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
        if ($this->branch && $this->startDate && $this->endDate) {
            $summaryRow = 7; // SUMMARY label row
            $summaryStartRow = 8; // First summary data row
            $summaryEndRow = 11; // Last summary data row
            $headerRow = 14; // Table header row (after 2 empty rows)
        } elseif ($this->branch) {
            $summaryRow = 5; // SUMMARY label row
            $summaryStartRow = 6; // First summary data row
            $summaryEndRow = 9; // Last summary data row
            $headerRow = 12; // Table header row (after 2 empty rows)
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
        $sheet->mergeCells('A1:H1');
        
        // Style for branch info section (if branch exists)
        if ($this->branch) {
            $infoStartRow = 3;
            $infoEndRow = $this->startDate && $this->endDate ? 6 : 4;
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
        $sheet->mergeCells("A{$summaryRow}:H{$summaryRow}");
        
        // Style for summary data rows
        $sheet->getStyle("A{$summaryStartRow}:A{$summaryEndRow}")->applyFromArray([
            'font' => ['bold' => true],
        ]);
        $sheet->getStyle("B{$summaryStartRow}:B{$summaryEndRow}")->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
        ]);
        
        // Style for table header row
        $sheet->getStyle("A{$headerRow}:H{$headerRow}")->applyFromArray([
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
            $sheet->getStyle("A" . ($headerRow + 1) . ":H{$highestRow}")->applyFromArray([
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
                    $sheet->getStyle("A{$row}:H{$row}")->applyFromArray([
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
            'A' => 15,  // Date
            'B' => 25,  // Employee
            'C' => 30,  // Project
            'D' => 15,  // Time Spent
            'E' => 15,  // Hourly Rate
            'F' => 15,  // Cost
            'G' => 15,  // Expense
            'H' => 50,  // Description
        ];
    }

    public function title(): string
    {
        return 'Branch Wise Report';
    }
}
