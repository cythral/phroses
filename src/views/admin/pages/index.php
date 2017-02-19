<div class="container">
	<div class="aln-r">
		<a class="btn" href="/admin/pages/create"><i class="fa fa-plus"></i> New Page</a>
		<div class="clear"></div>
	</div>
	<?php
	$q = Phroses\DB::Query("SELECT * FROM `pages` WHERE `siteID`=?", [ Phroses\SITE["ID"] ]);
	foreach($q as $page) {
		?>
	<a href="/admin/pages/<?= $page->id; ?>" class="page_item"><?= $page->uri; ?> ( <strong><?= $page->title; ?></strong> )</a>
		<?
	}
	?>
</div>