var $ = require("jquery"),
    Phroses = require("phroses");


$(document).on("click", ".upload", function() {
    var file = $(this).data("filename");
    var ext = file.substring(file.indexOf(".") + 1);

    if(["png", "jpg", "gif"].includes(ext)) {
        $("#preview img").attr('src', "/uploads/"+file);
    } else {
        $("#preview img").attr("src", "https://www.adcosales.com/files/products/no-preview-available.jpg");
    } 

    $("#seefull").attr("href", "/uploads/"+file);
    $("#preview").fadeIn();
});

$(document).on("click", ".upload input", function(e) {
    e.preventDefault();
    e.stopImmediatePropagation();
});

Phroses.utils.formify({
    selector: ".upload input",
    action: "change",
    collect: function() {
        return { action : "rename", filename : $(this).parent().data("filename"), to : $(this).val() };
    },
    success: function() {
        var upel = $(this).parent();
        upel.data("filename", $(this).val());

        upel.addClass("saved");
        setTimeout(function() {
            upel.removeClass("saved");
        }.bind(this), 1000);
    },
    failure: function(data) {
        var upel = $(this).parent();
        $(this).val(upel.data("filename"));

        upel.addClass("error");
        setTimeout(function() {
            upel.removeClass("error");
        }.bind(this), 1000);
    }
});

Phroses.utils.formify({
    selector: ".upload-delete",
    action: "click",
    collect: function() {
        return { action: "delete", filename: $(this).parent().parent().data("filename") };
    },
    success: function() {
        $(this).parent().parent().slideUp(); 
    }
});

$("#upload").on("drag dragstart dragend dragover dragenter dragleave drop", function(e) {
    e.preventDefault();
    e.stopPropagation();
});

$("#upload:not(.active)").on("dragenter", function() { $(this).addClass("dragover"); });
$("#upload:not(.active)").on("dragleave", function() { $(this).removeClass("dragover"); });
$("#upload:not(.active)").on("drop", function(e, byclick) {
    $(this).addClass("active");
    $(this).removeClass("dragover");

    var resetUplForm = function() {
        $("#upload").fadeOut(400, function() {
            $("#upload").off("submit");
            $("#upload-namer").fadeOut();
            $("#upload-namer input").val('');
            $("#upload label").fadeIn();
            $("#upload").removeClass("active");
            $(".phr-progress-bar").css({width:"0"});
            $(".phr-progress").removeClass("done");
            $(".phr-progress").fadeOut();
        });
    };
    
    file = (typeof byclick === 'undefined') ? e.originalEvent.dataTransfer.files[0] : $("#file").prop("files")[0];

    if(file.size > $("#maxuplsize").val()) {
        Phroses.utils.genericError("That file is too large.  Please select a file less than " + ($("#maxuplsize").val() / 1048576) + "MB or increase php's max_upload_filesize");
        resetUplForm();
        return;
    }

    if(file.size > $("#maxformsize").val()) {
        Phroses.utils.genericError("That file is too large.  Please select a file less than " + ($("#maxformsize").val() / 1048576) + "MB or increase php's post_max_size");
        resetUplForm();
        return;
    }

    $(this).find("label").fadeOut(400, function() { $("#upload-namer").fadeIn(); });
    
    

    var submit = function(e) {
        e.preventDefault();
        e.stopPropagation();

        $("#upload .phr-progress").fadeIn();

        var data = new FormData(), filename = $("[name='filename']").val();
        data.append("filename", filename);
        data.append("file", file);
        data.append("action", "new");
        
        $.ajax({
            url : "",
            data : data,
            method : "post",
            processData : false,
            dataType : 'json',
            contentType : false,
            xhr: function() {
                var xhr = new window.XMLHttpRequest();
                xhr.upload.addEventListener("progress", function(evt){
                    if (evt.lengthComputable) {  
                        var percentComplete = (evt.loaded / evt.total) * 100;
                        $(".phr-progress-bar").css({width:percentComplete+"%"});
                    }
                }, false); 

                return xhr;
            },
            success : function() {
                $(".phr-progress-bar").css({width:"100%"});
                $(".phr-progress").addClass("done");

                setTimeout(resetUplForm, 2000);

                $(".admin-page.uploads ul").append('<li class="upload" data-filename="'+filename+'"><input value="'+filename+'" data-method="post"><div class="upload-icons"><a href="/uploads/'+filename+'" class="fa fa-link"></a><a href="#" class="fa fa-search-plus"></a><a href="#" class="fa fa-times upload-delete" data-method="post"></a></div></li>');

            },
            error: function(data) {
                $(".phr-progress").addClass("error");

                setTimeout(function() {
                    $(".phr-progress-bar").css({width:0});
                    $(".phr-progress").removeClass("error");
                    $(".phr-progress").fadeOut();

                    if(data.responseJSON.error === "resource_exists") {
                        $("#upload-namer input").val('');
                    } else {
                        resetUplForm();
                    }

                }, 2000);

                Phroses.utils.genericError(Phroses.errors.uploads[data.responseJSON.error] || data.responseJSON);
                $("#upload").one("submit", submit);
            }
        });
    };

    $("#upload").one("submit", submit);
});

$("#upload #file").change(function() {
    $("#upload").trigger('drop', true);
});
