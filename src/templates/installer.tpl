<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
   <title>Install Phroses</title>
    <style><{var::styles}></style>
  </head>
  <body class="aln-c">
    <div class="screen" id="install-welcome">
      <h1>Welcome</h1>
      <div></div>
    </div>
    <div id="install-flow">
      <h1 class="c">Install Phroses</h1>
      
      <form class="flow" id="flow-db" action="" method="post">
        <h2>1. Setup Database</h2>
        <p>I need your database credentials</p>
        
        <div class="form_icfix c aln-l">
          <div>Host:</div>
          <input class="form_field form_input" placeholder="Host" name="host" value="localhost" id="host" required autocomplete="off">  
          <div class="clear"></div>
        </div>

        <div class="form_icfix c aln-l">
          <div>Database:</div>
          <input class="form_field form_input" placeholder="Database" name="database" id="database" required autocomplete="off">  
          <div class="clear"></div>
        </div>

        <div class="form_icfix c aln-l">
          <div>Username:</div>
          <input class="form_field form_input" placeholder="Username" name="username" id="username" required autocomplete="new-password">  
          <div class="clear"></div>
        </div>

         <div class="form_icfix c aln-l">
          <div>Password:</div>
          <input class="form_field form_input" placeholder="Password" name="password" id="password" type="password" autocomplete="new-password">  
          <div class="clear"></div>
        </div>
        <br>
        <input type="submit" class="pst_btn txt" value="Next">
      </form>
     
      <form action="" method="post" id="flow-site">
        <h2>2. Site Specifics</h2>
        <p>Lets set up your site together xoxo</p>
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
        Install
        </button>

        <div class="ns-error"></div>
      </form>
    </div>
    
    <div id="flow-db-error" class="screen">
      <h2 class="c">Oops..</h2>
      <br>
      A minimum MySQL version of <span id="flow-db-error-ver"></span> is required to run Phroses.
    </div>
    
    <div id="flow-success" class="screen">
      <h2 class="c">Success</h2>
    </div>
    <script data-mode="installer"><{var::script}></script>
  </body>
</html>