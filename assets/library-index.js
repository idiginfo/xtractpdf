$(document).ready(function() {

    //
    // File upload button
    //
    $('#upload-pdf button').click(function(e) {
        e.preventDefault();
        $('#upload-pdf input[name="pdffile"]').click();
    });
    $('#upload-pdf input[name="pdffile"]').bind('change dialogclose', function() {
        var fname = basename($(this).val());
        if (fname != '') {
            $('#upload-pdf').submit();
        }                
    });

    //
    // File upload submit
    //
    $('#upload-pdf').ajaxForm({
        dataType:     'json',
        beforeSubmit: function(arr, $form, options) {
            $('#upload-pdf button').attr('disabled', 'disabled').removeClass('btn-primary').addClass('btn-danger');
            $('#upload-pdf button span').text("Processing (this will take several moments)");
            $('#upload-pdf button i').removeClass('icon-upload-alt').addClass('icon-spinner icon-spin');
        },
        success: function(responseText, statusText, xhr, jq) {
            alert(statusText);
        },
        error: function(jqXHR, textStatus, errorThrown) {
            alert(textStatus);
        },
        complete: function() {
            $('#upload-pdf button').removeAttr('disabled').removeClass('btn-danger').addClass('btn-primary');
            $('#upload-pdf button span').text("Upload PDF");
            $('#upload-pdf button i').removeClass('icon-spinner icon-spin').addClass('icon-upload-alt');
        }
    });

});

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