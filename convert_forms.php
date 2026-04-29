<?php

/**
 * Script to convert LaravelCollective HTML forms to Spatie Laravel HTML
 */

$bladeDirectory = 'resources/views';
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($bladeDirectory)
);

$files = [];
foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $files[] = $file->getPathname();
    }
}

$conversions = [
    // Form opening
    'Form::open(' => '<form',
    'Form::model(' => '<form',
    'Form::close()' => '</form>',
    'Form::label(' => '<label',
    'Form::text(' => '<input type="text"',
    'Form::email(' => '<input type="email"',
    'Form::password(' => '<input type="password"',
    'Form::number(' => '<input type="number"',
    'Form::date(' => '<input type="date"',
    'Form::datetime(' => '<input type="datetime-local"',
    'Form::time(' => '<input type="time"',
    'Form::url(' => '<input type="url"',
    'Form::tel(' => '<input type="tel"',
    'Form::search(' => '<input type="search"',
    'Form::file(' => '<input type="file"',
    'Form::textarea(' => '<textarea',
    'Form::select(' => '<select',
    'Form::selectRange(' => '<select',
    'Form::selectYear(' => '<select',
    'Form::selectMonth(' => '<select',
    'Form::checkbox(' => '<input type="checkbox"',
    'Form::radio(' => '<input type="radio"',
    'Form::submit(' => '<button type="submit"',
    'Form::button(' => '<button type="button"',
    'Form::reset(' => '<button type="reset"',
    'Form::hidden(' => '<input type="hidden"',
    'Form::token()' => '<input type="hidden" name="_token" value="' . csrf_token() . '"',
];

foreach ($files as $file) {
    $content = file_get_contents($file);
    $originalContent = $content;
    
    foreach ($conversions as $from => $to) {
        $content = str_replace($from, $to, $content);
    }
    
    if ($content !== $originalContent) {
        echo "Updated: $file\n";
        file_put_contents($file, $content);
    }
}

echo "Form conversion completed!\n";
