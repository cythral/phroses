var jQuery = $ = require('jquery'),
    Phroses = require("phroses"),
    controller = {};

jQuery.fn.shake = function(interval,distance,times){
    interval = typeof interval == "undefined" ? 100 : interval;
    distance = typeof distance == "undefined" ? 10 : distance;
    times = typeof times == "undefined" ? 3 : times;
    var jTarget = $(this);
    jTarget.css('position','relative');
    for(var iter=0;iter<(times+1);iter++){
        jTarget.animate({ left: ((iter%2==0 ? distance : distance*-1))}, interval);
    }
    return jTarget.animate({ left: 0},interval);
};

console.log("-== Phroses Initialized ==-");
controller = new Phroses();

$(document).on("click", ".jlink", function() {
    document.location = $(this).data("href");
});  

if(!$("#phr-admin-page").val()) {

    
    var content = $("body").html();
    $("body").html('<div id="phr-container">'+content+"</div>");

    $.post(controller.adminuri+"/api/pst", { uri : window.location.pathname }, function(data) {

        $("body").append(data.content);
        
        Phroses.setupButtons();

        // setup editor
        var editor = new Phroses.editor;

        $("#pst-es-title").change(function() { $("#pst-es").submit() });
        
    
        /**
         * Deletion Screen
         */
        Phroses.utils.formify({
            selector: "#pst-ds",
            success: function(data) {
                location.reload();
            } 
        });
        
        /**
         * Move Screen
         */
        Phroses.utils.formify({
            selector: "#pst-ms",
            collect : function() {
                var data = $(this).serializeArray();
                data.push({ name : "id", value : $("#pid").val() });
                return data;
            },
            success: function(data) {
                history.replaceState({}, document.title, $("#puri").val());
                $("#pst-ms").fadeOut();
                Phroses.utils.displaySaved();
            },

            failure: function(data) {
                data = data.responseJSON;
                Phroses.genericError(Phroses.errors["pst-ms"][data.error] || Phroses.errors[data.error] || "An unknown error occurred.");
            }
        });
        
        /**
         * New Page Screen
         */
        Phroses.utils.formify({
            selector: "#pst-ns",
            hash: "#new",
            hashreqclass: {
                element: "#pst",
                class : "new"
            },
            success: function(data) {
                var title = $("#pst-ns [name=title]").val();

                $("#pid").val(data.id);
                $("#pst").removeClass("new");
                $("#pst").addClass("existing");
                $("#phr-container").html(data.content);
                $("#mode-content").html(data.typefields);
                
                $("#pst-es input[name=title]").val($("#pst-ns input[name=title]").val());
                $("#pst-es-type").val($("#pst-ns select").val());
                editor.aceify();
                document.title = title;
    
                $("#pst-ns").fadeOut(function() {
                    $("#pst-ns")[0].reset();
                });
            }
        });

        /**
         * Public / Private Switcher
         */
        Phroses.utils.formify({
            selector: "#pst-vis input",
            action: "change",
            collect: function() {
                return {
                    "id" : $("#pid").val(),
                    "public" : ($(this).is(":checked") === true) ? 1 : 0
                };
            },
            success: function() {}
        });
    });
    

} else {

    Phroses.setupButtons();

    /**
     * Login Screen
     */
    Phroses.utils.formify({
        selector: "#phroses-login",
        success: function() {
            $("#phroses-login").animate({width:0}, function() {
                location.reload();
            });
        },
        failure: function() {
            $("#phroses-login").shake();
        }
    });


    /**
     * Type switcher on /admin/pages
     */
    Phroses.utils.formify({
        selector: ".pageman-select",
        action: "change",
        collect: function() {
            return { type : $(this).val(), id : $(this).parent().parent().data("id"), nocontent : true };
        },
        success: function() {
            var parent = $(this).parent().parent();
            parent.addClass("saved");
            setTimeout(function() { parent.removeClass("saved"); }, 1000);
        }
    });
    $(".pageman-select").click(function(e) { e.preventDefault(); e.stopImmediatePropagation(); });

    /**
     * Page deletion on /admin/pages
     */
    Phroses.utils.formify({
        selector: ".pageman-delete",
        action: "click",
        collect : function() { return null; },
        success: function() {
            $(this).parent().parent().slideUp(function() {
                $(this).remove();
            });
        }
    });

    /**
     * Theme selector on /admin
     */
    Phroses.utils.formify({
        selector: "#theme-selector",
        action: "change",
        collect: function() { return { theme : $(this).val() }; },
        success: function() {
            Phroses.utils.displaySaved();
            
            setTimeout(function() {
                location.reload();
            }, 2000); 
        }
    });

    /**
     * Upgrade screen
     * uses EventSource to track progress, so formify doesnt work here.
     */
    $("#phr-upgrade-screen").submit(function(e) {
        e.preventDefault();
        $(this).fadeIn();
        
        var ev = new EventSource(controller.adminuri+"/update/start");
        ev.addEventListener("progress", function(e) {
            console.log(e.data);
            $(".phr-progress-bar").css({width: JSON.parse(e.data).progress+"%" });
            
            // completion
            if(JSON.parse(e.data).progress === 100) {
                ev.close();
                
                $("#phr-upgrade-screen .phr-progress").addClass("done");
                $("#phr-upgrade-screen h1").fadeOut(function() {
                    $(this).html("Phroses updated to "+JSON.parse(e.data).version);
                    $(this).fadeIn();
                });
                
                setTimeout(function() {
                    location.reload();
                }, 5000);
            }
        });

        ev.addEventListener("error", function(e) {
            ev.close();
            console.log(e.data);
            var data = JSON.parse(e.data);

            var extra = "";
            if(data.error == "write") extra = "<br>debug: operation " + data.action + " on file " + data.file;

            $(".phr-progress-error").html(Phroses.errors[data.error] + extra);
            $(".phr-progress").addClass("error");
        });
    });

    $(".phr-update-icon").click(function() {
        $(this).addClass("done");
        setTimeout(function() {
            $("#phr-upgrade-screen").submit();
        }, 1000);
    });
        
    $("#phr-new-page").submit(function(e) {
        document.location = $("#phr-new-page input").val() + "#new";
    });
    

    Phroses.utils.formify({
        selector : "#phroses_site_creds",
        success : function() {
            Phroses.utils.displaySaved();
            $("#phroses_site_creds input:not([name='username'])").val('');
        },
        failure: function(data) {
            data = data.responseJSON;

            $("#error").html({ 
                "username" : "Please enter a value for the username field.",
                "old" : "The value for the old password was incorrect.",
                "repeat" : "The two new passwords do not match."
            }[data.field] || Phroses.errors[data.error]);

            $("#error").addClass("active");

            setTimeout(function() {
                $("#error").removeClass("active");
            }, 5000);
        }
    });

    Phroses.utils.formify({
        selector: ".admin-uri input",
        action: "change",
        collect: function() {
            return { uri : $(this).val() };
        },
        success: function() {
            Phroses.utils.displaySaved();
            var olduri = $(this).data("initial-value"), newuri = $(this).val();

            $(".adminlink").each(function() {
                $(this).attr("href", $(this).attr("href").replace(new RegExp("^"+olduri, 'g'), newuri));
            });

            history.replaceState({}, document.title, $(this).val());
            $(this).data("initial-value", $(this).val());
        },
        failure: function(data) {
            $(this).val($(this).data("initial-value"));
            data = data.responseJSON;

            Phroses.utils.genericError(Phroses.errors.admin[data.error] || Phroses.errors[data.error]);
        }
    });

    Phroses.utils.formify({
        selector: ".maintenance-select select",
        action: "change",
        collect: function() { return { "maintenance" : $(this).val() } },
        success: Phroses.utils.displaySaved
    });
    
    Phroses.utils.formify({
        selector: ".site-namer input",
        action: "change",
        collect: function() { return { "name" : $(this).val() } },
        success: Phroses.utils.displaySaved
    });
    
    Phroses.utils.formify({
        selector: ".siteurl-changer input",
        action: "change",
        collect: function() { return { "url" : $(this).val() } },
        success: function() {
            Phroses.utils.displaySaved();
            var url = $(this).val();
            
            setTimeout(function() {
                window.location.href = "http://" + url + window.location.pathname;
            }, 2000);
        }
    });
}