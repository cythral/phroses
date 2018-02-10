$(function() {
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

    Phroses.formify({
        selector: ".upload input",
        action: "change",
        collect: function() {
            return { action : "rename", filename : $(this).parent().data("filename"), to : $(this).val() };
        },
        success: function() {
            console.log($(this).parent().selector);
            var upel = $(this).parent();
            upel.data("filename", $(this).val());

            upel.addClass("saved");
            setTimeout(function() {
                upel.removeClass("saved");
            }.bind(this), 1000);
        }
    });

    Phroses.formify({
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
        
        file = (typeof byclick === 'undefined') ? e.originalEvent.dataTransfer.files[0] : $("#file").prop("files")[0];
        $(this).find("label").fadeOut(400, function() { $("#upload-namer").fadeIn(); });
        
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

                    Phroses.genericError(Phroses.errors.uploads[data.responseJSON.error] || data.responseJSON);
                    $("#upload").one("submit", submit);
                }
            });
        };

        $("#upload").one("submit", submit);
    });

    $("#upload #file").change(function() {
        $("#upload").trigger('drop', true);
    });
});