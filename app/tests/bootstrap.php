<?php

/**
 * Sighthound PHPUnit Bootstrap File
 */

//List of files to ensure exist
$checkFiles['autoload'] = __DIR__ . '/../vendor/autoload.php';

//Check em
foreach($checkFiles as $file) {
    if ( ! file_exists($file)) {
        die('Install dependencies with --dev option to run test suite ($ composer.phar install --dev)' . "\n");
    }
}

//Include the Composer autoloader
$autoload = require_once $checkFiles['autoload'];

//Manually Require Some Files (may fix this later)
require_once(__DIR__ . '/XtractPDF/XtractPDFControllerTestCase.php');

/* EOF: bootstrap.php */