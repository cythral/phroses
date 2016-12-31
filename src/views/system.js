var editors = {};

$(function() {
	$(window).bind("keydown", function(e) {
		if((e.ctrlKey || e.metaKey) && String.fromCharCode(e.which).toLowerCase() == 's') {
			e.preventDefault();
			e.stopImmediatePropagation();
			$("#phroses_editor").submit();
		}
	});
	
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
	
	$("#phroses_editor").submit(function(e) {
		e.preventDefault();
		e.stopPropagation();
		
		var data = $(this).serializeArray(), content = {};		
		$(".content").each(function() {
			if($(this).hasClass("editor")) content[$(this).attr("id")] = editors[$(this).attr("id")].getValue();
		});
		data.push({ name : "content", value : JSON.stringify(content) });
		
		$.post("/admin/editor", data)
		.done(function(data) {
			console.log(data);
			$("#saved").addClass("active");
			setTimeout(function() {
				$("#saved").removeClass("active");
			}, 5000);
		})
		.fail(function(data) {
			console.log(data);
		});
	});
});