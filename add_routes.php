<?php $file = "c:/xampp/htdocs/hrm_archivista/routes/web.php"; $content = file_get_contents($file); $routes = "
    Route::get(\"salary-processing\", [\App\Http\Controllers\PaySlipController::class, \"salaryProcessing\"])->name(\"salary-processing.index\")->middleware([\"auth\", \"XSS\"]);
    Route::post(\"salary-processing/search_json\", [\App\Http\Controllers\PaySlipController::class, \"salaryProcessingSearch\"])->name(\"salary-processing.search_json\")->middleware([\"auth\", \"XSS\"]);
    Route::post(\"salary-processing/export\", [\App\Http\Controllers\PaySlipController::class, \"salaryProcessingExport\"])->name(\"salary-processing.export\")->middleware([\"auth\", \"XSS\"]);
    Route::post(\"salary-processing/update-status\", [\App\Http\Controllers\PaySlipController::class, \"updateSalaryProcessingStatus\"])->name(\"salary-processing.update-status\")->middleware([\"auth\", \"XSS\"]);
"; $content = str_replace("Route::resource(\"payslip\", PaySlipController::class)", "Route::resource(\"payslip\", PaySlipController::class)->middleware([\"auth\", \"XSS\"]);\n" . $routes, $content); file_put_contents($file, $content);
