<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$las = new \App\services\LeaveAllocationService();
$balances = $las->getCurrentLeaveBalances(64);
print_r($balances);
