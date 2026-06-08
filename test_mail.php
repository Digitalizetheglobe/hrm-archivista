<?php
require 'c:\xampp\htdocs\hrm_archivista\vendor\autoload.php';
$app = require 'c:\xampp\htdocs\hrm_archivista\bootstrap\app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$dummyTemplate = new \stdClass();
$dummyTemplate->subject = 'Test Subject';
$dummyTemplate->content = "Dear Adarsh,\nYour request has been approved.\nWarm Regards.";

$settings = \App\Models\Utility::getSMTPDetails(4);

$mailable = new \App\Mail\CommonEmailTemplate($dummyTemplate, $settings, 'test@example.com');
echo $mailable->render();
