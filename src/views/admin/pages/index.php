<div class="container">
	<div class="aln-r">
		<a class="btn" href="/admin/pages/create"><i class="fa fa-plus"></i> New Page</a>
		<div class="clear"></div>
	</div>
	<?php
	$q = Phroses\DB::Query("SELECT * FROM `pages` WHERE `siteID`=?", [ Phroses\SITE["ID"] ]);
	
	if(count($q) == 0) {
		?>
	<em>No pages for <?= Phroses\REQ["BASEURL"]; ?>.  <a href="/admin/pages/create"><strong>Create your first one?</strong></a></em>
		<?
	}
	
	foreach($q as $page) {
		?>
	<a href="/admin/pages/<?= $page->id; ?>" class="page_item"><?= $page->uri; ?> ( <strong><?= $page->title; ?></strong> )</a>
		<?
	}
	?>
</div>