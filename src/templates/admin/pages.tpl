<div class="container pages">
    <div class="pages-top">
        <a class="phr-btn btn pull-r" href="#" id="new" data-target="phr-new-page" data-action="fadeIn"><i class="fa fa-plus"></i> New Page</a>
        <h1 class="c">Pages</h1>
        <div class="clear"></div>
    </div>

    <{var::empty}>

    <{array::pages::
        <a href="@uri" class="page_item" data-id="@id"><strong>@title</strong> @uri 
            <div class="pull-r">    
                <select class="pageman-select">
                    @types
                </select>
                <i class="pageman-delete fa fa-times"></i>
            </div>
        </a>
    }>
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