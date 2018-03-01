<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Create Phroses Site</title>
    <style><{var::styles}></style>
  </head>
  <body class="aln-c">
    <div class="screen" id="install-welcome">
      <h1>Welcome</h1>
      <div></div>
    </div>
    <div id="install-flow">
      <h1 class="c">Create a Site</h1>
      <p>I couldn't find a site at <{var::url}>, so I'll help you create one.</p>
      
      <form action="" method="post" id="flow-site">
        <div class="form_icfix c aln-l">
          <div>Site Name:</div>
          <input class="form_field form_input" placeholder="Phroses" name="name" id="name" required>
          <div class="clear"></div>
        </div>
        
        <div class="form_icfix c aln-l">
          <div>Username:</div>
          <input class="form_field form_input" placeholder="Username" name="username" id="susername" required autocomplete="new-password">
          <div class="clear"></div>
        </div>
        
        <div class="form_icfix c aln-l">
          <div>Password:</div>
          <input class="form_field form_input" placeholder="Password" name="password" id="spassword" required type="password" autocomplete="new-password">
          <div class="clear"></div>
        </div>

        <br>
        <button class="pst_btn txt">
        Submit
        </button>

        <div class="ns-error"></div>
      </form>
    </div>
    
    <div id="flow-success" class="screen">
      <h2 class="c">Success</h2>
    </div>
    <script data-mode="installer"><{var::script}></script>
  </body>
</html>