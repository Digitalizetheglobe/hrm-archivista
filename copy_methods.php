<?php
$lines = file('c:\xampp\htdocs\hrm_rising\app\Http\Controllers\AttendanceEmployeeController.php');
$start = -1;
$end = -1;
foreach($lines as $i => $line) {
    if(strpos($line, 'public function calendar(Request $request)') !== false) {
        if ($start == -1) $start = $i;
    }
    if(strpos($line, 'public function export(Request $request)') !== false) {
        if ($end == -1) $end = $i;
    }
}

$methods1 = implode('', array_slice($lines, $start, $end - $start));

$start2 = -1;
$end2 = -1;
foreach($lines as $i => $line) {
    if(strpos($line, 'public function updateCalendarAttendance(Request $request)') !== false) {
        $start2 = $i;
    }
}
$methods2 = implode('', array_slice($lines, $start2));
// Remove the last closing brace of the class
$methods2 = preg_replace('/}\s*$/', '', $methods2);

file_put_contents('c:\xampp\htdocs\hrm_archivista\calendar_methods.txt', $methods1 . "\n" . $methods2);
