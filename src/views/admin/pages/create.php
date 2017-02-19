<div class="container">
    <div>
        <h2>You're creating a new page on <a href="/"><?= Phroses\SITE["NAME"]; ?></a>.</h2>
        <p>I just need a few details.</p>
    </div>
    
    <br>
    
    <form id="phroses_creator" class="sys form" data-uri="/admin/create" data-method="POST">
        <div class="form_icfix">
            <div>Title:</div>
            <input name="title" required type="text" placeholder="Page Title" class="form_input form_field" autocomplete="off">
        </div>
        
        <div class="form_icfix">
            <div>URI:</div>
            <input id="pageuri" name="uri" required type="text" placeholder="Page URI" class="form_input form_field" autocomplete="off" value="<?= $_GET["uri"] ?? "" ?>">
        </div>
        
        <div class="form_icfix">
            <div>Type:</div>
            <select name="type" class="form_select form_field" required>
                <option value="">Select a Page Type.</option>
                <?php foreach($theme->GetTypes() as $type) { ?>
                <option value="<?= $type; ?>"><?= ucfirst($type); ?></option>
                <? } ?>
            </select>
        </div>
        <div class="aln-c"><button></button></div>
    </form>

</div>

