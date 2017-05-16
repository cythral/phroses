<div id="pst">
  <a href="#" id="pst-delete" class="pst_btn">Delete</a>
  <a href="/admin/pages/<{var:id}>" id="pst-edit" class="pst_btn" data-target="pst-es" data-action="fadeIn">Edit</a>
</div>

<form id="pst-es" class="container">
  
  <h1>Page Editor</h1>
  <div id="pst-es-actions">
    <a id="pst-es-save" href="#" class="pst_btn" data-target="pst-es" data-action="submit">Save</a>
    <a id="pst-es-done" href="#" class="pst_btn" data-target="pst-es" data-action="fadeOut">Done</a>
  </div>
  <input type="hidden" name="id" value="<{var:id}>">
  <{var:fields}>
</form>

<div id="saved">saved</div>