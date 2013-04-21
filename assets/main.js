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
    // Setting dialog
    //
    $('#settings-toggle').click(function(e) {
        e.preventDefault();

        var pos = $('#settings-toggle').position().top + $('#settings-toggle').outerHeight();
        $('#settings-dialog').css({ top: pos });
        $('#settings-dialog').slideToggle('fast');
    });

    // ------------------------------------------------------------------

    //
    // File upload button functionality
    //
    $('#top h3.noscript').remove();
    $('#pdf-upload').show();
    $("#settings-toggle").show();
    $('#pdf-upload #pdffile-input').hide();
    $('#pdf-upload button[type=submit]').hide();
    $("#pdf-upload label[for='pdffile-input']").show();
    $("#pdf-upload label[for='pdffile-input']").addClass('btn');
    unlockUploadButton();

    $("#pdf-upload label[for='pdffile-input']").click(function(e) {
        e.preventDefault();

        if (uploadLocked == false) {
            $('#pdf-upload #pdffile-input').click();
        }
    });

    $('#pdf-upload #pdffile-input').bind('change dialogclose', function() {
        var fname = basename($(this).val());
        $('#pdf-upload').submit();
    }); 

    // ------------------------------------------------------------------

    //
    // PDF Upload Functionality
    //
    $('#pdf-upload').ajaxForm({
        dataType:     'json', 
        beforeSubmit: function(arr, $form, options) {

            //Add engine selection to the form
            arr.push({ name: 'engine', value: $('input[name=engine]:checked').attr('value') });

            //Hide the settings dialog if it is open
            $('#settings-dialog').slideUp('fast');

            //Display loading icons in the textareas
            $("#left.pane").html("<p class='placeholder'>Converting <i class='icon-spinner icon-spin'></i><span class='patience'>(please be patient - this can take a moment)</span></p>");
            $("#right.pane").html("<p class='placeholder'>Converting <i class='icon-spinner icon-spin'></i><span class='patience'>(please be patient - this can take a moment)</span></p>");

            //Disable the upload button
            lockUploadButton();
        },        

        success: function(responseText, statusText, xhr, jq) {

            unlockUploadButton("Convert another PDF");

            $('#left.pane').html("<iframe src='" + responseText.pdfurl + "'></iframe>")

            if (responseText.txt != '') {
                $('#right.pane').html("<textarea>" + responseText.txt + "</textarea>");
            }
            else {
                $('#right.pane').html("<p class='placeholder error'>Could not Parse the Document<br/><br/>Some are simply unparsable.</p>");
            }
        },

        error: function(jqXHR, textStatus, errorThrown) {

            //Kill the loading dialog
            unlockUploadButton("Try Again");
            var resp = $.parseJSON(jqXHR.responseText);

            //Build the message
            var msg = "<h2><i class='icon-exclamation-sign'></i> Whoops! Something went wrong.</h2><ul>";
            $.each(resp.messages, function(k,v) {
                msg = msg + "<li>" + v + "</li>";
            });
            msg = msg + "</ul>";

            //Reset the PDF side
            $('#left.pane').html("<p class='placeholder'>PDF will Appear Here</p>");

            //Reset the text side with the errors
            $('#right.pane').html("<div class='placeholder error'>" + msg + "</div>");

        }
    });

});

// ------------------------------------------------------------------

function unlockUploadButton(text)
{
    if (typeof(text) == undefined) {
        var text = "Convert a PDF";
    }

    var btnObj = $("#pdf-upload label[for='pdffile-input']");

    btnObj.css('cursor', 'pointer');
    btnObj.addClass('btn-primary btn-ready');
    btnObj.removeClass('btn-danger btn-locked disabled');
    btnObj.html(text);

    uploadLocked = false;
}

// ------------------------------------------------------------------

function lockUploadButton()
{
    uploadLocked = true;

    var btnObj = $("#pdf-upload label[for='pdffile-input']");

    btnObj.html("Converting&nbsp;&nbsp;<i class='icon-spinner icon-spin'></i>");
    btnObj.css('cursor', 'auto');
    btnObj.addClass('btn-danger btn-locked disabled');
    btnObj.removeClass('btn-primary btn-ready');
}

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