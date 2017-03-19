<?php
Phroses\HandleMethod("POST", function() {
  Phroses\DB::Query("UPDATE `sites` SET `theme`=? WHERE `id`=?", [ $_POST["theme"], Phroses\SITE["ID"] ]);
  Phroses\JsonOutput(["type" => "success"], 200);
});
?>

<div class="container">
  

  <div id="saved">
    Saved Settings!
  </div>
  <div>
    <strong>Change Theme: </strong>
    <select id="theme_selector">
      <?php
      foreach(Phroses\Theme::List() as $thm) {
      ?><option value="<?= $thm; ?>" <? if($thm == Phroses\SITE["THEME"]) { ?>selected<? } ?>><?= ucfirst($thm); ?></option><?
      }
      ?>
    </select>
  </div>

</div>