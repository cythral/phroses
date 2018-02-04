<div class="container">
    <form id="phroses_site_creds" class="sys form" data-method="POST" data-url="/admin/creds">
        <h1 class="c">
        Change Site Credentials
        </h1>
        <div id="saved">Saved Credentials!</div>
        <div id="error"></div>
        
        <section>
        <div class="form_icfix c">
            <div>Username:</div>
            <input name="username" required placeholder="Username" class="form_input form_field" autocomplete="off" value="<{var::username}>">
        </div>
        </section>
        
        <section>
        <h2 class="c">
            Password
        </h2>
        <br>
        <div class="form_icfix c">
            <div>Old:</div>
            <input name="old" type="password" placeholder="Old Password" class="form_input form_field" autocomplete="off">
        </div>
        <div class="form_icfix c">
            <div>New:</div>
            <input name="new" type="password" placeholder="New Password" class="form_input form_field" autocomplete="off">
        </div>
        <div class="form_icfix c">
            <div>Repeat:</div>
            <input name="repeat" type="password" placeholder="Repeat Password" class="form_input form_field" autocomplete="off">
        </div>
        </section>
        <div class="aln-c">
            <a href="#" class="pst_btn txt" data-target="phroses_site_creds" data-action="submit">Submit</a>
        </div>
    </form>
</div>