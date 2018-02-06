
<br>

<div id="saved">Saved</div>
<div id="error">Error</div>

<div class="container">
    <h1 class="c panel-heading aln-c">~ Welcome ~</h1>
    
    <br>

    <div class="admin-stats container aln-c">
        <div class="stats-bubble"><span title="<{var::fullpagecount}>"><{var::pagecount}></span> Pages</div>
        <div class="stats-bubble"><span title="<{var::fullviewcount}>"><{var::viewcount}></span> Page Views</div>
    </div>

    <div class="admin-links">
        <a href="/admin/pages"><i class="fa fa-file"></i><span>Manage Pages</span></a>
        <a href="/admin/creds"><i class="fa fa-sign-in"></i><span>Manage Login</span></a>
        <a href="/admin/uploads"><i class="fa fa-upload"></i><span>Manage Uploads</span></a>
        <a href="/admin/update"><i class="fa fa-wrench"></i><span>Update Phroses</span></a>
    </div>

    <br>

    <div class="aln-c">
        <div class="form_icfix aln-l c theme-select">
            <div>Theme:</div>
            <select class="c form_field form_select" id="theme-selector" data-method="POST" data-url="/admin">
                <{array::themes::<option value="@name" @selected>@name</option>}>
                
            </select>
        </div>
    </div>
</div>