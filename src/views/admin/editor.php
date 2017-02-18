<?php
use Phroses\DB;

// Handle POST method -- saves page updates over AJAX
Phroses\HandleMethod("POST", function() {
	$q = Phroses\DB::Query("UPDATE `pages` SET `title`=?, `uri`=?, `content`=?, `type`=? WHERE `id`=?", [
		$_POST["title"], urldecode($_POST["uri"]), htmlspecialchars_decode($_POST["content"]), urldecode($_POST["type"]), (int)$_POST["id"]
	]);	
}, [ "id", "title", "uri", "content", "type" ]);

Phroses\HandleMethod("DELETE", function() {
	$q = Phroses\DB::Query("DELETE FROM `pages` WHERE `id`=?", [ $_REQUEST["id"] ]);
}, [ "id" ]);

// Fetch Page Variables
$page = DB::Query("SELECT * FROM `pages` WHERE `siteID`=? AND `uri`=?", [ Phroses\SITE["ID"], $_GET["uri"] ])[0];
$page->content = json_decode($page->content, true);

$theme->Push("scripts", [ 
	"src" => "//cdnjs.cloudflare.com/ajax/libs/ace/1.2.6/ace.js",
	"attrs" => ""
]);

$theme->Push("stylesheets", [ "src" => "//cdnjs.cloudflare.com/ajax/libs/font-awesome/4.4.0/css/font-awesome.min.css" ]);

?>
<div class="container">
	<div>
		<h2>You're editing <a href="<?= $_GET["uri"]; ?>"><?= $page->title; ?></a>, a page on <a href="/"><?= Phroses\SITE["NAME"]; ?></a></h2>
		<p>This page was created on <?= date("m/d/Y @ h:ia", strtotime($page->dateCreated)); ?> <?php if($page->dateCreated != $page->dateModified) { ?>and last modified on <?= date("m/d/Y @ h:ia", strtotime($page->dateModified)); ?><? } ?></p>
	</div>
	
	
	<form id="phroses_editor" class="form">
		<div id="saved">Page Saved!</div>
		<div class="aln-r">
			<div id="phroses_editor_delete"><i class="fa fa-trash"></i> Delete This Page</div>
		</div>
		<input id="pageID" name="id" type="hidden" value="<?= $page->id; ?>">
		<div class="form_icfix">
			<div>Title:</div>
			<input name="title" class="form_input form_field" placeholder="Page Title" value="<?= $page->title; ?>" autocomplete="off">	
		</div>
		<div class="form_icfix">
			<div>URI:</div>
			<input name="uri" class="form_input form_field" placeholder="Page URI" value="<?= $page->uri; ?>" autocomplete="off">	
		</div>
		
		<div class="form_icfix">
			<div>Type:</div>
			<select id="page_type" name="type" class="form_select form_field">
				<?php 
				foreach($theme->GetTypes() as $type) { ?>
				<option value="<?= $type; ?>" <? if($type == $page->type) { ?>selected<? } ?>><?= ucfirst($type); ?></option>
				<? } ?>
			</select>	
		</div>
		
		<div id="form_fields">Loading ...</div>
		
		<input class="form_submit form_field" type="submit" value="Save">
	</form>
</div>

<?php

foreach($theme->GetTypes() as $type) { ?>
	<div class="editor_tpl" id="type-<?= $type; ?>">
		<?php
		foreach($theme->GetContentFields($type) as $key => $field) { 
			if($field == "editor")  { ?><div class="form_field content editor" id="<?= $type; ?>-main" data-id="<?= $key; ?>"><?= trim(htmlspecialchars($page->content[$key] ?? "")); ?></div><? }
			else if(in_array($field, ["text", "url"])) { ?><input id="<?= $key; ?>" placeholder="<?= $key; ?>" type="<?= $field; ?>" class="form_input form_field content" value="<?= $page->content[$key] ?? ""; ?>"><? }	
		} ?>
	</div>
<?
}

?>
