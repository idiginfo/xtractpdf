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
    var noticeClass = (alertType === undefined) ? 'alert-' + alertType : 'alert-info';

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