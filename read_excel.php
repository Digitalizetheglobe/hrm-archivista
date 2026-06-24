<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$inputFileType = IOFactory::identify('attendance_2026-06-23.xlsx');
$reader = IOFactory::createReader($inputFileType);
$spreadsheet = $reader->load('attendance_2026-06-23.xlsx');

$sheet = $spreadsheet->getActiveSheet();

$highestRow = 15;
$highestColumn = $sheet->getHighestColumn();

echo "Highest Column: " . $highestColumn . "\n";

foreach ($sheet->getRowIterator(1, $highestRow) as $row) {
    $cellIterator = $row->getCellIterator();
    $cellIterator->setIterateOnlyExistingCells(false); 
    $rowData = [];
    foreach ($cellIterator as $cell) {
        $val = $cell->getValue();
        if ($val !== null && $val !== '') {
            $rowData[] = $cell->getColumn() . ":" . $val;
        }
    }
    if (!empty($rowData)) {
        echo "Row " . $row->getRowIndex() . ": " . implode(" | ", $rowData) . "\n";
    }
}
