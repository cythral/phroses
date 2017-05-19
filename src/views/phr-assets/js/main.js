var editors = {};

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
  createEditors();
	
	$("#pst-es").bind("keydown", function(e) {
		if((e.ctrlKey || e.metaKey) && String.fromCharCode(e.which).toLowerCase() == 's') {
			e.preventDefault();
			e.stopImmediatePropagation();
			$("#pst-es").submit();
		}
	});
	
	$(".pst_btn").click(function(e) {
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
	
	$("#pst-es-type").change(function() {
		var data = { type : $(this).val(), id : $("#pst-es input[name=id]").val() };
		$("#pst-es-fields").slideUp();
		$.ajax({ url : window.location.href, method : "PATCH", data: data })
		.done(function(pdata) {
			$("#pst-es-fields").html(pdata.typefields);
			createEditors();
			if(typeof pdata.content !== 'undefined') $("#phr-container").html(pdata.content);
			$("#pst-es-fields").slideDown();
			if(data.type != "redirect") displaySaved();
		});
	});
	
	$("#pst-es-title").change(function() {
		$("#pst-es").submit();
	});
});