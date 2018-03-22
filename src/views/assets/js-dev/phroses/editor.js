var 
    $ = require("jquery"),
    utils = require("./utils"),
    editors = {}; 

var editor = function() {
    this.aceify();
    this.setupTrigger();
    this.setupTabbing();
    this.setupSaving();
    this.setupStylesTab();
};

editor.prototype.setupTrigger = function() {
    $("#pst-edit-trg").click(function() { 
        $("body").toggleClass("editView"); 
        setTimeout(function() {
            editors["css-editor"].resize(true);
        }, 1000);
    });
};

editor.prototype.setupTabbing = function() {
    $(".mode").click(function() { 
        $(this).parent().parent().attr("data-view", $(this).data("view") )
    });
}


editor.prototype.setupSaving = function() {
    var $this = this;

    $("html").on("keydown", "body.editView", function(e) {
        if((e.ctrlKey || e.metaKey) && String.fromCharCode(e.which).toLowerCase() == 's') {
            e.preventDefault();
            e.stopImmediatePropagation();
            $("#pst-edit").submit();
        }
    });

    utils.formify({
        selector: "#pst-edit",
        collect: function() {
            var data = $(this).serializeArray(), content = {};

            $(this).find(".content").each(function() {
                if($(this).hasClass("editor")) content[$(this).data("id")] = editors[$(this).attr("id")].getValue();
                else content[$(this).attr("id")] = $(this).val();
            });

            data.push({name : "id", value : $("#pid").val() });
            data.push({name:"title", value: $("#pst-es-title").val() });
            data.push({name : "content", value : JSON.stringify(content) });
            data.push({ name: "css", value: editors["css-editor"].getValue() });
            if($("#pst-es-type").val() === "redirect") data.push({name : "type", value : "redirect" });

            return data;
        },
        success: function(pdata) {
            utils.displaySaved();
            $this.updatePage($("#pst-es-title").val(), pdata.content);
        }
    });

    // type switcher
    utils.formify({
        selector:  "#pst-es-type",
        action: "change",
        collect: function() {
            if($("#pst-edit").attr("data-view") == "mode-content") {
                $("#mode-content").slideUp();
            }

            return { type : $(this).val(), id : $("#pid").val() };
        },
        success: function(pdata) {
            $("#mode-content").html(pdata.typefields);
            $this.aceify();
            if(typeof pdata.content !== 'undefined') $("#phr-container").html(pdata.content);
            
            if($("#pst-edit").attr("data-view") == "mode-content") {
                $("#mode-content").slideDown();
            }

            if(pdata.type !== "redirect") utils.displaySaved();
        }
    });
};

editor.prototype.setupStylesTab = function() {
    // retrieve styling
    $.get("?mode=css")
    .always(function(css) {
        $("head").append("<style id=\"phroses-page-styles\">"+css+"</style>");
        editors["css-editor"].setValue(css);
    });

    // refresh style on keyup
    $("#css-editor").on("keyup", function() { 
        $("#phroses-page-styles").html(editors["css-editor"].getValue()); 
    });

};

editor.prototype.aceify = function() {
    $(".editor").each(function() {
        var id = $(this).attr("id");
        editors[id] = ace.edit(id);
        editors[id].setTheme("ace/theme/monokai");
        editors[id].getSession().setMode("ace/mode/"+($(this).data("mode") || "html"));

        editors[id].commands.addCommand({
            name: 'Save',
            bindKey: {win: 'Ctrl-S',  mac: 'Command-S'},
            exec: function(editor) {
                $(this).parent().submit();
            }.bind(this),
            readOnly: true // false if this command should not apply in readOnly mode
        });
    });
}

editor.prototype.updatePage = function(title, content) {
    if(typeof title !== 'undefined') document.title = title;
    if(typeof content !== 'undefined') $("#phr-container").html(content);
    this.reloadStyles();
}

editor.prototype.reloadStyles = function() {
    $("head link").each(function() {
        var href = $(this).attr("href"), pass = false;
        var origin = window.location.origin.replace(/http(s)?\:/g, "");

        // only reload internal stylesheets
        if(href.substring(0, 1) === "/" && href.substring(1, 2) !== "/") pass = true; // relative
        if(href.replace(/http(s)?\:/g, "").substring(0, origin.length) === origin) pass = true; // on the same domain

        if(href !== document.querySelector("#phroses-script").getAttribute("data-adminuri")+"/assets/css/phroses.css" && pass) {
            
            $.get(href, function(body) {
                $("head").append('<style class="phr-reloaded" data-href="'+href+'">'+body+'</style>');
                $(this).remove();
                console.log("Reloaded " + href);
            }.bind(this));
        }
    });

    $(".phr-reloaded").each(function() {
        $.get($(this).data("href"), function(body) {
            $(this).html(body);
            console.log("Reloaded Stylesheet <" + $(this).data("href") + ">");
        }.bind(this));
    });
};

module.exports = editor;
