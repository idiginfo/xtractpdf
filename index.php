<?php

namespace XtractPDF;

// ------------------------------------------------------------------

require(__DIR__ . '/app/vendor/autoload.php');

App::main(isset($dev) ? App::DEVELOP : App::PRODUCTION);