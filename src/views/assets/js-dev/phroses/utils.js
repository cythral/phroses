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


utils.requiredFields = function(el) {
    var elements = el.find("[required]").toArray();

    for(var element in elements) {
        element = $(elements[element]);

        if(!element.val()) {
            element.parent(".c.form_icfix").addClass("missing");

            element.one("focus", function() {
                $(this).parent(".c.form_icfix").removeClass("missing");
            });
            return false; 
        }
    }

    return true;
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

        if(!utils.requiredFields($(this))) return;

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


utils.getParameters = function() {
    var src = document.currentScript.getAttribute("src"),
        qs = src.substring(src.indexOf("?") + 1),
        params = [],
        parts = qs.split("&");

    for(var part in parts) {
        part = parts[part];
        params[part.substring(0, part.indexOf("="))] = part.substring(part.indexOf("=") + 1);
    }

    return params;
}

module.exports = utils; 