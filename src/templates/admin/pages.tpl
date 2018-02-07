<div class="container pages admin-page">
    <div class="admin-page-top">
        <h2 class="c">Pages</h2>
        <a class="pst_btn lefttooltip ci ci-new" href="#" data-target="phr-new-page" data-action="fadeIn">New</a>
    </div>

    <{var::empty}>

    <ul>
    <{array::pages::
        <li class="page-item jlink" data-href="@uri" data-id="@id">
            <strong class="title">@title</strong>
            <span class="uri">@uri</span>
            <div class="page-item-options">    
                <select class="pageman-select" data-method="PATCH" data-url="@uri">
                    @types
                </select>
                <i class="pageman-delete fa fa-times" data-method="DELETE" data-url="@uri"></i>
            </div>
        </li>
    }>
    </ul>
</div>

<div id="phr-new-page" class="container screen">
    <h1>Enter new page URI:</h1>
    <div class="form_icfix c aln-l">
        <div>URI:</div>
        <input name="title" class="form_input form_field" placeholder="/" autocomplete="off">
    </div>
    <br>
    <a href="#" class="pst_btn txt" data-target="phr-new-page" data-action="submit">Go</a>
    <a href="#" class="pst_btn txt" data-target="phr-new-page" data-action="fadeOut">Cancel</a>
</div>