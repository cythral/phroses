var editors = {};

$(function() {
	$(window).bind("keydown", function(e) {
		if((e.ctrlKey || e.metaKey) && String.fromCharCode(e.which).toLowerCase() == 's') {
			e.preventDefault();
			e.stopImmediatePropagation();
			$("#phroses_editor").submit();
		}
	});
	
	$("#form_fields").html($("#type-"+$("#page_type").val()).html());
	
	$(".editor").each(function() {
		var id = $(this).attr("id");
		editors[id] = ace.edit(id);
		editors[id].setTheme("ace/theme/monokai");
		editors[id].getSession().setMode("ace/mode/html");

		editors[id].commands.addCommand({
			name: 'Save',
			bindKey: {win: 'Ctrl-S',  mac: 'Command-S'},
			exec: function(editor) {
				$("#phroses_editor").submit();
			},
			readOnly: true // false if this command should not apply in readOnly mode
		});
	});

	$("#page_type").change(function() {
		$("#form_fields").slideUp(400, function() {
			$("#form_fields").html($("#type-"+$("#page_type").val()).html());	
			$("#form_fields .editor").each(function() {
				var id = $(this).attr("id");
				editors[id] = ace.edit(id);
				editors[id].setTheme("ace/theme/monokai");
				editors[id].getSession().setMode("ace/mode/html");
				if(editors[id].getValue() == "XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX") {
					editors[id].setValue("");
				}

				editors[id].commands.addCommand({
					name: 'Save',
					bindKey: {win: 'Ctrl-S',  mac: 'Command-S'},
					exec: function(editor) {
						$("#phroses_editor").submit();
					},
					readOnly: true // false if this command should not apply in readOnly mode
				});
			});
			$("#form_fields").slideDown();
		});
	});
	
	
	$("#phroses_editor_delete").click(function(e) {
		$.ajax({ url : $("#pageuri").val(), method: "DELETE" })
		.done(function(data) {
			document.location = "/admin/pages";
		})
		.fail(function(data) {
			console.log(data);
		});
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
				}[data.field]);
				$("#error").addClass("active");
				setTimeout(function() {
					$("#error").removeClass("active");
				}, 5000);
			}
		});
	});
	
	$("#phroses-login").submit(function(e) {
		e.preventDefault();
		e.stopPropagation();
		
		var data = $(this).serializeArray();
		$.post("/admin/login", data, { async: false })
		.done(function(data) {
			console.log(data);
			location.reload();
		})
		.fail(function(data) {
			console.log(data);
		});
	});
});