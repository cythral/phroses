
<div id="pst" class="<{var::pst_type}>">
    <div id="pst-vis" >
        <input type="checkbox" <{var::visibility}> id="vs-cb" data-method="PATCH" data-url="">
        <label for="vs-cb">Public</label>
    </div>
    <a href="#" id="pst-delete" class="pst_btn" data-target="pst-ds" data-action="fadeIn" data-scroll="off">Delete</a>
    <a href="#" id="pst-move" class="pst_btn" data-target="pst-ms" data-action="fadeIn" data-scroll="off">Move</a>
    <a href="#" id="pst-edit" class="pst_btn" data-target="pst-es" data-action="fadeIn" data-scroll="off">Edit</a>
    <a href="#" id="pst-new" class="pst_btn txt" data-target="pst-ns" data-action="fadeIn" data-scroll="off">New</a>
</div>
<div id="saved">saved</div>
<div id="error">error</div>


<input type="hidden" id="pid" value="<{var::id}>">

<form id="pst-es" class="container screen" data-method="PATCH" data-url="">
  <div id="pst-es-top">
    <input id="pst-es-title" name="title" placeholder="Page Title" value="<{var::title}>">
    <div id="pst-es-actions">
      <select id="pst-es-type" data-method="patch" data-url=""><{array::types::<option value="@type" @checked>@type</option>}></select>
      <a id="pst-es-save" href="#" class="pst_btn" data-target="pst-es" data-action="submit">Save</a>
      <a id="pst-es-done" href="#" class="pst_btn" data-target="pst-es" data-action="fadeOut" data-scroll="on">Done</a>
    </div>
  </div>
  
  <div id="pst-es-fields"><{var::fields}></div>
</form>

<form id="pst-ds" class="container screen" data-method="DELETE" data-url="">
  <h1>Are you sure?</h1>
  <p>You're about to permanently delete this page.  It cannot be recovered.</p>
  <a id="pst-ds-y" href="#" class="pst_btn txt" data-target="pst-ds" data-action="submit" data-scroll="on">Yes</a>
  <a id="pst-ds-n" href="#" class="pst_btn txt" data-target="pst-ds" data-action="fadeOut" data-scroll="on">No</a>
</form>

<form id="pst-ms" class="container screen" data-method="PATCH", data-url="">
	<h1>Move Page</h1>
	<p>You may change the URI of the page with this form.</p>
		<div class="container">
			<div class="form_icfix c aln-l">
				<div>URI:</div>
				<input id="puri" name="uri" class="form_input form_field" placeholder="Page URI" value="<{var::uri}>" autocomplete="off">     
			</div>
		</div>
    <a id="pst-ms-s" href="#" class="pst_btn txt" data-target="pst-ms" data-action="submit" data-scroll="on">Submit</a>
    <a id="pst-ms-c" href="#" class="pst_btn txt" data-target="pst-ms" data-action="fadeOut" data-scroll="on">Cancel</a>
</form>

<form id="pst-ns" class="container screen" data-method="POST" data-url="">
	<h1>Create a New Page</h1>
	<div class="container">
		<div class="form_icfix c aln-l">
			<div>Title:</div>
			<input name="title" class="form_input form_field" placeholder="Page Title" autocomplete="off">
		</div>
		<div class="form_icfix c aln-l">
			<div>Type:</div>
			<select name="type" class="c form_select form_field">
				<option value="" disabled selected>Select a Page Type</option>
				<{array::types::<option value="@type">@type</option>}>
			</select>
		</div>
	</div>
	<a id="pst-ns-s" href="#" class="pst_btn txt" data-target="pst-ns" data-action="submit" data-scroll="on">Submit</a>
  <a id="pst-ns-c" href="#" class="pst_btn txt" data-target="pst-ns" data-action="fadeOut" data-scroll="on">Cancel</a>
</form>
<script> </script><!-- LEAVE THIS HERE (see https://bugs.chromium.org/p/chromium/issues/detail?id=332189) -->