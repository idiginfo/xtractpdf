$(document).ready(function() {

    // ------------------------------------------------------------------

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

        if ( ! $(this).hasClass('locked')) {
            $('#sidebar-content').toggle(500);
        }
    });

    // ------------------------------------------------------------------

    //
    // Click links on sidebar
    //
    $('#sidebar-pdf-list').on('click', 'a.doc-link', function(e) {

        //Don't go to the link
        e.preventDefault();

        var wsUrl = $(this).attr('href');

        //Derive pdf view URL from workspace URL
        //This is a cruddy hack.  Consider improving how this works...
        pdfUrl = wsUrl.replace('/workspace/', '/pdf/');

        $(this).parent('li').addClass('active').siblings('li').removeClass('active');

        //Invoke workspaces
        loadPdfView(pdfUrl);
        loadWorkspace(wsUrl);

    });

    // ------------------------------------------------------------------

    //
    // File upload button
    //
    $('#pdf-upload #pdffile-input').hide();
    $('#pdf-upload button[type=submit]').hide();
    $("#pdf-upload label[for='pdffile-input']").show();
    $("#pdf-upload label[for='pdffile-input']").addClass('btn btn-primary');

    $("#pdf-upload label[for='pdffile-input']").click(function(e) {
        e.preventDefault();

        if ( ! $('#sidebar-tab').hasClass('locked')) {
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

            //Hide and Lock the sidebar
            $('#sidebar-content').hide(500);
            $('#sidebar-tab').addClass('locked');

            //Set the left pane to a message
            $('#left.pane').html("<p class='placeholder working'><i class='icon-spinner icon-spin'></i> Uploading PDF</p>");
        },        

        success: function(responseText, statusText, xhr, jq) {

            //Unlock the sidebar
            $('#sidebar-tab').removeClass('locked');

            //Load PDF Preview
            loadPdfView(responseText.pdfurl);

            //Add to list on sidebar
            if (responseText.isNew) {
                $('#sidebar-pdf-list').prepend(responseText.listHtml);
                $('#sidebar-pdf-list li:first-child').addClass('active').siblings('li').removeClass('active');
            }

            //Load Workspace
            loadWorkspace(responseText.wsurl);
        },

        error: function(jqXHR, textStatus, errorThrown) {

            //Unlock the sidebar
            $('#sidebar-tab').removeClass('locked');

            //Build the message
            var msg = "<h2><i class='icon-exclamation-sign'></i> Whoops! Something went wrong.</h2><ul><li>Messages Here</li></ul>";        

            //Reset the text side with the errors
            $('#left.pane').html("<div class='placeholder error'>" + msg + "</div>");
        }
    });

    // ------------------------------------------------------------------

    //
    // Workspace Biblio-Metadata Update
    //
    //$('#right.pane').on('.ws-form :input').change(function() {
    //});
});

// ------------------------------------------------------------------

//
// PDF Preview Loader
//
function loadPdfView(pdfUrl)
{
    //Just hide the sidebar
    $('#sidebar-content').hide(500);

    //Loading message
    $('#left.pane').html("<iframe src='" + pdfUrl + "'></iframe>");
}


// ------------------------------------------------------------------

//
// Workspace Loader
//
function loadWorkspace(wsUrl)
{
    //Do an ajax request
    $.ajax(
        wsUrl,
        {
            beforeSend: function() {

                //Hide and Lock the sidebar
                $('#sidebar-content').hide(500);
                $('#sidebar-tab').addClass('locked');

                //Loading message
                $('#right.pane').html("<p class='placeholder working'><i class='icon-spinner icon-spin'></i> Converting and Rendering Workspace<br />(this may take a moment)</p>");

            },
            success: function(data, textStatus, jqXHR) {

                //Unlock the sidebar
                $('#sidebar-tab').removeClass('locked');

                //Load the HTML content from the request
                $('#right.pane').html(data);
            },
            error: function(jqXHR, textStatus, errorThrown) {

                //Unlock the sidebar
                $('#sidebar-tab').removeClass('locked');

                //Error Message
                $('#right.pane').html("<p class='placeholder error'><i class='icon-exclamation-sign'></i> Converting Failed<br/>Perhaps add some notes here</p>");                
            }
        }
    );    
}

// ------------------------------------------------------------------

//
// Fix position of main workspace
//
function fixMainPos()
{
    var topHeight = $('#top').outerHeight();
    $('#main').css('top', topHeight);
}

// ------------------------------------------------------------------

//
// Get file basename
//
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