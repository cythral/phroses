<?php

use Phroses\DB;
use const Phroses\{SITE, REQ};

?>

<div class="container">
	<div class="aln-r">
		<a class="phr-btn btn" href="#" id="new" data-target="phr-new-page" data-action="fadeIn"><i class="fa fa-plus"></i> New Page</a>
		<div class="clear"></div>
	</div>
    
    
    
	<?php
	$q = DB::Query("SELECT * FROM `pages` WHERE `siteID`=?", [ SITE["ID"] ]);
	
	if(count($q) == 0) {
		?><em>No pages for <?= REQ["BASEURL"]; ?>.  <a href="/admin/pages/create"><strong>Create your first one?</strong></a></em><?
	}
	
	foreach($q as $page) {
		?><a href="<?= $page->uri; ?>" class="page_item" data-id="<?= $page->id; ?>"><?= $page->uri; ?> <strong><?= $page->title; ?></strong> 
        
            <div class="pull-r">    
                <select class="pageman-select">
                    <?
                    foreach($theme->GetTypes() as $type) {
                        ?><option <?= ($type == "redirect") ? "disabled" : ""; ?> <?= ($type == $page->type) ? "selected" : ""; ?>><?= $type; ?></option><?
                    } ?>
                </select>
                <i class="pageman-delete fa fa-times"></i>
            </div>
        </a><?
	}
	?>
</div>

<div id="phr-new-page" class="container screen">
    <h1>Enter new page URI:</h1>
    <div class="form_icfix c aln-l">
        <div>URI:</div>
        <input name="title" class="form_input form_field" placeholder="/" autocomplete="off">
    </div>
    <br>
    <a href="#" class="pst_btn txt" data-target="phr-new-page" data-action="submit">Go</a>
    <a href="#" class="pst_btn txt" data-target="phr-new-page" data-action="fadeOut">Cancel</a>
</div>