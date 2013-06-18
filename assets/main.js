$(document).ready(function() {

    //
    // Disable '#' Links
    //
    $('a[href="#"]').click(function(e) {
        e.preventDefault();
    });

});
