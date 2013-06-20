
/*
 * Javascript for library-single.html.twig
 */

// ------------------------------------------------------------------

//
// Content Item Object
//
function ContentItem(data) {
    this.content = ko.observable(data.content);
    this.type = (data.type !== undefined) ? ko.observable(data.type) : null;
}

// ------------------------------------------------------------------

//
// Biblio Meta Item Object
//
function BiblioMetaItem(name, value, disp, placeholder) {
    this.name        = name;
    this.value       = ko.observable(value);
    this.disp        = disp;
    this.placeholder = placeholder;
}

// ------------------------------------------------------------------

//
// Define a ViewModel for a single document
//
function DocumentViewModel(docUrl) {
    
    var self = this;

    //Meta Data
    self.docId         = '';
    self.availSecTypes = ko.observableArray([]);
    self.isComplete    = ko.observable(false);

    //Content Data
    self.biblioMeta = ko.observableArray([]);
    self.authors    = ko.observableArray([]);
    self.abstract   = ko.observableArray([]);
    self.content    = ko.observableArray([]);
    self.citations  = ko.observableArray([]);

    //Operations
    self.addAuthor = function(index, data, event) {
        if (index !== 'undefined') {
            self.authors.splice(index+1, 0, {name: ''});
        }
        else {
            self.authors.push({ name: '' });    
        } 
    }

    self.addAbstractSection = function(index, data, event) {
        if (index !== 'undefined') {
            self.abstract.splice(index+1, 0, new ContentItem({}));
        }
        else {
            self.abstract.push(new ContentItem({}));
        }
    }

    self.addContentSection = function(index, data, event) {
        if (index !== 'undefined') {
            self.content.splice(index+1, 0, new ContentItem({}));
        }
        else {
            self.content.push(new ContentItem({}));
        }
    }

    self.addCitation = function(index, data, event) {
        if (index !== 'undefined') {
            self.citations.splice(index+1, 0, new ContentItem({}));
        }
        else {
            self.citations.push(new ContentItem({}));
        }
    }

    //Remove an item from an array
    self.removeItem = function(arr, data, event) {
        arr.remove(data);
    }

    //Mark document complete
    self.toggleMarkedComplete = function() {
        this.isComplete(this.isComplete() == true ? false: true);
    }
}

// ------------------------------------------------------------------

//
// Doc Model Builder
//
function buildDocModel(docUrl)
{
    var docViewModel = new DocumentViewModel();

    $.ajax({
        url:      docUrl,
        type:     "get",
        data:     { disp_opts: 'true'},
        async:    false,  //IMPORTANT!! Per: http://goo.gl/Xv6VY
        dataType: "json",
        success: function(serverData) {
    
            var doc      = serverData.document;
            var dispOpts = serverData.dispOptions;

            //Basic Information
            docViewModel.docId = doc.uniqId;
            docViewModel.isComplete(doc.isComplete);

            //Biblio Meta
            $.each(doc.biblioMeta, function (k, v) {
                var dispName = dispOpts.biblioMetaDisp[k].dispName;
                var dispPH   = dispOpts.biblioMetaDisp[k].dispPlaceholder;
                docViewModel.biblioMeta.push(new BiblioMetaItem(k, v, dispName, dispPH));
            });

            //Authors
            $.each(doc.authors, function(k, v) {
                docViewModel.authors.push(v);
            });

            //Abstract
            $.each(doc.abstract.sections, function(k, v) {
                docViewModel.abstract.push(new ContentItem(v));
            });

            //Content
            $.each(doc.content.sections, function(k, v) {
                docViewModel.content.push(new ContentItem(v));
            });

            //Citations
            $.each(doc.citations, function(k, v) {
                docViewModel.citations.push(new ContentItem(v));
            });

            //Content Types
            $.each(dispOpts.availSecTypes, function(k, v) {
                docViewModel.availSecTypes.push({ type: k, dispName: v });
            });

            //Remove main loading indicator
            if ($('#main-loading').length > 0) {
                $('#main-loading').remove();
            }            
        }
    });

    return docViewModel;
}

// ------------------------------------------------------------------

//
// Run!!
//
$(document).ready(function() {

    //
    // Init
    //

    //Get the URL to the document info from the DOM
    var docUrl = $('#workform').data('docurl');

    //Build it!
    docViewModel = buildDocModel(docUrl);

    //Apply Knockout Bindings
    ko.applyBindings(docViewModel);

    //Start the persist timer
    var dp = new DocumentPersister(docViewModel, docUrl, true);    

    //Manual save button
    $('#save-button').click(function() {
        dp.updateDocument(ko.toJSON(docViewModel), true);
    });

    //Control-S to trigger manual save
    document.addEventListener("keydown", function(e) {
      if (e.keyCode == 83 && (navigator.platform.match("Mac") ? e.metaKey : e.ctrlKey)) {
        e.preventDefault();
        dp.updateDocument(ko.toJSON(docViewModel), true);
      }
    }, false);    

    //
    // Autosize textareas
    //
    $('#right-workform .input-item textarea').autosize();

    //
    // Handle Paste Events
    //
    $('#right-workform').on('paste', '.input-item textarea', function(e) {
        var element = this;
        $(element).autosize(); //possibly re-apply autosize - doesn't seem to throw any errors
        setTimeout(function () {
            var text = $(element).val();
            text = text.replace(/(\r\n|\n|\r)/gm," ");
            text = text.replace(/\s{2,}/g," ");
            $(element).val(text).trigger('autosize');
            $(element).trigger('change');
        }, 100);
    });    

    $('#right-workform').on('change', '.input-item :input', function(e) {
        $(this).parents('li.form-row').addClass('change-pending');
    });

});

// ------------------------------------------------------------------

//
// Document Persister is intelligently aware of document clean/dirty state
//
function DocumentPersister(docViewModel, docUrl, autoPersist) {

    var self = this;

    //Data
    self.lastCleanState = ko.toJSON(docViewModel);

    //Functions
    self.updateDocument = function(docData, force) {

        if (self.lastCleanState.localeCompare(docData) != 0 || force == true) {
            $.ajax({
                url:      docUrl,
                type:     "POST",
                data:     { document: docData },
                dataType: 'json', 
                beforeSend: function() {
                    $('#save-button').attr('disabled', 'disabled').addClass('btn-info');
                    $('#save-button').find('i').removeClass('').addClass('icon-spinner icon-spin');
                },
                complete: function() {
                    $('#save-button').removeAttr('disabled').removeClass('btn-info');
                    $('#save-button').find('i').removeClass('icon-spinner icon-spin').addClass('icon-save');
                },
                success: function(responseData) {
                    self.lastCleanState = docData;  
                    debug("Persisted");
                }
            });            
        }
        else {
            debug("Skipped persist");
        }

        $('li.form-row').removeClass('change-pending');
    }

    self.startTimer = function() {
    
        //Hack - Reset self.lastCleanState after Knockout has done its work
        self.lastCleanState = ko.toJSON(docViewModel);

        window.setInterval(function() {
            self.updateDocument(ko.toJSON(docViewModel));
        }, 5000);
    }

    //Init
    if (autoPersist == true) {
        setTimeout(function() { self.startTimer() }, 5000);
        console.log("Started timer");
    }
}