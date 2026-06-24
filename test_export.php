<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Exports\AttendanceExport;
use Illuminate\Http\Request;

// Mock user since Auth is required
$user = App\Models\User::first();
Auth::login($user);

$request = new Request([
    'type' => 'monthly',
    'month' => '2026-06'
]);

try {
    $export = new AttendanceExport($request);
    $view = $export->view();
    echo "View rendered successfully. View name: " . $view->name() . "\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n" . $e->getTraceAsString();
}
