<?php

use Phroses\{Theme, DB};
use function Phroses\{HandleMethod, JsonOutputSuccess};
use const Phroses\SITE;

HandleMethod("POST", function() {
  DB::Query("UPDATE `sites` SET `theme`=? WHERE `id`=?", [ $_POST["theme"], SITE["ID"] ]);
  JsonOutputSuccess();
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
      foreach(Theme::List() as $thm) {
      ?><option value="<?= $thm; ?>" <? if($thm == SITE["THEME"]) { ?>selected<? } ?>><?= ucfirst($thm); ?></option><?
      }
      ?>
    </select>
  </div>

</div>