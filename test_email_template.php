<?php
require 'c:\xampp\htdocs\hrm_archivista\vendor\autoload.php';
$app = require 'c:\xampp\htdocs\hrm_archivista\bootstrap\app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$template = \App\Models\EmailTemplateLang::first();
if ($template) print_r($template->toArray());
else echo "No EmailTemplateLang found";
