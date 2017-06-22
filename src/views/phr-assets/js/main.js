var editors = {};
var errors = {
    "write" : "Phroses encountered a problem writing and/or deleting files.  Please check filesystem permissions and try again.",
    "api" : "There was a problem accessing the api.  Please try again later",
    "extract" : "There was an issue extracting files from the archive.  Please check filesystem permissions and try again."
};

function displaySaved() {
	$("#saved").addClass("active");
		setTimeout(function() {
			$("#saved").removeClass("active");
		}, 5000);
}

function createEditors() {
	$(".editor").each(function() {
		var id = $(this).attr("id");
		editors[id] = ace.edit(id);
		editors[id].setTheme("ace/theme/monokai");
		editors[id].getSession().setMode("ace/mode/html");

		editors[id].commands.addCommand({
			name: 'Save',
			bindKey: {win: 'Ctrl-S',  mac: 'Command-S'},
			exec: function(editor) {
				$("#pst-es").submit();
			},
			readOnly: true // false if this command should not apply in readOnly mode
		});
	});
}

$(function() {
	console.log("phroses initialized");
  createEditors();
	
	$("#pst-es").bind("keydown", function(e) {
		if((e.ctrlKey || e.metaKey) && String.fromCharCode(e.which).toLowerCase() == 's') {
			e.preventDefault();
			e.stopImmediatePropagation();
			$("#pst-es").submit();
		}
	});
	
	$(".pst_btn, .phr-btn").click(function(e) {
		e.preventDefault();
		if($(this).data("target")) {
			$("#"+$(this).data("target"))[$(this).data("action")]();
		}
	});
	
	$(".pst_btn").on("dragstart", function() { return false; });
	
	$("#pst-es").submit(function(e) {
		e.preventDefault();
		var data = $(this).serializeArray(), content = {};
		
		$("#pst-es .content").each(function() {
			if($(this).hasClass("editor")) content[$(this).data("id")] = editors[$(this).attr("id")].getValue();
			else content[$(this).attr("id")] = $(this).val();
		});
		
		data.push({name : "id", value : $("#pid").val() });
		data.push({name : "content", value : JSON.stringify(content) });
		if($("#pst-es-type").val() === "redirect") data.push({name : "type", value : "redirect" });
		
		$.ajax({ url : window.location.href, method : "PATCH", data : data })
		.done(function(pdata) {
			displaySaved();
			if(typeof pdata.content !== 'undefined') $("#phr-container").html(pdata.content);
			document.title = $("#pst-es-title").val();
		}).fail(function(pdata) {});
	});
	
	$("#pst-ds").submit(function(e) {
		e.preventDefault();
		var data = $(this).serializeArray();
		
		$.ajax({ url : window.location.href, method : "DELETE", data : data })
		.done(function(pdata) {
			location.reload();
		}).fail(function(pdata) {
			console.error("delete page error");
		});
	});
	
	$("#pst-ms").submit(function(e) {
		e.preventDefault();
		var data = $(this).serializeArray();
		data.push({ name : "id", value : $("#pid").val() });
		
		$.ajax({ url : window.location.href, method : "PATCH", data : data })
		.done(function(pdata) {
			history.replaceState({}, document.title, $("#puri").val());
			$("#pst-ms").fadeOut();
			displaySaved();
		}).fail(function(pdata) {
			
		});
	});
        
        
	if(window.location.hash === "#new") $("#pst-ns").fadeIn();
	$("#pst-ns").submit(function(e) {
		e.preventDefault();
		
		var data = $(this).serializeArray();
		$.ajax({ url : window.location.href, method: "POST", data : data })
		.done(function(pdata) {
			$("#pid").val(pdata.id);
			$("#pst").removeClass("new");
			$("#pst").addClass("existing");
			$("#phr-container").html(pdata.content);
			$("#pst-es-fields").html(pdata.typefields);
			$("#pst-es input[name=title]").val($("#pst-ns input[name=title]").val());
			$("#pst-es-type").val($("#pst-ns select").val());
			$("#pst-ns")[0].reset();
			createEditors();
			$("#pst-ns").fadeOut();
		}).fail(function(pdata) {
			console.log(pdata);
		});
	});
	
	$("#pst-es-type").change(function() {
            var data = { type : $(this).val(), id : $("#pid").val() };
            $("#pst-es-fields").slideUp();
            $.ajax({ url : window.location.href, method : "PATCH", data: data })
            .done(function(pdata) {
                    $("#pst-es-fields").html(pdata.typefields);
                    createEditors();
                    if(typeof pdata.content !== 'undefined') $("#phr-container").html(pdata.content);
                    $("#pst-es-fields").slideDown();
                    if(data.type !== "redirect") displaySaved();
            });
	});
	
	$("#pst-es-title").change(function() {
		$("#pst-es").submit();
	});
	
	
	$(".phr-update-icon").click(function() {
		$(this).addClass("done");
		setTimeout(function() {
			$("#phr-upgrade-screen").submit();
		}, 1000);
	});
	$("#phr-upgrade-screen").submit(function(e) {
		e.preventDefault();
		$(this).fadeIn();
		
		var ev = new EventSource("/admin/update?start_upgrade");
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
			$(".phr-progress-error").html(errors[data.error]);
			$(".phr-progress").addClass("error");
		});
	});
        
        $("#phr-new-page").submit(function(e) {
            document.location = $("#phr-new-page input").val() + "#new";
        });
        
    $(".pageman-select").click(function(e) { e.preventDefault(); });
    $(".pageman-select").change(function(e) {
        var $this = $(this);
        var $parent = $this.parent().parent();
        var data = { type : $(this).val(), id : $parent.data("id") };
        $.ajax({ url : $parent.attr("href"), method : "PATCH", data : data });
    });
});