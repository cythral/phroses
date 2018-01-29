var editors = {};

function Phroses() {}
Phroses.errors = {
	"write" : "Phroses encountered a problem writing and/or deleting files.  Please check filesystem permissions and try again.",
    "api" : "There was a problem accessing the api.  Please try again later",
	"extract" : "There was an issue extracting files from the archive.  Please check filesystem permissions and try again.",
	"pw_length" : "Password is too long, please keep it less than or equal to 50 characters.",
	"access_denied" : "You do not have permission to do that."
};

Phroses.formify = function(options) {
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

	$(options.selector)[options.action || "submit"](function(e) {
		e.preventDefault();
		e.stopPropagation();
		var collect = options.collect.bind(this);

		$.ajax({
			url : $(this).data("url"),
			data : collect(),
			method : $(this).data("method")
		}).then(options.success.bind(this)).catch((options.failure) ? options.failure.bind(this) : Phroses.genericError);
	});

	console.log("Formified element "+options.selector);
};

Phroses.genericError = function(message) {
	if(typeof message === 'object') {
		if(message.responseJSON) message = message.responseJSON;

		if(message.error && Object.keys(Phroses.errors).includes(message.error)) message = Phroses.errors[message.error];
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
	console.log("-== Phroses Initialized ==-");
    createEditors();
	
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

	$("#pst-es").bind("keydown", function(e) {
		if((e.ctrlKey || e.metaKey) && String.fromCharCode(e.which).toLowerCase() == 's') {
			e.preventDefault();
			e.stopImmediatePropagation();
			$("#pst-es").submit();
		}
	});
	

	$("#pst-es-title").change(function() { $("#pst-es").submit() });


	/**
	 * Editor Screen
	 */
	Phroses.formify({
		selector : "#pst-es",
		collect: function() {
			var data = $(this).serializeArray(), content = {};
		
			$("#pst-es .content").each(function() {
				if($(this).hasClass("editor")) content[$(this).data("id")] = editors[$(this).attr("id")].getValue();
				else content[$(this).attr("id")] = $(this).val();
			});
			
			data.push({name : "id", value : $("#pid").val() });
			data.push({name : "content", value : JSON.stringify(content) });
			if($("#pst-es-type").val() === "redirect") data.push({name : "type", value : "redirect" });
			return data;
		},
		success: function(pdata) {
			displaySaved();
			if(typeof pdata.content !== 'undefined') $("#phr-container").html(pdata.content);
			document.title = $("#pst-es-title").val();
		}
	});

	/**
	 * Deletion Screen
	 */
	Phroses.formify({
		selector: "#pst-ds",
		collect: function() {
			return $(this).serializeArray();
		},
		success: function(data) {
			location.reload();
		}
	});
	
	/**
	 * Move Screen
	 */
	Phroses.formify({
		selector: "#pst-ms",
		collect : function() {
			var data = $(this).serializeArray();
			data.push({ name : "id", value : $("#pid").val() });
		},
		success: function(data) {
			history.replaceState({}, document.title, $("#puri").val());
			$("#pst-ms").fadeOut();
			displaySaved();
		}
	});
	
    /**
	 * New Page Screen
	 */
    Phroses.formify({
		selector: "#pst-ns",
		hash: "#new",
		hashreqclass: {
			element: "#pst",
			class : "new"
		},
		collect: function() {
			return $(this).serializeArray();
		},
		success: function(pdata) {
			var title = $("#pst-ns [name=title]").val();
			$("#pid").val(pdata.id);
			$("#pst").removeClass("new");
			$("#pst").addClass("existing");
			$("#phr-container").html(pdata.content);
			$("#pst-es-fields").html(pdata.typefields);
			$("#pst-es input[name=title]").val($("#pst-ns input[name=title]").val());
			$("#pst-es-type").val($("#pst-ns select").val());
			createEditors();
			document.title = title;

			$("#pst-ns").fadeOut(function() {
				$("#pst-ns")[0].reset();
			});
		}
	});

	/**
	 * Type changer on the edit screen
	 */
	Phroses.formify({
		selector:  "#pst-es-type",
		action: "change",
		collect: function() {
			$("#pst-es-fields").slideUp();
			return { type : $(this).val(), id : $("#pid").val() };
		},
		success: function(pdata) {
			$("#pst-es-fields").html(pdata.typefields);
			createEditors();
			if(typeof pdata.content !== 'undefined') $("#phr-container").html(pdata.content);
			$("#pst-es-fields").slideDown();
			if(data.type !== "redirect") displaySaved();
		}
	});

	/**
	 * Public / Private Switcher
	 */
	Phroses.formify({
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

	/**
	 * Login Screen
	 */
	Phroses.formify({
		selector: "#phroses-login",
		collect: function() { return $(this).serializeArray(); },
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
	Phroses.formify({
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
	$(".pageman-select").click(function(e) { e.preventDefault(); });

	/**
	 * Page deletion on /admin/pages
	 */
	Phroses.formify({
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
	Phroses.formify({
		selector: "#theme-selector",
		action: "change",
		collect: function() { return { theme : $(this).val() }; },
		success: displaySaved
	});

	/**
	 * Upgrade screen
	 * uses EventSource to track progress, so formify doesnt work here.
	 */
	$("#phr-upgrade-screen").submit(function(e) {
		e.preventDefault();
		$(this).fadeIn();
		
		var ev = new EventSource("/admin/update/start");
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
	

	$(".sys.form").submit(function(e) {
		e.preventDefault();
		e.stopPropagation();
		
		var data = $(this).serializeArray(), 
            content = {}, 
            method = $(this).data("method"),
            uri = $("[name='uri']").val() || $(this).data("uri"),
						id = $(this).attr("id");	
                    
		$("#phroses_editor .content").each(function() {
			if($(this).hasClass("editor")) content[$(this).data("id")] = editors[$(this).attr("id")].getValue();
			else content[$(this).attr("id")] = $(this).val();
		});
		
   	 	data.push({ name : "content", value : JSON.stringify(content) });
		
		$.ajax({url : uri, data: data, method : method })
		.done(function(postdata) {
      		if(id == "phroses_editor" || id == "phroses_site_creds") {
				$("#saved").addClass("active");
				setTimeout(function() {
					$("#saved").removeClass("active");
				}, 5000);
			} else if(id == "phroses_creator") {
				document.location = "/admin/pages/"+postdata.id;
			}
		})
		.fail(function(data) {
			data = data.responseJSON;
			
			if(id =="phroses_site_creds") {
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
	});
});