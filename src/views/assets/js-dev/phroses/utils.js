var $ = require("jquery"), 
    errors = require("./errors"),
    utils = {};


utils.displaySaved = function() {
    $("#saved").addClass("active");
    setTimeout(function() {
        $("#saved").removeClass("active");
    }, 5000);
};

utils.genericError = function(message) {
    if(typeof message === 'object') {
        if(message.responseJSON) message = message.responseJSON;

        if(message.error && Object.keys(errors).includes(message.error)) message = errors[message.error];
        else message = "Unknown Error: "+JSON.stringify(message);
    }

    $("body").append('<div id="phroses-error" class="phroses-generic-error screen dflts"><h1>Error:</h1><p>'+message+'</p></div>');
    $("#phroses-error").fadeIn();
    
    setTimeout(function() {
        $("#phroses-error").fadeOut(400, function() {
            $(this).remove();
        });
    }, 5000);
    
};

utils.formify = function(options) {
    if(options.hash) {
        if(window.location.hash === options.hash) {
            if(options.hashreqclass) {
                if($(options.hashreqclass.element).attr("class") === options.hashreqclass.class) {
                    $(options.selector).fadeIn();
                }
            } else {
                $(options.selector).fadeIn();
            }
        }
    }

    $(document).on(options.action || "submit", options.selector, function(e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        var data = (options.collect || function() { return $(this).serializeArray(); }).bind(this)();
        

        $.ajax({
            url : $(this).data("url"),
            data : data,
            method : $(this).data("method")
        })
        .then(options.success.bind(this))
        .catch((options.failure) ? options.failure.bind(this) : utils.genericError);
    });

    console.log("Formified element <"+options.selector+">");
};

module.exports = utils; 