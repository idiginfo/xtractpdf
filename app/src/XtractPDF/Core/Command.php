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

namespace XtractPDF\Core; 

use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Silex\Application;

/**
 * Abstract Base Command
 */
abstract class Command extends SymfonyCommand
{
    /**
     * Initialize the Command
     *
     * @param Silex\Application
     */
    public function init(Application $app)
    {
        //pass - meant to be overriden
    }
}

/* EOF: Command.php */