var $ = require('jquery');

function Phroses() {
    this.adminuri = $("#phroses-script").data("adminuri");
};

Phroses.errors = require("./errors");
Phroses.editor = require("./editor");
Phroses.utils = require("./utils");

Phroses.setupButtons = function() {
    $(".pst_btn, .phr-btn").click(function(e) {
        e.preventDefault();
        
        if($(this).data("target")) {
            if($(this).data("scroll") === "off") {
                $("body").addClass("noscroll");
            } else if($(this).data("scroll") === "on") {
                $("body").removeClass("noscroll");
            }

            $("#"+$(this).data("target"))[$(this).data("action")]();            
        }

    });
    
    $(".pst_btn").on("dragstart", function() { return false; });	
};

Phroses.setupScreens = function() {
    $(".screen").keydown(function(e) {
        if($(this).is(":visible")) {
            if(e.which === 13) {
                $(this).find(".screen-enter")[0].click();
            }
            if(e.which === 27) {
                $(this).find(".screen-escape")[0].click();
            }
        }
    });
}


module.exports = Phroses;
 