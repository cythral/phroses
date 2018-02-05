<h1 class="c panel-heading">Phroses Panel Home</h1>
<br>

<div id="saved">Saved</div>
<div id="error">Error</div>

<div class="container">
    <div class="panel-row">
        <section class="panel-section panel-pages aln-c">
            <h2>Page Stats</h2>
            <div class="panel-pages-line"><span><{var::pagecount}></span> Pages</div>
            <div class="panel-pages-line"><span><{var::viewcount}></span> Page Views</div>
            <br><a href="/admin/pages">Manage Pages <i class="fa fa-chevron-right"></i></a>
        </section>
        <section class="panel-section">
            <div class="form_icfix aln-l c">
                <div>Theme:</div>
                <select class="c form_field form_select" id="theme-selector" data-method="POST" data-url="/admin">
                    <{array::themes::<option value="@name" @selected>@name</option>}>
                    
                </select>
            </div>
            <div class="aln-c bold"><br><a href="/admin/creds">Change Site Login <i class="fa fa-chevron-right"></i></a>
        </section>
    </div>
    <div class="clear"></div>
    <div class="admin-links">
        <a href="/admin/creds"><i class="fa fa-sign-in"></i><span>Manage Login</span></a>
        <a href="/admin/uploads"><i class="fa fa-upload"></i><span>Manage Uploads</span></a>
        <a href="/admin/update"><i class="fa fa-angle-double-up"></i><span>Update Phroses</span></a>
    </div>
</div>