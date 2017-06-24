<?

use \Phroses\{ DB, Theme };
use function Phroses\{HandleMethod, JsonOutput };
use const \Phroses\{ SITE, INCLUDES };



HandleMethod("POST", function() {
    if(!file_exists(INCLUDES["THEMES"]."/".$_POST["theme"])) JsonOutput(["type" => "error", "error" => "bad_theme"]);
    DB::Query("UPDATE `sites` SET `theme`=? WHERE `id`=?", [ $_POST["theme"], SITE["ID"] ]);
    JsonOutputSuccess();
});

$vars = DB::Query("SELECT SUM(`views`) AS viewcount, COUNT(`id`) AS pagecount FROM `pages` WHERE `siteID`=?", [ SITE["ID"] ])[0];
?>
<h1 class="c panel-heading">Phroses Panel Home</h1>
<br>

<div id="saved">Saved</div>
<div id="error">Error</div>

<div class="container">
    <div class="panel-row">
        <section class="panel-section panel-pages aln-c">
            <h2>Page Stats</h2>
            <div class="panel-pages-line"><span><?= $vars->pagecount; ?></span> Pages</div>
            <div class="panel-pages-line"><span><?= $vars->viewcount ?? 0; ?></span> Page Views</div>
            <br><a href="/admin/pages">Manage Pages <i class="fa fa-chevron-right"></i></a>
        </section>
        <section class="panel-section">
            <div class="form_icfix aln-l c">
                <div>Theme:</div>
                <select class="c form_field form_select" id="theme-selector">
                    <? 
                    foreach(Theme::List() as $thm) {
                    ?><option value="<?= $thm; ?>" <? if($thm == SITE["THEME"]) { ?>selected<? } ?>><?= ucfirst($thm); ?></option><?
                    } ?>
                </select>
            </div>
            <div class="aln-c bold"><br><a href="/admin/creds">Change Site Login <i class="fa fa-chevron-right"></i></a>
        </section>
    </div>
</div>