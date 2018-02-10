<div class="admin-page uploads">
    <div class="admin-page-top">
        <h2>Uploads</h2>
        <a class="pst_btn lefttooltip" data-target="upload" data-action="fadeIn"><i class="ci ci-new"></i></a>
    </div>

    <ul>
        <{array::files::
            <li class="upload" data-filename="@filename">
                <input value="@filename" data-method="post"> 
                <div class="upload-icons">
                    <a href="/uploads/@filename" class="fa fa-link"></a>
                    <a href="#" class="fa fa-search-plus"></a>
                    <a href="#" class="fa fa-times upload-delete" data-method="post"></a>
                </div>
            </li>}>
    </ul>
</div>

<div id="preview" class="container screen aln-c">
    <div><img src="https://www.adcosales.com/files/products/no-preview-available.jpg"></div>
    <a id="seefull" class="c" href="https://www.adcosales.com/files/products/no-preview-available.jpg">Go to file <i class="fa fa-chevron-right"></i></a>
    
    <div class="actions">
        <a class="pst_btn" data-target="preview" data-action="fadeOut"><i class="ci ci-close"></i></a>
    </div>
</div>


<form id="upload" class="container screen aln-c">
    <input id="file" name="file" type="file">
    <label for="file"><strong>Choose a file</strong> or drag it here</label>

    <div id="upload-namer">
        <div class="container">
            <h2>Name the file:</h2>
            <div class="form_icfix c aln-l">
                <div>Filename:</div>
                <input required name="filename" class="form_input form_field" placeholder="filename.jpg" autocomplete="off">     
            </div>

            <a class="pst_btn txt" data-target="upload" data-action="submit">Upload</a>

            <div class="phr-progress"><div class="phr-progress-bar"></div></div>
        </div>
    </div>
</form>
