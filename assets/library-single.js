
//Define a ViewModel for a single document
var DocumentViewModel = {
    
    //Data
    biblioMeta = ko.observableArray();
    authors    = ko.observableArray();
    abstract   = ko.observableArray();
    content    = ko.observableArray();
    citations  = ko.observableArray();
};

//Apply the bindings
ko.applyBindings(DocumentViewModel);