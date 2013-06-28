  
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

$(document).ready(function() {

    //
    // Disable '#' Links
    //
    $('a[href="#"]').click(function(e) {
        e.preventDefault();
    });
});

// ------------------------------------------------------------------

//
// Sets a Notice
//
function setNotice(message, alertType)
{
    var noticeClass = (typeof alertType === 'undefined') ? 'alert-' + alertType : 'alert-info';

    if ($('#notices').length > 0) {
        $('#notices').append('<div class="alert alert-block '+ noticeClass +'">'+ message + '<button type="button" class="close" data-dismiss="alert">×</button></div>');
    }
    else {
        debug("Couldn't set notice because there is no notices container in the DOM: '" + message + "'");
    }
}

// ------------------------------------------------------------------

function debug()
{
    if (DEBUG === true) {
        console.log(arguments);
    }
}