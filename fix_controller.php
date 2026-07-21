<?php
$source = file_get_contents("c:/xampp/htdocs/hrm_rising/app/Http/Controllers/PaySlipController.php");
preg_match_all("/^use App\\\\Models\\\\[^;]+;/m", $source, $matches);
$imports = implode("\n", $matches[0]);

$dest_file = "c:/xampp/htdocs/hrm_archivista/app/Http/Controllers/PaySlipController.php";
$dest = file_get_contents($dest_file);

// Add missing imports
preg_match_all("/^use App\\\\Models\\\\[^;]+;/m", $dest, $dest_matches);
$dest_imports = $dest_matches[0];
$to_add = array_diff($matches[0], $dest_imports);
$dest = str_replace("use App\Models\Employee;", "use App\Models\Employee;\n" . implode("\n", $to_add), $dest);

// Inject helpers
$helpers = file_get_contents("scratch_helpers.txt");
$dest = str_replace("public function salaryProcessing()", $helpers . "\n    public function salaryProcessing()", $dest);

file_put_contents($dest_file, $dest);

