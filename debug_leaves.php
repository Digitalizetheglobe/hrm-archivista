<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

use App\Models\LeaveType;

echo "=== Checking Leave Types ===\n";

// Get all leave types for creator ID 4
$leaveTypes = LeaveType::where('created_by', 4)->get();

echo "Total Leave Types: " . $leaveTypes->count() . "\n\n";

foreach ($leaveTypes as $leaveType) {
    echo "ID: " . $leaveType->id . "\n";
    echo "Title: " . $leaveType->title . "\n";
    echo "Days: " . $leaveType->days . "\n";
    echo "Is Unlimited: " . ($leaveType->is_unlimited ? 'Yes' : 'No') . "\n";
    echo "Eligible Employee Types: " . json_encode($leaveType->eligible_employee_types) . "\n";
    echo "Created By: " . $leaveType->created_by . "\n";
    echo "------------------------\n";
}

echo "\n=== Checking Employee Type Matching ===\n";

// Test matching for "Payroll"
$employeeType = "Payroll";

$matchingLeaveTypes = LeaveType::where('created_by', 4)
    ->where('is_unlimited', 0)
    ->where(function($query) use ($employeeType) {
        $query->whereJsonContains('eligible_employee_types', $employeeType)
              ->orWhereNull('eligible_employee_types');
    })
    ->get();

echo "Matching Leave Types for '$employeeType': " . $matchingLeaveTypes->count() . "\n";

foreach ($matchingLeaveTypes as $leaveType) {
    echo "- " . $leaveType->title . "\n";
}
