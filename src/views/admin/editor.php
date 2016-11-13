<?php
use Phroses\DB;

Phroses\HandleMethod("POST", function() {
	$q = Phroses\DB::Query("UPDATE `pages` SET `title`=?, `uri`=?, `content`=? WHERE `id`=?", [
		$_POST["title"], urldecode($_POST["uri"]), htmlspecialchars_decode($_POST["content"]), (int)$_POST["id"]
	]);
	var_dump(DB::Error());
}, [ "id", "title", "uri", "content" ]);

$page = DB::Query("SELECT * FROM `pages` WHERE `siteID`=? AND `uri`=?", [ 
	Phroses\SITE["ID"], $_GET["uri"]
])[0];
$page->content = json_decode($page->content, true);

$theme->Push("scripts", [ 
	"src" => "//cdnjs.cloudflare.com/ajax/libs/ace/1.2.5/ace.js",
	"attrs" => "async"
]);

?>
<hgroup>
	<h1>Phroses Editor</h1>
	<h2>Editing <?= $page->title; ?> <strong>[PID:<?= $page->id; ?>]</strong></h2>
</hgroup>

<form id="phroses_editor" class="form">
	<div id="saved">Page Saved!</div>
	<input name="id" type="hidden" value="<?= $page->id; ?>">
	<input name="title" class="form_input form_field" placeholder="Page Title" value="<?= $page->title; ?>">
	<input name="uri" class="form_input form_field" placeholder="Page URI" value="<?= $page->uri; ?>">
	<br>
	<?php
	
	foreach($theme->GetContentFields($page->type) as $key => $type) {
		if($type == "editor")  { ?><div class="form_field editor content" id="<?= $key; ?>"><?= htmlspecialchars(((string)$page->content[$key] ?? "")); ?></div><? }
		else if($type == "text") { ?><input id="<?= $key; ?>" placeholder="<?= $key; ?>" class="form_field content" value="<?= $page->content[$key] ?? ""; ?>"><? }
	}
	
	?>
	<input class="form_submit form_field" type="submit" value="Save">
</form>

