var editors = {};

function Phroses() {}
Phroses.errors = {
	"write" : "Phroses encountered a problem writing and/or deleting files.  Please check filesystem permissions and try again.",
    "api" : "There was a problem accessing the api.  Please try again later",
	"extract" : "There was an issue extracting files from the archive.  Please check filesystem permissions and try again.",
	"pw_length" : "Password is too long, please keep it less than or equal to 50 characters.",
	"access_denied" : "You do not have permission to do that.",

	"pst-ms" : {
		"resource_exists" : "The URI you are trying to move this page to already exists."
	},

	"uploads" : {
		"resource_exists" : "That filename already exists.",
		"failed_upl" : "There was an error uploading that file, it may be too large.",
		"topupldir_notfound" : "The /uploads directory does not exist, please create it and give Phroses write access.",
		"siteupldir_notfound" : "The uploads sub directory for this website does not exist.  Please give phroses write access to the /uploads folder."
	}
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

	$(document).on(options.action || "submit", options.selector, function(e) {
		e.preventDefault();
		e.stopImmediatePropagation();
		var data = (options.collect || function() { return $(this).serializeArray(); }).bind(this)();
		

		$.ajax({
			url : $(this).data("url"),
			data : data,
			method : $(this).data("method")
		}).then(options.success.bind(this)).catch((options.failure) ? options.failure.bind(this) : Phroses.genericError);
	});

	console.log("Formified element <"+options.selector+">");
};

Phroses.updatePage = function(title, content) {
	if(typeof title !== 'undefined') document.title = title;
	if(typeof content !== 'undefined') $("#phr-container").html(content);
	this.reloadStyles();
};

Phroses.reloadStyles = function() {
	$("head link").each(function() {
		var href = $(this).attr("href"), pass = false;
		var origin = window.location.origin.replace(/http(s)?\:/g, "");

		// only reload internal stylesheets
		if(href.substring(0, 1) === "/" && href.substring(1, 2) !== "/") pass = true; // relative
		if(href.replace(/http(s)?\:/g, "").substring(0, origin.length) === origin) pass = true; // on the same domain


		if(href !== "/phr-assets/css/main.css" && pass) {
			
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
}

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



Phroses.displaySaved = function() {
	$("#saved").addClass("active");
	setTimeout(function() {
		$("#saved").removeClass("active");
	}, 5000);
}

Phroses.createEditors = function() {
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


$(function() {
	console.log("-== Phroses Initialized ==-");
	

	$(document).on("click", ".jlink", function() {
		document.location = $(this).data("href");
	});
	
	if(!$("#phr-admin-page").val()) {
		var content = $("body").html();
		$("body").html('<div id="phr-container">'+content+"</div>");
		$.post("/admin/api/pst", { uri : window.location.pathname }, function(data) {
			$("body").append(data.content);
			
			Phroses.setupButtons();
			Phroses.createEditors();

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
					Phroses.displaySaved();
					Phroses.updatePage($("#pst-es-title").val(), pdata.content);
				}
			});
		
			/**
			 * Deletion Screen
			 */
			Phroses.formify({
				selector: "#pst-ds",
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
					return data;
				},
				success: function(data) {
					history.replaceState({}, document.title, $("#puri").val());
					$("#pst-ms").fadeOut();
					Phroses.displaySaved();
				},

				failure: function(data) {
					data = data.responseJSON;
					Phroses.genericError(Phroses.errors["pst-ms"][data.error] || Phroses.errors[data.error] || "An unknown error occurred.");
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
				success: function(pdata) {
					var title = $("#pst-ns [name=title]").val();
					$("#pid").val(pdata.id);
					$("#pst").removeClass("new");
					$("#pst").addClass("existing");
					$("#phr-container").html(pdata.content);
					$("#pst-es-fields").html(pdata.typefields);
					$("#pst-es input[name=title]").val($("#pst-ns input[name=title]").val());
					$("#pst-es-type").val($("#pst-ns select").val());
					Phroses.createEditors();
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
					if(data.type !== "redirect") Phroses.displaySaved();
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
		});

	} else {

		Phroses.setupButtons();

		/**
		 * Login Screen
		 */
		Phroses.formify({
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
		$(".pageman-select").click(function(e) { e.preventDefault(); e.stopImmediatePropagation(); });

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
			success: function() {
				Phroses.displaySaved();
				
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
		

		Phroses.formify({
			selector : "#phroses_site_creds",
			success : function() {
				Phroses.displaySaved();
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
								var percentComplete = evt.loaded / evt.total;
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
	}
});