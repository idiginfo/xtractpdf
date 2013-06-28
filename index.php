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

namespace XtractPDF;

// ------------------------------------------------------------------

if ( ! is_readable(__DIR__ . '/app/vendor/autoload.php')) {
    die("Incomplete installation");
}

require(__DIR__ . '/app/vendor/autoload.php');

App::main(isset($dev) ? App::DEVELOP : App::PRODUCTION);