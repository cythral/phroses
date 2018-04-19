<div class="phroses-container">
  <div id="pst" class="<{var::pst_type}>">
      <a href="#" id="pst-delete" class="pst_btn" data-target="pst-ds" data-action="fadeIn" data-scroll="off"><i class="ci ci-delete"></i></a>
      <a href="#" id="pst-metadata-trg" class="pst_btn multiViewTrigger" data-view="metadata"><i class="ci ci-urlmove"></i></a>
      <a href="#" id="pst-edit-trg" class="pst_btn multiViewTrigger" data-view="edit"><i class="ci ci-edit"></i></a>
      <a href="#" id="pst-save" class="pst_btn" data-target="pst-edit" data-action="submit"><i class="ci ci-save"></i></a>
      <a href="#" id="pst-new" class="pst_btn" data-target="pst-ns" data-action="fadeIn" data-scroll="off"><i class="ci ci-new"></i></a>
  </div>
  <div id="saved">saved</div>
  <div id="error">error</div>

  <input type="hidden" id="pid" value="<{var::id}>">

  <form id="pst-ds" class="container screen" data-method="DELETE" data-url="" tabindex="0">
    <h1>Are you sure?</h1>
    <p>You're about to permanently delete this page.  It cannot be recovered.</p>
    <a id="pst-ds-y" href="#" class="pst_btn txt screen-enter" data-target="pst-ds" data-action="submit" data-scroll="on">Yes</a>
    <a id="pst-ds-n" href="#" class="pst_btn txt screen-escape" data-target="pst-ds" data-action="fadeOut" data-scroll="on">No</a>
  </form>

  <form id="pst-ms" class="container screen" data-method="PATCH", data-url="" tabindex="0">
    <h1>Move Page</h1>
    <p>You may change the URI of the page with this form.</p>
      <div class="container">
        <div class="form_icfix c aln-l">
          <div>URI:</div>
          <input id="puri" name="uri" class="form_input form_field" placeholder="Page URI" value="<{var::uri}>" autocomplete="off">     
        </div>
      </div>
      <a id="pst-ms-s" href="#" class="pst_btn txt screen-enter" data-target="pst-ms" data-action="submit" data-scroll="on">Submit</a>
      <a id="pst-ms-c" href="#" class="pst_btn txt screen-escape" data-target="pst-ms" data-action="fadeOut" data-scroll="on">Cancel</a>
  </form>

  <form id="pst-ns" class="container screen" data-method="POST" data-url="" tabindex="0">
    <h1>Create a New Page</h1>
    <div class="container">
      <div class="form_icfix c aln-l">
        <div>Title:</div>
        <input name="title" class="form_input form_field" placeholder="Page Title" autocomplete="off" required>
      </div>
      <div class="form_icfix c aln-l">
        <div>Type:</div>
        <select name="type" class="c form_select form_field" required>
          <option value="" disabled selected>Select a Page Type</option>
          <{array::types::<option value="@type">@type</option>}>
        </select>
      </div>
    </div>
    <a id="pst-ns-s" href="#" class="pst_btn txt screen-enter" data-target="pst-ns" data-action="submit" data-scroll="on">Submit</a>
    <a id="pst-ns-c" href="#" class="pst_btn txt screen-escape" data-target="pst-ns" data-action="fadeOut" data-scroll="on">Cancel</a>
  </form>

  <form id="pst-edit" class="container screen pst-content" data-method="PATCH" data-url="" data-view="mode-content">
    <div class="mode-switcher">
      <div class="mode mode-content" data-view="mode-content">Content</div>
      <div class="mode mode-style" data-view="mode-style">style</div>
    </div>
    
    <div id="mode-content" class="mode-view">
        <{var::fields}>
    </div>

    <div id="mode-style" class="mode-view">
      <div class="editor" id="css-editor" data-mode="css"><{var::css}></div>
    </div>
  </form>


  <form id="pst-metadata" class="container screen" data-method="PATCH" data-url="" tabindex="0">
    <div class="form_icfix c aln-l">
        <div>Title:</div>
        <input id="pst-es-title" name="title" class="title form_input form_field" placeholder="Page Title" value="<{var::title}>" autocomplete="off">     
    </div>

    <div class="form_icfix c aln-l">
      <div>URI:</div>
      <input id="puri" name="uri" class="uri form_input form_field" placeholder="Page URI" value="<{var::uri}>" autocomplete="off">     
    </div>
    <div class="form_icfix c aln-l">
      <div>Type:</div>
      <select id="pst-es-type" name="type" dir="rtl" class="type c form_select form_field" data-method="patch" data-url=""><{array::types::<option value="@type" @checked>@type</option>}></select>
    </div>

    <div class="form_icfix c aln-l">
      <div>Public:</div>
      <div class="checkbox">
        <input type="checkbox" <{var::visibility}> id="pst-pub" name="public">
        <div><div></div></div>
      </div>
    </div>
  </form>
</div>
<script> </script><!-- LEAVE THIS HERE (see https://bugs.chromium.org/p/chromium/issues/detail?id=332189) -->