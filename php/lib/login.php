<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function print_login_form() {
  $url = $_SERVER['SERVER_NAME'] . $_SERVER['PHP_SELF'];
  $url = strtok($url, '?');
  echo <<<EOL
<table>
<tr>
<td background='img/logos/Veil_nebula_modified.png' width='520px' height='320px' valign='bottom' style='padding: 20px;'>
  <span style='letter-spacing: 0.1em; font-size: 4em; font-weight: bold;'>Heart of Rana</span><br>
  <span style='letter-spacing: 0.1em; font-size: 1em; font-weight: bold;'>Version 0.1</span>
</td>

</tr>
<tr><td>
    
    <div class='box'>
      <h2>Existing account</h2>
      <form action="http://$url" method="post">
      Login: <input name="login" size='18'>
      Password: <input type="password" name="password" size='18'>
      <input type="submit" value="Login">
      </form>
    </div>
    
</td></tr>
<tr><td>
    
    <div class='box'>
      <h2>New account</h2>
      <form action="basic/news.php" method="post">
        Email address: <input name="login" size='30'><br>
      Login: <input name="login" size='18'>
      Password: <input type="password" name="password" size='18'>
      <input type="submit" value="Login">
      </form>
    </div>
    
</td></tr>
</table>
EOL;
}

function check_login() {
  
}

function print_login_failed() {
  echo <<<EOHTML
<div>
You need to login with proper credentials.
</div>
EOHTML;
}

