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
			document.location = "/admin";
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
            uri = $("[name='uri']").val();	
                    
		$("#phroses_editor .content").each(function() {
			if($(this).hasClass("editor")) content[$(this).data("id")] = editors[$(this).attr("id")].getValue();
			else content[$(this).attr("id")] = $(this).val();
		});
		
        data.push({ name : "content", value : JSON.stringify(content) });
		
		$.ajax({url : uri, data: data, method : method })
		.done(function(postdata) {
            if(method == "PATCH") {
				$("#saved").addClass("active");
				setTimeout(function() {
					$("#saved").removeClass("active");
				}, 5000);
			} else if(method == "POST") {
				document.location = "/admin/editor?uri="+uri;
			}
		})
		.fail(function(data) {
			console.log(data);
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