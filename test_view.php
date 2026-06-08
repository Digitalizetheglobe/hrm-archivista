<?php
require 'c:\xampp\htdocs\hrm_archivista\vendor\autoload.php';
$app = require 'c:\xampp\htdocs\hrm_archivista\bootstrap\app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$view = view('email.common_email_template', ['content' => 'Hello World'])->render();
echo $view;
