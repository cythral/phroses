<?php
use Phroses\Phroses;
use Phroses\Exceptions\WriteException;
use function Phroses\{sendEvent, rrmdir};
use const Phroses\{ROOT, VERSION, INCLUDES, IMPORTANT_FILES};

$version = json_decode(@file_get_contents("http://api.phroses.com/version"))->latest_version ?? null;

if($version == null) { ?>
<div id="phr-update-apier" class="container aln-c phr-update c">
  <h1>
    Having some trouble accessing the API.  Please try again later.
  </h1>
</div>
<? } else if(version_compare(VERSION, $version, "<")) { ?>

  <div id="phr-update-avail" class="container aln-c phr-update">
    <h1 class="c">
      An update is available
    </h1>
    <div class="phr-update-icon">
      <img src="/phr-assets/img/update-ring.png">
      <img src="/phr-assets/img/update-arrow.png">
    </div>
    <p class="c">
      click the above icon to start updating
    </p>
  </div>

  <div id="phr-upgrade-screen" class="container screen">
    <h1>
      Updating Phroses
    </h1>
    <div class="phr-progress"><div class="phr-progress-bar"></div></div>
    <p class="phr-progress-error"></p>
  </div>

<? } else { ?>
  <div id="phr-update-noavail" class="container aln-c phr-update c">
    <h1>
      Phroses is up-to-date
      <div>
        <img src="/phr-assets/img/checkmark.png" style="width:250px;height:250px;">
      </div>
    </h1>
  </div>
 <? }