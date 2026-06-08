<?php
require 'c:\xampp\htdocs\hrm_archivista\vendor\autoload.php';
$app = require 'c:\xampp\htdocs\hrm_archivista\bootstrap\app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$settings = \DB::table('settings')->whereIn('name', ['custom_leave_approve_subject', 'custom_leave_approve_body'])->get();
foreach($settings as $setting) {
    echo $setting->name . " (user " . $setting->created_by . "): \n" . $setting->value . "\n\n";
}
