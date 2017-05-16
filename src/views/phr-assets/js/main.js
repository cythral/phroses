var editors = {};

function displaySaved() {
	$("#saved").addClass("active");
		setTimeout(function() {
			$("#saved").removeClass("active");
		}, 5000);
}

$(function() {
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
	
	$(".pst_btn").click(function(e) {
		e.preventDefault();
		if($(this).data("target")) {
			$("#"+$(this).data("target"))[$(this).data("action")]();
		}
	});
	
	$("#pst-es").submit(function(e) {
		e.preventDefault();
		var data = $(this).serializeArray(), content = {};
		
		$("#pst-es .content").each(function() {
			if($(this).hasClass("editor")) content[$(this).data("id")] = editors[$(this).attr("id")].getValue();
			else content[$(this).attr("id")] = $(this).val();
		});
		
		data.push({name : "content", value : JSON.stringify(content) });
		
		$.ajax({ url : window.location.href, method : "PATCH", data : data })
		.done(function(pdata) {
			displaySaved();
			$("main").html(pdata.content);
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
});