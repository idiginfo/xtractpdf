<?php
  
/**
 *   XtractPDF - A PDF Content Extraction and Curation Tool
 *
 *   This program is free software under the GNU General Public License (v2)
 *   See LICENSE.md for a complete copy of the license
 *
 * @package     XtractPDF
 * @author      Florida State University iDigInfo (http://idiginfo.org)
 * @copyright   Copyright (C) 2013 Florida State University (http://fsu.edu)
 * @license     http://www.gnu.org/licenses/gpl-2.0.txt
 * @link        http://idiginfo.org
 */

// ------------------------------------------------------------------

/**
 * XtractPDF PHPUnit Bootstrap File
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

/* EOF: bootstrap.php */