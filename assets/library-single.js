
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



// ------------------------------------------------------------------

//
// Define a ViewModel for a single document
//
function DocumentViewModel(docUrl) {
    
    var self = this;

    //Meta Data
    self.docId         = '';
    self.availSecTypes = ko.observableArray([]);

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
}

// ------------------------------------------------------------------

//
// Doc Model Builder
//
function buildDocModel(docUrl)
{
    var docViewModel = new DocumentViewModel();

    $.getJSON(docUrl, { disp_opts: 'true' }, function(serverData) {
        
        var doc      = serverData.document;
        var dispOpts = serverData.dispOptions;

        //ID
        docViewModel.docId = doc.uniqId;

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
            docViewModel.availSecTypes.push({ slug: k, name: v });
        });
    });

    return docViewModel;
}

// ------------------------------------------------------------------

//
// Run!!
//
$(document).ready(function() {

    //Get the URL to the document info from the DOM
    var docUrl = $('#workform').data('docurl');

    //Build it!
    var docViewModel = buildDocModel(docUrl);

    //Apply Knockout Bindings
    ko.applyBindings(docViewModel);

    //Start the persist timer
    var dp = new DocumentPersister(docViewModel, docUrl, true);
    
});

// ------------------------------------------------------------------

function DocumentPersister(docViewModel, docUrl, autoPersist) {

    var self = this;

    //Data
    self.lastCleanState = ko.toJSON(docViewModel);

    //Functions
    self.updateDocument = function(jsonData, force) {

        if (self.lastCleanState.localeCompare(jsonData) != 0 || force == true) {
            $.ajax({
                url:      docUrl,
                type:     "POST",
                data:     jsonData,
                dataType: 'json', 
                success: function(responseData) {
                    self.lastCleanState = jsonData;  
                    console.log("Persisted");              
                }
            });            
        }
        else {
            console.log("Skipped persist");
        }

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