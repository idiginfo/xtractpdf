
//
// A document item for display in the list
//
function DocumentItem(data, docUrl, highlight) {

    this.id        = data.uniqId;
    this.title     = (data.biblioMeta.title != '') ? data.biblioMeta.title : 'Untitled Document';
    this.uploaded  = data.created.date;
    this.modified  = data.modified.date;
    this.isDone    = data.isComplete;
    this.url       = docUrl;
    this.highlight = (highlight !== undefined) ? highlight : false;

    this.dispId = ko.computed(function() {
        return (this.id.length > 7) ? "\u2026" + this.id.substr(-7) : this.id;
    }, this);
}

// ------------------------------------------------------------------

//
// Define a ViewModel for a single document
//
function DocumentListViewModel() {
    
    var self = this;

    self.docList       = ko.observableArray([]);
    self.totalDocCount = ko.observable(0);
}

// ------------------------------------------------------------------

function buildDocListModel(docListUrl) {

    var docListViewModel = new DocumentListViewModel();

    $.ajax({
        url:      docListUrl,
        type:     "get",
        async:    false,  //IMPORTANT!! Per: http://goo.gl/Xv6VY
        dataType: "json",
        success: function(serverData) {

            docListViewModel.totalDocCount(serverData.docs.length);

            $.each(serverData.docs, function(k, v) {
                docListViewModel.docList.push(new DocumentItem(v, docListUrl + '/' + v.uniqId));
            });

            //Remove main loading indicator
            if ($('#main-loading').length > 0) {
                $('#main-loading').remove();
            }
        }    
    });

    return docListViewModel;
}

// ------------------------------------------------------------------

$(document).ready(function() {

    //
    //Instantiate the table list and load the data
    //
    
    //Build it!
    docListViewModel = buildDocListModel(doclist_url);

    //Apply bindings
    ko.applyBindings(docListViewModel);

    // --------------------------------------------------------------

    //
    // File upload button
    //
    $('.upload-pdf-submit').click(function(e) {
        e.preventDefault();
        $('#upload-pdf input[name="pdffile"]').click();
    });
    $('#upload-pdf input[name="pdffile"]').bind('change dialogclose', function() {
        var fname = basename($(this).val());
        if (fname != '') {
            $('#upload-pdf').submit();
        }                
    });

    // --------------------------------------------------------------

    //
    // File upload submit
    //
    $('#upload-pdf').ajaxForm({
        dataType:     'json',
        beforeSubmit: function(arr, $form, options) {
            $('.upload-pdf-submit').attr('disabled', 'disabled').removeClass('btn-primary').addClass('btn-danger');
            $('.upload-pdf-submit span').text("Processing (this will take several moments)");
            $('.upload-pdf-submit i').removeClass('icon-upload-alt').addClass('icon-spinner icon-spin');
        },
        success: function(serverData, statusText, xhr, jq) {
        
            //Add the item to the table as new item and increment total document count
            if (serverData.new == true) {
                docListViewModel.docList.unshift(new DocumentItem(serverData.doc, serverData.url, true));
                setNotice("Uploaded the document", 'success');
            }
            else { //set notice that document exists, and provide a link to it.
                setNotice("That document already exists.  <a href='" + serverData.url + "' title='Edit the Document'></a>");
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.log(jqXHR);
        },
        complete: function() {
            $('.upload-pdf-submit').removeAttr('disabled').removeClass('btn-danger').addClass('btn-primary');
            $('.upload-pdf-submit span').text("Upload PDF");
            $('.upload-pdf-submit i').removeClass('icon-spinner icon-spin').addClass('icon-upload-alt');
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