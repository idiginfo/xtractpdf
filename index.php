<?php

namespace XtractPDF;

// ------------------------------------------------------------------

if ( ! is_readable(__DIR__ . '/app/vendor/autoload.php')) {
    die("Incomplete installation");
}

require(__DIR__ . '/app/vendor/autoload.php');

App::main(isset($dev) ? App::DEVELOP : App::PRODUCTION);