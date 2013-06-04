$(document).ready(function() {

    var uploadLocked = false;

    //
    // Disable '#' Links
    //
    $('a[href="#"]').click(function(e) {
        e.preventDefault();
    });

    // ------------------------------------------------------------------

    //
    // Layout Correction for Absolute Position Items
    //
    fixMainPos();
    $(window).resize(function() {
        fixMainPos();
    });

    // ------------------------------------------------------------------

    //
    // Sidebar functionality
    //
    $('#sidebar-tab').click(function() {
        $('#sidebar-content').toggle(500);
    });

    // ------------------------------------------------------------------

    //
    // File upload button functionality
    $('#pdf-upload #pdffile-input').hide();
    $('#pdf-upload button[type=submit]').hide();
    $("#pdf-upload label[for='pdffile-input']").show();
    $("#pdf-upload label[for='pdffile-input']").addClass('btn btn-primary');

    $("#pdf-upload label[for='pdffile-input']").click(function(e) {
        e.preventDefault();

        if (uploadLocked == false) {
            $('#pdf-upload #pdffile-input').click();
        }
    });

    $('#pdf-upload #pdffile-input').bind('change dialogclose', function() {
        var fname = basename($(this).val());
        if (fname != '') {
            $('#pdf-upload').submit();
        }
    }); 

    // ------------------------------------------------------------------

    //
    // PDF Upload Functionality
    //
    $('#pdf-upload').ajaxForm({
        dataType:     'json', 
        beforeSubmit: function(arr, $form, options) {

            //Show a modal
            $('#working-modal').modal({
                keyboard: false
            });

        },        

        success: function(responseText, statusText, xhr, jq) {

            //Set the display
            $('#left.pane').html("<iframe src='" + responseText.pdfurl + "'></iframe>")

            if (responseText.txt != '') {
                $('#right.pane').html("<textarea>" + responseText.txt + "</textarea>");
            }
            else {
                $('#right.pane').html("<p class='placeholder error'>Could not Parse the Document<br/><br/>Some are simply unparsable.</p>");
            }

            //Kill the modal & hide the sidebar
            $('#working-modal').modal('hide'); 
            $('#sidebar-content').hide(500);
        },

        error: function(jqXHR, textStatus, errorThrown) {

            //Build the message
            var msg = "<h2><i class='icon-exclamation-sign'></i> Whoops! Something went wrong.</h2><ul><li>Messages Here</li></ul>"            

            //Reset the PDF side
            $('#left.pane').html("<p class='placeholder'>PDF will Appear Here</p>");

            //Reset the text side with the errors
            $('#right.pane').html("<div class='placeholder error'>" + msg + "</div>");

            //Kill the modal
            $('#working-modal').modal('hide');
        }
    });

});

// ------------------------------------------------------------------

function fixMainPos()
{
    var topHeight = $('#top').outerHeight();
    $('#main').css('top', topHeight);
}

// ------------------------------------------------------------------

function basename(path, suffix) 
{
    // http://kevin.vanzonneveld.net
    // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   improved by: Ash Searle (http://hexmen.com/blog/)
    // +   improved by: Lincoln Ramsay
    // +   improved by: djmix
    // *     example 1: basename('/www/site/home.htm', '.htm');
    // *     returns 1: 'home'
    // *     example 2: basename('ecra.php?p=1');
    // *     returns 2: 'ecra.php?p=1'
    var b = path.replace(/^.*[\/\\]/g, '');

    if (typeof(suffix) == 'string' && b.substr(b.length - suffix.length) == suffix) {
        b = b.substr(0, b.length - suffix.length);
    }

    return b;
}