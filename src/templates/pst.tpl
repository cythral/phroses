<div id="pst">
  <a href="#" id="pst-delete" class="pst_btn" data-target="pst-ds" data-action="fadeIn">Delete</a>
  <a href="/admin/pages/<{var:id}>" id="pst-edit" class="pst_btn" data-target="pst-es" data-action="fadeIn">Edit</a>
</div>

<form id="pst-es" class="container screen">
  
  <h1>Page Editor</h1>
  <div id="pst-es-actions">
    <a id="pst-es-save" href="#" class="pst_btn" data-target="pst-es" data-action="submit">Save</a>
    <a id="pst-es-done" href="#" class="pst_btn" data-target="pst-es" data-action="fadeOut">Done</a>
  </div>
  <input type="hidden" name="id" value="<{var:id}>">
  <{var:fields}>
</form>

<form id="pst-ds" class="container screen">
  <h1>Are you sure?</h1>
  <p>You're about to permanently delete this page.  It cannot be recovered.</p>
  <a id="pst-ds-y" href="#" class="pst_btn txt" data-target="pst-ds" data-action="submit">Yes</a>
  <a id="pst-ds-n" href="#" class="pst_btn txt" data-target="pst-ds" data-action="fadeOut">No</a>
</form>

<div id="saved">saved</div>